<?php
/*
Plugin Name: TranslatePress - Language by GET parameter Add-on
Plugin URI: https://translatepress.com/
Description: Extends the functionality of TranslatePress by enabling the language in the URL to be encoded as a GET parameter instead of directory.
Version: 1.0.3
Author: Cozmoslabs, Razvan Mocanu
Author URI: https://translatepress.com/
License: GPL2

== Copyright ==
Copyright 2018 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'TRP_GP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TRP_GP_PLUGIN_VERSION', '1.0.1' );

function is_rest() {
	if (defined('REST_REQUEST') && REST_REQUEST // (#1)
			|| isset($_GET['rest_route']) // (#2)
					&& strpos( $_GET['rest_route'] , '/', 0 ) === 0)
			return true;

	// (#3)
	global $wp_rewrite;
	if ($wp_rewrite === null) $wp_rewrite = new WP_Rewrite();
		
	// (#4)
	$rest_url = wp_parse_url( trailingslashit( rest_url( ) ) );
	$current_url = wp_parse_url( add_query_arg( array( ) ) );
	return strpos( $current_url['path'] ?? '/', $rest_url['path'], 0 ) === 0;
}

function trp_gp_is_tp_active() {
// If TP is not active, do nothing
	if ( class_exists( 'TRP_Translate_Press' ) ) {
		return true;
	}else{
		return false;
	}

}

/**
 * Returns filtered parameter name
 *
 */
function trp_gp_get_parameter_name(){
	return apply_filters( 'trp_gp_lang_parameter', 'lang' );
}


/**
 * Returns language from the given url if it is encoded into GET parameter
 *
 * Returns the url-slug, not the language code
 *
 * @param $lang
 * @param $url
 *
 * @return string
 */
function trp_gp_get_lang_from_get( $lang, $url ){
	if (is_rest()) {
		$lang_parameter = trp_gp_get_parameter_name();
		parse_str(parse_url( $url, PHP_URL_QUERY), $get );
		if ( isset ( $get[ $lang_parameter ] ) && $get[ $lang_parameter ] != '' ){
			$lang = sanitize_text_field( trim( $get[ $lang_parameter ], '/' ) );
		}else{
			$lang = null;
		}

		return $lang;
	}

	return $lang;
}
add_filter( 'trp_get_lang_from_url_string', 'trp_gp_get_lang_from_get', 10, 2 );

/**
 * Add script to set cookie to change language.
 * Only active when TP ADL Add-on is active.
 */
function trp_gp_cookie_adding(){
	if (is_rest()) {
		if ( ! trp_gp_is_tp_active() ){
			return;
		}
	
		// dependent on TP Add-on Automatic Detection Language
		wp_enqueue_script( 'trp-gp-language-cookie', TRP_GP_PLUGIN_URL . 'assets/js/trp-gp-language-cookie.js', array( 'jquery', 'trp-language-cookie' ), TRP_GP_PLUGIN_VERSION );
		wp_localize_script( 'trp-gp-language-cookie', 'trp_gp_language_cookie_data', array( 'lang_parameter' => trp_gp_get_parameter_name() ) );
	}
	
}
add_action( 'wp_enqueue_scripts', 'trp_gp_cookie_adding' );

/**
 * Encode the given url to contain GET parameter for language.
 *
 * If no language code is given, uses default language.
 * The url slug is encoded to the link, not the language code.
 *
 * @param string $link                  Link to encode.
 * @param string $language_code         Refers to the language code, not language url slug.
 *
 * @return string                       Encoded link.
 */
function trp_gp_add_language_param_to_link( $link, $language_code = null ){
	if ( ! trp_gp_is_tp_active() ){
		return $link;
	}
	global $TRP_LANGUAGE;
	if ( $language_code == null ){
		$language_code = $TRP_LANGUAGE;
	}
	$trp = TRP_Translate_Press::get_trp_instance();
	$trp_settings = $trp->get_component( 'settings' );
	$settings = $trp_settings->get_settings();
	$lang_parameter = trp_gp_get_parameter_name();
	if ( isset( $settings['add-subdirectory-to-default-language'] ) && $settings['add-subdirectory-to-default-language'] == 'no' && $language_code == $settings['default-language'] ) {
		return remove_query_arg( $lang_parameter, $link );
	}
	if ( $language_code ) {
		// this could be called inside get_permalink, and $TRP_LANGUAGE temporarily doesn't have value in order to return the default url
		$url_slug = $settings['url-slugs'][ $language_code ];
		$link = add_query_arg( $lang_parameter, $url_slug, $link );
	}
	return $link;
}

add_filter('app_builder_prepare_settings_data', 'trp_prepare_template_object', 100, 1);
// product data
add_filter( 'app_builder_prepare_product_object', 'trp_prepare_product_object', 100, 3);

// product category data
add_filter( 'app_builder_prepare_product_category_name', 'trp_prepare_product_category_name', 100, 1);

// variable and attribute
add_filter( 'app_builder_prepare_product_option_object', 'trp_prepare_product_option_object', 100, 1);
add_filter( 'app_builder_prepare_product_attribute_object', 'trp_prepare_product_attribute_object', 100, 1);
add_filter( 'trp_prepare_product_attribute_text', 'trp_prepare_product_attribute_text', 100, 1);
add_filter( 'wpml_translate_single_string', 'trp_prepare_product_attribute_text', 100, 1);

// post data
add_filter( 'app_builder_prepare_post_object', 'trp_prepare_post_object', 100, 1);

function trp_prepare_template_object($data) {
	$trp_obj = TRP_Translate_Press::get_trp_instance();
    $settings_obj = $trp_obj->get_component('settings');
    $lang_obj = $trp_obj->get_component('languages');

    $published_lang = $settings_obj->get_setting('publish-languages');
	$default_lang = $settings_obj->get_setting('default-language');


    $slugs = $settings_obj->get_setting('url-slugs');

	$iso_lang = $lang_obj->get_iso_codes($published_lang);
    $name_translate = $lang_obj->get_language_names($published_lang, 'english_name');
    $name_native = $lang_obj->get_language_names($published_lang, 'native_name');

	$languages = [];
	if (isset( $published_lang ) ) {
		foreach ( $published_lang as $value ) {
			$key = $slugs[$value];
			$languages[$key] = array(
				"code"              => $key,
				"native_name"       => $name_native[$value],
				"default_locale"    => $value,
				"translated_name"   => $name_translate[$value],
				"language_code"     => $iso_lang[$value],
			);
			if ($value == $default_lang) {
				$default_lang = $key;
			}
		}
	}
	
	$data['languages'] = $languages;
	$data['language'] = $default_lang;

	return $data;
}

function trp_prepare_product_object( $data, $post, $request) {
    $data['name'] = trp_translate($data['name'], null , false);
    $data['sku'] = trp_translate($data['sku'], null , false);
    $data['button_text'] = trp_translate($data['button_text'], null , false);

	$categories = [];
	if (isset( $data['categories'] ) ) {
		foreach ( $data['categories'] as $value ) {
			if ( isset( $value )) {
				$value['name'] = trp_translate($value['name'], null , false);
			}

			$categories[] = $value;
		}
	}
	$data['categories'] = $categories;

	$tags = [];
	if (isset( $data['tags'] ) ) {
		foreach ( $data['tags'] as $value ) {
			if ( isset( $value )) {
				$value['name'] = trp_translate($value['name'], null , false);
			}

			$tags[] = $value;
		}
	}
	$data['tags'] = $tags;

    return $data;
}

function trp_prepare_product_category_name( $name) {
    return trp_translate($name, null , false);
}

function trp_prepare_product_option_object( $term) {
	$term->name = trp_translate($term->name, null , false);
    return $term;
}

function trp_prepare_product_attribute_object( $response) {
	$response->data['name'] = trp_translate($response->data['name'], null , false);
    return $response;
}

function trp_prepare_product_attribute_text( $text) {
    return trp_translate($text, null , false);
}

function trp_prepare_post_object( $data ) {

    $data['post_title'] = trp_translate($data['post_title'], null , false);

	$categories = [];
	if (isset( $data['post_categories'] ) ) {
		foreach ( $data['post_categories'] as $value ) {
			if ( isset( $value )) {
				$value['name'] = trp_translate($value['name'], null , false);
			}

			$categories[] = $value;
		}
	}
	$data['post_categories'] = $categories;

	$tags = [];
	if (isset( $data['post_tags'] ) ) {
		foreach ( $data['post_tags'] as $value ) {
			if ( isset( $value )) {
				$value['name'] = trp_translate($value['name'], null , false);
			}

			$tags[] = $value;
		}
	}
	$data['post_tags'] = $tags;

    return $data;
}