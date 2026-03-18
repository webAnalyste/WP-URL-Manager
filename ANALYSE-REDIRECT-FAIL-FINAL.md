# ANALYSE FINALE - Pourquoi la redirection 301 NE FONCTIONNE PAS

## État actuel
- ✅ Rewrite rule enregistrée : `^articles/%postname%/?$ => index.php?name=$matches[1]&post_type=post`
- ✅ Règle active : `[active] => 1`
- ✅ Redirection 301 activée : `[redirect_301] => 1`
- ❌ **MAIS la redirection 301 ne se déclenche JAMAIS**

## Analyse du code de redirection (class-redirect-manager.php)

### Ligne 21-27 : Conditions de sortie
```php
if (is_admin() || is_404() || is_search() || is_archive() || is_home()) {
    return;
}

if (!is_singular()) {
    return;
}
```

**PROBLÈME POTENTIEL #1 :** Si l'utilisateur accède à `/test/`, WordPress ne trouve PAS l'article (car l'URL a changé), donc `is_404()` = TRUE → **sortie immédiate, pas de redirection !**

### Ligne 29-33 : Vérification du post
```php
global $post;

if (!$post) {
    return;
}
```

**PROBLÈME POTENTIEL #2 :** Si WordPress est en 404, `$post` est NULL → **sortie immédiate !**

### Ligne 35 : Récupération des règles
```php
$rules = $this->rules_manager->get_rules_by_post_type($post->post_type);
```

**PROBLÈME #3 :** Si `$post` est NULL (404), cette ligne plante ou retourne vide.

## LE VRAI PROBLÈME

**La logique actuelle ne peut PAS fonctionner pour une redirection 301 depuis une ancienne URL !**

Voici pourquoi :
1. Utilisateur accède à `/test/` (ancienne URL)
2. WordPress cherche l'article avec le slug "test" à l'URL `/test/`
3. **MAIS** le permalink a été changé en `/articles/test/` par le plugin
4. WordPress ne trouve RIEN à `/test/` → **404**
5. `handle_redirects()` s'exécute
6. `is_404()` = TRUE → **sortie ligne 22, pas de redirection**

## SOLUTION

Il faut intercepter la requête **AVANT** que WordPress décide que c'est un 404.

### Option 1 : Hook `parse_request` (AVANT la détection 404)
```php
add_action('parse_request', array($this, 'check_legacy_url'), 1);
```

Dans `check_legacy_url()` :
1. Récupérer l'URL demandée
2. Chercher dans TOUS les posts si un post existe avec ce slug
3. Si trouvé, construire la nouvelle URL avec le pattern cible
4. Comparer ancienne vs nouvelle
5. Si différent, rediriger 301

### Option 2 : Hook `template_redirect` MAIS sans vérifier is_404()
```php
public function handle_redirects() {
    if (is_admin()) {
        return;
    }
    
    // NE PAS vérifier is_404() !
    
    $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    
    // Chercher un post qui correspond au slug dans l'URL
    // Peu importe si WordPress pense que c'est un 404
    
    // Si trouvé, construire target URL et rediriger si différent
}
```

### Option 3 : Hook `request` pour modifier la query AVANT l'exécution
Modifier la query WordPress pour qu'elle cherche le post au bon endroit.

## CONCLUSION

**Le code actuel ne peut PAS gérer les redirections 301 depuis les anciennes URLs car il sort immédiatement si is_404() = true.**

Il faut réécrire complètement la logique de redirection pour :
1. Intercepter TOUTES les requêtes (même 404)
2. Extraire le slug de l'URL demandée
3. Chercher le post correspondant dans la DB
4. Construire la nouvelle URL avec le pattern cible
5. Rediriger si l'URL actuelle ≠ URL cible
