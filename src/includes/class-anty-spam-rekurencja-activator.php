<?php

/**
 * Fired during plugin activation
 *
 * @link  https://korneliuszburian.github.io/
 * @since 1.0.0
 *
 * @package    Anty_Spam_Rekurencja
 * @subpackage Anty_Spam_Rekurencja/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Anty_Spam_Rekurencja
 * @subpackage Anty_Spam_Rekurencja/includes
 * @author     Korneliusz Burian <krnijuu@gmail.com>
 */
class Anty_Spam_Rekurencja_Activator
{
    /**
     * Activate the plugin by creating necessary database tables.
     *
     * @since 1.0.0
     */
    public static function activate()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_email_logs';
        $blocked_ips_table = $wpdb->prefix . 'cf7_blocked_ips';
        $blocked_words_table = $wpdb->prefix . 'cf7_blocked_words';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            sender_email VARCHAR(100) NOT NULL,
            sender_ip VARCHAR(100) NOT NULL,
            is_spam BOOLEAN DEFAULT false,
            form_data TEXT NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE $blocked_ips_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip_address VARCHAR(100) NOT NULL,
            block_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql3 = "CREATE TABLE $blocked_words_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            word VARCHAR(255) NOT NULL,
            language VARCHAR(10) DEFAULT 'en' NOT NULL,
            added_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        dbDelta($sql);
        dbDelta($sql2);
        dbDelta($sql3);
    }
}