<?php

class ProperRssWidget extends WP_Widget {

	private $css_class = 'proper-rss-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		parent::__construct(
			$this->css_class,
			__( 'PROPER RSS', 'proper-widgets' ),
			array(
				'classname' => $this->css_class,
			)
		);

		// Widget options
		$this->widget_fields = array(
			array(
				'label'       => __( 'Title', 'proper-widgets' ),
				'type'        => 'text',
				'id'          => 'title',
				'description' => __( 'Title for this widget or leave blank for none', 'proper-widgets' ),
				'default'     => '',
			),
			array(
				'label'       => __( 'RSS URL', 'proper-widgets' ),
				'type'        => 'url',
				'id'          => 'rss_url',
				'description' => __( 'A direct link to a valid RSS or ATOM feed', 'proper-widgets' ),
				'default'     => ''
			),
			array(
				'label'       => __( '# of items to show', 'proper-widgets' ),
				'type'        => 'number',
				'id'          => 'num_posts',
				'default'     => get_option( 'posts_per_page' ),
			),
			array(
				'label'       => __( 'Show blurb (if available)', 'proper-widgets' ),
				'type'        => 'checkbox',
				'id'          => 'show_blurb',
				'default'     => 1,
			),
			array(
				'label'       => __( 'Show date (if available)', 'proper-widgets' ),
				'type'        => 'checkbox',
				'id'          => 'show_date',
				'default'     => 1,
			),
			array(
				'label'       => __( 'Open links in a new tab', 'proper-widgets' ),
				'type'        => 'checkbox',
				'id'          => 'target',
				'default'     => 1,
			),
			array(
				'label'       => __( 'Add rel="nofollow" to links?', 'proper-widgets' ),
				'type'        => 'checkbox',
				'id'          => 'link_rel',
				'default'     => 1,
			),
			array(
				'label'       => __( 'Cache duration (minutes)', 'proper-widgets' ),
				'type'        => 'number',
				'id'          => 'cache_duration',
				'description' => __( 'How long should this feed be cached? A longer cache will lead to a faster page load', 'proper-widgets' ),
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
			echo '<!-- ' . __( 'PROPER RSS widget found no content at URL', 'proper-widgets' ) . ': ' . $feed_args['url'] . '-->';
			return;
		}

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );

		echo '<ul class="proper-feed-links proper-links-list">';

		foreach ( $feed_content as $item ) {

			echo '<li><p>';

			echo sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				esc_url( $item['link'] ),
				esc_attr( $item['title'] ),
				$link_insert,
				apply_filters( 'the_title', $item['title'] )
			);

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
		proper_widget_wrap_bottom_html( $args );
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