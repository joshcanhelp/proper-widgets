<?php

class ProperArticleWidget extends WP_Widget {

	private $allowed_tags = '<br><strong><b><em><i><a><hr><p><h1><h2><h3><h4><h5><h6>';
	private $css_class = 'proper-article-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		parent::__construct(
			$this->css_class,
			__( 'PROPER Article', 'proper-widgets' ),
			array(
				'classname' => $this->css_class,
			)
		);

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
				'label'       => __( 'Subitle', 'proper-widgets' ),
				'type'        => 'text',
				'id'          => 'subtitle',
				'description' => __( 'Add an italic subtitle or leave blank for none', 'proper-widgets' ),
				'default'     => '',
			),
			array(
				'label'       => __( 'Body text', 'proper-widgets' ),
				'type'        => 'textarea',
				'id'          => 'body',
				'description' => __( 'Main body of text. HTML allowed: ', 'proper-widgets' )  .
					htmlentities( $this->allowed_tags ),
				'default'     => '',
			),
			array(
				'label'       => __( 'Link text to read more', 'proper-widgets' ),
				'type'        => 'text',
				'id'          => 'link_text',
				'description' => __( 'Text to display for the link added below', 'proper-widgets' ) ,
				'default'     => 'Read More &raquo;',
			),
			array(
				'label'       => __( 'URL to read more', 'proper-widgets' ),
				'type'        => 'url',
				'id'          => 'link',
				'description' => __( 'Add a link here to display after the blurb', 'proper-widgets' ) ,
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

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );

		if ( ! empty( $subtitle ) ) {
			echo '<p class="proper-subtitle"><em>' . $subtitle . '</em></p>';
		}

		if ( ! empty( $body ) ) {
			echo $body;
		}

		if ( ! empty( $link ) ) {
			$link_text = ! empty( $link_text ) ? $link_text : '#';
			echo '<a href="' . $link . '" class="read-more">' . $link_text . '</a>';
		}

		proper_widget_wrap_bottom_html( $args );

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