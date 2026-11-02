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
// Rev. 4.5.2: only files of this type are displayed as a page (a PDF or a
// .docx is never interpreted as HTML)
if ( !cms_doc_dir_ok($path, $doclist_allowed) || !cms_doc_file_ok($path, $file)
     || !preg_match('/\.(html|htm)$/i', $file) ) {
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

$web_title = $web_title.' - Display "'.cms_h($path."/".$file).'"' ;

// Not used for HTML
// Load Header and Nav (menu)
// require __DIR__ . '/nav.php';


/////////////////////////////
//        Log File         //
/////////////////////////////

// $logfile value, ip address, user_agent, etc are defined in config.php
if ($logfile == 1) {
    $stmt = $dbh->prepare( "INSERT INTO t_logfile ( dt, ip_address, user_agent, request_url, page_descr )
                            VALUES ( (:dt) , (:ip_address) , (:user_agent) , (:request_url) , (:page_descr) ) ") ;
    $stmt->bindParam(':dt', $dt);
    $stmt->bindParam(':ip_address', $current_ip);
    $stmt->bindParam(':user_agent', $user_agent);
    $stmt->bindParam(':request_url', $request_url);
    $stmt->bindParam(':page_descr', $web_title);
    $stmt->execute() ;
}


///////////////////
//      TOP      //
///////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'DISPLAY_HTML_TOP' " ) ;
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

echo $output;


//////////////////////
//      BOTTOM      //
//////////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'DISPLAY_HTML_BOTTOM' " ) ;
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


/* Not Used for HTML
// Load Footer (bottom of page)
require dirname(__DIR__) . '/src/footer.php';
*/