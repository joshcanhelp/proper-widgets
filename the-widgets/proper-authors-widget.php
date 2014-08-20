<?php 

class ProperAuthorsWidget extends WP_Widget {

	private $css_class = 'proper-authors-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, 'PROPER Authors', $widget_ops);

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
				'label' => 'Show People',
				'type' => 'select',
				'id' => 'role',
				'options' => $this->get_roles(),
				'description' => 'Select the roles of users that should be shown'
			),
			array(
				'label' => 'Show name',
				'type' => 'select_assoc',
				'id' => 'show_name',
				'options' => array(
					'' => 'No',
					'plain' => 'Yes, plain text',
					'author' => 'Yes, linked to author page',
					'website' => 'Yes, linked to website',
					'email' => 'Yes, linked to email',
				),
				'default' => ''
			),
			array(
				'label' => 'Show avatar',
				'type' => 'select_assoc',
				'id' => 'show_img',
				'options' => array(
					''       => 'No',
					'plain'   => 'Yes, not linked',
					'author'  => 'Yes, linked to author page',
					'website' => 'Yes, linked to website',
					'email'   => 'Yes, linked to email',
				),
				'default' => ''
			),
			array(
				'label' => 'Show Email',
				'type' => 'checkbox',
				'id' => 'show_email',
				'default' => 0
			),
			array(
				'label' => 'Show Website',
				'type' => 'checkbox',
				'id' => 'show_url',
				'default' => 1
			),
			array(
				'label' => 'Show Bio',
				'type' => 'checkbox',
				'id' => 'show_bio',
				'default' => 1
			),
			
		);
	
	}

	/*
	 * Front-end widget output
	 */
	function widget($args, $instance) {

		// Output escaping
		$title = sanitize_text_field( $instance['title'] );
		$title = apply_filters( 'widget_title', $title );

		$the_users = get_users( array(
			'blog_id' => $GLOBALS['blog_id'],
			'role' => sanitize_text_field( $instance['role'] )
		) );

		// No users found for the options show. Nothing to do ...
		if ( empty( $the_users ) ) {
			echo '<!-- No users found! -->';
			return;
		}

		proper_widget_wrap_html( $args, 'top', $instance['title'], $this->css_class );
		echo '<ul>';

		foreach ( $the_users as $a_user ) :

			$email = sanitize_email( $a_user->data->user_email );
			$url   = esc_url( $a_user->data->user_url );
			$uid = $a_user->data->ID;

			echo '<li>';

			// Display avatar
			if ( ! empty( $instance['show_img'] ) ) {
				$avatar = $this->get_avatar_url( get_avatar( $email, 60 ) );

				if ( ! empty( $avatar ) ) {
					$avatar = '<img src="' . $avatar . '" width="60" class="alignleft">';
					echo $this->link_wrap( $avatar, $instance['show_img'], $uid, $url, $email );
				}
			}

			// Display name
			if ( ! empty( $instance['show_name'] ) ) {
				$name = sanitize_text_field( $a_user->data->display_name );
				$name = $this->link_wrap( $name, $instance['show_name'], $uid, $url, $email );
				echo '<p><strong>' . $name . '</strong></p>';
			}

			// Display bio
			if ( ! empty( $instance['show_bio'] ) ) {
				$bio = get_the_author_meta( 'description', $uid );
				echo wpautop( $bio );
			}

			// Display url
			if ( ! empty( $instance['show_url'] ) ) {
				$url = $this->link_wrap( $url, 'website', 0, $url, '' );
				echo $url . '<br>';
			}


			// Display email
			if ( ! empty( $instance['show_email'] ) ) {
				$email = $this->link_wrap( $email, 'email', 0, '', $email );
				echo $email . '<br>';
			}

			echo '</li>';

		endforeach;

		echo '</ul>';
		proper_widget_wrap_html( $args, 'bottom' );
	}

	/*
	 * Sanitize and validate options
	 */
	function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['role'] = sanitize_text_field( $new_instance['role'] );
		$instance['show_name'] = sanitize_text_field( $new_instance['show_name'] );
		$instance['show_img'] = sanitize_text_field( $new_instance['show_img'] );
		$instance['show_email'] = intval( $new_instance['show_email'] );
		$instance['show_url'] = intval( $new_instance['show_url'] );
		$instance['show_bio'] = intval( $new_instance['show_bio'] );

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

	/*
	 * Get all system roles and create a formatted array
	 */
	function get_roles () {

		global $wp_roles;

		$roles = array();

		foreach ( $wp_roles->roles as $id => $data ) {
			$roles[$id] = $data['name'];
		}

		$roles['all'] = 'All';

		return $roles;

	}

	/*
	 * Wrap name and avatar with a link, if this option is set
	 */
	function link_wrap( $inner, $type, $uid, $url, $email ) {

		switch ( $type ) {
			case 'author';
				$inner = '<a href="' . get_author_posts_url( $uid ) . '">' . $inner . '</a>';
				break;

			case 'website';
				if ( ! empty( $url ) ) {
					$inner = '<a href="' . $url . '">' . $inner . '</a>';
				}
				break;

			case 'email';
				$inner = '<a href="mailto:' . $email . '">' . $inner . '</a>';
				break;

		}

		return $inner;
	}

	/*
	 * Get just the avatar's URL
	 */
	function get_avatar_url( $get_avatar ) {
		preg_match( "/src='(.*?)'/i", $get_avatar, $matches );
		return $matches[1];
	}


}

add_action( 'widgets_init', create_function('', 'return register_widget("ProperAuthorsWidget");') );
