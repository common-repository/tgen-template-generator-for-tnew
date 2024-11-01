<?php

use WpLHLAdminUi\LicenseKeys\LicenseKeyAdminGUI;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.lehelmatyus.com
 * @since      1.0.0
 *
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/admin
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Tgen_Template_Generator_Admin {

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
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tgen_Template_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tgen_Template_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tgen-template-generator-admin.css', array(), $this->version, 'all');
		$adnimUI = new WpLHLAdminUi("tgen");
		$adnimUI->wp_enqueue_style();
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tgen-template-generator-admin.js', array('jquery'), $this->version, false);


		if ($hook == 'settings_page_tgentg_options') {
			/**
			 * Enqueue the License Key JS
			 */
			LicenseKeyAdminGUI::wp_enqueue_license_js();

			/**
			 * Pass an OBJ to our Script
			 */
			wp_localize_script($this->plugin_name, 'tgentgApiSettings', array(
				'root' => esc_url_raw(rest_url()),
				'tgentg_nonce' => wp_create_nonce('wp_rest')
			));
			/**
			 * Pass an OBJ to our Script
			 */
			wp_localize_script($this->plugin_name, 'LbrtyBoxApiSettings', array(
				'root' => esc_url_raw(rest_url()),
				'lbrty_nonce' => wp_create_nonce('wp_rest'),
				'ActivateKeyApiEndpoint' => 'tgen-template-generator/v1/action/activatekey',
				'DeActivateKeyApiEndpoint' => 'tgen-template-generator/v1/action/deactivatekey',

			));
		}
	}
}
