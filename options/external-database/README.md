# Option: external database

*English · [Français](README.fr.md)*

`extdb.php` shows, within the site's look, the content of **another SQLite database**: a list (with sorting and search) and a detail record. Handy for publishing a catalogue, an inventory, a library… without programming. The database is never modified: it is read through two SQL **views**, which decide what is shown.

This option has not been part of the base CMS since 4.7.0. It is **not active** until its files have been copied into the site.

For a database that must also be **edited** online, a dedicated tool (OscarDB, for example) is a better fit.

## Contents

```
options/external-database/
└── install/
    ├── public/extdb.php
    └── data/base_de_donnees_bouquins.db    "Bouquins" sample database (optional)
```

## Activating

**Git installation (VPS, mode 3)**, from the installation directory:

```sh
cp options/external-database/install/public/extdb.php public/
cp options/external-database/install/data/base_de_donnees_bouquins.db data/    # sample database, optional
```

**Classic hosting (modes 1 and 2)**: upload `install/public/extdb.php` into `www/`, and the database into `data/`.

Then, in `config/config.ini`, one line per browsable database: name, path (relative to the served folder), list view, detail view.

```ini
external_db_allowed = Bouquins, ../data/base_de_donnees_bouquins.db , vue_collections, vue_detail_titre
```

The list is then at `extdb.php?db=Bouquins`. Add a link in the menu (`t_menu` table, with DB Browser) or in a page.

The copies are ignored by Git: `git status` stays clean, and `git pull` does not touch them.

## Preparing your own database

In the database to publish, create two views (with DB Browser):

- the **list view**: first column the record's identifier (an integer, which is not shown), then the columns to show. The column labels (`AS 'Title'`) become the list's headings;
- the **detail view**: a column named **`id`** (the same identifier), then the record's fields.

Only what the views show is visible: the other tables and columns remain out of reach from the site. The sample database contains the two views `vue_collections` and `vue_detail_titre`.

## Updating

A copy does not follow `git pull`. When a CMS Lite version's changelog mentions a change to this option, run the first command above again.

## Deactivating

Delete `public/extdb.php` and remove the `external_db_allowed` lines from `config.ini`.

## Look and feel

The text above and below the list and the record comes from the `t_parameters` table: `EXTERNAL_DB_LIST_TOP`, `EXTERNAL_DB_LIST_BOTTOM`, `EXTERNAL_DB_DETAIL_TOP`, `EXTERNAL_DB_DETAIL_BOTTOM`. For dark mode, see "Starting from an existing CMS Lite 3.1 database" in `README.md`, at the repository root.
