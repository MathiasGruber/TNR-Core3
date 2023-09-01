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
        if(!isset($_GET['act']) || $_GET['act'] == "edits"){
            $this->main_screen();
        }
        elseif($_GET['act'] == 'ips'){
            $this->adminIps();
        }  
	}
	
	private function main_screen(){
        
        
        // Show form
        $min =  tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` ORDER BY `time` DESC LIMIT ".$min.",10");        
        tableParser::show_list(
            'log',
            'Latest Admin Edits', 
            $edits,
            array(
                'aid' => "Admin Name", 
                'time' => "Time",
                'IP' => "IP Used",
                'changes' => "Changes"
            ),
            false,
            true,
            true,
            array(
                array( "name" => "Admin Edits", "href" => "?id=".$_GET["id"]."&act=edits" ),
                array( "name" => "Admin IPs", "href" => "?id=".$_GET["id"]."&act=ips" )                
            )
        );     
	}
    
    private function adminIps()
    {
        
        // Show form
        $min =  tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `adminIpLog` ORDER BY `time` DESC LIMIT ".$min.",10");
        tableParser::show_list(
            'log',
            'Latest Admin IPs', 
            $edits,
            array(
                'admin' => "Admin Name", 
                'ip' => "Using IP",
                'time' => "Time"
            ),
            false,
            true,
            true,
            array(
                array( "name" => "Admin Edits", "href" => "?id=".$_GET["id"]."&act=edits" ),
                array( "name" => "Admin IPs", "href" => "?id=".$_GET["id"]."&act=ips" )                
            )
        );
        
        
    }

}

new admin_item();