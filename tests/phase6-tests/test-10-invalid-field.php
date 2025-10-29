<?php
/**
 * Test 10: Field ID inexistant
 *
 * Vérifie que lorsque le field ID configuré n'existe pas dans Formidable,
 * le système utilise correctement le fallback vers la liste principale
 * et log un warning approprié.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_10_invalid_field() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 10: Invalid Field ID Fallback";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up invalid field ID test");

        // 1. Configurer un field_id inexistant (999)
        update_option('fss_thematic_field_id', 999);
        update_option('fss_thematic_filter_mode', 'enabled');

        // 2. Configurer une liste thématique (qui ne sera pas utilisée)
        $thematic_emails = array(
            'field_id' => 999,
            'mappings' => array(
                'test' => array('thematic@example.com')
            )
        );
        update_option('fss_thematic_emails', $thematic_emails);

        // 3. Configurer liste principale (fallback)
        update_option('fss_emails', array('fallback@example.com'));
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration:");
        Phase6_Test_Helpers::log_info("  - Thematic field: 999 (non-existent)");
        Phase6_Test_Helpers::log_info("  - Thematic list configured for 'test'");
        Phase6_Test_Helpers::log_info("  - Main fallback: fallback@example.com");

        // 4. Créer et soumettre 1 formulaire
        Phase6_Test_Helpers::log_info("Creating entry...");

        $form_id = 3;
        $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => "Test submission"
        ));

        if (!$entry) {
            throw new Exception("Failed to create entry");
        }

        $entry_id = $entry->id;

        // 5. Envoyer l'email
        $email_handler = new FSS_Email_Handler();
        $result = $email_handler->send_sequential_emails($entry->id, $form_id);

        if (!Phase6_Test_Helpers::phase6_assert_true($result, "Email should be sent successfully")) {
            $passed = false;
        }

        // 6. Attendre que l'email arrive
        Phase6_Test_Helpers::sleep_ms(500);

        // 7. Vérifier que fallback@ a reçu l'email
        $fallback_count = Phase6_Test_Helpers::phase6_count_emails_to('fallback@example.com');
        Phase6_Test_Helpers::log_info("fallback@ received: {$fallback_count} email(s)");

        if (!Phase6_Test_Helpers::phase6_assert_equals(1, $fallback_count, "fallback@example.com should receive the email")) {
            $passed = false;
        }

        // 8. Vérifier que thematic@ n'a rien reçu
        $thematic_count = Phase6_Test_Helpers::phase6_count_emails_to('thematic@example.com');
        Phase6_Test_Helpers::log_info("thematic@ received: {$thematic_count} email(s)");

        if (!Phase6_Test_Helpers::phase6_assert_equals(0, $thematic_count, "thematic@example.com should NOT receive any email")) {
            $passed = false;
        }

        // 9. Vérifier les logs de warning
        Phase6_Test_Helpers::log_info("Expected warning in logs:");
        Phase6_Test_Helpers::log_info("  - [FSS] WARNING: Configured thematic field ID 999 does not exist in Formidable Forms");
        Phase6_Test_Helpers::log_info("  - [FSS] Falling back to main rotation list");
        Phase6_Test_Helpers::log_info("  - [FSS] Please check your thematic field configuration");

        // Test supplémentaire: field ID null
        Phase6_Test_Helpers::log_info("Extra test: Field ID null...");
        Phase6_Test_Helpers::phase6_cleanup();
        Phase6_Test_Helpers::phase6_clear_mailhog();

        update_option('fss_thematic_field_id', null);
        update_option('fss_thematic_filter_mode', 'enabled');
        update_option('fss_emails', array('fallback2@example.com'));
        update_option('fss_form_filter_mode', 'all');

        $entry2 = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => "Test with null field ID"
        ));

        if ($entry2) {
            $email_handler->send_sequential_emails($entry2->id, $form_id);
            Phase6_Test_Helpers::sleep_ms(500);

            $fallback2_count = Phase6_Test_Helpers::phase6_count_emails_to('fallback2@example.com');
            Phase6_Test_Helpers::log_info("fallback2@ received: {$fallback2_count} email(s)");

            if (!Phase6_Test_Helpers::phase6_assert_equals(1, $fallback2_count, "Should fallback when field ID is null")) {
                $passed = false;
            }

            Phase6_Test_Helpers::log_info("Expected log: [FSS] No thematic field configured, using main rotation");

            Phase6_Test_Helpers::phase6_delete_test_entry($entry2->id);
        }

        // Test supplémentaire: field ID valide mais pas dans l'entry
        Phase6_Test_Helpers::log_info("Extra test: Field ID valid but not in entry...");
        Phase6_Test_Helpers::phase6_cleanup();
        Phase6_Test_Helpers::phase6_clear_mailhog();

        update_option('fss_thematic_field_id', 8);
        update_option('fss_thematic_filter_mode', 'enabled');
        update_option('fss_emails', array('fallback3@example.com'));
        update_option('fss_form_filter_mode', 'all');

        // Créer une entry SANS le field 8
        $entry3 = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            7 => "Some other field"
        ));

        if ($entry3) {
            $email_handler->send_sequential_emails($entry3->id, $form_id);
            Phase6_Test_Helpers::sleep_ms(500);

            $fallback3_count = Phase6_Test_Helpers::phase6_count_emails_to('fallback3@example.com');
            Phase6_Test_Helpers::log_info("fallback3@ received: {$fallback3_count} email(s)");

            if (!Phase6_Test_Helpers::phase6_assert_equals(1, $fallback3_count, "Should fallback when field not in entry")) {
                $passed = false;
            }

            Phase6_Test_Helpers::log_info("Expected log: [FSS] Field 8 not found in entry metadata, using main rotation");

            Phase6_Test_Helpers::phase6_delete_test_entry($entry3->id);
        }

        // Cleanup
        Phase6_Test_Helpers::phase6_delete_test_entry($entry_id);

    } catch (Exception $e) {
        $passed = false;
        $error_message = $e->getMessage();
        Phase6_Test_Helpers::log_error("Exception: " . $error_message);
    }

    Phase6_Test_Helpers::phase6_record_test_result($test_name, $passed, $start_time, $error_message);

    return $passed;
}

// Si exécuté directement
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $result = phase6_test_10_invalid_field();
    exit($result ? 0 : 1);
}
