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

class nameChanges {

    function __construct() {
        $this->main_screen();
    }

    function main_screen() {

        // Table parser variables
        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 100 );
        
        // Show form
        $edits = $GLOBALS['database']->fetch_data("
            SELECT *
            FROM `log_namechanges` 
            ORDER BY `time` DESC
            LIMIT ".$min.",".$number);

        // Show it
        tableParser::show_list(
                'changes', 
                'Latest Namechanges', 
                $edits, array(
            'id' => "record ID",
            'uid' => "User ID",
            'oldName' => "Old Name",
            'newName' => "New Name",
            'time' => "Request Time",
            'request_ip' => "IP"
                ), 
                false, 
                true, 
                true, 
                false
        );
    }

}

new nameChanges();