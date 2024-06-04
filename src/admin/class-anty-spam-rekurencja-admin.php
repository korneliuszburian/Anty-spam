<?php
require_once 'class-spam-interface.php';
require_once 'class-anty-spam-rekurencja-ip-manager.php';
require_once 'class-anty-spam-rekurencja-word-manager.php';
require_once 'class-anty-spam-rekurencja-forms-manager.php';
require_once 'class-anty-spam-rekurencja-honeypot-manager.php';
require_once 'class-anty-spam-rekurencja-dns-manager.php';
require_once 'class-anty-spam-rekurencja-validation-manager.php';
require_once 'class-log-manager.php';

class Anty_Spam_Rekurencja_Admin {
    private $plugin_name;
    private $version;
    private $managers;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->managers = [
            'IP Manager' => new Anty_Spam_Rekurencja_IP_Manager(),
            'Word Manager' => new Anty_Spam_Rekurencja_Word_Manager(),
            'Forms Manager' => new Anty_Spam_Rekurencja_Forms_Manager(),
            'Honeypot Manager' => new Anty_Spam_Rekurencja_Honeypot_Manager(),
            'DNS Block Manager' => new DNS_Block_Manager(),
            'Validation Manager' => new Validation_Manager(),
        ];
        $this->register_hooks();
    }

    private function register_hooks() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        foreach ($this->managers as $manager) {
            $manager->register_hooks();
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/anty-spam-rekurencja-admin.css', [], $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/anty-spam-rekurencja-admin.js', ['jquery'], $this->version, false);
    }

    public function add_admin_menu() {
        add_menu_page('Anty Spam Options', 'Anty Spam Options', 'manage_options', 'anty-spam-options', [$this, 'display_options_page'], 'dashicons-shield-alt');
        foreach ($this->managers as $friendly_name => $manager) {
            add_submenu_page('anty-spam-options', $friendly_name, $friendly_name, 'manage_options', strtolower(str_replace(' ', '-', $friendly_name)), [$manager, 'display_page']);
        }
        add_submenu_page('anty-spam-options', 'Error Log', 'Error Log', 'manage_options', 'error-log', ['LogManager', 'display_error_log_page']);
        add_submenu_page('anty-spam-options', 'Activity Log', 'Activity Log', 'manage_options', 'activity-log', ['LogManager', 'displayActivityLogPage']);
    }

    public function display_options_page() {
        echo '<div class="wrap"><h1>Anty Spam Options</h1><p>Welcome to the Anty Spam Configuration Page.</p></div>';
    }
}