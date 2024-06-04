<?php

require_once 'class-base-manager.php';
require_once 'class-log-manager.php';

class Anty_Spam_Rekurencja_Word_Manager extends BaseManager {
    public function __construct() {
        parent::__construct();
        $this->maybe_add_language_column();
    }

    public function register_hooks() {
        parent::register_hooks();
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles_scripts']);
        add_action('admin_post_update_blocked_words', [$this, 'handle_actions']);
        add_action('admin_post_upload_words_file', [$this, 'handle_actions']);
        add_action('admin_post_delete_blocked_word', [$this, 'handle_actions']);
        add_action('wpcf7_validate', [$this, 'check_spam_words'], 10, 2);
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
        $blocked_words_html = '<div class="wrapper"><h1>Blocked Words</h1>';
        $blocked_words_html .= '<p>Manage the list of words that trigger spam detection. Add words manually or by uploading a .txt file.</p>';

        $blocked_words_html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        $blocked_words_html .= '<input type="hidden" name="action" value="update_blocked_words">';
        $blocked_words_html .= wp_nonce_field('update-blocked-words', '_wpnonce_update_blocked_words', true, false);
        $blocked_words_html .= '<input type="text" name="new_word" placeholder="Enter Word">';
        $blocked_words_html .= '<select name="language">
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="ru">Russian</option>
                                    <option value="ch">Chinese</option>
                                    <option value="fr">French</option>
                                </select>';
        $blocked_words_html .= '<input type="submit" name="submit" value="Add Word">';
        $blocked_words_html .= '</form>';

        $blocked_words_html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        $blocked_words_html .= '<input type="hidden" name="action" value="upload_words_file">';
        $blocked_words_html .= wp_nonce_field('upload-words-file', '_wpnonce_upload_words_file', true, false);
        $blocked_words_html .= '<input type="file" name="words_file" accept=".txt">';
        $blocked_words_html .= '<select name="language">
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="ru">Russian</option>
                                    <option value="ch">Chinese</option>
                                    <option value="fr">French</option>
                                </select>';
        $blocked_words_html .= '<input type="submit" name="upload_file" value="Upload File">';
        $blocked_words_html .= '</form>';
        $blocked_words = $this->get_blocked_words_grouped_by_language();

        foreach ($blocked_words as $language => $words) {
            $blocked_words_html .= '<div class="language-block">';
            $blocked_words_html .= '<h2 class="language-header">' . esc_html(ucfirst($language)) . ' (' . count($words) . ' words) <button class="toggle-button" onclick="toggleList(this)">+</button></h2>';
            $blocked_words_html .= '<div class="language-words" style="display: none;">';
            $blocked_words_html .= '<table class="wp-list-table widefat fixed striped">';
            $blocked_words_html .= '<thead><tr><th>Word</th><th>Added Time</th><th>Action</th></tr></thead><tbody>';

            foreach ($words as $word) {
                $blocked_words_html .= '<tr><td>' . esc_html($word->word) . '</td>';
                $blocked_words_html .= '<td>' . esc_html($word->added_time) . '</td>';
                $blocked_words_html .= '<td><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
                $blocked_words_html .= '<input type="hidden" name="action" value="delete_blocked_word">';
                $blocked_words_html .= wp_nonce_field('delete-blocked-word', '_wpnonce_delete_blocked_word', true, false);
                $blocked_words_html .= '<input type="hidden" name="word" value="' . esc_attr($word->word) . '">';
                $blocked_words_html .= '<input type="submit" name="delete" value="Delete" class="button button-danger">';
                $blocked_words_html .= '</form></td></tr>';
            }

            $blocked_words_html .= '</tbody></table></div></div>';
        }

        $blocked_words_html .= '</div>';

        return $blocked_words_html;
    }

    private function get_blocked_words_grouped_by_language() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT word, language, added_time FROM {$wpdb->prefix}cf7_blocked_words ORDER BY language, added_time DESC");

        $grouped_words = [];
        foreach ($results as $result) {
            if (!isset($grouped_words[$result->language])) {
                $grouped_words[$result->language] = [];
            }
            $grouped_words[$result->language][] = $result;
        }

        return $grouped_words;
    }

    private function maybe_add_language_column() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_blocked_words';

        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'language'");

        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD language VARCHAR(10) DEFAULT 'en'");
        }
    }

    private function word_exists($word, $language = 'en') {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cf7_blocked_words WHERE word = %s AND language = %s",
            $word,
            $language
        );
        return $wpdb->get_var($query) > 0;
    }

    private function contains_blocked_words($message) {
        global $wpdb;
        $blocked_words = $wpdb->get_col("SELECT word FROM {$wpdb->prefix}cf7_blocked_words");
    
        if (is_array($message)) {
            $message = implode(' ', $message);
        }
    
        foreach ($blocked_words as $blocked_word) {
            if (stripos($message, $blocked_word) !== false) {
                return true;
            }
        }
        return false;
    }

    private function add_blocked_word($word, $language = 'en') {
        global $wpdb;

        if ($this->word_exists($word, $language)) {
            LogManager::logError("Attempted to add a duplicate word: $word in language: $language");
            throw new Anty_Spam_Rekurencja_Exception("The word '$word' already exists in the $language language list.");
        }

        $wpdb->insert(
            $wpdb->prefix . 'cf7_blocked_words',
            ['word' => $word, 'language' => $language, 'added_time' => current_time('mysql')],
            ['%s', '%s', '%s']
        );

        LogManager::logActivity('Add Word', "Added word: $word in language: $language");
    }

    public function handle_actions() {
        try {
            if (isset($_POST['delete']) && check_admin_referer('delete-blocked-word', '_wpnonce_delete_blocked_word')) {
                $word = sanitize_text_field($_POST['word'] ?? '');
                if (!empty($word)) {
                    $this->delete_word($word);
                }
                wp_redirect(admin_url('admin.php?page=word-manager'));
                exit;
            }

            if (isset($_POST['submit']) && check_admin_referer('update-blocked-words', '_wpnonce_update_blocked_words')) {
                $new_word = sanitize_text_field($_POST['new_word'] ?? '');
                $language = sanitize_text_field($_POST['language'] ?? 'en');
                if (!empty($new_word)) {
                    $this->add_blocked_word($new_word, $language);
                }
                wp_redirect(admin_url('admin.php?page=word-manager'));
                exit;
            }

            if (isset($_POST['upload_file']) && check_admin_referer('upload-words-file', '_wpnonce_upload_words_file')) {
                $this->handle_file_upload();
                wp_redirect(admin_url('admin.php?page=word-manager'));
                exit;
            }
        } catch (Anty_Spam_Rekurencja_Exception $e) {
            wp_die($e->getMessage());
        }
    }

    private function delete_word($word) {
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'cf7_blocked_words', ['word' => $word], ['%s']);
        LogManager::logActivity('Delete Word', "Deleted word: $word");
    }

    private function handle_file_upload() {
        if (isset($_FILES['words_file']) && $_FILES['words_file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['words_file']['tmp_name'];
            $words = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $language = sanitize_text_field($_POST['language'] ?? 'en');
            foreach ($words as $word) {
                try {
                    $this->add_blocked_word(sanitize_text_field($word), $language);
                } catch (Anty_Spam_Rekurencja_Exception $e) {
                    continue;
                }
            }
        } else {
            throw new Anty_Spam_Rekurencja_Exception("File upload error or no file uploaded.");
        }
    }

    public function is_spam($message, $email = '') {
        if ($this->contains_blocked_words($message)) {
            LogManager::logActivity('Spam Detection', "Message blocked due to restricted words.");
            return true;
        }

        if ($this->has_multiple_links($message)) {
            LogManager::logActivity('Spam Detection', "Message blocked due to multiple links.");
            return true;
        }

        if (!empty($email) && !$this->is_business_email($email)) {
            LogManager::logActivity('Spam Detection', "Message blocked due to non-business email: $email");
            return true;
        }

        return false;
    }
    
    private function is_business_email($email) {
        $freeEmailDomains = [
            '@hotmail.com', '@gmail.com', '@yahoo.co', '@yahoo.com', '@mailinator.com', '@gmail.co.in', '@aol.com', '@yandex.com', '@msn.com', 
            '@gawab.com', '@inbox.com', '@gmx.com', '@rediffmail.com', '@in.com', '@live.com', '@hotmail.co.uk', '@hotmail.fr', '@yahoo.fr', 
            '@wanadoo.fr', '@comcast.net', '@yahoo.co.uk', '@yahoo.com.br', '@yahoo.co.in', '@free.fr', '@gmx.de', '@yandex.ru', '@ymail.com', 
            '@libero.it', '@outlook.com', '@uol.com.br', '@bol.com.br', '@mail.ru', '@cox.net', '@hotmail.it', '@sbcglobal.net', '@sfr.fr', 
            '@live.fr', '@verizon.net', '@live.co.uk', '@googlemail.com', '@yahoo.es', '@ig.com.br', '@live.nl', '@bigpond.com', '@terra.com.br', 
            '@yahoo.it', '@neuf.fr', '@yahoo.de', '@aim.com', '@autograf.pl', '@gazeta.pl', '@interia.pl', '@migmail.pl', '@net-c.pl', '@netc.pl', 
            '@o2.pl', '@onet.pl', '@op.pl', '@opoczta.pl', '@poczta.onet.pl', '@vp.pl', '@wp.pl', '@yahoo.pl', '@yandex.pl', '@zzz.pl', '@bigpond.net.au'
        ];

        foreach ($freeEmailDomains as $domain) {
            if (preg_match("/" . preg_quote($domain, '/') . "/i", $email)) {
                return false;
            }
        }
        return true;
    }
    
    private function has_multiple_links($message) {
        if (is_array($message)) {
            $message = implode(' ', $message);
        }
    
        $link_count = preg_match_all('/\bhttps?:\/\/\S+/i', $message, $matches);
        return $link_count > 1;
    }

    public function check_spam_words($result, $tags) {
        $submission = WPCF7_Submission::get_instance();
        if ($submission) {
            $posted_data = $submission->get_posted_data();
            $message = '';

            foreach ($posted_data as $key => $value) {
                if (is_array($value)) {
                    $message .= ' ' . implode(' ', $value);
                } else {
                    $message .= ' ' . $value;
                }
            }

            if ($this->is_spam($message)) {
                $result->invalidate($tags[0], "Your message contains blocked words.");
                LogManager::logActivity('Spam Check', "Blocked form submission due to spam words in message: $message");
            }
        }
        return $result;
    }
}