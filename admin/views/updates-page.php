<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wp-url-manager-wrap">
    <div class="wp-url-manager-header">
        <h1 class="wp-url-manager-title">
            <span class="dashicons dashicons-update"></span>
            <?php _e('Mises à jour - WP URL Manager', 'wp-url-manager'); ?>
        </h1>
        <p class="wp-url-manager-subtitle">
            <?php _e('Vérification automatique des nouvelles versions sur GitHub', 'wp-url-manager'); ?>
        </p>
    </div>

    <div class="wp-url-manager-container" style="grid-template-columns: 1fr;">
        <div class="wp-url-manager-main">
            
            <?php if (isset($_GET['check-now'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php _e('Vérification effectuée !', 'wp-url-manager'); ?></strong></p>
                </div>
            <?php endif; ?>

            <div class="wp-url-manager-card" style="margin-bottom: 20px;">
                <h2><?php _e('Version actuelle', 'wp-url-manager'); ?></h2>
                <p style="font-size: 24px; font-weight: 600; color: #2271b1; margin: 10px 0;">
                    v<?php echo esc_html($current_version); ?>
                </p>
                <p style="color: #50575e;">
                    <?php printf(__('Dernière vérification : %s', 'wp-url-manager'), esc_html($last_check)); ?>
                </p>
            </div>

            <?php if ($update_available && $remote_version) : ?>
                <div class="wp-url-manager-card" style="border-left: 4px solid #00a32a; background: #ecfdf5;">
                    <h2 style="color: #00a32a;">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Mise à jour disponible !', 'wp-url-manager'); ?>
                    </h2>
                    <p style="font-size: 18px; font-weight: 600; margin: 10px 0;">
                        <?php printf(__('Version %s disponible', 'wp-url-manager'), esc_html($remote_version)); ?>
                    </p>
                    <p style="margin: 15px 0;">
                        <?php _e('Une nouvelle version du plugin est disponible. Vous pouvez la mettre à jour depuis la page Extensions.', 'wp-url-manager'); ?>
                    </p>
                    <a href="<?php echo admin_url('plugins.php'); ?>" class="button button-primary button-large">
                        <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                        <?php _e('Aller aux Extensions', 'wp-url-manager'); ?>
                    </a>
                </div>
            <?php elseif ($remote_version) : ?>
                <div class="wp-url-manager-card" style="border-left: 4px solid #2271b1; background: #f0f6fc;">
                    <h2 style="color: #2271b1;">
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Plugin à jour', 'wp-url-manager'); ?>
                    </h2>
                    <p style="font-size: 16px; margin: 10px 0;">
                        <?php _e('Vous utilisez la dernière version du plugin.', 'wp-url-manager'); ?>
                    </p>
                    <p style="color: #50575e;">
                        <?php printf(__('Dernière version disponible : v%s', 'wp-url-manager'), esc_html($remote_version)); ?>
                    </p>
                </div>
            <?php else : ?>
                <div class="wp-url-manager-card" style="border-left: 4px solid #dba617; background: #fffbf0;">
                    <h2 style="color: #dba617;">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('Impossible de vérifier', 'wp-url-manager'); ?>
                    </h2>
                    <p><?php _e('Impossible de contacter GitHub pour vérifier les mises à jour.', 'wp-url-manager'); ?></p>
                    <p style="color: #50575e; font-size: 13px;">
                        <?php _e('Vérifiez votre connexion internet ou réessayez plus tard.', 'wp-url-manager'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="wp-url-manager-card">
                <h2><?php _e('Vérification manuelle', 'wp-url-manager'); ?></h2>
                <p><?php _e('Le plugin vérifie automatiquement les mises à jour toutes les heures. Vous pouvez forcer une vérification immédiate.', 'wp-url-manager'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=wp-url-manager-updates&check-now=1'); ?>" class="button button-secondary button-large">
                    <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                    <?php _e('Vérifier maintenant', 'wp-url-manager'); ?>
                </a>
            </div>

            <div class="wp-url-manager-card">
                <h3><?php _e('Comment fonctionne la mise à jour automatique ?', 'wp-url-manager'); ?></h3>
                <ol style="line-height: 1.8;">
                    <li><?php _e('Le plugin vérifie GitHub toutes les heures', 'wp-url-manager'); ?></li>
                    <li><?php _e('Si une nouvelle version est disponible, une notification apparaît dans Extensions', 'wp-url-manager'); ?></li>
                    <li><?php _e('Cliquez sur "Mettre à jour" pour installer automatiquement', 'wp-url-manager'); ?></li>
                    <li><?php _e('Le plugin se met à jour sans perdre vos règles', 'wp-url-manager'); ?></li>
                </ol>
                
                <h3 style="margin-top: 20px;"><?php _e('Informations', 'wp-url-manager'); ?></h3>
                <ul style="line-height: 1.8;">
                    <li><strong><?php _e('Dépôt GitHub :', 'wp-url-manager'); ?></strong> 
                        <a href="https://github.com/webAnalyste/WP-URL-Manager" target="_blank">
                            webAnalyste/WP-URL-Manager
                        </a>
                    </li>
                    <li><strong><?php _e('Fréquence de vérification :', 'wp-url-manager'); ?></strong> 
                        <?php _e('Toutes les heures (quasi temps réel)', 'wp-url-manager'); ?>
                    </li>
                    <li><strong><?php _e('Type de mise à jour :', 'wp-url-manager'); ?></strong> 
                        <?php _e('Automatique via l\'admin WordPress', 'wp-url-manager'); ?>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
