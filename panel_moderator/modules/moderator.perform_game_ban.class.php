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
                // Choose Between Ban Form or Ban Submission
                (!isset($_POST['Submit'])) ? self::ban_form() : self::give_ban();
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
        private function ban_form() {

            $options = $GLOBALS['page']->banLengths;
            if($GLOBALS['userdata'][0]['user_rank'] !== 'Admin') {
                unset($options['permanent']);
            }

            // Create the input form
            $GLOBALS['page']->UserInput("All fields must be filled out to ban user. Use responsibly!", "Game Ban",
                array(
                    // Username
                    array(
                        "infoText" => "Username",
                        "inputFieldName" => "ban_username",
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
                    // Reason
                    array(
                        "infoText" => "Reason",
                        "inputFieldName" => "ban_reason",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // Message
                    array(
                        "infoText" => "Message<br>",
                        "inputFieldName" => "ban_message",
                        "type" => "textarea",
                        "inputFieldValue" => "",
                        "maxlength" => 2500
                    ),
                ),
                array(
                    "href" => "?id=".$_REQUEST['id'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"),
                false ,
                "trainingForm"
            );
        }

        //	Ban user:
        private function give_ban() {

            if (!isset($_POST['ban_username']) || functions::ws_remove($_POST['ban_username']) === '') {
                throw new Exception("You did not specify a username!");
            }
            elseif (!isset($_POST['ban_message']) || functions::ws_remove($_POST['ban_message']) === '') {
                throw new Exception("You did not specify a message to send to the user!");
            }
            elseif(strlen(functions::ws_remove($_POST['ban_message'])) < 10) {
                throw new Exception('You did not specify a valid message to the user! '.
                    '(Minimum of 10 Characters without Spaces)');
            }
            elseif (!isset($_POST['ban_reason']) || functions::ws_remove($_POST['ban_reason']) === '') {
                throw new Exception("You did not specify a reason!");
            }

            $GLOBALS['database']->transaction_start();

            if(!($user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`village`, `users`.`id`,
                `users`.`ban_time`, `users`.`perm_ban`, `users_statistics`.`user_rank`, `villages`.`leader`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                WHERE `users`.`username` = '" . $_POST['ban_username'] . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception('There was an issue trying to obtain the user!');
            }
            elseif ($user === "0 rows") {
                throw new Exception("This user does not exist!");
            }

            if (((int)$user[0]['ban_time'] !== 0) || ($user[0]['ban_time'] >= time()) || $user[0]['perm_ban'] === '1') {
                throw new Exception("User is already banned");
            }

            if (!in_array($user[0]['user_rank'], array('Member', 'Paid', 'Event', 'EventMod'), true)) {
                $canBan = false;
                if(
                    $GLOBALS['userdata'][0]['user_rank'] == "Admin" ||
                    ($GLOBALS['userdata'][0]['user_rank'] == "Supermod" && $user[0]['user_rank'] !== "Admin") ||
                    ($GLOBALS['userdata'][0]['user_rank'] == "Moderator" && $user[0]['user_rank'] !== "Supermod" && $user[0]['user_rank'] !== "Admin")
                ){
                    $canBan = true;
                }

                // Don't allow banning
                if( $canBan == false ){
                    throw new Exception("You cannot ban administrators, supermods or moderators!");
                }
            }

            //    Determine the bantime:
            $banName = $GLOBALS['page']->banLengths[$_POST['game_ban_time']];
            $bantime = $GLOBALS['page']->calcBanTime($banName);

            if((int)$bantime === 0) {
                throw new Exception("Invalid ban time specified: ".$_POST['game_ban_time']);
            }

            //    Ban the user
            if ($banName === 'Permanent' && $user[0]['perm_ban'] !== '1') {
                $PermBan = '`users`.`perm_ban` = "1"';
            }

            if ($user[0]['leader'] === $user[0]['username']) {
                $LdrRmv = "`villages`.`leader` = '".Data::$VILLAGE_KAGENAMES[$user[0]['village']]."'";
            }

            if($GLOBALS['database']->execute_query("UPDATE `users`".(isset($LdrRmv) ? ', `villages` ' : '')."
                SET `users`.`login_id` = DEFAULT,
                    `users`.`ban_time` = '" . $bantime . "',
                    `users`.`logout_timer` = UNIX_TIMESTAMP()".((isset($PermBan) || isset($LdrRmv)) ? ', ' : ' ')."
                    ".(isset($PermBan) ? $PermBan.(isset($LdrRmv) ? ', ' : '') : '')."
                    ".(isset($LdrRmv) ? $LdrRmv : '')."
                WHERE `users`.`id` = '" . $user[0]['id'] . "'
                    ".(isset($LdrRmv) ? 'AND `villages`.`leader` = "'.$user[0]['username'].'"' : 'LIMIT 1')) === false) {
                throw new Exception('There was an issue banning the user!');
            }

            //    Log the ban:
            $GLOBALS['page']->log_moderator_action(
                time(),
                $user[0]['id'],
                $user[0]['username'],
                $banName,
                $GLOBALS['page']->user[0]['username'],
                "ban",
                $_POST['ban_reason'],
                $_POST['ban_message']
            );

            //    Output success:
            $GLOBALS['page']->Message($user[0]['username'] . ' has been banned.', 'Ban System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }
    }

    new module();