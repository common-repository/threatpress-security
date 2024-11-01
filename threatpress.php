<?php
/*
 * Plugin Name: ThreatPress Security
 * Description: Active protection, advanced scanning and monitoring. Scans for WordPress core changes, plugins & themes vulnerabilities, known malware, blacklisting, and more.
 * Version: 0.9.5
 * Author: ThreatPress
 * Author URI: https://www.threatpress.com
 * License: GPL2+
 *
 * Text Domain: threatpress
 * Domain Path: /languages/
 */

// don't call the file directly
defined( 'ABSPATH' ) or die();

// Define constants
define( 'THREATPRESS_VERSION', '0.9.5' );
define( 'THREATPRESS_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'THREATPRESS_PLUGIN_URL', trailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'THREATPRESS_PLUGIN_FILE', __FILE__ );

require_once( dirname( THREATPRESS_PLUGIN_FILE ) . '/bootstrap.php' );