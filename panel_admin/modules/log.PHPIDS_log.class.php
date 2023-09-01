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

class news{
	function __construct(){
		$this->main_screen();
	}
	                         
	function main_screen(){               
        
        // Show form
        $min =  tableParser::get_page_min();
        $log = $GLOBALS['database']->fetch_data("SELECT * FROM `IDS_log` ORDER BY `id` ASC LIMIT ".$min.",10");        
        tableParser::show_list(
            'log',
            'Latest Injection Attempts Registered by PHP IDS', 
            $log,
            array(
                'id' => "ID", 
                'created' => "Created",
                'page' => "Page",
                'name' => "Input Field",
                'value' => "Value",
                'ip' => "IP 1",
                'ip2' => "IP 2"
            ),
            false,
            true,
            true,
            false
        );
	}
	
}

new news();