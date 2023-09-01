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

class admin_item{
	
	function __construct(){
	    $this->main_screen();		
	}
	
	function main_screen(){
        $min =  tableParser::get_page_min();
        $ryo = $GLOBALS['database']->fetch_data("
            SELECT `username`, `bank`, `money`, (`money` + `bank`) AS `geld` 
            FROM `users`,`users_statistics` 
            WHERE 
                `users`.`id` = `users_statistics`.`uid` AND
                (`money` + `bank`) > 50000000 
            ORDER BY `geld` DESC 
            LIMIT " . $min . ",10");
        tableParser::show_list(
            'ryo',
            'Ryo Distribution', 
            $ryo,
            array(
                'username' => "Name", 
                'bank' => "Bank",
                'money' => "Money",
                'geld' => "Total"
            ), 
            false ,
            true, // Send directly to contentLoad
            true,
            false
        );            
	}

}

new admin_item();