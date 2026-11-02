<?php

// Rev. 3.1 (2025-08-18) 
// Rev. 4.3.2 (2026-10-29) : security fix

///////////////////
//  Load Config  //
///////////////////

require dirname(__DIR__) . '/src/config.php';
require dirname(__DIR__) . '/src/doclist_lib.php';   // Rev. 4.7.0 : option Web Documents Browser


///////////////////////////////////////////////////
//  validate $GET                                //
///////////////////////////////////////////////////

// Rev. 4.5.0: ?rev (version display) removed: it told anyone which version was installed.

if (isset($_GET['dir'])){
    $path = cms_get('dir'); 
}    
else{
    $path = '';
}    

// $doclist_allowed = array(), loaded in config.php, contains directories allowed for scanning
// Rev. 4.3.2 : a directory that is not listed gives an empty list
if ( cms_doc_dir_ok($path, $doclist_allowed) ) {
  $doclist_allowed = scandir($path);
}
else {
  if ( $path != '' && !headers_sent() ) { http_response_code(404); }
  $path = '';
  $doclist_allowed = array();
}

if (isset($_GET['order'])) {
    $order = cms_get('order'); 
    if( is_numeric( $order ) ) {
        $order = intval($order) ;
    }
    else {
        $order = intval(0) ;
    }
}
else {
    // default is order by name
    $order = intval(2) ;
}

if (isset($_GET['prev_ord'])){
    $prev_ord = cms_get('prev_ord'); 
    if( is_numeric( $prev_ord ) ) {
        $prev_ord = intval($prev_ord) ;
    }
    else{
        $prev_ord = intval(0) ;
    }
}
else {
    $prev_ord = intval(0) ;
}

////////////////////////////
//  Reverting sort order  //
////////////////////////////

$original_order = $order;
$original_prev_ord = $prev_ord;

if ( $order != 0 ) {
    if ( $order == $prev_ord ) {
        $order = $order * -1 ;
    }
    $prev_ord = $order;
}


//////////////////////////////////////////////
//  Load Markdown converter and Nav (Menu)  //
//////////////////////////////////////////////

// Load Markdown converter
// require __DIR__ . '/vendor/autoload.php';
// use League\CommonMark\GithubFlavoredMarkdownConverter;
// v. 3.0
require dirname(__DIR__) . '/src/markdown_converter.php';

// In page.php, before calling 'nav.php', '$web_title' is completed by '$web_title = $web_title.' - '.trim($row[0])'
$web_title = $web_title.' - Doclist : '.cms_h($path) ; 

// Load Header and Nav (menu)
require dirname(__DIR__) . '/src/nav.php';

//////////////////////////////////////////////
//                 Filelist                 //
//////////////////////////////////////////////

// link.date.name.author.category.extension
$filelist = array();

foreach($doclist_allowed as $filefound){
  
// v. 3.1 :   HTML files are intentionally no longer displayed
// because it now allows you to put (in the "doclisted" directory) an index.html (invisible in doclist.php,
// therefore) which contains nothing, this in order to prevent the Internet user from being able to view the 
// contents of a directory in their browser
//
//  if(preg_match("#\.(jpg|jpeg|png|gif|bmp|tif|pdf|doc|docx|xls|xlsx|zip|md|txt|html)$#i", $filefound)){
//

  if(preg_match("#\.(jpg|jpeg|png|gif|bmp|tif|pdf|doc|docx|xls|xlsx|zip|md|txt)$#i", $filefound)){
    // source https://www.c2script.com/scripts/lister-les-fichiers-d-un-dossier-en-php-exemples-simples-s31.html
    // Starts with a dot "." (escaped with a backslash, since a dot otherwise means "any character")
    // "|" between parentheses means "or" (several possibilities: jpg or jpeg or png...)
    // "$" means the file name must end with the given string.
    
    $filepart = explode(".", $filefound);

    $lnk = $filefound;

    if ( count ($filepart) == 2 ) {
      if( !ctype_digit($filepart[0]) ) {
        array_push($filelist , [ $lnk , 
                                " " , 
                                trim($filepart[0]), 
                                " ", 
                                " ", 
                                trim($filepart[1]) 
                               ] )  ;
      } 
      else {
        array_push($filelist , [ $lnk , 
                                trim($filepart[0]), 
                                "?" , 
                                " ", 
                                " ", 
                                trim($filepart[1]) 
                               ] )  ;
      } 
    } 

    if ( count ($filepart) == 3 ) {
      if( !ctype_digit($filepart[0]) ) {
        array_push($filelist , [ $lnk , 
                                " " , 
                                trim($filepart[0]), 
                                " ", 
                                trim($filepart[1]), 
                                trim($filepart[2]) 
                               ] )  ;
      } 
      else {
        array_push($filelist , [ $lnk , 
                                trim($filepart[0]), 
                                trim($filepart[1]), 
                                " ", 
                                " ", 
                                trim($filepart[2]) 
                               ] )  ;
      } 
    } 

    if ( count ($filepart) == 4 ) {
      if( !ctype_digit($filepart[0]) ) {
        array_push($filelist , [ $lnk , 
                                " " , 
                                trim($filepart[0]), 
                                trim($filepart[1]), 
                                trim($filepart[2]), 
                                trim($filepart[3]) 
                               ] )  ;
      } 
      else {
        array_push($filelist , [ $lnk , 
                                trim($filepart[0]), 
                                trim($filepart[1]), 
                                " ", 
                                trim($filepart[2]), 
                                trim($filepart[3]) 
                               ] )  ;
      } 
    } 

    if ( count ($filepart) == 5 ) {
      if( !ctype_digit($filepart[0]) ) {
        array_push($filelist , [ $lnk , 
                                " " , 
                                trim($filepart[0]), 
                                trim($filepart[1]), 
                                trim($filepart[2]), 
                                trim($filepart[3]) 
                               ] )  ;
      } 
      else {
        array_push($filelist , [ $lnk , 
                                trim($filepart[0]), 
                                trim($filepart[1]), 
                                trim($filepart[2]), 
                                trim($filepart[3]), 
                                trim($filepart[4]) 
                               ] )  ;
      } 
    } 
  }    
} 

// source https://www.php.net/manual/fr/function.array-multisort.php

$lnk    = array_column($filelist, 0);
$date   = array_column($filelist, 1);
$name   = array_column($filelist, 2);
$author = array_column($filelist, 3);
$categ  = array_column($filelist, 4);
$ext    = array_column($filelist, 5);


if ($order == 1 ){
  array_multisort($date, SORT_ASC, $filelist);
}
if ($order == -1 ){
  array_multisort($date, SORT_DESC, $filelist);
}


if ($order == 2 ){
  array_multisort($name, SORT_ASC, $filelist);
}
if ($order == -2 ){
  array_multisort($name, SORT_DESC, $filelist);
}


if ($order == 3 ){
  array_multisort($author, SORT_ASC, $filelist);
}
if ($order == -3 ){
  array_multisort($author, SORT_DESC, $filelist);
}


if ($order == 4 ){
  array_multisort($categ, SORT_ASC, $filelist);
}
if ($order == -4 ){
  array_multisort($categ, SORT_DESC, $filelist);
}


if ($order == 5 ){
  array_multisort($ext, SORT_ASC, $filelist);
}
if ($order == -5 ){
  array_multisort($ext, SORT_DESC, $filelist);
}


////////////////////////////////////////////
//  Get and validate categ and/or author  //
////////////////////////////////////////////

// nota : inspired by toc.php in cms_lite

if (isset($_GET['categ'])){
    $get_categ = cms_get('categ'); 
}    
else{
    $get_categ = '';
}    
if (isset($_GET['author'])){
    $get_author = cms_get('author'); 
}    
else{
    $get_author = '';
}    


if ($get_categ != '') {
    if (!in_array($get_categ, $categ)) {
        // BAD CATEG 
        $get_categ = '';
    }
}

if ($get_author != '') {
    if (!in_array($get_author, $author)) {
        // BAD AUTHOR 
        $get_author = '';
    }
}


///////////////////////
//    DOCLIST TOP    //
///////////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'DOCLIST_TOP' " ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
  array_push($html , $row[0]); 
}
if ( empty($html) ) {
  echo '<style> ';
  echo 'td {
        padding-right: 30px;}' ;
        echo '  th {  ';
        // echo ('    background: rgb(61,92,122); ');
        // echo ('    color: white; ');
        echo '  }';
        // echo '    tr:nth-child(even) {background: lightblue}';
        echo '    tr:nth-child(even) {background: #d7f6ff}';
        // echo '    tr:nth-child(odd) {background: whitesmoke}';
        echo '    tr:nth-child(odd) {background: #f6fbff}';
        echo '</style>';
}
else {
  for($i = 0; $i < count($html); $i++) {
    echo $html[$i] ;
  }
}

// Rev. 4.3.2 : links built with http_build_query (encoding) and cms_h (escaping)
function doclist_link($script, $params, $label) {
  return '<a href="'.cms_h($script.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986)).'">'.cms_h($label).'</a>';
}

echo "<table>";

echo "<tr align='left'>";
// echo "  <th>Link</th>";

$heads = array( 1 => 'Date', 2 => 'Titre', 3 => 'Auteur', 4 => 'Catégorie', 5 => 'Type' );
foreach ( $heads as $num => $label ) {
  echo "  <th>".doclist_link('doclist.php', array( 'dir' => $path, 'order' => $num, 'prev_ord' => $prev_ord,
                                                   'categ' => $get_categ, 'author' => $get_author ), $label)."</th>";
}
echo "</tr>";


foreach($filelist as $filepart){

  $line_to_display = TRUE;

  if ( $get_categ !="" and $filepart[4] != $get_categ ) {
    $line_to_display = FALSE;
  }   
  if ( $get_author !="" and $filepart[3] != $get_author ) {
    $line_to_display = FALSE;
  }   
  
  if ( $line_to_display == TRUE ) {

    echo "<tr>";
    // v. 3.0 date format modified
    if ( strlen($filepart[1]) == 8 ) {
      echo "<td>".cms_h(substr($filepart[1], 0, 4)."-".substr($filepart[1], 4, 2)."-".substr($filepart[1], 6, 2))."</td>";  
    }   
    else {
      echo "<td>".cms_h($filepart[1])."</td>";
    }   

    if ( strtoupper($filepart[5]) == "MD" ) {
      $script = 'displaymd.php';
    }   
    elseif ( strtoupper($filepart[5]) == "HTML" ) {
      $script = 'displayhtml.php';
    }   
    else {
      $script = 'displayfile.php';
    }
    echo "<td>".doclist_link($script, array( 'dir' => $path, 'file' => $filepart[0] ), $filepart[2])."</td>";

    echo "  <td>".doclist_link('doclist.php', array( 'dir' => $path, 'order' => $original_order, 'prev_ord' => $original_prev_ord,
                                                     'categ' => $get_categ, 'author' => trim($filepart[3]) ), $filepart[3])."</td>";

    echo "  <td>".doclist_link('doclist.php', array( 'dir' => $path, 'order' => $original_order, 'prev_ord' => $original_prev_ord,
                                                     'categ' => trim($filepart[4]), 'author' => $get_author ), $filepart[4])."</td>";

    echo "<td>".cms_h($filepart[5])."</td>";
    echo "</tr>";
  }
} 

echo "</table>";

//////////////////////////
//    DOCLIST BOTTOM    //
//////////////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'DOCLIST_BOTTOM' " ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
  array_push($html , $row[0]); 
}
if ( empty($html) ) {
  echo '<p>&nbsp;</p> ';
}
else {
  for($i = 0; $i < count($html); $i++) {
     echo $html[$i] ;
 }
}


// Load Footer (bottom of page)
require dirname(__DIR__) . '/src/footer.php';

