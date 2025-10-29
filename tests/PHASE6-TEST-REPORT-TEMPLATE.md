# Phase 6 - Test Report Template

Ce fichier est un template de rapport. Le rapport réel est généré automatiquement après l'exécution des tests dans `/tmp/PHASE6-TEST-REPORT.md`.

## Structure du Rapport

### En-tête

```markdown
# Phase 6 - Test Report

**Date:** YYYY-MM-DD HH:MM:SS
**WordPress Version:** X.X.X
**PHP Version:** X.X.X
**Plugin Version:** 1.0
```

### Résumé

```markdown
## Summary

- **Tests run:** X
- **Passed:** X ✓
- **Failed:** X ✗
- **Success rate:** XX%
- **Execution time:** XX.Xs
```

### Résultats détaillés

Pour chaque test :

```markdown
### Test: [Nom du test]

- **Status:** ✓ PASSED / ✗ FAILED
- **Duration:** X.Xs
- **Error:** [Message d'erreur si échec]
```

### Recommandations

```markdown
## Recommendations

[Si des tests ont échoué]
The following issues were detected:

- **[Nom du test]:** [Description de l'erreur]
- ...

[Si tous les tests ont réussi]
All tests passed successfully. No issues detected.
```

## Exemple de Rapport Complet (Succès)

```markdown
# Phase 6 - Test Report

**Date:** 2025-10-29 10:30:45

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

### Test: Test 02: No Emails Configured

- **Status:** ✓ PASSED
- **Duration:** 1.2s

### Test: Test 03: CC Independent

- **Status:** ✓ PASSED
- **Duration:** 2.1s

### Test: Test 04: Performance

- **Status:** ✓ PASSED
- **Duration:** 4.5s

### Test: Test 05: Rotation Coherence

- **Status:** ✓ PASSED
- **Duration:** 18.7s

### Test: Test 06: Thematic Routing

- **Status:** ✓ PASSED
- **Duration:** 2.8s

### Test: Test 07: Fallback

- **Status:** ✓ PASSED
- **Duration:** 2.5s

### Test: Test 08: Key Normalization

- **Status:** ✓ PASSED
- **Duration:** 3.4s

### Test: Test 09: Invalid Email Filtering

- **Status:** ✓ PASSED
- **Duration:** 2.7s

### Test: Test 10: Invalid Field ID

- **Status:** ✓ PASSED
- **Duration:** 2.3s

## Recommendations

All tests passed successfully. No issues detected.

The plugin is functioning correctly:
- ✓ Simple rotation working as expected
- ✓ Error handling is robust
- ✓ CC recipients working independently
- ✓ Performance is acceptable (<5s for 10 submissions)
- ✓ Rotation remains coherent under load
- ✓ Thematic routing functioning correctly
- ✓ Fallback mechanism working properly
- ✓ Key normalization working as designed
- ✓ Invalid emails are properly filtered
- ✓ Invalid field IDs are handled gracefully
```

## Exemple de Rapport (Avec Échecs)

```markdown
# Phase 6 - Test Report

**Date:** 2025-10-29 11:15:30

## Summary

- **Tests run:** 10
- **Passed:** 8 ✓
- **Failed:** 2 ✗
- **Success rate:** 80%
- **Execution time:** 26.3s

## Detailed Results

### Test: Test 01: Rotation Simple

- **Status:** ✓ PASSED
- **Duration:** 2.3s

### Test: Test 02: No Emails Configured

- **Status:** ✓ PASSED
- **Duration:** 1.2s

### Test: Test 03: CC Independent

- **Status:** ✗ FAILED
- **Duration:** 2.1s
- **Error:** CC recipients did not receive all emails (expected 2, got 1)

### Test: Test 04: Performance

- **Status:** ✗ FAILED
- **Duration:** 6.8s
- **Error:** Execution too slow: 6.8s (expected < 5s)

### Test: Test 05: Rotation Coherence

- **Status:** ✓ PASSED
- **Duration:** 18.7s

### Test: Test 06: Thematic Routing

- **Status:** ✓ PASSED
- **Duration:** 2.8s

### Test: Test 07: Fallback

- **Status:** ✓ PASSED
- **Duration:** 2.5s

### Test: Test 08: Key Normalization

- **Status:** ✓ PASSED
- **Duration:** 3.4s

### Test: Test 09: Invalid Email Filtering

- **Status:** ✓ PASSED
- **Duration:** 2.7s

### Test: Test 10: Invalid Field ID

- **Status:** ✓ PASSED
- **Duration:** 2.3s

## Recommendations

The following issues were detected:

- **Test 03: CC Independent:** CC recipients are not receiving all emails. Check the email sending logic to ensure CC addresses are properly added to the wp_mail() call.

- **Test 04: Performance:** Performance issue detected. Execution time of 6.8s exceeds the 5s threshold for 10 submissions. Consider:
  - Optimizing database queries
  - Reducing the number of option reads/writes
  - Caching email lists between submissions
  - Reviewing SMTP configuration

### Action Items

1. **Priority High**: Fix CC recipient issue
   - Review FSS_Email_Handler::send_sequential_emails()
   - Check how CC emails are merged with TO addresses
   - Verify wp_mail() is receiving all recipients

2. **Priority Medium**: Optimize performance
   - Profile the email sending process
   - Check if SMTP server is responding slowly
   - Consider implementing email queuing for high-volume submissions

3. **Re-test**: Run tests again after fixes:
   ```bash
   /tmp/PHASE6-RUNNER.sh --test 3
   /tmp/PHASE6-RUNNER.sh --test 4
   ```
```

## Interprétation

### Status Codes

- **✓ PASSED** : Le test a réussi tous ses critères
- **✗ FAILED** : Au moins un critère n'a pas été satisfait

### Success Rate

- **100%** : Parfait, aucun problème détecté
- **90-99%** : Bon, mais quelques problèmes mineurs
- **80-89%** : Acceptable, nécessite attention
- **< 80%** : Problématique, révision majeure requise

### Execution Time

- **< 30s** : Excellent
- **30-60s** : Acceptable
- **> 60s** : Peut indiquer des problèmes de performance

### Durée par test

- **< 2s** : Rapide
- **2-5s** : Normal
- **5-20s** : Lent (normal pour les tests de charge)
- **> 20s** : Très lent (vérifier l'optimisation)

## Utilisation du Rapport

### Afficher le rapport

```bash
# Méthode 1 : Avec le runner
/tmp/PHASE6-RUNNER.sh --report

# Méthode 2 : Directement
docker exec wp_axa cat /tmp/PHASE6-TEST-REPORT.md

# Méthode 3 : Copier en local
docker cp wp_axa:/tmp/PHASE6-TEST-REPORT.md ./PHASE6-TEST-REPORT.md
```

### Sauvegarder pour référence

```bash
# Sauvegarder avec timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker cp wp_axa:/tmp/PHASE6-TEST-REPORT.md ./reports/PHASE6-TEST-REPORT-${TIMESTAMP}.md
```

### Partager le rapport

Le rapport en Markdown peut être :
- Copié dans la documentation du projet
- Partagé dans un ticket/issue GitHub
- Converti en PDF avec pandoc
- Intégré dans un système CI/CD

## Automatisation

### CI/CD Integration

Exemple de script pour GitLab CI :

```yaml
test:
  stage: test
  script:
    - docker start wp_axa
    - /tmp/PHASE6-RUNNER.sh
  artifacts:
    when: always
    paths:
      - /tmp/PHASE6-TEST-REPORT.md
    expire_in: 30 days
```

### Notifications

Exemple pour envoyer une notification en cas d'échec :

```bash
#!/bin/bash
/tmp/PHASE6-RUNNER.sh
EXIT_CODE=$?

if [ $EXIT_CODE -ne 0 ]; then
    # Envoyer notification (email, Slack, etc.)
    curl -X POST https://hooks.slack.com/... \
        -H 'Content-Type: application/json' \
        -d '{"text":"Phase 6 tests FAILED! Check report."}'
fi
```

## Notes

- Le rapport est régénéré à chaque exécution de la suite complète
- Les tests individuels ne génèrent pas de rapport Markdown (seulement console)
- Le rapport est stocké dans le container, pensez à le sauvegarder si nécessaire
- Format Markdown pour faciliter l'intégration dans la documentation
