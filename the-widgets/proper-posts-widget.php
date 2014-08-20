<?php 

class ProperPostsWidget extends WP_Widget {

	private $css_class = 'proper-posts-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {
		
		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( 'proper-posts-widget', 'PROPER Posts', $widget_ops);
		
		// Get post categories
		$categories = get_categories(array (
			'type' => 'post',
			'hide_empty' => 0
		)); 
		
		$post_cats = array(
		    'all' => '- All -'
		);

		foreach($categories as $category) {
			$post_cats[$category->term_id] = $category->name;
		}

		// Widget options
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
				'type' => 'select_assoc',
				'id' => 'category',
				'options' => $post_cats,
				'description' => 'Select the category of posts to display',
				'default' => ''
			),
			array(
				'label' => 'Show publish date',
				'type' => 'checkbox',
				'id' => 'show_date',
				'default' => 0
			),
			array(
				'label' => 'Show thumbnail',
				'type' => 'select_assoc',
				'id' => 'show_thumb',
				'options' => array(
					'' => 'No',
					'left' => 'Yes, float left',
					'right' => 'Yes, float right',
					'center' => 'Yes, centered',
				),
				'default' => ''
			),
			array(
				'label' => '# of posts to show',
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
			array(
				'label'       => 'Display posts that are ...',
				'type'        => 'select_assoc',
				'id'          => 'orderby',
				'description' => '',
				'options' => array(
					'date' => 'Most recently published',
					'modified' => 'Most recently changed',
					'comment_count' => 'Most commented',
					'rand' => 'Random',
				),
				'default'     => 'date'
			),
			array(
				'label'       => 'Order displayed posts by ...',
				'type'        => 'select_assoc',
				'id'          => 'orderby_final',
				'description' => '',
				'options' => array(
					'date' => 'Publish date',
					'modified' => 'Last change date',
					'rand' => 'Random order',
					'title' => 'Alphabetical by title',
					'comment_count' => 'Comment count',
				),
				'default'     => 'date'
			),
			
		);
	}

	/*
	 * Front-end widget output
	 */
	function widget($args, $instance) {

		$orderby = sanitize_text_field( $instance['orderby'] );

		// Args for posts to be displayed, set in the widget form
		$list_args = array(
			'posts_per_page' => intval( $instance['num_posts'] ),
			'offset'         => intval( $instance['offset'] ),
			'orderby'         => $orderby
		);

		// Category to display, if not all
		if ( $instance['category'] != 'all' ) {
			$list_args['cat'] = intval( $instance['category'] );
		}

		$the_posts = get_posts( $list_args );

		if ( empty( $the_posts ) ) {
			return;
		}

		// Order retrieved posts by widget option
		if ( $orderby !== $instance['orderby_final'] ) {
			switch ( $instance['orderby_final'] ) {
				case 'date':
					usort( $the_posts, function ($a, $b) {
						return $a->post_date < $b->post_date;
					});
					break;
				case 'modified':
					usort( $the_posts, function ( $a, $b ) {
						return $a->post_modified < $b->post_modified;
					} );
					break;
				case 'rand':
					shuffle( $the_posts );
					break;
				case 'title':
					usort( $the_posts, function  ($a, $b) {
						return strcmp( $a->post_title, $b->post_title );
					});
					break;
				case 'comment_count':
					usort( $the_posts, function ( $a, $b ) {
						return $a->comment_count > $b->comment_count;
					} );
					break;
			}
		}
		
		// HTML output
		echo $args['before_widget'] . '<div class="proper-widget">';

		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
			
		echo '<ul class="proper-posts-links proper-links-list links-category-'. $instance['category'].'">';

		foreach ($the_posts as $a_post) {

			$pid = $a_post->ID;

			echo '<li>';

			if ( $instance['show_thumb'] && has_post_thumbnail( $pid ) ) {
				$align = 'align' . $instance['show_thumb'];
				$thumb_size = $instance['show_thumb'] === 'center' ? 'medium' : 'thumbnail';
				echo get_the_post_thumbnail( $pid, $thumb_size, array(
					'class' => $align,
					'style' => 'max-width: 100%'
				) );
			}

			$permalink = get_permalink( $pid );
			$title = apply_filters( 'the_title', $a_post->post_title );

			echo '<p><a class="proper-headline-link" href="';
			echo esc_url( $permalink );
			echo '" title="';
			echo esc_attr( $title );
			echo '">' . $title . '</a>';

			if ( $instance['show_date'] ) {
				echo '<br><span class="proper-date">' . date_i18n(
					get_option('date_format'),
					strtotime( $a_post->post_date )
				) . '</span>';
			}


			echo '</p>';

			if ( $instance['excerpt_len'] ) {
				$the_excerpt = $a_post->post_excerpt;
				if ( empty( $the_excerpt ) ) {
					$the_excerpt = strip_tags( $a_post->post_content );
				}
				$the_excerpt = substr( $the_excerpt, 0, $instance['excerpt_len'] );
				$the_excerpt = apply_filters( 'the_excerpt', $the_excerpt );
				echo $the_excerpt;
			}

			echo '</li>';

		}

		echo '</ul></div>' . $args['after_widget'];
	}

	/*
	 * Sanitize and validate options
	 */
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['category'] = intval( $new_instance['category'] );
		$instance['show_date'] = intval( $new_instance['show_date'] );
		$instance['show_thumb'] = sanitize_text_field( $new_instance['show_thumb'] );
        $instance['excerpt_len'] = intval( $new_instance['excerpt_len'] );
		$instance['num_posts'] = intval( $new_instance['num_posts'] );
		$instance['offset'] = intval( $new_instance['offset'] );
		$instance['orderby'] = sanitize_text_field( $new_instance['orderby'] );
		$instance['orderby_final'] = sanitize_text_field( $new_instance['orderby_final'] );

		return $instance;

	}

	/*
	 * Output the widget form in wp-admin
	 */
	function form($instance) {
		
		for ($i = 0; $i < count($this->widget_fields); $i++) :
			$field_id = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id'] = $this->get_field_id($field_id);
			$this->widget_fields[$i]['field_name'] = $this->get_field_name($field_id);
		endfor;
		proper_widget_output_fields($this->widget_fields, $instance);

	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("ProperPostsWidget");') );