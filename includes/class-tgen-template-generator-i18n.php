<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.lehelmatyus.com
 * @since      1.0.0
 *
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/includes
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Tgen_Template_Generator_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'tgen-template-generator',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
