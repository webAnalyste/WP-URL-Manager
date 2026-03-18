(function($) {
    'use strict';

    const WPURLManager = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Basculer vers le formulaire
            $('#show-add-form, #show-add-form-empty').on('click', () => this.showForm());
            
            // Retour à la liste
            $('#cancel-form, #cancel-form-btn').on('click', () => this.showList());
            
            // Éditer une règle
            $(document).on('click', '.edit-rule-btn', (e) => this.editRule(e));
            
            // Supprimer une règle
            $(document).on('click', '.delete-rule', (e) => this.deleteRule(e));
            
            // Toggle activation
            $(document).on('change', '.rule-toggle', (e) => this.toggleRule(e));
            
            // Sauvegarder le formulaire
            $('#rule-form').on('submit', (e) => {
                e.preventDefault();
                this.saveRule();
            });
            
            // Validation en temps réel
            $('#rule-target-pattern').on('input', () => this.validatePattern('#rule-target-pattern', '#target-validation', '#target-preview'));
            $('#rule-source-pattern').on('input', () => this.validatePattern('#rule-source-pattern', '#source-validation'));
            $('#rule-post-type').on('change', () => {
                this.validatePattern('#rule-target-pattern', '#target-validation', '#target-preview');
                this.validatePattern('#rule-source-pattern', '#source-validation');
            });
        },

        showForm: function(ruleData = null) {
            $('#rules-list-view').removeClass('active');
            $('#rule-form-view').addClass('active');
            
            // Reset form
            $('#rule-form')[0].reset();
            $('#target-validation, #source-validation').removeClass('success error').hide();
            $('#target-preview').removeClass('show').hide();
            
            if (ruleData) {
                $('#form-title').text('Modifier la règle');
                $('#rule-id').val(ruleData.id);
                $('#rule-label').val(ruleData.label);
                $('#rule-post-type').val(ruleData.post_type);
                $('#rule-source-pattern').val(ruleData.source_pattern);
                $('#rule-target-pattern').val(ruleData.target_pattern);
                $('#rule-redirect-301').prop('checked', ruleData.redirect_301);
                $('#rule-active').prop('checked', ruleData.active);
            } else {
                $('#form-title').text(wpUrlManager.i18n.newRule || 'Nouvelle règle');
                $('#rule-id').val('');
                $('#rule-active').prop('checked', true);
            }
            
            // Scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        },

        showList: function() {
            $('#rule-form-view').removeClass('active');
            $('#rules-list-view').addClass('active');
            
            // Scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        },

        editRule: function(e) {
            const $button = $(e.currentTarget);
            const ruleData = $button.data('rule');
            this.showForm(ruleData);
        },

        saveRule: function() {
            const $form = $('#rule-form');
            const $submitBtn = $form.find('button[type="submit"]');
            
            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }
            
            $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Enregistrement...');
            
            const formData = {
                action: 'wp_url_manager_save_rule',
                nonce: wpUrlManager.nonce,
                rule_id: $('#rule-id').val(),
                label: $('#rule-label').val(),
                post_type: $('#rule-post-type').val(),
                source_pattern: $('#rule-source-pattern').val(),
                target_pattern: $('#rule-target-pattern').val(),
                redirect_301: $('#rule-redirect-301').is(':checked') ? 'true' : 'false',
                active: $('#rule-active').is(':checked') ? 'true' : 'false'
            };
            
            $.post(wpUrlManager.ajaxUrl, formData)
                .done((response) => {
                    if (response.success) {
                        this.showNotification('Règle enregistrée avec succès', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        this.showNotification(response.data.message || 'Erreur lors de l\'enregistrement', 'error');
                        $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Enregistrer');
                    }
                })
                .fail(() => {
                    this.showNotification('Erreur de connexion', 'error');
                    $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Enregistrer');
                });
        },

        deleteRule: function(e) {
            const $card = $(e.currentTarget).closest('.wp-url-manager-rule-card');
            const ruleId = $card.data('rule-id');
            
            if (!confirm(wpUrlManager.i18n.confirmDelete || 'Êtes-vous sûr de vouloir supprimer cette règle ?')) {
                return;
            }
            
            $.post(wpUrlManager.ajaxUrl, {
                action: 'wp_url_manager_delete_rule',
                nonce: wpUrlManager.nonce,
                rule_id: ruleId
            })
            .done((response) => {
                if (response.success) {
                    $card.fadeOut(300, function() {
                        $(this).remove();
                        if ($('.wp-url-manager-rule-card').length === 0) {
                            location.reload();
                        }
                    });
                    this.showNotification('Règle supprimée', 'success');
                } else {
                    this.showNotification(response.data.message || 'Erreur', 'error');
                }
            })
            .fail(() => {
                this.showNotification('Erreur de connexion', 'error');
            });
        },

        toggleRule: function(e) {
            const $toggle = $(e.currentTarget);
            const $card = $toggle.closest('.wp-url-manager-rule-card');
            const ruleId = $card.data('rule-id');
            const active = $toggle.is(':checked');
            
            $.post(wpUrlManager.ajaxUrl, {
                action: 'wp_url_manager_toggle_rule',
                nonce: wpUrlManager.nonce,
                rule_id: ruleId,
                active: active ? 'true' : 'false'
            })
            .done((response) => {
                if (response.success) {
                    this.showNotification('Statut mis à jour', 'success');
                } else {
                    $toggle.prop('checked', !active);
                    this.showNotification(response.data.message || 'Erreur', 'error');
                }
            })
            .fail(() => {
                $toggle.prop('checked', !active);
                this.showNotification('Erreur de connexion', 'error');
            });
        },

        validatePattern: function(patternSelector, validationSelector, previewSelector = null) {
            const pattern = $(patternSelector).val();
            const postType = $('#rule-post-type').val();
            
            if (!pattern) {
                $(validationSelector).removeClass('success error').hide();
                if (previewSelector) $(previewSelector).removeClass('show').hide();
                return;
            }
            
            $.post(wpUrlManager.ajaxUrl, {
                action: 'wp_url_manager_validate_pattern',
                nonce: wpUrlManager.nonce,
                pattern: pattern,
                post_type: postType
            })
            .done((response) => {
                if (response.success && response.data.valid) {
                    $(validationSelector).removeClass('error').addClass('success').html('✓ Pattern valide').show();
                    
                    if (previewSelector) {
                        this.previewUrl(pattern, postType, previewSelector);
                    }
                } else {
                    const errors = response.data.errors || ['Pattern invalide'];
                    $(validationSelector).removeClass('success').addClass('error').html('✗ ' + errors.join(', ')).show();
                    if (previewSelector) $(previewSelector).removeClass('show').hide();
                }
            });
        },

        previewUrl: function(pattern, postType, previewSelector) {
            $.post(wpUrlManager.ajaxUrl, {
                action: 'wp_url_manager_preview_url',
                nonce: wpUrlManager.nonce,
                pattern: pattern,
                post_type: postType
            })
            .done((response) => {
                if (response.success && response.data.preview) {
                    $(previewSelector).html('<strong>Aperçu :</strong> ' + response.data.preview).addClass('show').show();
                }
            });
        },

        showNotification: function(message, type = 'info') {
            const $notification = $('<div class="wp-url-manager-notification ' + type + '">' + message + '</div>');
            $('body').append($notification);
            
            setTimeout(() => {
                $notification.addClass('show');
            }, 10);
            
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => $notification.remove(), 300);
            }, 3000);
        }
    };

    $(document).ready(() => {
        WPURLManager.init();
    });

})(jQuery);
