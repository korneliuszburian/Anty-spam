<?php

require_once 'class-base-manager.php';
require_once 'class-anty-spam-rekurencja-ip-manager.php'; 
require_once 'class-anty-spam-rekurencja-word-manager.php'; 
require_once 'class-log-manager.php'; 

class Anty_Spam_Rekurencja_Forms_Manager extends BaseManager {
    private $ip_manager;
    private $word_manager;

    public function __construct() {
        parent::__construct();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_user_scripts']);
        $this->ip_manager = new Anty_Spam_Rekurencja_IP_Manager();
        $this->word_manager = new Anty_Spam_Rekurencja_Word_Manager();
    }

    public function register_hooks() {
        parent::register_hooks();
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'handle_actions']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
        wp_enqueue_script('chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns', ['chart-js'], null, true);
        wp_enqueue_script('popup-script', plugins_url('/js/popup.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_style('popup-style', plugins_url('/css/popup.css', __FILE__));
    }

    public function enqueue_user_scripts() {
        wp_enqueue_script('form-interaction', plugins_url('/js/form-interaction.js', __FILE__), [], null, true);
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
        $submitted_forms_html .= '<form id="spam-check-form">';
        $submitted_forms_html .= $this->render_chart();
        $submitted_forms_html .= $this->render_submitted_forms();
        $submitted_forms_html .= '</form>';
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
                $form_data = $this->parse_form_data($row->form_data);
                $submitted_forms_html .= '<tr><td>' . esc_html($row->id) . '</td><td>' . esc_html($row->time) . '</td><td>' . esc_html($row->sender_email) . '</td><td>' . esc_html($row->sender_ip) . '</td>';
                $submitted_forms_html .= '<td>' . ($is_spam ? 'Yes' : 'No') . '</td><td><button class="view-message" data-message="' . esc_html($form_data) . '">View Message</button></td>';
                $submitted_forms_html .= '<td><a href="' . esc_url(admin_url('admin.php?page=forms-manager&action=' . ($is_ip_blocked ? 'unblock' : 'block') . '&ip_address=' . urlencode($row->sender_ip))) . '">' . ($is_ip_blocked ? 'Unblock IP' : 'Block IP') . '</a></td></tr>';
            }
        }

        $submitted_forms_html .= '</tbody></table>';
        $submitted_forms_html .= '<div id="message-popup" class="message-popup"><div class="message-popup-content"><span class="close-popup">&times;</span><pre class="popup-message"></pre></div></div>';

        return $submitted_forms_html;
    }

    private function parse_form_data($form_data) {
        $data = maybe_unserialize($form_data);
        if (is_array($data)) {
            $output = "";
            foreach ($data as $key => $value) {
                $output .= ucfirst(str_replace("-", " ", $key)) . ": " . $value . "\n";
            }
            return $output;
        }
        return $form_data;
    }

    private function render_chart() {
        $log_table_name = $this->wpdb->prefix . 'cf7_email_logs';
        $forms_data = $this->wpdb->get_results("SELECT * FROM $log_table_name ORDER BY time DESC");
        $grouped_data = $this->group_forms_by_date($forms_data);

        $dates = [];
        $counts = [];

        foreach ($grouped_data as $year => $months) {
            foreach ($months as $month => $days) {
                foreach ($days as $day => $emails) {
                    $date = $year . '-' . $month . '-' . $day;
                    $dates[] = $date;
                    $counts[] = count($emails);
                }
            }
        }

        $chart_html = '<canvas id="emailsChart" width="400" height="200"></canvas>';
        $chart_html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var ctx = document.getElementById("emailsChart").getContext("2d");
                var emailsChart = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: ' . json_encode($dates) . ',
                        datasets: [{
                            label: "Emails Sent",
                            data: ' . json_encode($counts) . ',
                            borderColor: "rgba(75, 192, 192, 1)",
                            backgroundColor: "rgba(75, 192, 192, 0.2)",
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                type: "time",
                                time: {
                                    unit: "day"
                                },
                                title: {
                                    display: true,
                                    text: "Date"
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Emails Count"
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top"
                            },
                            tooltip: {
                                enabled: true
                            }
                        }
                    }
                });
            });
        </script>';

        return $chart_html;
    }

    private function group_forms_by_date($forms_data) {
        $grouped_data = [];

        foreach ($forms_data as $form) {
            $timestamp = strtotime($form->time);
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            $day = date('d', $timestamp);

            if (!isset($grouped_data[$year])) {
                $grouped_data[$year] = [];
            }
            if (!isset($grouped_data[$year][$month])) {
                $grouped_data[$year][$month] = [];
            }
            if (!isset($grouped_data[$year][$month][$day])) {
                $grouped_data[$year][$month][$day] = [];
            }

            $grouped_data[$year][$month][$day][] = $form;
        }

        return $grouped_data;
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
                LogManager::logActivity('Action Handled', 'IP blocked: ' . $ip_address);
            } elseif ($action === 'unblock') {
                $this->unblock_ip($ip_address);
                LogManager::logActivity('Action Handled', 'IP unblocked: ' . $ip_address);
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

    private function block_ip($ip_address) {
        $this->ip_manager->block_ip($ip_address);
        LogManager::logActivity('Block IP', 'IP address blocked: ' . $ip_address);
    }

    private function unblock_ip($ip_address) {
        $this->ip_manager->unblock_ip($ip_address);
        LogManager::logActivity('Unblock IP', 'IP address unblocked: ' . $ip_address);
    }

    public function display_admin_error($e) {
        echo '<div class="error"><p>' . esc_html($e->getMessage()) . '</p></div>';
        LogManager::logError($e->getMessage());
    }
}