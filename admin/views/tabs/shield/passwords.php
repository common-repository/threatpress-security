<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

$tform->table_header();

$tform->table_row(
    __( 'Password Expiration', 'threatpress' ), array( 'for' => 'password_expiration' ),
    $tform->light_switch( 'passwords', 'password_expiration' ),
    __( 'Force users to change their passwords after a certain number of days.', 'threatpress' )
);

$tform->table_row(
    __( 'Maximum Password Age', 'threatpress' ), array( 'for' => 'change_on_next_login' ),
    $tform->select( 'passwords', 'maximum_age', array( '30' => __( '30 days', 'threatpress' ), '60' => __( '60 days', 'threatpress' ), '120' => __( '120 days', 'threatpress' ) ) ),
    __( 'The maximum number of days a password may be kept before it expired.', 'threatpress' )
);

$tform->table_footer();