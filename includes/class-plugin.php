<?php
require_once plugin_dir_path(__DIR__) . 'includes/class-fss-email-handler.php';

class Formidable_Sequential_Submissions {

    private $email_handler;

    public function run() {
        $this->email_handler = new FSS_Email_Handler();
        $this->define_admin_hooks();
    }

    private function define_admin_hooks() {
        $plugin_admin = new Formidable_Sequential_Submissions_Admin();
    }
}
