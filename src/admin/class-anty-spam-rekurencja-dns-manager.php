<?php

require_once 'class-base-manager.php';
require_once 'class-log-manager.php'; // Include LogManager

class DNS_Block_Manager extends BaseManager
{

    public function display_page()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_dns'])) {
                $this->add_dns_entry($_POST['new_dns']);
            } elseif (isset($_POST['delete_dns'])) {
                $this->delete_dns_entry($_POST['dns_to_delete']);
            }
        }

        $dnsbl_lookup = get_option('dnsbl_lookup', ['psbl.surriel.com', 'bl.spamcop.net', 'ix.dnsbl.manitu.net']);

        echo '<div class="wrap"><h1>DNS Block Settings</h1>';
        echo '<form method="post">';
        echo '<h2>Current DNS Block List</h2>';
        echo '<ul>';
        foreach ($dnsbl_lookup as $dns) {
            echo '<li>' . esc_html($dns) . ' <button type="submit" name="delete_dns" value="' . esc_attr($dns) . '">Delete</button></li>';
        }
        echo '</ul>';
        echo '<h2>Add New DNS Entry</h2>';
        echo '<input type="text" name="new_dns" required />';
        echo '<button type="submit" name="add_dns">Add DNS</button>';
        echo '</form>';
        echo '</div>';
    }

    private function add_dns_entry($dns)
    {
        $dnsbl_lookup = get_option('dnsbl_lookup', ['psbl.surriel.com', 'bl.spamcop.net', 'ix.dnsbl.manitu.net']);
        if (!in_array($dns, $dnsbl_lookup)) {
            $dnsbl_lookup[] = sanitize_text_field($dns);
            update_option('dnsbl_lookup', $dnsbl_lookup);
        }
    }

    private function delete_dns_entry($dns)
    {
        $dnsbl_lookup = get_option('dnsbl_lookup', ['psbl.surriel.com', 'bl.spamcop.net', 'ix.dnsbl.manitu.net']);
        if (($key = array_search($dns, $dnsbl_lookup)) !== false) {
            unset($dnsbl_lookup[$key]);
            update_option('dnsbl_lookup', $dnsbl_lookup);
        }
    }

    public function is_spam($data)
    {
        $ip = $data['ip'] ?? '';
        return $this->is_ip_listed($ip);
    }

    public function check_dnsbl()
    {
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

    private function is_ip_listed($ip)
    {
        $dnsbl_lookup = get_option('dnsbl_lookup', ['psbl.surriel.com', 'bl.spamcop.net', 'ix.dnsbl.manitu.net']);
        foreach ($dnsbl_lookup as $host) {
            $lookup = implode('.', array_reverse(explode('.', $ip))) . '.' . $host;
            if (checkdnsrr($lookup . '.', 'A')) {
                return $host;
            }
        }
        return false;
    }
}
