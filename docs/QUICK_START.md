# Guide Démarrage Rapide - WP Rolling Mail

**Version condensée 1 page** | Pour utilisateurs pressés

---

## En 3 minutes : Comprendre le plugin

**WP Rolling Mail** distribue automatiquement vos formulaires Formidable entre plusieurs emails.

**Rotation simple :** Formulaire 1 → alice@, Formulaire 2 → bob@, Formulaire 3 → claire@, Formulaire 4 → alice@...

**Routage thématique (avancé) :** Les formulaires "Santé" vont à l'équipe santé, "Retraite" à l'équipe retraite, etc.

---

## Configuration de base en 5 étapes (5 minutes)

### Étape 1 : Accéder au plugin
```
Tableau de bord WordPress → Menu latéral → Sequential Submissions
```

### Étape 2 : Configurer le sujet
Section **"Email Subject"** → Tapez : `Nouveau formulaire de contact`

### Étape 3 : Ajouter les emails
Section **"Email Addresses"** → Cliquez 3× sur "Ajouter un autre email"
```
Email 1 : alice@entreprise.fr
Email 2 : bob@entreprise.fr
Email 3 : claire@entreprise.fr
```

### Étape 4 : Sauvegarder
Cliquez sur **"Enregistrer les modifications"** en bas de page

### Étape 5 : Tester
Soumettez 3 formulaires et vérifiez que chaque personne reçoit 1 email tour à tour.

**C'est fait !** Vos formulaires sont maintenant distribués équitablement.

---

## Configuration avancée (Routage thématique) en 7 étapes

**Prérequis :** Vous avez un champ radio dans Formidable avec des options (ex: Santé, Prévoyance, Retraite)

### Étape 1 : Activer le routage thématique
**"Thematic Filter Mode"** → Sélectionnez **"Enabled"** → Sauvegardez

### Étape 2 : Sélectionner le champ
**"Thematic Field Selection"** → Sélectionnez votre champ (ex: "Type de demande (ID: 8)")
→ La page se recharge automatiquement

### Étape 3 : Configurer les emails par thématique
Vous verrez apparaître des blocs pour chaque valeur (Santé, Prévoyance, Retraite)

**Pour CHAQUE bloc :**
- Cliquez sur "Ajouter un autre email"
- Tapez l'adresse de l'équipe
- Répétez pour ajouter plusieurs adresses (rotation au sein de l'équipe)

Exemple :
```
Santé :
  - sante1@entreprise.fr
  - sante2@entreprise.fr

Prévoyance :
  - prevoyance@entreprise.fr

Retraite :
  - retraite1@entreprise.fr
  - retraite2@entreprise.fr
  - retraite3@entreprise.fr
```

### Étape 4 : Configurer le fallback (IMPORTANT)
Section **"Email Addresses"** → Ajoutez au moins 1 email de secours :
```
contact-general@entreprise.fr
```

### Étape 5 : (Optionnel) Ajouter un CC
Section **"CC Email Addresses"** → Ajoutez une adresse qui reçoit TOUT :
```
direction@entreprise.fr
```

### Étape 6 : Sauvegarder
**"Enregistrer les modifications"**

### Étape 7 : Tester
- Soumettez un formulaire "Santé" → doit aller à sante1@ ou sante2@
- Soumettez un formulaire "Retraite" → doit aller à retraite@

---

## Checklist de vérification avant mise en production

- [ ] Au moins 1 email dans "Email Addresses" (liste principale)
- [ ] Sujet d'email configuré
- [ ] Testé avec vos vraies adresses (vérifiez que vous recevez bien)
- [ ] Vérifié que les emails n'arrivent pas en spam
- [ ] Si routage thématique : toutes les valeurs ont des emails configurés
- [ ] Si routage thématique : fallback configuré
- [ ] Documentation de votre config sauvegardée quelque part (capture d'écran)

---

## Problèmes courants - Solutions rapides

### Problème : Aucun email n'arrive

**Solution 1 :** Vérifiez qu'il y a au moins 1 email dans "Email Addresses"
**Solution 2 :** Testez l'envoi WordPress (plugin "Check Email")
**Solution 3 :** Activez les logs (voir guide complet section 8.4)

### Problème : Tous les emails vont à la même personne

**Solution :** Vérifiez qu'il y a PLUSIEURS emails dans la liste (pas juste 1)

### Problème : Le routage thématique ne marche pas

**Solution 1 :** Vérifiez que "Thematic Filter Mode" est sur "Enabled"
**Solution 2 :** Soumettez au moins 1 formulaire avec chaque option AVANT de configurer
**Solution 3 :** Vérifiez les logs (cherchez `[FSS]` dans `/wp-content/debug.log`)

### Problème : Comment voir ce qui se passe ?

**Solution : Activer les logs**

1. Éditez `wp-config.php`
2. Ajoutez :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
3. Soumettez un formulaire
4. Consultez `/wp-content/debug.log`
5. Cherchez les lignes `[FSS]`

---

## Glossaire éclair

| Terme | Signification |
|-------|---------------|
| **Rotation** | Distribution tour à tour (A → B → C → A) |
| **Thématique** | Type de demande (Santé, Retraite, etc.) |
| **Fallback** | Liste de secours si routage échoue |
| **CC** | Copie envoyée à TOUS les formulaires |
| **Field ID** | Numéro du champ Formidable (ex: 8) |

---

## Bonnes pratiques essentielles

1. **Toujours configurer un fallback** → Évite la perte d'emails
2. **Tester AVANT production** → Utilisez vos propres emails pour tester
3. **Documenter votre config** → Prenez une capture d'écran
4. **Utiliser des emails génériques** → equipe-sante@ plutôt que marie@
5. **Limiter les CC** → Max 1-2 adresses pour éviter le spam

---

## Cas d'usage typiques

### Cas 1 : PME avec 3 commerciaux
```
Configuration :
- Rotation simple (pas de thématique)
- 3 emails : commercial1@, commercial2@, commercial3@
- Pas de CC

Résultat : Chaque commercial reçoit 33% des leads
```

### Cas 2 : Compagnie d'assurance multi-départements
```
Configuration :
- Routage thématique activé
- Champ : "Type de demande" (ID 8)
- Santé : sante1@, sante2@
- Retraite : retraite@
- Fallback : contact@
- CC : direction@

Résultat : Chaque département reçoit ses demandes, direction supervise tout
```

### Cas 3 : Support avec archivage
```
Configuration :
- Rotation simple
- 3 emails : support1@, support2@, support3@
- CC : archive@entreprise.fr

Résultat : Tickets distribués équitablement, tous archivés automatiquement
```

---

## Pour aller plus loin

**Guide complet :** `/tmp/GUIDE_CONFIGURATION.md` (100+ pages, tous les détails)

**Exemples détaillés :** `/tmp/EXEMPLES_CONFIGURATION.md` (10 cas d'usage complets)

**Aide-mémoire imprimable :** `/tmp/AIDE_MEMOIRE.txt` (checklist papier)

---

**Support :** En cas de problème, consultez d'abord le guide complet section 8 (Dépannage) et section 10 (FAQ).

---

**Créé par Kiora Tech** | **Version 1.0** | 2025
