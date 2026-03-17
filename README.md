# WP URL Manager

Plugin WordPress de gestion des structures d'URL et redirections 301 par type de contenu.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8+-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-red.svg)

## 📋 Description

**WP URL Manager** est un plugin WordPress léger et autonome qui permet de définir des structures d'URL personnalisées par type de contenu, de générer automatiquement les permaliens correspondants et de gérer les redirections 301 depuis les anciennes URLs.

### Pourquoi ce plugin ?

- ✅ **Contrôle total** sur vos structures d'URL
- ✅ **Redirections 301 intelligentes** sans casser l'existant
- ✅ **Placeholders dynamiques** pour des URLs flexibles
- ✅ **Interface moderne** et intuitive
- ✅ **Aucune dépendance** tierce
- ✅ **Léger et performant**

## 🚀 Fonctionnalités

### Gestion des patterns d'URL

- Définir une structure d'URL cible par type de contenu
- Support des patterns source (anciennes URLs) et cible (nouvelles URLs)
- Activation/désactivation des règles en un clic
- Validation en temps réel des patterns

### Placeholders supportés

#### Standards
- `%postname%` - Slug du contenu
- `%post_id%` - ID du contenu
- `%year%` - Année de publication (YYYY)
- `%monthnum%` - Mois de publication (MM)
- `%day%` - Jour de publication (DD)
- `%post_type%` - Type de contenu
- `%author%` - Slug de l'auteur
- `%parent_postname%` - Slug du parent (contenus hiérarchiques)

#### Taxonomies
- `{taxonomy:nom_taxonomie}` - Premier terme de la taxonomie spécifiée
- Exemple : `{taxonomy:category}`, `{taxonomy:post_tag}`

### Redirections 301

- Redirection automatique des anciennes URLs vers les nouvelles
- Détection intelligente pour éviter les boucles
- Redirection uniquement des contenus concernés
- Pas de redirection globale aveugle

### Interface d'administration

- Design moderne et épuré
- Validation en temps réel des patterns
- Aperçu instantané des URLs générées
- Aide contextuelle avec exemples
- Notifications visuelles claires

## 📦 Installation

### Méthode 1 : Installation manuelle

1. Téléchargez le plugin
2. Décompressez l'archive dans `/wp-content/plugins/`
3. Activez le plugin depuis l'administration WordPress

### Méthode 2 : Via Git

```bash
cd wp-content/plugins/
git clone https://github.com/webAnalyste/WP-URL-Manager.git wp-url-manager
```

Puis activez le plugin depuis l'administration WordPress.

## 🎯 Utilisation

### Créer une règle

1. Accédez à **URL Manager** dans le menu admin
2. Cliquez sur **Nouvelle règle**
3. Remplissez le formulaire :
   - **Libellé** : nom de la règle (ex: "Articles vers blog")
   - **Type de contenu** : sélectionnez le post type concerné
   - **Pattern source** : ancienne structure (optionnel)
   - **Pattern cible** : nouvelle structure (requis)
   - **Redirection 301** : activez si besoin
4. Enregistrez

### Exemples de patterns

#### Cas 1 : Articles à la racine vers /blog/

```
Source : /%postname%/
Cible  : /blog/%postname%/
```

**Résultat :**
- Ancien : `https://site.com/mon-article/`
- Nouveau : `https://site.com/blog/mon-article/`

#### Cas 2 : Articles avec structure datée

```
Source : /%postname%/
Cible  : /articles/%year%/%postname%/
```

**Résultat :**
- Ancien : `https://site.com/mon-article/`
- Nouveau : `https://site.com/articles/2026/mon-article/`

#### Cas 3 : Guides avec taxonomie

```
Source : /%postname%/
Cible  : /guide/%year%/{taxonomy:category}/%postname%/
```

**Résultat :**
- Ancien : `https://site.com/mon-guide/`
- Nouveau : `https://site.com/guide/2026/tutoriels/mon-guide/`

#### Cas 4 : Custom Post Type

```
Post Type : cas_client
Source    : /case-studies/%postname%/
Cible     : /cas-clients/%postname%/
```

**Résultat :**
- Ancien : `https://site.com/case-studies/client-xyz/`
- Nouveau : `https://site.com/cas-clients/client-xyz/`

## 🔧 Configuration

### Règles de validation

Le plugin valide automatiquement vos patterns :

- ✅ Doit commencer et finir par `/`
- ✅ Doit contenir au moins un identifiant de contenu (`%postname%` ou `%post_id%`)
- ✅ Les taxonomies doivent exister et être associées au post type
- ✅ Pas de caractères invalides

### Fallback taxonomie

Si un contenu n'a pas de terme pour une taxonomie requise, le plugin utilise :
- Par défaut : `uncategorized`
- Personnalisable via le filtre `wp_url_manager_taxonomy_fallback`

```php
add_filter('wp_url_manager_taxonomy_fallback', function($fallback, $taxonomy, $post_id) {
    return 'non-classe';
}, 10, 3);
```

## 🛡️ Sécurité

Le plugin respecte les standards WordPress :

- ✅ Vérification des capacités utilisateur (`manage_options`)
- ✅ Nonces pour toutes les actions AJAX
- ✅ Sanitisation de toutes les entrées
- ✅ Échappement de toutes les sorties
- ✅ Pas d'exécution directe des fichiers
- ✅ Validation stricte des patterns

## ⚡ Performance

- Pas de requêtes inutiles
- Flush rewrite uniquement quand nécessaire
- Logique de redirection optimisée
- Aucun impact sur les contenus non concernés

## 🔄 Hooks & Filtres

### Actions

```php
// Après mise à jour des règles
do_action('wp_url_manager_rules_updated', $rules);
```

### Filtres

```php
// Personnaliser le fallback taxonomie
apply_filters('wp_url_manager_taxonomy_fallback', $fallback, $taxonomy, $post_id);
```

## 📋 Prérequis

- WordPress 5.8 ou supérieur
- PHP 7.4 ou supérieur
- Capacité `manage_options` pour l'administration

## 🐛 Dépannage

### Les nouvelles URLs ne fonctionnent pas (404)

1. Allez dans **Réglages > Permaliens**
2. Cliquez sur **Enregistrer** (sans rien changer)
3. Testez à nouveau

### Les redirections ne fonctionnent pas

1. Vérifiez que la règle est **active**
2. Vérifiez que **Redirection 301** est cochée
3. Vérifiez que le **pattern source** correspond à l'ancienne URL
4. Testez en navigation privée (cache navigateur)

### Pattern invalide

- Vérifiez que le pattern commence et finit par `/`
- Vérifiez qu'il contient `%postname%` ou `%post_id%`
- Vérifiez que les taxonomies existent

## 📝 Changelog

### Version 1.0.0 (2026-03-17)

- ✨ Version initiale
- ✅ Gestion des règles par post type
- ✅ Support des placeholders standards et taxonomies
- ✅ Génération automatique des permaliens
- ✅ Rewrite rules dynamiques
- ✅ Redirections 301 intelligentes
- ✅ Interface admin moderne
- ✅ Validation en temps réel
- ✅ Aperçu des URLs

## 🤝 Contribution

Les contributions sont les bienvenues !

1. Fork le projet
2. Créez une branche (`git checkout -b feature/amelioration`)
3. Committez vos changements (`git commit -m 'Ajout fonctionnalité'`)
4. Pushez (`git push origin feature/amelioration`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce plugin est sous licence GPL v2 ou ultérieure.

## 👤 Auteur

**webAnalyste**

- GitHub: [@webAnalyste](https://github.com/webAnalyste)
- Plugin: [WP URL Manager](https://github.com/webAnalyste/WP-URL-Manager)

## 🙏 Support

Si vous rencontrez un problème ou avez une question :

1. Consultez la [documentation](#-utilisation)
2. Vérifiez les [issues existantes](https://github.com/webAnalyste/WP-URL-Manager/issues)
3. Ouvrez une [nouvelle issue](https://github.com/webAnalyste/WP-URL-Manager/issues/new)

---

**⭐ Si ce plugin vous est utile, n'hésitez pas à lui donner une étoile sur GitHub !**
