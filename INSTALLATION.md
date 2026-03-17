# Installation et Déploiement - WP URL Manager

## 📦 Fichier de distribution

Le fichier ZIP de distribution est disponible dans `/dist/wp-url-manager-1.0.0.zip` (28 Ko)

## 🚀 Installation sur WordPress

### Méthode 1 : Via l'admin WordPress (recommandée)

1. Téléchargez `dist/wp-url-manager-1.0.0.zip`
2. Connectez-vous à votre admin WordPress
3. Allez dans **Extensions > Ajouter**
4. Cliquez sur **Téléverser une extension**
5. Sélectionnez le fichier ZIP
6. Cliquez sur **Installer maintenant**
7. Activez le plugin

### Méthode 2 : Via FTP/SFTP

1. Décompressez `wp-url-manager-1.0.0.zip`
2. Uploadez le dossier `wp-url-manager` dans `/wp-content/plugins/`
3. Allez dans **Extensions** dans l'admin WordPress
4. Activez **WP URL Manager**

### Méthode 3 : Via SSH/WP-CLI

```bash
# Upload du ZIP sur le serveur
scp dist/wp-url-manager-1.0.0.zip user@server:/tmp/

# Connexion SSH
ssh user@server

# Installation via WP-CLI
wp plugin install /tmp/wp-url-manager-1.0.0.zip --activate
```

## 🔄 Système de mise à jour automatique

### Comment ça fonctionne

Le plugin vérifie automatiquement les nouvelles versions sur GitHub :

1. **Vérification** : Toutes les 12 heures via l'API GitHub
2. **Notification** : Une notification apparaît dans Extensions si une mise à jour est disponible
3. **Mise à jour** : Clic sur "Mettre à jour" pour installer automatiquement

### Configuration requise

- Connexion internet depuis le serveur WordPress
- Accès à `api.github.com` (port 443)
- Permissions `update_plugins` pour l'utilisateur

### Forcer la vérification

Pour forcer une vérification immédiate :

1. Désactiver le plugin
2. Réactiver le plugin
3. Aller dans Extensions

Ou via WP-CLI :
```bash
wp transient delete wp_url_manager_update_cache
```

## 📋 Prérequis serveur

### Minimum requis

- **WordPress** : 5.8 ou supérieur
- **PHP** : 7.4 ou supérieur
- **MySQL** : 5.6 ou supérieur (ou MariaDB équivalent)

### Recommandé

- **WordPress** : 6.0+
- **PHP** : 8.0+
- **MySQL** : 5.7+ ou MariaDB 10.2+
- **HTTPS** : Activé
- **mod_rewrite** : Activé (Apache) ou équivalent (Nginx)

### Extensions PHP requises

- `json` (généralement inclus)
- `curl` ou `allow_url_fopen` (pour les mises à jour)

## 🔧 Configuration post-installation

### 1. Vérifier les permaliens

Après activation, allez dans **Réglages > Permaliens** et cliquez sur **Enregistrer** pour régénérer les règles de réécriture.

### 2. Créer votre première règle

1. Allez dans **URL Manager**
2. Cliquez sur **Nouvelle règle**
3. Configurez votre règle
4. Enregistrez

### 3. Tester

1. Créez un contenu de test
2. Vérifiez que le permalink est correct
3. Testez l'accès à l'URL
4. Si redirection 301 activée, testez l'ancienne URL

## 🌐 Configuration serveur web

### Apache (.htaccess)

WordPress gère automatiquement le `.htaccess`. Assurez-vous que :

```apache
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
```

### Nginx

Ajoutez dans votre configuration :

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## 🔒 Permissions fichiers

```bash
# Dossiers
find wp-content/plugins/wp-url-manager -type d -exec chmod 755 {} \;

# Fichiers
find wp-content/plugins/wp-url-manager -type f -exec chmod 644 {} \;
```

## 🧪 Environnement de test

Avant de déployer en production, testez sur un environnement de staging :

1. Clone de la production
2. Installation du plugin
3. Tests fonctionnels complets
4. Vérification des performances
5. Validation SEO

## 📊 Monitoring post-installation

### Vérifications à effectuer

- [ ] Plugin activé sans erreur
- [ ] Menu "URL Manager" visible
- [ ] Aucune erreur dans les logs PHP
- [ ] Permaliens fonctionnels
- [ ] Redirections 301 opérationnelles
- [ ] Performance acceptable (< 100ms overhead)

### Logs à surveiller

```bash
# Logs WordPress
tail -f wp-content/debug.log

# Logs serveur (Apache)
tail -f /var/log/apache2/error.log

# Logs serveur (Nginx)
tail -f /var/log/nginx/error.log
```

## 🔄 Mise à jour manuelle

Si la mise à jour automatique échoue :

1. Désactiver le plugin
2. Supprimer le dossier `wp-content/plugins/wp-url-manager`
3. Installer la nouvelle version
4. Réactiver le plugin

**Note** : Les règles sont conservées dans la base de données.

## 🗑️ Désinstallation

### Désinstallation propre

1. Désactiver toutes les règles
2. Désactiver le plugin
3. Supprimer le plugin via l'admin WordPress

Cela supprimera automatiquement :
- Les options en base de données
- Les règles de réécriture

### Désinstallation manuelle

```bash
# Via WP-CLI
wp plugin deactivate wp-url-manager
wp plugin delete wp-url-manager

# Nettoyage base de données (optionnel)
wp option delete wp_url_manager_rules
wp option delete wp_url_manager_rules_version
```

## 🆘 Dépannage installation

### Le plugin ne s'active pas

- Vérifier la version PHP (minimum 7.4)
- Vérifier les logs d'erreur
- Vérifier les permissions fichiers

### Erreur 404 après activation

- Aller dans Réglages > Permaliens
- Cliquer sur Enregistrer
- Vider le cache si présent

### Mise à jour automatique ne fonctionne pas

- Vérifier la connexion à api.github.com
- Vérifier les permissions update_plugins
- Forcer la vérification (voir ci-dessus)

## 📞 Support

En cas de problème :

1. Consulter [TESTING.md](TESTING.md)
2. Consulter [README.md](README.md)
3. Ouvrir une issue sur [GitHub](https://github.com/webAnalyste/WP-URL-Manager/issues)
