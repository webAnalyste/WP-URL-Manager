# Correction du bug de redirection 301

## Problème identifié

La redirection 301 ne fonctionnait pas car :

1. **Hook trop tardif** : Le hook `template_redirect` s'exécute après que WordPress ait déjà résolu l'URL via les nouveaux permaliens
2. **Mauvaise logique** : Le code comparait l'URL actuelle (déjà transformée) avec le pattern source
3. **Résultat** : La condition `$current_path === $source_path` n'était jamais vraie

## Solution implémentée

### 1. Nouveau hook `parse_request`

Ajout d'un hook plus précoce qui intercepte la requête AVANT la résolution WordPress :

```php
add_action('parse_request', array($this, 'check_legacy_urls'), 1);
```

### 2. Nouvelle méthode `check_legacy_urls()`

Cette méthode :
- Récupère le chemin de la requête brute (`$wp->request`)
- Compare avec les patterns source via regex
- Extrait le slug ou l'ID du contenu
- Récupère le post correspondant
- Redirige vers la nouvelle URL si match

### 3. Méthode `match_legacy_url()`

Logique de matching :
- Convertit le pattern source en regex
- Extrait les placeholders (postname, post_id, etc.)
- Recherche le post correspondant en base
- Retourne le post si trouvé

### 4. Méthode `pattern_to_regex()`

Convertit les patterns en expressions régulières :
- `%postname%` → `([^/]+)`
- `%post_id%` → `([0-9]+)`
- `%year%` → `([0-9]{4})`
- etc.

## Exemples de fonctionnement

### Cas 1 : Redirection simple

**Règle :**
- Source : `/%postname%/`
- Cible : `/blog/%postname%/`
- 301 : activée

**Scénario :**
1. Utilisateur accède à `/mon-article/`
2. Hook `parse_request` intercepte la requête
3. Regex match : `#^/([^/]+)/?$#` → capture "mon-article"
4. Recherche en BDD : `get_posts(['name' => 'mon-article'])`
5. Post trouvé → génère URL cible `/blog/mon-article/`
6. Redirection 301 vers `/blog/mon-article/`

### Cas 2 : Redirection avec date

**Règle :**
- Source : `/%year%/%postname%/`
- Cible : `/articles/%year%/%postname%/`
- 301 : activée

**Scénario :**
1. Utilisateur accède à `/2026/mon-article/`
2. Regex match : `#^/([0-9]{4})/([^/]+)/?$#`
3. Capture : année=2026, slug=mon-article
4. Recherche post par slug
5. Redirection vers `/articles/2026/mon-article/`

### Cas 3 : Pas de redirection

**Règle :**
- Cible : `/blog/%postname%/`
- 301 : désactivée

**Scénario :**
1. Utilisateur accède à `/mon-article/`
2. Aucune règle avec `redirect_301 = true` et `source_pattern`
3. Pas de redirection
4. WordPress charge normalement avec le nouveau permalink

## Tests à effectuer

### Test 1 : Redirection basique
```
1. Créer un article "Test Article" (slug: test-article)
2. Créer une règle :
   - Source : /%postname%/
   - Cible : /blog/%postname%/
   - 301 : activée
3. Accéder à /test-article/
4. Vérifier redirection vers /blog/test-article/
5. Vérifier code HTTP 301
```

### Test 2 : Pas de boucle
```
1. Accéder directement à /blog/test-article/
2. Vérifier qu'il n'y a PAS de redirection
3. Page doit charger normalement
```

### Test 3 : Post inexistant
```
1. Accéder à /article-inexistant/
2. Vérifier qu'il n'y a PAS de redirection
3. WordPress doit afficher 404 normalement
```

### Test 4 : Redirection avec ID
```
1. Créer une règle avec %post_id%
2. Accéder à /123/ (ID du post)
3. Vérifier redirection vers la nouvelle URL
```

## Améliorations apportées

1. ✅ **Performance** : Vérification uniquement sur les requêtes non-admin
2. ✅ **Sécurité** : Validation du post_type et post_status
3. ✅ **Robustesse** : Gestion des cas limites (URL vide, post inexistant)
4. ✅ **Anti-boucle** : Vérification pour éviter les redirections infinies
5. ✅ **Flexibilité** : Support de tous les placeholders

## Code modifié

Fichier : `includes/class-redirect-manager.php`

- Ajout de `check_legacy_urls()` - 27 lignes
- Ajout de `match_legacy_url()` - 34 lignes
- Ajout de `pattern_to_regex()` - 16 lignes
- Ajout de `get_placeholder_index()` - 14 lignes

Total : ~90 lignes ajoutées

## Compatibilité

- ✅ WordPress 5.8+
- ✅ PHP 7.4+
- ✅ Multisite
- ✅ Tous les post types
- ✅ Tous les placeholders

## Notes importantes

1. Le hook `parse_request` s'exécute très tôt dans le cycle WordPress
2. La recherche de post se fait par slug (performant avec index BDD)
3. Une seule requête BDD par tentative de redirection
4. Pas d'impact sur les performances si pas de règle active
5. Compatible avec les plugins de cache (redirection avant cache)
