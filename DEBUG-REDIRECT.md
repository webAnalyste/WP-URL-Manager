# DEBUG - Redirection 301 ne fonctionne pas (404)

## Analyse du problème

### Scénario utilisateur
1. Créer une règle avec :
   - Source : `/%postname%/`
   - Cible : `/blog/%postname%/`
   - Redirection 301 : ✅ activée
2. Créer un article "test" (slug: `test`)
3. Accéder à `/test/` → **404** (au lieu de redirection vers `/blog/test/`)
4. Accéder à `/blog/test/` → **404** également

## Problème identifié

### 1. Les rewrite rules ne sont PAS générées correctement
Le `pattern_to_query()` génère : `index.php?name=$matches[1]&post_type=post`

**Mais** : WordPress ne peut pas résoudre cette query car :
- Le pattern `/blog/%postname%/` génère le regex `^blog/([^/]+)/?$`
- La query `name=$matches[1]` cherche un post avec le slug capturé
- **MAIS** WordPress ne sait pas que `/blog/test/` doit afficher le post "test"

### 2. Le hook `parse_request` ne s'exécute PAS
Le hook `parse_request` dans `check_legacy_urls()` :
- Vérifie `$wp->request` qui contient déjà le chemin résolu par WordPress
- Si WordPress ne trouve pas de rewrite rule correspondante, `$wp->request` peut être vide ou incorrect
- La redirection ne se déclenche jamais

### 3. Le problème fondamental

**WordPress ne résout PAS les nouvelles URLs** parce que :
1. Les rewrite rules sont ajoutées MAIS ne fonctionnent pas
2. WordPress retourne 404 pour `/blog/test/`
3. Le hook `parse_request` ne capture pas la requête car WordPress a déjà décidé que c'est un 404

## Solution requise

### Il faut DEUX choses :

1. **Générer les rewrite rules CORRECTEMENT** pour que WordPress résolve `/blog/test/` vers le post "test"
2. **Activer le hook de redirection AVANT que WordPress décide du 404**

### Approche correcte

**Option A : Utiliser `request` filter au lieu de `parse_request`**
- Hook plus tôt dans le cycle
- Permet de modifier la requête AVANT résolution

**Option B : Améliorer les rewrite rules**
- S'assurer que les rules sont bien enregistrées
- Forcer le flush après ajout de règle
- Vérifier que les rules sont en "top" priority

**Option C : Utiliser `do_parse_request` filter**
- Intercepter AVANT que WordPress parse la requête
- Construire manuellement la query

## Test à effectuer

1. Vérifier les rewrite rules enregistrées :
```php
global $wp_rewrite;
print_r($wp_rewrite->rules);
```

2. Vérifier si le flush est appelé après ajout de règle

3. Tester avec un hook plus précoce
