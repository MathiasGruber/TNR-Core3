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

    class notes {

        private $staff_search;

        public function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Limit User Rank Searching
                $this->staff_search = ($GLOBALS['userdata'][0]['user_rank'] === 'Admin') ? "'Moderator', 'Supermod'" : "'Moderator'";

                if (!isset($_GET['act'])) {
                    self::main_screen();
                }
                else {
                    switch($_GET['act']) {
                        case('add'): {
                            (!isset($_POST['Submit'])) ? self::add_mod_form() : self::add_mod_do();
                        } break;
                        case('fire'): {
                            (!isset($_POST['Submit'])) ? self::fire_mod_form() : self::fire_mod_do();
                        } break;
                        case('list'): self::list_mods(); break;
                        default: self::main_screen(); break;
                    }
                }
            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), "Staff Management System", 'id='.$_REQUEST['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        // Main page
         private function main_screen() {

            $menu = array(
                array(
                    "name" => "Add Staff Member",
                    "href" => "?id=".$_GET['id']."&act=add"
                ),
                array(
                    "name" => "Fire Staff Member",
                    "href" => "?id=".$_GET['id']."&act=fire"
                ),
                array(
                    "name" => "List Staff Members",
                    "href" => "?id=".$_GET['id']."&act=list"
                )
            );

            $GLOBALS['template']->assign('subHeader', 'Manage Staff Members');
            $GLOBALS['template']->assign('nCols', 3);
            $GLOBALS['template']->assign('nRows', 1);
            $GLOBALS['template']->assign('subTitle', 'Management of the staff in the system');
            $GLOBALS['template']->assign('linkMenu', $menu);
            $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
        }

        // Fire moderator functions
        // ========================

        // Show a form
        private function fire_mod_form(){

            // Get the data
            if (!($mods = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id` AND
                      `users_statistics`.`user_rank` IN (".$this->staff_search."))
                ORDER BY `users`.`id` ASC"))) {
                throw new Exception('Could not retrieve the moderators from the database');
            }
            elseif($mods === '0 rows') {
                throw new Exception('There are no moderators within the system!');
            }

            $names = array();
            foreach($mods as $mod) { $names[ $mod['id'] ] = $mod['username']; }

            // Show the form
            $GLOBALS['page']->UserInput("Select a staff member to fire", "Fire Staff Member",
                array(
                    // A select box
                    array(
                        "infoText" => "Moderator Name",
                        "inputFieldName" => "fire_user",
                        "type" => "select",
                        "inputFieldValue" => $names
                    )
                ),
                array(
                    "href" => "?id=".$_REQUEST['id']."&act=".$_GET['act'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                "?id=".$_GET['id'],
                "modFireForm"
            );

        }

        // Do fire mod
        private function fire_mod_do(){

            if (!isset($_POST['fire_user'])) {
                throw new Exception('No user specified');
            }
            elseif (ctype_digit($_POST['fire_user']) === false) {
                throw new Exception('No valid user id specified');
            }

            $GLOBALS['database']->transaction_start();

            if (!($user = $GLOBALS['database']->fetch_data("SELECT `users_statistics`.`user_rank`,
                `users`.`username`, `users`.`id`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`
                        AND `users_statistics`.`user_rank` IN (".$this->staff_search."))
                WHERE `users`.`id` = '" . $_POST['fire_user'] . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception('Could not retrieve user in database');
            }
            elseif ($user === "0 rows") {
                throw new Exception('No userdata was retrieved from database');
            }

            if (($GLOBALS['database']->execute_query('UPDATE `users_statistics`
                    INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                SET `users_statistics`.`user_rank` = "Member", `users`.`logout_timer` = UNIX_TIMESTAMP()
                WHERE `users_statistics`.`uid` = ' . $user[0]['id'])) === false) {
                throw new Exception('There was an error updating the user data');
            }

            // Log the action
            $GLOBALS['page']->log_for_admins(
                time(),
                $GLOBALS['page']->user[0]['id'],
                $GLOBALS['page']->user[0]['username'],
                "Fired the user ".$user[0]['username']." as a staff member"
            );

            $GLOBALS['page']->Message('You have fired ' . $user[0]['username'] . ' from being a staff member!', 'Staff Management System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }

        // Add moderator functions
        // ========================

        // Show form
        private function add_mod_form() {

            // Create the input form
            $GLOBALS['page']->UserInput("Enter username to hire as a moderator", "Staff Hiring System",
                array(
                    // Username
                    array(
                        "infoText" => "Username",
                        "inputFieldName" => "username_hire",
                        "type" => "input",
                        "inputFieldValue" => ""
                    )
                ),
                array(
                    "href" => "?id=" . $_REQUEST['id'] . "&act=" . $_GET['act'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                "?id=".$_GET['id'],
                "hireModForm"
            );
        }

        // Add mod
        private function add_mod_do() {

            if (!isset($_POST['username_hire']) || functions::ws_remove($_POST['username_hire']) === '') {
                throw new Exception('You did not specify a username');
            }

            $GLOBALS['database']->transaction_start();

            if (!($user = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`, `users`.`federal_timer`,
                `users_statistics`.`user_rank`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`
                        AND `users_statistics`.`user_rank` IN ("Member", "Paid"))
                WHERE `users`.`username` = "' . $_POST['username_hire'] . '" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error with the query retrieving the user');
            }
            elseif ($user === "0 rows") {
                throw new Exception('No userdata could be found in the database OR is already a staff member!');
            }

            if ($user[0]['user_rank'] === 'Member') {
                if ($GLOBALS['database']->execute_query('UPDATE `users_statistics`
                    SET `users_statistics`.`user_rank` = "Moderator"
                    WHERE `users_statistics`.`uid` = ' . $user[0]['id'] . ' LIMIT 1') === false) {
                    throw new Exception('There was an error updating the user');
                }
            }
            else { // Remove Federal upon Rank change
                if ($GLOBALS['database']->execute_query('UPDATE `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    SET `users`.`federal_timer` = UNIX_TIMESTAMP(),
                        `users_statistics`.`user_rank` = "Moderator"
                    WHERE `users`.`id` = "' . $user[0]['id'] . '"') === false) {
                    throw new Exception('There was an error updating the user and his federal status');
                }

                $users_notifications = new NotificationSystem('', $user[0]['id']);

                $users_notifications->addNotification(array(
                                                            'id' => 15,
                                                            'duration' => 'none',
                                                            'text' => "Your federal support has been removed due to becoming a Moderator!",
                                                            'dismiss' => 'yes'
                                                        ));

                $users_notifications->recordNotifications();
            }

            // Log the action
            $GLOBALS['page']->log_for_admins(
                time(),
                $GLOBALS['page']->user[0]['id'],
                $GLOBALS['page']->user[0]['username'],
                "Hired the user ".$user[0]['username']." as a moderator"
            );

            $GLOBALS['page']->Message('You have appointed ' . $user[0]['username'] . ' as a moderator', 'Staff Hiring System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }

        // List current moderators
        // =======================

        private function list_mods(){

            // Get the data
            if (!($mods = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id` AND
                        `users_statistics`.`user_rank` IN (".$this->staff_search."))
                ORDER BY `users`.`id` ASC"))) {
                throw new Exception('Could not retrieve the moderators from the database');
            }
            elseif($mods === '0 rows') {
                throw new Exception('There are no moderators within the system!');
            }

            // Show the list /w tracking links
            tableParser::show_list('log', "Current Staff Members",
                $mods,
                array(
                    'id' => "User ID",
                    'username' => "Username"
                ),
                array(
                    array(
                        "name" => "Track Record",
                        "id" => $GLOBALS['page']->trackPage,
                        "moderator_track" => "table.username"
                    )
                ),
                true, // Send directly to contentLoad
                false,
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                "For more details, check the staff members track record"
            );
        }
    }

    new notes();