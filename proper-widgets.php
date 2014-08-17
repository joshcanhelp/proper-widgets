<?php

/*
Plugin Name: PROPER Widgets
Plugin URI: http://theproperweb.com/code/wp/proper-widgets
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
require_once( PROPER_WIDGETS_PLUGIN_DIR . 'plugin-settings.php' );


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
 * Helper functions
 */

function proper_widget_truncate( $string, $limit, $break = " " ) {

	if ( strlen( $string ) >= $limit ) {

		$string = substr( $string, 0, $limit );

		while ( $string[strlen( $string ) - 1] != $break && ! empty( $string ) ) {
			$string = substr( $string, 0, strlen( $string ) - 1 );
		}
	}

	return $string;
}

/*
Builds all widget admin forms
*/
function proper_widgets_output_fields( $fields, $instance ) {

	foreach ( $fields as $field ) :

		echo '<p class="pstart_widget_fields">';

		switch ( $field['type'] ) :

			case 'text':
			case 'email':
			case 'url':
			case 'number':
			case 'password':
				?>

				<label for="<?php echo esc_attr( $field['field_id'] ); ?>">
					<?php echo $field['label'] ?>
				</label>
				<input type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $field['field_id'] ) ?>" name="<?php echo $field['field_name'] ?>" value="<?php echo isset( $instance[$field['id']] ) ? $instance[$field['id']] : $field['default'] ?>" class="widefat">

				<?php
				break;

			case 'textarea':
				?>

				<label for="<?php echo esc_attr( $field['field_id'] ) ?>" class="textarea_label"><?php echo $field['label'] ?></label>
				<textarea id="<?php echo esc_attr( $field['field_id'] ) ?>" name="<?php echo $field['field_name'] ?>" class="widefat"><?php
					echo isset( $instance[$field['id']] ) ? $instance[$field['id']] : $field['default']
				?></textarea>

				<?php
				break;

			case 'checkbox':
				?>

				<input type="checkbox" id="<?php echo esc_attr( $field['field_id'] ) ?>" name="<?php echo $field['field_name'] ?>" value="1" <?php
				if ( !empty( $instance[$field['id']] ) ) echo 'checked';
				?>>
				<label for="<?php echo esc_attr( $field['field_id'] ) ?>" class="checkbox_label"><?php echo $field['label'] ?></label>

				<?php
				break;

			case 'select':
			case 'select_assoc':
				?>

				<label for="<?php echo esc_attr( $field['field_id'] ) ?>" class="select_label">
					<?php echo $field['label'] ?>
				</label>
				<select id="<?php echo esc_attr( $field['field_id'] ) ?>" name="<?php echo $field['field_name'] ?>" class="widefat">

					<?php
					if ( $field['options'] == 'callback' && function_exists( $field['options_callback'] ) ) :

						// Use the options_callback to output options
						// Pass in the selected value, if any
						$field['options_callback']( ! empty( $instance[$field['id']] ) ? $instance[$field['id']] : '' );

					elseif ( is_array( $field['options'] ) ) :
						foreach ( $field['options'] as $key => $val ) :

							// Adjusting for non-assoc arrays for options
							if ( $field['type'] == 'select' ) {
								$key = $val;
							}

							// Selecting the current set option
							$checked = isset( $instance[$field['id']] ) && $instance[$field['id']] == $key ? ' selected' : '';
							?>

							<option value="<?php echo esc_attr( $key ); ?>"<?php echo $checked; ?>><?php echo $val; ?></option>

						<?php
						endforeach;
						?>

					<?php
					else: ?>
						<option value="">No valid options available</option>
					<?php endif; ?>

				</select>

				<?php
				break;

		endswitch;

		if ( ! empty( $field['description'] ) ) {
			echo '<span class="field_description">' . $field['description'] . '</span>';
		}

		echo '</p>';

	endforeach;

}

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

// Output an embedded player using a URL and size parameters
function proper_widget_output_embed( $url, $w = 500, $h = 280 ) {

	$output    = '';
	$embed_url = parse_url( $url );
	$host      = isset( $embed_url['host'] ) ? $embed_url['host'] : '';
	$path      = isset( $embed_url['path'] ) ? str_replace( '/', '', $embed_url['path'] ) : '';
	$query     = isset( $embed_url['query'] ) ? $embed_url['query'] : '';

	switch ( $host ) :

		case 'www.youtube.com':
		case 'youtube.com':
			$queries = explode( '&', $query );
			foreach ( $queries as $q ) :
				$q_parts = explode( '=', $q );
				if ( $q_parts[0] == 'v' )
					$output .= proper_widget_output_youtube( $q_parts[1], $w, $h );

			endforeach;
			break;

		case 'youtu.be':
			if ( ! empty( $path ) )
				$output .= proper_widget_output_youtube( $path, $w, $h );
			break;

		case 'www.vimeo.com':
		case 'vimeo.com':
			if ( ! empty( $path ) )
				$output .= proper_widget_output_vimeo( $path, $w, $h );
			break;

	endswitch;

	return ! empty( $output ) ? $output : 'Bad URL';
}
