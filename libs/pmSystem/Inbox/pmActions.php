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

    require_once(Data::$absSvrPath.'/vendor/autoload.php');
    require_once(Data::$absSvrPath.'/libs/pmSystem/Outbox/pmOutboxActions.php');
    require_once(Data::$absSvrPath.'/libs/mail.inc.php');
    require_once(Data::$absSvrPath.'/libs/notificationSystem/notificationSystem.php');

    class pmActions extends pmOutboxActions {

        //  Reset the Read pms
        public function resetReadNotification() {
            // Start Transactions
            $GLOBALS['database']->transaction_start();

            $GLOBALS['database']->execute_query('UPDATE `users` SET `new_pm` = (SELECT count(*) FROM `users_pm` WHERE `receiver_uid` = '.$GLOBALS['userdata'][0]['id'].' AND `read` = \'no\') WHERE `users`.`id` = '.$GLOBALS['userdata'][0]['id'].' LIMIT 1');

            // Grab Necessary User Information
            if(!($user = $GLOBALS['database']->fetch_data('SELECT `users`.`new_pm`, `users`.`id`
                FROM `users` WHERE `users`.`id` = '.$GLOBALS['userdata'][0]['id'].' LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an issue trying to obtain PM Notification Data!');
            }

            if($user === '0 rows') { throw new Exception('There was an issue obtaining user data!'); }

            if ($user[0]['new_pm'] != 0)
            {
                if($user[0]['new_pm'] != 1)
                    $plurality = 's';
                else
                    $plurality = '';

                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=3','You have '.$user[0]['new_pm'].' unread PM'.$plurality)));
            }

            // Commit Transaction
            $GLOBALS['database']->transaction_commit();
        }

        // Mark All PMs Read
        public function mark_PM_read() {
            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Obtain Necessary PM Data from User
            if(!($pm_data = $GLOBALS['database']->fetch_data("SELECT `users_pm`.`pm_id`, `users_pm`.`read` FROM `users_pm`
                WHERE `users_pm`.`receiver_uid` = ".$GLOBALS['userdata'][0]['id']." AND `read` = 'no' FOR UPDATE"))) {
                throw new Exception("You don't have any unread PMs available!");
            }

            if($pm_data === '0 rows') { throw new Exception('All PMs within your Inbox have been indicated to have been read!'); }

            // Formulate String for Target PM IDs
            $targetList = array();
            for($i = 0, $size = count($pm_data); $i < $size; $i++) { $targetList[] = $pm_data[$i]['pm_id']; }

            if(!empty($targetList)) { // If there was Unread PMs, Update the PMs
                if(($GLOBALS['database']->execute_query('UPDATE `users_pm`
                    SET `users_pm`.`read` = "yes"
                    WHERE `users_pm`.`pm_id` IN (\''.implode("', '", $targetList).'\') LIMIT '.count($targetList))) === false) {
                    throw new Exception('There was an error updating the unread messages');
                }
            }

            // Commit transaction
            $GLOBALS['database']->transaction_commit();
        }

        // Function for getting a PM related to the user
        public function get_user_pm($pmID) {
            // Check ID
            if(!ctype_digit($pmID)) { throw new Exception('Message ID is corrupt'); }

            // Return result from database
            if(!($dbResult = $GLOBALS['database']->fetch_data('SELECT `users_pm`.`sender_uid`, `users_pm`.`time`,
                `users_pm`.`message`, `users_pm`.`subject`, `users_pm`.`read`, `users`.`username` AS `sender`,
                `users_statistics`.`user_rank` AS `sender_rank`, `users_statistics`.`federal_level` AS `sender_fed_lv`
                FROM `users_pm`
                    LEFT JOIN `users` ON (`users`.`id` = `users_pm`.`sender_uid`)
                    LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_pm`.`sender_uid`)
                WHERE `users_pm`.`pm_id` = '.$pmID.' AND `users_pm`.`receiver_uid` = '.$GLOBALS['userdata'][0]['id'].' LIMIT 1'))) {
                throw new Exception("There was an error finding the PM in the database");
            }

            // Check to see if User exists
            if($dbResult === '0 rows') { throw new Exception("The message doesn't exist!"); }

            // Convert entities back to special characters
            $dbResult[0]['subject'] = htmlspecialchars_decode($dbResult[0]['subject']);

            // Return result
            return $dbResult;
        }

        // New pm send
        public function new_pm_send() {
            self::do_send_message($_REQUEST['to'], $_REQUEST['message'], $_REQUEST['subject']);
        }

        // Reply send
        public function reply_send() {
            // Get message
            $message_data = self::get_user_pm($_REQUEST['pmid']);

            // Fix up subject
            $subject = (preg_match("/^RE:.+/", $message_data[0]['subject'])) ? $message_data[0]['subject'] : "RE: ".$message_data[0]['subject'];

            // Send message to user
            self::do_send_message($message_data[0]['sender'], $_REQUEST['message'], $subject, true);
        }

        // The function actively sending a pm
        public function do_send_message($receiverName , $message, $subject, $isReply = false) {
            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Remove Unnecessary Content
            $subject = functions::store_content($subject);
            $message = functions::store_content($message);

            // Get recipient
            $receiverName = functions::ws_remove($receiverName);
            if($receiverName === '') {
                throw new Exception('You must enter a Username to send a PM!');
            }

            // Re-Check if Message isn't Empty
            if(functions::ws_remove($subject) === '') {
                throw new Exception('You must enter a Subject to send a PM!');
            }

            // Check if Message was entered
            if(functions::ws_remove($message) === '') {
                throw new Exception('You must enter a Message to send a PM!');
            }

            // Get information
            if(isset($receiverName)) {

                // Obtain Necessary User Information
                if(!($user = $GLOBALS['database']->fetch_data('SELECT `users`.`username`,
                    `users`.`id`, `users`.`mail`, `users`.`new_pm`,
                    `users_statistics`.`user_rank`, `users_statistics`.`federal_level`,
                    `sender`.`user_rank` AS `sender_user_rank`,
                    `sender`.`uid` AS `sender_uid`,
                    `users_preferences`.`pm_block`, `users_preferences`.`pm_setting`, `users_preferences`.`pm_by_email`,
                    `users_preferences`.`pm_whitelist`, `users_preferences`.`pm_blacklist`,
                    COUNT(`users_pm`.`pm_id`) AS `target_user_inbox`
                    FROM `users`
                        LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                        LEFT JOIN `users_statistics` AS `sender` ON (`sender`.`uid` = '.$GLOBALS['userdata'][0]['id'].')
                        LEFT JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                        LEFT JOIN `users_pm` ON (`users_pm`.`receiver_uid` = `users`.`id`)
                    WHERE `users`.`username` = "'.$receiverName.'" LIMIT 1 FOR UPDATE')))
                {
                    throw new Exception("Error retrieving information on the user you're trying to message");
                }
            }
            else {
                throw new Exception("You must specify who you're trying to message");
            }

            // Check if User exists
            if($user === '0 rows' || $user[0]['id'] === null) {
                throw new Exception("Apparently, this user doesn't exist within the system!");
            }

            // Check if Message fits within Constraints
            if((strlen($message) < 5) || (strlen($message) > 1000)) {
                if($user[0]['sender_user_rank'] !== 'Admin') {
                    throw new Exception('The Message must be between 5 - 1000 characters long!');
                }
            }

            // Check to see if PMs Block is Active
            if($user[0]['pm_block'] === '1') {
                if(in_array($user[0]['sender_user_rank'], array('Member', 'Paid'), true)) {
                    throw new Exception('The selected user has disabled their Inbox!');
                }
            }

            // Every other User Rank gets a Bigger Inbox Size
            $max_inbox = ($user[0]['user_rank'] !== 'Member') ? 75 : 50;
            if( $user[0]['federal_level'] == "Gold" ){
                $max_inbox += 25;
            }

            // Increase for staff
            if(in_array($user[0]['user_rank'], Data::$STAFF_RANKS, true)) {
                $max_inbox = 500;
            }

            // Check Target User's Inbox Amount
            if($user[0]['target_user_inbox'] >= $max_inbox) { throw new Exception("The User's Inbox is currently full!"); }

            // Verify PM Setting is Valid Data
            if($user[0]['pm_setting'] === null) { throw new Exception("There was an error receiving the PM settings!"); }

            // Check PM Settings
            if($user[0]['pm_setting'] !== 'off') {
                // Member/Paid/Event User's Constraint
                if(in_array($user[0]['sender_user_rank'], array('Member', 'Paid', 'Event'), true)) {
                    if($user[0]['pm_setting'] === 'white_only') { // If Whitelist is Active
                        if(!stristr($user[0]['pm_whitelist'], ';'.$user[0]['sender_uid'].';')) {
                            throw new Exception("You are currently not on this User's Whitelist!");
                        }
                    }
                    elseif($user[0]['pm_setting'] === 'block_black' || $user[0]['pm_setting'] === 'block_black_pm') { // If Blacklist is Active
                        if(stristr($user[0]['pm_blacklist'], ';'.$user[0]['sender_uid'].';')) {
                            throw new Exception("You are currently on this User's Blacklist!");
                        }
                    }
                }
            }

            // If Someone tries to send an "Official Warning!"
            if (stristr(functions::ws_remove(strtolower($subject)), 'officialwarning')) {
                if($isReply !== true) {
                    if(!in_array($user[0]['sender_user_rank'], Data::$MOD_STAFF_RANKS, true)) {
                        $subject = 'Non-Authorized Warning!';
                        $message = 'This Message is NOT an Official Warning.
                            Please disregard this message and report the user if this kind of behavior persists.
                            ~TNR Staff~';
                    }
                }
            }


            // Insert PM into User PM Table
            if($GLOBALS['database']->execute_query('INSERT INTO
                `users_pm`
                    (`sender_uid` ,`receiver_uid` ,`time` ,`message` ,`subject` ,`read`)
                VALUES
                    ('.$GLOBALS['userdata'][0]['id'].', '.$user[0]['id'].', '.$GLOBALS['user']->load_time.', "'.$message.'",
                        "'.$subject.'", "no")') === false) {
                throw new Exception('There was an error trying to send the PM to the User!');
            }

            // Check PM by Email preference
            if (isset($user[0]['pm_by_email']) && $user[0]['pm_by_email']) {

                // Send email:
                $mail = new Mail();

                $recipient = $user[0]['mail'];
                $subject = $subject . ' (New PM on TheNinja-RPG)';
                $emailBodyHtml = "One new message from <b>". $GLOBALS['userdata'][0]['username'] ."</b> in your inbox.<br><br>";
                $emailBodyHtml .= "<p>" . str_replace("\n", "<br>", $message) . "</p>";
                $emailBodyHtml .= '<br><br>
                        You can answer on this message in your <a href="http://www.theninja-rpg.com?id=3">inbox</a>. <br>
                        <br>';


                $mail->Send($recipient, $subject, $emailBodyHtml, $emailBodyHtml);

            }

            parent::saveOutboxMessage($GLOBALS['database']->get_inserted_id(), $GLOBALS['userdata'][0]['id'],
                $user[0]['id'], $subject, $message);

            if($user[0]['target_user_inbox'] == $max_inbox - 1) {
                $users_notifications = new NotificationSystem('', $user[0]['id']);

                $users_notifications->addNotification(array(
                                                            'id' => 1,
                                                            'duration' => 'none',
                                                            'text' => array('?id=3','Your inbox is currently full! Be sure to make room, so other people can message you!'),
                                                            'dismiss' => 'yes'
                                                        ));

                $users_notifications->recordNotifications();
            }

            // Set notification
            // Reply PM has been read, Update PM Notification
            if($GLOBALS['database']->execute_query('UPDATE `users` SET `users`.`new_pm` = `users`.`new_pm` + 1
                WHERE `users`.`id` = '.$user[0]['id'].' LIMIT 1') === false) {
                throw new Exception("There was an error trying to notify the User's Inbox Reply!");
            }

            if($receiverName != $GLOBALS['userdata'][0]['username'])
            {
                $GLOBALS['Events']->acceptEvent('pm_send', array('data'=>preg_replace('/([^\\\\])(\\\\{2}\')/m', '$1\\\\\\\\\\\'', str_replace('\'','\\\'',strip_tags($message))), 'context'=>$GLOBALS['userdata'][0]['username']));
            }

            // Commit transaction
            $GLOBALS['database']->transaction_commit();


        }

        // Delete a single PM
        public function delete_single_pm() {
            // Delete the pm
            self::do_delete_pms(array($_REQUEST['pmid']));
        }

        // Delete a list of PMs
        public function delete_list_pms() {
            // The list of PMs
            $pmList = array();

            // Check that list is specified
            if(!isset($_REQUEST['pmIDs']) || empty($_REQUEST['pmIDs']) || count($_REQUEST['pmIDs']) === 0) {
                throw new Exception('No PMs selected for deletion');
            }

            // Add ids to list
            foreach($_REQUEST['pmIDs'] as $value) {
                if(!ctype_digit($value)) { throw new Exception('Invalid PM id: '.$value ); }
                else { $pmList[] = $value; }
            }

            // Do delete the pm
            self::do_delete_pms($pmList);
        }

        // Clear the inbox
        public function delete_inbox() {
            // Do deletion
            self::do_delete_pms("ALL");
        }

        // A function for deleting PMs
        public function do_delete_pms($pmList) {
            // Do delete
            $GLOBALS['database']->transaction_start();

            // Selector of deletion
            $selector = ($pmList === "ALL") ? '' : 'AND `users_pm`.`pm_id` IN (\''.implode("','", $pmList).'\') LIMIT '.count($pmList).' ';

            // Select the pms in question
            if(!($pm_data = $GLOBALS['database']->fetch_data('SELECT `users_pm`.`pm_id` FROM `users_pm`
                WHERE `users_pm`.`receiver_uid` = '.$GLOBALS['userdata'][0]['id'].' '.$selector.' FOR UPDATE'))) {
                throw new Exception('Could not retrieve any PMs to delete');
            }

            // Nothing found
            if($pm_data === '0 rows') { throw new Exception('Could not find any PMs to delete'); }

            // Get the retrieved ids (those are the ones that actually belong to the user
            $deleteList = array();
            foreach($pm_data as $pm) { $deleteList[] = $pm['pm_id']; }

            // Check that some were found
            if(empty($deleteList)) { throw new Exception("No PMs belonging to you were found"); }

            // Do delete
            if(($GLOBALS['database']->execute_query('DELETE FROM `users_pm`
                WHERE `users_pm`.`receiver_uid` = '.$GLOBALS['userdata'][0]['id'].'
                    AND `users_pm`.`pm_id` IN (\''.implode("', '", $deleteList).'\') LIMIT '.count($deleteList))) === false) {
                throw new Exception('There was an error deleting the PMs');
            }

            // Commit the transaction
            $GLOBALS['database']->transaction_commit();
        }
    }