# BUGFIX CRITIQUE v1.0.6

## Problème 1 : Plugin se désactive après mise à jour

**Symptôme :** Après mise à jour du plugin, message d'erreur "vous n'avez pas le droit d'être là" et plugin désactivé.

**Cause probable :**
- Erreur PHP fatale lors du chargement du plugin
- Fichier manquant ou mal chargé
- Problème de permissions dans le code

**À vérifier :**
1. Logs PHP WordPress (wp-content/debug.log)
2. Ordre de chargement des fichiers
3. Hooks d'activation/désactivation

## Problème 2 : Redirection 301 → 404

**Test utilisateur :**
- Source : `/%postname%/`
- Cible : `/articles/%postname%/`
- Résultat : 404 sur `/articles/test/`

**Analyse :**
La redirection 301 fonctionne probablement, MAIS la nouvelle URL `/articles/test/` génère un 404.

**Pourquoi ?**
Les rewrite rules ne sont PAS enregistrées correctement pour capturer `/articles/%postname%/`.

**Vérification nécessaire :**
```php
global $wp_rewrite;
print_r($wp_rewrite->rules);
// Doit contenir : '^articles/([^/]+)/?$' => 'index.php?name=$matches[1]&post_type=post'
```

**Solution :**
1. S'assurer que `add_rewrite_rule()` est appelé
2. Vérifier que le flush est effectué
3. Tester manuellement les rewrite rules

## Plan de correction

1. **Corriger le bug de désactivation** (PRIORITÉ 1)
   - Vérifier tous les `require_once`
   - Ajouter des vérifications d'existence de fichiers
   - Gérer les erreurs proprement

2. **Corriger les rewrite rules** (PRIORITÉ 2)
   - Vérifier que `pattern_to_regex()` génère le bon regex
   - Vérifier que `pattern_to_query()` génère la bonne query
   - Ajouter des logs pour debug
   - Forcer le flush après sauvegarde de règle

3. **Ajouter une page de diagnostic**
   - Afficher les rewrite rules WordPress
   - Afficher les règles du plugin
   - Tester manuellement une URL
