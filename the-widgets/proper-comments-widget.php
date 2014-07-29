<?php 

class proper_comments_widget extends WP_Widget {
	
	function proper_comments_widget() {

		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-comments-widget', 'PROPER Comments', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => ''
			)
		);
	}
	 
	function widget($args, $instance) {
		
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-comments-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
			
		echo '
			<ul class="proper-comments">';

		echo '
			</ul>
		</div>
		'. $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

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

	function get_roles () {

		global $wp_roles;

		$roles = array();

		foreach ( $wp_roles->roles as $id => $data ) {
			$roles[$id] = $data['name'];
		}

		return $roles;

	}

}

add_action( 'widgets_init', create_function('', 'return register_widget("proper_comments_widget");') );
