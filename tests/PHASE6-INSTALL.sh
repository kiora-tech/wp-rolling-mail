#!/bin/bash

##############################################################################
#
# Phase 6 - Installation Script
#
# Ce script copie tous les fichiers de test dans le container wp_axa
#
# Usage: ./PHASE6-INSTALL.sh
#
##############################################################################

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

CONTAINER_NAME="wp_axa"

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                                                            ║"
echo "║        PHASE 6 - INSTALLATION DES FICHIERS DE TEST        ║"
echo "║                                                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker n'est pas installé${NC}"
    exit 1
fi

# Vérifier que le container existe et est en cours d'exécution
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo -e "${RED}✗ Container '${CONTAINER_NAME}' n'est pas en cours d'exécution${NC}"
    echo -e "${YELLOW}  Démarrez le container avec: docker start ${CONTAINER_NAME}${NC}"
    exit 1
fi

echo -e "${BLUE}ℹ Container '${CONTAINER_NAME}' détecté et en cours d'exécution${NC}"
echo ""

# Fonction pour copier un fichier
copy_file() {
    local source=$1
    local dest=$2

    if [ ! -f "$source" ]; then
        echo -e "${RED}✗ Fichier source introuvable: $source${NC}"
        return 1
    fi

    docker cp "$source" "${CONTAINER_NAME}:${dest}" 2>/dev/null

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $(basename $source)"
        return 0
    else
        echo -e "${RED}✗${NC} $(basename $source)"
        return 1
    fi
}

# Copier les fichiers principaux
echo "Copie des fichiers principaux..."
copy_file "/tmp/phase6-test-suite.php" "/tmp/phase6-test-suite.php"
copy_file "/tmp/phase6-test-helpers.php" "/tmp/phase6-test-helpers.php"
copy_file "/tmp/PHASE6-RUNNER.sh" "/tmp/PHASE6-RUNNER.sh"
echo ""

# Créer le répertoire des tests dans le container
echo "Création du répertoire des tests..."
docker exec ${CONTAINER_NAME} mkdir -p /tmp/phase6-tests 2>/dev/null
echo -e "${GREEN}✓${NC} Répertoire /tmp/phase6-tests créé"
echo ""

# Copier les tests individuels
echo "Copie des tests individuels..."
for i in {01..10}; do
    TEST_FILE="/tmp/phase6-tests/test-${i}-"*.php
    if ls $TEST_FILE 1> /dev/null 2>&1; then
        for file in $TEST_FILE; do
            copy_file "$file" "/tmp/phase6-tests/$(basename $file)"
        done
    fi
done
echo ""

# Copier la documentation
echo "Copie de la documentation..."
copy_file "/tmp/PHASE6-TESTING-GUIDE.md" "/tmp/PHASE6-TESTING-GUIDE.md"
copy_file "/tmp/PHASE6-TEST-REPORT-TEMPLATE.md" "/tmp/PHASE6-TEST-REPORT-TEMPLATE.md"
copy_file "/tmp/PHASE6-README.md" "/tmp/PHASE6-README.md"
copy_file "/tmp/PHASE6-FILES-CREATED.txt" "/tmp/PHASE6-FILES-CREATED.txt"
echo ""

# Rendre le script runner exécutable dans le container
echo "Configuration des permissions..."
docker exec ${CONTAINER_NAME} chmod +x /tmp/PHASE6-RUNNER.sh 2>/dev/null
echo -e "${GREEN}✓${NC} PHASE6-RUNNER.sh rendu exécutable"
echo ""

# Vérification de l'installation
echo "════════════════════════════════════════════════════════════"
echo "Vérification de l'installation..."
echo "════════════════════════════════════════════════════════════"
echo ""

ERRORS=0

# Vérifier les fichiers principaux
docker exec ${CONTAINER_NAME} test -f /tmp/phase6-test-suite.php && echo -e "${GREEN}✓${NC} phase6-test-suite.php" || { echo -e "${RED}✗${NC} phase6-test-suite.php"; ERRORS=$((ERRORS+1)); }
docker exec ${CONTAINER_NAME} test -f /tmp/phase6-test-helpers.php && echo -e "${GREEN}✓${NC} phase6-test-helpers.php" || { echo -e "${RED}✗${NC} phase6-test-helpers.php"; ERRORS=$((ERRORS+1)); }
docker exec ${CONTAINER_NAME} test -x /tmp/PHASE6-RUNNER.sh && echo -e "${GREEN}✓${NC} PHASE6-RUNNER.sh (exécutable)" || { echo -e "${RED}✗${NC} PHASE6-RUNNER.sh"; ERRORS=$((ERRORS+1)); }

# Vérifier les tests
for i in {01..10}; do
    docker exec ${CONTAINER_NAME} bash -c "ls /tmp/phase6-tests/test-${i}-*.php" > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} Test ${i}" || { echo -e "${RED}✗${NC} Test ${i}"; ERRORS=$((ERRORS+1)); }
done

# Vérifier la documentation
docker exec ${CONTAINER_NAME} test -f /tmp/PHASE6-TESTING-GUIDE.md && echo -e "${GREEN}✓${NC} PHASE6-TESTING-GUIDE.md" || { echo -e "${RED}✗${NC} PHASE6-TESTING-GUIDE.md"; ERRORS=$((ERRORS+1)); }
docker exec ${CONTAINER_NAME} test -f /tmp/PHASE6-README.md && echo -e "${GREEN}✓${NC} PHASE6-README.md" || { echo -e "${RED}✗${NC} PHASE6-README.md"; ERRORS=$((ERRORS+1)); }

echo ""

# Vérifier la syntaxe PHP
echo "════════════════════════════════════════════════════════════"
echo "Vérification de la syntaxe PHP..."
echo "════════════════════════════════════════════════════════════"
echo ""

docker exec ${CONTAINER_NAME} php -l /tmp/phase6-test-suite.php > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} Syntaxe PHP valide: phase6-test-suite.php" || { echo -e "${RED}✗${NC} Erreur de syntaxe: phase6-test-suite.php"; ERRORS=$((ERRORS+1)); }
docker exec ${CONTAINER_NAME} php -l /tmp/phase6-test-helpers.php > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} Syntaxe PHP valide: phase6-test-helpers.php" || { echo -e "${RED}✗${NC} Erreur de syntaxe: phase6-test-helpers.php"; ERRORS=$((ERRORS+1)); }

echo ""

# Résumé final
echo "════════════════════════════════════════════════════════════"
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓ Installation réussie !${NC}"
    echo ""
    echo "Tous les fichiers ont été copiés et vérifiés avec succès."
    echo ""
    echo "Prochaines étapes :"
    echo "  1. Tester l'installation :"
    echo "     docker exec wp_axa /tmp/PHASE6-RUNNER.sh --list"
    echo ""
    echo "  2. Exécuter les tests :"
    echo "     docker exec wp_axa /tmp/PHASE6-RUNNER.sh"
    echo ""
    echo "  3. Consulter la documentation :"
    echo "     docker exec wp_axa cat /tmp/PHASE6-README.md"
    echo ""
else
    echo -e "${RED}✗ Installation terminée avec $ERRORS erreur(s)${NC}"
    echo ""
    echo "Veuillez vérifier les erreurs ci-dessus et réessayer."
    exit 1
fi

echo "════════════════════════════════════════════════════════════"
echo ""
