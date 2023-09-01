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


class data_integrity {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_screen();   
        } 
        // User Purges
        elseif ($_GET['act'] == 'battleData') {
            $this->battleRecords();
        }  
        elseif ($_GET['act'] == 'userData') {
            $this->userVillageRecords();
        }
        elseif ($_GET['act'] == 'tablePresence') {
            $this->userTablePresence();
        }
        elseif ($_GET['act'] == 'senseiStudent') {
            $this->senseiStudentCorrelation();
        }
        elseif ($_GET['act'] == 'anbu') {
            $this->fixAnbu();
        }
        elseif ($_GET['act'] == 'clans') {
            $this->fixClans();
        }
        elseif ($_GET['act'] == 'fedStatus') {
            $this->fedStatus();
        }
        elseif ($_GET['act'] == 'fedPaymentCheck') {
            $this->fedPaymentCheck();
        }
        elseif ($_GET['act'] == 'regenerationCheck') {
            $this->regenerationCheck();
        }
        elseif ($_GET['act'] == 'banData') {
            $this->banData();
        }
        elseif ($_GET['act'] == 'sleepingBugs') {
            $this->sleepingBugs();
        }
        elseif ($_GET['act'] == 'tradeBugs') {
            $this->tradeBugs();
        }
        elseif ($_GET['act'] == 'highStats') {
            $this->highStats();
        }
        elseif ($_GET['act'] == 'bloodlineMasks') {
            $this->bloodlineMasks();
        }
        elseif ($_GET['act'] == 'userRanks') {
            $this->userRanks();
        }
        elseif ($_GET['act'] == 'respectDays') {
            $this->respectDays();
        }        
        elseif ($_GET['act'] == 'aiTypes') {
            $this->aiTypes();
        }        
        elseif ($_GET['act'] == 'hospitalMessups') {
            $this->hospitalMessups();
        }        
        elseif ($_GET['act'] == 'restoreReps') {
            $this->restoreReps();
        }        
        elseif ($_GET['act'] == 'SPnoWars') {
            $this->SPbutNoWar();
        }
        elseif( $_GET['act'] == 'ItemsWithoutUsers' ){
            $this->ItemsWithoutUsers();
        }
    }
    
    private function main_screen() {        
        $menu = array(
            array( "name" => "Battle Records", "href" => "?id=".$_GET['id']."&act=battleData"),
            array( "name" => "User Village Setting", "href" => "?id=".$_GET['id']."&act=userData"),
            array( "name" => "User Tables Presence", "href" => "?id=".$_GET['id']."&act=tablePresence"),
            array( "name" => "Sensei-Student Correlation", "href" => "?id=".$_GET['id']."&act=senseiStudent"),
            array( "name" => "Correlate clans with village", "href" => "?id=".$_GET['id']."&act=clans"),
            array( "name" => "Correlate anbu with village", "href" => "?id=".$_GET['id']."&act=anbu"),
            array( "name" => "Missing Fed Status", "href" => "?id=".$_GET['id']."&act=fedStatus"),
            array( "name" => "Wrong Fed Status", "href" => "?id=".$_GET['id']."&act=fedPaymentCheck"),
            array( "name" => "Wrong Base Regeneration", "href" => "?id=".$_GET['id']."&act=regenerationCheck"),
            array( "name" => "Inactive Bans", "href" => "?id=".$_GET['id']."&act=banData"),
            array( "name" => "Sleeping Bugs", "href" => "?id=".$_GET['id']."&act=sleepingBugs"),
            array( "name" => "Trading Bugs", "href" => "?id=".$_GET['id']."&act=tradeBugs"),
            array( "name" => "High Stats Bugs", "href" => "?id=".$_GET['id']."&act=highStats"),
            array( "name" => "Bloodline Masks", "href" => "?id=".$_GET['id']."&act=bloodlineMasks"),
            array( "name" => "Wrong User Ranks", "href" => "?id=".$_GET['id']."&act=userRanks"),
            array( "name" => "Inaccurate Respect Days", "href" => "?id=".$_GET['id']."&act=respectDays"),
            array( "name" => "AI Types", "href" => "?id=".$_GET['id']."&act=aiTypes"),
            array( "name" => "Messed Up Hospital Status", "href" => "?id=".$_GET['id']."&act=hospitalMessups"),
            array( "name" => "Canceled Reversal Paypal Fix", "href" => "?id=".$_GET['id']."&act=restoreReps"),
            array( "name" => "Structure points without war", "href" => "?id=".$_GET['id']."&act=SPnoWars"),
            array( "name" => "User-less Items", "href" => "?id=".$_GET['id']."&act=ItemsWithoutUsers")
        );
        $GLOBALS['template']->assign('subHeader', 'Purge Options');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', 9);
        $GLOBALS['template']->assign('subTitle', 'This panel is for checking the integrity of dynamic data in the database. In essence, it checks the integrity of inter-table settings, and show all cases where something has gone wrong. Contact Terriator with any requests for additional checks.');
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');        
    }
    
    // items in the users_inventory table without users
    private function ItemsWithoutUsers(){
        
        // Check if we should fix entries
        if( isset($_GET['dofix']) && $_GET['dofix'] == "true"){
            $GLOBALS['database']->execute_query("
                DELETE `users_inventory` FROM `users_inventory`
                LEFT JOIN `users` ON `users`.`id` = `users_inventory`.`uid`
                WHERE `users`.`id` IS NULL"
            );   
        }
        
        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
             SELECT `users_inventory`.`id`, `users_inventory`.`uid`, `users_inventory`.`iid`
             FROM `users_inventory`
             LEFT JOIN `users` ON `users`.`id` = `users_inventory`.`uid`
             WHERE `users`.`id` IS NULL
        ");
        
        // Show form
        tableParser::show_list(
                'log', 'Items in users_inventory without users: '.count($users), 
                $users,
            array(
                'id' => "Inventory ID",
                'uid' => "UID",
                'iid' => "IID"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            array(
                array("name" => "Fix All", "href" => "?id=" . $_GET["id"] . "&act=ItemsWithoutUsers&dofix=true")
            ), // Top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Currently handles: some items may not have a specific user attached to them; i.e. if they have accidentally been set to belong to an AI.'
        );
        
    }
    
    // Get all users with structure points without them being in war
    private function SPbutNoWar(){
        
        // Fix it
        if( isset($_GET['uid'], $_GET['act2']) && $_GET['act2'] == "fixSPamount" ){           
            $GLOBALS['database']->execute_query("
                UPDATE `users_missions`
                SET `structureDestructionPoints` = 0,
                    `structureGatherPoints` = 0,
                    `structurePointsActivity` = 0
                WHERE `userid` = ".$_GET['uid']." 
                LIMIT 1"
            );            
        }
        
        // Get the users
        $withdrawals = $GLOBALS['database']->fetch_data("
             SELECT 
                `users_missions`.*, `users`.`username`, `alliances`.*, `users`.`id`
             FROM `users`
             LEFT JOIN `alliances` ON (`alliances`.`village` = `users`.`village`)
             LEFT JOIN `users_missions` ON (`users_missions`.`userid` = `users`.`id`)
             WHERE 
                ((`alliances`.`Konoki` != '2' AND 
                 `alliances`.`Silence` != '2' AND 
                 `alliances`.`Samui` != '2' AND 
                 `alliances`.`Shroud` != '2' AND 
                 `alliances`.`Shine` != '2') OR
                 `users`.`village` = 'Syndicate' 
                )
                AND                
                ( `structureDestructionPoints` != 0 OR 
                  `structureGatherPoints` != 0 OR 
                  `structurePointsActivity` != 0)
            "
        );
        
        // Show form
        tableParser::show_list(
            'log', 
            'PayPal Withdrawal Log',
            $withdrawals, 
            array(
                'username' => "User",
                'structureDestructionPoints' => "SP Heal",
                'structureGatherPoints' => "SP Gather",
                'structurePointsActivity' => "SP Total"           
             ), 
             array( 
                array( "id" => $_GET['id'], "name" => "QuickFix", "act" => "SPnoWars", "uid" => "table.id", "act2" => "fixSPamount")
             ), 
             true, 
             true, 
             false
        );
    }
    
    private function restoreReps(){
        
        
        // Get data
        $withdrawals = $GLOBALS['database']->fetch_data("
             SELECT 
                `ipn_payments`.*, 
                `senderTable`.`username` as `senderActiveName`, 
                `receiverTable`.`username` as `receiverActiveName`
             FROM `ipn_payments`
             LEFT JOIN `users` AS `senderTable` ON (`ipn_payments`.`s_uid` = `senderTable`.`id`)
             LEFT JOIN `users` AS `receiverTable` ON (`ipn_payments`.`r_uid` = `receiverTable`.`id`)
             WHERE 
                `ipn_payments`.`status` = 'Canceled_Reversal' AND `reversed` = '0'
             ORDER BY time DESC LIMIT 72,50"
        );
        
        // Check if anything to do
        if($withdrawals !== "0 rows"){
            
            /* TODO: I THINK THERE ARE MULTIPLE ENTRIES PER PAYMENT - LIMIT TO ONLY 1 BEFORE USING AGAIN
            // Only show the cancel reversed
            foreach( $withdrawals as $withdrawal ){
                //echo"<pre />";
                //echo"=================";
                $withdrawal['price'] = $withdrawal['price'] * 1.1 + 0.3;
                //print_r($withdrawal);

                if( $withdrawal['item'] == "Reputation Points" ){
                    $reps = 500;
                    if( $withdrawal['price'] < 300 ){ $reps = 200; }
                    if( $withdrawal['price'] < 120 ){ $reps = 120; }
                    if( $withdrawal['price'] < 70 ){ $reps = 48; }
                    if( $withdrawal['price'] < 30 ){ $reps = 24; }
                    if( $withdrawal['price'] < 10 ){ $reps = 6; }
                    if( $withdrawal['price'] < 8 ){ $reps = 1; }
                    echo "GIVING ".$reps." REPS TO ".$withdrawal['recipient']." - ".$withdrawal['r_uid']."<br>";

                    // Update User 
                    $GLOBALS['database']->execute_query("
                            UPDATE `users_statistics` SET `rep_now` = `rep_now` + " . $reps . "                             
                            WHERE `uid` = '" . $withdrawal['r_uid'] . "'
                            LIMIT 1"
                    );

                    // Unmark entry
                    $GLOBALS['database']->execute_query("
                            UPDATE `ipn_payments` SET `reversed` = '1' 
                            WHERE `transaction_id` = '" . $withdrawal['transaction_id'] . "'
                            LIMIT 1"
                    );
                }                
                elseif( stristr($withdrawal['item'],"Federal Support") ){                

                    // Get support level
                    $this->fedType = trim(str_replace("Federal Support", "", $withdrawal['item']));
                    $this->fedType = empty($this->fedType) ? "Normal" : $this->fedType;

                    // Don't overwrite higher fed
                    $this->notWhere = "";
                    switch( $this->fedType ){
                        case "Normal": $this->notWhere = " AND `federal_level` != 'Silver' AND `federal_level` != 'Gold' "; break;
                        case "Silver": $this->notWhere = " AND `federal_level` != 'Gold' "; break;
                        case "Gold": $this->notWhere = ""; break;
                    }

                    // Give fed
                    $GLOBALS['database']->execute_query("
                            UPDATE `users`,`users_statistics` 
                            SET 
                                `federal_timer` = '" . (time() + 2678400) . "', 
                                `subscr_id` = 'FromReversal', 
                                `user_rank` = 'Paid' ,
                                `federal_level` = '".$this->fedType."'
                            WHERE 
                                `users`.`id` = `users_statistics`.`uid` AND 
                                `users`.`id` = '" . $withdrawal['r_uid'] . "' ".$this->notWhere
                    );

                    // Unmark entry
                    $GLOBALS['database']->execute_query("
                            UPDATE `ipn_payments` SET `reversed` = '1' 
                            WHERE `transaction_id` = '" . $withdrawal['transaction_id'] . "'
                            LIMIT 1"
                    );


                    echo "GIVING ".$this->fedType." SUPPORT <br>";
                }

            }*/
            
        }
        
        
        
        // Show form
        tableParser::show_list(
            'log', 
            'PayPal Withdrawal Log',
            $withdrawals, 
            array(
                'time' => "Time of Transaction",
                'item' => "Item",
                'price' => "Price",
                'status' => "Status",
                'receiverActiveName' => "Current Recipient Name",
                'recipient' => "Recipient Name at Purchase",
                'reversed' => 'Restored to User',
                'senderActiveName' => "From User",
                'txn_id' => "txn#",
                'rep_now' => 'Recipient Current Reps'                
             ), 
             false, 
             true, 
             true, 
             false
        );
        
    }
    
    // Wake up person
    private function fixHospital(){
        if( isset($_GET['uid'], $_GET['act2']) && $_GET['act2'] == "fixHospital" ){
            
            // Update User
            $GLOBALS['database']->execute_query("
                UPDATE `users`
                SET `status` = 'hospitalized',
                    `location` = `village`
                WHERE `id` = ".$_GET['uid']." LIMIT 1"
            );
            
            $GLOBALS['page']->Message("Woke up user", 'Sleep Fix System', 'id='.$_GET['id']."&act=regenerationCheck"); 
        }
    }
    
    // Check that the regeneration values listed for each user are correct
    private function hospitalMessups(){
        
        // Handle any regen fix requests
        $this->fixHospital();
        
        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`location`,
                `users`.`status`,
                `users`.`village`,
                `users_statistics`.`rank_id`,
                `users_statistics`.`user_rank`,
                `cur_health`
             FROM (`users`,`users_statistics`)
             WHERE 
                `users_statistics`.`uid` = `users`.`id` AND     
                `users`.`status` != 'combat' AND
                ((  
                    `users`.`status` = 'hospitalized' AND
                    `users`.`status` = 'hospitalized' AND
                    `users`.`location` NOT LIKE CONCAT('%', `village`, '%') AND 
                    !(`village` = 'Syndicate' AND `location` = 'Disoriented')
                ) OR (
                    `users`.`status` != 'hospitalized' AND
                    `cur_health` <= 0 
                ))
        ");
        
        // Show form
        tableParser::show_list(
                'log', 'Wrong Hospitalization: '.count($users), 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'user_rank' => "Member Type",
                'cur_health' => "Current HP",
                'village' => "Village",
                'location' => "Location",
                'status' => "Status",
                'apartment' => "House"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "QuickFix", "act" => "hospitalMessups", "uid" => "table.id", "act2" => "fixHospital")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Currently handles: people who are hospitalized in wrong village, or have 0HP without being hospitalized'
        );
        
    }
    
    private function aiTypes(){
        
        // Create original AI list
        $aiList = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` ");
        foreach( $aiList as $k => $v ){
            $aiList[$k]['Status'] = "";
        }
        
        // Go through all missions
        $this->allEntries = cachefunctions::getTasksQuestsMissions(true);
        foreach( $this->allEntries as $mission ){
            $requirements = explode(";", $mission['requirements']);
            foreach( $requirements as $requirement ){
                
                // Expand requirement
                $tags = explode(",",trim($requirement));
                $aiIDs = array();
                
                // handle the two combat tags
                switch( $tags[0] ){
                    case "createAI":
                        // createAI,[.-separated IDlist for single battle],territoryName,chance
                        $aiIDs = explode(".",$tags[1]);                        
                    break;
                    case "initiateCombat":
                        // Format: initiateCombat,aiList,30.55
                        $aiIDs = explode(".",$tags[2]);                        
                    break;
                }
                
                // If AIs were found, check them in aiList
                if( !empty($aiIDs) ){
                    foreach( $aiIDs as $aiID ){
                        foreach( $aiList as $k => $v ){
                            // Convert "mission_" to "crime_", 
                            // since mission_a should be equal to crime_a etc.
                            if( $v['id'] == $aiID && str_replace("mission_","crime_",$v["type"]) !== str_replace("mission_","crime_",$mission['type']) ){
                                $aiList[$k]['Status'] .= "Also called from type <b>".$mission['type']."</b> in \"<i>".$mission['name']."</i>\"<br> ";                                
                            }
                        }
                    }
                }
                
            }
        }
        
        // Clear out all entries from AI list with empty status
        $newAiList = array();
        foreach( $aiList as $k => $v ){
            if( !empty($v['Status']) ){
                $newAiList[] = $v;
            }
        }
        
        // Show form
        tableParser::show_list(
                'log', 'AIs which might not have their type set properly', 
                $newAiList,
            array(
                'id' => "ID",
                'name' => "Name",
                'type' => "Type",
                'location' => "Location",
                'Status' => "Status"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->aiModuleID, "name" => "Edit AI", "act" => "edit", "oid" => "table.id")
            )  ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, //  sorting on columns
            false, // No pretty options
            false, // No top search field
            'Currently simply checks all missions / crimes to see if AI are used in them, and if the type of the AI is set properly.'
        );
        
    }
    
    private function respectDays(){
        
        // Settings
        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 50 );
        
        // Search in the moderator log for bans, and tavern bans, connect to users, 
        // and find the users on who are supposed to be banned, but who are not
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `vil_loyal_pts`,
                `users`.`village`,
                `last_activity`,
                `user_rank`
             FROM `users`,`users_loyalty`,`users_timer`,`users_statistics`
             WHERE 
                `users`.`id` = `users_loyalty`.`uid` AND
                `users`.`id` = `users_timer`.`userid` AND
                `users`.`id` = `users_statistics`.`uid` AND
                ((`users_loyalty`.`village` = 'Syndicate' AND `vil_loyal_pts` > 0) OR (`users_loyalty`.`village` != 'Syndicate' AND `vil_loyal_pts` < 0))
             ORDER BY `last_activity` DESC
             LIMIT ".$min.", ".$number
        );
        
        // Show form
        tableParser::show_list(
                'log', 'Users with stat-ordering', 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'village' => "Village",
                'vil_loyal_pts' => "Respect Points",
                'last_activity' => "Last Activity",
                'user_rank' => "User Rank"
            ), 
            false ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, //  sorting on columns
            false, // No pretty options
            false, // No top search field
            'Shows users in village with negative respect points, or outlaws with positive respect points.'
        );
        
    }
    
    
    private function userRanks(){
        $users = $GLOBALS['database']->fetch_data("
            SELECT 
                `id`, `username`,`user_rank`,`rank`, `village`
            FROM `users`,`users_statistics` 
            WHERE 
                `users`.`id` = `users_statistics`.`uid` AND
                (
                    (`users`.`village` != 'Syndicate' AND `rank` != 'Academy student' AND `rank` != 'Genin' AND `rank` != 'Chuunin' AND `rank` != 'Jounin' AND `rank` != 'Elite jounin') OR
                    (`users`.`village` = 'Syndicate' AND `rank` != 'Lower outlaw' AND `rank` != 'Higher outlaw' AND `rank` != 'Elite outlaw')
                )");
        
        // Show form
        tableParser::show_list(
                'log', 'Non-standard user rank: '.count($users), 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'village' => "Village",
                'rank' => "Rank",
                'user_rank' => "User Type"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Looks up all users who have non-standard ranks'
        );
    }
    
    private function bloodlineMasks(){
        $users = $GLOBALS['database']->fetch_data("
            SELECT 
                `id`, `username`,`user_rank`, `bloodline`, `bloodlineMask`
            FROM `users`,`users_statistics` 
            WHERE 
                `users`.`id` = `users_statistics`.`uid` AND
                `bloodlineMask` != ''");
        
        // Show form
        tableParser::show_list(
                'log', 'Wrong Base Regeneration: '.count($users), 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'bloodline' => "Bloodline",
                'bloodlineMask' => "Bloodline Mask",
                'user_rank' => "User Type"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Looks up all users who have a bloodline mask'
        );
    }
    
    private function highStats(){
        
        // Settings
        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 50 );
        $order =  tableParser::get_page_order( array("vil_loyal_pts") );
        
        // Search in the moderator log for bans, and tavern bans, connect to users, 
        // and find the users on who are supposed to be banned, but who are not
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `vil_loyal_pts`,
                `last_activity`,
                `user_rank`
             FROM `users`,`users_loyalty`,`users_timer`,`users_statistics`
             WHERE 
                `users`.`id` = `users_loyalty`.`uid` AND
                `users`.`id` = `users_timer`.`userid` AND
                `users`.`id` = `users_statistics`.`uid`
             ".$order.' LIMIT '.$min.", ".$number
        );
        
        // Show form
        tableParser::show_list(
                'log', 'Users with stat-ordering', 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'vil_loyal_pts' => "Respect Points",
                'last_activity' => "Last Activity",
                'user_rank' => "User Rank"
            ), 
            false ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            true, //  sorting on columns
            false, // No pretty options
            false, // No top search field
            'Checks users on key stats, and let\'s you order on those stats. If you want additional columns shown, please request from coder.'
        );
        
    }
    
    private function banData(){
        
        // Search in the moderator log for bans, and tavern bans, connect to users, 
        // and find the users on who are supposed to be banned, but who are not
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.perm_ban,
                `moderator_log`.`duration`,
                `moderator_log`.`action`,
                `moderator_log`.`time`,
                `users`.`ban_time`,
                `users`.`post_ban`,
                `users`.`tban_time`
             FROM `moderator_log`
             LEFT JOIN `users` ON `moderator_log`.`uid` = `users`.`id`
             WHERE 
                ((`moderator_log`.`time` > (UNIX_TIMESTAMP() - (7 * 24 * 3600)) AND `duration` = '1 week') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - 3600) AND `duration` = '1 hour') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - (30 * 24 * 3600)) AND `duration` = '1 month') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - (12 * 3600)) AND `duration` = '12 hours') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - (2 * 24 * 3600)) AND `duration` = '2 days') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - (14 * 24 * 3600)) AND `duration` = '2 weeks') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - 1800) AND `duration` = '30 minutes') OR
                (`moderator_log`.`time` > (UNIX_TIMESTAMP() - (8 * 3600)) AND `duration` = '8 hours') OR
                (`duration` = 'Indefinte') OR
                (`duration` = 'Permanent')) AND
                `users`.`id` IS NOT NULL
        ");
        
        foreach( $users as $key => $value ){
            if( $value['action'] == "Ban" && $value['ban_time'] > 0 ){
                $users[$key]["banTo"] = $value['ban_time'];
            }
            else{
                $users[$key]["banTo"] = 0;
            }
            if( $value['action'] == "Tavern-Ban" && $value['tban_time'] > 0){
                $users[$key]["tbanTo"] = $value['tban_time'];
            }
            else{
                $users[$key]["tbanTo"] = 0;
            }
        }
        
        // Show form
        tableParser::show_list(
                'log', 'Potentially Wrong Ban Status: '.count($users), 
                $users,
            array(
                'id' => "ID",
                'time' => "Ban Time Start",
                'username' => "Username",
                'duration' => "Ban Duration",
                'action' => "Ban Type",
                'perm_ban' => "GPB Status",
                'banTo' => "GB Release Time",
                'post_ban' => "TB Status",
                'tbanTo' => "TB Release Time"
            ), 
            false ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Checks all bans in the database & their duration, 
             shows all the users connected to these bans who are not banned currently. 
             Not sure this accounts for bans that have been withdrawn. <br>
             GB = Game Ban<br>
             GPB = Game Permanent Ban<br>
             TB = Tavern Ban<br>
             TPB = Tavern Permanent Ban'
        );
        
    }
    
    // Wake up person
    private function fixItem(){
        if( isset($_GET['invID'], $_GET['act2']) && $_GET['act2'] == "removeFromTrade" ){
            
            // Get the item
            $item = $GLOBALS['database']->fetch_data("
            SELECT * FROM `users_inventory` WHERE `users_inventory`.`id` = ".$_GET['invID']." LIMIT 1");
            if( $item !== "0 rows" ){
                
                // Update User
                $GLOBALS['database']->execute_query("
                    UPDATE `users_inventory`
                    SET 
                        `trading` = NULL, 
                        `trade_type` = NULL
                    WHERE 
                        `id` = ".$_GET['invID']." 
                    LIMIT 1"
                );
                
                // Remove trade
                if( !empty($item[0]['trading']) ){
                    $GLOBALS['database']->execute_query("
                        DELETE FROM `trades`
                        WHERE `id` = ".$item[0]['trading']." 
                        LIMIT 1"
                    );
                }
                
            }
        }
    }
    
    // Check that the regeneration values listed for each user are correct
    private function tradeBugs(){
        
        // Handle any regen fix requests
        $this->fixItem();
        
        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`village` as `userVillage`,
                `items`.`name`,
                `items`.`type`,
                `users_inventory`.`trade_type`,
                `users_inventory`.`trading`,
                `users_inventory`.`id` as `invID`,
                `trades`.`trade_type` as `tradeVillage`
             FROM (`users`,`users_inventory`)
             LEFT JOIN `items` ON `users_inventory`.`iid` = `items`.`id`
             LEFT JOIN `trades` ON `users_inventory`.`trading` = `trades`.`id`
             LEFT JOIN `trade_offers` ON (`trades`.`id` = `trade_offers`.`tid` AND `trade_offers`.`uid` = `users`.`id`)
             WHERE 
                `users_inventory`.`uid` = `users`.`id` AND
                `users_inventory`.`trading` IS NOT NULL AND
                ((`trade_offers`.`id` IS NULL AND `users_inventory`.`trade_type` = 'offer') OR 
                 (`trades`.`id` IS NULL AND `users_inventory`.`trade_type` = 'trade') OR
                 (`trades`.`trade_type` != 'Global' AND `trades`.`trade_type` != `users`.`village`))
        ");
        
        // Show form
        tableParser::show_list(
                'log', 'Wrong Base Regeneration: '.count($users), 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'userVillage' => "User Village",
                'tradeVillage' => "Trade Type/Village",
                'name' => "Item Name",
                'type' => "Item Type",
                'trade_type' => "Trade Type",
                'trading' => "Trading"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "QuickFix", "act" => "tradeBugs", "invID" => "table.invID", "act2" => "removeFromTrade")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Currently handles: people who have items marked as trading, without them being attached to any trades'
        );
        
    }
    
    // Wake up person
    private function fixSleep(){
        if( isset($_GET['uid'], $_GET['act2']) && $_GET['act2'] == "wakeup" ){
            // Update User
            $GLOBALS['database']->execute_query("
                UPDATE `users`
                SET `status` = 'awake'
                WHERE 
                    `status` = 'asleep' AND
                    `id` = ".$_GET['uid']." LIMIT 1"
            );
            
            $GLOBALS['page']->Message("Woke up user", 'Sleep Fix System', 'id='.$_GET['id']."&act=regenerationCheck"); 
        }
    }
    
    // Check that the regeneration values listed for each user are correct
    private function sleepingBugs(){
        
        // Handle any regen fix requests
        $this->fixSleep();
        
        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`apartment`,
                `users`.`location`,
                `users`.`status`,
                `users_statistics`.`rank_id`,
                `users_statistics`.`user_rank`,
                IF( `users`.`apartment` IS NULL, 'N/A', `users`.`apartment` )  as `apartment`
             FROM (`users`,`users_statistics`)
             LEFT JOIN `homes` ON `users`.`apartment` = `homes`.`id`
             WHERE 
                `users_statistics`.`uid` = `users`.`id` AND
                `users`.`status` = 'asleep' AND
                `users`.`location` LIKE '%village' AND
                `homes`.`id` IS NULL
        ");
        
        // Show form
        tableParser::show_list(
                'log', 'Wrong Base Regeneration: '.count($users), 
                $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'user_rank' => "Member Type",
                'location' => "Location",
                'status' => "Status",
                'apartment' => "House"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "QuickFix", "act" => "sleepingBugs", "uid" => "table.id", "act2" => "wakeup")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Currently handles: people who are asleep in village, but have no homes'
        );
        
    }
    
    // Fix regeneration
    private function fixRegen(){
        if( isset($_GET['uid'], $_GET['newRegen']) ){
            // Update User
            $GLOBALS['database']->execute_query("
                UPDATE `users_statistics`
                SET `regen_rate` = `regen_rate` - '".$_GET['newRegen']."'
                WHERE `uid` = ".$_GET['uid']." LIMIT 1"
            );            
        }
    }
    
    
    // Check that the regeneration values listed for each user are correct
    private function regenerationCheck(){
        
        // Handle any regen fix requests
        $extraText = "";
        if(isset($_GET['uid'], $_GET['newRegen'])) {
            $this->fixRegen();
            $extraText = "<br><b>Set the regen of userID ".$_GET['uid']." to ".$_GET['newRegen']."</b>";
        }
        

        // Regen Factors that directly affect Regen Rate
        $regen_factors = "IF ( `users`.`status` = 'asleep', 
                IF( `users`.`location` LIKE CONCAT('%', `users_loyalty`.`village`, '%') != 1,
                    ( `users_statistics`.`regen_rate` - (2 * `users_statistics`.`rank_id`) - 
                        IF( ( ISNULL(`users`.`bloodline`) OR `users`.`bloodline` IN ('', 'None') ) = 1, 0, `bloodlines`.`regen_increase` )
                    ),
                    ( `users_statistics`.`regen_rate` - 
                        IF( ISNULL(`homes`.`regen`) = 1, 0, `homes`.`regen` ) - 
                        IF( ( ISNULL(`users`.`bloodline`) OR `users`.`bloodline` IN ('', 'None') ) = 1, 0, `bloodlines`.`regen_increase` )
                    )
                ),
                ( `users_statistics`.`regen_rate` - 
                    IF( ( ISNULL(`users`.`bloodline`) OR `users`.`bloodline` IN ('', 'None') ) = 1, 0, `bloodlines`.`regen_increase` )
                )
            )";

        // User Base Regen Calculation
        $base_regen = "IF( `users_statistics`.`rank_id` = 1, 25,
            IF( `users_statistics`.`rank_id` = 2, (25 + ".DATA::$RANK_REGEN_GAIN[2]."), 
            IF( `users_statistics`.`rank_id` = 3, (25 + ".DATA::$RANK_REGEN_GAIN[2]." + ".DATA::$RANK_REGEN_GAIN[3]."),
            IF( `users_statistics`.`rank_id` = 4, (25 + ".DATA::$RANK_REGEN_GAIN[2]." + ".DATA::$RANK_REGEN_GAIN[3]." + ".DATA::$RANK_REGEN_GAIN[4]."),
            IF( `users_statistics`.`rank_id` = 5, (25 + ".DATA::$RANK_REGEN_GAIN[2]." + ".DATA::$RANK_REGEN_GAIN[3]." + ".DATA::$RANK_REGEN_GAIN[4]." + ".DATA::$RANK_REGEN_GAIN[5]."), 'Invalid Rank')))))";

        if(!($users = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users`.`status`, 
            `users`.`location`, `users`.`apartment`, `users`.`regen_boost` AS `boostRegen`, `users`.`item_regen_boost` AS `boostIRegen`,

            `users_statistics`.`user_rank`, `users_statistics`.`rank_id`, `users_statistics`.`regen_rate` AS `currentRate`, 
            `users_statistics`.`regen_bonus` AS `bonusRegen`, 

            IF( ISNULL(`bloodlines`.`name`), 'N/A', `bloodlines`.`name` ) AS `bloodline`,
            IFNULL(`bloodlines`.`regen_increase`, 0) AS `bloodRegen`,

            IFNULL(`homes`.`regen`, 0) AS `homeRegen`,

            (".$base_regen.") AS `baseRegen`,

            ROUND(((".$regen_factors.") - (SELECT `baseRegen`)), 2) AS `offsetRegen`

            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                LEFT JOIN `homes` ON (`homes`.`id` = `users`.`apartment`)
                LEFT JOIN `bloodlines` ON (`users`.`bloodline` LIKE CONCAT(`bloodlines`.`name`,'%'))
            HAVING `offsetRegen` != 0"))) {
            throw new Exception('There was an error with the query!');
        }

        // Show form
        tableParser::show_list('log', 'Wrong Base Regeneration: '.count($users), $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'rank_id' => "RankID",
                'user_rank' => "Member Type",
                'location' => "Location",
                'status' => "Status",
                'bloodline' => "Bloodline",
                'currentRate' => "Current Regen",
                'baseRegen' => "Base Regen",
                'offsetRegen' => "Regen Offset",
                'homeRegen' => "Home Regen",
                'bloodRegen' => "Bloodline Regen",
                'bonusRegen' => "Bonus Regen",
                'boostRegen' => "Regen Boost",
                'boostIRegen' => "Item Regen Boost"
            ), 
            array( 
                array( 
                    "id" => $GLOBALS['page']->userModuleID, 
                    "name" => "Profile", 
                    "act" => "mod", 
                    "uid" => "table.id"
                ),
                array( 
                    "id" => $_GET['id'],
                    "name" => "QuickFix", 
                    "act" => "regenerationCheck", 
                    "uid" => "table.id", 
                    "newRegen" => "table.offsetRegen"
                )
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Checks the base regeneration of user compared with what it\'s supposed to be given: home, rank, and bloodline. <b>Currently only handles people not asleep or asleep in homes in villages</b>'.$extraText
        );

    }
    
    // Check ipn payments for fed support, and users who don't seem to have fed support
    private function fedPaymentCheck(){
        
        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
            SELECT * , `users_statistics`.`federal_level`
            FROM  `ipn_payments`
            LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `ipn_payments`.`r_uid`)
            WHERE  
                ((`item` =  'Normal Federal Support' AND `federal_level` !=  'Normal') OR
                (`item` =  'Silver Federal Support' AND `federal_level` !=  'Silver') OR
                (`item` =  'Gold Federal Support' AND `federal_level` !=  'Gold')) AND
                `time` > (UNIX_TIMESTAMP() - (30 * 24 * 3600)) AND
                `price` > 0
            ORDER BY  `ipn_payments`.`time` DESC 
        ");
        
        // Fed support ranking
        $fsr = array(
            "None Federal Support" => 1,
            "Normal Federal Support" => 1,
            "Silver Federal Support" => 2,
            "Gold Federal Support" => 3
        );
        
        $sortedUsers = array();
        if( $users !== "0 rows" ){
            foreach( $users as $user ){
                if(!(
                    ($user["status"] == "Failed" && $user["federal_level"] == "None") ||
                    ($user["status"] == "Pending" && $user["federal_level"] == "None") ||
                    ($user["status"] == "" && $user["federal_level"] == "None") ||
                    ($user["status"] == "Completed" && $fsr[ $user["federal_level"]." Federal Support"] > $fsr[ $user["item"] ] )
                )){
                    $sortedUsers[] = $user;
                }
            }
        }
        
        // Show form
        tableParser::show_list(
                'users', 'Users having a wrong federal rank based on paypal records', 
                $sortedUsers, array(
            'transaction_id' => "ID",
            'time' => "Time of Transaction",
            'price' => "Price",
            'sender' => "From User",
            'item' => "Item",
            'federal_level' => "Current Level",
            'status' => "Status",
            'recipient' => "To User",
            'txn_id' => "txn#",
            'txn_type' => "txn type"
            ), 
                array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.r_uid")
            ) ,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Fix users who have federal level without a corresponding rank - upon request
        if( isset($_GET['userID']) && is_numeric($_GET['userID']) ){
            // Update User
            $GLOBALS['database']->execute_query("
                UPDATE `users_statistics` SET `federal_level` = 'None'
                WHERE `uid` = '".$_GET['userID']."' AND `user_rank` = 'Member'
                LIMIT 1"
            );
        }
        
        // Users who have fed level without the user rank
        $users = $GLOBALS['database']->fetch_data("
            SELECT * , `users_statistics`.`federal_level`
            FROM  `users`
            LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
            WHERE `user_rank` = 'Member' AND `federal_level` != 'None'
        ");
        
        tableParser::show_list(
                'users', 'Users having a wrong federal rank based on user rank', 
                $users, array(
            'username' => "Username",
            'federal_level' => "Current Fed Level",
            'user_rank' => "User Rank",
            'status' => "Status",
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "QuickFix", "act" => "fedPaymentCheck", "userID" => "table.id")
            ) ,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/data_integrityTests/main.tpl');
        
    }
    
    // People which have paid without a fed status level
    private function fedStatus(){
        
        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users_statistics`.`federal_level`,
                `users_statistics`.`user_rank`
             FROM `users`,`users_statistics`
             WHERE 
                `users_statistics`.`uid` = `users`.`id` AND
                `users_statistics`.`user_rank` = 'Paid' AND
                (`users_statistics`.`federal_level` = 'None' OR `users_statistics`.`federal_level` = '')
        ");
        
        tableParser::show_list(
            'users',
            "People which have paid without a fed status level: ".count($users), 
            $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'federal_level' => "Fed Level",
                'user_rank' => "User Rank"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
    }
    
    // Fix up any clans
    private function fixClans(){
        
        // Users who are in a clan that isn't in their village
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`village`,
                `users_preferences`.`clan`,
                `clans`.`village` as `clanVillage`
             FROM `users`,`users_preferences`
             LEFT JOIN `clans` ON (`clans`.`id` = `users_preferences`.`clan`)
             WHERE 
                `users_preferences`.`uid` = `users`.`id` AND
                `squads`.`village` !=  `users`.`village` 
        ");
        
        tableParser::show_list(
            'users',
            "People with a clan that isnt for their village: ".count($users), 
            $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'clan' => "Clan ID",
                'village' => "User Village",
                'clanVillage' => "Clan Village"
            ), 
            false ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Fixing
        if( $users !== "0 rows" ){
            foreach( $users as $user ){

                // Update User
                $GLOBALS['database']->execute_query("
                    UPDATE `users_preferences`
                    LEFT JOIN `clans` ON (`clans`.`village` = '".$user['village']."')
                    SET `users_preferences`.`clan` = `clans`.`id`
                    WHERE `users_preferences`.`uid` = ".$user['id']
                );
            }
        }
        
    }
    
    // Fix up any ANBU
    private function fixAnbu(){
        
        // Users who are in a clan that isn't in their village
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`village`,
                `users_preferences`.`anbu`,
                `squads`.`village` as `anbuVillage`
             FROM `users`,`users_preferences`
             LEFT JOIN `squads` ON (`squads`.`id` = `users_preferences`.`anbu`)
             WHERE 
                `users_preferences`.`uid` = `users`.`id` AND
                `squads`.`village` !=  `users`.`village` 
        ");
        
        tableParser::show_list(
            'users',
            "People with a ANBU that isnt for their village: ".count($users), 
            $users,
            array(
                'id' => "user ID",
                'username' => "Username",
                'anbu' => "ANBU ID",
                'village' => "User Village",
                'anbuVillage' => "Clan Village"
            ), 
            array(
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id"),
            ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        
    }
    
    // Fix students & senseis
    private function fixStudents(){
        
        // Grab the actions
        if( isset($_GET['perform']) ){
            switch( $_GET['perform'] ){
                case "remvSensei": 
                    
                    // Confirm validity of student data
                    $student = $GLOBALS['database']->fetch_data("
                        SELECT `users`.`id`, `users`.`username`,`users_preferences`.`sensei`
                        FROM `users`,`users_preferences`
                        WHERE 
                           `users_preferences`.`uid` = `users`.`id` AND
                           `users`.`id` = '".$_GET['uid']."' AND
                           `users_preferences`.`sensei` != '_none' AND
                           `users_preferences`.`sensei` != '_disabled' AND
                           `users_preferences`.`sensei` != ''
                   ");
                    if( $student !== "0 rows" ){
                        
                        // Remove sensei from student
                        $GLOBALS['database']->execute_query("
                            UPDATE `users_preferences`
                            SET `sensei` = ''
                            WHERE `uid` = ".$_GET['uid']."
                            LIMIT 1"
                        );
                    }
                break;
                case "remvStudent": 
                    
                    // Confirm validity of student data
                    $sensei = $GLOBALS['database']->fetch_data("
                        SELECT `users`.`id`, `users`.`username`,
                               `users`.`student_1`,`users`.`student_2`,`users`.`student_3`
                        FROM `users`,`users_preferences`
                        WHERE 
                           `users_preferences`.`uid` = `users`.`id` AND
                           `users`.`id` = '".$_GET['sensei_id']."'
                   ");
                    $student = $GLOBALS['database']->fetch_data("
                        SELECT `users`.`id`, `users`.`username`,`users_preferences`.`sensei`
                        FROM `users`,`users_preferences`
                        WHERE 
                           `users_preferences`.`uid` = `users`.`id` AND
                           `users`.`id` = '".$_GET['student_id']."'
                   ");
                    
                    // Run checks
                    if( $sensei !== "0 rows" ){
                        
                        // If student doesn't exist, or is not sensei's student
                        if( $student == "0 rows" || in_array($student[0]['id'], array($sensei[0]['student_1'],$sensei[0]['student_2'],$sensei[0]['student_3'])) ){

                            // Get position of the student
                            foreach( array("student_1","student_2","student_3") as $sPos ){
                                if ($sensei[0][$sPos] == $_GET['student_id'] ) {
                                    $GLOBALS['database']->execute_query("UPDATE `users` SET `".$sPos."` = '' WHERE `id` = '" . $sensei[0]['id'] . "' LIMIT 1" );
                                }
                            }
                            
                        }
                        
                    }
                    
                    break;
            }
        }
    }
    
    // Check sensei-student correlations
    private function senseiStudentCorrelation(){
        
        // Perform either of the two actions available - i.e. resetting students or senseis
        $this->fixStudents();
        
        // Students with a sensei set, where sensei doesn't have the students
        $students = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users_preferences`.`sensei`,
                `senseiTable`.`username` as `senseiName`
             FROM `users`,`users_preferences`
             LEFT JOIN `users` AS `senseiTable` ON (`senseiTable`.`student_1` = `users_preferences`.`uid` OR `senseiTable`.`student_2` = `users_preferences`.`uid` OR `senseiTable`.`student_3` = `users_preferences`.`uid`)
             WHERE 
                `users_preferences`.`uid` = `users`.`id` AND
                `users_preferences`.`sensei` IS NOT NULL AND
                `users_preferences`.`sensei` != '_none' AND
                `users_preferences`.`sensei` != '_disabled' AND
                `users_preferences`.`sensei` != '' AND
                `senseiTable`.`username` IS NULL
        ");
        
        tableParser::show_list(
            'users',
            "Students with a sensei set, where sensei doesn't have the students", 
            $students,
            array(
                'id' => "ID",
                'username' => "Username",
                'sensei' => "Sensei"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Sensei Profile", "act" => "mod", "uid" => "table.sensei"),
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Student Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "Remove Sensei from Students",  "act" => "senseiStudent", "perform"=>"remvSensei", "uid" => "table.id")
            ) ,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Senseis with students set, where the students don't have the sensei
        $sensei1 = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`student_1` AS `student`,
                `studentTable1`.`uid` AS `studentUID1`,
                `studentTable2`.`uid` AS `studentUID2`
             FROM `users`
             LEFT JOIN `users_preferences` AS `studentTable1` ON (`studentTable1`.`sensei` = `users`.`id`)
             LEFT JOIN `users_preferences` AS `studentTable2` ON (`studentTable2`.`uid` = `users`.`student_1`)
             WHERE 
                `users`.`student_1` != '_none' AND
                `users`.`student_1` != '' AND
                (`studentTable1`.`uid` IS NULL OR `studentTable2`.`uid` IS NULL)
        ");
        $sensei2 = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`student_2` AS `student`,
                `studentTable1`.`uid` AS `studentUID1`,
                `studentTable2`.`uid` AS `studentUID2`
             FROM `users`
             LEFT JOIN `users_preferences` AS `studentTable1` ON (`studentTable1`.`sensei` = `users`.`id`)
             LEFT JOIN `users_preferences` AS `studentTable2` ON (`studentTable2`.`uid` = `users`.`student_2`)
             WHERE 
                `users`.`student_2` != '_none' AND
                `users`.`student_2` != '' AND
                (`studentTable1`.`uid` IS NULL OR `studentTable2`.`uid` IS NULL)
        ");
        $sensei3 = $GLOBALS['database']->fetch_data("
             SELECT 
                `users`.`id`,
                `users`.`username`,
                `users`.`student_3` AS `student`,
                `studentTable1`.`uid` AS `studentUID1`,
                `studentTable2`.`uid` AS `studentUID2`
             FROM `users`
             LEFT JOIN `users_preferences` AS `studentTable1` ON (`studentTable1`.`sensei` = `users`.`id`)
             LEFT JOIN `users_preferences` AS `studentTable2` ON (`studentTable2`.`uid` = `users`.`student_3`)
             WHERE 
                `users`.`student_3` != '_none' AND
                `users`.`student_3` != '' AND
                (`studentTable1`.`uid` IS NULL OR `studentTable2`.`uid` IS NULL)
        ");
        $final = array();
        if( $sensei1 !== "0 rows" ){ $final = array_merge($final, $sensei1); }
        if( $sensei2 !== "0 rows" ){ $final = array_merge($final, $sensei2); }
        if( $sensei3 !== "0 rows" ){ $final = array_merge($final, $sensei3); }
        
        
        tableParser::show_list(
            'usersBattles',
            "Senseis with students set, where the students don't have the sensei", 
            $final,
            array(
                'id' => "ID",
                'username' => "Username",
                'student' => "Student Name"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Sensei Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Student Profile", "act" => "mod", "uid" => "table.student"),
                array( "id" => $_GET['id'], "name" => "Remove Students from Sensei", "act" => "senseiStudent", "perform"=>"remvStudent", "sensei_id" => "table.id", "student_id" => "table.student")
            ) ,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Load template
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/data_integrityTests/main.tpl');
        
    }

    // Wake up person
    private function fixBattle(){
        if( isset($_GET['battleID']) && is_numeric($_GET['battleID']) ){
            // Update User
            $GLOBALS['database']->execute_query("
                DELETE FROM `multi_battle` 
                WHERE `id` = '".$_GET['battleID']."'
                LIMIT 1"
            );
        }
    }
    
    //  User Battle Records
    private function battleRecords() {
        
        // Fix battle entries upon request
        $this->fixBattle();
        
        // Get errors in the users table
        $battles = $GLOBALS['database']->fetch_data("
             SELECT `id`,`username`,`status`,`battle_id` FROM `users` 
             WHERE 
                (`battle_id` = 0 AND `status` = 'combat') OR 
                (`battle_id` != 0 AND `status` != 'combat')
        ");
        
        tableParser::show_list(
            'users',
            'Users in battle without battle IDs', 
            $battles,
            array(
                'id' => "ID",
                'username' => "Username",
                'status' => "Status",
                'battle_id' => "Battle ID"
            ), 
            false,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Get all users in battle, for which no battle exists
        $usersBattle = $GLOBALS['database']->fetch_data("
             SELECT `users`.`id`,`username`,`status`,`battle_id`
             FROM `users` 
             LEFT JOIN `multi_battle` ON (`users`.`battle_id` = `multi_battle`.`id`)
             WHERE 
                `status` = 'combat' AND
                `multi_battle`.`id` IS NULL
        ");
        
        tableParser::show_list(
            'usersBattles',
            'Could happen if the battle table was cleared, and users not updated yet', 
            $usersBattle,
            array(
                'id' => "ID",
                'username' => "Username",
                'status' => "Status",
                'battle_id' => "Battle ID"
            ), 
            false,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Get all battles, and check that the users exist (if they are not AI)
        $battles = $GLOBALS['database']->fetch_data("SELECT * FROM `multi_battle`");
        $usersFromBattles = array();
        if( $battles !== "0 rows" ){
            foreach( $battles as $battle ){
                $userIds = array_merge( explode("|||", $battle['user_ids']), explode("|||", $battle['opponent_ids']) );
                foreach( $userIds as $uid ){
                    $localUser = $GLOBALS['database']->fetch_data("
                          SELECT `id`,`username`,`status`,`battle_id`
                          FROM `users`
                          WHERE `id` = '".$uid."'
                          LIMIT 1
                    ");
                    if( $localUser !== "0 rows" ){
                        if( $localUser[0]['status'] !== "combat" || $battle['id'] !== $localUser[0]['battle_id']){
                            $localUser[0]['realBattle'] = $battle['id'];
                            $localUser[0]['battle_type'] = $battle['battle_type'];
                            $usersFromBattles[] = $localUser[0];
                        }
                    }
                }
            }
        }
        
        
        tableParser::show_list(
            'battleUsers',
            'Users found in battle entries, which are not marked as in battle', 
            $usersFromBattles,
            array(
                'id' => "ID",
                'username' => "Username",
                'status' => "Status",
                'battle_id' => "User Battle ID",
                'battle_type' => "Battle Type",
                'realBattle' => "Actual Battle ID"
            ), 
            array( 
                array( "id" => $GLOBALS['page']->userModuleID, "name" => "Profile", "act" => "mod", "uid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "QuickFix", "act" => "battleData", "battleID" => "table.realBattle")
            ) ,
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Not supposed to happen. Figure out how they ended up like this.'
        );
        
        // Load template
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/data_integrityTests/main.tpl');
        
    }

    // Table presence
    private function userTablePresence(){
        
        // Get all rows in the users table without unmatched rows in the other tables
        $users = $GLOBALS['database']->fetch_data("
             SELECT 
                `id`,`username`,`users`.`village`,
                `bingo_book`.`userid` as `bingoBookID`,
                `users_timer`.`userid` as `usersTimerID`,
                `users_loyalty`.`uid` as `usersLoyaltyID`,
                `users_missions`.`userid` as `usersMissionID`,
                `users_occupations`.`userid` as `usersOccupationID`,
                `users_preferences`.`uid` as `usersPreferencesID`,
                `users_statistics`.`uid` as `usersStatisticsID`
             FROM `users`
             LEFT JOIN `bingo_book` ON `bingo_book`.`userid` = `users`.`id`
             LEFT JOIN `users_timer` ON `users_timer`.`userid` = `users`.`id`
             LEFT JOIN `users_loyalty` ON `users_loyalty`.`uid` = `users`.`id`
             LEFT JOIN `users_missions` ON `users_missions`.`userid` = `users`.`id`
             LEFT JOIN `users_occupations` ON `users_occupations`.`userid` = `users`.`id`
             LEFT JOIN `users_preferences` ON `users_preferences`.`uid` = `users`.`id`
             LEFT JOIN `users_statistics` ON `users_statistics`.`uid` = `users`.`id`
             WHERE 
                `bingo_book`.`userid` is null OR
                `users_timer`.`userid` is null OR
                `users_loyalty`.`uid` is null OR
                `users_missions`.`userid` is null OR
                `users_occupations`.`userid` is null OR
                `users_preferences`.`uid` is null OR
                `users_statistics`.`uid` is null
        ");
        
        // Show to admin
        tableParser::show_list(
            'users',
            'Incomplete Users', 
            $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'village' => "Village",
                'bingoBookID' => "bingo_book",
                'usersTimerID' => "timers",
                'usersLoyaltyID' => "loyalty",
                'usersMissionID' => "missions",
                'usersOccupationID' => "occupation",
                'usersPreferencesID' => "preferences",
                'usersStatisticsID' => "statistics"
            ), 
            false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'In order for each user to be functional, he must have several entries in the database. 
             This table shows all users who do not have all required entries'
        );
        
    }
    
    // User village records
    private function userVillageRecords(){
        
        // Get all the users
        $users = $GLOBALS['database']->fetch_data("
             SELECT `id`,`username`,`users`.`village` as `userVillage`, `users_loyalty`.`village` as `loyalVillage` 
             FROM `users`,`users_loyalty`
             WHERE 
                `users`.`id` = `users_loyalty`.`uid` AND
                `users`.`village` != `users_loyalty`.`village`
        ");
        
        // Show to admin
        tableParser::show_list(
            'users',
            'Unsynced User Village ', 
            $users,
            array(
                'id' => "ID",
                'username' => "Username",
                'loyalVillage' => "Loyalty Village",
                'userVillage' => "Users Village"
            ), 
            false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'User village is set in two tables. The below show users where these two village values are not synced. This should be empty!'
        );
        
    }
    
        
}

new data_integrity();