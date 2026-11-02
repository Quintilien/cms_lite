# Option : consultation d'une base externe

*[English](README.md) · Français*

`extdb.php` affiche, dans l'habillage du site, le contenu d'une **autre base SQLite** : une liste (avec tri et recherche) et une fiche de détail. Utile pour publier un catalogue, un inventaire, une bibliothèque… sans programmer. La base n'est jamais modifiée : elle est lue au travers de deux **vues** SQL, qui décident de ce qui est montré.

Cette option ne fait pas partie du CMS de base depuis la 4.7.0. Elle n'est **pas active** tant que ses fichiers n'ont pas été copiés dans le site.

Pour une base qu'il faut aussi **modifier** en ligne, un outil dédié (OscarDB, par exemple) convient mieux.

## Contenu

```
options/external-database/
└── install/
    ├── public/extdb.php
    └── data/base_de_donnees_bouquins.db    base d'exemple « Bouquins » (facultatif)
```

## Activer

**Installation par Git (VPS, mode 3)**, depuis le répertoire de l'installation :

```sh
cp options/external-database/install/public/extdb.php public/
cp options/external-database/install/data/base_de_donnees_bouquins.db data/    # base d'exemple, facultatif
```

**Hébergement classique (modes 1 et 2)** : téléverser `install/public/extdb.php` dans `www/`, et la base dans `data/`.

Puis, dans `config/config.ini`, une ligne par base consultable : nom, chemin (relatif au dossier servi), vue de liste, vue de détail.

```ini
external_db_allowed = Bouquins, ../data/base_de_donnees_bouquins.db , vue_collections, vue_detail_titre
```

La liste est alors à l'adresse `extdb.php?db=Bouquins`. Ajouter un lien dans le menu (table `t_menu`, avec DB Browser) ou dans une page.

Les copies sont ignorées par Git : `git status` reste propre, et un `git pull` ne les touche pas.

## Préparer sa propre base

Dans la base à publier, créer deux vues (avec DB Browser) :

- la **vue de liste** : en première colonne l'identifiant de la fiche (un nombre entier, qui ne s'affiche pas), puis les colonnes à afficher. Les intitulés des colonnes (`AS 'Titre'`) deviennent les en-têtes de la liste ;
- la **vue de détail** : une colonne nommée **`id`** (le même identifiant), puis les champs de la fiche.

Seul ce que les vues montrent est visible : les autres tables et colonnes restent inaccessibles depuis le site. La base d'exemple contient les deux vues `vue_collections` et `vue_detail_titre`.

## Mettre à jour

Une copie ne suit pas `git pull`. Quand l'historique d'une version de CMS Lite signale une modification de cette option, refaire la première commande ci-dessus.

## Désactiver

Supprimer `public/extdb.php` et retirer les lignes `external_db_allowed` de `config.ini`.

## Apparence

Les textes au-dessus et au-dessous de la liste et de la fiche viennent de la table `t_parameters` : `EXTERNAL_DB_LIST_TOP`, `EXTERNAL_DB_LIST_BOTTOM`, `EXTERNAL_DB_DETAIL_TOP`, `EXTERNAL_DB_DETAIL_BOTTOM`. Pour le mode sombre, voir « Partir d'une base CMS Lite 3.1 existante » dans `README.fr.md`, à la racine du dépôt.
