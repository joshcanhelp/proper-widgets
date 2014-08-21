<?php

class ProperCommentsWidget extends WP_Widget {

	private $css_class = 'proper-comments-widget';

	/*
	 * Constructor called on initialize
	 */
	function __construct() {

		$widget_ops = array( 'classname' => $this->css_class );
		$this->WP_Widget( $this->css_class, __( 'PROPER Comments', 'proper-widgets' ), $widget_ops );

		// Widget options
		$this->widget_fields = array(
			array(
				'label'       => __( 'Title', 'proper-widgets' ),
				'type'        => 'text',
				'id'          => 'title',
				'description' => __( 'Title for this widget or leave blank for none', 'proper-widgets' ),
				'default'     => ''
			),
			array(
				'label'   => __( '# of comments to show', 'proper-widgets' ) ,
				'type'    => 'number',
				'id'      => 'number',
				'default' => 5
			),
			array(
				'label'   => __( 'Comment character length', 'proper-widgets' ) ,
				'type'    => 'number',
				'id'      => 'content_length',
				'description' => __( 'Length of the comment body', 'proper-widgets' ),
				'default' => 100
			),
			array(
				'label'       => __( 'Offset', 'proper-widgets' ) ,
				'type'        => 'number',
				'id'          => 'offset',
				'description' => __( 'Enter the number of comments to skip', 'proper-widgets' ) ,
				'default'     => 0
			),
			array(
				'label'       => __( 'Show commenter Gravatar', 'proper-widgets' ),
				'type'        => 'checkbox',
				'id'          => 'show_avatar',
				'default'     => ''
			),
			array(
				'label'       => __( 'Comment type', 'proper-widgets' ),
				'type'        => 'select_assoc',
				'id'          => 'type',
				'description' => __( 'Select the comment type', 'proper-widgets' ),
				'default'     => 'comment',
				'options'     => array(
					'comment'  => __( 'Comment', 'proper-widgets' ),
					'pingback' => __( 'Pingback', 'proper-widgets' ),
					'trackback' => __( 'Trackback', 'proper-widgets' ),
					'all'     => __( 'All', 'proper-widgets' ),
				)
			),
			array(
				'label'       => __( 'Comment header', 'proper-widgets' ),
				'type'        => 'textarea',
				'id'          => 'header',
				'description' => __( 'Build the comment header using the following tags: ', 'proper-widgets' ) .
					'[date], [date_time], [time_ago] [post_name], [post_name_link], [name], [name_link]',
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

		$body_add = '';

		proper_widget_wrap_top_html( $args, $instance['title'], $this->css_class );

		echo '<ul class="proper-comments">';
		foreach ( $comments as $comment ) {

			$comment_link = '<a href="' . get_comment_link( $comment->comment_ID ) . '">#</a>';

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
				$header = $comment_link . ' ' . $header;

			}
			else {
				$body_add = $comment_link;
			}

			echo '<li>';

			if ( $show_avatar ) {
				$avatar_url = proper_widget_get_avatar_url( get_avatar( $comment->comment_author_email, 30 ) );

				if ( ! empty( $avatar_url ) ) {
					echo sprintf(
						'<img src="%s" width="%d" class="alignleft">',
						esc_url( $avatar_url ),
						30
					);
				}
			}

			if ( ! empty( $header ) ) {
				echo '<p class="proper-comment-header">' . $header . '</p>';
			}

			if ( ! empty( $instance['content_length'] ) ) {
				$body = proper_widget_truncate( $comment->comment_content, $instance['content_length'] );
				echo apply_filters( 'comment_text', $body . ' ' . $body_add );
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

		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = intval( $new_instance['number'] );
		$instance['offset'] = intval( $new_instance['offset'] );
		$instance['content_length'] = intval( $new_instance['content_length'] );
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
		proper_widget_output_fields( $this->widget_fields, $instance );

	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("ProperCommentsWidget");' ) );
