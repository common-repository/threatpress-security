<?php

$options = get_option( 'threatpress_scanner' );
$threat_status = (boolean) get_option( 'threatpress_threat_status' );
$threats = get_option( 'threatpress_threats' );
$scan_time = get_option( 'threatpress_scan_time' );

$tform = ThreatPress_Admin_Form::get_instance();

$tform->admin_header( true, 'threatpress_scanner' );

$tabs = new ThreatPress_Admin_Option_Tabs( 'scanner' );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'scanner', __( 'Scanner', 'threatpress' ) ) );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'settings', __( 'Settings', 'threatpress' ) ) );

$tabs->display( $tform, $options, array( 'threat_status' => $threat_status, 'threats' => $threats, 'last_scan_time' => $scan_time ) );

$tform->admin_footer();

