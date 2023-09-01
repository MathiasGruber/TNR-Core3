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

require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');

class backup {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_screen();   
        } 
        elseif ($_GET['act'] == 'dobackup') {
            $this->takeBackup();
        }   
        elseif ($_GET['act'] == 'browse') {
            $this->browse();
        } 
        elseif ($_GET['act'] == 'look') {            
            if (!isset($_POST['Submit'])) {
                $this->look();
            } else {
                $this->insertUser();
            }
        }
        elseif ($_GET['act'] == 'search') {
            if(!isset($_POST['Submit'])){
                $this->search_form();
            }
            else{
                $this->execute_search();
            }
        }              
    }

    private function main_screen() {        
        $menu = array(
            array( "name" => "Take Backup", "href" => "?id=".$_GET['id']."&act=dobackup"),
            array( "name" => "Show Backup List", "href" => "?id=".$_GET['id']."&act=browse"),
            array( "name" => "Search Backup List", "href" => "?id=".$_GET['id']."&act=search")            
        );
        $GLOBALS['template']->assign('subHeader', 'Purge Options');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', 1);
        $GLOBALS['template']->assign('subTitle', 'This system can be used to backup users of TNR (this can take a bit of time), and to re-instate backed up users.');
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');        
    }
    
    private function backup_tables($tables = '*',$uid)
    {          
        $return = "";
        $returnArray = array();
        foreach($tables as $table)
        {
            $result = $GLOBALS['database']->fetch_data("SELECT * FROM `".$table[1]."` WHERE `".$table[0]."` = '".$uid."'");
            if( $result !== "0 rows" )
            {
                foreach( $result as $row ){
                    $num_fields = count($row);
                    $return= 'INSERT INTO `'.$table[1].'` ('; 
                    $j = 0;
                    foreach( $row as $key => $value ){
                        if( isset($key) ){ $return.= "`".$key."`" ; } else { $return.= '""';  }
                        if ($j < ($num_fields-1)) { $return.= ', '; }   
                        $j++;
                    }
                    $return.= ') VALUES(';
                    $j = 0; 
                    foreach( $row as $key => $value ){
                        if( isset($value) ){ $return.= "'".$value."'" ; } else { $return.= '""';  }
                        if ($j < ($num_fields-1)) { $return.= ', '; }   
                        $j++;
                    }
                    $return.= ");";
                    $returnArray[] = $return; 
                }
            }
        }
        return $returnArray;                 
    }

    
    private function takeBackup()
    {
        // The basic query 
        $basicQuery = "users 
                LEFT JOIN `users_timer` ON `users_timer`.`userid` = `users`.`id`
                LEFT JOIN `users_statistics` ON `users_statistics`.`uid` = `users`.`id`
                LEFT JOIN `backups` ON `backups`.`identifier` = `users`.`id`
               WHERE 
                `users_statistics`.`rep_ever` > 20 AND
                (  `users_timer`.`last_login` > `backups`.`time` OR `backups`.`time` is null ) ";

        // Confirmation & Execution
        if (!isset($_POST['Submit'])) {
            $accounts = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `total` FROM ".$basicQuery);
            $GLOBALS['page']->Confirm("There are ".$accounts[0]["total"]." users in the system that match the backup criteria; 
            more than 20 reputation points ever, and no backup which is taken less than 7 days from the users last active login. 
            Run the backup system?", 'Backup System', 'Backup Now!'); 
        } else {
            
            // Get all the accounts
            $accounts = $GLOBALS['database']->fetch_data("SELECT `id`,`username`,`rank`,`mail` FROM ".$basicQuery);
            if( $accounts != "0 rows" ){
                
                // Go through all the accounts
                foreach( $accounts as $account ){
                    
                    // backup the db OR just a table 
                    $selector = array(
                        array('id','users') ,
                        array('uid','users_jutsu') ,
                        array('uid','users_loyalty') ,
                        array('uid','users_preferences') ,
                        array('uid','users_statistics'),
                        array('uid','users_inventory') ,
                        array('userid','users_occupations') ,
                        array('userid','users_missions') ,
                        array('userID','bingo_book') ,
                        array('userid','users_timer') 
                    );
                    $updateQueries = $this->backup_tables($selector, $account["id"] );
                    $niceElements = array("name" => $account["username"], "rank" => $account["rank"], "mail" => $account["mail"]);
                                  
                    // Delete previous
                    $GLOBALS['database']->execute_query("DELETE FROM `backups` WHERE `identifier` = '".$account["id"]."' LIMIT 1 ");                                  
                                      
                    // Insert new                                                                                                              
                    $GLOBALS['database']->execute_query("INSERT INTO `backups` (`type` ,`time` ,`identifier` ,`sqlBackup`, `elements`)VALUES 
                    ('user', UNIX_TIMESTAMP(), '" . $account["id"] . "', '".addslashes(serialize($updateQueries))."', '".serialize($niceElements)."' );");
                    
                    
                }
                
                // Message
                $GLOBALS['page']->Message( "Backup done!." , 'Backup System', 'id='.$_GET['id']); 
                
                // Notes
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', 'MULTIPLE', 'Backed Up Users', '" . $GLOBALS['user']->real_ip_address() . "');");
            }
            else{
                $GLOBALS['page']->Message( "No accounts left to backup." , 'Backup System', 'id='.$_GET['id']); 
            }                                                         
        }
    }
       
    private function showLog( $query ) 
    {
        $backups = $GLOBALS['database']->fetch_data($query);
        
        if( $backups != "0 rows" ){
            $i = 0;
            while( $i < count( $backups ) ){
                $unserialized = unserialize( $backups[$i]["elements"] );
                $backups[$i]["name"] = $unserialized["name"];
                $backups[$i]["rank"] = $unserialized["rank"];
                $backups[$i]["mail"] = $unserialized["mail"];
                $i++;
            }
        }
        
        tableParser::show_list(
            'backup',
            'Backup System', 
            $backups,
            array(
                'name' => "Name", 
                'rank' => "Rank",
                'mail' => "Mail",
                'time' => "Time of Backup"
            ), 
            array( 
                array("name" => "Review & Recover", "act" => "look", "iid" => "table.identifier")
            ) ,
            true, // Send directly to contentLoad
            true,
            false
        );
        
        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }
        
    private function browse()
    {   
        $min =  tableParser::get_page_min();
        $this->showLog("SELECT * FROM `backups` ORDER BY `time` DESC LIMIT ".$min.",10");
    }
    
    private function look()
    {
        if( isset( $_GET["iid"] ) && is_numeric( $_GET["iid"] ) )
        {
            $backup = $GLOBALS['database']->fetch_data("SELECT * FROM `backups` WHERE `identifier` = '".$_GET['iid']."'");
            if( $backup !== "0 rows" ){
                // Get the query for the user table
                $queries = unserialize(stripslashes($backup[0]["sqlBackup"]));
                $userTableQuery = str_replace("users","temp_users",$queries[0]);
                
                // Temporary user table
                $tempTable = "CREATE TEMPORARY TABLE IF NOT EXISTS `temp_users` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `username` varchar(50) NOT NULL,
                    `password` varchar(255) NOT NULL DEFAULT '',
                    `salted_password` varchar(255) NOT NULL DEFAULT '',
                    `mail` varchar(150) NOT NULL DEFAULT '',
                    `past_IPs` varchar(150) NOT NULL DEFAULT '',
                    `region` varchar(150) NOT NULL DEFAULT '',
                    `item_regen_boost` varchar(150) NOT NULL DEFAULT '',
                    `item_regen_endtime` varchar(150) NOT NULL DEFAULT '',
                    `repel_chance` varchar(150) NOT NULL DEFAULT '',
                    `repel_endtime` varchar(150) NOT NULL DEFAULT '',
                    `join_date` int(11) unsigned NOT NULL,
                    `join_ip` varchar(25) NOT NULL,
                    `last_ip` varchar(25) NOT NULL,
                    `last_UA` varchar(255) NOT NULL,
                    `gender` varchar(20) NOT NULL DEFAULT '',
                    `post_ban` enum('1','0') NOT NULL DEFAULT '0',
                    `bloodline` varchar(50) NOT NULL,
                    `village` varchar(50) NOT NULL,
                    `apartment` int(11) unsigned DEFAULT NULL,
                    `status` varchar(50) NOT NULL DEFAULT 'awake',
                    `student_1` varchar(50) NOT NULL,
                    `student_2` varchar(50) NOT NULL,
                    `student_3` varchar(50) NOT NULL,
                    `latitude` int(11) NOT NULL DEFAULT '0',
                    `longitude` int(11) NOT NULL DEFAULT '0',
                    `location` varchar(255) NOT NULL,
                    `notifications` text NOT NULL,
                    `notifications` varchar(255) NOT NULL,
                    `activation` enum('0','1') NOT NULL DEFAULT '0',
                    `login_id` varchar(255) NOT NULL,
                    `ryoCheckLimit` int(11) NOT NULL DEFAULT '0',
                    `new_pm` tinyint(3) unsigned NOT NULL DEFAULT '0',
                    `fbID` bigint(20) unsigned NOT NULL,
                    `nindo` longtext NOT NULL,
                    `federal_timer` int(11) unsigned NOT NULL,
                    `subscr_id` int(11) unsigned NOT NULL,
                    `logout_timer` int(11) unsigned NOT NULL,
                    `perm_ban` enum('0','1') NOT NULL DEFAULT '0',
                    `ban_time` int(11) unsigned NOT NULL,
                    `tban_time` int(11) unsigned NOT NULL,
                    `deletion_timer` int(11) unsigned NOT NULL,
                    `reset_timer` int(11) unsigned NOT NULL,
                    `immunity` int(11) unsigned NOT NULL,
                    `regen_boost` int(11) unsigned NOT NULL,
                    `regen_endtime` int(11) unsigned NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `username` (`username`),
                    KEY `password` (`password`),
                    KEY `village` (`village`),
                    KEY `fightShowPeople` (`id`,`longitude`,`latitude`,`status`),
                    KEY `activation` (`activation`),
                    KEY `join_ip` (`join_ip`)
                )";
                
                // Insert data
                $GLOBALS['database']->execute_query($tempTable);
                $GLOBALS['database']->execute_query($userTableQuery);
                
                // Show the table
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `temp_users` LIMIT 1");
                tableParser::parse_form('temp_users', 'Re-insert User into Database', array('id'), $data);                
                
            }
            else{
                $GLOBALS['page']->Message( "A backup could not be found for this user id." , 'Backup System', 'id='.$_GET['id']); 
            }
        }
        else{
            $GLOBALS['page']->Message( "This is not a valid user ID." , 'Backup System', 'id='.$_GET['id']); 
        }
    }
    
    private function insertUser()
    {
        if( isset( $_GET["iid"] ) && is_numeric( $_GET["iid"] ) )
        {
            $backup = $GLOBALS['database']->fetch_data("SELECT * FROM `backups` WHERE `identifier` = '".$_GET['iid']."'");
            if( $backup !== "0 rows" ){
                $check = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `username` = '".$_POST['username']."'");
                if( $check == "0 rows" ){
                    if ( tableParser::insert_data('users' , $_POST ) ) {
                        // Get the new ID of this user
                        $newID = $GLOBALS['database']->get_inserted_id();
                        
                        // Run the rest of the queries
                        $queries = unserialize(stripslashes($backup[0]["sqlBackup"]));
                        $i = 1;
                        while( $i < count($queries) ){
                            $updateQuery = str_replace($_GET["iid"],$newID,$queries[$i]);
                            $GLOBALS['database']->execute_query($updateQuery);
                            $i++;
                        }
                        $GLOBALS['page']->Message( "User has been restored with the userID: ".$newID , 'Backup System', 'id='.$_GET['id']); 
                    }
                    else{
                        $GLOBALS['page']->Message( "There was an error inserting the user into the database" , 'Backup System', 'id='.$_GET['id']); 
                    }
                }
                else{
                    $GLOBALS['page']->Message( "A user with this username is already in the system. Please change the username." , 'Backup System', 'id='.$_GET['id']); 
                }                               
            }
            else{
                $GLOBALS['page']->Message( "A backup could not be found for this user id." , 'Backup System', 'id='.$_GET['id']); 
            }
        }
        else{
            $GLOBALS['page']->Message( "This is not a valid user ID." , 'Backup System', 'id='.$_GET['id']); 
        }
    }
    
    private function search_form()
    {
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/backups/search.tpl');        
    }
    
    private function execute_search()
    {   
        if( isset($_POST["search"]) && $_POST["search"] !== ""){
            
            $min =  tableParser::get_page_min();
            $this->showLog("SELECT * FROM `backups` WHERE `sqlBackup` LIKE '%".$_POST["search"]."%' ORDER BY `time` DESC LIMIT ".$min.",10");
        }
        else{
            $GLOBALS['page']->Message( "You did not serch anything." , 'Backup System', 'id='.$_GET['id']); 
        }
    }
}

new backup();