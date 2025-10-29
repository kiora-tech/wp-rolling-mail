<?php
/**
 * Test 06: Routage thématique basique
 *
 * Vérifie que le routage thématique fonctionne correctement
 * et que les emails sont envoyés à la bonne liste thématique.
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

function phase6_test_06_thematic_routing() {
    require_once '/tmp/phase6-test-helpers.php';

    $test_name = "Test 06: Thematic Routing Basic Functionality";
    $start_time = Phase6_Test_Helpers::phase6_setup_test_environment($test_name);

    $passed = true;
    $error_message = '';

    try {
        Phase6_Test_Helpers::log_info("Setting up thematic routing configuration");

        // 1. Configurer field_id = 8
        update_option('fss_thematic_field_id', 8);
        update_option('fss_thematic_filter_mode', 'enabled');

        // 2. Configurer emails pour "prevoyance"
        $thematic_emails = array(
            'field_id' => 8,
            'mappings' => array(
                'prevoyance' => array('prev1@example.com', 'prev2@example.com'),
                'sante' => array('sante1@example.com')
            )
        );
        update_option('fss_thematic_emails', $thematic_emails);

        // 3. Configurer liste principale (fallback)
        update_option('fss_emails', array('main@example.com'));
        update_option('fss_form_filter_mode', 'all');

        Phase6_Test_Helpers::log_info("Configuration complete:");
        Phase6_Test_Helpers::log_info("  - Thematic field: 8");
        Phase6_Test_Helpers::log_info("  - prevoyance: prev1, prev2");
        Phase6_Test_Helpers::log_info("  - sante: sante1");

        // 4. Créer une entry avec field 8 = "Prévoyance"
        Phase6_Test_Helpers::log_info("Creating entry with thematic value 'Prévoyance'...");

        $form_id = 3;
        $entry = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => 'Prévoyance'
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

        // 7. Vérifier que prev1@ ou prev2@ l'a reçu (pas sante1@ ni main@)
        $prev1_count = Phase6_Test_Helpers::phase6_count_emails_to('prev1@example.com');
        $prev2_count = Phase6_Test_Helpers::phase6_count_emails_to('prev2@example.com');
        $sante1_count = Phase6_Test_Helpers::phase6_count_emails_to('sante1@example.com');
        $main_count = Phase6_Test_Helpers::phase6_count_emails_to('main@example.com');

        Phase6_Test_Helpers::log_info("Email distribution:");
        Phase6_Test_Helpers::log_info("  prev1: {$prev1_count}");
        Phase6_Test_Helpers::log_info("  prev2: {$prev2_count}");
        Phase6_Test_Helpers::log_info("  sante1: {$sante1_count}");
        Phase6_Test_Helpers::log_info("  main: {$main_count}");

        // Un des deux emails de prévoyance doit avoir reçu l'email
        $prevoyance_total = $prev1_count + $prev2_count;
        if (!Phase6_Test_Helpers::phase6_assert_equals(1, $prevoyance_total, "One prevoyance email should receive the submission")) {
            $passed = false;
        }

        // Sante1 ne doit rien avoir reçu
        if (!Phase6_Test_Helpers::phase6_assert_equals(0, $sante1_count, "sante1 should NOT receive this submission")) {
            $passed = false;
        }

        // Main ne doit rien avoir reçu (pas de fallback nécessaire)
        if (!Phase6_Test_Helpers::phase6_assert_equals(0, $main_count, "main should NOT receive this submission")) {
            $passed = false;
        }

        // Test avec une deuxième entry pour vérifier la rotation dans la liste thématique
        Phase6_Test_Helpers::log_info("Testing rotation within thematic list...");

        $entry2 = Phase6_Test_Helpers::phase6_create_test_entry($form_id, array(
            8 => 'Prévoyance'
        ));

        if ($entry2) {
            $email_handler->send_sequential_emails($entry2->id, $form_id);
            Phase6_Test_Helpers::sleep_ms(500);

            // Vérifier que l'autre email de la liste a reçu
            $prev1_count2 = Phase6_Test_Helpers::phase6_count_emails_to('prev1@example.com');
            $prev2_count2 = Phase6_Test_Helpers::phase6_count_emails_to('prev2@example.com');

            Phase6_Test_Helpers::log_info("After 2nd submission:");
            Phase6_Test_Helpers::log_info("  prev1: {$prev1_count2}");
            Phase6_Test_Helpers::log_info("  prev2: {$prev2_count2}");

            // Chacun devrait avoir reçu 1 email
            if ($prev1_count2 === 1 && $prev2_count2 === 1) {
                Phase6_Test_Helpers::log_success("Rotation working correctly within thematic list");
            } else {
                Phase6_Test_Helpers::log_warning("Rotation may not be working correctly");
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
    $result = phase6_test_06_thematic_routing();
    exit($result ? 0 : 1);
}
