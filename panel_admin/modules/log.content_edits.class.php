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

class contentLog{

    function __construct(){
        
        // Show form
        $type = 'Event Char Hijack';
        if( isset( $_GET['type'] ) ){
            switch( $_GET['type'] ){
                case "hijack": $type = 'Event Char Hijack'; break;
                case "mass": $type = 'Mass Edit'; break;
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

        // Load table parser
        $min =  tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `content_edits` WHERE `title` = '" . $type . "' ORDER BY `time` DESC LIMIT " . $min . ",10");

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
            true, // Send directly to contentLoad   
            true,
            array(
                array( "name" => "Event Char Hijack", "href" => "?id=".$_GET['id']."&act=logs&type=hijack"  ),
                array( "name" => "Mass Changes", "href" => "?id=".$_GET['id']."&act=logs&type=mass"  ),
                array( "name" => "Event AI", "href" => "?id=".$_GET['id']."&act=logs&type=ai"  ),
                array( "name" => "Admin AI", "href" => "?id=".$_GET['id']."&act=logs&type=aai" ),
                array( "name" => "Item Change", "href" => "?id=".$_GET['id']."&act=logs&type=item" ),
                array( "name" => "Bloodlines", "href" => "?id=".$_GET['id']."&act=logs&type=blood" ),
                array( "name" => "Jutsus", "href" => "?id=".$_GET['id']."&act=logs&type=jutsu" ),
                array( "name" => "Crimes", "href" => "?id=".$_GET['id']."&act=logs&type=crime" ),
                array( "name" => "Missions", "href" => "?id=".$_GET['id']."&act=logs&type=mission" ),
                array( "name" => "Village Funds", "href" => "?id=".$_GET['id']."&act=logs&type=village" )
            )
        ); 
    }
}

new contentLog();