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

class timedEntries {

    public function __construct() {
        
        // Get the tables
        $this->items();
        $this->jutsus();
        $this->quests();
                
        // Show the tables
        $GLOBALS['template']->assign('contentLoad', './panel_event/templates/timedEntries/main.tpl');
    }
    
    // Items
    private function items() {
        $items = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `start_date` IS NOT NULL OR `end_date` IS NOT NULL");
         tableParser::show_list(
            'items', 
             'Items with start and/or end date set', 
             $items, array(
                'id' => "ID",
                'name' => "Name",
                'required_rank' => "Rank",
                'type' => "Type",
                'in_shop' => "Shop",
                'start_date' => 'Start Time',
                'end_date' => 'End Time',
             ), false, false, false
        );
    }
    
    // Jutsus
    private function jutsus(){
         $jutsus = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `start_date` IS NOT NULL OR `end_date` IS NOT NULL");
         tableParser::show_list(
            'jutsus', 
             'Jutsus with start and/or end date set', 
             $jutsus, array(
                'id' => "ID",
                'name' => "Name",
                'required_rank' => "Rank",
                'attack_type' => "Type",
                'jutsu_type' => "Jutsu Type",
                'start_date' => 'Start Time',
                'end_date' => 'End Time',
             ), false, false, false
        );
    }
    
    // Quests
    private function quests(){
        $quests = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `start_date` IS NOT NULL OR `end_date` IS NOT NULL");
         tableParser::show_list(
            'quests', 
             'Quests/Tasks/etc with start and/or end date set', 
             $quests, array(
                'id' => "ID",
                'id' => "ID",
                'name' => "Name",
                'type' => "Type",
                'start_date' => 'Start Time',
                'end_date' => 'End Time',
             ), false, false, false
        );
    }

    

}

new timedEntries();