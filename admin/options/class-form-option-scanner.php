<?php

/**
 * Option: threatpress_scanner
 */
class ThreatPress_Admin_Form_Option_Scanner extends ThreatPress_Admin_Option {

    /**
     * @var  string  option name
     *
     */
    public $option_name = 'threatpress_scanner';

    /**
     * @var  array  Array of defaults for the option
     *
     */
    protected $defaults = array(
        'scanner' => array(
            'enable' => 'on',
            'vulnerabilities_db' => 'on',
            'sitescan' => 'on',
            'checksums' => 'on',
            'schedule' => 'daily'
        )
    );

    /**
     * Add the actions and filters for the option
     *
     */
    protected function __construct() {
        parent::__construct();
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
     * Fires after the value of a specific option has been successfully updated.
     *
     * @param mixed  $old_value The old option value.
     * @param mixed  $value     The new option value.
     * @param string $option    Option name.
     */
    protected function updated_option( $old_value, $value, $option ) {

        if ( $old_value['scanner']['schedule'] !== $value['scanner']['schedule'] )
            wp_clear_scheduled_hook( 'threatpress_scanner' );

    }

    /**
     * Validate the option
     *
     * @param  array $dirty New value for the option.
     * @param  array $clean Clean value for the option, normally the defaults.
     * @param  array $old   Old value of the option.
     *
     * @return  array      Validated clean value for the option to be saved to the database
     */
    protected function validate_option( $dirty, $clean ) {
        foreach ( $clean as $key => $value ) {
            if ( is_array( $clean[$key] ) ) {
                foreach ( $clean[$key] as $option_key => $val ) {

                    switch ( $option_key ) {
                        default:
                            $clean[ $key ][ $option_key ] = ( isset( $dirty[ $key ][ $option_key ] ) ? sanitize_text_field( $dirty[ $key ][ $option_key ] ) : false);
                            break;
                    }
                }
            }
        }

        return $clean;
    }
}
