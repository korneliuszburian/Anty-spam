<?php

require_once 'class-base-manager.php';
require_once 'class-log-manager.php';

class Anty_Spam_Rekurencja_Honeypot_Manager extends BaseManager {
    public function __construct() {
        parent::__construct();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles_scripts']);
    }

    public function register_hooks() {
        parent::register_hooks();
        add_action('wpcf7_init', [$this, 'add_honeypot_field']);
        add_filter('wpcf7_validate', [$this, 'validate_honeypot'], 10, 2);
        add_action('wpcf7_admin_init', [$this, 'generate_form_tag']);
    }

    public function enqueue_styles_scripts() {
        wp_enqueue_style('honeypot_manager_style', plugin_dir_url(__FILE__) . 'css/honeypot-manager.css', [], '1.0', 'all');
    }

    public function add_honeypot_field() {
        $do_not_store = true;
        if (function_exists('wpcf7_add_form_tag')) {
            wpcf7_add_form_tag(
                'honeypot',
                [$this, 'render_honeypot_field'],
                [
                    'name-attr' => true,
                    'do-not-store' => $do_not_store,
                    'not-for-mail' => true,
                ]
            );
        } else {
            wpcf7_add_shortcode('honeypot', [$this, 'render_honeypot_field'], true);
        }
    }

    public function render_honeypot_field($tag) {
        $tag = new WPCF7_FormTag($tag);

        if (empty($tag->name)) {
            return '';
        }

        $validation_error = wpcf7_get_validation_error($tag->name);

        $class = wpcf7_form_controls_class('text');

        $placeholder = (string) reset($tag->values);

        $atts = [
            'class' => $tag->get_class_option($class),
            'id' => $tag->get_option('id', 'id', true),
            'wrapper_id' => $tag->get_option('wrapper-id'),
            'placeholder' => $placeholder,
            'name' => $tag->name,
            'type' => $tag->type,
            'validation_error' => $validation_error,
        ];

        $unique_id = uniqid('wpcf7-');
        $wrapper_id = !empty($atts['wrapper_id']) ? reset($atts['wrapper_id']) : $unique_id . '-wrapper';
        $input_placeholder = !empty($atts['placeholder']) ? ' placeholder="' . esc_attr($atts['placeholder']) . '" ' : '';
        $input_id = !empty($atts['id']) ? $atts['id'] : $unique_id . '-field';

        $html = '<span id="' . esc_attr($wrapper_id) . '" class="wpcf7-form-control-wrap ' . esc_attr($atts['name']) . '-wrap">';
        $html .= '<label for="' . esc_attr($input_id) . '" class="confirm-your-email wpcf7-form-control-pot">' . esc_html__('Please leave this field empty.', 'contact-form-7-honeypot') . '</label>';
        $html .= '<input id="' . esc_attr($input_id) . '" ' . $input_placeholder . ' class="' . esc_attr($atts['class']) . ' wpcf7-form-control-pot' . '" type="text" name="' . esc_attr($atts['name']) . '" value="" size="40" tabindex="-1" autocomplete="off" />';
        $html .= $validation_error . '</span>';

        return $html;
    }

    public function validate_honeypot($result, $tags) {
        $submission = WPCF7_Submission::get_instance();
        if ($submission) {
            $posted_data = $submission->get_posted_data();

            foreach ($posted_data as $key => $value) {
                if (strpos($key, 'your_honeypot_') !== false && !empty($value)) {
                    $result->invalidate($tags[0], "Your message was flagged as spam.");
                    LogManager::logActivity('Honeypot Check', "Blocked form submission due to honeypot filled in: $key");
                    break;
                }
            }
        }
        return $result;
    }

    public function generate_form_tag() {
        $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->add('honeypot', __('Honeypot', 'contact-form-7-honeypot'), [$this, 'form_tag_generator']);
    }

    public function form_tag_generator($contact_form, $args = '') {
        $args = wp_parse_args($args, []);
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e('Generate a form-tag for a spam-stopping honeypot field. For more details, see the Honeypot for CF7 documentation.', 'contact-form-7-honeypot'); ?></legend>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-name'); ?>"><?php esc_html_e('Name', 'contact-form-7-honeypot'); ?></label></th>
                        <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr($args['content'] . '-name'); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-id'); ?>"><?php esc_html_e('ID', 'contact-form-7-honeypot'); ?></label></th>
                        <td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr($args['content'] . '-id'); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-class'); ?>"><?php esc_html_e('Class', 'contact-form-7-honeypot'); ?></label></th>
                        <td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr($args['content'] . '-class'); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-values'); ?>"><?php echo esc_html(__('Placeholder', 'contact-form-7-honeypot')); ?></label></th>
                        <td><input type="text" name="values" class="oneline" id="<?php echo esc_attr($args['content'] . '-values'); ?>" /></td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>

        <div class="insert-box">
            <input type="text" name="honeypot" class="tag code" readonly="readonly" onfocus="this.select()" />
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e('Insert Tag', 'contact-form-7-honeypot'); ?>" />
            </div>
            <br class="clear" />
        </div>
        <?php
    }

    public function display_page() {
        echo '<div class="wrap"><h1>Honeypot Manager</h1><p>Honeypots are used to catch bots. They are invisible fields that real users do not fill out, but bots do.</p></div>';
    }
}
