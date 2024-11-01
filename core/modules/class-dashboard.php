<?php
/**
 * Module name: Dashboard
 *
 */
class ThreatPress_Module_Dashboard {

    private static $options;
    private static $problems;

    /**
     * Class constructor
     */
    public function __construct( $options ) {

    }

    /**
     * Get threat status
     *
     * @return bool
     *
     */
    public static function get_threat_status() {
        return (boolean) get_option( 'threatpress_threat_status' );
    }

    /**
     * Get the total number of problems
     *
     * @return int
     *
     */
    public static function get_problems_num() {
        $problems = 0;

        if ( self::get_threat_status() )
            $problems += 1;

        return $problems;
    }

}