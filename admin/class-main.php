<?php

/**
 * The main admin class.
 */
class ThreatPress_Admin_Main {

    /** The page identifier used in WordPress to register the admin page */
    const PAGE_IDENTIFIER = 'threatpress_dashboard';

    /**
     * Holds the options
     *
     * @var array
     */
    private $options;

    /**
     * Class constructor
     */
    function __construct() {

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_menu', array( $this, 'admin_pages' ), 5 );
    }

    /**
     * Register the menu item and its sub menu's.
     *
     * @global array $submenu used to change the label on the first item.
     */
    function admin_pages() {
        $admin_page = add_menu_page( 'ThreatPress: ' . __( 'Dashboard', 'og' ), __( 'ThreatPress', 'og' ), 'manage_options', self::PAGE_IDENTIFIER, array(
            $this, 'load_page',
        ), 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIGlkPSJMYXllcl8xIiB2aWV3Qm94PSItMyAtMSA1NiA1NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4gIDx0aXRsZT5UaHJlYXRQcmVzczwvdGl0bGU+ICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xOC40MiwyNy43MXYtOC40aDUuNzFWOS4xNkgxOC41NVYwTDAsMTkuMzFIOC4yOHY4LjRBMTguNTcsMTguNTcsMCwwLDAsMjYuODMsNDYuMjVWMzYuMTFBOC40MSw4LjQxLDAsMCwxLDE4LjQyLDI3LjcxWiIgc3R5bGU9ImZpbGw6IHJnYigyNDEsIDI0MSwgMjQxKTsiLz4gIDxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTMwLjY4LDkuMTZIMjguNDJWMTkuMzFoMi4yNmE4LjQsOC40LDAsMCwxLDAsMTYuODFWNDYuMjVhMTguNTQsMTguNTQsMCwwLDAsMC0zNy4wOVoiIHN0eWxlPSJmaWxsOiByZ2IoMjQxLCAyNDEsIDI0MSk7Ii8+ICA8cGF0aCBjbGFzcz0iY2xzLTIiIGQ9Ik04LjI4LDQwLjA3VjUzaDkuODVWNDguMjRBMjIuNDMsMjIuNDMsMCwwLDEsOC4yOCw0MC4wN1oiIHN0eWxlPSJmaWxsOiByZ2IoMjQxLCAyNDEsIDI0MSk7Ii8+ICA8cGF0aCBkPSJNIDQwLjY4MyAyMy45MjYgWiIgc3R5bGU9ImZpbGw6IHJnYigyNDEsIDI0MSwgMjQxKTsiLz48L3N2Zz4=', '100' );

        // Add submenu pages
        // Scanner page
        add_submenu_page( self::PAGE_IDENTIFIER, 'ThreatPress: ' . __( 'Scanner', 'og' ), __( 'Scanner', 'og' ),
            'manage_options', 'threatpress_scanner', array( $this, 'load_page') );

        // Shield page
        add_submenu_page( self::PAGE_IDENTIFIER, 'ThreatPress: ' . __( 'Shield', 'og' ), __( 'Shield', 'og' ),
            'manage_options', 'threatpress_shield', array( $this, 'load_page') );

        // Settings page
        add_submenu_page( self::PAGE_IDENTIFIER, 'ThreatPress: ' . __( 'Settings', 'og' ), __( 'Settings', 'og' ),
            'manage_options', 'threatpress_settings', array( $this, 'load_page') );

        global $submenu;
        if ( isset( $submenu[ self::PAGE_IDENTIFIER ] ) && current_user_can( 'manage_options' ) ) {
            $submenu[ self::PAGE_IDENTIFIER ][0][0] = __( 'Dashboard', 'og' );
        }
    }

    /**
     * Load the form for a ThreatPress admin page
     */
    function load_page() {
        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

        switch ( $page ) {
            case 'threatpress_scanner':
                require_once( THREATPRESS_PLUGIN_DIR . 'admin/pages/scanner.php' );
                break;

            case 'threatpress_shield':
                require_once( THREATPRESS_PLUGIN_DIR . 'admin/pages/shield.php' );
                break;

            case 'threatpress_settings':
                require_once( THREATPRESS_PLUGIN_DIR . 'admin/pages/settings.php' );
                break;

            case self::PAGE_IDENTIFIER:
            default:
                require_once( THREATPRESS_PLUGIN_DIR . 'admin/pages/dashboard.php' );
                break;
        }
    }

    /**
     * Load CSS and JavaScript files
     */
    function admin_enqueue_scripts() {
        global $pagenow;

        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

        $page = ( isset( $page ) ) ? $page : '';

        wp_enqueue_script( 'threatpress-dismiss', THREATPRESS_PLUGIN_URL . 'admin/assets/js/threatpress-dismiss.js' );

        if ( strpos( $page, 'threatpress_' ) === false && $pagenow !== 'admin.php' )
            return;

        wp_enqueue_style( 'threatpress-admin', THREATPRESS_PLUGIN_URL . 'admin/assets/css/threatpress-admin.css' );
        wp_enqueue_script( 'threatpress-admin', THREATPRESS_PLUGIN_URL . 'admin/assets/js/threatpress-admin.js' );
        wp_enqueue_script( 'threatpress-ajax', THREATPRESS_PLUGIN_URL . 'admin/assets/js/threatpress-ajax.js' );

        if ( $page === 'threatpress_dashboard' && $pagenow === 'admin.php' ) {
            wp_enqueue_style( 'threatpress-dashboard', THREATPRESS_PLUGIN_URL . 'admin/assets/css/threatpress-dashboard.css' );
            wp_enqueue_script( 'threatpress-dashboard', THREATPRESS_PLUGIN_URL . 'admin/assets/js/threatpress-dashboard.js' );
        }

        if ( $page === 'threatpress_scanner' && $pagenow === 'admin.php' ) {
            wp_enqueue_style( 'threatpress-scanner', THREATPRESS_PLUGIN_URL . 'admin/assets/css/threatpress-scanner.css' );
            wp_enqueue_script( 'threatpress-scanner', THREATPRESS_PLUGIN_URL . 'admin/assets/js/threatpress-scanner.js' );
        }

        if ( $page === 'threatpress_shield' && $pagenow === 'admin.php' ) {
            wp_enqueue_style( 'threatpress-shield', THREATPRESS_PLUGIN_URL . 'admin/assets/css/threatpress-shield.css' );
            wp_enqueue_script( 'threatpress-shield', THREATPRESS_PLUGIN_URL . 'admin/assets/js/threatpress-shield.js' );
        }

    }

}