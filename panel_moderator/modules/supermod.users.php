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

class users {

    public function __construct() {
        if (!isset($_GET['act']) || $_GET['act'] == 'edits') {
            $this->main_page();
        } elseif ($_GET['act'] == 'search') {
            //	Search for specific user(s)
            if (!isset($_POST['Submit'])) {
                if (isset($_GET['uid'])) {
                    $this->search_results();
                } else {
                    $this->search_form();
                }
            } else {
                $this->search_results();
            }
        } elseif ($_GET['act'] == 'mod') {
            if (!isset($_GET['type'])) {
                $this->edit_main();
            } elseif (
               $_GET['type'] == 'users' ||
               $_GET['type'] == 'users_preferences' ||
               $_GET['type'] == 'users_statistics' ||
               $_GET['type'] == 'users_loyalty' ||
               $_GET['type'] == 'users_occupations' ||
               $_GET['type'] == 'users_timer' ||
               $_GET['type'] == 'bingo_book' ||
               $_GET['type'] == 'users_missions'
            ) {
                $this->show_user_data( $_GET['type'] );
            } elseif ($_GET['type'] == 'battleHistory') {
                //  Control user
                $this->showBattleHistory();

            } elseif ($_GET['type'] == 'missionHistory') {
                //  Control user
                $this->showMissionHistory();

            } elseif ($_GET['type'] == 'pageHistory') {
                //  Control user
                $this->showPageHistory();

            } elseif ($_GET['type'] == 'inv') {
                //      Edit inventory
                $this->show_inventory();
            } elseif ($_GET['type'] == 'jut') {
                //      Show jutsu    
                $this->show_jutsu();
            } elseif ($_GET['type'] == 'delstatus') {
                //      Deletion Timer
                if (!isset($_POST['Submit'])) {
                    $this->del_status_form();
                } else {
                    $this->remove_del_status();
                }
            } elseif ($_GET['type'] == 'tradelog') {
                $this->tradelog();
            } elseif ($_GET['type'] == 'replog') {
                $this->replog();
            } elseif ($_GET['type'] == 'ryolog') {
                $this->ryolog();
            } elseif ($_GET['type'] == 'bloodlineRolls') {
                $this->bloodRolls();
            }
        } elseif ($_GET['act'] == 'editsearch') {
            if (!isset($_POST['Submit'])) {
                $this->search_edits_form();
            } else {
                $this->search_edits_results();
            }
        }
    }

    //	Main page
    private function main_page() {

        // Show form
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `changes` LIKE 'User %' ORDER BY `time` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'log', 'User admin', $edits, array(
            'aid' => "Admin Name",
            'uid' => "Username",
            'time' => "Time",
            'IP' => "IP Used",
            'changes' => "Changes"
                ), false, true, true, array(
            array("name" => "Latest User Modifications", "href" => "?id=" . $_GET["id"] . "&act=edits"),
            array("name" => "Search User Modifications", "href" => "?id=" . $_GET["id"] . "&act=editsearch"),
            array("name" => "Search userlist", "href" => "?id=" . $_GET["id"] . "&act=search")
                )
        );
    }

    //    Search
    private function search_form() {
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/content_users/search.tpl');
    }

    private function search_results() {
        $query = "SELECT `users_timer`.*,`username`,`id`,`mail`,`last_ip` FROM `users_timer`,`users` WHERE ";
        if (isset($_POST['username']) && $_POST['username'] != '') {
            $query .= " `username` LIKE '" . $_POST['username'] . "'";
        }
        if (isset($_POST['email']) && $_POST['email'] != '') {
            if ($_POST['username'] != '') {
                $query .= " AND ";
            }
            $query .= " `mail` LIKE '" . $_POST['email'] . "'";
        }
        if (isset($_POST['userid']) && $_POST['userid'] != '') {
            if ($_POST['username'] != '' || $_POST['email'] != '') {
                $query .= " AND ";
            }
            $query .= " `id` = " . $_POST['userid'] . "";
        }
        if (isset($_POST['last_ip']) && $_POST['last_ip'] != '') {
            if ($_POST['username'] != '' || $_POST['email'] != '' || $_POST['userid'] != '') {
                $query .= " AND ";
            }
            $query .= " `last_ip` = '" . $_POST['last_ip'] . "'";
        }
        if (isset($_GET['uid']) && $_GET['uid'] != '') {
            if ($_POST['username'] != '' || $_POST['email'] != '') {
                $query .= " AND ";
            }
            $query .= " `id` = " . $_GET['uid'] . "";
        }

        $query .= " AND `users`.`id` = `users_timer`.`userid` ORDER BY `join_date` ASC";

        // Show form
        $min = tableParser::get_page_min();
        $results = $GLOBALS['database']->fetch_data($query);
        tableParser::show_list(
                'users', 'Search results', $results, array(
            'id' => "ID",
            'username' => "Username",
            'last_ip' => "Last IP",
            'last_activity' => "Time of Activity"
                ), array(
            array("name" => "Show Info", "act" => "mod", "uid" => "table.id")
                ), true, // Send directly to contentLoad
                true, false
        );
    }

    
    //	User Main Screen 
    private function edit_main() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
        if ($data != '0 rows') {
            $menu = array(
                array("name" => "Users-table", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users"),
                array("name" => "Users_preferences", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_preferences"),
                array("name" => "Users_missions", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_missions"),
                array("name" => "Users_statistics", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_statistics"),
                array("name" => "Users_loyalty", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_loyalty"),
                array("name" => "Users_occupations", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_occupations"),
                array("name" => "Users_timer", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_timer"),
                array("name" => "Users bingo book", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=bingo_book"),
                array("name" => "See inventory", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=inv"),
                array("name" => "See jutsu", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=jut"),
                array("name" => "See Battle History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=battleHistory"),
                array("name" => "See Mission History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=missionHistory"),
                array("name" => "See Page History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=pageHistory"),
                array("name" => "Deletion Timer", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=delstatus"),
                array("name" => "Trade Log", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=tradelog"),
                array("name" => "Ryo Log", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=ryolog"),
                array("name" => "Bloodline Rolls", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=bloodlineRolls")
            );
            
            $GLOBALS['template']->assign('subHeader', 'User admin for user: ' . $data[0]['username']);
            $GLOBALS['template']->assign('nCols', 4);
            $GLOBALS['template']->assign('nRows', 6);
            $GLOBALS['template']->assign('subTitle', '');
            $GLOBALS['template']->assign('linkMenu', $menu);
            $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id']);
        }
    }
    
    // Show all data in a given user table
    private function show_user_data( $userTable ){
        
        // Check that user ID is set
        if (isset($_GET['uid'])) {

            // Check that it's a supported table
            if(in_array($userTable, array( "users", "users_preferences", "users_missions", "users_statistics", "users_loyalty", "users_occupations", "users_timer", "bingo_book" )) ){

                // Column to select on
                $col = "uid";
                if( $userTable == "users" ){
                    $col = "id";
                }
                elseif( $userTable == "users_occupations" || $userTable == "users_timer"|| $userTable == "users_missions"){
                    $col = "userid";
                }
                elseif( $userTable == "bingo_book"){
                    $col = "userID";
                }

                $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `".$userTable."` WHERE `".$col."` = '".$_GET['uid']."' LIMIT 1");
                if( $edits !== "0 rows" ){
                    
                    // Create columns array
                    $columns = array();
                    foreach( $edits[0] as $key => $value ){
                        if( !in_array( $key , array( "salted_password", "password", "login_id", "tasks" )) ){
                            $columns[ $key ] = $key;
                        }
                    }
                    
                    tableParser::show_list(
                        'data', 'User data', $edits, 
                        $columns, false, true, true, false
                    );
                    
                    $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
                }
                else{
                    $GLOBALS['page']->Message("Could not find user data in database", 'User System', 'id=' . $_GET['id']);
                }
            }
            else{
                $GLOBALS['page']->Message("No valid table specified", 'User System', 'id=' . $_GET['id']);
            }
        }
        else{
            $GLOBALS['page']->Message("No valid user specified", 'User System', 'id=' . $_GET['id']);
        }
    }
    
    // Show bloodline rolls
    private function bloodRolls() {

        // Get Data
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodline_rolls` WHERE `uid` = '" . $_GET['uid'] . "' ");


        // SHow form
        tableParser::show_list(
                'log', 'Bloodline Rolls', $edits, array(
            'time' => "Time",
            'bloodlineName' => "Bloodline",
            'bloodRank' => "Rank"
                ), false, true, false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Battle History
    private function showBattleHistory(){
        
        // Get the log
        $combatLog = cachefunctions::getCombatLog($_GET['uid']);
        if( !empty($combatLog) ){
            $i = 1;
            foreach( $combatLog as $key => $logEntry ){
                
                // Set ID
                $combatLog[ $key ]['id'] = $i;
                
                // Set status text
                switch( $combatLog[ $key ][2] ){
                    case "wins": $combatLog[ $key ][2] = "Won"; break;
                    case "losses": $combatLog[ $key ][2] = "Lost"; break;
                    case "draws": $combatLog[ $key ][2] = "Draw"; break;
                }
                
                // Capitalize type
                $combatLog[ $key ][0] = ucfirst($combatLog[ $key ][0]);
                
                $i++;
            }
        }
        else{
            $combatLog = "0 rows";
        }
        
        // Show the table of users
        tableParser::show_list(
             'users', 
             'Battle History', 
            $combatLog, 
                array(
            'id' => "Battle ID",
            '0' => "Battle Type",
            '3' => "Opponent Name",
            '2' => "Battle Status"
                ),
                false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            "Is cleared after 12 hours inactivity"
        );
        
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }

    // Mission History
    private function showMissionHistory(){
        
        // Get the log
        $missionLog = json_decode( cachefunctions::getMissionLog( $_GET['uid'] ), true );
        if( empty($missionLog) ){
            $missionLog = "0 rows";
        }

        // Show the table of users
        tableParser::show_list(
             'users', 
             'Mission History', 
            $missionLog, 
                array(
            '0' => "Type",
            '1' => "Rank",
            '2' => "Name"
                ),
                false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            "Cleared every 12 hours if it hasn't been updated. "
        );
        
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Mission History
    private function showPageHistory(){
        
        // Get the log
        $pageLog = json_decode( cachefunctions::getUserPages( $_GET['uid'] ) , true);
             
        $data = array();
        if( empty($pageLog) ){
            $data = "0 rows";
        }
        else{
            foreach( $pageLog as $key => $value ){
                $data[] = array("page"=>$key);
            }
        }

        // Show the table of users
        tableParser::show_list(
             'pages', 
             'Page History', 
            $data, 
                array(
            'page' => "Page"
                ),
                false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            "Cleared every hour if it hasn't been updated. "
        );
        
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
        
    }

    // Inventory Control    
    private function show_inventory() {

        // Show form
        $min = tableParser::get_page_min();
        $user_items = $GLOBALS['database']->fetch_data("
                SELECT `users_inventory`.*, `items`.`name`, 'users' as `location`
                FROM `users_inventory`,`items` 
                WHERE 
                  `uid` = '" . $_GET['uid'] . "' AND 
                  `users_inventory`.`iid` = `items`.`id` AND
                  `durabilityPoints` > 0
                ORDER BY `timekey` ASC");

        $home_items = $GLOBALS['database']->fetch_data("
                SELECT `home_inventory`.*, `items`.`name`, 'home' as `location` 
                FROM `home_inventory`,`items` 
                WHERE 
                  `uid` = '" . $_GET['uid'] . "' AND 
                  `home_inventory`.`iid` = `items`.`id` AND
                  `durabilityPoints` > 0
                ORDER BY `timekey` ASC");

        tableParser::show_list(
                'items', 'User Item Menu', array_merge($user_items, $home_items), array(
            'location' => 'location',
            'id' => "Inventory ID",
            'iid' => "Item ID",
            'name' => "Item Name",
            'equipped' => "Equipped",
            'stack' => "Stack Size",
            'durabilityPoints' => "Durability"
                ), false, true, false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // User Jutsu
    private function show_jutsu() {

        // Show form
        $jutsu = $GLOBALS['database']->fetch_data("SELECT `users_jutsu`.*, `jutsu`.`name` FROM `users_jutsu`,`jutsu` WHERE `uid` = '" . $_GET['uid'] . "' AND `users_jutsu`.`jid` = `jutsu`.`id` ORDER BY `name` DESC");
        tableParser::show_list(
                'items', 'User Jutsu Menu', $jutsu, array(
            'jid' => "Jutsu ID",
            'name' => "Jutsu Name",
            'level' => "Level",
            'tagged' => "Tagged"
                ), false, true, false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // User Deletion Time
    private function del_status_form() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $fed_data = $GLOBALS['database']->fetch_data("SELECT `deletion_timer`, `username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "'");
            if ($fed_data[0]['deletion_timer'] == 0) {
                $GLOBALS['page']->Message("This user is not scheduled to be deleted", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            } else {
                $GLOBALS['page']->Confirm("User is set to be deleted at " . date('G:i:s d-m-Y', $fed_data[0]['deletion_timer'] + 604800) . ". Are you sure you wish to stop the timer?", 'User System', 'Remove Timer');
            }
        } else {
            $GLOBALS['page']->Message("No valid user ID specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function remove_del_status() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            if ($_POST['Submit'] == 'Remove Timer') {
                $GLOBALS['database']->execute_query("UPDATE `users` SET `deletion_timer` = 0 WHERE id = '" . $_GET['uid'] . "'");
                $GLOBALS['page']->Message("Deletion timer was reset", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("Data could not be retrieved (does this user exist?)", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }


    // Trade Log
    private function printRtoArray($printRoutput) {
        $printRoutput = str_replace("&nbsp;", "", $printRoutput);
        $pos = strpos($printRoutput, "Array");
        if ($pos === false) {
            $conArray = $printRoutput;
        } else {
            $conArray = array();
            $regs = array();
            $explodes = explode("\n", $printRoutput);
            foreach ($explodes as $explode) {
                if (preg_match("/([^\)]*)=>([^\)]*)/", $explode, $regs)) {
                    $regs[1] = str_replace("[", "", $regs[1]);
                    $regs[1] = str_replace("]", "", $regs[1]);
                    $conArray['' . $regs[1] . ''] = $regs[2];
                }
            }
        }
        return $conArray;
    }

    private function translateItemName($field, $name, $id) {
        if (ctype_digit($name)) {
            $item = $GLOBALS['database']->fetch_data("SELECT `name` FROM `items` WHERE `id` = '" . $name . "' LIMIT 1");
            if ($item !== "0 rows") {
                $name = $item[0]['name'];
                $GLOBALS['database']->execute_query("UPDATE `trade_log` SET `" . $field . "` = '" . addslashes($name) . "' WHERE `tradeid` = '" . $id . "' LIMIT 1");
            }
        }
        return $name;
    }

    private function translateUserName($field, $name, $id, $userID) {
        if ($name == "") {
            $item = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $userID . "' LIMIT 1");
            if ($item !== "0 rows") {
                $name = $item[0]['username'];
                $GLOBALS['database']->execute_query("UPDATE `trade_log` SET `" . $field . "` = '" . addslashes($name) . "' WHERE `tradeid` = '" . $id . "' LIMIT 1");
            }
        }
        return $name;
    }

    private function tradelog() {

        // Get Data
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `trade_log` WHERE `user1` = '" . $_GET['uid'] . "' OR `user2` = '" . $_GET['uid'] . "' ORDER BY `tradeid` DESC LIMIT " . $min . ",10");

        // Manipulate data
        if ($edits !== "0 rows") {
            $i = 0;
            while ($i < count($edits)) {
                $edits[$i]['items1'] = str_replace(" ", "&nbsp;", $edits[$i]['items1']);
                $edits[$i]['items2'] = str_replace(" ", "&nbsp;", $edits[$i]['items2']);

                $item1array = $this->printRtoArray($edits[$i]['items1']);
                $item2array = $this->printRtoArray($edits[$i]['items2']);
                $item1name = is_array($item1array) ? $item1array['iid'] : $item1array;
                $item2name = is_array($item2array) ? $item2array['iid'] : $item2array;

                $item1name = isset($edits[$i]['item1name']) && $edits[$i]['item1name'] != "" ? $edits[$i]['item1name'] : $item1name;
                $item2name = isset($edits[$i]['item2name']) && $edits[$i]['item2name'] != "" ? $edits[$i]['item2name'] : $item2name;

                $edits[$i]['item1time'] = is_array($item1array) ? date('d-m-Y', $item1array['timekey']) : "N/A";
                $edits[$i]['item2time'] = is_array($item2array) ? date('d-m-Y', $item2array['timekey']) : "N/A";

                $edits[$i]['item1name'] = $this->translateItemName("item1name", $item1name, $edits[$i]['tradeid']);
                $edits[$i]['item2name'] = $this->translateItemName("item2name", $item2name, $edits[$i]['tradeid']);

                $edits[$i]['user1name'] = $this->translateUserName("username1", $edits[$i]['username1'], $edits[$i]['tradeid'], $edits[$i]['user1']);
                $edits[$i]['user2name'] = $this->translateUserName("username2", $edits[$i]['username2'], $edits[$i]['tradeid'], $edits[$i]['user2']);

                $i++;
            }
        }

        // SHow form
        tableParser::show_list(
                'log', 'Latest Trades of User', $edits, array(
            'user1name' => "Trader",
            'user2name' => "Offer User",
            'item1name' => "Trader Items",
            'time' => "Creation Time",
            'item2name' => "Offer",
                ), false, true, true, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }

    // Ryo Log
    private function ryolog() {

        // Get the user
        if (is_numeric($_GET['uid'])) {
            $user_in_question = $GLOBALS['database']->fetch_data("SELECT `users`.`username` 
                FROM `users` 
                WHERE `users`.`id` = '" . $_GET['uid'] . "' LIMIT 1");
            $id = $_GET['uid'];
        } 
        else {
            $user_in_question = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username` 
                FROM `users` 
                WHERE `users`.`username` = '" . $_GET['uid'] . "' LIMIT 1");
            $id = $user_in_question[0]['id'];
        }

        // Use the table parser library to show notes in system
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` 
            WHERE `ryo_track`.`uid` = '" . $id . "'  ORDER BY `ryo_track`.`time` DESC LIMIT 100");
        
        tableParser::show_list(
            'sendingsFrom', 
            'Latest Sending from ' . $user_in_question[0]['username'] . '', 
            $edits, 
            array(
                'time' => "Time",
                'receiver' => "Ryo Sent To",
                'amount' => "Ryo Amount"
            ), false, false, false
        );

        $edits1 = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` 
            WHERE `ryo_track`.`receiver` = '" . $user_in_question[0]['username'] . "'  
                ORDER BY `ryo_track`.`time` DESC LIMIT 100");
        
        tableParser::show_list(
            'sendingsTo', 
            'Latest Sending to ' . $user_in_question[0]['username'] . '', 
            $edits1, 
            array(
                'time' => "Time",
                'sender' => "Ryo Sent By",
                'amount' => "Ryo Amount"
            ), false, false, false
        );

        // Load template
        $GLOBALS['template']->assign('username', $user_in_question[0]['username']);
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/ryoLog.tpl');
    }

    // Search Modifications
    private function search_edits_form() {
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/searchModifications.tpl');
    }

    private function search_edits_results() {

        //      GENERATE QUERY
        if (isset($_POST['adminname']) && $_POST['adminname'] != '') {
            $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `aid` = '" . $_POST['adminname'] . "' ORDER BY `time` DESC LIMIT 200");
        } elseif (isset($_POST['username'])) {
            if (!is_numeric($_POST['username']) && $_POST['username'] != 'MULTIPLE') {
                $uidt = $GLOBALS['database']->fetch_data("SELECT `id` FROM `users` WHERE `username` = '" . $_POST['username'] . "' LIMIT 1");
                if ($uidt != '0 rows') {
                    $uid = $uidt[0]['id'];
                } else {
                    $edits = 'noquery';
                    $editserror = 'this user does not exist';
                }
            } else {
                $uid = $_POST['username'];
            }
            if (!isset($editserror)) {
                $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `uid` = '" . $uid . "' ORDER BY `time` DESC LIMIT 200");
            }
        } else {
            $edits = '0 rows';
        }

        // Show form
        tableParser::show_list(
                'log', 'User Modifications', $edits, array(
            'aid' => "Admin Name",
            'uid' => "Username",
            'time' => "Time",
            'IP' => "IP Used",
            'changes' => "Changes"
                ), false, true, true, array(
            array("name" => "Latest User Modifications", "href" => "?id=" . $_GET["id"] . "&act=edits"),
            array("name" => "Search User Modifications", "href" => "?id=" . $_GET["id"] . "&act=editsearch"),
            array("name" => "Search userlist", "href" => "?id=" . $_GET["id"] . "&act=search")
                )
        );
    }

}

$users = new users();
?>