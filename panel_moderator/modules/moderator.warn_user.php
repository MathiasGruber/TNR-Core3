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

    class module {
        public function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Choose Between Warning Form or Warning Submission
                (!isset($_POST['Submit'])) ? self::warning_form() : self::give_warning();
            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), "Warning System", 'id='.$_GET['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        // Main Warning Form Page
        private function warning_form() {

            // Create the input form
            $GLOBALS['page']->UserInput("Warnings will alert a user to misconduct on their part without banning them.!", "Warning System",
                array(
                    // Username
                    array(
                        "infoText" => "Username",
                        "inputFieldName" => "warn_username",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // Reason
                    array(
                        "infoText" => "Reason",
                        "inputFieldName" => "warn_reason",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // Message
                    array(
                        "infoText" => "Message<br>",
                        "inputFieldName" => "warn_message",
                        "type" => "textarea",
                        "inputFieldValue" => ""
                    ),
                ),
                array(
                    "href" => "?id=".$_REQUEST['id'] ,
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                false ,
                "warningForm"
            );
        }

        // Perform Warning Submission Action
        private function give_warning() {

            if (!isset($_POST['warn_username']) || functions::ws_remove($_POST['warn_username']) === '') { // Check If Username Filled Out
                throw new Exception('You did not specify a username');
            }
            elseif (!isset($_POST['warn_message']) || functions::ws_remove($_POST['warn_message']) === '') { // Check If Warning Message Filled Out
                throw new Exception('You did not specify a message to send to the user');
            }
            elseif (!isset($_POST['warn_reason']) || functions::ws_remove($_POST['warn_reason']) === '') { // Check If Reason Filled Out
                throw new Exception('You did not specify a reason');
            }

            $GLOBALS['database']->transaction_start();

            // Lock Targeted User For Warning
            if (!($user = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`,
                `users`.`new_pm`
                FROM `users` WHERE `users`.`username` = "' . $_POST['warn_username'] . '" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('This user does not exist within the system!');
            }
            elseif ($user === '0 rows') { // Check if User Exists
                throw new Exception('This user does not exist within the system!');
            }

            // Log The Warning Action
            $GLOBALS['page']->log_moderator_action(
                time(), // Time of Action
                $user[0]['id'], // Moderator ID
                $user[0]['username'], // Moderator Username
                "Indefinite",  // Time Duration
                $GLOBALS['page']->user[0]['username'],
                "Warning", // Moderator Action Type
                $_POST['warn_reason'], // Reason
                $_POST['warn_message'] // Message
            );

            // Send Warning Message In PM
            if (($GLOBALS['database']->execute_query("INSERT INTO `users_pm`
                    (`sender_uid`, `receiver_uid`, `time`, `message`, `subject`)
                VALUES
                    ('" . $GLOBALS['page']->user[0]['id'] . "', '" . $user[0]['id'] . "', UNIX_TIMESTAMP(),
                        '" . functions::store_content($_POST['warn_message']) . "', 'Official Warning!')")) === false) {
                throw new Exception('There was an error sending the user PM');
            }

            // Update User System Message
            if (($GLOBALS['database']->execute_query('UPDATE `users`
                SET `users`.`new_pm` = `users`.`new_pm` + 1
                WHERE `users`.`id` = ' . $user[0]['id'] . ' LIMIT 1')) === false) {
                throw new Exception('There was an error setting the warning notification');
            }

            $users_notifications = new NotificationSystem('', $user[0]['id']);

            $users_notifications->addNotification(array(
                                                        'id' => 15,
                                                        'duration' => 'none',
                                                        'text' => "You have received an OFFICIAL warning, which can be found in your inbox. Please read it as soon as possible!",
                                                        'dismiss' => 'yes'
                                                    ));

            $users_notifications->recordNotifications();

            // Output success:
            $GLOBALS['page']->Message("Your warning has been sent", 'Warning System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }
    }

    new module();