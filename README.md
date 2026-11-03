# CMS Lite 4

*English · [Français](README.fr.md)*

**The home page for your self-hosted tools**: a small website that a non-technical person updates in their browser, whose entire content fits in a single SQLite file, and which leaves user accounts to the authentication you already have.

In PHP and SQLite, for sites of a few dozen pages that don't need WordPress: no database server to install, no plugins or themes to install and keep updated, no user accounts. The look remains entirely yours: header, footer and stylesheets are set in the database. The whole site fits in one SQLite database and one image folder.

CMS Lite serves two audiences, who work on the same database without getting in each other's way:

- **Non-technical people** get an **online editor**, Docmost-style (Tiptap): headings, lists, links, images dragged or pasted in, columns, tables, callouts, attachments, audio and video. Enough to keep the site up to date without installing anything.
- **Tinkerers** manage everything "offline": pages in Markdown, menus and submenus, header, footer and stylesheets in the database, with [DB Browser for SQLite](https://sqlitebrowser.org/), then upload to the host by FTP. That is how CMS Lite has always worked, and the editor remains optional.

The repository includes a **demo database**: the site works as soon as it is downloaded. The demo content is in French.

## At a glance: what is published, what is protected

```
cms_lite/
├── public/    WHAT IS PUBLISHED: the only folder served on the web
├── admin/     WHAT IS PROTECTED: the editor, never freely accessible (or not uploaded at all)
├── config/    never inside the web root: config.ini
├── src/       never inside the web root: the site's shared code
├── vendor/    never inside the web root: the Markdown library
└── data/      never inside the web root: the SQLite databases
```

The editor **has no password of its own**, by design: it is protected with the server's standard tools, or with the authentication that already protects your other tools.

| Your situation | What to do |
|---|---|
| VPS with Caddy, nginx, Traefik… (recommended) | site and editor on two separate subdomains, the editor behind a forward-auth (Tinyauth, Authelia…) **or** Basic authentication (mode 3, `Caddyfile.example`) |
| Apache hosting, with the editor | protect the editor's folder with `.htaccess` and `.htpasswd` (mode 2, `admin/htaccess.example`) |
| A site without an online editor, the safest | don't upload `admin/`; edit the database at home with DB Browser for SQLite, then upload it by FTP (mode 1) |
| In every case | the check from outside ("Protecting the editor"): the editor must answer `401` or `302` |

## Trying it on your computer

With PHP 8.1 or later (`pdo_sqlite` and `gd` extensions):

```sh
git clone https://github.com/Quintilien/cms_lite.git
cd cms_lite
php -d upload_max_filesize=20M -d post_max_size=24M -S localhost:8080
```

| | Address |
|---|---|
| The site | http://localhost:8080/public/ |
| The editor | http://localhost:8080/admin/ |

No configuration needed: without `config/config.ini`, the site and the editor read `config/config.ini.example`, which points to the demo database. Locally, the editor is not protected by any password: it must not be reachable from the network (`localhost`, not `0.0.0.0`).

This command serves the **whole** repository, configuration and databases included: fine for a trial on your own machine, never on a server. In production, only `public/` is served.

To work on **your own database** instead of the demo:

```sh
cp data/cms_lite_demo.db data/cms_lite.db     # or an empty one: sqlite3 data/cms_lite.db < init/empty_database.sql
cp config/config.ini.example config/config.ini   # then, in config.ini: db = ../data/cms_lite.db
```

`config.ini`, `data/cms_lite.db`, and the images and attachments uploaded from the editor are ignored by Git: an update with `git pull` never touches them.

### Trying it with Docker (Mac, Windows, Linux)

`compose.local.yml` sets up the whole mode 3 architecture on your machine, with nothing but Docker: the same two PHP + Apache containers (the read-only site, the editor), with a Caddy in front playing the part of the VPS's Caddy, and a password instead of forward-auth.

```sh
docker compose -f compose.local.yml up -d --build   # first run: 1 to 2 min (PHP image)
```

On Linux, prefix `docker compose` commands with `sudo` (see mode 3, step 5).

| | Address | |
|---|---|---|
| Site | http://localhost:8081 | |
| Editor | http://localhost:8082 | trial account: `secretaire` / `essai-cms-lite` |

The editor is on its own port, as it is on its own host in production (`admin.…`). It is therefore the same setup, including the password prompt in the browser.

```sh
docker compose -f compose.local.yml logs -f     # logs (Ctrl+C to quit)
docker compose -f compose.local.yml down        # stop; the data stays in data/ and public/
```

- **Changing the trial password**: `docker run --rm caddy:2 caddy hash-password --plaintext 'new'`, then paste the hash into `Caddyfile.local` and restart (`docker compose -f compose.local.yml restart caddy`).
- **Database used**: the one in `config/config.ini`, or the demo otherwise. To keep the demo database untouched, create your own first (above).
- **Working folder**: preferably outside a synced folder (iCloud, Dropbox…), which copes badly with an SQLite database being written.
- **Permissions**: nothing to do on Mac and Windows (Docker Desktop handles it). On Linux, see the shared permissions of mode 3 (step 4).
- The ports are only open on the machine itself (`127.0.0.1`): nothing is visible from the network.

## Installing

| | For whom | Online editor | Editor protection |
|---|---|---|---|
| **3. VPS with Docker** (recommended) | everyone | yes | Caddy: forward-auth or password |
| **2. Classic hosting, with the editor** | everyone | yes | `.htaccess` (Apache) |
| **1. Classic hosting, without the editor** | tinkerers | no | — |

**One rule, in all three modes: only the content of `public/` is served on the web.** `config/`, `src/`, `vendor/` and `data/` stay **next to it**, at the same level, outside the web root. CMS Lite always looks for them there: this is not a recommendation, it is the only layout that works. The databases, the configuration and the code therefore cannot be downloaded, whatever the web server (Apache, nginx, Caddy…), with no special setting.

**Mode 3** is the recommended path for a home page for self-hosted tools: the editor sits behind the same authentication as the other tools, and the public site runs read-only. Mode 1 is not a cut-down version for all that: it is how CMS Lite has always worked, the one where the server cannot write anything, hence the safest. You can switch from one mode to another at any time, since the database is the same.

### Mode 3 — VPS with Docker (PHP + Apache containers, system Caddy)

Each container is a small self-contained web server (PHP 8.3 + Apache), whose web root is `public/` (the site) or `admin/` (the editor); `config/`, `src/`, `vendor/` and `data/` are mounted next to it, outside the web root. The Caddy **installed on the VPS** (outside Docker) passes requests to it with `reverse_proxy`, and handles HTTPS and authentication. Caddy does not need to see the files.

Two containers, from the same image:

| Container | Role | Port (VPS) | Access to `data/` |
|---|---|---|---|
| `cmslite-public` | the site | `127.0.0.1:8090` | read-only |
| `cmslite-admin` | the editor | `127.0.0.1:8091` | read and write |

1. Clone the repository on the VPS with your usual account, not as `root`: this account will do the updates, without `sudo`.
   ```sh
   cd /srv && sudo install -d -o "$USER" -g "$USER" cmslite
   git clone https://github.com/Quintilien/cms_lite.git cmslite
   cd cmslite
   ```
2. Prepare the configuration and the site's database (files ignored by Git, never overwritten by a `git pull`):
   ```sh
   cp compose.example.yml compose.yml            # then set CMS_PUBLIC_URL
   cp config/config.ini.example config/config.ini   # then set the db key
   cp data/cms_lite_demo.db data/cms_lite.db        # or an empty database, or an existing one
   ```
   For a new site rather than the demo: `sqlite3 data/cms_lite.db < init/empty_database.sql` (blank database, two pages; without `sqlite3`: `sudo apt install sqlite3`).
   In `config/config.ini`: `db = ../data/cms_lite.db`. Both containers use the database named in this file.
3. Check that the ports are free: `ss -ltn | grep -E ':809[01]\b'` (no output = free). Otherwise, change them in `compose.yml` *and* in the Caddyfile. **Second CMS Lite site on the same server**: also change the two `container_name` entries in `compose.yml` (for example `mysite_public` and `mysite_admin`), which must be unique on the machine, as well as the ports.
4. Shared write permissions. Two accounts write to `data/`, `public/images/uploads/` and `public/files/`: yours (for `git pull`) and Apache (`www-data`, uid **33**, for the editor). These directories are therefore given to the `www-data` group, writable by the group. The `data/` **directory** itself must be writable, because SQLite creates its journal there during each write:
   ```sh
   sudo chgrp -R www-data data public/images/uploads public/files
   sudo chmod -R g+w data public/images/uploads public/files
   sudo find data public/images/uploads public/files -type d -exec chmod g+s {} +
   ```
   The `g+s` makes every file created later in these directories inherit the `www-data` group. Do not use `chown` to `www-data`: your account could no longer write there, and `git pull` would fail.
5. Start the containers:
   ```sh
   sudo docker compose up -d --build
   ```
   On Debian, Docker requires `sudo`. The alternative, adding your account to the `docker` group, amounts in practice to giving it `root` rights without a password. So keep `sudo` for Docker, and Git without `sudo`, so that the repository's files stay in your name.
6. Add the blocks of `Caddyfile.example` to the VPS's Caddyfile (domain names, ports, protection variant, and if you wish the optional security headers block), then:
   ```sh
   sudo caddy validate --config /etc/caddy/Caddyfile && sudo systemctl reload caddy
   ```
7. **Do the check** described in "Protecting the editor".

**Updating**, from the installation directory:

```sh
umask 002 && git pull --ff-only
```

- `umask 002`: the files Git rewrites stay writable by the group, hence by Apache.
- `--ff-only`: if a file tracked by Git was modified on the server, the update stops cleanly instead of attempting a merge. Per-installation files (`config.ini`, `compose.yml`, the site's database, uploaded files, activated options) are ignored by Git and never touched.
- The code is mounted in the containers: files are served as soon as they are replaced, without a restart. `sudo docker compose up -d --build` is only needed if `php/` has changed, or if the changelog asks you to modify `compose.yml`.

**Restricted-access site.** `Caddyfile.example` also shows how to put the site itself behind a forward-auth. In that case, `/css/fonts/*` stays outside authentication: the editor, on the other host, loads the font without a cookie (CORS request), and the forward-auth would refuse it.

Each container is limited to 256 MB of memory and half a CPU, which is plenty, photo resizing included.

### Mode 2 — Classic hosting, with the editor (Apache, SFTP)

```
(account root)
├── config/                   outside the web root: config.ini
├── data/                     outside the web root: the SQLite databases
├── src/                      outside the web root: the site's shared code
├── vendor/                   outside the web root: the Markdown library
└── www/                      web root: the content of this repository's public/
    └── admin/                the content of this repository's admin/ (without editor-src/)
```

1. Upload the **content** of `public/` into the web root (often `www/`), and `config/`, `data/`, `src/` and `vendor/` **next to** the web root, not inside it. If the host lets you choose the site's root folder (OVH: "Multisite"; cPanel, Plesk: "Document root"), you can also upload the repository as is and set `public/` as the root.
2. Upload `admin/` **into** the web root. The editor detects by itself that it sits inside the site, and finds the database through `config/config.ini`, like the site.
3. Create `config/config.ini` from `config/config.ini.example` (`db` key).
4. **Protect `www/admin/`**: rename `admin/htaccess.example` to `.htaccess` and create the password file (instructions in the file), then **do the check** described in "Protecting the editor".
5. `data/`, `www/images/uploads/` and `www/files/` must be writable by PHP. On most shared hosts, PHP runs under the SFTP account, so this is already the case.
6. PHP 8.1 or later, with GD. If the host caps uploads at 2 or 8 MB, the `.htaccess` settings (or a `.user.ini`) allow up to 100 MB, when the host permits it.

What you lose compared with mode 3: the separation between public site and editor. Everything runs under the same PHP and the same user, and the public site is no longer read-only. For a small site, that is a reasonable trade-off, provided the password is strong, since Apache's Basic authentication does not limit attempts.

**Same origin.** In this mode, the site and the editor share the same address: booby-trapped content displayed on the site could act in the editor opened by an author. CMS Lite filters what the editor saves, but if the host allows it, **putting the editor on a separate subdomain** (for example `admin.example.com`, whose root is `admin/`) removes this risk at the source.

**Updating**: upload the folders listed in the changelog entry of the version.

### Mode 1 — Classic hosting, without the editor (shared hosting, FTP)

Like mode 2, **without** `admin/`: no editor, nothing to protect, and PHP 8.1 is enough (GD not needed).

You then update the site on your computer, in the database, with DB Browser for SQLite, and upload the database and images again by FTP.

### Depending on your server

The layout (only `public/` is served) protects the configuration, the code and the databases **on any web server**, with no special setting. What changes from one server to another is how to protect the editor.

| Server | Public site | Online editor |
|---|---|---|
| VPS with Docker | mode 3 | Caddy: forward-auth or Basic authentication |
| VPS without Docker (Apache, nginx or Caddy with PHP-FPM) | site web root = `public/` | a second site, on another subdomain, whose web root is `admin/`; the editor finds `public/` and `config/` next to it by itself. Protection as in mode 3 (forward-auth or Basic authentication) |
| Apache (shared or not) | modes 1 or 2 | `.htaccess` and `.htpasswd` (`admin/htaccess.example`) |
| Shared hosting without Apache (nginx, IIS…) | mode 1 | the host panel's folder protection (cPanel, Plesk: "Directory Privacy"); if there is none: no online editor, stay in mode 1 |
| Hosting where every uploaded file necessarily lands in the web root | Apache only, thanks to the `.htaccess` safety net; **not supported** with another server | same |

In every case, do the check described below, "Checking, after every installation".

## ⚠ Protecting the editor: required

**The editor contains no password, by design.** It never checks who is using it: it relies on the web server in front of it to let only authorised people through. If the `admin/` directory is reachable without protection, **anyone on the Internet can change, publish or delete pages, and upload images and files to the site.** There is no second barrier.

### The rules

1. **Protect the whole `admin/` directory**, not just `index.php`. The sensitive file is `api.php`: it saves, deletes and receives files. A bot will call it directly, without ever going through `index.php`.
2. **HTTPS is required.** With Basic authentication (Apache or Caddy), the password accompanies every request. Over HTTP, it travels in clear text.
3. **A long, unique password** (at least 16 characters, ideally generated). Apache and Caddy do not limit the number of attempts; a forward-auth such as Tinyauth does.
4. **The password file outside the web root**, so that it can never be downloaded.

### Three ways to do it

**Caddy with a forward-auth** (Tinyauth, Authelia…): `forward_auth` directive on the editor's host (see `Caddyfile.example`). This is the most comfortable solution: a login page, limited attempts, and one account for all protected services.

**Caddy alone**: Caddy's built-in Basic authentication.

```
admin.example.org {
   basic_auth {
      secretaire <hash produced by: caddy hash-password>
   }
   reverse_proxy 127.0.0.1:8091
}
```

(`basic_auth` directive since Caddy 2.8; `basicauth` in earlier versions.)

**Apache hosting** (SFTP, shared): `admin/htaccess.example`, to be renamed `.htaccess`. Instructions in the file.

```sh
htpasswd -c -B /path/outside/root/.htpasswd-admin secretaire   # -c: create; without -c to add an account
```

### Checking, after every installation or server change

From a computer **outside**, without being logged in:

```sh
curl -s -o /dev/null -w '%{http_code}\n' https://…/admin/
curl -s -o /dev/null -w '%{http_code}\n' -X POST 'https://…/admin/api.php?action=save'
```

In **mode 3**, the editor is at the root of its own name (for example `admin.example.org`), without `/admin/`:

```sh
curl -s -o /dev/null -w '%{http_code}\n' https://admin.example.org/
curl -s -o /dev/null -w '%{http_code}\n' -X POST 'https://admin.example.org/api.php?action=save'
```

| Code returned | Meaning |
|---|---|
| `401` | Protected: Basic authentication, or a forward-auth that answers 401 to a non-browser client (Tinyauth, for example) ✔ |
| `302` or `303` | Protected: redirect to the forward-auth login page ✔ |
| `200` on the first command | **Not protected**: the page list is visible ✘ |
| `403` on the second command | **Not protected**: the request reached `api.php`, which only refused it through its origin check. A better-disguised bot would get through ✘ |

Both commands must return `401` or `302`/`303`. If not, the protection does not cover the whole directory (or the whole name, in mode 3).

### Locally

No protection is needed as long as the server only listens on the machine itself (`php -S localhost:8080`). With `php -S 0.0.0.0:8080`, any device on the network (Wi-Fi included) can change the site.

## Configuration: `config.ini`

`config/config.ini` is read by the site and by the editor. It is specific to each installation and ignored by Git; `config/config.ini.example` serves as a template and as the default configuration. The `config/` folder is outside the web root: this file cannot be downloaded.

```ini
db = ../data/cms_lite.db
title = CMS Lite
```

- **`db`**: the site's database, relative to the served folder (`public/`, or `www/` on classic hosting): `../data/…` therefore means `data/`, next to it.
- **`title`**: the title shown in the browser tab.
- **`timezone`** (optional, 4.5.0): time zone of the dates CMS Lite produces (names of uploaded images), for example `timezone = Europe/Paris`. Default `Europe/Brussels`.
- **`debug`** (optional): `debug = 1` shows PHP errors on the pages, for the time of a diagnosis. By default, they are never shown: the messages reveal server paths and SQL queries.
- Options (see "Options") add their own keys: `doclist_allowed`, `external_db_allowed`.

The file is plain text, **UTF-8 without BOM**, the default encoding of today's text editors (Windows Notepad included). A BOM, if any, is ignored.

## Options

Two features from earlier versions became **options** in 4.7.0: they ship in `options/`, outside the site, and are only active once copied into the installation. The base CMS does not need them, and what is not installed does not need protecting.

| Option | What it does | Folder |
|---|---|---|
| **Web Documents Browser** | list and display the documents in a folder (PDF, Word, Markdown, HTML) | `options/web-documents-browser/` |
| **External database** | browse another SQLite database (catalogue, inventory…), read-only | `options/external-database/` |

Each folder has its README: activate, update, deactivate. An activated option is a **copy**: it does not follow `git pull`. When a version changes it, the changelog says so, and the copy must be redone.

## The editor

- The database keeps **exactly the structure of CMS Lite 3.1**. The editor writes `title`, `txt` and `pending` in `t_pages` and, since 4.5.0, the **page properties**: `categ` (category), `categ_pagenum` (order within the category, which drives the "Previous page" / "Next page" links), `author` and `date`. They are set in the "Page properties" panel, collapsed under the title; category and author suggest the values already in use. The date is free text ("2026-11-01", "1862"…), never filled in automatically. These fields are plain text: extra spaces removed, `<` and `>` refused.
- A page written with the editor starts with `<!--html-->` in `txt`. `page.php` then serves this HTML as is, without `t_conversion` or Markdown. Other pages go through the usual route: you can still paste Markdown in DB Browser, as before.
- Pages in the classic format appear in the editor, but cannot be edited or deleted there: they remain DB Browser's business.
- Columns produce CMS Lite's familiar grid (`row`, `one-half column`, `one-third column`, `two-thirds column`): exactly what `<x->`, `<xx->`, etc. produced.
- **Menu: one level only (`t_menu`).** The "Show in menu" box creates or removes the entry pointing to `page.php?id=N`. The ↑↓ arrows in the page list reorder `t_menu`, including entries created by hand (which can be moved, but neither renamed nor deleted from the editor). The label follows the page title, unless it was customised in DB Browser. **Submenus (`t_sub_menu`) and `HOMEPAGE` are never modified**: they remain DB Browser's business.
- **How links open** (set automatically on saving): a page of the site opens in the same tab, an attachment or an external site in a new tab.
- **Code**: "Code" button for a fragment within a sentence (file name, command; Ctrl+E), and "Code block" button for several lines, with spaces, indentation and blank lines kept. The content is shown as is: an HTML tag typed in a code block remains text.
- **Links**: the "Link" dialog accepts an address (website, e-mail or phone), or a page of the site chosen by its title. Addresses are completed automatically: `name@example.com` becomes `mailto:…`, `www.example.com` becomes `https://…`, and a number becomes `tel:…`. With no text selected, the link is inserted with readable text (page title or the address typed).
- **Callouts**: "Callout" button; inside the callout, the choice of colour (Info, Note, Success, Warning, Danger) and "Remove callout", which keeps the text.
- **Collapsible blocks**: type the question, then "Toggle block": the question becomes the title, and the answer is written in the block. With several paragraphs selected, they become the content, to be given a title afterwards. On the site, blocks appear collapsed and open on click, without JavaScript. When printed, they are all expanded (recent browsers).
- **Tables**: "Table" button (3 × 3 with a header row). Inside a table, tools add or remove rows and columns, toggle the header row or delete the table, and **Tab** moves to the next cell. No mouse resizing, no merged cells: the browser shares out the width, and tables scroll horizontally on phones. A table pasted from Word or a spreadsheet is reduced to its structure.
- **Where files go**: images in `public/images/uploads/`, attachments in `public/files/` (on classic hosting: `www/images/uploads/` and `www/files/`).
- **Images already on the site**: a page can also show images stored elsewhere in `images/` (for example `images/logo/…`), such as those of an older site taken over in the editor (4.6.0). Only simple relative paths are accepted (no `..`, no absolute path), as well as `https://` addresses. An image cannot carry a link: put the link in the text.
- **Attachments**: "Attachment" button, or drag the file into the text. Formats: PDF, docx, xlsx, odt, ods, **audio** (mp3, m4a) and **video** (mp4, webm), up to **100 MB**. The file is stored in `files/` under a readable name preceded by the date (`2026-09-25-programme-du-trimestre.pdf`), and a link is inserted, such as "Programme du trimestre (PDF, 240 KB)", whose text can be edited like ordinary text. For an **audio** or **video** file (4.7.0), a **player** is inserted above the link, which remains for downloading. The video takes the width of the column and never starts by itself. For long or numerous videos, dedicated hosting (Seafile, PeerTube…) and a plain link are a better fit. YouTube or Vimeo videos are not embedded in the page (that would mean allowing `<iframe>`, i.e. another site's content and trackers): use a link. Images have their own 25 MB ceiling, since they are resized in memory.

### Editor language

The editor is available in **French, Dutch and English**. The language is chosen in the banner, next to Auto / Light / Dark, and the choice is remembered by the browser. Otherwise, the editor uses the browser's language, then French.

The labels are in `admin/lang/`: one file per language (`fr.php`, `nl.php`, `en.php`). **Adding a language** means copying `fr.php` under the desired name (`de.php`, for example), translating the values while keeping the keys and the `%s`, then changing `_name`. The selector offers it straight away. A forgotten key shows in French.

Only the interface is translated, not the site's content. Small exception: the automatic label of an attachment ("Programme (PDF, 240 KB)") uses the units of the editor's language (Ko in French, kB in Dutch, KB in English). It can be edited like any other text.

## Look and feel

CMS Lite 4 ships with **`css/site.css`**, a single stylesheet (about 330 lines) that replaces `style.css`, `nav.css` and `editor.css`, on the same HTML markup:

- **Typeface**: Inter (OFL licence), hosted in `css/fonts/` (2 files, 100 KB). Rendering is identical on Mac, Windows, Linux, iPhone and Android, with no call to Google Fonts.
- **Headings**: all in CMS Lite blue `rgb(61,92,122)`, weight 400.
- **Grid**: `one-half`, `one-third` and `two-thirds` (editor and `t_conversion` tags), in flexbox. Columns stack below 700 px.
- **Menu**: same `nav.php`. It shows in one line on large screens, and becomes a "Menu ☰" button with collapsible submenus on phones.
- **Images**: the editor's sizes and positions, and CMS Lite's older keywords (`max480px`, `center`, `float-left`…). No text wrapping on phones.
- Scrolling tables on small screens, print stylesheet.
- **Automatic dark mode**, following the visitor's system setting (macOS, Windows, iOS, Android), with no button or JavaScript. Headings and links get lighter (`rgb(168,196,226)`), and images are slightly dimmed. The public site has no button. **The editor** has an **Auto / Light / Dark** selector in its banner, remembered by the browser: it then shows the page as a visitor sees it in that mode, which lets you check that images and logos work in both. All colours are declared once, with both values, thanks to the CSS `light-dark()` function. A browser too old to know it simply shows the site in light mode.

The stylesheet is declared in the `HTML_META_CSS` parameter (`t_parameters` table):

```html
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="css/site.css">
```

You can add your own stylesheets there (animations, slideshow, Ken Burns effect…): no themes to install, but a look that is entirely yours. **Keep your own stylesheets and images in `public/theme/`** (for example `theme/mysite.css`, `theme/banner.jpg`): this folder is ignored by Git, so never overwritten by an update, and `git status` stays clean. The editor automatically loads the stylesheets declared in `HTML_META_CSS`: its preview therefore follows the choice made for the site. Header, body and footer are set the same way in the `HEADER`, `BODY` and `FOOTER` parameters.

CMS Lite 3.1's older stylesheets (`style.css`, `nav.css`, `kenburns.css`, `animations.css`) are no longer shipped. A notable difference from `style.css`: it stretched **all** images to the page width, whereas `site.css` shows them at their natural size, never exceeding the available width.

### Starting from an existing CMS Lite 3.1 database

The structure is unchanged: a 3.1 database can be used as is. Just replace, in the `HTML_META_CSS` parameter, the older stylesheets with `css/site.css` (see above).

For **dark mode**, also check the `EXTERNAL_DB_LIST_TOP` parameter (external database option): in 3.1 databases, it forces light colours on the table rows, which become unreadable on a dark background. The demo database's version relies on `site.css`'s colours:

```html
<style>tr:nth-child(even) > td { background: var(--wash); }</style>
<p style="text-align: center"><small>(Control-F pour rechercher)</small></p>
<table>
```

Existing pages stay in the classic format. For a page to move into the editor, it has to be recreated in the editor.

## ⚠ Editor and DB Browser on the same site

As soon as the editor is online, the server's database becomes the reference: download it just before editing it in DB Browser, upload it again right away, and nobody saves anything in the editor in between. Otherwise, uploading the database overwrites the pages saved in the meantime.

## Backup

The database (`data/cms_lite.db`), `config/config.ini`, `public/images/uploads/` and `public/files/`, nothing else (`data/sessions/`, the editor's sessions since 4.7.3, needs no backup). To copy the database safely while the editor is running:

```sh
sqlite3 data/cms_lite.db ".backup '/path/backup/cms_lite-$(date +%F).db'"
```

## Known limitations

- A draft is not visible on the site (that is what `pending` means), including through the "View page" link. Untick it to see the result.
- Deleting a page or removing a link does not delete the corresponding image or attachment. Orphan files in `images/uploads/` and `files/` are cleaned up by hand (the dated name helps spot the old ones).
- Deleting a page also deletes its menu entry. However, a page that a submenu or `HOMEPAGE` points to cannot be deleted from the editor.
- A page turned into a draft is removed from the menu (otherwise the link would lead to an empty page). When published again, it comes back **in last position and under its title**: a custom label or a chosen position have to be redone.
- With Docker, `LOGFILE` must stay at 0: the public container accesses the database read-only.

## Security in brief

- **Authentication**: entirely upstream (Apache, Caddy, forward-auth). The editor contains no authentication code, and an unauthenticated request never reaches PHP.
- **CSRF** (*OWASP CSRF Prevention Cheat Sheet*): every POST request must carry the editor session's token ("synchronizer token", `X-CSRF-Token` header, 4.5.0) **and** an `Origin` header equal to the editor's host. Session cookie `HttpOnly`, `SameSite=Strict`, `Secure` over HTTPS.
- **HTML**: content goes through an allow-list (the tags, attributes and classes the editor produces; `http(s)`, `mailto` and `tel` links only). Everything else is removed on saving. Audio and video players only accept files from `files/`, without autoplay.
- **Attachments**: the extension is derived from the actual content, never from the uploaded name. A `.php` disguised as `.pdf` is refused, and no file can receive an executable extension.
- **Images**: the type is checked on the actual content (not on the extension), the image is re-encoded by GD (which removes stray data and EXIF metadata, GPS position included), limited to 1600 px wide and renamed.
- **Only `public/` is served**: configuration, shared code, libraries, databases and options are outside the web root, whatever the server. A `.htaccess` at the repository root acts as a safety net on Apache, in case the whole repository is dropped into a web root by mistake.
- **PHP errors** never shown (except `debug = 1`). **Headers** recommended by the *OWASP Secure Headers Project*: `nosniff` and `Referrer-Policy` on the site; on the editor, also a ban on displaying it in a frame (anti-clickjacking). **Recommended**: also set `X-Content-Type-Options: nosniff` on the server side, for the static files (`files/`, `images/uploads/`, and `Documents/` with the Web Documents Browser option) that PHP does not serve: `security_headers` block in `Caddyfile.example`, or `Header set X-Content-Type-Options nosniff` in Apache's `.htaccess`.
- **Text from the database** (titles, categories, authors, dates): shown as plain text on the site, never as HTML (4.5.0).
- **Version**: no longer shown on the site (the old `page.php?rev` was removed in 4.5.0); read it in the editor or in the `VERSION` file.
- **Options**: not installed by default. Their security rules are in their README.
- **Public site (Docker)**: database and files mounted read-only. The public code cannot write anything, even in case of a flaw.

## Rebuilding the editor

Only if you modify `admin/editor-src/main.js`. You need Node.js on your workstation, not on the server.

```sh
cd admin/editor-src
./build.sh        # produces admin/editor.js
```

Versions pinned in `package-lock.json`: Tiptap 3.31, esbuild 0.28.

## Repository contents

```
cms_lite/
├── README.md, README.fr.md        this document (English, French)
├── CHANGELOG.md, CHANGELOG.fr.md  version history (English, French)
├── VERSION                  version number
├── LICENSE                  GNU AGPL v3
├── compose.example.yml      mode 3: two PHP + Apache containers (site / editor), ports 127.0.0.1:8090 / 8091
├── Caddyfile.example        mode 3: blocks to add to the VPS's Caddy
├── compose.local.yml        complete local trial with Docker (Caddy + PHP), on localhost:8081 / 8082
├── Caddyfile.local          its Caddy configuration
├── php/                     PHP 8.3 + Apache + GD + EXIF image, and its configuration
├── data/                    OUTSIDE the web root
│   └── cms_lite_demo.db     demo database
├── init/empty_database.sql  empty database, in SQL (two pages, in the menu)
├── .htaccess, index.php     safety net (Apache) if the whole repository is served by mistake
├── config/                  OUTSIDE the web root: config.ini.example, and config.ini (local)
├── src/                     OUTSIDE the web root: config.php, config_ini.php, nav.php, footer.php, markdown_converter.php
├── vendor/                  OUTSIDE the web root: league/commonmark (Markdown), bundled:
│                            nothing to download at install time; composer.json and .lock next to it
├── public/                  THE web root: the site
│   ├── css/site.css         single stylesheet, and css/fonts/ (Inter)
│   ├── images/              site images; images/uploads/: images uploaded from the editor
│   ├── files/               attachments uploaded from the editor
│   ├── theme/               your own look (stylesheets, images), ignored by Git
│   └── …                    index.php, page.php, toc.php
├── options/                 OUTSIDE the site until copied (see "Options")
│   ├── web-documents-browser/   doclist.php, display*.php, demo documents
│   └── external-database/       extdb.php, "Bouquins" demo database
└── admin/                   MUST BE PROTECTED: the editor (PHP), compiled editor.js, lang/ (fr, nl, en), htaccess.example
    └── editor-src/          the editor's sources, to rebuild if needed
```

The code and its comments are in English; the demo content is in French.

## Versions

The installed version is shown in the `VERSION` file and at the bottom of the editor's page list.

Numbering **major.minor.patch** (semantic versioning): the last number changes for a fix (4.1.0 → 4.1.1), the second for new features (4.1.x → 4.2.0), the first for a redesign (4.x → 5.0.0).

Each version's details, with the steps to update, are in **[CHANGELOG.md](CHANGELOG.md)**.

## Licence

Copyright (C) 2024-2026 PhS & Quintilien

This program is free software, distributed under the terms of the **GNU Affero General Public License**, version 3 or (at your option) any later version. Full text in the `LICENSE` file. In short: you may use, modify and redistribute it, provided that any modified version stays under the same licence and that its source code is made available to its users, including when it is only used online, on a server.

Versions up to 3.1 were published under the GNU GPL version 2 or later.

Demo photos of the recipes: roast beef and Caesar salad from Wikimedia Commons, public domain (CC0) — [Roast beef dish](https://commons.wikimedia.org/wiki/File:Roast_beef_dish.jpg), [A Caesar salad](https://commons.wikimedia.org/wiki/File:A_Caesar_salad.jpg); crêpes from [Pexels](https://www.pexels.com/license/) and goat cheese salad from [Pixabay](https://pixabay.com/service/license-summary/) (free use, no attribution required; not covered by the AGPL); pound cake: free image bank, licence checked.

Logo: drawn for CMS Lite (stack of pages), lettering set in QTOptimum (QualiType, free licence), the counterpart of the Optima originally used. Illustrations of the demo: "Papy", a character created by PhS & Quintilien; images generated with ChatGPT (OpenAI) from text descriptions and earlier images of the character, then cut out for CMS Lite.

Third-party components, each under its own licence: [Tiptap](https://tiptap.dev/) (MIT), [league/commonmark](https://commonmark.thephpleague.com/) and its dependencies (BSD-3-Clause / MIT, see `vendor/`), [Inter](https://rsms.me/inter/) typeface (SIL Open Font License, `public/css/fonts/OFL.txt`).
