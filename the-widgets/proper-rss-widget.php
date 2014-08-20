<?php

class ProperRssWidget extends WP_Widget {

	private $css_class = 'proper-rss-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, 'PROPER RSS', $widget_ops );

		// Widget options
		$this->widget_fields = array(
			array(
				'label'       => 'Title',
				'type'        => 'text',
				'id'          => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default'     => 'RSS Feed',
			),
			array(
				'label'       => 'RSS URL',
				'type'        => 'url',
				'id'          => 'rss_url',
				'description' => 'A direct link to a valid RSS or ATOM feed',
				'default'     => ''
			),
			array(
				'label'       => '# of items to show',
				'type'        => 'number',
				'id'          => 'num_posts',
				'description' => '',
				'default'     => get_option( 'posts_per_page' ),
			),
			array(
				'label'       => 'Show blurb (if available)?',
				'type'        => 'checkbox',
				'id'          => 'show_blurb',
				'description' => '',
				'default'     => 'yes',
			),
			array(
				'label'       => 'Show date (if available)?',
				'type'        => 'checkbox',
				'id'          => 'show_date',
				'description' => '',
				'default'     => 'yes',
			),
			array(
				'label'       => 'Open links in a new tab?',
				'type'        => 'checkbox',
				'id'          => 'target',
				'description' => '',
				'default'     => 'yes',
			),
			array(
				'label'       => 'Add rel="nofollow" to links?',
				'type'        => 'checkbox',
				'id'          => 'link_rel',
				'description' => '',
				'default'     => 'yes',
			),
			array(
				'label'       => 'Cache duration (minutes)',
				'type'        => 'number',
				'id'          => 'cache_duration',
				'description' => 'How long should this feed be cached? A longer cache will lead to a faster page load',
				'default'     => 30,
			),

		);

	}

	/*
	 * Front-end widget output
	 */
	function widget( $args, $instance ) {

		// Build argument array for feed fetching function
		$feed_args = array(
			'url'   => esc_url( $instance['rss_url'] ),
			'items' => intval( $instance['num_posts'] )
		);

		// Configure cache settings
		$cache_duration            = intval( $instance['cache_duration'] );
		$feed_args['enable_cache'] = 0;
		if ( ! empty( $cache_duration ) ) {
			$feed_args['enable_cache']   = 1;
			$feed_args['cache_duration'] = $cache_duration;
		}

		// Should the date be shown?
		$feed_args['get_date'] = 0;
		if ( ! empty( $instance['show_date'] ) ) {
			$feed_args['get_date'] = 1;
		}

		// Should the blurb be shown?
		$feed_args['get_blurb'] = 0;
		if ( ! empty( $instance['show_blurb'] ) ) {
			$feed_args['get_blurb'] = 1;
		}

		// Link HTML insert
		$link_insert = '';
		if ( ! empty( $instance['target'] ) ) {
			$link_insert .= ' target="_blank"';
		}
		if ( ! empty( $instance['link_rel'] ) ) {
			$link_insert .= ' rel="nofollow"';
		}

		$feed_content = proper_widget_fetch_rss( $feed_args );

		// Get the feed and check for content
		$feed_content = proper_widget_fetch_rss( $feed_args );
		if ( ! is_array( $feed_content ) || ! count( $feed_content ) > 0 ) {
			echo '<!-- PROPER RSS widget found no content at URL ' . $feed_args['url'] . '-->';
			return;
		}

		proper_widget_wrap_html( $args, 'top', $instance['title'], $this->css_class );

		echo '<ul class="proper-feed-links proper-links-list">';

		foreach ( $feed_content as $item ) {

			echo '<li><p>';
			echo '<a href="';
			echo esc_url( $item['link'] );
			echo '" title="';
			echo esc_attr( $item['title'] );
			echo '" rel="nofollow"' . $link_insert . '>';
			echo apply_filters( 'the_title', $item['title'] );
			echo '</a>';

			if ( $feed_args['get_date'] ) {
				echo '<br><span class="proper-date">' . $item['date'] . '</span>';
			}

			echo '</p>';

			if ( $feed_args['get_blurb'] ) {
				echo '<p class="proper-rss-blurb">' . wpautop( $item['blurb'] ) . '</p>';
			}

			echo '</li>';
		}

		echo '</ul>';
		proper_widget_wrap_html( $args, 'bottom' );
	}

	/*
	 * Sanitize and validate options
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['show_date'] = intval( $new_instance['show_date'] );
		$instance['show_blurb'] = intval( $new_instance['show_blurb'] );
		$instance['target']    = intval( $new_instance['target'] );
		$instance['link_rel']    = intval( $new_instance['link_rel'] );
		$instance['rss_url'] = filter_var( $new_instance['rss_url'], FILTER_SANITIZE_URL );

		$num_posts = intval( $new_instance['num_posts'] );
		$instance['num_posts'] = $num_posts > 0 ? $num_posts : 10;

		$cache_dur = intval( $new_instance['cache_duration'] );
		$instance['cache_duration'] = $num_posts > 0 ? $cache_dur : 0;

		return $instance;
	}

	/*
	 * Output the widget form in wp-admin
	 */
	function form( $instance ) {

		for ( $i = 0; $i < count( $this->widget_fields ); $i ++ ) :
			$field_id                              = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id']   = $this->get_field_id( $field_id );
			$this->widget_fields[$i]['field_name'] = $this->get_field_name( $field_id );
		endfor;
		proper_widget_output_fields( $this->widget_fields, $instance );
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("ProperRssWidget");' ) );