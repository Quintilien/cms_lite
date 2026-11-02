<?php

// Rev. 4.1.0 (2026-09-25) : version (voir VERSION) ; aucun changement fonctionnel depuis 4.0.0
// Rev. 4.0.0 (2026-09-25): HTML pages from the editor (<!--html--> marker)
// Rev. 3.1 (2025-08-18) 


if (isset($_GET['id'])){
    // print_r('GET received');
    $rec_id = is_string($_GET['id']) ? $_GET['id'] : '';   // Rev. 4.5.2 (config.php, hence cms_get(), is not loaded yet)
    // basic precaution ...
    if( !ctype_digit($rec_id)) {
       // print_r('GET not valid');
       $rec_id = 0;
    }
}
else{
    // print_r('GET NOT received');
    $rec_id = intval(0);
}


// Load Config
require dirname(__DIR__) . '/src/config.php';


// Rev. 4.5.0: ?rev (version display) removed: it told anyone which version was installed.
    

// Load Markdown converter
// require __DIR__ . '/vendor/autoload.php';
// use League\CommonMark\GithubFlavoredMarkdownConverter;
// v. 3.0
require dirname(__DIR__) . '/src/markdown_converter.php';


// $web_title is initialized in 'config.php' and displayed by 'nav.php'
$stmt = $dbh->prepare( "SELECT t_pages.title FROM t_pages WHERE t_pages.id = (:rec_id)   
                        AND t_pages.pending <> 1 ") ;
$stmt->bindParam(':rec_id', $rec_id);
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    if ( trim($row[0]) != '' ) {    
        $web_title = $web_title.' - '.trim($row[0]);
    }
}


// Load Header and Nav (menu)
require dirname(__DIR__) . '/src/nav.php';


/////////////////////////
//      Main page      //
/////////////////////////
$output          = '';
$date            = '';
$categ           = '';
$current_pagenum = 0 ;
$author          = '';
$tmp             = '';


$stmt = $dbh->prepare( "SELECT t_pages.title, t_pages.date, t_pages.categ, t_pages.categ_pagenum,
                        t_pages.author, t_pages.txt FROM t_pages WHERE t_pages.id = (:rec_id) 
                        AND t_pages.pending <> 1 " ) ;
$stmt->bindParam(':rec_id', $rec_id);
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    // Title management
    // $output = (new GithubFlavoredMarkdownConverter())->convert('# '.$row[0])->getContent();
    // v. 3.0
    // Rev. 4.5.2: title displayed as is, without Markdown conversion ("[x](javascript:...)"
    // became a link); same rendering as the former "# title".
    $output = '<h1>'.cms_t($row[0])."</h1>\n";

    echo $output   ;

    $date            = $row[1];
    $categ           = $row[2];
    $current_pagenum = $row[3];
    $author          = $row[4];

    // category, author and date, if exists, concatenate on a single line with bullets
    $display_date    = '';
    $display_categ   = '';
    $display_author  = '';
    $tmp    = '';
    // "toc" pages = Table Of Contents  
    // toc.php?categ=...&author=...&order=...
    if ( $categ != '' )  {
        // $display_categ  = $label_categ.' <a href="toc.php?categ='.$categ.'&author=&order=&prev_ord=">'.$categ.'</a>' ;    
        $display_categ  = $label_categ.' <a href="toc.php?categ='.rawurlencode((string)$categ).'">'.cms_t($categ).'</a>' ;    
    } 
    if ( $author != '' ) {
        // $display_author = $label_author.' <a href="toc.php?categ=&author='.$author.'&order=&prev_ord=">'.$author.'</a>' ;
        $display_author = $label_author.' <a href="toc.php?categ=&author='.rawurlencode((string)$author).'">'.cms_t($author).'</a>' ;
    } 
    if ( $date != '' ) {
        $display_date   = $label_date.' '.cms_t($date);
    } 
    if ($categ != '')  {
        $tmp   = $tmp.$display_categ;
    } 
    if ($author != '') {
        if ($categ != '')  {
            $tmp   = $tmp.'&nbsp; &nbsp; &bull; &nbsp; &nbsp;'.$display_author;
        }
        else {
          $tmp   = $tmp.$display_author;
        }
    } 
    if ($date != '') {
        if ($tmp != '') {
            $tmp   = $tmp.'&nbsp; &nbsp; &bull; &nbsp; &nbsp;'.$display_date;                  
        } 
        else {
            $tmp   = $tmp.$display_date;                  
        } 
    } 
    if ($tmp != '' ) {
        echo '<small>'.$tmp.'</small><br>'   ;
    }

    // Main text 
    $output = $row[5];

    // Rev. 4.0.0: pages written with the editor (admin): ready-made HTML,
    // recognised by the <!--html--> marker at the start of the txt field.
    // No t_conversion, no Markdown.
    if (str_starts_with(ltrim($output), '<!--html-->')) {
        $output = '<div class="ed-page">'.$output.'</div>';
    }
    else {

        // Layout conversion for thirds, halves, etc
        // item's "translation" (see DB) refers on classes in CSS 
        // style.css must be adapted accordingly for correct use!!
        $stmt = $dbh->prepare( "SELECT t_conversion.item, t_conversion.translation FROM t_conversion" ) ;
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            $item =   $row[0];
            $conv =   $row[1];
            $output = str_replace($item , $conv, $output);
        }

        // $output = (new GithubFlavoredMarkdownConverter())->convert($output)->getContent();
        // v. 3.0
        $output = $converter->convert($output);
    }

}          

// Previous page / next page management Rev. 1.2 (2024-09-17)
$previous_id       = 0  ;
$previous_title    = '' ;
$previous_pagenum  = 0  ;
$next_id           = 0  ;
$next_title        = '' ;
$next_pagenum      = 0  ;

if ($current_pagenum == '') {
    $current_pagenum = 0;
}
if ($categ != '') {
    if ($current_pagenum != 0) {

        $stmt = $dbh->prepare( "SELECT t_pages.id, t_pages.title, t_pages.categ_pagenum 
                                FROM   t_pages   WHERE t_pages.categ = (:categ)
                                AND    t_pages.pending <> 1  
                                AND    t_pages.categ_pagenum < (:current_pagenum)
                                ORDER BY categ_pagenum  DESC LIMIT 1" ) ;
        $stmt->bindParam(':categ', $categ);
        $stmt->bindParam(':current_pagenum', $current_pagenum);
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            $previous_id      = $row[0];
            $previous_title   = $row[1];
            $previous_pagenum = $row[2];
        }

        $stmt = $dbh->prepare( "SELECT t_pages.id, t_pages.title, t_pages.categ_pagenum
                                FROM   t_pages   WHERE t_pages.categ = (:categ)
                                AND    t_pages.pending <> 1  
                                AND    t_pages.categ_pagenum > (:current_pagenum)
                                ORDER BY categ_pagenum  ASC LIMIT 1" ) ;
        $stmt->bindParam(':categ', $categ);
        $stmt->bindParam(':current_pagenum', $current_pagenum);
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row)  {
            $next_id      = $row[0];
            $next_title   = $row[1];
            $next_pagenum = $row[2];
        }
        if ( $previous_pagenum == '') {  
            $previous_pagenum = 0;
        }
        if ( $next_pagenum == '') {  
            $next_pagenum = 0;
        }
        if ( $previous_pagenum == 0) {  
            $previous_id = 0;
        }
        if ( $next_pagenum == 0) {  
            $next_id = 0;
        }
        if ( $previous_id != 0) {                  
            echo '<a href="page.php?id='.$previous_id.'"><small>'.$label_previous.'</a>'.$label_colon.cms_t($previous_title) ;
            echo '</small><br>';
        }
        if ( $next_id != 0) {                  
            echo '<a href="page.php?id='.$next_id.'"><small>'.$label_next.'</a>'.$label_colon.cms_t($next_title) ;
            echo '</small>';
        }
    }      
} 

if ( $tmp != '' or $previous_id != 0 or $next_id != 0) { 
    echo '<p></p>';
} 
////////////////
echo $output ;
////////////////
if ( $previous_id != 0 or $next_id != 0) {                  
    echo '<p><br></p>';
    echo '&nbsp;&uarr; &nbsp;<a href="#top-of-page"><small>'.$label_top_of_page.'</a></small><br>' ;
}
if ( $previous_id != 0)  {                  
    echo '&larr; <a href="page.php?id='.$previous_id.'"><small>'.$label_previous.'</a>'.$label_colon.cms_t($previous_title).'</small>' ;
    echo '<br>';
}
if ( $next_id != 0)  {                  
    echo '&rarr; <a href="page.php?id='.$next_id.'"><small>'.$label_next.'</a>'.$label_colon.cms_t($next_title).'</small>' ;
}

// Load Footer (bottom of page)
require dirname(__DIR__) . '/src/footer.php';
