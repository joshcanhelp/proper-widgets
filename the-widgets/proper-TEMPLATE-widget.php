<?php

class ProperTEMPLATEWidget extends WP_Widget {

	private $css_class = 'proper-TEMPLATE-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, __( 'PROPER TEMPLATE', 'proper-widgets' ) , $widget_ops );

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
				'label'       => __( 'Body text', 'proper-widgets' ),
				'type'        => 'textarea',
				'id'          => 'body_text',
				'default'     => '',
			),
			array(
				'label'   => __( 'A number', 'proper-widgets' ),
				'type'    => 'number',
				'id'      => 'number',
				'default' => 5
			),
			array(
				'label'       => __( 'Some select options', 'proper-widgets' ),
				'type'        => 'select_assoc',
				'id'          => 'select_option',
				'default'     => '1',
				'options'     => array(
					'1'   => __( 'One', 'proper-widgets' ),
					'2'  => __( 'Two', 'proper-widgets' ),
					'3' => __( 'Three', 'proper-widgets' ),
					'4'       => __( 'Four', 'proper-widgets' ),
				)
			),
			array(
				'label'   => __( 'Yes or no?', 'proper-widgets' ),
				'type'    => 'checkbox',
				'id'      => 'yesno',
				'default' => 1
			),

		);

	}

	/*
	 * Front-end widget output
	 */
	function widget( $args, $instance ) {

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );

		proper_widget_wrap_bottom_html( $args );

	}

	/*
	 * Sanitize and validate options
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['body_text']  = sanitize_text_field( $new_instance['body_text'] );
		$instance['number']  = intval( $new_instance['number'] );
		$instance['select_option'] = sanitize_text_field( $new_instance['select_option'] );
		$instance['yesno'] = intval( $new_instance['yesno'] );

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

add_action( 'widgets_init', create_function( '', 'return register_widget("ProperTEMPLATEWidget");' ) );