# LOGS NÉCESSAIRES POUR DIAGNOSTIC

## Ce que je dois voir dans debug.log

Après installation de v1.1.1 et accès à l'ancienne URL `/test/`, je dois voir :

### 1. Hook parse_request s'exécute
```
WP URL Manager: check_legacy_url() - Request path: test
```
**Si absent** → Le hook ne s'exécute pas, problème de priorité ou de hook

### 2. Extraction du slug
```
WP URL Manager: Extracted slug: test from test
```
**Si absent** → La regex ne match pas, problème dans extract_slug_from_url()

### 3. Post trouvé
```
WP URL Manager: Found post #123 (test)
```
**Si absent** → get_posts() ne trouve pas le post, problème de slug ou post_type

### 4. Comparaison des paths
```
WP URL Manager: Current path: /test/
WP URL Manager: Target path: /articles/test/
```
**Si absent** → Problème dans build_target_url()

### 5. Redirection
```
WP URL Manager: ✅ Redirecting 301 from /test/ to http://site.com/articles/test/
```
**Si absent** → Les paths sont identiques (pas de redirection nécessaire) ou problème dans wp_safe_redirect()

## Scénarios possibles

### Scénario A : Aucun log
→ Le hook parse_request ne s'exécute pas
→ Vérifier que WP_DEBUG est activé
→ Vérifier que le plugin est bien activé

### Scénario B : Log 1 seulement
→ Le slug n'est pas extrait
→ Problème de regex ou source_pattern vide

### Scénario C : Log 1 + 2 seulement
→ Le post n'est pas trouvé
→ Vérifier que le post existe avec ce slug
→ Vérifier le post_type de la règle

### Scénario D : Tous les logs SAUF redirection
→ Current path = Target path (déjà sur la bonne URL)
→ Ou wp_safe_redirect() échoue

### Scénario E : Tous les logs + redirection
→ **ÇA MARCHE !** 🎉

## Ce que l'utilisateur doit faire

1. Installer v1.1.1
2. Activer WP_DEBUG dans wp-config.php
3. Créer un article avec slug "test"
4. Créer une règle :
   - Source : `/%postname%/`
   - Cible : `/articles/%postname%/`
   - Redirection 301 : ✅
5. Accéder à `http://site.com/test/`
6. Copier TOUTES les lignes contenant "WP URL Manager" dans debug.log
7. Me les envoyer

Avec ces logs, je saurai EXACTEMENT où ça bloque.
