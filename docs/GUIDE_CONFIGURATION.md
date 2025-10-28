# Guide de Configuration - WP Rolling Mail

**Version 1.0** | Plugin WordPress de rotation d'emails pour Formidable Forms

---

## 1. INTRODUCTION

### Qu'est-ce que WP Rolling Mail ?

**WP Rolling Mail** (également appelé "Formidable Sequential Submissions") est un plugin WordPress qui automatise la distribution équitable des formulaires web entre plusieurs destinataires. Au lieu d'envoyer tous les formulaires à une seule personne, le plugin les distribue tour à tour (rotation séquentielle) à une liste d'adresses email configurables.

**Exemple concret :** Vous avez 3 commerciaux qui doivent recevoir les demandes de contact. Au lieu que le premier reçoive tous les leads, le plugin distribue automatiquement : lead 1 → commercial A, lead 2 → commercial B, lead 3 → commercial C, lead 4 → retour au commercial A, etc.

Le plugin inclut également un **routage thématique avancé** qui permet d'envoyer différents types de demandes à des équipes spécialisées (ex: les demandes "Santé" vers l'équipe santé, les demandes "Retraite" vers l'équipe retraite).

### Qui devrait lire ce guide ?

Ce guide s'adresse aux **administrateurs WordPress** qui doivent configurer et gérer la distribution des formulaires. Aucune compétence technique avancée n'est requise - le guide explique chaque étape en détail avec des exemples concrets.

Vous devez simplement avoir accès au tableau de bord WordPress de votre site avec les droits d'administrateur.

### Prérequis techniques

Avant d'utiliser WP Rolling Mail, assurez-vous que votre site possède :

- **WordPress** version 5.0 ou supérieure
- **Formidable Forms** (version gratuite ou Pro) - plugin de création de formulaires
- **Accès administrateur** au tableau de bord WordPress
- **Configuration email fonctionnelle** - votre WordPress doit pouvoir envoyer des emails (testez avec un formulaire classique d'abord)

---

## 2. CONCEPTS CLÉS

Avant de commencer la configuration, comprenons les concepts fondamentaux du plugin.

### 2.1 Rotation d'emails

#### Qu'est-ce que c'est ?

La rotation d'emails est un système de distribution équitable qui envoie chaque nouveau formulaire à une adresse différente en suivant un ordre séquentiel. Une fois arrivé au bout de la liste, le système recommence au début.

#### Pourquoi c'est utile ?

Sans rotation, tous les formulaires arrivent à la même personne, ce qui crée :
- Une surcharge de travail pour une seule personne
- Une distribution inégale des opportunités commerciales
- Un risque si cette personne est absente ou en congé

Avec la rotation, vous assurez :
- Une distribution équitable automatique
- Une meilleure réactivité (charge de travail répartie)
- Une continuité de service

#### Exemple concret : 3 commerciaux qui reçoivent des leads tour à tour

Imaginons une entreprise avec 3 commerciaux : Alice, Bob et Claire.

**Configuration :**
- alice@entreprise.fr
- bob@entreprise.fr
- claire@entreprise.fr

**Résultat :**
- Formulaire n°1 (lundi 10h) → Alice
- Formulaire n°2 (lundi 14h) → Bob
- Formulaire n°3 (mardi 9h) → Claire
- Formulaire n°4 (mardi 11h) → Alice (retour au début)
- Formulaire n°5 (mercredi 8h) → Bob
- Et ainsi de suite...

Sur 30 demandes, chacun recevra environ 10 leads de façon automatique et équitable.

---

### 2.2 Routage thématique

#### Qu'est-ce que c'est ?

Le routage thématique permet d'envoyer automatiquement les formulaires vers différentes listes de rotation selon une catégorie choisie par l'utilisateur dans le formulaire.

Au lieu d'avoir une seule liste pour tous les formulaires, vous pouvez avoir plusieurs listes spécialisées. Le plugin lit une valeur dans le formulaire (par exemple un champ radio "Type de demande") et route automatiquement vers la bonne équipe.

#### Différence avec rotation simple

**Rotation simple :**
- Une seule liste d'emails
- Tous les formulaires vont dans la même rotation
- Pas de distinction par type de demande

**Routage thématique :**
- Plusieurs listes d'emails (une par thématique)
- Chaque type de demande a sa propre rotation
- Distribution intelligente selon le contenu du formulaire

#### Exemple concret : Département Santé vs Département Retraite

**Situation :** Vous êtes une compagnie d'assurance avec 3 départements spécialisés.

**Dans votre formulaire Formidable, vous avez un champ radio :**
```
Type de demande :
○ Santé / Mutuelle
○ Prévoyance
○ Épargne Retraite
```

**Configuration du routage thématique :**

**Équipe Santé :**
- sante1@entreprise.fr
- sante2@entreprise.fr

**Équipe Prévoyance :**
- prevoyance@entreprise.fr

**Équipe Retraite :**
- retraite1@entreprise.fr
- retraite2@entreprise.fr
- retraite3@entreprise.fr

**Résultat automatique :**

| Formulaire | Valeur choisie | Destinataire |
|------------|----------------|--------------|
| #1 | Santé | sante1@entreprise.fr |
| #2 | Santé | sante2@entreprise.fr (rotation) |
| #3 | Retraite | retraite1@entreprise.fr |
| #4 | Prévoyance | prevoyance@entreprise.fr |
| #5 | Santé | sante1@entreprise.fr (retour au début) |
| #6 | Retraite | retraite2@entreprise.fr (rotation) |

Chaque thématique a sa propre rotation indépendante. Les experts Santé ne reçoivent que les demandes Santé, les experts Retraite ne reçoivent que les demandes Retraite, etc.

---

### 2.3 Liste de fallback (principale)

#### Qu'est-ce que c'est ?

La liste de fallback (ou "liste principale") est une liste d'emails de secours utilisée quand le routage thématique ne peut pas fonctionner.

C'est votre **filet de sécurité** : si le plugin ne sait pas où envoyer un formulaire, il l'envoie à cette liste.

#### Quand est-elle utilisée ?

La liste principale est utilisée dans ces situations :

1. **Routage thématique désactivé** → Tous les formulaires vont à cette liste
2. **Champ thématique non rempli** → Si l'utilisateur ne sélectionne rien dans le champ "Type de demande"
3. **Valeur inattendue** → Si une nouvelle valeur apparaît qui n'a pas été configurée
4. **Liste thématique vide** → Si une thématique n'a aucun email configuré
5. **Erreur de configuration** → Si le champ thématique configuré n'existe plus dans Formidable

#### Pourquoi c'est important

Sans liste de fallback, certains formulaires pourraient ne jamais être envoyés si le routage thématique échoue. La liste principale garantit qu'**aucun formulaire n'est perdu**.

**Recommandation :** Même si vous utilisez le routage thématique, configurez toujours au moins une adresse dans la liste principale (par exemple : contact-general@entreprise.fr ou direction@entreprise.fr).

---

### 2.4 CC (Copies Carbone)

#### À quoi servent-elles ?

Les adresses en CC (Copie Carbone) reçoivent une copie de **TOUS** les formulaires envoyés, quelle que soit la thématique ou la rotation.

Contrairement aux listes de rotation où chaque email ne reçoit qu'une partie des formulaires, les CC reçoivent absolument tout.

#### Cas d'usage typiques

**1. Supervision managériale**
```
Liste principale : commercial1@, commercial2@, commercial3@
CC : directeur-commercial@entreprise.fr

Résultat : Les commerciaux reçoivent les leads en rotation,
mais le directeur reçoit une copie de TOUT pour superviser.
```

**2. Archivage centralisé**
```
Liste principale : support1@, support2@
CC : archive@entreprise.fr

Résultat : Les agents support traitent les tickets en rotation,
mais tous les tickets sont archivés dans la boîte "archive@".
```

**3. Intégration CRM automatique**
```
Liste principale : vente@entreprise.fr
CC : crm-ingest@entreprise.fr

Résultat : L'équipe vente reçoit les leads normalement,
mais une copie est envoyée au CRM qui l'ingère automatiquement
via une adresse email spéciale.
```

**4. Direction et conformité**
```
Liste thématique Santé : sante1@, sante2@
Liste thématique Retraite : retraite@
CC : conformite@entreprise.fr

Résultat : Chaque département reçoit ses demandes,
mais le service conformité reçoit tout pour audit.
```

**Important :** Utilisez les CC avec parcimonie. Si vous mettez 5 adresses en CC, chaque formulaire génèrera 5 emails supplémentaires (plus l'email principal = 6 emails au total).

---

## 3. ACCÉDER AU PLUGIN

### Localisation dans WordPress

Une fois le plugin installé et activé, accédez à la configuration :

**Chemin complet :**
```
Tableau de bord WordPress
    → Menu latéral gauche
    → Sequential Submissions (icône email 📧)
```

Le menu apparaît dans la barre latérale principale, généralement en position haute (juste après le tableau de bord).

**Note :** Si vous ne voyez pas ce menu, vérifiez que :
1. Le plugin est bien activé (Extensions → Plugins installés → "Formidable Sequential Submissions" doit être activé)
2. Votre compte a les droits d'administrateur
3. Formidable Forms est installé et activé

### Description de la page de configuration

Lorsque vous cliquez sur "Sequential Submissions", vous arrivez sur une page avec plusieurs sections :

**Section 1 : Form Filter Mode**
→ Choisir quels formulaires utilisent la rotation (tous ou seulement certains)

**Section 2 : Thematic Filter Mode**
→ Activer/désactiver le routage par thématique

**Section 3 : Thematic Field Selection**
→ Choisir le champ Formidable qui détermine la thématique (ex: "Type de demande")

**Section 4 : Thematic Email Mappings**
→ Configurer les emails pour chaque thématique (Santé, Prévoyance, Retraite, etc.)

**Section 5 : Email Subject**
→ Définir le sujet de tous les emails

**Section 6 : Email Addresses (liste principale)**
→ La liste de fallback / rotation principale

**Section 7 : CC Email Addresses**
→ Les adresses qui reçoivent TOUT en copie

**Bouton en bas :** "Enregistrer les modifications" (TOUJOURS sauvegarder après chaque modification)

---

## 4. CONFIGURATION DE BASE (Scénario simple)

### Objectif

Mettre en place une rotation simple entre 3 adresses email, sans routage thématique. Idéal pour débuter avec le plugin.

**Ce que vous allez créer :**
- 3 commerciaux reçoivent les formulaires tour à tour
- Pas de distinction par type de demande
- Distribution équitable automatique

---

### Étape 1 : Email Subject (Sujet des emails)

**1.1** Accédez à la page de configuration : `Tableau de bord → Sequential Submissions`

**1.2** Descendez jusqu'à la section **"Email Subject"**

**1.3** Dans le champ texte, saisissez le sujet souhaité :
```
Nouveau formulaire de contact
```
Ou bien :
```
Demande d'information - Site web
```

**1.4** Cliquez sur le bouton **"Enregistrer les modifications"** en bas de page

**1.5** Vérifiez qu'un message de confirmation apparaît en haut de la page (fond vert)

**Pourquoi cette étape ?** Le sujet permet au destinataire de savoir instantanément de quoi il s'agit dans sa boîte mail. Choisissez un sujet clair et professionnel.

---

### Étape 2 : Email Addresses (Configuration de la rotation)

**2.1** Dans la même page, localisez la section **"Email Addresses"**

**2.2** Cette section affiche la liste principale de rotation. Par défaut, elle peut être vide.

**2.3** Cliquez sur le bouton **"Ajouter un autre email"** (en anglais : "add another email")

**2.4** Un nouveau champ email apparaît. Saisissez la première adresse :
```
commercial1@entreprise.fr
```

**2.5** Cliquez à nouveau sur **"Ajouter un autre email"**

**2.6** Saisissez la deuxième adresse :
```
commercial2@entreprise.fr
```

**2.7** Cliquez une troisième fois sur **"Ajouter un autre email"**

**2.8** Saisissez la troisième adresse :
```
commercial3@entreprise.fr
```

**Votre configuration devrait ressembler à :**
```
Email Addresses
---------------
Email 1: commercial1@entreprise.fr [🗑]
Email 2: commercial2@entreprise.fr [🗑]
Email 3: commercial3@entreprise.fr [🗑]

[Ajouter un autre email]
```

**2.9** Cliquez sur **"Enregistrer les modifications"**

**2.10** Attendez le message de confirmation (fond vert en haut)

**Astuce :** Vous pouvez ajouter autant d'emails que nécessaire (5, 10, 20...). La rotation s'adapte automatiquement. Pour supprimer un email, cliquez sur l'icône 🗑 (poubelle) à droite de l'adresse.

---

### Étape 3 : Vérification de la configuration

Avant de tester en réel, vérifiez que tout est correct :

**3.1** Vérifiez que la section **"Thematic Filter Mode"** est sur **"Disabled"**
→ Vous ne voulez pas de routage thématique pour cette configuration simple

**3.2** Vérifiez que la section **"Form Filter Mode"** est sur **"All forms"**
→ Tous vos formulaires Formidable utiliseront la rotation

**3.3** La section **"CC Email Addresses"** peut rester vide pour l'instant

**3.4** Sauvegardez une dernière fois si vous avez fait des changements

---

### Étape 4 : Test de la rotation

Maintenant, testons que la rotation fonctionne correctement.

**4.1** Allez sur une page de votre site contenant un formulaire Formidable

**4.2** Remplissez le formulaire avec des données de test (utilisez vos vraies adresses email pour vérifier)

**4.3** Soumettez le formulaire (premier envoi)

**4.4** Vérifiez que **commercial1@entreprise.fr** a reçu l'email

**4.5** Soumettez le formulaire une deuxième fois (avec d'autres données)

**4.6** Vérifiez que **commercial2@entreprise.fr** a reçu ce second email

**4.7** Soumettez une troisième fois

**4.8** Vérifiez que **commercial3@entreprise.fr** a reçu ce troisième email

**4.9** Soumettez une quatrième fois

**4.10** Vérifiez que l'email est revenu à **commercial1@entreprise.fr** (rotation bouclée)

**En cas de problème :**
- Vérifiez vos adresses email (pas de typo ?)
- Vérifiez que WordPress peut envoyer des emails (testez avec un formulaire classique)
- Consultez la section "Dépannage" de ce guide (section 8)

---

### Résultat attendu

Après ces étapes, voici ce qui se passe automatiquement à chaque soumission de formulaire :

```
Formulaire #1 → commercial1@entreprise.fr
Formulaire #2 → commercial2@entreprise.fr
Formulaire #3 → commercial3@entreprise.fr
Formulaire #4 → commercial1@entreprise.fr (rotation complète, retour au début)
Formulaire #5 → commercial2@entreprise.fr
Formulaire #6 → commercial3@entreprise.fr
Formulaire #7 → commercial1@entreprise.fr
...et ainsi de suite
```

**Distribution sur 30 formulaires :**
- commercial1@ recevra 10 formulaires
- commercial2@ recevra 10 formulaires
- commercial3@ recevra 10 formulaires

**Félicitations !** Vous avez configuré avec succès la rotation de base. Tous les nouveaux formulaires seront automatiquement distribués de façon équitable.

---

## 5. CONFIGURATION AVANCÉE (Routage thématique)

### Objectif

Mettre en place un système de routage intelligent qui envoie automatiquement :
- Les formulaires "Santé" vers l'équipe Santé
- Les formulaires "Prévoyance" vers l'équipe Prévoyance
- Les formulaires "Retraite" vers l'équipe Retraite

Chaque équipe peut avoir plusieurs membres (rotation indépendante).

### Prérequis

Avant de configurer le routage thématique, vous devez avoir dans Formidable Forms :

**1. Un champ de choix unique** (radio button ou liste déroulante)

**Exemple de configuration dans Formidable :**
```
Nom du champ : Type de demande
Type de champ : Radio Button
Options :
  ○ Santé / Mutuelle
  ○ Prévoyance
  ○ Épargne Retraite
```

**2. Notez l'ID du champ**

Pour trouver l'ID du champ dans Formidable Forms :
1. Allez dans Formidable → Formulaires
2. Modifiez votre formulaire
3. Cliquez sur le champ "Type de demande"
4. Dans l'encadré de droite, cherchez "ID du champ" ou "Field ID"
5. Notez ce numéro (par exemple : 8)

**Important :** Le champ DOIT être de type radio button ou liste déroulante (select). Les champs texte, checkbox multiples ou autres types ne sont pas compatibles avec le routage thématique.

---

### Étape 1 : Activer le routage thématique

**1.1** Accédez à `Tableau de bord → Sequential Submissions`

**1.2** Localisez la section **"Thematic Filter Mode"** (en haut de la page)

**1.3** Dans le menu déroulant, sélectionnez **"Enabled (route by thematic field)"**

**1.4** Cliquez sur **"Enregistrer les modifications"**

**1.5** La page se recharge. Vous verrez maintenant de nouvelles sections apparaître.

**Ce qui se passe :** En activant ce mode, vous dites au plugin : "Je veux router les formulaires selon une catégorie, pas juste faire une rotation simple."

---

### Étape 2 : Sélectionner le champ thématique

**2.1** Juste en dessous, localisez la section **"Thematic Field Selection"**

**2.2** Vous verrez un message explicatif bleu qui dit : "Un champ thématique permet de router automatiquement les emails..."

**2.3** Dans le menu déroulant, recherchez votre champ "Type de demande"

Le format affiché sera quelque chose comme :
```
Type de demande (ID: 8, Form: Formulaire de contact, Type: radio)
```

**2.4** Sélectionnez ce champ dans la liste

**2.5** Attendez quelques secondes - un message "⏳ Chargement des valeurs du champ..." apparaît

**2.6** La page se recharge automatiquement

**Ce qui se passe :** Le plugin va lire toutes les soumissions existantes de votre formulaire et détecter toutes les valeurs qui ont été choisies dans ce champ (Santé, Prévoyance, Retraite, etc.). Ces valeurs apparaîtront dans l'étape suivante.

**Note :** Si vous n'avez encore aucune soumission de formulaire, aucune valeur ne sera détectée. Dans ce cas, soumettez au moins un formulaire de test avec chaque option avant de continuer.

---

### Étape 3 : Configurer les emails par thématique

Après le rechargement de la page, une nouvelle section apparaît : **"Thematic Email Mappings"** (Configuration des emails par thématique).

Cette section affiche automatiquement toutes les valeurs détectées. Dans notre exemple, vous devriez voir 3 blocs :

#### Bloc 1 : Santé / Mutuelle

```
┌─────────────────────────────────────────────┐
│ Santé / Mutuelle (12 entries)              │
│ Normalized key: sante_mutuelle             │
├─────────────────────────────────────────────┤
│ [Aucun champ email pour l'instant]         │
│                                             │
│ [Ajouter un autre email]                   │
│                                             │
│ ⚠️ Aucun email configuré pour cette valeur │
└─────────────────────────────────────────────┘
```

**3.1** Cliquez sur le bouton **"Ajouter un autre email"** dans le bloc Santé

**3.2** Saisissez la première adresse de l'équipe Santé :
```
sante1@entreprise.fr
```

**3.3** Cliquez à nouveau sur **"Ajouter un autre email"**

**3.4** Saisissez la deuxième adresse :
```
sante2@entreprise.fr
```

Le bloc Santé devrait maintenant ressembler à :
```
┌─────────────────────────────────────────────┐
│ Santé / Mutuelle (12 entries)              │
│ Normalized key: sante_mutuelle             │
├─────────────────────────────────────────────┤
│ Email 1: sante1@entreprise.fr [🗑]         │
│ Email 2: sante2@entreprise.fr [🗑]         │
│                                             │
│ [Ajouter un autre email]                   │
│                                             │
│ ✅ 2 email(s) configuré(s)                 │
└─────────────────────────────────────────────┘
```

#### Bloc 2 : Prévoyance

**3.5** Descendez au bloc "Prévoyance"

**3.6** Cliquez sur **"Ajouter un autre email"**

**3.7** Saisissez l'adresse de l'équipe Prévoyance :
```
prevoyance@entreprise.fr
```

Pour cette thématique, une seule adresse suffit (pas de rotation, tous les formulaires Prévoyance iront à cette unique adresse).

#### Bloc 3 : Épargne Retraite

**3.8** Descendez au bloc "Épargne Retraite"

**3.9** Cliquez sur **"Ajouter un autre email"** trois fois

**3.10** Saisissez les 3 adresses de l'équipe Retraite :
```
retraite1@entreprise.fr
retraite2@entreprise.fr
retraite3@entreprise.fr
```

**Important :** Si une thématique a plusieurs emails, la rotation se fera uniquement au sein de cette thématique. Par exemple, les 3 adresses retraite@ tourneront uniquement entre elles pour les formulaires "Retraite".

---

### Étape 4 : Configurer la liste de fallback

C'est l'étape CRUCIALE pour éviter la perte de formulaires.

**4.1** Descendez jusqu'à la section **"Email Addresses"** (liste principale)

**4.2** Cliquez sur **"Ajouter un autre email"**

**4.3** Saisissez au moins une adresse de secours :
```
contact-general@entreprise.fr
```
Ou bien :
```
direction@entreprise.fr
```

**Pourquoi cette étape est importante ?**

La liste de fallback sera utilisée dans ces cas :
- Un utilisateur soumet le formulaire sans sélectionner de type de demande
- Une nouvelle valeur apparaît (par exemple si vous ajoutez "Automobile" plus tard)
- Une thématique n'a aucun email configuré
- Le champ thématique est supprimé par erreur de Formidable

**Recommandation :** Utilisez une adresse générique qui est surveillée quotidiennement, comme contact@, info@ ou direction@.

**4.4** Cliquez sur **"Enregistrer les modifications"**

**4.5** Attendez le message de confirmation

---

### Étape 5 : Test du routage thématique

Maintenant, testons que chaque thématique route correctement.

**Test 1 : Formulaire "Santé"**

**5.1** Allez sur votre formulaire

**5.2** Sélectionnez "Santé / Mutuelle" dans le champ radio

**5.3** Remplissez et soumettez le formulaire

**5.4** Vérifiez que **sante1@entreprise.fr** a reçu l'email

**5.5** Soumettez un second formulaire "Santé"

**5.6** Vérifiez que **sante2@entreprise.fr** a reçu l'email (rotation)

**5.7** Soumettez un troisième formulaire "Santé"

**5.8** Vérifiez que l'email revient à **sante1@entreprise.fr**

✅ **Résultat attendu :** La rotation fonctionne au sein de l'équipe Santé.

---

**Test 2 : Formulaire "Retraite"**

**5.9** Soumettez un formulaire en sélectionnant "Épargne Retraite"

**5.10** Vérifiez que **retraite1@entreprise.fr** a reçu l'email

**5.11** Soumettez un second formulaire "Retraite"

**5.12** Vérifiez que **retraite2@entreprise.fr** a reçu l'email

✅ **Résultat attendu :** Chaque thématique a sa rotation indépendante. Les envois "Santé" n'affectent pas la rotation "Retraite".

---

**Test 3 : Formulaire sans sélection (fallback)**

**5.13** Soumettez un formulaire SANS sélectionner de type de demande (laissez le champ vide si possible, ou modifiez temporairement le formulaire pour que le champ ne soit pas obligatoire)

**5.14** Vérifiez que **contact-general@entreprise.fr** a reçu l'email

✅ **Résultat attendu :** Le fallback fonctionne quand aucune thématique n'est détectée.

---

### Schéma de fonctionnement (Vue d'ensemble)

Voici ce qui se passe en coulisse à chaque soumission :

```
┌─────────────────────────────────┐
│  Formulaire soumis              │
└──────────────┬──────────────────┘
               ↓
┌─────────────────────────────────┐
│  Lecture du champ thématique    │
│  (ex: Field ID 8)               │
└──────────────┬──────────────────┘
               ↓
        ┌──────┴──────┐
        │  Valeur ?   │
        └─────┬───┬───┘
              │   │
        OUI ──┘   └── NON
         ↓             ↓
    ┌─────────┐   ┌─────────┐
    │Thématique│   │Liste    │
    │détectée  │   │principale│
    └────┬────┘   └────┬────┘
         │             │
         ↓             ↓
    ┌─────────┐   ┌─────────┐
    │Santé =  │   │general@ │
    │sante1@  │   │         │
    │ou       │   │         │
    │sante2@  │   │         │
    ├─────────┤   └─────────┘
    │Retraite │
    │= ret1@  │
    │ou ret2@ │
    │ou ret3@ │
    └─────────┘
```

**En résumé :**
1. Le plugin lit la valeur du champ thématique
2. Si une valeur est détectée ET qu'une liste d'emails existe pour cette valeur → utilise cette liste
3. Sinon → utilise la liste principale (fallback)
4. Applique la rotation au sein de la liste choisie
5. Envoie l'email au destinataire sélectionné

---

## 6. CONFIGURATION DES CC (Copies Carbone)

### Objectif

Ajouter des adresses qui recevront une copie de TOUS les formulaires, quelle que soit la thématique ou la rotation. Idéal pour la supervision, l'archivage ou l'intégration avec un CRM.

---

### Cas d'usage typiques

#### Cas 1 : Direction qui veut tout superviser

**Besoin :** Le directeur commercial veut recevoir une copie de tous les leads, même s'ils sont distribués aux commerciaux.

**Solution :** Ajouter directeur@entreprise.fr en CC

**Résultat :**
- Les commerciaux continuent de recevoir les leads en rotation (chacun leur tour)
- Le directeur reçoit TOUT en copie pour supervision
- Personne ne manque d'information

---

#### Cas 2 : Archivage automatique

**Besoin :** Garder une trace de tous les formulaires dans une boîte mail dédiée, pour audit et conformité.

**Solution :** Ajouter archive@entreprise.fr en CC

**Résultat :**
- Les équipes traitent les demandes normalement
- Tous les formulaires s'accumulent dans la boîte "archive@" pour historique
- Facilite les recherches et les audits

---

#### Cas 3 : Intégration CRM

**Besoin :** Votre CRM (Salesforce, HubSpot, Pipedrive, etc.) peut ingérer automatiquement les emails envoyés à une adresse spécifique.

**Solution :** Ajouter l'adresse d'ingestion du CRM en CC (par exemple : crm-ingest-abc123@yourcrm.com)

**Résultat :**
- Les commerciaux reçoivent les leads normalement
- Le CRM reçoit une copie et crée automatiquement un contact/deal
- Synchronisation automatique sans action manuelle

---

### Étapes de configuration

**Étape 1 : Accéder à la section CC**

**1.1** Accédez à `Tableau de bord → Sequential Submissions`

**1.2** Descendez jusqu'à la section **"CC Email Addresses"** (en bas de page)

**1.3** Vous verrez un message explicatif bleu :
```
📧 Les emails en copie (CC) reçoivent TOUS les formulaires
Les adresses en copie carbone recevront systématiquement une copie
de chaque formulaire soumis, qu'il soit routé via la liste principale
ou via une liste thématique.
```

---

**Étape 2 : Ajouter une adresse CC**

**2.1** Cliquez sur le bouton **"Ajouter un autre email CC"** ("add another cc email")

**2.2** Un nouveau champ apparaît. Saisissez l'adresse :
```
direction@entreprise.fr
```
Ou bien :
```
archive@entreprise.fr
```
Ou encore :
```
crm-ingest@yourcrm.com
```

**2.3** Si vous avez plusieurs adresses à ajouter en CC, cliquez à nouveau sur **"Ajouter un autre email CC"** et répétez l'opération

---

**Étape 3 : Sauvegarder**

**3.1** Cliquez sur **"Enregistrer les modifications"**

**3.2** Attendez le message de confirmation (fond vert)

**3.3** Vous verrez un message de confirmation :
```
✅ X email(s) en copie configuré(s) - Ces adresses recevront
une copie de chaque formulaire.
```

---

**Étape 4 : Tester**

**4.1** Soumettez un formulaire de test

**4.2** Vérifiez que :
- Le destinataire principal a reçu l'email (selon la rotation)
- L'adresse CC a également reçu une copie du même email

**4.3** Soumettez un second formulaire (qui ira à un autre destinataire en rotation)

**4.4** Vérifiez que l'adresse CC a encore reçu une copie

---

### Important : Gestion du volume

⚠️ **Attention au nombre de CC**

Chaque adresse CC génère un email supplémentaire par formulaire.

**Exemple :**
- 1 destinataire principal + 0 CC = 1 email envoyé
- 1 destinataire principal + 1 CC = 2 emails envoyés
- 1 destinataire principal + 3 CC = 4 emails envoyés

Si vous recevez 100 formulaires par jour et avez 3 adresses en CC :
→ 400 emails envoyés par jour au total (100 × 4)

**Recommandation :**
- Limitez les CC à 1 ou 2 adresses maximum
- Utilisez des adresses dédiées (archive@, supervision@) plutôt que des boîtes personnelles qui seront inondées
- Si vous avez besoin de plusieurs superviseurs, créez une liste de diffusion côté serveur mail (ex: supervision@entreprise.fr qui redirige vers 3 personnes), et mettez cette unique adresse en CC

---

## 7. CAS D'USAGE COMPLETS

Cette section présente des scénarios réels de A à Z pour vous inspirer et vous guider.

---

### Cas 1 : PME avec 3 commerciaux (Configuration simple)

#### Contexte

**Entreprise :** PME de services B2B avec 3 commerciaux

**Besoin :**
- Distribuer équitablement les demandes de contact du site web
- Chaque commercial doit recevoir environ 33% des leads
- Pas de distinction par type de produit (tous les commerciaux sont polyvalents)

#### Configuration

**Form Filter Mode :** All forms (tous les formulaires)

**Thematic Filter Mode :** Disabled (pas de routage thématique)

**Email Subject :** "Nouvelle demande de contact - Site web"

**Email Addresses (liste principale) :**
- alice@entreprise.fr
- bob@entreprise.fr
- claire@entreprise.fr

**CC Email Addresses :** (vide - pas de supervision)

#### Résultat

Sur 30 demandes de contact :
- Alice recevra 10 demandes
- Bob recevra 10 demandes
- Claire recevra 10 demandes

Distribution automatique, équitable, sans intervention humaine.

#### Avantages

- Configuration en 5 minutes
- Aucun risque d'oubli ou de favoritisme
- Si un commercial est absent, il reçoit quand même ses leads (il les traitera à son retour)
- Équité totale sur le long terme

#### Points de vigilance

- Si un commercial part en congé de 2 semaines, ses leads s'accumulent dans sa boîte
- Solution : modifier temporairement la liste en retirant son adresse pendant l'absence

---

### Cas 2 : Entreprise multi-départements (Configuration avancée)

#### Contexte

**Entreprise :** Compagnie d'assurance avec départements spécialisés

**Besoin :**
- Router automatiquement selon le type de demande (Santé, Prévoyance, Retraite)
- Chaque département a plusieurs experts (rotation interne)
- La direction veut recevoir une copie de tout
- Une adresse générique doit gérer les demandes "Autre"

#### Configuration

**Form Filter Mode :** All forms

**Thematic Filter Mode :** Enabled

**Thematic Field Selection :** Field ID 8 - "Type de demande" (radio)

**Thematic Email Mappings :**

**Santé / Mutuelle :**
- sante1@entreprise.fr
- sante2@entreprise.fr

**Prévoyance :**
- prevoyance1@entreprise.fr
- prevoyance2@entreprise.fr

**Épargne Retraite :**
- retraite1@entreprise.fr
- retraite2@entreprise.fr
- retraite3@entreprise.fr

**Email Subject :** "Nouvelle demande d'information"

**Email Addresses (liste principale - fallback) :**
- contact@entreprise.fr

**CC Email Addresses :**
- direction@entreprise.fr

#### Résultat détaillé

**Scénario 1 : Formulaire "Santé"**
- Formulaire #1 Santé → sante1@entreprise.fr + direction@ (CC)
- Formulaire #2 Santé → sante2@entreprise.fr + direction@ (CC)
- Formulaire #3 Santé → sante1@entreprise.fr + direction@ (CC) [rotation]

**Scénario 2 : Formulaire "Prévoyance"**
- Formulaire #1 Prévoyance → prevoyance1@entreprise.fr + direction@ (CC)
- Formulaire #2 Prévoyance → prevoyance2@entreprise.fr + direction@ (CC)
- Formulaire #3 Prévoyance → prevoyance1@entreprise.fr + direction@ (CC) [rotation]

**Scénario 3 : Formulaire "Retraite"**
- Formulaire #1 Retraite → retraite1@entreprise.fr + direction@ (CC)
- Formulaire #2 Retraite → retraite2@entreprise.fr + direction@ (CC)
- Formulaire #3 Retraite → retraite3@entreprise.fr + direction@ (CC)
- Formulaire #4 Retraite → retraite1@entreprise.fr + direction@ (CC) [rotation]

**Scénario 4 : Formulaire sans sélection ou "Autre"**
- Formulaire → contact@entreprise.fr + direction@ (CC) [fallback]

#### Avantages

- Chaque expert reçoit SEULEMENT les demandes de sa spécialité
- Pas de perte de temps à transférer les emails
- Réponse plus rapide (l'expert compétent traite directement)
- La direction garde la visibilité sur tout
- Aucune demande perdue (fallback configuré)

#### Statistiques après 1 mois

Exemple avec 300 formulaires reçus :
- 120 demandes Santé → 60 à sante1@, 60 à sante2@
- 80 demandes Prévoyance → 40 à prevoyance1@, 40 à prevoyance2@
- 90 demandes Retraite → 30 à chaque retraite1/2/3@
- 10 demandes "Autre" → contact@entreprise.fr

Direction a reçu 300 emails en CC (tous).

---

### Cas 3 : Support client avec archivage (Configuration mixte)

#### Contexte

**Entreprise :** Éditeur de logiciel SaaS avec équipe support

**Besoin :**
- 3 agents support traitent les tickets en rotation
- Tous les tickets doivent être archivés dans un CRM (via email)
- Pas de distinction par type de problème (support généraliste)

#### Configuration

**Form Filter Mode :** All forms

**Thematic Filter Mode :** Disabled

**Email Subject :** "Nouveau ticket support - [Client]"

**Email Addresses (liste principale) :**
- support-agent1@entreprise.fr
- support-agent2@entreprise.fr
- support-agent3@entreprise.fr

**CC Email Addresses :**
- crm-ingest-abc123@votrecrm.com (adresse d'ingestion automatique du CRM)

#### Résultat

**Ticket #1 soumis (lundi 9h00)**
→ Envoyé à support-agent1@entreprise.fr
→ Copie à crm-ingest-abc123@votrecrm.com
→ Le CRM crée automatiquement un ticket #1 assigné à "Agent 1"

**Ticket #2 soumis (lundi 10h30)**
→ Envoyé à support-agent2@entreprise.fr
→ Copie à crm-ingest-abc123@votrecrm.com
→ Le CRM crée automatiquement un ticket #2 assigné à "Agent 2"

**Ticket #3 soumis (lundi 14h00)**
→ Envoyé à support-agent3@entreprise.fr
→ Copie à crm-ingest-abc123@votrecrm.com
→ Le CRM crée automatiquement un ticket #3 assigné à "Agent 3"

**Ticket #4 soumis (mardi 8h00)**
→ Envoyé à support-agent1@entreprise.fr (rotation)
→ Copie au CRM

#### Avantages

- Distribution équitable de la charge de travail support
- Synchronisation automatique avec le CRM (pas de saisie manuelle)
- Traçabilité complète de tous les tickets
- Reporting facilité (le CRM a tout l'historique)

#### Points de vigilance

**CRM configuration :**
- Vérifiez que l'adresse d'ingestion du CRM est correcte
- Testez avec 1-2 tickets avant de mettre en production
- Certains CRM nécessitent un format d'email spécifique (sujet, corps, etc.)

**Gestion des absences :**
Si un agent est absent pour 1 semaine, 2 solutions :
1. Le retirer temporairement de la rotation
2. Le laisser (ses tickets s'accumulent, il les traitera au retour)

---

### Cas 4 : Agence immobilière avec zones géographiques

#### Contexte

**Entreprise :** Agence immobilière multi-sites

**Besoin :**
- Router les demandes selon la ville d'intérêt
- Paris → équipe Paris
- Lyon → équipe Lyon
- Marseille → équipe Marseille
- Autres villes → agence principale

#### Configuration

**Champ Formidable :** "Ville d'intérêt" (radio ou select)
- Paris
- Lyon
- Marseille
- Autre

**Thematic Filter Mode :** Enabled

**Thematic Field Selection :** Field ID 12 - "Ville d'intérêt"

**Thematic Email Mappings :**

**Paris :**
- agent-paris1@agence.fr
- agent-paris2@agence.fr

**Lyon :**
- agent-lyon@agence.fr

**Marseille :**
- agent-marseille1@agence.fr
- agent-marseille2@agence.fr
- agent-marseille3@agence.fr

**Email Subject :** "Nouvelle demande immobilière"

**Email Addresses (liste principale) :**
- contact@agence.fr

**CC Email Addresses :** (vide)

#### Résultat

- Demandes Paris → rotation entre paris1@ et paris2@
- Demandes Lyon → toutes à lyon@ (un seul agent)
- Demandes Marseille → rotation entre marseille1/2/3@
- Demandes "Autre" ou villes non configurées → contact@agence.fr

#### Avantages

- Routage géographique intelligent
- Agents locaux répondent aux clients locaux (meilleure connaissance du marché)
- Aucune demande perdue grâce au fallback

---

### Cas 5 : Association avec bénévoles multilingues

#### Contexte

**Organisation :** Association internationale

**Besoin :**
- Router les demandes selon la langue
- Français → bénévoles francophones
- Anglais → bénévoles anglophones
- Espagnol → bénévoles hispanophones

#### Configuration

**Champ Formidable :** "Langue préférée" (radio)
- Français
- English
- Español

**Thematic Filter Mode :** Enabled

**Thematic Email Mappings :**

**Français :**
- benevole-fr1@association.org
- benevole-fr2@association.org

**English :**
- volunteer-en1@association.org
- volunteer-en2@association.org

**Español :**
- voluntario-es@association.org

**Email Subject :** "Nouvelle demande d'assistance"

**Liste principale (fallback) :**
- contact@association.org

**CC :**
- coordination@association.org (coordinateur général)

#### Résultat

- Demandes en français → rotation fr1/fr2
- Demandes en anglais → rotation en1/en2
- Demandes en espagnol → voluntario-es (unique)
- Coordination reçoit tout pour supervision

---

## 8. DÉPANNAGE (TROUBLESHOOTING)

Cette section vous aide à résoudre les problèmes les plus courants.

---

### Problème 1 : Aucun email n'est envoyé

#### Symptômes

- Vous soumettez un formulaire
- Le formulaire affiche "Merci" ou "Envoyé avec succès"
- Mais aucun email n'arrive dans aucune boîte (ni destinataire principal, ni CC)

#### Causes possibles

**Cause A :** Aucune adresse email configurée dans le plugin

**Cause B :** WordPress ne peut pas envoyer d'emails (problème serveur SMTP)

**Cause C :** Le plugin est désactivé

**Cause D :** Votre formulaire Formidable est configuré pour ne PAS déclencher les hooks (rare)

#### Solutions

**Solution A : Vérifier la configuration du plugin**

1. Allez dans `Tableau de bord → Sequential Submissions`
2. Vérifiez que la section **"Email Addresses"** contient au moins une adresse
3. Si vide, ajoutez au moins une adresse
4. Cliquez sur "Enregistrer les modifications"
5. Testez à nouveau

**Solution B : Tester l'envoi d'emails WordPress**

WordPress lui-même peut avoir des problèmes d'envoi d'emails. Pour tester :

1. Installez le plugin "Check Email" ou "WP Mail SMTP"
2. Envoyez un email de test depuis ce plugin
3. Si l'email de test n'arrive pas, le problème est WordPress, pas WP Rolling Mail

Solutions pour réparer l'envoi d'emails WordPress :
- Utilisez le plugin "WP Mail SMTP" pour configurer un serveur SMTP externe (Gmail, SendGrid, etc.)
- Contactez votre hébergeur pour activer la fonction mail() de PHP
- Vérifiez que votre serveur n'est pas blacklisté

**Solution C : Vérifier que le plugin est activé**

1. Allez dans `Extensions → Extensions installées`
2. Cherchez "Formidable Sequential Submissions"
3. Si "Désactiver" est affiché, le plugin est actif (OK)
4. Si "Activer" est affiché, cliquez dessus pour l'activer

**Solution D : Consulter les logs**

Activez le mode debug de WordPress pour voir les erreurs :

1. Éditez le fichier `wp-config.php` (via FTP ou gestionnaire de fichiers de l'hébergeur)
2. Cherchez la ligne `define('WP_DEBUG', false);`
3. Remplacez par :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
4. Sauvegardez le fichier
5. Soumettez un nouveau formulaire
6. Consultez le fichier `/wp-content/debug.log`
7. Cherchez les lignes commençant par `[FSS]`

Exemple de log normal :
```
[FSS] === START Processing Entry 123 from Form 3 ===
[FSS] No thematic field configured, using main rotation
[FSS] Using main rotation list (3 addresses)
[FSS] Selected email: commercial1@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] === END Processing Entry 123 ===
```

Si vous voyez :
```
[FSS] CRITICAL ERROR: No valid email addresses configured
```
→ Ajoutez des adresses dans la configuration

---

### Problème 2 : Tous les emails vont au même destinataire

#### Symptômes

- Vous avez configuré 3 adresses : alice@, bob@, claire@
- Vous soumettez 5 formulaires
- Tous les 5 emails arrivent à alice@
- Bob et Claire ne reçoivent rien

#### Causes possibles

**Cause A :** Vous n'avez qu'un seul email dans la liste (typo ? oubli ?)

**Cause B :** L'index de rotation est bloqué (bug base de données)

**Cause C :** Vous testez trop vite (emails groupés)

#### Solutions

**Solution A : Vérifier le nombre d'adresses**

1. Allez dans `Tableau de bord → Sequential Submissions`
2. Section "Email Addresses"
3. Comptez combien d'adresses sont affichées
4. Si vous n'en voyez qu'une, ajoutez les autres
5. Sauvegardez

**Solution B : Réinitialiser la rotation**

Pour forcer la rotation à redémarrer :

1. Notez vos adresses actuelles (copiez-les quelque part)
2. Supprimez toutes les adresses (icône 🗑)
3. Sauvegardez (liste vide)
4. Rajoutez les adresses une par une
5. Sauvegardez à nouveau
6. Testez

**Solution C : Vérifier les logs**

Activez WP_DEBUG et consultez debug.log :

```
[FSS] Current rotation index: 0 (total addresses: 3)
[FSS] Selected email: alice@entreprise.fr
[FSS] New rotation index: 0
```

Si vous voyez toujours "index: 0" après plusieurs soumissions, c'est que la rotation ne s'incrémente pas.

Dans ce cas :
1. Vérifiez que les emails sont bien ENVOYÉS (pas juste sélectionnés)
2. Cherchez des erreurs WordPress (permissions base de données ?)
3. Contactez le support technique avec les logs

**Solution D : Tester en espaçant les soumissions**

Parfois, si vous soumettez 3 formulaires en 10 secondes, votre serveur mail peut les grouper.

Essayez :
1. Soumettez 1 formulaire
2. Attendez 1 minute
3. Vérifiez qui a reçu (alice)
4. Soumettez un 2ème formulaire
5. Attendez 1 minute
6. Vérifiez qui a reçu (doit être bob)

---

### Problème 3 : Le routage thématique ne fonctionne pas

#### Symptômes

- Vous avez configuré le routage thématique
- Vous soumettez un formulaire avec "Santé"
- L'email va à la liste principale au lieu d'aller à sante@

#### Causes possibles

**Cause A :** Le champ thématique sélectionné n'est pas le bon

**Cause B :** Le champ a été supprimé de Formidable Forms

**Cause C :** Les valeurs du formulaire ne correspondent pas exactement

**Cause D :** Le mode thématique est sur "Disabled"

#### Solutions

**Solution A : Vérifier le champ sélectionné**

1. Allez dans `Formidable → Formulaires`
2. Éditez votre formulaire
3. Identifiez quel champ contient les valeurs "Santé", "Prévoyance", etc.
4. Notez son Field ID (par exemple : 8)
5. Allez dans `Sequential Submissions → Thematic Field Selection`
6. Vérifiez que le champ sélectionné correspond bien à l'ID noté

**Solution B : Vérifier que le champ existe**

Si vous avez supprimé ou modifié le champ dans Formidable :

1. Consultez les logs (debug.log)
2. Cherchez :
```
[FSS] WARNING: Configured thematic field ID 8 does not exist in Formidable Forms
[FSS] Falling back to main rotation list
```

Si vous voyez ce message :
1. Le champ ID 8 n'existe plus
2. Recréez le champ dans Formidable OU
3. Sélectionnez un autre champ dans la configuration

**Solution C : Vérifier la correspondance des valeurs**

C'est la cause la plus fréquente.

**Exemple de problème :**

Dans Formidable, votre champ radio a la valeur :
```
Mutuelle / Santé
```

Mais dans WP Rolling Mail, vous voyez un bloc nommé :
```
Santé / Mutuelle
```

Ces deux valeurs sont DIFFÉRENTES pour l'ordinateur. Le routage ne fonctionnera pas.

**Comment vérifier :**

1. Consultez les logs après soumission :
```
[FSS] Raw thematic value: 'Mutuelle / Santé'
[FSS] Normalized thematic key: 'mutuelle_sante'
[FSS] Thematic email list 'mutuelle_sante' has 0 addresses
[FSS] Falling back to main rotation list
```

Vous voyez que la clé normalisée est `mutuelle_sante`, mais dans votre configuration, vous avez peut-être configuré `sante_mutuelle`.

**Solution :**

1. Soumettez AU MOINS UN formulaire avec chaque option (Santé, Prévoyance, Retraite)
2. Retournez dans `Sequential Submissions`
3. La section "Thematic Email Mappings" affichera les VRAIES valeurs détectées
4. Configurez les emails pour ces valeurs exactes

**Solution D : Activer le mode thématique**

1. Vérifiez que **"Thematic Filter Mode"** est sur **"Enabled"**
2. Si c'est sur "Disabled", le plugin ignore complètement le champ thématique
3. Changez pour "Enabled" et sauvegardez

---

### Problème 4 : Comment voir ce qui se passe en détail ?

#### Objectif

Vous voulez comprendre exactement ce qui se passe lors de chaque soumission de formulaire pour diagnostiquer un problème complexe.

#### Solution : Activer les logs de debug

Les logs sont des fichiers texte qui enregistrent tout ce que fait le plugin.

**Étape 1 : Activer WP_DEBUG**

1. Connectez-vous à votre hébergement (via FTP, cPanel, ou gestionnaire de fichiers)
2. Localisez le fichier `wp-config.php` à la racine de WordPress
3. Éditez ce fichier
4. Cherchez cette ligne :
```php
define('WP_DEBUG', false);
```
5. Remplacez par ces 3 lignes :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
6. Sauvegardez le fichier

**Important :** `WP_DEBUG_DISPLAY` DOIT être sur `false` pour éviter d'afficher les erreurs sur votre site public.

**Étape 2 : Soumettre un formulaire**

1. Allez sur votre formulaire
2. Remplissez-le avec des données de test
3. Soumettez-le

**Étape 3 : Consulter les logs**

1. Retournez dans votre gestionnaire de fichiers
2. Naviguez vers `/wp-content/debug.log`
3. Téléchargez ce fichier OU ouvrez-le dans un éditeur de texte

**Étape 4 : Chercher les lignes [FSS]**

Le plugin WP Rolling Mail préfixe tous ses messages par `[FSS]` (Formidable Sequential Submissions).

Utilisez la fonction "Rechercher" (Ctrl+F) de votre éditeur et cherchez `[FSS]`.

**Exemple de log réussi (rotation simple) :**

```
[FSS] === START Processing Entry 123 from Form 3 ===
[FSS] Form 3 | Entry 123 | Rotation: YES | Reason: Form included by filter settings
[FSS] No thematic field configured, using main rotation
[FSS] Current rotation index: 0 (total addresses: 3)
[FSS] Selected email: commercial1@entreprise.fr
[FSS] New rotation index: 0
[FSS] No CC recipients configured
[FSS] Building email with subject: 'Nouvelle demande de contact'
[FSS] Message body length: 245 characters
[FSS] Sending email to: commercial1@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] Updated main rotation index: 0
[FSS] === END Processing Entry 123 ===
```

**Exemple de log réussi (routage thématique) :**

```
[FSS] === START Processing Entry 456 from Form 3 ===
[FSS] Form 3 | Entry 456 | Rotation: YES
[FSS] Thematic field ID configured: 8
[FSS] Raw thematic value: 'Prévoyance'
[FSS] Normalized thematic key: 'prevoyance'
[FSS] Thematic email list 'prevoyance' has 2 addresses
[FSS] Current rotation index: 0 (total addresses: 2)
[FSS] Selected email: prevoyance1@entreprise.fr
[FSS] Adding 1 CC recipients: direction@entreprise.fr
[FSS] Building email with subject: 'Nouvelle demande'
[FSS] Sending email to: prevoyance1@entreprise.fr (+ 1 CC)
[FSS] ✓ Email sent successfully
[FSS] Updated thematic rotation index for 'prevoyance': 0
[FSS] === END Processing Entry 456 ===
```

**Exemple de log avec problème (email invalide) :**

```
[FSS] === START Processing Entry 789 from Form 3 ===
[FSS] WARNING: Invalid email address removed from main/thematic rotation list: 'commercial1@entreprisefr'
[FSS] 1 invalid email(s) removed from main/thematic rotation list
[FSS] Using main rotation (2 addresses)
[FSS] Selected email: commercial2@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] === END Processing Entry 789 ===
```

**Exemple de log avec erreur critique (aucun email configuré) :**

```
[FSS] === START Processing Entry 999 from Form 3 ===
[FSS] CRITICAL ERROR: No valid email addresses configured anywhere (neither thematic nor main)
[FSS] Cannot send email for entry 999. Please configure at least one valid email address.
[FSS] === END Processing Entry 999 ===
```

#### Interpréter les logs

**Signes de bon fonctionnement :**
- Vous voyez `✓ Email sent successfully`
- L'email sélectionné correspond à vos attentes
- L'index de rotation change entre les soumissions

**Signes de problème :**
- Vous voyez `CRITICAL ERROR`
- Vous voyez `WARNING: Invalid email`
- Vous voyez `Falling back to main rotation list` alors que vous attendiez un routage thématique
- Vous ne voyez AUCUNE ligne `[FSS]` (le plugin ne s'exécute pas du tout)

#### Désactiver les logs après diagnostic

Une fois votre problème résolu, pensez à désactiver les logs :

1. Rééditez `wp-config.php`
2. Remettez :
```php
define('WP_DEBUG', false);
```
3. Sauvegardez

Les logs peuvent grossir rapidement et ralentir votre site si laissés activés en permanence.

---

### Problème 5 : Les emails arrivent en spam

#### Symptômes

- Les emails sont bien envoyés (vous voyez `✓ Email sent successfully` dans les logs)
- Mais les destinataires ne les voient pas dans leur boîte de réception
- Ils sont dans le dossier Spam/Indésirables

#### Causes possibles

**Cause A :** Votre serveur WordPress envoie les emails sans authentification SMTP

**Cause B :** Votre domaine n'a pas de records SPF/DKIM configurés

**Cause C :** L'adresse expéditrice est suspecte (ex: wordpress@votredomaine.com)

#### Solutions

**Solution A : Utiliser un service SMTP professionnel**

Au lieu de laisser WordPress envoyer les emails directement (fonction PHP mail()), utilisez un service SMTP :

1. Installez le plugin **WP Mail SMTP**
2. Configurez-le avec un service fiable :
   - **SendGrid** (gratuit jusqu'à 100 emails/jour)
   - **Mailgun** (gratuit jusqu'à 5000 emails/mois)
   - **Gmail SMTP** (si vous avez un compte G Suite / Google Workspace)
   - **Amazon SES** (très bon taux de délivrabilité)
3. Testez l'envoi depuis WP Mail SMTP
4. Une fois configuré, WP Rolling Mail utilisera automatiquement ce service

**Solution B : Configurer SPF et DKIM**

Ces paramètres DNS prouvent que vous êtes bien le propriétaire du domaine qui envoie les emails.

1. Contactez votre hébergeur ou votre service SMTP
2. Demandez les enregistrements SPF et DKIM à ajouter
3. Ajoutez-les dans votre zone DNS (souvent via le panel de votre hébergeur)
4. Attendez 24-48h pour la propagation

**Solution C : Changer l'adresse expéditrice**

Par défaut, WordPress envoie depuis `wordpress@votredomaine.com`, ce qui peut être suspect.

Changez-la pour une adresse professionnelle :

1. Utilisez le plugin **WP Mail SMTP** (section "From Email")
2. Changez pour : `noreply@votredomaine.com` ou `contact@votredomaine.com`
3. Sauvegardez

**Test de délivrabilité :**

Envoyez un email de test à ces services pour vérifier votre score :
- https://www.mail-tester.com/
- Score souhaité : 8/10 ou plus

---

### Problème 6 : La rotation ne semble pas équitable sur le long terme

#### Symptômes

- Après 90 soumissions entre 3 adresses
- Vous vous attendez à : 30 / 30 / 30
- Vous obtenez : 35 / 28 / 27

#### Causes possibles

**Cause A :** Certains emails ont échoué et n'ont pas incrémenté l'index

**Cause B :** La configuration a été modifiée en cours de route (adresse ajoutée/supprimée)

**Cause C :** Attentes irréalistes (la distribution est équitable à long terme, pas parfaite à chaque instant)

#### Explication

Le plugin garantit une distribution séquentielle stricte : A → B → C → A → B → C...

Mais plusieurs facteurs peuvent créer des petites variations :

**Facteur 1 : Échecs d'envoi**

Si un email vers bob@entreprise.fr échoue (serveur mail down), le plugin ne l'envoie PAS et ne compte PAS cette soumission pour Bob. La prochaine ira à nouveau à Bob (retry automatique).

Résultat : Bob reçoit 1 de moins.

**Facteur 2 : Modifications de configuration**

Si vous aviez 3 adresses (A, B, C) et que vous retirez B en cours de route, la rotation devient A → C → A → C, ce qui crée un déséquilibre par rapport aux stats précédentes.

**Facteur 3 : Rotation par thématique**

Si vous utilisez le routage thématique, chaque liste tourne INDÉPENDAMMENT.

Exemple :
- Liste Santé : alice@ et bob@
- Liste Retraite : claire@ seule

Sur 100 formulaires :
- 50 Santé → 25 alice, 25 bob
- 50 Retraite → 50 claire

Résultat total : alice 25, bob 25, claire 50. C'est NORMAL, car claire est seule sur sa liste.

#### Solution

**Pour une distribution parfaitement équitable :**

1. Utilisez une rotation simple (pas de thématique)
2. N'ajoutez/supprimez JAMAIS d'adresses en cours d'utilisation
3. Surveillez les échecs d'envoi (logs)
4. Acceptez les petites variations (29/31/30 sur 90 est normal)

**Sur le long terme (1000+ soumissions), la distribution s'équilibre naturellement.**

Si après 1000 soumissions vous avez 400/300/300, il y a un bug. Contactez le support avec les logs.

---

## 9. BONNES PRATIQUES

### Recommandations

#### 1. Toujours configurer une liste de fallback (Email Addresses)

**Pourquoi ?**
La liste principale (fallback) est votre filet de sécurité. Même si vous utilisez le routage thématique, gardez au moins une adresse dans cette liste.

**Cas où le fallback est utilisé :**
- Formulaire soumis sans champ thématique rempli
- Nouvelle valeur apparue que vous n'avez pas encore configurée
- Champ thématique supprimé par erreur de Formidable
- Liste thématique configurée mais vide

**Recommandation :**
```
Liste principale : contact@entreprise.fr
ou
Liste principale : direction@entreprise.fr
```

Choisissez une adresse qui :
- Est surveillée quotidiennement
- Peut traiter tous types de demandes
- Ne part jamais en congé (adresse générique, pas personnelle)

---

#### 2. Tester avec de vrais emails avant mise en production

**Pourquoi ?**
Mieux vaut détecter les problèmes AVANT que les vrais clients soient impactés.

**Comment tester efficacement :**

**Phase 1 : Test en local**
1. Utilisez vos propres adresses email personnelles (Gmail, Outlook, etc.)
2. Configurez 3 adresses : votre-email+1@gmail.com, votre-email+2@gmail.com, votre-email+3@gmail.com
3. Soumettez 10 formulaires
4. Vérifiez que vous recevez bien les emails (regardez dans Spam aussi)

**Phase 2 : Test avec les vraies adresses**
1. Configurez les vraies adresses de vos collègues
2. Prévenez-les qu'ils vont recevoir des tests
3. Soumettez 5-10 formulaires de test
4. Demandez confirmation de réception à chacun

**Phase 3 : Test en production avec surveillance**
1. Mettez en production
2. Surveillez les logs pendant 48h (activez WP_DEBUG temporairement)
3. Demandez un retour à vos collègues après 1 semaine

**Checklist de test :**
- ✅ Rotation simple fonctionne (si applicable)
- ✅ Routage thématique fonctionne (si applicable)
- ✅ Fallback fonctionne (testez en laissant le champ vide)
- ✅ CC reçoivent tout (si applicable)
- ✅ Emails n'arrivent pas en spam
- ✅ Sujet d'email correct
- ✅ Contenu du formulaire complet dans l'email

---

#### 3. Documenter votre configuration

**Pourquoi ?**
Dans 6 mois, vous aurez peut-être oublié pourquoi telle adresse est configurée. Si un collègue reprend la gestion, il sera perdu.

**Ce qu'il faut documenter :**

**Exemple de documentation (dans un fichier Word/Google Doc) :**

```
=== CONFIGURATION WP ROLLING MAIL ===
Dernière mise à jour : 15 mars 2024
Responsable : Sophie Dubois

--- ROTATION GÉNÉRALE ---
Mode : Routage thématique activé
Champ utilisé : Field ID 8 - "Type de demande"

--- ÉQUIPE SANTÉ ---
Emails : sante1@entreprise.fr (Marie Martin)
         sante2@entreprise.fr (Luc Dupont)
Quand : Formulaires avec "Santé / Mutuelle" sélectionné

--- ÉQUIPE PRÉVOYANCE ---
Emails : prevoyance@entreprise.fr (boîte partagée - Pierre & Julie)
Quand : Formulaires avec "Prévoyance" sélectionné

--- ÉQUIPE RETRAITE ---
Emails : retraite1@entreprise.fr (Jean Durand)
         retraite2@entreprise.fr (Claire Lefebvre)
         retraite3@entreprise.fr (Thomas Rousseau)
Quand : Formulaires avec "Épargne Retraite" sélectionné

--- FALLBACK ---
Email : contact@entreprise.fr
Utilisé si : valeur non reconnue ou champ vide

--- CC ---
Email : direction@entreprise.fr (Directrice générale Anne Petit)
Raison : Supervision de tous les leads

--- NOTES ---
- Si Marie part en congé, retirer temporairement sante1@
- La boîte prevoyance@ est gérée par Pierre ET Julie (pas besoin de rotation)
- Thomas (retraite3@) est nouveau depuis janvier 2024
```

**Où stocker cette documentation ?**
- Google Drive partagé avec l'équipe IT
- Confluence ou wiki interne
- Dans un dossier "Documentation WordPress" sur votre serveur

**Bonus :** Prenez une capture d'écran de la page de configuration et joignez-la au document.

---

#### 4. Surveiller les logs les premières semaines

**Pourquoi ?**
Les premières semaines de production sont critiques. Vous pouvez détecter :
- Des problèmes de délivrabilité
- Des erreurs de configuration que vous n'aviez pas anticipées
- Des cas d'usage non prévus

**Comment faire :**

**Semaine 1 : Surveillance active**
1. Activez WP_DEBUG (voir section Dépannage)
2. Consultez debug.log tous les jours
3. Cherchez les lignes `[FSS]`
4. Vérifiez qu'il y a des `✓ Email sent successfully`
5. Vérifiez qu'il n'y a pas de `CRITICAL ERROR` ou trop de `WARNING`

**Semaine 2-4 : Surveillance passive**
1. Consultez les logs 2 fois par semaine
2. Demandez du feedback aux destinataires ("Recevez-vous bien les formulaires ?")

**Après 1 mois : Désactivation des logs**
1. Si tout fonctionne bien, désactivez WP_DEBUG
2. Gardez quand même votre documentation à jour
3. Réactivez les logs seulement en cas de problème

**Que surveiller dans les logs :**

**Bon signe :**
```
[FSS] ✓ Email sent successfully
```
→ Tout va bien

**Signe d'alerte :**
```
[FSS] WARNING: Invalid email address removed
```
→ Vous avez une typo dans une adresse

**Signe critique :**
```
[FSS] CRITICAL ERROR: No valid email addresses configured
```
→ Aucun email ne peut être envoyé, agissez immédiatement

---

#### 5. Utiliser des adresses email dédiées plutôt que personnelles

**Pourquoi ?**

**Problème avec adresses personnelles :**
```
marie.martin@entreprise.fr (adresse personnelle de Marie)
```

**Risques :**
- Marie part en congé 3 semaines → 1/3 des leads non traités
- Marie quitte l'entreprise → son adresse est désactivée → emails perdus
- Marie change de poste → faut reconfigurer tout le plugin

**Solution : Adresses génériques/fonctionnelles**
```
equipe-sante@entreprise.fr (boîte partagée)
ou
sante-leads@entreprise.fr
ou
commercial-zone-nord@entreprise.fr
```

**Avantages :**
- Plusieurs personnes peuvent accéder à la boîte
- Si quelqu'un part, pas besoin de reconfigurer
- Continuité de service garantie
- Plus facile à transférer en cas de réorganisation

**Comment créer ces adresses :**

**Option A : Boîte email partagée**
Créez une vraie boîte mail que plusieurs personnes consultent (via webmail ou ajout du compte dans Outlook/Thunderbird)

**Option B : Alias avec redirection**
Créez un alias qui redirige vers 2-3 adresses personnelles :
```
equipe-sante@entreprise.fr → marie@, luc@, pierre@
```

Tous les trois reçoivent les mêmes emails (pas de rotation, mais redondance).

**Recommandation :**
- Rotation AU SEIN d'adresses génériques : OK
- Rotation sur adresses personnelles directes : À éviter

Exemple optimal :
```
Liste Santé :
- sante-commercial-1@entreprise.fr (géré par Marie)
- sante-commercial-2@entreprise.fr (géré par Luc)
```

Si Luc part, vous assignez la boîte sante-commercial-2@ à son remplaçant. Pas besoin de toucher au plugin.

---

### Erreurs à éviter

#### 1. Ne pas configurer d'emails du tout

**Erreur :**
Activer le plugin sans ajouter aucune adresse dans "Email Addresses".

**Conséquence :**
Aucun email ne sera jamais envoyé. Les formulaires seront soumis mais les données perdues.

**Détection :**
Les logs montreront :
```
[FSS] CRITICAL ERROR: No valid email addresses configured
```

**Solution :**
Ajoutez AU MOINS une adresse dans "Email Addresses" (liste principale).

---

#### 2. Oublier la liste de fallback en mode thématique

**Erreur :**
Activer le routage thématique, configurer les listes thématiques, mais laisser "Email Addresses" (liste principale) vide.

**Conséquence :**
Si une valeur inattendue arrive (nouveau type de demande, champ vide, erreur), aucun email de secours n'est disponible. Le formulaire est perdu.

**Exemple concret :**
Vous configurez :
- Santé → sante@
- Retraite → retraite@

Un utilisateur soumet avec "Prévoyance" (que vous aviez oublié de configurer).
→ Si pas de fallback, email perdu.

**Solution :**
TOUJOURS mettre au moins 1 adresse dans "Email Addresses", même en mode thématique.

---

#### 3. Utiliser des emails avec typos

**Erreur :**
Taper commercial1@entreprisefr (oubli du point avant "fr") au lieu de commercial1@entreprise.fr

**Conséquence :**
Le plugin détecte que l'email est invalide et le retire automatiquement de la liste. Vous croyez avoir 3 adresses en rotation, mais en réalité il n'y en a que 2 (la 3ème est ignorée).

**Détection :**
Les logs montreront :
```
[FSS] WARNING: Invalid email address removed from main/thematic rotation list: 'commercial1@entreprisefr'
```

**Solution :**
- Copiez-collez les adresses depuis votre annuaire d'entreprise plutôt que de les taper
- Après configuration, envoyez un email de test à chaque adresse pour vérifier
- Surveillez les logs la première semaine

---

#### 4. Changer le Field ID sans reconfigurer

**Erreur :**
Vous aviez configuré Field ID 8 pour le routage thématique. Vous modifiez votre formulaire Formidable et vous supprimez ce champ. Vous créez un nouveau champ similaire qui a maintenant l'ID 12. Vous oubliez de mettre à jour la configuration du plugin.

**Conséquence :**
Le plugin cherche toujours le Field ID 8 qui n'existe plus. Il ne trouve rien et bascule systématiquement sur le fallback.

**Détection :**
Les logs montreront :
```
[FSS] WARNING: Configured thematic field ID 8 does not exist in Formidable Forms
[FSS] Falling back to main rotation list
```

**Solution :**
- Évitez de supprimer des champs dans Formidable si possible (modifiez-les plutôt)
- Si vous devez supprimer, notez le nouvel ID du champ de remplacement
- Retournez dans la config du plugin et sélectionnez le nouveau champ
- Sauvegardez
- La page recharge et affiche les nouvelles valeurs

**Bonne pratique :**
Avant de modifier un formulaire Formidable, vérifiez si ce formulaire est utilisé par WP Rolling Mail. Si oui, notez le Field ID actuel et vérifiez-le après modification.

---

## 10. FAQ (FOIRE AUX QUESTIONS)

### Q : Combien d'emails puis-je ajouter dans une liste ?

**R :** Il n'y a pas de limite technique imposée par le plugin. Vous pouvez ajouter 3, 10, 50 ou 100 adresses si vous le souhaitez.

**Limites pratiques :**
- **10-20 emails maximum recommandé** pour des raisons de gestion
- Au-delà de 20, la rotation devient très lente (chaque adresse ne reçoit qu'un email tous les 20+ formulaires)
- Plus il y a d'adresses, plus il est difficile de gérer les absences/départs

**Exemple de problème avec trop d'adresses :**
Si vous avez 50 commerciaux en rotation et recevez 10 leads par jour, chaque commercial recevra 1 lead tous les 5 jours. Autant faire 5 listes de 10 personnes avec routage thématique ou géographique.

**Recommandation :**
- Rotation simple : 3-10 adresses max
- Rotation thématique : 2-5 adresses par thématique

---

### Q : Puis-je utiliser le même email dans plusieurs listes ?

**R :** Oui, absolument. Une même adresse peut apparaître dans :
- La liste principale
- Une ou plusieurs listes thématiques
- Les CC

**Exemple valide :**
```
Liste principale : contact@entreprise.fr, commercial1@entreprise.fr
Liste Santé : commercial1@entreprise.fr, sante@entreprise.fr
CC : direction@entreprise.fr
```

Dans cet exemple, commercial1@entreprise.fr peut recevoir :
- Des emails de la rotation principale (quand pas de thématique)
- Des emails de la rotation Santé (quand thématique = Santé)

**Cas d'usage :**
Vous avez un commercial polyvalent (Alice) qui peut traiter tous types de demandes, et des spécialistes qui ne traitent qu'un type.

Configuration :
```
Liste principale : alice@, bob@, claire@
Liste thématique "Santé" : alice@, sante-expert@
```

Résultat :
- Demandes sans thématique → rotation entre alice, bob, claire
- Demandes "Santé" → rotation entre alice et sante-expert
- Alice reçoit donc plus d'emails que les autres (elle est dans 2 listes)

---

### Q : Que se passe-t-il si je supprime un champ Formidable configuré ?

**R :** Si vous supprimez le champ thématique (par exemple Field ID 8) de votre formulaire Formidable, le plugin détecte l'erreur et bascule automatiquement sur la liste principale (fallback).

**Ce qui se passe techniquement :**
1. Un formulaire est soumis
2. Le plugin essaie de lire le Field ID 8
3. Il ne le trouve pas dans Formidable
4. Il logue un WARNING dans debug.log :
```
[FSS] WARNING: Configured thematic field ID 8 does not exist
[FSS] Falling back to main rotation list
```
5. Il envoie l'email à la liste principale

**Conséquence :**
- Aucun email n'est perdu (grâce au fallback)
- Mais le routage thématique cesse de fonctionner
- Tous les formulaires vont à la liste principale

**Solution :**
1. Si vous avez supprimé le champ par erreur, recréez-le (il aura un nouvel ID)
2. Allez dans la config du plugin
3. Sélectionnez le nouveau champ
4. Sauvegardez
5. Reconfigurez les listes thématiques si nécessaire (si les valeurs ont changé)

**Bonne pratique :**
Avant de supprimer un champ dans Formidable, vérifiez s'il est utilisé par le plugin (regardez la configuration).

---

### Q : Les emails sont-ils envoyés immédiatement ?

**R :** Oui, les emails sont envoyés immédiatement lors de la soumission du formulaire.

**Chronologie exacte :**
1. Utilisateur clique sur "Envoyer" dans le formulaire
2. Formidable Forms traite la soumission
3. Formidable déclenche le hook `frm_after_create_entry`
4. WP Rolling Mail intercepte ce hook (en quelques millisecondes)
5. WP Rolling Mail sélectionne le destinataire selon la rotation
6. WP Rolling Mail appelle `wp_mail()` pour envoyer l'email
7. WordPress/PHP envoie l'email via SMTP ou fonction mail()
8. L'utilisateur voit le message "Merci, votre formulaire a été envoyé"

**Délai total typique :**
- Entre la soumission et l'envoi : < 1 seconde
- Entre l'envoi et la réception : dépend du serveur mail (généralement 0-30 secondes)

**Délai de réception peut varier selon :**
- Votre serveur SMTP (SendGrid, Mailgun = rapide ; serveur PHP mail() = plus lent)
- Le serveur de réception (Gmail est rapide, certains serveurs d'entreprise peuvent prendre plusieurs minutes)
- Les filtres antispam intermédiaires

**Pas de file d'attente / cron :**
Le plugin n'utilise PAS de système de file d'attente différée. L'envoi est synchrone et immédiat. Si `wp_mail()` échoue, l'email n'est pas mis en attente pour retry plus tard.

---

### Q : Puis-je voir l'historique des emails envoyés ?

**R :** Le plugin lui-même ne stocke pas d'historique des emails envoyés. Cependant, il existe plusieurs solutions pour tracer les envois :

**Solution 1 : Logs de debug (temporaire)**
1. Activez WP_DEBUG (voir section Dépannage)
2. Consultez `/wp-content/debug.log`
3. Cherchez les lignes `[FSS]`
4. Vous verrez tous les envois avec :
   - ID de l'entrée
   - Email sélectionné
   - Thématique détectée
   - Résultat de l'envoi (succès/échec)

**Limite :** Les logs sont écrasés régulièrement et ne sont pas un archivage permanent.

**Solution 2 : Plugin d'archivage d'emails**
Installez un plugin WordPress comme :
- **WP Mail Logging** (gratuit)
- **Mail Log** (gratuit)
- **WP Mail SMTP** (version Pro a un log intégré)

Ces plugins interceptent TOUS les emails envoyés par WordPress et les stockent dans la base de données. Vous pouvez voir :
- Date/heure d'envoi
- Destinataire
- Sujet
- Contenu de l'email
- Statut (envoyé/échoué)

**Solution 3 : Utiliser une adresse CC d'archivage**
Configurez une adresse CC dédiée à l'archivage (par exemple : archive@entreprise.fr). Tous les formulaires seront archivés dans cette boîte mail.

Avantage : Vous avez un historique consultable directement dans votre client mail (Outlook, Gmail, etc.).

**Solution 4 : Consulter les entrées Formidable**
Formidable Forms garde toutes les soumissions dans `Formidable → Entrées`.

Vous ne verrez pas directement "à qui l'email a été envoyé", mais vous pouvez :
- Voir toutes les soumissions avec date/heure
- Déduire la rotation (entrée 1 → commercial1, entrée 2 → commercial2, etc.)

**Recommandation :**
Pour un archivage permanent, utilisez une combinaison :
- Plugin WP Mail Logging (pour voir l'historique technique)
- Adresse CC d'archivage (pour avoir les emails complets)

---

### Q : Comment désactiver temporairement le plugin ?

**R :** Il y a 2 façons de désactiver le plugin selon votre besoin :

**Méthode 1 : Désactivation complète (le plugin ne s'exécute plus du tout)**

1. Allez dans `Extensions → Extensions installées`
2. Cherchez "Formidable Sequential Submissions"
3. Cliquez sur "Désactiver"

**Effet :**
- Le plugin cesse complètement de fonctionner
- Les formulaires Formidable continuent de fonctionner normalement
- Mais ils utilisent les paramètres d'envoi d'email de Formidable (notifications configurées dans Formidable Forms)

**Quand utiliser :**
- Vous voulez retourner au comportement par défaut de Formidable (tous les emails vont au même destinataire configuré dans Formidable)
- Vous suspectez un bug et voulez tester sans le plugin

---

**Méthode 2 : Désactivation partielle (exclure certains formulaires)**

Si vous voulez que CERTAINS formulaires utilisent la rotation et d'autres non :

1. Allez dans `Sequential Submissions`
2. Section "Form Filter Mode"
3. Sélectionnez "Disable rotation for selected forms"
4. Cochez les formulaires à exclure
5. Sauvegardez

**Effet :**
- Les formulaires cochés n'utilisent PAS la rotation
- Les autres formulaires continuent d'utiliser la rotation
- Le plugin reste actif

**Quand utiliser :**
- Vous avez un formulaire de contact général qui doit utiliser la rotation
- Et un formulaire "Recrutement" qui doit aller uniquement à RH@

Configuration :
```
Form Filter Mode : Disable rotation for selected forms
☑ Formulaire de recrutement (ID 5)
☐ Formulaire de contact (ID 3)
```

Résultat :
- Formulaire de contact (3) → utilise la rotation
- Formulaire de recrutement (5) → utilise les paramètres Formidable (notification configurée dans Formidable Forms, par exemple rh@entreprise.fr)

---

**Méthode 3 : Désactivation temporaire d'un destinataire (congé, absence)**

Si un commercial part en congé et que vous ne voulez pas qu'il reçoive d'emails pendant 2 semaines :

1. Allez dans `Sequential Submissions`
2. Section "Email Addresses"
3. Cliquez sur l'icône 🗑 à droite de son adresse
4. Sauvegardez

**Effet :**
- La rotation continue avec les adresses restantes
- L'adresse supprimée ne reçoit plus rien

**Important :** Pensez à RE-AJOUTER l'adresse au retour de la personne.

**Astuce :** Notez quelque part (dans un calendrier) la date de retour pour ne pas oublier de réactiver l'adresse.

---

## 11. SUPPORT ET AIDE

### Où obtenir de l'aide

Si après avoir lu ce guide vous rencontrez encore des difficultés, voici les ressources disponibles :

**1. Logs de debug**
Avant de demander de l'aide, activez TOUJOURS les logs (voir section 8.4) et consultez-les. Ils contiennent 90% des réponses aux problèmes courants.

**2. Documentation WordPress et Formidable**
- Documentation officielle Formidable Forms : https://formidableforms.com/knowledgebase/
- Documentation WordPress : https://wordpress.org/support/

**3. Contact du développeur**
Pour toute question technique spécifique au plugin WP Rolling Mail :
- Email : support@kiora.tech (remplacer par l'adresse réelle)
- Indiquez toujours : version WordPress, version Formidable Forms, description du problème, et copie des logs [FSS]

---

### Informations utiles à fournir au support

Lorsque vous contactez le support, facilitez le diagnostic en fournissant :

**Informations système :**
```
Version WordPress : [ex: 6.4.2]
Version Formidable Forms : [ex: 6.7.1]
Version PHP : [ex: 8.2]
Plugin WP Rolling Mail actif : Oui/Non
```

**Configuration du plugin :**
Faites une capture d'écran de votre page de configuration (cachez les adresses email sensibles si besoin).

**Logs [FSS] :**
Copiez les dernières lignes des logs (section 8.4) en anonymisant les emails si nécessaire.

Exemple :
```
[FSS] === START Processing Entry 123 from Form 3 ===
[FSS] Thematic field ID configured: 8
[FSS] Raw thematic value: 'Santé'
[FSS] Normalized thematic key: 'sante'
[FSS] WARNING: Thematic list 'sante' is configured but empty
[FSS] Falling back to main rotation list (0 addresses)
[FSS] CRITICAL ERROR: No valid email addresses configured anywhere
[FSS] === END Processing Entry 123 ===
```

**Description précise du problème :**
- **Comportement attendu :** "Je m'attends à ce que le formulaire 'Santé' soit envoyé à sante1@ ou sante2@"
- **Comportement observé :** "Tous les formulaires vont à contact@"
- **Étapes pour reproduire :** "1. Je soumets un formulaire en sélectionnant 'Santé', 2. L'email arrive à contact@ au lieu de sante@"

---

## 12. GLOSSAIRE

**Rotation** : Système de distribution automatique qui envoie chaque nouveau formulaire à une adresse différente en suivant un ordre séquentiel (A → B → C → A → B → C...). Garantit une distribution équitable à long terme.

**Thématique** : Catégorie ou type de demande basé sur une valeur de champ Formidable. Exemple : "Santé", "Prévoyance", "Retraite". Permet de router les formulaires vers des listes d'emails spécialisées.

**Fallback (Liste principale)** : Liste d'emails de secours utilisée quand le routage thématique ne peut pas déterminer où envoyer un formulaire (champ vide, valeur non configurée, erreur). Aussi appelée "liste principale" ou "Email Addresses".

**CC (Copie Carbone)** : Adresse(s) email qui reçoivent une copie de TOUS les formulaires, quelle que soit la rotation ou la thématique. Utilisé pour supervision, archivage ou intégration CRM.

**Field ID** : Identifiant numérique unique d'un champ dans Formidable Forms. Exemple : 8, 12, 45. Utilisé par le plugin pour identifier quel champ contient la valeur thématique.

**Index (de rotation)** : Position actuelle dans la liste de rotation. Si la liste contient [alice@, bob@, claire@], l'index peut être 0 (alice), 1 (bob) ou 2 (claire). Après chaque envoi, l'index avance (rotation).

**Normalized key (Clé normalisée)** : Version standardisée d'une valeur thématique. Exemple : "Santé / Mutuelle" devient "sante_mutuelle". Permet de regrouper des variantes similaires ("Santé", "Santé / Mutuelle", "Type : Santé") sous la même clé.

**Formidable Forms** : Plugin WordPress de création de formulaires avancés. Prérequis pour utiliser WP Rolling Mail.

**wp_mail()** : Fonction WordPress utilisée pour envoyer des emails. Le plugin l'utilise pour l'envoi effectif des emails.

**SMTP (Simple Mail Transfer Protocol)** : Protocole standard d'envoi d'emails. Recommandé d'utiliser un service SMTP externe (SendGrid, Mailgun) plutôt que la fonction PHP mail() native pour meilleure délivrabilité.

**WP_DEBUG** : Mode de debug de WordPress qui active les logs d'erreurs. Permet de diagnostiquer les problèmes en enregistrant toutes les actions du plugin dans `/wp-content/debug.log`.

**Hook (WordPress)** : Point d'accroche dans le code WordPress qui permet aux plugins d'intercepter des actions. WP Rolling Mail utilise le hook `frm_after_create_entry` de Formidable Forms pour intercepter les soumissions.

**Entry (Entrée)** : Une soumission de formulaire dans Formidable Forms. Chaque fois qu'un utilisateur soumet un formulaire, Formidable crée une "entry" avec un ID unique.

**Sanitization** : Processus de nettoyage et validation des données (notamment emails) pour éviter les erreurs et failles de sécurité. Le plugin valide automatiquement toutes les adresses email configurées.

---

## ANNEXE : EXEMPLES DE LOGS

Cette section montre des exemples réels de logs pour vous aider à comprendre ce qui se passe en coulisse.

---

### Log complet d'un envoi réussi avec thématique

**Contexte :** Formulaire soumis avec valeur "Prévoyance", routage thématique activé, liste de 2 emails pour Prévoyance, 1 adresse en CC.

```
[FSS] === START Processing Entry 456 from Form 3 ===
[FSS] Form 3 | Entry 456 | Rotation: YES | Reason: Form included by filter settings
[FSS] Thematic field ID configured: 8
[FSS] Raw thematic value: 'Prévoyance'
[FSS] Normalized thematic key: 'prevoyance'
[FSS] Thematic email list 'prevoyance' has 2 addresses
[FSS] Current rotation index: 0 (total addresses: 2)
[FSS] Selected email: prevoyance1@entreprise.fr
[FSS] New rotation index: 0
[FSS] Adding 1 CC recipients: direction@entreprise.fr
[FSS] Building email with subject: 'Nouvelle demande d'information'
[FSS] Message body length: 342 characters
[FSS] Sending email to: prevoyance1@entreprise.fr (+ 1 CC)
[FSS] ✓ Email sent successfully
[FSS] Updated thematic rotation index for 'prevoyance': 0
[FSS] === END Processing Entry 456 ===
```

**Interprétation :**
- ✅ Entrée 456 traitée avec succès
- ✅ Thématique détectée : "Prévoyance" → clé : prevoyance
- ✅ Liste thématique utilisée (2 adresses disponibles)
- ✅ Email envoyé à prevoyance1@entreprise.fr
- ✅ CC envoyé à direction@entreprise.fr
- ✅ Rotation mise à jour (prochaine soumission ira à prevoyance2@)

---

### Log d'un envoi réussi avec rotation simple (sans thématique)

**Contexte :** Routage thématique désactivé, liste principale de 3 emails, pas de CC.

```
[FSS] === START Processing Entry 789 from Form 3 ===
[FSS] Form 3 | Entry 789 | Rotation: YES | Reason: Form included by filter settings
[FSS] No thematic field configured, using main rotation
[FSS] Using main rotation list (3 addresses)
[FSS] Current rotation index: 0 (total addresses: 3)
[FSS] Selected email: commercial2@entreprise.fr
[FSS] New rotation index: 0
[FSS] No CC recipients configured
[FSS] Building email with subject: 'Nouveau formulaire de contact'
[FSS] Message body length: 198 characters
[FSS] Sending email to: commercial2@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] Updated main rotation index: 0
[FSS] === END Processing Entry 789 ===
```

**Interprétation :**
- ✅ Configuration simple sans thématique
- ✅ Utilisation de la liste principale (3 adresses)
- ✅ Email envoyé à commercial2@ (c'était son tour dans la rotation)
- ✅ Pas de CC
- ✅ Rotation mise à jour

---

### Log avec fallback vers liste principale

**Contexte :** Routage thématique activé, mais la valeur soumise ("Automobile") n'a pas de liste d'emails configurée. Le plugin utilise le fallback.

```
[FSS] === START Processing Entry 1001 from Form 3 ===
[FSS] Form 3 | Entry 1001 | Rotation: YES
[FSS] Thematic field ID configured: 8
[FSS] Raw thematic value: 'Automobile'
[FSS] Normalized thematic key: 'automobile'
[FSS] WARNING: Thematic list 'automobile' is configured but empty
[FSS] Falling back to main rotation list (1 addresses)
[FSS] Current rotation index: 0 (total addresses: 1)
[FSS] Selected email: contact-general@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] Updated main rotation index: 0
[FSS] === END Processing Entry 1001 ===
```

**Interprétation :**
- ⚠️ Valeur "Automobile" détectée mais non configurée
- ✅ Fallback vers liste principale automatique
- ✅ Email envoyé à contact-general@entreprise.fr
- 💡 Action à faire : Ajouter une liste d'emails pour "automobile" si cette valeur est fréquente

---

### Log avec détection d'email invalide

**Contexte :** Une adresse email mal saisie (typo) est détectée et retirée automatiquement.

```
[FSS] === START Processing Entry 555 from Form 3 ===
[FSS] Form 3 | Entry 555 | Rotation: YES
[FSS] No thematic field configured, using main rotation
[FSS] WARNING: Invalid email address removed from main/thematic rotation list: 'commercial1@entreprisefr'
[FSS] 1 invalid email(s) removed from main/thematic rotation list
[FSS] Using main rotation list (2 addresses)
[FSS] Current rotation index: 0 (total addresses: 2)
[FSS] Selected email: commercial2@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] === END Processing Entry 555 ===
```

**Interprétation :**
- ⚠️ Email invalide détecté : `commercial1@entreprisefr` (manque le `.`)
- ✅ Email automatiquement retiré de la rotation
- ✅ Rotation continue avec les 2 adresses valides restantes
- 💡 Action à faire : Corriger la typo dans la configuration (commercial1@entreprise.fr)

---

### Log d'erreur critique - Aucun email configuré

**Contexte :** Aucune adresse email n'est configurée nulle part (ni thématique ni principale).

```
[FSS] === START Processing Entry 666 from Form 3 ===
[FSS] Form 3 | Entry 666 | Rotation: YES
[FSS] Thematic field ID configured: 8
[FSS] Raw thematic value: 'Santé'
[FSS] Normalized thematic key: 'sante'
[FSS] WARNING: Thematic list 'sante' is configured but empty
[FSS] Falling back to main rotation list (0 addresses)
[FSS] CRITICAL ERROR: No valid email addresses configured anywhere (neither thematic nor main)
[FSS] Cannot send email for entry 666. Please configure at least one valid email address.
[FSS] === END Processing Entry 666 ===
```

**Interprétation :**
- ❌ Aucun email envoyé
- ❌ Liste thématique "sante" vide
- ❌ Liste principale également vide
- 🚨 Action urgente : Ajouter au moins une adresse dans "Email Addresses"

---

### Log d'échec d'envoi email (problème serveur)

**Contexte :** `wp_mail()` retourne false (échec d'envoi côté serveur SMTP).

```
[FSS] === START Processing Entry 888 from Form 3 ===
[FSS] Form 3 | Entry 888 | Rotation: YES
[FSS] No thematic field configured, using main rotation
[FSS] Using main rotation list (3 addresses)
[FSS] Selected email: commercial1@entreprise.fr
[FSS] Building email with subject: 'Nouvelle demande'
[FSS] Sending email to: commercial1@entreprise.fr
[FSS] ✗ CRITICAL: Email sending FAILED to commercial1@entreprise.fr
[FSS] Rotation index NOT incremented (will retry with same email on next submission)
[FSS] Possible causes: SMTP server down, incorrect email configuration, blocked by server
[FSS] === END Processing Entry 888 ===
```

**Interprétation :**
- ❌ Email non envoyé (problème serveur)
- ✅ L'index de rotation n'a PAS été incrémenté (protection)
- ✅ La prochaine soumission essaiera à nouveau commercial1@ (retry automatique)
- 💡 Action à faire :
  - Vérifier que WordPress peut envoyer des emails (tester avec un autre plugin)
  - Vérifier la configuration SMTP
  - Contacter l'hébergeur si le problème persiste

---

### Log avec champ thématique inexistant

**Contexte :** Le Field ID 8 configuré n'existe plus dans Formidable (champ supprimé par erreur).

```
[FSS] === START Processing Entry 999 from Form 3 ===
[FSS] Form 3 | Entry 999 | Rotation: YES
[FSS] Thematic field ID configured: 8
[FSS] WARNING: Configured thematic field ID 8 does not exist in Formidable Forms
[FSS] Falling back to main rotation list
[FSS] Please check your thematic field configuration and ensure the field still exists
[FSS] Using main rotation list (1 addresses)
[FSS] Selected email: contact@entreprise.fr
[FSS] ✓ Email sent successfully
[FSS] === END Processing Entry 999 ===
```

**Interprétation :**
- ⚠️ Champ thématique supprimé ou ID incorrect
- ✅ Fallback vers liste principale automatique
- ✅ Email envoyé quand même (pas de perte)
- 💡 Action à faire : Aller dans la config et sélectionner le bon champ (ou recréer le champ dans Formidable)

---

**FIN DU GUIDE DE CONFIGURATION** - Version 1.0

---

## Crédits

**Plugin développé par :** Kiora Tech

**Documentation rédigée par :** Assistant Documentation Technique

**Dernière mise à jour :** 2025

**Support :** Pour toute question, consultez d'abord la section Dépannage (section 8) et la FAQ (section 10). En cas de problème persistant, contactez le support avec les logs [FSS].

---

**Bon usage du plugin WP Rolling Mail !**
