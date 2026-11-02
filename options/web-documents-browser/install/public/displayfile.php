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


if (isset($_GET['file'])){
    $file = cms_get('file'); 
}
else{
    $file = '';
}


//////////////////////////////////////////////
//  Read file                               //
//////////////////////////////////////////////

// Rev. 4.3.2 : only a plain file name, in a directory listed in doclist_allowed
if ( !cms_doc_dir_ok($path, $doclist_allowed) || !cms_doc_file_ok($path, $file) ) {
  if ( !headers_sent() ) { http_response_code(404); }
  $path = '';
  $file = '';
}

$output = '';
if ( $file != '' ) {
  $output = file_get_contents($path."/".$file);
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
// In displaymd.php, before calling 'nav.php', '$web_title' is completed with the markdown filename
// Idem in displayfile.php and displayhtml.php

$web_title = $web_title.' - Display "'.cms_h($path."/".$file).'"' ;

// Load Header and Nav (menu)
require dirname(__DIR__) . '/src/nav.php';

echo "<p>&nbsp;</p>";



///////////////////
//      TOP      //
///////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'DISPLAY_FILE_TOP' " ) ;
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

//////////////////////////////////////////////
//  Display file                            //
//////////////////////////////////////////////

/*
if (file_exists($path."/".$file)) {
  echo '<iframe src="'.$path."/".$file.'" width="100%" height="100%" style="border:none;"></iframe>';
} 
*/

// v. 3.1 : PDF viewing was not working on iPad or iPhone when using iFrame !
if ( $file != '' ) {
  $tmp = explode(".", $file) ; 
  $tmp = end($tmp) ;
  $tmp = trim($tmp) ; 
  $lnk = cms_h(cms_doc_url($path, $file)) ;
  if ( strtoupper($tmp) == "PDF" ) {
    echo '<meta http-equiv="refresh" content="0; url='.$lnk.'">'; 
  } 
  else {
    echo '<iframe src="'.$lnk.'" width="100%" height="100%" style="border:none;"></iframe>';
  } 
} 


//////////////////
//    BOTTOM    //
//////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'DISPLAY_FILE_BOTTOM' " ) ;
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
