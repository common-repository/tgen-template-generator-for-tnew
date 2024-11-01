<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.lehelmatyus.com
 * @since      1.0.0
 *
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/includes
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Tgen_Template_Generator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tgen_Template_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('TGEN_TEMPLATE_GENERATOR_VERSION')) {
			$this->version = TGEN_TEMPLATE_GENERATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tgen-template-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tgen_Template_Generator_Loader. Orchestrates the hooks of the plugin.
	 * - Tgen_Template_Generator_i18n. Defines internationalization functionality.
	 * - Tgen_Template_Generator_Admin. Defines all hooks for the admin area.
	 * - Tgen_Template_Generator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once __DIR__ . '../../vendor/autoload.php';

		/**
		 * The class responsible for admin ui helper functions
		 */
		if (!class_exists('LHL_Admin_UI_TGEN')) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lhl-admin/class-lhl-admin-ui.php';
		}

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tgen-license-data-provider.php';


		/**
		 * Utility class for removing head tags
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'utils/HeadTagRemoverUtility.php';

		/**
		 * Utility class for removing Content Tags
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'utils/ContentTagRemoverUtility.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'utils/TagExtractor.php';

		/**
		 * The class responsible for the options model
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'models/parser-options-model.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'models/filter-model.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tgen-template-generator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tgen-template-generator-i18n.php';

		/**
		 * The class responsible for HTML parsing functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tgen-template-generator-parser.php';

		/**
		 * The class responsible for the REST Api
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tgen-template-generator-restapi.php';


		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tgen-template-generator-admin.php';


		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tgen-template-generator-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-tgen-template-generator-public.php';

		$this->loader = new Tgen_Template_Generator_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tgen_Template_Generator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tgen_Template_Generator_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tgen_Template_Generator_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		$plugin_settings = new TGENTG_Admin_Settings($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('admin_menu', $plugin_settings, 'setup_plugin_options_menu');
		$this->loader->add_action('admin_init', $plugin_settings, 'initialize_main_options');
		$this->loader->add_action('admin_init', $plugin_settings, 'initialize_generator_options');
		$this->loader->add_action('admin_init', $plugin_settings, 'initialize_cleanupfilter_options');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tgen_Template_Generator_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');


		/**
		 * Content Wrapper
		 */
		// Wrap content for later replacement with TNEW token
		$this->loader->add_filter('the_content', $plugin_public, 'content_wrapper');



		/**
		 * Res APi stuff
		 */

		$plugin_restapi = new TGEN_template_Generator_Rest_API($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('rest_api_init', $plugin_restapi, 'register_routes');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tgen_Template_Generator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
