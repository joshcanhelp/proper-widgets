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
		'Widgets to show',
		'',
		'',
		'title',
		'',
	),
	array(
		'Proper Article Widget',
		'widget_article',
		'',
		'checkbox',
		'yes',
	),
	array(
		'Proper Google News Widget',
		'widget_gnews',
		'',
		'checkbox',
		'yes',
	),array(
		'Proper Linked Image Widget',
		'widget_linkedimg',
		'',
		'checkbox',
		'yes',
	),
	array(
		'Proper Links Widget',
		'widget_links',
		'',
		'checkbox',
		'yes',
	),
	array(
		'Proper Posts Widget',
		'widget_posts',
		'',
		'checkbox',
		'yes',
	),
	array(
		'Proper RSS Widget',
		'widget_rss',
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

	add_submenu_page('options-general.php', "Proper Widget Options", "Proper Widgets", 'edit_themes', 'pwidget-admin', 'proper_widget_admin');

}

add_action('admin_menu' , 'pwidget_add_admin');

function proper_widget_admin() {

    global $pwidget_options, $pwidget_options_saved;

    if ( isset($_REQUEST['saved']) && $_REQUEST['saved'] ) 
			echo '<div id="message" class="updated fade"><p><strong>'.__('Settings saved.','thematic').'</strong></p></div>';
			
		elseif ( isset($_REQUEST['reset']) && $_REQUEST['reset'] ) 
			echo '<div id="message" class="updated fade"><p><strong>'.__('Settings reset.','thematic').'</strong></p></div>';
	
	?>
	<div class="wrap" id="proper-options-page">
		
		<h2>Proper Widgets Settings</h2>
		
		<?php 
		$doc_file = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/inc/docs.html';
		if (is_readable($doc_file)) echo file_get_contents($doc_file) 
		?>
            
		<form method="post">
	
			<table class="jch-form-table" cellpadding="0" cellspacing="0">	    
			<?php 
			foreach ($pwidget_options as $value) :
				
				/*
				Setting better variable names for clarity
				*/
				
				$opt_name = $value[0];
				
				$opt_id = $value[1];
				
				$opt_desc = $value[2];
				
				$opt_type = $value[3];
				
				if (isset($value[4][0]) && $value[4][0] == '#')
					$opt_default = substr($value[4], 1, 6);
				else 
					$opt_default = $value[4];
				
				if(isset($value[5])) $opt_options = $value[5];
				
				/*
				Descriptive text in the theme settings
				*/
				if ($opt_type == 'description') { 
				?>
				
				<tr>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1" colspan="2">
						<h4><?php echo $opt_name; ?>:</h4>
						<p><?php echo $opt_desc ?></p>
					</td>
				</tr>
				
				<?php
				/*
				Text input
				*/		
				} elseif ($opt_type == 'text' || $opt_type == 'url' || $opt_type == 'email') { 
				?>
				<tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<label for="<?php echo $opt_id; ?>"><?php echo $opt_name; ?>:</label>
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
						<?php echo $opt_desc ?><br >
						<input size="60" onfocus="this.select();" name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>" type="<?php echo $opt_type ?>" value="<?php 
							if ( isset($pwidget_options_saved[$opt_id]) && !empty($pwidget_options_saved[$opt_id])) { 
								echo stripslashes($pwidget_options_saved[$opt_id]); 
							} else {
								echo $opt_default;
							}
							?>" >
					</td>
				</tr>
                <?php 
				// Displays correct inputs for "select" type
				} elseif ($opt_type == 'select') {
				?>
                
                <tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<label for="<?php echo $opt_id; ?>"><?php echo $opt_name; ?>:</label>
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
						<?php echo $opt_desc; ?><br >
						<select name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>">	
								<option value="">None</option>
                        <?php foreach ($opt_options as $key => $val) {?>
                        	<option value="<?php echo $key ?>" <?php 
						if ( isset($pwidget_options_saved[$opt_id]) && $pwidget_options_saved[$opt_id] == $key) { 
							echo 'selected';
						} ?>><?php echo $val ?></option> 
                        <?php } ?>
                        </select>
					</td>
				</tr>
                
                <?php 
				} 
				// Displays correct inputs for "radio" type
				elseif ($opt_type == 'radio') {
				?>
                
                <tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<?php echo $opt_name; ?>:
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
						<?php echo $opt_desc; ?><br >
                        <?php foreach ($opt_options as $val) {?>
                        
                        <input type="radio" value="<?php echo $val ?>" <?php if ( $pwidget_options_saved[$opt_id] == $val || ($pwidget_options_saved[$opt_id] == '' && $opt_default == $val )) { echo 'checked';} ?> name="<?php echo $opt_id; ?>" id="<?php echo $opt_id . $val; ?>">
                        <label for="<?php echo $opt_id . $val; ?>"><?php echo $val ?></label><br >
                        <?php } ?>
					</td>
				</tr>
                
                <?php } elseif ($opt_type == 'checkbox') {?>
                
                <tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<?php echo $opt_name; ?>:
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">	
						<?php echo $opt_desc ?><br >
                        <input type="checkbox" value="yes" <?php if ( isset($pwidget_options_saved[$opt_id]) && $pwidget_options_saved[$opt_id] == 'yes' ) { echo 'checked';} ?> name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>">
                        <label for="<?php echo $opt_id; ?>">Yes</label><br >

					</td>
				</tr>
                
				<?php } 
				// Displays correct inputs for "textarea" type
				elseif ($opt_type == 'textarea') { ?>
				<tr>
					<th style="border-bottom: 1px solid #f1f1f1; text-align: left; padding: 10px" scope="row">
						<?php echo $opt_name; ?>:
					</th>
					<td style="padding: 20px 10px; border-bottom: 1px solid #f1f1f1">
					<?php echo $opt_desc ?><br>
						<textarea onfocus="this.select();" rows="6" cols="60" name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>" style="<?php echo $value['style']; ?>" type="<?php echo $opt_type; ?>" ><?php if ( isset($pwidget_options_saved[$opt_id])) { echo stripslashes($pwidget_options_saved[$opt_id]); }?></textarea>
					</td>
				</tr>
				
				<?php } elseif ($opt_type == 'title') {?>
				<tr>
					<td style="padding: 20px 10px;" colspan="2" class="header">
						 <h3 style="font-size: 1.6em"><?php  echo $opt_name ?></h3>
					 </td>
				 </tr>
				<?php  }?>
            
			<?php 
	
			endforeach; 
			?>
			 </table>
			 <p class="submit">
			 <input name="save" type="submit" value="Save changes" class="button-primary">
			 <input type="hidden" name="action" value="save" >
			</p>
		  </form>
	
		</div>
		
<?php 

}//end function mytheme_admin() 


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