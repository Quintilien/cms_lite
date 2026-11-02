# CMS Lite — historique des versions

*[English](CHANGELOG.md) · Français*

Numérotation **majeure.mineure.correction** : le dernier chiffre change pour une correction, le deuxième pour de nouvelles fonctions, le premier pour une refonte. Les noms de fichiers et de sections cités sont ceux de chaque version.

**4.7.2** (2 novembre 2026)

- Site : les mots-clés de taille et de position des images hérités de CMS Lite 3 (`images/x.png# max320px`, `thumbnail`, `center`, `float-left`…) restent actifs sur une page passée par l'éditeur. L'éditeur écrit l'espace qui précède le mot-clé sous la forme `%20`, la forme correcte dans une adresse, et `site.css` ne reconnaissait que l'espace : l'image reprenait alors toute la largeur. Les deux formes sont reconnues.

Mettre à jour : `git pull` (mode 3) ; sinon, envoyer `public/css/site.css` (dans `www/css/`). Un site qui utilise encore les anciennes feuilles de style (`style.css` de la 3.1) a besoin des mêmes règles : voir `site.css`, section « Older CMS Lite conventions ».

**4.7.1** (1er novembre 2026)

Aucune fonction nouvelle : le dépôt passe à l'anglais, en vue de sa publication.

- `README.md` en anglais, `README.fr.md` en français, de même pour les options ; historique dans `CHANGELOG.md` / `CHANGELOG.fr.md`. Le README est réorganisé : l'installation recommandée (mode 3) vient en premier.
- Commentaires du code et des fichiers de configuration en anglais ; le code lui-même ne change pas, ni les libellés du site et de l'éditeur.
- Renommés : `options/base-externe/` → `options/external-database/`, `admin/htaccess.exemple` → `admin/htaccess.example`, `init/nouvelle_base.sql` → `init/empty_database.sql`, et dans `Caddyfile.example` le bloc `entetes_securite` → `security_headers` (un Caddyfile existant qui l'importe sous l'ancien nom continue de fonctionner : il contient sa propre copie).
- Filet de secours `.htaccess` (dépôt servi en entier par erreur) : couvre aussi `options/` et `README.fr.md`.

Mise à jour : `git pull` (mode 3) ; sinon, rien d'indispensable (fichiers de documentation et commentaires). Une option activée avant la 4.7.1 reste valable : ses fichiers ne changent pas.

**4.7.0** (1er novembre 2026)

- **Options.** Web Documents Browser (`doclist.php`, `display*.php`, `Documents/`) et la consultation d'une base externe (`extdb.php`, base « Bouquins ») quittent le site de base pour `options/`, avec leur README. Leur code commun quitte aussi `src/config.php`. `config.ini.example` se réduit à `db` et `title`.
- **Qui utilise l'une de ces fonctions** : après le `git pull`, ses fichiers ont disparu de `public/`. Les recopier selon le README de l'option (deux commandes) ; `config.ini` est inchangé.
- Éditeur : **lecteur vidéo** dans la page (mp4, webm), sur le modèle du lecteur audio. Pas de lecture automatique ; les vidéos d'autres sites (YouTube…) restent des liens.
- Démo : entrée de menu de la base externe retirée ; la page qui la décrit le signale.

Mise à jour : `git pull` (mode 3) ; sinon, téléverser `admin/`, `src/` et `public/css/` (dans `www/css/`). Les anciens `doclist.php`, `display*.php` et `extdb.php` restés dans `www/` ne fonctionnent plus avec le nouveau `src/config.php` : les remplacer par ceux de l'option si on s'en sert, sinon les supprimer.

**4.6.0** (1er novembre 2026)

- Démo : toutes les pages sont au **format de l'éditeur** et s'y ouvrent, sauf « Gâteau Quatre-Quarts », gardée en Markdown comme exemple du format classique. Mise en page, textes et images inchangés ; les encadrés deviennent ceux de l'éditeur.
- Éditeur : accepte les images rangées n'importe où dans `images/`, et plus seulement dans `images/uploads/` : une page reprise d'un ancien site garde ses illustrations.

Mise à jour : `git pull` (mode 3) ; sinon, téléverser `admin/` et `src/`. La base de démonstration change : sa propre base n'est pas touchée.

**4.5.2** (1er novembre 2026) — **correctif de sécurité, mise à jour recommandée**

Revue de tous les points d'entrée, par un relecteur indépendant :

- Éditeur : filtrage des liens renforcé ; titre de page affiché comme texte simple.
- `displayhtml.php` et `displaymd.php` n'affichent plus que leur type de fichier.
- `extdb.php`, `toc.php`, scripts publics : paramètres d'adresse inattendus traités sans erreur ; fiche inexistante signalée comme telle.
- Journal des visites (option `LOGFILE`) : adresse du visiteur fiable derrière un proxy, champs bornés.
- Éditeur : les erreurs internes ne sont plus détaillées à l'écran (journal seulement). Le numéro de version suit la mise à jour sans redémarrer le conteneur (`VERSION` n'est plus monté seul ; la ligne peut rester dans un `compose.yml` existant).
- README : sous-domaine distinct conseillé pour l'éditeur en mode 2 ; `nosniff` côté serveur recommandé ; `files` et `images/uploads` exclus de `doclist_allowed`.

Mise à jour : `git pull` (mode 3) ; sinon, téléverser `public/`, `src/` et `admin/`.

**4.5.1** (1er novembre 2026)

- Éditeur : la session (et donc le jeton anti-CSRF) démarre avant toute sortie HTML. En 4.5.0, sous Docker (PHP sans tampon de sortie), le jeton restait vide et tout enregistrement était refusé. Mise à jour : `git pull`, rien d'autre.

**4.5.0** (1er novembre 2026)

- Éditeur : panneau **« Propriétés de la page »** (catégorie, ordre dans la catégorie, auteur, date), replié sous le titre, avec les valeurs déjà utilisées en suggestion. Espaces superflus retirés à l'enregistrement.
- Éditeur : jeton anti-CSRF en plus du contrôle d'origine (session `cms_lite_admin`).
- Site : titres, catégories, auteurs et dates affichés comme texte simple. `?rev` retiré (il affichait la version à n'importe qui).
- `config.ini` : une seule lecture, commune au site et à l'éditeur (`src/config_ini.php`) ; format inchangé. Clé facultative `timezone` (défaut `Europe/Brussels`) : sous Docker, les noms des images envoyées étaient horodatés en UTC.
- Liste des pages : la colonne « État » ne se coupe plus.
- Démo : page « Transférer le site… » à jour (`config/`, UTF-8 sans BOM), titres sans tabulations parasites.
- Docker : l'éditeur monte aussi `src/` (lecture seule). **Ajouter la ligne dans son `compose.yml`** (voir ci-dessous).

**Passer de la 4.4 à la 4.5.0**

*Installation par Git (mode 3)* : `umask 002 && git pull --ff-only`, puis ajouter au service de l'éditeur, dans `compose.yml`, la ligne `- ./src:/var/www/src:ro` (comme dans `compose.example.yml`), et `sudo docker compose up -d`.

*Hébergement classique (modes 1 et 2)* : téléverser `public/`, `src/` et, en mode 2, `admin/`. Rien à changer dans `config.ini`.

**4.4.0** (30 octobre 2026) — **nouvelle disposition des fichiers**

- Seul `public/` (auparavant `www/`) est servi par le web. `config.ini` passe dans `config/`, le code commun (`config.php`, `nav.php`, `footer.php`, `markdown_converter.php`) dans `src/`, et `vendor/` ainsi que `composer.*` à la racine du dépôt : tous hors de la racine web, quel que soit le serveur. Noms de dossiers usuels en PHP (`public/`, `config/`, `src/`, comme le propose *pds/skeleton*).
- Erreurs PHP masquées par défaut sur tous les hébergements (clé `debug` de `config.ini`) ; en-têtes de sécurité posés par CMS Lite lui-même ; bloc facultatif d'en-têtes dans `Caddyfile.example`.
- `config.ini` en UTF-8 sans BOM (le conseil « ANSI pour IIS » est abandonné) ; une marque BOM est ignorée.
- Un court `README.md` dans `admin/`, `config/`, `data/` et `src/` rappelle ce qui doit être protégé ou rester hors de la racine web.
- Les bases, le contenu de `config.ini` et les pages sont inchangés : seuls les emplacements changent. Pages du site identiques, octet pour octet, à celles de la 4.3.3.
- Docker : `compose.example.yml` et `compose.local.yml` montent `public/`, `src/`, `config/` et `vendor/`. **Recopier les volumes dans son `compose.yml`.**

**Passer de la 4.3 à la 4.4.0**

*Installation par Git (mode 3)*, depuis le répertoire de l'installation :

```sh
sudo docker compose down
umask 002 && git pull --ff-only
# fichiers propres à l'installation, ignorés par Git, restés dans l'ancien www/ :
mv www/config.ini config/config.ini                          # s'il existe
mv www/images/uploads/* public/images/uploads/ 2>/dev/null
mv www/files/* public/files/ 2>/dev/null
find www -name .DS_Store -delete; find www -depth -type d -empty -delete
ls www 2>/dev/null                                           # doit être vide ou absent
sudo chgrp -R www-data public/images/uploads public/files
sudo chmod -R g+w public/images/uploads public/files
sudo find public/images/uploads public/files -type d -exec chmod g+s {} +
# puis reporter dans compose.yml les volumes de compose.example.yml
sudo docker compose up -d
```

*Hébergement classique (modes 1 et 2)* : téléverser la nouvelle disposition (voir « Installer » dans le README), déplacer `config.ini` dans `config/`, et supprimer de la racine web les anciens `config.ini`, `config.php`, `nav.php`, `footer.php`, `markdown_converter.php`, `composer.*` et `vendor/`.

**4.3.3** (29 octobre 2026)

- Docker : les erreurs PHP ne s'affichent plus sur les pages ; elles vont dans le journal du conteneur (`sudo docker logs cms_lite_demo_public`, ou le nom donné au conteneur). Jusqu'ici, l'image officielle de PHP, sans `php.ini`, les affichait, avec chemins du serveur et requêtes SQL. Reconstruire les images : `sudo docker compose up -d --build`.
- Hébergement classique : sans changement, c'est le réglage de l'hébergeur qui s'applique.

**4.3.2** (29 octobre 2026) — **correctif de sécurité, mise à jour vivement recommandée**

- Consultation de documents (`doclist.php`, `display*.php`, Web Documents Browser) et bases externes (`extdb.php`) : contrôles renforcés.
- Les versions 3.x et Web Documents Browser sont aussi concernées : y remplacer ces scripts et `config.php` par ceux d'une version 4.3.2 ou plus récente, ou à défaut supprimer ces scripts s'ils ne servent pas.

**4.3.1** (28 octobre 2026)

- Démonstration : logo en médaillon rond, sur fond transparent, lisible en mode sombre ; marge au-dessus du logo.
- Paramètre `EXTERNAL_DB_LIST_TOP` (base de démonstration et `init/nouvelle_base.sql`) : la liste d'une base externe utilise les couleurs de `site.css`, et reste lisible en mode sombre. Pour une base existante, voir « Partir d'une base CMS Lite 3.1 existante ».
- README : `sudo` devant les commandes `docker compose` (mode 3).

**4.3.0** (28 octobre 2026)

- Code source sous licence AGPL-3.0, dans un dépôt GitHub (privé jusqu'à la publication).
- En l'absence de `config.ini`, le site et l'éditeur lisent `config.ini.example` : le dépôt fonctionne dès le clonage, sur la base de démonstration.
- Fichiers de déploiement fournis comme modèles (`compose.example.yml`, `Caddyfile.example`) ; avec Docker, les deux conteneurs utilisent la base désignée par `config.ini`.

**4.2.0** (26 septembre 2026)

- Éditeur : **encadrés** en cinq couleurs (info, remarque, succès, avertissement, danger), et **blocs dépliables** (`<details>` natif : ouverture et fermeture par le navigateur, sans JavaScript). Sur le site, un bloc dépliable apparaît toujours replié. À l'impression, tous les blocs sont dépliés, et un encadré n'est pas coupé entre deux pages.

**4.1.0** (25 septembre 2026)

- Éditeur : tableaux, code et blocs de code, fenêtre de lien (pages du site par leur titre, e-mail, téléphone), lecteur audio, vidéos en pièce jointe (100 Mo), images en cinq positions (seule à gauche, centrée ou à droite, ou avec texte à côté).
- Éditeur en français, néerlandais et anglais, en mode clair, sombre ou automatique.
- Feuille de style unique `css/site.css` : police Inter hébergée sur le site, titres hiérarchisés, grille demi, tiers et deux tiers, mode sombre automatique, longueur de ligne limitée sauf à côté d'une image.
- Déploiement : deux conteneurs PHP + Apache (site en lecture seule, éditeur), derrière le Caddy du système.

**4.0.0** (25 septembre 2026)

- Première version avec éditeur : administration web (Tiptap) à côté de CMS Lite 3.1, sur la même base SQLite. `page.php` sert tel quel le HTML des pages marquées `<!--html-->`, et les pages classiques en Markdown restent inchangées.

**3.1** (août 2025) et antérieures : voir la page « Historique des versions » du site de démonstration.
