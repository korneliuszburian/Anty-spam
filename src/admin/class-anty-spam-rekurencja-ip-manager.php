<?php
require_once 'class-base-manager.php';

class Anty_Spam_Rekurencja_IP_Manager extends BaseManager {
    public function register_hooks() {
        parent::register_hooks();
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles_scripts']);
        add_action('admin_init', [$this, 'handle_ip_actions']);
    }

    public function enqueue_styles_scripts() {
        wp_enqueue_style('ip_manager_style', plugin_dir_url(__FILE__) . 'css/ip_manager.css', [], '1.0', 'all');
        wp_enqueue_script('ip_manager_script', plugin_dir_url(__FILE__) . 'js/ip_manager.js', ['jquery'], '1.0', true);
    }

    public function display_page() {
        try {
            echo $this->render_blocked_ips();
        } catch (Anty_Spam_Rekurencja_Exception $e) {
            $this->display_admin_error($e);
        }
    }

    private function render_blocked_ips() {
        $blocked_ips_html = '<div class="wrap"><h1>Blocked IPs</h1>';
        $blocked_ips_html .= '<table class="wp-list-table widefat fixed striped">';
        $blocked_ips_html .= '<thead><tr><th>IP Address</th><th>Block Time</th><th>Action</th></tr></thead><tbody>';

        $blocked_ips = $this->get_blocked_ips();
        
        foreach ($blocked_ips as $ip) {
            $blocked_ips_html .= '<tr><td>' . esc_html($ip->ip_address) . '</td>';
            $blocked_ips_html .= '<td>' . esc_html($ip->block_time) . '</td>';
            $blocked_ips_html .= '<td><a href="' . esc_url(admin_url('admin.php?page=ip-manager&action=unblock&ip_address=' . urlencode($ip->ip_address))) . '" class="button">Unblock</a></td></tr>';
        }

        $blocked_ips_html .= '</tbody></table></div>';
        return $blocked_ips_html;
    }

    public function get_blocked_ips() {
        return $this->wpdb->get_results("SELECT ip_address, block_time FROM {$this->wpdb->prefix}cf7_blocked_ips ORDER BY block_time DESC");
    }

    public function block_ip($ip_address) {
        try {
            if (!$this->is_ip_blocked($ip_address)) {
                $this->wpdb->insert(
                    $this->wpdb->prefix . 'cf7_blocked_ips',
                    ['ip_address' => $ip_address, 'block_time' => current_time('mysql')],
                    ['%s', '%s']
                );
                $this->log_activity('Block IP', "Blocked IP Address: $ip_address");
            }
        } catch (Anty_Spam_Rekurencja_Exception $e) {
            $this->display_admin_error($e);
        }
    }

    public function unblock_ip($ip_address) {
        try {
            $this->wpdb->delete(
                $this->wpdb->prefix . 'cf7_blocked_ips',
                ['ip_address' => $ip_address],
                ['%s']
            );
            $this->log_activity('Unblock IP', "Unblocked IP Address: $ip_address");
        } catch (Anty_Spam_Rekurencja_Exception $e) {
            $this->display_admin_error($e);
        }
    }

    public function is_ip_blocked($ip_address) {
        $result = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}cf7_blocked_ips WHERE ip_address = %s",
            $ip_address
        ));
        return (int) $result > 0;
    }

    public function handle_ip_actions() {
        if (isset($_GET['action']) && isset($_GET['ip_address'])) {
            $action = sanitize_text_field($_GET['action']);
            $ip_address = sanitize_text_field($_GET['ip_address']);
            if ($action === 'block') {
                $this->block_ip($ip_address);
            } elseif ($action === 'unblock') {
                $this->unblock_ip($ip_address);
            }
            
            wp_redirect(admin_url('admin.php?page=forms-manager'));
            exit;
        }
    }

    public function is_spam($data) {
        return $this->is_ip_blocked($data['sender_ip']);
    }
}
