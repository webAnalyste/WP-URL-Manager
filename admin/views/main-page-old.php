<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wp-url-manager-wrap">
    <div class="wp-url-manager-header">
        <h1 class="wp-url-manager-title">
            <span class="dashicons dashicons-admin-links"></span>
            <?php _e('WP URL Manager', 'wp-url-manager'); ?>
        </h1>
        <p class="wp-url-manager-subtitle">
            <?php _e('Gérez vos structures d\'URL et redirections 301 par type de contenu', 'wp-url-manager'); ?>
        </p>
    </div>

    <div class="wp-url-manager-container">
        <div class="wp-url-manager-sidebar">
            <div class="wp-url-manager-card">
                <h3><?php _e('Aide rapide', 'wp-url-manager'); ?></h3>
                
                <div class="help-section">
                    <h4><?php _e('Placeholders standards', 'wp-url-manager'); ?></h4>
                    <ul class="placeholder-list">
                        <li><code>%postname%</code> - <?php _e('Slug du contenu', 'wp-url-manager'); ?></li>
                        <li><code>%post_id%</code> - <?php _e('ID du contenu', 'wp-url-manager'); ?></li>
                        <li><code>%year%</code> - <?php _e('Année (YYYY)', 'wp-url-manager'); ?></li>
                        <li><code>%monthnum%</code> - <?php _e('Mois (MM)', 'wp-url-manager'); ?></li>
                        <li><code>%day%</code> - <?php _e('Jour (DD)', 'wp-url-manager'); ?></li>
                        <li><code>%post_type%</code> - <?php _e('Type de contenu', 'wp-url-manager'); ?></li>
                        <li><code>%author%</code> - <?php _e('Slug auteur', 'wp-url-manager'); ?></li>
                    </ul>
                </div>

                <div class="help-section">
                    <h4><?php _e('Taxonomies', 'wp-url-manager'); ?></h4>
                    <p><code>{taxonomy:nom_taxonomie}</code></p>
                    <p class="help-text"><?php _e('Exemple : {taxonomy:category}', 'wp-url-manager'); ?></p>
                </div>

                <div class="help-section">
                    <h4><?php _e('Exemples de patterns', 'wp-url-manager'); ?></h4>
                    <ul class="example-list">
                        <li><code>/blog/%postname%/</code></li>
                        <li><code>/articles/%year%/%postname%/</code></li>
                        <li><code>/guide/{taxonomy:category}/%postname%/</code></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="wp-url-manager-main">
            <div class="wp-url-manager-actions">
                <button type="button" class="button button-primary button-large" id="add-new-rule">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Nouvelle règle', 'wp-url-manager'); ?>
                </button>
            </div>

            <div class="wp-url-manager-rules-list">
                <?php if (empty($rules)) : ?>
                    <div class="wp-url-manager-empty-state">
                        <span class="dashicons dashicons-admin-links"></span>
                        <h3><?php _e('Aucune règle configurée', 'wp-url-manager'); ?></h3>
                        <p><?php _e('Créez votre première règle pour commencer à gérer vos URLs', 'wp-url-manager'); ?></p>
                        <button type="button" class="button button-primary button-large" id="add-first-rule">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Créer ma première règle', 'wp-url-manager'); ?>
                        </button>
                    </div>
                <?php else : ?>
                    <?php foreach ($rules as $rule) : ?>
                        <div class="wp-url-manager-rule-card" data-rule-id="<?php echo esc_attr($rule['id']); ?>">
                            <div class="rule-card-header">
                                <div class="rule-card-title">
                                    <label class="toggle-switch">
                                        <input type="checkbox" class="rule-toggle" <?php checked($rule['active']); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <h3><?php echo esc_html($rule['label']); ?></h3>
                                    <span class="rule-badge rule-badge-<?php echo esc_attr($rule['post_type']); ?>">
                                        <?php echo esc_html($rule['post_type']); ?>
                                    </span>
                                    <?php if ($rule['redirect_301']) : ?>
                                        <span class="rule-badge rule-badge-redirect">301</span>
                                    <?php endif; ?>
                                </div>
                                <div class="rule-card-actions">
                                    <button type="button" class="button button-small edit-rule" title="<?php _e('Modifier', 'wp-url-manager'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button type="button" class="button button-small delete-rule" title="<?php _e('Supprimer', 'wp-url-manager'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="rule-card-body">
                                <?php if (!empty($rule['source_pattern'])) : ?>
                                    <div class="rule-pattern">
                                        <span class="pattern-label"><?php _e('Source', 'wp-url-manager'); ?></span>
                                        <code class="pattern-code"><?php echo esc_html($rule['source_pattern']); ?></code>
                                    </div>
                                    <div class="rule-arrow">→</div>
                                <?php endif; ?>
                                <div class="rule-pattern">
                                    <span class="pattern-label"><?php _e('Cible', 'wp-url-manager'); ?></span>
                                    <code class="pattern-code pattern-code-target"><?php echo esc_html($rule['target_pattern']); ?></code>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="rule-modal" class="wp-url-manager-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title"><?php _e('Nouvelle règle', 'wp-url-manager'); ?></h2>
            <button type="button" class="modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="modal-body">
            <form id="rule-form">
                <input type="hidden" id="rule-id" name="rule_id" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="rule-label">
                            <?php _e('Libellé de la règle', 'wp-url-manager'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="rule-label" name="label" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rule-post-type">
                            <?php _e('Type de contenu', 'wp-url-manager'); ?>
                            <span class="required">*</span>
                        </label>
                        <select id="rule-post-type" name="post_type" class="form-control" required>
                            <?php foreach ($post_types as $post_type) : ?>
                                <option value="<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rule-source-pattern">
                            <?php _e('Pattern source (ancienne URL)', 'wp-url-manager'); ?>
                        </label>
                        <input type="text" id="rule-source-pattern" name="source_pattern" class="form-control" placeholder="/%postname%/">
                        <p class="form-help"><?php _e('Optionnel. Laissez vide si pas de redirection.', 'wp-url-manager'); ?></p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rule-target-pattern">
                            <?php _e('Pattern cible (nouvelle URL)', 'wp-url-manager'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="rule-target-pattern" name="target_pattern" class="form-control" placeholder="/blog/%postname%/" required>
                        <div id="pattern-validation" class="validation-feedback"></div>
                        <div id="pattern-preview" class="pattern-preview"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group-checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="rule-redirect-301" name="redirect_301" value="1">
                            <span><?php _e('Activer la redirection 301', 'wp-url-manager'); ?></span>
                        </label>
                        <p class="form-help"><?php _e('Redirige automatiquement l\'ancienne URL vers la nouvelle.', 'wp-url-manager'); ?></p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group-checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="rule-active" name="active" value="1" checked>
                            <span><?php _e('Règle active', 'wp-url-manager'); ?></span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-large modal-cancel"><?php _e('Annuler', 'wp-url-manager'); ?></button>
            <button type="button" class="button button-primary button-large" id="save-rule">
                <span class="button-text"><?php _e('Enregistrer', 'wp-url-manager'); ?></span>
                <span class="button-spinner"></span>
            </button>
        </div>
    </div>
</div>

<div id="notification-container"></div>
