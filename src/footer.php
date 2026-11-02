<?php

// Rev. 3.1 (2025-08-18) 

///////////////////////////////
//      Footer               //
///////////////////////////////

$stmt = $dbh->prepare( "SELECT t_parameters.descr FROM t_parameters 
                      WHERE t_parameters.ref = 'FOOTER' " ) ;
$stmt->execute() ;
foreach ( $stmt->fetchAll() AS $row)  {
    echo $row[0];
}

echo '</body>';
echo '</html>';
