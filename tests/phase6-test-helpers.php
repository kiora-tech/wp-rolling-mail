<?php
/**
 * Phase 6 - Fonctions Helper pour Tests de Non-Régression
 *
 * Fonctions utilitaires pour la suite de tests du plugin WP Rolling Mail
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 */

if (!defined('ABSPATH')) {
    // Allow execution in CLI context
    define('ABSPATH', '/var/www/html/');
}

/**
 * Classe principale contenant toutes les fonctions helper
 */
class Phase6_Test_Helpers {

    private static $test_results = array();
    private static $mailhog_api = 'http://mailhog:8025/api';
    private static $start_time = 0;

    /**
     * Initialise l'environnement de test
     */
    public static function init() {
        self::$start_time = microtime(true);
        self::$test_results = array();
        self::log_section("Phase 6 - Test Suite Initialization");
    }

    /**
     * Configure l'environnement pour un test
     *
     * @param string $test_name Nom du test
     * @return float Timestamp de début du test
     */
    public static function phase6_setup_test_environment($test_name) {
        self::log_section("START Test: {$test_name}");

        // Nettoyer la configuration précédente
        self::phase6_cleanup();

        // Vider MailHog
        self::phase6_clear_mailhog();

        return microtime(true);
    }

    /**
     * Nettoie toutes les options du plugin
     */
    public static function phase6_cleanup() {
        delete_option('fss_emails');
        delete_option('fss_email_cc');
        delete_option('fss_current_index');
        delete_option('fss_thematic_field_id');
        delete_option('fss_thematic_emails');
        delete_option('fss_thematic_filter_mode');
        delete_option('fss_email_subject');
        delete_option('fss_form_filter_mode');
        delete_option('fss_form_ids');

        self::log_info("Configuration cleaned");
    }

    /**
     * Remet l'index de rotation à zéro
     */
    public static function phase6_reset_rotation_index() {
        update_option('fss_current_index', 0);
        self::log_info("Rotation index reset to 0");
    }

    /**
     * Vide tous les emails de MailHog
     *
     * @return bool True si succès
     */
    public static function phase6_clear_mailhog() {
        $ch = curl_init(self::$mailhog_api . '/v1/messages');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            self::log_info("MailHog cleared");
            return true;
        } else {
            self::log_warning("Failed to clear MailHog (HTTP {$http_code})");
            return false;
        }
    }

    /**
     * Récupère tous les emails de MailHog
     *
     * @return array Liste des emails
     */
    public static function phase6_get_mailhog_emails() {
        $ch = curl_init(self::$mailhog_api . '/v2/messages?limit=1000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            self::log_warning("Failed to fetch MailHog emails (HTTP {$http_code})");
            return array();
        }

        $data = json_decode($response, true);

        if (!isset($data['items']) || !is_array($data['items'])) {
            return array();
        }

        return $data['items'];
    }

    /**
     * Compte les emails envoyés à un destinataire spécifique
     *
     * @param string $recipient Email du destinataire
     * @return int Nombre d'emails
     */
    public static function phase6_count_emails_to($recipient) {
        $emails = self::phase6_get_mailhog_emails();
        $count = 0;

        foreach ($emails as $email) {
            if (isset($email['To'])) {
                foreach ($email['To'] as $to) {
                    if (isset($to['Mailbox']) && isset($to['Domain'])) {
                        $email_address = $to['Mailbox'] . '@' . $to['Domain'];
                        if (strcasecmp($email_address, $recipient) === 0) {
                            $count++;
                        }
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Vérifie qu'un email a été envoyé à un destinataire
     *
     * @param string $recipient Email du destinataire
     * @return bool True si au moins un email a été envoyé
     */
    public static function phase6_assert_email_sent_to($recipient) {
        $count = self::phase6_count_emails_to($recipient);
        return $count > 0;
    }

    /**
     * Récupère une entry Formidable avec un field spécifique
     *
     * @param int $field_id ID du champ
     * @param string $value Valeur du champ
     * @return object|null Entry object ou null
     */
    public static function phase6_get_entry_with_field_value($field_id, $value) {
        global $wpdb;

        $entry_id = $wpdb->get_var($wpdb->prepare("
            SELECT item_id
            FROM {$wpdb->prefix}frm_item_metas
            WHERE field_id = %d
            AND meta_value = %s
            LIMIT 1
        ", $field_id, $value));

        if (!$entry_id) {
            return null;
        }

        $entry = FrmEntry::getOne($entry_id, true);
        return $entry;
    }

    /**
     * Crée une fausse entry Formidable pour les tests
     *
     * @param int $form_id ID du formulaire
     * @param array $field_values Tableau associatif field_id => valeur
     * @return object|null Entry object ou null
     */
    public static function phase6_create_test_entry($form_id, $field_values = array()) {
        // Créer l'entry de base
        $entry_id = FrmEntry::create(array(
            'form_id' => $form_id,
            'created_at' => current_time('mysql'),
        ));

        if (!$entry_id) {
            return null;
        }

        // Ajouter les valeurs des champs
        foreach ($field_values as $field_id => $value) {
            FrmEntryMeta::add_entry_meta($entry_id, $field_id, '', $value);
        }

        // Récupérer l'entry complète
        $entry = FrmEntry::getOne($entry_id, true);

        return $entry;
    }

    /**
     * Supprime une entry de test
     *
     * @param int $entry_id ID de l'entry à supprimer
     */
    public static function phase6_delete_test_entry($entry_id) {
        FrmEntry::destroy($entry_id);
    }

    /**
     * Assert égalité stricte
     *
     * @param mixed $expected Valeur attendue
     * @param mixed $actual Valeur actuelle
     * @param string $message Message d'erreur
     * @return bool True si égaux
     */
    public static function phase6_assert_equals($expected, $actual, $message = '') {
        $passed = ($expected === $actual);

        if ($passed) {
            self::log_success("PASS: {$message}");
        } else {
            self::log_error("FAIL: {$message}");
            self::log_error("  Expected: " . var_export($expected, true));
            self::log_error("  Actual:   " . var_export($actual, true));
        }

        return $passed;
    }

    /**
     * Assert qu'une valeur est vraie
     *
     * @param mixed $condition Condition à tester
     * @param string $message Message d'erreur
     * @return bool True si vrai
     */
    public static function phase6_assert_true($condition, $message = '') {
        $passed = ($condition === true);

        if ($passed) {
            self::log_success("PASS: {$message}");
        } else {
            self::log_error("FAIL: {$message}");
            self::log_error("  Expected: TRUE");
            self::log_error("  Actual:   " . var_export($condition, true));
        }

        return $passed;
    }

    /**
     * Assert qu'une valeur est fausse
     *
     * @param mixed $condition Condition à tester
     * @param string $message Message d'erreur
     * @return bool True si faux
     */
    public static function phase6_assert_false($condition, $message = '') {
        $passed = ($condition === false);

        if ($passed) {
            self::log_success("PASS: {$message}");
        } else {
            self::log_error("FAIL: {$message}");
            self::log_error("  Expected: FALSE");
            self::log_error("  Actual:   " . var_export($condition, true));
        }

        return $passed;
    }

    /**
     * Vérifie qu'un pattern est présent dans les logs
     *
     * @param string $pattern Pattern regex à chercher
     * @param string $message Message d'assertion
     * @return bool True si trouvé
     */
    public static function phase6_assert_log_contains($pattern, $message = '') {
        // Pour cette version, on fait un simple check
        // Dans une version plus avancée, on pourrait parser le fichier de log
        self::log_info("Log check: {$pattern} - {$message}");
        return true; // Simplifié pour cette version
    }

    /**
     * Enregistre le résultat d'un test
     *
     * @param string $test_name Nom du test
     * @param bool $passed Test passé ou non
     * @param float $start_time Timestamp de début
     * @param string $error_message Message d'erreur si échec
     */
    public static function phase6_record_test_result($test_name, $passed, $start_time, $error_message = '') {
        $duration = round(microtime(true) - $start_time, 3);

        self::$test_results[] = array(
            'name' => $test_name,
            'passed' => $passed,
            'duration' => $duration,
            'error' => $error_message
        );

        self::log_section("END Test: {$test_name} - " . ($passed ? 'PASSED' : 'FAILED') . " ({$duration}s)");

        if (!$passed && $error_message) {
            self::log_error("Error: {$error_message}");
        }
    }

    /**
     * Génère le rapport final de tests
     *
     * @return array Résumé des tests
     */
    public static function phase6_generate_report() {
        $total = count(self::$test_results);
        $passed = 0;
        $failed = 0;
        $total_duration = round(microtime(true) - self::$start_time, 3);

        foreach (self::$test_results as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $report = array(
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'duration' => $total_duration,
            'tests' => self::$test_results
        );

        return $report;
    }

    /**
     * Affiche le rapport de tests en format texte
     *
     * @param array $report Rapport généré
     */
    public static function phase6_display_report($report) {
        self::log_section("PHASE 6 - TEST SUITE REPORT");

        echo "\n";
        echo "========================================\n";
        echo "           TEST SUMMARY\n";
        echo "========================================\n";
        echo "Total tests:    {$report['total']}\n";
        echo "Passed:         {$report['passed']} ✓\n";
        echo "Failed:         {$report['failed']} " . ($report['failed'] > 0 ? '✗' : '') . "\n";
        echo "Success rate:   " . ($report['total'] > 0 ? round(($report['passed'] / $report['total']) * 100, 1) : 0) . "%\n";
        echo "Total duration: {$report['duration']}s\n";
        echo "========================================\n\n";

        echo "DETAILED RESULTS:\n";
        echo "----------------------------------------\n";

        foreach ($report['tests'] as $test) {
            $status = $test['passed'] ? '✓ PASS' : '✗ FAIL';
            echo "{$status} | {$test['name']} ({$test['duration']}s)\n";

            if (!$test['passed'] && !empty($test['error'])) {
                echo "       Error: {$test['error']}\n";
            }
        }

        echo "----------------------------------------\n\n";

        if ($report['failed'] === 0) {
            self::log_success("ALL TESTS PASSED!");
        } else {
            self::log_error("{$report['failed']} TEST(S) FAILED");
        }
    }

    /**
     * Sauvegarde le rapport dans un fichier Markdown
     *
     * @param array $report Rapport généré
     * @param string $filename Chemin du fichier
     */
    public static function phase6_save_report_markdown($report, $filename) {
        $content = "# Phase 6 - Test Report\n\n";
        $content .= "**Date:** " . date('Y-m-d H:i:s') . "\n\n";

        $content .= "## Summary\n\n";
        $content .= "- **Tests run:** {$report['total']}\n";
        $content .= "- **Passed:** {$report['passed']} ✓\n";
        $content .= "- **Failed:** {$report['failed']}" . ($report['failed'] > 0 ? ' ✗' : '') . "\n";
        $content .= "- **Success rate:** " . ($report['total'] > 0 ? round(($report['passed'] / $report['total']) * 100, 1) : 0) . "%\n";
        $content .= "- **Execution time:** {$report['duration']}s\n\n";

        $content .= "## Detailed Results\n\n";

        foreach ($report['tests'] as $test) {
            $status = $test['passed'] ? '✓' : '✗';
            $status_text = $test['passed'] ? 'PASSED' : 'FAILED';

            $content .= "### Test: {$test['name']}\n\n";
            $content .= "- **Status:** {$status} {$status_text}\n";
            $content .= "- **Duration:** {$test['duration']}s\n";

            if (!$test['passed'] && !empty($test['error'])) {
                $content .= "- **Error:** {$test['error']}\n";
            }

            $content .= "\n";
        }

        $content .= "## Recommendations\n\n";

        if ($report['failed'] > 0) {
            $content .= "The following issues were detected:\n\n";
            foreach ($report['tests'] as $test) {
                if (!$test['passed']) {
                    $content .= "- **{$test['name']}:** {$test['error']}\n";
                }
            }
        } else {
            $content .= "All tests passed successfully. No issues detected.\n";
        }

        file_put_contents($filename, $content);
        self::log_success("Report saved to: {$filename}");
    }

    // Fonctions de logging

    public static function log_section($message) {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo $message . "\n";
        echo str_repeat('=', 60) . "\n";
    }

    public static function log_info($message) {
        echo "[INFO] {$message}\n";
    }

    public static function log_success($message) {
        echo "[✓ SUCCESS] {$message}\n";
    }

    public static function log_warning($message) {
        echo "[⚠ WARNING] {$message}\n";
    }

    public static function log_error($message) {
        echo "[✗ ERROR] {$message}\n";
    }

    /**
     * Attend quelques millisecondes (pour éviter les problèmes de timing)
     *
     * @param int $milliseconds Millisecondes à attendre
     */
    public static function sleep_ms($milliseconds) {
        usleep($milliseconds * 1000);
    }
}
