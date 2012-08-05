<?php 

class proper_gnews_widget extends WP_Widget {
	
	function proper_gnews_widget () {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-gnews-widget', 'Proper Google News', $widget_ops);
	
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => '',
				'default' => 'Google News',
			),		
			array(
				'label' => 'Keyword or phrase',
				'type' => 'text',
				'id' => 'keyword',
				'description' => '',
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
				'label' => 'Show date (if available)',
				'type' => 'checkbox',
				'id' => 'show_date',
				'description' => '',
				'default' => '',
			),
			array(
				'label' => 'Open links in a new tab?',
				'type' => 'checkbox',
				'id' => 'target',
				'description' => '',
				'default' => 'yes',
			),
			array(
				'label' => 'Cache duration (minutes)',
				'type' => 'number',
				'id' => 'cache_duration',
				'description' => '',
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
		<div class="proper-widget proper-gnews-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
		
		echo '
		<ul class="proper-gnews-links proper-links-list">';
		
		// Build argument array for feed fetching function
		$feed_args = array(
			'get_date' => false,
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
		 
		// Build the keyword URL and pass the whole thing to the feed fetcher
		$gn_query = trim($keyword);
		$gn_query = str_replace(' ', '+', $gn_query);
		$feed_args['url'] = 'http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&q='.$gn_query.'&cf=all&output=rss';
		
		$feed_content = proper_fetch_rss($feed_args);
	
		if (is_array($feed_content) && count($feed_content) > 0) {
			
			$target = isset($target) ? ' target="_blank"' : '';
	
			foreach($feed_content as $item) :
				
				// Now we output the individual link
				echo '
				<li>
					<a href="' . $item['link'] . '" title="'. $item['title'] . '" rel="nofollow"' . $target . '>'. $item['title'] . '</a>';
					
				if ($feed_args['get_date']) 
					echo '
					<p>' . $item['date'] . '</p>';
				
				echo '
				</li>';
		
			endforeach;
			
		}
		
		echo '
			</ul>
		</div>
		'. $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));
	
		$instance['show_date'] = isset($new_instance['show_date']) && $new_instance['show_date'] === 'yes' ? 'yes' : ''; 
		$instance['target'] = isset($new_instance['target']) && $new_instance['target'] === 'yes' ? 'yes' : ''; 
		
		$instance['keyword'] = sanitize_text_field($new_instance['keyword']);
	
		$num_posts = filter_var($new_instance['num_posts'], FILTER_SANITIZE_NUMBER_INT);
		if (empty($num_posts) || (int)$num_posts < 1 || (int)$num_posts > 20) 
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
	
		proper_output_widget_fields($this->widget_fields, $instance);

	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("proper_gnews_widget");') );