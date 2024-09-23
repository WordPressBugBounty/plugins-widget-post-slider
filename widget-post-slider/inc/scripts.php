<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access

/**
 * Enqueue Styles and Scripts.
 *
 * @return void
 */
function sp_widget_post_slider_script_and_style() {
	// CSS.
	wp_enqueue_style( 'slick', WIDGET_POST_SLIDER_URL . 'assets/css/slick.css', array(), 'all' );
	wp_enqueue_style( 'font-awesome-css', WIDGET_POST_SLIDER_URL . 'assets/css/font-awesome.min.css', array(), 'all' );
	wp_enqueue_style( 'widget-post-slider-style', WIDGET_POST_SLIDER_URL . 'assets/css/style.css', array(), 'all' );

	// JS.
	wp_enqueue_script( 'slick-min-js', WIDGET_POST_SLIDER_URL . 'assets/js/slick.min.js', array( 'jquery' ), '1.6.0', true );
}
add_action( 'wp_enqueue_scripts', 'sp_widget_post_slider_script_and_style' );
