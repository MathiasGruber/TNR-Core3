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

class admin_item {

    function __construct() {
        if (!isset($_GET['act']) || $_GET['act'] == "edits") {
            $this->main_screen();
        } 
    }

    private function main_screen() {


        // Show form
        $num = tableParser::set_items_showed(100);
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("
             SELECT * 
             FROM `admin_edits` 
             WHERE `changes` LIKE '%\"rep_now\"%'
             ORDER BY `time` DESC");
        tableParser::show_list(
                'log', 'Latest Admin Rep Transfers', $edits, array(
            'aid' => "Admin Name",
            'uid' => "User ID",
            'time' => "Time",
            'IP' => "IP Used",
            'changes' => "Changes"
                ), false, true, false, false
        );
    }

}

new admin_item();