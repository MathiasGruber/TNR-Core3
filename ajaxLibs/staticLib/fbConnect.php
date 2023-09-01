<?php 
 /* ============== LICENSE INFO START ==============
  * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
  * 
  * This file is part of the project www.TheNinja-RPG.com.
  * Dissemination of this information or reproduction of this material
  * is strictly forbidden unless prior written permission is obtained
  * from Studie-Tech ApS.
  * ============== LICENSE INFO END ============== */ 
?>
<?php

if( isset($_POST['username']) && isset($_POST['data']) ){
    
    // Change directory
    chdir( "../../" );
    
    // Get IDS library
    require('./global_libs/IDS/Init.php');
    set_include_path(get_include_path() . PATH_SEPARATOR . './global_libs/');

    // Initiate IDS
    $request = array(
        'REQUEST' => $_REQUEST,
        'GET' => $_GET,
        'POST' => $_POST,
        'COOKIE' => $_COOKIE
    );
    $init = IDS_Init::init('./global_libs/IDS/Config/Config.ini.php');
    $ids = new IDS_Monitor($request, $init);

    // Run IDS
    $result = $ids->run();
    $impact = $result->getImpact();

    // Check impact
    if ( $impact <= 5) {
        echo insertFriendInvites( $_POST['username'] , $_POST['data'] );
    }
    else{
        echo"Suspecious data sent to server. Your information has been logged and admins notified. Security threat: ".$impact;
        echo"Info on suspecious data: ".$result;
    }
}

// Insert Friend Requests into database
function insertFriendInvites($username,$dataArray){
    
    // Create a response
    $response = "Start -";
    
    // Include Database
    require('./global_libs/Site/database.class.php');
    $GLOBALS['database'] = new database;
    
    if( isset($GLOBALS['database']) ){
        
        // DB was ok
        $response .= " DB OK -";
        
        foreach($dataArray as $fbID){
            $response .= " Attempt adding ".$fbID." -";
            $request = $GLOBALS['database']->fetch_data("SELECT * FROM `fbRequests` WHERE `fbID` LIKE '".$fbID."' LIMIT 1");                       
            if($request == "0 rows"){
                $response .= " Check clear, inserting -";
                $GLOBALS['database']->execute_query("INSERT INTO `fbRequests` ( `username` , `fbID` ) 
                                                     VALUES ( '".$username."','".$fbID."') ");
            }
        }
    }
    else{
         $response .= " No DB -";
    }
    $response .= " End";
    return $response;
}