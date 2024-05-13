<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://korneliuszburian.github.io/
 * @since      1.0.0
 *
 * @package    Anti_Spam
 * @subpackage Anti_Spam/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Anti_Spam
 * @subpackage Anti_Spam/admin
 * @author     Korneliusz Burian <krnijuu@gmail.com>
 */
class Anti_Spam_Admin {

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
	public function __construct( $plugin_name, $version ) {

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
		 * defined in Anti_Spam_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anti_Spam_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/anti-spam-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anti_Spam_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anti_Spam_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/anti-spam-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
				'CF7 Anti-Spam', // Page title
				'CF7 Anti-Spam', // Menu title
				'manage_options', // Capability
				$this->plugin_name, // Menu slug
				array($this, 'display_plugin_setup_page'), // Function
				'dashicons-shield-alt', // Icon
				26 // Position
		);
}

public function display_plugin_setup_page() {
		include_once('partials/anti-spam-admin-display.php');
}

}
