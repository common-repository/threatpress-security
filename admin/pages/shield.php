<?php

$options = get_option( 'threatpress_shield' );

$tform = ThreatPress_Admin_Form::get_instance();

$tform->admin_header( true, 'threatpress_shield' );

$tabs = new ThreatPress_Admin_Option_Tabs( 'shield' );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'limit-login-attempts', __( 'Login Protection', 'threatpress' ) ) );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'lockouts', __( 'Lockouts', 'threatpress' ) ) );
$tabs->add_tab( new ThreatPress_Admin_Option_Tab( 'passwords', __( 'Passwords', 'threatpress' ) ) );

$tabs->display( $tform, $options );

$tform->admin_footer();

