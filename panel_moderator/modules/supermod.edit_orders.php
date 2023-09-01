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

    class notes {

        public function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Choose Between Orders Form and Order Submission
                (!isset($_POST['Submit'])) ? self::order_form() : self::edit_orders();
            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), "Moderator Orders", 'id='.$_GET['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        // Main page
        protected function order_form() {

            // Get the orders
            if (!($nindo = $GLOBALS['database']->fetch_data("SELECT `user_notes`.`message`
                FROM `user_notes` WHERE `user_notes`.`user_id` = 0 LIMIT 1"))) {
                throw new Exception('There was an error when obtaining mod orders, please try again!');
            }
            elseif($nindo === '0 rows') {
                self::order_recreate();

                // Show message
                $GLOBALS['page']->Message('Moderator Orders could not be found in system, '.
                    'but it was successfully re-created!', 'Moderator Orders', 'id=' . $_GET['id']);
            }

            $GLOBALS['page']->UserInput("Write the orders for the moderator team", "Edit Moderator Orders",
                array(
                    array(
                        "infoText" => "",
                        "inputFieldName" => "orders",
                        "type" => "textarea",
                        "inputFieldValue" => $nindo[0]['message']
                    )
                ),
                array(
                    "href" => "?id=".$_GET['id'] ,
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit Orders"
                ),
                "Return"
             );
        }

        protected function edit_orders() {

            if (!isset($_POST['orders']) || functions::ws_remove($_POST['orders']) === '') {
                throw new Exception('Moderator Orders cannot be left blank!');
            }
            elseif (strlen($_POST['orders']) > 1500) {
                throw new Exception('Moderator orders may not be longer than 1500 characters');
            }

            $nindo_text = functions::store_content($_POST['orders']);

            $GLOBALS['database']->transaction_start();

            if (!($nindo = $GLOBALS['database']->fetch_data('SELECT `user_notes`.`message`
                FROM `user_notes` WHERE `user_notes`.`user_id` = 0 LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error when obtaining Moderator Orders, please try again!');
            }
            elseif($nindo === '0 rows') {
                self::order_recreate($nindo_text);

                // Show message
                $GLOBALS['page']->Message('Moderator Orders could not be found in system, '.
                    'but it was successfully re-created!', 'Moderator Orders', 'id=' . $_GET['id']);
            }
            else {
                if ($nindo_text === functions::store_content($nindo[0]['message'])) {
                    throw new Exception('Your editted orders are similar to the previous order submitted!');
                }

                if ($GLOBALS['database']->execute_query('UPDATE `user_notes`
                    SET `user_notes`.`message` = "' . $nindo_text . '"
                    WHERE `user_notes`.`user_id` = 0 LIMIT 1') === false) {
                    throw new Exception('Could not successfully update the moderator orders in the database!');
                }

                // Show message
                $GLOBALS['page']->Message('Moderator Orders have been updated within the system!',
                    'Moderator Orders', 'id=' . $_GET['id']);
            }

            $GLOBALS['database']->transaction_commit();
        }

        private function order_recreate($message = '') {
            // Attempt to Re-Create and Insert Nindo if it doesn't exist
            if ($GLOBALS['database']->execute_query('INSERT INTO `user_notes`
                    (`user_id`, `user`, `moderator`, `time`, `message`)
                VALUES
                    (0, "Mod_Orders", "' . $GLOBALS['page']->user[0]['username'] . '",
                        UNIX_TIMESTAMP(), "' . $message . '")') === false) {
                throw new Exception('Could not recreate the moderator orders in the system. Contact coder.');
            }
        }
    }

    new notes();