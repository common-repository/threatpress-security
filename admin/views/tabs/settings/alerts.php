<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

$tform->table_header();

$tform->table_row(
    __( 'Alerts', 'threatpress' ), array( 'for' => 'status' ),
    $tform->light_switch( 'alerts', 'status' ), __('Enable security scanner email alerts.', 'threatpress')
);

$tform->table_row(
    __( 'Email', 'threatpress' ), array( 'for' => 'email' ),
    $tform->textinput( 'alerts', 'email' ), __('Please enter your e-mail address that you check frequently.', 'threatpress')
);

$tform->table_footer();
