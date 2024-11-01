jQuery( document ).ready(function($) {
    threatpress_hide_save_button();
});

/*
 * Hide save button for Lockouts tab.
 */
function threatpress_hide_save_button() {
    var d = window.location.hash.replace("#top#", "");

    if( "" !== d && "#_=_" !== d || (d = jQuery(".threatpress_tab").attr("id"))){
        if(d == 'lockouts') {
            jQuery('.submit').hide();
        }
    }

    jQuery( ".nav-tab" ).click(function () {
        var a = jQuery(this).attr("id").replace("-tab", "");

        if( a != 'lockouts' ) {
            jQuery('.submit').show();
        } else {
            jQuery('.submit').hide();
        }
    });
}

