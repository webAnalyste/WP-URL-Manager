# DIAGNOSTIC - Rewrite Rules non enregistrées

## Problème confirmé

**Test utilisateur :** URL `/articles/comment-mettre-en-place-la-supervision-humaine-en-ia/` **NON TROUVÉE** dans les rewrite rules WordPress.

## Analyse du code actuel

### `pattern_to_regex()` - Ligne 53-68
```php
private function pattern_to_regex($pattern) {
    $pattern = trim($pattern, '/');
    
    $regex = preg_replace('/%postname%/', '([^/]+)', $pattern);
    // ... autres placeholders
    
    $regex = '^' . $regex . '/?$';
    return $regex;
}
```

**Pour le pattern `/articles/%postname%/` :**
- Input : `articles/%postname%`
- Output : `^articles/([^/]+)/?$`
- ✅ **CORRECT**

### `pattern_to_query()` - Ligne 71-92
```php
private function pattern_to_query($pattern, $post_type) {
    $query_parts = array();
    $match_index = 1;
    
    $parts = explode('/', trim($pattern, '/'));
    
    foreach ($parts as $part) {
        if ($part === '%postname%') {
            $query_parts[] = 'name=$matches[' . $match_index . ']';
            $match_index++;
        }
        // ...
    }
    
    $query_parts[] = 'post_type=' . $post_type;
    return 'index.php?' . implode('&', $query_parts);
}
```

**Pour le pattern `/articles/%postname%/` :**
- Parts : `['articles', '%postname%']`
- Loop 1 : `articles` → pas de match → rien
- Loop 2 : `%postname%` → match → `name=$matches[1]`
- Output : `index.php?name=$matches[1]&post_type=post`
- ✅ **CORRECT**

## Hypothèses

### Hypothèse 1 : Les règles ne sont pas actives
- Vérifier que `$rule['active']` = true
- Vérifier que `get_active_rules()` retourne bien les règles

### Hypothèse 2 : Le hook `init` ne s'exécute pas
- Le hook est à priorité 20
- Peut-être trop tard ou en conflit avec un autre plugin

### Hypothèse 3 : Les rewrite rules sont écrasées
- WordPress ou un autre plugin flush les rules après notre ajout
- Nos rules sont ajoutées mais ensuite supprimées

### Hypothèse 4 : Le flush ne fonctionne pas
- `flush_rewrite_rules()` ne persiste pas les règles
- Les règles sont en mémoire mais pas dans la DB

## Solution à tester

### Test 1 : Vérifier que les règles sont bien ajoutées
Ajouter un log dans `add_rewrite_rules()` pour compter les règles actives.

### Test 2 : Vérifier le timing du hook
Essayer priorité 10 au lieu de 20, ou même 5.

### Test 3 : Forcer l'enregistrement des query vars
Ajouter un hook `query_vars` pour enregistrer les query vars custom.

### Test 4 : Vérifier que flush_rewrite_rules() est bien appelé
Ajouter un log dans `schedule_rewrite_flush()`.

## Action immédiate

Ajouter des logs détaillés pour voir :
1. Combien de règles actives sont trouvées
2. Quels regex/query sont générés
3. Si `add_rewrite_rule()` est bien appelé
4. Si les règles apparaissent dans `$wp_rewrite->rules` juste après l'ajout
