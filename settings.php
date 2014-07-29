<?php 

// Theme settings/options page	

/* 
0 = name
1 = id
2 = desc
3 = type
4 = default
5 = options
*/

global $pwidget_options;
$pwidget_options = array(
	array(
		'PROPER Widgets to show',
		'',
		'',
		'title',
		'',
	),
	array(
		'PROPER Article Widget',
		'widget_article',
		'',
		'checkbox',
		'yes',
	),
	array(
		'PROPER Google News Widget',
		'widget_gnews',
		'',
		'checkbox',
		'yes',
	),array(
		'PROPER Linked Image Widget',
		'widget_linkedimg',
		'',
		'checkbox',
		'yes',
	),
	array(
		'PROPER Links Widget',
		'widget_links',
		'',
		'checkbox',
		'yes',
	),
	array(
		'PROPER Posts Widget',
		'widget_posts',
		'',
		'checkbox',
		'yes',
	),
	array(
		'PROPER RSS Widget',
		'widget_rss',
		'',
		'checkbox',
		'yes',
	),
//	array(
//			'PROPER Embed Widget',
//			'widget_embed',
//			'',
//			'checkbox',
//			'yes',
//	),
//	array(
//			'PROPER Comments Widget',
//			'widget_comments',
//			'',
//			'checkbox',
//			'yes',
//	),
//	array(
//			'PROPER Authors Widget',
//			'widget_authors',
//			'',
//			'checkbox',
//			'yes',
//	),
	array(
		'Core widgets to show',
		'',
		'',
		'title',
		'',
	),
	array(
		'Core RSS widget',
		'widget_core_rss',
		'',
		'checkbox',
		'yes',
	),
	array(
		'Core Posts widget',
		'widget_core_posts',
		'',
		'checkbox',
		'yes',
	),
	array(
		'Core Links widget',
		'widget_core_links',
		'',
		'checkbox',
		'yes',
	),
	
	
);

function pwidget_add_admin() {
	
	global $pwidget_options, $pwidget_options_saved ;
	
	if ( array_key_exists('page', $_GET) && $_GET['page'] === 'pwidget-admin' ) {
		
		if (array_key_exists('action', $_REQUEST)) {
		
			if ('save' == $_REQUEST['action'] ) {
		
				foreach ($pwidget_options as $opt) {
		
					if (isset($_REQUEST[$opt[1]])) $pwidget_options_saved[$opt[1]] = $_REQUEST[$opt[1]];
					else $pwidget_options_saved[$opt[1]] = '';
		
				}
				
				update_option('pwidget_settings_array', $pwidget_options_saved);
		
				header("Location: admin.php?page=pwidget-admin&saved=true");
				
				die;
		
			} 
		}
	}

	add_submenu_page(
		'options-general.php',
		"PROPER Widget Options",
		"PROPER Widgets",
		'edit_themes',
		'pwidget-admin',
		'proper_widget_admin'
	);

}

add_action('admin_menu' , 'pwidget_add_admin');



function proper_widget_admin() {

    global $pwidget_options, $pwidget_options_saved;
?>
	
		<div class="wrap" id="proper-contact-options">
			<h1>PROPER Widgets Settings</h1>
			
			<?php 
			$doc_file = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/inc/docs.html';
			if (is_readable($doc_file)) echo file_get_contents($doc_file) 
			?>
		
			<?php
			// Show the "saved" message
			if ( !empty($_REQUEST['saved']) ) : 
			?>
			
				<div id="message" class="updated fade">
					<p><strong>PROPER Widgets <?php echo  __('settings saved.','thematic') ?></strong></p>
				</div>
		
			<?php endif ?>
			
			<form method="post">
				<table cellpadding="0" cellspacing="0">

			<?php 
			foreach ($pwidget_options as $value) :
				
				// More clear option names
				
				// Human-readable name
				$opt_name = $value[0];
				
				// Machine name as ID
				$opt_id = $value[1];
				
				// Description for this field, aka help text
				$opt_desc = $value[2];
				
				// Input type, set to callback to use a function to build the input
				$opt_type = $value[3];
				
				// Default vale
				$opt_default = $value[4];
			
				// Value currently saved
				$opt_val = isset($pwidget_options_saved[$opt_id]) ? $pwidget_options_saved[$opt_id] : $opt_default;
	
				// Options if checkbox, select, or radio
				$opt_options = empty($value[5]) ? array() : $value[5];
		
				// Allow for blocks of HTML to be displayed within the settings form
				if ($opt_type == 'html') :
				?>
					<tr>
						<td colspan="2">
							<h4><?php echo $opt_name ?></h4>
							<p class="option_desc"><?php echo $opt_desc ?></p>
						</td>
					</tr>
				<?php
				
				// Allow titles to be added to deliniate sections
				elseif ($opt_type == 'title') :
				?>
				
					<tr>
						<td colspan="2" class="header">
							<h3><?php  echo $opt_name ?></h3>
						</td>
					</tr>
					
				<?php  
				
				// Horizontal breaks
				elseif ($opt_type == "break") : 
				?>
					
					<tr><td colspan="2"><hr></td></tr>
					
				<?php
			
				// Displays correct inputs for "text" type			
				elseif ($opt_type == 'text' || $opt_type == 'number' || $opt_type == 'email' || $opt_type == 'url') :
				?>
				
					<tr>
						<th>
							<label for="<?php echo $opt_id ?>"><?php echo $opt_name ?>:</label>
						</th>
						<td>
							<p class="option_desc"><?php echo $opt_desc ?></p>
							<p><input size="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type ?>" value="<?php echo stripslashes($opt_val) ?>"></p>
						
						</td>
					</tr>
				
        <?php 
				
				// Displays correct inputs for "select" type
				elseif ($opt_type == 'select') :
				?>
                
					<tr>
						<th>
							<label for="<?php echo $opt_id ?>"><?php echo $opt_name ?>:</label>
						</th>
						<td>
							<p class="option_desc"><?php echo $opt_desc; ?></p>
							<p>
								<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>">
									<?php 
									foreach ($opt_options as $val) : 
									
										$selected = '';	
										if ( $pwidget_options_saved[$opt_id] == $val || ( empty($pwidget_options_saved[$opt_id]) && $opt_default == $val ) ) 
											$selected = 'selected';	
											?>
										<option value="<?php echo $val ?>" <?php echo $selected ?>><?php echo $val ?></option> 
									<?php endforeach; ?>
								</select>
							</p>
						</td>
					</tr>
                
       	<?php 
				 
				// Displays correct inputs for "radio" type
				elseif ($opt_type == 'radio') :
				?>
                
					<tr>
						<th>
							<span><?php echo $opt_name ?>:</span>
						</th>
						<td>
							<p class="option_desc"><?php echo $opt_desc; ?></p>
							
							<?php 
							foreach ($opt_options as $val) : 
								
								$checked = '';
								if ( $pwidget_options_saved[$opt_id] == $val || ( empty($pwidget_options_saved[$opt_id]) && $opt_default == $val )) 
									$checked = 'checked';
									?>		
												
								<p><input type="radio" value="<?php echo $val ?>" name="<?php echo $opt_id ?>" id="<?php echo $opt_id . '_' . $val; ?>" <?php echo $checked ?>>
								<label for="<?php echo $opt_id . $val; ?>"><?php echo $val ?></label><br></p>
								
							<?php endforeach; ?>
						</td>
					</tr>
                
        <?php 
				
				// Checkbox input, allows for multiple or single
				elseif ($opt_type == 'checkbox') :
				?>
                
					<tr>
						<th>
							<span><?php echo $opt_name ?>:</span>
						</th>
					<td>	
						<p class="option_desc"><?php echo $opt_desc ?></p>
						<?php
						// If we have multiple checkboxes to show
						if (!empty($opt_options)) : 
							for ( $i = 0; $i < count($opt_options); $i++ ) :
								
								// Need to mark current options as checked
								$checked = '';
								if ( in_array($opt_options[$i], $pwidget_options_saved[$opt_id]) ) 
									$checked = 'checked';
									?>
								<p>
								<input type="checkbox" value="<?php echo $opt_options[$i] ?>" name="<?php echo $opt_id ?>[]" id="<?php echo $opt_id . '_' . $i ?>" <?php echo $checked ?>>
								<label for="<?php echo $opt_id . '_' . $i ?>"><?php echo $opt_options[$i] ?></label>
								</p>
							<?php
							endfor;
						
						// Single "on-off" checkbox
						else :
							$checked = '';
							if ( $opt_val == 'yes' ) 
								$checked = 'checked';
								?>
						<p>
							<input type="checkbox" value="yes" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" <?php echo $checked ?>>
							<label for="<?php echo $opt_id ?>">Yes</label>
						</p>
						<?php endif; ?>
					
					</td>
					</tr>
                
				<?php 
				
				// Displays input for "textarea" type
				elseif ($opt_type == 'textarea') : 
				?>
				<tr>
					<th>
						<?php echo $opt_name ?>:
					</th>
					<td>
						<textarea rows="6" cols="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>"><?php echo stripslashes($opt_val)?></textarea>
					</td>
				</tr>
				
				<?php 
				endif;
	
			endforeach; 
			?>
				<tr>
					<td colspan="2">
						<p>
							<input name="save" type="submit" value="Save changes" class="button-primary">
							<input type="hidden" name="action" value="save" >
							
							<?php if (isset($pwidget_options_saved['last-panel'])) : ?>
							<input type="hidden" id="proper-show-panel" name="panel" value="<?php echo $pwidget_options_saved['last-panel'] ?>" >
							<?php else : ?>
							<input type="hidden" id="proper-show-panel" name="panel" value="header" >
							<?php endif; ?>
						</p>
						
					</td>
				</tr>
			</table>
		</form>
	
	</div>
	
	<?php 
} 


function pwidget_settings_init() {
	
	global $pwidget_options, $pwidget_options_saved;
	
	if (!get_option('pwidget_settings_array')) :
		
		foreach ($pwidget_options as $opt) {
				
			$pwidget_options_saved[$opt[1]] = $opt[4];
			
		}
			
		update_option( 'pwidget_settings_array', $pwidget_options_saved);
	
	endif; 

}

add_action('admin_head', 'pwidget_settings_init');