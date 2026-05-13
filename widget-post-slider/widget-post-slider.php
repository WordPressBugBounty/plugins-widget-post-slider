<?php
/**
 * Widget Post Slider
 *
 * @since             1.3.3
 * @package           Widget_Post_Slider
 *
 * @wordpress-plugin
 * Plugin Name:       Widget Post Slider
 * Plugin URI:        https://wordpress.org/plugins/widget-post-slider/
 * Description:       Widget Post Slider to display posts image in a slider from category.
 * Version:           1.3.8
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            ShapedPlugin
 * Author URI:        https://shapedplugin.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       widget-post-slider
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define constants.
 */
define( 'WIDGET_POST_SLIDER_VERSION', '1.3.8' );
define( 'WIDGET_POST_SLIDER_URL', plugin_dir_url( __FILE__ ) );
define( 'WIDGET_POST_SLIDER_PATH', plugin_dir_path( __FILE__ ) );

require_once WIDGET_POST_SLIDER_PATH . 'inc/scripts.php';
require_once WIDGET_POST_SLIDER_PATH . 'inc/functions.php';
