<?php

class proper_article_widget extends WP_Widget {
	
	function proper_article_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-article-widget', 'PROPER Article', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title *',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => '',
			),		
			array(
				'label' => 'Subtitle',
				'type' => 'text',
				'id' => 'subtitle',
				'description' => 'Add an italic subtitle, if you\'d like',
				'default' => '',
			),
			array(
				'label' => 'Body text',
				'type' => 'textarea',
				'id' => 'body',
				'description' => 'Main body of text. HTML allowed: br, strong, em, a, em, b, br',
				'default' => '',
			),
			array(
				'label' => 'Read More link',
				'type' => 'url',
				'id' => 'link',
				'description' => 'Add a link here to display "Read More" after the blurb',
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
			echo $before_title . apply_filters( 'widget_title', $title ) . $after_title;
		
		echo isset($subtitle) && !empty($subtitle) ? '
		<p class="proper-subtitle"><em>' . $subtitle . '</em></p>' : '';
		
		echo isset($body) && !empty($body) ? wpautop($body) : '';
		
		echo isset($link) && !empty($link) ? '
		<a href="' . $link . '" class="read-more">Read More &raquo;</a>' : '';
		
		echo '
		</div>
		' . $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['subtitle'] = sanitize_text_field($new_instance['subtitle']);
		$instance['body'] = strip_tags($new_instance['body'], '<br><strong><em><a><b><em><br>');
		$instance['link'] = filter_var($new_instance['link'], FILTER_VALIDATE_URL);

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_article_widget");') );