# CMS Lite 4

*[English](README.md) · Français*

**La page d'accueil de vos outils auto-hébergés** : un petit site qu'un non-informaticien met à jour dans son navigateur, dont tout le contenu tient dans un seul fichier SQLite, et qui laisse la gestion des comptes à l'authentification que vous avez déjà.

En PHP et SQLite, pour les sites de quelques dizaines de pages qui n'ont pas besoin de WordPress : pas de serveur de base de données à installer, pas d'extensions ni de thèmes à installer et à mettre à jour, pas de comptes d'utilisateurs. L'apparence reste entièrement libre : en-tête, pied de page et feuilles de style se règlent dans la base. Tout le site tient dans une base SQLite et un répertoire d'images.

CMS Lite s'adresse à deux publics, qui travaillent sur la même base sans se gêner :

- **Ceux qui ne sont pas informaticiens** disposent d'un **éditeur en ligne**, façon Docmost (Tiptap) : titres, listes, liens, images glissées ou collées, colonnes, tableaux, encadrés, pièces jointes, audio et vidéo. De quoi tenir le site à jour sans rien installer.
- **Ceux qui aiment bricoler** gèrent tout « hors ligne » : les pages en Markdown, les menus et sous-menus, l'en-tête, le pied de page et les feuilles de style dans la base, avec [DB Browser for SQLite](https://sqlitebrowser.org/), puis l'envoi sur l'hébergement par FTP. C'est le fonctionnement d'origine de CMS Lite, et l'éditeur reste facultatif.

Le dépôt contient une **base de démonstration** : le site fonctionne dès le téléchargement.

## En bref : ce qui est publié, ce qui est protégé

```
cms_lite/
├── public/    CE QUI EST PUBLIÉ : le seul dossier servi par le web
├── admin/     CE QUI EST PROTÉGÉ : l'éditeur, jamais en accès libre (ou pas téléversé du tout)
├── config/    jamais dans la racine web : config.ini
├── src/       jamais dans la racine web : le code commun du site
├── vendor/    jamais dans la racine web : la bibliothèque Markdown
└── data/      jamais dans la racine web : les bases SQLite
```

L'éditeur **n'a aucun mot de passe propre**, et c'est voulu : on le protège avec les outils standard du serveur, ou avec l'authentification qui protège déjà vos autres outils.

| Votre situation | Ce qu'il faut faire |
|---|---|
| VPS avec Caddy, nginx, Traefik… (recommandé) | le site et l'éditeur sur deux sous-domaines distincts, l'éditeur derrière un forward-auth (Tinyauth, Authelia…) **ou** une authentification Basic (mode 3, `Caddyfile.example`) |
| Hébergement Apache, avec éditeur | protéger le dossier de l'éditeur par `.htaccess` et `.htpasswd` (mode 2, `admin/htaccess.example`) |
| Un site sans éditeur en ligne, le plus sûr | ne pas téléverser `admin/` ; modifier la base chez soi avec DB Browser for SQLite, puis l'envoyer par FTP (mode 1) |
| Dans tous les cas | la vérification depuis l'extérieur (« Protéger l'éditeur ») : l'éditeur doit répondre `401` ou `302` |

## Essayer sur son ordinateur

Avec PHP 8.1 ou plus (extensions `pdo_sqlite` et `gd`) :

```sh
git clone https://github.com/Quintilien/cms_lite.git
cd cms_lite
php -d upload_max_filesize=20M -d post_max_size=24M -S localhost:8080
```

| | Adresse |
|---|---|
| Le site | http://localhost:8080/public/ |
| L'éditeur | http://localhost:8080/admin/ |

Aucune configuration n'est nécessaire : en l'absence de `config/config.ini`, le site et l'éditeur lisent `config/config.ini.example`, qui désigne la base de démonstration. En local, l'éditeur n'est protégé par aucun mot de passe : il ne doit pas être joignable depuis le réseau (`localhost`, pas `0.0.0.0`).

Ce lancement sert **tout** le dépôt, configuration et bases comprises : il convient pour un essai sur sa propre machine, jamais sur un serveur. En production, seul `public/` est servi.

Pour travailler sur **sa propre base** plutôt que sur la démonstration :

```sh
cp data/cms_lite_demo.db data/cms_lite.db     # ou une base vierge : sqlite3 data/cms_lite.db < init/empty_database.sql
cp config/config.ini.example config/config.ini   # puis, dans config.ini : db = ../data/cms_lite.db
```

`config.ini`, `data/cms_lite.db`, ainsi que les images et pièces jointes envoyées depuis l'éditeur, sont ignorés par Git : une mise à jour par `git pull` ne les touche jamais.

### Essai avec Docker (Mac, Windows, Linux)

`compose.local.yml` monte toute l'architecture du mode 3 sur la machine, sans rien d'autre que Docker : les deux mêmes conteneurs PHP + Apache (le site en lecture seule, l'éditeur), avec un Caddy devant qui joue le rôle du Caddy du VPS, et un mot de passe à la place du forward-auth.

```sh
docker compose -f compose.local.yml up -d --build   # premier lancement : 1 à 2 min (image PHP)
```

Sous Linux, préfixer les commandes `docker compose` par `sudo` (voir le mode 3, point 5).

| | Adresse | |
|---|---|---|
| Site | http://localhost:8081 | |
| Éditeur | http://localhost:8082 | compte d'essai : `secretaire` / `essai-cms-lite` |

L'éditeur est sur son propre port, comme il est sur son propre hôte en production (`admin.…`). C'est donc la même configuration, y compris le mot de passe demandé par le navigateur.

```sh
docker compose -f compose.local.yml logs -f     # journaux (Ctrl+C pour quitter)
docker compose -f compose.local.yml down        # arrêt ; les données restent dans data/ et public/
```

- **Changer le mot de passe d'essai** : `docker run --rm caddy:2 caddy hash-password --plaintext 'nouveau'`, puis coller l'empreinte dans `Caddyfile.local` et relancer (`docker compose -f compose.local.yml restart caddy`).
- **Base utilisée** : celle de `config/config.ini`, ou la démonstration à défaut. Pour ne pas modifier la base de démonstration, créer d'abord sa propre base (ci-dessus).
- **Dossier de travail** : de préférence hors d'un dossier synchronisé (iCloud, Dropbox…), qui supporte mal une base SQLite en cours d'écriture.
- **Droits** : rien à faire sur Mac et Windows (Docker Desktop s'en charge). Sous Linux, voir les droits partagés du mode 3 (point 4).
- Les ports ne sont ouverts que sur la machine elle-même (`127.0.0.1`) : rien n'est visible depuis le réseau.

## Installer

| | Pour qui | Éditeur en ligne | Protection de l'éditeur |
|---|---|---|---|
| **3. VPS avec Docker** (recommandé) | tous | oui | Caddy : forward-auth ou mot de passe |
| **2. Hébergement classique, avec éditeur** | tous | oui | `.htaccess` (Apache) |
| **1. Hébergement classique, sans éditeur** | bricoleurs | non | — |

**Une seule règle, dans les trois modes : seul le contenu de `public/` est servi par le web.** `config/`, `src/`, `vendor/` et `data/` restent **à côté**, au même niveau, hors de la racine web. CMS Lite les cherche toujours à cet endroit : ce n'est pas une recommandation, c'est la seule disposition qui fonctionne. Les bases, la configuration et le code ne peuvent donc pas être téléchargés, quel que soit le serveur web (Apache, nginx, Caddy…), sans réglage particulier.

Le **mode 3** est le chemin recommandé pour une page d'accueil d'outils auto-hébergés : l'éditeur se range derrière la même authentification que les autres outils, et le site public tourne en lecture seule. Le mode 1 n'est pas une version au rabais pour autant : c'est le fonctionnement d'origine, celui où le serveur ne peut rien écrire, donc le plus sûr. On peut passer d'un mode à l'autre à tout moment, puisque la base est la même.

### Mode 3 — VPS avec Docker (conteneurs PHP + Apache, Caddy du système)

Chaque conteneur est un petit serveur web autonome (PHP 8.3 + Apache), dont la racine web est `public/` (le site) ou `admin/` (l'éditeur) ; `config/`, `src/`, `vendor/` et `data/` sont montés à côté, hors de la racine web. Le Caddy **installé sur le VPS** (hors Docker) lui passe les requêtes en `reverse_proxy`, gère HTTPS et l'authentification. Caddy n'a pas besoin de voir les fichiers.

Deux conteneurs, de la même image :

| Conteneur | Rôle | Port (VPS) | Accès à `data/` |
|---|---|---|---|
| `cmslite-public` | le site | `127.0.0.1:8090` | lecture seule |
| `cmslite-admin` | l'éditeur | `127.0.0.1:8091` | lecture et écriture |

1. Cloner le dépôt sur le VPS avec son compte habituel, pas en `root` : c'est ce compte qui fera les mises à jour, sans `sudo`.
   ```sh
   cd /srv && sudo install -d -o "$USER" -g "$USER" cmslite
   git clone https://github.com/Quintilien/cms_lite.git cmslite
   cd cmslite
   ```
2. Préparer la configuration et la base du site (fichiers ignorés par Git, jamais écrasés par un `git pull`) :
   ```sh
   cp compose.example.yml compose.yml            # puis régler CMS_PUBLIC_URL
   cp config/config.ini.example config/config.ini   # puis régler la clé db
   cp data/cms_lite_demo.db data/cms_lite.db        # ou une base vierge, ou une base existante
   ```
   Pour un site neuf, plutôt que la démo : `sqlite3 data/cms_lite.db < init/empty_database.sql` (base vierge, deux pages ; sans `sqlite3` : `sudo apt install sqlite3`).
   Dans `config/config.ini` : `db = ../data/cms_lite.db`. Les deux conteneurs utilisent la base désignée par ce fichier.
3. Vérifier que les ports sont libres : `ss -ltn | grep -E ':809[01]\b'` (aucune réponse = libres). Sinon, les changer dans `compose.yml` *et* dans le Caddyfile. **Deuxième site CMS Lite sur le même serveur** : changer aussi les deux `container_name` de `compose.yml` (par exemple `monsite_public` et `monsite_admin`), qui doivent être uniques sur la machine, en plus des ports.
4. Droits d'écriture partagés. Deux comptes écrivent dans `data/`, `public/images/uploads/` et `public/files/` : le vôtre (pour `git pull`) et Apache (`www-data`, uid **33**, pour l'éditeur). On donne donc ces répertoires au groupe `www-data`, en écriture pour le groupe. Le **répertoire** `data/` doit lui-même être accessible en écriture, car SQLite y crée son journal pendant chaque écriture :
   ```sh
   sudo chgrp -R www-data data public/images/uploads public/files
   sudo chmod -R g+w data public/images/uploads public/files
   sudo find data public/images/uploads public/files -type d -exec chmod g+s {} +
   ```
   Le `g+s` fait hériter le groupe `www-data` à tout fichier créé ensuite dans ces répertoires. Ne pas utiliser `chown` vers `www-data` : votre compte ne pourrait plus y écrire, et le `git pull` échouerait.
5. Lancer les conteneurs :
   ```sh
   sudo docker compose up -d --build
   ```
   Sous Debian, Docker demande `sudo`. L'autre solution, ajouter son compte au groupe `docker`, revient en pratique à lui donner les droits de `root` sans mot de passe. On garde donc `sudo` pour Docker, et Git sans `sudo`, pour que les fichiers du dépôt restent à votre nom.
6. Ajouter au Caddyfile du VPS les blocs de `Caddyfile.example` (noms de domaine, ports, variante de protection, et si on le souhaite le bloc facultatif d'en-têtes de sécurité), puis :
   ```sh
   sudo caddy validate --config /etc/caddy/Caddyfile && sudo systemctl reload caddy
   ```
7. **Faire la vérification** décrite dans « Protéger l'éditeur ».

**Mettre à jour**, depuis le répertoire de l'installation :

```sh
umask 002 && git pull --ff-only
```

- `umask 002` : les fichiers que Git réécrit restent modifiables par le groupe, donc par Apache.
- `--ff-only` : si un fichier suivi par Git a été modifié sur le serveur, la mise à jour s'arrête proprement au lieu de tenter une fusion. Les fichiers propres à l'installation (`config.ini`, `compose.yml`, la base du site, les fichiers envoyés, les options activées) sont ignorés par Git et ne sont jamais touchés.
- Le code est monté dans les conteneurs : les fichiers sont servis dès qu'ils sont remplacés, sans redémarrage. `sudo docker compose up -d --build` n'est nécessaire que si `php/` a changé, ou si l'historique demande de modifier `compose.yml`.

**Site à accès restreint.** `Caddyfile.example` montre aussi comment placer le site lui-même derrière un forward-auth. Dans ce cas, `/css/fonts/*` reste hors authentification : l'éditeur, sur l'autre hôte, charge la police sans cookie (requête CORS), et le forward-auth la refuserait.

Chaque conteneur est limité à 256 Mo de mémoire et à un demi-processeur, ce qui suffit largement, redimensionnement de photos compris.

### Mode 2 — Hébergement classique, avec éditeur (Apache, SFTP)

```
(racine du compte)
├── config/                   hors de la racine web : config.ini
├── data/                     hors de la racine web : les bases SQLite
├── src/                      hors de la racine web : le code commun du site
├── vendor/                   hors de la racine web : la bibliothèque Markdown
└── www/                      racine web : le contenu de public/ de ce dépôt
    └── admin/                le contenu de admin/ de ce dépôt (sans editor-src/)
```

1. Téléverser le **contenu** de `public/` dans la racine web (souvent `www/`), et `config/`, `data/`, `src/` et `vendor/` **à côté** de la racine web, pas dedans. Si l'hébergeur permet de choisir le dossier racine du site (OVH : « Multisite » ; cPanel, Plesk : « Document root »), on peut aussi téléverser le dépôt tel quel et désigner `public/` comme racine.
2. Téléverser `admin/` **dans** la racine web. L'éditeur détecte seul qu'il se trouve dans le site, et trouve la base via `config/config.ini`, comme le site.
3. Créer `config/config.ini` à partir de `config/config.ini.example` (clé `db`).
4. **Protéger `www/admin/`** : renommer `admin/htaccess.example` en `.htaccess` et créer le fichier de mots de passe (instructions dans le fichier), puis **faire la vérification** décrite dans « Protéger l'éditeur ».
5. `data/`, `www/images/uploads/` et `www/files/` doivent être accessibles en écriture par PHP. Sur la plupart des mutualisés, PHP tourne sous le compte SFTP, et c'est donc déjà le cas.
6. PHP 8.1 ou plus, avec GD. Si l'hébergeur plafonne les envois à 2 ou 8 Mo, les réglages de `.htaccess` (ou d'un `.user.ini`) permettent d'aller jusqu'à 100 Mo, quand il l'autorise.

Ce que l'on perd par rapport au mode 3 : la séparation entre site public et éditeur. Tout tourne sous le même PHP et le même utilisateur, et le site public n'est plus en lecture seule. Pour un petit site, c'est un compromis raisonnable, à condition que le mot de passe soit solide, puisque l'authentification Basic d'Apache ne limite pas les tentatives.

**Même origine.** Dans ce mode, le site et l'éditeur partagent la même adresse : un contenu piégé affiché sur le site pourrait agir dans l'éditeur ouvert par un rédacteur. CMS Lite filtre ce que l'éditeur enregistre, mais si l'hébergeur le permet, **placer l'éditeur sur un sous-domaine distinct** (par exemple `admin.exemple.be`, dont la racine est `admin/`) supprime ce risque à la source.

**Mettre à jour** : téléverser les dossiers indiqués par l'historique de la version.

### Mode 1 — Hébergement classique, sans éditeur (mutualisé, FTP)

Comme le mode 2, **sans** `admin/` : ni éditeur, ni protection à prévoir, et PHP 8.1 suffit (GD inutile).

On met ensuite le site à jour sur son ordinateur, dans la base, avec DB Browser for SQLite, puis on renvoie la base et les images par FTP.

### Selon votre serveur

La disposition (seul `public/` est servi) protège la configuration, le code et les bases **sur tout serveur web**, sans réglage particulier. Ce qui change d'un serveur à l'autre, c'est la façon de protéger l'éditeur.

| Serveur | Site public | Éditeur en ligne |
|---|---|---|
| VPS avec Docker | mode 3 | Caddy : forward-auth ou authentification Basic |
| VPS sans Docker (Apache, nginx ou Caddy avec PHP-FPM) | racine web du site = `public/` | un second site, sur un autre sous-domaine, dont la racine web est `admin/` ; l'éditeur trouve seul `public/` et `config/` à côté. Protection comme en mode 3 (forward-auth ou authentification Basic) |
| Apache (mutualisé ou non) | modes 1 ou 2 | `.htaccess` et `.htpasswd` (`admin/htaccess.example`) |
| Mutualisé sans Apache (nginx, IIS…) | mode 1 | protection de dossier du panneau de l'hébergeur (cPanel, Plesk : « Répertoires protégés ») ; s'il n'en propose pas : pas d'éditeur en ligne, on reste en mode 1 |
| Hébergement où tout fichier déposé est forcément dans la racine web | Apache seulement, grâce au filet de secours `.htaccess` ; **non pris en charge** avec un autre serveur | idem |

Dans tous les cas, faire la vérification décrite ci-dessous, « Vérifier, après chaque installation ».

## ⚠ Protéger l'éditeur : obligatoire

**L'éditeur ne contient aucun mot de passe, et c'est voulu.** Il ne vérifie jamais qui l'utilise : il compte sur le serveur web placé devant lui pour ne laisser passer que les personnes autorisées. Si le répertoire `admin/` est accessible sans protection, **n'importe qui sur Internet peut modifier, publier ou supprimer des pages, et déposer des images et des fichiers sur le site.** Il n'y a aucune seconde barrière.

### Les règles

1. **Protéger tout le répertoire `admin/`**, pas seulement `index.php`. Le fichier sensible est `api.php` : c'est lui qui enregistre, supprime et reçoit les fichiers. Un robot l'appellera directement, sans jamais passer par `index.php`.
2. **HTTPS obligatoire.** Avec l'authentification Basic (Apache ou Caddy), le mot de passe accompagne chaque requête. En HTTP, il circule en clair.
3. **Un mot de passe long et unique** (au moins 16 caractères, idéalement généré). Apache et Caddy ne limitent pas le nombre d'essais ; un forward-auth comme Tinyauth, si.
4. **Le fichier de mots de passe hors de la racine web**, pour qu'il ne puisse jamais être téléchargé.

### Les trois façons de faire

**Caddy avec un forward-auth** (Tinyauth, Authelia…) : directive `forward_auth` sur l'hôte de l'éditeur (voir `Caddyfile.example`). C'est la solution la plus confortable : une page de connexion, les tentatives limitées, et un seul identifiant pour tous les services protégés.

**Caddy seul** : authentification Basic intégrée à Caddy.

```
admin.example.org {
   basic_auth {
      secretaire <empreinte produite par : caddy hash-password>
   }
   reverse_proxy 127.0.0.1:8091
}
```

(Directive `basic_auth` depuis Caddy 2.8 ; `basicauth` dans les versions antérieures.)

**Hébergement Apache** (SFTP, mutualisé) : `admin/htaccess.example`, à renommer en `.htaccess`. Mode d'emploi dans le fichier.

```sh
htpasswd -c -B /chemin/hors/racine/.htpasswd-admin secretaire   # -c : création ; sans -c pour ajouter un compte
```

### Vérifier, après chaque installation ou modification du serveur

Depuis un ordinateur **extérieur**, sans être connecté :

```sh
curl -s -o /dev/null -w '%{http_code}\n' https://…/admin/
curl -s -o /dev/null -w '%{http_code}\n' -X POST 'https://…/admin/api.php?action=save'
```

En **mode 3**, l'éditeur est à la racine de son propre nom (par exemple `admin.example.org`), sans `/admin/` :

```sh
curl -s -o /dev/null -w '%{http_code}\n' https://admin.example.org/
curl -s -o /dev/null -w '%{http_code}\n' -X POST 'https://admin.example.org/api.php?action=save'
```

| Code obtenu | Signification |
|---|---|
| `401` | Protégé : authentification Basic, ou forward-auth qui répond 401 à un client autre qu'un navigateur (Tinyauth, par exemple) ✔ |
| `302` ou `303` | Protégé : renvoi vers la page de connexion du forward-auth ✔ |
| `200` sur la première commande | **Non protégé** : la liste des pages est visible ✘ |
| `403` sur la seconde commande | **Non protégé** : la requête a atteint `api.php`, qui ne l'a refusée que par son contrôle d'origine. Un robot mieux déguisé passerait ✘ |

Les deux commandes doivent donner `401` ou `302`/`303`. Si ce n'est pas le cas, la protection ne couvre pas tout le répertoire (ou tout le nom, en mode 3).

### En local

Aucune protection n'est nécessaire tant que le serveur n'écoute que sur la machine elle-même (`php -S localhost:8080`). Avec `php -S 0.0.0.0:8080`, n'importe quel appareil du réseau (Wi-Fi compris) peut modifier le site.

## Configuration : `config.ini`

`config/config.ini` est lu par le site et par l'éditeur. Il est propre à chaque installation et ignoré par Git ; `config/config.ini.example` sert de modèle et de configuration par défaut. Le dossier `config/` est hors de la racine web : ce fichier ne peut pas être téléchargé.

```ini
db = ../data/cms_lite.db
title = CMS Lite
```

- **`db`** : la base du site, relative au dossier servi (`public/`, ou `www/` sur un hébergement classique) : `../data/…` désigne donc `data/`, à côté.
- **`title`** : le titre affiché dans l'onglet du navigateur.
- **`timezone`** (facultatif, 4.5.0) : fuseau horaire des dates produites par CMS Lite (noms des images envoyées), par exemple `timezone = Europe/Paris`. Par défaut `Europe/Brussels`.
- **`debug`** (facultatif) : `debug = 1` affiche les erreurs PHP sur les pages, le temps d'un diagnostic. Par défaut, elles ne s'affichent jamais : les messages révèlent les chemins du serveur et des requêtes SQL.
- Les options (voir « Options ») ajoutent leurs propres clés : `doclist_allowed`, `external_db_allowed`.

Le fichier est en texte brut, **UTF-8 sans BOM**, l'encodage par défaut des éditeurs de texte actuels (Bloc-notes de Windows compris). Une marque BOM éventuelle est ignorée.

## Options

Deux fonctions, présentes dans les versions antérieures, sont devenues des **options** en 4.7.0 : elles sont livrées dans `options/`, hors du site, et ne sont actives qu'une fois copiées dans l'installation. Le CMS de base n'en a pas besoin, et ce qui n'est pas installé n'a pas à être protégé.

| Option | Ce qu'elle fait | Dossier |
|---|---|---|
| **Web Documents Browser** | lister et afficher les documents d'un répertoire (PDF, Word, Markdown, HTML) | `options/web-documents-browser/` |
| **Base externe** | consulter une autre base SQLite (catalogue, inventaire…), en lecture seule | `options/external-database/` |

Chaque dossier a son README (`README.fr.md` en français) : activer, mettre à jour, désactiver. Une option activée est une **copie** : elle ne suit pas `git pull`. Quand une version la modifie, l'historique le signale, et il faut refaire la copie.

## L'éditeur

- La base garde **exactement la structure de CMS Lite 3.1**. L'éditeur écrit `title`, `txt` et `pending` dans `t_pages` et, depuis la 4.5.0, les **propriétés de la page** : `categ` (catégorie), `categ_pagenum` (ordre dans la catégorie, qui commande les liens « Page précédente » / « Page suivante »), `author` et `date`. Elles se règlent dans le panneau « Propriétés de la page », replié sous le titre ; la catégorie et l'auteur proposent les valeurs déjà utilisées. La date est du texte libre (« 2026-11-01 », « 1862 »…), jamais remplie automatiquement. Ces champs sont du texte simple : espaces superflus retirés, caractères `<` et `>` refusés.
- Une page rédigée avec l'éditeur commence par `<!--html-->` dans `txt`. `page.php` sert alors ce HTML tel quel, sans `t_conversion` ni Markdown. Les autres pages passent par le circuit habituel : on peut toujours coller du Markdown dans DB Browser, comme avant.
- Les pages au format classique apparaissent dans l'éditeur, mais ne s'y modifient pas et ne s'y suppriment pas : elles restent du ressort de DB Browser.
- Les colonnes produisent la grille déjà connue de CMS Lite (`row`, `one-half column`, `one-third column`, `two-thirds column`) : exactement ce que donnaient `<x->`, `<xx->`, etc.
- **Menu : un seul niveau (`t_menu`).** La case « Afficher dans le menu » crée ou retire l'entrée qui pointe vers `page.php?id=N`. Les flèches ↑↓ de la liste des pages réordonnent `t_menu`, y compris les entrées créées à la main (qui peuvent être déplacées, mais ni renommées ni supprimées depuis l'éditeur). Le libellé suit le titre de la page, sauf s'il a été personnalisé dans DB Browser. **Les sous-menus (`t_sub_menu`) et `HOMEPAGE` ne sont jamais modifiés** : ils restent du ressort de DB Browser.
- **Ouverture des liens** (réglée automatiquement à l'enregistrement) : une page du site s'ouvre dans le même onglet, une pièce jointe ou un site externe dans un nouvel onglet.
- **Code** : bouton « Code » pour un fragment dans une phrase (nom de fichier, commande ; Ctrl+E), et bouton « Bloc de code » pour plusieurs lignes, avec espaces, indentation et lignes vides conservés. Le contenu s'affiche tel quel : une balise HTML tapée dans un bloc de code reste du texte.
- **Liens** : la fenêtre du bouton « Lien » accepte une adresse (site, e-mail ou téléphone), ou une page du site choisie par son titre. Les adresses sont complétées automatiquement : `nom@exemple.be` devient `mailto:…`, `www.exemple.be` devient `https://…`, et un numéro devient `tel:…`. Sans texte sélectionné, le lien est inséré avec un texte lisible (titre de la page ou adresse tapée).
- **Encadrés** : bouton « Encadré » ; dans l'encadré, le choix de la couleur (Info, Remarque, Succès, Avertissement, Danger) et « Retirer l'encadré », qui garde le texte.
- **Blocs dépliables** : on tape la question, puis « Bloc dépliable » : la question devient le titre, et la réponse s'écrit dans le bloc. Avec plusieurs paragraphes sélectionnés, ils deviennent le contenu, à titrer ensuite. Sur le site, les blocs apparaissent repliés et s'ouvrent au clic, sans JavaScript. À l'impression, ils sont tous dépliés (navigateurs récents).
- **Tableaux** : bouton « Tableau » (3 × 3 avec ligne d'en-tête). Dans un tableau, des outils ajoutent ou retirent lignes et colonnes, activent la ligne d'en-tête ou suppriment le tableau, et **Tab** passe à la cellule suivante. Ni redimensionnement à la souris, ni fusion de cellules : le navigateur répartit la largeur, et les tableaux défilent horizontalement sur téléphone. Un tableau collé depuis Word ou un tableur est ramené à sa structure.
- **Où vont les fichiers** : images dans `public/images/uploads/`, pièces jointes dans `public/files/` (sur un hébergement classique : `www/images/uploads/` et `www/files/`).
- **Images déjà sur le site** : une page peut aussi afficher les images rangées ailleurs dans `images/` (par exemple `images/logo/…`), comme celles d'un ancien site repris dans l'éditeur (4.6.0). Seuls les chemins relatifs simples sont acceptés (ni `..`, ni chemin absolu), ainsi que les adresses `https://`. Une image ne peut pas porter de lien : on met le lien dans le texte.
- **Pièces jointes** : bouton « Pièce jointe », ou glisser le fichier dans le texte. Formats : PDF, docx, xlsx, odt, ods, **audio** (mp3, m4a) et **vidéo** (mp4, webm), jusqu'à **100 Mo**. Le fichier est rangé dans `files/` sous un nom lisible précédé de la date (`2026-09-25-programme-du-trimestre.pdf`), et un lien est inséré, du type « Programme du trimestre (PDF, 240 Ko) », dont le texte se modifie comme du texte ordinaire. Pour un fichier **audio** ou **vidéo** (4.7.0), un **lecteur** est inséré au-dessus du lien, qui reste pour télécharger. La vidéo occupe la largeur de la colonne et ne démarre jamais seule. Pour des vidéos longues ou nombreuses, un hébergement dédié (Seafile, PeerTube…) et un simple lien conviennent mieux. Les vidéos YouTube ou Vimeo ne s'intègrent pas dans la page (il faudrait autoriser les `<iframe>`, c'est-à-dire le contenu et les traceurs d'un autre site) : on met un lien. Les images ont leur propre plafond de 25 Mo, puisqu'elles sont redimensionnées en mémoire.

### Langue de l'éditeur

L'éditeur existe en **français, néerlandais et anglais**. La langue se choisit dans le bandeau, à côté de Auto / Clair / Sombre, et le choix est mémorisé par le navigateur. À défaut, l'éditeur prend la langue du navigateur, puis le français.

Les libellés sont dans `admin/lang/` : un fichier par langue (`fr.php`, `nl.php`, `en.php`). **Ajouter une langue** revient à copier `fr.php` sous le nom voulu (`de.php`, par exemple), à traduire les valeurs en gardant les clés et les `%s`, puis à changer `_name`. Le sélecteur la propose aussitôt. Une clé oubliée s'affiche en français.

Seule l'interface est traduite, pas le contenu du site. Petite exception : le libellé automatique d'une pièce jointe (« Programme (PDF, 240 Ko) ») utilise les unités de la langue de l'éditeur (Ko en français, kB en néerlandais, KB en anglais). Il reste modifiable comme n'importe quel texte.

## Apparence

CMS Lite 4 est livré avec **`css/site.css`**, une feuille unique (environ 330 lignes) qui remplace `style.css`, `nav.css` et `editor.css`, sur le même balisage HTML :

- **Police** : Inter (licence OFL), hébergée dans `css/fonts/` (2 fichiers, 100 Ko). Le rendu est identique sur Mac, Windows, Linux, iPhone et Android, sans aucun appel à Google Fonts.
- **Titres** : tous en bleu CMS Lite `rgb(61,92,122)`, graisse 400.
- **Grille** : `one-half`, `one-third` et `two-thirds` (éditeur et balises de `t_conversion`), en flexbox. Les colonnes s'empilent sous 700 px.
- **Menu** : même `nav.php`. Il s'affiche en ligne sur grand écran, et devient un bouton « Menu ☰ » avec sous-menus dépliables sur téléphone.
- **Images** : tailles et positions de l'éditeur, et anciens mots-clés CMS Lite (`max480px`, `center`, `float-left`…). Plus aucun habillage sur téléphone.
- Tableaux défilants sur petit écran, feuille d'impression.
- **Mode sombre automatique**, selon le réglage du système du visiteur (macOS, Windows, iOS, Android), sans bouton ni JavaScript. Titres et liens s'éclaircissent (`rgb(168,196,226)`), et les images sont légèrement atténuées. Le site public n'a pas de bouton. **L'éditeur** a un sélecteur **Auto / Clair / Sombre** dans son bandeau, retenu par le navigateur : il montre alors la page telle que la voit un visiteur dans ce mode, ce qui permet de vérifier qu'images et logos passent dans les deux. Toutes les couleurs sont déclarées une seule fois, avec leurs deux valeurs, grâce à la fonction CSS `light-dark()`. Un navigateur trop ancien pour la connaître affiche simplement le site en clair.

La feuille est déclarée dans le paramètre `HTML_META_CSS` (table `t_parameters`) :

```html
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="css/site.css">
```

On peut y ajouter ses propres feuilles (animations, diaporama, effet Ken Burns…) : pas de thèmes à installer, mais une apparence entièrement libre. **Rangez vos feuilles et vos images d'habillage dans `public/theme/`** (par exemple `theme/monsite.css`, `theme/bandeau.jpg`) : ce dossier est ignoré par Git, donc jamais écrasé par une mise à jour, et `git status` reste propre. L'éditeur charge automatiquement les feuilles déclarées dans `HTML_META_CSS` : son aperçu suit donc le choix fait pour le site. L'en-tête, le corps et le pied de page se règlent de même dans les paramètres `HEADER`, `BODY` et `FOOTER`.

Les anciennes feuilles de CMS Lite 3.1 (`style.css`, `nav.css`, `kenburns.css`, `animations.css`) ne sont plus livrées. Différence notable avec `style.css` : celui-ci étirait **toutes** les images à la largeur de la page, alors que `site.css` les affiche à leur taille naturelle, sans jamais dépasser la largeur disponible.

### Partir d'une base CMS Lite 3.1 existante

La structure est inchangée : on peut utiliser une base 3.1 telle quelle. Il suffit de remplacer, dans le paramètre `HTML_META_CSS`, les anciennes feuilles par `css/site.css` (voir ci-dessus).

Pour le **mode sombre**, vérifier aussi le paramètre `EXTERNAL_DB_LIST_TOP` (option base externe) : dans les bases 3.1, il impose des couleurs claires aux lignes du tableau, qui deviennent illisibles sur fond sombre. La version de la base de démonstration s'appuie sur les couleurs de `site.css` :

```html
<style>tr:nth-child(even) > td { background: var(--wash); }</style>
<p style="text-align: center"><small>(Control-F pour rechercher)</small></p>
<table>
```

Les pages existantes restent au format classique. Pour qu'une page passe dans l'éditeur, il faut la recréer dans l'éditeur.

## ⚠ Éditeur et DB Browser sur le même site

Dès que l'éditeur est en ligne, la base du serveur devient la référence : on la télécharge juste avant de la modifier dans DB Browser, on la renvoie aussitôt, et personne n'enregistre rien dans l'éditeur entre les deux. Sinon, l'envoi de la base écrase les pages enregistrées entre-temps.

## Sauvegarde

La base (`data/cms_lite.db`), `config/config.ini`, `public/images/uploads/` et `public/files/`, rien d'autre. Pour copier la base sans risque pendant que l'éditeur tourne :

```sh
sqlite3 data/cms_lite.db ".backup '/chemin/sauvegarde/cms_lite-$(date +%F).db'"
```

## Limites connues

- Un brouillon n'est pas visible sur le site (c'est le sens de `pending`), y compris par le lien « Voir la page ». Il faut le décocher pour voir le résultat.
- Supprimer une page ou effacer un lien ne supprime pas l'image ou la pièce jointe correspondante. Les fichiers orphelins de `images/uploads/` et `files/` se nettoient à la main (le nom daté aide à repérer les anciens).
- Supprimer une page supprime aussi son entrée de menu. En revanche, une page vers laquelle mène un sous-menu ou `HOMEPAGE` ne peut pas être supprimée depuis l'éditeur.
- Une page passée en brouillon est retirée du menu (sinon, le lien mènerait à une page vide). Republiée, elle y revient **en dernière position et sous son titre** : un libellé personnalisé ou une place choisie sont à refaire.
- Avec Docker, `LOGFILE` doit rester à 0 : le conteneur public accède à la base en lecture seule.

## Sécurité, en bref

- **Authentification** : entièrement en amont (Apache, Caddy, forward-auth). L'éditeur ne contient aucun code d'authentification, et une requête non authentifiée n'atteint pas PHP.
- **CSRF** (*OWASP CSRF Prevention Cheat Sheet*) : toute requête POST doit porter le jeton de la session de l'éditeur (« synchronizer token », en-tête `X-CSRF-Token`, 4.5.0) **et** un en-tête `Origin` égal à l'hôte de l'éditeur. Cookie de session `HttpOnly`, `SameSite=Strict`, `Secure` en HTTPS.
- **HTML** : le contenu passe par une liste blanche (balises, attributs et classes que l'éditeur produit, liens `http(s)`, `mailto` et `tel` seulement). Tout le reste est retiré à l'enregistrement. Les lecteurs audio et vidéo n'acceptent que des fichiers de `files/`, sans lecture automatique.
- **Pièces jointes** : l'extension est déduite du contenu réel, jamais du nom envoyé. Un `.php` déguisé en `.pdf` est refusé, et aucun fichier ne peut recevoir une extension exécutable.
- **Images** : le type est vérifié sur le contenu réel (pas sur l'extension), l'image est réencodée par GD (ce qui élimine les données parasites et les métadonnées EXIF, dont la position GPS), elle est limitée à 1600 px de large et renommée.
- **Seul `public/` est servi** : configuration, code commun, bibliothèques, bases et options sont hors de la racine web, quel que soit le serveur. Un `.htaccess` à la racine du dépôt sert de filet de secours sur Apache, si le dépôt entier était déposé par erreur dans une racine web.
- **Erreurs PHP** jamais affichées (sauf `debug = 1`). **En-têtes** recommandés par l'*OWASP Secure Headers Project* : `nosniff` et `Referrer-Policy` sur le site ; en plus, sur l'éditeur, interdiction de l'afficher dans un cadre (anti-clickjacking). **Recommandé** : poser aussi `X-Content-Type-Options: nosniff` côté serveur, pour les fichiers statiques (`files/`, `images/uploads/`, et `Documents/` avec l'option Web Documents Browser) que PHP ne sert pas : bloc `security_headers` de `Caddyfile.example`, ou `Header set X-Content-Type-Options nosniff` dans le `.htaccess` d'Apache.
- **Texte issu de la base** (titres, catégories, auteurs, dates) : affiché comme texte simple sur le site, jamais comme du HTML (4.5.0).
- **Version** : elle n'est plus affichée sur le site (l'ancien `page.php?rev` a été retiré en 4.5.0) ; on la lit dans l'éditeur ou dans le fichier `VERSION`.
- **Options** : non installées par défaut. Leurs règles de sécurité sont dans leur README.
- **Site public (Docker)** : base et fichiers montés en lecture seule. Le code public ne peut rien écrire, même en cas de faille.

## Recompiler l'éditeur

Uniquement si on modifie `admin/editor-src/main.js`. Il faut Node.js sur le poste de travail, pas sur le serveur.

```sh
cd admin/editor-src
./build.sh        # produit admin/editor.js
```

Versions figées dans `package-lock.json` : Tiptap 3.31, esbuild 0.28.

## Contenu du dépôt

```
cms_lite/
├── README.md, README.fr.md        ce document (anglais, français)
├── CHANGELOG.md, CHANGELOG.fr.md  historique des versions (anglais, français)
├── VERSION                  numéro de version
├── LICENSE                  GNU AGPL v3
├── compose.example.yml      mode 3 : deux conteneurs PHP + Apache (site / éditeur), ports 127.0.0.1:8090 / 8091
├── Caddyfile.example        mode 3 : blocs à ajouter au Caddy du VPS
├── compose.local.yml        essai local complet avec Docker (Caddy + PHP), sur localhost:8081 / 8082
├── Caddyfile.local          sa configuration Caddy
├── php/                     image PHP 8.3 + Apache + GD + EXIF, et sa configuration
├── data/                    HORS de la racine web
│   └── cms_lite_demo.db     base de démonstration
├── init/empty_database.sql  base vierge, en SQL (deux pages, dans le menu)
├── .htaccess, index.php     filet de secours (Apache) si le dépôt entier est servi par erreur
├── config/                  HORS de la racine web : config.ini.example, et config.ini (local)
├── src/                     HORS de la racine web : config.php, config_ini.php, nav.php, footer.php, markdown_converter.php
├── vendor/                  HORS de la racine web : league/commonmark (Markdown), embarqué :
│                            aucun téléchargement à l'installation ; composer.json et .lock à côté
├── public/                  LA racine web : le site
│   ├── css/site.css         feuille de style unique, et css/fonts/ (Inter)
│   ├── images/              images du site ; images/uploads/ : images envoyées depuis l'éditeur
│   ├── files/               pièces jointes envoyées depuis l'éditeur
│   ├── theme/               votre habillage (feuilles, images), ignoré par Git
│   └── …                    index.php, page.php, toc.php
├── options/                 HORS du site tant qu'on ne les copie pas (voir « Options »)
│   ├── web-documents-browser/   doclist.php, display*.php, documents de démonstration
│   └── external-database/       extdb.php, base de démonstration « Bouquins »
└── admin/                   À PROTÉGER : l'éditeur (PHP), editor.js compilé, lang/ (fr, nl, en), htaccess.example
    └── editor-src/          sources de l'éditeur, pour recompiler si besoin
```

Le code et ses commentaires sont en anglais ; le contenu de la démonstration est en français.

## Versions

La version installée se lit dans le fichier `VERSION` et en bas de la liste des pages de l'éditeur.

Numérotation **majeure.mineure.correction** (version sémantique) : le dernier chiffre change pour une correction (4.1.0 → 4.1.1), le deuxième pour de nouvelles fonctions (4.1.x → 4.2.0), le premier pour une refonte (4.x → 5.0.0).

Le détail de chaque version, avec la marche à suivre pour mettre à jour, est dans **[CHANGELOG.fr.md](CHANGELOG.fr.md)**.

## Licence

Copyright (C) 2024-2026 PhS & Quintilien

Ce programme est un logiciel libre, distribué sous les termes de la **GNU Affero General Public License**, version 3 ou (à votre choix) toute version ultérieure. Texte complet dans le fichier `LICENSE`. En résumé : vous pouvez l'utiliser, le modifier et le redistribuer, à condition que toute version modifiée reste sous la même licence et que son code source soit mis à la disposition de ses utilisateurs, y compris lorsqu'elle n'est utilisée qu'en ligne, sur un serveur.

Les versions jusqu'à la 3.1 ont été publiées sous GNU GPL version 2 ou ultérieure.

Photos des recettes de la démonstration : rosbif et salade César de Wikimedia Commons, domaine public (CC0) — [Roast beef dish](https://commons.wikimedia.org/wiki/File:Roast_beef_dish.jpg), [A Caesar salad](https://commons.wikimedia.org/wiki/File:A_Caesar_salad.jpg) ; crêpes de [Pexels](https://www.pexels.com/fr-fr/license/) et salade de chèvre chaud de [Pixabay](https://pixabay.com/fr/service/license-summary/) (utilisation libre, sans mention obligatoire ; hors AGPL) ; quatre-quarts : banque d'images gratuite, licence vérifiée.

Logo : dessiné pour CMS Lite (pile de pages), lettrage composé en QTOptimum (QualiType, licence libre), équivalent de l'Optima utilisée à l'origine. Illustrations de la démonstration : « Papy », personnage créé par PhS & Quintilien ; images générées avec ChatGPT (OpenAI) à partir de descriptions écrites et d'images antérieures du personnage, puis détourées pour CMS Lite.

Composants tiers, chacun sous sa propre licence : [Tiptap](https://tiptap.dev/) (MIT), [league/commonmark](https://commonmark.thephpleague.com/) et ses dépendances (BSD-3-Clause / MIT, voir `vendor/`), police [Inter](https://rsms.me/inter/) (SIL Open Font License, `public/css/fonts/OFL.txt`).
