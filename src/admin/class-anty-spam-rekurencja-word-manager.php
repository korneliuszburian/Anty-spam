<?php

require_once 'class-base-manager.php';

class Anty_Spam_Rekurencja_Word_Manager extends BaseManager {
    public function register_hooks() {
        parent::register_hooks();
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles_scripts']);
    }

    public function enqueue_styles_scripts() {
        wp_enqueue_style('word_manager_style', plugin_dir_url(__FILE__) . 'css/word_manager.css', [], '1.0', 'all');
        wp_enqueue_script('word_manager_script', plugin_dir_url(__FILE__) . 'js/word_manager.js', ['jquery'], '1.0', true);
    }

    public function display_page() {
        try {
            echo $this->display_blocked_words();
        } catch (Anty_Spam_Rekurencja_Exception $e) {
            $this->display_admin_error($e);
        }
    }

    private function display_blocked_words() {
        $blocked_words_html = '<div class="wrap"><h1>Blocked Words</h1>';
        $blocked_words_html .= '<p>Manage the list of words that trigger spam detection. Add words manually or by uploading a .txt file.</p>';
        $blocked_words_html .= '<form method="post" enctype="multipart/form-data">';
        $blocked_words_html .= '<input type="text" name="new_word" placeholder="Enter Word">';
        $blocked_words_html .= wp_nonce_field('update-blocked-words', '_wpnonce_update_blocked_words', true, false);
        $blocked_words_html .= '<input type="submit" name="submit" value="Add Word">';
        $blocked_words_html .= '<input type="file" name="words_file" accept=".txt">';
        $blocked_words_html .= '<input type="submit" name="upload_file" value="Upload File">';
        $blocked_words_html .= '</form>';

        $blocked_words_html .= '<table class="wp-list-table widefat fixed striped">';
        $blocked_words_html .= '<thead><tr><th>Word</th><th>Added Time</th></tr></thead><tbody>';

        $blocked_words = $this->get_blocked_words();
        foreach ($blocked_words as $word) {
            $blocked_words_html .= '<tr><td>' . esc_html($word->word) . '</td>';
            $blocked_words_html .= '<td>' . esc_html($word->added_time) . '</td></tr>';
        }

        $blocked_words_html .= '</tbody></table></div>';

        return $blocked_words_html;
    }

    private function get_blocked_words() {
        return $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}cf7_blocked_words ORDER BY added_time DESC");
    }

    public function add_blocked_word($word) {
        $this->wpdb->insert(
            $this->wpdb->prefix . 'cf7_blocked_words',
            ['word' => $word, 'added_time' => current_time('mysql')],
            ['%s', '%s']
        );
    }

    public function handle_actions() {
        if (isset($_POST['submit']) && check_admin_referer('update-blocked-words', '_wpnonce_update_blocked_words')) {
            $new_word = sanitize_text_field($_POST['new_word']);
            if (!empty($new_word)) {
                $this->add_blocked_word($new_word);
            }
        }

        if (isset($_POST['upload_file']) && check_admin_referer('upload-words-file', '_wpnonce_upload_words_file')) {
            try {
                $this->handle_file_upload();
            } catch (Exception $e) {
                throw new Anty_Spam_Rekurencja_Exception("Error processing file upload: " . $e->getMessage());
            }
        }
    }

    private function handle_file_upload() {
        if (isset($_FILES['words_file']) && $_FILES['words_file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['words_file']['tmp_name'];
            $words = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($words as $word) {
                $this->add_blocked_word(sanitize_text_field($word));
            }
        } else {
            throw new Anty_Spam_Rekurencja_Exception("File upload error or no file uploaded.");
        }
    }

    public function is_spam($data) {
        return false;
    }
}
