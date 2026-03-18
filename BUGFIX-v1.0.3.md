# Corrections v1.0.3 - 3 bugs critiques

## Bug 1 : Mise à jour automatique ne fonctionne pas

**Problème :** L'URL de téléchargement pointe vers le code source GitHub au lieu du ZIP de release.

**URL actuelle (incorrecte) :**
```
https://github.com/webAnalyste/WP-URL-Manager/archive/refs/tags/v1.0.2.zip
```
Cette URL télécharge le code source, pas le ZIP de release attaché.

**Solution :** Utiliser l'API GitHub pour récupérer l'URL du ZIP de release attaché.

---

## Bug 2 : Formulaire en popup au lieu d'intégré

**Problème :** Le formulaire est dans une modale, on ne peut pas voir les shortcodes pendant l'édition.

**Solution :** Refondre l'interface avec :
- Formulaire intégré dans la page principale
- Sidebar avec les shortcodes toujours visible
- UI dynamique pour basculer entre liste et formulaire

---

## Bug 3 : Redirection 301 ne fonctionne pas (404)

**Problème :** La nouvelle URL est générée mais retourne 404.

**Cause probable :** Les rewrite rules ne sont pas correctement générées ou le pattern_to_query ne capture pas tous les placeholders.

**Solution :** Améliorer la génération des rewrite rules pour capturer tous les placeholders.
