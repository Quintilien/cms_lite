# admin/ — the online editor: MUST BE PROTECTED

The editor has **no password of its own**. Without protection placed in front
of it by the server, anyone can change or delete the site.

- No need for an online editor? **Do not upload this folder**: edit the
  database at home (DB Browser for SQLite) and send it by FTP.
- Apache: `htaccess.example` → `.htaccess`, with a `.htpasswd` file.
- Caddy, nginx…: separate subdomain, behind a forward-auth (Tinyauth,
  Authelia…) or Basic authentication.

Details and checks: main README, "Protecting the editor".

---

**FR —** L'éditeur n'a **aucun mot de passe propre** : sans protection placée devant lui par le serveur, n'importe qui peut modifier ou supprimer le site. Pas d'éditeur en ligne ? Ne téléversez pas ce dossier. Apache : `htaccess.example` → `.htaccess`, avec un `.htpasswd`. Caddy, nginx… : sous-domaine distinct, derrière un forward-auth ou une authentification Basic. Détails : `README.fr.md`, « Protéger l'éditeur ».
