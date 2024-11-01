<?php

/**
 * Overall Option Management class
 *
 * Instantiates all the options and offers a number of utility methods to work with the options
 */
class ThreatPress_Admin_Options {

    /**
     * @var  array  Options this class uses
     *              Array format:  (string) option_name  => (string) name of concrete class for the option
     * @static
     */
    public static $options = array(
        'threatpress_scanner' => 'ThreatPress_Admin_Form_Option_Scanner',
        'threatpress_shield' => 'ThreatPress_Admin_Form_Option_Shield',
        'threatpress_settings'   => 'ThreatPress_Admin_Form_Option_Settings'
    );

    /**
     * @var  array   Array of instantiated option objects
     */
    protected static $option_instances = array();

    /**
     * @var  object  Instance of this class
     */
    protected static $instance;


    /**
     * Instantiate all the ThreatPress option management classes
     */
    protected function __construct() {

        foreach ( self::$options as $option_name => $option_class ) {
            $instance = call_user_func( array( $option_class, 'get_instance' ) );

            self::$option_instances[ $option_name ] = $instance;
        }

    }

    /**
     * Get the singleton instance of this class
     *
     * @return object
     */
    public static function get_instance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the group name of an option for use in the settings form
     *
     * @param  string $option_name the option for which you want to retrieve the option group name.
     *
     * @return  string|bool
     */
    public static function get_group_name( $option_name ) {
        if ( isset( self::$option_instances[ $option_name ] ) ) {
            return self::$option_instances[ $option_name ]->group_name;
        }

        return false;
    }

    /**
     * Get a specific default value for an option
     *
     * @param  string $option_name The option for which you want to retrieve a default.
     * @param  string $group_key   The group key.
     * @param  string $key         The key within the option who's default you want.
     *
     * @return  mixed
     */
    public static function get_default( $option_name, $group_key, $key ) {
        if ( isset( self::$option_instances[ $option_name ] ) ) {
            $defaults = self::$option_instances[ $option_name ]->get_defaults();
            if ( isset( $defaults[ $group_key ][ $key ] ) ) {
                return $defaults[ $group_key ][ $key ];
            }
        }

        return null;
    }

    /**
     * Get the instantiated option instance
     *
     * @param  string $option_name The option for which you want to retrieve the instance.
     *
     * @return  object|bool
     */
    public static function get_option_instance( $option_name ) {
        if ( isset( self::$option_instances[ $option_name ] ) ) {
            return self::$option_instances[ $option_name ];
        }

        return false;
    }

    /**
     * Retrieve an array of the options which should be included in get_all() and reset().
     *
     * @static
     * @return  array  Array of option names
     */
    public static function get_option_names() {
        static $option_names = array();

        if ( $option_names === array() ) {
            foreach ( self::$option_instances as $option_name => $option_object ) {
                if ( $option_object->include_in_all === true ) {
                    $option_names[] = $option_name;
                }
            }
        }

        return $option_names;
    }

    /**
     * Retrieve all the options for the ThreatPress plugin in one go.
     *
     * @static
     * @return  array  Array combining the values of all the options
     */
    public static function get_all() {
        return self::get_options( self::get_option_names() );
    }

    /**
     * Retrieve one or more options for the ThreatPress plugin.
     *
     * @static
     *
     * @param array $option_names An array of option names of the options you want to get.
     *
     * @return  array  Array combining the values of the requested options
     */
    public static function get_options( array $option_names ) {
        $options      = array();
        $option_names = array_filter( $option_names, 'is_string' );
        foreach ( $option_names as $option_name ) {
            if ( isset( self::$option_instances[ $option_name ] ) ) {
                $option  = self::get_option( $option_name );
                $options = array_merge( $options, $option );
            }
        }

        return $options;
    }

    /**
     * Retrieve a single option for the ThreatPress plugin.
     *
     * @static
     *
     * @param string $option_name the name of the option you want to get.
     *
     * @return array Array containing the requested option
     */
    public static function get_option( $option_name ) {
        $option = null;
        if ( is_string( $option_name ) && ! empty( $option_name ) ) {
            if ( isset( self::$option_instances[ $option_name ] ) ) {
                $option = get_option( $option_name );
            }
        }

        return $option;
    }

    /**
     * Check that all options exist in the database and add any which don't
     *
     * @return  void
     */
    public static function ensure_options_exist() {
        foreach ( self::$option_instances as $instance ) {
            $instance->maybe_add_option();
        }

        if ( ! get_option( 'threatpress_threat_status' ) )
            add_option( 'threatpress_threat_status', '0' );

        if ( ! get_option( 'threatpress_scan_time' ) )
            add_option( 'threatpress_scan_time', '' );
    }

    /**
     * Reset all options to their default values and rerun some tests
     *
     * @static
     * @return void
     */
    public static function reset() {
        $option_names = self::get_option_names();
            if ( is_array( $option_names ) && $option_names !== array() ) {
                foreach ( $option_names as $option_name ) {
                    delete_option( $option_name );
                    update_option( $option_name, get_option( $option_name ) );
                }
            }

        unset( $option_names );
    }
}
