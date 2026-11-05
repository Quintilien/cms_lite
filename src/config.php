<?php
/*
#
# Copyright (C) 2024-2025 PhS & Quintilien
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# Rev. 1.0 (sept.2024)
# Rev. 1.1 (2024-09-12)  : All DIV's classes are in DB HEADER and FOOTER 
# Rev. 1.2 (2024-09-17)  : previous page / next page management in categ 
# Rev. 1.3 (2024-09-22)  : web_title + allows operation from a subdirectory
# Rev. 1.4 (2024-09-24)  : translatable labels
# Rev. 1.5 (2024-09-25)  : sub menus allowed
# Rev. 1.6 BETA (2024-09-27) : order desc allowed
# Rev. 2.0 : 
# Rev. 2.1 (2024-10-21)  : security optimization 
# Rev. 2.2 (2024-10-23)  : field "pending" added
# Rev. 2.21 (2024-11-22) : "Control F pour rechercher" in extdb.php + line's color
# Rev. 2.3 (2024-12-07)  : tracking ip and user-agent
# Rev. 2.4 (2025-03-10)  : file list scanning allowed for defined directories + display markdown
# Rev. 2.5 (2025-03-18)  : bug corrected in extdb.php when order == 0
# Rev. 2.6 (2025-03-30)  : since scandir.php is not accepted in some php environment 
#                          name "scandir.php" is replaced by "doclist.php"
#                          similarly, in config.ini, the "scandir=" parameter is now
#                          replaced by "doclist_allowed="
#                          Furthermore, it is now possible to display the current
#                          version number with the "?rev" parameter. 
#                          example : "page.php?rev" or "page.php?id=123&rev"
# Rev. 3.0 (2025-07-22)  : display pdf, display md and other files optimized 
#                          parameters top and bottom
#                          Markdown converter now handles footnotes 
#                          Doclist optmized (select author and/or categ)
# Rev. 3.1 (2025-08-18   : 1) HTML files are intentionally no longer displayed in doclist.php
#                             because it now allows you to put (in the "doclisted" directory) an index.html 
#                             (invisible in doclist.php, therefore) which contains nothing, this in order 
#                             to prevent the Internet user from being able to view the contents of a directory 
#                             in their browser
#                          2) displayfile.php modified because PDF viewing 
#                             was not working on iPad or iPhone when using iFrame 
# Rev. 4.7.1 (2026-11-01) : English comments and documentation (README.md / README.fr.md, CHANGELOG)
# Rev. 4.7.0 (2026-11-01) : Web Documents Browser (doclist.php, display*.php) and extdb.php moved
#                          to options/ (installed on demand); their settings are read there
# Rev. 4.6.0 (2026-11-01) : demo pages in the editor's HTML format (one page kept in Markdown);
#                          editor accepts all site images (images/…)
# Rev. 4.5.2 (2026-11-01) : review fixes (titles not parsed as Markdown, unsafe Markdown links off,
#                          display*.php restricted to their file type, GET parameters as strings,
#                          extdb 404, toc sort, visit log IP and lengths)
# Rev. 4.5.0 (2026-11-01) : config.ini read by src/config_ini.php (shared with the editor);
#                          ?rev removed; text from the database (title, categ, author, date)
#                          escaped (cms_t) in page.php, toc.php and nav.php
# Rev. 4.4.0 (2026-10-30) : new layout: only public/ is served; src/ (this file, nav.php,
#                          footer.php, markdown_converter.php), config/ (config.ini), vendor/
#                          and data/ are next to it, outside the web root. PHP errors hidden
#                          unless debug = 1 in config.ini; nosniff and Referrer-Policy headers.
# Rev. 4.3.3 (2026-10-29) : Docker image: PHP errors are logged, no longer displayed
#                          (php/php.ini: display_errors = Off, log_errors = On)
# Rev. 4.3.2 (2026-10-29) : security fix (document browsing scripts, extdb.php); update recommended
# Rev. 4.3.1 (2026-10-28) : demo database (logo margin, external DB list readable in dark mode)
# Rev. 4.3.0 (2026-10-28) : published on GitHub (AGPL-3.0-or-later); config.ini falls back to
#                          config.ini.example (demo database) when absent
# Rev. 4.2.0 (2026-09-26) : editor: callouts (info, note, success, warning, danger), toggle blocks
# Rev. 4.1.0 (2026-09-25) : editor: tables, code blocks, link dialog (site pages, e-mail, phone),
#                          audio player, video attachments, image positions; admin in fr/nl/en,
#                          light/dark; single stylesheet css/site.css (Inter, dark mode)
# Rev. 4.0.0 (2026-09-25) : web administration with a rich text editor (admin/, separate host
#                          behind an authenticating proxy); page.php serves the HTML of pages
#                          written with the editor (txt starting with <!--html-->) as is;
#                          main menu (t_menu) managed from the admin; images and attachments
#
*/

$current_rev = 'CMS_Lite version 4.7.4 (2026-11-05)' ;   // Rev. 4.5.0: no longer shown on the site (?rev removed)

// config.ini is a local file containing path and SQLite database filename 
$db = '';
$web_title = '';
$debug = false;

//////////////////////////////////////////////////////////////////////////////////////////////
//                                                                                          //
//  config.ini: plain text, UTF-8 without BOM (the default of current editors,             //
//  including Windows Notepad). Rev. 4.4.0: the former advice to save it in "ANSI" for     //
//  IIS is obsolete.                                                                        //
//                                                                                          //
//////////////////////////////////////////////////////////////////////////////////////////////


// config/config.ini (production, not in the Git repository); otherwise config.ini.example (demo).
// Rev. 4.4.0: config/ sits next to public/, outside the web root (formerly inside www/).
// Rev. 4.5.0: reading shared with the editor (src/config_ini.php).
require_once __DIR__ . '/config_ini.php';
$config_dir = dirname(__DIR__) . '/config';
$db        = (string)(cms_config_value($config_dir, 'db') ?? '');
$web_title = (string)(cms_config_value($config_dir, 'title') ?? '');
$debug     = cms_config_value($config_dir, 'debug') === '1';
// Rev. 4.5.0: time zone of generated dates (optional "timezone" key)
date_default_timezone_set(cms_config_value($config_dir, 'timezone') ?: 'Europe/Brussels');

// Rev. 4.4.0: PHP errors never displayed on pages, except with "debug = 1" in
// config.ini (for the time of a diagnosis); they are still recorded in the server
// log. Headers recommended by OWASP (Secure Headers Project) for a read-only
// site: no guessing of file types, no full address sent to other sites.
// Pages may still be framed (no action is possible on them).
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
if (!headers_sent()) {
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: same-origin');
}

// Rev. 4.3.2: escaping for HTML attributes (used by the options)
function cms_h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
// Rev. 4.5.2: URL parameter, always a string ("?x[]=..." gives '' instead of an error)
function cms_get($key) {
  $v = $_GET[$key] ?? '';
  return is_string($v) ? $v : '';
}
// Rev. 4.5.0: text from the database (title, category, author, date) shown as is,
// without HTML: since 4.5.0, the online editor also writes category, author and date.
// Quotes are not converted (tag content, not an attribute).
function cms_t($s) {
  return htmlspecialchars((string)$s, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// mostly used in page.php
$label_categ        = 'Page classée dans :';
$label_author       = 'Auteur :';
$label_date         = 'Dernière mise à jour :';
$label_next         = 'Page suivante';
$label_top_of_page  = 'Retourner en début de page';

// mostly used in page.php and extdb.php
$label_ctrl_f       = 'Control F pour rechercher';
$label_previous     = 'Page précédente';
$label_top_of_page  = 'Retourner en début de page';
$label_colon        = ' : ';
$label_not_found_ext = 'Fiche introuvable.';   // Rev. 4.5.2 (extdb.php)

// mostly used in toc.php
$page_title         = '';
$categ_empty        = '(Non classé)';
$title_empty        = '(Sans titre)';

$col_title_title    = 'Titre';
$col_title_categ    = 'Catégorie';
$col_title_categ_2  = 'Catégories';
$col_title_pagenum  = 'Page n°';
$col_title_author   = 'Auteur';
$col_title_author_2 = 'Auteurs';
$col_title_date     = 'Dernière mise à jour';
$col_title_lastpub  = 'Dernières pages publiées';
$col_title_cat_used = 'Classé dans';


// Connecting database
$dbh = new PDO('sqlite:'.$db) 
       or die("Unable to connect to sqlite database");
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Tracking Internet user IP address (if LOGFILE == 1 in t_parameters)
$logfile = -1 ;
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'LOGFILE' " ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    $logfile = $row[0];
}
if ($logfile == 1) {
  $stmt = $dbh->prepare( "CREATE TABLE IF NOT EXISTS 't_logfile'
    ( 'id'          INTEGER NOT NULL UNIQUE,
      'dt'          TEXT DEFAULT '',
      'ip_address'  TEXT DEFAULT '',
      'user_agent'  TEXT DEFAULT '',
      'request_url' TEXT DEFAULT '',
      'page_descr'  TEXT DEFAULT '',
      PRIMARY KEY('id' AUTOINCREMENT) )  " ) ;
  $stmt->execute() ;

  // Rev. 4.5.2: the address passed on by a proxy (X-Forwarded-For) is only trusted if
  // the request itself comes from a private or local address (reverse proxy, container);
  // otherwise any visitor could make it up. Client-IP is no longer read.
  $current_ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
  $from_proxy = $current_ip !== '' && !filter_var($current_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
  if ( $from_proxy && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
    $xff = trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    if ( filter_var($xff, FILTER_VALIDATE_IP) ) { $current_ip = $xff; }
  }

  $dt          = new \DateTime();
  $dt          = $dt->format('Y/m/d H:i:s');
  $user_agent  = mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300);   // Rev. 4.5.2: bounded lengths
  $request_url = mb_substr((string)($_SERVER['REQUEST_URI'] ?? ''), 0, 500);
  // See also https://stackoverflow.com/questions/8754080/how-to-get-exact-browser-name-and-version
}


