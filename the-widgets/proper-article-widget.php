<?php

class proper_article_widget extends WP_Widget {
	
	function proper_article_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-article-widget', 'Proper Article', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title *',
				'type' => 'text',
				'id' => 'title',
				'description' => '',
				'default' => '',
			),		
			array(
				'label' => 'Subtitle',
				'type' => 'text',
				'id' => 'subtitle',
				'description' => '',
				'default' => '',
			),
			array(
				'label' => 'Body text',
				'type' => 'textarea',
				'id' => 'body',
				'description' => 'Main body of text. HTML allowed: br, strong, em',
				'default' => '',
			),
			array(
				'label' => 'Link to *',
				'type' => 'url',
				'id' => 'link',
				'description' => '',
				'default' => '',
			),
			
		);
	
	}
	 
	function widget($args, $instance) {
	
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-article-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
		
		echo isset($subtitle) && !empty($subtitle) ? '
		<p class="proper-subtitle"><em>' . $subtitle . '</em></p>' : '';
		
		echo isset($body) && !empty($body) ? wpautop($body) : '';
		
		echo '
				<a href="' . $link . '" class="read-more">More &raquo;</a>
		</div>
		' . $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));
		
		$instance['subtitle'] = sanitize_text_field($new_instance['subtitle']);
		$instance['body'] = strip_tags($new_instance['body'], '<br><strong><em><a>');
		
		$instance['link'] = filter_var($new_instance['link'], FILTER_VALIDATE_URL);

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_article_widget");') );