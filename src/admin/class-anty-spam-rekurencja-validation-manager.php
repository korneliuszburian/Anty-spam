<?php

require_once 'class-base-manager.php';

class Validation_Manager extends BaseManager {

    public function register_hooks() {
        add_action('init', [$this, 'validate_input']);
    }

    public function display_page() {
        echo '<div class="wrap"><h1>Validation Settings</h1></div>';
    }

    public function is_spam($data) {
        return !$this->is_valid($data);
    }

    public function validate_input() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validate_all($_POST);
        }
    }

    private function is_valid($data) {
        return $this->validate_all($data);
    }

    private function validate_all($data) {
        $validators = [
            'email' => function($value) {
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            },
            'name' => function($value) {
                return preg_match('/^[a-zA-Z\s]+$/', $value);
            },
            'postal_code' => function($value) {
                return preg_match('/^\d{4,5}(-\d{4})?$/', $value);
            },
            'phone' => function($value) {
                return preg_match('/^\+?\d{10,15}$/', $value);
            },
            'url' => function($value) {
                return filter_var($value, FILTER_VALIDATE_URL);
            },
            'text' => function($value) {
                return strlen($value) > 0 && strlen($value) <= 500;
            },
            'number' => function($value) {
                return is_numeric($value);
            }
        ];

        foreach ($data as $key => $value) {
            if (isset($validators[$key]) && !$validators[$key]($value)) {
                exit("Invalid input detected: $key");
            }
        }
        return true;
    }
}
