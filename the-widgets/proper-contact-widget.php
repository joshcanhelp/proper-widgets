<?php 

class proper_contact_widget extends WP_Widget {
	
	function proper_contact_widget () {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => __FUNCTION__);

		/* Create the widget. */
		$this->WP_Widget( 'proper-contact-widget', 'Proper Contact', $widget_ops);
		
		$this->widget_fields = array(
			array(
				'label' => 'Title *',
				'type' => 'text',
				'id' => 'title',
				'description' => 'Enter a title for this widget or leave blank for no title',
				'default' => 'Contact Us',
			),		
			array(
				'label' => 'Send to email',
				'type' => 'email',
				'id' => 'contact_email',
				'description' => '',
				'default' => get_bloginfo('admin_email')
			),
			array(
				'label' => 'Prompt text',
				'type' => 'textarea',
				'id' => 'text_prompt',
				'description' => '',
				'default' => 'Submit the form below to send us a lead!',
			),
			array(
				'label' => 'Thanks text',
				'type' => 'textarea',
				'id' => 'text_thanks',
				'description' => '',
				'default' => 'Thank you for the contact; we\'ll be in touch soon!',
			),
			array(
				'label' => 'Show math captcha?',
				'type' => 'checkbox',
				'id' => 'show_math',
				'description' => '',
				'default' => 'yes',
			),
			array(
				'label' => 'Request contact email?',
				'type' => 'checkbox',
				'id' => 'request_email',
				'description' => '',
				'default' => 'yes',
			),
			
		);
	
	}
	 
	function widget($args, $instance) {
	
		// Pulling out all settings
		extract($args); 
		extract($instance); 
		
		// Output all wrappers
		echo $before_widget . '
		<div class="proper-widget proper-contact-widget">';
		
		if(isset($title) && !empty($title)) 
			echo $before_title . $title . $after_title;
		
		//If the form is submitted
		if(!empty($_POST['submit_type'])) include(TEMPLATEPATH . '/inc/process-contact.php');
		 
		//If the email was sent, show "thank you"
		if(isset($email_sent) && $email_sent === true) :
		
			echo '<p class="link-content success">' . $text_thanks . '</p>';
		
		//If the email was not sent, show the form
		else :
		
			echo '<p class="link-content">' . $text_prompt . '</p>';
			
			if (isset($errors) && count($errors) !== 0) :
				echo '<ul class="error-box links-list link-content">';
				foreach ($errors as $er) :
					echo '<li>&raquo; ' . $er . '</li>';
				endforeach;
				echo '</ul>';
			endif;
		?>
		
		<form class="wpd-contact" enctype="multipart/form-data" method="post" action="#<?php echo $widget_id ?>">
			<?php if ($request_email == 'yes') : ?>
			<p class="link-content">
				<label for="from_email">Email address <strong>*</strong></label>
				<input type="email" class="text-field"  name="from_email" id="from_email" value="<?php if(isset($_POST['from_email'])) echo $_POST['from_email'];?>">
				<input type="hidden" name="email_required" value="yes">
			</p>
			<?php endif; ?>
			<p class="link-content">
				<label for="from_message">Message <strong>*</strong></label>
				<textarea name="from_message" id="from_message" class="text-field" cols="30" rows="4"><?php if(isset($_POST['from_message'])) echo $_POST['from_message'];?></textarea>
			</p>
			<?php 
			if ($show_math == 'yes') : 
				$math1 = mt_rand(1, 10);
				$math2 = mt_rand(1, 10);
				$math_total_bin = decbin($math1 + $math2);
			?>
			<p class="inline-field link-content">
				<input type="text" value="" id="math_input" name="math_input">
				<label for="math_input">What is <?php echo $math1 ?> + <?php echo $math2 ?>?</label>
				<input type="hidden" name="math_gotcha" value="<?php echo $math_total_bin ?>">
			</p>
			<?php endif; ?>
			<p class="hidden">
				<label for="honeypot">Leave this empty to submit:</label>
				<input type="text" name="honeypot" id="honeypot" value="">
			</p>
			<p>
				<input type="hidden" name="submit_type" value="widget">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wp-drudge-nonce') ?>">
				<input type="submit" id="form_submit" value="Contact &raquo;">
			</p>
		</form>
		
		<?php
		
		endif;
		
		echo '
		</div>
		' . $after_widget;
			
	}
 
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;

		$instance['title'] = apply_filters('widget_title', strip_tags($new_instance['title']));

		$instance['contact_email'] = filter_var($new_instance['contact_email'], FILTER_VALIDATE_EMAIL);
	
		$instance['show_math'] = isset($new_instance['show_math']) && $new_instance['show_date'] === 'yes' ? 'yes' : ''; 
		$instance['request_email'] = isset($new_instance['request_email']) && $new_instance['target'] === 'yes' ? 'yes' : ''; 
		
		$instance['text_prompt'] = sanitize_text_field($new_instance['text_prompt']);	
		$instance['text_thanks'] = sanitize_text_field($new_instance['text_thanks']);	

		return $instance;

	}
 
	function form($instance) {
		
		for ($i = 0; $i < count($this->widget_fields); $i++) :
			$field_id = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id'] = $this->get_field_id($field_id);
			$this->widget_fields[$i]['field_name'] = $this->get_field_name($field_id);
		endfor;
	
		proper_output_widget_fields($this->widget_fields, $instance);
		
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("proper_contact_widget");') );