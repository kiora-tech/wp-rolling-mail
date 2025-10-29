<?php
/**
 * Test 01: Configuration vide → Rotation simple
 *
 * Vérifie que sans configuration thématique, le comportement est identique
 * à la rotation simple originale.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_01_rotation_simple() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 01: Rotation Simple (Default Behavior)";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up simple rotation configuration");

        // 1. Vider la config thématique (mode désactivé)
        update_option('fss_thematic_filter_mode', 'disabled');
        update_option('fss_thematic_field_id', null);
        update_option('fss_thematic_emails', array('field_id' => null, 'mappings' => array()));

        // 2. Configurer 3 emails dans fss_emails
        $emails = array('test1@example.com', 'test2@example.com', 'test3@example.com');
        update_option('fss_emails', $emails);

        // 3. Configurer le formulaire pour utiliser la rotation
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration complete: 3 emails in rotation");

        // 4. Créer et soumettre 6 formulaires
        Phase6_Test_Helpers::log_info("Creating 6 test entries...");

        $form_id = 3; // Form ID par défaut
        $entry_ids = array();

        for ($i = 1; $i <= 6; $i++) {
            $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
                8 => "Test submission {$i}"
            ));

            if (!$entry) {
                throw new Exception("Failed to create entry {$i}");
            }

            $entry_ids[] = $entry->id;

            // Déclencher le hook d'envoi d'email
            $email_handler = new FSS_Email_Handler();
            $email_handler->send_sequential_emails($entry->id, $form_id);

            Phase6_Test_Helpers::sleep_ms(100); // Petite pause pour éviter les problèmes de timing
        }

        Phase6_Test_Helpers::log_info("All 6 entries created and processed");

        // 5. Attendre que les emails soient dans MailHog
        Phase6_Test_Helpers::sleep_ms(500);

        // 6. Vérifier que chaque email a reçu exactement 2 formulaires
        $count1 = Phase6_Test_Helpers::phase6_count_emails_to('test1@example.com');
        $count2 = Phase6_Test_Helpers::phase6_count_emails_to('test2@example.com');
        $count3 = Phase6_Test_Helpers::phase6_count_emails_to('test3@example.com');

        Phase6_Test_Helpers::log_info("Email distribution: test1={$count1}, test2={$count2}, test3={$count3}");

        // Assertions
        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $count1, "test1@example.com should receive exactly 2 emails")) {
            $passed = false;
        }

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $count2, "test2@example.com should receive exactly 2 emails")) {
            $passed = false;
        }

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $count3, "test3@example.com should receive exactly 2 emails")) {
            $passed = false;
        }

        // Vérifier l'ordre (test1, test2, test3, test1, test2, test3)
        $emails_list = Phase6_Test_Helpers::phase6_get_mailhog_emails();

        if (count($emails_list) === 6) {
            // MailHog retourne les emails dans l'ordre inverse (plus récent en premier)
            $emails_list = array_reverse($emails_list);

            $expected_order = array('test1', 'test2', 'test3', 'test1', 'test2', 'test3');
            $actual_order = array();

            foreach ($emails_list as $email) {
                if (isset($email['To'][0]['Mailbox'])) {
                    $actual_order[] = $email['To'][0]['Mailbox'];
                }
            }

            Phase6_Test_Helpers::log_info("Expected order: " . implode(', ', $expected_order));
            Phase6_Test_Helpers::log_info("Actual order: " . implode(', ', $actual_order));

            if (!Phase6_Test_Helpers::phase6_assert_equals($expected_order, $actual_order, "Email order should follow rotation pattern")) {
                $passed = false;
            }
        } else {
            Phase6_Test_Helpers::log_error("Expected 6 emails total, got " . count($emails_list));
            $passed = false;
        }

        // Cleanup: supprimer les entries de test
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
    $result = phase6_test_01_rotation_simple();
    exit($result ? 0 : 1);
}
