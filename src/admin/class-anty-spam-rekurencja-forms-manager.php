<?php

require_once 'class-base-manager.php';
require_once 'class-anty-spam-rekurencja-ip-manager.php'; // Include the IP manager
require_once 'class-anty-spam-rekurencja-word-manager.php'; // Include the Word manager

class Anty_Spam_Rekurencja_Forms_Manager extends BaseManager {
    private $ip_manager;
    private $word_manager;

    public function __construct() {
        parent::__construct();
        $this->ip_manager = new Anty_Spam_Rekurencja_IP_Manager(); // Instantiate the IP manager
        $this->word_manager = new Anty_Spam_Rekurencja_Word_Manager(); // Instantiate the Word manager
    }

    public function register_hooks() {
        parent::register_hooks();
        add_action('admin_init', [$this, 'handle_actions']);
    }

    public function display_page() {
        try {
            echo $this->display_submitted_forms_page();
        } catch (Anty_Spam_Rekurencja_Exception $e) {
            $this->display_admin_error($e);
        }
    }

    private function display_submitted_forms_page() {
        $submitted_forms_html = '<div class="wrap">';
        $submitted_forms_html .= '<h1>Submitted Forms</h1>';
        $submitted_forms_html .= '<p>Here you can manage and review all submitted forms.</p>';
        $submitted_forms_html .= $this->render_submitted_forms();
        $submitted_forms_html .= '</div>';

        return $submitted_forms_html;
    }

    private function render_submitted_forms() {
        $log_table_name = $this->wpdb->prefix . 'cf7_email_logs';
        $forms_data = $this->wpdb->get_results("SELECT * FROM $log_table_name ORDER BY time DESC");
        $grouped_data = $this->group_forms_by_sender($forms_data);

        $submitted_forms_html = '<table class="wp-list-table widefat fixed striped">';
        $submitted_forms_html .= '<thead><tr><th>ID</th><th>Time</th><th>Sender Email</th><th>Sender IP</th><th>Is Spam?</th><th>Form Data</th><th>Action</th></tr></thead><tbody>';

        foreach ($grouped_data as $email => $forms) {
            foreach ($forms as $row) {
                $is_spam = $this->is_spam([
                    'sender_email' => $row->sender_email,
                    'sender_ip' => $row->sender_ip,
                    'form_data' => $row->form_data
                ]);
                $is_ip_blocked = $this->ip_manager->is_ip_blocked($row->sender_ip);
                $submitted_forms_html .= '<tr><td>' . esc_html($row->id) . '</td><td>' . esc_html($row->time) . '</td><td>' . esc_html($row->sender_email) . '</td><td>' . esc_html($row->sender_ip) . '</td>';
                $submitted_forms_html .= '<td>' . ($is_spam ? 'Yes' : 'No') . '</td><td>' . esc_html($row->form_data) . '</td>';
                $submitted_forms_html .= '<td><a href="' . esc_url(admin_url('admin.php?page=forms-manager&action=' . ($is_ip_blocked ? 'unblock' : 'block') . '&ip_address=' . urlencode($row->sender_ip))) . '">' . ($is_ip_blocked ? 'Unblock IP' : 'Block IP') . '</a></td></tr>';
            }
        }

        $submitted_forms_html .= '</tbody></table>';

        return $submitted_forms_html;
    }

    private function group_forms_by_sender($forms_data) {
        $grouped_data = [];

        foreach ($forms_data as $form) {
            $email = $form->sender_email;
            if (!isset($grouped_data[$email])) {
                $grouped_data[$email] = [];
            }
            $grouped_data[$email][] = $form;
        }

        return $grouped_data;
    }

    public function handle_actions() {
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
        $is_ip_blocked = $this->ip_manager->is_ip_blocked($data['sender_ip']);
        $contains_spam_words = $this->word_manager->is_spam($data);

        $this->log_activity('is_spam check', 'IP Blocked: ' . ($is_ip_blocked ? 'true' : 'false') . ', Contains Spam Words: ' . ($contains_spam_words ? 'true' : 'false'));

        return $is_ip_blocked || $contains_spam_words;
    }
}
