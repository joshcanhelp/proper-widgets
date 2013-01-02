<?php 

class proper_video_widget extends WP_Widget {
	
	function proper_video_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper_video_widget', 'Proper Video Widget', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => ''
			),
            array(
                'label' => 'Video URL',
                'type' => 'url',
                'id' => 'video_url',
                'description' => 'Enter a valid URL to a YouTube or Vimeo video',
                'default' => ''
            ),
            array(
                'label' => 'Video width',
                'type' => 'number',
                'id' => 'video_w',
                'description' => 'Enter a width, in pixels, for this video',
                'default' => 300
            ),
            array(
                'label' => 'Video height',
                'type' => 'number',
                'id' => 'video_h',
                'description' => 'Enter a height, in pixels, for this video',
                'default' => 200
            ),
		);
	
	}
	 
	function widget($args, $instance) {
		
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-video-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;

		echo proper_widget_output_embed($video_url, $video_w, $video_h) . '
		</div>
		'. $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));
		$instance['video_url'] = $new_instance['video_url'];
        $instance['video_w'] = intval($new_instance['video_w']);
        $instance['video_h'] = intval($new_instance['video_h']);

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_video_widget");') );
