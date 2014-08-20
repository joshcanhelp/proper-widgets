<?php

class ProperCommentsWidget extends WP_Widget {

	private $css_class = 'proper-comments-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, 'PROPER Comments', $widget_ops );

		// Widget options
		$this->widget_fields = array(
			array(
				'label'       => 'Title',
				'type'        => 'text',
				'id'          => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default'     => ''
			),
			array(
				'label'   => '# of comments to show',
				'type'    => 'number',
				'id'      => 'number',
				'default' => 5
			),
			array(
				'label'       => 'Offset',
				'type'        => 'number',
				'id'          => 'offset',
				'description' => 'Enter the number of comments to skip',
				'default'     => 0
			),
			array(
				'label'       => 'Show commenter Gravatar',
				'type'        => 'checkbox',
				'id'          => 'show_avatar',
				'default'     => ''
			),
			array(
				'label'       => 'Comment type',
				'type'        => 'select_assoc',
				'id'          => 'type',
				'description' => 'Select the comment type',
				'default'     => 'comment',
				'options'     => array(
					'comment'  => 'Comment',
					'pingback' => 'Pingback',
					'trackback' => 'Trackback',
					'all'     => 'All',
				)
			),
			array(
				'label'       => 'Comment header',
				'type'        => 'textarea',
				'id'          => 'header',
				'description' => 'Build the comment header using the following tags: [date], [date_time], [time_ago] [post_name], [post_name_link], [name], [name_link]',
				'default'     => 'Posted by [name_link] [date_time] on "[post_name_link]"',
			),
		);
	}

	/*
	 * Front-end widget output
	 */
	function widget( $args, $instance ) {

		$comment_args = array(
			'number'  => intval( $instance['number'] ),
			'offset'  => intval( $instance['offset'] ),
			'orderby' => 'comment_date',
			'order'   => 'DESC'
		);

		if ( $instance['type'] != 'all' ) {
			$comment_args['type'] = sanitize_text_field( $instance['type'] );
		}

		$comments = get_comments( $comment_args );

		if ( empty( $comments ) ) {
			return;
		}

		$show_avatar = FALSE;
		if ( ! empty( $instance['show_avatar'] ) ) {
			$show_avatar = TRUE;
		}

		proper_widget_wrap_html( $args, 'top', $instance['title'] );

		echo '<ul class="proper-comments">';
		foreach ( $comments as $comment ) {

			// Format comment header
			if ( ! empty( $instance['header'] ) ) {
				$header = sanitize_text_field( $instance['header'] );

				// Dates
				$comment_timestamp = strtotime( $comment->comment_date );

				$header = str_replace( '[date]', date_i18n(
					get_option( 'date_format' ),
					$comment_timestamp
				), $header );

				$header = str_replace( '[date_time]', date_i18n(
					get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
					$comment_timestamp
				), $header );

				$header = str_replace( '[time_ago]', human_time_diff( $comment_timestamp ), $header );

				// Post info
				if ( strpos( $header, '[post_name]' ) !== FALSE || strpos( $header, '[post_name_link]' ) !== FALSE ) {
					$on_post_id = $comment->comment_post_ID;
					$post_title = get_the_title( $on_post_id );

					$header = str_replace( '[post_name]', $post_title, $header );
					$header = str_replace( '[post_name_link]', sprintf(
						'<a href="%s" title="%s">%s</a>',
						get_permalink( $on_post_id ),
						esc_attr( $post_title ),
						$post_title
					), $header );
				}

				// Comment author
				$header = str_replace( '[name]', $comment->comment_author, $header );
				if ( ! empty( $comment->comment_author_url ) ) {
					$name_link = sprintf(
						'<a href="%s" title="%s">%s</a>',
						esc_url( $comment->comment_author_url ),
						esc_attr( $comment->comment_author ),
						$comment->comment_author
					);
				} elseif ( ! empty( $comment->user_id ) ) {
					$name_link = sprintf(
						'<a href="%s" title="%s">%s</a>',
						get_author_posts_url( $comment->user_id ),
						esc_attr( $comment->comment_author ),
						$comment->comment_author
					);
				} else {
					$name_link = $comment->comment_author;
				}

				$header = str_replace( '[name_link]', $name_link, $header );

			}

			echo '<li>';

			if ( $show_avatar ) {
				echo get_avatar( $comment->comment_author_email, 30 );
			}

			if ( ! empty( $header ) ) {
				echo '<p class="proper-comment-header">' . $header . '</p>';
			}

			echo apply_filters( 'comment_text', $comment->comment_content );
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

		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = intval( $new_instance['number'] );
		$instance['offset'] = intval( $new_instance['offset'] );
		$instance['show_avatar'] = intval( $new_instance['show_avatar'] );
		$instance['type']   = sanitize_text_field( $new_instance['type'] );
		$instance['header']   = sanitize_text_field( $new_instance['header'] );

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
		proper_widgets_output_fields( $this->widget_fields, $instance );

	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("ProperCommentsWidget");' ) );
