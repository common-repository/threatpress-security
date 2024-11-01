jQuery( document ).ready(function($) {
    // Dimiss admin notice
    threatpress_ajax_dismiss_admin_notice();
});

/*
 * AJAX: Dismiss admin notice
 */
function threatpress_ajax_dismiss_admin_notice() {
    jQuery( '.threatpress-notice' ).on('click', '.notice-dismiss', function ( event ) {
        var security = jQuery("input[name='_threatpress_dismiss_admin_notice_nonce']").val();

        var data = {
            action: 'threatpress_dismiss_admin_notice',
            security: security
        };

        jQuery.post(ajaxurl, data, function(response) {
            // Done
        });
    });
}