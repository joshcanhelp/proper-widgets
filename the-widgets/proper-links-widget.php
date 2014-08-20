<?php

class ProperLinksWidget extends WP_Widget {

	private $css_class = 'proper-links-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, 'PROPER Links', $widget_ops );

		// Get link categories
		$categories = get_categories( array(
			'taxonomy'   => 'link_category',
			'hide_empty' => 0
		) );

		$link_cats = array(
			'all' => '- All -'
		);

		foreach ( $categories as $category ) {
			$link_cats[$category->term_id] = $category->name;
		}

		// Widget options
		$this->widget_fields = array(
			array(
				'label'       => 'Title',
				'type'        => 'text',
				'id'          => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default'     => 'Links',
			),
			array(
				'label'       => 'Link category',
				'type'        => 'select_assoc',
				'id'          => 'category',
				'options'     => $link_cats,
				'description' => 'Select the category of links to display',
				'default'     => '',
			),
			array(
				'label'       => 'Order links by',
				'type'        => 'select_assoc',
				'id'          => 'orderby',
				'options'     => array(
					'name'   => 'Alphabetical',
					'rating' => 'Rating',
					'rand'   => 'Random',
				),
				'description' => '',
			),
			array(
				'label'       => 'Image size',
				'type'        => 'number',
				'id'          => 'img_size',
				'description' => 'Enter the size of the link image in pixels. <br>
				Enter "0" to not show any images.<br>
				Enter "-1" to keep the image\'s natural size',
				'default'     => 100,
			),

		);

	}

	/*
	 * Front-end widget output
	 */
	function widget( $args, $instance ) {

		$category  = intval( $instance['category'] );
		$link_args = array(
			'orderby'  => sanitize_text_field( $instance['orderby'] ),
			'category' => $category !== 'all' ? $category : ''
		);

		$links = get_bookmarks( $link_args );

		if ( empty( $links ) ) {
			echo '<!-- PROPER Links widget found no links -->';
			return;
		}

		proper_widget_wrap_html( $args, 'top', $instance['title'] );
		echo '<ul class="proper-wp-links proper-links-list links-category-' . $category . '">';

		foreach ( $links as $link ) {

			$link_url    = esc_url( $link->link_url );
			$link_target = esc_attr( $link->link_target );
			$link_name   = sanitize_text_field( $link->link_name );
			$link_rel    = esc_attr( $link->link_rel );
			$img_size    = intval( $instance['img_size'] );

			echo '<li>';

			if ( ! empty( $link->link_image ) && ! empty( $img_size ) ) {
				echo sprintf(
					'<a href="%s" target="%s" rel="%s"><img src="%s" alt="%s" width="%s"></a>',
					$link_url, $link_target, $link_rel, esc_url( $link->link_image ), esc_attr( $link_name ), $img_size
				);
			}

			echo sprintf(
				'<a href="%s" target="%s" rel="%s">%s</a>',
				$link_url, $link_target, $link_rel, $link_name
			);

			if ( ! empty( $link->link_description ) ) {
				echo wpautop( $link->link_description );
			}

			echo '</li>';
		}

		echo '</ul>';
		proper_widget_wrap_html( $args, 'bottom' );

	}

	/*
	 * Sanitize and validate options
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['category'] = intval( $new_instance['category'] );
		$instance['orderby']  = sanitize_text_field( $new_instance['orderby'] );
		$instance['img_size'] = intval( $new_instance['img_size'] );

		return $instance;

	}

	/*
	 * Output the widget form in wp-admin
	 */
	function form( $instance ) {

		for ( $i = 0; $i < count( $this->widget_fields ); $i ++ ) :
			$field_id                              = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id']   = $this->get_field_id( $field_id );
			$this->widget_fields[$i]['field_name'] = $this->get_field_name( $field_id );
		endfor;
		proper_widget_output_fields( $this->widget_fields, $instance );

	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("ProperLinksWidget");' ) );