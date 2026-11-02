<?php

// Rev. 3.1 (2025-08-18) 

///////////////////
//  Load Config  //
///////////////////

require dirname(__DIR__) . '/src/config.php';


///////////////////////////////////////////////////
//  validate $GET and determinate type of Table  //
///////////////////////////////////////////////////

// Rev. 4.5.0: ?rev (version display) removed: it told anyone which version was installed.

if (isset($_GET['categ'])){
    $categ = cms_get('categ'); 
}    
else{
    $categ = '';
}    
if (isset($_GET['author'])){
    $author = cms_get('author'); 
}    
else{
    $author = '';
}    

if (isset($_GET['order'])){
    $order = cms_get('order'); 
}    
else{
    $order = '';
}    

if (isset($_GET['prev_ord'])){
    $prev_ord = cms_get('prev_ord'); 
}    
else{
    $prev_ord = '';
}    


//////////////////////
//  validate categ  //
//////////////////////

$categ_list   = array();
$author_list  = array();
$order_list   = array();
$toc_selected = 0;

$stmt = $dbh->prepare( "SELECT t_pages.categ FROM t_pages WHERE categ <> '' AND t_pages.pending <> 1 
                        GROUP BY t_pages.categ ORDER BY t_pages.categ" ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    $categ_list[] = $row[0];
}
if ($categ != '') {
    if (!in_array($categ, $categ_list)) {
        // BAD CATEG 
        $toc_selected = -1;
        $categ = '';
    }
    else {
        $toc_selected = 1;
        $web_title = $web_title.' - '.$categ;
    }
}

///////////////////////
//  validate author  //
///////////////////////
$stmt = $dbh->prepare( "SELECT t_pages.author FROM t_pages WHERE author <> '' AND t_pages.pending <> 1 
                        GROUP BY t_pages.author ORDER BY t_pages.author" ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    $author_list[] = $row[0];
}
if ($author != '') {
    if (!in_array($author, $author_list)) {
        // BAD AUTHOR 
        $toc_selected = -1;
        $author = '';
    }
    else {
        $toc_selected = 2;
        $web_title = $web_title.' - '.$author;
    }
}

//////////////////////
//  validate order  //
//////////////////////

$stmt = $dbh->prepare( 'PRAGMA table_info ("t_pages")' ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    $order_list[] = $row[1];
    $order_list[] = $row[1].' DESC';
}
if ($order != '') {
    if (!in_array($order, $order_list)) {
        // BAD ORDER 
        $toc_selected = -1;
        $order = 'id';
    }
}
else {
    $order = 'id';
}

if ($prev_ord != '') {
    if (!in_array($prev_ord, $order_list)) {
        // BAD PREV ORDER 
        $toc_selected = -1;
        $prev_ord = '';
    }
}


// $toc_selected = -1  => bad selection / selection refused
// $toc_selected =  0  => nothing selected
// $toc_selected =  1  => By categ
// $toc_selected =  2  => By author


////////////////////////////
//  Reverting sort order  //
////////////////////////////

if ( $toc_selected > 0 ) {
    if ( $order != '' ) {
        if ( $order == $prev_ord && substr($order, -5) !== ' DESC' ) {   // Rev. 4.5.2: no "DESC DESC"
            $order = $order.' DESC';
        }
        $prev_ord = $order;
        // $order = 'ORDER BY '.$order;
    }
}


///////////////////////////////////////////////
//  Web Page Title IF NOT categ, NOR author  //
///////////////////////////////////////////////

if ( $categ == '' ) {
    if ( $author == '' ) {
        if ( $order == 'categ') {
            $web_title = $web_title.' - '.$col_title_categ_2;
        }
        if ( $order == 'author') {
            $web_title = $web_title.' - '.$col_title_author_2;
        }
        if ( $order == 'date' ) {
            $web_title = $web_title.' - '.$col_title_lastpub;
        }
    }
}


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

//// print_r("Selection: ".$toc_selected." - Order: ->".$order."<-");



// TABLE_OF_CONTENTS_TOP /////////////////////////////////////////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'TABLE_OF_CONTENTS_TOP' " ) ;
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




/////////////////////////////////////////////////////////////////////
// Table of contents = PAGES TITLES by CATEG ordered by ORDER      //
//                     ORDER can be  :  - id (by default)          //
//                                      - title                    //
//                                      - author                   //
//                                      - date                     //
/////////////////////////////////////////////////////////////////////

if ( $toc_selected == 1 ) {
    echo '<h1>'.cms_t($categ).'</h1>' ;
    echo '<table>' ;
    echo '<tr align="left">';
    echo '<th><a href="toc.php?categ='.rawurlencode((string)$categ).'&author=&order=title&prev_ord='.$prev_ord.' ">'.$col_title_title.'<a></th>';
    echo '<th><a href="toc.php?categ='.rawurlencode((string)$categ).'&author=&order=categ_pagenum&prev_ord='.$prev_ord.' ">'.$col_title_pagenum.'<a></th>';
    echo '<th><a href="toc.php?categ='.rawurlencode((string)$categ).'&author=&order=author&prev_ord='.$prev_ord.' ">'.$col_title_author.'<a></th>';
    echo '<th><a href="toc.php?categ='.rawurlencode((string)$categ).'&author=&order=date&prev_ord='.$prev_ord.' ">'.$col_title_date.'<a></th>';
    echo '</tr>';
    $stmt = $dbh->prepare( "SELECT t_pages.id, t_pages.title, t_pages.author, t_pages.date, t_pages.categ, 
                            t_pages.categ_pagenum FROM t_pages 
                            WHERE t_pages.categ = (:categ) AND t_pages.pending <> 1 ORDER BY ".$order);
    $stmt->bindParam(':categ', $categ);
    // $stmt->bindParam(':order', $order);  ORDER IS NOT BINDABLE ...
    // https://stackoverflow.com/questions/2542410/how-do-i-set-order-by-params-using-prepared-pdo-statement
    $stmt->execute() ;
    foreach ( $stmt->fetchAll() AS $row)  {
        $page_title = $row[1];
        if (trim($page_title) == '') {
            $page_title = $title_empty;
        }
        echo '<tr>';
        echo '<td> <a href="page.php?id='.rawurlencode((string)$row[0]).'">  '.cms_t($page_title).'</a> </td>';
        if ($row[5] == 0) {
            $row[5] = '';
        }
        if ($row[5] != '') {
            echo '<td align="right"> <a href="page.php?id='.rawurlencode((string)$row[0]).'">'.cms_t($row[5]).'</td>';
        }
        else {
            echo '<td align="right"> </td>';
        }
        echo '<td> <a href="toc.php?categ=&author='.rawurlencode((string)$row[2]).'&order=&prev_ord='.$prev_ord.' ">'.cms_t($row[2]).'</a> </td>';
        echo '<td>'.cms_t($row[3]).'</td>';
        echo '</tr>';
    }
    echo '</table>' ;
}

////////////////////////////////////////////////
// Table of contents = PAGES TITLES by AUTHOR //
////////////////////////////////////////////////

if ( $toc_selected == 2 ) {
    echo '<h1>'.cms_t($author).'</h1>' ;
    echo '<table>' ;
    echo '<tr align="left">';
    echo '<th><a href="toc.php?categ=&author='.rawurlencode((string)$author).'&order=title&prev_ord='.$prev_ord.' ">'.$col_title_title.'<a></th>';
    echo '<th><a href="toc.php?categ=&author='.rawurlencode((string)$author).'&order=categ&prev_ord='.$prev_ord.' ">'.$col_title_categ.'<a></th>';
    echo '<th><a href="toc.php?categ=&author='.rawurlencode((string)$author).'&order=date&prev_ord='.$prev_ord.' ">'.$col_title_date.'<a></th>';
    echo '</tr>';
    $stmt = $dbh->prepare( "SELECT t_pages.id, t_pages.title, t_pages.categ, t_pages.date, t_pages.author
                            FROM t_pages WHERE t_pages.author = (:author) AND t_pages.pending <> 1 
                            ORDER BY ".$order) ;
    $stmt->bindParam(':author', $author);
    $stmt->execute() ;
    foreach ( $stmt->fetchAll() AS $row)  {
        $page_title = $row[1];
        if (trim($page_title) == '') {
            $page_title = $title_empty;
        }
        echo '<tr>';
        // echo '<td> <a href="./page.php?id='.$row['col1'].'">  '.$row['col1'].'</td>';
        echo '<td> <a href="page.php?id='.rawurlencode((string)$row[0]).'">  '.cms_t($page_title).'</a> </td>';
        echo '<td> <a href="toc.php?categ='.rawurlencode((string)$row[2]).'&author=&order=&prev_ord='.$prev_ord.' "> '.cms_t($row[2]).'</a> </td>';
        echo '<td>'.cms_t($row[3]).'</td>';
        echo '</tr>';
    }
    echo '</table>' ;
}

////////////////////////////////////
//  No categ Nor author selected  // 
////////////////////////////////////

if ( $toc_selected == 0 ) {
    $prev_ord = '';
    if ( $order == 'categ') {
        ///////////////////////////////////////////////
        //  Table of contents = CATEGORIE'S LISTING  // 
        ///////////////////////////////////////////////
        echo '<h1>'.$col_title_categ_2.'</h1>' ;
        echo '<table>' ;
        echo '<tr align="left">';
        echo '<th> </th>';
        echo '</tr>';
        echo '<tr><td><a href="toc.php?categ=&author=&order=&prev_ord='.$prev_ord.' ">  '.$categ_empty.'</a> </td></tr>' ;
        $stmt = $dbh->prepare( "SELECT t_pages.categ FROM t_pages WHERE categ <> '' AND t_pages.pending <> 1 
                               GROUP BY categ ORDER BY categ ") ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            echo '<tr>';
            echo '<td><a href="toc.php?categ='.rawurlencode((string)$row[0]).'&author=&order=&prev_ord='.$prev_ord.' ">'.cms_t($row[0]).'</a></td>';
            echo '</tr>';
        }
    echo '</table>' ;
    }

    if ( $order == 'author') {
        ////////////////////////////////////////////
        //  Table of contents = AUTHOR's LISTING  //
        ////////////////////////////////////////////
        echo '<h1>'.$col_title_author_2.'</h1>';
        echo '<table>';
        echo  '<tr align="left">';
        echo  '<th> </th>';
        echo '</tr>';
        $stmt = $dbh->prepare( "SELECT t_pages.author FROM t_pages WHERE author <> '' AND t_pages.pending <> 1 
                               GROUP BY author ORDER BY author" ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            echo '<tr>';
            echo '<td><a href="toc.php?categ=&author='.rawurlencode((string)$row[0]).'&order=&prev_ord='.$prev_ord.' ">  '.cms_t($row[0]).'</a> </td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    if ( $order == 'date' ) {
        ////////////////////////////////////////////////
        //  Table of contents = PAGES TITLES by DATE  //
        ////////////////////////////////////////////////
        echo "<h1>".$col_title_lastpub."</h1>";
        echo '<p></p>';
        echo '<table>' ;
        echo '<tr align="left">';
        echo '<th>'.$col_title_date.'</th>';
        echo '<th>'.$col_title_title.'</th>';
        echo '<th>'.$col_title_cat_used.'</th>';
        echo '<th>'.$col_title_author.'</th>';
        echo '</tr>';
        echo '  ' ;
        $stmt = $dbh->prepare( "SELECT t_pages.id, t_pages.title, t_pages.author, t_pages.date, t_pages.categ
                                FROM t_pages WHERE t_pages.date <> '' 
                                AND t_pages.pending <> 1 ORDER BY t_pages.date DESC" ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            echo '<tr>';
            echo '<td>'.cms_t($row[3]).'</td>';
            echo '<td> <a href="page.php?id='.rawurlencode((string)$row[0]).'">  '.cms_t($row[1]).'</a> </td>';
            echo '<td>'.cms_t($row[4]).'</td>';
            echo '<td>'.cms_t($row[2]).'</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    if ( $order == 'id' )  {
        ///////////////////////////////////////////////////////
        // Table of contents = PAGES TITLES WHEN CATEG EMPTY //
        ///////////////////////////////////////////////////////
        echo "<h1>".$categ_empty."</h1>";     
        echo '<p></p>  ';
        echo '<table>';
        echo '<tr align="left">';
        echo '<th>'.$col_title_title.'</th>';
        echo '<th>'.$col_title_author.'</th>';
        echo '<th>'.$col_title_date.'</th>';
        echo '</tr>';
        echo '  ';
        $stmt = $dbh->prepare( "SELECT t_pages.id, t_pages.title, t_pages.author, t_pages.date, t_pages.categ
                                FROM t_pages WHERE t_pages.categ = '' AND t_pages.pending <> 1 
                                ORDER BY t_pages.title" ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            $page_title = $row[1];
            if (trim($page_title) == '') {
                $page_title = $title_empty;
            }
            echo '<tr align="left">';
            echo '<td> <a href="page.php?id='.rawurlencode((string)$row[0]).'">  '.cms_t($page_title).'</a> </td>';
            echo '<td>'.cms_t($row[2]).'</td>';
            echo '<td>'.cms_t($row[3]).'</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}


// TABLE_OF_CONTENTS_BOTTOM /////////////////////////////////////////////////////
$html = array();
$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'TABLE_OF_CONTENTS_BOTTOM' " ) ;
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


// Load Footer (bottom of page)
require dirname(__DIR__) . '/src/footer.php';

