
<x-->


![](images/wd_browser.png)

<-xx>

# Web Documents Browser

#### Aspects techniques

<small><small>Dernière mise à jour : 20 jul. 2025</small></small>

<p>&nbsp;</p>

&uarr; [Retour à l'accueil](displaymd.php?dir=Documents&file=A%20propos%20de%20WD%20Browser.A%20propos%20de%20WD%20Browser.md)


<--->



## De quoi avez-vous besoin ?

#### - un hébergement basique suffit

- il suffit que l'hébergement[^1] soit accessible par FTP 
- SSH n'est pas indispensable)
- un espace disque de 50 Mb suffit[^2] 
- langage PHP version 8.2 ou supérieur
- pas besoin de base de données (WD Browser embarque sa propre petite base de données SQLite)



[^1]: Par exemple chez [OVH](https://www.ovhcloud.com/fr/web-hosting/) ou chez [Infomaniak](https://www.infomaniak.com/fr/hebergement/hebergement-web).

[^2]: OVH propose un [nom de domaine associé à un hébergement Web de 100 Mb](https://www.ovhcloud.com/fr/domains/tld/be/) pour quelques euros par an !




#### - localement, sur votre PC ou votre Mac

- d'un logiciel permettant d' "uploader" vos données sur l'hébergement (certains fournisseurs d'hébergement proposent leur propre système) ; à défaut, choisissez par exemple [FileZilla](https://filezilla-project.org/)
- si vous souhaitez modifier les paramètres proposés par défaut, vous aurez également de besoin de [DB Browser for SQLite](https://sqlitebrowser.org/)


Pour ceux qui désirent aller plus loin et restreindre l'accès de certains de leurs documents aux seules personnes autorisées, il est possible de mettre en place des systèmes de mots de passe (par exemple au moyen des fichiers [htaccess](https://help.ovhcloud.com/csm/fr-web-hosting-htaccess-use-cases?id=kb_article_view&sysparm_article=KB0052874)) et [htpasswd](https://httpd.apache.org/docs/2.4/fr/programs/htpasswd.html).






## Quelques conseils :

- Evitez les espaces et les accents dans les noms de répertoire
- Respectez bien la *casse* (majuscules / minuscules)
- Veillez à ce que les **paramètres** présents dans **config.ini** soient corrects !


## Le fichier *config.ini*

- on y indique au système l'endroit où se trouve la base de données des paramètres : l'instruction à y mettre doit ressembler à **db =  ../web_documents_browser.db** ou **db =  ../db/web_documents_browser.db**, suivant l'endroit où vous stockerez la base de données.   
- vous devrez également y mettre le chemin du répertoire que WD Browser peut consulter :  **doclist_allowed= Documents** (ou *doclist_allowed= Documents* ***<big>,</big>*** *Autres documents* si vous avez plusieurs répertoires).

La logique de l'ensemble est celle de l'application [CMS Lite](https://quintilien.net/CMS_Lite)


## La page d'accueil

Le paramétrage par défaut prévoit que WD Browser appelle la page "*Accueil.md*".    
Cette page contient une *redirection* vers la page "A propos de WD Browser".

Vous pouvez modifier le contenu de "*Accueil.md*" et y mettre (au format Markdown) ce que vous estimez nécessaire pour votre site.   
N'oubliez pas d'y prévoir un lien vers la liste des documents !    
En Markdown, il suffira d'écrire : 

```
[Liste des documents](doclist.php?dir=Documents) 
```

Si on souhaite une liste triée dans un certain ordre, il faut préciser le numéro de la colonne à trier (précédé du signe *moins* si on veut un tri en sens inverse).   
Ainsi, pour un tri sur la date (la plus récente au dessus), on indiquera :

```
[Liste des documents](doclist.php?dir=Documents&order=-1) 
```




## Principales commandes de Web Documents Browser


#### Affichage de la liste des documents

- L'affichage du contenu du répertoire Documents peut être appelé depuis n'importe quel endroit de votre site par l'instruction    
**doclist.php?dir=Documents**
- Si vous avez installé WD Browser dans un sous-répertoire, l'instruction devient    
**nom-du-sous-repertoire/doclist.php?dir=Nom_du_répertoire_documents**
- Le *classement* en fonction d'une colonne s'obtient en précisant le *numéro de la colonne*.   
Exemple de classement par catégorie (colonne 4) : *doclist.php?dir=Documents&order=4*
- Le classement *en sens inverse* s'obtient en mettant un *signe moins* devant le numéro de la colonne    
*doclist.php?dir=Documents&order=-4*

#### Pour afficher directement un fichier donné

- pour les fichiers **markdown** : *displaymd.php?dir=Documents&file=nom_du_fichier.md*
- pour les fichiers **html** : *displayhtml.php?dir=Documents&file=nom_du_fichier.html*
- pour les **autres fichiers**, dont les **pdf** : *displayfile.php?dir=Documents&file=nom_du_fichier.pdf*
- **Remarque** : les espaces dans les noms de fichiers doivent être remplacés par ```%20``` pour que les liens fonctionnent.







 
## Quelques infos utiles en rapport avec WD Browser

<h3 id="markdown" >A propos de Markdown</h3>


Markdown permet de se concentrer sur le contenu du texte. Le style et la présentation sont indépendants du texte lui-même. Cela évite de figer le graphisme ou le format, ce qui est indispensable lorsqu'on souhaite publier un texte sur le Web : il suffit de penser à l'affichage qui doit s'adapter selon que l'internaute utilise un smartphone, une tablette ou un ordinateur.

Si vous n'êtes pas familiarisé avec Markdownn, [cliquez ici](displaymd.php?dir=Documents&file=Pourquoi%20Markdown.A%20propos%20de%20WD%20Browser.md).


<h3 id="pandoc" >A propos de Pandoc</h3>

Pandoc, véritable "*couteau-suisse de l'édition*", est un logiciel qui permet de convertir des documents depuis des formats comme Markdown, HTML, docx, etc vers d'autres formats (HTML, Microsoft Word, PDF, PowerPoint, etc).

Pandoc s'utilise en mode *ligne de commande* : par exemple, pour convertir un fichier *Markdown* en fichier *HTML* avec table des matières :

```
pandoc "Exemple de fichier.md"  --css dummy.css -s --toc -o "Exemple de fichier.html"              
```

Explications 

- *-o* (pour *output*) est suivi du nom de fichier à créer
- *--toc* (avec deux tirets) insère une table des matières lors de la création du fichier HTML
- *-s* (pour *standalone*) est obligatoire pour créer une table des matières (cf documentation de Pandoc)
- *--css* (avec deux tirets) est nécessaire pour éviter que Pandoc ne génère un style HTML par défaut (ce qui se produit en "standalone") 
- *dummy.css* renvoie volontairement à une feuille de style inexistante ; ce sera le style précisé dans les paramètres de WD Browser qui sera appliqué.


Vous trouverez aisément d'autres informations sur Pandoc, par exemple sur

- <https://fr.wikipedia.org/wiki/Pandoc>
- <https://pandoc.org/>
- <https://www.arthurperret.fr/cours/pandoc.html>
- <https://www.jdbonjour.ch/cours/markdown-pandoc/>
- <https://e-publish.uliege.be/md2/chapter/sites-de-reference>

<p>&nbsp;</p>






## Utilisation avancée

#### **WD Browser est "prêt à l'emploi"**. <br>Il est toutefois possible d'affiner l'utilisation qui en est faite.

Des paramètres peuvent être ajustés dans la petite base de données "**web_documents_browser.db**" [^3]  .    
La table concernée est "**t_parameters**".     
On y trouvera différents paramètres :  


- *HOMEPAGE* : nom de la page d'accueil

- *DISPLAY_FILE_TOP* qui permet d'associer quelques instructions HTML pour afficher, par exemple, un petit message "*Retour à la liste des fichiers*" en haut de chaque affichage de fichier :    

```
<a href="doclist.php?dir=Documents&order=-1">Retour à la liste des fichiers</a>
```

- *DOCLIST_TOP* qui permet d'agir sur l'affichage en haut de la liste des fichiers, par exemple pour afficher le message "*Retour à l'accueil*".     

```
<a href="displaymd.php?dir=Documents&file=Accueil.md"Accueil</a><br>
```

- *HTML_META_CSS* gère l'appel des feuilles de style de WD Browser lui-même, mais aussi des fichiers Markdown


```
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/nav.css">
<link rel="stylesheet" type="text/css" href="css/ken_burns.css">
<link rel="stylesheet" type="text/css" href="css/animations.css">
```



- *DISPLAY_HTML_TOP* dans lequel on peut prévoir un style qui sera appliqué d'office ai début de chaque code

```
<style>
.container-960px {
  max-width: 960px;
  margin: 0 auto;
  padding: 0 20px;  }
</style>

<div class=" container-960px">
```


- *DISPLAY_MD_TOP* permet d'affiner l'affichage qui précède celui d'un fichier Markdown;




Un simple coup d'oeil sur le contenu de la table "*t_parameters"* vous donnera la liste des autres paramètres disponibles.

[^3]: Pour mémoire, le contenu de la base de données peut être consulté ou modifié au moyen de [DB Browser for SQLite](https://sqlitebrowser.org/).


La base de données "**web_documents_browser.db**" permet d'autres fonctionnalités (notamment la gestion de menus et sous-menus). Cf <https://www.quintilien.net/CMS_Lite>








#### Notes

