#!/bin/bash

##############################################################################
#
# Phase 6 - Test Runner Script
#
# Ce script permet d'exécuter facilement la suite de tests de non-régression
# pour le plugin WP Rolling Mail.
#
# Usage:
#   ./PHASE6-RUNNER.sh              # Exécuter tous les tests
#   ./PHASE6-RUNNER.sh --test 1     # Exécuter le test #1 uniquement
#   ./PHASE6-RUNNER.sh --list       # Lister tous les tests disponibles
#   ./PHASE6-RUNNER.sh --help       # Afficher l'aide
#
##############################################################################

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
CONTAINER_NAME="wp_axa"
TEST_SUITE_FILE="/tmp/phase6-test-suite.php"
REPORT_FILE="/tmp/PHASE6-TEST-REPORT.md"

##############################################################################
# Fonctions utilitaires
##############################################################################

print_header() {
    echo ""
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║                                                            ║"
    echo "║          WP ROLLING MAIL - PHASE 6 TEST RUNNER            ║"
    echo "║                                                            ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed or not in PATH"
        exit 1
    fi
}

check_container() {
    if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
        print_error "Container '${CONTAINER_NAME}' is not running"
        print_info "Start the container with: docker start ${CONTAINER_NAME}"
        exit 1
    fi
}

check_test_files() {
    if ! docker exec ${CONTAINER_NAME} test -f ${TEST_SUITE_FILE} 2>/dev/null; then
        print_error "Test suite file not found: ${TEST_SUITE_FILE}"
        print_info "Please ensure all test files are in place in /tmp/"
        exit 1
    fi
}

##############################################################################
# Commandes principales
##############################################################################

run_all_tests() {
    print_header
    print_info "Running all Phase 6 tests..."
    echo ""

    docker exec ${CONTAINER_NAME} wp eval-file ${TEST_SUITE_FILE} --allow-root

    EXIT_CODE=$?

    echo ""
    echo "════════════════════════════════════════════════════════════"
    echo ""

    if [ $EXIT_CODE -eq 0 ]; then
        print_success "All tests PASSED"
    else
        print_error "Some tests FAILED"
    fi

    # Vérifier si le rapport a été généré
    if docker exec ${CONTAINER_NAME} test -f ${REPORT_FILE} 2>/dev/null; then
        print_info "Test report saved to: ${REPORT_FILE}"
        print_info "View it with: docker exec ${CONTAINER_NAME} cat ${REPORT_FILE}"
    fi

    echo ""
    exit $EXIT_CODE
}

run_single_test() {
    local TEST_NUM=$1

    if ! [[ "$TEST_NUM" =~ ^[0-9]+$ ]]; then
        print_error "Invalid test number: $TEST_NUM"
        print_info "Test number must be between 1 and 10"
        exit 1
    fi

    if [ "$TEST_NUM" -lt 1 ] || [ "$TEST_NUM" -gt 10 ]; then
        print_error "Test number must be between 1 and 10"
        exit 1
    fi

    print_header
    print_info "Running Test #${TEST_NUM}..."
    echo ""

    docker exec ${CONTAINER_NAME} wp eval-file ${TEST_SUITE_FILE} ${TEST_NUM} --allow-root

    EXIT_CODE=$?

    echo ""
    echo "════════════════════════════════════════════════════════════"
    echo ""

    if [ $EXIT_CODE -eq 0 ]; then
        print_success "Test #${TEST_NUM} PASSED"
    else
        print_error "Test #${TEST_NUM} FAILED"
    fi

    echo ""
    exit $EXIT_CODE
}

list_tests() {
    print_header
    print_info "Available tests:"
    echo ""

    docker exec ${CONTAINER_NAME} wp eval-file ${TEST_SUITE_FILE} list --allow-root

    echo ""
}

show_report() {
    if ! docker exec ${CONTAINER_NAME} test -f ${REPORT_FILE} 2>/dev/null; then
        print_warning "No report found at ${REPORT_FILE}"
        print_info "Run the tests first to generate a report"
        exit 1
    fi

    print_header
    print_info "Displaying test report:"
    echo ""

    docker exec ${CONTAINER_NAME} cat ${REPORT_FILE}

    echo ""
}

clean_test_data() {
    print_header
    print_warning "Cleaning test data..."
    echo ""

    # Supprimer le rapport
    if docker exec ${CONTAINER_NAME} test -f ${REPORT_FILE} 2>/dev/null; then
        docker exec ${CONTAINER_NAME} rm ${REPORT_FILE}
        print_success "Removed test report"
    fi

    # Nettoyer MailHog
    print_info "Clearing MailHog..."
    curl -X DELETE http://localhost:8025/api/v1/messages 2>/dev/null
    print_success "MailHog cleared"

    # Nettoyer les options WordPress
    print_info "Cleaning WordPress options..."
    docker exec ${CONTAINER_NAME} wp eval --allow-root '
        delete_option("fss_emails");
        delete_option("fss_email_cc");
        delete_option("fss_current_index");
        delete_option("fss_thematic_field_id");
        delete_option("fss_thematic_emails");
        delete_option("fss_thematic_filter_mode");
        delete_option("fss_form_filter_mode");
        delete_option("fss_form_ids");
        echo "Options cleaned\n";
    '

    echo ""
    print_success "Test data cleaned successfully"
    echo ""
}

show_help() {
    print_header

    cat << EOF
Usage: ./PHASE6-RUNNER.sh [COMMAND] [OPTIONS]

COMMANDS:
    (no command)           Run all tests (default)
    --test NUM             Run a specific test (1-10)
    --list                 List all available tests
    --report               Display the last test report
    --clean                Clean test data and reset environment
    --help                 Show this help message

EXAMPLES:
    ./PHASE6-RUNNER.sh                    # Run all tests
    ./PHASE6-RUNNER.sh --test 1           # Run test #1 only
    ./PHASE6-RUNNER.sh --list             # List all tests
    ./PHASE6-RUNNER.sh --report           # Show last report
    ./PHASE6-RUNNER.sh --clean            # Clean test data

ENVIRONMENT:
    Container: ${CONTAINER_NAME}
    Test Suite: ${TEST_SUITE_FILE}
    Report: ${REPORT_FILE}

PREREQUISITES:
    - Docker must be installed and running
    - Container '${CONTAINER_NAME}' must be running
    - All test files must be in /tmp/ inside the container
    - MailHog must be accessible at http://localhost:8025
    - WordPress with Formidable Forms must be installed

For more information, see /tmp/PHASE6-TESTING-GUIDE.md

EOF
}

##############################################################################
# Point d'entrée principal
##############################################################################

# Vérifications préliminaires (sauf pour --help)
if [ "$1" != "--help" ] && [ "$1" != "-h" ]; then
    check_docker
    check_container
    check_test_files
fi

# Parser les arguments
case "$1" in
    "")
        run_all_tests
        ;;
    --test|-t)
        if [ -z "$2" ]; then
            print_error "Test number required"
            print_info "Usage: ./PHASE6-RUNNER.sh --test NUM"
            exit 1
        fi
        run_single_test "$2"
        ;;
    --list|-l)
        list_tests
        ;;
    --report|-r)
        show_report
        ;;
    --clean|-c)
        clean_test_data
        ;;
    --help|-h)
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        print_info "Use --help to see available commands"
        exit 1
        ;;
esac
