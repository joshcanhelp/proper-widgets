<?php 

class proper_posts_widget extends WP_Widget {
	
	function proper_posts_widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-posts-widget', 'Proper Posts', $widget_ops);
		
		// Get link categories
		$categories = get_categories(array (
			'type' => 'post',
			'hide_empty' => 0
		)); 
		
		$post_cats = array(
		    'all' => '- All -'
		);
		foreach($categories as $category) $post_cats[$category->term_id] = $category->name;
		
		$defaults = array(
			'title' => '',
			'categories' => '',
			'offset' => '0',
		);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => 'Posts'
			),		
			array(
				'label' => 'Category',
				'type' => 'select',
				'id' => 'category',
				'options' => $post_cats,
				'description' => 'Select the category of posts to display',
				'default' => ''
			),
			array(
				'label' => '# of items to show',
				'type' => 'number',
				'id' => 'num_posts',
				'description' => '',
				'default' => get_option('posts_per_page')
			),
            array(
                'label' => 'Excerpt length',
                'type' => 'number',
                'id' => 'excerpt_len',
                'description' => 'Length of the excerpt to show. Leave this as 0 to not display an excerpt.',
                'default' => 0
            ),
			array(
				'label' => 'Offset',
				'type' => 'number',
				'id' => 'offset',
				'description' => 'The number of posts to offset on this list',
				'default' => 0
			),
			
		);
	
	}
	 
	function widget($args, $instance) {
		
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-posts-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
			
		echo '
			<ul class="proper-posts-links proper-links-list links-category-'.$category.'">';
			
			// Args for posts to be displayed, set in the widget form
		$list_args = array(
			'posts_per_page' => $num_posts,
			'offset' => $offset
		);
		
		// Category to display, if not all
		if ($category != 'all') 
			$list_args['cat'] = $category;
	
		$the_posts = get_posts($list_args);
		
		if (!empty($the_posts)) :
			
			foreach ($the_posts as $a_post) :

                $the_excerpt = '';
                if ($excerpt_len) {
                    $the_excerpt = !empty($a_post->post_excerpt) ?
                        $a_post->post_excerpt :
                        strip_tags($a_post->post_content);
                    $the_excerpt = substr($the_excerpt, 0, $excerpt_len) . ' ...';
                }
				
				echo '
			<li class="the-content">
				<p><a href="' . get_permalink($a_post->ID).'" title="' . $a_post->post_title . '">' .
				$a_post->post_title . '</a>';

            if ( !empty( $the_excerpt ) )
                echo '<br>' . $the_excerpt;

		    echo '</p>
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

		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));
		
		$instance['category'] = $new_instance['category'];

        $instance['excerpt_len'] = filter_var($new_instance['excerpt_len'], FILTER_SANITIZE_NUMBER_INT);
		$instance['num_posts'] = filter_var($new_instance['num_posts'], FILTER_SANITIZE_NUMBER_INT);
		$instance['offset'] = filter_var($new_instance['offset'], FILTER_SANITIZE_NUMBER_INT);

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

add_action( 'widgets_init', create_function('', 'return register_widget("proper_posts_widget");') );