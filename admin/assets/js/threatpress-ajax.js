jQuery( document ).ready(function($) {
    // Scanner
    threatpress_ajax_scanner();

    // Limit login attempts
    threatpress_ajax_remove_lockout();
});

/*
 * AJAX: Scanner
 */
function threatpress_ajax_scanner() {
    jQuery( "#scanning").hide();

    jQuery( "input[name='threatpress_ajax[run_scanner]']" ).on( "click", function( event ) {
        jQuery('#run-scanner-btn').prop( "disabled", true );
        jQuery( "#scanning").show();

        var security = jQuery("input[name='_scanner_nonce']").val();

        jQuery.post( ajaxurl, { action: 'threatpress_scanner', security: security }, function( response ) {
            // Scan
        });

        // Call every 2 seconds and get scan progress
        var scanInterval = window.setInterval(function() {
            jQuery.post( ajaxurl, { action: 'threatpress_scanner_status', security: security }, function( status ) {
                jQuery('.scanning-text').empty().text( status.log + "...\r\n" );

                // Scan done
                if ( status.status == 100 ) {
                    window.clearInterval( scanInterval );

                    jQuery('#run-scanner-btn').prop( "disabled", false );
                    jQuery( "#scanning").hide();

                    location.reload();
                }
            });
        }, 2000);
    });
}

/*
 * AJAX: Remove lockout record
 */
function threatpress_ajax_remove_lockout() {
    jQuery( "input[name='threatpress_ajax[remove_lockout]']" ).on( "click", function( event ) {
        var IDs = [];

        jQuery("input[name='id[]']:checked").each(function() {
           IDs.push(jQuery(this).val());
        });

        var security = jQuery("input[name='_remove_lockout_nonce']").val();

        var data = {
            'action': 'threatpress_remove_lockout',
            'ids' : IDs,
            'security': security
        };

        jQuery.post(ajaxurl, data, function(response) {
            jQuery.each( IDs, function( key, ID ) {
                jQuery("input[type='checkbox'][value="+ ID +"]").parent().parent().css( 'background', 'red').fadeOut( 1000 );
            });
        });
    });
}