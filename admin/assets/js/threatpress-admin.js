jQuery( document ).ready(function($) {
    threatpress_tabs();
    threatpress_active_tab();
});

/*
 * Show/Hide admin tab content
 */
function threatpress_tabs() {
    jQuery("#threatpress-tabs").find("a").click(function() {
        jQuery("#threatpress-tabs").find("a").removeClass("nav-tab-active"), jQuery(".threatpress_tab").removeClass("active");
        var a = jQuery(this).attr("id").replace("-tab", "");
        jQuery("#" + a).addClass("active"), jQuery(this).addClass("nav-tab-active");
    });
}

/*
 * Show active tab content based on URL
 */
function threatpress_active_tab() {
    var d = window.location.hash.replace("#top#", "");

    if( "" !== d && "#_=_" !== d || (d = jQuery(".threatpress_tab").attr("id")) ) {
        jQuery("#" + d).addClass("activerr");
        jQuery("#" + d + "-tab").addClass("nav-tab-activerr");
        jQuery(".nav-tab-activerr").click();
    }
}
