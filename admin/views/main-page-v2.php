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

    <div class="wp-url-manager-container-v2">
        <!-- Sidebar avec aide (toujours visible) -->
        <div class="wp-url-manager-sidebar-v2">
            <div class="wp-url-manager-card">
                <h3><?php _e('Placeholders', 'wp-url-manager'); ?></h3>
                
                <div class="help-section">
                    <h4><?php _e('Standards', 'wp-url-manager'); ?></h4>
                    <ul class="placeholder-list">
                        <li><code>%postname%</code> - <?php _e('Slug', 'wp-url-manager'); ?></li>
                        <li><code>%post_id%</code> - <?php _e('ID', 'wp-url-manager'); ?></li>
                        <li><code>%year%</code> - <?php _e('Année', 'wp-url-manager'); ?></li>
                        <li><code>%monthnum%</code> - <?php _e('Mois', 'wp-url-manager'); ?></li>
                        <li><code>%day%</code> - <?php _e('Jour', 'wp-url-manager'); ?></li>
                        <li><code>%post_type%</code> - <?php _e('Type', 'wp-url-manager'); ?></li>
                        <li><code>%author%</code> - <?php _e('Auteur', 'wp-url-manager'); ?></li>
                    </ul>
                </div>

                <div class="help-section">
                    <h4><?php _e('Taxonomies', 'wp-url-manager'); ?></h4>
                    <p><code>{taxonomy:nom}</code></p>
                    <p class="help-text"><?php _e('Ex: {taxonomy:category}', 'wp-url-manager'); ?></p>
                </div>

                <div class="help-section">
                    <h4><?php _e('Exemples', 'wp-url-manager'); ?></h4>
                    <ul class="example-list">
                        <li><code>/blog/%postname%/</code></li>
                        <li><code>/articles/%year%/%postname%/</code></li>
                        <li><code>/guide/{taxonomy:category}/%postname%/</code></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Zone principale : Liste OU Formulaire -->
        <div class="wp-url-manager-main-v2">
            
            <!-- Vue Liste -->
            <div id="rules-list-view" class="rules-view active">
                <div class="wp-url-manager-actions">
                    <button type="button" class="button button-primary button-large" id="show-add-form">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Nouvelle règle', 'wp-url-manager'); ?>
                    </button>
                </div>

                <div class="wp-url-manager-rules-list">
                    <?php if (empty($rules)) : ?>
                        <div class="wp-url-manager-empty-state">
                            <span class="dashicons dashicons-admin-links"></span>
                            <h3><?php _e('Aucune règle configurée', 'wp-url-manager'); ?></h3>
                            <p><?php _e('Créez votre première règle pour commencer', 'wp-url-manager'); ?></p>
                            <button type="button" class="button button-primary button-large" id="show-add-form-empty">
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
                                        <button type="button" class="button button-small edit-rule-btn" 
                                                data-rule='<?php echo esc_attr(json_encode($rule)); ?>'
                                                title="<?php _e('Modifier', 'wp-url-manager'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="button button-small delete-rule" 
                                                title="<?php _e('Supprimer', 'wp-url-manager'); ?>">
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

            <!-- Vue Formulaire -->
            <div id="rule-form-view" class="rules-view">
                <div class="wp-url-manager-card">
                    <div class="form-header">
                        <h2 id="form-title"><?php _e('Nouvelle règle', 'wp-url-manager'); ?></h2>
                        <button type="button" class="button" id="cancel-form">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                            <?php _e('Retour à la liste', 'wp-url-manager'); ?>
                        </button>
                    </div>

                    <form id="rule-form" class="wp-url-manager-form">
                        <input type="hidden" id="rule-id" name="rule_id" value="">

                        <div class="form-row">
                            <label for="rule-label" class="form-label">
                                <?php _e('Libellé de la règle', 'wp-url-manager'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="rule-label" name="label" class="form-control" required 
                                   placeholder="<?php _e('Ex: Articles vers /blog/', 'wp-url-manager'); ?>">
                        </div>

                        <div class="form-row">
                            <label for="rule-post-type" class="form-label">
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

                        <div class="form-row">
                            <label for="rule-source-pattern" class="form-label">
                                <?php _e('Pattern source (ancienne URL)', 'wp-url-manager'); ?>
                            </label>
                            <input type="text" id="rule-source-pattern" name="source_pattern" class="form-control" 
                                   placeholder="<?php _e('Ex: /%postname%/', 'wp-url-manager'); ?>">
                            <p class="form-help"><?php _e('Optionnel. Laissez vide si pas de redirection.', 'wp-url-manager'); ?></p>
                            <div id="source-validation" class="validation-message"></div>
                        </div>

                        <div class="form-row">
                            <label for="rule-target-pattern" class="form-label">
                                <?php _e('Pattern cible (nouvelle URL)', 'wp-url-manager'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="rule-target-pattern" name="target_pattern" class="form-control" required 
                                   placeholder="<?php _e('Ex: /blog/%postname%/', 'wp-url-manager'); ?>">
                            <div id="target-validation" class="validation-message"></div>
                            <div id="target-preview" class="url-preview"></div>
                        </div>

                        <div class="form-row">
                            <label class="checkbox-label">
                                <input type="checkbox" id="rule-redirect-301" name="redirect_301">
                                <span><?php _e('Activer la redirection 301', 'wp-url-manager'); ?></span>
                            </label>
                            <p class="form-help"><?php _e('Redirige automatiquement l\'ancienne URL vers la nouvelle.', 'wp-url-manager'); ?></p>
                        </div>

                        <div class="form-row">
                            <label class="checkbox-label">
                                <input type="checkbox" id="rule-active" name="active" checked>
                                <span><?php _e('Règle active', 'wp-url-manager'); ?></span>
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="button" id="cancel-form-btn">
                                <?php _e('Annuler', 'wp-url-manager'); ?>
                            </button>
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-saved"></span>
                                <?php _e('Enregistrer', 'wp-url-manager'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
