<?php

$options = get_option( 'threatpress_settings' );

$tform = ThreatPress_Admin_Form::get_instance();

$tform->admin_header( true, 'threatpress_settings' );

$tabs = new ThreatPress_Admin_Option_Tabs( 'settings' );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'alerts', __( 'Alerts', 'threatpress' ) ) );

$tabs->display( $tform, $options );

$tform->admin_footer();

