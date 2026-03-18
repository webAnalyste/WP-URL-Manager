# DIAGNOSTIC - Règle désactivée

## Problème identifié ✅

**La règle existe MAIS elle est DÉSACTIVÉE**

```
[active] =>    // VIDE au lieu de 1
[label] => article
[post_type] => post
[source_pattern] => /%postname%/
[target_pattern] => /articles/%postname%/
[redirect_301] => 1
```

## Conséquence

Dans `class-rewrite-manager.php`, la méthode `get_active_rules()` filtre les règles :
```php
$rules = $this->rules_manager->get_active_rules();
```

Si `[active]` est vide, la règle n'est PAS retournée par `get_active_rules()`.
Donc `add_rewrite_rule()` n'est JAMAIS appelé.

## Cause probable

Dans le formulaire d'ajout/édition de règle, le champ "active" n'est pas correctement sauvegardé.

Deux possibilités :
1. Le formulaire ne contient pas de checkbox "Activer la règle"
2. La checkbox existe mais la valeur n'est pas sauvegardée dans l'AJAX

## Solution

1. Vérifier le code AJAX `ajax_save_rule()` dans `admin/class-admin-interface.php`
2. Vérifier que `$_POST['active']` est bien récupéré et sauvegardé
3. Par défaut, une nouvelle règle devrait être active (`active = 1`)
