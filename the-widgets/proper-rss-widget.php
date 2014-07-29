<?php 

class proper_rss_widget extends WP_Widget {
	
	function proper_rss_widget () {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-rss-widget', 'PROPER RSS', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => 'RSS Feed',
			),		
			array(
				'label' => 'RSS URL',
				'type' => 'url',
				'id' => 'rss_url',
				'description' => 'A direct link to a valid RSS or ATOM feed',
				'default' => ''
			),
			array(
				'label' => '# of items to show',
				'type' => 'number',
				'id' => 'num_posts',
				'description' => '',
				'default' => get_option('posts_per_page'),
			),
			array(
				'label' => 'Show blurb (if available)?',
				'type' => 'checkbox',
				'id' => 'show_blurb',
				'description' => '',
				'default' => 'yes',
			),
			array(
				'label' => 'Show date (if available)?',
				'type' => 'checkbox',
				'id' => 'show_date',
				'description' => '',
				'default' => 'yes',
			),
			array(
				'label' => 'Open links in a new tab?',
				'type' => 'checkbox',
				'id' => 'target',
				'description' => '',
				'default' => 'yes',
			),
			array(
				'label' => 'Add rel="nofollow" to links?',
				'type' => 'checkbox',
				'id' => 'link_rel',
				'description' => '',
				'default' => 'yes',
			),
			array(
				'label' => 'Cache duration (minutes)',
				'type' => 'number',
				'id' => 'cache_duration',
				'description' => 'How long should this feed be cached? A longer cache will lead to a faster page load',
				'default' => 30,
			),
			
		);
	
	}
	 
	function widget($args, $instance) {
		
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-links-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
		
		echo '
			<ul class="proper-feed-links proper-links-list">';
		
		// Build argument array for feed fetching function
		$feed_args = array(
			'url' => $rss_url,
			'items' => $num_posts
		);
		
		// Configure cache settings
		$feed_args['enable_cache'] = 1;
		if ($cache_duration < 1) 
			$feed_args['enable_cache'] = 0;
		else 
			$feed_args['cache_duration'] = $cache_duration;
		
		$feed_args['get_date'] = 0;
		if ($show_date == 'yes') 
			$feed_args['get_date'] = 1;
		
		$feed_args['get_blurb'] = 0;
		if ($show_blurb == 'yes') 
			$feed_args['get_blurb'] = 1;
		
		$link_insert = '';
		if ($target == 'yes') $link_insert .= ' target="_blank"';
		if ($link_rel == 'yes') $link_insert .= ' rel="nofollow"';
		
		$feed_content = proper_widget_fetch_rss($feed_args);
		
		if (is_array($feed_content) && count($feed_content) > 0) :
	
			foreach($feed_content as $item) :
			
				// Now we output the individual link
				echo '
				<li>
					<a href="' . $item['link'] . '" ' . $link_insert . '>'. $item['title'] . '</a>';
					
				if ($feed_args['get_date']) 
					echo '
					<p class="proper-link-date">' . $item['date'] . '</p>';
					
				if ($feed_args['get_blurb']) 
					echo '
					<p class="proper-link-blurb">' . wpautop($item['blurb']) . '</p>';
				
				echo '
				</li>';
		
			endforeach;

		else :
			
			echo '<li><em>No items to show...</em></li>';
				
		endif;
		
		echo '
			</ul>
		</div>
		'. $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));
		
		$instance['rss_url'] = filter_var($new_instance['rss_url'], FILTER_SANITIZE_URL);
		
		$instance['show_blurb'] = isset($new_instance['show_blurb']) && $new_instance['show_blurb'] === 'yes' ? 'yes' : ''; 
		$instance['show_date'] = isset($new_instance['show_date']) && $new_instance['show_date'] === 'yes' ? 'yes' : ''; 
		$instance['target'] = isset($new_instance['target']) && $new_instance['target'] === 'yes' ? 'yes' : ''; 
		$instance['link_rel'] = isset($new_instance['link_rel']) && $new_instance['link_rel'] === 'yes' ? 'yes' : ''; 
		
		// Validating and storing the number of items to show
		$num_posts = filter_var($new_instance['num_posts'], FILTER_SANITIZE_NUMBER_INT);
		if (empty($num_posts) || (int)$num_posts < 1) 
			$instance['num_posts'] = 10;
		else
			$instance['num_posts'] = $num_posts;
		
		$cache_dur = filter_var($new_instance['cache_duration'], FILTER_SANITIZE_NUMBER_INT);
		if (empty($cache_dur) || (int)$cache_dur < 1) 
			$instance['cache_duration'] = 0;
		else 
			$instance['cache_duration'] = $cache_dur;
		
		return $instance;

	}
 
	function form($instance) {
		
		for ($i = 0; $i < count($this->widget_fields); $i++) :
			$field_id = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id'] = $this->get_field_id($field_id);
			$this->widget_fields[$i]['field_name'] = $this->get_field_name($field_id);
		endfor;
		proper_widgets_output_fields($this->widget_fields, $instance);

	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("proper_rss_widget");') );