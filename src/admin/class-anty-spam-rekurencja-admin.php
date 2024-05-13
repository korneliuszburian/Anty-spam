<?php

class Anty_Spam_Rekurencja_Admin
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->register_hooks();
    }

    private function register_hooks()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_actions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/anty-spam-rekurencja-admin.css', [], $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/anty-spam-rekurencja-admin.js', ['jquery'], $this->version, false);
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Anty Spam Options',
            'Anty Spam Options',
            'manage_options',
            'anty-spam-options',
            [$this, 'display_options_page'],
            'dashicons-shield-alt'
        );

        $this->add_submenu_pages();
    }

    private function add_submenu_pages()
    {
        add_submenu_page('anty-spam-options', 'Submitted Forms', 'Submitted Forms', 'manage_options', 'submitted-forms', [$this, 'display_submitted_forms_page']);
        add_submenu_page('anty-spam-options', 'Blocked IPs', 'Blocked IPs', 'manage_options', 'blocked-ips', [$this, 'display_blocked_ips_page']);
        add_submenu_page('anty-spam-options', 'Blocked Words', 'Blocked Words', 'manage_options', 'blocked-words', [$this, 'display_blocked_words_page']);
    }

    public function display_options_page()
    {
        ?>
        <div class="wrap">
            <h1>Anty Spam Options</h1>
            <p>Welcome to the Anty Spam Options page. Here you can configure various settings related to spam protection.</p>
        </div>
        <?php
    }

    public function display_submitted_forms_page()
    {
        echo $this->render_submitted_forms();
    }

    public function display_blocked_ips_page()
    {
        echo $this->render_blocked_ips();
    }

    public function display_blocked_words_page()
    {
        echo $this->render_blocked_words();
    }

    public function handle_actions()
    {
        $this->handle_ip_actions();
        $this->handle_word_actions();
    }

    private function handle_ip_actions()
    {
        $action = $_GET['action'] ?? '';
        $ip_address = sanitize_text_field($_GET['ip_address'] ?? '');

        if (!empty($ip_address)) {
            if ('block' === $action) {
                $this->block_ip($ip_address);
            } elseif ('unblock' === $action) {
                $this->unblock_ip($ip_address);
            }
        }
    }

    private function handle_word_actions()
    {
        if (isset($_POST['submit']) && check_admin_referer('update-blocked-words', '_wpnonce_update_blocked_words')) {
            $new_word = sanitize_text_field($_POST['new_word'] ?? '');
            if (!empty($new_word)) {
                $this->add_blocked_word($new_word);
            }
        }
    }

    private function render_submitted_forms()
    {
        $submitted_forms = $this->get_submitted_forms();
        ob_start();
        ?>
        <div class="wrap">
            <h1>Submitted Forms</h1>
            <?php echo $submitted_forms; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_blocked_ips()
    {
        $blocked_ips = $this->get_blocked_ips();
        ob_start();
        ?>
        <div class="wrap">
            <h1>Blocked IPs</h1>
            <p>Here you can view and manage the list of IP addresses that have been blocked from submitting forms.</p>
            <?php echo $blocked_ips; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_blocked_words()
    {
        $blocked_words = $this->get_blocked_words(); 
        ob_start();
        ?>
        <div class="wrap">
            <h1>Blocked Words</h1>
            <p>Manage the list of words that trigger spam detection. Add words manually or by uploading a .txt file.</p>
            
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="new_word" placeholder="Enter Word">
                <?php wp_nonce_field('update-blocked-words', '_wpnonce_update_blocked_words'); ?>
                <input type="submit" name="submit" value="Add Word">
                
                <input type="file" name="words_file" accept=".txt">
                <input type="submit" name="upload_file" value="Upload File">
            </form>
            
            <?php echo $blocked_words; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    

    private function get_submitted_forms()
    {
        global $wpdb;
        $log_table_name = $wpdb->prefix . 'cf7_email_logs';
        $blocked_ips = $this->get_blocked_ips_array();

        $submitted_forms_html = '<table class="wp-list-table widefat fixed striped">';
        $submitted_forms_html .= '<thead><tr><th>ID</th><th>Time</th><th>Sender Email</th><th>Sender IP</th><th>Is Spam?</th><th>Form Data</th><th>Action</th></tr></thead><tbody>';

        foreach ($wpdb->get_results("SELECT * FROM $log_table_name ORDER BY time DESC") as $row) {
            $is_blocked = in_array($row->sender_ip, $blocked_ips);
            $submitted_forms_html .= '<tr><td>' . esc_html($row->id) . '</td><td>' . esc_html($row->time) . '</td><td>' . esc_html($row->sender_email) . '</td><td>' . esc_html($row->sender_ip) . '</td>';
            $submitted_forms_html .= '<td>' . ($is_blocked ? 'Yes' : 'No') . '</td><td>' . esc_html($row->form_data) . '</td>';
            $submitted_forms_html .= '<td><a href="' . esc_url(admin_url('admin.php?page=submitted-forms&action=' . ($is_blocked ? 'unblock' : 'block') . '&ip_address=' . urlencode($row->sender_ip))) . '">' . ($is_blocked ? 'Unblock IP' : 'Block IP') . '</a></td></tr>';
        }

        $submitted_forms_html .= '</tbody></table>';
        return $submitted_forms_html;
    }

    private function get_blocked_ips()
    {
        global $wpdb;
        $blocked_ips = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cf7_blocked_ips ORDER BY block_time DESC");
        $blocked_ips_html = '<table class="wp-list-table widefat fixed striped">';
        $blocked_ips_html .= '<thead><tr><th>IP Address</th><th>Block Time</th><th>Action</th></tr></thead><tbody>';

        foreach ($blocked_ips as $ip) {
            $blocked_ips_html .= '<tr><td>' . esc_html($ip->ip_address) . '</td><td>' . esc_html($ip->block_time) . '</td>';
            $blocked_ips_html .= '<td><a href="' . esc_url(admin_url('admin.php?page=blocked-ips&action=unblock&ip_address=' . urlencode($ip->ip_address))) . '">Unblock</a></td></tr>';
        }

        $blocked_ips_html .= '</tbody></table>';
        return $blocked_ips_html;
    }

    private function get_blocked_words()
    {
        global $wpdb;
        $blocked_words = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cf7_blocked_words ORDER BY added_time DESC");
        $blocked_words_html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Word</th><th>Added Time</th></tr></thead><tbody>';

        foreach ($blocked_words as $word) {
            $blocked_words_html .= '<tr><td>' . esc_html($word->word) . '</td><td>' . esc_html($word->added_time) . '</td></tr>';
        }

        $blocked_words_html .= '</tbody></table>';
        return $blocked_words_html;
    }

    private function block_ip($ip_address)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_blocked_ips';
        if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE ip_address = %s", $ip_address))) {
            $wpdb->insert($table_name, ['ip_address' => $ip_address, 'block_time' => current_time('mysql')], ['%s', '%s']);
        }
    }

    private function unblock_ip($ip_address)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_blocked_ips';
        $wpdb->delete($table_name, ['ip_address' => $ip_address], ['%s']);
    }

    private function add_blocked_word($word)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_blocked_words';
        $wpdb->insert($table_name, ['word' => $word, 'added_time' => current_time('mysql')], ['%s', '%s']);
    }

    private function get_blocked_ips_array()
    {
        global $wpdb;
        return $wpdb->get_col("SELECT ip_address FROM {$wpdb->prefix}cf7_blocked_ips");
    }
}
