<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://korneliuszburian.github.io/
 * @since             1.0.0
 * @package           Anti_Spam_Rekurencja
 *
 * @wordpress-plugin
 * Plugin Name:       Anti-spam - Rekurencja
 * Plugin URI:        https://anti-spam.rekurencja.com/
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Korneliusz Burian
 * Author URI:        https://korneliuszburian.github.io//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       anti-spam-rekurencja
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ANTI_SPAM_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-anti-spam-activator.php
 */
function activate_anti_spam() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-anti-spam-activator.php';
	Anti_Spam_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-anti-spam-deactivator.php
 */
function deactivate_anti_spam() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-anti-spam-deactivator.php';
	Anti_Spam_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_anti_spam' );
register_deactivation_hook( __FILE__, 'deactivate_anti_spam' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-anti-spam.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_anti_spam() {

	$plugin = new Anti_Spam();
	$plugin->run();

}
run_anti_spam();
