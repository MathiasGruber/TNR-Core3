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
        $this->main_screen();
    }

    function main_screen() {
        $min = tableParser::get_page_min();
        $distribution = $GLOBALS['database']->fetch_data("
            SELECT 
                `layout`,
                COUNT(`uid`) as `count`
            FROM `users_preferences` 
            GROUP BY `layout`");
        tableParser::show_list(
                'ryo', 
                'Layout Distribution', $distribution, 
                array(
            'layout' => "Layout Name",
            'count' => "Users Using Layout"
                ), false, true, // Send directly to contentLoad
                false, false
        );
    }

}

new admin_item();