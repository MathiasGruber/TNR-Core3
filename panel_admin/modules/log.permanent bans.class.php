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

class permanentBanned{
	
	function __construct(){
		$this->main_screen();
	}
		
	function main_screen(){
            
        // Show form
        $edits = $GLOBALS['database']->fetch_data("
            SELECT `username`,`rank`,`village` 
            FROM `users`,`users_statistics` 
            WHERE 
                `users`.`id` = `users_statistics`.`uid` AND 
                `users`.`perm_ban` = CONVERT( _utf8 '1' USING latin1 ) 
            ORDER BY `username` ASC");        
        tableParser::show_list(
            'permbanned',
            'Permanently Banned Users', 
            $edits,
            array(
                'username' => "Name", 
                'rank' => "Rank",
                'village' => "Village"
            ),
            false,
            true,
            false,
            false
        );
	}

}

new permanentBanned();