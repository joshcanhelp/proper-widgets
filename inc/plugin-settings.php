<?php

class ProperWidgetSettings
{

	private static $__instance = NULL;

	private $settings = array();
	private $default_settings = array();
	private $settings_texts = array();

	private $plugin_prefix = 'proper_widgets';
	private $plugin_name = 'PROPER Widgets';
	private $settings_page_name = null;
	private $dashed_name = 'proper-widgets';

	/*
	 * Constructor
	 */
	public function __construct() {

		// Hooks to activate the settings and settings page
		add_action( 'admin_init', array( &$this, 'register_setting' ) );
		add_action( 'admin_menu', array( &$this, 'register_settings_page' ) );

		// Default settings
		$this->default_settings = (array) apply_filters(
			$this->plugin_prefix . '_setting_defaults',
			array(
				'output_css' => 1,
				'widget_article' => 1,
				'widget_authors' => 1,
				'widget_comments' => 1,
				'widget_embed' => 1,
				'widget_gnews' => 1,
				'widget_linkedimg' => 1,
				'widget_links' => 1,
				'widget_posts' => 1,
				'widget_rss' => 1,
				'widget_core_rss' => 0,
				'widget_core_posts' => 0,
				'widget_core_links' => 0,
				'widget_core_comments' => 0
			)
		);

		// Settings labels and types
		$this->settings_texts = (array) apply_filters(
			$this->plugin_prefix . '_setting_labels',
			array(
				'output_css' => array(
					'label' => __( 'Use PROPER Widget CSS', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_article' => array(
					'label' => __( 'Show PROPER Article widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_gnews' => array(
					'label' => __( 'Show PROPER Google News widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_linkedimg' => array(
					'label' => __( 'Show PROPER Linked Image widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_links' => array(
					'label' => __( 'Show PROPER Links widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_posts' => array(
					'label' => __( 'Show PROPER Posts widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_rss' => array(
					'label' => __( 'Show PROPER RSS widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_embed' => array(
					'label' => __( 'Show PROPER Embed widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_comments' => array(
					'label' => __( 'Show PROPER Comments widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_authors' => array(
					'label' => __( 'Show PROPER Authors widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_core_rss' => array(
					'label' => __( 'Show core RSS widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_core_posts' => array(
					'label' => __( 'Show core Posts widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_core_links' => array(
					'label' => __( 'Show core Links widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
				'widget_core_comments' => array(
					'label' => __( 'Show core Recent Comments widget', $this->dashed_name ),
					'type'  => 'yesno'
				),
			)
		);

		// Grab current saved settings
		$plugin_settings = get_option( $this->plugin_prefix . '_settings', array() );

		// Grab legacy settings and parse
		if ( $legacy_options = get_option( 'pwidget_settings_array' ) ) {
			foreach ( $legacy_options as $key=> $val ) {
				$plugin_settings[$key] = $val == 'yes' ? 1 : 0;
			}
			delete_option( 'pwidget_settings_array' );
		}

		$this->settings = wp_parse_args( $plugin_settings, $this->default_settings );
	}

	/*
	 * Startup procedure
	 */
	public static function init() {
		self::instance()->settings_page_name = __( 'PROPER Widgets Settings', self::instance()->dashed_name );
	}

	/*
	 * Use this singleton to address methods
	 */
	public static function instance() {
		if ( self::$__instance == NULL ) {
			self::$__instance = new ProperWidgetSettings;
		}
		return self::$__instance;
	}

	/*
	 * Getter for settings
	 */
	public static function get_setting( $opt ) {
		return isset( self::instance()->settings[$opt] ) ? self::instance()->settings[$opt] : '';
	}

	/*
	 * Add the settings page as a sub-page to the wp-admin Settings menu
	 */
	public function register_settings_page() {
		add_submenu_page(
			'options-general.php',
			'PROPER Widgets Settings',
			$this->plugin_name,
			'manage_options',
			'pwidget-admin',
			array( &$this, 'settings_page' )
		);
	}

	/*
	 * Register the settings group being used
	 */
	public function register_setting() {
		register_setting(
			$this->plugin_prefix . '_settings',
			$this->plugin_prefix . '_settings',
			array( &$this, 'validate_settings' )
		);
	}

	/*
	 * Validate and sanitize all settings
	 */
	public function validate_settings( $settings ) {

		// Reset to defaults
		if ( ! empty( $_POST[$this->dashed_name . '-defaults'] ) ) {
			$settings                     = $this->default_settings;
			$_REQUEST['_wp_http_referer'] = add_query_arg( 'defaults', 'true', $_REQUEST['_wp_http_referer'] );

		// Sanitize and validate settings
		} else {
			$settings = array_map( 'intval', $settings );
		}
		return $settings;
	}

	/*
	 * Display the settings page
	 */
	public function settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to access this page.', $this->dashed_name ) );
		}

		?>
		<div class="wrap">
			<h2><?php echo $this->settings_page_name; ?></h2>

			<form method="post" action="options.php">

				<?php settings_fields( $this->plugin_prefix . '_settings' ); ?>

				<table class="form-table">
					<?php
					foreach ( $this->settings as $setting => $value ):

						// Nothing set for this option ... skip
						if ( empty( $this->settings_texts[$setting] ) ) {
							continue;
						}

					?>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo $this->dashed_name . '-' . $setting; ?>">
									<?php if ( isset( $this->settings_texts[$setting]['label'] ) ) {
										echo $this->settings_texts[$setting]['label'];
									}
									else {
										echo $setting;
									} ?>
								</label>
							</th>
							<td>
								<?php
								switch ( $this->settings_texts[$setting]['type'] ):
									case 'yesno':
										?>
										<select name="<?php echo $this->plugin_prefix; ?>_settings[<?php echo $setting; ?>]" id="<?php
										echo $this->dashed_name . '-' . $setting; ?>" class="postform">
											<?php
											$yesno = array(
												0 => __( 'No', $this->dashed_name ),
												1 => __( 'Yes', $this->dashed_name )
											);
											foreach ( $yesno as $val => $txt ) {
												echo '<option value="' . esc_attr( $val ) . '"' . selected( $value, $val, false ) . '>' . esc_html( $txt ) . "&nbsp;</option>\n";
											}
											?>
										</select><br />
										<?php
										break;

									case 'text':
									case 'email':
									case 'url':
									case 'number':
										?>
										<div>
											<input type="<?php
												echo $this->settings_texts[$setting]['type'];
												?>" name="<?php
												echo $this->plugin_prefix; ?>_settings[<?php echo $setting;
												?>]" id="<?php echo $this->dashed_name . '-' . $setting;
												?>" class="postform" value="<?php echo esc_attr( $value ); ?>">
										</div>
										<?php
										break;

									default:
										echo '<div>';
										echo '<p>No output available for type ' . $this->settings_texts[$setting]['type'];
										echo '</div>';
										break;

								endswitch;
								?>
								<?php
								if ( ! empty( $this->settings_texts[$setting]['desc'] ) ) {
									echo '<p class="description">' . $this->settings_texts[$setting]['desc'] . '</p>';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>

				<p class="submit">
					<?php
					if ( function_exists( 'submit_button' ) ) {
						submit_button( null, 'primary', $this->dashed_name . '-submit', false );
						echo ' ';
						submit_button( __( 'Reset to Defaults', $this->plugin_prefix ), '', $this->dashed_name . '-defaults', false );
					}
					else {
						echo '<input type="submit" name="' . $this->dashed_name . '-submit" class="button-primary" value="' . __( 'Save Changes', $this->plugin_prefix ) . '" />' . "\n";
						echo '<input type="submit" name="' . $this->dashed_name . '-defaults" id="' . $this->dashed_name . '-defaults" class="button-primary" value="' . __( 'Reset to Defaults', $this->plugin_prefix ) . '" />' . "\n";
					}
					?>
				</p>

			</form>
		</div>

	<?php
	}
}

// if we loaded wp-config then ABSPATH is defined and we know the script was not called directly to issue a cli call
if ( defined( 'ABSPATH' ) ) {
	ProperWidgetSettings::init();
}
