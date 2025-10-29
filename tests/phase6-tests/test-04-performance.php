<?php
/**
 * Test 04: 10 soumissions rapides (Performance)
 *
 * Vérifie que le plugin gère bien plusieurs soumissions rapides
 * sans ralentissement notable et que la distribution reste correcte.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_04_performance() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 04: Performance with 10 Rapid Submissions";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up performance test configuration");

        // 1. Configurer 3 emails en rotation
        $emails = array('perf1@example.com', 'perf2@example.com', 'perf3@example.com');
        update_option('fss_emails', $emails);
        update_option('fss_thematic_filter_mode', 'disabled');
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration: 3 emails in rotation");

        // 2. Mesurer le temps de traitement
        $perf_start = microtime(true);

        Phase6_Test_Helpers::log_info("Starting rapid submission of 10 entries...");

        $form_id = 3;
        $entry_ids = array();

        // 3. Soumettre 10 formulaires en boucle (sans sleep)
        for ($i = 1; $i <= 10; $i++) {
            $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
                8 => "Performance test entry {$i}"
            ));

            if (!$entry) {
                throw new Exception("Failed to create entry {$i}");
            }

            $entry_ids[] = $entry->id;

            // Déclencher l'envoi
            $email_handler = new FSS_Email_Handler();
            $email_handler->send_sequential_emails($entry->id, $form_id);
        }

        $perf_end = microtime(true);
        $execution_time = round($perf_end - $perf_start, 3);

        Phase6_Test_Helpers::log_info("All 10 entries processed in {$execution_time}s");

        // 4. Vérifier que le temps total est < 5 secondes
        if ($execution_time < 5.0) {
            Phase6_Test_Helpers::log_success("Performance acceptable: {$execution_time}s < 5s");
        } else {
            Phase6_Test_Helpers::log_warning("Performance issue: {$execution_time}s >= 5s");
            $passed = false;
            $error_message = "Execution too slow: {$execution_time}s (expected < 5s)";
        }

        // 5. Attendre que les emails arrivent dans MailHog
        Phase6_Test_Helpers::sleep_ms(1000);

        // 6. Vérifier que tous les emails ont été envoyés
        $all_emails = Phase6_Test_Helpers::phase6_get_mailhog_emails();
        $total_count = count($all_emails);

        Phase6_Test_Helpers::log_info("Total emails sent: {$total_count}");

        if (!Phase6_Test_Helpers::phase6_assert_equals(10, $total_count, "Should have sent 10 emails")) {
            $passed = false;
        }

        // 7. Vérifier la distribution correcte
        // 10 emails / 3 destinataires = 3, 3, 4 ou 4, 3, 3
        $count1 = Phase6_Test_Helpers::phase6_count_emails_to('perf1@example.com');
        $count2 = Phase6_Test_Helpers::phase6_count_emails_to('perf2@example.com');
        $count3 = Phase6_Test_Helpers::phase6_count_emails_to('perf3@example.com');

        Phase6_Test_Helpers::log_info("Distribution: perf1={$count1}, perf2={$count2}, perf3={$count3}");

        // Vérifier que la somme est 10
        $total = $count1 + $count2 + $count3;
        if (!Phase6_Test_Helpers::phase6_assert_equals(10, $total, "Sum of distributed emails should be 10")) {
            $passed = false;
        }

        // Vérifier que la distribution est équitable (différence max de 1)
        $counts = array($count1, $count2, $count3);
        $min_count = min($counts);
        $max_count = max($counts);
        $distribution_diff = $max_count - $min_count;

        if ($distribution_diff <= 1) {
            Phase6_Test_Helpers::log_success("Distribution is fair (max difference: {$distribution_diff})");
        } else {
            Phase6_Test_Helpers::log_warning("Distribution is uneven (max difference: {$distribution_diff})");
            $passed = false;
            $error_message = "Uneven distribution: difference of {$distribution_diff} between recipients";
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
    $result = phase6_test_04_performance();
    exit($result ? 0 : 1);
}
