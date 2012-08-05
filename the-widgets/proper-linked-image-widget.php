<?php



class proper_linked_image_widget extends WP_Widget {

	function proper_linked_image_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-linked-image-widget', 'Proper Linked Image', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Image URL *',
				'type' => 'url',
				'id' => 'image',
				'description' => 'A direct link to an image',
				'default' => '',
			),
			array(
				'label' => 'Link to *',
				'type' => 'url',
				'id' => 'link',
				'description' => 'A direct link to where the clicked image will go',
				'default' => '',
			),
			array(
				'label' => 'Open link in new tab?',
				'type' => 'checkbox',
				'id' => 'target',
				'description' => 'Should the link open in a new tab?',
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
		<div class="proper-widget proper-image-widget">';
			
		// Set the link target
		$target = isset($target) && $target === 'yes' ? ' target="_blank"' : '';
		
		// Output the rest of the widget content
		echo '
				<a href="' . $link . '"' . $target . '><img alt="" src="' . $image . '" class="aligncenter" style="max-width: 100%"></a>
			</div>
		' . $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['image'] = filter_var($new_instance['image'], FILTER_SANITIZE_URL);
		$instance['link'] = filter_var($new_instance['link'], FILTER_SANITIZE_URL);
		
		$instance['target'] = isset($new_instance['target']) && $new_instance['target'] === 'yes' ? 'yes' : ''; 

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_linked_image_widget");') );