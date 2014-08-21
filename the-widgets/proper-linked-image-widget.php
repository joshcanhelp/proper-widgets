<?php


class ProperLinkedImageWidget extends WP_Widget {

	private $css_class = 'proper-linked-image-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {
		
		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, __( 'PROPER Linked Image', 'proper-widgets' ), $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => __( 'Image URL', 'proper-widgets' ),
				'type' => 'url',
				'id' => 'image',
				'description' => __( 'A direct link to an image', 'proper-widgets' ),
				'default' => '',
			),
			array(
				'label' => __( 'Link to', 'proper-widgets' ),
				'type' => 'url',
				'id' => 'link',
				'description' => __( 'A direct link to where the clicked image will go', 'proper-widgets' ),
				'default' => '',
			),
			array(
				'label' => __( 'Open link in new tab?', 'proper-widgets' ),
				'type' => 'checkbox',
				'id' => 'target',
				'description' => __( 'Should the link open in a new tab?', 'proper-widgets' ),
				'default' => '',
			),
			
		);
	
	}

	/*
	 * Front-end widget output
	 */
	function widget($args, $instance) {

		if ( empty( $instance['image'] ) ) {
			return;
		}

		proper_widget_wrap_top_html( $args, '', $this->css_class );

		echo sprintf(
			'<a href="%s"%s><img src="%s"></a>',
			esc_url( $instance['link'] ),
			! empty( $instance['target'] ) ? ' target="_blank"' : '',
			esc_url( $instance['image'] )
		);

		proper_widget_wrap_bottom_html( $args );
			
	}

	/*
	 * Sanitize and validate options
	 */
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['image'] = esc_url( $new_instance['image'] );
		$instance['link'] = esc_url( $new_instance['link'] );
		$instance['target'] = intval( $new_instance['target'] );

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

add_action( 'widgets_init', create_function('', 'return register_widget("ProperLinkedImageWidget");') );