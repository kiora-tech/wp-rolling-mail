<?php
if (!defined('ABSPATH')) {
    exit;
}

class FSS_Email_Handler {

    public function __construct() {
        add_action('frm_after_create_entry', array($this, 'send_sequential_emails'), 10, 2);
    }

    public function send_sequential_emails($entry_id, $form_id) {
        // PHASE 5.2: Log début de traitement
        error_log("[FSS] === START Processing Entry {$entry_id} from Form {$form_id} ===");

        // PHASE 2: Vérifier si ce formulaire doit utiliser la rotation
        if (!$this->should_use_rotation($form_id)) {
            error_log("[FSS] Form {$form_id} | Entry {$entry_id} | Rotation: NO | Reason: Form excluded by filter settings");
            error_log("[FSS] === END Processing Entry {$entry_id} ===");
            return; // Skip rotation, laisser Formidable gérer l'envoi normal
        }

        error_log("[FSS] Form {$form_id} | Entry {$entry_id} | Rotation: YES | Reason: Form included by filter settings");

        $entry = FrmEntry::getOne($entry_id, true);
        $submitted_data = $entry->metas;

        // PHASE 5.3 - CAS 8: Détecter configuration thématique incohérente
        $thematic_field_id = get_option('fss_thematic_field_id', null);
        $thematic_emails_option = get_option('fss_thematic_emails', array(
            'field_id' => null,
            'mappings' => array()
        ));
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');

        if ($thematic_mode === 'enabled' && $thematic_field_id && empty($thematic_emails_option['mappings'])) {
            error_log("[FSS] WARNING: Thematic field is configured (ID: {$thematic_field_id}) but no thematic email mappings are defined");
            error_log("[FSS] Please add email addresses for each thematic value, or disable thematic routing");
            error_log("[FSS] System will fallback to main rotation list");
        }

        // PHASE 3 ENHANCED: Déterminer la liste d'emails appropriée selon la thématique
        $thematic_key = $this->get_entry_thematic($entry);
        $email_list = $this->get_appropriate_email_list($entry);
        $cc_email_list = get_option('fss_email_cc', []);
        $bcc_email_list = get_option('fss_email_bcc', []);

        // PHASE 5.3 - CAS 5: Filtrer et valider les emails
        $email_list = $this->validate_and_filter_emails($email_list, 'main/thematic rotation');
        $cc_email_list = $this->validate_and_filter_emails($cc_email_list, 'CC');
        $bcc_email_list = $this->validate_and_filter_emails($bcc_email_list, 'BCC');

        // PHASE 5.3 - CAS 3: Aucune liste d'emails configurée (ni thématique ni principale)
        if (empty($email_list)) {
            error_log("[FSS] CRITICAL ERROR: No valid email addresses configured anywhere (neither thematic nor main)");
            error_log("[FSS] Cannot send email for entry {$entry_id}. Please configure at least one valid email address.");
            error_log("[FSS] === END Processing Entry {$entry_id} ===");
            return false;
        }

        // PHASE 5.2: Log de la rotation
        $current_index = 0; // L'index actuel est toujours 0 car on utilise array_shift
        $total_emails = count($email_list);
        error_log("[FSS] Current rotation index: {$current_index} (total addresses: {$total_emails})");

        // Rotation de l'email: prendre le premier et le mettre à la fin
        $next_email = array_shift($email_list);
        array_push($email_list, $next_email);

        $new_index = 0; // Après rotation, le nouvel index est aussi 0
        error_log("[FSS] Selected email: {$next_email}");
        error_log("[FSS] New rotation index: {$new_index}");

        $to = array($next_email);

        // PHASE 5.2: Log des CC
        $cc_count = count($cc_email_list);
        if ($cc_count > 0) {
            error_log("[FSS] Adding {$cc_count} CC recipients: " . implode(', ', $cc_email_list));
            $to = array_merge($to, $cc_email_list);
        } else {
            error_log("[FSS] No CC recipients configured");
        }

        // BONUS: Gestion des BCC (copie cachée)
        $bcc_count = count($bcc_email_list);
        $headers = array();
        if ($bcc_count > 0) {
            error_log("[FSS] Adding {$bcc_count} BCC recipients: " . implode(', ', $bcc_email_list));
            foreach ($bcc_email_list as $bcc_email) {
                $headers[] = 'Bcc: ' . $bcc_email;
            }
        } else {
            error_log("[FSS] No BCC recipients configured");
        }

        // PHASE 5.2: Log de la construction du message
        $subject = get_option('fss_email_subject', 'Nouvelle soumission de formulaire');
        error_log("[FSS] Building email with subject: '{$subject}'");

        $message = "";
        foreach ($submitted_data as $key => $value) {
            if (is_numeric($key)) {
                $field = FrmField::getOne($key);
                if ($field) {
                    $message .= (isset($field->name) ? $field->name : "Field #$key") . ": " . $value . "\n";
                }
            }
        }

        $message_length = strlen($message);
        error_log("[FSS] Message body length: {$message_length} characters");

        // PHASE 5.2: Log de l'envoi
        $recipient_info = "{$next_email}";
        if ($cc_count > 0) $recipient_info .= " (+ {$cc_count} CC)";
        if ($bcc_count > 0) $recipient_info .= " (+ {$bcc_count} BCC)";
        error_log("[FSS] Sending email to: {$recipient_info}");

        // PHASE 5.3 - CAS 7: Gestion de l'échec de wp_mail()
        $result = wp_mail($to, $subject, $message, $headers);

        if ($result) {
            error_log("[FSS] ✓ Email sent successfully");

            // PHASE 3 ENHANCED: Mettre à jour la bonne option selon la thématique
            // Incrémenter SEULEMENT si envoi réussi
            $this->update_email_list($entry, $email_list);
        } else {
            error_log("[FSS] ✗ CRITICAL: Email sending FAILED to {$next_email}");
            error_log("[FSS] Rotation index NOT incremented (will retry with same email on next submission)");
            error_log("[FSS] Possible causes: SMTP server down, incorrect email configuration, blocked by server");
            error_log("[FSS] === END Processing Entry {$entry_id} ===");
            return false;
        }

        // PHASE 5.2: Log fin de traitement
        error_log("[FSS] === END Processing Entry {$entry_id} ===");

        return $result;
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
     * PHASE 3 ENHANCED + PHASE 5.3: Extrait et normalise la valeur thématique du champ configuré
     * Utilise le champ ID défini dans les options au lieu d'être hardcodé à 8
     * Gère les formats avec préfixe "Type : " automatiquement
     *
     * PHASE 5.3 - Edge Cases gérés:
     * - CAS 1: Field ID inexistant ou invalide
     * - CAS 2: Valeur de champ vide ou null
     *
     * @param object $entry L'entrée Formidable contenant les métadonnées
     * @return string|null La clé thématique normalisée ou null
     */
    private function get_entry_thematic($entry) {
        // Récupérer le Field ID configuré dans les options
        $thematic_field_id = get_option('fss_thematic_field_id', null);

        // PHASE 5.2: Log de la configuration du champ thématique
        if (!$thematic_field_id) {
            error_log("[FSS] No thematic field configured, using main rotation");
            return null;
        }

        error_log("[FSS] Thematic field ID configured: {$thematic_field_id}");

        // PHASE 5.3 - CAS 1: Vérifier que le field existe dans Formidable
        $field = FrmField::getOne($thematic_field_id);
        if (!$field) {
            error_log("[FSS] WARNING: Configured thematic field ID {$thematic_field_id} does not exist in Formidable Forms");
            error_log("[FSS] Falling back to main rotation list");
            error_log("[FSS] Please check your thematic field configuration and ensure the field still exists");
            return null;
        }

        // Récupérer la valeur du champ depuis les métadonnées de l'entrée
        if (!isset($entry->metas[$thematic_field_id])) {
            error_log("[FSS] Field {$thematic_field_id} not found in entry metadata, using main rotation");
            return null;
        }

        $field_value = $entry->metas[$thematic_field_id];

        // PHASE 5.3 - CAS 2: Valeur de champ vide ou null
        $field_value = trim($field_value);
        if (empty($field_value)) {
            error_log("[FSS] Thematic field {$thematic_field_id} is empty in entry {$entry->id}");
            error_log("[FSS] Using main rotation list as fallback");
            return null;
        }

        // PHASE 5.2: Log de la valeur brute
        $safe_field_value = addslashes($field_value);
        error_log("[FSS] Raw thematic value: '{$safe_field_value}'");

        // Normaliser la valeur: enlever le préfixe "Type : " s'il existe
        $normalized_value = preg_replace('/^Type\s*:\s*/i', '', $field_value);
        $normalized_value = trim($normalized_value);

        // Créer une clé normalisée (lowercase, pas d'accents, underscores)
        $normalized_key = $this->normalize_key($normalized_value);

        // PHASE 5.2: Log de la clé normalisée
        error_log("[FSS] Normalized thematic key: '{$normalized_key}'");

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

        // PHASE 5.2: Log de la normalisation (optionnel, peut être verbeux)
        // error_log("[FSS] Normalizing key: '{$value}' → '{$key}'");

        return $key;
    }

    /**
     * PHASE 3 ENHANCED + PHASE 5.3: Récupère la liste d'emails appropriée selon la thématique de l'entrée
     * Utilise la nouvelle structure de données dynamique au lieu des options hardcodées
     * Priorité: Liste thématique > Liste principale (fallback)
     *
     * PHASE 5.3 - Edge Cases gérés:
     * - CAS 4: Liste thématique existe mais est vide
     *
     * @param object $entry L'entrée Formidable
     * @return array Liste d'emails à utiliser pour la rotation
     */
    private function get_appropriate_email_list($entry) {
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');

        // Si le filtrage thématique est désactivé, utiliser la liste principale
        if ($thematic_mode !== 'enabled') {
            $main_emails = get_option('fss_emails', []);
            $main_count = count($main_emails);
            error_log("[FSS] Thematic mode disabled, using main rotation ({$main_count} addresses)");
            return $main_emails;
        }

        // Récupérer la thématique de l'entrée
        $thematic_key = $this->get_entry_thematic($entry);

        // Si pas de thématique détectée, utiliser la liste principale
        if ($thematic_key === null) {
            $main_emails = get_option('fss_emails', []);
            $main_count = count($main_emails);
            error_log("[FSS] No thematic detected, falling back to main rotation ({$main_count} addresses)");
            return $main_emails;
        }

        // Récupérer la structure complète des emails thématiques
        $thematic_emails = get_option('fss_thematic_emails', array(
            'field_id' => null,
            'mappings' => array()
        ));

        // Vérifier que la structure est valide
        if (!isset($thematic_emails['mappings']) || !is_array($thematic_emails['mappings'])) {
            $main_emails = get_option('fss_emails', []);
            $main_count = count($main_emails);
            error_log("[FSS] Invalid thematic structure, falling back to main rotation ({$main_count} addresses)");
            return $main_emails;
        }

        // Récupérer la liste d'emails pour cette clé thématique
        $thematic_email_list = isset($thematic_emails['mappings'][$thematic_key])
            ? $thematic_emails['mappings'][$thematic_key]
            : [];

        // PHASE 5.3 - CAS 4: Si la liste thématique est vide, utiliser la liste principale comme fallback
        if (empty($thematic_email_list)) {
            $main_emails = get_option('fss_emails', []);
            $main_count = count($main_emails);
            error_log("[FSS] WARNING: Thematic list '{$thematic_key}' is configured but empty");
            error_log("[FSS] Falling back to main rotation list ({$main_count} addresses)");
            return $main_emails;
        }

        // PHASE 5.2: Log de la liste thématique trouvée
        $thematic_count = count($thematic_email_list);
        error_log("[FSS] Thematic email list '{$thematic_key}' has {$thematic_count} addresses");

        return $thematic_email_list;
    }

    /**
     * PHASE 5.3: Valide et filtre une liste d'emails
     * Supprime les emails invalides et log les problèmes détectés
     *
     * PHASE 5.3 - Edge Cases gérés:
     * - CAS 5: Email invalide dans la liste
     *
     * @param array $emails Liste d'emails à valider
     * @param string $list_type Type de liste (pour le logging)
     * @return array Liste d'emails valides uniquement
     */
    private function validate_and_filter_emails($emails, $list_type = 'email') {
        if (!is_array($emails)) {
            error_log("[FSS] WARNING: Invalid email list format for {$list_type} (expected array)");
            return array();
        }

        if (empty($emails)) {
            return array();
        }

        $original_count = count($emails);
        $invalid_emails = array();

        // Filtrer et valider chaque email
        $valid_emails = array_filter($emails, function($email) use (&$invalid_emails) {
            $email = trim($email);
            if (empty($email)) {
                return false;
            }
            if (!is_email($email)) {
                $invalid_emails[] = $email;
                return false;
            }
            return true;
        });

        // Logger les emails invalides détectés
        if (!empty($invalid_emails)) {
            foreach ($invalid_emails as $invalid_email) {
                error_log("[FSS] WARNING: Invalid email address removed from {$list_type} list: '{$invalid_email}'");
            }
            $removed_count = count($invalid_emails);
            error_log("[FSS] {$removed_count} invalid email(s) removed from {$list_type} list");
        }

        $valid_count = count($valid_emails);
        if ($original_count > 0 && $valid_count === 0) {
            error_log("[FSS] CRITICAL ERROR: All {$original_count} email addresses in {$list_type} list are invalid");
        }

        return array_values($valid_emails); // Re-index array
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
            // PHASE 5.2: Log de la mise à jour
            $new_index = 0; // Après rotation, le premier élément est le nouvel index
            error_log("[FSS] Updated main rotation index: {$new_index}");
            return;
        }

        // Récupérer la thématique de l'entrée
        $thematic_key = $this->get_entry_thematic($entry);

        // Si pas de thématique, mettre à jour la liste principale
        if ($thematic_key === null) {
            update_option('fss_emails', $email_list);
            // PHASE 5.2: Log de la mise à jour
            $new_index = 0;
            error_log("[FSS] Updated main rotation index: {$new_index}");
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
            // PHASE 5.2: Log de la mise à jour
            $new_index = 0;
            error_log("[FSS] Updated main rotation index: {$new_index} (thematic structure invalid)");
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
            // PHASE 5.2: Log de la mise à jour thématique
            $new_index = 0;
            error_log("[FSS] Updated thematic rotation index for '{$thematic_key}': {$new_index}");
        } else {
            // Sinon, on a utilisé le fallback, donc mettre à jour la liste principale
            update_option('fss_emails', $email_list);
            // PHASE 5.2: Log de la mise à jour
            $new_index = 0;
            error_log("[FSS] Updated main rotation index: {$new_index} (thematic list was empty)");
        }
    }
}
