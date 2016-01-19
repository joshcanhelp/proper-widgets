<?php 

class ProperGnewsWidget extends WP_Widget {

	private $css_class = 'proper-gnews-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct () {

		parent::__construct(
			$this->css_class,
			__( 'PROPER Google News Lite', 'proper-widgets' ),
			array(
				'classname' => $this->css_class,
			)
		);

		// Widget options
		$this->widget_fields = array(
			array(
				'label' => __( 'Title', 'proper-widgets' ),
				'type' => 'text',
				'id' => 'title',
				'description' => __( 'Title for this widget or leave blank for none', 'proper-widgets' ),
				'default' => 'Google News',
			),		
			array(
				'label' => __( 'Keyword or phrase', 'proper-widgets' ),
				'type' => 'text',
				'id' => 'keyword',
				'default' => ''
			),
			array(
				'label' => __( '# of items to show', 'proper-widgets' ),
				'type' => 'number',
				'id' => 'num_posts',
				'default' => get_option('posts_per_page'),
			),
			array(
				'label' => __( 'Show date (if available)', 'proper-widgets' ),
				'type' => 'checkbox',
				'id' => 'show_date',
				'default' => '',
			),
			array(
				'label' => __( 'Open links in a new tab?', 'proper-widgets' ),
				'type' => 'checkbox',
				'id' => 'target',
				'default' => 'yes',
			),
			array(
				'label' => __( 'Cache duration (minutes)', 'proper-widgets' ),
				'type' => 'number',
				'id' => 'cache_duration',
				'description' => __( 'How long should this feed be cached? A longer cache will lead to a faster page load', 'proper-widgets' ),
				'default' => 30,
			),
			
		);
	}

	/*
	 * Front-end widget output
	 */
	function widget($args, $instance) {

		// Setup feed arguments and set the number of items to show
		$feed_args = array(
			'items'    => intval( $instance['num_posts'] )
		);

		// Configure cache settings
		$cache_duration = intval( $instance['cache_duration'] );
		$feed_args['enable_cache'] = 0;
		if ( ! empty( $cache_duration ) ) {
			$feed_args['enable_cache'] = 1;
			$feed_args['cache_duration'] = $cache_duration;
		}

		// Should the date be shown?
		$feed_args['get_date'] = 0;
		if ( ! empty( $instance['show_date'] ) ) {
			$feed_args['get_date'] = 1;
		}

		// Build the keyword URL and pass the whole thing to the feed fetcher
		$gn_query         = trim( $instance['keyword'] );
		$gn_query         = str_replace( ' ', '+', $gn_query );
		$feed_args['url'] = 'http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&q=' . $gn_query . '&cf=all&output=rss';

		// Get the feed and check for content
		$feed_content = proper_widget_fetch_rss( $feed_args );
		if ( !is_array( $feed_content ) || ! count( $feed_content ) > 0 ) {
			echo '<!-- ' . __( 'PROPER Google News widget found no content at URL', 'proper-widgets' ) .
				': ' .$feed_args['url'] . '-->';
			return;
		}

		$target = ! empty( $instance['target'] ) ? ' target="_blank"' : '';

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );
		
		echo '<ul class="proper-gnews-links proper-links-list">';
		foreach ( $feed_content as $item ) {
			echo '<li><p>';
			echo '<a class="proper-headline-link" href="';
			echo esc_url( $item['link'] );
			echo '" title="';
			echo esc_attr( $item['title']);
			echo '" rel="nofollow"' . $target . '>';
			echo apply_filters('the_title', $item['title']);
			echo '</a>';

			if ( $feed_args['get_date'] ) {
				echo '<br><span class="proper-date">' . $item['date'] . '</span>';
			}

			echo '</p></li>';
		}

		echo '</ul>';
		proper_widget_wrap_bottom_html( $args );
	}

	/*
	 * Sanitize and validate options
	 */
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['show_date'] = intval( $new_instance['show_date'] );
		$instance['target'] = intval( $new_instance['target'] );
		$instance['keyword'] = sanitize_text_field($new_instance['keyword']);

		// Set the number of posts according to widget limits
		$num_posts = intval( $new_instance['num_posts'] );
		if ( $num_posts < 1 ) {
			$instance['num_posts'] = 1;
		} else if ( $num_posts > 20 ) {
			$instance['num_posts'] = 20;
		} else {
			$instance['num_posts'] = $num_posts;
		}

		// Set a rational cache duration
		$cache_dur = intval( $new_instance['cache_duration'] );
		if ( $cache_dur < 1 ) {
			$instance['cache_duration'] = 0;
		}
		else {
			$instance['cache_duration'] = $cache_dur;
		}

		return $instance;
	}

	/*
	 * Output the widget form in wp-admin
	 */
	function form($instance) {
		
		for ($i = 0; $i < count($this->widget_fields); $i++) :
			$field_id = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id'] = $this->get_field_id($field_id);
			$this->widget_fields[$i]['field_name'] = $this->get_field_name($field_id);
		endfor;
		proper_widget_output_fields($this->widget_fields, $instance);

		echo '<p><strong>' . __( 'Want to display an excerpt and thumbnail? Try the', 'proper-widgets' );
		echo ' <a href="http://theproperweb.com/product/google-news-wordpress/" target="_blank">';
		echo __( 'Google News for WordPress plugin', 'proper-widgets' ) . '.</strong></p>';
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("ProperGnewsWidget");') );