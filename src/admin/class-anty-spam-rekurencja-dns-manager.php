<?php

require_once 'class-base-manager.php';
require_once 'class-log-manager.php'; // Include LogManager

class DNS_Block_Manager extends BaseManager {

    public function register_hooks() {
        add_action('init', [$this, 'check_dnsbl']);
    }

    public function display_page() {
        echo '<div class="wrap"><h1>DNS Block Settings</h1></div>';
    }

    public function is_spam($data) {
        $ip = $data['ip'] ?? '';
        return $this->is_ip_listed($ip);
    }

    public function check_dnsbl() {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        try {
            if ($blocked_by = $this->is_ip_listed($user_ip)) {
                $message = "Blocked IP: $user_ip by $blocked_by";
                LogManager::logActivity('DNS Block', $message);
                wp_die("Your IP ($user_ip) is listed in $blocked_by. Access denied.");
            }
        } catch (Exception $e) {
            LogManager::logError($e->getMessage());
        }
    }

    private function is_ip_listed($ip) {
        // 'sbl.spamhaus.org', 'xbl.spamhaus.org'
        $dnsbl_lookup = [ 'psbl.surriel.com', 'bl.spamcop.net', 'ix.dnsbl.manitu.net' ];
        foreach ($dnsbl_lookup as $host) {
            $lookup = implode('.', array_reverse(explode('.', $ip))) . '.' . $host;
            if (checkdnsrr($lookup . '.', 'A')) {
                return $host;
            }
        }
        return false;
    }
}