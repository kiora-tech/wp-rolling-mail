<?php
if (!defined('ABSPATH')) {
    exit;
}

class FSS_Email_Handler {

    public function __construct() {
        add_action('frm_after_create_entry', array($this, 'send_sequential_emails'), 10, 2);
    }

    public function send_sequential_emails($entry_id, $form_id) {
        // PHASE 2: Vérifier si ce formulaire doit utiliser la rotation
        if (!$this->should_use_rotation($form_id)) {
            return; // Skip rotation, laisser Formidable gérer l'envoi normal
        }

        $entry = FrmEntry::getOne($entry_id, true);
        $submitted_data = $entry->metas;

        // PHASE 3 ENHANCED: Déterminer la liste d'emails appropriée selon la thématique
        $email_list = $this->get_appropriate_email_list($entry);
        $cc_email_list = get_option('fss_email_cc', []);

        if (empty($email_list)) {
            return;
        }

        // Rotation de l'email: prendre le premier et le mettre à la fin
        $next_email = array_shift($email_list);
        array_push($email_list, $next_email);

        // PHASE 3 ENHANCED: Mettre à jour la bonne option selon la thématique
        $this->update_email_list($entry, $email_list);

        $to = array($next_email);
        if (!empty($cc_email_list)) {
            $to = array_merge($to, $cc_email_list);
        }

        $subject = get_option('fss_email_subject', 'Nouvelle soumission de formulaire');
        $message = "";
        foreach ($submitted_data as $key => $value) {
            if (is_numeric($key)) {
                $field = FrmField::getOne($key);
                if ($field) {
                    $message .= (isset($field->name) ? $field->name : "Field #$key") . ": " . $value . "\n";
                }
            }
        }

        wp_mail($to, $subject, $message);
    }

    /**
     * PHASE 2: Vérifie si un formulaire doit utiliser la rotation d'emails
     * @param int $form_id ID du formulaire
     * @return bool True si la rotation doit être utilisée
     */
    private function should_use_rotation($form_id) {
        $filter_mode = get_option('fss_form_filter_mode', 'all');
        $form_ids = get_option('fss_form_ids', array());

        // Mode "all": tous les formulaires utilisent la rotation (comportement actuel)
        if ($filter_mode === 'all') {
            return true;
        }

        // Mode "include": rotation uniquement si form_id est dans la liste
        if ($filter_mode === 'include') {
            return in_array($form_id, $form_ids);
        }

        // Mode "exclude": rotation sauf si form_id est dans la liste
        if ($filter_mode === 'exclude') {
            return !in_array($form_id, $form_ids);
        }

        // Par défaut, utiliser la rotation
        return true;
    }

    /**
     * PHASE 3 ENHANCED: Extrait et normalise la valeur thématique du champ configuré
     * Utilise le champ ID défini dans les options au lieu d'être hardcodé à 8
     * Gère les formats avec préfixe "Type : " automatiquement
     *
     * @param object $entry L'entrée Formidable contenant les métadonnées
     * @return string|null La clé thématique normalisée ou null
     */
    private function get_entry_thematic($entry) {
        // Récupérer le Field ID configuré dans les options
        $thematic_field_id = get_option('fss_thematic_field_id', null);

        if (!$thematic_field_id) {
            return null;
        }

        // Récupérer la valeur du champ depuis les métadonnées de l'entrée
        if (!isset($entry->metas[$thematic_field_id]) || empty($entry->metas[$thematic_field_id])) {
            return null;
        }

        $field_value = $entry->metas[$thematic_field_id];

        // Normaliser la valeur: enlever le préfixe "Type : " s'il existe
        $normalized_value = preg_replace('/^Type\s*:\s*/i', '', $field_value);
        $normalized_value = trim($normalized_value);

        // Créer une clé normalisée (lowercase, pas d'accents, underscores)
        $normalized_key = $this->normalize_key($normalized_value);

        return $normalized_key;
    }

    /**
     * PHASE 3 ENHANCED: Normalise une valeur de champ en clé utilisable
     * Convertit en minuscules, enlève les accents, remplace les caractères spéciaux
     *
     * @param string $value Valeur à normaliser
     * @return string Clé normalisée
     */
    private function normalize_key($value) {
        // Convertir en minuscules
        $key = strtolower($value);
        // Enlever les accents
        $key = remove_accents($key);
        // Remplacer les espaces et caractères spéciaux par des underscores
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        // Enlever les underscores en début/fin
        $key = trim($key, '_');
        return $key;
    }

    /**
     * PHASE 3 ENHANCED: Récupère la liste d'emails appropriée selon la thématique de l'entrée
     * Utilise la nouvelle structure de données dynamique au lieu des options hardcodées
     * Priorité: Liste thématique > Liste principale (fallback)
     *
     * @param object $entry L'entrée Formidable
     * @return array Liste d'emails à utiliser pour la rotation
     */
    private function get_appropriate_email_list($entry) {
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');

        // Si le filtrage thématique est désactivé, utiliser la liste principale
        if ($thematic_mode !== 'enabled') {
            return get_option('fss_emails', []);
        }

        // Récupérer la thématique de l'entrée
        $thematic_key = $this->get_entry_thematic($entry);

        // Si pas de thématique détectée, utiliser la liste principale
        if ($thematic_key === null) {
            return get_option('fss_emails', []);
        }

        // Récupérer la structure complète des emails thématiques
        $thematic_emails = get_option('fss_thematic_emails', array(
            'field_id' => null,
            'mappings' => array()
        ));

        // Vérifier que la structure est valide
        if (!isset($thematic_emails['mappings']) || !is_array($thematic_emails['mappings'])) {
            return get_option('fss_emails', []);
        }

        // Récupérer la liste d'emails pour cette clé thématique
        $thematic_email_list = isset($thematic_emails['mappings'][$thematic_key])
            ? $thematic_emails['mappings'][$thematic_key]
            : [];

        // Si la liste thématique est vide, utiliser la liste principale comme fallback
        if (empty($thematic_email_list)) {
            return get_option('fss_emails', []);
        }

        return $thematic_email_list;
    }

    /**
     * PHASE 3 ENHANCED: Met à jour la liste d'emails appropriée après rotation
     * Utilise la nouvelle structure de données dynamique
     * Sauvegarde la liste dans la bonne option (thématique ou principale)
     *
     * @param object $entry L'entrée Formidable
     * @param array $email_list La liste d'emails mise à jour après rotation
     */
    private function update_email_list($entry, $email_list) {
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');

        // Si le filtrage thématique est désactivé, mettre à jour la liste principale
        if ($thematic_mode !== 'enabled') {
            update_option('fss_emails', $email_list);
            return;
        }

        // Récupérer la thématique de l'entrée
        $thematic_key = $this->get_entry_thematic($entry);

        // Si pas de thématique, mettre à jour la liste principale
        if ($thematic_key === null) {
            update_option('fss_emails', $email_list);
            return;
        }

        // Récupérer la structure complète des emails thématiques
        $thematic_emails = get_option('fss_thematic_emails', array(
            'field_id' => null,
            'mappings' => array()
        ));

        // Vérifier que la structure est valide
        if (!isset($thematic_emails['mappings']) || !is_array($thematic_emails['mappings'])) {
            // Structure invalide, utiliser le fallback
            update_option('fss_emails', $email_list);
            return;
        }

        // Vérifier si cette clé thématique avait une liste configurée
        $original_thematic_list = isset($thematic_emails['mappings'][$thematic_key])
            ? $thematic_emails['mappings'][$thematic_key]
            : [];

        // Si la liste thématique n'était pas vide, on l'a utilisée, donc la mettre à jour
        if (!empty($original_thematic_list)) {
            $thematic_emails['mappings'][$thematic_key] = $email_list;
            update_option('fss_thematic_emails', $thematic_emails);
        } else {
            // Sinon, on a utilisé le fallback, donc mettre à jour la liste principale
            update_option('fss_emails', $email_list);
        }
    }
}
