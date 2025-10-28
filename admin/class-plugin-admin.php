<?php

class Formidable_Sequential_Submissions_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'maybe_run_migration' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    public function enqueue_scripts($hook) {
        if ('toplevel_page_formidable-sequential-submissions' !== $hook) {
            return;
        }
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('fss-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), null, true);
    }

    public function enqueue_styles() {
        wp_enqueue_style('fss-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css', array(), '1.0', 'all');

        $translations = array(
            'emailAddress' => __('email', 'fss'),
            'ccEmailAddress' => __('email cc', 'fss'),
            'invalidEmail' => __('invalid email', 'fss'),
        );
        wp_localize_script('fss-admin-script', 'fss_translations', $translations);
    }

    public function add_admin_menu() {
        add_menu_page('Formidable Sequential Submissions', 'Sequential Submissions', 'manage_options', 'formidable-sequential-submissions', array($this, 'display_plugin_admin_page'), 'dashicons-email', 6);
    }

    public function display_plugin_admin_page() {
        ?>
        <div class="wrap">
            <h2>Formidable Sequential Submissions</h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('fss_options_group');
                do_settings_sections('formidable-sequential-submissions');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        // Section générale
        add_settings_section('fss_general', __('general settings', 'fss'), null, 'formidable-sequential-submissions');

        // Filtrage par formulaire
        register_setting('fss_options_group', 'fss_form_filter_mode', 'sanitize_text_field');
        add_settings_field('fss_form_filter_mode_field', __('form filter mode', 'fss'), array($this, 'form_filter_mode_callback'), 'formidable-sequential-submissions', 'fss_general');

        register_setting('fss_options_group', 'fss_form_ids', array($this, 'sanitize_form_ids'));
        add_settings_field('fss_form_ids_field', __('filtered forms', 'fss'), array($this, 'form_ids_callback'), 'formidable-sequential-submissions', 'fss_general');

        // PHASE 3 ENHANCED: Filtrage par thématique
        register_setting('fss_options_group', 'fss_thematic_filter_mode', 'sanitize_text_field');
        add_settings_field('fss_thematic_filter_mode_field', __('thematic filter mode', 'fss'), array($this, 'thematic_filter_mode_callback'), 'formidable-sequential-submissions', 'fss_general');

        // PHASE 3 ENHANCED: Sélecteur de champ thématique
        register_setting('fss_options_group', 'fss_thematic_field_id', 'intval');
        add_settings_field('fss_thematic_field_id_field', __('Thematic Field Selection', 'fss'), array($this, 'thematic_field_id_callback'), 'formidable-sequential-submissions', 'fss_general');

        // PHASE 3 ENHANCED: Stockage des mappings d'emails par thématique
        register_setting('fss_options_group', 'fss_thematic_emails', array($this, 'sanitize_thematic_emails'));
        add_settings_field('fss_thematic_emails_field', __('Thematic Email Mappings', 'fss'), array($this, 'thematic_emails_callback'), 'formidable-sequential-submissions', 'fss_general');

        // Configuration des emails
        register_setting('fss_options_group', 'fss_email_subject', 'sanitize_text_field');
        add_settings_field('fss_email_subject_field', __('email subject', 'fss'), array($this, 'email_subject_callback'), 'formidable-sequential-submissions', 'fss_general');

        register_setting('fss_options_group', 'fss_emails', array($this, 'sanitize_emails'));
        add_settings_field('fss_emails_field', __('email addresses', 'fss'), array($this, 'emails_field_callback'), 'formidable-sequential-submissions', 'fss_general');

        register_setting('fss_options_group', 'fss_email_cc', array($this, 'sanitize_emails'));
        add_settings_field('fss_email_cc_field', __('email cc addresses', 'fss'), array($this, 'email_cc_callback'), 'formidable-sequential-submissions', 'fss_general');
    }

    public function form_filter_mode_callback() {
        $filter_mode = get_option('fss_form_filter_mode', 'all');
        ?>
        <select id="fss_form_filter_mode" name="fss_form_filter_mode">
            <option value="all" <?php selected($filter_mode, 'all'); ?>><?php _e('All forms (current behavior)', 'fss'); ?></option>
            <option value="include" <?php selected($filter_mode, 'include'); ?>><?php _e('Enable rotation only for selected forms', 'fss'); ?></option>
            <option value="exclude" <?php selected($filter_mode, 'exclude'); ?>><?php _e('Disable rotation for selected forms', 'fss'); ?></option>
        </select>
        <p class="description">
            <?php _e('Choose how to filter forms for sequential email rotation.', 'fss'); ?>
        </p>
        <?php
    }

    public function form_ids_callback() {
        $form_ids = get_option('fss_form_ids', array());

        // Récupérer tous les formulaires Formidable
        global $wpdb;
        $forms = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}frm_forms WHERE status='published' ORDER BY name");

        if (empty($forms)) {
            echo '<p class="description">' . __('No Formidable forms found.', 'fss') . '</p>';
            return;
        }

        echo '<div id="fss-form-ids">';
        foreach ($forms as $form) {
            $checked = in_array($form->id, $form_ids) ? 'checked' : '';
            echo '<label style="display: block; margin-bottom: 8px;">';
            echo '<input type="checkbox" name="fss_form_ids[]" value="' . esc_attr($form->id) . '" ' . $checked . ' />';
            echo ' ' . esc_html($form->name) . ' <span style="color: #666;">(ID: ' . $form->id . ')</span>';
            echo '</label>';
        }
        echo '</div>';

        echo '<p class="description">';
        _e('Select the forms to include or exclude from rotation, depending on the filter mode selected above.', 'fss');
        echo '</p>';
    }

    public function sanitize_form_ids($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_map('intval', array_filter($input));
    }

    /**
     * PHASE 3 ENHANCED: Callback pour le mode de filtrage par thématique
     * Permet d'activer/désactiver le routage par thématique
     */
    public function thematic_filter_mode_callback() {
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');
        ?>
        <select id="fss_thematic_filter_mode" name="fss_thematic_filter_mode">
            <option value="disabled" <?php selected($thematic_mode, 'disabled'); ?>><?php _e('Disabled (use main rotation list)', 'fss'); ?></option>
            <option value="enabled" <?php selected($thematic_mode, 'enabled'); ?>><?php _e('Enabled (route by thematic field)', 'fss'); ?></option>
        </select>
        <p class="description">
            <?php _e('When enabled, emails will be routed to different rotation lists based on the selected thematic field value. If no thematic-specific list is configured, the main rotation list will be used as fallback.', 'fss'); ?>
        </p>
        <?php
    }

    /**
     * PHASE 3 ENHANCED: Callback pour la sélection du champ thématique
     * Affiche un dropdown avec tous les champs Formidable compatibles
     */
    public function thematic_field_id_callback() {
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');
        $selected_field_id = get_option('fss_thematic_field_id', null);
        $disabled = ($thematic_mode === 'disabled') ? 'disabled' : '';

        global $wpdb;

        // Récupérer tous les champs compatibles (radio, select uniquement - choix simple)
        $fields = $wpdb->get_results(
            "SELECT f.id, f.name, f.form_id, f.type, fm.name as form_name
            FROM {$wpdb->prefix}frm_fields f
            LEFT JOIN {$wpdb->prefix}frm_forms fm ON f.form_id = fm.id
            WHERE f.type IN ('radio', 'select')
            AND fm.status = 'published'
            ORDER BY fm.name, f.name"
        );

        echo '<div style="' . ($thematic_mode === 'disabled' ? 'opacity: 0.5;' : '') . '">';

        // Message explicatif détaillé
        echo '<div class="notice notice-info inline" style="margin: 10px 0 15px 0; padding: 10px;">';
        echo '<p style="margin: 0 0 8px 0;"><strong>ℹ️ Qu\'est-ce qu\'un champ thématique ?</strong></p>';
        echo '<p style="margin: 0 0 8px 0;">Un champ thématique permet de router automatiquement les emails vers différentes listes selon la valeur sélectionnée par l\'utilisateur dans le formulaire.</p>';
        echo '<p style="margin: 0 0 8px 0;"><strong>Exemple :</strong> Si vous avez un champ "Type de demande" avec les valeurs "Santé", "Prévoyance" et "Retraite", chaque type sera envoyé à une liste d\'emails spécifique.</p>';
        echo '<p style="margin: 0;"><strong>📋 Note :</strong> Seuls les champs de type radio button ou liste déroulante (select) sont compatibles avec le routage thématique.</p>';
        echo '</div>';

        if (empty($fields)) {
            echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 10px;">';
            echo '<p style="margin: 0;"><strong>⚠️ Aucun champ compatible trouvé</strong></p>';
            echo '<p style="margin: 5px 0 0 0;">Seuls les champs de type radio button ou liste déroulante peuvent être utilisés pour le routage thématique. Assurez-vous d\'avoir créé au moins un champ de ce type dans vos formulaires Formidable.</p>';
            echo '</div>';
        } else {
            echo '<select id="fss_thematic_field_id" name="fss_thematic_field_id" ' . $disabled . '>';
            echo '<option value="">' . __('-- Sélectionnez un champ --', 'fss') . '</option>';

            foreach ($fields as $field) {
                $selected = ($selected_field_id == $field->id) ? 'selected' : '';
                $label = sprintf(
                    '%s (ID: %d, Form: %s, Type: %s)',
                    esc_html($field->name),
                    $field->id,
                    esc_html($field->form_name),
                    esc_html($field->type)
                );
                echo '<option value="' . esc_attr($field->id) . '" ' . $selected . '>' . $label . '</option>';
            }

            echo '</select>';

            // JavaScript pour auto-submit lors du changement de field
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                console.log('[FSS] Auto-refresh script loaded');

                var $fieldSelect = $('#fss_thematic_field_id');
                console.log('[FSS] Field select found:', $fieldSelect.length > 0);

                $fieldSelect.on('change', function() {
                    console.log('[FSS] Field changed to:', $(this).val());

                    if ($(this).val() !== '') {
                        // Afficher un message de chargement
                        var $description = $(this).nextAll('.description').first();
                        console.log('[FSS] Description element found:', $description.length > 0);

                        if ($description.length) {
                            $description.html('<span style="color: #2271b1;"><strong>⏳ Chargement des valeurs du champ... La page va se recharger automatiquement.</strong></span>');
                        }

                        // Auto-submit le formulaire en cliquant sur le bouton submit
                        console.log('[FSS] Will submit in 800ms...');
                        setTimeout(function() {
                            var $submitButton = $('input[type="submit"][name="submit"]');
                            console.log('[FSS] Submit button found:', $submitButton.length > 0);

                            if ($submitButton.length > 0) {
                                console.log('[FSS] Clicking submit button');
                                $submitButton.click();
                            } else {
                                console.log('[FSS] Fallback: direct form submit');
                                $('form').submit();
                            }
                        }, 800);
                    }
                });
            });
            </script>
            <?php

            echo '<p class="description" style="margin-top: 10px;">';
            echo '<strong>💡 Conseil :</strong> Après avoir sélectionné un champ, la page se rechargera pour afficher toutes les valeurs possibles dans la section "Configuration des emails par thématique" ci-dessous.';
            echo '</p>';

            // Message si aucun champ sélectionné
            if (!$selected_field_id) {
                echo '<p class="description" style="color: #d63638; margin-top: 8px;">';
                echo '⚠️ <strong>Laissez ce champ vide pour désactiver complètement le routage thématique</strong> et utiliser uniquement la liste principale de rotation.';
                echo '</p>';
            }

            // Afficher un avertissement si le champ a changé
            if ($selected_field_id && isset($_GET['settings-updated'])) {
                // Invalider le cache après la sauvegarde
                delete_transient('fss_field_values_' . $selected_field_id);
                echo '<div class="notice notice-success inline" style="margin: 10px 0;">';
                echo '<p style="margin: 0;">✅ <strong>Champ mis à jour avec succès.</strong> Les valeurs disponibles ont été rechargées pour le nouveau champ sélectionné.</p>';
                echo '</div>';
            }
        }

        echo '</div>';
    }

    /**
     * PHASE 3 ENHANCED: Callback pour les mappings d'emails par thématique
     * Affiche dynamiquement les valeurs détectées pour le champ sélectionné
     * Les valeurs sont groupées par leur clé normalisée
     */
    public function thematic_emails_callback() {
        $thematic_mode = get_option('fss_thematic_filter_mode', 'disabled');
        $selected_field_id = get_option('fss_thematic_field_id', null);
        $thematic_emails = get_option('fss_thematic_emails', array(
            'field_id' => null,
            'mappings' => array()
        ));

        $disabled = ($thematic_mode === 'disabled') ? 'disabled' : '';

        echo '<div style="' . ($thematic_mode === 'disabled' ? 'opacity: 0.5;' : '') . '">';

        // Message explicatif détaillé en haut
        echo '<div class="notice notice-info inline" style="margin: 10px 0 15px 0; padding: 10px;">';
        echo '<p style="margin: 0 0 8px 0;"><strong>📧 Configuration des emails par thématique</strong></p>';
        echo '<p style="margin: 0 0 8px 0;">Cette section vous permet d\'assigner des listes d\'emails spécifiques à chaque valeur du champ thématique. Les valeurs similaires sont automatiquement regroupées ensemble.</p>';
        echo '<p style="margin: 0 0 8px 0;"><strong>Exemple :</strong> Si vous sélectionnez "Prévoyance", tous les formulaires où l\'utilisateur a choisi "Prévoyance" seront envoyés aux emails configurés dans cette section.</p>';
        echo '<p style="margin: 0;"><strong>💡 Important :</strong> Si aucun email n\'est configuré pour une valeur, le système utilisera automatiquement la liste principale de rotation comme solution de secours (fallback).</p>';
        echo '</div>';

        if (!$selected_field_id) {
            echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 10px;">';
            echo '<p style="margin: 0;">⚠️ <strong>Veuillez d\'abord sélectionner un champ thématique dans la section ci-dessus.</strong></p>';
            echo '</div>';
            echo '</div>';
            return;
        }

        // Récupérer les valeurs uniques pour ce champ avec leur nombre d'occurrences
        $field_values = $this->get_field_values_with_counts($selected_field_id);

        if (empty($field_values)) {
            echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 10px;">';
            echo '<p style="margin: 0;"><strong>⚠️ Aucune valeur trouvée pour ce champ</strong></p>';
            echo '<p style="margin: 5px 0 0 0;">Assurez-vous qu\'il existe au moins une soumission de formulaire avec ce champ rempli. Les valeurs possibles apparaîtront ici dès qu\'elles seront utilisées dans les formulaires.</p>';
            echo '</div>';
            echo '</div>';
            return;
        }

        echo '<input type="hidden" name="fss_thematic_emails[field_id]" value="' . esc_attr($selected_field_id) . '" />';

        // Grouper les valeurs par leur clé normalisée
        $grouped_values = array();
        foreach ($field_values as $field_value) {
            $raw_value = $field_value['value'];
            $entry_count = intval($field_value['count']);

            $normalized_key = $this->normalize_key_for_display($raw_value);

            if (!isset($grouped_values[$normalized_key])) {
                $grouped_values[$normalized_key] = array(
                    'raw_values' => array(),
                    'total_entries' => 0,
                    'display_name' => ''
                );
            }

            $grouped_values[$normalized_key]['raw_values'][$raw_value] = $entry_count;
            $grouped_values[$normalized_key]['total_entries'] += $entry_count;

            // Set display name (prefer value without "Type : " prefix)
            if (empty($grouped_values[$normalized_key]['display_name']) ||
                !preg_match('/^Type\s*:/i', $raw_value)) {
                $grouped_values[$normalized_key]['display_name'] = $raw_value;
            }
        }

        echo '<div class="notice notice-success inline" style="margin: 10px 0 15px 0; padding: 10px;">';
        echo '<p style="margin: 0;">✅ <strong>';
        printf(__('%d valeur(s) unique(s) détectée(s) dans le champ sélectionné', 'fss'), count($grouped_values));
        echo '</strong></p>';
        echo '</div>';

        // Afficher un champ pour chaque valeur groupée
        foreach ($grouped_values as $normalized_key => $group_data) {
            // Récupérer les emails existants pour cette clé normalisée
            $existing_emails = isset($thematic_emails['mappings'][$normalized_key])
                ? $thematic_emails['mappings'][$normalized_key]
                : array();

            echo '<div class="fss-thematic-mapping" style="margin-bottom: 25px; padding: 15px; background: #f8f8f8; border-left: 4px solid #0073aa;">';

            echo '<h4 style="margin-top: 0;">';
            echo esc_html($group_data['display_name']);
            echo ' <span style="color: #666; font-weight: normal; font-size: 0.9em;">(' . $group_data['total_entries'] . ' ' . _n('entry', 'entries', $group_data['total_entries'], 'fss') . ')</span>';
            echo '</h4>';

            echo '<p class="description" style="margin-bottom: 10px;">';
            echo '<strong>' . __('Normalized key:', 'fss') . '</strong> <code>' . esc_html($normalized_key) . '</code>';

            // Show raw values breakdown (only if multiple)
            if (count($group_data['raw_values']) > 1) {
                echo '<br><em style="color: #666;">' . __('Combines:', 'fss') . ' ';
                $details = array();
                foreach ($group_data['raw_values'] as $raw => $count) {
                    $details[] = '"' . esc_html($raw) . '" (' . $count . ')';
                }
                echo implode(', ', $details);
                echo '</em>';
            }
            echo '</p>';

            echo '<div id="fss-thematic-emails-' . esc_attr($normalized_key) . '">';

            $index = 0;
            if (!empty($existing_emails)) {
                foreach ($existing_emails as $email) {
                    echo '<div class="fss-email-field">';
                    echo '<label>' . __('email', 'fss') . ' ' . (++$index) . '</label>';
                    echo '<input type="email" name="fss_thematic_emails[mappings][' . esc_attr($normalized_key) . '][]" value="' . esc_attr($email) . '" ' . $disabled . ' />';
                    echo '<span class="fss-delete-email" ' . ($disabled ? 'style="pointer-events: none;"' : '') . '>🗑</span>';
                    echo '</div>';
                }
            }

            echo '</div>';

            echo '<button type="button" class="button fss-add-thematic-email" data-key="' . esc_attr($normalized_key) . '" ' . $disabled . '>';
            _e('add another email', 'fss');
            echo '</button>';

            // Message d'avertissement si aucun email configuré mais des entrées existent
            if (empty($existing_emails) && $group_data['total_entries'] > 0) {
                echo '<div class="notice notice-warning inline" style="margin-top: 10px; padding: 8px;">';
                echo '<p style="margin: 0;">⚠️ <strong>Aucun email configuré pour cette valeur</strong></p>';
                echo '<p style="margin: 5px 0 0 0;">Il existe ' . $group_data['total_entries'] . ' soumission(s) avec cette valeur, mais aucun email n\'est configuré. Ces formulaires seront automatiquement envoyés à la liste principale de rotation (fallback).</p>';
                echo '</div>';
            }

            // Message informatif si des emails sont configurés
            if (!empty($existing_emails)) {
                echo '<p class="description" style="margin-top: 10px; color: #2271b1;">';
                echo '✅ <strong>' . count($existing_emails) . ' email(s) configuré(s)</strong> - Les formulaires avec cette valeur seront envoyés en rotation à ces adresses.';
                echo '</p>';
            }

            echo '</div>'; // fss-thematic-mapping
        }

        // JavaScript pour gérer l'ajout d'emails thématiques
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('[FSS] Thematic email buttons script loaded');

            // Gestion des boutons "Ajouter un autre email" pour chaque thématique
            $('.fss-add-thematic-email').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var key = $button.data('key');
                var $container = $('#fss-thematic-emails-' + key);

                console.log('[FSS] Add thematic email clicked for key:', key);

                // Compter le nombre d'emails existants
                var count = $container.find('.fss-email-field').length + 1;

                // Créer un nouveau champ email
                var $newField = $('<div class="fss-email-field">' +
                    '<label>Adresse email ' + count + '</label>' +
                    '<input type="email" name="fss_thematic_emails[mappings][' + key + '][]" value="" />' +
                    '<span class="fss-delete-email">🗑</span>' +
                    '</div>');

                // Ajouter le nouveau champ
                $container.append($newField);

                console.log('[FSS] New thematic email field added');
            });

            // Gestion de la suppression d'emails thématiques (délégation d'événement)
            $(document).on('click', '.fss-thematic-mapping .fss-delete-email', function(e) {
                e.preventDefault();
                var $field = $(this).closest('.fss-email-field');
                var $container = $field.closest('[id^="fss-thematic-emails-"]');

                console.log('[FSS] Delete thematic email clicked');

                $field.remove();

                // Renumbéroter les labels
                $container.find('.fss-email-field').each(function(index) {
                    $(this).find('label').text('<?php _e("email", "fss"); ?> ' + (index + 1));
                });

                console.log('[FSS] Thematic email field removed and labels updated');
            });
        });
        </script>
        <?php

        echo '</div>';
    }

    /**
     * Récupère les valeurs uniques d'un champ avec leur nombre d'occurrences
     * Utilise un cache WordPress transient pour améliorer les performances
     *
     * @param int $field_id ID du champ Formidable
     * @return array Tableau de ['value' => string, 'count' => int]
     */
    private function get_field_values_with_counts($field_id) {
        // Vérifier le cache
        $cache_key = 'fss_field_values_' . $field_id;
        $cached_values = get_transient($cache_key);

        if ($cached_values !== false) {
            return $cached_values;
        }

        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_value as value, COUNT(*) as count
                FROM {$wpdb->prefix}frm_item_metas
                WHERE field_id = %d
                AND meta_value != ''
                AND meta_value IS NOT NULL
                GROUP BY meta_value
                ORDER BY count DESC, meta_value ASC",
                $field_id
            ),
            ARRAY_A
        );

        // Mettre en cache pour 1 heure
        set_transient($cache_key, $results, HOUR_IN_SECONDS);

        return $results;
    }

    /**
     * Normalise une valeur de champ en clé utilisable
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
     * Normalise une valeur de champ pour l'affichage et le groupement
     * Enlève le préfixe "Type : " puis applique la normalisation standard
     * Cette méthode doit correspondre EXACTEMENT à la logique dans class-fss-email-handler.php
     *
     * @param string $value Valeur à normaliser
     * @return string Clé normalisée
     */
    private function normalize_key_for_display($value) {
        // Remove "Type : " prefix
        $normalized = preg_replace('/^Type\s*:\s*/i', '', $value);
        $normalized = trim($normalized);

        // Convert to lowercase
        $key = strtolower($normalized);
        // Remove accents
        $key = remove_accents($key);
        // Replace spaces and special chars with underscores
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        // Remove leading/trailing underscores
        $key = trim($key, '_');

        return $key;
    }

    public function email_subject_callback() {
        $subject = get_option('fss_email_subject', '');

        echo '<div id="fss-email-subject">';
        echo '<div class="fss-email-subject-field"><input type="text" id="fss_email_subject" name="fss_email_subject" value="' . esc_attr($subject) . '" style="width: 100%; max-width: 500px;"/></div>';

        echo '<div class="notice notice-info inline" style="margin: 10px 0; padding: 10px;">';
        echo '<p style="margin: 0 0 8px 0;"><strong>ℹ️ Sujet de TOUS les emails envoyés</strong></p>';
        echo '<p style="margin: 0 0 8px 0;">Ce sujet sera appliqué à tous les emails de rotation, qu\'ils soient envoyés via la liste principale ou via les listes thématiques.</p>';
        echo '<p style="margin: 0;"><strong>Exemple :</strong> "Nouveau formulaire de contact reçu" ou "Demande d\'information - Site web"</p>';
        echo '</div>';

        if (empty($subject)) {
            echo '<p class="description" style="color: #d63638; margin-top: 8px;">';
            echo '⚠️ <strong>Sujet vide :</strong> Un sujet par défaut sera utilisé si vous laissez ce champ vide.';
            echo '</p>';
        }

        echo '</div>';
    }

    public function email_cc_callback() {
        $cc_emails = get_option('fss_email_cc', array());

        echo '<div class="notice notice-info inline" style="margin: 0 0 15px 0; padding: 10px;">';
        echo '<p style="margin: 0 0 8px 0;"><strong>📧 Les emails en copie (CC) reçoivent TOUS les formulaires</strong></p>';
        echo '<p style="margin: 0 0 8px 0;">Les adresses en copie carbone recevront systématiquement une copie de chaque formulaire soumis, qu\'il soit routé via la liste principale ou via une liste thématique.</p>';
        echo '<p style="margin: 0;"><strong>Exemple d\'usage :</strong> Idéal pour l\'archivage centralisé, la supervision managériale, ou pour garder une trace de tous les formulaires reçus.</p>';
        echo '</div>';

        echo '<div id="fss-email-cc-fields">';
        if (!empty($cc_emails)) {
            foreach ($cc_emails as $email) {
                echo '<div class="fss-cc-email-field"><label>'.__('email cc', 'fss').'</label><input type="email" name="fss_email_cc[]" value="' . esc_attr($email) . '" /><span class="fss-delete-email">🗑</span></div>';
            }
        }
        echo '</div>';

        echo '<button type="button" id="fss-add-cc-email">'.__('add another cc email', 'fss').'</button>';

        if (empty($cc_emails)) {
            echo '<p class="description" style="margin-top: 10px; color: #666;">';
            echo 'ℹ️ Aucun email en copie configuré. Laissez cette section vide si vous n\'avez pas besoin de copies systématiques.';
            echo '</p>';
        } else {
            echo '<p class="description" style="margin-top: 10px; color: #2271b1;">';
            echo '✅ <strong>' . count($cc_emails) . ' email(s) en copie configuré(s)</strong> - Ces adresses recevront une copie de chaque formulaire.';
            echo '</p>';
        }
    }

    public function emails_field_callback() {
        $emails = get_option('fss_emails');

        echo '<div class="notice notice-info inline" style="margin: 0 0 15px 0; padding: 10px;">';
        echo '<p style="margin: 0 0 8px 0;"><strong>📧 Liste principale de rotation (Fallback)</strong></p>';
        echo '<p style="margin: 0 0 8px 0;">Cette liste contient les adresses emails qui recevront les formulaires en rotation séquentielle. Chaque nouveau formulaire est envoyé à l\'adresse suivante dans la liste.</p>';
        echo '<p style="margin: 0 0 8px 0;"><strong>Fonctionnement de la rotation :</strong> Email 1 → Email 2 → Email 3 → Email 1 → Email 2 → etc.</p>';
        echo '<p style="margin: 0;"><strong>💡 Utilisation comme fallback :</strong> Si le routage thématique est activé mais qu\'aucun email n\'est configuré pour une valeur spécifique, cette liste sera utilisée automatiquement. Elle reçoit aussi tous les formulaires sans champ thématique rempli.</p>';
        echo '</div>';

        echo '<div id="fss-email-fields">';
        $index = 0;
        if (!empty($emails)) {
            foreach ($emails as $email) {
                echo '<div class="fss-email-field"><label>'.__('email', 'fss').' ' . (++$index) . '</label><input type="email" name="fss_emails[]" value="' . esc_attr($email) . '" /><span class="fss-delete-email">🗑</span></div>';
            }
        }
        echo '</div>';

        echo '<button type="button" id="fss-add-email">'.__('add another email', 'fss').'</button>';

        if (empty($emails)) {
            echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 8px;">';
            echo '<p style="margin: 0;">⚠️ <strong>Aucun email configuré dans la liste principale</strong></p>';
            echo '<p style="margin: 5px 0 0 0;">Attention : Si le routage thématique n\'a pas d\'emails configurés pour certaines valeurs, ces formulaires ne pourront pas être envoyés (aucun fallback disponible).</p>';
            echo '</div>';
        } else {
            echo '<p class="description" style="margin-top: 10px; color: #2271b1;">';
            echo '✅ <strong>' . count($emails) . ' email(s) configuré(s)</strong> - Les formulaires seront distribués en rotation parmi ces ' . count($emails) . ' adresse(s).';
            echo '</p>';
        }
    }

    public function sanitize_emails($input) {
        $input = is_array($input) ? $input : [];
        $emails = array_filter($input, function($email) {
            return !empty($email);
        });

        return array_map('sanitize_email', $emails);
    }

    /**
     * PHASE 3 ENHANCED: Sanitize thematic emails structure
     * Valide et nettoie la structure des mappings d'emails thématiques
     *
     * @param array $input Données brutes du formulaire
     * @return array Données sanitizées
     */
    public function sanitize_thematic_emails($input) {
        if (!is_array($input)) {
            return array(
                'field_id' => null,
                'mappings' => array()
            );
        }

        $field_id = isset($input['field_id']) ? intval($input['field_id']) : null;
        $mappings = array();

        if (isset($input['mappings']) && is_array($input['mappings'])) {
            foreach ($input['mappings'] as $key => $emails) {
                $sanitized_key = sanitize_key($key);

                if (is_array($emails)) {
                    $sanitized_emails = array_filter($emails, function($email) {
                        return !empty($email);
                    });
                    $sanitized_emails = array_map('sanitize_email', $sanitized_emails);

                    if (!empty($sanitized_emails)) {
                        $mappings[$sanitized_key] = array_values($sanitized_emails);
                    }
                }
            }
        }

        return array(
            'field_id' => $field_id,
            'mappings' => $mappings
        );
    }

    public function admin_notices() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('success message', 'fss') . '</p></div>';
        }

        // Afficher un avertissement si la migration est nécessaire
        if (get_option('fss_needs_migration', false)) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>' . __('Migration required:', 'fss') . '</strong> ';
            echo __('Your thematic email configuration needs to be migrated to the new format. This will happen automatically on the next save.', 'fss');
            echo '</p>';
            echo '</div>';
        }
    }

    /**
     * PHASE 3 ENHANCED: Migration des anciennes options vers la nouvelle structure
     * Exécuté une seule fois pour migrer de Phase 3 v1 à Phase 3 v2
     */
    public function maybe_run_migration() {
        // Vérifier si la migration a déjà été effectuée
        if (get_option('fss_migration_completed', false)) {
            return;
        }

        // Vérifier si des anciennes options existent
        $old_prevoyance = get_option('fss_thematic_prevoyance_emails', false);
        $old_sante = get_option('fss_thematic_sante_emails', false);
        $old_retraite = get_option('fss_thematic_retraite_emails', false);

        // Si aucune ancienne option n'existe, marquer comme migré
        if ($old_prevoyance === false && $old_sante === false && $old_retraite === false) {
            update_option('fss_migration_completed', true);
            return;
        }

        // Effectuer la migration
        $new_structure = array(
            'field_id' => 8, // L'ancien système utilisait le Field ID 8
            'mappings' => array()
        );

        if ($old_prevoyance !== false && !empty($old_prevoyance)) {
            $new_structure['mappings']['prevoyance'] = $old_prevoyance;
        }

        if ($old_sante !== false && !empty($old_sante)) {
            $new_structure['mappings']['mutuelle_complementaire_sante'] = $old_sante;
        }

        if ($old_retraite !== false && !empty($old_retraite)) {
            $new_structure['mappings']['epargne_retraite'] = $old_retraite;
        }

        // Sauvegarder la nouvelle structure
        update_option('fss_thematic_emails', $new_structure);
        update_option('fss_thematic_field_id', 8);

        // Marquer la migration comme complétée
        update_option('fss_migration_completed', true);

        // Optionnel: Supprimer les anciennes options
        // delete_option('fss_thematic_prevoyance_emails');
        // delete_option('fss_thematic_sante_emails');
        // delete_option('fss_thematic_retraite_emails');

        // Ajouter une notice pour informer l'utilisateur
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>' . __('Migration completed:', 'fss') . '</strong> ';
            echo __('Your thematic email configuration has been successfully migrated to the new dynamic format.', 'fss');
            echo '</p>';
            echo '</div>';
        });
    }
}
