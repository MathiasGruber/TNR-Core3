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

class warSystem {

    // Page handler
    public function __construct() {
        
        try{
            
            // Check actions
            if (!isset($_GET['act'])) {
                $this->main_page();
            } elseif ($_GET['act'] == 'clearWars') {
                if (!isset($_POST['Submit'])) {
                    $this->clear_wars_form();
                } else {
                    $this->do_clear_wars();
                }
            } elseif ($_GET['act'] == 'clearHistory') {
                if (!isset($_POST['Submit'])) {
                    $this->clear_history_form();
                } else {
                    $this->do_clear_history();
                }
            } elseif ($_GET['act'] == 'clearLocations') {
                if (!isset($_POST['Submit'])) {
                    $this->clear_locations_form();
                } else {
                    $this->do_clear_locations();
                }
            } 
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'War System', 'id='.$_GET['id'],'Return');
        }
    }

    //	Main page
    private function main_page() {

        // Show form
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `changes` LIKE 'War System: %' ORDER BY `time` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'log', 'User admin', $edits, array(
            'aid' => "Admin Name",
            'uid' => "Admin UID",
            'time' => "Time",
            'IP' => "IP Used",
            'changes' => "Changes"
                ), false, true, true, array(
            array("name" => "Reset all Wars", "href" => "?id=" . $_GET["id"] . "&act=clearWars"),
            array("name" => "Reset War History", "href" => "?id=" . $_GET["id"] . "&act=clearHistory"),
            array("name" => "Reset Locations", "href" => "?id=" . $_GET["id"] . "&act=clearLocations")
                )
        );
    }
    
    // Add items to users
    private function clear_wars_form() {
        $GLOBALS['page']->Confirm("Clear all wars, all alliances, all vassals etc", 'War System', 'Perform Now!');
    }
    
    // Do add the items
    private function do_clear_wars() {
        
        // Reset alliances
        $GLOBALS['database']->execute_query("UPDATE `alliances` SET `Konoki` = 1,`Silence` = 0, `Samui` = 0, `Shine` = 0, `Shroud` = 0 WHERE `village` = 'Konoki';");
        $GLOBALS['database']->execute_query("UPDATE `alliances` SET `Konoki` = 0,`Silence` = 1, `Samui` = 0, `Shine` = 0, `Shroud` = 0 WHERE `village` = 'Silence';");
        $GLOBALS['database']->execute_query("UPDATE `alliances` SET `Konoki` = 0,`Silence` = 0, `Samui` = 1, `Shine` = 0, `Shroud` = 0 WHERE `village` = 'Samui';");
        $GLOBALS['database']->execute_query("UPDATE `alliances` SET `Konoki` = 0,`Silence` = 0, `Samui` = 0, `Shine` = 1, `Shroud` = 0 WHERE `village` = 'Shine';");
        $GLOBALS['database']->execute_query("UPDATE `alliances` SET `Konoki` = 0,`Silence` = 0, `Samui` = 0, `Shine` = 0, `Shroud` = 1 WHERE `village` = 'Shroud';");
        
        // Reset village structures & vassal
        $GLOBALS['database']->execute_query("
                    UPDATE `village_structures` 
                    SET `village_structures`.`cur_structurePoints` = 0, 
                        `village_structures`.`start_structurePoints` = 0,
                        `village_structures`.`counted_structurePoints` = 0,
                        `village_structures`.`vassal` = 0,
                        `village_structures`.`vassal_time` = 0");
        
        // Reset user collected structure points
        $GLOBALS['database']->execute_query("
            UPDATE `users_missions`
            SET `users_missions`.`structureDestructionPoints` = DEFAULT,
                `users_missions`.`structureGatherPoints` = DEFAULT, 
                `users_missions`.`structurePointsActivity` = DEFAULT");
        
        // Clear cache
        cachefunctions::deleteAlliances();
        
        // Log action
        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'War System: all wars cleared', '" . $GLOBALS['user']->real_ip_address() . "');");
        
        // All wars have been reset
        $GLOBALS['page']->Message( "All wars have been reset" , 'War System', 'id='.$_GET['id'],'Return');
    }  
    
    // Clear the war history
    private function clear_history_form() {
        $GLOBALS['page']->Confirm("Clearing the war history will allow villages to re-initiate wars", 'War System', 'Perform Now!');
    }
    
    private function do_clear_history() {
        
        cachefunctions::deleteAlliances();
        
        $GLOBALS['database']->execute_query("DELETE FROM `users_actionLog`");
        $GLOBALS['database']->execute_query("DELETE FROM `log_wars`");
        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'War System: war history cleared', '" . $GLOBALS['user']->real_ip_address() . "');");
        $GLOBALS['page']->Message( "All wars history has been reset" , 'War System', 'id='.$_GET['id'],'Return');
    }
    
    // Clear the locations
    private function clear_locations_form() {
        $GLOBALS['page']->Confirm("Clearing the locations will reset the ownership of all locations to their default values", 'War System', 'Perform Now!');
    }
    
    private function do_clear_locations() {
        
        // Get location library
        require('../global_libs/Site/map.inc.php');
        $mapInfo = mapfunctions::getMapInformation();

        // Go through the locations
        $locations = $GLOBALS['database']->fetch_data("SELECT * FROM `locations` WHERE `identifier` != 'village'");
        foreach( $locations as $location ){
            
            // Get the original owner
            $oriOwner = false;
            foreach( $mapInfo as $infoEntry ){
                if( $infoEntry['name'] == $location['name'] ){
                    $oriOwner = $infoEntry['startOwner'];
                }
            }
            
            // If original owner set, update it
            if( $oriOwner !== false ){
                $GLOBALS['database']->execute_query("UPDATE `locations` SET `owner` = '".$oriOwner."' WHERE `id` = '".$location['id']."';");
            }
        }
        
        // Reset the map and Delete cache
        echo 'this wont re-create the map, delete locations cache or location information';
        //mapfunctions::create_map("..");
        //cachefunctions::deleteLocations(true);
        //cachefunctions::deleteLocationInformation(true);
        
        // Log and show message
        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'War System: location owners reset', '" . $GLOBALS['user']->real_ip_address() . "');");
        $GLOBALS['page']->Message( "All locations been reset" , 'War System', 'id='.$_GET['id'],'Return');
    }
}

new warSystem();
