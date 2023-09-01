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

set_time_limit ( 2 );

    class account_purge {

        public function __construct() {
            if (!isset($_GET['act'])) {
                $this->main_screen();   
            }
            // User Purges
            elseif ($_GET['act'] == 'flagged') {
                $this->flagged_accounts();
            } 
            elseif ($_GET['act'] == 'inactive') {
                $this->inactive_accounts();            
            } 
            elseif ($_GET['act'] == 'unactivated') {
                $this->unactivated_accounts();
            } 
            elseif ($_GET['act'] == 'clans') {
                if (!isset($_POST['Submit'])) {
                    $this->clansForm();
                } else {
                    $this->do_purge_clans();
                }
            } 
            elseif ($_GET['act'] == 'challenges') {
                if (!isset($_POST['Submit'])) {
                    $this->challengeForm();
                } else {
                    $this->do_purge_challenge();
                }
            }
            elseif ($_GET['act'] == 'trade') {
                if (!isset($_POST['Submit'])) {
                    $this->tradeForm();
                } else {
                    $this->do_purge_trade();
                }
            }  
            elseif ($_GET['act'] == 'battle') {
                if (!isset($_POST['Submit'])) {
                    $this->battleForm();
                } else {
                    $this->do_purge_battle();
                }
            }              
            elseif ($_GET['act'] == 'brokenItems') {
                $this->brokenItemsForm();
            }
        }

        private function main_screen() {        
            $menu = array(
                array( "name" => "Inactive accounts", "href" => "?id=".$_GET['id']."&act=inactive"),
                array( "name" => "Flagged accounts", "href" => "?id=".$_GET['id']."&act=flagged"),
                array( "name" => "Unactivated accounts", "href" => "?id=".$_GET['id']."&act=unactivated"),
                array( "name" => "Purge Challenges", "href" => "?id=".$_GET['id']."&act=challenges"),
                array( "name" => "Purge Clans", "href" => "?id=".$_GET['id']."&act=clans"),
                array( "name" => "Purge Battles", "href" => "?id=".$_GET['id']."&act=battle"),
                array( "name" => "Purge Trades", "href" => "?id=".$_GET['id']."&act=trade"),
                array( "name" => "Purge Broken Items", "href" => "?id=".$_GET['id']."&act=brokenItems")
            );
            $GLOBALS['template']->assign('subHeader', 'Purge Options');
            $GLOBALS['template']->assign('nCols', 3);
            $GLOBALS['template']->assign('nRows', 4);
            $GLOBALS['template']->assign('subTitle', 'Using this panel various parts of TNR can be purged from data.');
            $GLOBALS['template']->assign('linkMenu', $menu);
            $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');        
        }
        
        // BROKEN / OLD ITEMS
        private function brokenItemsForm() {
            
            // Broken items retrieval
            $items = $GLOBALS['database']->fetch_data("
                    SELECT `users_inventory`.* , `items`.`name` , `items`.`type`
                    FROM `users_inventory` 
                    LEFT JOIN `items` ON (`users_inventory`.`iid` = `items`.`id`)
                    WHERE `durabilityPoints` <= 0 AND `timekey` < '".(time()-30*24*3600)."'
                    ORDER BY `timekey` DESC "
            );
            
            // Check items
            if( $items !== "0 rows" ){
                
                // Remove items
                if( isset($_GET['action']) && $_GET['action'] == "remove" ){
                    $GLOBALS['database']->execute_query("
                        DELETE
                        FROM `users_inventory` 
                        WHERE `durabilityPoints` <= 0 AND `timekey` < '".(time()-30*24*3600)."'"
                    );
                }
                
                // Show the table of changes
                tableParser::show_list(
                    'log',
                    'Broken Items', 
                    $items,
                    array(
                        'name' => "Item Name",
                        'uid' => "User ID",
                        'type' => "Type",
                        'durabilityPoints' => "Durability",
                        'timekey' => "Last Time"
                    ), 
                    false,
                    true, // Send directly to contentLoad
                    true,   // Show previous/next links                    
                    array(
                        array("name" => "Remove Broken Items", "href" =>"?id=" . $_GET['id'] . "&act=brokenItems&action=remove")
                    ),  // No links at the top to show
                    false,   // Allow sorting on columns
                    false,   // pretty-hide options
                    false, // Top stuff
                    "A list of ".count($items)." items with durabilityPoints = 0 and which were used more than 30 days ago." // Top information
                );                
            }
            else{
                $GLOBALS['page']->Message( "No broken items found" , 'Purge System', 'id='.$_GET['id']); 
            }
        }
        
        
        // CLANS
        private function avatarForm() {
            $GLOBALS['page']->Confirm("Will purge avatars/signatures for users who are no longer active in the system.", 'Purge System', 'Delete now!'); 
        }
                

        // CLANS
        private function clansForm() {
            $GLOBALS['page']->Confirm("All clans with less than 300 activity points which have been in the system for more than 1 week will be deleted.", 'Purge System', 'Delete now!'); 
        }

        private function do_purge_clans() {
            $GLOBALS['database']->execute_query("DELETE FROM `clans` WHERE `activity` < 300 AND `time` <= UNIX_TIMESTAMP() - 604800 AND `clan_type` != 'core'");
            $GLOBALS['database']->execute_query("
                      UPDATE `users`, `users_preferences`
                      SET `clan` = '_none', `notifications` = CONCAT('id:12;duration:none;text:Your clan has been deleted due to inactivity!;dismiss:yes;buttons:none;select:none;//',`notifications`)
                      WHERE 
                        `users`.`id` = `users_preferences`.`uid` AND
                        `users_preferences`.`clan` != '_none' AND 
                        `users_preferences`.`clan` != '_disabled' AND 
                        `users_preferences`.`clan` != '' AND 
                        NOT EXISTS (SELECT `id` FROM `clans` WHERE `clans`.`id` = `users_preferences`.`clan` LIMIT 1)
                      ");
            $GLOBALS['page']->Message( "Inactive Clans have been deleted" , 'Purge System', 'id='.$_GET['id']); 
        }

        // MARRIAGE AND CHALLENGES
        private function challengeForm() {
            $GLOBALS['page']->Confirm("All challenges older than 48 hours will be deleted.", 'Purge System', 'Delete now!'); 
        }

        private function do_purge_challenge() {
            $GLOBALS['database']->execute_query("DELETE FROM `spar_challenges` WHERE `time` <= UNIX_TIMESTAMP() - 172800");
            $GLOBALS['page']->Message( "Challenges have been deleted" , 'Purge System', 'id='.$_GET['id']); 
        }

        // TRADE
        private function tradeForm() {
            $GLOBALS['page']->Confirm("All trades older than 7 days will be cancelled.", 'Purge System', 'Delete now!'); 
        }

        private function do_purge_trade() {

            // Get all trades older than 7 days
            $trades = $GLOBALS['database']->fetch_data( "SELECT * FROM `trades` WHERE `time` < (UNIX_TIMESTAMP() - (7 * 24 * 3600))" );   
            if( $trades !== "0 rows" ){

                // Array with all the trade IDs to be deleted
                $tradeIDs = array();
                foreach( $trades as $trade ){
                    $tradeIDs[] = $trade['id'];
                }

                // Return money to people who made offers
                $GLOBALS['database']->execute_query("
                    UPDATE `users_statistics`,`trade_offers` 
                    SET `money` = `money` + `ryo` 
                    WHERE 
                        `trade_offers`.`tid` IN (".implode(",",$tradeIDs).") AND
                        `users_statistics`.`uid` = `trade_offers`.`uid`
                ");

                // Remove trades
                $GLOBALS['database']->execute_query("DELETE FROM `trades` WHERE `id` IN (".implode(",",$tradeIDs).") ");

                // Remove offers
                $GLOBALS['database']->execute_query("DELETE FROM `trade_offers` WHERE `tid` IN (".implode(",",$tradeIDs).") ");

                // Return items
                $GLOBALS['database']->execute_query("UPDATE `users_inventory` 
                                                 LEFT JOIN `trades` ON `users_inventory`.`trading` = `trades`.`id`
                                                 SET `users_inventory`.`trading` = NULL, 
                                                     `users_inventory`.`trade_type` = NULL 
                                                 WHERE 
                                                    `trades`.`id` is null AND
                                                    (`users_inventory`.`trade_type` = 'offer' OR 
                                                     `users_inventory`.`trade_type` = 'trade')");

                // Optimize and give message
                $GLOBALS['database']->execute_query("OPTIMIZE TABLE `trades`, `users_inventory`, `trade_offers`");
                $GLOBALS['page']->Message( "Trades have been purged old entries" , 'Purge System', 'id='.$_GET['id']); 
            }
            else{
                $GLOBALS['page']->Message( "No entries older than 7 days are present." , 'Purge System', 'id='.$_GET['id']); 
            }
        }

        // BATTLES
        private function battleForm() {
            $GLOBALS['page']->Confirm("All battles will be deleted.", 'Purge System', 'Delete now!'); 
        }

        private function do_purge_battle() {
            
            $GLOBALS['database']->execute_query("TRUNCATE `multi_battle`");
            $GLOBALS['database']->execute_query("OPTIMIZE TABLE `multi_battle`");
            
            $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'awake', `battle_id` = 0, `database_fallback` = 0 WHERE `status` = 'combat'");
            $GLOBALS['database']->execute_query("UPDATE `users` SET `battle_id` = 0, `database_fallback` = 0 WHERE `status` != 'combat'");

            $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'awake' WHERE `status` = 'asleep' AND ((`location` != `village`) and `location` IN ('Konoki','Shroud','Silence','Samui','Shine')) OR (`location` IN ('Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout') AND `Village` != 'Syndicate'))");

            $GLOBALS['database']->execute_query('DELETE FROM `battle_fallback` WHERE `time` < '.(time()+(60*60*24)));
            
            $GLOBALS['page']->Message( "Battles have been purged." , 'Purge System', 'id='.$_GET['id']); 
        }

        // User delete confirmations
        private function inactive_accounts() {
            
            if(!($inactive_accounts = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id` 
                        AND `users_timer`.`last_activity` < (UNIX_TIMESTAMP() - 31536000))
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_timer`.`userid` 
                        AND `users_statistics`.`user_rank` = 'Member' AND `users_statistics`.`rep_ever` < 20 
                        AND `users_statistics`.`rank_id` < 3)
                WHERE (`users`.`perm_ban` = '0' || `users`.`perm_ban` = 0) AND `users`.`join_date` < (UNIX_TIMESTAMP() - 31536000)"))) {
                throw new Exception('There was an error trying to run the inactive query!');
            }
            elseif($inactive_accounts !== '0 rows') {
                
                $accounts = array('inactive' => array());
                
                // Set up Hash Table of Inactive Accounts
                for($i = 0, $size = count($inactive_accounts); $i < $size; $i++) {
                    $accounts['inactive'][$inactive_accounts[$i]['id']] = $inactive_accounts[$i]['username'];
                }

                unset($inactive_accounts);
                
                // Confirmation & Execution
                if (!isset($_POST['Submit'])) {

                    $GLOBALS['page']->Confirm("There are ".count(array_keys($accounts['inactive']))." inactive users in the system.".
                        "<br><br>Inactive accounts are defined as not permanently banned, ".
                        "not logged in and a user for a year or more, a regular member, lower than 20 reputation points ever, ".
                        "and lower than Chuunin rank.<br><br>Delete them?", 'Purge System', 'Delete now!'); 
                } 
                else { 

                    // Obtain Last User ID from Inactive Accounts
                    $last = array_slice(array_keys($accounts['inactive']), -1, 1, TRUE);
                    $last = array_pop($last);
                    
                    // UID and Username String
                    $uids = $usernames = '';
                    
                    // Formulate the Inactive Account UID and Username String
                    foreach(array_keys($accounts['inactive']) as $key) {
                        $uids .= '"' . $key . '"' . (($last == $key) ? '' : ', ');
                        $usernames .= '"' . $accounts['inactive'][$key] . '"' . (($last == $key) ?  '' : ', ');
                    }

                    // Obtain User Manager for Purging Accounts
                    require_once(Data::$absSvrPath.'/clusterup/modules/userManage.class.php');
                    $acctManager = new userManage;
                    
                    // Purge Listed UIDs and/or Usernames
                    $acctManager->purge_accounts($uids, $usernames);
                    
                    $GLOBALS['page']->Message("Inactive accounts have been purged." , 'Purge System', 'id='.$_GET['id']); 
                }
            }
            else {
                $GLOBALS['page']->Message("There are currently no inactive accounts to purge!" , 'Purge System', 'id='.$_GET['id']); 
            }
        }   

        private function flagged_accounts() {
            
            if(
            !($flagged_accounts = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users`.`deletion_timer`
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_timer`.`userid`)
                WHERE (`users`.`perm_ban` = '0' || `users`.`perm_ban` = 0) AND `users`.`deletion_timer` != '0' 
                    AND `users`.`deletion_timer` < (UNIX_TIMESTAMP() - 604800)"))
            || 
            !($flagged_accounts_waiting = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users`.`deletion_timer`
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_timer`.`userid`)
                WHERE (`users`.`perm_ban` = '0' || `users`.`perm_ban` = 0) AND `users`.`deletion_timer` != '0' 
                    AND `users`.`deletion_timer` > (UNIX_TIMESTAMP() - 604800)"))) 
            {
                throw new Exception('There was an error trying to run the flagged query!');
            }
            elseif($flagged_accounts !== '0 rows') {

                if($flagged_accounts_waiting !== '0 rows')
                {
                    $accounts_waiting = array('flagged' => array());
                    // Set up Hash Table of Inactive Accounts
                    for($i = 0, $size = count($flagged_accounts_waiting); $i < $size; $i++) {
                        $accounts_waiting['flagged'][$flagged_accounts_waiting[$i]['id']] = $flagged_accounts_waiting[$i]['username'].':  '.($flagged_accounts_waiting[$i]['deletion_timer'] - (time() - 604800)).'s left';
                    }
                }
                else
                    $accounts_waiting = array('flagged' => array());
                
                $accounts = array('flagged' => array());
                
                // Set up Hash Table of Inactive Accounts
                for($i = 0, $size = count($flagged_accounts); $i < $size; $i++) {
                    $accounts['flagged'][$flagged_accounts[$i]['id']] = $flagged_accounts[$i]['username'];
                }

                // Confirmation & Execution
                if (!isset($_POST['Submit'])) {

                    $GLOBALS['page']->Confirm("There are <span title='".print_r($accounts['flagged'], true)."'>".count(array_keys($accounts['flagged']))."</span> flagged and ready users in the system. There are <span title='".print_r($accounts_waiting['flagged'], true)."'>".count(array_keys($accounts_waiting['flagged']))."</span> users waiting".
                        "<br><br>Flagged accounts are defined as not permanently banned, ".
                        "a deletion timer is set, and the deletion timer is older than the set time.<br><br>".
                        "Delete them?", 'Purge System', 'Delete now!'); 
                } 
                else { 

                    // Obtain Last User ID from Inactive Accounts
                    $last = array_slice(array_keys($accounts['flagged']), -1, 1, TRUE);
                    $last = array_pop($last);
                    
                    // UID and Username String
                    $uids = $usernames = '';
                    
                    // Formulate the Inactive Account UID and Username String
                    foreach(array_keys($accounts['flagged']) as $key) {
                        $uids .= '"' . $key . '"' . (($last == $key) ? '' : ', ');
                        $usernames .= '"' . $accounts['flagged'][$key] . '"' . (($last == $key) ?  '' : ', ');
                    }

                    // Obtain User Manager for Purging Accounts
                    require_once(Data::$absSvrPath.'/clusterup/modules/userManage.class.php');
                    $acctManager = new userManage;
                    
                    // Purge Listed UIDs and/or Usernames
                    $acctManager->purge_accounts($uids, $usernames);
                    
                    $GLOBALS['page']->Message("Flagged accounts have been purged." , 'Purge System', 'id='.$_GET['id']); 
                }
            }
            else if($flagged_accounts_waiting !== '0 rows'){
                $accounts_waiting = array('flagged' => array());
                
                // Set up Hash Table of Inactive Accounts
                for($i = 0, $size = count($flagged_accounts_waiting); $i < $size; $i++) {
                    $accounts_waiting['flagged'][$flagged_accounts_waiting[$i]['id']] = $flagged_accounts_waiting[$i]['username'].':  '.($flagged_accounts_waiting[$i]['deletion_timer'] - (time() - 604800)).'s left';
                }

                $GLOBALS['page']->Message("There are no flagged and ready users in the system. There are <span title='".print_r($accounts_waiting['flagged'], true)."'>".count(array_keys($accounts_waiting['flagged']))."</span> users waiting".
                        "<br><br>Flagged accounts are defined as not permanently banned, ".
                        "a deletion timer is set, and the deletion timer is older than the set time.",
                        'Purge System', 'id='.$_GET['id']); 
            }
            else {
                $GLOBALS['page']->Message("There are currently no flagged accounts to purge!" , 'Purge System', 'id='.$_GET['id']); 
            }
        }

        private function unactivated_accounts() {

            if(!($unactivated_accounts = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username` 
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id` AND `users_timer`.`last_activity` = 0)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_timer`.`userid`)
                WHERE (`users`.`perm_ban` = '0' || `users`.`perm_ban` = 0) AND (`users`.`activation` = '0' || `users`.`activation` = 0) 
                    AND `users`.`join_date` < (UNIX_TIMESTAMP() - 31536000)"))) {
                throw new Exception('There was an error trying to run the unactivated query!');
            }
            elseif($unactivated_accounts !== '0 rows') {
                
                $accounts = array('unactivated' => array());
                
                // Set up Hash Table of Inactive Accounts
                for($i = 0, $size = count($unactivated_accounts); $i < $size; $i++) {
                    $accounts['unactivated'][$unactivated_accounts[$i]['id']] = $unactivated_accounts[$i]['username'];
                }

                unset($unactivated_accounts);
                
                // Confirmation & Execution
                if (!isset($_POST['Submit'])) {

                    $GLOBALS['page']->Confirm("There are ".count(array_keys($accounts['unactivated']))." unactivated users in the system.".
                        "<br><br>Unactivated accounts are defined as not permanently banned, not activated, ".
                        "no activity has been detected, and a user for 42 days or more.".
                        "<br><br>Delete them?", 'Purge System', 'Delete now!');
                } 
                else { 

                    // Obtain Last User ID from Inactive Accounts
                    $last = array_slice(array_keys($accounts['unactivated']), -1, 1, TRUE);
                    $last = array_pop($last);
                    
                    // UID and Username String
                    $uids = $usernames = '';
                    
                    // Formulate the Inactive Account UID and Username String
                    foreach(array_keys($accounts['unactivated']) as $key) {
                        $uids .= '"' . $key . '"' . (($last == $key) ? '' : ', ');
                        $usernames .= '"' . $accounts['unactivated'][$key] . '"' . (($last == $key) ?  '' : ', ');
                    }

                    // Obtain User Manager for Purging Accounts
                    require_once(Data::$absSvrPath.'/clusterup/modules/userManage.class.php');
                    $acctManager = new userManage;
                    
                    // Purge Listed UIDs and/or Usernames
                    $acctManager->purge_accounts($uids, $usernames);
                    
                    $GLOBALS['page']->Message("Unactivated accounts have been purged." , 'Purge System', 'id='.$_GET['id']); 
                }
            }
            else {
                $GLOBALS['page']->Message("There are currently no unactivated accounts to purge!" , 'Purge System', 'id='.$_GET['id']); 
            }
        }        
    }

    new account_purge();