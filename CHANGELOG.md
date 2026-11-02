# CMS Lite — changelog

*English · [Français](CHANGELOG.fr.md)*

Numbering **major.minor.patch**: the last number changes for a fix, the second for new features, the first for a redesign. File and section names are those of each version.

**4.7.2** (2 November 2026)

- Site: the image size and position keywords inherited from CMS Lite 3 (`images/x.png# max320px`, `thumbnail`, `center`, `float-left`…) keep working on a page that went through the editor. The editor writes the space before the keyword as `%20`, the correct form in an address, and `site.css` only recognised the space: the image then took the full width. Both forms are recognised.

Updating: `git pull` (mode 3); otherwise, upload `public/css/site.css` (into `www/css/`). A site still using the old style sheets (3.1 `style.css`) needs the same rules: see `site.css`, section "Older CMS Lite conventions".

**4.7.1** (1 November 2026)

No new feature: the repository switches to English, ahead of its publication.

- `README.md` in English, `README.fr.md` in French, likewise for the options; history in `CHANGELOG.md` / `CHANGELOG.fr.md`. The README is reorganised: the recommended installation (mode 3) comes first.
- Comments in the code and configuration files in English; the code itself does not change, nor do the labels of the site and the editor.
- Renamed: `options/base-externe/` → `options/external-database/`, `admin/htaccess.exemple` → `admin/htaccess.example`, `init/nouvelle_base.sql` → `init/empty_database.sql`, and in `Caddyfile.example` the `entetes_securite` block → `security_headers` (an existing Caddyfile importing it under the old name keeps working: it contains its own copy).
- `.htaccess` safety net (whole repository served by mistake): also covers `options/` and `README.fr.md`.

Updating: `git pull` (mode 3); otherwise, nothing essential (documentation files and comments). An option activated before 4.7.1 remains valid: its files do not change.

**4.7.0** (1 November 2026)

- **Options.** Web Documents Browser (`doclist.php`, `display*.php`, `Documents/`) and browsing an external database (`extdb.php`, "Bouquins" database) leave the base site for `options/`, with their README. Their shared code also leaves `src/config.php`. `config.ini.example` is reduced to `db` and `title`.
- **If you use one of these features**: after `git pull`, its files have gone from `public/`. Copy them again following the option's README (two commands); `config.ini` is unchanged.
- Editor: **video player** in the page (mp4, webm), modelled on the audio player. No autoplay; videos from other sites (YouTube…) remain links.
- Demo: external database menu entry removed; the page describing it says so.

Updating: `git pull` (mode 3); otherwise, upload `admin/`, `src/` and `public/css/` (into `www/css/`). The old `doclist.php`, `display*.php` and `extdb.php` left in `www/` no longer work with the new `src/config.php`: replace them with the option's files if you use them, otherwise delete them.

**4.6.0** (1 November 2026)

- Demo: all pages are in the **editor's format** and open in it, except "Gâteau Quatre-Quarts", kept in Markdown as an example of the classic format. Layout, text and images unchanged; callouts become the editor's.
- Editor: accepts images stored anywhere in `images/`, not only in `images/uploads/`: a page taken over from an older site keeps its illustrations.

Updating: `git pull` (mode 3); otherwise, upload `admin/` and `src/`. The demo database changes: your own database is not touched.

**4.5.2** (1 November 2026) — **security fix, update recommended**

Review of all entry points, by an independent reviewer:

- Editor: stronger link filtering; page title shown as plain text.
- `displayhtml.php` and `displaymd.php` only show their own file type.
- `extdb.php`, `toc.php`, public scripts: unexpected address parameters handled without error; a missing record is reported as such.
- Visit log (`LOGFILE` option): reliable visitor address behind a proxy, bounded fields.
- Editor: internal errors are no longer detailed on screen (log only). The version number follows the update without restarting the container (`VERSION` is no longer mounted on its own; the line may stay in an existing `compose.yml`).
- README: separate subdomain advised for the editor in mode 2; server-side `nosniff` recommended; `files` and `images/uploads` excluded from `doclist_allowed`.

Updating: `git pull` (mode 3); otherwise, upload `public/`, `src/` and `admin/`.

**4.5.1** (1 November 2026)

- Editor: the session (and therefore the anti-CSRF token) starts before any HTML output. In 4.5.0, under Docker (PHP without output buffering), the token stayed empty and every save was refused. Updating: `git pull`, nothing else.

**4.5.0** (1 November 2026)

- Editor: **"Page properties"** panel (category, order within the category, author, date), collapsed under the title, with the values already in use as suggestions. Extra spaces removed on saving.
- Editor: anti-CSRF token in addition to the origin check (session `cms_lite_admin`).
- Site: titles, categories, authors and dates shown as plain text. `?rev` removed (it showed the version to anyone).
- `config.ini`: a single reader, shared by the site and the editor (`src/config_ini.php`); format unchanged. Optional `timezone` key (default `Europe/Brussels`): under Docker, the names of uploaded images were timestamped in UTC.
- Page list: the "Status" column no longer wraps.
- Demo: "Transférer le site…" page updated (`config/`, UTF-8 without BOM), titles without stray tabs.
- Docker: the editor also mounts `src/` (read-only). **Add the line to your `compose.yml`** (see below).

**Upgrading from 4.4 to 4.5.0**

*Git installation (mode 3)*: `umask 002 && git pull --ff-only`, then add to the editor's service, in `compose.yml`, the line `- ./src:/var/www/src:ro` (as in `compose.example.yml`), and `sudo docker compose up -d`.

*Classic hosting (modes 1 and 2)*: upload `public/`, `src/` and, in mode 2, `admin/`. Nothing to change in `config.ini`.

**4.4.0** (30 October 2026) — **new file layout**

- Only `public/` (formerly `www/`) is served on the web. `config.ini` moves to `config/`, the shared code (`config.php`, `nav.php`, `footer.php`, `markdown_converter.php`) to `src/`, and `vendor/` and `composer.*` to the repository root: all outside the web root, whatever the server. Folder names customary in PHP (`public/`, `config/`, `src/`, as proposed by *pds/skeleton*).
- PHP errors hidden by default on all hosts (`debug` key in `config.ini`); security headers set by CMS Lite itself; optional headers block in `Caddyfile.example`.
- `config.ini` in UTF-8 without BOM (the "ANSI for IIS" advice is dropped); a BOM is ignored.
- A short `README.md` in `admin/`, `config/`, `data/` and `src/` recalls what must be protected or stay outside the web root.
- Databases, `config.ini` content and pages are unchanged: only locations change. Site pages identical, byte for byte, to those of 4.3.3.
- Docker: `compose.example.yml` and `compose.local.yml` mount `public/`, `src/`, `config/` and `vendor/`. **Copy the volumes into your `compose.yml`.**

**Upgrading from 4.3 to 4.4.0**

*Git installation (mode 3)*, from the installation directory:

```sh
sudo docker compose down
umask 002 && git pull --ff-only
# per-installation files, ignored by Git, left in the old www/:
mv www/config.ini config/config.ini                          # if it exists
mv www/images/uploads/* public/images/uploads/ 2>/dev/null
mv www/files/* public/files/ 2>/dev/null
find www -name .DS_Store -delete; find www -depth -type d -empty -delete
ls www 2>/dev/null                                           # must be empty or absent
sudo chgrp -R www-data public/images/uploads public/files
sudo chmod -R g+w public/images/uploads public/files
sudo find public/images/uploads public/files -type d -exec chmod g+s {} +
# then copy the volumes of compose.example.yml into compose.yml
sudo docker compose up -d
```

*Classic hosting (modes 1 and 2)*: upload the new layout (see "Installing" in the README), move `config.ini` into `config/`, and delete from the web root the old `config.ini`, `config.php`, `nav.php`, `footer.php`, `markdown_converter.php`, `composer.*` and `vendor/`.

**4.3.3** (29 October 2026)

- Docker: PHP errors are no longer shown on the pages; they go to the container log (`sudo docker logs cms_lite_demo_public`, or the container's name). Until now, the official PHP image, without `php.ini`, showed them, with server paths and SQL queries. Rebuild the images: `sudo docker compose up -d --build`.
- Classic hosting: unchanged, the host's setting applies.

**4.3.2** (29 October 2026) — **security fix, update strongly recommended**

- Document browsing (`doclist.php`, `display*.php`, Web Documents Browser) and external databases (`extdb.php`): stronger checks.
- Versions 3.x and Web Documents Browser are also affected: replace these scripts and `config.php` there with those of version 4.3.2 or later, or else delete these scripts if they are not used.

**4.3.1** (28 October 2026)

- Demo: logo in a round medallion, on a transparent background, readable in dark mode; margin above the logo.
- `EXTERNAL_DB_LIST_TOP` parameter (demo database and `init/nouvelle_base.sql`): an external database's list uses `site.css`'s colours, and stays readable in dark mode. For an existing database, see "Starting from an existing CMS Lite 3.1 database".
- README: `sudo` in front of `docker compose` commands (mode 3).

**4.3.0** (28 October 2026)

- Source code under the AGPL-3.0 licence, in a GitHub repository (private until publication).
- Without `config.ini`, the site and the editor read `config.ini.example`: the repository works as soon as it is cloned, on the demo database.
- Deployment files provided as templates (`compose.example.yml`, `Caddyfile.example`); with Docker, both containers use the database named in `config.ini`.

**4.2.0** (26 September 2026)

- Editor: **callouts** in five colours (info, note, success, warning, danger), and **toggle blocks** (native `<details>`: opened and closed by the browser, without JavaScript). On the site, a toggle block always appears collapsed. When printed, all blocks are expanded, and a callout is not split across two pages.

**4.1.0** (25 September 2026)

- Editor: tables, code and code blocks, link dialog (site pages by title, e-mail, phone), audio player, videos as attachments (100 MB), images in five positions (alone on the left, centred or on the right, or with text alongside).
- Editor in French, Dutch and English, in light, dark or automatic mode.
- Single stylesheet `css/site.css`: Inter typeface hosted on the site, graded headings, half / third / two-thirds grid, automatic dark mode, line length limited except next to an image.
- Deployment: two PHP + Apache containers (read-only site, editor), behind the system's Caddy.

**4.0.0** (25 September 2026)

- First version with an editor: web administration (Tiptap) alongside CMS Lite 3.1, on the same SQLite database. `page.php` serves as is the HTML of pages marked `<!--html-->`, and classic Markdown pages remain unchanged.

**3.1** (August 2025) and earlier: see the "Historique des versions" page of the demo site.
