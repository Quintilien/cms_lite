# data/ — the SQLite databases: never inside the web root

This folder stays **next to** the served folder, never inside it: otherwise
the database (drafts included) can be downloaded. Together with
`config/config.ini` and the uploaded files (`public/images/uploads/`,
`public/files/`), it is what needs backing up.

**FR —** Ce dossier reste **à côté** du dossier servi, jamais dedans : sinon, la base (brouillons compris) se télécharge. C'est lui, avec `config/config.ini` et les fichiers envoyés (`public/images/uploads/`, `public/files/`), qu'il faut sauvegarder.
