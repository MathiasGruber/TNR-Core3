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

class ninjaFarmerTracker{
	
        // Constructor. Let admin search device ID
	function __construct(){            
            if(!isset($_POST['Submit'])){
                $this->main_screen();
            }
            else{
                $this->execute_search();
            }		
	}
	
            
        // Show user prompt
	function main_screen(){                       
            $GLOBALS['page']->UserInput(
                "Search the device ID of the device you want to investigate", // Information
                "Search device ID", // Title
                array(
                    array("infoText"=>"Device ID","inputFieldName"=>"deviceID", "type" => "input", "inputFieldValue" => "")
                ), // input fields
                array("href" => "?id=" . $_GET['id'] , "submitFieldName" => "Submit","submitFieldText" => "Search"), // Submit button
                false // Return link name
            );
	}
        
        // Show search results
        private function execute_search(){
            
            // Get all entries from this device
            if( isset($_POST['deviceID']) ){
                
                $min =  tableParser::get_page_min();
                $ryo = $GLOBALS['database']->fetch_data("
                    SELECT `log_minigame_points`.*, `users`.`username`
                    FROM `log_minigame_points`
                    LEFT JOIN `users` ON (`users`.`id` = `log_minigame_points`.`uid`)
                    WHERE `deviceID` = '".$_POST['deviceID']."'
                    ORDER BY `time` DESC 
                    LIMIT " . $min . ",10");
                tableParser::show_list(
                    'ninjaFarmerEntries',
                    'Ninja Farmer Transactions', 
                    $ryo,
                    array(
                        'username' => "Username", 
                        'deviceID' => "Device ID",
                        'points' => "Farmer Points",
                        'time' => "Time"
                    ), 
                    false,
                    true, // Send directly to contentLoad
                    true, // Yes newer/older links
                    false, // No top options links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    "All entries attached to device ".$_POST['deviceID'].", after 28th of July, 2015."
                ); 
                
            }
            else{
                throw new Exception("No device ID specified");
            }
           
            
            
        }

}

new ninjaFarmerTracker();