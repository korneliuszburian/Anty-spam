<?php
class ContentBasedSpamDetector extends SpamDetector
{
    private $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function isSpam($data): bool
    {
        $content = $data['content'];
        $table_name = $this->wpdb->prefix . 'cf7_blocked_words';
        $words = $this->wpdb->get_results("SELECT * FROM $table_name");
        foreach ($words as $word) {
            if (strpos($content, $word->word) !== false) {
                return true;
            }
        }
        return parent::isSpam($data);
    }
}
?>
