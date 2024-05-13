<?php
class IPBasedSpamDetector extends SpamDetector {
    public function isSpam($data): bool {
        global $wpdb;
        $ip_address = $data['ip_address'];
        $table_name = $wpdb->prefix . 'cf7_blocked_ips';
        $is_blocked = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE ip_address = %s", $ip_address)) > 0;

        if ($is_blocked) {
            return true;
        } else {
            return $this->component ? $this->component->isSpam($data) : false;
        }
    }
}
?>