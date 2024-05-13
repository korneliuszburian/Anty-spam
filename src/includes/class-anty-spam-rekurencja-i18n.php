<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://korneliuszburian.github.io/
 * @since      1.0.0
 *
 * @package    Anty_Spam_Rekurencja
 * @subpackage Anty_Spam_Rekurencja/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Anty_Spam_Rekurencja
 * @subpackage Anty_Spam_Rekurencja/includes
 * @author     Korneliusz Burian <krnijuu@gmail.com>
 */
class Anty_Spam_Rekurencja_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'anty-spam-rekurencja',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
