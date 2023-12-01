=== TranslatePress - Language by GET parameter Add-on ===
Contributors: cozmoslabs, razvan.mo
Donate link: https://www.cozmoslabs.com/
Tags: translate, translation, multilingual, automatic translation, front-end translation, google translate, bilingual, get parameter,
Requires at least: 3.1.0
Tested up to: 4.9.8
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

TranslatePress Add-on that enables the language in the URL to be encoded as a GET Parameter.

== FAQ ==

1.How to change the GET parameter name?
Changing the parameter name can be done using a bit of coding:

 - Create an empty plugin like this: https://gist.github.com/sareiodata/76f701e01db6685829db

 - Add the following code to the end of it:

add_filter( 'trp_gp_lang_parameter', 'trp_gp_change_parameter_name' );
function trp_gp_change_parameter_name( $name ){
	return 'language';
}

 - Install this plugin via FTP (copy it inside wp-content/plugins) or create a zip archive with it and install it via the WordPress plugin upload functionality


== Changelog ==
= 1.0.3 =
* Fixed issues when TranslatePress core is deactivated

= 1.0.2 =
* Fixed compatibility issues with TP Add-on Automatic Language Detection

= 1.0.1 =
* Added support for TP Add-on Automatic Language Detection
