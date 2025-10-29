<?php
/**
 * Phase 6 - Suite de Tests de Non-Régression
 *
 * Script principal qui exécute tous les tests du plugin WP Rolling Mail
 * et génère un rapport détaillé des résultats.
 *
 * Usage: docker exec wp_axa wp eval-file /tmp/phase6-test-suite.php --allow-root
 *
 * @package WP_Rolling_Mail
 * @subpackage Tests
 * @version 1.0.0
 */

// Empêcher l'exécution directe hors contexte WordPress
if (!defined('ABSPATH')) {
    echo "Error: This script must be run within WordPress context.\n";
    echo "Usage: docker exec wp_axa wp eval-file /tmp/phase6-test-suite.php --allow-root\n";
    exit(1);
}

// Charger les fonctions helper
require_once '/tmp/phase6-test-helpers.php';

/**
 * Classe principale de la suite de tests
 */
class Phase6_Test_Suite {

    private $tests = array();
    private $test_results = array();

    /**
     * Constructeur - enregistre tous les tests disponibles
     */
    public function __construct() {
        $this->register_tests();
    }

    /**
     * Enregistre tous les tests disponibles
     */
    private function register_tests() {
        $this->tests = array(
            array(
                'file' => '/tmp/phase6-tests/test-01-rotation-simple.php',
                'function' => 'phase6_test_01_rotation_simple',
                'name' => 'Test 01: Rotation Simple',
                'description' => 'Vérifie que la rotation simple fonctionne sans configuration thématique'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-02-no-emails.php',
                'function' => 'phase6_test_02_no_emails',
                'name' => 'Test 02: No Emails Configured',
                'description' => 'Vérifie la gestion gracieuse de l\'absence d\'emails'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-03-cc-independent.php',
                'function' => 'phase6_test_03_cc_independent',
                'name' => 'Test 03: CC Independent',
                'description' => 'Vérifie que les CC reçoivent tous les emails'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-04-performance.php',
                'function' => 'phase6_test_04_performance',
                'name' => 'Test 04: Performance',
                'description' => 'Teste les performances avec 10 soumissions rapides'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-05-rotation-coherence.php',
                'function' => 'phase6_test_05_rotation_coherence',
                'name' => 'Test 05: Rotation Coherence',
                'description' => 'Vérifie la cohérence de la rotation avec 50 soumissions'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-06-thematic-routing.php',
                'function' => 'phase6_test_06_thematic_routing',
                'name' => 'Test 06: Thematic Routing',
                'description' => 'Teste le routage thématique basique'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-07-fallback.php',
                'function' => 'phase6_test_07_fallback',
                'name' => 'Test 07: Fallback',
                'description' => 'Vérifie le fallback vers la liste principale'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-08-normalization.php',
                'function' => 'phase6_test_08_normalization',
                'name' => 'Test 08: Key Normalization',
                'description' => 'Teste la normalisation des clés thématiques'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-09-invalid-emails.php',
                'function' => 'phase6_test_09_invalid_emails',
                'name' => 'Test 09: Invalid Email Filtering',
                'description' => 'Vérifie le filtrage des emails invalides'
            ),
            array(
                'file' => '/tmp/phase6-tests/test-10-invalid-field.php',
                'function' => 'phase6_test_10_invalid_field',
                'name' => 'Test 10: Invalid Field ID',
                'description' => 'Teste la gestion des field ID inexistants'
            ),
        );
    }

    /**
     * Exécute tous les tests
     *
     * @param array $options Options d'exécution
     * @return array Résultats des tests
     */
    public function run_all_tests($options = array()) {
        Phase6_Test_Helpers::init();

        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                                                            ║\n";
        echo "║          WP ROLLING MAIL - PHASE 6 TEST SUITE             ║\n";
        echo "║              Tests de Non-Régression                       ║\n";
        echo "║                                                            ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\n";

        Phase6_Test_Helpers::log_info("Total tests to run: " . count($this->tests));
        Phase6_Test_Helpers::log_info("Environment: WordPress " . get_bloginfo('version'));
        Phase6_Test_Helpers::log_info("PHP Version: " . phpversion());
        Phase6_Test_Helpers::log_info("Date: " . date('Y-m-d H:i:s'));
        echo "\n";

        // Vérifier que MailHog est accessible
        if (!$this->check_mailhog()) {
            Phase6_Test_Helpers::log_error("MailHog is not accessible. Tests cannot proceed.");
            return array();
        }

        // Vérifier que Formidable Forms est actif
        if (!class_exists('FrmEntry')) {
            Phase6_Test_Helpers::log_error("Formidable Forms is not active. Tests cannot proceed.");
            return array();
        }

        Phase6_Test_Helpers::log_success("Prerequisites check passed");
        echo "\n";

        // Exécuter chaque test
        $test_number = 0;
        foreach ($this->tests as $test) {
            $test_number++;

            // Séparer visuellement les tests
            if ($test_number > 1) {
                echo "\n" . str_repeat('-', 60) . "\n\n";
            }

            Phase6_Test_Helpers::log_section("Running Test {$test_number}/{" . count($this->tests) . "}");
            Phase6_Test_Helpers::log_info("Name: {$test['name']}");
            Phase6_Test_Helpers::log_info("Description: {$test['description']}");
            echo "\n";

            // Charger le fichier de test
            if (!file_exists($test['file'])) {
                Phase6_Test_Helpers::log_error("Test file not found: {$test['file']}");
                continue;
            }

            require_once $test['file'];

            // Vérifier que la fonction existe
            if (!function_exists($test['function'])) {
                Phase6_Test_Helpers::log_error("Test function not found: {$test['function']}");
                continue;
            }

            // Exécuter le test
            try {
                $result = call_user_func($test['function']);
                // Les résultats sont déjà enregistrés par chaque test via phase6_record_test_result
            } catch (Exception $e) {
                Phase6_Test_Helpers::log_error("Test threw exception: " . $e->getMessage());
            }

            // Pause entre les tests pour éviter les problèmes de timing
            Phase6_Test_Helpers::sleep_ms(500);
        }

        return Phase6_Test_Helpers::phase6_generate_report();
    }

    /**
     * Exécute un seul test spécifique
     *
     * @param int $test_number Numéro du test (1-10)
     * @return bool Résultat du test
     */
    public function run_single_test($test_number) {
        Phase6_Test_Helpers::init();

        $test_index = $test_number - 1;

        if (!isset($this->tests[$test_index])) {
            Phase6_Test_Helpers::log_error("Test #{$test_number} does not exist");
            return false;
        }

        $test = $this->tests[$test_index];

        Phase6_Test_Helpers::log_section("Running Single Test #{$test_number}");
        Phase6_Test_Helpers::log_info("Name: {$test['name']}");
        Phase6_Test_Helpers::log_info("Description: {$test['description']}");
        echo "\n";

        // Vérifier les prérequis
        if (!$this->check_mailhog()) {
            Phase6_Test_Helpers::log_error("MailHog is not accessible");
            return false;
        }

        if (!class_exists('FrmEntry')) {
            Phase6_Test_Helpers::log_error("Formidable Forms is not active");
            return false;
        }

        // Charger et exécuter le test
        require_once $test['file'];

        if (!function_exists($test['function'])) {
            Phase6_Test_Helpers::log_error("Test function not found: {$test['function']}");
            return false;
        }

        $result = call_user_func($test['function']);

        // Afficher le résumé
        $report = Phase6_Test_Helpers::phase6_generate_report();
        Phase6_Test_Helpers::phase6_display_report($report);

        return $result;
    }

    /**
     * Vérifie que MailHog est accessible
     *
     * @return bool True si accessible
     */
    private function check_mailhog() {
        $ch = curl_init('http://mailhog:8025/api/v2/messages?limit=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code === 200);
    }

    /**
     * Liste tous les tests disponibles
     */
    public function list_tests() {
        echo "\n";
        echo "Available Tests:\n";
        echo str_repeat('=', 60) . "\n";

        foreach ($this->tests as $index => $test) {
            $test_num = $index + 1;
            echo "\n{$test_num}. {$test['name']}\n";
            echo "   {$test['description']}\n";
        }

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "Total: " . count($this->tests) . " tests\n\n";
    }
}

// ============================================================================
// EXECUTION PRINCIPALE
// ============================================================================

// Créer l'instance de la suite de tests
$test_suite = new Phase6_Test_Suite();

// Vérifier si un argument a été passé
if (isset($args) && is_array($args) && count($args) > 0) {
    $command = $args[0];

    if ($command === 'list') {
        // Lister tous les tests
        $test_suite->list_tests();
    } elseif (is_numeric($command)) {
        // Exécuter un seul test
        $test_number = intval($command);
        $result = $test_suite->run_single_test($test_number);
        exit($result ? 0 : 1);
    } else {
        echo "Invalid argument: {$command}\n";
        echo "Usage:\n";
        echo "  wp eval-file /tmp/phase6-test-suite.php --allow-root          # Run all tests\n";
        echo "  wp eval-file /tmp/phase6-test-suite.php list --allow-root     # List tests\n";
        echo "  wp eval-file /tmp/phase6-test-suite.php 1 --allow-root        # Run test #1\n";
        exit(1);
    }
} else {
    // Exécuter tous les tests
    $report = $test_suite->run_all_tests();

    // Afficher le rapport
    echo "\n\n";
    Phase6_Test_Helpers::phase6_display_report($report);

    // Sauvegarder le rapport en Markdown
    $report_file = '/tmp/PHASE6-TEST-REPORT.md';
    Phase6_Test_Helpers::phase6_save_report_markdown($report, $report_file);

    // Code de sortie basé sur les résultats
    exit($report['failed'] === 0 ? 0 : 1);
}
