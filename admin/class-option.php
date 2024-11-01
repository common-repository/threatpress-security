<?php

/**
 * This abstract class and it's concrete classes implement defaults and value validation for
 * all ThreatPress options and subkeys within options.
 *
*/
abstract class ThreatPress_Admin_Option {

    /**
     * @var  string  Option name - MUST be set in concrete class and set to public.
     */
    protected $option_name;

    /**
     * @var  string  Option group name for use in settings forms
     *
     */
    public $group_name;

    /**
     * @var  bool  Whether to include the option in the return for ThreatPress_Options::get_all().
     *
     */
    public $include_in_all = true;

    /**
     * @var  array  Array of defaults for the option - MUST be set in concrete class.
     *
     */
    protected $defaults;

    /**
     * @var  object  Instance of this class
     */
    protected static $instance;


    /* *********** INSTANTIATION METHODS *********** */

    /**
     * Add all the actions and filters for the option
     *
     */
    protected function __construct() {

        add_filter( 'sanitize_option_' . $this->option_name, array( $this, 'validate' ) );

        add_action( 'update_option_' . $this->option_name, array( $this, 'update' ), 10, 3 );

        /* Register our option for the admin pages */
        add_action( 'admin_init', array( $this, 'register_setting' ) );

        /* Set option group name if not given */
        if ( ! isset( $this->group_name ) || $this->group_name === '' ) {
            $this->group_name = $this->option_name;
        }

        if ( method_exists( $this, 'enrich_defaults' ) ) {
            add_action( 'init', array( $this, 'enrich_defaults' ), 99 );
        }

    }

    /* *********** METHODS INFLUENCING get_option() *********** */

    /**
     * Get the enriched default value for an option
     *
     * Checks if the concrete class contains an enrich_defaults() method and if so, runs it.
     *
     * @return  array
     */
    public function get_defaults() {
        if ( method_exists( $this, 'enrich_defaults' ) ) {
            $this->enrich_defaults();
        }

        return $this->defaults;
    }

    /**
     * Merge an option with its default values
     *
     * It is only meant to filter the get_option() results.
     *
     * @param   mixed $options Option value.
     *
     * @return  mixed        Option merged with the defaults for that option
     */
    public function get_option( $options = null ) {
        $filtered = $this->array_filter_merge( $options );

        return $filtered;
    }

    /* *********** METHODS influencing add_option(), update_option() and saving from admin pages *********** */

    /**
     * Register (whitelist) the option for the configuration pages.
     * The validation callback is already registered separately on the sanitize_option hook,
     * so no need to double register.
     *
     * @return void
     */
    public function register_setting() {

        register_setting( $this->group_name, $this->option_name );
    }

    /**
     * Fires after the value of a specific option has been successfully updated.
     *
     * @param mixed  $old_value The old option value.
     * @param mixed  $value     The new option value.
     * @param string $option    Option name.
     */
    public function update( $old_value, $value, $option ) {
        $this->updated_option( $old_value, $value, $option );
    }

    /**
     * Validate the option
     *
     * @param  mixed $option_value The unvalidated new value for the option.
     *
     * @return  array          Validated new value for the option
     */
    public function validate( $option_value ) {
        $clean = $this->get_defaults();

        /* Return the defaults if the new value is empty */
        if ( ! is_array( $option_value ) || $option_value === array() ) {
            return $clean;
        }

        //$option_value = array_map( array( 'ThreatPress_Utils', 'trim_recursive' ), $option_value );
        //$old = get_option( $this->option_name );

        $clean = $this->validate_option( $option_value, $clean );

        return $clean;
    }

    /* *********** METHODS for ADDING/UPDATING/UPGRADING the option *********** */

    /**
     * Add the option if it doesn't exist for some strange reason
     *
     * @return void
     */
    public function maybe_add_option() {
        update_option( $this->option_name, $this->get_defaults() );
    }

    /**
     * Helper method - Combines a fixed array of default values with an options array
     * while filtering out any keys which are not in the defaults array.
     *
     * @param  array $options (Optional) Current options. If not set, the option defaults for the $option_key will be returned.
     *
     * @return  array  Combined and filtered options array.
     */
    protected function array_filter_merge( $options = null ) {

        $defaults = $this->get_defaults();

        if ( ! isset( $options ) || $options === false || $options === array() ) {
            return $defaults;
        }

        $options = (array) $options;

        $filtered = array_merge( $defaults, $options );

        return $filtered;
    }

}