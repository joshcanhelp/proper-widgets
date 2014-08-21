<?php

/*
Plugin Name: PROPER Widgets
Plugin URI: http://theproperweb.com/product/proper-widgets/
Description: More widgets than you can shake a stick at.
Version: 1.0.0
Author: PROPER Web Development
Author URI: http://theproperweb.com
License: GPLv2 or later
Text Domain: proper-widgets
*/

// Constants
define( 'PROPER_WIDGETS_VERSION', '1.0.0' );
define( 'PROPER_WIDGETS_MINIMUM_WP_VERSION', '3.0' );
define( 'PROPER_WIDGETS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PROPER_WIDGETS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Core files
require_once( PROPER_WIDGETS_PLUGIN_DIR . 'inc/plugin-settings.php' );
if ( ! class_exists( 'PhpFormBuilder' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'inc/PhpFormBuilder.php' );
}


// Require widget files if settings allow for it

if ( ProperWidgetSettings::get_setting( 'widget_article' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-article-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_gnews' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-gnews-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_linkedimg' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-linked-image-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_links' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-links-widget.php' );
	add_filter( 'pre_option_link_manager_enabled', '__return_true' );
}

if ( ProperWidgetSettings::get_setting( 'widget_posts' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-posts-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_rss' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-rss-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_embed' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-embed-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_authors' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-authors-widget.php' );
}

if ( ProperWidgetSettings::get_setting( 'widget_comments' ) ) {
	require_once( PROPER_WIDGETS_PLUGIN_DIR . 'the-widgets/proper-comments-widget.php' );
}


// Hide core widgets
function proper_widget_unregister_widgets() {

	if ( ! ProperWidgetSettings::get_setting( 'widget_core_links' ) ) {
		unregister_widget( 'WP_Widget_Links' );
	}

	if ( ! ProperWidgetSettings::get_setting( 'widget_core_posts' ) ) {
		unregister_widget( 'WP_Widget_Recent_Posts' );
	}

	if ( ! ProperWidgetSettings::get_setting( 'widget_core_rss' ) ) {
		unregister_widget( 'WP_Widget_RSS' );
	}

	if ( ! ProperWidgetSettings::get_setting( 'widget_core_comments' ) ) {
		unregister_widget( 'WP_Widget_Recent_Comments' );
	}
}

add_action( 'widgets_init', 'proper_widget_unregister_widgets', 1 );


/*
Builds all widget admin forms
*/
function proper_widget_output_fields( $fields, $instance ) {

	$widget_form = new PhpFormBuilder();
	$widget_form->set_att('add_honeypot', FALSE);
	$widget_form->set_att('form_element', FALSE);
	$widget_form->set_att('add_submit', FALSE);
	$widget_form->set_att('markup', 'html');

	foreach ( $fields as $field ) {

		$input_args = array(
			'type'       => $field['type'],
			'name'       => $field['field_name'],
			'class'      => array( 'widefat' ),
			'wrap_tag'	=> '',
			'before_html'   => '<p class="proper_widget_fields field_type_' . $field['type'] . '">',
			'after_html' => '</p>',
		);

		if ( ! empty( $field['description'] ) ) {
			$input_args['after_html'] = '<span class="description">' .
				$field['description'] . '</span>' . $input_args['after_html'];
		}

		switch ( $field['type'] ) {

			case 'text':
			case 'email':
			case 'url':
			case 'number':
			case 'password':
			case 'textarea':
				$input_args['value'] = isset( $instance[$field['id']] ) ? $instance[$field['id']] : $field['default'];
				break;

			case 'checkbox':
				$input_args['value'] = 1;
				$input_args['checked'] = ! empty( $instance[$field['id']] ) ? TRUE : FALSE;
				$input_args['class'] = array( 'checkbox' );
				break;

			case 'select':
			case 'select_assoc':
				$input_args['selected'] = isset( $instance[$field['id']] ) ? $instance[$field['id']] : FALSE;
				$input_args['options'] = $field['options'];
				if ( $field['type'] == 'select' ) {
					$input_args['options'] = array();
					foreach ( $field['options'] as $val ) {
						$input_args['options'][$val] = $val;
					}
				} else {
					$input_args['type'] = 'select';
				}
				break;

		}

		$widget_form->add_input( $field['label'], $input_args, esc_attr( $field['field_id'] ) );

	}

	$widget_form->build_form();

}

/**
 * Output CSS if the setting is chosen
 */
function proper_widget_wp_head() {

	echo '<style type="text/css">';
	include( PROPER_WIDGETS_PLUGIN_DIR . 'css/widgets.css' );
	echo '</style>';

}

add_action( 'wp_head', 'proper_widget_wp_head' );

/**
 * CSS and JavaScript for admin pages
 */
function proper_widget_admin_enqueue_scripts() {

	global $pagenow;

	if ( $pagenow == 'widgets.php' ) {
		wp_enqueue_style(
			'proper-widgets-admin',
			PROPER_WIDGETS_PLUGIN_URL . 'css/admin.css'
		);
	}

}

add_action( 'admin_enqueue_scripts', 'proper_widget_admin_enqueue_scripts' );

/**
 * Process RSS feeds using SimplePie
 *
 * @param $feed_args
 *
 * @return array|string
 */
function proper_widget_fetch_rss( $feed_args ) {

	$rss = fetch_feed( $feed_args['url'] );

	// Store the error message as a string to return at the end
	if ( is_wp_error( $rss ) ) {
		return $rss->get_error_message();
	}

	// default settings if none are present in the args passed
	$defaults = array(
		'get_blurb'      => FALSE,
		'get_date'       => FALSE,
		'enable_cache'   => TRUE,
		'cache_duration' => 1800,
		'items'          => 10
	);

	// Merges defaults with args passed and stores them as variables to use here
	$feed_args = wp_parse_args( $feed_args, $defaults );

	if ( $feed_args['enable_cache'] ) {
		// Set the cache duration
		$rss->enable_cache( $feed_args['enable_cache'] );
		$rss->set_cache_duration( $feed_args['cache_duration'] );
	}

	// Start 'er up
	$rss->init();

	if ( ! $rss->get_item_quantity() ) {
		return '';
	}


	// Parsing, formatting, and storing feed content
	$feed_content = array();
	foreach ( $rss->get_items( 0, $feed_args['items'] ) as $item ) {


		$link = $item->get_link();
		if ( empty( $link ) ) {
			$link = $item->get_permalink();
		}

		$title = $item->get_title();

		if ( ! empty( $feed_args['get_blurb'] ) ) {
			$blurb = $item->get_description();
		}

		if ( ! empty( $feed_args['get_date'] ) ) {
			$date = $item->get_date( get_option( 'date_format' ) );
		}

		$feed_content[] = compact( "link", "title", "blurb", "date" );

	}

	return $feed_content;
}

/**
 * Widget wrapper HTML for beginning of the widget
 *
 * @param        $args
 * @param string $title
 * @param string $class
 */
function proper_widget_wrap_top_html ( $args, $title = '', $class = '' ) {

	$title = sanitize_text_field( $title );
	$title = apply_filters( 'widget_title', $title );

	echo $args['before_widget'] . '<div class="proper-widget ' . $class . '">';

	if ( ! empty( $title ) ) {
		echo $args['before_title'] . $title . $args['after_title'];
	}

}

/**
 * Widget wrapper HTML for end of the widget
 *
 * @param        $args
 */
function proper_widget_wrap_bottom_html ( $args ) {

	echo '</div>' . $args['after_widget'];
}

/**
 * Get just the avatar's URL from get_avatar output
 *
 * @param string $get_avatar get_avatar output
 *
 * @return string
 */
function proper_widget_get_avatar_url( $get_avatar ) {
	preg_match( "/src='(.*?)'/i", $get_avatar, $matches );
	return $matches[1];
}

/**
 * Truncate a string
 *
 * @param        $string
 * @param        $limit
 * @param string $break
 *
 * @return string
 */
function proper_widget_truncate( $string, $limit, $break = " " ) {

	if ( strlen( $string ) >= $limit ) {

		$string = substr( $string, 0, $limit );

		while ( $string[strlen( $string ) - 1] != $break && ! empty( $string ) ) {
			$string = substr( $string, 0, strlen( $string ) - 1 );
		}

		return $string . ' ...';
	}

	return $string;
}