# Guide de Release - WP URL Manager

## Processus de release

### 1. Préparer la release

```bash
# Mettre à jour la version dans les fichiers
# - wp-url-manager.php (Version: X.X.X)
# - includes/class-wp-url-manager.php (WP_URL_MANAGER_VERSION)
# - build.sh (VERSION="X.X.X")

# Mettre à jour CHANGELOG.md
# Ajouter les nouvelles fonctionnalités, corrections, etc.
```

### 2. Créer le ZIP de distribution

```bash
bash build.sh
```

Cela génère : `dist/wp-url-manager-X.X.X.zip`

### 3. Commit et tag

```bash
git add .
git commit -m "chore: Release v1.0.0"
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin main
git push origin v1.0.0
```

### 4. Créer la release sur GitHub

1. Aller sur https://github.com/webAnalyste/WP-URL-Manager/releases
2. Cliquer sur "Draft a new release"
3. Choisir le tag : `v1.0.0`
4. Titre : `Version 1.0.0`
5. Description : Copier depuis CHANGELOG.md
6. Attacher le fichier : `dist/wp-url-manager-1.0.0.zip`
7. Publier la release

### 5. Vérifier la mise à jour automatique

1. Installer une version antérieure du plugin sur un WordPress de test
2. Aller dans Extensions > Extensions installées
3. Vérifier qu'une mise à jour est disponible
4. Cliquer sur "Mettre à jour"
5. Vérifier que la mise à jour fonctionne

## Format du tag

- Format : `vX.X.X` (ex: `v1.0.0`, `v1.2.3`)
- Le `v` initial est obligatoire pour le système de mise à jour

## Format de la description de release

```markdown
## 🎉 Version X.X.X

### ✨ Nouvelles fonctionnalités
- Fonctionnalité 1
- Fonctionnalité 2

### 🐛 Corrections
- Correction 1
- Correction 2

### 🔧 Améliorations
- Amélioration 1
- Amélioration 2

### 📝 Documentation
- Mise à jour documentation

### ⚠️ Breaking Changes
- Changement cassant 1 (si applicable)

## 📦 Installation

Téléchargez le fichier `wp-url-manager-X.X.X.zip` ci-dessous et installez-le via l'admin WordPress.

## 🔄 Mise à jour

Si vous avez déjà installé le plugin, la mise à jour automatique est disponible depuis l'admin WordPress.
```

## Versioning (Semantic Versioning)

- **MAJOR** (X.0.0) : Changements incompatibles avec les versions précédentes
- **MINOR** (0.X.0) : Nouvelles fonctionnalités compatibles
- **PATCH** (0.0.X) : Corrections de bugs compatibles

## Checklist avant release

- [ ] Tests fonctionnels passent
- [ ] Aucune erreur PHP
- [ ] Version mise à jour dans tous les fichiers
- [ ] CHANGELOG.md à jour
- [ ] README.md à jour si nécessaire
- [ ] Build ZIP réussi
- [ ] Commit et tag créés
- [ ] Release GitHub publiée avec ZIP attaché
- [ ] Mise à jour automatique testée

## Rollback en cas de problème

```bash
# Supprimer le tag local
git tag -d v1.0.0

# Supprimer le tag distant
git push origin :refs/tags/v1.0.0

# Supprimer la release sur GitHub (via interface web)
```

## Notes importantes

- Le système de mise à jour vérifie GitHub toutes les 12 heures
- Le cache peut être vidé en désactivant/réactivant le plugin
- Les utilisateurs doivent avoir les permissions `update_plugins`
- Le ZIP doit être attaché à la release GitHub (pas juste le source code auto-généré)
