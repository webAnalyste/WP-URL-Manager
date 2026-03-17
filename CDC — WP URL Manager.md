# CDC — WP URL MAnager - Plugin WordPress de gestion des structures d’URL et redirections 301 par type de contenu

## 1. Objet du plugin

Créer un **petit plugin WordPress générique** permettant de :

* définir une **structure d’URL cible** par type de contenu ;
* générer les **permaliens** à partir de cette structure ;
* gérer les **règles de réécriture** nécessaires ;
* rediriger en **301** les anciennes URLs vers les nouvelles ;
* composer les URLs cibles à partir de **placeholders dynamiques** issus des données natives du contenu.

Le plugin ne doit pas être pensé comme un plugin “articles”, mais comme un **outil de gestion de patterns d’URL** pour WordPress.

---

## 2. Problématique

Sur un site WordPress, différents contenus peuvent avoir :

* des URLs historiques mal structurées ;
* des URLs ambiguës à la racine ;
* des structures non cohérentes entre pages, articles et CPT ;
* des besoins de migration SEO sans casser l’existant.

Exemple de besoin :

* URL actuelle : `/comment-obtenir-sa-certification-ga4/`
* URL cible : `/blog/comment-obtenir-sa-certification-ga4/`

Autres besoins possibles :

* `/articles/%year%/%postname%/`
* `/guide/%year%/{taxonomy:categorie_article}/%postname%/`
* `/%post_type%/%postname%/`

Le plugin doit permettre cette souplesse **sans redirection globale aveugle**, et sans dépendre d’un plugin tiers de redirection incapable de contextualiser les contenus.

---

## 3. Objectifs

### 3.1 Objectifs fonctionnels

Le plugin doit permettre de :

* sélectionner un **type de contenu WordPress** ;
* définir un **pattern d’URL cible** ;
* définir, si nécessaire, un **pattern legacy/source** ;
* activer ou désactiver la **redirection 301** ;
* calculer les nouvelles URLs de façon dynamique à partir des données du contenu ;
* rediriger uniquement les contenus réellement concernés.

### 3.2 Objectifs techniques

Le plugin doit :

* être léger ;
* être autonome ;
* ne dépendre d’aucun plugin tiers ;
* s’appuyer sur les hooks standards WordPress ;
* être maintenable ;
* éviter les comportements destructeurs sur le reste du site.

### 3.3 Objectifs SEO

Le plugin doit permettre de :

* clarifier la structure des contenus ;
* consolider les anciennes URLs vers les nouvelles ;
* éviter les pertes de trafic liées à une migration d’URL mal gérée ;
* garder une cohérence entre permaliens, maillage et redirections.

---

## 4. Périmètre

## Inclus

* gestion des patterns d’URL par type de contenu ;
* génération des nouveaux permaliens ;
* gestion des rewrite rules ;
* redirections 301 legacy vers nouvelles URLs ;
* support des placeholders standards ;
* support des placeholders de taxonomie ;
* interface minimale d’administration pour déclarer les règles ;
* validation des règles saisies.

## Exclus

* gestion des taxonomies elles-mêmes ;
* édition avancée des slugs de taxonomie ;
* logs détaillés type analytics ;
* migration de contenu ;
* mise à jour automatique des liens internes dans la base ;
* import/export massif de règles ;
* moteur complexe de priorités multi-règles ;
* compatibilité spécifique avec plugins SEO tiers au-delà du fonctionnement WordPress standard.

---

## 5. Utilisateurs concernés

### Administrateur WordPress

Doit pouvoir :

* créer une règle de structure d’URL ;
* choisir le type de contenu concerné ;
* définir le pattern cible ;
* définir le pattern source si nécessaire ;
* activer la redirection 301 ;
* sauvegarder la règle sans manipuler de code.

### Développeur / intégrateur

Doit pouvoir :

* comprendre rapidement le fonctionnement ;
* étendre le plugin si besoin ;
* intervenir sans devoir contourner une architecture opaque.

---

## 6. Principes de fonctionnement

Le plugin repose sur une logique simple :

1. l’administrateur choisit un **type de contenu** ;
2. il définit une **structure cible** ;
3. le plugin calcule le **permalink** du contenu selon ce pattern ;
4. le plugin ajoute les **rewrite rules** nécessaires ;
5. si une ancienne URL est demandée, le plugin calcule la nouvelle URL et renvoie une **301**.

Le plugin ne doit jamais appliquer une redirection générique du type :

```text
/(.*) => /nouvelle-base/$1
```

Cette approche est interdite car elle redirigerait des URLs non concernées.

---

## 7. Types de contenus pris en charge

Le plugin doit fonctionner sur :

* `post`
* tout **custom post type public**
* éventuellement les types hiérarchiques, sous réserve de règles compatibles

Le plugin ne doit pas s’appliquer à :

* `page` par défaut, sauf si explicitement activé
* taxonomies
* archives
* pages système
* endpoints techniques
* AJAX
* administration
* cron

---

## 8. Modèle de règle

Chaque règle doit comporter au minimum les propriétés suivantes :

| Champ            | Description                                       |
| ---------------- | ------------------------------------------------- |
| `active`         | active ou non la règle                            |
| `post_type`      | type de contenu concerné                          |
| `source_pattern` | pattern de l’ancienne URL, optionnel selon le cas |
| `target_pattern` | pattern de la nouvelle URL                        |
| `redirect_301`   | active ou non la redirection permanente           |
| `label`          | libellé interne de la règle pour l’admin          |

### Exemple de règle

```json
{
  "active": true,
  "label": "Articles vers blog",
  "post_type": "post",
  "source_pattern": "/%postname%/",
  "target_pattern": "/blog/%postname%/",
  "redirect_301": true
}
```

### Exemple avancé

```json
{
  "active": true,
  "label": "Guides éditoriaux",
  "post_type": "post",
  "source_pattern": "/%postname%/",
  "target_pattern": "/guide/%year%/{taxonomy:categorie_article}/%postname%/",
  "redirect_301": true
}
```

---

## 9. Placeholders à supporter

## 9.1 Placeholders standards

Le plugin doit supporter au minimum :

| Placeholder   | Valeur                 |
| ------------- | ---------------------- |
| `%postname%`  | slug du contenu        |
| `%year%`      | année de publication   |
| `%monthnum%`  | mois de publication    |
| `%day%`       | jour de publication    |
| `%post_id%`   | identifiant du contenu |
| `%post_type%` | nom du type de contenu |

## 9.2 Placeholders complémentaires

| Placeholder         | Valeur                                 |
| ------------------- | -------------------------------------- |
| `%author%`          | slug de l’auteur si disponible         |
| `%parent_postname%` | slug du parent si contenu hiérarchique |

## 9.3 Placeholders de taxonomie

Le plugin doit supporter :

```text
{taxonomy:nom_de_taxonomie}
```

Exemples :

* `{taxonomy:categorie_article}`
* `{taxonomy:parcours}`

### Règle de résolution

Si plusieurs termes existent :

* prendre le **premier terme retourné** par WordPress, ou
* une règle stable documentée dans le plugin.

Si aucun terme n’est trouvé :

* fallback défini par le plugin ;
* ou pattern considéré invalide si aucun fallback n’est possible.

Le comportement devra être explicite, prévisible et documenté.

---

## 10. Exemples de patterns valides

### Cas simples

```text
/blog/%postname%/
/articles/%postname%/
/ressources/%postname%/
/%post_type%/%postname%/
```

### Cas avec date

```text
/articles/%year%/%postname%/
/blog/%year%/%monthnum%/%postname%/
```

### Cas avec taxonomie

```text
/guide/{taxonomy:categorie_article}/%postname%/
/guide/%year%/{taxonomy:categorie_article}/%postname%/
```

### Cas mixtes

```text
/%post_type%/%year%/%postname%/
/contenus/{taxonomy:topic}/%postname%/
```

---

## 11. Exigences fonctionnelles détaillées

## 11.1 Gestion des règles

Le plugin doit permettre :

* d’ajouter une règle ;
* de modifier une règle ;
* de supprimer une règle ;
* d’activer / désactiver une règle ;
* de visualiser les règles existantes.

## 11.2 Calcul des permaliens

Pour tout contenu correspondant à une règle active :

* le permalink affiché par WordPress doit refléter le `target_pattern` ;
* les URLs générées dans l’admin et en front doivent être cohérentes.

## 11.3 Résolution des URLs

Le plugin doit permettre à WordPress de comprendre les nouvelles URLs.

Exemple :

```text
/blog/mon-article/
```

doit charger correctement le bon contenu sans 404.

## 11.4 Redirection 301

Si une ancienne URL correspondant au `source_pattern` est appelée :

* le plugin doit calculer la cible ;
* renvoyer une **301** ;
* uniquement si le contenu concerné existe ;
* uniquement si la règle active le prévoit.

## 11.5 Préservation du reste du site

Le plugin ne doit jamais :

* rediriger une page non concernée ;
* rediriger un autre type de contenu par erreur ;
* rediriger une archive ;
* rediriger une taxonomie ;
* créer de boucle de redirection ;
* casser l’accès admin.

---

## 12. Interface d’administration

Le plugin doit disposer d’une interface simple dans l’administration WordPress.

## 12.1 Liste des règles

Un écran doit afficher :

* le libellé ;
* le type de contenu ;
* le pattern source ;
* le pattern cible ;
* la 301 activée ou non ;
* le statut actif ou inactif.

## 12.2 Formulaire d’édition

Le formulaire doit permettre de saisir :

* libellé ;
* type de contenu ;
* pattern source ;
* pattern cible ;
* activation de la 301 ;
* activation générale de la règle.

## 12.3 Aide contextuelle

L’interface doit rappeler :

* les placeholders disponibles ;
* un ou plusieurs exemples ;
* les contraintes de syntaxe.

## 12.4 Validation

Le plugin doit empêcher l’enregistrement de règles invalides :

* pattern vide ;
* pattern cible sans slug ni identifiant permettant d’identifier le contenu ;
* taxonomie inexistante ;
* caractères interdits ;
* pattern ambigu ou manifestement dangereux.

---

## 13. Exigences UX

L’UX admin doit être :

* simple ;
* compréhensible sans documentation longue ;
* centrée sur la règle ;
* sans jargon inutile.

### Le plugin doit éviter :

* les écrans surchargés ;
* les options techniques opaques ;
* les formulations ambiguës.

### Le plugin doit favoriser :

* clarté des libellés ;
* exemples visibles ;
* feedback explicite après sauvegarde ;
* lisibilité rapide des règles actives.

---

## 14. Comportements attendus

## 14.1 Cas nominal

Une règle existe :

* `post`
* source : `/%postname%/`
* cible : `/blog/%postname%/`
* 301 activée

Alors :

* le permalink d’un post devient `/blog/mon-post/`
* l’ancienne URL `/mon-post/` redirige en 301 vers `/blog/mon-post/`

## 14.2 Cas avec taxonomie

Une règle existe :

* `post`
* cible : `/guide/%year%/{taxonomy:categorie_article}/%postname%/`

Alors :

* le plugin calcule l’année ;
* récupère le terme de taxonomie ;
* compose l’URL finale ;
* génère le permalink correspondant.

## 14.3 Cas sans terme de taxonomie

Si le pattern exige une taxonomie absente :

* fallback défini ;
* ou blocage / alerte selon la stratégie retenue.

Le comportement doit être stable et documenté.

---

## 15. Contraintes techniques

## 15.1 Stack

* WordPress standard
* PHP moderne compatible avec la version supportée du site
* aucun plugin tiers requis

## 15.2 Hooks WordPress à exploiter

Le plugin devra s’appuyer notamment sur :

* `post_link`
* `post_type_link`
* `init`
* `template_redirect`
* hooks d’activation / désactivation

## 15.3 Rewrite rules

Le plugin doit :

* générer les règles nécessaires ;
* flusher les règles uniquement quand nécessaire :

  * activation ;
  * désactivation ;
  * modification des règles.

Il ne doit pas flusher les règles à chaque chargement.

## 15.4 Sécurité

Le plugin doit :

* vérifier les capacités utilisateur dans l’admin ;
* sanitiser les champs ;
* valider les patterns ;
* échapper les sorties ;
* ne rien faire en contexte admin hors besoin ;
* ne rien faire en AJAX / cron / CLI si non pertinent.

---

## 16. Règles de redirection

## 16.1 Principe

Une redirection 301 ne doit être appliquée que si :

* la règle est active ;
* le contenu existe ;
* l’URL demandée correspond réellement à une ancienne forme ;
* la cible calculée est différente ;
* aucune boucle n’est possible.

## 16.2 Interdictions

Le plugin ne doit pas :

* appliquer une regex fourre-tout ;
* rediriger sans vérification du contenu ;
* supposer qu’une URL racine est forcément un post ;
* produire une cible vide ou invalide.

## 16.3 Code HTTP

Le plugin doit utiliser :

* **301** pour les redirections permanentes.

---

## 17. Gestion des collisions

Le plugin doit limiter les risques de collision entre :

* une page existante ;
* un CPT existant ;
* une archive ;
* une base déjà utilisée ;
* une taxonomie ;
* une règle déjà enregistrée.

### Attendu minimal

Le plugin doit au minimum :

* détecter les conflits évidents de base ;
* avertir l’administrateur ;
* empêcher les configurations grossièrement incohérentes.

---

## 18. Performances

Le plugin doit rester léger.

### Exigences

* pas de traitement lourd à chaque requête ;
* pas de flush rewrite permanent ;
* pas de requêtes inutiles ;
* logique de redirection courte et conditionnelle.

---

## 19. Maintenabilité

Le code doit être :

* structuré ;
* lisible ;
* commenté utilement ;
* sans duplication inutile ;
* organisé autour d’une architecture simple.

Le plugin doit rester un **petit plugin**, pas une usine à gaz.

---

## 20. Critères de recette

Le plugin sera considéré conforme si les points suivants sont validés.

## 20.1 Gestion des règles

* ajout d’une règle fonctionnel ;
* modification d’une règle fonctionnelle ;
* suppression d’une règle fonctionnelle ;
* activation / désactivation fonctionnelle.

## 20.2 Permaliens

* les contenus concernés affichent la nouvelle structure ;
* les contenus non concernés restent inchangés.

## 20.3 Redirections

* ancienne URL redirige en 301 vers la nouvelle ;
* pas de redirection sur les pages non concernées ;
* pas de boucle.

## 20.4 Rewrite

* les nouvelles URLs chargent correctement ;
* pas de 404 induite sur les contenus concernés ;
* pas d’impact négatif sur les autres contenus.

## 20.5 Placeholders

* `%postname%` fonctionne ;
* `%year%` fonctionne ;
* `%post_type%` fonctionne ;
* `{taxonomy:...}` fonctionne si la taxonomie existe.

## 20.6 Administration

* la liste des règles est lisible ;
* les patterns invalides sont refusés ;
* les messages de retour sont clairs.

---

## 21. Cas d’usage types

### Cas 1 — Articles à la racine vers blog

* source : `/%postname%/`
* cible : `/blog/%postname%/`

### Cas 2 — Articles vers structure datée

* source : `/%postname%/`
* cible : `/articles/%year%/%postname%/`

### Cas 3 — Guides avec taxonomie

* source : `/%postname%/`
* cible : `/guide/%year%/{taxonomy:categorie_article}/%postname%/`

### Cas 4 — CPT structuré

* post type : `cas_client`
* source : `/case-studies/%postname%/`
* cible : `/cas-clients/%postname%/`

---

## 22. Livrables attendus

Le développement doit livrer :

* le plugin WordPress complet ;
* le code source ;
* l’écran d’administration ;
* la logique de rewrite ;
* la logique de redirection 301 ;
* la gestion des placeholders ;
* une courte documentation d’installation et d’utilisation.

---

## 23. Résumé de la demande

Le plugin attendu est un **petit plugin WordPress générique** capable de :

* définir librement un **pattern d’URL cible** par type de contenu ;
* éventuellement définir un **pattern source** ;
* composer l’URL finale à partir de **placeholders dynamiques** ;
* mettre en place les **rewrite rules** correspondantes ;
* rediriger en **301** les anciennes URLs vers les nouvelles ;
* le tout sans casser le reste du site, sans redirection globale aveugle, et avec une interface d’administration simple.

---

# Spécification courte à transmettre à un développeur

## Besoin

Créer un plugin WordPress léger de gestion des structures d’URL par type de contenu.

## Le plugin doit

* permettre de définir une règle par post type ;
* accepter un `source_pattern` et un `target_pattern` ;
* supporter des placeholders comme `%postname%`, `%year%`, `%post_type%`, `{taxonomy:xxx}` ;
* recalculer les permaliens ;
* créer les rewrite rules ;
* rediriger en 301 les anciennes URLs ;
* ne jamais rediriger globalement tout ce qui passe ;
* inclure une petite interface d’administration.

## Exemples de patterns

* `/blog/%postname%/`
* `/articles/%year%/%postname%/`
* `/guide/%year%/{taxonomy:categorie_article}/%postname%/`

## Contraintes

* petit plugin ;
* pas de dépendance tierce ;
* code propre ;
* validation des patterns ;
* pas de comportements ambigus ;
* pas d’impact sur les contenus non concernés.

Si tu veux, je peux maintenant te livrer la **version technique ultra-opérationnelle**, avec les sections :
**architecture PHP, stockage des règles, hooks exacts, algorithme de résolution des placeholders et logique de matching source/cible**.
