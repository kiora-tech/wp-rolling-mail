<?php
/**
 * Test 07: Fallback vers liste principale
 *
 * Vérifie que le système utilise correctement la liste principale
 * lorsqu'aucune liste thématique n'est configurée pour la valeur détectée.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_07_fallback() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 07: Fallback to Main List";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up fallback test configuration");

        // 1. Configurer field_id = 8
        update_option('fss_thematic_field_id', 8);
        update_option('fss_thematic_filter_mode', 'enabled');

        // 2. Configurer SEULEMENT emails pour "prevoyance" (pas pour "epargne_retraite")
        $thematic_emails = array(
            'field_id' => 8,
            'mappings' => array(
                'prevoyance' => array('prev1@example.com')
            )
        );
        update_option('fss_thematic_emails', $thematic_emails);

        // 3. Configurer liste principale (fallback)
        update_option('fss_emails', array('main@example.com'));
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration:");
        Phase6_Test_Helpers::log_info("  - Thematic field: 8");
        Phase6_Test_Helpers::log_info("  - Only 'prevoyance' thematic list configured");
        Phase6_Test_Helpers::log_info("  - Main fallback: main@example.com");

        // 4. Créer une entry avec field 8 = "Epargne Retraite"
        Phase6_Test_Helpers::log_info("Creating entry with unmapped thematic value 'Epargne Retraite'...");

        $form_id = 3;
        $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => 'Epargne Retraite'
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

        // 7. Vérifier que main@ l'a reçu (fallback)
        $main_count = Phase6_Test_Helpers::phase6_count_emails_to('main@example.com');
        Phase6_Test_Helpers::log_info("Main fallback received: {$main_count} email(s)");

        if (!Phase6_Test_Helpers::phase6_assert_equals(1, $main_count, "main@example.com should receive the email via fallback")) {
            $passed = false;
        }

        // 8. Vérifier que prev1@ n'a rien reçu
        $prev1_count = Phase6_Test_Helpers::phase6_count_emails_to('prev1@example.com');
        if (!Phase6_Test_Helpers::phase6_assert_equals(0, $prev1_count, "prev1@example.com should NOT receive this email")) {
            $passed = false;
        }

        // 9. Vérifier le log de fallback
        // Expected log: "[FSS] No thematic list for 'epargne_retraite', falling back"
        Phase6_Test_Helpers::log_info("Expected log: [FSS] WARNING: Thematic list 'epargne_retraite' is configured but empty");
        Phase6_Test_Helpers::log_info("Expected log: [FSS] Falling back to main rotation list");

        // Test avec une valeur thématique qui EST configurée pour vérifier que le fallback ne s'active pas
        Phase6_Test_Helpers::log_info("Testing that configured thematic values do NOT fallback...");

        $entry2 = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => 'Prévoyance'
        ));

        if ($entry2) {
            $email_handler->send_sequential_emails($entry2->id, $form_id);
            Phase6_Test_Helpers::sleep_ms(500);

            // prev1 devrait avoir reçu cet email
            $prev1_count2 = Phase6_Test_Helpers::phase6_count_emails_to('prev1@example.com');
            $main_count2 = Phase6_Test_Helpers::phase6_count_emails_to('main@example.com');

            Phase6_Test_Helpers::log_info("After Prévoyance submission:");
            Phase6_Test_Helpers::log_info("  prev1: {$prev1_count2}");
            Phase6_Test_Helpers::log_info("  main: {$main_count2}");

            // prev1 devrait avoir 1, main devrait toujours avoir 1 (pas 2)
            if (!Phase6_Test_Helpers::phase6_assert_equals(1, $prev1_count2, "prev1 should receive Prévoyance email")) {
                $passed = false;
            }

            if (!Phase6_Test_Helpers::phase6_assert_equals(1, $main_count2, "main should still have only 1 email (no new email)")) {
                $passed = false;
            }

            Phase6_Test_Helpers::phase6_delete_test_entry($entry2->id);
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
    $result = phase6_test_07_fallback();
    exit($result ? 0 : 1);
}
