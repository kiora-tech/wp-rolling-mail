# Exemples de Configuration - WP Rolling Mail

**10 cas d'usage détaillés de A à Z**

Chaque exemple contient : contexte, configuration complète, résultat attendu, captures d'écran textuelles, et points de vigilance.

---

## Table des matières

1. Agence web - 5 développeurs en rotation simple
2. Cabinet d'avocats - Routage par spécialité juridique
3. École - Routage par niveau scolaire
4. Agence immobilière - Routage géographique par ville
5. Clinique médicale - Routage par type de rendez-vous
6. E-commerce - Support client multilingue
7. Association - Gestion bénévoles avec supervision
8. Start-up SaaS - Routage par taille d'entreprise (leads)
9. Concessionnaire auto - Routage neuf vs occasion
10. Cabinet comptable - Routage par type de service

---

## Exemple 1 : Agence web - 5 développeurs en rotation simple

### Contexte

**Type d'entreprise :** Agence de développement web

**Équipe :**
- 5 développeurs freelances qui travaillent à distance
- Tous polyvalents (peuvent traiter tous types de demandes)
- Besoin de distribution équitable pour ne pas surcharger une seule personne

**Formulaire Formidable :**
- Formulaire de devis sur le site
- Champs : Nom, Email, Téléphone, Description du projet, Budget estimé

**Objectif :**
Distribuer automatiquement les demandes de devis entre les 5 développeurs, en rotation équitable.

---

### Configuration complète

#### Section : Form Filter Mode
```
Form Filter Mode : All forms (current behavior)
```
→ Tous les formulaires Formidable utilisent la rotation

#### Section : Thematic Filter Mode
```
Thematic Filter Mode : Disabled (use main rotation list)
```
→ Pas de routage thématique, on veut une simple rotation

#### Section : Email Subject
```
Email Subject : Nouvelle demande de devis - Site web
```

#### Section : Email Addresses (liste principale)
```
Email 1 : alex.dupont@freelance.fr
Email 2 : beatrice.martin@freelance.fr
Email 3 : charles.leroy@freelance.fr
Email 4 : diane.bernard@freelance.fr
Email 5 : etienne.rousseau@freelance.fr
```

#### Section : CC Email Addresses
```
(vide - pas de CC)
```

---

### Résultat attendu

**Scénario sur 20 demandes de devis :**

| Demande | Destinataire | Email envoyé à |
|---------|--------------|----------------|
| #1 | Dev 1 | alex.dupont@freelance.fr |
| #2 | Dev 2 | beatrice.martin@freelance.fr |
| #3 | Dev 3 | charles.leroy@freelance.fr |
| #4 | Dev 4 | diane.bernard@freelance.fr |
| #5 | Dev 5 | etienne.rousseau@freelance.fr |
| #6 | Dev 1 | alex.dupont@freelance.fr (rotation) |
| #7 | Dev 2 | beatrice.martin@freelance.fr |
| ... | ... | ... |
| #20 | Dev 5 | etienne.rousseau@freelance.fr |

**Distribution finale :**
- Alex : 4 devis
- Béatrice : 4 devis
- Charles : 4 devis
- Diane : 4 devis
- Étienne : 4 devis

**Total : 20 devis distribués équitablement (4 chacun)**

---

### Capture d'écran textuelle de la configuration

```
===================================
Sequential Submissions - Configuration
===================================

[General Settings]

Form Filter Mode : [All forms (current behavior) ▼]

Thematic Filter Mode : [Disabled (use main rotation list) ▼]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Email Subject]
┌────────────────────────────────────────┐
│ Nouvelle demande de devis - Site web   │
└────────────────────────────────────────┘

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Email Addresses]
┌────────────────────────────────────────┐
│ Email 1 : alex.dupont@freelance.fr  🗑 │
│ Email 2 : beatrice.martin@freelance.fr 🗑│
│ Email 3 : charles.leroy@freelance.fr  🗑│
│ Email 4 : diane.bernard@freelance.fr  🗑│
│ Email 5 : etienne.rousseau@freelance.fr🗑│
└────────────────────────────────────────┘
[Ajouter un autre email]

✅ 5 email(s) configuré(s)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[CC Email Addresses]
(aucun email en copie configuré)

[Ajouter un autre email CC]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Enregistrer les modifications]
```

---

### Points de vigilance

**1. Gestion des congés**

Si Béatrice part en vacances 2 semaines :
- **Option A (recommandée) :** Retirer temporairement son email de la liste
  - → Les 4 autres développeurs se partagent les demandes (25% chacun)
  - → Penser à RE-AJOUTER son email à son retour

- **Option B :** Laisser son email
  - → Elle recevra 20% des demandes qui s'accumuleront dans sa boîte
  - → Elle les traitera à son retour

**2. Ajout d'un nouveau développeur**

Vous embauchez Fanny :
1. Allez dans Sequential Submissions
2. Cliquez sur "Ajouter un autre email"
3. Tapez : fanny.nouveau@freelance.fr
4. Sauvegardez
5. À partir de maintenant, rotation sur 6 personnes (16.67% chacune)

**3. Surveillance du volume**

Avec 5 développeurs :
- Si vous recevez 100 devis/mois → 20 devis/mois par personne (OK)
- Si vous recevez 500 devis/mois → 100 devis/mois par personne (peut-être trop)

Ajustez le nombre de développeurs selon le volume.

---

## Exemple 2 : Cabinet d'avocats - Routage par spécialité juridique

### Contexte

**Type d'entreprise :** Cabinet d'avocats multi-spécialités

**Équipes :**
- **Droit de la famille :** 2 avocats (maître Dubois, maître Martin)
- **Droit commercial :** 3 avocats (maître Leroy, maître Bernard, maître Rousseau)
- **Droit immobilier :** 1 avocat (maître Petit)
- **Autres demandes :** Secrétariat général (accueil@)

**Formulaire Formidable :**
- Formulaire de première consultation
- Champ radio **"Domaine juridique"** (Field ID 12) avec options :
  - Droit de la famille
  - Droit commercial
  - Droit immobilier
  - Autre / Je ne sais pas

**Objectif :**
Router automatiquement chaque demande vers les avocats spécialisés, avec rotation au sein de chaque spécialité.

---

### Configuration complète

#### Section : Form Filter Mode
```
Form Filter Mode : All forms
```

#### Section : Thematic Filter Mode
```
Thematic Filter Mode : Enabled (route by thematic field)
```

#### Section : Thematic Field Selection
```
Thematic Field Selection : Domaine juridique (ID: 12, Form: Consultation, Type: radio)
```
→ Après sélection, la page se recharge

#### Section : Thematic Email Mappings

**Bloc "Droit de la famille" (2 avocats)**
```
┌─────────────────────────────────────────┐
│ Droit de la famille (45 entries)       │
│ Normalized key: droit_de_la_famille     │
├─────────────────────────────────────────┤
│ Email 1 : dubois@cabinet-avocat.fr  🗑  │
│ Email 2 : martin@cabinet-avocat.fr  🗑  │
│ [Ajouter un autre email]                │
│ ✅ 2 email(s) configuré(s)              │
└─────────────────────────────────────────┘
```

**Bloc "Droit commercial" (3 avocats)**
```
┌─────────────────────────────────────────┐
│ Droit commercial (78 entries)           │
│ Normalized key: droit_commercial        │
├─────────────────────────────────────────┤
│ Email 1 : leroy@cabinet-avocat.fr   🗑  │
│ Email 2 : bernard@cabinet-avocat.fr 🗑  │
│ Email 3 : rousseau@cabinet-avocat.fr🗑  │
│ [Ajouter un autre email]                │
│ ✅ 3 email(s) configuré(s)              │
└─────────────────────────────────────────┘
```

**Bloc "Droit immobilier" (1 avocat)**
```
┌─────────────────────────────────────────┐
│ Droit immobilier (23 entries)           │
│ Normalized key: droit_immobilier        │
├─────────────────────────────────────────┤
│ Email 1 : petit@cabinet-avocat.fr   🗑  │
│ [Ajouter un autre email]                │
│ ✅ 1 email(s) configuré(s)              │
└─────────────────────────────────────────┘
```

**Bloc "Autre / Je ne sais pas" (secrétariat)**
```
┌─────────────────────────────────────────┐
│ Autre / Je ne sais pas (12 entries)     │
│ Normalized key: autre_je_ne_sais_pas    │
├─────────────────────────────────────────┤
│ Email 1 : accueil@cabinet-avocat.fr 🗑  │
│ [Ajouter un autre email]                │
│ ✅ 1 email(s) configuré(s)              │
└─────────────────────────────────────────┘
```

#### Section : Email Subject
```
Email Subject : Nouvelle demande de consultation juridique
```

#### Section : Email Addresses (fallback)
```
Email 1 : accueil@cabinet-avocat.fr
```
→ Si une valeur inattendue arrive, elle va au secrétariat

#### Section : CC
```
Email CC 1 : direction@cabinet-avocat.fr
```
→ L'associé principal reçoit une copie de toutes les demandes

---

### Résultat attendu

**Scénario sur 30 demandes :**

| # | Domaine sélectionné | Destinataire | Raison |
|---|---------------------|--------------|--------|
| 1 | Droit de la famille | dubois@ | 1er de la liste famille |
| 2 | Droit commercial | leroy@ | 1er de la liste commercial |
| 3 | Droit de la famille | martin@ | rotation famille (2ème) |
| 4 | Droit immobilier | petit@ | seul sur immobilier |
| 5 | Droit commercial | bernard@ | rotation commercial (2ème) |
| 6 | Autre | accueil@ | liste thématique "Autre" |
| 7 | Droit de la famille | dubois@ | rotation famille (retour au 1er) |
| 8 | Droit commercial | rousseau@ | rotation commercial (3ème) |
| 9 | Droit immobilier | petit@ | seul sur immobilier |
| 10 | Droit commercial | leroy@ | rotation commercial (retour au 1er) |

**Distribution finale après 30 demandes (estimation) :**
- Droit famille : 10 demandes → dubois 5, martin 5
- Droit commercial : 15 demandes → leroy 5, bernard 5, rousseau 5
- Droit immobilier : 3 demandes → petit 3
- Autre : 2 demandes → accueil 2

**PLUS :** direction@ reçoit les 30 demandes en CC

---

### Points de vigilance

**1. Avocat seul sur une spécialité**

Maître Petit est seul sur l'immobilier. Si il part en congé :
- Retirer son email de la liste thématique "Droit immobilier"
- Résultat : les demandes immobilier iront au fallback (accueil@)
- Le secrétariat pourra les réorienter manuellement vers un autre avocat ou les garder pour le retour de maître Petit

**2. Nouvelle spécialité**

Vous ajoutez une option "Droit du travail" dans le formulaire Formidable :
1. Soumettez AU MOINS 1 formulaire test avec "Droit du travail"
2. Retournez dans Sequential Submissions
3. Un nouveau bloc "Droit du travail" apparaîtra automatiquement
4. Ajoutez les emails des avocats spécialisés
5. Sauvegardez

**3. Supervision par la direction**

La direction reçoit TOUT en CC (potentiellement 500+ emails/mois).
- Créez un filtre automatique dans leur boîte mail pour organiser par spécialité
- Ou retirez le CC et consultez plutôt les logs WordPress en cas de besoin

---

## Exemple 3 : École - Routage par niveau scolaire

### Contexte

**Type d'organisation :** École primaire et collège privé

**Équipes :**
- **Maternelle :** directrice-maternelle@ecole.fr
- **CP-CE1-CE2 :** responsable-cycle2@ecole.fr
- **CM1-CM2 :** responsable-cycle3@ecole.fr
- **Collège (6ème-3ème) :** cpe-college@ecole.fr
- **Administration générale :** secretariat@ecole.fr (fallback)

**Formulaire Formidable :**
- Formulaire d'inscription ou demande d'information
- Champ select **"Niveau scolaire demandé"** (Field ID 5) :
  - Petite/Moyenne/Grande Section (Maternelle)
  - CP
  - CE1
  - CE2
  - CM1
  - CM2
  - 6ème
  - 5ème
  - 4ème
  - 3ème

**Objectif :**
Chaque niveau envoie vers le bon responsable pédagogique. Pas besoin de rotation (1 seul responsable par cycle).

---

### Configuration

#### Thematic Filter Mode
```
Enabled (route by thematic field)
```

#### Thematic Field Selection
```
Niveau scolaire demandé (ID: 5, Type: select)
```

#### Thematic Email Mappings

**Stratégie :** Grouper les niveaux par cycle

**Maternelle (PS, MS, GS)**
```
Petite/Moyenne/Grande Section (Maternelle)
Email : directrice-maternelle@ecole.fr
```

**Cycle 2 (CP, CE1, CE2)**
```
CP
Email : responsable-cycle2@ecole.fr

CE1
Email : responsable-cycle2@ecole.fr

CE2
Email : responsable-cycle2@ecole.fr
```

**Cycle 3 (CM1, CM2)**
```
CM1
Email : responsable-cycle3@ecole.fr

CM2
Email : responsable-cycle3@ecole.fr
```

**Collège (6ème à 3ème)**
```
6ème, 5ème, 4ème, 3ème
Email : cpe-college@ecole.fr
```

#### Email Addresses (fallback)
```
secretariat@ecole.fr
```

#### CC
```
(vide)
```

---

### Résultat attendu

| Formulaire | Niveau choisi | Destinataire |
|------------|---------------|--------------|
| #1 | Petite Section | directrice-maternelle@ |
| #2 | CP | responsable-cycle2@ |
| #3 | CM1 | responsable-cycle3@ |
| #4 | 5ème | cpe-college@ |
| #5 | CE2 | responsable-cycle2@ |
| #6 | Grande Section | directrice-maternelle@ |

**Logique :** Chaque responsable ne reçoit QUE les demandes de son cycle.

---

### Points de vigilance

**1. Répétition de la même adresse**

Vous allez saisir `responsable-cycle2@ecole.fr` trois fois (pour CP, CE1, CE2). C'est normal et voulu. Le plugin détecte que c'est la même personne mais route correctement selon le niveau choisi.

**2. Changement de responsable**

Si le responsable du cycle 2 change :
1. Allez dans la config
2. Pour CP : changez l'email
3. Pour CE1 : changez l'email
4. Pour CE2 : changez l'email
→ 3 modifications pour la même personne (un peu fastidieux mais nécessaire)

**Alternative :** Utilisez une adresse générique `cycle2@ecole.fr` qui pointe vers le bon responsable (alias). Si le responsable change, vous modifiez juste l'alias côté serveur mail, pas la config du plugin.

---

## Exemple 4 : Agence immobilière - Routage géographique par ville

### Contexte

**Type d'entreprise :** Réseau d'agences immobilières multi-villes

**Agences :**
- **Paris :** 3 agents (rotation interne)
- **Lyon :** 2 agents
- **Marseille :** 4 agents
- **Bordeaux :** 1 agent
- **Autres villes :** Siège national

**Formulaire :**
- Recherche de bien immobilier
- Champ radio **"Ville d'intérêt"** (Field ID 9) :
  - Paris
  - Lyon
  - Marseille
  - Bordeaux
  - Autre ville

---

### Configuration

#### Thematic Email Mappings

**Paris (3 agents en rotation)**
```
agent-paris-1@agence-immo.fr
agent-paris-2@agence-immo.fr
agent-paris-3@agence-immo.fr
```

**Lyon (2 agents en rotation)**
```
agent-lyon-1@agence-immo.fr
agent-lyon-2@agence-immo.fr
```

**Marseille (4 agents en rotation)**
```
agent-marseille-1@agence-immo.fr
agent-marseille-2@agence-immo.fr
agent-marseille-3@agence-immo.fr
agent-marseille-4@agence-immo.fr
```

**Bordeaux (1 agent)**
```
agent-bordeaux@agence-immo.fr
```

**Autre ville**
```
siege-national@agence-immo.fr
```

#### Email Addresses (fallback)
```
contact@agence-immo.fr
```

#### CC
```
direction-commerciale@agence-immo.fr
```

---

### Résultat attendu

**Scénario : 20 demandes**

| Demande | Ville | Destinataire | Note |
|---------|-------|--------------|------|
| #1 | Paris | agent-paris-1@ | 1er de la rotation Paris |
| #2 | Lyon | agent-lyon-1@ | 1er de la rotation Lyon |
| #3 | Paris | agent-paris-2@ | 2ème de la rotation Paris |
| #4 | Marseille | agent-marseille-1@ | 1er Marseille |
| #5 | Paris | agent-paris-3@ | 3ème Paris |
| #6 | Lyon | agent-lyon-2@ | 2ème Lyon |
| #7 | Bordeaux | agent-bordeaux@ | seul agent |
| #8 | Paris | agent-paris-1@ | retour au 1er (rotation) |
| #9 | Marseille | agent-marseille-2@ | 2ème Marseille |
| #10 | Autre | siege-national@ | valeur "Autre" |

**Chaque ville a sa rotation INDÉPENDANTE.**

---

### Points de vigilance

**1. Équité par ville, pas globale**

Les agents Paris reçoivent plus d'emails que les agents Bordeaux (car Paris a plus de demandes). C'est normal et souhaité (routage géographique).

Si vous voulez une équité GLOBALE (chaque agent reçoit le même nombre), utilisez une rotation simple sans thématique.

**2. Expansion géographique**

Vous ouvrez une agence à Toulouse :
1. Ajoutez "Toulouse" dans les options du champ Formidable
2. Soumettez 1 formulaire test avec "Toulouse"
3. Retournez dans la config → un bloc "Toulouse" apparaît
4. Ajoutez les emails des agents Toulouse
5. Sauvegardez

---

## Exemple 5 : Clinique médicale - Routage par type de rendez-vous

### Contexte

**Type d'organisation :** Clinique médicale privée

**Services :**
- **Médecine générale :** 4 médecins (rotation)
- **Pédiatrie :** 2 pédiatres (rotation)
- **Cardiologie :** 1 cardiologue
- **Dermatologie :** 2 dermatologues (rotation)
- **Secrétariat médical :** Pour les urgences et autres

**Formulaire :**
- Prise de rendez-vous en ligne
- Champ select **"Type de consultation"** (Field ID 7) :
  - Médecine générale
  - Pédiatrie
  - Cardiologie
  - Dermatologie
  - Urgence / Autre

---

### Configuration

#### Thematic Email Mappings

**Médecine générale**
```
dr.martin@clinique.fr
dr.dubois@clinique.fr
dr.bernard@clinique.fr
dr.rousseau@clinique.fr
```

**Pédiatrie**
```
dr.pediatre-leroy@clinique.fr
dr.pediatre-petit@clinique.fr
```

**Cardiologie**
```
dr.cardio-durand@clinique.fr
```

**Dermatologie**
```
dr.dermato-garcia@clinique.fr
dr.dermato-lopez@clinique.fr
```

**Urgence / Autre**
```
secretariat-medical@clinique.fr
```

#### Email Subject
```
Nouvelle demande de rendez-vous médical
```

#### Fallback
```
secretariat-medical@clinique.fr
```

#### CC
```
coordinateur-medical@clinique.fr
```

---

### Résultat attendu

**Avantages :**
- Chaque médecin reçoit uniquement les demandes de sa spécialité
- Pas de tri manuel à faire
- Le coordinateur médical supervise tout (CC)
- Les urgences vont directement au secrétariat qui peut gérer en priorité

**Distribution typique (100 demandes/mois) :**
- Médecine générale : 50 demandes → 12-13 par médecin
- Pédiatrie : 20 demandes → 10 par pédiatre
- Cardiologie : 15 demandes → 15 au cardiologue
- Dermatologie : 10 demandes → 5 par dermatologue
- Urgence : 5 demandes → secrétariat

---

### Points de vigilance

**1. Médecin en congé**

Dr Martin (médecine générale) part 3 semaines :
- Retirez son email de la liste "Médecine générale"
- Les 3 autres médecins se partagent ses demandes temporairement
- PENSEZ à le rajouter à son retour

**2. Coordination avec le logiciel de rendez-vous**

Ce formulaire génère un EMAIL, pas un rendez-vous dans l'agenda.

Workflow recommandé :
1. Patient soumet le formulaire
2. Médecin reçoit l'email
3. Médecin (ou son assistante) appelle le patient pour fixer le RDV
4. RDV saisi dans le logiciel de planning

Si vous voulez une création automatique de RDV, il faut une intégration plus poussée (pas juste WP Rolling Mail).

---

## Exemple 6 : E-commerce - Support client multilingue

### Contexte

**Type d'entreprise :** Boutique en ligne internationale

**Équipe support :**
- **Français :** 3 agents
- **Anglais :** 4 agents
- **Espagnol :** 2 agents
- **Allemand :** 1 agent
- **Autres langues :** Support général (Google Translate)

**Formulaire :**
- Formulaire de contact SAV
- Champ radio **"Langue préférée"** (Field ID 11) :
  - Français
  - English
  - Español
  - Deutsch
  - Other / Autre

---

### Configuration

#### Thematic Email Mappings

**Français**
```
support-fr-alice@boutique.com
support-fr-bob@boutique.com
support-fr-claire@boutique.com
```

**English**
```
support-en-john@boutique.com
support-en-mary@boutique.com
support-en-david@boutique.com
support-en-sarah@boutique.com
```

**Español**
```
support-es-carlos@boutique.com
support-es-maria@boutique.com
```

**Deutsch**
```
support-de-hans@boutique.com
```

**Other / Autre**
```
support-general@boutique.com
```

#### CC
```
manager-support@boutique.com
```

---

### Résultat attendu

**Client francophone → Reçoit une réponse en français d'un agent FR**

**Client anglophone → Reçoit une réponse en anglais d'un agent EN**

**Temps de réponse :** Amélioration de 30% car les agents répondent dans leur langue maternelle (pas de traduction manuelle nécessaire).

---

### Points de vigilance

**Fuseau horaire**

Si vos agents anglais sont aux États-Unis et vos agents français en France :
- Les demandes arrivent 24/7
- Un agent FR qui se lève à 8h (Paris) voit peut-être 5 emails accumulés de la nuit
- Un agent US à 9h (New York) voit les demandes européennes de la journée

Solution : accepter ce délai OU embaucher des agents de nuit.

---

## Exemple 7 : Association - Gestion bénévoles avec supervision

### Contexte

**Type d'organisation :** Association humanitaire

**Équipe :**
- 6 bénévoles qui répondent aux demandes d'aide
- 1 coordinateur qui supervise et intervient si besoin

**Formulaire :**
- Demande d'assistance
- Pas de thématique (rotation simple)

---

### Configuration

#### Rotation simple (pas de thématique)

**Email Addresses**
```
benevole1@association.org
benevole2@association.org
benevole3@association.org
benevole4@association.org
benevole5@association.org
benevole6@association.org
```

**CC**
```
coordinateur@association.org
```

---

### Résultat attendu

- Les 6 bénévoles se partagent équitablement les demandes
- Le coordinateur reçoit TOUT en CC
- Si un bénévole ne répond pas sous 48h, le coordinateur peut prendre le relais

---

### Points de vigilance

**Bénévoles peu disponibles**

Contrairement à des employés, les bénévoles peuvent :
- Oublier de consulter leurs emails
- Partir en vacances sans prévenir
- Arrêter leur engagement

**Solution :**
- Le coordinateur (CC) voit tout et peut relancer
- Mettez en place une règle : "Si pas de réponse sous 48h, transférer au coordinateur"
- Privilégiez une adresse email partagée plutôt que des adresses personnelles

---

## Exemple 8 : Start-up SaaS - Routage par taille d'entreprise (leads)

### Contexte

**Type d'entreprise :** Start-up SaaS B2B (logiciel pour entreprises)

**Équipes commerciales :**
- **Petites entreprises (1-10 employés) :** Inside sales (2 commerciaux juniors)
- **PME (11-100 employés) :** Account Executives (3 commerciaux)
- **Grandes entreprises (100+) :** Enterprise sales (1 commercial senior + 1 ingénieur avant-vente)

**Formulaire :**
- Demande de démo
- Champ select **"Taille de votre entreprise"** (Field ID 14) :
  - 1-10 employés
  - 11-50 employés
  - 51-100 employés
  - 101-500 employés
  - 500+ employés

---

### Configuration

#### Stratégie de groupement

Grouper les tailles en 3 catégories :

**Small (1-10)**
```
inside-sales-junior1@saas.io
inside-sales-junior2@saas.io
```

**Medium (11-100)**
```
ae-commercial1@saas.io
ae-commercial2@saas.io
ae-commercial3@saas.io
```

**Enterprise (100+)**
```
enterprise-sales@saas.io
presales-engineer@saas.io
```

**Astuce :** Vous devrez configurer les mêmes emails pour "11-50" et "51-100" (car les deux → Medium).

#### CC
```
vp-sales@saas.io
crm-ingest@salesforce.com (intégration CRM automatique)
```

---

### Résultat attendu

**Lead TPE (5 employés) → Junior traite (cycle de vente court, produit simple)**

**Lead PME (50 employés) → Account Executive traite (cycle moyen, démo personnalisée)**

**Lead Enterprise (1000 employés) → Senior + Ingénieur traitent ensemble (cycle long, POC technique)**

**Avantages :**
- Meilleure qualification automatique
- Taux de conversion amélioré (bon interlocuteur selon la taille)
- Les seniors ne perdent pas de temps sur les petits deals
- Les juniors ne sont pas dépassés par les gros comptes

---

## Exemple 9 : Concessionnaire auto - Routage neuf vs occasion

### Contexte

**Type d'entreprise :** Concessionnaire automobile multi-marques

**Équipes :**
- **Véhicules neufs :** 4 vendeurs
- **Véhicules d'occasion :** 3 vendeurs
- **Service après-vente :** 2 conseillers

**Formulaire :**
- Demande d'information véhicule
- Champ radio **"Type de véhicule recherché"** (Field ID 6) :
  - Véhicule neuf
  - Véhicule d'occasion
  - Service après-vente / Réparation
  - Autre

---

### Configuration

#### Thematic Email Mappings

**Véhicule neuf**
```
vendeur-neuf-1@concess-auto.fr
vendeur-neuf-2@concess-auto.fr
vendeur-neuf-3@concess-auto.fr
vendeur-neuf-4@concess-auto.fr
```

**Véhicule d'occasion**
```
vendeur-occasion-1@concess-auto.fr
vendeur-occasion-2@concess-auto.fr
vendeur-occasion-3@concess-auto.fr
```

**Service après-vente**
```
sav-conseiller-1@concess-auto.fr
sav-conseiller-2@concess-auto.fr
```

**Autre**
```
accueil@concess-auto.fr
```

#### CC
```
directeur-ventes@concess-auto.fr
```

---

### Résultat attendu

- Vendeurs neuf ne reçoivent que des demandes neuf (meilleure spécialisation)
- Vendeurs occasion ne reçoivent que des demandes occasion
- SAV traite uniquement les demandes techniques
- Pas de confusion ni de transfert manuel

---

## Exemple 10 : Cabinet comptable - Routage par type de service

### Contexte

**Type d'entreprise :** Cabinet d'expertise comptable

**Services :**
- **Comptabilité TPE/PME :** 5 comptables
- **Déclaration fiscale :** 2 fiscalistes
- **Paie et social :** 3 gestionnaires paie
- **Création d'entreprise :** 1 expert création
- **Audit et conseil :** 2 experts-comptables senior

**Formulaire :**
- Demande de devis
- Champ select **"Service demandé"** (Field ID 10) :
  - Tenue de comptabilité
  - Déclaration d'impôts / Fiscalité
  - Gestion de la paie
  - Création d'entreprise
  - Audit et conseil
  - Autre / Je ne sais pas

---

### Configuration

#### Thematic Email Mappings

**Tenue de comptabilité**
```
compta-1@cabinet-expert.fr
compta-2@cabinet-expert.fr
compta-3@cabinet-expert.fr
compta-4@cabinet-expert.fr
compta-5@cabinet-expert.fr
```

**Déclaration d'impôts / Fiscalité**
```
fiscal-expert-1@cabinet-expert.fr
fiscal-expert-2@cabinet-expert.fr
```

**Gestion de la paie**
```
paie-1@cabinet-expert.fr
paie-2@cabinet-expert.fr
paie-3@cabinet-expert.fr
```

**Création d'entreprise**
```
expert-creation@cabinet-expert.fr
```

**Audit et conseil**
```
ec-senior-1@cabinet-expert.fr
ec-senior-2@cabinet-expert.fr
```

**Autre / Je ne sais pas**
```
secretariat@cabinet-expert.fr
```

#### Fallback
```
contact@cabinet-expert.fr
```

#### CC
```
associe-principal@cabinet-expert.fr
comptabilite-analytique@cabinet-expert.fr (pour statistiques internes)
```

---

### Résultat attendu

**Client demande tenue de comptabilité → Reçoit un devis d'un comptable spécialisé TPE/PME**

**Client demande conseil fiscal → Reçoit une réponse d'un fiscaliste expert**

**Client demande création SARL → Expert création répond avec checklist complète**

**Avantages :**
- Réponse ultra-qualifiée (chaque expert dans son domaine)
- Devis plus précis et rapides
- Meilleure satisfaction client
- L'associé principal garde une visibilité sur tous les prospects (CC)

---

### Points de vigilance

**Double CC**

Vous avez 2 adresses en CC :
- associe-principal@ (supervision)
- comptabilite-analytique@ (statistiques)

Résultat : Chaque formulaire génère 3 emails (1 principal + 2 CC).

Sur 100 formulaires/mois = 300 emails envoyés.

**Impact :** Vérifiez les quotas de votre serveur SMTP (certains limitent à 500/jour).

---

## Tableau récapitulatif des 10 exemples

| # | Cas d'usage | Thématique ? | Nb de listes | CC ? | Complexité |
|---|-------------|--------------|--------------|------|------------|
| 1 | Agence web | Non | 1 (5 emails) | Non | ⭐ Simple |
| 2 | Cabinet avocats | Oui | 4 listes | Oui | ⭐⭐⭐ Avancé |
| 3 | École | Oui | 4 listes | Non | ⭐⭐ Moyen |
| 4 | Agence immo | Oui | 5 listes | Oui | ⭐⭐⭐ Avancé |
| 5 | Clinique | Oui | 5 listes | Oui | ⭐⭐⭐ Avancé |
| 6 | E-commerce | Oui | 5 listes | Oui | ⭐⭐ Moyen |
| 7 | Association | Non | 1 (6 emails) | Oui | ⭐ Simple |
| 8 | SaaS B2B | Oui | 3 listes | Oui (2 CC) | ⭐⭐⭐ Avancé |
| 9 | Concessionnaire | Oui | 4 listes | Oui | ⭐⭐ Moyen |
| 10 | Cabinet compta | Oui | 6 listes | Oui (2 CC) | ⭐⭐⭐⭐ Expert |

---

## Conseils pour créer votre propre configuration

**Étape 1 : Analysez votre besoin**

Posez-vous ces questions :
- Combien de personnes doivent recevoir les formulaires ?
- Sont-ils polyvalents ou spécialisés ?
- Y a-t-il des catégories naturelles dans vos formulaires ? (type de demande, géographie, langue, etc.)
- Avez-vous besoin de supervision (CC) ?

**Étape 2 : Dessinez votre flux**

Sur papier, dessinez :
```
Formulaire soumis
    ↓
[Champ thématique ?] → OUI : Quelle valeur ? → Liste A, B ou C
                     → NON : Liste principale
```

**Étape 3 : Commencez simple**

Ne configurez pas tout d'un coup. Commencez par :
1. Une rotation simple sans thématique
2. Testez 1 semaine
3. Puis ajoutez le routage thématique si besoin

**Étape 4 : Documentez**

Prenez une capture d'écran ET écrivez un document Word/Google Doc avec :
- Date de configuration
- Qui reçoit quoi
- Raison de chaque choix

**Étape 5 : Surveillez et ajustez**

Pendant les 2 premières semaines :
- Activez les logs
- Consultez-les quotidiennement
- Demandez des retours aux destinataires
- Ajustez la configuration si nécessaire

---

**FIN DES EXEMPLES** - 10 cas d'usage complets détaillés

Pour toute question, consultez le **GUIDE_CONFIGURATION.md** (guide complet).
