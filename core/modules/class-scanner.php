<?php

class ThreatPress_Module_Scanner {

    private $options;

    /**
     * Class constructor
     */
    public function __construct( $options ) {
        $this->options = $options;

        add_action( 'wp_ajax_threatpress_scanner', array( $this, 'ajax_scanner_callback' ) );
        add_action( 'wp_ajax_threatpress_scanner_status', array( $this, 'ajax_scanner_status_callback' ) );

        add_action( 'wp_ajax_threatpress_dismiss_admin_notice', array( $this, 'ajax_dismiss_admin_notice' ) );

        if ( self::get_notice_status() == true )
            add_action( 'admin_notices', array( $this, 'admin_notice' ) );

        if ( ! wp_next_scheduled( 'threatpress_scanner' ) )
            wp_schedule_event( time(), $this->options['schedule'], 'threatpress_scanner' );

        add_action( 'threatpress_scanner', array( $this, 'background_scanner' ) );
    }

    /**
     * AJAX
     *
     */

    /**
     * AJAX callback to run scanner
     *
     */
    public function ajax_scanner_callback( ) {
        check_ajax_referer( 'scanner', 'security' );

        // Run scanner
        $this->scan();

        wp_send_json( true );
        die();
    }

    /**
     * AJAX callback to get scan progress and log
     *
     */
    public function ajax_scanner_status_callback( ) {
        check_ajax_referer( 'scanner', 'security' );

        $response = array(
            'log' => self::get_scan_log(),
            'status' => self::get_scan_status()
        );

        wp_send_json( $response );
        die();
    }

    /**
     * AJAX callback to dismiss admin notice
     *
     */
    public function ajax_dismiss_admin_notice( ) {
        check_ajax_referer( 'threatpress_dismiss_admin_notice', 'security' );

        $status = false;
        if ( self::set_notice_status( false ) )
            $status = true;

        wp_send_json( $status );

        die();
    }

    /**
     * Runs background scanner
     *
     * @global $wp_version WP version
     *
     */
    public function background_scanner() {

        // Run scanner
        $this->scan();

        // Check for threats
        if ( self::get_threat_status() ) {
            global $wp_version;

            $threats = self::get_threats();

            $message = '';

            if ( $threats['vulnerabilities']['plugins']['status'] == true ) :
                foreach ( $threats['vulnerabilities']['plugins']['list'] as $plugin_path => $info ) :
                    $plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_path );
                    $message .= '<p>' . sprintf( __('%s plugin %s version is vulnerable. Update immediately.', 'threatpress'), $plugin['Name'], $plugin['Version'] ) . '</p>';
                endforeach;
            endif;

            if ( $threats['vulnerabilities']['themes']['status'] == true ) :
                foreach ( $threats['vulnerabilities']['themes']['list'] as $theme_file => $info ) :
                    $theme = wp_get_theme( $theme_file );
                    $message .= '<p>' . sprintf( __('%s theme %s version is vulnerable. Update immediately.', 'threatpress'), $theme->get( 'Name' ), $theme->get( 'Version' ) ) . '</p>';
                endforeach;
            endif;

            if ( $threats['vulnerabilities']['wordpress']['status'] == true ) :
                $message .= '<p>' . sprintf( __('Current WordPress %s version has known security issues. Update immediately.', 'threatpress'), $wp_version ) . '</p>';
            endif;

            if ( $threats['site']['malware'] === true ) :
                $message .= '<p>' . __( 'Malware has been detected on your site. You need to remove your site from blacklisting.', 'threatpress' ) . '</p>';
            endif;

            if ( $threats['site']['phishing'] === true ) :
                $message .= '<p>' . __('Your site has been flagged as phishing. You need to remove your site from blacklisting.', 'threatpress' ) . '</p>';
            endif;

            if ( $threats['site']['spam'] === true ) :
                $message .= '<p>' . __('Your site has been flagged as spam. You need to remove your site from blacklisting.', 'threatpress' ) . '</p>';
            endif;

            if ( $threats['site']['errors'] === true ) :
                $message .= '<p>' . __('Site errors have been detected. You need to remove your site from blacklisting.', 'threatpress' ) . '</p>';
            endif;

            if ( $threats['checksums']['status'] == true ) :
                $message .= '<p>' . __('Checksum verification failed. You need to remove your site from blacklisting.', 'threatpress' ) . '</p>';
            endif;

            $headers = array('Content-Type: text/html; charset=UTF-8');


            // Send alert
            if ( $this->options['status'] === 'on' && !empty( $this->options['email'] ) ) :
                wp_mail( $this->options['email'], __('Serious security threats have been detected', 'threatpress'), $message, $headers );
            endif;

        }
    }

    /**
     * The main method to scan web site
     *
     */
    private function scan() {
        $threats = array(
            'checksums' => array(
                'status' => false,
                'files' => array()
            ),
            'vulnerabilities' => array(
                'plugins' => array(
                    'status' => false,
                    'list' => array()
                ),
                'themes' => array(
                    'status' => false,
                    'list' => false
                ),
                'wordpress' => array(
                    'status' => false,
                    'info' => ''
                )
            ),
            'site' => array(
                'malware' => false,
                'phishing' => false,
                'spam' => false,
                'errors' => false,
                'errors_info' => array()
            )
        );

        self::set_scan_time( current_time( 'timestamp', 1 ) );
        self::set_scan_status( '0' );
        self::clear_scan_log();

        if ( $this->options['vulnerabilities_db'] === 'on' ) :
            self::set_scan_log( __('Scanning for vulnerable plugins and themes', 'threatpress') );

            $vulnerabilities = self::scan_for_vulnerabilities();

            if ( !empty( $vulnerabilities ) ) {
                if ( !empty( $vulnerabilities['vulnerabilities']['plugins'] ) ) {
                    $threats['vulnerabilities']['plugins']['status'] = true;

                    foreach( $vulnerabilities['vulnerabilities']['plugins'] as $plugin_path => $info ) {
                        $threats['vulnerabilities']['plugins']['list'][$plugin_path] = $info;
                    }
                }

                if ( !empty( $vulnerabilities['vulnerabilities']['themes'] ) ) {
                    $threats['vulnerabilities']['themes']['status'] = true;

                    foreach( $vulnerabilities['vulnerabilities']['themes'] as $theme_slug => $info ) {
                        $threats['vulnerabilities']['themes']['list'][$theme_slug] = $info;
                    }
                }

                if ( !empty( $vulnerabilities['vulnerabilities']['wordpress'] ) ) {
                    $threats['vulnerabilities']['wordpress']['status'] = true;

                    $threats['vulnerabilities']['wordpress']['info'] = $vulnerabilities['vulnerabilities']['wordpress'];
                }
            }
        endif;

        if ( $this->options['sitescan'] === 'on' ) :
            self::set_scan_log( __('Scanning for known malware, phishing, spam and errors', 'threatpress') );

            $site_status = self::scan_site();

            if ( !empty( $site_status ) && $site_status['error'] !== true ) {
                $malware = $site_status['status']->malware;
                $phishing = $site_status['status']->phishing;
                $injected_spam = $site_status['status']->injected_spam;
                $errors = $site_status['status']->errors;
                $errors_info = $site_status['details']->server_errors_info;

                $threats['site'] = array(
                    'malware' => $malware,
                    'phishing' => $phishing,
                    'spam' => $injected_spam,
                    'errors' => $errors,
                    'errors_info' => $errors_info
                );
            }
        endif;

        if ( $this->options['checksums'] === 'on' ) :
            self::set_scan_log( __('Verifying WordPress core checksums', 'threatpress') );

            $checksums_matches = self::match_checksums( self::get_checksums() );

            if ( ! empty( $checksums_matches ) ) {
                $threats['checksums']['status'] = true;
                foreach( $checksums_matches as $k => $file_name ) {
                    $threats['checksums']['files'][$k] = $file_name;
                }
            }
        endif;

        self::set_scan_log( 'Scan done' );
        self::set_scan_status( '100' );

        if ( self::any_threats( $threats ) ) :
            self::set_threat_status( true );
            self::set_notice_status( true );
            self::set_threats( $threats );

        else :
            self::set_threat_status( false );
            self::set_threats( $threats );
        endif;
    }

    /**
     * Scans plugins, themes and WordPress against ThreatPress database of vulnerabilities https://db.threatpress.com
     *
     * @global $wp_version
     * @return array
     *
     */
    private static function scan_for_vulnerabilities() {
        global $wp_version;

        $vulnerabilities = array();
        $error = false;

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $request = new WP_Http;

        // Check plugins
        foreach ( get_plugins() as $path => $details ) :
            $plugin_slug = explode( '/' , $path );

            $result = $request->request( 'https://db.threatpress.com/api/v1/product/' . $plugin_slug[0] . '/' . $details['Version'] );

            if ( $result ) :
                $data = json_decode( wp_remote_retrieve_body( $result ) );

                if ( !empty( $data->vulnerabilities ) ) :
                    foreach( $data->vulnerabilities as $k => $vulnerability ) :
                        $vulnerabilities['plugins'][$path] = $vulnerability->title;
                    endforeach;
                endif;
            else :
                $error = $result->get_error_message();
            endif;
        endforeach;

        // Check themes
        foreach ( wp_get_themes() as $theme_slug => $details ) :

            $result = $request->request( 'https://db.threatpress.com/api/v1/product/' . $theme_slug . '/' . $details['Version'] );

            if ( $result ) :
                $data = json_decode( wp_remote_retrieve_body( $result ) );

                if ( !empty( $data->vulnerabilities ) ) :
                    foreach( $data->vulnerabilities as $k => $vulnerability ) :
                        $vulnerabilities['themes'][$theme_slug] = $vulnerability->title;
                    endforeach;
                endif;
            else :
                $error = $result->get_error_message();
            endif;

        endforeach;

        // Check WordPress
        $result = $request->request( 'https://db.threatpress.com/api/v1/product/wordpress/' . $wp_version );

        if ( $result ) :
            $data = json_decode( wp_remote_retrieve_body( $result ) );

            if ( !empty( $data->vulnerabilities ) ) :
                foreach( $data->vulnerabilities as $k => $vulnerability ) :
                    $vulnerabilities['wordpress'] = $vulnerability->title;
                endforeach;
            endif;
        else :
            $error = $result->get_error_message();
        endif;

        return array( 'vulnerabilities' => $vulnerabilities, 'error' => $error );
    }

    /**
     * Scans website for known malware, phishing, spam, blacklisting and much more using https://sitescan.threatpress.com
     *
     * @return array
     *
     */
    private static function scan_site() {
        $status = array(
            'status' => '',
            'details' => '',
            'error' => false
        );

        $status['status'] = new stdClass();
        $status['status']->malware = false;
        $status['status']->phishing = false;
        $status['status']->injected_spam = false;
        $status['status']->errors = false;

        $status['details'] = new stdClass();
        $status['details']->server_errors_info = false;

        $error = false;

        $request = new WP_Http;

        $site_url = preg_replace( '#^https?://#', '', site_url() );
        $result = $request->request( 'https://sitescan.threatpress.com/api/v1/url/' . $site_url );

        if ( $result ) :
            $data = json_decode( wp_remote_retrieve_body( $result ) );

            if ( $data ):
                if ($data->error === true) :
                    $status['error'] = true;
                else :
                    $status['status'] = $data->data->status;
                    $status['details'] = $data->data->website_details;
                endif;
            endif;
        else :
            $error = $result->get_error_message();
        endif;

        return $status;
    }

    /**
     * Get file checksums from https://api.wordpress.org
     *
     * @return  array
     *
     */
    private static function get_checksums() {
        global $wp_version;

        $request = new WP_Http;

        $response = $request->request(
            add_query_arg(
                array(
                    'version' => $wp_version,
                    'locale'  => get_locale()
                ),
                'https://api.wordpress.org/core/checksums/1.0/'
            )
        );

        if ( is_wp_error( $response ) ) {
            return;
        }

        if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return;
        }

        $json = json_decode(
            wp_remote_retrieve_body( $response )
        );

        if ( empty( $json->checksums ) ) {
            return;
        }

        $checksums = $json->checksums;

        return $checksums;
    }

    /**
     * Match MD5 hashes
     *
     * @param   array   $checksums
     * @return  array   $matches
     */

    private static function match_checksums($checksums) {

        $matches = array();

        /* Ignore files filter */
        $ignore_files = (array)apply_filters(
            'threatpress_checksum_ignore_files',
            array(
                'wp-config-sample.php',
                'wp-includes/version.php',
                'readme.html',
                'license.txt'
            )
        );

        /* Loop files */
        foreach( $checksums as $file => $checksum ) {

            // Skip plugins, themes and languages
            if ( strpos( $file, '/plugins/' ) !== false || strpos( $file, '/themes/' ) !== false || strpos( $file, '/languages/' ) !== false ) {
                continue;
            }

            /* File path */
            $file_path = ABSPATH . $file;

            /* Skip version.php */
            if ( in_array( $file, $ignore_files ) ) {
                continue;
            }

            /* File check */
            if ( validate_file( $file_path ) !== 0 OR ! file_exists( $file_path ) ) {
                continue;
            }

            /* Compare MD5 hashes */
            if ( md5_file( $file_path ) !== $checksum ) {
                $matches[] = $file;
            }
        }

        return $matches;
    }

    /**
     * Set scan time
     *
     * @param $time string
     * @return bool
     *
     */
    private static function set_scan_time( $time ) {
        return update_option( 'threatpress_scan_time', $time );
    }

    /**
     * Set scan progress
     *
     * @param $status string
     * @return bool
     *
     */
    private static function set_scan_status( $progress ) {
        return update_option( 'threatpress_scan_status', $progress );
    }

    /**
     * Set threat status
     *
     * @param $status bool
     * @return bool
     *
     */
    private static function set_threat_status( $status ) {
        return update_option( 'threatpress_threat_status', $status );
    }

    /**
     * Set found threats
     *
     * @param $threats array
     * @return bool
     *
     */
    private static function set_threats( $threats ) {
        return update_option( 'threatpress_threats', $threats );
    }

    /**
     * Update scan log
     *
     * @param $value string
     * @return bool
     *
     */
    private static function set_scan_log( $value ) {
        return update_option( 'threatpress_scan_log', $value );
    }

    /**
     * Set notice status
     *
     * @param $status bool
     * @return bool
     *
     */
    private static function set_notice_status( $status ) {
        return update_option( 'threatpress_notice_status', $status );
    }

    /**
     * Get last scan time
     *
     * @return bool
     *
     */
    private static function get_scan_time() {
        return get_option( 'threatpress_scan_time' );
    }

    /**
     * Get scan progress
     *
     * @return string
     *
     */
    private static function get_scan_status() {
        return get_option( 'threatpress_scan_status' );
    }

    /**
     * Get scan log
     *
     * @return string
     *
     */
    private static function get_scan_log() {
        return get_option( 'threatpress_scan_log' );
    }

    /**
     * Get threat status
     *
     * @return bool
     *
     */
    private static function get_threat_status() {
        return (boolean) get_option( 'threatpress_threat_status' );
    }

    /**
     * Get threats
     *
     * @return string
     *
     */
    private static function get_threats() {
        return get_option( 'threatpress_threats' );
    }

    /**
     * Get notice status
     *
     * @return bool
     *
     */
    private static function get_notice_status() {
        return (boolean) get_option( 'threatpress_notice_status' );
    }

    /**
     * Clear scan log
     *
     * @return bool
     *
     */
    private static function clear_scan_log() {
        return update_option( 'threatpress_scan_log', '' );
    }

    /**
     * Check if any threat exist
     *
     * @param $threats
     *
     */
    private static function any_threats( $threats ) {
        if ( $threats['checksums']['status'] === true ||
        $threats['vulnerabilities']['plugins']['status'] === true ||
        $threats['vulnerabilities']['themes']['status'] === true ||
        $threats['vulnerabilities']['wordpress']['status'] === true ||
        $threats['site']['malware'] === true ||
        $threats['site']['phishing'] === true ||
        $threats['site']['spam'] === true ||
        $threats['site']['errors'] === true ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Admin notice. Show found threats
     *
     * @global $wp_version, $pagenow
     *
     */
    public function admin_notice() {
        global $wp_version, $pagenow;

        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

        $page = ( isset( $page ) ) ? $page : '';
        if ( $page === 'threatpress_dashboard' || $page === 'threatpress_scanner' ) return;

        $threats = self::get_threats();

        if ( ! self::any_threats( $threats ) )
            return;

        ?>
        <div class="notice notice-error threatpress-notice is-dismissible">
            <p><strong><?php _e('ATTENTION!', 'threatpress'); ?></strong></p>
            <?php
            $threats = self::get_threats();

            if ( $threats['vulnerabilities']['plugins']['status'] == true ) :
                foreach ( $threats['vulnerabilities']['plugins']['list'] as $plugin_path => $info ) :
                    $plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_path );
                    ?>
                    <p><strong><?php printf( __('%s plugin %s version is vulnerable. Update immediately.', 'threatpress'), $plugin['Name'], $plugin['Version'] ); ?></strong></p>
                    <?php
                endforeach;
            endif;

            if ( $threats['vulnerabilities']['themes']['status'] == true ) :
                foreach ( $threats['vulnerabilities']['themes']['list'] as $theme_file => $info ) :
                    $theme = wp_get_theme( $theme_file );
                    ?>
                    <p><strong><?php printf( __('%s theme %s version is vulnerable. Update immediately.', 'threatpress'), $theme->get( 'Name' ), $theme->get( 'Version' ) ); ?></strong></p>
                    <?php
                endforeach;
            endif;

            if ( $threats['vulnerabilities']['wordpress']['status'] == true ) :
                ?>
                <p><strong><?php printf( __('Current WordPress %s version has known security issues. Update immediately.', 'threatpress'), $wp_version ); ?></strong></p>
                <?php
            endif;

            if ( $threats['site']['malware'] === true ) :
                ?>
                <p><strong><?php printf( __('Malware has been detected on your site. For more information, please go to <a href="%s">ThreatPress &rarr; Scanner</a>', 'threatpress'), esc_url( admin_url('admin.php?page=threatpress_scanner') ) ); ?></strong></p>
                <?php
            endif;

            if ( $threats['site']['phishing'] === true ) :
                ?>
                <p><strong><?php printf( __('Your site has been flagged as phishing. For more information, please go to <a href="%s">ThreatPress &rarr; Scanner</a>', 'threatpress'), esc_url( admin_url('admin.php?page=threatpress_scanner') ) ); ?></strong></p>
                <?php
            endif;

            if ( $threats['site']['spam'] === true ) :
                ?>
                <p><strong><?php printf( __('Your site has been flagged as spam. For more information, please go to <a href="%s">ThreatPress &rarr; Scanner</a>', 'threatpress'), esc_url( admin_url('admin.php?page=threatpress_scanner') ) ); ?></strong></p>
                <?php
            endif;

            if ( $threats['site']['errors'] === true ) :
                ?>
                <p><strong><?php printf( __('Site errors have been detected. For more information, please go to <a href="%s">ThreatPress &rarr; Scanner</a>', 'threatpress'), esc_url( admin_url('admin.php?page=threatpress_scanner') ) ); ?></strong></p>
                <?php
            endif;

            if ( $threats['checksums']['status'] == true ) : ?>
                <p><strong><?php printf( __('Checksum verification failed. For more information, please go to <a href="%s">ThreatPress &rarr; Scanner</a>', 'threatpress'), esc_url( admin_url('admin.php?page=threatpress_scanner') ) ); ?></strong></p>
                <?php
            endif;

            wp_nonce_field( 'threatpress_dismiss_admin_notice', '_threatpress_dismiss_admin_notice_nonce' );

            ?>
        </div>
        <?php
    }

}