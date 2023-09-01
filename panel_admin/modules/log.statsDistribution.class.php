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

class distributionModule {

    function __construct() {
        try {
            $this->main_screen();
        } catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Stat Distribution Log', 'id='.$_GET['id']);
        }
        
    }

    // Show the average stats for each level in the system
    function main_screen() {
        
        // Get all the levels
        $levels = $GLOBALS['database']->fetch_data("SELECT * FROM `levels`");
        
        // Query to select relevant columns
        $statsOfInterst = array( "nin_off", "tai_off", "gen_off", "weap_off", "nin_def", "tai_def", "gen_def", "weap_def", "strength","intelligence","willpower","speed" );
        $showColumns = array("level_id" => "Level ID","rank" => "Rank");
        $selectQuery = "";
        foreach( $statsOfInterst as $stat ){
            $selectQuery .= ", AVG(`".$stat."`) as `".$stat."`";
            $showColumns[ $stat ] = $stat;
        }
        
        // Create array to show        
        $users = $GLOBALS['database']->fetch_data('
        SELECT `level_id`, `rank` '.$selectQuery.'
        FROM `users`, `users_statistics`
        WHERE users.id = users_statistics.uid AND user_rank IN ("Member","Paid") GROUP BY (`level_id`)');
        
        
        tableParser::show_list(
                'ryo', 
                'Stat Distribution Distribution', $users, 
                $showColumns, 
                false, true, // Send directly to contentLoad
                false, false
        );
    }

}

new distributionModule();