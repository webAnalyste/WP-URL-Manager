# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [1.0.0] - 2026-03-17

### Ajouté

#### Fonctionnalités principales
- Gestion des règles d'URL par type de contenu
- Support des placeholders standards (`%postname%`, `%year%`, `%post_id%`, etc.)
- Support des placeholders de taxonomie (`{taxonomy:nom}`)
- Génération automatique des permaliens
- Système de rewrite rules dynamiques
- Redirections 301 intelligentes
- Validation en temps réel des patterns
- Aperçu instantané des URLs générées

#### Interface d'administration
- Design moderne et épuré avec gradient
- Interface responsive et accessible
- Modal d'édition avec animations fluides
- Toggle switch pour activation/désactivation rapide
- Notifications toast élégantes
- Sidebar d'aide contextuelle
- Validation inline avec feedback visuel
- Badges de statut colorés
- Actions rapides (éditer, supprimer)

#### Sécurité
- Vérification des capacités utilisateur (`manage_options`)
- Nonces pour toutes les actions AJAX
- Sanitisation complète des entrées
- Échappement des sorties
- Validation stricte des patterns
- Protection contre l'exécution directe

#### Performance
- Flush rewrite conditionnel (pas à chaque requête)
- Logique de redirection optimisée
- Aucune requête inutile
- Code léger et performant

#### Documentation
- README complet avec exemples
- Guide de tests détaillé
- Aide contextuelle dans l'admin
- Commentaires dans le code
- Fichier de traduction (.pot)

### Technique

#### Architecture
- Structure modulaire avec classes séparées
- Pattern singleton pour la classe principale
- Séparation admin/public
- Hooks WordPress standards
- Code PSR-compatible

#### Classes principales
- `WP_URL_Manager` : Classe principale
- `WP_URL_Manager_Rules_Manager` : Gestion des règles
- `WP_URL_Manager_Permalink_Manager` : Génération permaliens
- `WP_URL_Manager_Rewrite_Manager` : Rewrite rules
- `WP_URL_Manager_Redirect_Manager` : Redirections 301
- `WP_URL_Manager_Placeholder_Resolver` : Résolution placeholders
- `WP_URL_Manager_Pattern_Validator` : Validation patterns
- `WP_URL_Manager_Admin_Interface` : Interface admin

#### Hooks implémentés
- `post_link` : Modification permaliens posts
- `post_type_link` : Modification permaliens CPT
- `init` : Ajout rewrite rules
- `template_redirect` : Gestion redirections
- `admin_menu` : Ajout menu admin
- `admin_enqueue_scripts` : Chargement assets

#### AJAX
- `wp_url_manager_save_rule` : Sauvegarde règle
- `wp_url_manager_delete_rule` : Suppression règle
- `wp_url_manager_toggle_rule` : Toggle activation
- `wp_url_manager_validate_pattern` : Validation pattern
- `wp_url_manager_preview_url` : Aperçu URL

### Prérequis
- WordPress 5.8+
- PHP 7.4+
- Capacité `manage_options`

### Fichiers
- `wp-url-manager.php` : Fichier principal
- `includes/` : Classes core
- `admin/` : Interface administration
- `languages/` : Fichiers de traduction
- `uninstall.php` : Script désinstallation
- `README.md` : Documentation
- `TESTING.md` : Guide de tests
- `CHANGELOG.md` : Ce fichier

---

## [1.0.2] - 2026-03-17

### ⚡ Améliorations

- **Mise à jour quasi temps réel** : Vérification toutes les heures au lieu de 12h
- **Page de vérification manuelle** : Nouvelle page "Mises à jour" dans le menu admin
- **Vérification forcée** : Bouton "Vérifier maintenant" pour forcer une vérification immédiate
- **Meilleure UX** : Affichage clair de la version actuelle et disponible
- **Auto-refresh** : Vérification automatique lors de l'accès à la page Extensions

### 🔧 Technique

- Cache réduit de 12h à 1h (HOUR_IN_SECONDS)
- Ajout du hook `load-plugins.php` pour vérification automatique
- Nouvelle page admin `wp-url-manager-updates`
- Interface moderne pour le suivi des mises à jour

## [1.0.1] - 2026-03-17

### 🐛 Corrections

- **Redirection 301** : Correction majeure du système de redirection qui ne fonctionnait pas
  - Ajout du hook `parse_request` pour intercepter les URLs legacy avant résolution WordPress
  - Nouvelle logique de matching avec regex pour identifier les anciennes URLs
  - Recherche intelligente du post par slug ou ID
  - Prévention des boucles de redirection
  - Tests de non-régression ajoutés

### 🔧 Améliorations techniques

- Méthode `match_legacy_url()` pour matcher les patterns source
- Méthode `pattern_to_regex()` pour convertir patterns en regex
- Méthode `get_placeholder_index()` pour extraire les valeurs des placeholders
- Performance optimisée : une seule requête BDD par tentative de redirection

## [Non publié]

### À venir dans les prochaines versions

#### Fonctionnalités envisagées
- Import/export de règles en JSON
- Logs des redirections
- Statistiques d'utilisation
- Drag & drop pour réorganiser les règles
- Prévisualisation bulk des URLs
- Support des archives et taxonomies
- Règles conditionnelles avancées
- API REST pour gestion externe
- Intégration avec plugins SEO populaires

#### Améliorations UX
- Mode sombre
- Recherche/filtrage des règles
- Historique des modifications
- Undo/Redo
- Raccourcis clavier

#### Performance
- Cache des résolutions de placeholders
- Optimisation des requêtes
- Lazy loading de l'interface

---

**Légende :**
- `Ajouté` : Nouvelles fonctionnalités
- `Modifié` : Changements de fonctionnalités existantes
- `Déprécié` : Fonctionnalités bientôt supprimées
- `Supprimé` : Fonctionnalités supprimées
- `Corrigé` : Corrections de bugs
- `Sécurité` : Correctifs de sécurité
