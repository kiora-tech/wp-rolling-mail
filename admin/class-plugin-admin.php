<?php

class Formidable_Sequential_Submissions_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    public function enqueue_scripts($hook) {
        if ('toplevel_page_formidable-sequential-submissions' !== $hook) {
            return;
        }
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('fss-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), null, true);
    }

    public function enqueue_styles() {
        wp_enqueue_style('fss-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css', array(), '1.0', 'all');

        $translations = array(
            'emailAddress' => __('email', 'fss'),
            'ccEmailAddress' => __('email cc', 'fss'),
            'invalidEmail' => __('invalid email', 'fss'),
        );
        wp_localize_script('fss-admin-script', 'fss_translations', $translations);
    }

    public function add_admin_menu() {
        add_menu_page('Formidable Sequential Submissions', 'Sequential Submissions', 'manage_options', 'formidable-sequential-submissions', array($this, 'display_plugin_admin_page'), 'dashicons-email', 6);
    }

    public function display_plugin_admin_page() {
        ?>
        <div class="wrap">
            <h2>Formidable Sequential Submissions</h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('fss_options_group');
                do_settings_sections('formidable-sequential-submissions');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('fss_options_group', 'fss_email_subject', 'sanitize_text_field');
        add_settings_field('fss_email_subject_field', __('email subject', 'fss'), array($this, 'email_subject_callback'), 'formidable-sequential-submissions', 'fss_general');
        register_setting('fss_options_group', 'fss_emails', array($this, 'sanitize_emails'));
        add_settings_section('fss_general', __('general settings', 'fss'), null, 'formidable-sequential-submissions');
        add_settings_field('fss_emails_field', __('email addresses', 'fss'), array($this, 'emails_field_callback'), 'formidable-sequential-submissions', 'fss_general');
        register_setting('fss_options_group', 'fss_email_cc', array($this, 'sanitize_emails'));
        add_settings_field('fss_email_cc_field', __('email cc addresses', 'fss'), array($this, 'email_cc_callback'), 'formidable-sequential-submissions', 'fss_general');
    }

    public function email_subject_callback() {
        $subject = get_option('fss_email_subject', '');
        echo '<div id="fss-email-subject">';
        echo '<div class="fss-email-subject-field"><input type="text" id="fss_email_subject" name="fss_email_subject" value="' . esc_attr($subject) . '"/></div>';
        echo '</div>';
    }

    public function email_cc_callback() {
        $cc_emails = get_option('fss_email_cc', array());
        echo '<div id="fss-email-cc-fields">';
        if (!empty($cc_emails)) {
            foreach ($cc_emails as $email) {
                echo '<div class="fss-cc-email-field"><label>'.__('email cc', 'fss').'</label><input type="email" name="fss_email_cc[]" value="' . esc_attr($email) . '" /><span class="fss-delete-email">🗑</span></div>';
            }
        }
        echo '</div>';
        echo '<button type="button" id="fss-add-cc-email">'.__('add another cc email', 'fss').'</button>';
        echo '<p class="description">'.__('add more cc email help', 'fss').'</p>';
    }

    public function emails_field_callback() {
        $emails = get_option('fss_emails');
        echo '<div id="fss-email-fields">';
        $index = 0;
        if (!empty($emails)) {
            foreach ($emails as $email) {
                echo '<div class="fss-email-field"><label>'.__('email', 'fss').' ' . (++$index) . '</label><input type="email" name="fss_emails[]" value="' . esc_attr($email) . '" /><span class="fss-delete-email">🗑</span></div>';
            }
        }
        echo '</div>';
        echo '<button type="button" id="fss-add-email">'.__('add another email', 'fss').'</button>';
        echo '<p class="description">'.__('add more email help', 'fss').'</p>';
    }

    public function sanitize_emails($input) {
        $emails = array_filter($input, function($email) {
            return !empty($email);
        });
        return array_map('sanitize_email', $emails);
    }

    public function admin_notices() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('success message', 'fss') . '</p></div>';
        }
    }
}
