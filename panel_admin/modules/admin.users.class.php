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
        
        try{
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
            } elseif ($_GET['act'] == 'del') {
                //  Delete user
                if (!isset($_POST['Submit'])) {
                    $this->verify_delete();
                } else {
                    $this->do_delete();
                }
            } elseif ($_GET['act'] == 'enter') {
                //  Control user
                if (!isset($_POST['Submit'])) {
                    $this->verify_enter();
                } else {
                    $this->do_enter();
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
                   $_GET['type'] == 'bingo_book'
                ) {
                    //      Edit chardata
                    if (!isset($_POST['Submit'])) {
                        $this->edit_user_form();
                    } else {
                        $this->do_edit_user();
                    }
                } elseif ($_GET['type'] == 'logbook') {
                    $this->show_logbook();
                } elseif ($_GET['type'] == 'addLogEntry') {
                    if (!isset($_POST['Submit'])) {
                        $this->addLogEntryForm();
                    } else {
                        $this->doAddLogEntry();
                    }
                } elseif ($_GET['type'] == 'deleteLogEntry') {
                    if (!isset($_POST['Submit'])) {
                        $this->deleteLogEntryForm();
                    } else {
                        $this->doDeleteLogEntry();
                    }
                } elseif ($_GET['type'] == 'setActiveLogEntry' ||
                          $_GET['type'] == 'setCompleteLogEntry' ) {
                    if (!isset($_POST['Submit'])) {
                        $this->editLogEntryForm();
                    } else {
                        $this->doEditLogEntry();
                    }
                } elseif ($_GET['type'] == 'battleHistory') {
                    //  Control user
                    $this->showBattleHistory();

                } elseif ($_GET['type'] == 'missionHistory') {
                    //  Control user
                    $this->showMissionHistory();

                } elseif ($_GET['type'] == 'pageHistory') {
                    //  Control user
                    $this->showPageHistory();

                } elseif ($_GET['type'] == 'misc') {
                    //      Edit mission data
                    if (!isset($_POST['Submit'])) {
                        $this->edit_misc_form();
                    } else {
                        $this->do_edit_misc();
                    }
                } elseif ($_GET['type'] == 'inv') {
                    //      Edit inventory
                    $this->show_inventory();
                } elseif ($_GET['type'] == 'invadd') {
                    //      Insert new item   
                    if (!isset($_POST['Submit'])) {
                        $this->add_item_form();
                    } else {
                        $this->do_item_add();
                    }
                } elseif ($_GET['type'] == 'invdel') {
                    //      Remove item from inventory
                    if (!isset($_POST['Submit'])) {
                        $this->confirm_item_delete();
                    } else {
                        $this->do_item_delete();
                    }
                } elseif ($_GET['type'] == 'invedit') {
                    //      Remove item from inventory
                    if (!isset($_POST['Submit'])) {
                        $this->edit_item_form();
                    } else {
                        $this->do_edit_item();
                    }
                } elseif ($_GET['type'] == 'jut') {
                    //      Show jutsu    
                    $this->show_jutsu();
                } elseif ($_GET['type'] == 'jutadd') {
                    //      Insert new jutsu   
                    if (!isset($_POST['Submit'])) {
                        $this->add_jutsu_form();
                    } else {
                        $this->do_jutsu_add();
                    }
                } elseif ($_GET['type'] == 'jutdel') {
                    //      Remove jutsu    
                    if (!isset($_POST['Submit'])) {
                        $this->confirm_jutsu_delete();
                    } else {
                        $this->do_jutsu_delete();
                    }
                } elseif ($_GET['type'] == 'jutedit') {
                    //      Remove item from inventory
                    if (!isset($_POST['Submit'])) {
                        $this->edit_jutsu_form();
                    } else {
                        $this->do_edit_jutsu();
                    }
                } elseif ($_GET['type'] == 'fedstatus') {
                    //      Fed status
                    if (!isset($_POST['Submit'])) {
                        $this->fed_status_form();
                    } else {
                        $this->update_fed_status();
                    }
                } elseif ($_GET['type'] == 'delstatus') {
                    //      Deletion Timer
                    if (!isset($_POST['Submit'])) {
                        $this->del_status_form();
                    } else {
                        $this->remove_del_status();
                    }
                } elseif ($_GET['type'] == 'ban') {
                    //      Ban Options
                    echo 'Code is Obsolete! Use the Moderator Panel version!';
                } elseif ($_GET['type'] == 'assignment') {
                    //      Edit chardata
                    if (!isset($_POST['Submit'])) {
                        $this->edit_assignment_form();
                    } else {
                        $this->do_edit_assignment();
                    }
                } elseif ($_GET['type'] == 'alterstat') {
                    //      Edit chardata
                    if (!isset($_POST['Submit'])) {
                        $this->alter_stat_form();
                    } else {
                        $this->do_alter_stat();
                    }
                } elseif ($_GET['type'] == 'tradelog') {
                    $this->tradelog();
                } elseif ($_GET['type'] == 'replog') {
                    $this->replog( "r_uid" );
                } elseif ($_GET['type'] == 'sentreplog') {
                    $this->replog( "s_uid" );
                } elseif ($_GET['type'] == 'withdrawals') {
                    $this->withdrawals();
                } elseif ($_GET['type'] == 'repCheck') {
                    $this->repCheck();
                } elseif ($_GET['type'] == 'blackmarketlog') {
                    $this->blackmarketlog();
                }  elseif ($_GET['type'] == 'limitedSpecialSurprises') {
                    $this->limitedSpecialSurprises();
                }elseif ($_GET['type'] == 'ninjaFarmerData') {
                    $this->ninjaFarmerLog();
                } elseif ($_GET['type'] == 'villageChangeLog') {
                    $this->villageChangeLog();
                } elseif ($_GET['type'] == 'ryolog') {
                    $this->ryolog();
                } elseif ($_GET['type'] == 'bloodlineRolls') {
                    $this->bloodRolls();
                } elseif ($_GET['type'] == 'elementRolls') {
                    $this->elementRolls();
                }
            } elseif ($_GET['act'] == 'editsearch') {
                if (!isset($_POST['Submit'])) {
                    $this->search_edits_form();
                } else {
                    $this->search_edits_results();
                }
            }
        } catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
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
            array("name" => "Edit", "act" => "mod", "uid" => "table.id"),
            array("name" => "Control", "act" => "enter", "uid" => "table.id"),
            array("name" => "Delete", "act" => "del", "uid" => "table.id")
                ), true, // Send directly to contentLoad
                true, false
        );
    }

    //	Remove user(s)
    private function verify_delete() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $GLOBALS['page']->Confirm("Delete the user: " . $data[0]['username'] . "?", 'User System', 'Delete now!');
            } else {
                $GLOBALS['page']->Message("User could not be found.", 'User System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid User ID.", 'User System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete() {
        $user = $GLOBALS['database']->fetch_data("SELECT * FROM `users`, `users_statistics`  WHERE `id` = '" . $_GET['uid'] . "' AND `users`.`id` = `users_statistics`.`uid` LIMIT 1");
        if ($user != '0 rows') {
            if ($user[0]['user_rank'] != 'Admin') {
                
                // Obtain User Manager for Purging Accounts
                require_once(Data::$absSvrPath.'/clusterup/modules/userManage.class.php');
                $acctManager = new userManage;

                // Purge Listed UIDs and/or Usernames
                $acctManager->purge_accounts('"'.$user[0]['id'].'"', '"'.$user[0]['username'].'"');

                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`) VALUES ('" 
                    . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" 
                    . $user[0]['username'] . "', 'User deleted', '" . $GLOBALS['user']->real_ip_address() . "');");

                $GLOBALS['page']->Message("User has been deleted.", 'User System', 'id=' . $_GET['id']);
            
            } else {
                $GLOBALS['page']->Message("You cannot remove admin accounts.", 'User System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("The userid submitted is not valid.", 'User System', 'id=' . $_GET['id']);
        }
    }
    
    
    //	Control user(s)
    private function verify_enter() {
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/content_users/hijackVerify.tpl');
    }

    private function do_enter() {
        $user = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
        if ($user != '0 rows') {
            if (isset($_POST['reason']) && $_POST['reason'] !== "") {

                // Log Changes
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` 
                (`time` ,`aid` ,`uid` ,`changes`,`IP`)
                VALUES (
                    '" . time() . "', 
                    '" . $GLOBALS['userdata'][0]['username'] . "', 
                    '" . $user[0]['username'] . "', 
                     'User controlled with following reason:<br> " . $_POST['reason'] . "', 
                     '" . $GLOBALS['user']->real_ip_address() . "'
                );");

                // Update session stuff
                session_regenerate_id();

                // Allow for easy go-back
                $hash = hash("sha512", "secretTadaaa" . $_SESSION['uid']);
                $_SESSION['backData'] = array($_SESSION['uid'], $hash);

                // Set session
                $_SESSION['uid'] = $user[0]['id'];
                $_SESSION['override'] = true;

                // Update user table
                $loginID = session_id() . md5($user[0]['username'] . "xXx");
                $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_login` = '" . time() . "'      WHERE `userid` = '" . $user[0]['id'] . "' LIMIT 1");
                $GLOBALS['database']->execute_query("UPDATE `users` SET `logout_timer` = '" . (time() + 7200) . "', `login_id` = '".$loginID."' WHERE `id` = '" . $user[0]['id'] . "' LIMIT 1");

                // Use message
                $GLOBALS['page']->Message("You have taken control of the character.", 'Hijack System');
            } else {
                $GLOBALS['page']->Message("No reason for controlling this user was supplied.", 'Hijack System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("The userid submitted is not valid.", 'Hijack System', 'id=' . $_GET['id']);
        }
    }

    //	User Main Screen 
    private function edit_main() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
        if ($data != '0 rows') {
            $menu = array(
                array("name" => "Edit: users-table", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users"),
                array("name" => "Edit: users_preferences", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_preferences"),
                array("name" => "Edit: users_statistics", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_statistics"),
                array("name" => "Edit: users_loyalty", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_loyalty"),
                array("name" => "Edit: users_occupations", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_occupations"),
                array("name" => "Edit: users_timer", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=users_timer"),
                array("name" => "Edit: users bingo book", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=bingo_book"),
                array("name" => "Edit: users log book", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=logbook"),
                array("name" => "Edit: users mission data", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=misc"),
                array("name" => "See inventory", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=inv"),
                array("name" => "See jutsu", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=jut"),
                array("name" => "See Battle History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=battleHistory"),
                array("name" => "See Mission History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=missionHistory"),
                array("name" => "See Page History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=pageHistory"),
                array("name" => "Fed support status", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=fedstatus"),
                array("name" => "Deletion Timer", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=delstatus"),
                array("name" => "Ban options", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=ban"),
                array("name" => "Achievements", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=assignment"),
                array("name" => "Stat Reduction", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=alterstat"),
                array("name" => "Log: Trades", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=tradelog"),
                array("name" => "Log: Ryo Transfers", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=ryolog"),
                array("name" => "Log: BlackMarket", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=blackmarketlog"),
                array("name" => "Log: Limited Special Surprises", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=limitedSpecialSurprises"),
                array("name" => "Log: Bloodline Rolls", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=bloodlineRolls"),
                array("name" => "Log: Element Rolls", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=elementRolls"),
                array("name" => "Log: Village Change", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=villageChangeLog"),
                array("name" => "Ninja Farmer Data", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=ninjaFarmerData"),
                array("name" => "Log: PayPal", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=replog"),
                array("name" => "Log: Sent Paypal Payments", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=sentreplog"),
                array("name" => "Log: Withdrawn Paypal Payments", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=withdrawals"),
                array("name" => "Check Reputation Points", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=repCheck")
            );
            
            $GLOBALS['template']->assign('subHeader', 'User admin for user: ' . $data[0]['username']);
            $GLOBALS['template']->assign('nCols', 4);
            $GLOBALS['template']->assign('nRows', 8);
            $GLOBALS['template']->assign('subTitle', '');
            $GLOBALS['template']->assign('linkMenu', $menu);
            $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id']);
        }
    }
    
    // Show the logbook
    private function show_logbook(){
        
        // Get the task library and instantiate
        require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
        $this->taskLibrary = new tasks;  
        
        // Get tasks of the user
        $userTasks = cachefunctions::getUserTasks( $_GET['uid'] , true );
        $userTasks = json_decode($userTasks[0]['tasks'], true);
        
        // Get all entries
        $allEntries = cachefunctions::getTasksQuestsMissions( true ); 
        
        // Decide on which filter to use
        $filter = "orders";
        if( isset($_GET['filter']) && preg_match( "/(active|completed|quests|orders|special)/i", $_GET['filter'] ) ){
            $filter = $_GET['filter'];
        }
        
        // Set all entries
        $min =  tableParser::get_page_min();
        $allEntries = $this->taskLibrary->filterEntries( $allEntries, $userTasks, $filter , $min );
        
        // Columns to show
        $columns = array(
            'id' => "ID", 
            'name' => "Name", 
            'type' => "Entry Type",
            'status' => "Status", 
            'levelReq' => "Lvl Req", 
            'levelMax' => "Lvl Max"
        );
        
        // Show form
        tableParser::show_list(
            'entries',
            'LogBook: '.  ucfirst($filter), 
            $allEntries,
            $columns,
            array(
                array("name" => "Set Active", "act" => "mod", "uid" => $_GET['uid'], "type" => "setActiveLogEntry", "lid" => "table.id"),
                array("name" => "Set Complete", "act" => "mod", "uid" => $_GET['uid'], "type" => "setCompleteLogEntry", "lid" => "table.id"),
                array("name" => "Delete", "act" => "mod", "uid" => $_GET['uid'], "type" => "deleteLogEntry", "lid" => "table.id")                
            ),
            true,
            false,
            array(
                array("name" => "Orders", "href" =>"?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=logbook&filter=orders"),
                array("name" => "Active", "href" =>"?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=logbook&filter=active"),
                array("name" => "Completed", "href" =>"?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=logbook&filter=completed"),
                array("name" => "Quests", "href" =>"?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=logbook&filter=quests"),
                array("name" => "Special", "href" =>"?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=logbook&filter=special"),
                array("name" => "New Entry", "href" =>"?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=addLogEntry")
            ),
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            "This is the log book for user ID: ".$_GET['uid']
        );  
    }

    // Add a logbook entry to this user
    private function addLogEntryForm(){
        
        // Get all entries
        $allEntries = cachefunctions::getTasksQuestsMissions(true); 
        
        // Create user input
        $selectArray = array();
        if( $allEntries !== "0 rows" ){
            foreach( $allEntries as $entry ){
                $selectArray[ $entry['id'] ] = $entry['name'];
            }
        }
        
        // Sort the quests
        asort($selectArray);
        
        // Create the input form
        $GLOBALS['page']->UserInput( 
                "Chose which task/quest to add to this user, and its status (active / completed)", 
                "Training System", 
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"lid",
                        "type"=>"select",
                        "inputFieldValue"=> $selectArray
                    ),
                    // A select box
                    array(
                        "inputFieldName"=>"lstat",
                        "type"=>"select",
                        "inputFieldValue"=> array( "a" => "Active", "c" => "Completed" )
                    )
                ), 
                array(
                    "href"=>"?id=".$_REQUEST['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=addLogEntry" ,
                    "submitFieldName"=>"Submit", 
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "addLogentryForm"
        ); 
    }
    
    private function doAddLogEntry(){

        // Get library
        require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
        $this->taskLibrary = new tasks;  
        
        // Get user tasks
        $userTasks = cachefunctions::getUserTasks( $_GET['uid'] );
        $userTasks = json_decode($userTasks[0]['tasks'], true);
        
        // Check if it's possible to delete this (can't delete tasks perpetually available to user)
        if(!array_key_exists($_POST['lid'], $userTasks) ){
            
            // New status
            $newStatus = ($_POST['lstat'] == 'a') ? "a" : "c";
            $userTasks[ $_POST['lid'] ] = $newStatus;
            
            // Save
            $this->taskLibrary->updateUserTasks( $_GET['uid'], $userTasks );
            
            // Message
            $GLOBALS['page']->Message("Added log entry", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']."&type=logbook");
            
        }
        else{
            throw new Exception("Cannot add this entry, it's already available to the user.");
        }
    }
    
    // Delete a logbook entry from this user
    private function deleteLogEntryForm(){
        $GLOBALS['page']->Confirm("Are you sure you wish to delete this entry from the user' logbook?", 'User System', 'Delete now!');
    }
    
    private function doDeleteLogEntry(){
        
        // Get user tasks
        $userTasks = cachefunctions::getUserTasks( $_GET['uid'] );
        $userTasks = json_decode($userTasks[0]['tasks'], true);
        
        // Check if it's possible to delete this (can't delete tasks perpetually available to user)
        if(array_key_exists($_GET['lid'], $userTasks) ){
            
            // Delete entry
            unset($userTasks[$_GET['lid']]);
            
            // Get library
            require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
            $this->taskLibrary = new tasks;  
            
            // Save
            $this->taskLibrary->updateUserTasks( $_GET['uid'], $userTasks );
            
            // Message
            $GLOBALS['page']->Message("Updated log entry", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']."&type=logbook");
            
        }
        else{
            throw new Exception("Cannot delete this task, it's perpetual for the user and would just pop back up immidiately in his/her log, since he/she fullfills the requirements for it.");
        }
    }
    
    // Edit a logbook entry from this user
    private function editLogEntryForm(){
        $newStatus = ($_GET['type'] == 'setActiveLogEntry') ? "Active" : "Complete";
        $GLOBALS['page']->Confirm("Are you sure you wish to set this entry to: ".$newStatus, 'User System', 'Perform now!');
    }
    
    private function doEditLogEntry(){
        
        // Get library
        require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
        $this->taskLibrary = new tasks;  
        
        // the new status
        $newStatus = ($_GET['type'] == 'setActiveLogEntry') ? "a" : "c";
        
        // Get all entries
        $allEntries = cachefunctions::getTasksQuestsMissions(true); 
        
        // Get user tasks
        $userTasks = cachefunctions::getUserTasks( $_GET['uid'] );
        $userTasks = json_decode($userTasks[0]['tasks'], true);
        
        // Check that the ID exists in all entries
        $pass = false;
        foreach( $allEntries as $entry ){
            if( $entry['id'] == $_GET['lid'] ){
                $pass = $entry;
                break;
            }
        }
        if( $pass == false ){
            throw new Exception("This entry does no longer exist. Weird error, should not occur, please report to coder.");
        }
        
        // Update the mission log
        if( stristr( $entry['type'], "mission" ) || stristr( $entry['type'], "crime" ) ){
            
            // Get data and set mission log
            $temp = explode("_",$entry['type']);
            $this->pageType = $temp[0]; 
            $this->pageRank = strtoupper($temp[1]);
            
            // Save in mission history        
            cachefunctions::updateMissionLog( $_GET['uid'] , $this->pageType, $this->pageRank, $entry['name'] );

        }
        
        // Check if entry already exists, if not then create it
        if(!array_key_exists($_GET['lid'], $userTasks) ){
            $userTasks[ $_GET['lid'] ] = "a";
        }
        
        // Set the status
        $userTasks[ $_GET['lid'] ] = $newStatus;
        
        // Save it
        $this->taskLibrary->updateUserTasks( $_GET['uid'], $userTasks );
        
        // Show message
        $GLOBALS['page']->Message("Updated log entry", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']."&type=logbook");
    }
    
    
    private function bloodRolls() {

        // Get Data
        $edits = $GLOBALS['database']->fetch_data("SELECT `bloodline_rolls`.*, `items`.`name` as 'item' FROM `bloodline_rolls` LEFT JOIN `items` on (`bloodline_rolls`.`iid` = `items`.`id`) WHERE `uid` = '" . $_GET['uid'] . "' ");


        // SHow form
        tableParser::show_list(
                'log', 'Bloodline Rolls', $edits, array(
            'time' => "Time",
            'bloodlineName' => "Bloodline",
            'bloodRank' => "Rank",
            'item' => 'item'
                ), false, true, false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }

    private function elementRolls() {

        // Get Data
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `element_rolls` WHERE `uid` = '" . $_GET['uid'] . "' ");


        // SHow form
        tableParser::show_list(
                'log', 'Element Rolls', $edits, array(
                    'time' => "Time",
                    'element' => "Element",
                    'category' => "Category"
                ), false, true, false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Battle History
    private function showBattleHistory(){
        
        // Clear the combat log
        if( isset($_GET['action']) && $_GET['action'] == "clear"){
            cachefunctions::deleteCombatLog( $_GET['uid'] );
        }
        
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
            array(
                array("name" => "Clear Battle History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=battleHistory&action=clear")
            ), // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            "Is cleared after 12 hours inactivity."
        );
        
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }

    // Mission History
    private function showMissionHistory(){
        
        // Clear the combat log
        if( isset($_GET['action']) && $_GET['action'] == "clear"){
            cachefunctions::deleteMissionLog( $_GET['uid'] );
        }
        
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
            array(
                array("name" => "Clear Mission History", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=missionHistory&action=clear")
            ), // No top options links
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

    // Edit User
    private function edit_user_form() {
        if (isset($_GET['uid'])) {
            
            $col = "uid";
            if( $_GET['type'] == "users" ){
                $col = "id";
            }
            elseif( $_GET['type'] == "users_occupations" || $_GET['type'] == "users_timer"){
                $col = "userid";
            }
            elseif( $_GET['type'] == "bingo_book"){
                $col = "userID";
            }
            
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `".$_GET['type']."` WHERE `".$col."` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                // Parse the user table
                tableParser::parse_form( $_GET['type'] , 'Edit userID: '.$_GET['uid'], array( $col , "salted_password"), $data);

                // Set the return link
                $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function do_edit_user() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            
            // Extra information for user
            $extra = "";
            
            // Default primary column
            $col = "uid";
            
            // Potentially change primary column
            if( $_GET['type'] == "users" ){
                
                // Users table uses 'id' in primary column
                $col = "id";
                
                // Get previous data
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                if( $data !== "0 rows" ){

                    // If user was changed from asleep -> awake, or the other way, alter regen appropriately
                    if( $data[0]['status'] == "asleep" && $_POST['status'] == "awake" ){
                        $extra = "<br>ASLEEP->AWAKE CHANGE: REMEMBER TO CHECK USERS REGEN. USE DATA INTEGRITY MODULE.";
                    }
                    elseif( $data[0]['status'] == "awake" && $_POST['status'] == "asleep" ){
                        $extra = "<br>AWAKE->ASLEEP CHANGE: REMEMBER TO CHECK USERS REGEN. USE DATA INTEGRITY MODULE.";
                    }
                }
            }
            elseif( $_GET['type'] == "users_occupations" || $_GET['type'] == "users_timer"){
                $col = "userid";
            }
            elseif( $_GET['type'] == "bingo_book"){
                $col = "userID";
            }
            $changed = tableParser::check_data($_GET['type'], $col, $_GET['uid'], array($col));
            if (tableParser::update_data($_GET['type'], $col, $_GET['uid'])) {
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'User stats updated:<br> " . $changed . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                $GLOBALS['page']->Message("The user has been updated.".$extra, 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the user", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
            
            // If user village was set, it needs to be syncronized in both loyalty & users
            if( in_array($changed, array('Shroud','Shine','Samui','Silence','Konoki')) && isset($_POST['village']) ){
                if( $_GET['type'] == "users" || $_GET['type'] == "users_loyalty" ){
                    $GLOBALS['database']->execute_query("
                        UPDATE `users`,`users_loyalty` 
                        SET `users`.`village` = '" . $_POST['village'] . "', 
                            `users_loyalty`.`village` = '" . $_POST['village'] . "'
                        WHERE 
                            `users`.`id` = `users_loyalty`.`uid` AND 
                            `users`.`id` = '" . $_GET['uid'] . "'"
                     );
                }
            }
            
            
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    // Mission Data
    private function edit_misc_form() {
        if (isset($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_missions` WHERE `userid` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                // Parse data
                tableParser::parse_form('users_missions', 'Edit user statistics', array('userid'), $data);

                // Set the return link
                $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function do_edit_misc() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $changed = tableParser::check_data('users_missions', 'userid', $_GET['uid']);
            if (tableParser::update_data('users_missions', 'userid', $_GET['uid'])) {
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'User misc statistics updated: " . $changed . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                $GLOBALS['page']->Message("The user has been updated and cache cleared", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            
                // Clear cache
                cachefunctions::deleteUserTasks( $_GET['uid'] );
                
            } else {
                $GLOBALS['page']->Message("An error occured while updating the user", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
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
            'finishProcessing' => "Processing",
            'durabilityPoints' => "Durability"
                ), array(
            array("name" => "Edit", "act" => "mod", "uid" => $_GET['uid'], "location" => "table.location", "type" => "invedit", "iid" => "table.id"),
            array("name" => "Remove", "act" => "mod", "uid" => $_GET['uid'], "location" => "table.location", "type" => "invdel", "iid" => "table.iid", "timekey" => "table.timekey")
                ), true, false, array(
            array("name" => "Add New Item to user", "href" => '?id=' . $_GET['id'] . '&type=invadd&location=users&act=mod&uid=' . $_GET['uid']),
            array("name" => "Add New Item to home", "href" => '?id=' . $_GET['id'] . '&type=invadd&location=home&act=mod&uid=' . $_GET['uid'])
                )
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Edit User item
    private function edit_item_form() {

        $table = $_GET['location'].'_inventory';

        if (isset($_GET['iid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `{$table}` WHERE `id` = '" . $_GET['iid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                // Parse data
                tableParser::parse_form($table, 'Edit user item', array('id','trading','trade_type','tempModifier'), $data);

                // Set the return link
                $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
            } else {
                $GLOBALS['page']->Message("This item does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No item was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function do_edit_item() {

        $table = $_GET['location'].'_inventory';

        if (isset($_GET['iid']) && is_numeric($_GET['iid'])) {
            $changed = tableParser::check_data($table, 'id', $_GET['iid']);
            if (tableParser::update_data($table, 'id', $_GET['iid'])) {
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'User item edited: " . $changed . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                $GLOBALS['page']->Message("The item has been updated", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            
            } else {
                $GLOBALS['page']->Message("An error occured while updating the item", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No item was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function confirm_item_delete() {
        $GLOBALS['page']->Confirm("Are you sure you wish to delete this item from the user's inventory", 'User System', 'Delete now!');
    }

    private function do_item_delete() {

        $table = $_GET['location'].'_inventory';
        
        
        if ($GLOBALS['database']->execute_query("DELETE FROM {$table} WHERE `uid` = '" . $_GET['uid'] . "' AND `iid` = '" . $_GET['iid'] . "' AND `timekey` = '" . $_GET['timekey'] . "' LIMIT 1")) {
            $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'Item deleted from inventory: " . $_GET['iid'] . "', '" . $GLOBALS['user']->real_ip_address() . "');");
            $GLOBALS['page']->Message("The item was removed from the user's inventory", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        } else {
            $GLOBALS['page']->Message("Item could not be removed from the user's inventory", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function add_item_form() {
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `items` ORDER BY `name` ASC");
        $GLOBALS['template']->assign('items', $items);
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/add_item.tpl');
    }

    private function do_item_add() {

        $table = $_GET['location'].'_inventory';
        
        
        if (isset($_POST['iid']) && is_numeric($_POST['iid'])) {
            $item = $GLOBALS['database']->fetch_data("SELECT `name`,`durability` FROM `items` WHERE `id` = '" . $_POST['iid'] . "' LIMIT 1");
            if ($item != '0 rows') {
                if ($GLOBALS['database']->execute_query("INSERT INTO {$table} 
                    ( `uid` , `iid` , `equipped` , `stack` , `timekey` , `durabilityPoints`) VALUES 
                    ('" . $_GET['uid'] . "', '" . $_POST['iid'] . "', 'no', '1', '" . time() . "', '".$item[0]['durability']."');")
                ) {
                    $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'User item added to inventory: " . addslashes($item[0]['name']) . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                    $GLOBALS['page']->Message('The ' . stripslashes($item[0]['name']) . ' has been added to the user\'s inventory', 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                } else {
                    $GLOBALS['page']->Message('The ' . stripslashes($item[0]['name']) . ' could not be added to the user\'s inventory', 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                }
            } else {
                $GLOBALS['page']->Message("The specified item does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No item ID was set", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
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
                ), array(
            array("name" => "Remove", "act" => "mod", "uid" => $_GET['uid'], "type" => "jutdel", "jid" => "table.jid"),
            array("name" => "Edit", "act" => "mod", "uid" => $_GET['uid'], "type" => "jutedit", "jid" => "table.entry_id")
                ), true, false, array(
            array("name" => "Add New Jutsu to User", "href" => '?id=' . $_GET['id'] . '&type=jutadd&act=mod&uid=' . $_GET['uid'])
                )
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Edit User item
    private function edit_jutsu_form() {
        if (isset($_GET['jid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_jutsu` WHERE `entry_id` = '" . $_GET['jid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                // Parse data
                tableParser::parse_form('users_jutsu', 'Edit user item', array('entry_id'), $data);

                // Set the return link
                $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
            } else {
                $GLOBALS['page']->Message("This jutsu does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No jutsu was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function do_edit_jutsu() {
        if (isset($_GET['jid']) && is_numeric($_GET['jid'])) {
            $changed = tableParser::check_data('users_jutsu', 'entry_id', $_GET['jid']);
            if (tableParser::update_data('users_jutsu', 'entry_id', $_GET['jid'])) {
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'User jutsu edited: " . $changed . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                $GLOBALS['page']->Message("The jutsu has been updated", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            
            } else {
                $GLOBALS['page']->Message("An error occured while updating the jutsu", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No jutsu was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function confirm_jutsu_delete() {
        $GLOBALS['page']->Confirm("Are you sure you wish to delete this jutsu from the user", 'User System', 'Delete now!');
    }

    private function do_jutsu_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `users_jutsu` WHERE `uid` = '" . $_GET['uid'] . "' AND `jid` = '" . $_GET['jid'] . "' LIMIT 1")) {
            $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'Jutsu removed: " . $_GET['jid'] . "', '" . $GLOBALS['user']->real_ip_address() . "');");
            $GLOBALS['page']->Message("The jutsu was removed from the user", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        } else {
            $GLOBALS['page']->Message("The jutsu could not be removed from the user", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function add_jutsu_form() {
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `jutsu` ORDER BY `name` ASC");
        $GLOBALS['template']->assign('items', $items);
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/add_jutsu.tpl');
    }

    private function do_jutsu_add() {
        if (isset($_POST['jid']) && is_numeric($_POST['jid'])) {
            $item = $GLOBALS['database']->fetch_data("SELECT `name` FROM `jutsu` WHERE `id` = '" . $_POST['jid'] . "' LIMIT 1");
            if ($item != '0 rows') {
                if (
                        $GLOBALS['database']->execute_query("INSERT INTO `users_jutsu` 
                        ( `uid` , `jid` , `level` , `exp` , `tagged` ) 
                        VALUES 
                        ('" . $_GET['uid'] . "', '" . $_POST['jid'] . "', '1', '1', 'no');")
                ) {
                    $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_GET['uid'] . "', 'User jutsu added to inventory: " . addslashes($item[0]['name']) . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                    $GLOBALS['page']->Message('The jutsu ' . stripslashes($item[0]['name']) . ' has been added to the user', 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                } else {
                    $GLOBALS['page']->Message('The jutsu ' . stripslashes($item[0]['name']) . ' could not be added to the user', 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                }
            } else {
                $GLOBALS['page']->Message("The specified jutsu does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No jutsu ID was set", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    // Federal Support
    private function fed_status_form() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {

            $fed_data = $GLOBALS['database']->fetch_data("
                SELECT `users`.`federal_timer`, `users`.`subscr_id`, `users`.`username` 
                FROM `users`,`users_timer` 
                WHERE `users`.`id` = `users_timer`.`userid` AND `users`.`id` = '" . $_GET['uid'] . "'");
            $GLOBALS['template']->assign('fed_data', $fed_data);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/fed_overview.tpl');
        } else {
            $GLOBALS['page']->Message("Data could not be retrieved (does this user exist?)", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function update_fed_status() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            if ($_POST['Submit'] == 'Update subscription ID') {
                $GLOBALS['database']->execute_query("
                    UPDATE `users`,`users_statistics` 
                    SET `subscr_id` = '" . $_POST['subscr_id'] . "', `user_rank` = 'Paid', `federal_timer` = '" . (time() + 3024000) . "' 
                    WHERE 
                        `users`.`id` = `users_statistics`.`uid` AND `users`.`id` = '" . $_GET['uid'] . "' AND
                        `users_statistics`.`user_rank` != 'Admin' "
                 );
            } elseif ($_POST['Submit'] == 'Give Fed for a Month') {
                $GLOBALS['database']->execute_query("
                    UPDATE `users`,`users_statistics` 
                    SET `subscr_id` = 'Admin set', `user_rank` = 'Paid', `federal_timer` = '" . (time() + 3024000) . "' 
                    WHERE 
                        `users`.`id` = `users_statistics`.`uid` AND `users`.`id` = '" . $_GET['uid'] . "' AND
                        `users_statistics`.`user_rank` != 'Admin'
                ");
            }
            $GLOBALS['page']->Message("Fed status updated", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        } else {
            $GLOBALS['page']->Message("Data could not be retrieved (does this user exist?)", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
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

    // Ban Options
    private function banMenu() {

        // Get Ban Data
        $bandata = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `action` = 'ban' AND `uid` = '" . $_GET['uid'] . "' AND `duration` NOT LIKE 'Extension:%' ORDER BY `time` DESC LIMIT 1");
        $bandata2 = $GLOBALS['database']->fetch_data("SELECT `ban_time`,`perm_ban` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
        $message = "";
        if ($bandata != '0 rows' && $bandata2 != "0 rows") {
            if ($bandata2[0]['ban_time'] > time()) {
                $message .= 'This user has been banned with the following reason:<br><b>' . $bandata[0]['reason'] . ':</b>' . $bandata[0]['message'] . '';
                $extension = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `action` = 'ban' AND `uid` = '" . $_GET['uid'] . "' AND `duration` LIKE 'Extension:%' ORDER BY `time` DESC LIMIT 1");
                if ($extension != '0 rows' && ($extension[0]['message'] !== "" || $extension[0]['reason'] !== "")) {
                    $message .= '<br><br><b>' . $extension[0]['duration'] . ': ' . $extension[0]['reason'] . '</b><br>' . $extension[0]['message'] . '';
                }
            }
        }

        // Show overview Menu
        $menu = array(
            array("name" => "Permanent Ban", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=ban&banOption=permBan"),
            array("name" => "Extend Ban", "href" => "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid'] . "&type=ban&banOption=banExtension")
        );
        $GLOBALS['template']->assign('subHeader', 'Ban Menu');
        $GLOBALS['template']->assign('nCols', 2);
        $GLOBALS['template']->assign('nRows', 1);
        $GLOBALS['template']->assign('subTitle', $message);
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }

    private function permBanSwitch() {
        $bandata = $GLOBALS['database']->fetch_data("SELECT `ban_time`,`perm_ban` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
        if ($bandata != '0 rows') {
            if (!isset($_POST['Submit'])) {
                if ($bandata[0]['perm_ban'] == 0) {
                    $GLOBALS['template']->assign('value', 'Insert a reason for banning');
                    $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/banReason.tpl');
                } else {
                    $GLOBALS['template']->assign('value', 'Insert a reason for unbanning');
                    $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/banReason.tpl');
                }
            } else {
                // get the user
                $user = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                if ($user != "0 rows") {

                    // Do something depending on the current ban status
                    if ($bandata[0]['perm_ban'] == 0) {

                        // Perm Ban user                
                        $GLOBALS['page']->Message("User has been permanently banned", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);

                        $GLOBALS['database']->execute_query("UPDATE `users` SET `perm_ban` = '1', `logout_timer` = '1' WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                        $GLOBALS['database']->execute_query("UPDATE `villages` SET `leader` = `name` WHERE `leader` = '" . $user[0]['username'] . "'");

                        // Log the perm ban:
                        $query = "INSERT INTO `moderator_log` 
                            ( `time` , `uid` , `username`, `duration`, `moderator` , `action` , `reason` , `message` ) VALUES 
                            ('" . time() . "', '" . $user[0]['id'] . "', '" . $user[0]['username'] . "', 
                             'Permanent','" . $GLOBALS['userdata'][0]['username'] . "', 'ban', 'Admin Rule', 
                             '" . functions::store_content($_POST['message']) . "')";
                        $GLOBALS['database']->execute_query($query);
                    } else {

                        // Un perm ban user
                        $GLOBALS['page']->Message("User has been unbanned", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                        $GLOBALS['database']->execute_query("UPDATE `users` SET `perm_ban` = '0' WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");

                        // Log the perm ban:
                        $query = "INSERT INTO `moderator_log` 
                            ( `time` , `uid` , `username`, `duration`, `moderator` , `action` , `reason` , `message` ) VALUES 
                            ('" . time() . "', '" . $user[0]['id'] . "', '" . $user[0]['username'] . "', 
                             'Unpermanent','" . $GLOBALS['userdata'][0]['username'] . "', 'ban', 'Admin Rule', 
                             '" . functions::store_content($_POST['message']) . "')";
                        $GLOBALS['database']->execute_query($query);
                    }
                } else {
                    $GLOBALS['page']->Message("User could not be found", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                }
            }
        } else {
            $GLOBALS['page']->Message("User data is corrupt. No entry in users_timers table found", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function banExtension() {
        $bandata = $GLOBALS['database']->fetch_data("SELECT `ban_time`,`perm_ban` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
        if ($bandata != '0 rows') {
            if ($bandata[0]['perm_ban'] == 0) {
                if ($bandata[0]['ban_time'] > time()) {
                    if (!isset($_POST['Submit'])) {
                        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/banExtensionForm.tpl');
                    } else {
                        $timeString = "";
                        switch ($_POST['length']) {
                            case 1:
                                $length = 3600 * 24;
                                $timeString = "1 day";
                                break;
                            case 2:
                                $length = 3600 * 24 * 7;
                                $timeString = "1 week";
                                break;
                            case 3:
                                $length = 3600 * 24 * 14;
                                $timeString = "2 weeks";
                                break;
                            case 4:
                                $length = 3600 * 24 * 31;
                                $timeString = "1 month";
                                break;
                            case 5:
                                $length = 3600 * 24 * 62;
                                $timeString = "2 months";
                                break;
                            case 6:
                                $length = 3600 * 24 * (386 / 2);
                                $timeString = "6 months";
                                break;
                        }

                        $GLOBALS['database']->execute_query("UPDATE `users` SET `ban_time` = `ban_time` + '" . $length . "' WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");

                        $GLOBALS['page']->Message("The ban length has been increased", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);

                        //    Log/Extend the ban:
                        $user = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                        $query = "INSERT INTO `moderator_log` 
                            ( `time` , `uid` , `username`, `duration`, `moderator` , `action` , `reason` , `message` ) VALUES 
                            ('" . time() . "', '" . $user[0]['id'] . "', '" . $user[0]['username'] . "', 
                             'Extension: " . $timeString . "','" . $GLOBALS['userdata'][0]['username'] . "', 'ban', '" . $_POST['reason'] . "', 
                             '" . functions::store_content($_POST['message']) . "')";
                        $GLOBALS['database']->execute_query($query);
                    }
                } else {
                    $GLOBALS['page']->Message("The ban length could not be increased, because this user is not currently banned.", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
                }
            } else {
                $GLOBALS['page']->Message("User is currently permanently banned.", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("User data is corrupt. No entry in users_timers table found", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    // User Events
    private function edit_assignment_form() {
        if (isset($_GET['uid'])) {
            $userTasks = $GLOBALS['database']->fetch_data("SELECT `tasks` FROM `users_missions` WHERE `userid` = '".$_GET['uid']."'");
            $userTasks = json_decode( $userTasks[0]['tasks'] ,true );
            
            $allTasks = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests`");
            
            $adminTasks = array();
            foreach( $allTasks as $task ){
                if( $task['type'] == "admin" ){
                    $adminTasks[] = $task;
                }
            }
            
            $GLOBALS['template']->assign('userTasks', $userTasks );
            $GLOBALS['template']->assign('adminTasks', $adminTasks);
            $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/content_users/achievements.tpl');
            
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function do_edit_assignment() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $userTasks = $GLOBALS['database']->fetch_data("SELECT `tasks` FROM `users_missions` WHERE `userid` = '".$_GET['uid']."'");
            
            $currentTasks = json_decode( $userTasks[0]['tasks'] ,true );
            if( $currentTasks == "" ){
                $currentTasks = array();
            }
            
            foreach( $_POST as $key => $post ){
                if( stristr( $key, "achievementID" ) ){
                    $split = explode(":::", $key);
                    if( !isset( $currentTasks[$split[1]] ) ){
                        if( $post == "yes" ){
                            $currentTasks[$split[1]] = "c";
                        }
                    }
                    else{
                        if( $post == "yes"  ){
                            $currentTasks[$split[1]] = "c";
                        }
                        else{
                            unset( $currentTasks[$split[1]] );
                        }
                    }
                }
            }
            
            $GLOBALS['database']->execute_query("UPDATE `users_missions` SET `tasks` = '".json_encode( $currentTasks )."' WHERE `userid` = '" . $_GET['uid'] . "' LIMIT 1");
            
            cachefunctions::deleteUserTasks( $_SESSION['uid'] );
            
            $GLOBALS['page']->Message( "Achievements have been updated" , 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    // Reduce Stats
    private function alter_stat_form() {
        $this->edit_main();
        if (isset($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/statReductionForm.tpl');
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    private function do_alter_stat() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_statistics` WHERE `uid` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $uery = "UPDATE `users_statistics` SET 
                                                            `strength` = " . ($data[0]['strength'] * ($_POST['strength'] / 100)) . ",
                                                            `intelligence` = " . ($data[0]['intelligence'] * ($_POST['intelligence'] / 100)) . ",
                                                            `willpower` = " . ($data[0]['willpower'] * ($_POST['willpower'] / 100)) . ",
                                                            `speed` = " . ($data[0]['speed'] * ($_POST['speed'] / 100)) . ",
                                                            `tai_off` = " . ($data[0]['tai_off'] * ($_POST['taiatt'] / 100)) . ",
                                                            `nin_off` = " . ($data[0]['nin_off'] * ($_POST['ninatt'] / 100)) . ",
                                                            `gen_off` = " . ($data[0]['gen_off'] * ($_POST['genatt'] / 100)) . ",
                                                            `weap_off` = " . ($data[0]['weap_off'] * ($_POST['weaatt'] / 100)) . ",
                                                            `tai_def` = " . ($data[0]['tai_def'] * ($_POST['taidef'] / 100)) . ",
                                                            `nin_def` = " . ($data[0]['nin_def'] * ($_POST['nindef'] / 100)) . ",
                                                            `gen_def` = " . ($data[0]['gen_def'] * ($_POST['gendef'] / 100)) . ",
                                                            `weap_def` = " . ($data[0]['weap_def'] * ($_POST['weadef'] / 100)) . ",
                                                            `max_health` = " . ($data[0]['max_health'] * ($_POST['hp'] / 100)) . ",
                                                            `max_cha` = " . ($data[0]['max_cha'] * ($_POST['cha'] / 100)) . ",
                                                            `max_sta` = " . ($data[0]['max_sta'] * ($_POST['sta'] / 100)) . "
                                                     WHERE `uid` = '" . $_GET['uid'] . "' LIMIT 1";

                $GLOBALS['database']->execute_query($uery);

                $GLOBALS['page']->Message("Stats successfully reduced", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
    }

    // Trade Log. Convert print_r string to array
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
        $edits = $GLOBALS['database']->fetch_data("
              SELECT * FROM `trade_log` 
              WHERE `user1` = '" . $_GET['uid'] . "' OR `user2` = '" . $_GET['uid'] . "' 
              ORDER BY `tradeid` DESC 
              LIMIT " . $min . ",10"
        );

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
            'time' => "Transaction Time",
            'item2name' => "Offer"
                ), false, true, true, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }

    // Ryo Log
    private function ryolog() {

        // Get the user
        if (is_numeric($_GET['uid'])) {
            $user_in_question = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            $id = $_GET['uid'];
        } else {
            $user_in_question = $GLOBALS['database']->fetch_data("SELECT `id`, `username` FROM `users` WHERE `username` = '" . $_GET['uid'] . "' LIMIT 1");
            $id = $user_in_question[0]['id'];
        }

        // Use the table parser library to show notes in system
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` WHERE `uid` = '" . $id . "'  ORDER BY `time` DESC LIMIT 100");
        tableParser::show_list(
                'sendingsFrom', 'Latest Sending from ' . $user_in_question[0]['username'] . '', $edits, array(
            'time' => "Time",
            'receiver' => "Received by",
            'amount' => "Ryo Amount"
                ), false, false, false
        );

        $edits1 = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` WHERE `receiver` = '" . $user_in_question[0]['username'] . "'  ORDER BY `time` DESC LIMIT 100");
        tableParser::show_list(
                'sendingsTo', 'Latest Sending to ' . $user_in_question[0]['username'] . '', $edits1, array(
            'time' => "Time",
            'sender' => "Received From",
            'amount' => "Ryo Amount"
                ), false, false, false
        );

        // Load template
        $GLOBALS['template']->assign('username', $user_in_question[0]['username']);
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/ryoLog.tpl');
    }
    
    // withdrawals made by user
    private function withdrawals(){
        
        // Get data
        // $min = tableParser::get_page_min();
        // tableParser::set_items_showed(100);
        $withdrawals = $GLOBALS['database']->fetch_data("
             SELECT 
                `ipn_payments`.*, 
                `senderTable`.`username` as `senderActiveName`, 
                `receiverTable`.`username` as `receiverActiveName`,
                `receiverTable2`.`rep_now`
             FROM `ipn_payments` 
             LEFT JOIN `users` AS `senderTable` ON (`ipn_payments`.`s_uid` = `senderTable`.`id`)
             LEFT JOIN `users` AS `receiverTable` ON (`ipn_payments`.`r_uid` = `receiverTable`.`id`)
             LEFT JOIN `users_statistics` AS `receiverTable2` ON (`ipn_payments`.`r_uid` = `receiverTable2`.`uid`)
             WHERE 
                `ipn_payments`.`status` IN ('Reversed','Canceled_Reversal') AND
                `ipn_payments`.`s_uid` = '" . $_GET['uid'] . "'
             ORDER BY time DESC 
             " 
             // LIMIT " . $min . ",100"
        );
        
        // Get canceled reversals
        $canceledReversals = array();
        foreach( $withdrawals as $withdrawal ){
            if( $withdrawal['status'] == "Canceled_Reversal" ){
                $canceledReversals[] = $withdrawal['txn_id'];
            }
        }
        
        // Modify data - only show reversals
        $withdrawalsToShow = array();
        foreach( $withdrawals as $withdrawal ){
            if( $withdrawal['status'] == "Reversed" ){
                if( in_array( $withdrawal['txn_id'] , $canceledReversals) ){
                    $withdrawal['status'] = "<font color='green'>Returned to TNR</font>";
                }
                else{
                    $withdrawal['status'] = "<font color='red'>Revered to user</font>";
                }
                // link on receiver
                $withdrawal['receiverActiveName'] = "<a href='?id=".$GLOBALS['page']->userModuleID."&act=mod&uid=".$withdrawal['r_uid']."'>".$withdrawal['receiverActiveName']."</a>";
                $withdrawalsToShow[] = $withdrawal;
            }
        }

        // Show form
        tableParser::show_list(
            'log', 
            'PayPal Withdrawal Log',
            $withdrawalsToShow, 
            array(
                'time' => "Time of Transaction",
                'item' => "Item",
                'price' => "Price",
                'status' => "Status",
                'receiverActiveName' => "Current Recipient Name",
                'recipient' => "Recipient Name at Purchase",
                'senderActiveName' => "From User",
                'txn_id' => "txn#",
                'rep_now' => 'Recipient Current Reps'
             ), 
             false, 
             true, 
             true, 
             false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
        
    }
    
    // Get reps from price
    private function getReps( $price ){        
        $price = $price * 1.1 + 0.3;
        $reps = 500;
        if( $price < 300 ){ $reps = 200; }
        if( $price < 120 ){ $reps = 120; }
        if( $price < 70 ){ $reps = 48; }
        if( $price < 30 ){ $reps = 24; }
        if( $price < 10 ){ $reps = 6; }
        if( $price < 8 ){ $reps = 1; }
        return $reps;
    }
    
    // Rep log
    private function repCheck(){
        
        if( $_SESSION['uid'] == "3819" ){
            
            // Payments to user
            $entries = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `ipn_payments`             
                WHERE `r_uid` = '" . $_GET['uid'] . "'"
            );

            // Black Market Purchases
            $bmEntries = $GLOBALS['database']->fetch_data("
                 SELECT * 
                 FROM `log_blackMarket`
                 WHERE `uid` = '" . $_GET['uid'] . "'"
            );

            // Get user data
            $userdata = $GLOBALS['database']->fetch_data("
                 SELECT `rep_ever`,`rep_now`
                 FROM `users_statistics`
                 WHERE `uid` = '" . $_GET['uid'] . "'
                 LIMIT 1"
            );

            // Track txn_id
            $cancelReversal = array();
            $reversal = array();

            // Total Income
            $total = 0;
            foreach( $entries as $entry ){
                if(stristr($entry['item'], "Reputation") ){
                    switch( $entry['status'] ){
                        case "Completed":
                            $total += $this->getReps($entry['price']);
                        break;
                        case "Canceled_Reversal":
                            if( !in_array($entry['txn_id'], $cancelReversal) ){
                                $total += $this->getReps($entry['price']);
                                $cancelReversal[] = $entry['txn_id'];
                            }
                        break;
                        case "Reversed": 
                            if( !in_array($entry['txn_id'], $reversal) ){
                                $total -= $this->getReps(-$entry['price']);
                                $reversal[] = $entry['txn_id'];
                            }
                        break;
                    }
                }
            }

            // Maximum current reps
            $maxCurrent = $total;
            if( $bmEntries !== "0 rows" ){
                foreach( $bmEntries as $entry ){
                    $maxCurrent -= $entry['repPrice'];
                }
            }

            // Perform actions
            if( isset( $_GET['action'] ) ){
                if( $_GET['action'] == "currentFix" ){
                    $GLOBALS['database']->execute_query("
                        UPDATE `users_statistics` SET 
                        `rep_now` = " . $maxCurrent . "
                        WHERE `uid` = '" . $_GET['uid'] . "'
                        LIMIT 1"
                    );
                    $userdata[0]['rep_now'] = $maxCurrent;
                }
                elseif( $_GET['action'] == "totalFix"  ){
                    $GLOBALS['database']->execute_query("
                        UPDATE `users_statistics` SET 
                        `rep_ever` = " . $total . "
                        WHERE `uid` = '" . $_GET['uid'] . "'
                        LIMIT 1"
                    );
                    $userdata[0]['rep_ever'] = $total;
                }
            }

            // Actions
            $actions = "";

            if( $userdata[0]['rep_now'] > $maxCurrent ){
                $actions .= "<br><a href='?id=" . $_GET['id'] . "&act=mod&uid=". $_GET['uid'] ."&type=repCheck&action=currentFix'>Fix Current</a>";
            }
            if( $userdata[0]['rep_ever'] != $total ){
                $actions .= "<br><a href='?id=" . $_GET['id'] . "&act=mod&uid=". $_GET['uid'] ."&type=repCheck&action=totalFix'>Fix Total</a>";
            }

            // Show to user
            $GLOBALS['page']->Message("<b>THIS IS BETA - NOT FOR USE YET</b>:<br>"
                    . " - Maximum total reps on user (based on paypal log) <b>".$total."</b><br>"                
                    . " - Maximum current reps (not including events, based on BM log): <b>".$maxCurrent."</b><br><br>"
                    . " - Rep ever on user is: <b>".$userdata[0]['rep_ever']."</b><br>"
                    . " - Rep current on user is: <b>".$userdata[0]['rep_now']."</b><br>".$actions."<br>", 
                    'User System', 
                    'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
        else{
            $GLOBALS['page']->Message("This feature can only be used by Terr", 
                    'User System', 
                    'id=' . $_GET['id'] . '&act=mod&uid=' . $_GET['uid']);
        }
        
        
    }

    // Reputation Log
    private function replog( $type = "r_uid" ) {

        // Get data
        $min = tableParser::get_page_min();
        tableParser::set_items_showed(100);
        $edits = $GLOBALS['database']->fetch_data("
             SELECT 
                `ipn_payments`.*, 
                `senderTable`.`username` as `senderActiveName`, 
                `receiverTable`.`username` as `receiverActiveName`
             FROM `ipn_payments`
             LEFT JOIN `users` AS `senderTable` ON (`ipn_payments`.`s_uid` = `senderTable`.`id`)
             LEFT JOIN `users` AS `receiverTable` ON (`ipn_payments`.`r_uid` = `receiverTable`.`id`)
             WHERE 
                `".$type."` = '" . $_GET['uid'] . "'
             ORDER BY time DESC 
             LIMIT " . $min . ",100"
        );

        // Modify data
        if ($edits !== "0 rows") {
            $i = 0;
            while ($i < count($edits)) {
                if ($edits[$i]['senderActiveName'] == "") {
                    $edits[$i]['senderActiveName'] = "Unregistered";
                }
                if ($edits[$i]['receiverActiveName'] == "") {
                    $edits[$i]['receiverActiveName'] = "Unregistered";
                }
                if( stristr($edits[$i]['item'],"Reputation") ){
                    $edits[$i]['item'] = $this->getReps($edits[$i]['price'])." ".$edits[$i]['item'];
                }
                $i++;
            }
        }

        // Show form
        tableParser::show_list(
                'log', 'PayPal Log', $edits, array(
            'time' => "Time of Transaction",
            'item' => "Item",
            'price' => "Price",
            'status' => "Status",
            'receiverActiveName' => "Current Recipient Name",
            'recipient' => "Recipient Name at Purchase",
            'senderActiveName' => "From User",
            'txn_id' => "txn#",
            'txn_type' => 'txn type'
                ), false, true, true, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Special Surprises Log
    private function limitedSpecialSurprises() {

        // Get data
        $min = tableParser::get_page_min();
        $entries = $GLOBALS['database']->fetch_data("
            SELECT `log_specialSurprisePurchases`.*, `items`.`name` as `itemname`
            FROM `log_specialSurprisePurchases` 
            LEFT JOIN `items` ON (`log_specialSurprisePurchases`.`reward_iid` = `items`.`id`)
            WHERE `uid` = '" . $_GET['uid'] . "' 
            ORDER BY time DESC 
            LIMIT 100");

        // Show form
        tableParser::show_list(
                'log', 
                'Limited Edition Special Surprises', $entries, array(
            'time' => "Time of Transaction",
            'reward_iid' => "Item Reward ID",
            'reward_name' => "Item Reward Name",
            'reward_count' => "Reward Count",
            'cost_type' => "Cost Type",
            'cost_amount' => "Cost Amount"), 
                false, true, true, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Black Market Log
    private function blackmarketlog() {

        // Get data
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `log_blackMarket` WHERE `uid` = '" . $_GET['uid'] . "' ORDER BY time DESC LIMIT 100");

        // Show form
        tableParser::show_list(
                'log', 'Black Market Log', $edits, array(
            'time' => "Time of Transaction",
            'blackMarketName' => "Item",
            'repPrice' => "Rep Price"), false, true, true, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
    }
    
    // Ninja Farmer Log
    private function ninjaFarmerLog() {

        // Get status
        $status = $GLOBALS['database']->fetch_data("SELECT * FROM `ninja_farmer` WHERE `uid` = '" . $_GET['uid'] . "' LIMIT 1");
        if( $status !== "0 rows" ){
            
            // Get entries
            $min = tableParser::get_page_min();
            $entries = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `log_minigame_points` 
                WHERE `uid` = '" . $_GET['uid'] . "' AND `gameName` = 'NinjaFarmer'
                ORDER BY `time` DESC 
                LIMIT " . $min . ",10");

            // Show form
            tableParser::show_list(
                    'log', 'Ninja Farmer Log', $entries, array(
                'time' => "Time of Transaction",
                'points' => "Farmer Points",
                'deviceID' => "Device ID"), 
                    false, 
                    true, // Send directly to contentLoad
                    true, // Yes newer/older links
                    false, // No top options links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    "User has received a total of ".$status[0]['pop_points']." popularity points from the app."
            );

            // Set the return link
            $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
            
        }
        else{
            throw new Exception("This user hasn't received any ninja farmer points");
        }
    }
    
    
    
    // Reputation Log
    private function villageChangeLog() {

        // Get data
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `log_villageChanges` WHERE `uid` = '" . $_GET['uid'] . "' ORDER BY time DESC LIMIT 100");

        // Show form
        tableParser::show_list(
                'log', 'Village Change Log', $edits, array(
            'time' => "Time of Action",
            'startVillage' => "Start Village",
            'endVillage' => "End Village",
            'reason' => "Reason"
                ), false, true, true, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod&uid=" . $_GET['uid']);
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

new users();