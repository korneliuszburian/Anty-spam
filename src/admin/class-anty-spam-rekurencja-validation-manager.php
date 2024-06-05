<?php
require_once 'class-base-manager.php';

class Validation_Manager extends BaseManager {

    public function register_hooks() {
        add_action('wpcf7_validate', [$this, 'validate_cf7_input'], 10, 2);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_save_validation_setting', [$this, 'save_validation_setting']);
    }

    public function add_admin_menu() {
        add_menu_page('Validation Settings', 'Validation Settings', 'manage_options', 'validation_settings', [$this, 'display_page']);
    }

    public function display_page() {
        echo '<div class="wrap"><h1>Validation Settings</h1>';
        $this->render_settings();
        echo '</div>';
        $this->add_scripts();
    }

    private function render_settings() {
        $settings = $this->get_validation_rules();
        echo '<form id="validation-settings-form">';
        foreach ($settings as $field => $rule) {
            echo '<div id="setting-' . $field . '" class="validation-setting">';
            echo '<label>' . ucfirst(str_replace('-', ' ', $field)) . '</label>: ';
            echo '<input type="text" value="' . htmlspecialchars(json_encode($rule)) . '" disabled />';
            echo '<button type="button" onclick="toggleEdit(\'' . $field . '\')">🔒</button>';
            echo '</div>';
        }
        echo '<input type="submit" value="Save Changes" id="save-button" disabled />';
        echo '</form>';
    }

    private function get_validation_rules() {
        return [
            'your-email' => 'FILTER_VALIDATE_EMAIL',
            'your-name' => 'preg_match("/^[a-zA-Z\s]+$/")',
            'postal_code' => 'preg_match("/^\\d{4,5}(-\\d{4})?$/")',
            'your-phone' => 'preg_match("/^\\+?\\d{10,15}$/")',
            'your-url' => 'FILTER_VALIDATE_URL',
            'your-subject' => 'strlen($value) > 0 && strlen($value) <= 100',
            'your-message' => 'strlen($value) > 0 && strlen($value) <= 500',
            'number' => 'is_numeric($value)'
        ];
    }

    public function save_validation_setting() {
        check_ajax_referer('validation_setting_nonce', 'nonce');

        $field = $_POST['field'];
        $value = $_POST['value'];

        $settings = $this->get_validation_rules();
        $settings[$field] = json_decode(stripslashes($value), true);

        update_option('validation_settings', $settings);

        wp_send_json_success('Settings saved successfully.');
    }

    private function add_scripts() {
        echo '<script>
        function toggleEdit(field) {
            const setting = document.getElementById("setting-" + field);
            const input = setting.querySelector("input");
            const button = setting.querySelector("button");
            const saveButton = document.getElementById("save-button");

            if (input.disabled) {
                input.disabled = false;
                button.textContent = "🔓";
                saveButton.disabled = false;
            } else {
                input.disabled = true;
                button.textContent = "🔒";
                // You could potentially disable the save button if no other fields are unlocked
                // saveButton.disabled = true;
            }
        }

        document.getElementById("validation-settings-form").addEventListener("submit", function(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const settings = {};
            event.target.querySelectorAll(".validation-setting").forEach(setting => {
                const input = setting.querySelector("input");
                settings[input.name] = input.value;
            });

            fetch(ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    action: "save_validation_setting",
                    settings: settings,
                    nonce: "' . wp_create_nonce('validation_setting_nonce') . '"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Settings saved successfully.");
                } else {
                    alert("Failed to save settings.");
                }
            })
            .catch(error => {
                console.error("Error saving settings:", error);
                alert("Failed to save settings.");
            });
        });
        </script>';
    }
}