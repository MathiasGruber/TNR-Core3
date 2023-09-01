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

function populateTable( $getType , $id , $async = true )
{    
    // Show form
    $type = 'Event Char Hijack';
    if( isset( $getType ) ){
        switch( $getType ){
            case "hijack": $type = 'Event Char Hijack'; break;
            case "ai": $type = 'Event AI'; break;
            case "aai": $type = 'AI Change'; break;
            case "item": $type = 'Item Change'; break;
            case "blood": $type = 'Bloodline Change'; break;
            case "jutsu": $type = 'Jutsu Change'; break;
            case "crime": $type = 'Crime Change'; break;
            case "mission": $type = 'Mission Change'; break;
            case "village": $type = 'Village Change'; break;
        }
    } 
            
    // If async call, get neccesary files
    if( $async == true ){
        include('../../global_libs/Smarty-3.1.29/Smarty.class.php');
        $GLOBALS['template'] = new Smarty;
        include('../../global_libs/General/tableparser.inc.php');  
        include('../../global_libs/Site/database.class.php');
        $GLOBALS['database'] = new database;             
    }   
    
    // Load table parser
    $min =  tableParser::get_page_min();
    $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `content_edits` WHERE `title` = '" . $type . "' ORDER BY `time` DESC LIMIT " . $min . ",10");
    
    // Fix links in smarty
    if( !isset($_GET['id'])) {$_GET['id'] = $id;}
    if( !isset($_GET['type'])) {$_GET['type'] = $getType;}
    
    // Run the show_list function            
    tableParser::show_list(
        'log',
        $type, 
        $edits,
        array(
            'time' => "Time", 
            'aid' => "User",
            'changes' => "Information",
        ), 
        false ,
        false, // Send directly to contentLoad   
        true,
        array(
            array( "name" => "Event Char Hijack", "href" => "?id=".$id."&amp;act=logs&type=hijack"  , "onclick" => "xajax_populateTable('hijack',".$id.");" ),
            array( "name" => "Event AI", "href" => "?id=".$id."&amp;act=logs&amp;type=ai" , "onclick" => "xajax_populateTable('ai',".$id.");" ),
            array( "name" => "Admin AI", "href" => "?id=".$id."&amp;act=logs&amp;type=aai" , "onclick" => "xajax_populateTable('aai',".$id.");"),
            array( "name" => "Item Change", "href" => "?id=".$id."&amp;act=logs&amp;type=item" , "onclick" => "xajax_populateTable('item',".$id.");"),
            array( "name" => "Bloodlines", "href" => "?id=".$id."&amp;act=logs&amp;type=blood" , "onclick" => "xajax_populateTable('blood',".$id.");"),
            array( "name" => "Jutsus", "href" => "?id=".$id."&amp;act=logs&amp;type=jutsu" , "onclick" => "xajax_populateTable('jutsu',".$id.");"),
            array( "name" => "Crimes", "href" => "?id=".$id."&amp;act=logs&amp;type=crime" , "onclick" => "xajax_populateTable('crime',".$id.");"),
            array( "name" => "Missions", "href" => "?id=".$id."&amp;act=logs&amp;type=mission" , "onclick" => "xajax_populateTable('mission',".$id.");"),
            array( "name" => "Village Funds", "href" => "?id=".$id."&amp;act=logs&amp;type=village" , "onclick" => "xajax_populateTable('village',".$id.");")
        )
    ); 
    $GLOBALS['template']->assign('subSelect', "log"); 
                                                            
    // In case of xajax call, send proper response      
    if( $async == true ){             
        $GLOBALS['template']->assign('contentLoad', 'templates/dbparser/showTable.tpl');
        $response = new xajaxResponse();        
        $response->assign('contentTable', 'innerHTML', $GLOBALS['template']->fetch("../../files/general_includes/contentInclude.tpl"));
        return $response;
    }   
}

// Load Xajax
if( isset($_POST['xjxfun']) ){
    include '../../global_libs/xajax/xajax_core/xajax.inc.php';
    $xajax = new xajax();
    $xajax->register(XAJAX_FUNCTION, 'populateTable');             
    $xajax->processRequest();  
}

?>
