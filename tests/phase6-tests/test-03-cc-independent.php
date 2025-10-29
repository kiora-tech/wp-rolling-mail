<?php
/**
 * Test 03: CC fonctionnent indépendamment de la rotation
 *
 * Vérifie que les CC reçoivent tous les emails indépendamment
 * de la rotation des destinataires principaux.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_03_cc_independent() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 03: CC Recipients Independent from Rotation";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up rotation with CC configuration");

        // 1. Configurer 2 emails en rotation
        $emails = array('email1@example.com', 'email2@example.com');
        update_option('fss_emails', $emails);

        // 2. Configurer 1 CC
        $cc_emails = array('cc@example.com');
        update_option('fss_email_cc', $cc_emails);

        // 3. Désactiver le mode thématique
        update_option('fss_thematic_filter_mode', 'disabled');
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration: 2 rotation emails + 1 CC");

        // 4. Créer et soumettre 2 formulaires
        Phase6_Test_Helpers::log_info("Creating 2 test entries...");

        $form_id = 3;
        $entry_ids = array();

        for ($i = 1; $i <= 2; $i++) {
            $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
                8 => "Test submission {$i} for CC test"
            ));

            if (!$entry) {
                throw new Exception("Failed to create entry {$i}");
            }

            $entry_ids[] = $entry->id;

            // Déclencher l'envoi
            $email_handler = new FSS_Email_Handler();
            $email_handler->send_sequential_emails($entry->id, $form_id);

            Phase6_Test_Helpers::sleep_ms(100);
        }

        Phase6_Test_Helpers::log_info("All 2 entries created and processed");

        // 5. Attendre que les emails arrivent
        Phase6_Test_Helpers::sleep_ms(500);

        // 6. Vérifier que CC a reçu 2 emails
        $cc_count = Phase6_Test_Helpers::phase6_count_emails_to('cc@example.com');
        Phase6_Test_Helpers::log_info("CC recipient received: {$cc_count} emails");

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $cc_count, "CC should receive all 2 emails")) {
            $passed = false;
        }

        // 7. Vérifier que email1 et email2 ont chacun reçu 1 email (rotation)
        $count1 = Phase6_Test_Helpers::phase6_count_emails_to('email1@example.com');
        $count2 = Phase6_Test_Helpers::phase6_count_emails_to('email2@example.com');

        Phase6_Test_Helpers::log_info("Rotation distribution: email1={$count1}, email2={$count2}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(1, $count1, "email1@example.com should receive 1 email")) {
            $passed = false;
        }

        if (!Phase6_Test_Helpers::phase6_assert_equals(1, $count2, "email2@example.com should receive 1 email")) {
            $passed = false;
        }

        // 8. Vérifier le total d'emails envoyés
        $all_emails = Phase6_Test_Helpers::phase6_get_mailhog_emails();
        $total_count = count($all_emails);

        // On s'attend à 2 emails envoyés, mais chacun avec 2 destinataires (rotation + CC)
        // MailHog compte les emails envoyés, pas les destinataires
        Phase6_Test_Helpers::log_info("Total emails in MailHog: {$total_count}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(2, $total_count, "Should have sent 2 emails total")) {
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
    $result = phase6_test_03_cc_independent();
    exit($result ? 0 : 1);
}
