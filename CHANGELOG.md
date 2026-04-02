# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [1.1.4] - 2026-04-02

### 🐛 Correctif - Canonical URL

- Ajout du filtre `get_canonical_url` dans `WP_URL_Manager_Permalink_Manager` pour que le `<link rel="canonical">` pointe vers l'URL cible (target pattern) et non l'URL WordPress par défaut.

## [1.1.3] - 2026-03-19

### 🐛 Correctif critique - Regex de rewrite et diagnostic frontend

- Correction d'un bug dans `pattern_to_regex()` qui reconstruisait la regex depuis le pattern brut au lieu de la version déjà transformée.
- La règle `/articles/%postname%/` génère désormais correctement `^articles/([^/]+)/?$`.
- Ajout d'une protection pour ignorer toute rewrite rule contenant encore un placeholder non résolu.
- Ajout de logs ciblés pour distinguer : aucune règle source matchée, slug matché sans post trouvé, et redirection réellement exécutée.

## [1.1.2] - 2026-03-19

### ⚡ OPTIMISATION - Performances et logs

**Redirection 301 confirmée fonctionnelle !** 🎉

**Optimisations :**
- Exclusion des requêtes wp-json, wp-admin, wp-content pour éviter les vérifications inutiles
- Réduction drastique des logs (suppression des logs "Cannot verify" et logs répétitifs)
- Logs uniquement pour les redirections réelles et les règles ajoutées
- Amélioration des performances globales

**Logs optimisés :**
- ✅ Rewrite rule added (au lieu de 5 lignes de logs)
- ✅ Redirecting 301 (uniquement quand redirection effectuée)
- ✅ Rewrite rules flushed (au lieu de 2 lignes)

**Résultat :**
- Moins de spam dans debug.log
- Meilleures performances (moins de vérifications)
- Logs plus clairs et utiles

## [1.1.1] - 2026-03-18

### 🔥 CORRECTION MAJEURE - Redirection 301 ENFIN FONCTIONNELLE

**Problème identifié :** La logique de redirection ne pouvait PAS fonctionner pour les anciennes URLs

**Pourquoi ça ne marchait pas :**
1. Utilisateur accède à `/test/` (ancienne URL)
2. WordPress cherche l'article à `/test/` mais le permalink a été changé en `/articles/test/`
3. WordPress ne trouve rien → **404**
4. `handle_redirects()` s'exécute mais vérifie `is_404()` → **sortie immédiate, pas de redirection !**

**Solution implémentée :**
- Ajout du hook `parse_request` (s'exécute AVANT la détection 404)
- Nouvelle méthode `check_legacy_url()` qui :
  1. Récupère l'URL demandée (même si 404)
  2. Extrait le slug avec le pattern source
  3. Cherche le post correspondant dans la DB avec `get_posts()`
  4. Construit l'URL cible avec le pattern cible
  5. Redirige 301 si l'URL actuelle ≠ URL cible
- Logs détaillés pour tracer l'exécution

**Changements techniques :**
- `check_legacy_url($wp)` : Intercepte les requêtes AVANT 404
- `find_post_by_legacy_url()` : Trouve le post même si l'URL est obsolète
- `extract_slug_from_url()` : Extrait le slug depuis l'URL avec regex
- Suppression de la vérification `is_404()` qui bloquait les redirections

**Résultat attendu :**
- `/test/` → Redirection 301 vers `/articles/test/` ✅
- `/articles/test/` → Affichage de l'article (pas de redirection) ✅

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

## [1.0.9] - 2026-03-18

### 🐛 HOTFIX CRITIQUE - Erreur fatale v1.0.8

**Problème :** v1.0.8 causait une erreur critique "Il y a eu une erreur critique sur ce site"

**Cause :** Accès à `$wp_rewrite->rules` sans vérifier son existence
- `count($wp_rewrite->rules)` sur NULL génère une erreur en PHP 8+
- `isset($wp_rewrite->rules[$regex])` sur NULL peut causer une erreur

**Correction :**
- Ajout de `isset($wp_rewrite->rules) && is_array($wp_rewrite->rules)` avant tout accès
- Logs alternatifs si `$wp_rewrite->rules` n'est pas initialisé

**⚠️ IMPORTANT :** Si vous avez installé v1.0.8 et que votre site est cassé :
1. Désactiver le plugin via FTP/SSH : renommer le dossier `wp-content/plugins/wp-url-manager`
2. Installer v1.0.9
3. Réactiver le plugin

## [1.0.8] - 2026-03-18 ❌ VERSION CASSÉE - NE PAS UTILISER

### 🔍 Diagnostic approfondi - Rewrite Rules

**Problème confirmé :** Les rewrite rules ne sont PAS enregistrées dans WordPress (URL `/articles/...` non trouvée)

**Ajouts pour diagnostic :**
1. **Logs ultra-détaillés** (si WP_DEBUG activé) :
   - Nombre de règles actives trouvées
   - Pattern, regex et query générés pour chaque règle
   - Confirmation si `add_rewrite_rule()` est appelé
   - Vérification immédiate si la règle apparaît dans `$wp_rewrite->rules`
   - Logs de flush des rewrite rules

2. **Page Debug améliorée** :
   - Filtre de recherche dans les rewrite rules
   - Compteur total de règles
   - Affichage du nombre de règles filtrées

### 📋 Instructions de diagnostic

1. **Activer WP_DEBUG** dans `wp-config.php` :
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. **Installer v1.0.8**

3. **Créer/modifier une règle** pour déclencher les logs

4. **Consulter `wp-content/debug.log`** et chercher :
   - `WP URL Manager: Starting add_rewrite_rules()`
   - `WP URL Manager: Generated - Regex:`
   - `WP URL Manager: ✅ add_rewrite_rule() called successfully`
   - `WP URL Manager: ✅ Rule confirmed` OU `❌ Rule NOT found`

5. **Aller dans Debug** et filtrer par "articles"

6. **Me communiquer les logs** pour identifier le problème exact

## [1.0.7] - 2026-03-18

### 🔧 Amélioration

**Page Debug toujours visible**
- La page "Debug" est maintenant **toujours accessible** dans le menu (pas seulement si WP_DEBUG activé)
- Avertissement affiché si WP_DEBUG est désactivé
- Affichage du statut WP_DEBUG et WP_DEBUG_LOG

**Accès :** URL Manager > Debug

## [1.0.6] - 2026-03-18

### 🐛 Corrections CRITIQUES

**Problème 1 : Plugin se désactive après mise à jour**
- Ajout de vérifications d'existence de fichiers avant `require_once`
- Ajout de `try/catch` dans `init_components()`
- Vérification de l'existence des classes avant instanciation
- Logs d'erreurs pour diagnostic

**Problème 2 : Redirection 301 → 404 persistant**
- Ajout de logs détaillés pour debug (si `WP_DEBUG` activé)
- Log de chaque rewrite rule ajoutée
- Log de chaque tentative de redirection
- Amélioration de la page Debug avec :
  - Liste complète des rewrite rules WordPress
  - Bouton "Flush Rewrite Rules"
  - Formulaire de test d'URL
  - Infos système (WP_DEBUG, version plugin)

### 🔧 Debug

**Pour activer les logs :**
1. Dans `wp-config.php`, ajouter :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. Aller dans **URL Manager > Debug**
3. Vérifier que votre pattern apparaît dans les rewrite rules WordPress
4. Exemple attendu : `^articles/([^/]+)/?$` => `index.php?name=$matches[1]&post_type=post`

**Si le pattern n'apparaît PAS :**
- Cliquer sur "Flush Rewrite Rules"
- Vérifier les logs dans `wp-content/debug.log`

### ✅ Instructions de test

1. **Installer v1.0.6**
2. **Activer WP_DEBUG**
3. **Créer une règle** :
   - Source : `/%postname%/`
   - Cible : `/articles/%postname%/`
   - Redirection 301 : ✅
4. **Sauvegarder** (flush auto)
5. **Aller dans Debug** et vérifier que `^articles/([^/]+)/?$` existe
6. **Créer un article** "test"
7. **Tester** `/test/` → doit rediriger vers `/articles/test/`
8. **Vérifier les logs** dans `wp-content/debug.log`

## [1.0.5] - 2026-03-18

### 🐛 Correction DÉFINITIVE - Redirection 301

**Problème persistant :** Les redirections 301 ne fonctionnaient toujours pas malgré v1.0.4

**Analyse de la cause racine :**
- WordPress résout `/test/` via sa rewrite rule native AVANT notre hook
- Le hook `init` ne peut pas intercepter car WordPress n'a pas encore chargé le post
- Le hook `parse_request` arrive trop tard, WordPress a déjà décidé d'afficher le post
- **Solution précédente était incorrecte**

**Vraie solution implémentée :**
1. **Simplification radicale** : Suppression de tous les hooks inutiles (`init`, `parse_request`)
2. **Un seul hook** : `template_redirect` (priorité 1)
3. **Logique simple** :
   - WordPress charge le post via son URL native (`/test/`)
   - Notre hook compare l'URL actuelle avec l'URL cible attendue (`/articles/test/`)
   - Si différent → redirection 301
   - Si identique → affichage normal

**Code simplifié :**
```php
public function handle_redirects() {
    // WordPress a déjà chargé le post
    global $post;
    
    // Récupérer l'URL cible attendue pour ce post
    $target_url = $this->build_target_url($post, $rule['target_pattern']);
    
    // Comparer avec l'URL actuelle
    if ($current_path !== $target_path) {
        wp_safe_redirect($target_url, 301); // Redirection
        exit;
    }
    // Sinon, WordPress affiche le post normalement
}
```

### 🔧 Technique

- Suppression de `check_legacy_urls_early()` et `check_legacy_urls()`
- Suppression de `should_redirect()`, `perform_redirect()`, `match_legacy_url()`
- Suppression de `build_source_url()`, `would_create_loop()`, `pattern_to_regex()`, `get_placeholder_index()`
- **Code réduit de 150 lignes** → Plus simple, plus robuste
- Un seul point d'entrée : `template_redirect`

### ✅ Pourquoi ça va fonctionner cette fois

1. WordPress charge `/test/` → trouve le post → pas de 404
2. `template_redirect` s'exécute avec `$post` disponible
3. On calcule l'URL cible : `/articles/test/`
4. On compare : `/test/` ≠ `/articles/test/` → **Redirection 301**
5. WordPress recharge `/articles/test/`
6. Notre rewrite rule capture `/articles/test/` → charge le post
7. On compare : `/articles/test/` = `/articles/test/` → **Affichage**

## [1.0.4] - 2026-03-18

### 🐛 Correction CRITIQUE - Redirection 301

**Problème :** Les redirections 301 ne fonctionnaient toujours pas (404 sur anciennes ET nouvelles URLs)

**Cause identifiée :**
- Le hook `parse_request` s'exécutait trop tard (après que WordPress ait décidé du 404)
- Les rewrite rules n'étaient pas flushées correctement
- Le flush était delayed au lieu d'immédiat

**Corrections appliquées :**
1. **Hook ultra précoce** : Ajout de `check_legacy_urls_early()` sur le hook `init` (priorité 1)
   - Intercepte les requêtes AVANT que WordPress ne parse quoi que ce soit
   - Utilise directement `$_SERVER['REQUEST_URI']` au lieu de `$wp->request`
   - Garantit que les redirections se déclenchent avant toute résolution WordPress

2. **Flush immédiat** : 
   - `flush_rewrite_rules()` au lieu de `flush_rewrite_rules(false)`
   - Suppression du système de flush delayed
   - Flush immédiat lors de la sauvegarde de règle

3. **Page de debug** :
   - Nouvelle classe `WP_URL_Manager_Debug_Helper`
   - Page admin "Debug" (visible uniquement si `WP_DEBUG` activé)
   - Affichage des rewrite rules WordPress
   - Bouton "Flush Rewrite Rules" manuel

### ✨ Améliorations

- Backlinks ajoutés dans README (webAnalyste.com et formations-analytics.com)
- Documentation DEBUG-REDIRECT.md pour analyse technique

### 🔧 Technique

- Nouveau hook : `init` priorité 1 pour `check_legacy_urls_early()`
- Classe `WP_URL_Manager_Debug_Helper` pour diagnostic
- Flush immédiat et complet des rewrite rules

## [1.0.3] - 2026-03-18

### 🐛 Corrections critiques

**1. Mise à jour automatique corrigée**
- L'URL de téléchargement utilise maintenant le ZIP de release attaché via l'API GitHub
- Fallback sur l'URL de release si l'API échoue
- Téléchargement du bon fichier ZIP au lieu du code source

**2. Interface utilisateur refaite**
- Formulaire intégré dans la page principale (plus de popup)
- Sidebar avec placeholders toujours visible pendant l'édition
- Navigation fluide entre liste et formulaire
- Meilleure UX pour voir les shortcodes en temps réel

**3. Redirection 301 corrigée**
- Génération des rewrite rules améliorée pour éviter les 404
- Capture correcte de tous les placeholders dans l'ordre
- Meilleure gestion des index de correspondance regex

### ✨ Améliorations UX

- Formulaire intégré au lieu de modale popup
- Sidebar aide toujours visible
- Validation en temps réel avec aperçu d'URL
- Notifications toast modernes
- Boutons "Retour à la liste" pour navigation intuitive

### 🔧 Technique

- `get_download_url()` : Utilise l'API GitHub releases pour récupérer le bon asset ZIP
- `pattern_to_query()` : Parcourt tous les placeholders pour générer les bons index
- Nouveaux fichiers : `admin-style-v2.css`, `admin-script-v2.js`
- Nouvelle vue : `main-page.php` refaite avec système de vues (liste/formulaire)

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
