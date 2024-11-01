<?php

use WpLHLAdminUi\Forms\AdminForm;

/**
 * The settings of the plugin.
 *
 * @link       https://lehelmatyus.com
 * @since      1.0.0
 *
 * @package    tgentg_Plugin
 * @subpackage tgentg_Plugin/admin
 */

/**
 * Class WordPress_Plugin_Template_Settings
 *
 */

use WpLHLAdminUi\LicenseKeys\LicenseKeyHandler;
use WpLHLAdminUi\LicenseKeys\LicenseKeyAdminGUI;
use WpLHLAdminUi\Models\LHLLinkModel;

class TGENTG_Admin_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private LicenseKeyHandler $license_key_handler;
	private $license_key_valid = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->license_key_handler = new LicenseKeyHandler(new TGEN_LicenseKeyDataProvider());
		$this->license_key_valid = $this->license_key_handler->is_active();
	}

	/**
	 * This function introduces the theme options into the 'Appearance' menu and into a top-level
	 * 'TGEN - Template Generator for TNEW' menu.
	 */
	public function setup_plugin_options_menu() {

		//Add the menu to the Plugins set of menu items
		add_submenu_page(
			'options-general.php',
			'TGEN - Template Generator for TNEW', 				  // The title to be displayed in the browser window for this page.
			'TGEN - Template Generator for TNEW',				  // The text to be displayed for this menu item
			'manage_options',					              // Which type of users can see this menu item
			'tgentg_options',			                      // The unique ID - that is, the slug - for this menu item
			array($this, 'render_settings_page_content')	  // The name of the function to call when rendering this menu's page
		);
	}

	/**
	 * Provides default values for the Settings.
	 *
	 * @return array
	 */
	public function default_main_options() {
		$defaults = array(
			'test'		=>	'',
		);
		return $defaults;
	}

	/**
	 * Provide default values for the Generator.
	 *
	 * @return array
	 */
	public function default_generator_options() {
		$defaults = array(
			'tgen_teamplate_page_id' => '',
			'tgen_content_region_selector' => '',
			'tgen_container_wrap' => '',
			'tgen_inline_css' => '',
			'tgen_external_css_url' => '',
		);
		return  $defaults;
	}
	public function default_cleanupfilter_options() {
		$defaults = array(
			'tgen_header_filter_option' => '',
			'tgen_header_filters' => '',
			'tgen_tag_filters' => '',

		);
		return  $defaults;
	}

	/**
	 * Provides default values for the Input Options.
	 *
	 * @return array
	 */
	public function default_input_options() {
		$defaults = array(
			'input_example'		=>	'default generate_action option',
			'textarea_example'	=>	'',
			'checkbox_example'	=>	'',
			'radio_example'		=>	'2',
			'time_options'		=>	'default'
		);
		return $defaults;
	}

	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	public function render_settings_page_content($active_tab = '') {
?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<h2><?php _e('TGEN - Template Generator for TNEW', 'tgen-template-generator'); ?></h2>
			<?php // settings_errors(); 
			?>

			<?php if (isset($_GET['tab'])) {
				$active_tab = sanitize_text_field($_GET['tab']);
			} else if ($active_tab == 'generator_options') {
				$active_tab = 'generator_options';
			} else if ($active_tab == 'generate_action') {
				$active_tab = 'generate_action';
			} else {
				$active_tab = 'main_options';
			} // end if/else 
			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=tgentg_options&tab=main_options" class="nav-tab <?php echo $active_tab == 'main_options' ? 'nav-tab-active' : ''; ?>"><?php _e('Info', 'tgen-template-generator'); ?></a>
				<a href="?page=tgentg_options&tab=generator_options" class="nav-tab <?php echo $active_tab == 'generator_options' ? 'nav-tab-active' : ''; ?>"><?php _e('Generator Options', 'tgen-template-generator'); ?></a>
				<a href="?page=tgentg_options&tab=generator_cleanup_filters" class="nav-tab <?php echo $active_tab == 'generator_cleanup_filters' ? 'nav-tab-active' : ''; ?>"><?php _e('Cleanup Filters', 'tgen-template-generator'); ?></a>
				<a href="?page=tgentg_options&tab=generate_action" class="nav-tab <?php echo $active_tab == 'generate_action' ? 'nav-tab-active' : ''; ?>"><?php _e('Generate Template', 'tgen-template-generator'); ?></a>
			</h2>

			<form method="post" action="options.php">
				<?php

				if ($active_tab == 'generator_options') {

				?>
					<div class="tgen_container">
						<?php
						settings_fields('tgentg_generator_options');
						do_settings_sections('tgentg_generator_options');

						submit_button();
						?>
					</div>

				<?php
				} elseif ($active_tab == 'generator_cleanup_filters') {

				?>
					<div class="tgen_container">
						<?php
						settings_fields('tgentg_cleanupfilter_options');
						do_settings_sections('tgentg_cleanupfilter_options');

						submit_button();
						?>
					</div>

				<?php

				} elseif ($active_tab == 'generate_action') {

					// settings_fields( 'tgentg_generate_action_options' );
					// do_settings_sections( 'tgentg_generate_action_options' );

					$this->__generate_template_button();
				} elseif ($active_tab == 'main_options') {


					$link_to_license = new LHLLinkModel(
						__('Get a License Key.', 'tgen-template-generator'),
						'https://lehelmatyus.com/tgen-template-generator-for-tnew?utm_source=plugin&utm_medium=license&utm_campaign=tgen-template-generator-for-tnew',
					);

					$licenes_gui = new LicenseKeyAdminGUI(
						$this->license_key_valid,
						$link_to_license,
						__("Don't have a license? Obtain one now to unlock all features and receive full support.", 'tgen-template-generator'),
						'tgen-template-generator'
					);
					$licenes_gui->__license_card_display();

				?>
					<div class="tgen_container">
						<h2>
							<?php _e('READ these steps before you do anything.', 'tgen-template-generator') ?>
						</h2>
						<p class="tgen_note tgen_first_note">
							<?php _e('Folow it step by step to generate an awesome TNEW template from one of your pages on this website.', 'tgen-template-generator') ?>
						</p>

						<div>
							<ol class="tgen_instructions">
								<li>
									<?php _e('Create a', 'tgen-template-generator') ?>
									<a href="/wp-admin/post-new.php?post_type=page">
										<?php _e('New Page on your website', 'tgen-template-generator') ?>
									</a>
									<ul class="tgen_instructions tgen_instructions_sub">
										<li>
											<?php _e('This will be later turned into the TNEW template. ', 'tgen-template-generator') ?>
										</li>
										<li>
											<?php _e('The page doesnt need to have content, but the page does need to be published. No need to add it to any menus.', 'tgen-template-generator') ?>
										</li>
										<li>
											<?php _e('Make sure that page loads the sidebar, footer, menu, global elements etc. <b>As you would like it to appear on your TNEW Website.</b>.', 'tgen-template-generator') ?>
										</li>
									</ul>
								</li>

								<li>
									<?php _e('Head to "Generator Options" Tab', 'tgen-template-generator') ?>
									<ul class="tgen_instructions tgen_instructions_sub">
										<li>
											<?php _e('Select the newly created Page in the "Template Source Page" dropdown', 'tgen-template-generator') ?>
										</li>
										<li>
											<?php _e('Customize generator with extra CSS.', 'tgen-template-generator') ?>
										</li>
										<li>
											<?php _e('Add Javascript if needed and configure element stripping.', 'tgen-template-generator') ?>
										</li>
									</ul>
								</li>
								<li>
									<?php _e('Head to "Generate Template" Tab .', 'tgen-template-generator') ?>
									<ul class="tgen_instructions tgen_instructions_sub">
										<li>
											<?php _e('Check if all looks good then hit "Generate"', 'tgen-template-generator') ?>
										</li>
									</ul>
								</li>
								<li>
									<?php _e('Head to Your TNEW Website .', 'tgen-template-generator') ?>
									<ul class="tgen_instructions tgen_instructions_sub">
										<li>
											<?php _e('Point your TNEW installation to the path that the generator defines for you.', 'tgen-template-generator') ?>
										</li>
									</ul>
								</li>
							</ol>
							<p>
								<?php _e('For professional assisentance head to the support page.', 'tgen-template-generator') ?>
							<p>
						</div>
					</div>
				<?php
					// Main options
					// settings_fields( 'tgentg_main_options' );
					// do_settings_sections( 'tgentg_main_options' );
					// submit_button();
				} else {
				?>
					but why?
				<?php
				}


				?>
			</form>

		</div><!-- /.wrap -->
	<?php
	}

	/**
	 * This function provides a simple description for the generate_action options page.
	 */

	public function main_options_callback() {
		$options = get_option('tgentg_main_options');
		echo '<p>' . __('Main options', 'tgen-template-generator') . '</p>';
	}

	public function generator_options_callback() {
		// $options = get_option('tgentg_generator_options');
		// echo '<p>' . __( 'Generator with options', 'tgen-template-generator' ) . '</p>';
	}

	public function cleanupfilter_options_callback() {
		// $options = get_option('tgentg_cleanupfilter_options');
		echo '<p>' . htmlspecialchars(__('Clenup the template file from unnecesarry <script>, <meta> etc. tags that are necesarry for wordpress but not for TNEW templates.', 'tgen-template-generator')) . '</p>';
		echo '<p>' . __('Using your browser inspector tool: Inspect the template that you generated and take a look at all the tags that are adding overhead to yout TNEW template.', 'tgen-template-generator') . '</p>';
		echo '<p>' . __('If you need help with identifying Template overhead feel free to reach out at <a href="https://lehelmatyus.com/TGEN-template-help" target="_blank" >https://lehelmatyus.com/TGEN-template-help</a>', 'tgen-template-generator') . '</p>';
	}

	public function generate_action_options_callback() {
		$options = get_option('tgentg_generate_action_options');
		echo '<p>' . __('generate_action', 'tgen-template-generator') . '</p>';
	}


	/**
	 * Initializes the theme's Settings page by registering the Sections,
	 * Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_main_options() {

		//delete_option('tgentg_main_options');

		// If the theme options don't exist, create them.
		if (false == get_option('tgentg_main_options')) {
			$default_array = $this->default_main_options();
			add_option('tgentg_main_options', $default_array);
		}

		add_settings_section(
			'general_settings_section',			                       // ID used to identify this section and with which to register options
			__('Settings', 'tgen-template-generator'),		        // Title to be displayed on the administration page
			array($this, 'main_options_callback'),	    // Callback used to render the description of the section
			'tgentg_main_options'		                     // Page on which to add this section of options
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'tgentg_main_options',
			'tgentg_main_options'
		);
	} // end Ttg-demo_initialize_theme_options


	/**
	 * Initializes the theme's Generator by registering the Sections,
	 * Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_generator_options() {

		// delete_option('tgentg_generator_options');

		if (false == get_option('tgentg_generator_options')) {
			$default_array = $this->default_generator_options();
			update_option('tgentg_generator_options', $default_array);
		} // end if

		add_settings_section(
			'generator_section',    			                        // ID used to identify this section and with which to register options
			__('Generator Options', 'tgen-template-generator'),		// Title to be displayed on the administration page
			array($this, 'generator_options_callback'),	                    // Callback used to render the description of the section
			'tgentg_generator_options'		                            // Page on which to add this section of options
		);

		add_settings_field(
			'tgen_teamplate_page',
			__('Template Source Page', 'tgen-template-generator'),
			array($this, 'render_tgen_teamplate_page'),
			'tgentg_generator_options',
			'generator_section'
		);

		add_settings_field(
			'delimiter_container',
			__('', 'tgen-template-generator'),
			array($this, 'render_delimiter_container'),
			'tgentg_generator_options',
			'generator_section'
		);


		add_settings_field(
			'tgen_content_region_selector',
			__('Content Region Selector', 'tgen-template-generator'),
			array($this, 'render_tgen_content_region_selector'),
			'tgentg_generator_options',
			'generator_section'
		);

		add_settings_field(
			'tgen_container_wrap',						             				// ID used to identify the field throughout the theme
			__('Container wrap', 'tgen-template-generator'),   				// The label to the left of the option interface element
			array($this, 'render_tgen_container_wrap'),	             				// The name of the function responsible for rendering the option interface
			'tgentg_generator_options',	                        				// The page on which this option will be displayed
			'generator_section',    			                 				// The name of the section to which this field belongs
			array(								                 				// The array of arguments to pass to the callback. In this case, just a description.
				__('Wrap the TNEW content in a Bootstrap "container" class.', 'tgen-template-generator'),
			)
		);

		add_settings_field(
			'delimiter_css',
			__('', 'tgen-template-generator'),
			array($this, 'render_delimiter_css'),
			'tgentg_generator_options',
			'generator_section'
		);

		add_settings_field(
			'tgen_inline_css',
			__('Add Inline CSS', 'tgen-template-generator'),
			array($this, 'render_tgen_inline_css_wrap'),
			'tgentg_generator_options',
			'generator_section'
		);

		add_settings_field(
			'tgen_external_css_url',
			__('Add External CSS file URL', 'tgen-template-generator'),
			array($this, 'render_tgen_external_css_url_wrap'),
			'tgentg_generator_options',
			'generator_section'
		);

		add_settings_field(
			'delimiter_cleaner',
			__('', 'tgen-template-generator'),
			array($this, 'render_delimiter_cleaner'),
			'tgentg_generator_options',
			'generator_section'
		);

		add_settings_field(
			'tgen_clean_body_tag',						             				// ID used to identify the field throughout the theme
			__('Clean the HTML body tag', 'tgen-template-generator'),   				// The label to the left of the option interface element
			array($this, 'render_tgen_clean_body_tag'),	             				// The name of the function responsible for rendering the option interface
			'tgentg_generator_options',	                        				// The page on which this option will be displayed
			'generator_section',    			                 				// The name of the section to which this field belongs
			array(								                 				// The array of arguments to pass to the callback. In this case, just a description.
				__('Clean the body tag from css class names', 'tgen-template-generator'),
			)
		);

		add_settings_field(
			'tgen_remove_html_comments',						             				// ID used to identify the field throughout the theme
			__('Remove HTML comments', 'tgen-template-generator'),   				// The label to the left of the option interface element
			array($this, 'render_tgen_remove_html_comments'),	             				// The name of the function responsible for rendering the option interface
			'tgentg_generator_options',	                        				// The page on which this option will be displayed
			'generator_section',    			                 				// The name of the section to which this field belongs
			array(								                 				// The array of arguments to pass to the callback. In this case, just a description.
				__('Remove HTML comments', 'tgen-template-generator'),
			)
		);

		add_settings_field(
			'tgen_remove_inline_script_tags',						             				// ID used to identify the field throughout the theme
			__('Remove Inline Scripts', 'tgen-template-generator'),   				// The label to the left of the option interface element
			array($this, 'render_tgen_remove_inline_script_tags'),	             				// The name of the function responsible for rendering the option interface
			'tgentg_generator_options',	                        				// The page on which this option will be displayed
			'generator_section',    			                 				// The name of the section to which this field belongs
			array(								                 				// The array of arguments to pass to the callback. In this case, just a description.
				__('Remove Inline Scripts from page template', 'tgen-template-generator'),
			)
		);


		register_setting(
			'tgentg_generator_options',
			'tgentg_generator_options',
			array($this, 'sanitize_generator_options')
		);
	}

	/**
	 * Init Cleanup filter options
	 */
	public function initialize_cleanupfilter_options() {

		// delete_option('tgentg_cleanupfilter_options');

		if (false == get_option('tgentg_cleanupfilter_options')) {
			$default_array = $this->default_cleanupfilter_options();
			update_option('tgentg_cleanupfilter_options', $default_array);
		} // end if

		add_settings_section(
			'cleanupfilter_section',    			                        // ID used to identify this section and with which to register options
			__('Cleanup Filters', 'tgen-template-generator'),		// Title to be displayed on the administration page
			array($this, 'cleanupfilter_options_callback'),	                    // Callback used to render the description of the section
			'tgentg_cleanupfilter_options'		                            // Page on which to add this section of options
		);

		add_settings_field(
			'delimiter_header_filter',
			__('', 'tgen-template-generator'),
			array($this, 'render_delimiter_header_filter'),
			'tgentg_cleanupfilter_options',
			'cleanupfilter_section'
		);

		add_settings_field(
			'tgen_header_filter_option',
			__('Head Filter Option', 'tgen-template-generator'),
			array($this, 'render_tgen_header_filter_option'),
			'tgentg_cleanupfilter_options',
			'cleanupfilter_section'
		);

		add_settings_field(
			'tgen_header_filters',
			__('Head Filters', 'tgen-template-generator'),
			array($this, 'render_tgen_header_filters'),
			'tgentg_cleanupfilter_options',
			'cleanupfilter_section'
		);

		register_setting(
			'tgentg_cleanupfilter_options',
			'tgentg_cleanupfilter_options',
			array($this, 'sanitize_cleanupfilter_options')
		);
	}

	/**
	 * Form Elements
	 */
	public function render_tgen_teamplate_page($args) {
		$options = get_option('tgentg_generator_options');
	?>

		<select name='tgentg_generator_options[tgen_teamplate_page_id]'>
			<option value='' <?php selected($options['tgen_teamplate_page_id'], ''); ?>> - Select Page - </option>
			<?php
			if ($pages = get_pages()) {
				foreach ($pages as $page) {
					echo '<option value="' . esc_attr($page->ID) . '" ' . esc_attr(selected($page->ID, $options['tgen_teamplate_page_id'])) . '>' . esc_html($page->post_title) . '</option>';
				}
			}
			?>
		</select>

		<p class="description lhl-admin-description"> <?php echo __('Select the page that will be turned into the TNEW template.', 'tgen-template-generator'); ?> </p>
		<p class="description lhl-admin-description"> <?php echo __('Ideally you may want to create a page for this that you can customize with the menu and sidebars that you want on your TNEW website.', 'tgen-template-generator'); ?> </p>

<?php
	}

	public function render_tgen_header_filter_option($args) {

		$options = get_option('tgentg_cleanupfilter_options');

		$select_options_array = [
			'do_not_use' => [
				'value' => 'do_not_use',
				'label' => __('Ignore Filters, do nothing', 'tgen-template-generator'),
				'with_license_key_only' => false
			],
			'remove_filters_only' => [
				'value' => 'remove_filters_only',
				'label' => __('Remove tags that you specify in filters below', 'tgen-template-generator'),
				'with_license_key_only' => false
			],
			'keep_filters_only' => [
				'value' => 'keep_filters_only',
				'label' => __('Remove ALL tags from header EXCEPT filters below', 'tgen-template-generator'),
				'with_license_key_only' => true
			]
		];

		// Render Select box
		AdminForm::select__active_key_required(
			$this->license_key_valid,
			$options,
			'tgentg_cleanupfilter_options',
			'tgen_header_filter_option',
			$select_options_array,
			true
		);

		echo '<p class="description lhl-admin-description">' . esc_html__('Select what should happen to the tags that the filters will match. IE: remove them and kepp the remaining tags or keep them and remove the remaining tags', 'tgen-template-generator') . '</p>';
	}

	public function render_tgen_header_filters($args) {
		$options = get_option('tgentg_cleanupfilter_options');

		AdminForm::textarea__active_key_required(
			$this->license_key_valid,
			$options,
			'tgentg_cleanupfilter_options',
			'tgen_header_filters',
			false,
			10
		);
		echo '<p class="description lhl-admin-description">' . esc_html__('Add multiple filters in seperate rows, example:', 'tgen-template-generator') . '</p>';
		echo '<p class="description lhl-admin-description">' . htmlspecialchars(__('<link> : googleapis', 'tgen-template-generator')) . '</p>';
		echo '<p class="description lhl-admin-description">' . htmlspecialchars(__('<link> : fontawesome', 'tgen-template-generator')) . '</p>';
		echo '<p class="description lhl-admin-description">' . htmlspecialchars(__('<script> : bootstrapcdn', 'tgen-template-generator')) . '</p>';
		echo '<p class="description lhl-admin-description">' . htmlspecialchars(__('<meta> : WordPress', 'tgen-template-generator')) . '</p>';
		echo '<p class="description lhl-admin-description"><b>Example above will match</b>' . htmlspecialchars(__('', 'tgen-template-generator')) . '</p>';
		echo '<p class="description lhl-admin-description">' . esc_html__('<link> tags that have the word "googleapis" or "fontawesome" in them:', 'tgen-template-generator') . '</p>';
		echo '<p class="description lhl-admin-description">' . esc_html__('<script> tags that have the word "bootstrapcdn" in them:', 'tgen-template-generator') . '</p>';
		echo '<p class="description lhl-admin-description">' . esc_html__('<meta> tags that have the word "WordPress" in them:', 'tgen-template-generator') . '</p>';
	}

	public function render_tgen_clean_body_tag($args) {

		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_clean_body_tag";
		AdminForm::checkbox_single(
			$options,
			$option_name,
			$option_id,
			$args[0]
		);

		echo '<p class="description lhl-admin-description">' . esc_html__('Clean CSS classes form the main <body> tag. This may break CSS and Javascript if you rely on it.', 'tgen-template-generator') . '</p>';
	} // end render_tgen_clean_body_tag

	public function render_tgen_remove_html_comments($args) {

		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_remove_html_comments";
		AdminForm::checkbox_single(
			$options,
			$option_name,
			$option_id,
			$args[0]
		);

		echo '<p class="description lhl-admin-description">' . esc_html__('Remove HTML Comment markup from the WP page bofore generating a TNEW template from it.', 'tgen-template-generator') . '</p>';
	} // end render_tgen_remove_html_comments

	public function render_tgen_remove_inline_script_tags($args) {

		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_remove_inline_script_tags";
		AdminForm::checkbox_single(
			$options,
			$option_name,
			$option_id,
			$args[0]
		);

		echo '<p class="description lhl-admin-description">' . esc_html__('Remove inline <script>', 'tgen-template-generator') . '</p>';
	} // end render_tgen_remove_inline_script_tags


	public function render_delimiter_cleaner() {
		echo '<h3>' . __('Template Cleanup', 'tgen-template-generator') . '</h3>';
	}

	public function render_delimiter_css() {
		echo '<h3>' . __('Custom CSS', 'tgen-template-generator') . '</h3>';
	}

	public function render_delimiter_header_filter() {
		echo '<h4>' . __('Filter out unwanted tags from the HTML header', 'tgen-template-generator') . '</h4>';
	}

	public function render_delimiter_container() {
		echo '<h3>' . __('TNEW Content Plceholder', 'tgen-template-generator') . '</h3>';
	}

	public function render_tgen_container_wrap($args) {

		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_container_wrap";
		AdminForm::checkbox_single(
			$options,
			$option_name,
			$option_id,
			$args[0]
		);

		echo '<p class="description lhl-admin-description">' . esc_html__('This will give a maximum with container around the TNEW content. Check this if you do not want the TNEW content to go full width.', 'tgen-template-generator') . '</p>';
	} // end render_tgen_container_wrap


	public function render_tgen_inline_css_wrap() {

		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_inline_css";
		AdminForm::textarea(
			$options,
			$option_name,
			$option_id
		);

		echo '<p class="description lhl-admin-description">';
		echo esc_html__('Do not include <style> tags, only add plain CSS', 'tgen-template-generator');
		echo __('<br> Be sure to only put valid CSS here, use a validator such as https://codebeautify.org/cssvalidate', 'tgen-template-generator') . '</p>';
	}


	public function render_tgen_content_region_selector() {

		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_content_region_selector";
		AdminForm::text_input(
			$options,
			$option_name,
			$option_id
		);

		echo '<p class="description lhl-admin-description">' . __('You can use simply use valid CSS selectors to target a region ex: <code>#main</code> or <code>.content</code>', 'tgen-template-generator') . '</p>';
		echo '<p class="description lhl-admin-description">' . __('By default TGEN designates the WordPress "the_content" region to be used by the TNEW website.', 'tgen-template-generator') . '</p>';
		echo '<p class="description lhl-admin-description">' . __('If this does not fit your custom WordPress theme. You can use this field to custimize which part of your WordPress website should be used as a placeholder for the TNEW content. Giving you fine grain control on how to use TNEW.', 'tgen-template-generator') . '</p>';
	}

	public function render_tgen_external_css_url_wrap() {
		$options = get_option('tgentg_generator_options');
		$option_name = "tgentg_generator_options";
		$option_id = "tgen_external_css_url";
		AdminForm::text_input__active_key_required(
			$this->license_key_valid,
			$options,
			$option_name,
			$option_id
		);
		echo '<p class="description lhl-admin-description">' . __('Since this is consumed by TNEW you must use absolute path to the css file. <br> Example: <i>https://www.yourwebsite.com/assets/your-styles.css</i>', 'tgen-template-generator') . '</p>';
	}


	/**
	 * Sanitization callback for the Generator. Since each of the Generator are text inputs,
	 * this function loops through the incoming option and strips all tags and slashes from the value
	 * before serializing it.
	 *
	 * @params	$input	The unsanitized collection of options.
	 *
	 * @returns			The collection of sanitized values.
	 */
	public function sanitize_generator_options_links($input) {
		v_dump("sanitize_generator_options_links");
		// Define the array for the updated options
		$output = array();
		// Loop through each of the options sanitizing the data
		foreach ($input as $key => $val) {
			if (isset($input[$key])) {
				$output[$key] = esc_url_raw(strip_tags(stripslashes($input[$key])));
			} // end if
		} // end foreach
		// Return the new collection
		return apply_filters('sanitize_generator_options_links', $output, $input);
	}



	public function sanitize_generator_options($input) {
		v_dump("sanitize_generator_options");
		// Create our array for storing the validated options
		$output = array();
		// Loop through each of the incoming options
		foreach ($input as $key => $value) {
			// Check to see if the current option has a value. If so, process it.
			if (isset($input[$key])) {
				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = strip_tags(stripslashes($input[$key]));
			} // end if
		} // end foreach
		// Return the array processing any additional functions filtered by this action
		return apply_filters('sanitize_generator_options', $output, $input);
	}


	public function __generate_template_button() {
		echo '<div>';
		echo '<table class="form-table" role="presentation">';
		echo '<tbody>';
		echo '<tr>';
		echo '<th scope="row"></th>';
		echo '<td>';

		$repsoneHTML = "";
		$btn_title = __('Generate Template', 'tgen-template-generator');
		LHL_Admin_UI_TGEN::button(['tgentg_generate_button'], $btn_title, "tgenGenerateTemplate", $repsoneHTML);

		echo '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}
}
