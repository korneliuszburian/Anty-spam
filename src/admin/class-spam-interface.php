<?php

interface SpamInterface {
    /**
     * Register actions and hooks for the WordPress admin.
     */
    public function register_hooks();

    /**
     * Display the page associated with this manager in the WordPress admin.
     */
    public function display_page();

    /**
     * Check if the given data is considered spam by this manager.
     * 
     * @param array $data The data to check for spam.
     * @return bool True if the data is considered spam, false otherwise.
     */
    public function is_spam($data);
}
