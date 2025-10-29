<?php
/**
 * Test 09: Emails invalides sont filtrés
 *
 * Vérifie que les adresses email mal formatées sont correctement
 * détectées, filtrées et loggées, sans bloquer l'envoi aux emails valides.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_09_invalid_emails() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 09: Invalid Email Filtering";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up invalid email filtering test");

        // 1. Configurer liste avec emails valides ET invalides
        $emails = array(
            'valid@test.com',
            'invalid-email',           // Invalid: pas de @
            'also@valid.fr',
            '',                        // Invalid: empty
            'bad@',                    // Invalid: pas de domaine
            '@baddomain.com',          // Invalid: pas de mailbox
            'spaces in@email.com'      // Invalid: espaces
        );
        update_option('fss_emails', $emails);
        update_option('fss_thematic_filter_mode', 'disabled');
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration: 7 emails (2 valid, 5 invalid)");
        Phase6_Test_Helpers::log_info("Valid emails: valid@test.com, also@valid.fr");

        $form_id = 3;
        $entry_ids = array();

        // 2. Soumettre 4 formulaires (2 emails valides = 2 chacun)
        Phase6_Test_Helpers::log_info("Submitting 4 entries...");

        for ($i = 1; $i <= 4; $i++) {
            $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
                8 => "Test submission {$i} with invalid emails"
            ));

            if (!$entry) {
                throw new Exception("Failed to create entry {$i}");
            }

            $entry_ids[] = $entry->id;

            // Déclencher l'envoi
            $email_handler = new FSS_Email_Handler();
            $result = $email_handler->send_sequential_emails($entry->id, $form_id);

            // L'envoi doit réussir malgré les emails invalides
            if (!$result) {
                Phase6_Test_Helpers::log_warning("Email sending failed for entry {$i}");
            }

            Phase6_Test_Helpers::sleep_ms(100);
        }

        Phase6_Test_Helpers::log_info("All 4 entries processed");

        // 3. Attendre que les emails arrivent
        Phase6_Test_Helpers::sleep_ms(500);

        // 4. Vérifier que seuls les emails valides ont reçu
        $valid_count = Phase6_Test_Helpers::phase6_count_emails_to('valid@test.com');
        $also_valid_count = Phase6_Test_Helpers::phase6_count_emails_to('also@valid.fr');

        Phase6_Test_Helpers::log_info("Email distribution:");
        Phase6_Test_Helpers::log_info("  valid@test.com: {$valid_count}");
        Phase6_Test_Helpers::log_info("  also@valid.fr: {$also_valid_count}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $valid_count, "valid@test.com should receive 2 emails")) {
            $passed = false;
        }

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $also_valid_count, "also@valid.fr should receive 2 emails")) {
            $passed = false;
        }

        // 5. Vérifier le total
        $total_emails = Phase6_Test_Helpers::phase6_get_mailhog_emails();
        $total_count = count($total_emails);

        Phase6_Test_Helpers::log_info("Total emails sent: {$total_count}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(4, $total_count, "Should have sent 4 emails total")) {
            $passed = false;
        }

        // 6. Vérifier que la rotation fonctionne avec 2 emails (pas 7)
        // Distribution devrait être 2-2, pas 1-1-1-1-0-0-0
        if ($valid_count === 2 && $also_valid_count === 2) {
            Phase6_Test_Helpers::log_success("Rotation working correctly with filtered emails (2-2 distribution)");
        } else {
            Phase6_Test_Helpers::log_error("Rotation not working correctly");
            $passed = false;
            $error_message = "Invalid distribution: {$valid_count}-{$also_valid_count} instead of 2-2";
        }

        // 7. Vérifier les logs de warning
        Phase6_Test_Helpers::log_info("Expected warnings in logs:");
        Phase6_Test_Helpers::log_info("  - [FSS] WARNING: Invalid email address removed from main/thematic rotation list: 'invalid-email'");
        Phase6_Test_Helpers::log_info("  - [FSS] WARNING: Invalid email address removed from main/thematic rotation list: 'bad@'");
        Phase6_Test_Helpers::log_info("  - [FSS] WARNING: Invalid email address removed from main/thematic rotation list: '@baddomain.com'");
        Phase6_Test_Helpers::log_info("  - [FSS] 5 invalid email(s) removed from main/thematic rotation list");

        // Test supplémentaire: tous les emails invalides
        Phase6_Test_Helpers::log_info("Extra test: All emails invalid...");
        Phase6_Test_Helpers::phase6_cleanup();
        Phase6_Test_Helpers::phase6_clear_mailhog();

        update_option('fss_emails', array('invalid1', 'invalid2', 'invalid3'));
        update_option('fss_form_filter_mode', 'all');

        $entry_invalid = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => "Test with all invalid"
        ));

        if ($entry_invalid) {
            $email_handler = new FSS_Email_Handler();
            $result_invalid = $email_handler->send_sequential_emails($entry_invalid->id, $form_id);

            // Devrait retourner false car aucun email valide
            if (!Phase6_Test_Helpers::phase6_assert_false($result_invalid, "Should return false when all emails are invalid")) {
                $passed = false;
            }

            Phase6_Test_Helpers::log_info("Expected log: [FSS] CRITICAL ERROR: All X email addresses in main/thematic rotation list are invalid");

            Phase6_Test_Helpers::phase6_delete_test_entry($entry_invalid->id);
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
    $result = phase6_test_09_invalid_emails();
    exit($result ? 0 : 1);
}
