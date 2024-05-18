<?php

require_once 'class-spam-interface.php';

abstract class BaseManager implements SpamInterface {
    protected $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function register_hooks() {
        // Common hooks can be registered here, if any.
    }

    public function display_page() {
        // To be implemented by each manager.
    }

    public function is_spam($data) {
        // To be implemented by each manager.
        return false;
    }

    protected function display_admin_error($e) {
        throw new Anty_Spam_Rekurencja_Exception($e->getMessage(), $e->getCode(), $e);
    }

    protected function log_activity($activity_type, $message) {
        LogManager::logActivity($activity_type, $message);
    }
}
