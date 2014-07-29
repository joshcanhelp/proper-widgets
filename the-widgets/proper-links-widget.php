<?php

class proper_links_widget extends WP_Widget {
	
	function proper_links_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-links-widget', 'PROPER Links', $widget_ops);
		
		// Get link categories
		$categories = get_categories(array (
			'taxonomy' => 'link_category',
			'hide_empty' => 0
		)); 
		
		$link_cats = array(
			'all' => 'All'
		);
		foreach($categories as $category) $link_cats[$category->term_id] = $category->name;
		
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => 'Links',
			),		
			array(
				'label' => 'Link category',
				'type' => 'select',
				'id' => 'category',
				'options' => $link_cats,
				'description' => 'Select the category of links to display',
				'default' => '',
			),
			array(
				'label' => 'Order links by',
				'type' => 'select',
				'id' => 'orderby',
				'options' => array(
					'name' => 'Alphabetical',
					'rating' => 'Rating',
					'rand' => 'Random',
				),
				'description' => '',
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
		<ul class="proper-wp-links proper-links-list links-category-' . $category . '">';
	
		$link_args['orderby'] = $orderby;
		
		if ($category !== 'all') 
			$link_args['category'] = $category;
			
		$links = get_bookmarks($link_args);
		
		foreach ($links as $link) :
			echo '
			<li>
				<p>';
			if (!empty($link->link_image)) 
				echo '<a href="' . $link->link_url . '" target="' . $link->link_target . '" rel="' . $link->link_rel . '"><img src="' . $link->link_image . '" alt="' . $link->link_name . '" style="max-width: 100%"></a>';
			
			echo'
				<a href="' . $link->link_url . '" target="' . $link->link_target . '" rel="' . $link->link_rel . '">' . $link->link_name . '</a></p>';
			
			if (!empty($link->link_description)) 
				echo wpautop($link->link_description);
			
			echo '
			</li>';	
		endforeach;
		
		echo '
			</ul>
		</div>
		'. $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));

		$instance['category'] = $new_instance['category'];
		$instance['orderby'] = $new_instance['orderby'];

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_links_widget");') );