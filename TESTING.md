# Guide de tests - WP URL Manager

## Tests fonctionnels à effectuer

### 1. Installation et activation

- [ ] Installation du plugin réussie
- [ ] Activation sans erreur
- [ ] Menu "URL Manager" visible dans l'admin
- [ ] Aucune erreur PHP dans les logs

### 2. Interface d'administration

#### Affichage initial
- [ ] Page d'accueil s'affiche correctement
- [ ] État vide affiché si aucune règle
- [ ] Sidebar d'aide visible
- [ ] Bouton "Nouvelle règle" fonctionnel

#### Création de règle
- [ ] Modal s'ouvre au clic sur "Nouvelle règle"
- [ ] Tous les champs sont présents
- [ ] Liste des post types correcte
- [ ] Validation en temps réel fonctionne
- [ ] Aperçu d'URL s'affiche
- [ ] Messages d'erreur clairs si pattern invalide
- [ ] Sauvegarde réussie
- [ ] Notification de succès affichée
- [ ] Règle apparaît dans la liste

#### Modification de règle
- [ ] Modal s'ouvre avec données pré-remplies
- [ ] Modification sauvegardée correctement
- [ ] Notification de succès affichée

#### Suppression de règle
- [ ] Confirmation demandée
- [ ] Suppression effective
- [ ] Notification de succès affichée
- [ ] Règle disparaît de la liste

#### Toggle activation
- [ ] Switch fonctionne
- [ ] État sauvegardé immédiatement
- [ ] Notification affichée

### 3. Validation des patterns

#### Patterns valides
- [ ] `/blog/%postname%/` accepté
- [ ] `/articles/%year%/%postname%/` accepté
- [ ] `/guide/{taxonomy:category}/%postname%/` accepté
- [ ] `/%post_type%/%postname%/` accepté

#### Patterns invalides
- [ ] `blog/%postname%/` refusé (pas de slash initial)
- [ ] `/blog/%postname%` refusé (pas de slash final)
- [ ] `/blog/` refusé (pas d'identifiant)
- [ ] `/blog/%invalid%/` refusé (placeholder invalide)
- [ ] `/blog/{taxonomy:inexistante}/` refusé (taxonomie inexistante)

### 4. Génération des permaliens

#### Test avec règle simple
**Règle :** `post` → `/blog/%postname%/`

- [ ] Créer un article "Test Article"
- [ ] Vérifier que le permalink est `/blog/test-article/`
- [ ] Vérifier en frontend que l'URL fonctionne
- [ ] Pas de 404

#### Test avec date
**Règle :** `post` → `/articles/%year%/%postname%/`

- [ ] Créer un article
- [ ] Vérifier que l'année est correcte dans l'URL
- [ ] URL accessible sans 404

#### Test avec taxonomie
**Règle :** `post` → `/guide/{taxonomy:category}/%postname%/`

- [ ] Créer une catégorie "Tutoriels"
- [ ] Créer un article dans cette catégorie
- [ ] Vérifier que l'URL contient `/guide/tutoriels/`
- [ ] URL accessible sans 404

#### Test sans taxonomie
**Règle :** `post` → `/guide/{taxonomy:category}/%postname%/`

- [ ] Créer un article sans catégorie
- [ ] Vérifier le fallback (uncategorized)
- [ ] URL accessible

### 5. Redirections 301

#### Test redirection simple
**Règle :**
- Source : `/%postname%/`
- Cible : `/blog/%postname%/`
- 301 : activée

**Tests :**
- [ ] Créer un article "Mon Article"
- [ ] Accéder à `/mon-article/`
- [ ] Vérifier redirection vers `/blog/mon-article/`
- [ ] Code HTTP = 301
- [ ] Pas de boucle de redirection

#### Test sans redirection
**Règle :**
- Cible : `/blog/%postname%/`
- 301 : désactivée

**Tests :**
- [ ] Ancienne URL ne redirige pas
- [ ] Nouvelle URL fonctionne

#### Test redirection avec date
**Règle :**
- Source : `/%postname%/`
- Cible : `/articles/%year%/%postname%/`
- 301 : activée

**Tests :**
- [ ] Redirection fonctionne
- [ ] Année correcte dans la cible
- [ ] Code 301

### 6. Rewrite rules

- [ ] Flush automatique après création de règle
- [ ] Flush automatique après modification
- [ ] Pas de flush à chaque requête
- [ ] URLs résolues correctement

### 7. Sécurité

#### Permissions
- [ ] Seuls les administrateurs peuvent accéder
- [ ] Utilisateurs non-admin bloqués
- [ ] Nonces vérifiés sur toutes les actions AJAX

#### Sanitization
- [ ] Champs texte sanitisés
- [ ] Patterns validés
- [ ] Pas d'injection possible

### 8. Performance

- [ ] Pas de requêtes lentes
- [ ] Pas de flush rewrite inutile
- [ ] Frontend rapide
- [ ] Admin réactif

### 9. Compatibilité

#### Post types
- [ ] Fonctionne avec `post`
- [ ] Fonctionne avec `page` (si activé)
- [ ] Fonctionne avec CPT personnalisés

#### Taxonomies
- [ ] Fonctionne avec `category`
- [ ] Fonctionne avec `post_tag`
- [ ] Fonctionne avec taxonomies personnalisées

### 10. Cas limites

#### Contenus sans données
- [ ] Article sans slug → gestion correcte
- [ ] Article sans date → gestion correcte
- [ ] Article sans auteur → gestion correcte
- [ ] Article sans parent → gestion correcte

#### Règles multiples
- [ ] Plusieurs règles pour différents post types
- [ ] Pas de conflit entre règles
- [ ] Activation/désactivation indépendante

#### Désactivation du plugin
- [ ] Désactivation sans erreur
- [ ] Permaliens reviennent à la normale
- [ ] Pas de 404 après désactivation

#### Désinstallation
- [ ] Options supprimées
- [ ] Rewrite rules nettoyées
- [ ] Pas de traces dans la BDD

## Tests de régression

Après chaque modification du code :

1. Vérifier que les règles existantes fonctionnent toujours
2. Vérifier que les permaliens sont corrects
3. Vérifier que les redirections fonctionnent
4. Vérifier l'interface admin
5. Vérifier les logs d'erreur

## Environnements de test

- [ ] WordPress 5.8
- [ ] WordPress 6.0+
- [ ] WordPress dernière version
- [ ] PHP 7.4
- [ ] PHP 8.0+
- [ ] PHP 8.2+
- [ ] Multisite
- [ ] Site simple

## Outils de test

- **Navigateur** : Chrome, Firefox, Safari
- **Outils dev** : Console, Network, Performance
- **WordPress** : Query Monitor, Debug Bar
- **HTTP** : Vérifier codes de réponse (200, 301, 404)

## Checklist avant release

- [ ] Tous les tests fonctionnels passent
- [ ] Aucune erreur PHP
- [ ] Aucun warning PHP
- [ ] Performance acceptable
- [ ] Documentation à jour
- [ ] README complet
- [ ] Changelog mis à jour
- [ ] Version incrémentée
- [ ] Code commenté
- [ ] Sécurité validée
