<?php
/**
 * Module: Password expiration
 *
 */
class ThreatPress_Module_Passwords {

    private $options;

    /**
     * Class constructor
     */
    public function __construct( $options ) {
        $this->options = $options;

        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
        add_action( 'user_register',  array( __CLASS__, 'save_user_meta' ) );
        add_action( 'password_reset', array( __CLASS__, 'save_user_meta' ) );
        add_action( 'validate_password_reset', array( $this, 'validate_password_reset' ), 10, 2 );
        add_filter( 'login_message',           array( $this, 'lost_password_message' ) );
    }

    /**
     * Save password reset time
     *
     * @param integer $user_id
     */
    public static function save_user_meta( $user_id ) {
        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        update_user_meta( $user_id, '_password_reset', gmdate( 'U' ) );
    }

    /**
     * Get password reset time
     *
     * @param  integer $user_id
     *
     * @return integer|false
     */
    public static function get_user_meta( $user_id ) {
        $value = get_user_meta( $user_id, '_password_reset', true );

        return ( $value ) ? absint( $value ) : false;
    }

    /**
     * Return the password expiration date for a user
     *
     * @param  integer $user_id
     * @param  string      $date_format (optional)
     *
     * @return string|false
     */
    public function get_expiration( $user_id, $date_format = 'U' ) {

        if ( false === ( $reset = self::get_user_meta( $user_id ) ) )
            return false;

        $expires = strtotime( sprintf( '@%d + %d days', $reset, $this->options['maximum_age'] ) );

        return gmdate( $date_format, $expires );

    }

    /**
     * Determine if a user's password has expired
     *
     * @param  integer $user_id
     *
     * @return bool
     */
    public function is_expired( $user_id ) {
        return ( false === ( $expires = $this->get_expiration( $user_id ) ) ) ? false : ( time() > $expires );
    }

    /**
     * Enforce password reset after user login, when applicable
     *
     * @action wp_login
     *
     * @param string  $user_login
     * @param WP_User $user
     */
    public function wp_login( $user_login, $user ) {

        if ( ! self::get_user_meta( $user->ID ) ) {
            self::save_user_meta( $user->ID );
        }

        if ( ! $this->is_expired( $user->ID ) ) {
            return;
        }

        // Destroy session
        $GLOBALS['current_user'] = $user;

        wp_destroy_all_sessions();

        wp_safe_redirect(
            add_query_arg(
                array(
                    'action' => 'lostpassword',
                    'password' => 'expired',
                ),
                wp_login_url()
            ),
            302
        );

        exit;
    }

    /**
     * Disallow using the same password as before on reset
     *
     * @action validate_password_reset
     *
     * @param WP_Error $errors
     * @param WP_User  $user
     */
    public function validate_password_reset( $errors, $user ) {

        $pass1 = filter_input( INPUT_POST, 'pass1', FILTER_SANITIZE_STRING );
        $pass2 = filter_input( INPUT_POST, 'pass2', FILTER_SANITIZE_STRING );

        if ( ! $pass1 ||  ! $pass2 || $pass1 !== $pass2 )
            return;

        $is_same = wp_check_password( $pass1, $user->data->user_pass, $user->ID );

        if ( $is_same ) {
            $errors->add( 'the_same_password', esc_html__( 'You can not use your old password.', 'threatpress' ) );
        }

    }

    /**
     * Display a custom message on the lost password login screen
     *
     * @filter login_message
     *
     * @param  string $message
     *
     * @return string
     */
    public function lost_password_message( $message ) {

        $action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
        $status = filter_input( INPUT_GET, 'password', FILTER_SANITIZE_STRING );

        if ( 'lostpassword' !== $action || 'expired' !== $status )
            return $message;

        return sprintf(
            '<p class="message">%s<br />%s</p>',
            sprintf(
                _n(
                    'Your password must be reset every days.',
                    'Your password must be reset every %d days.',
                    'threatpress'
                ),
                $this->options['maximum_age']
            ),
            esc_html__( 'Please enter your username or e-mail below and a password reset link will be sent to you.', 'threatpress' )
        );

    }

}