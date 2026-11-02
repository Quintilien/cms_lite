# Option : Web Documents Browser

*[English](README.md) · Français*

Une page qui liste les documents d'un répertoire du site (PDF, Word, Markdown, HTML…), avec tri par date, titre, auteur ou catégorie, et leur affichage dans l'habillage du site. Les informations viennent du **nom des fichiers** : `20250715.Titre.Auteur.Catégorie.pdf`. Aucune base, aucun éditeur : on dépose des fichiers, la liste suit.

Cette option ne fait pas partie du CMS de base depuis la 4.7.0. Elle n'est **pas active** tant que ses fichiers n'ont pas été copiés dans le site.

## Contenu

```
options/web-documents-browser/
└── install/                 à copier tel quel à la racine de l'installation
    ├── public/
    │   ├── doclist.php      la liste des documents
    │   ├── displayfile.php  affichage d'un PDF ou lien de téléchargement
    │   ├── displayhtml.php  affichage d'un .html / .htm
    │   ├── displaymd.php    affichage d'un .md / .txt
    │   └── Documents/       documents de démonstration (facultatif)
    └── src/
        └── doclist_lib.php  réglage doclist_allowed et contrôles communs
```

## Activer

**Installation par Git (VPS, mode 3)**, depuis le répertoire de l'installation :

```sh
cp options/web-documents-browser/install/public/*.php public/
cp options/web-documents-browser/install/src/*.php src/
cp -R options/web-documents-browser/install/public/Documents public/    # documents de démonstration, facultatif
```

**Hébergement classique (modes 1 et 2)** : téléverser le contenu de `install/public/` dans `www/`, et `install/src/doclist_lib.php` dans `src/`.

Puis, dans `config/config.ini`, indiquer le ou les répertoires à lister, séparés par des virgules :

```ini
doclist_allowed = Documents
```

La liste est alors à l'adresse `doclist.php?dir=Documents`. Ajouter un lien dans le menu (table `t_menu`, avec DB Browser) ou dans une page.

Les copies sont ignorées par Git : `git status` reste propre, et un `git pull` ne les touche pas.

## Mettre à jour

Une copie ne suit pas `git pull`. Quand l'historique d'une version de CMS Lite signale une modification de cette option, refaire les deux premières commandes ci-dessus (pas celle des documents de démonstration, qui écraserait les vôtres s'ils portent le même nom).

## Désactiver

Supprimer `public/doclist.php`, `public/display*.php` et `src/doclist_lib.php`, et retirer `doclist_allowed` de `config.ini`.

## Sécurité

- Seuls les répertoires listés dans `doclist_allowed`, indiqués tels quels (`Documents`, pas `./Documents/`), et les noms de fichiers simples qu'ils contiennent directement sont accessibles (4.3.2). `displayhtml.php` n'affiche que les `.html`/`.htm`, `displaymd.php` que les `.md`/`.txt` (4.5.2).
- Tout ce qui se trouve dans ces répertoires est **public** : n'y mettre que des documents à publier. **Ne jamais y mettre `files` ni `images/uploads`**, les dossiers de dépôt de l'éditeur.
- Recommandé : l'en-tête `X-Content-Type-Options: nosniff` côté serveur pour ces fichiers statiques (voir « Sécurité, en bref » dans `README.fr.md`, à la racine du dépôt).

## Apparence

Les textes au-dessus et au-dessous de la liste et des documents affichés viennent de la table `t_parameters` : `DOCLIST_TOP`, `DOCLIST_BOTTOM`, `DISPLAY_FILE_TOP`, `DISPLAY_FILE_BOTTOM`, `DISPLAY_HTML_TOP`, `DISPLAY_HTML_BOTTOM`, `DISPLAY_MD_TOP`, `DISPLAY_MD_BOTTOM`. La base de démonstration et `init/empty_database.sql` les contiennent déjà.
