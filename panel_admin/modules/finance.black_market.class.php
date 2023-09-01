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

class admin_blackMarket {

    function __construct() {
        
        // Show form
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `market_purchases`");
        tableParser::show_list(
                'log', 'Black Market Purchases', $edits, array(
            'market_id' => "Item ID",
            'name' => "Item Name",
            'purchases' => "Purchases"),
            false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false // No top search field
        );
    }
}

new admin_blackMarket();