(function($) {
    'use strict';

    const WPURLManager = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('#add-new-rule, #add-first-rule').on('click', () => this.openModal());
            $('.modal-close, .modal-cancel, .modal-overlay').on('click', () => this.closeModal());
            $('#save-rule').on('click', () => this.saveRule());
            
            $(document).on('click', '.edit-rule', (e) => this.editRule(e));
            $(document).on('click', '.delete-rule', (e) => this.deleteRule(e));
            $(document).on('change', '.rule-toggle', (e) => this.toggleRule(e));
            
            $('#rule-target-pattern').on('input', () => this.validatePattern());
            $('#rule-post-type').on('change', () => this.validatePattern());
            
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && $('#rule-modal').is(':visible')) {
                    this.closeModal();
                }
            });
        },

        openModal: function(ruleData = null) {
            const $modal = $('#rule-modal');
            const $form = $('#rule-form');
            
            $form[0].reset();
            $('#pattern-validation').removeClass('valid invalid').hide();
            $('#pattern-preview').removeClass('show').hide();
            
            if (ruleData) {
                $('#modal-title').text('Modifier la règle');
                $('#rule-id').val(ruleData.id);
                $('#rule-label').val(ruleData.label);
                $('#rule-post-type').val(ruleData.post_type);
                $('#rule-source-pattern').val(ruleData.source_pattern);
                $('#rule-target-pattern').val(ruleData.target_pattern);
                $('#rule-redirect-301').prop('checked', ruleData.redirect_301);
                $('#rule-active').prop('checked', ruleData.active);
            } else {
                $('#modal-title').text(wpUrlManager.i18n.newRule || 'Nouvelle règle');
                $('#rule-id').val('');
                $('#rule-active').prop('checked', true);
            }
            
            $modal.fadeIn(200);
            $('body').css('overflow', 'hidden');
        },

        closeModal: function() {
            $('#rule-modal').fadeOut(200);
            $('body').css('overflow', '');
        },

        saveRule: function() {
            const $button = $('#save-rule');
            const $form = $('#rule-form');
            
            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }
            
            $button.addClass('loading').prop('disabled', true);
            
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
            
            $.ajax({
                url: wpUrlManager.ajaxUrl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message, 'success');
                        this.closeModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        this.showNotification(response.data.message, 'error');
                        if (response.data.errors) {
                            this.displayErrors(response.data.errors);
                        }
                    }
                },
                error: () => {
                    this.showNotification(wpUrlManager.i18n.error, 'error');
                },
                complete: () => {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        editRule: function(e) {
            const $card = $(e.currentTarget).closest('.wp-url-manager-rule-card');
            const ruleId = $card.data('rule-id');
            
            $.ajax({
                url: wpUrlManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_url_manager_get_rule',
                    nonce: wpUrlManager.nonce,
                    rule_id: ruleId
                },
                success: (response) => {
                    if (response.success) {
                        this.openModal(response.data.rule);
                    }
                }
            });
            
            const ruleData = {
                id: ruleId,
                label: $card.find('h3').text().trim(),
                post_type: $card.find('.rule-badge').first().text().trim(),
                source_pattern: $card.find('.pattern-code').first().text().trim() || '',
                target_pattern: $card.find('.pattern-code-target').text().trim(),
                redirect_301: $card.find('.rule-badge-redirect').length > 0,
                active: $card.find('.rule-toggle').is(':checked')
            };
            
            this.openModal(ruleData);
        },

        deleteRule: function(e) {
            if (!confirm(wpUrlManager.i18n.confirmDelete)) {
                return;
            }
            
            const $card = $(e.currentTarget).closest('.wp-url-manager-rule-card');
            const ruleId = $card.data('rule-id');
            
            $card.css('opacity', '0.5');
            
            $.ajax({
                url: wpUrlManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_url_manager_delete_rule',
                    nonce: wpUrlManager.nonce,
                    rule_id: ruleId
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message, 'success');
                        $card.slideUp(300, function() {
                            $(this).remove();
                            if ($('.wp-url-manager-rule-card').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        this.showNotification(response.data.message, 'error');
                        $card.css('opacity', '1');
                    }
                },
                error: () => {
                    this.showNotification(wpUrlManager.i18n.error, 'error');
                    $card.css('opacity', '1');
                }
            });
        },

        toggleRule: function(e) {
            const $toggle = $(e.currentTarget);
            const $card = $toggle.closest('.wp-url-manager-rule-card');
            const ruleId = $card.data('rule-id');
            const active = $toggle.is(':checked');
            
            $.ajax({
                url: wpUrlManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_url_manager_toggle_rule',
                    nonce: wpUrlManager.nonce,
                    rule_id: ruleId,
                    active: active ? 'true' : 'false'
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message, 'success');
                    } else {
                        this.showNotification(response.data.message, 'error');
                        $toggle.prop('checked', !active);
                    }
                },
                error: () => {
                    this.showNotification(wpUrlManager.i18n.error, 'error');
                    $toggle.prop('checked', !active);
                }
            });
        },

        validatePattern: function() {
            const pattern = $('#rule-target-pattern').val();
            const postType = $('#rule-post-type').val();
            const $feedback = $('#pattern-validation');
            const $preview = $('#pattern-preview');
            
            if (!pattern) {
                $feedback.removeClass('valid invalid').hide();
                $preview.removeClass('show').hide();
                return;
            }
            
            $.ajax({
                url: wpUrlManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_url_manager_validate_pattern',
                    nonce: wpUrlManager.nonce,
                    pattern: pattern,
                    post_type: postType
                },
                success: (response) => {
                    if (response.success) {
                        const validation = response.data;
                        
                        if (validation.valid) {
                            $feedback.removeClass('invalid').addClass('valid')
                                .html('<strong>✓ ' + wpUrlManager.i18n.valid + '</strong>');
                            this.previewUrl(pattern, postType);
                        } else {
                            $feedback.removeClass('valid').addClass('invalid')
                                .html('<strong>✗ ' + wpUrlManager.i18n.invalid + '</strong><ul>' + 
                                    validation.errors.map(err => '<li>' + err + '</li>').join('') + 
                                    '</ul>');
                            $preview.removeClass('show').hide();
                        }
                    }
                }
            });
        },

        previewUrl: function(pattern, postType) {
            $.ajax({
                url: wpUrlManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_url_manager_preview_url',
                    nonce: wpUrlManager.nonce,
                    pattern: pattern,
                    post_type: postType
                },
                success: (response) => {
                    if (response.success && response.data.preview) {
                        $('#pattern-preview').addClass('show').text(response.data.preview);
                    }
                }
            });
        },

        displayErrors: function(errors) {
            const $feedback = $('#pattern-validation');
            $feedback.removeClass('valid').addClass('invalid')
                .html('<strong>✗ Erreurs de validation</strong><ul>' + 
                    errors.map(err => '<li>' + err + '</li>').join('') + 
                    '</ul>');
        },

        showNotification: function(message, type = 'success') {
            const icon = type === 'success' ? 'yes-alt' : 'warning';
            const $notification = $('<div class="notification ' + type + '">' +
                '<span class="dashicons dashicons-' + icon + ' notification-icon"></span>' +
                '<span class="notification-message">' + message + '</span>' +
                '</div>');
            
            $('#notification-container').append($notification);
            
            setTimeout(() => {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        }
    };

    $(document).ready(function() {
        WPURLManager.init();
    });

})(jQuery);
