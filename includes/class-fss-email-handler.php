<?php
if (!defined('ABSPATH')) {
    exit;
}

class FSS_Email_Handler {

    public function __construct() {
        add_action('frm_after_create_entry', array($this, 'send_sequential_emails'), 10, 2);
    }

    public function send_sequential_emails($entry_id, $form_id) {
        $entry = FrmEntry::getOne($entry_id, true);
        $submitted_data = $entry->metas;

        $email_list = get_option('fss_emails', []);
        $cc_email_list = get_option('fss_email_cc', []);

        if (empty($email_list)) {
            return;
        }

        $next_email = array_shift($email_list);
        array_push($email_list, $next_email);
        update_option('fss_emails', $email_list);

        $to = array($next_email);
        if (!empty($cc_email_list)) {
            $to = array_merge($to, $cc_email_list);
        }

        $subject = get_option('fss_email_subject', 'Nouvelle soumission de formulaire');
        $message = "";
        foreach ($submitted_data as $key => $value) {
            if (is_numeric($key)) {
                $field = FrmField::getOne($key);
                if ($field) {
                    $message .= (isset($field->name) ? $field->name : "Field #$key") . ": " . $value . "\n";
                }
            }
        }

        wp_mail($to, $subject, $message);
    }
}
