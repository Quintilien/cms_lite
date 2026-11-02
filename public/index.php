<?php

// Rev. 3.1 (2025-08-18) 

require dirname(__DIR__) . '/src/config.php';


$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters WHERE t_parameters.ref = 'HOMEPAGE' " ) ;
$stmt->execute() ;
$row  = $stmt->fetch();
$row  = $row[0] ;

echo '<html>';
echo '<meta http-equiv="refresh" content="0; url='.$row.'"> ' ;  

echo '<head>';
echo '<title>'.$web_title.'</title>';
echo '</head>';

echo '<link rel="stylesheet" type="text/css" href="css/site.css"> ';
echo '<body> ';
echo '</body>';
echo '</html>';

?>
