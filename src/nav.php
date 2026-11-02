<?php

// Rev. 4.0.0 (2026-09-25): default style sheet css/site.css
// Rev. 3.1 (2025-08-18) 

/*
(html : pure html)
All DIV classes are stored in DB HEADER, BODY and FOOTER
(header : pure html)
        DIV CONTAINER
          NAV
            UL
              (php) ->> <li>menus items</li>
(body : pure html)
            /UL
          /NAV
          CONTAINER-960
            (php> ->> content (txt from DB converted first by markdown then in html)
(footer : pure html)
          /DIV (END CONTAINER-960)
        /DIV (END CONTAINER)
*/


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



/////////////////////
//  Page's title   //
/////////////////////

// $web_title is initialized in 'config.php' and displayed by 'nav.php'
// In page.php, before calling 'nav.php', '$web_title' is completed by '$web_title = $web_title.' - '.trim($row[0])'
// In displaymd.php, before calling 'nav.php', '$web_title' is completed with the markdown filename
// Idem in displayfile.php and displayhtml.php

echo '<html>
      <head>';
echo '<title>'.cms_t($web_title).'</title>';   // Rev. 4.5.0 : texte brut



/////////////////////////////
//      HTML & C.S.S.      //
//         Rev. 3.0        // 
/////////////////////////////

$html = array();

$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'HTML_META_CSS' " ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
  array_push($html , $row[0]); 
}

if ( empty($html) ) {
  echo '<meta charset="UTF-8">' ; 
  echo '<meta name="viewport" content="width=device-width, initial-scale=1">'; 
  echo '<link rel="stylesheet" type="text/css" href="css/site.css">'; 
}
else {
  for($i = 0; $i < count($html); $i++) {
    /// echo '<h1>'.$html[$i].'</h1>';
    echo $html[$i] ;
  }
}

echo '</head>
      <body>'; 


///////////////
//  Header   //
///////////////        

$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                        WHERE t_parameters.ref = 'HEADER' " ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    echo $row[0];
}



/////////////////////////
//   Begin nav (menu)  //
/////////////////////////

$menu_id           = 0 ;
$menu_item         = '';
$menu_target       = ''; 
$sub_menu_exists   = 0 ;
$sub_menu_number   = 0 ;
$sub_menu_extend   = ' &nbsp;<big>☰</big>' ;

$stmt = $dbh->prepare( "SELECT t_menu.id, t_menu.item, t_menu.target FROM t_menu 
                        ORDER BY t_menu.display_order" ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    // Main menu
    $menu_id           = $row[0];
    $menu_item         = $row[1];
    $menu_target       = $row[2];
    $sub_menu_exists   = 0 ;
    $row2 = '';               
    $stmt = $dbh->prepare( "SELECT t_sub_menu.fk_menu_id FROM t_sub_menu
                            WHERE t_sub_menu.fk_menu_id = (:menu_id) LIMIT 1" ) ;
    $stmt->bindParam(':menu_id', $menu_id);
    $stmt->execute() ;
    foreach ( $stmt->fetchAll() AS $row2)  {
        $sub_menu_exists   = 1 ;
        $sub_menu_number++ ;
    }
    if ($sub_menu_exists == 0) {
        echo '<li><a href="'.$menu_target.'">'.$menu_item.'</a></li>' ;
    }
    else {
        echo '<li>';  
        echo '<label for="drop-'.$sub_menu_number.'" class="toggle">'.$menu_item.$sub_menu_extend.'</label>' ;
        echo '<a href="#">'.$menu_item.'</a>' ;
        echo '<input type="checkbox" id="drop-'.$sub_menu_number.'"/>' ;
        echo '<ul>';
        $stmt = $dbh->prepare( "SELECT t_sub_menu.fk_menu_id, t_sub_menu.item, t_sub_menu.target
                                FROM t_sub_menu WHERE t_sub_menu.fk_menu_id = (:menu_id)
                                ORDER BY t_sub_menu.display_order" ) ;
        $stmt->bindParam(':menu_id', $menu_id);
        $stmt->execute() ;
        foreach ( $stmt->fetchAll() AS $row2)  {
              echo '<li><a href="'.$row2[2].'">'.$row2[1].'</a></li>' ;
        }
        echo '</ul>';
        echo '</li>';  
    }
}


/////////////////////////
//    End nav (menu)   //
/////////////////////////

$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters WHERE t_parameters.ref = 'BODY'") ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    echo $row[0];
}
