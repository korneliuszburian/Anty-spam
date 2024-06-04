<?php

class LogManager {
    const ERROR_LOG_PATH = 'logs/error_log.txt';
    const ACTIVITY_LOG_PATH = 'logs/activity_log.txt';

    public static function logError($message) {
        $log_message = sprintf("[%s] Error: %s\n", date("Y-m-d H:i:s"), $message);
        error_log($log_message, 3, plugin_dir_path(__FILE__) . self::ERROR_LOG_PATH);
    }

    public static function logActivity($activity_type, $message) {
        $log_message = sprintf("[%s] %s: %s\n", date("Y-m-d H:i:s"), $activity_type, $message);
        error_log($log_message, 3, plugin_dir_path(__FILE__) . self::ACTIVITY_LOG_PATH);
    }

    public static function displayActivityLogPage() {
        if (isset($_GET['action']) && $_GET['action'] === 'clean-up') {
            self::cleanUpActivityLog();
            wp_redirect(admin_url('admin.php?page=activity-log'));
            exit;
        }
        $log_path = plugin_dir_path(__FILE__) . self::ACTIVITY_LOG_PATH;
        $log_contents = file_exists($log_path) ? file_get_contents($log_path) : 'No activity logged.';
        $clean_up_url = admin_url('admin.php?page=activity-log&action=clean-up');

        echo '<div class="wrap"><h1>Activity Log</h1>';
        echo '<div style="max-height: 400px; overflow-y: auto;"><pre>' . esc_html($log_contents) . '</pre></div>';
        echo '<p><a href="' . esc_url($clean_up_url) . '" class="button">Clean up activity log</a></p>';
        echo '</div>';
    }

    public static function cleanUpActivityLog() {
        $log_path = plugin_dir_path(__FILE__) . self::ACTIVITY_LOG_PATH;
        if (file_exists($log_path)) {
            file_put_contents($log_path, '');
        }
    }

    public static function display_error_log_page() {
        $log_path = plugin_dir_path(__FILE__) . self::ERROR_LOG_PATH;
        $log_contents = file_exists($log_path) ? file_get_contents($log_path) : 'No errors logged.';

        echo '<div class="wrap"><h1>Error Log</h1>';
        echo '<div style="max-height: 400px; overflow-y: auto;"><pre>' . esc_html($log_contents) . '</pre></div>';
        echo '</div>';
    }
}

class Anty_Spam_Rekurencja_Exception extends Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        LogManager::logError(sprintf("Exception: %s in %s on line %d\nStack trace:\n%s",
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString()
        ));
    }
}