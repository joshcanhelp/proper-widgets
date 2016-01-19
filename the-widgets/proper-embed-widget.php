<?php 

class ProperEmbedWidget extends WP_Widget {

	/*
	 * Constructor called on initialize
	 */
	private $css_class = 'proper-embed-widget';
	
	function __construct() {

		parent::__construct(
			$this->css_class,
			__( 'PROPER Embed', 'proper-widgets' ),
			array(
				'classname' => $this->css_class,
			)
		);

		// Widget options
		$this->widget_fields = array(
			array(
				'label' => __( 'Title', 'proper-widgets' ),
				'type' => 'text',
				'id' => 'title',
				'description' => __( 'Title for this widget or leave blank for none', 'proper-widgets' ),
				'default' => ''
			),
            array(
                'label' => __( 'Embed URL', 'proper-widgets' ),
                'type' => 'url',
                'id' => 'embed_url',
                'description' => __( 'Enter a valid URL to an ', 'proper-widgets' ) .
                	'<a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">' . __( 'embeddable source', 'proper-widgets' ) .'</a>',
                'default' => ''
            ),
            array(
                'label' => __( 'Width', 'proper-widgets' ),
                'type' => 'number',
                'id' => 'embed_w',
                'description' => __( 'Enter a width, in pixels, for this media', 'proper-widgets' ),
                'default' => 300
            ),
            array(
                'label' => __( 'Height', 'proper-widgets' ),
                'type' => 'number',
                'id' => 'embed_h',
                'description' => __( 'Enter a height, in pixels, for this media', 'proper-widgets' ),
                'default' => 200
            ),
		);
	
	}

	/*
	 * Front-end widget output
	 */
	function widget($args, $instance) {

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );

		echo wp_oembed_get( $instance['embed_url'], array(
			'width' => intval( $instance['embed_w'] ),
			'height' => intval( $instance['embed_h'] )
		) );

		proper_widget_wrap_bottom_html( $args );
			
	}

	/*
	 * Sanitize and validate options
	 */
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['embed_url'] = esc_url( $new_instance['embed_url'] );
        $instance['embed_w'] = intval($new_instance['embed_w']);
        $instance['embed_h'] = intval($new_instance['embed_h']);

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

add_action( 'widgets_init', create_function('', 'return register_widget("ProperEmbedWidget");') );
