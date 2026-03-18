# ANALYSE - Pourquoi la redirection 301 ne fonctionne TOUJOURS PAS

## Test utilisateur
- Source : `/%postname%/`
- Cible : `/articles/%postname%/`
- Article créé : "test" (slug: `test`)
- Résultat : **404** (pas de redirection)

## Problème fondamental identifié

### Le vrai problème : WordPress résout `/test/` AVANT notre hook

1. **Cycle de vie WordPress** :
   ```
   init (priorité 1) → Notre hook check_legacy_urls_early()
   ↓
   parse_request → WordPress parse l'URL
   ↓
   wp → WordPress résout la query
   ↓
   template_redirect → Trop tard, WordPress a déjà trouvé le post
   ```

2. **Ce qui se passe** :
   - WordPress a une rewrite rule par défaut pour `/%postname%/`
   - WordPress trouve le post "test" avec l'URL `/test/`
   - WordPress affiche le post normalement
   - **Notre redirection ne se déclenche JAMAIS** car WordPress a déjà résolu l'URL

3. **Pourquoi `init` ne suffit pas** :
   - Le hook `init` s'exécute AVANT le parsing
   - MAIS on ne peut pas savoir quel post WordPress va charger
   - On ne peut pas matcher `/test/` avec le pattern `/%postname%/` sans connaître le post

## La vraie solution

### Il faut intercepter APRÈS que WordPress ait résolu le post

**Hook correct : `template_redirect`**

Mais avec une logique différente :
1. WordPress charge le post via son URL native (`/test/`)
2. On détecte que ce post a une règle active
3. On compare l'URL actuelle avec l'URL cible attendue
4. Si différent → redirection 301

### Code à implémenter

```php
public function handle_redirects() {
    if (is_admin() || is_404() || is_search() || is_archive() || is_home()) {
        return;
    }

    if (!is_singular()) {
        return;
    }

    global $post;

    if (!$post) {
        return;
    }

    // Récupérer les règles pour ce post_type
    $rules = $this->rules_manager->get_rules_by_post_type($post->post_type);

    foreach ($rules as $rule) {
        if (empty($rule['redirect_301']) || !$rule['active']) {
            continue;
        }

        // Construire l'URL cible attendue
        $target_url = $this->build_target_url($post, $rule['target_pattern']);
        
        if (empty($target_url)) {
            continue;
        }

        // Comparer avec l'URL actuelle
        $current_url = $this->get_current_url();
        
        $current_path = rtrim(parse_url($current_url, PHP_URL_PATH), '/');
        $target_path = rtrim(parse_url($target_url, PHP_URL_PATH), '/');

        // Si on n'est PAS sur l'URL cible, rediriger
        if ($current_path !== $target_path) {
            wp_safe_redirect($target_url, 301);
            exit;
        }
    }
}
```

## Pourquoi ça va fonctionner

1. WordPress charge le post via `/test/` (sa rewrite rule native)
2. Le post existe, donc pas de 404
3. Notre hook `template_redirect` s'exécute
4. On détecte que l'URL actuelle (`/test/`) ≠ URL cible (`/articles/test/`)
5. **Redirection 301 vers `/articles/test/`**
6. WordPress recharge avec `/articles/test/`
7. Notre rewrite rule custom capture `/articles/test/` et charge le post
8. L'URL actuelle = URL cible → pas de redirection → affichage du post

## Ce qu'il faut corriger

1. Supprimer `check_legacy_urls_early()` sur `init` (inutile)
2. Garder uniquement `template_redirect`
3. Simplifier la logique : comparer URL actuelle vs URL cible
4. S'assurer que les rewrite rules custom sont bien enregistrées pour `/articles/%postname%/`
