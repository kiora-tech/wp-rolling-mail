<?php
/**
 * Test 05: Rotation reste cohérente sous charge
 *
 * Vérifie que l'index de rotation n'est pas corrompu lors
 * de nombreuses soumissions et que chaque destinataire reçoit
 * exactement le même nombre d'emails.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_05_rotation_coherence() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 05: Rotation Coherence Under Load";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up rotation coherence test");

        // 1. Configurer 5 emails
        $emails = array(
            'coh1@example.com',
            'coh2@example.com',
            'coh3@example.com',
            'coh4@example.com',
            'coh5@example.com'
        );
        update_option('fss_emails', $emails);
        update_option('fss_thematic_filter_mode', 'disabled');
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration: 5 emails in rotation");

        // 2. Soumettre 50 formulaires
        Phase6_Test_Helpers::log_info("Submitting 50 entries (this may take a moment)...");

        $form_id = 3;
        $entry_ids = array();

        for ($i = 1; $i <= 50; $i++) {
            $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
                8 => "Coherence test entry {$i}"
            ));

            if (!$entry) {
                throw new Exception("Failed to create entry {$i}");
            }

            $entry_ids[] = $entry->id;

            // Déclencher l'envoi
            $email_handler = new FSS_Email_Handler();
            $result = $email_handler->send_sequential_emails($entry->id, $form_id);

            if (!$result) {
                Phase6_Test_Helpers::log_warning("Email sending failed for entry {$i}");
            }

            // Log de progression tous les 10 entries
            if ($i % 10 === 0) {
                Phase6_Test_Helpers::log_info("Progress: {$i}/50 entries processed");
            }
        }

        Phase6_Test_Helpers::log_info("All 50 entries processed");

        // 3. Attendre que tous les emails arrivent
        Phase6_Test_Helpers::sleep_ms(2000);

        // 4. Compter les emails reçus par chaque destinataire
        $counts = array();
        foreach ($emails as $email) {
            $counts[$email] = Phase6_Test_Helpers::phase6_count_emails_to($email);
        }

        Phase6_Test_Helpers::log_info("Email distribution:");
        foreach ($counts as $email => $count) {
            Phase6_Test_Helpers::log_info("  {$email}: {$count} emails");
        }

        // 5. Vérifier que chaque destinataire a reçu exactement 10 emails (50 / 5 = 10)
        $all_equal = true;
        foreach ($counts as $email => $count) {
            if ($count !== 10) {
                Phase6_Test_Helpers::log_error("{$email} received {$count} emails instead of 10");
                $all_equal = false;
                $passed = false;
            }
        }

        if ($all_equal) {
            Phase6_Test_Helpers::log_success("Perfect distribution: all recipients received exactly 10 emails");
        } else {
            $error_message = "Uneven distribution detected - rotation index may be corrupted";
        }

        // 6. Vérifier le total
        $total_sent = array_sum($counts);
        if (!Phase6_Test_Helpers::phase6_assert_equals(50, $total_sent, "Total emails should be 50")) {
            $passed = false;
        }

        // 7. Vérifier qu'il n'y a pas de warnings sur index corrompu dans les logs
        // Note: Dans une implémentation complète, on parserait error.log
        Phase6_Test_Helpers::log_info("Expected: No warnings about corrupted rotation index in logs");

        // Cleanup
        Phase6_Test_Helpers::log_info("Cleaning up 50 test entries...");
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
    $result = phase6_test_05_rotation_coherence();
    exit($result ? 0 : 1);
}
