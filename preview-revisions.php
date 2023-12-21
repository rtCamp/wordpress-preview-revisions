<?php
/**
 * Plugin Name: WordPress Preview Revisions
 * Description: Preview the WordPress revisions in new tab.
 * Plugin URI:  https://rtcamp.com
 * Author:      rtCamp
 * Author URI:  https://rtcamp.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.5
 * Text Domain: preview-revisions
 *
 * @package preview-revisions
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'PREVIEW_REVISIONS_FEATURES_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PREVIEW_REVISIONS_FEATURES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

if ( ! class_exists( 'Preview_Revisions' ) ) {
	require_once PREVIEW_REVISIONS_FEATURES_PATH . '/inc/classes/class-preview-revisions.php';

	new Preview_Revisions();
}
