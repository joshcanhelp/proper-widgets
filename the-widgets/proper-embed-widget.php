<?php 

class ProperEmbedWidget extends WP_Widget {

	/*
	 * Constructor called on initialize
	 */
	private $css_class = 'proper-embed-widget';
	
	function __construct() {
		
		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, 'PROPER Embed Widget', $widget_ops);

		// Widget options
		$this->widget_fields = array(
			array(
				'label' => 'Title',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => ''
			),
            array(
                'label' => 'Embed URL',
                'type' => 'url',
                'id' => 'embed_url',
                'description' => 'Enter a valid URL to an <a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">embeddable source</a>',
                'default' => ''
            ),
            array(
                'label' => 'Width',
                'type' => 'number',
                'id' => 'embed_w',
                'description' => 'Enter a width, in pixels, for this video',
                'default' => 300
            ),
            array(
                'label' => 'Height',
                'type' => 'number',
                'id' => 'embed_h',
                'description' => 'Enter a height, in pixels, for this video',
                'default' => 200
            ),
		);
	
	}

	/*
	 * Front-end widget output
	 */
	function widget($args, $instance) {

		proper_widget_wrap_html( $args, 'top', $instance['title'] );

		echo wp_oembed_get( $instance['embed_url'], array(
			'width' => intval( $instance['embed_w'] ),
			'height' => intval( $instance['embed_h'] )
		) );

		proper_widget_wrap_html( $args, 'bottom' );
			
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
