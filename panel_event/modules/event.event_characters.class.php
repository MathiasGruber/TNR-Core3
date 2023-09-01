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

class eventCharacters {

    public function __construct() {
        if (!isset($_GET['act']) || $_GET['act'] == 'eventchar') {
            $this->list_characters();
        } elseif ($_GET['act'] == 'usechar') {
            $this->hijack_character();
        } elseif ($_GET['act'] == 'deluser') {
            if (!isset($_POST['Submit'])) {
                $this->del_user_form();
            } else {
                $this->do_delete_user();
            }
        } elseif ($_GET['act'] == 'adduser') {
            if (!isset($_POST['Submit'])) {
                $this->add_user_form();
            } else {
                $this->insert_add_user();
            }
        } elseif ($_GET['act'] == 'edituser' || $_GET['act'] == 'editusers_statistics') {
            if (!isset($_POST['Submit'])) {
                $this->edit_user_form();
            } else {
                $this->do_edit_user();
            }
        }
        else if ($_GET['act'] == 'add_jutsu')
        {
            if (!isset($_POST['Submit']))
                $this->add_jutsu_form();
            else
                $this->add_jutsu_do();
        }
        else if($_GET['act'] == 'add_item')
        {
            if (!isset($_POST['Submit']))
                $this->add_item_form();
            else
                $this->add_item_do();
        }
    }

    private function add_jutsu_form()
    {
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `jutsu` ORDER BY `name` ASC");
        $GLOBALS['template']->assign('items', $items);
        $GLOBALS['template']->assign('contentLoad', './panel_event/templates/event_characters/add_jutsu.tpl');
    }

    private function add_item_form()
    {
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `items` ORDER BY `name` ASC");
        $GLOBALS['template']->assign('items', $items);
        $GLOBALS['template']->assign('contentLoad', './panel_event/templates/event_characters/add_item.tpl');
    }

    private function add_jutsu_do()
    {
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

    private function add_item_do()
    {
        if (isset($_POST['iid']) && is_numeric($_POST['iid'])) {
            $item = $GLOBALS['database']->fetch_data("SELECT `name`,`durability` FROM `items` WHERE `id` = '" . $_POST['iid'] . "' LIMIT 1");
            if ($item != '0 rows') {
                if ($GLOBALS['database']->execute_query("INSERT INTO `users_inventory`
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

    //  List Event Characters
    private function list_characters() {

        // Show form
        $chars = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users_statistics`.`rank`
            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
            WHERE `users_statistics`.`user_rank` = 'Event' ORDER BY `username` ASC");
        tableParser::show_list(
                'event', 'Event characters', $chars, array(
            'username' => "Username",
            'rank' => "Rank"
                ), array(
            array("name" => "Add Jutsu", "act" => "add_jutsu", "uid" => "table.id"),
            array("name" => "Add Item", "act" => "add_item", "uid" => "table.id"),
            array("name" => "Use", "act" => "usechar", "uid" => "table.id"),
            array("name" => "Stats", "act" => "editusers_statistics", "uid" => "table.id"),
            array("name" => "Modify", "act" => "edituser", "uid" => "table.id"),
            array("name" => "Delete", "act" => "deluser", "uid" => "table.id")
                ),
                true, // Send directly to contentLoad
                false,
                array(
            array("name" => "Add Event Character", "href" => "?id=" . $_GET["id"] . "&act=adduser")
                ),
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            ''
        );
    }

    // Hijack event characters
    private function hijack_character() {
        if (is_numeric($_GET['uid'])) {
            $char = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users_statistics`.`user_rank`, `users`.`username`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                WHERE `users`.`id` = " . $_GET['uid'] . " LIMIT 1");

            if ($char != '0 rows') {
                if ($char[0]['user_rank'] == 'Event') {


                    // Log Changes
                    $GLOBALS['page']->setLogEntry("Event Char Hijack", $char[0]['username'], $char[0]['id']);

                    // Update session stuff
                    session_regenerate_id();

                    // Allow for easy go-back
                    $hash = hash("sha512", "secretTadaaa" . $_SESSION['uid']);
                    $_SESSION['backData'] = array($_SESSION['uid'], $hash);

                    // Set session
                    $_SESSION['uid'] = $char[0]['id'];
                    $_SESSION['override'] = true;

                    // Update user table
                    $loginID = session_id() . md5($char[0]['username'] . "xXx");
                    $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_login` = '" . $GLOBALS['page']->load_time . "'      WHERE `userid` = '" . $char[0]['id'] . "' LIMIT 1");
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `logout_timer` = '" . ($GLOBALS['page']->load_time + 7200) . "', `login_id` = '" . $loginID . "' WHERE `id` = '" . $char[0]['id'] . "' LIMIT 1");

                    // Show message
                    $GLOBALS['page']->Message("You have hijacked " . $char[0]['username'], 'Hijack User', 'id=2');
                } else {
                    $GLOBALS['page']->Message("The specified user is not an event character.", 'Hijack User', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("The specified user does not exist.", 'Hijack User', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An invalid user id was specified.", 'Hijack User', 'id=' . $_GET['id']);
        }
    }

    //  Set the user back to admin/contentAdmin
    private function setBackToOriginal() {
        // Allow for easy go-back
        $hash = hash("sha512", "secretTadaaa" . $_SESSION['backData'][0]);
        if ($hash == $_SESSION['backData'][1]) {
            $user = $GLOBALS['database']->fetch_data("SELECT `users`.`username` FROM `users` WHERE `users`.`id` = '" . $_SESSION['backData'][0] . "' LIMIT 1");

            $GLOBALS['page']->Message("You have been logged in as: " . $user[0]['username'], 'Hijack User', 'id=' . $_GET['id']);

            // Set session
            $_SESSION['uid'] = $_SESSION['backData'][0];
            unset($_SESSION['backData']);

            // Update User
            $GLOBALS['database']->execute_query("UPDATE `users` SET `login_id` = '" . $_COOKIE['PHPSESSID'] . md5($user[0]['username'] . "xXx") . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
        } else {
            $GLOBALS['page']->Message("Session details are invalid.", 'Hijack User', 'id=' . $_GET['id']);
        }
    }

    //  Character Functions
    private function del_user_form() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $GLOBALS['page']->Confirm("Delete this character", 'Character System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid user ID was specified.", 'Character System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete_user() {
        if (is_numeric($_GET['uid']) && $_GET['uid'] > 0) {
            $users = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users_statistics`.`user_rank`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                WHERE `users`.`id` = " . $_GET['uid'] . " LIMIT 1");
            if ($users != '0 rows') {
                if ($users[0]['user_rank'] == 'Event') {
                    if ($GLOBALS['database']->execute_query("DELETE FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1")) {
                        $GLOBALS['page']->setLogEntry("Event Character", "Deleted event character ".$users[0]['username'] , $_GET['uid']);
                        $GLOBALS['page']->Message("The character has been removed.", 'Character System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured while removing the character.", 'Character System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("This is not an event character.", 'Character System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This character does not exist.", 'Character System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An invalid character has been specified.", 'Character System', 'id=' . $_GET['id']);
        }
    }

    private function edit_user_form() {
        if (isset($_GET['uid'])) {

            if ($_GET['act'] == 'edituser') {
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    WHERE `users`.`id` = " . $_GET['uid'] . " LIMIT 1");
                $table = "users";
                $col = "id";
            } else {
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_statistics` WHERE `uid` = '" . $_GET['uid'] . "' LIMIT 1");
                $table = "users_statistics";
                $col = "uid";
            }
            if ($data != '0 rows') {
                if ($data[0]['user_rank'] == 'Event') {
                    tableParser::parse_form($table, 'Edit user', array('id', 'uid', 'password', 'salted_password', 'join_date', 'logout', 'ip_lock', 'healed', 'rep_now', 'rep_ever', 'pop_now', 'pop_ever', 'fbID', 'user_rank', 'login_id'), $data);
                } else {
                    $GLOBALS['page']->Message("This is not an event character", 'Character System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist?", 'Character System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No user was specified.", 'Character System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit_user() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            if ($_GET['act'] == 'edituser') {
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    WHERE `users`.`id` = " . $_GET['uid'] . " LIMIT 1");
                $table = "users";
                $col = "id";
            } else {
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_statistics` WHERE `uid` = '" . $_GET['uid'] . "' LIMIT 1");
                $table = "users_statistics";
                $col = "uid";
            }
            if ($data != '0 rows') {
                if ($data[0]['user_rank'] == 'Event') {
                    // Get what is changed
                    $changed = tableParser::check_data($table, $col, $_GET['uid'], array('join_date', 'id', 'healed'));

                    // Run the update
                    if (tableParser::update_data($table, $col, $_GET['uid'])) {
                        $GLOBALS['page']->setLogEntry("Event Character", "Edited event character with ID ".$_GET['uid'].": <br>" . $changed, $_GET['uid']);
                        $GLOBALS['page']->Message("The user has been updated.", 'Character System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured while updating the user.", 'Character System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("This is not an event character.", 'Character System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist?.", 'Character System', 'id=' . $_GET['id']);
            }
        }
    }

    private function add_user_form() {
        tableParser::parse_form('users', 'New user', array('id', 'password', 'salted_password', 'join_date', 'logout', 'ip_lock', 'rep_now', 'rep_ever', 'pop_now', 'pop_ever', 'fbID', 'user_rank', 'login_id'));
    }

    private function insert_add_user() {
        $users = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `username` LIKE '" . $_POST['username'] . "' LIMIT 1");
        if ($users == '0 rows') {

            $_POST['password'] = md5("XYhUshaAPsj72jf");
            $_POST['join_date'] = $GLOBALS['page']->load_time;
            $_POST['mail'] = 'eventchar@tnr.com'.$GLOBALS['page']->load_time;

            //$data['user_rank'] = 'Event';
            // Insert into user table
            if (tableParser::insert_data('users')) {

                // Get the ID
                $id = $GLOBALS['database']->get_inserted_id();

                // Set data
                $statistics["user_rank"] = "Event";
                $statistics["uid"] = $id;

                // Insert into statistics table
                if (tableParser::insert_data('users_statistics', $statistics)) {
                    if (
                            $GLOBALS['database']->execute_query("INSERT INTO `users_missions` (`userid`) VALUES ('" . $id . "')") &&
                            $GLOBALS['database']->execute_query("INSERT INTO `users_timer` (`userid`) VALUES ('" . $id . "')") &&
                            $GLOBALS['database']->execute_query("INSERT INTO `users_occupations` (`userid`) VALUES ('" . $id . "')") &&
                            $GLOBALS['database']->execute_query("INSERT INTO `bingo_book` (`userID`) VALUES ('" . $id . "')") &&
                            $GLOBALS['database']->execute_query("INSERT INTO `users_loyalty` (`uid`) VALUES ('" . $id . "')") &&
                            $GLOBALS['database']->execute_query("INSERT INTO `users_preferences` (`uid`) VALUES ('" . $id . "')")
                    ) {

                        $GLOBALS['page']->setLogEntry("Event Character", "Created character with name: " . $_POST['username']
                                                                    ."<br>bloodline: ".$_POST['bloodline']
                                                                    ."<br>village: ".$_POST['village']
                                                                    ."<br>latitude: ".$_POST['latitude']
                                                                    ."<br>longitude: ".$_POST['longitude']
                                                                    ."<br>activation: ".$_POST['activation']
                                                                    ."<br>immunity: ".$_POST['immunity']);
                        $GLOBALS['page']->Message("Character was successfully created.", 'Character System', 'id=' . $_GET['id']);
                    }
                }
            }
        } else {
            $GLOBALS['page']->Message("Character name already in use.", 'Character System', 'id=' . $_GET['id']);
        }
    }

}

new eventCharacters();