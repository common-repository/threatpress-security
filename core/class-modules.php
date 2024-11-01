<?php

/**
 * Loads the modules.
 */
class ThreatPress_Core_Modules {

    /**
     * Class constructor
     */
    public function __construct() {
        self::load_modules();
    }

    private static function load_modules() {
        $options = ThreatPress_Admin_Options::get_all();

        new ThreatPress_Module_Dashboard( $options );
        new ThreatPress_Module_Diagnostic( $options );

        // Scanner
        new ThreatPress_Module_Scanner( array_merge( $options['scanner'], $options['alerts'] ) );

        // Shield tab
        // Login Protection
        if ( $options['limit_login_attempts']['enable'] === 'on' )
            new ThreatPress_Module_Limit_Login_Attempts( $options['limit_login_attempts'] );

        new ThreatPress_Module_Lockouts();

        // Settings tab
        // Passwords
        if ( $options['passwords']['password_expiration'] === 'on' )
            new ThreatPress_Module_Passwords( $options['passwords'] );

    }

}