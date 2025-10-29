<?php
/**
 * Plugin Name: Formidable Sequential Submissions
 * Description: This plugin allows you to send sequential submissions to Formidable Forms.
 * Version: 1.1.0
 * Author: Kiora
 * Text Domain: fss
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fss_load_textdomain() {
    load_plugin_textdomain('fss', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'fss_load_textdomain');

require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-plugin-admin.php';

function run_formidable_sequential_submissions() {
    $plugin = new Formidable_Sequential_Submissions();
    $plugin->run();
}

run_formidable_sequential_submissions();
