# Phase 6 - Guide des Tests de Non-Régression

## Vue d'ensemble

Cette suite de tests complète valide toutes les fonctionnalités du plugin **WP Rolling Mail** (Formidable Sequential Submissions). Elle couvre les comportements normaux, les cas limites et les situations d'erreur.

## Architecture des Tests

### Structure des fichiers

```
/tmp/
├── phase6-test-suite.php          # Script principal orchestrant tous les tests
├── phase6-test-helpers.php        # Fonctions utilitaires partagées
├── phase6-tests/                  # Répertoire contenant les tests individuels
│   ├── test-01-rotation-simple.php
│   ├── test-02-no-emails.php
│   ├── test-03-cc-independent.php
│   ├── test-04-performance.php
│   ├── test-05-rotation-coherence.php
│   ├── test-06-thematic-routing.php
│   ├── test-07-fallback.php
│   ├── test-08-normalization.php
│   ├── test-09-invalid-emails.php
│   └── test-10-invalid-field.php
├── PHASE6-RUNNER.sh               # Script bash pour exécution facile
├── PHASE6-TEST-REPORT.md          # Rapport généré (après exécution)
└── PHASE6-TESTING-GUIDE.md        # Ce guide

```

## Prérequis

### Environnement requis

1. **Docker** : Container `wp_axa` en cours d'exécution
2. **WordPress** : Installé et configuré dans le container
3. **Formidable Forms** : Plugin actif avec Form ID 3
4. **MailHog** : Serveur de test d'emails accessible sur `http://mailhog:8025`
5. **WP Rolling Mail** : Plugin actif
6. **WP-CLI** : Disponible dans le container

### Vérification des prérequis

```bash
# Vérifier que le container est en cours d'exécution
docker ps | grep wp_axa

# Vérifier WordPress
docker exec wp_axa wp core version --allow-root

# Vérifier Formidable Forms
docker exec wp_axa wp plugin list --allow-root | grep formidable

# Vérifier MailHog
curl http://localhost:8025/api/v2/messages
```

## Utilisation

### Méthode 1 : Script bash (recommandé)

Le script `PHASE6-RUNNER.sh` est la méthode la plus simple pour exécuter les tests.

```bash
# Rendre le script exécutable (une seule fois)
chmod +x /tmp/PHASE6-RUNNER.sh

# Exécuter tous les tests
/tmp/PHASE6-RUNNER.sh

# Exécuter un seul test
/tmp/PHASE6-RUNNER.sh --test 1

# Lister tous les tests disponibles
/tmp/PHASE6-RUNNER.sh --list

# Afficher le dernier rapport
/tmp/PHASE6-RUNNER.sh --report

# Nettoyer les données de test
/tmp/PHASE6-RUNNER.sh --clean

# Afficher l'aide
/tmp/PHASE6-RUNNER.sh --help
```

### Méthode 2 : WP-CLI direct

Pour une exécution plus fine ou un débogage :

```bash
# Exécuter tous les tests
docker exec wp_axa wp eval-file /tmp/phase6-test-suite.php --allow-root

# Exécuter un test spécifique (exemple : test #1)
docker exec wp_axa wp eval-file /tmp/phase6-test-suite.php 1 --allow-root

# Lister les tests
docker exec wp_axa wp eval-file /tmp/phase6-test-suite.php list --allow-root
```

### Méthode 3 : Test individuel

Pour déboguer un test spécifique :

```bash
docker exec wp_axa wp eval-file /tmp/phase6-tests/test-01-rotation-simple.php --allow-root
```

## Description des Tests

### Test 01 : Rotation Simple

**Objectif** : Vérifier que sans configuration thématique, la rotation fonctionne comme avant.

**Scénario** :
- Configurer 3 emails en rotation simple
- Envoyer 6 formulaires
- Vérifier distribution : 2 emails par destinataire
- Vérifier ordre : email1, email2, email3, email1, email2, email3

**Durée attendue** : ~2-3 secondes

### Test 02 : Pas d'emails configurés

**Objectif** : Vérifier la gestion gracieuse de l'absence d'emails.

**Scénario** :
- Vider toutes les listes d'emails
- Soumettre 1 formulaire
- Vérifier que `wp_mail()` n'est PAS appelé
- Vérifier log : `[FSS] CRITICAL ERROR: No valid email addresses`
- Vérifier return `false`

**Durée attendue** : ~1 seconde

### Test 03 : CC indépendants

**Objectif** : Vérifier que les CC reçoivent tous les emails.

**Scénario** :
- Configurer 2 emails en rotation + 1 CC
- Envoyer 2 formulaires
- Vérifier que CC reçoit 2 emails
- Vérifier que chaque email de rotation reçoit 1

**Durée attendue** : ~2 secondes

### Test 04 : Performance

**Objectif** : Tester les performances avec 10 soumissions rapides.

**Scénario** :
- Configurer 3 emails en rotation
- Mesurer le temps pour 10 soumissions
- Vérifier temps total < 5 secondes
- Vérifier distribution correcte (3-3-4 ou 4-3-3)

**Durée attendue** : ~3-5 secondes

### Test 05 : Cohérence de la rotation

**Objectif** : Vérifier que l'index n'est pas corrompu sous charge.

**Scénario** :
- Configurer 5 emails en rotation
- Soumettre 50 formulaires
- Vérifier que chaque email reçoit exactement 10 formulaires
- Vérifier logs : pas de warning sur index corrompu

**Durée attendue** : ~15-20 secondes

### Test 06 : Routage thématique

**Objectif** : Tester le routage thématique basique.

**Scénario** :
- Configurer field_id = 8
- Configurer liste pour "prevoyance" et "sante"
- Créer entry avec "Prévoyance"
- Vérifier routage vers liste "prevoyance"
- Vérifier que "sante" ne reçoit rien

**Durée attendue** : ~2-3 secondes

### Test 07 : Fallback

**Objectif** : Tester le fallback vers la liste principale.

**Scénario** :
- Configurer SEULEMENT liste pour "prevoyance"
- Créer entry avec "Epargne Retraite" (non configuré)
- Vérifier fallback vers liste principale
- Vérifier log : `[FSS] WARNING: Thematic list 'epargne_retraite' is configured but empty`

**Durée attendue** : ~2-3 secondes

### Test 08 : Normalisation

**Objectif** : Tester la normalisation des clés thématiques.

**Scénario** :
- Configurer liste pour clé "prevoyance"
- Tester avec "Prévoyance" (accent)
- Tester avec "Type : Prévoyance" (préfixe)
- Vérifier que les deux vont vers la même liste
- Vérifier logs : clé normalisée = "prevoyance"

**Durée attendue** : ~3-4 secondes

### Test 09 : Filtrage d'emails invalides

**Objectif** : Vérifier que les emails invalides sont filtrés.

**Scénario** :
- Configurer liste avec 2 emails valides + 5 invalides
- Soumettre 4 formulaires
- Vérifier que seuls les 2 valides reçoivent (2 chacun)
- Vérifier logs : warnings sur emails invalides

**Durée attendue** : ~2-3 secondes

### Test 10 : Field ID inexistant

**Objectif** : Tester la gestion des field ID invalides.

**Scénario** :
- Configurer field_id = 999 (inexistant)
- Créer entry et envoyer
- Vérifier fallback vers liste principale
- Vérifier log : `[FSS] WARNING: Field 999 does not exist`
- Tester aussi avec field_id = null

**Durée attendue** : ~2-3 secondes

## Interprétation des Résultats

### Rapport de test

Après l'exécution, un rapport est généré dans `/tmp/PHASE6-TEST-REPORT.md` :

```markdown
# Phase 6 - Test Report

**Date:** 2025-10-29 10:30:00

## Summary

- **Tests run:** 10
- **Passed:** 10 ✓
- **Failed:** 0
- **Success rate:** 100%
- **Execution time:** 28.5s

## Detailed Results

### Test: Test 01: Rotation Simple
- **Status:** ✓ PASSED
- **Duration:** 2.3s

[...]
```

### Codes de sortie

- `0` : Tous les tests ont réussi
- `1` : Au moins un test a échoué

### Logs

Les logs détaillés sont affichés dans la console pendant l'exécution. Format :

```
[INFO] Message d'information
[✓ SUCCESS] Opération réussie
[⚠ WARNING] Avertissement
[✗ ERROR] Erreur
```

## Dépannage

### Le container n'est pas trouvé

```bash
# Démarrer le container
docker start wp_axa

# Vérifier qu'il est en cours d'exécution
docker ps | grep wp_axa
```

### MailHog n'est pas accessible

```bash
# Vérifier que MailHog répond
curl http://localhost:8025/api/v2/messages

# Si le container MailHog est séparé, vérifier qu'il est démarré
docker ps | grep mailhog
```

### Formidable Forms n'est pas trouvé

```bash
# Vérifier que le plugin est installé et actif
docker exec wp_axa wp plugin list --allow-root | grep formidable

# Activer si nécessaire
docker exec wp_axa wp plugin activate formidable --allow-root
```

### Les fichiers de test sont introuvables

```bash
# Vérifier que les fichiers existent
docker exec wp_axa ls -la /tmp/phase6-*

# Les copier dans le container si nécessaire
docker cp /tmp/phase6-test-suite.php wp_axa:/tmp/
docker cp /tmp/phase6-test-helpers.php wp_axa:/tmp/
docker cp -r /tmp/phase6-tests wp_axa:/tmp/
```

### Un test échoue systématiquement

1. Exécuter le test individuellement pour voir les détails :
   ```bash
   /tmp/PHASE6-RUNNER.sh --test 1
   ```

2. Vérifier les logs WordPress :
   ```bash
   docker exec wp_axa tail -f /var/www/html/wp-content/debug.log
   ```

3. Nettoyer l'environnement et réessayer :
   ```bash
   /tmp/PHASE6-RUNNER.sh --clean
   /tmp/PHASE6-RUNNER.sh --test 1
   ```

### Problèmes de timing

Si les tests échouent de manière aléatoire (problèmes de timing avec MailHog), augmenter les délais dans `phase6-test-helpers.php` :

```php
// Augmenter les pauses
Phase6_Test_Helpers::sleep_ms(1000); // Au lieu de 500
```

## Maintenance

### Ajouter un nouveau test

1. Créer un nouveau fichier dans `/tmp/phase6-tests/` :
   ```php
   // test-11-mon-nouveau-test.php
   function phase6_test_11_mon_nouveau_test() {
       // Code du test
   }
   ```

2. L'enregistrer dans `phase6-test-suite.php` :
   ```php
   $this->tests[] = array(
       'file' => '/tmp/phase6-tests/test-11-mon-nouveau-test.php',
       'function' => 'phase6_test_11_mon_nouveau_test',
       'name' => 'Test 11: Mon nouveau test',
       'description' => 'Description'
   );
   ```

3. Tester :
   ```bash
   /tmp/PHASE6-RUNNER.sh --test 11
   ```

### Nettoyer après les tests

```bash
# Nettoyer automatiquement
/tmp/PHASE6-RUNNER.sh --clean

# Ou manuellement
docker exec wp_axa wp eval --allow-root 'delete_option("fss_emails");'
curl -X DELETE http://localhost:8025/api/v1/messages
```

## Bonnes Pratiques

1. **Toujours nettoyer** : Exécuter `--clean` avant une série de tests
2. **Tests isolés** : Chaque test doit être indépendant
3. **Pas de données de prod** : Utiliser des entries de test créées dynamiquement
4. **Logs verbeux** : Activer WP_DEBUG pour voir les logs détaillés
5. **Séquentiel** : Ne pas exécuter plusieurs suites en parallèle

## Performance

- **Suite complète** : ~30 secondes
- **Test individuel** : 1-20 secondes selon le test
- **Test le plus long** : Test 05 (cohérence, 50 entries)
- **Test le plus rapide** : Test 02 (pas d'emails)

## Support

Pour plus d'informations sur le plugin :
- Voir `/home/james/projets/wp_axa/wp-content/plugins/wp-rolling-mail/README.md`
- Consulter les logs : `docker exec wp_axa tail -f /var/www/html/wp-content/debug.log`

## Changelog

**Version 1.0.0** (2025-10-29)
- Création initiale de la suite de tests Phase 6
- 10 tests couvrant toutes les fonctionnalités
- Documentation complète
