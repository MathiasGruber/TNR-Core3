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

        public function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {

                if (isset($_GET['act'])) {

                    switch($_GET['act']) {
                        case('unban'): (!isset($_POST['Submit'])) ? self::confirm_unban() : self::do_unban(); break;
                        case('reduce'): (!isset($_POST['Submit'])) ? self::confirm_reduce() : self::do_change(); break;
                        case('extend'): (!isset($_POST['Submit'])) ? self::confirm_extend() : self::do_change(); break;
                        default: self::main_page(); break;
                    }

                }
                else {
                    self::main_page();
                }

            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), "Game Ban System", 'id='.$_GET['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }

        // Main page
        // =========
        private function main_page() {

            $perm_bans = "`users`.`ban_time` > UNIX_TIMESTAMP() AND `users`.`perm_ban` != '1'";

            if(isset($_GET['act']) && $_GET['act'] === 'showPermaBans') {
                if($GLOBALS['userdata'][0]['user_rank'] === 'Admin') {
                    $perm_bans = "`users`.`ban_time` = '1337' AND `users`.`perm_ban` = '1'";
                }
            }
            else {
                if($GLOBALS['userdata'][0]['user_rank'] === 'Admin') {
                    require_once(Data::$absSvrPath . "/ajaxLibs/staticLib/markitup.bbcode-parser.php");
                    $showPermBans = BBCode2Html("[url=".Data::$domainName."/panel_moderator/?id=".$_GET['id']."&act=showPermaBans]Show Permanent Bans[/url]");
                }
            }

            // Get currently banned users
            if(!($banned = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`, `users`.`ban_time`
                FROM `users`
                WHERE {$perm_bans} ORDER BY `users`.`username`"))) {
                throw new Exception('An error occurred trying to obtain banned users!');
            }

            // Fix up data
            if ($banned !== '0 rows') {
                for ($i = 0, $size = count($banned); $i < $size; $i++) {
                    $ban_record = self::get_latest_record($banned[$i]['id']);
                    $banned[$i] = array_merge($banned[$i], $ban_record[0]);
                }
            }

            // Show currently banned users
            tableParser::show_list('log', "Currently Active Game Bans",
                $banned,
                array(
                    'username' => "Username",
                    'moderator' => "Moderator",
                    'duration' => "Duration",
                    'reason' => "Reason"
                ),
                array(
                    array(
                        "name" => "Unban",
                        "act" => "unban",
                        "uid" => "table.id"
                    ),
                    array(
                        "name" => "Reduce",
                        "act" => "reduce",
                        "uid" => "table.id"
                    ),
                    array(
                        "name" => "Extend",
                        "act" => "extend",
                        "uid" => "table.id"
                    )
                ),
                true, // Send directly to contentLoad
                false,
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                "For more details, check the user's details! ".(isset($showPermBans) ? $showPermBans : '')
            );

        }

        // Unban functions
        // ===============
        protected function confirm_unban() {

            // Record
            $ban_record = self::get_latest_record($_REQUEST['uid']);

            // Create the input form
            $GLOBALS['page']->UserInput("Reason must be entered for unbanning", "Undo Game Ban",
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
                false,
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
                throw new Exception('You did not specify a valid message for unbanning the user! '.
                    '(Minimum of 10 Characters without Spaces)');
            }

            $GLOBALS['database']->transaction_start();

            if(!($user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`, `users`.`ban_time`, `users`.`perm_ban`
                FROM `users`
                    INNER JOIN `moderator_log` ON (`moderator_log`.`uid` = `users`.`id` AND `moderator_log`.`time` = '" . $_POST['unban_time'] . "')
                WHERE `users`.`id` = '" . $_POST['unban_uid'] . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception('There was an error trying to receiver user information!');
            }
            elseif($user === "0 rows") {
                throw new Exception('This user does not exist within the database!');
            }
            elseif($user[0]['ban_time'] <= 0) {
                throw new Exception("This user is not currently banned");
            }

            // Update DB
            if($GLOBALS['database']->execute_query("UPDATE `moderator_log`
                    INNER JOIN `users` ON (`users`.`id` = `moderator_log`.`uid`)
                SET `moderator_log`.`override_reason` = '" . functions::store_content($_POST['override_reason']) . "',
                    `moderator_log`.`override_by` = '" . $GLOBALS['page']->user[0]['username'] . "',
                    `users`.`ban_time` = 0 ".(($user[0]['perm_ban'] === '1') ? ", `users`.`perm_ban` = '0' " : '')."
                WHERE `moderator_log`.`uid` = '" . $user[0]['id'] . "' AND
                    `moderator_log`.`time` = '" . $_POST['unban_time'] . "'") === false) {
                throw new Exception('The process of unbanning the user failed!');
            }

            // Show message
            $GLOBALS['page']->Message('You have unbanned ' . $user[0]['username'], 'Ban System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();

        }

        // Reduce / Extend ban functions
        // Reductions & Extensions use the same initial ban time to group together
        // All actions have separate and unique action IDs associated to them
        // ====================
        protected function confirm_reduce() {

            // Record
            $ban_record = self::get_latest_record($_REQUEST['uid']);

            // Options for reduction. Only show the ones below current one
            $options = array();

            foreach($GLOBALS['page']->banLengths as $key => $value) {
                if($ban_record[0]['duration'] === $value) { break; }
                $options[$key] = $value;
            }

            // Check that it's possible to reduce sentence
            if(empty($options)) {
                 throw new Exception("No shorter duration ban is possible");
            }

            // Create the input form
            $GLOBALS['page']->UserInput("Reason must be entered for reducing ban time", "Reduce Game Ban",
                array(
                    // Reason
                    array(
                        "infoText" => "Reason to",
                        "inputFieldName" => "override_reason",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // A select box
                    array(
                        "infoText" => "Length",
                        "inputFieldName" => "game_ban_time",
                        "type" => "select",
                        "inputFieldValue" => $options
                    ),
                    // Message
                    array(
                        "infoText" => "Message<br>",
                        "inputFieldName" => "override_message",
                        "type" => "textarea",
                        "inputFieldValue" => ""
                    ),
                    // Pass on hidden data
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "change_uid",
                        "inputFieldValue" => $_REQUEST['uid']
                    ),
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "change_time",
                        "inputFieldValue" => $ban_record[0]['time']
                    ),
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "change_type",
                        "inputFieldValue" => "Reduction"
                    )
                ),
                array(
                    "href" => "?id=" . $_REQUEST['id'] . "&act=" . $_GET['act'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                false,
                "reductionForm"
            );

        }

        protected function confirm_extend() {

            // Record
            $ban_record = self::get_latest_record($_REQUEST['uid']);

            // Options for reduction. Only show the ones below current one
            $options = array();
            $start = false;
            foreach($GLOBALS['page']->banLengths as $key => $value) {
                if( $start == true ){
                    $options[$key] = $value;
                }
                if( $ban_record[0]['duration'] == $value ){
                    $start = true;
                }
            }

            if($GLOBALS['userdata'][0]['user_rank'] !== 'Admin') {
                unset($options['permanent']);
            }

            // Check that it's possible to reduce sentence
            if(empty($options)) {
                 throw new Exception("It's not possible for you to extend this ban any further");
            }

            // Create the input form
            $GLOBALS['page']->UserInput("Reason must be entered for extending ban time", "Extend Game Ban",
                array(
                    // Reason
                    array(
                        "infoText" => "Reason to",
                        "inputFieldName" => "override_reason",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // A select box
                    array(
                        "infoText" => "Length",
                        "inputFieldName" => "game_ban_time",
                        "type" => "select",
                        "inputFieldValue" => $options
                    ),
                    // Message
                    array(
                        "infoText" => "Message<br>",
                        "inputFieldName" => "override_message",
                        "type" => "textarea",
                        "inputFieldValue" => ""
                    ),
                    // Pass on hidden data
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "change_uid",
                        "inputFieldValue" => $_REQUEST['uid']
                    ),
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "change_time",
                        "inputFieldValue" => $ban_record[0]['time']
                    ),
                    array(
                        "type" => "hidden",
                        "inputFieldName" => "change_type",
                        "inputFieldValue" => "Extension"
                    )
                ),
                array(
                    "href" => "?id=" . $_REQUEST['id'] . "&act=" . $_GET['act'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                false,
                "extensionForm"
            );

        }

        protected function do_change() {

            if (!isset($_POST['change_uid']) || (functions::ws_remove($_POST['change_uid']) === '')) {
                throw new Exception("An error occurring trying to obtain User's Record Data!");
            }
            elseif(!isset($_POST['change_type'])|| (functions::ws_remove($_POST['change_type']) === '')) {
                throw new Exception("There was no Reduction or Extension type set!");
            }
            elseif(!isset($_POST['change_time']) || (functions::ws_remove($_POST['change_time']) === '')) {
                throw new Exception("There was no ban change time set!");
            }
            elseif(!isset($_POST['game_ban_time']) || (functions::ws_remove($_POST['game_ban_time']) === '')) {
                throw new Exception("There was no ban time type determined!");
            }
            elseif(!isset($_POST['override_reason']) || (functions::ws_remove($_POST['override_reason']) === '')) {
                throw new Exception("There was no override reason set!");
            }
            elseif(!isset($_POST['override_message']) || (functions::ws_remove($_POST['override_message']) === '')) {
                throw new Exception("There was no override message set!");
            }
            elseif(strlen(functions::ws_remove($_POST['override_message'])) < 10) {
                throw new Exception('You did not specify a valid message to the user for a '.$_POST['change_type'].'! '.
                    '(Minimum of 10 Characters without Spaces)');
            }
            elseif($_POST['change_type'] === 'Reduction') {
                $this->doReduce($_POST['change_uid'], $_POST['change_time'], $_POST['change_type'],
                    $_POST['game_ban_time'], $_POST['override_reason'], $_POST['override_message']);
            }
            elseif($_POST['change_type'] === 'Extension') {
                $this->doExtend($_POST['change_uid'], $_POST['change_time'], $_POST['change_type'],
                    $_POST['game_ban_time'], $_POST['override_reason'], $_POST['override_message']);
            }
            else {
                 throw new Exception("Could not figure out what you're trying to do with this ban");
            }
        }

        protected function doReduce($uid = NULL, $time = NULL, $type = NULL, $ban_time = NULL, $reason = NULL, $message = NULL) {

            $GLOBALS['database']->transaction_start();

            if(!($user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`, `users`.`ban_time`, `users`.`perm_ban`,
                `moderator_log`.`time`, `moderator_log`.`reason`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `moderator_log` ON (`moderator_log`.`uid` = `users`.`id` AND `moderator_log`.`time` = '".$time."')
                WHERE `users`.`id` = '" . $uid . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception("An error occurring trying to obtain User's Record Data!");
            }
            elseif ($user === "0 rows") {
                throw new Exception("This user does not exist!");
            }
            elseif ($user[0]['ban_time'] <= time() && $user[0]['ban_time'] !== '1337') {
                throw new Exception("User is no longer or hasn't been banned within the system!");
            }

            $options = $GLOBALS['page']->banLengths;
            unset($options['permanent']);

            // Determine the bantime
            $newbantime = ($bantime = $GLOBALS['page']->calcBanTime($options[$ban_time])) ? ($time + ($bantime - time())) : 0;

            if ($newbantime <= 0) {
                throw new Exception('The ban regarding ' . $user[0]['username'] .
                    ' (UID: ' . $user[0]['id'] . ') has failed due to a faulty reduction choice!');
            }

            if($user[0]['perm_ban'] === '1') {
                $removePermBan = "`users`.`perm_ban` = '0'";
            }

            //    Log the ban:
            $GLOBALS['page']->log_moderator_action(
                $user[0]['time'],
                $user[0]['id'],
                $user[0]['username'],
                $options[$ban_time],
                $GLOBALS['page']->user[0]['username'],
                $type,
                $reason,
                $message
            );

            // Update the user
            if($GLOBALS['database']->execute_query("UPDATE `users`
                SET `users`.`ban_time` = '" . $newbantime . "', `users`.`logout_timer` = UNIX_TIMESTAMP()"
                    .((isset($removePermBan)) ? ", ".$removePermBan : "")."
                WHERE `users`.`id` = '" . $user[0]['id'] . "' LIMIT 1") === false) {
                throw new Exception('There was an issue trying to reduce the current ban!');
            }

            // Output success:
            $GLOBALS['page']->Message('The ban regarding ' . $user[0]['username'] .
                ' (UID: ' . $user[0]['id'] . ') has been changed to a ' .
                $options[$ban_time] . ' ban!', 'Ban System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();

        }

        protected function doExtend($uid = NULL, $time = NULL, $type = NULL, $ban_time = NULL, $reason = NULL, $message = NULL) {

            $GLOBALS['database']->transaction_start();

            if(!($user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`, `users`.`ban_time`,
                `moderator_log`.`time`, `moderator_log`.`reason`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `moderator_log` ON (`moderator_log`.`uid` = `users`.`id` AND `moderator_log`.`time` = '".$time."')
                WHERE `users`.`id` = '" . $uid . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception("An error occurring trying to obtain User's Record Data!");
            }
            elseif ($user === "0 rows") {
                throw new Exception("This user does not exist!");
            }
            elseif ($user[0]['ban_time'] <= time()) {
                throw new Exception("User is no longer or hasn't been banned within the system!");
            }

            $options = $GLOBALS['page']->banLengths;

            // Determine the bantime
            if($ban_time !== 'permanent') {

                $newbantime = ($bantime = $GLOBALS['page']->calcBanTime($options[$ban_time])) ? ($time + ($bantime - time())) : 0;

                if ($newbantime <= 0) {
                    throw new Exception('The ban regarding ' . $user[0]['username'] .
                        ' (UID: ' . $user[0]['id'] . ') has failed due to a faulty reduction choice!');
                }

                //    Log the ban:
                $GLOBALS['page']->log_moderator_action(
                    $user[0]['time'],
                    $user[0]['id'],
                    $user[0]['username'],
                    $options[$ban_time],
                    $GLOBALS['page']->user[0]['username'],
                    $type,
                    $reason,
                    $message
                );

                // Update the user
                if($GLOBALS['database']->execute_query("UPDATE `users`
                    SET `users`.`ban_time` = '" . $newbantime . "', `users`.`logout_timer` = UNIX_TIMESTAMP()
                    WHERE `users`.`id` = '" . $user[0]['id'] . "' LIMIT 1") === false) {
                    throw new Exception('There was an issue trying to reduce the current ban!');
                }

                // Output success:
                $GLOBALS['page']->Message('The ban regarding ' . $user[0]['username'] .
                    ' (UID: ' . $user[0]['id'] . ') has been changed to a ' .
                    $options[$ban_time] . ' ban!', 'Ban System', 'id=' . $_GET['id']);

            }
            else {

                //    Log the ban:
                $GLOBALS['page']->log_moderator_action(
                    $user[0]['time'],
                    $user[0]['id'],
                    $user[0]['username'],
                    $options[$ban_time],
                    $GLOBALS['page']->user[0]['username'],
                    $type,
                    $reason,
                    $message
                );


                // Update the user
                if($GLOBALS['database']->execute_query("UPDATE `users`
                    SET `users`.`ban_time` = '1337',
                        `users`.`logout_timer` = UNIX_TIMESTAMP(),
                        `users`.`perm_ban` = '1'
                    WHERE `users`.`id` = '" . $user[0]['id'] . "' LIMIT 1") === false) {
                    throw new Exception('There was an issue trying to extend the current ban!');
                }

                // Output success:
                $GLOBALS['page']->Message('The ban regarding ' . $user[0]['username'] .
                    ' (UID: ' . $user[0]['id'] . ') has been changed to a Permanent Ban!', 'Ban System', 'id=' . $_GET['id']);

            }

            $GLOBALS['database']->transaction_commit();

        }

        // Convenience functions
        // =====================
        private function get_latest_record($uid) {

            if(!($ban_record = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.`time`, `moderator_log`.`uid`,
                `moderator_log`.`username`, `moderator_log`.`duration`, `moderator_log`.`moderator`, `moderator_log`.`action`,
                `moderator_log`.`reason`, `moderator_log`.`message`
                FROM `moderator_log`
                WHERE `moderator_log`.`uid` = '" . $uid . "' AND `moderator_log`.`action` IN ('ban', 'reduction', 'extension')
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