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

    class module {

        public function mo__constructdule() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {

                if (isset($_GET['act']) && $_GET['act'] === "unban") {
                    (!isset($_POST['Submit'])) ? self::confirm_unban() : self::do_unban();
                }
                else {
                    self::main_page();
                }

            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback( $e->getMessage() );
                $GLOBALS['page']->Message($e->getMessage(), "Tavern Ban System", 'id=' . $_GET['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        // Main page
        private function main_page() {

            // Parsing data
            $min =  tableParser::get_page_min();
            $number = tableParser::set_items_showed(10);

            if(isset($_POST['tban_search'])) {
                if(isset($_POST['name'])) {
                    if(functions::ws_remove($_POST['name']) === '') {
                        throw new Exception('You must enter a username to search within the active tavern bans!');
                    }
                }
                else {
                    throw new Exception('There is no username provided in the search feature!');
                }
            }

            if(!($banned = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`
                FROM `users` WHERE `users`.`post_ban` = '1' ".((isset($_POST['tban_search'])) ?
                    "AND `users`.`username` = '".addslashes($_POST['name'])."'" : "ORDER BY `users`.`username` LIMIT ".$min.", ".$number)))) {
                throw new Exception('There was an error trying to fetch data on tavern bans!');
            }
            elseif($banned === '0 rows') {
                throw new Exception('There are no active tavern bans within the section that was uploaded!');
            }

            for ($i = 0, $size = count($banned); $i < $size; $i++) {
                $ban_record = self::get_latest_record($banned[$i]['id']);
                $banned[$i] = array_merge($banned[$i], $ban_record[0]);
            }

            // Show currently banned users
            tableParser::show_list('log', "Currently Tavern Bans",
                $banned,
                array(
                    'username' => "Username",
                    'moderator' => "Moderator",
                    'duration' => "Duration",
                    'reason' => "Reason",
                ),
                array(
                    array(
                        "name" => "Unban",
                        "act" => "unban",
                        "uid" => "table.id"
                    )
                ),
                true, // Send directly to contentLoad
                true,
                false,
                false, // No sorting on columns
                false, // No pretty options
                array(
                    array(
                        "infoText" => "Search by username",
                        "postField" => "name",
                        "postIdentifier" => "tban_search",
                        "inputName" => "Search User",
                        "href" => "?id=" . $_GET['id']
                    )
                ), // No top search field
                "For more details, check the user's details"
            );
        }

        // Unban functions
        // ===============
        protected function confirm_unban() {

            // Record
            $ban_record = self::get_latest_record($_REQUEST['uid']);

            // Create the input form
            $GLOBALS['page']->UserInput("Reason must be entered for unbanning", "Undo Tavern Ban",
                array(
                    // Reason
                    array(
                        "infoText" => "Reason",
                        "inputFieldName" => "override_reason",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // Pass on hidden data
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "unban_uid",
                        "inputFieldValue" => $_REQUEST['uid']
                    ),
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "unban_time",
                        "inputFieldValue" => $ban_record[0]['time']
                    )
                ),
                array(
                    "href" => "?id=" . $_REQUEST['id'] . "&act=" . $_GET['act'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                false ,
                "unbanForm"
            );
        }

        protected function do_unban() {

            if (!isset($_POST['unban_uid'])) {
                throw new Exception('The User ID information is missing!');
            }
            elseif(!ctype_digit($_POST['unban_uid'])) {
                throw new exception('The User ID is not a numeric value!');
            }
            elseif(!isset($_POST['unban_time'])) {
                throw new Exception("The Ban Time information is missing!");
            }
            elseif(!ctype_digit($_POST['unban_time'])) {
                throw new Exception('The Ban Time is not a numeric value!');
            }
            elseif(!isset($_POST['override_reason'])) {
                throw new Exception('The Override Reason information is missing!');
            }
            elseif(strlen(functions::ws_remove($_POST['override_reason'])) < 10) {
                throw new Exception('You did not specify a valid reason for unbanning the user! '.
                    '(Minimum of 10 Characters without Spaces)');
            }

            $GLOBALS['database']->transaction_start();

            if(!($user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`, `users`.`tban_time`, `users`.`post_ban`
                FROM `users`
                    INNER JOIN `moderator_log` ON (`moderator_log`.`uid` = `users`.`id` AND `moderator_log`.`time` = '".$_POST['unban_time']."')
                WHERE `users`.`id` = '" . $_POST['unban_uid'] . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception('There was an error fetching the tavern ban data!');
            }
            elseif($user === '0 rows') {
                throw new Exception('The user does not exist within the database!');
            }
            elseif($user[0]['tban_time'] <= 0 || $user[0]['post_ban'] === '0') {
                throw new Exception('The user should not be tavern banned within the system! Possible Error in Timer or Privilege Settings!');
            }

            if($GLOBALS['database']->execute_query("UPDATE `moderator_log`
                    INNER JOIN `users` ON (`users`.`id` = `moderator_log`.`uid`)
                SET `moderator_log`.`override_reason` = '" . functions::store_content($_POST['override_reason']) . "',
                    `moderator_log`.`override_by` = '" . $GLOBALS['page']->user[0]['username'] . "',
                    `users`.`post_ban` = '0',
                    `users`.`tban_time` = '0'
                WHERE `moderator_log`.`uid` = '".$user[0]['id']."' AND `moderator_log`.`time` = '".$_POST['unban_time']."'") === false) {
                throw new Exception('There was an error trying to unban the specified user!');
            }

            // Show message
            $GLOBALS['page']->Message('You have unbanned ' . $user[0]['username']. ' from the tavern!', 'Tavern Ban System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }

        // Convenience functions
        // =====================
        private function get_latest_record($uid) {

            if(!($ban_record = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.`time`, `moderator_log`.`uid`,
                `moderator_log`.`username`, `moderator_log`.`duration`, `moderator_log`.`moderator`, `moderator_log`.`action`,
                `moderator_log`.`reason`, `moderator_log`.`message`
                FROM `moderator_log`
                WHERE `moderator_log`.`uid` = '" . $uid . "' AND `moderator_log`.`action` IN ('tavern-ban')
                    ORDER BY `moderator_log`.`id` DESC LIMIT 1"))) {
                throw new Exception('An error occurred trying to obtain the latest record action for the user!');
            }
            elseif($ban_record === '0 rows') {
                throw new Exception('The suggested record action could not be found.');
            }

            return $ban_record;
        }
    }

    new module();