<?php

$options = get_option( 'threatpress' );

$tform = ThreatPress_Admin_Form::get_instance();

$tform->admin_header( true, 'threatpress' );

$tabs = new ThreatPress_Admin_Option_Tabs( 'dashboard' );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'dashboard', __( 'Dashboard', 'threatpress' ) ) );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'diagnostic', __( 'Diagnostic', 'threatpress' ) ) );

$tabs->display( $tform, $options );

$tform->admin_footer();
