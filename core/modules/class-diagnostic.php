<?php
/**
 * Module name: Diagnostic
 *
 */
class ThreatPress_Module_Diagnostic {

    /**
     * Class constructor
     */
    public function __construct( $options ) {

    }

    /**
     * Get salts and keys status.
     *
     * @return bool
     */
    public static function get_salts_and_keys_status() {
        $salts = array(
            AUTH_KEY,
            SECURE_AUTH_KEY,
            LOGGED_IN_KEY,
            NONCE_KEY,
            AUTH_SALT,
            SECURE_AUTH_SALT,
            LOGGED_IN_SALT,
            NONCE_SALT,
        );

        $status = true;
        foreach ( $salts as $salt ) {
            if ( empty( $salt ) ) {
                $empty[] = $salt;

                $status = false;
            }
        }

        return $status;
    }

    /**
     * Get database prefix.
     *
     * @return bool
     */
    public static function get_database_prefix_status() {
        global $table_prefix;

        $status = true;
        if ( $table_prefix === 'wp_' ) {
            $status = false;
        }

        return $status;
    }

    /**
     * Get file editor status.
     *
     * @return bool
     */
    public static function get_file_editor_status() {
        $status = true;
        if ( ! defined( 'DISALLOW_FILE_EDIT' ) || DISALLOW_FILE_EDIT  === false ) {
            $status = false;
        }

        return $status;
    }

    /**
     * Get unfiltered HTML status.
     *
     * @return bool
     */
    public static function get_unfiltered_html_status() {
        $status = true;
        if ( ! defined( 'DISALLOW_UNFILTERED_HTML' ) || DISALLOW_UNFILTERED_HTML  === false ) {
            $status = false;
        }

        return $status;
    }

    /**
     * Get unfiltered uploads status.
     *
     * @return bool
     */
    public static function get_unfiltered_uploads_status() {
        $status = true;
        if ( defined( 'ALLOW_UNFILTERED_UPLOADS' ) && ALLOW_UNFILTERED_UPLOADS  === true ) {
            $status = false;
        }

        return $status;
    }

    /**
     * Get wp-config.php file permissions status.
     *
     * @return bool
     */
    public static function get_permissions_status() {
        if ( file_exists( ABSPATH . 'wp-config.php') ) {
            $path = ABSPATH . 'wp-config.php';
        } else if ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
            $path = dirname( ABSPATH ) . '/wp-config.php';
        } else {
            $path = ABSPATH . 'wp-config.php';
        }

        if ( $path ) {
            $status = true;

            $permissions = ThreatPress_Admin_Utils::get_file_permissions( $path );

            if ( $permissions > 644 )
                $status = false;
        }

        return $status;
    }

    /**
     * Get SSL status.
     *
     * @return bool
     */
    public static function get_https_status() {
        $status = true;
        if ( 'https' !== substr( get_home_url(), 0, 5 ) ) {
            $status = false;
        }

        return $status;
    }

    /**
     * Get diagnostic data.
     *
     * @return bool
     */
    public static function get_data() {
        $data['salts_and_security_keys'] = self::get_salts_and_keys_status();
        $data['database_prefix'] = self::get_database_prefix_status();
        $data['file_editing'] = self::get_file_editor_status();
        $data['unfiltered_html'] = self::get_unfiltered_html_status();
        $data['unfiltered_uploads'] = self::get_unfiltered_uploads_status();
        $data['permissions'] = self::get_permissions_status();
        $data['https'] = self::get_https_status();
        $data['server_info'] = $_SERVER['SERVER_SOFTWARE'];
        $data['php_version'] = phpversion();

        return $data;
    }

}