<?php 

class ProperPostsWidget extends WP_Widget {

	private $css_class = 'proper-posts-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {
		
		parent::__construct(
			$this->css_class,
			__( 'PROPER Posts', 'proper-widgets' ),
			array(
				'classname' => $this->css_class,
			)
		);
		
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
				'label' => __( 'Title', 'proper-widgets' ),
				'type' => 'text',
				'id' => 'title',
				'description' => __( 'Title for this widget or leave blank for none', 'proper-widgets' ),
				'default' => 'Posts'
			),		
			array(
				'label' => __( 'Category', 'proper-widgets' ),
				'type' => 'select_assoc',
				'id' => 'category',
				'options' => $post_cats,
				'description' => __( 'Select the category of posts to display', 'proper-widgets' ),
				'default' => ''
			),
			array(
				'label' => __( 'Show publish date', 'proper-widgets' ),
				'type' => 'checkbox',
				'id' => 'show_date',
				'default' => 0
			),
			array(
				'label' => __( 'Show thumbnail', 'proper-widgets' ),
				'type' => 'select_assoc',
				'id' => 'show_thumb',
				'options' => array(
					'' => 'No',
					'left' => __( 'Yes, float left', 'proper-widgets' ),
					'right' => __( 'Yes, float right', 'proper-widgets' ),
					'center' => __( 'Yes, centered', 'proper-widgets' ),
				),
				'default' => ''
			),
			array(
				'label' => __( '# of posts to show', 'proper-widgets' ),
				'type' => 'number',
				'id' => 'num_posts',
				'description' => '',
				'default' => get_option('posts_per_page')
			),
            array(
                'label' => __( 'Excerpt length', 'proper-widgets' ),
                'type' => 'number',
                'id' => 'excerpt_len',
                'description' => __( 'Length of the excerpt to show. Leave this as 0 to not display an excerpt.', 'proper-widgets' ),
                'default' => 0
            ),
			array(
				'label' => __( 'Offset', 'proper-widgets' ),
				'type' => 'number',
				'id' => 'offset',
				'description' => __( 'The number of posts to offset on this list', 'proper-widgets' ),
				'default' => 0
			),
			array(
				'label'       => __( 'Display posts that are', 'proper-widgets' ) . ' ...',
				'type'        => 'select_assoc',
				'id'          => 'orderby',
				'description' => '',
				'options' => array(
					'date' => __( 'Most recently published', 'proper-widgets' ),
					'modified' => __( 'Most recently changed', 'proper-widgets' ),
					'comment_count' => __( 'Most commented', 'proper-widgets' ),
					'rand' => __( 'Random', 'proper-widgets' ),
				),
				'default'     => 'date'
			),
			array(
				'label'       => __( 'Order displayed posts by ...', 'proper-widgets' ),
				'type'        => 'select_assoc',
				'id'          => 'orderby_final',
				'description' => '',
				'options' => array(
					'date' => __( 'Publish date', 'proper-widgets' ),
					'modified' => __( 'Last change date', 'proper-widgets' ),
					'rand' => __( 'Random order', 'proper-widgets' ),
					'title' => __( 'Alphabetical by title', 'proper-widgets' ),
					'comment_count' => __( 'Comment count', 'proper-widgets' ),
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
					usort( $the_posts, array( $this, 'usort_published' ) );
					break;

				case 'modified':
					usort( $the_posts, array( $this, 'usort_modified' ) );
					break;

				case 'title':
					usort( $the_posts, array( $this, 'usort_title' ) );
					break;

				case 'comment_count':
					usort( $the_posts, array( $this, 'usort_comments' ) );
					break;

				case 'rand':
					shuffle( $the_posts );
					break;
			}
		}

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );
			
		echo '<ul class="proper-posts-links proper-links-list links-category-'. $instance['category'].'">';

		foreach ($the_posts as $a_post) {

			$pid = $a_post->ID;
			$permalink = esc_url( get_permalink( $pid ) );
			$title = apply_filters( 'the_title', $a_post->post_title );

			echo '<li>';

			if ( $instance['show_thumb'] && has_post_thumbnail( $pid ) ) {
				$align = 'align' . $instance['show_thumb'];
				$thumb_size = $instance['show_thumb'] === 'center' ? 'medium' : 'thumbnail';
				echo sprintf(
					'<a href="%s" title="%s">',
					$permalink,
					esc_attr( $title )
				);
				echo get_the_post_thumbnail( $pid, $thumb_size, array(
					'class' => $align
				) );
				echo '</a>';
			}

			echo sprintf(
				'<p><a class="proper-headline-link" href="%s" title="%s">%s</a>',
				$permalink,
				esc_attr( $title ),
				$title
			);

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
				$the_excerpt = proper_widget_truncate( $the_excerpt, $instance['excerpt_len'] );
				$the_excerpt = apply_filters( 'the_excerpt', $the_excerpt );
				echo $the_excerpt;
			}

			echo '</li>';

		}

		echo '</ul>';
		proper_widget_wrap_bottom_html( $args );
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

	/**
	 * Sorting functions used by usort
	 */
	private function usort_published ( $a, $b ) {
		return $a->post_date < $b->post_date;
	}

	private function usort_modified ( $a, $b ) {
		return $a->post_modified < $b->post_modified;
	}

	private function usort_title ( $a, $b ) {
		return strcmp( $a->post_title, $b->post_title );
	}

	private function usort_comments ( $a, $b ) {
		return $a->comment_count > $b->comment_count;
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("ProperPostsWidget");') );