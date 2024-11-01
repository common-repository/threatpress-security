<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}
?>

<?php

if ( !defined( 'THREATPRESS_RECAPTCHA_VERSION' ) ) :
    echo '<p>' . sprintf( __( 'If you want to improve anti-spam protection, we recommend you to install our <a href="%s" target="_blank">Google Invisible reCaptcha by ThreatPress plugin.</a>', 'threatpress'), 'https://wordpress.org/plugins/invisible-recaptcha-by-threatpress/' ) . '</p>';
endif;

$tform->table_header();

$tform->table_row(
    __( 'Limit Login Attempts', 'threatpress' ), array( 'for' => 'enable' ),
    $tform->light_switch( 'limit_login_attempts', 'enable' )
);

$tform->table_row(
    __( 'Allowed retries', 'threatpress' ), array( 'for' => 'allowed_retries' ),
    $tform->number( 'limit_login_attempts', 'allowed_retries' )
);

$tform->table_row(
    __( 'Minutes lockout', 'threatpress' ), array( 'for' => 'minutes_lockout' ),
    $tform->number( 'limit_login_attempts', 'minutes_lockout' )
);

$tform->table_row(
    __( 'Lockouts to increase time', 'threatpress' ), array( 'for' => 'lockouts_to_increase_time' ),
    $tform->number( 'limit_login_attempts', 'lockouts_to_increase_time' )
);

$tform->table_row(
    __( 'Increase lockout time to', 'threatpress' ), array( 'for' => 'increase_lockout_time_to' ),
    $tform->number( 'limit_login_attempts', 'increase_lockout_time_to' ) . ' ' . __('hours', 'threatpress')
);

$tform->table_row(
    __( 'Hours until retries are reset', 'threatpress' ), array( 'for' => 'hours_until_retries_are_reset' ),
    $tform->number( 'limit_login_attempts', 'hours_until_retries_are_reset' )
);

$tform->table_footer();
