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
                if (isset($_SESSION['uid'])) {
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
                $this->main_page();
            } elseif (
               $_GET['type'] == 'users' ||
               $_GET['type'] == 'users_preferences' ||
               $_GET['type'] == 'users_statistics' ||
               $_GET['type'] == 'users_loyalty' ||
               $_GET['type'] == 'users_occupations'
            ) {
                //      Edit chardata
                if (!isset($_POST['Submit'])) {
                    $this->edit_user_form();
                } else {
                    $this->do_edit_user();
                }
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
                $this->replog();
            } elseif ($_GET['type'] == 'ryolog') {
                $this->ryolog();
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

        // Menu
        $menu = array(
                array("name" => "Edit: users-table", "href" => "?id=" . $_GET['id'] . "&act=mod&type=users"),
                array("name" => "Edit: users_preferences", "href" => "?id=" . $_GET['id'] . "&act=mod&type=users_preferences"),
                array("name" => "Edit: users_statistics", "href" => "?id=" . $_GET['id'] . "&act=mod&type=users_statistics"),
                array("name" => "Edit: users_loyalty", "href" => "?id=" . $_GET['id'] . "&act=mod&type=users_loyalty"),
                array("name" => "Edit: users_occupations", "href" => "?id=" . $_GET['id'] . "&act=mod&type=users_occupations"),
                array("name" => "Edit: users_missionData", "href" => "?id=" . $_GET['id'] . "&act=mod&type=misc"),
                array("name" => "See inventory", "href" => "?id=" . $_GET['id'] . "&act=mod&type=inv"),
                array("name" => "See jutsu", "href" => "?id=" . $_GET['id'] . "&act=mod&type=jut"),
                array("name" => "Latest User Modifications", "href" => "?id=" . $_GET["id"] . "&act=edits"),
                array("name" => "Search User Modifications", "href" => "?id=" . $_GET["id"] . "&act=editsearch")
            );
        
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
                ), false, true, true, $menu
        );
    }


    // Edit User
    private function edit_user_form() {
        if (isset($_SESSION['uid'])) {
            
            $col = "uid";
            if( $_GET['type'] == "users" ){
                $col = "id";
            }
            elseif( $_GET['type'] == "users_occupations" ){
                $col = "userid";
            }
            
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `".$_GET['type']."` WHERE `".$col."` = '" . $_SESSION['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                // Parse the user table
                tableParser::parse_form( $_GET['type'] , 'Edit userID: '.$_SESSION['uid'], array( $col , "salted_password",'user_rank'), $data);

                // Set the return link
                $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod");
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod' );
        }
    }

    private function do_edit_user() {
        if (isset($_SESSION['uid']) && is_numeric($_SESSION['uid'])) {
            $col = "uid";
            if( $_GET['type'] == "users" ){
                $col = "id";
            }
            elseif( $_GET['type'] == "users_occupations" ){
                $col = "userid";
            }
            $changed = tableParser::check_data($_GET['type'], $col, $_SESSION['uid'], array($col));
            if (tableParser::update_data($_GET['type'], $col, $_SESSION['uid'])) {
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_SESSION['uid'] . "', 'User stats updated:<br> " . $changed . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                $GLOBALS['page']->Message("The user has been updated", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            } else {
                $GLOBALS['page']->Message("An error occured while updating the user", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    // Mission Data
    private function edit_misc_form() {
        if (isset($_SESSION['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_missions` WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                // Parse data
                tableParser::parse_form('users_missions', 'Edit user statistics', array('userid'), $data);

                // Set the return link
                $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod");
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    private function do_edit_misc() {
        if (isset($_SESSION['uid']) && is_numeric($_SESSION['uid'])) {
            $changed = tableParser::check_data('users_missions', 'userid', $_SESSION['uid']);
            if (tableParser::update_data('users_missions', 'userid', $_SESSION['uid'])) {
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_SESSION['uid'] . "', 'User misc statistics updated: " . $changed . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                $GLOBALS['page']->Message("The user has been updated", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            
                // Clear cache
                cachefunctions::deleteUserTasks( $_SESSION['uid'] );
                
            } else {
                $GLOBALS['page']->Message("An error occured while updating the user", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            }
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    // Inventory Control    
    private function show_inventory() {

        // Show form
        $min = tableParser::get_page_min();
        $items = $GLOBALS['database']->fetch_data("SELECT `users_inventory`.*, `items`.`name` FROM `users_inventory`,`items` WHERE `uid` = '" . $_SESSION['uid'] . "' AND `users_inventory`.`iid` = `items`.`id` ORDER BY `timekey` ASC  LIMIT " . $min . ",10");
        tableParser::show_list(
                'items', 'User Item Menu', $items, array(
            'iid' => "Item ID",
            'name' => "Item Name",
            'equipped' => "Equipped",
            'stack' => "Stack Size"
                ), array(
            array("name" => "Remove", "act" => "mod", "uid" => $_SESSION['uid'], "type" => "invdel", "iid" => "table.iid", "timekey" => "table.timekey")
                ), true, true, array(
            array("name" => "Add New Item", "href" => '?id=' . $_GET['id'] . '&type=invadd&act=mod')
                )
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod");
    }

    private function confirm_item_delete() {
        $GLOBALS['page']->Confirm("Are you sure you wish to delete this item from the user's inventory", 'User System', 'Delete now!');
    }

    private function do_item_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `uid` = '" . $_SESSION['uid'] . "' AND `iid` = '" . $_GET['iid'] . "' AND `timekey` = '" . $_GET['timekey'] . "' LIMIT 1")) {
            $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_SESSION['uid'] . "', 'Item deleted from inventory: " . $_GET['iid'] . "', '" . $GLOBALS['user']->real_ip_address() . "');");
            $GLOBALS['page']->Message("The item was removed from the user's inventory", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        } else {
            $GLOBALS['page']->Message("Item could not be removed from the user's inventory", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    private function add_item_form() {
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `items` ORDER BY `name` ASC");
        $GLOBALS['template']->assign('items', $items);
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/add_item.tpl');
    }

    private function do_item_add() {
        if (isset($_POST['iid']) && is_numeric($_POST['iid'])) {
            $item = $GLOBALS['database']->fetch_data("SELECT `name` FROM `items` WHERE `id` = '" . $_POST['iid'] . "' LIMIT 1");
            if ($item != '0 rows') {
                if ($GLOBALS['database']->execute_query("INSERT INTO `users_inventory` ( `uid` , `iid` , `equipped` , `stack` , `timekey` ) VALUES ('" . $_SESSION['uid'] . "', '" . $_POST['iid'] . "', 'no', '1', UNIX_TIMESTAMP());")) {
                    $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_SESSION['uid'] . "', 'User item added to inventory: " . addslashes($item[0]['name']) . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                    $GLOBALS['page']->Message('The ' . stripslashes($item[0]['name']) . ' has been added to the user\'s inventory', 'User System', 'id=' . $_GET['id'] . '&act=mod');
                } else {
                    $GLOBALS['page']->Message('The ' . stripslashes($item[0]['name']) . ' could not be added to the user\'s inventory', 'User System', 'id=' . $_GET['id'] . '&act=mod');
                }
            } else {
                $GLOBALS['page']->Message("The specified item does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            }
        } else {
            $GLOBALS['page']->Message("No item ID was set", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    // User Jutsu
    private function show_jutsu() {

        // Show form
        $jutsu = $GLOBALS['database']->fetch_data("SELECT `users_jutsu`.*, `jutsu`.`name` FROM `users_jutsu`,`jutsu` WHERE `uid` = '" . $_SESSION['uid'] . "' AND `users_jutsu`.`jid` = `jutsu`.`id` ORDER BY `name` DESC");
        tableParser::show_list(
                'items', 'User Jutsu Menu', $jutsu, array(
            'jid' => "Jutsu ID",
            'name' => "Jutsu Name",
            'level' => "Level",
            'tagged' => "Tagged"
                ), array(
            array("name" => "Remove", "act" => "mod", "uid" => $_SESSION['uid'], "type" => "jutdel", "jid" => "table.jid")
                ), true, false, array(
            array("name" => "Add New Jutsu to User", "href" => '?id=' . $_GET['id'] . '&type=jutadd&act=mod')
                )
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] . "&act=mod");
    }

    private function confirm_jutsu_delete() {
        $GLOBALS['page']->Confirm("Are you sure you wish to delete this jutsu from the user", 'User System', 'Delete now!');
    }

    private function do_jutsu_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `users_jutsu` WHERE `uid` = '" . $_SESSION['uid'] . "' AND `jid` = '" . $_GET['jid'] . "' LIMIT 1")) {
            $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_SESSION['uid'] . "', 'Jutsu removed: " . $_GET['jid'] . "', '" . $GLOBALS['user']->real_ip_address() . "');");
            $GLOBALS['page']->Message("The jutsu was removed from the user", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        } else {
            $GLOBALS['page']->Message("The jutsu could not be removed from the user", 'User System', 'id=' . $_GET['id'] . '&act=mod');
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
                        ('" . $_SESSION['uid'] . "', '" . $_POST['jid'] . "', '1', '1', 'no');")
                ) {
                    $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES (UNIX_TIMESTAMP(), '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_SESSION['uid'] . "', 'User jutsu added to inventory: " . addslashes($item[0]['name']) . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                    $GLOBALS['page']->Message('The jutsu ' . stripslashes($item[0]['name']) . ' has been added to the user', 'User System', 'id=' . $_GET['id'] . '&act=mod');
                } else {
                    $GLOBALS['page']->Message('The jutsu ' . stripslashes($item[0]['name']) . ' could not be added to the user', 'User System', 'id=' . $_GET['id'] . '&act=mod');
                }
            } else {
                $GLOBALS['page']->Message("The specified jutsu does not exist", 'User System', 'id=' . $_GET['id'] . '&act=mod');
            }
        } else {
            $GLOBALS['page']->Message("No jutsu ID was set", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    
    // User Events
    private function edit_assignment_form() {
        if (isset($_SESSION['uid'])) {
            $userTasks = $GLOBALS['database']->fetch_data("SELECT `tasks` FROM `users_missions` WHERE `userid` = '".$_SESSION['uid']."'");
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
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
    }

    private function do_edit_assignment() {
        if (isset($_SESSION['uid']) && is_numeric($_SESSION['uid'])) {
            $userTasks = $GLOBALS['database']->fetch_data("SELECT `tasks` FROM `users_missions` WHERE `userid` = '".$_SESSION['uid']."'");
            
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
            
            $GLOBALS['database']->execute_query("UPDATE `users_missions` SET `tasks` = '".json_encode( $currentTasks )."' WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            
            cachefunctions::deleteUserTasks( $_SESSION['uid'] );
            
            $GLOBALS['page']->Message( "Achievements have been updated" , 'User System', 'id=' . $_GET['id'] . '&act=mod');
            
        } else {
            $GLOBALS['page']->Message("No user was specified", 'User System', 'id=' . $_GET['id'] . '&act=mod');
        }
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