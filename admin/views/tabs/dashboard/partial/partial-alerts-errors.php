<?php

$messages = array();

$threat_check = ThreatPress_Module_Dashboard::get_threat_status();
$active_total = ThreatPress_Module_Dashboard::get_problems_num();

$type = 'alerts';

if ( $active_total >= 1 ) {
    $dashicon = 'warning';
    $i18n_title = __( 'Problems', 'threatpress' ) . ' (' . $active_total . ')';

    if ( $threat_check == true ) {
        $messages[] = sprintf(__('Security threats detected. For more information, please go to <a href="%s">ThreatPress &rarr; Scanner</a>', 'threatpress'), esc_url(admin_url('admin.php?page=threatpress_scanner')));
    }
} else {
    $dashicon = 'yes';
    $i18n_title = __( 'No threats', 'threatpress' );

    $messages[] = __('No security threats detected.', 'threatpress');
}

include 'partial-alerts-template.php';
