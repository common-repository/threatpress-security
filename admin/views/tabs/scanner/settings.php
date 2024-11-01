<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

$tform->table_header();

$tform->table_row(
    __( 'Background scanner', 'threatpress' ), array( 'for' => 'enable' ),
    $tform->light_switch( 'scanner', 'enable' )
);

$tform->table_row(
    __( 'Vulnerability Scanner', 'threatpress' ), array( 'for' => 'vulnerabilities_db' ),
    $tform->checkbox( 'scanner', 'vulnerabilities_db' ),
    __( 'Checks your site for vulnerable versions of WordPress core, plugins and themes against ThreatPress Vulnerabilities Database.', 'threatpress' )
);

$tform->table_row(
    __( 'Advanced Site Scan', 'threatpress' ), array( 'for' => 'sitescan' ),
    $tform->checkbox( 'scanner', 'sitescan' ),
    __( 'Checks your site against several databases for malware, blacklisting status, phishing status, injected spam and errors.', 'threatpress' )
);

$tform->table_row(
    __( 'Core integrity check', 'threatpress' ), array( 'for' => 'checksums' ),
    $tform->checkbox( 'scanner', 'checksums' ),
    __( 'Checks the integrity of WordPress core and helps to identify any injected malware or changes made to the core files.', 'threatpress' )
);

$tform->table_row(
    __( 'Schedule', 'threatpress' ), array( 'for' => 'schedule' ),
    $tform->select( 'scanner', 'schedule', array(
            'daily' => __( 'Once a day', 'threatpress' ),
            'twice_daily' => __( 'Twice a day', 'threatpress' )
        )
    )
);

$tform->table_footer();