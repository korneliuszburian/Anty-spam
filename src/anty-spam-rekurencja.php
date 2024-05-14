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
 * @package           Anty_Spam_Rekurencja
 *
 * @wordpress-plugin
 * Plugin Name:       Anty-Spam by Rekurencja
 * Plugin URI:        https://anti-spam.rekurencja.com/
 * Description:       Antyspamowe narzędzie, do zatrzymania złośliwego spamu.
 * Version:           1.0.0
 * Author:            Korneliusz Burian
 * Author URI:        https://korneliuszburian.github.io//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       anty-spam-rekurencja
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
define( 'ANTY_SPAM_REKURENCJA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-anty-spam-rekurencja-activator.php
 */
function activate_anty_spam_rekurencja() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-anty-spam-rekurencja-activator.php';
	Anty_Spam_Rekurencja_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-anty-spam-rekurencja-deactivator.php
 */
function deactivate_anty_spam_rekurencja() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-anty-spam-rekurencja-deactivator.php';
	Anty_Spam_Rekurencja_Deactivator::deactivate();
}

/**
 * The code that runs during when form is being submitted.
 */
function log_cf7_submission($contact_form) {
	$submission = WPCF7_Submission::get_instance();
	if ($submission) {
			$data = $submission->get_posted_data();
			$sender_email = $data['your-email'];
			$sender_ip = $_SERVER['REMOTE_ADDR'];
			$form_data = maybe_serialize($data);
			global $wpdb;

			if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}cf7_blocked_ips WHERE ip_address = %s", $sender_ip))) {
					add_filter('wpcf7_skip_mail','__return_true');
					return;
			}

			$is_spam = false;
			$table_name = $wpdb->prefix . 'cf7_email_logs';
			$wpdb->insert(
					$table_name,
					array(
							'time' => current_time('mysql'),
							'sender_email' => $sender_email,
							'sender_ip' => $sender_ip,
							'is_spam' => $is_spam,
							'form_data' => $form_data
					),
					array('%s', '%s', '%s', '%d', '%s')
			);
	}
}
add_action('wpcf7_before_send_mail', 'log_cf7_submission');

register_activation_hook( __FILE__, 'activate_anty_spam_rekurencja' );
register_deactivation_hook( __FILE__, 'deactivate_anty_spam_rekurencja' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-anty-spam-rekurencja.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_anty_spam_rekurencja() {

	$plugin = new Anty_Spam_Rekurencja();
	$plugin->run();

}
run_anty_spam_rekurencja();
