/**
 * Set language Cookie.
 *
 * Only runs when TP Add-on Automatic Language Detection is active.
 *
 */
function TRP_GP_Language_Cookie(){
    var _this = this;
    var trpCookie;
    this.cookie_name = '';
    this.cookie_age = '';
    this.cookie_path = '';

    this.in_array = function (needle, haystack) {
        for(var i in haystack ) {
            if(haystack[i] == needle) {
                return true;
            }
        }
        return false;
    };

    this.array_search = function(val, array) {
        if(typeof(array) === 'array' || typeof(array) === 'object') {
            var rekey;
            for(var i in array) {
                if(array[i] == val) {
                    rekey = i;
                    break;
                }
            }
            return rekey;
        }
    };

    this.get_parameter_by_name = function(name, url){
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return null;
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    };

    this.get_lang_from_url = function( url ){
        var url_slug = _this.get_parameter_by_name( trp_gp_language_cookie_data['lang_parameter'],url );
        if ( url_slug && _this.in_array( url_slug, trp_language_cookie_data['url_slugs']) ){
            return _this.array_search(url_slug, trp_language_cookie_data['url_slugs'] );
        }
        return trp_language_cookie_data['default_language'];
    };

    this.initialize = function() {
        if (typeof TRP_Cookie !== "function" ) {
            return;
        }

        _this.cookie_name = trp_language_cookie_data['cookie_name'];
        _this.cookie_age = trp_language_cookie_data['cookie_age'];
        _this.cookie_path = trp_language_cookie_data['cookie_path'];
        trpCookie = new TRP_Cookie( );

        jQuery(document).on( 'click', 'a', function(e) {
            var clicked_url = jQuery(this).attr("href");
            var clicked_language = _this.get_lang_from_url( clicked_url );
            var trp_current_language = trpCookie.getCookie( _this.cookie_name );

            if ( trp_current_language != clicked_language ){
                trpCookie.setCookie( _this.cookie_name, clicked_language, _this.cookie_age, _this.cookie_path );
            }
        });
    };

    _this.initialize();
}

jQuery( function() {
    trpGPLanguageCookie = new TRP_GP_Language_Cookie();
});
