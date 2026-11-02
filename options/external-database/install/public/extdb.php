<?php

// Rev. 3.1 (2025-08-18) 
// Rev. 4.3.2 (2026-10-29) : security fix

///////////////////
//  Load Config  //
///////////////////

require dirname(__DIR__) . '/src/config.php';



//////////////////////
//  validate $GET   //
//////////////////////
// Display rev. 

// Rev. 4.5.0: ?rev (version display) removed: it told anyone which version was installed.

if (isset($_GET['db'])) {
    $db_name = cms_get('db'); 
}    
else {
    $db_name = '';
}    

if (isset($_GET['id'])){
    $rec_id = cms_get('id'); 
    if( !ctype_digit($rec_id)) {
       $rec_id = 0;
    }
}
else {
    $rec_id = intval(0) ;
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
    // default is order by id
    $order = intval(0) ;
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


///////////////////////////////////////////////////////////
//  Check DB                                             //
//  external_db_allowed: lines of config.ini             //
///////////////////////////////////////////////////////////

// Rev. 4.7.0: read here ("external database" option), no longer in src/config.php.
// Row 0 empty, as before: the loop below starts at 1.
$external_databases_allowed = array(array());
foreach (cms_config_ini($config_dir)['external_db_allowed'] ?? [] as $v) {
    $external_databases_allowed[] = $v;
}

$db_selected      = '';
$view_1           = '';
$view_2           = '';

// first row (row 0) of $external_databases_allowed[] exists but is always empty ...
// check begins at row = 1
$i = 1;
while ($i < count($external_databases_allowed) ) {
    $tmp = explode( "," , $external_databases_allowed[$i] );
    // echo '<h3>'.$tmp[0].'</h3>';
    if ( trim( $tmp[0] ) == trim( $db_name ) )  {
        if ( count($tmp) >= 4 ) {
            $db_selected = trim( $tmp[1] ) ; 
            $view_1      = trim( $tmp[2] ) ;
            $view_2      = trim( $tmp[3] ) ;
        }
    }
    $i++;
}


// Rev. 4.3.2 : a database name that is not in config.ini is not echoed back
if ( $db_selected == '' ) {
    $db_name = '';
}

$col_title  = array() ;
$row_title  = array() ;

if ( $db_selected != '' )  {
    $dbx = new PDO('sqlite:'.$db_selected) 
           or die("Unable to connect to sqlite database");
    $dbx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $dbx->prepare( 'PRAGMA table_info (`'.$view_1.'`)' ) ;
    $stmt->execute() ;
    foreach ( $stmt->fetchAll() AS $row)  {
        $col_title[]  = $row[1];
    }    

    $stmt = $dbx->prepare( 'PRAGMA table_info (`'.$view_2.'`)' ) ;
    $stmt->execute() ;
    foreach ( $stmt->fetchAll() AS $row)  {
        $row_title[]  = $row[1];
    }    
}    



///////////////////////////////////
//  validate order               //
//  order = col (=field) number  //
//  if order > 0 : order ASC     //
//  if order < 0 : order DESC    //
///////////////////////////////////

if ( abs ($order) > ( count( $col_title ) - 1 )  ) {
    $order = 0 ;
}

// print_r('id='.$rec_id.' - order = '.$order.' -  previous = '.$prev_ord.' - nbre col = '.count( $col_title )) ;


////////////////////////////
//  Reverting sort order  //
////////////////////////////

if ( $order != 0 ) {
    if ( $order == $prev_ord ) {
        $order = $order * -1 ;
    }
    $prev_ord = $order;
}


$web_title = $web_title.' - '.$db_name ;


//////////////////////////////////////////////
//  Load Markdown converter and Nav (Menu)  //
//////////////////////////////////////////////

// Load Markdown converter
// require __DIR__ . '/vendor/autoload.php';
// use League\CommonMark\GithubFlavoredMarkdownConverter;
// v. 3.0
require dirname(__DIR__) . '/src/markdown_converter.php';


// Load Header and Nav (menu)
require dirname(__DIR__) . '/src/nav.php';


//////////////////////////////////////
//  BROWSING EXTERNAL DATABASE dbx  //
//////////////////////////////////////

$tmp_db       = rawurlencode(trim($db_name));


if ( $db_selected != '' ) {
    if ( $rec_id == 0 )  {
        //////////////////////////////////////////////
        //  $rec_id == 0 means browse ALL records  //
        //////////////////////////////////////////////


        ////////////////////////////
        //  EXTERNAL_DB_LIST_TOP  //
        ////////////////////////////
        $html = array();
        $stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                                WHERE t_parameters.ref = 'EXTERNAL_DB_LIST_TOP' " ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
          array_push($html , $row[0]); 
        }
        if ( empty($html) ) {
            echo '<style> ';
            echo '  th {  ';
            // echo ('    background: rgb(61,92,122); ');
            // echo ('    color: white; ');
            echo '  }';
            // echo '    tr:nth-child(even) {background: lightblue}';
            echo '    tr:nth-child(even) {background: #d7f6ff}';
            // echo '    tr:nth-child(odd) {background: whitesmoke}';
            echo '    tr:nth-child(odd) {background: #f6fbff}';
            echo '</style>
                  </head>
                  <body> 
                  <p> </p>' ;
            echo '<h2 align="center">'.cms_h($db_name).'</h2>';
            echo '<p align="center"><small>'.$label_ctrl_f.'</small></p>';
            echo '</div>';
            // exiting div class="container-960px"
            echo '<table align="center">' ;
        }
        else {
          for($i = 0; $i < count($html); $i++) {
             echo $html[$i] ;
         }
        }


        echo '<tr align="left">';
            // column titles
            echo '<td></td>';
            $i = 1;  // NOT "$i = 0;" because first col (id) is intentionally not displayed 
            while ($i < count($col_title) ) {
                echo '<th><a href=extdb.php?db='.$tmp_db.'&id=&order='.$i.'&prev_ord='.$prev_ord.'><small>'.$col_title[$i].' </small></a></th>';
                $i++;
            }   
            echo '<td></td>';
        echo '</tr>';
        // 
        // the delimiter used to defined view's or field's name is ` (ASCII code 96) 
        // so as to handle the case where the name contains quote or double quote 
        // So "`". is placed before and ."`" after

        // Rev. 2.5 (2025-03-18)  : bug corrected when order == 0 (order by default)
        if ($order == 0) {
            $stmt = $dbx->prepare( "SELECT * FROM `".$view_1."`" ) ; 
        } elseif ($order > 0) {
            $stmt = $dbx->prepare( "SELECT * FROM `".$view_1."` ORDER BY `".$col_title[ abs($order) ]."`" ) ;
        } else {
            $stmt = $dbx->prepare( "SELECT * FROM `".$view_1."` ORDER BY `".$col_title[ abs($order) ]."` DESC") ; 
        }

        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            echo '<tr>';
                echo '<td></td>';
                $i = 1;  // NOT "$i = 0;" because first col (id) is intentionally not displayed 
                while ($i < count($col_title) ) {
                    echo '<td valign="top" >';
                    if ( $i == 1 ) {
                        echo '<a href="extdb.php?db='.$tmp_db.'&id='.$row[0].'">'.$row[$i].'</a>';
                    }
                    else {
                        echo $row[$i];
                    }
                    echo '</td>';
                    $i++;
                }
                echo '<td></td>';
            echo '</tr>';
        }


        /////////////////////////////////
        //   EXTERNAL_DB_LIST_BOTTOM   //
        /////////////////////////////////
        $html = array();
        $stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                                WHERE t_parameters.ref = 'EXTERNAL_DB_LIST_BOTTOM' " ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
          array_push($html , $row[0]); 
        }
        if ( empty($html) ) {

            echo '</table>' ;
            echo '<p> </p>';
            echo '<p align="center">';
            echo '&nbsp;&uarr; &nbsp;<a href="#top-of-page" ><small>'.$label_top_of_page.'</a></small><br>' ;
            echo '</p>';

        }
        else {
          for($i = 0; $i < count($html); $i++) {
             echo $html[$i] ;
         }
        }

    }
    else {
        ////////////////////////////////////////////////
        //  $rec_id NOT = 0 means browse ONE records  //
        ////////////////////////////////////////////////


        ////////////////////////////////
        //   EXTERNAL_DB_DETAIL_TOP   //
        ////////////////////////////////
        $html = array();
        $stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                                WHERE t_parameters.ref = 'EXTERNAL_DB_DETAIL_TOP' " ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
          array_push($html , $row[0]); 
        }
        if ( empty($html) ) {
            echo '<style> ';
            echo '</style>';
        }
        else {
          for($i = 0; $i < count($html); $i++) {
             echo $html[$i] ;
         }
        }




        echo '</head>
              <body> ';
        $stmt = $dbx->prepare( "SELECT * FROM `".$view_2."` WHERE id = (:rec_id)  ") ; 
        $stmt->bindParam(':rec_id', $rec_id);
        $stmt->execute() ;
        // Rev. 4.5.2: missing record -> 404 (previously, leftovers of a previous query)
        $row = $stmt->fetch(PDO::FETCH_NUM);
        if ( $row === false ) {
            if ( !headers_sent() ) { http_response_code(404); }
            echo '<p>'.$label_not_found_ext.'</p></body></html>';
            exit;
        }
        // echo '<h2>'.$row[1].'</h2>';
        echo '<table>' ;
        echo '<tr>';
            echo '<td>';
            echo '&nbsp;';
            echo '</td>';
            echo '<td>';
            echo '<h2>'.$row[1].'</h2>';
            echo '</td>';
        echo '</tr>';
        $i = 0;  // the first column (id) can optionally be hidden if "$i = 1;"
        while ($i < count($row_title) ) {
            echo '<tr>';
                echo '<td align="right" valign="top" ><small>'.$row_title[$i].' :</small></td>';
                echo '<td align="left" valign="top">';
                $output = '';
                if ( $row[$i] != '' ) {
                    // $output = (new GithubFlavoredMarkdownConverter())->convert( $row[$i] )->getContent();
                    // v. 3.0
                    $output = $converter->convert($row[$i]);
                }
                else {
                    // $output = (new GithubFlavoredMarkdownConverter())->convert('<p>&nbsp;</p>')->getContent();
                    $output = $converter->convert('<p>&nbsp;</p>');
                }
                echo $output;
                echo '</td>';
            echo '</tr>';
            $i++;
        }

        echo '</table>' ;
        echo '<p> </p>';

        ///////////////////////////////////
        //   EXTERNAL_DB_DETAIL_BOTTOM   //
        ///////////////////////////////////
        $html = array();
        $stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                                WHERE t_parameters.ref = 'EXTERNAL_DB_DETAIL_BOTTOM' " ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
          array_push($html , $row[0]); 
        }
        if ( empty($html) ) {
            echo '&nbsp;&uarr; &nbsp;<a href="#top-of-page"><small>'.$label_top_of_page.'</a></small><br>' ;
            echo '&larr; <a href=extdb.php?db='.$tmp_db.'><small>'.$label_previous.'</small></a>';
        }
        else {
          for($i = 0; $i < count($html); $i++) {
             echo $html[$i] ;
         }
        }


    }

}



// Load Footer (bottom of page)
require dirname(__DIR__) . '/src/footer.php';
