<?php 

class proper_authors_widget extends WP_Widget {
	
	function proper_authors_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-authors-widget', 'Proper Authors', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank to omit',
				'default' => ''
			),		
			array(
				'label' => 'Show People',
				'type' => 'checkbox',
				'id' => 'roles',
				'options' => array(
					'administrator' => 'Administrators',
					'editor' => 'Editors',
					'author' => 'Authors',
					'contributor' => 'Contributors',
					'subscriber' => 'Subscribers',
				),
				'description' => 'Select the roles of users that should be shown'
			),
			array(
				'label' => 'Show Fields',
				'type' => 'checkbox',
				'id' => 'fields',
				'options' => array(
					'display_name' => 'Name',
					'avatar' => 'Picture',
					'user_email' => 'Email',
					'user_url' => 'Website',
					'user_bio' => 'Bio',
				),
				'description' => 'Select the user fields that should be shown'
			),
			
		);
	
	}
	 
	function widget($args, $instance) {
		
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-author-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
			
		echo '
			<ul class="proper-author-links">';
	
		$the_users = get_posts();
		
		if (!empty($the_users)) :
			
			foreach ($the_users as $a_user) :
				
				echo '
			<li>
			</li>';
				
			endforeach;
	
		endif;
		
		echo '
			</ul>
		</div>
		'. $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		// Storing widget title as inputted option or category name
		
		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_authors_widget");') );
