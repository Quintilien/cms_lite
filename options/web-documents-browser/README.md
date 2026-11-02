# Option: Web Documents Browser

*English · [Français](README.fr.md)*

A page that lists the documents in a folder of the site (PDF, Word, Markdown, HTML…), sortable by date, title, author or category, and shows them within the site's look. The information comes from the **file names**: `20250715.Title.Author.Category.pdf`. No database, no editor: drop files in, the list follows.

This option has not been part of the base CMS since 4.7.0. It is **not active** until its files have been copied into the site.

## Contents

```
options/web-documents-browser/
└── install/                 to be copied as is to the root of the installation
    ├── public/
    │   ├── doclist.php      the document list
    │   ├── displayfile.php  shows a PDF, or a download link
    │   ├── displayhtml.php  shows an .html / .htm file
    │   ├── displaymd.php    shows an .md / .txt file
    │   └── Documents/       demo documents (optional)
    └── src/
        └── doclist_lib.php  doclist_allowed setting and shared checks
```

## Activating

**Git installation (VPS, mode 3)**, from the installation directory:

```sh
cp options/web-documents-browser/install/public/*.php public/
cp options/web-documents-browser/install/src/*.php src/
cp -R options/web-documents-browser/install/public/Documents public/    # demo documents, optional
```

**Classic hosting (modes 1 and 2)**: upload the content of `install/public/` into `www/`, and `install/src/doclist_lib.php` into `src/`.

Then, in `config/config.ini`, name the folder or folders to list, separated by commas:

```ini
doclist_allowed = Documents
```

The list is then at `doclist.php?dir=Documents`. Add a link in the menu (`t_menu` table, with DB Browser) or in a page.

The copies are ignored by Git: `git status` stays clean, and `git pull` does not touch them.

## Updating

A copy does not follow `git pull`. When a CMS Lite version's changelog mentions a change to this option, run the first two commands above again (not the demo documents one, which would overwrite yours if they have the same name).

## Deactivating

Delete `public/doclist.php`, `public/display*.php` and `src/doclist_lib.php`, and remove `doclist_allowed` from `config.ini`.

## Security

- Only the folders listed in `doclist_allowed`, written as is (`Documents`, not `./Documents/`), and the plain file names they directly contain are reachable (4.3.2). `displayhtml.php` only shows `.html`/`.htm`, `displaymd.php` only `.md`/`.txt` (4.5.2).
- Everything in these folders is **public**: put only documents meant for publication there. **Never put `files` or `images/uploads` there**, the editor's upload folders.
- Recommended: the `X-Content-Type-Options: nosniff` header on the server side for these static files (see "Security in brief" in `README.md`, at the repository root).

## Look and feel

The text above and below the list and the displayed documents comes from the `t_parameters` table: `DOCLIST_TOP`, `DOCLIST_BOTTOM`, `DISPLAY_FILE_TOP`, `DISPLAY_FILE_BOTTOM`, `DISPLAY_HTML_TOP`, `DISPLAY_HTML_BOTTOM`, `DISPLAY_MD_TOP`, `DISPLAY_MD_BOTTOM`. The demo database and `init/empty_database.sql` already contain them.
