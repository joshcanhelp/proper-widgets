<?php

class ProperLinksWidget extends WP_Widget {

	private $css_class = 'proper-links-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		parent::__construct(
			$this->css_class,
			__( 'PROPER Links', 'proper-widgets' ),
			array(
				'classname' => $this->css_class,
			)
		);

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
				'label'       => __( 'Title', 'proper-widgets' ),
				'type'        => 'text',
				'id'          => 'title',
				'description' => __( 'Title for this widget or leave blank for none', 'proper-widgets' ),
				'default'     => '',
			),
			array(
				'label'       => __( 'Link category', 'proper-widgets' ),
				'type'        => 'select_assoc',
				'id'          => 'category',
				'options'     => $link_cats,
				'description' => __( 'Select the category of links to display', 'proper-widgets' ),
				'default' => 'all'
			),
			array(
				'label'       => __( 'Order links by', 'proper-widgets' ),
				'type'        => 'select_assoc',
				'id'          => 'orderby',
				'options'     => array(
					'name'   => __( 'Alphabetical', 'proper-widgets' ),
					'rating' => __( 'Rating', 'proper-widgets' ),
					'rand'   => __( 'Random', 'proper-widgets' ),
				),
				'default' => 'name'
			),
			array(
				'label'       => 'Image size',
				'type'        => 'number',
				'id'          => 'img_size',
				'description' => __( 'Enter the size of the link image in pixels', 'proper-widgets' ).
					'.<br>' . __( 'Enter "0" to not show any images', 'proper-widgets' ) .
					'.<br>' . __( 'Enter "-1" to keep the natural size of the image', 'proper-widgets' ),
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
			echo '<!-- ' . __( 'PROPER Links widget found no links', 'proper-widgets' ) . ' -->';
			return;
		}

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );
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
		proper_widget_wrap_bottom_html( $args );

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