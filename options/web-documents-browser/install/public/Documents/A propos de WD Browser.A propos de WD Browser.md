
<x-->


![](images/wd_browser.png)


<-xx>


# Web Documents Browser

#### Vos documents se classent tout seuls ... ou presque !


<small><small>Dernière mise à jour : 20 jul. 2025</small></small>

<--->


[![](images/web_documents_browser.png)](doclist.php?dir=Documents&order=-1)


### Un exemple de ce que ça donne ? [C'est par ici](doclist.php?dir=Documents&order=-1) ...


<!-- - Le classement proposé par défaut est celui par date (la date la plus récente en premier).--->
- Pour classer les documents, il suffit de **cliquer sur le titre d'une des colonnes**.
- Un **deuxième clic** sur la même colonne affiche le **classement en sens inverse**.
- Pour **rechercher**, appuyer simultanément sur les **touches Control et F** (ce raccourci correspond à la fonction « chercher » de la plupart des navigateurs).
- Si **on clique sur un des auteurs**, seuls les documents de l'auteur s'affichent.
- Si **on clique sur une des catégories**, seuls les documents de cette catégorie s'affichent.
- Si **on clique sur le titre** du document, il s'affiche et/ou se télécharge (voir note [^1]).



[^1]: L'**affichage ou** le **téléchargement** se fait en fonction du type de fichier :   
**- pdf** : le fichier **s'affiche** dans une visionneuse gérée par le navigateur ; la plupart des navigateurs permettent de zoomer, d'afficher une table des matières du document et/ou de télécharger le fichier ;   
**- md** ([Markdown](displaymd.php?dir=Documents&file=Informations%20techniques.A%20propos%20de%20WD%20Browser.md#markdown)) : le contenu du fichier **s'affiche en tenant compte du style de votre site Web** (grâce à l'utilisation des feuilles de style intégrées dans WD Browser)   
**- html** : le fichier HTML **s'affiche tel qu'il a été défini par le code** ; avantage, si le fichier HTML a été [généré par Pandoc](displaymd.php?dir=Documents&file=Informations%20techniques.A%20propos%20de%20WD%20Browser.md#pandoc), il est possible d'y intégrer automatiquement une table des matières ! Pratique pour faciliter la consultation de vos très gros documents !   
**- docx, xlsx et autres extensions** : le fichier est **proposé au téléchargement** (ce qui est peut-être à éviter si vous ne voulez pas que vos documents s'éparpillent dans la nature ...) 



### Eh ben ça alors, comment c'est-y possible ?!

Tout simplement, **en structurant les noms que vous donnez à vos fichiers**, en séparant les critères de classement par un point (voir note [^2]).


[^2]: Exemples de **noms de fichiers**    
**- Pâte à crèpes<big>.</big>pdf** : si le fichier ne comporte qu'un seul point suivi de l'extension, son nom et son extension sont *affichés de manière classique*      
**- 20250315<big>.</big>Pâte à crèpes<big>.</big>pdf** : si le nom et l'extension du fichier sont précédés d'un nombre suivi d'un point, le nombre est présenté séparément dans la colonne **date**. On peut se contenter d'un millésime (*2025.Pâte à crèpes.pdf*) ou d'un millésime et d'un mois (*202503.Pâte à crèpes.pdf*)    
**- Pâte à crèpes<big>.</big>Recettes de cuisine<big>.</big>pdf** : si, entre le nom du fichier et son extension, on intercale un point suivi d'un texte, ce texte est affiché dans la colonne **catégorie**.   
**- Pâte à crèpes<big>.</big>Le Marmiton.Recettes de cuisine<big>.</big>pdf** : si, entre le nom du fichier et sa catégorie, on intercale à nouveau un point suivi d'un texte, ce dernier est affiché dans la colonne **auteur**. Dans le cas où on veut préciser un auteur mais pas de catégorie, mettre deux points avant l'extension (exemple : Pâte à crèpes<big>.</big>Le Marmiton<big>..</big>pdf)      
**- 20250315<big>.</big>Pâte à crèpes<big>.</big>Le Marmiton<big>.</big>Recettes de cuisine<big>.</big>pdf** : si on combine ce qui précède !



### Comment ça marche ?

Vous n'avez qu'à « téléverser » (*uploader*) vos **documents** et vos **images** sur l'hébergement de votre site, dans les répertoires correspondants, par exemple au moyen de l'application **FileZilla** (voir note [^3])..

[^3]: [**FileZilla**](https://filezilla-project.org/) est un client FTP (File Transfert Protocol).    
Son but est de vous permettre de connecter votre ordinateur à un serveur distant pour charger ou télécharger des fichiers sur ce dernier en toute simplicité.  Cet outil est la référence depuis des années dans sa catégorie, il est clairement le leader du secteur à travers le monde.  *Source : 01.net*


### Structure du site sur votre hébergement

- **WD Browser peut coexister avec n'importe quelle autre l'application web** ; il faut juste veiller, dans ce cas, à ce que les fichiers de WD Browser (*php*, *config*, etc) soient dans un répertoire distinct ("*affilies*" dans l'exemple ci-dessous).  
- C'est dans ce répertoire (ou, à défaut, dans www) qu'on prévoira les répertoires "**Documents**" et "**images**" utilisés par WD Browser.
- Le contenu du ficbier ***config.ini*** de WD Browser doit être adapté en conséquence (voir les [aspects techniques](displaymd.php?dir=Documents&file=Informations%20techniques.A%20propos%20de%20WD%20Browser.md)).
- En général, un hébergeur vous propose un serveur (ou une partie de serveur) dans lequel on trouve une "**racine**" ( "**/**" ), qui n'est pas directement accessible en tant que telle par les internautes. C'est à cet endroit qu'il faudra placer la petite base de données des paramètres de WD Browser.

[![](images/filezilla.png)](displaymd.php?dir=Documents&file=Informations%20techniques.A%20propos%20de%20WD%20Browser.md)


<p>&nbsp;</p>


#### Notes




