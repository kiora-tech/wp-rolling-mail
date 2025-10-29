<?php
/**
 * Test 08: Normalisation des clés thématiques
 *
 * Vérifie que les différentes variations d'une même valeur thématique
 * (avec/sans préfixe, avec/sans accents) sont correctement normalisées
 * et routées vers la même liste d'emails.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_08_normalization() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 08: Thematic Key Normalization";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up normalization test configuration");

        // 1. Configurer field_id = 8
        update_option('fss_thematic_field_id', 8);
        update_option('fss_thematic_filter_mode', 'enabled');

        // 2. Configurer emails pour la clé normalisée "prevoyance"
        $thematic_emails = array(
            'field_id' => 8,
            'mappings' => array(
                'prevoyance' => array('prev@example.com')
            )
        );
        update_option('fss_thematic_emails', $thematic_emails);

        // 3. Configurer liste principale (au cas où)
        update_option('fss_emails', array('fallback@example.com'));
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration:");
        Phase6_Test_Helpers::log_info("  - Thematic list key: 'prevoyance'");
        Phase6_Test_Helpers::log_info("  - Target email: prev@example.com");

        $form_id = 3;
        $entry_ids = array();

        // 4. Tester avec "Prévoyance" (avec accent, sans préfixe)
        Phase6_Test_Helpers::log_info("Test 1: Creating entry with 'Prévoyance'...");

        $entry1 = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => 'Prévoyance'
        ));

        if (!$entry1) {
            throw new Exception("Failed to create entry 1");
        }

        $entry_ids[] = $entry1->id;

        $email_handler = new FSS_Email_Handler();
        $result1 = $email_handler->send_sequential_emails($entry1->id, $form_id);

        if (!Phase6_Test_Helpers::phase6_assert_true($result1, "Email 1 should be sent successfully")) {
            $passed = false;
        }

        Phase6_Test_Helpers::sleep_ms(300);

        // Vérifier que prev@ l'a reçu
        $prev_count = Phase6_Test_Helpers::phase6_count_emails_to('prev@example.com');
        Phase6_Test_Helpers::log_info("prev@ count after 'Prévoyance': {$prev_count}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(1, $prev_count, "prev@ should receive email from 'Prévoyance'")) {
            $passed = false;
        }

        // 5. Tester avec "Type : Prévoyance" (avec préfixe)
        Phase6_Test_Helpers::log_info("Test 2: Creating entry with 'Type : Prévoyance'...");

        $entry2 = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => 'Type : Prévoyance'
        ));

        if (!$entry2) {
            throw new Exception("Failed to create entry 2");
        }

        $entry_ids[] = $entry2->id;

        $result2 = $email_handler->send_sequential_emails($entry2->id, $form_id);

        if (!Phase6_Test_Helpers::phase6_assert_true($result2, "Email 2 should be sent successfully")) {
            $passed = false;
        }

        Phase6_Test_Helpers::sleep_ms(300);

        // Vérifier que prev@ a maintenant reçu 2 emails
        $prev_count2 = Phase6_Test_Helpers::phase6_count_emails_to('prev@example.com');
        Phase6_Test_Helpers::log_info("prev@ count after 'Type : Prévoyance': {$prev_count2}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $prev_count2, "prev@ should receive email from 'Type : Prévoyance'")) {
            $passed = false;
        }

        // 6. Vérifier que fallback@ n'a rien reçu (pas de fallback activé)
        $fallback_count = Phase6_Test_Helpers::phase6_count_emails_to('fallback@example.com');
        Phase6_Test_Helpers::log_info("fallback@ count: {$fallback_count}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(0, $fallback_count, "fallback@ should NOT receive any emails")) {
            $passed = false;
        }

        // 7. Vérifier les logs de normalisation
        Phase6_Test_Helpers::log_info("Expected logs:");
        Phase6_Test_Helpers::log_info("  - [FSS] Normalized thematic key: 'prevoyance' (from 'Prévoyance')");
        Phase6_Test_Helpers::log_info("  - [FSS] Normalized thematic key: 'prevoyance' (from 'Type : Prévoyance')");

        // Test supplémentaire avec d'autres variations
        Phase6_Test_Helpers::log_info("Test 3: Testing other variations...");

        $variations = array(
            'PRÉVOYANCE' => 'uppercase with accent',
            'prévoyance' => 'lowercase with accent',
            'Prevoyance' => 'no accent',
            'Type: Prévoyance' => 'no space after colon',
            'Type :Prévoyance' => 'no space before colon'
        );

        foreach ($variations as $variation => $description) {
            Phase6_Test_Helpers::log_info("Testing variation: '{$variation}' ({$description})");

            $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
                8 => $variation
            ));

            if ($entry) {
                $entry_ids[] = $entry->id;
                $email_handler->send_sequential_emails($entry->id, $form_id);
                Phase6_Test_Helpers::sleep_ms(200);
            }
        }

        // Vérifier le compte final
        Phase6_Test_Helpers::sleep_ms(500);
        $final_prev_count = Phase6_Test_Helpers::phase6_count_emails_to('prev@example.com');
        $expected_count = 2 + count($variations); // 2 premiers tests + variations

        Phase6_Test_Helpers::log_info("Final prev@ count: {$final_prev_count} (expected: {$expected_count})");

        if (!Phase6_Test_Helpers::phase6_assert_equals($expected_count, $final_prev_count, "All variations should route to prev@")) {
            $passed = false;
        }

        // Cleanup
        foreach ($entry_ids as $entry_id) {
            Phase6_Test_Helpers::phase6_delete_test_entry($entry_id);
        }

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
    $result = phase6_test_08_normalization();
    exit($result ? 0 : 1);
}
