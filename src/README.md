# src/ — the site's shared code: never inside the web root

Files included by the pages in `public/` (`config.php`, `config_ini.php`,
`nav.php`, `footer.php`, `markdown_converter.php`). This folder stays **next
to** the served folder, never inside it. The editor (`admin/`) also reads
`config_ini.php` from here, to read `config.ini` exactly as the site does.

**FR —** Fichiers inclus par les pages de `public/`. Ce dossier reste **à côté** du dossier servi, jamais dedans. L'éditeur (`admin/`) y lit aussi `config_ini.php`, pour lire `config.ini` exactement comme le site.
