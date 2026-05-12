<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register front-end styles and scripts.
 *
 * Handles are registered (not enqueued) here and pulled in on-demand
 * either via the is_active_widget() check below or from inside the
 * widget() render itself, so the assets do not load on pages that
 * never use the widget.
 *
 * @return void
 */
function sp_widget_post_slider_register_assets() {
	$suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) ? '' : '.min';

	wp_register_style( 'slick', WIDGET_POST_SLIDER_URL . "assets/css/slick{$suffix}.css", array(), '1.6.0' );
	wp_register_style( 'widget-post-slider-style', WIDGET_POST_SLIDER_URL . "assets/css/style{$suffix}.css", array( 'slick' ), '1.3.7' );

	wp_register_script( 'slick-min-js', WIDGET_POST_SLIDER_URL . 'assets/js/slick.min.js', array( 'jquery' ), '1.6.0', true );
	wp_register_script( 'widget-post-slider-js', WIDGET_POST_SLIDER_URL . "assets/js/widget-post-slider{$suffix}.js", array( 'slick-min-js' ), '1.3.7', true );
}
add_action( 'wp_enqueue_scripts', 'sp_widget_post_slider_register_assets' );

/**
 * Enqueue front-end assets early when the widget is placed in a classic sidebar.
 *
 * This is the FOUC-friendly path: styles get printed in <head> rather
 * than the footer. The widget() method enqueues the same handles again
 * as a fallback for block-editor / Legacy Widget Block contexts where
 * is_active_widget() does not return true.
 *
 * @return void
 */
function sp_widget_post_slider_maybe_enqueue_assets() {
	if ( ! is_active_widget( false, false, 'sp_widget_post_slider', true ) ) {
		return;
	}
	sp_widget_post_slider_enqueue_assets();
}
add_action( 'wp_enqueue_scripts', 'sp_widget_post_slider_maybe_enqueue_assets' );

/**
 * Enqueue the widget's registered styles and script.
 *
 * Safe to call multiple times; wp_enqueue_* is idempotent per handle.
 *
 * @return void
 */
function sp_widget_post_slider_enqueue_assets() {
	wp_enqueue_style( 'slick' );
	wp_enqueue_style( 'widget-post-slider-style' );
	wp_enqueue_script( 'widget-post-slider-js' );
}
