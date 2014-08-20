<?php

class ProperArticleWidget extends WP_Widget {

	private $allowed_tags = '<br><strong><em><a><b><em><hr><p><h1><h2><h3><h4><h5><h6>';
	private $css_class = 'proper-article-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, 'PROPER Article', $widget_ops );

		// Widget options
		$this->widget_fields = array(
			array(
				'label'       => 'Title *',
				'type'        => 'text',
				'id'          => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default'     => '',
			),
			array(
				'label'       => 'Subtitle',
				'type'        => 'text',
				'id'          => 'subtitle',
				'description' => 'Add an italic subtitle, if you\'d like',
				'default'     => '',
			),
			array(
				'label'       => 'Body text',
				'type'        => 'textarea',
				'id'          => 'body',
				'description' => 'Main body of text. HTML allowed: br, strong, em, a, em, b, br',
				'default'     => '',
			),
			array(
				'label'       => 'Read More link text',
				'type'        => 'text',
				'id'          => 'link_text',
				'description' => 'Text to display for the link added below',
				'default'     => 'Read More &raquo;',
			),
			array(
				'label'       => 'Read More link URL',
				'type'        => 'url',
				'id'          => 'link',
				'description' => 'Add a link here to display after the blurb',
				'default'     => '',
			),

		);

	}

	/*
	 * Front-end widget output
	 */
	function widget( $args, $instance ) {

		$subtitle = sanitize_text_field( $instance['subtitle'] );

		$body = strip_tags( $instance['body'], $this->allowed_tags );
		$body = wpautop( $body );

		$link_text = sanitize_text_field( $instance['link_text'] );
		$link      = esc_url( $instance['link'] );

		proper_widget_wrap_html( $args, 'top', $instance['title'], $this->css_class );

		if ( ! empty( $subtitle ) ) {
			echo '<p class="proper-subtitle"><em>' . $subtitle . '</em></p>';
		}

		if ( ! empty( $body ) ) {
			echo $body;
		}

		if ( ! empty( $link ) ) {
			$link_text = ! empty( $link_text ) ? $link_text : __( 'Read More &raquo;', 'proper-widgets' );
			echo '<a href="' . $link . '" class="read-more">' . $link_text . '</a>';
		}

		proper_widget_wrap_html( $args, 'bottom' );

	}

	/*
	 * Sanitize and validate options
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['subtitle']  = sanitize_text_field( $new_instance['subtitle'] );
		$instance['body']      = strip_tags( $new_instance['body'], $this->allowed_tags );
		$instance['link_text'] = sanitize_text_field( $new_instance['link_text'] );
		$instance['link']      = filter_var( $new_instance['link'], FILTER_VALIDATE_URL );

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

add_action( 'widgets_init', create_function( '', 'return register_widget("ProperArticleWidget");' ) );