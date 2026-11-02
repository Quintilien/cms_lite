-- CMS Lite 4: empty database (CMS Lite 3.1 structure, without the demo pages)
-- Usage: sqlite3 cms_lite.db < empty_database.sql   (or "Execute SQL" in DB Browser)
BEGIN TRANSACTION;
CREATE TABLE "t_conversion" (
	"id"	INTEGER NOT NULL UNIQUE,
	"item"	TEXT DEFAULT '',
	"translation"	TEXT DEFAULT '',
	PRIMARY KEY("id" AUTOINCREMENT)
);
INSERT INTO "t_conversion" VALUES(14,'<xx->','<div class="row"><div class="two-thirds column">');
INSERT INTO "t_conversion" VALUES(15,'<--x>','</div><div class="one-third column">');
INSERT INTO "t_conversion" VALUES(16,'<--->','</div></div>');
INSERT INTO "t_conversion" VALUES(17,'<x-->','<div class="row"><div class="one-third column">');
INSERT INTO "t_conversion" VALUES(18,'<-xx>','</div><div class="two-thirds column">');
INSERT INTO "t_conversion" VALUES(19,'<-x->','</div><div class="one-third column">');
INSERT INTO "t_conversion" VALUES(20,'<x->','<div class="row"><div class="one-half column">');
INSERT INTO "t_conversion" VALUES(21,'<-x>','</div><div class="one-half column">');
INSERT INTO "t_conversion" VALUES(22,'<-->','</div></div>');
INSERT INTO "t_conversion" VALUES(23,'<!>','<div style="background-color: #567895; color: white; padding-top: 15px; padding-bottom: 6px; padding-left: 16px; padding-right: 16px;">
');
INSERT INTO "t_conversion" VALUES(24,'<->','</div>
');
CREATE TABLE 't_logfile'
    ( 'id'          INTEGER NOT NULL UNIQUE,
      'dt'          TEXT DEFAULT '',
      'ip_address'  TEXT DEFAULT '',
      'user_agent'  TEXT DEFAULT '',
      'request_url' TEXT DEFAULT '',
      'page_descr'  TEXT DEFAULT '',
      PRIMARY KEY('id' AUTOINCREMENT) );
CREATE TABLE "t_menu" (
	"id"	INTEGER NOT NULL UNIQUE,
	"item"	TEXT DEFAULT '',
	"target"	TEXT DEFAULT '',
	"display_order"	INTEGER DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE "t_pages" (
	"id"	INTEGER NOT NULL UNIQUE,
	"title"	TEXT DEFAULT '',
	"pending"	INTEGER DEFAULT 0,
	"date"	TEXT DEFAULT '',
	"categ"	TEXT DEFAULT '',
	"categ_pagenum"	INTEGER DEFAULT 0,
	"author"	TEXT DEFAULT '',
	"txt"	TEXT DEFAULT '',
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE "t_parameters" (
	"id"	INTEGER NOT NULL UNIQUE,
	"ref"	TEXT,
	"descr"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
INSERT INTO "t_parameters" VALUES(1,'HOMEPAGE','page.php?id=3
');
INSERT INTO "t_parameters" VALUES(2,'HEADER','<!----- <h1>BEGIN HEADER</h1>  ----->

<div class="container">

	<div id="top-of-page">
	</div>

   <div class="container-960px">
         <!-----  <h1>HEADER IN CONTAINER 960px </h1>  ----->
		 <p style="margin: 1.5rem 0 0"><img alt="CMS Lite" src="images/logo/cms_lite.png" width="373" height="130"></p>
        <p></p>
   </div>

<!-----  <h1>HEADER BEFORE NAV</h1>  ----->

<nav>	
<!-- <div id="logo">Demo Page</div> -->
<label for="drop" class="toggle">Menu <big>☰</big></label>
<input type="checkbox" id="drop" />
<ul class="menu">

');
INSERT INTO "t_parameters" VALUES(3,'FOOTER','</div>
</div>
<p>&nbsp;</p>
');
INSERT INTO "t_parameters" VALUES(4,'BODY','            </ul>
        </nav>
		<div class="container-960px">
<hr>
		<!---    <h1>END BODY</h1>    -->
	');
INSERT INTO "t_parameters" VALUES(5,'LOGFILE','0');
INSERT INTO "t_parameters" VALUES(6,'HTML_META_CSS','<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/nav.css">
<link rel="stylesheet" type="text/css" href="css/kenburns.css">
<link rel="stylesheet" type="text/css" href="css/animations.css">');
INSERT INTO "t_parameters" VALUES(8,'DOCLIST_TOP','<!---
<h1>Documents disponibles</h1>
--->
<p><small>


&nbsp;&larr; <a href="doclist.php?dir=Documents&order=-1">Réinit. liste </a> 

&nbsp;&nbsp;&nbsp;

&nbsp;&uarr; <a href="displaymd.php?dir=Documents&file=Accueil.md">Accueil</a>

</small></p>');
INSERT INTO "t_parameters" VALUES(9,'DOCLIST_BOTTOM','<!--
<p>&uarr; <a href="#top-of-page">Top</a></p> 
-->
<small>
<div class="row">
<div class="one-half column">
<ul>
<li> Pour <b>classer</b> les documents, cliquer sur le <b>titre d''une des colonnes</b>.</li> 
<li> Un <b>deuxième clic</b> sur la même colonne = classement <b>en sens inverse</b>.</li>
<li> Pour <b>rechercher</b>, appuyer simultanément sur les touches <b>Control et F</b>.</li>
<li> Pour sélectionner un <b>auteur</b> : cliquer sur son <b>nom</b>.</li>
<li> Pour sélectionner une <b>catégorie</b> : cliquer sur son <b>nom</b>.</li>
<li> Si on clique sur le <b>titre</b> du document, il <b>s''affiche et/ou se télécharge</b>.</li>
</ul>
</div>

<div class="one-half column">
<ul> 
<li> fichier <b>pdf</b> :  <b>s''affiche</b> dans une <b>visionneuse </b> gérée par le navigateur</li> 
<li> fichier <b>md</b> (Markdown) : le contenu du fichier <b>s''affiche en tenant compte du style</b> de votre site Web</li>
<li> fichier <b>html</b> : le fichier HTML <b>s''affiche tel qu''il a été défini</b> par le code</li>
<li> fichiers <b>docx, xlsx et autres extensions</b> : le fichier est proposé au <b>téléchargement</b></li>
</ul>
</div>
</div>
 </small>');
INSERT INTO "t_parameters" VALUES(10,'DISPLAY_FILE_TOP','<!-----  <h1>DISPLAY FILE  TOP</h1>  ----->
<p>
&nbsp;&uarr; &nbsp;
<a href="doclist.php?dir=Documents&order=-1"><small>Retour à la liste des fichiers</small></a>
</p>');
INSERT INTO "t_parameters" VALUES(11,'DISPLAY_FILE_BOTTOM','<!-----  <h1>DISPLAY FILE  BOTTOM</h1>  ----->
<!-----  <p>&uarr; <a href="#top-of-page">Retourner en début de page</a></p>   ----->');
INSERT INTO "t_parameters" VALUES(12,'DISPLAY_HTML_TOP','<!-----------
<style>
table {
 border-collapse:collapse;
 width:100%;
 font-size: 90%;
 }
th, td {
 border:1px solid black;
 padding-left: 10px;
 padding-top: 10px;
 padding-bottom: 10px;
 padding-right: 10px;
 vertical-align: top;
 }
</style>
-------->


<style>

/*
* Skeleton V2.0.4
* Copyright 2014, Dave Gamache
* www.getskeleton.com
* Free to use under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
* 12/29/2014
* Previous update 19 feb 2020 by Quintilien (animations)
* Last updates
*   8 aug 2021 by Quintilien (videos and code horizontal scrolling on iphone)
*  10 sept 2024 by Quintilien (img max size and ul bottom margin, bottom lines removed in td list)
*  20 sept 2024 by Quintilien (Font Optima)
*  22 sept 2024 by Quintilien (Font Size)
*/


/* Table of contents
––––––––––––––––––––––––––––––––––––––––––––––––––
- Grid
- Base Styles
- Typography
- Links
- Buttons
- Forms
- Lists
- Code
- Tables
- Spacing
- Utilities
- Clearing
- Media Queries
*/


/* Grid
–––––––––––––––––––––––––––––––––––––––––––––––––– */
.container {
  position: relative;
  width: 100%;
  /* max-width: 960px; */
  margin: 0 ;
  padding: 0 ;
  box-sizing: border-box; }

.container-960px {
  max-width: 960px;
  margin: 0 auto;
  padding: 0 20px;  }

/* added 20241023 */
.container-1280px {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 20px;  }

.column,
.columns {
  width: 100%;
  float: left;
  box-sizing: border-box; }

/* For devices larger than 400px */
@media (min-width: 400px) {
  .container-960px {
    /* width: 85%; */
    width: 90%;
    padding: 0; }
}

/* For devices larger than 550px */
@media (min-width: 550px) {
  .container-960px {
    /* width: 80%; } */
    width: 90%; }
  .column,
  .columns {
    margin-left: 4%; }
  .column:first-child,
  .columns:first-child {
    margin-left: 0; }

  .one.column,
  .one.columns                    { width: 4.66666666667%; }
  .two.columns                    { width: 13.3333333333%; }
  .three.columns                  { width: 22%;            }
  .four.columns                   { width: 30.6666666667%; }
  .five.columns                   { width: 39.3333333333%; }
  .six.columns                    { width: 48%;            }
  .seven.columns                  { width: 56.6666666667%; }
  .eight.columns                  { width: 65.3333333333%; }
  .nine.columns                   { width: 74.0%;          }
  .ten.columns                    { width: 82.6666666667%; }
  .eleven.columns                 { width: 91.3333333333%; }
  .twelve.columns                 { width: 100%; margin-left: 0; }

  .one-third.column               { width: 30.6666666667%; }
  .two-thirds.column              { width: 65.3333333333%; }

  .one-half.column                { width: 48%; }

  /* Offsets */
  .offset-by-one.column,
  .offset-by-one.columns          { margin-left: 8.66666666667%; }
  .offset-by-two.column,
  .offset-by-two.columns          { margin-left: 17.3333333333%; }
  .offset-by-three.column,
  .offset-by-three.columns        { margin-left: 26%;            }
  .offset-by-four.column,
  .offset-by-four.columns         { margin-left: 34.6666666667%; }
  .offset-by-five.column,
  .offset-by-five.columns         { margin-left: 43.3333333333%; }
  .offset-by-six.column,
  .offset-by-six.columns          { margin-left: 52%;            }
  .offset-by-seven.column,
  .offset-by-seven.columns        { margin-left: 60.6666666667%; }
  .offset-by-eight.column,
  .offset-by-eight.columns        { margin-left: 69.3333333333%; }
  .offset-by-nine.column,
  .offset-by-nine.columns         { margin-left: 78.0%;          }
  .offset-by-ten.column,
  .offset-by-ten.columns          { margin-left: 86.6666666667%; }
  .offset-by-eleven.column,
  .offset-by-eleven.columns       { margin-left: 95.3333333333%; }

  .offset-by-one-third.column,
  .offset-by-one-third.columns    { margin-left: 34.6666666667%; }
  .offset-by-two-thirds.column,
  .offset-by-two-thirds.columns   { margin-left: 69.3333333333%; }

  .offset-by-one-half.column,
  .offset-by-one-half.columns     { margin-left: 52%; }

}


/* Base Styles
–––––––––––––––––––––––––––––––––––––––––––––––––– */
/* NOTE
html is set to 62.5% so that all the REM measurements throughout Skeleton
are based on 10px sizing. So basically 1.5rem = 15px :) */

/* Added 24 feb. 2024 */ 
/* @import url("https://fonts.googleapis.com/css?family=Poppins:100,200,400,500,600,700,800|Nunito:300,400,600,700,800");
/* Removed 20 sept. 2024 */

html {
  /* font-size: 62.5%;  */
  /* Modif 20240922 */
  font-size: 70.0%; 
     }
body {
  margin: 0; /* added by Quintilien to remove defaut margin in Safari */
  font-size: 1.5em; /* currently ems cause chrome bug misinterpreting rems on body element */

/* Modified 24 feb. 2024 */ 
  line-height: 1.6; 
  font-weight: 400; 
  /* font-family: "Raleway", "HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif; color: #222; } */ 
  /* font-family: "Poppins", "HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif; */
/* Modified 20 sept; 2024 */
  /* https://kinsta.com/fr/blog/polices-caracteres-web-safe/ */
  Font-family: Optima, Segoe, Segoe UI, Candara, Calibri, Arial, sans-serif ; 
  color: #222; 
  }

/* Typography
–– (original) ––––––––––––––––––––––––––––––––––––
h1, h2, h3, h4, h5, h6 {
  margin-top: 0;
  margin-bottom: 2rem;
  font-weight: 300; }
h1 { font-size: 4.0rem; line-height: 1.2;  letter-spacing: -.1rem;}
h2 { font-size: 3.6rem; line-height: 1.25; letter-spacing: -.1rem; }
h3 { font-size: 3.0rem; line-height: 1.3;  letter-spacing: -.1rem; }
h4 { font-size: 2.4rem; line-height: 1.35; letter-spacing: -.08rem; }
h5 { font-size: 1.8rem; line-height: 1.5;  letter-spacing: -.05rem; }
h6 { font-size: 1.5rem; line-height: 1.6;  letter-spacing: 0; }

@media (min-width: 550px) {
  h1 { font-size: 5.0rem; }
  h2 { font-size: 4.2rem; }
  h3 { font-size: 3.6rem; }
  h4 { font-size: 3.0rem; }
  h5 { font-size: 2.4rem; }
  h6 { font-size: 1.5rem; }
}
–––––––––––––––––––––––––––––––––––––––––––––––––– */

h1, h2, h3, h4, h5, h6 { 

/* 20240920 */
color: rgb(61,92,122); 
/* color:#567895; */
/* color: rgb(117,144,174); */
/* color: #5D7792; */
/* color: #627790 ; */
/* color: #282151; */
/* color: ##111439; */


  margin-top: 0;
  margin-bottom: 2rem;
  font-weight: 300; }
h1 {  padding: 1ex 0 0 0; font-size: 3.6rem; line-height: 1.25; letter-spacing: -.1rem; }
h2 {  padding: 1ex 0 0 0; font-size: 3.0rem; line-height: 1.3;  letter-spacing: -.1rem; }
h3 {                      font-size: 2.4rem; line-height: 1.35; letter-spacing: -.08rem; }
h4 {                      font-size: 1.8rem; line-height: 1.5;  letter-spacing: -.05rem; }
/* 20240920 */
/* h5 {                      font-size: 1.5rem; line-height: 1.6;  letter-spacing: 0; } */
h5 {                      font-size: 1.6rem; line-height: 1.6;  letter-spacing: 0; }


/* Larger than phablet */
@media (min-width: 550px) {
  h1 { font-size: 4.2rem; }
  h2 { font-size: 3.6rem; }
  h3 { font-size: 3.0rem; }
  h4 { font-size: 2.4rem; }
/* 20240920 */
/*  h5 { font-size: 1.5rem; }  */
  h5 { font-size: 1.6rem; }
}

p {
  margin-top: 0; }


/* Add-on Quintilien nov. 2019 for Makesite.py 
–––––––––––––––––––––––––––––––––––––––––––––––––– */
p.meta {
    margin-top: 0;
    font-size: 0.8em;
    color: #777777;
    font-style: italic;
}
p.summary {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}
div .more {
    margin-top: 0;
    font-size: 0.8em;
    color: #777777;
    font-style: italic;
    font-weight: bold;
}
img {
   margin-top: 6px;
   /* Modif 2024-09-07 */
   width: 100%;
   height = auto; 
}


/* Add-on Quintilien dec. 2019 / jan 2020 (images) 
–––––––––––––––––––––––––––––––––––––––––––––––––– */

img[src~="max960px"] {
   max-width:960px;
   height = auto;
}

img[src~="max640px"] {
   max-width:640px;
   height = auto;
}

img[src~="max480px"] {
   max-width:480px;
   height = auto;
}

img[src~="max320px"] {
   max-width:320px;
   height = auto;
}

img[src~="thumbnail"] {
   max-width:192px;
   height = auto;
}

img[src~="center"] {
   display: block;
   margin-left: auto;
   margin-right: auto;
}

img[src~="bordered"] {
   border: 1px solid black;
}

img[src~="shadow"] {
  background-color: white;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

img[src~="float-left"] {
   float:left;
   margin-right: 2em;
   margin-bottom: 4px;
}

img[src~="float-right"] {
   float:right;
   margin-left: 2em;
   margin-bottom: 4px;
}

/* Links
–– (original) ––––––––––––––––––––––––––––––––––––

a {
  color: #1EAEDB; }
a:hover {
  color: #0FA0CE; } 

–– (modif. nov. 2019) –––––––––––––––––––––––– */

a {
  color: rgb(61,92,122); 
  background-color: transparent;
  text-decoration: underline;
  margin: 0;
  padding: 0 1px 2px 1px;
}

a:hover {
  color: rgb(117,144,174);
  text-decoration: none;
}

/* Buttons
–––––––––––––––––––––––––––––––––––––––––––––––––– */
.button,
button,
input[type="submit"],
input[type="reset"],
input[type="button"] {
  display: inline-block;
  height: 38px;
  padding: 0 30px;
  color: #555;
  text-align: center;
  font-size: 11px;
  font-weight: 600;
  line-height: 38px;
  letter-spacing: .1rem;
  text-transform: uppercase;
  text-decoration: none;
  white-space: nowrap;
  background-color: transparent;
  border-radius: 4px;
  border: 1px solid #bbb;
  cursor: pointer;
  box-sizing: border-box; }
.button:hover,
button:hover,
input[type="submit"]:hover,
input[type="reset"]:hover,
input[type="button"]:hover,
.button:focus,
button:focus,
input[type="submit"]:focus,
input[type="reset"]:focus,
input[type="button"]:focus {
  color: #333;
  border-color: #888;
  outline: 0; }
.button.button-primary,
button.button-primary,
input[type="submit"].button-primary,
input[type="reset"].button-primary,
input[type="button"].button-primary {
  color: #FFF;
  background-color: #33C3F0;
  border-color: #33C3F0; }
.button.button-primary:hover,
button.button-primary:hover,
input[type="submit"].button-primary:hover,
input[type="reset"].button-primary:hover,
input[type="button"].button-primary:hover,
.button.button-primary:focus,
button.button-primary:focus,
input[type="submit"].button-primary:focus,
input[type="reset"].button-primary:focus,
input[type="button"].button-primary:focus {
  color: #FFF;
  background-color: #1EAEDB;
  border-color: #1EAEDB; }

/* Forms
–––––––––––––––––––––––––––––––––––––––––––––––––– */
input[type="email"],
input[type="number"],
input[type="search"],
input[type="text"],
input[type="tel"],
input[type="url"],
input[type="password"],
textarea,
select {
  height: 38px;
  padding: 6px 10px; /* The 6px vertically centers text on FF, ignored by Webkit */
  background-color: #fff;
  border: 1px solid #D1D1D1;
  border-radius: 4px;
  box-shadow: none;
  box-sizing: border-box; }
/* Removes awkward default styles on some inputs for iOS */
input[type="email"],
input[type="number"],
input[type="search"],
input[type="text"],
input[type="tel"],
input[type="url"],
input[type="password"],
textarea {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none; }
textarea {
  min-height: 65px;
  padding-top: 6px;
  padding-bottom: 6px; }
input[type="email"]:focus,
input[type="number"]:focus,
input[type="search"]:focus,
input[type="text"]:focus,
input[type="tel"]:focus,
input[type="url"]:focus,
input[type="password"]:focus,
textarea:focus,
select:focus {
  border: 1px solid #33C3F0;
  outline: 0; }
label,
legend {
  display: block;
  margin-bottom: .5rem;
  font-weight: 600; }
fieldset {
  padding: 0;
  border-width: 0; }
input[type="checkbox"],
input[type="radio"] {
  display: inline; }
label > .label-body {
  display: inline-block;
  margin-left: .5rem;
  font-weight: normal; }


/* Lists
–––––––––––––––––––––––––––––––––––––––––––––––––– */
ul {
  /* list-style: circle inside; } */
  list-style: square outside; }
ol {
  list-style: decimal outside; }

ul ul,
ul ol,
ol ol,
ol, ul {
  line-height:1.5 ;
  padding-left: 3.0rem;
  margin: 0, 0, 0, 0 ;
  font-size: 95%; }
  
li {
  margin-bottom: 0; }


/* Code
–––––––––––––––––––––––––––––––––––––––––––––––––– */

code {
  padding: .2rem .5rem;
  margin: 0 .2rem;
  /* font-size: 90%; */
  white-space: nowrap;
  background: #F1F1F1;
  border: 1px solid #E1E1E1;
  border-radius: 4px; }


/* pre > code {              */
/*   display: block;         */
/*   padding: 1rem 1.5rem;   */
/*   white-space: pre; }     */

/* Updated 8 aug 2021 for horizontal scrolling */

pre code {
  display: block;
  background: none;
  white-space: pre;
  -webkit-overflow-scrolling: touch;
  overflow-x: scroll;
  max-width: 100%;
  min-width: 100px;
/*   padding: 0;  */
  padding: 1rem 1.5rem;  

  background: #F1F1F1;
  border: 1px solid #E1E1E1;
  border-radius: 4px; 

}


/* Blockquote (Add-on Quintilien nov. 2019)
–––––––––––––––––––––––––––––––––––––––––––––––––– */
blockquote {
  font-style: normal;
 /* font-size: 15px; */ 
 font-size: 1em; 
 margin-left: 32px;
  font-family: "Times New Roman", Verdana;
  border-left: 4px solid #CCC;
  padding-left: 8px;
}


/* Videos (Add-on Quintilien aug. 2021)
–––––––––––––––––––––––––––––––––––––––––––––––––– */

video {
  /* override other styles to make responsive */
  width: 100%    !important;
  height: auto   !important;
}

/* Tables
–––––––––––––––––––––––––––––––––––––––––––––––––– */
th,
td {
  /* padding: 12px 15px; */
  padding: 1px 15px;
  /* text-align: left; */
  /* modif 20240910
  border-bottom: 1px solid #E1E1E1; 
  */
   }

 /*  
   th:first-child,
td:first-child {
  padding-left: 0; }
th:last-child,
td:last-child {
  padding-right: 0; }
*/

/* Spacing
–––––––––––––––––––––––––––––––––––––––––––––––––– */
button,
.button {
  margin-bottom: 1rem; }
input,
textarea,
select,
fieldset {
  margin-bottom: 1.5rem; }
pre,
blockquote,
dl,
figure,
table,
p,
ul,
ol,
form {
  margin-bottom: 1.5rem; }

/* ORIGINAL margin-bottom: 2.5rem; }. */

/* Utilities
–––––––––––––––––––––––––––––––––––––––––––––––––– */
.u-full-width {
  width: 100%;
  box-sizing: border-box; }
.u-max-full-width {
  max-width: 100%;
  box-sizing: border-box; }
.u-pull-right {
  float: right; }
.u-pull-left {
  float: left; }


/* Misc
–––––––––––––––––––––––––––––––––––––––––––––––––– */
hr {
  margin-top: 3rem;
  margin-bottom: 3.5rem;
  border-width: 0;
  border-top: 1px solid #E1E1E1; }


/* Clearing
–––––––––––––––––––––––––––––––––––––––––––––––––– */

/* Self Clearing Goodness */
.container:after,
.row:after,
.u-cf {
  content: "";
  display: table;
  clear: both; }


/* Media Queries
–––––––––––––––––––––––––––––––––––––––––––––––––– */
/*
Note: The best way to structure the use of media queries is to create the queries
near the relevant code. For example, if you wanted to change the styles for buttons
on small devices, paste the mobile query code up in the buttons section and style it
there.
*/


/* Larger than mobile */
@media (min-width: 400px) {}

/* Larger than phablet (also point when grid becomes active) */
@media (min-width: 550px) {}

/* Larger than tablet */
@media (min-width: 750px) {}

/* Larger than desktop */
@media (min-width: 1000px) {}

/* Larger than Desktop HD */
@media (min-width: 1200px) {}




table {
 border-collapse:collapse;
 }
 
 th, td {
 border:1px solid black;
 padding-left: 10px;
 padding-top: 10px;
 padding-bottom: 10px;
 padding-right: 10px;
 vertical-align: top;
 }


/* 
/#TOC  ol, ul {
   line-height: 1.5; 
   font-weight: 200; 
   padding-left: 0.1em;
   padding-top: 0.0em;
   padding-bottom: 0.0em;
   padding-right: 0.0em;
   margin-left: 0.0 em;
   margin-top: 0.0 em;
   margin-bottom: 0.0em;
   margin-right: 0.0em; 
   }
 */
 
 #TOC  li {
  list-style: none;
   line-height: 2; 
   }
 
#TOC  li > ol, li > ul {
   margin-left: 0.0 em;
   margin-top: 0.0em;
   margin-bottom: 0.0em;
   margin-right: 0.0em; 
   }
  
#TOC a:not(:hover) {
  text-decoration: none; 
  }
  
#TOC a:hover {
  text-decoration: underline; 
background-color: whitesmoke;
  }


</style>

<div class=" container">

<div class=" container-960px">

<div id="top-of-page">
</div>

<!---
 <img alt="" src="images/logo/cms_lite.png" >
---->  
 <p>
&uarr;  <a href="doclist.php?dir=Documents&order=-1">Documents</a> 
</p>
<hr>

<h1>Table des matières</h1>');
INSERT INTO "t_parameters" VALUES(13,'DISPLAY_HTML_BOTTOM','<!-----  <h1>DISPLAY HTML  BOTTOM</h1>  ----->
<!-----  <p>&uarr; <a href="#top-of-page">Retourner en début de page</a></p>  ----->');
INSERT INTO "t_parameters" VALUES(14,'EXTERNAL_DB_LIST_TOP','<!-- EXTERNAL DB LIST TOP -->
<style>tr:nth-child(even) > td { background: var(--wash); }</style>
<p style="text-align: center"><small>(Control-F pour rechercher)</small></p>
<table>');
INSERT INTO "t_parameters" VALUES(15,'EXTERNAL_DB_LIST_BOTTOM','</table>
<p> </p>
<p align="center">
&nbsp;&uarr; &nbsp;<a href="#top-of-page" ><small>Retourner en début de page</a></small><br>
</p>

<!-----  <h1>EXTERNAL DB LIST BOTTOM</h1> ----->');
INSERT INTO "t_parameters" VALUES(16,'EXTERNAL_DB_DETAIL_TOP','<!-----  <h1>EXTERNAL DB DETAIL TOP</h1>  ----->');
INSERT INTO "t_parameters" VALUES(17,'EXTERNAL_DB_DETAIL_BOTTOM_is_not_set','<!-----  <h1>EXTERNAL DB DETAIL BOTTOM</h1>  ----->
<p>
&nbsp;&uarr; &nbsp;<a href="#top-of-page"><small>Retourner en début de page</a></small><br>
</p>');
INSERT INTO "t_parameters" VALUES(18,'TABLE_OF_CONTENTS_TOP','<!-----  <h1>TABLE OF CONTENTS TOP</h1> ----->');
INSERT INTO "t_parameters" VALUES(19,'TABLE_OF_CONTENTS_BOTTOM','<!-----  <h1>TABLE OF CONTENTS BOTTOM</h1>  ----->');
INSERT INTO "t_parameters" VALUES(20,'DISPLAY_MD_TOP','<!---  <h1>DISPLAY_MD_TOP</h1> 
<p>&nbsp;</p>
-->');
INSERT INTO "t_parameters" VALUES(21,'DISPLAY_MD_BOTTOM','<!-----  <h1>DISPLAY_MD_BOTTOM</h1>  ----->');
CREATE TABLE "t_sub_menu" (
	"id"	INTEGER NOT NULL UNIQUE,
	"fk_menu_id"	INTEGER,
	"item"	TEXT DEFAULT '',
	"target"	TEXT DEFAULT '',
	"display_order"	INTEGER DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT),
	FOREIGN KEY("fk_menu_id") REFERENCES "t_menu"("id")
);
CREATE VIEW 'Vue Menu / Sous-menus' AS
SELECT col2 AS 'Choix menu', col3 AS 'id', col4 AS 'Sous-menu', col5 AS 'id. sous-menu', col6 AS 'Lien' FROM (
		SELECT 
		'1' 										AS niveau  ,
		t_menu.display_order AS col1 ,  
		t_menu.item 					AS col2 ,
		t_menu.id 						AS col3 ,
		'' 											AS col4 ,
		'' 											AS col5 ,
		t_menu.target 				AS col6 
		FROM t_menu 

		UNION

		SELECT 
		'2' AS niveau, 
		t_menu.display_order AS col1 ,  
		t_menu.item 					AS col2 ,
		t_menu.id 						AS col3 ,
		t_sub_menu.item			AS col4 ,
		t_sub_menu.id	 			AS col5,
		t_sub_menu.target 		AS col6 

		FROM t_menu
		INNER JOIN t_sub_menu ON fk_menu_id = t_menu.id 

		ORDER BY col1, niveau, col5
);
-- Two pages in the editor's format, both in the menu
INSERT INTO t_pages (id, title, pending, txt) VALUES (1, 'Accueil', 0, '<!--html-->
<p>Bienvenue sur votre site CMS Lite.</p>');
INSERT INTO t_pages (id, title, pending, txt) VALUES (2, 'Actualités', 0, '<!--html-->
<p>Les dernières nouvelles du site.</p>');
INSERT INTO t_menu (item, target, display_order) VALUES ('Accueil', 'page.php?id=1', 0);
INSERT INTO t_menu (item, target, display_order) VALUES ('Actualités', 'page.php?id=2', 1);
UPDATE t_parameters SET descr = 'page.php?id=1' WHERE ref = 'HOMEPAGE';
-- CMS Lite 4's single stylesheet (replaces style.css, nav.css, editor.css)
UPDATE t_parameters SET descr = '<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="css/site.css">' WHERE ref = 'HTML_META_CSS';
COMMIT;
