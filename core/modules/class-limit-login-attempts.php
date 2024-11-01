<?php
/**
 * Module name: Limit login attempts
 *
 */
class ThreatPress_Module_Limit_Login_Attempts {

    private $options;

    /**
     * Class constructor
     */
    public function __construct( $options ) {
        $this->options = $options;

        add_filter( 'authenticate', array( $this, 'authenticate' ), 999, 3); // Called by XML RPC as well as GUI
        add_action( 'wp_login_failed', array( $this, 'login_failed' ) );
        add_action( 'wp_login_errors', array( $this, 'login_errors' ), 999, 2 ); // Called before displaying the error message

        $this->reset();
    }

    /**
     * Get a record from the database
     *
     * @param string $ip
     * @return object
     *
     */
    public static function get_record( $ip ) {
        global $wpdb;

        $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}threatpress_login_log WHERE ip = %s", $ip ) );

        return $result;
    }

    /**
     * Add a record to the database
     *
     * @param string $username
     * @param integer $time
     * @param integer $count
     * @param integer $lockout
     * @param string $ip
     *
     */
    public static function add_record( $username, $time, $count, $lockout, $ip ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'threatpress_login_log',
            array(
                'username' => $username,
                'time' => $time,
                'count' => $count,
                'lockout' => $lockout,
                'ip' => $ip
            ),
            array(
                '%s',
                '%d',
                '%d',
                '%d',
                '%s'
            )
        );
    }

    /**
     * Update a record in the database
     *
     * @param string $username
     * @param integer $time
     * @param integer $count
     * @param integer $lockout
     * @param string $ip
     *
     */
    public static function update_record( $username, $time, $count, $lockout, $ip ) {
        global $wpdb;

        if ( $username ) {
            $args['username'] = $username;
            $prepare[] = '%s';
        }

        $args['time'] = $time;
        $args['count'] = $count;
        $args['lockout'] = $lockout;
        $args['ip'] = $ip;

        $prepare[] = '%d';
        $prepare[] = '%d';
        $prepare[] = '%d';
        $prepare[] = '%s';

        $wpdb->update(
            $wpdb->prefix . 'threatpress_login_log',
            $args,
            array( 'ip' => $ip ),
            $prepare,
            array( '%s' )
        );
    }

    /**
     * Delete records from the database where time <= delete_time
     *
     * @param integer $delete_time
     * @return object
     *
     */
    public static function delete_records( $delete_time ) {
        global $wpdb;

        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}threatpress_login_log
		 WHERE time <= %d
		",
            $delete_time
        ) );

        update_option( 'threatpress_login_log_reset_time', time() );
    }

    /**
     * Delete records when reset hours has passed
     *
     */
    public function reset() {
        if ( ! get_option( 'threatpress_login_log_reset_time' ) )
            update_option( 'threatpress_login_log_reset_time', time() );

        $reset_time = absint( $this->options['hours_until_retries_are_reset'] ) * 3600; // Convert hours to seconds
        $delete_time = time() - $reset_time;

        // Determine if hours has passed after last reset time
        if ( time() >= absint( get_option( 'threatpress_login_log_reset_time' ) ) - $reset_time ) {
            self::delete_records( $delete_time );
        }
    }

    /**
     * Check if user is blocked. If so, don't allow to login
     *
     * @filter authenticate
     *
     * @param WP_User $user
     * @param string $username
     * @param string $password
     */
    public function authenticate( $user, $username, $password ){

        if ( $record = self::get_record( self::get_ip() ) ) {
            if ( $record->lockout == 0 )
                return;

            // Increase wait time after X lockouts
            if ( absint( $record->lockout ) >= absint( $this->options['lockouts_to_increase_time'] ) ) {
                $time = absint( $record->time ) + ( absint( $this->options['increase_lockout_time_to'] * 60 * 60 ) );
            } else {
                $time = absint( $record->time ) + ( absint( $this->options['minutes_lockout'] * 60 ) );
            }

            // Check if user is blocked
            if ($time > time()) {
                $wait_time = self::secondsToTime( $time - time() );

                return new WP_Error( 'threatpress_login_user_is_blocked', '<strong>' . __('ERROR:') . '</strong> ' . sprintf( __('You have to wait %s before you can login again.', 'threatpress' ), $wait_time ) );
            }
        }

        return $user;
    }

    /**
     * Update records if login failed
     *
     * @action wp_login_failed
     *
     * @param string $username
     */
    public function login_failed( $username ) {

        $username = filter_var( $username, FILTER_SANITIZE_STRING );

        if ( $record = self::get_record( self::get_ip() ) ) {

            $count = absint( $record->count ) + 1;
            $lockout = floor( $count / absint( $this->options['allowed_retries'] ) );

            self::update_record( $username, time(), $count, $lockout, self::get_ip() );
        } else {
            self::add_record( $username, time(), 1, 0, self::get_ip() );
        }
    }

    /**
     * Handle errors
     *
     * @action wp_login_errors
     *
     * @param object $errors
     */
    public function login_errors( $errors, $redirect_to ) {

        if ( is_wp_error( $errors ) ) {
            if ( array_search( 'authentication_failed', $errors->get_error_codes() ) !== FALSE || array_search( 'incorrect_password', $errors->get_error_codes() ) !== FALSE ) {

                if ( $record = self::get_record( self::get_ip() ) ) {
                    $retries_left = absint( $this->options['allowed_retries'] ) - absint( $record->count );

                    if ( $retries_left === 0 ) {
                        $time = absint( $record->time ) + ( absint( $this->options['minutes_lockout'] * 60 ) );

                        $wait_time = self::secondsToTime( $time - time() );

                        $errors->add( 'threatpress_login_user_is_blocked', '<strong>' . __('ERROR:') . '</strong> ' . sprintf( __('You have to wait %s before you can login again.', 'threatpress' ), $wait_time ) );
                    } else {
                        $errors->add( 'threatpress_login_user_retries_left', '<strong>' . __('ERROR:') . '</strong> ' . sprintf( __('%d attempt(s) left.', 'threatpress'), $retries_left ) );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_ip() {
        $ip_address = '';

        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        else if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
        else if( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
        else if( isset( $_SERVER['HTTP_FORWARDED'] ) )
            $ip_address = $_SERVER['HTTP_FORWARDED'];
        else if( isset( $_SERVER['REMOTE_ADDR'] ) )
            $ip_address = $_SERVER['REMOTE_ADDR'];

        return $ip_address;
    }

    /**
     * Convert UNIX time to day, hours, minutes and seconds
     *
     * @param integer $seconds
     *
     */
    public static function secondsToTime( $seconds ) {

        // Change format for next day
        if ( $seconds > time() + 24 * 60 * 60 ) {
            $format = '%a days, %h hours, %i minutes and %s seconds';
        } else {
            $format = '%h hours, %i minutes and %s seconds';
        }

        $dtF = new \DateTime( '@0' );
        $dtT = new \DateTime( "@$seconds" );

        return $dtF->diff($dtT)->format( $format );
    }

}