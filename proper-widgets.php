<?php 

/*
Plugin Name: PROPER Widgets
Plugin URI: http://theproperweb.com/shipped/wp/proper-widgets
Description: More widgets than you can shake a stick at.
Version: 0.9.1
Author: PROPER Web Development
Author URI: http://theproperweb.com
License: GPLv2 or later
*/

require_once('settings.php');

// Custom plugin settings
global $pwidget_options_saved;
$pwidget_options_saved = get_option('pwidget_settings_array');

require_once('settings.php');

// Require widget files if settings allow for it

if ($pwidget_options_saved['widget_article'] === 'yes')
	require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-article-widget.php');
	
if ($pwidget_options_saved['widget_gnews'] === 'yes')
	require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-gnews-widget.php');

if ($pwidget_options_saved['widget_linkedimg'] === 'yes')
	require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-linked-image-widget.php');

if ($pwidget_options_saved['widget_links'] === 'yes')
	require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-links-widget.php');

if ($pwidget_options_saved['widget_posts'] === 'yes')
	require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-posts-widget.php');

if ($pwidget_options_saved['widget_rss'] === 'yes')
	require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-rss-widget.php');

if ($pwidget_options_saved['widget_video'] === 'yes')
    require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-video-widget.php');
	
// Hide core widgets
function pwidget_unregister_widgets() {
	
	global $pwidget_options_saved;
	
	if ($pwidget_options_saved['widget_core_links'] !== 'yes')
		unregister_widget('WP_Widget_Links');
	
	if ($pwidget_options_saved['widget_core_posts'] !== 'yes')
		unregister_widget('WP_Widget_Recent_Posts');
	
	if ($pwidget_options_saved['widget_core_rss'] !== 'yes')
		unregister_widget('WP_Widget_RSS');
}
add_action('widgets_init', 'pwidget_unregister_widgets', 1);

// Coming soon: require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-contact-widget.php');
// Coming soon: require_once(plugin_dir_path( __FILE__ ) . 'the-widgets/proper-authors-widget.php');

/*
 * Helper functions
 */

function proper_widget_truncate($string, $limit, $break = " ") {

    if (strlen($string) >= $limit) {

        $string = substr($string, 0, $limit);

        while($string[strlen($string) - 1] != $break && !empty($string)) {
            $string = substr($string, 0, strlen($string) - 1);
        }
    }

    return $string;
}

/*
Builds all widget admin forms
*/
function proper_output_widget_fields($fields, $instance) {
	
	$label_style = 'style="font-weight: bold; display: block; margin: 0 0 4px"';
	
	foreach ($fields as $field) :
		
			echo '<p>';
			
			switch ($field['type']) : 
				
				case 'text':
				case 'email':
				case 'url':
				case 'number':
					?>
					
					<label for="<?php echo $field['field_id'] ?>" <?php echo $label_style ?>><?php echo $field['label'] ?></label>
					<input type="<?php echo $field['type'] ?>" id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" value="<?php echo isset($instance[$field['id']]) ? $instance[$field['id']] : $field['default'] ?>" style="display: block; width: 100%" />
					<?php  echo !empty($field['description']) ? '<span class="description">' . $field['description'] . '</span>' : '' ?>
					
					<?php
					break;
				
				case 'textarea':
					?>
					
					<label for="<?php echo $field['field_id'] ?>" <?php echo $label_style ?>><?php echo $field['label'] ?></label>
					<textarea id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" style="display: block; width: 100%"><?php echo isset($instance[$field['id']]) ? $instance[$field['id']] : $field['default'] ?></textarea>
					<?php  echo !empty($field['description']) ? '<span class="description">' . $field['description'] . '</span>' : '' ?>
					
					<?php
					break;
				
				case 'checkbox':
					?>
					
					<input type="checkbox" id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" value="yes" <?php if (isset($instance[$field['id']]) && $instance[$field['id']] == 'yes') echo 'checked' ?>/>
          <label for="<?php echo $field['field_id'] ?>" style="font-weight: bold"><?php echo $field['label'] ?></label>
					<?php  echo !empty($field['description']) ? '<span class="description">' . $field['description'] . '</span>' : '' ?>
					
					<?php
					break;
				
				case 'select':
					if (is_array($field['options'])) :
					?>
					<label for="<?php echo $field['field_id'] ?>" <?php echo $label_style ?>><?php echo $field['label'] ?></label>
					<select id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" style="display: block; width: 100%">
					<?php
						foreach ($field['options'] as $key => $val) :
							// Selecting the current set option
							$checked = isset($instance[$field['id']]) && $instance[$field['id']] == $key ? ' selected' : '';
						?>
						<option value="<?php echo $key ?>"<?php echo $checked ?>><?php echo $val ?></option>
						<?php
						endforeach;
					?>
					</select>
					<?php
					endif;
					break;
			
			endswitch;
			
			echo '</p>';
			
		endforeach;
	
}

function proper_widget_fetch_rss($feed_args) {
	
	$rss = fetch_feed($feed_args['url']);
	
	// Store the error message as a string to return at the end
	if ( is_wp_error($rss) ) {
		echo $rss->get_error_message();
	}
	
	// default settings if none are present in the args passed
	$defaults = array(
		'get_link' => true,
		'get_title' => true,
		'get_blurb' => false,
		'get_date' => false,
		'enable_cache' => true,
		'cache_duration' => 1800,
		'items' => 10
	);
	
	// Merges defaults with args passed and stores them as variables to use here
	$feed_args = wp_parse_args($feed_args, $defaults);
	extract($feed_args, EXTR_SKIP);
	
	if ($enable_cache) {
		// Set the cache duration
		$rss->enable_cache($enable_cache);
		$rss->set_cache_duration($cache_duration);
	}
	
	// Start 'er up
	$rss->init();
	
	if ( !$rss->get_item_quantity() ) {
		$error .= 'No items were found. ';
	}
	
	
	// Parsing, formatting, and storing feed content
	$feed_content = array();
	foreach ( $rss->get_items(0, $items) as $item ) {
		
		if ($get_link) {
			$link = $item->get_link();
			if (empty($link)) {
				$link = $item->get_permalink();
			}
		}
		
		if ($get_title) {
			$title = $item->get_title();
			$title = $item->sanitize(strip_tags($title), SIMPLEPIE_CONSTRUCT_TEXT);
		}
	
		if ($get_blurb) {
			$blurb = $item->get_description();
		}
		
		if ($get_date) {
			$date = $item->get_date(get_option('date_format'));
		}
		
		$feed_content[] = compact("link", "title", "blurb", "date");
		
	}
	
	if (!empty($error)) return $error;
	else return $feed_content;
}

// Output an embedded player using a URL and size parameters
function proper_widget_output_embed ($url, $w = 500, $h = 280) {

    $output = '';
    $embed_url = parse_url($url);
    $host = isset($embed_url['host']) ? $embed_url['host'] : '';
    $path = isset($embed_url['path']) ? str_replace('/', '', $embed_url['path']) : '';
    $query = isset($embed_url['query']) ? $embed_url['query'] : '';

    switch ($host) :

        case 'www.youtube.com':
        case 'youtube.com':
            $queries = explode('&', $query);
            foreach ($queries as $q) :
                $q_parts = explode('=', $q);
                if ($q_parts[0] == 'v')
                    $output .= proper_widget_output_youtube($q_parts[1], $w, $h);

            endforeach;
            break;

        case 'youtu.be':
            if (!empty($path))
                $output .= proper_widget_output_youtube($path, $w, $h);
            break;

        case 'www.vimeo.com':
        case 'vimeo.com':
            if (!empty($path))
                $output .= proper_widget_output_vimeo($path, $w, $h);
            break;

    endswitch;

    return !empty($output) ? $output : 'Bad URL';
}

function proper_widget_output_youtube ($id, $w, $h) {
    // Updated embed code 12/31/2012
    return '<iframe width="'.$w.'" height="' . $h . '" src="http://www.youtube.com/embed/'.$id.'" frameborder="0"
class="aligncenter" allowfullscreen></iframe>';

}

function proper_widget_output_vimeo ($id, $w, $h) {
    // Updated embed code 12/31/2012
    return '<iframe src="http://player.vimeo.com/video/'.$id.'?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff" width="'.$w.'" height="'.$h.'" frameborder="0" class="aligncenter" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

}
