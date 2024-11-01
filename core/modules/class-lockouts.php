<?php
/**
 * Module name: Lockouts
 *
 */
class ThreatPress_Module_Lockouts {

    /**
     * Class constructor
     */
    public function __construct( ) {
        add_action( 'wp_ajax_threatpress_remove_lockout', array( __CLASS__, 'ajax_remove_lockout_callback' ) );

    }

    /**
     * AJAX
     *
     */

    /**
     * AJAX callback to remove lockout button
     *
     */
    public static function ajax_remove_lockout_callback( ) {
        check_ajax_referer( 'remove_lockout', 'security' );

        if ( is_array( $_POST['ids'] ) && !empty( $_POST['ids'] ) ) {
            foreach ( $_POST['ids'] as $id ) :
                $id = filter_var( $id, FILTER_SANITIZE_NUMBER_INT );

                $id = absint( $id );

                self::delete( $id );
            endforeach;

            $status = true;

        } else {
            $status = false;
        }

        wp_send_json( $status );

        die();
    }

    /**
     * Delete lockout by ID
     *
     * @param $id
     * @global $wpdb
     * @return bool
     *
     */
    public static function delete( $id ) {
        global $wpdb;

        return $wpdb->delete( "{$wpdb->prefix}threatpress_login_log", array( 'id' => $id ) );
    }

    /**
     * Get all lockouts
     *
     * @global $wpdb
     * @return bool
     *
     */
    public static function get_records() {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}threatpress_login_log WHERE lockout >= %d", 1 ) );
    }


}