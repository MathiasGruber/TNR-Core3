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

// let's see all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

class payment {

    //	Script settings
    const P_EMAIL = "nano.mathias@gmail.com";
    const SANDBOX_EMAIL = "nano.mathias-facilitator@gmail.com";
    const E_TABLE = "paypal_error";
    const SANDBOX = false;

    const SSL_P_URL = 'https://ipnpb.paypal.com/cgi-bin/webscr';
    const SSL_SAND_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    //	Paypal IPN settings
    const TIMEOUT = 120;

    //	IPN data
    private $paypal_post_vars;
    private $send_time;
    private $paypal_response;
    private $response_status;


    public function __construct() {

        // Set active email
        $this->email = self::SANDBOX == true ? self::SANDBOX_EMAIL : self::P_EMAIL;

        //  Set IPN variables and send request
        $this->paypal_post_vars = $_POST;

        // Set missing variables if they are not there
        if( !isset($this->paypal_post_vars['mc_gross']) ){      $this->paypal_post_vars['mc_gross'] = "";}
        if( !isset($this->paypal_post_vars['txn_id']) ){        $this->paypal_post_vars['txn_id'] = "";}
        if( !isset($this->paypal_post_vars['payment_status']) ){$this->paypal_post_vars['payment_status'] = "";}
        if( !isset($this->paypal_post_vars['payment_date']) ){  $this->paypal_post_vars['payment_date'] = "";}
        if( !isset($this->paypal_post_vars['txn_type']) ){  $this->paypal_post_vars['txn_type'] = "";}
        if( !isset($this->paypal_post_vars['item_name']) ){  $this->paypal_post_vars['item_name'] = "Unknown";}

        // Verify
        $this->curlPost();

        //	Set username / UserID
        $this->custom   = explode("|",$_POST['custom']);
        $this->rID      = $this->custom[0];
        $this->receiver = $this->custom[1];
        $this->sID      = $this->custom[2];
        $this->sender   = $this->custom[3];

        //opening events if it is not already
        $this->closeEvents = false;
        require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
        $this->events = new Events($this->rID);
        $this->closeEvents = true;

        // Debug payment
        $this->debug();

        //	Check if payment is verified
        if ($this->is_verified()) {

            // Response is valid, insert in databse
            $this->insertInPaymentTable();

            if ($this->paypal_post_vars['txn_type'] == 'subscr_signup') {
                $this->verify_fed();
                $this->get_fed();
            } elseif ($this->paypal_post_vars['txn_type'] == 'subscr_payment') {
                $this->verify_fed();
                $this->update_fed();
            } elseif ($this->paypal_post_vars['txn_type'] == 'subscr_cancel') {

                // Remove subscr ID.
                $this->stop_fed();

            } elseif ($this->paypal_post_vars['txn_type'] == 'subscr_eot') {

                // Remove subscr ID.
                $this->stop_fed();
                // $this->remove_fed();

            } elseif( in_array($this->paypal_post_vars['item_name'], array("NormalToSilver","NormalToGold","SilverToGold")) ){
                $this->upgradeFed();
            } else {
                $this->process_payment();
            }
        } else {
            $this->error_out("Paypal could not verify this payment");
        }

        if($this->closeEvents)
            $this->events->closeEvents();
    }

    //	Sends out an error message as specified in the settings
    private function error_out($message) {
        $GLOBALS['database']->execute_query("INSERT INTO `ipn_errors`
            (
                `txn_id` ,
                `time` ,
                `custom` ,
                `error` ,
                `data` ,
                `email`
            )
                VALUES
            (
                '" . $this->paypal_post_vars['txn_id'] . "',
                '" . time() . "',
                '" . $_POST['custom'] . "',
                '" . $message . " - ".$this->paypal_response." - ".$this->response_status."',
                '" . print_r($this->paypal_post_vars, true) . "',
                '" . $this->paypal_post_vars['payer_email'] . "'
            );");
        die("Error: ".$message);
    }

    // Test output
    private function test_out($message) {
        $GLOBALS['database']->execute_query("INSERT INTO `ipn_errors`
            (
                `txn_id` ,
                `time` ,
                `custom` ,
                `error` ,
                `data` ,
                `email`
            )
                VALUES
            (
                '" . $this->paypal_post_vars['txn_id'] . "',
                '" . time() . "',
                '" . $_POST['custom'] . "',
                '" . $message . "',
                '" . print_r($this->paypal_post_vars, true) . "',
                '" . $this->paypal_post_vars['payer_email'] . "'
            );");
    }

    //  Insert the payment into the database table
    private function insertInPaymentTable(){
        $country = isset( $this->paypal_post_vars['residence_country'] ) ? $this->paypal_post_vars['residence_country'] : "N/A";
        $query = "
            INSERT INTO `ipn_payments` (
                `r_uid` ,
                `recipient`,
                `time` ,
                `price` ,
                `item` ,
                `txn_id`,
                `txn_type`,
                `status`,
                `country` ,
                `s_uid`,
                `sender`,
                `date`
            ) VALUES (
                '" . $this->rID . "',
                '" . $this->receiver . "',
                '" . time() . "',
                '" . $this->paypal_post_vars['mc_gross'] . "',
                '" . $this->paypal_post_vars['item_name'] . "',
                '" . $this->paypal_post_vars['txn_id'] . "',
                '" . $this->paypal_post_vars['txn_type'] . "',
                '" . $this->paypal_post_vars['payment_status'] . "',
                '" . $country . "',
                '" . $this->sID . "',
                '" . $this->sender . "',
                '" . $this->paypal_post_vars['payment_date'] . "'
            )";
        $GLOBALS['database']->execute_query( $query );
    }

    //	Does other checks on the payment
    private function check_payment() {
        if ($this->paypal_post_vars['business'] == $this->email) {
            if( $this->paypal_post_vars['mc_currency'] == "USD" ){
                switch ($this->paypal_post_vars['option_selection1']) {
                    case '1 Reputation Point':
                        if ($this->paypal_post_vars['mc_gross'] < 2.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '6 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 8.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '12 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 10.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '24 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 15.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '48 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 30.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '120 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 70.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '200 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 120.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                    case '500 Reputation Points':
                        if ($this->paypal_post_vars['mc_gross'] < 300.00) {
                            $this->error_out("The monetary amount does not match the product, or an invalid product was specified " . $this->paypal_post_vars['mc_gross'] . ':' . $this->paypal_post_vars['item_number']);
                        }
                        break;
                }
                return 1;
            }
            else{
                $this->error_out('The currency paid is not USD. Someone is trying to trick us?');
            }
        } else {
            $this->error_out('The Business e-mail ('.$this->paypal_post_vars['business'].') did not match the paypal address! ('.$this->email.')');
        }
    }

    //	Checks the payment status and continues processing.
    private function process_payment() {

        // if ($GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `txn_id` = '" . $this->paypal_post_vars['txn_id'] . "' LIMIT 1") == '0 rows') {
        switch ($this->paypal_post_vars['payment_status']) {
            case 'Completed':
                if ($this->check_payment()) {
                    $this->grant_item();
                }
                break;
            case 'Pending':
                if ($this->paypal_post_vars['pending_reason'] != "intl") {
                    $this->error_out("Pending Payment - " . $this->paypal_post_vars['pending_reason']);
                }
                break;
            case 'Failed':
                $this->error_out("Payment failed");
                break;
            case 'Denied':
                $this->error_out("Payment denied");
                break;
            case 'Canceled':
                $this->error_out("Payment canceled");
                break;
            case 'Refunded':
                $this->take_item( $this->paypal_post_vars['parent_txn_id'] );
                $this->error_out("Payment refunded");
                break;
            case 'Reversed':
                $this->take_item( $this->paypal_post_vars['parent_txn_id'] );
                $this->error_out("Payment reversed");
                break;
            default:
                $this->error_out("Invalid payment status!");
                break;
        }
    }

    //	Credits the user the correct amount of rep
    private function grant_item() {
        switch ($this->paypal_post_vars['option_selection1']) {
            case '1 Reputation Point':
                $rep_increase = 1;
                break;
            case '6 Reputation Points':
                $rep_increase = 6;
                break;
            case '12 Reputation Points':
                $rep_increase = 12;
                break;
            case '24 Reputation Points':
                $rep_increase = 24;
                break;
            case '48 Reputation Points':
                $rep_increase = 48;
                break;
            case '120 Reputation Points':
                $rep_increase = 120;
                break;
            case '200 Reputation Points':
                $rep_increase = 200;
                break;
            case '500 Reputation Points':
                $rep_increase = 500;
                break;
            default:
                $this->error_out("Unknown item type during upload process!");
                break;
        }

        // Implement discounts
        $rep_increase += floor( data::$REP_EXTRA * $rep_increase );

        // Do Update this to the user
        if ( !$GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rep_ever` = `rep_ever` + " . $rep_increase . ", `rep_now` = `rep_now` + " . $rep_increase . " WHERE `uid` = '" . $this->rID . "'"))
        {
                $this->error_out("Reputation increase failed to upload, please investigate!");
        }
        else
        {
            $this->events->acceptEvent('rep_gain', array('new'=> $rep_increase));
        }
    }

    //  Deals with invalid payments
    private function take_item( $txnID ) {
        $payment_data = $GLOBALS['database']->fetch_data("
            SELECT *
            FROM `ipn_payments`
            WHERE
                `txn_id` = '" . $txnID . "' AND
                `status` = 'Completed' AND
                `reversed` = '0'
            LIMIT 1");

        if ($payment_data != '0 rows') {

            // Update the payment
            $GLOBALS['database']->execute_query("
                    UPDATE `ipn_payments`
                    SET `reversed` = '1'
                    WHERE `transaction_id` = '" . $payment_data[0]['transaction_id'] . "'");

            if ( stristr($payment_data[0]['item_name'], 'Federal Support') ) {
                $this->remove_fed();
            } else {
                switch ($this->paypal_post_vars['option_selection1']) {
                    case '1 Reputation Point':
                        $rep_decrease = 1;
                        break;
                    case '6 Reputation Points':
                        $rep_decrease = 6;
                        break;
                    case '12 Reputation Points':
                        $rep_decrease = 12;
                        break;
                    case '24 Reputation Points':
                        $rep_decrease = 24;
                        break;
                    case '48 Reputation Points':
                        $rep_decrease = 48;
                        break;
                    case '120 Reputation Points':
                        $rep_decrease = 120;
                        break;
                    default:
                        $this->error_out("Refunded / withdrawn payment could not be processed automatically!");
                        break;
                }

                // Get the current reps of this user
                $user = $GLOBALS['database']->fetch_data("
                    SELECT `rep_now`,`rep_ever`,`username`
                    FROM `users_statistics`
                    INNER JOIN `users` ON (`uid` = `id`)
                    WHERE `uid` = '" . $payment_data[0]['r_uid'] . "'"
                );

                if ($rep_decrease <= $user[0]['rep_now']) {

                    $GLOBALS['database']->execute_query("
                        UPDATE `users_statistics`
                        SET
                            `rep_now` = `rep_now` - '" . $rep_decrease . "',
                            `rep_ever` = `rep_ever` - '" . $rep_decrease . "'
                        WHERE
                            `uid` = '" . $payment_data[0]['r_uid'] . "'");

                            $this->events->acceptEvent('rep_loss',array('count'=> $rep_decrease));

                } else {
                    $bantime = $bantime = time() + (604800 * 2);

                    $GLOBALS['database']->execute_query("
                        UPDATE `users`,`users_statistics`
                        SET
                            `rep_now` = `rep_now` - '" . $rep_decrease . "',
                            `rep_ever` = `rep_ever` - '" . $rep_decrease . "',
                            `ban_time` = '" . $bantime . "',
                            `logout_timer` = '0'
                        WHERE
                            `users`.`id` = '" . $payment_data[0]['r_uid'] . "' AND
                            `users`.`id` = `users_statistics`.`uid`"
                    );

                    $this->events->acceptEvent('rep_loss',array('count'=> $rep_decrease));

                    $GLOBALS['database']->execute_query("
                        INSERT INTO `moderator_log`
                        (
                            `time` ,
                            `uid` ,
                            `duration`,
                            `moderator` ,
                            `action` ,
                            `reason` ,
                            `message` ,
                            `username`
                        ) VALUES (
                            '" . time() . "',
                            '" . $payment_data[0]['r_uid'] . "',
                            '2 weeks',
                            'System',
                            'ban',
                            'Withdrawn paypal payment',
                            'You have been banned because a paypal payment was withdrawn, and you had already spent some or all of the reputation points.',
                            '". $user[0]['username']."'
                        );"
                    );
                }
            }
        } else {
            //Payment was not yet credited or logged
            $this->error_out("Tried to take item: ".$txnID.", but could not find in DB");
        }
    }

    //	Verify federal support
    private function verify_fed() {
        if (
            $this->paypal_post_vars['item_name'] == 'Normal Federal Support' ||
            $this->paypal_post_vars['item_name'] == 'Silver Federal Support' ||
            $this->paypal_post_vars['item_name'] == 'Gold Federal Support'
        ) {
            if ($this->paypal_post_vars['business'] == $this->email ) {
                if( $this->paypal_post_vars['mc_currency'] == "USD" ){
                    if( $this->paypal_post_vars['payment_status'] == "Completed" ){
                        $this->fedType = "None";
                        $this->notWhere = "";
                        if( isset($this->paypal_post_vars['payment_gross']) && $this->paypal_post_vars['payment_gross'] > 0 ){
                            switch( $this->paypal_post_vars['payment_gross'] ){
                                case 5.00:  $this->fedType = "Normal"; break;
                                case 10.00: $this->fedType = "Silver"; break;
                                case 15.00: $this->fedType = "Gold"; break;
                            }
                        }
                        if( isset($this->paypal_post_vars['amount3']) && $this->paypal_post_vars['amount3'] > 0 ){
                            switch( $this->paypal_post_vars['amount3'] ){
                                case 5.00:  $this->fedType = "Normal"; break;
                                case 10.00: $this->fedType = "Silver"; break;
                                case 15.00: $this->fedType = "Gold"; break;
                            }
                        }
                        switch( $this->fedType ){
                            case "Normal": $this->notWhere = " AND `federal_level` != 'Silver' AND `federal_level` != 'Gold' "; break;
                            case "Silver": $this->notWhere = " AND `federal_level` != 'Gold' "; break;
                            case "Gold": $this->notWhere = ""; break;
                        }
                        if( $this->fedType == "None" ){
                            $this->error_out("The cost does not match that of federal support");
                        }
                    }
                    else{
                        $this->error_out('Payment needs to be cleared first!');
                    }
                }
                else{
                    $this->error_out('The currency paid is not USD. Someone is trying to trick us?');
                }
            } else {
                $this->error_out('The Business e-mail ('.$this->paypal_post_vars['business'].') did not match the paypal address! ('.$this->email.')');
            }
        } else {
            $this->error_out("The item number does not match federal support");
        }
    }

    private function get_fed() {
        if (!$GLOBALS['database']->execute_query("
                UPDATE `users`,`users_statistics`
                SET
                    `federal_timer` = '" . (time() + 2678400) . "',
                    `subscr_id` = '" . $this->paypal_post_vars['subscr_id'] . "',
                    `user_rank` = 'Paid' ,
                    `federal_level` = '".$this->fedType."'
                WHERE
                    `users`.`id` = `users_statistics`.`uid` AND
                    `users`.`id` = '" . $this->rID . "' ".$this->notWhere)
        ) {
            $this->error_out("Federal support status could not be granted.");
        }
    }

    private function update_fed() {
        if (!$GLOBALS['database']->execute_query("
            UPDATE `users`,`users_statistics`
            SET
                `federal_timer` = '" . (time() + 2678400) . "',
                `subscr_id` = '" . $this->paypal_post_vars['subscr_id'] . "',
                `user_rank` = 'Paid',
                `federal_level` = '".$this->fedType."'
            WHERE
                `user_rank` != 'Admin' AND
                `user_rank` != 'Moderator' AND
                `user_rank` != 'Supermod' AND
                `user_rank` != 'Event' AND
                `user_rank` != 'EventMod' AND
                `user_rank` != 'ContentAdmin' AND
                `user_rank` != 'PRmanager' AND
                `users`.`id` = `users_statistics`.`uid` AND
                `users`.`id` = '" . $this->rID . "'
            ".$this->notWhere )) {
            $this->error_out("Federal support status could not be updated.");
        }
    }

    private function remove_fed() {
        if (!$GLOBALS['database']->execute_query("
                UPDATE `users`,`users_statistics`
                SET
                    `federal_timer` = '',
                    `subscr_id` = '0',
                    `user_rank` = 'Member',
                    `federal_level` = 'None'
                WHERE
                    `users`.`id` = `users_statistics`.`uid` AND
                    `users`.`id` = '" . $this->rID . "'"
            )
        ) {
            $this->error_out("Federal support status could not be removed.");
        }
    }

    private function stop_fed(){
        if (!$GLOBALS['database']->execute_query("
                UPDATE `users`,`users_statistics`
                SET
                    `subscr_id` = '0'
                WHERE
                    `users`.`id` = `users_statistics`.`uid` AND
                    `users`.`id` = '" . $this->rID . "'"
            )
        ) {
            $this->error_out("Federal support status could not be removed.");
        }
    }

    // Upgrade federal support using one-time payment
    private function upgradeFed(){
        if( isset($this->paypal_post_vars['item_name']) ){
            if ($this->paypal_post_vars['business'] == $this->email ) {
                if( $this->paypal_post_vars['mc_currency'] == "USD" ){
                    if( $this->paypal_post_vars['payment_status'] == "Completed" ){

                        // Get current user data
                        $supp_data = $GLOBALS['database']->fetch_data("
                        SELECT `user_rank`,`federal_timer`,`subscr_id`,`federal_level`
                        FROM `users`,`users_statistics`
                        WHERE `id` = `uid` AND `id` = '" . $this->rID . "'");
                        if( $supp_data !== "0 rows" ){

                            // Check current timer
                            if( $supp_data[0]['federal_timer'] !== "0" ){

                                // Trying and catching
                                try{

                                    // Get the library
                                    require_once('/var/app/current/libs/profileFunctions/fedsupportLib.inc.php');

                                    // Get seconds left
                                    $secondsLeft = fedsupportLib::getTimeLeft( $supp_data[0]['federal_timer'] );
                                    if( $secondsLeft > 0 ){

                                        // Calculate price
                                        $price = 0;
                                        $newLevel = "";
                                        switch( $this->paypal_post_vars['item_name'] ){
                                            case "NormalToSilver":
                                                $price = fedsupportLib::getUpgradePrice("Normal", "Silver", $secondsLeft);
                                                $newLevel = "Silver";
                                                break;
                                            case "NormalToGold":
                                                $price = fedsupportLib::getUpgradePrice("Normal", "Gold", $secondsLeft);
                                                $newLevel = "Gold";
                                                break;
                                            case "SilverToGold":
                                                $price = fedsupportLib::getUpgradePrice("Silver", "Gold", $secondsLeft);
                                                $newLevel = "Gold";
                                                break;
                                        }
                                        if( $price > 0 ){

                                            // Validate the amount send by the user -
                                            if( $this->paypal_post_vars['payment_gross'] >= $price ){

                                                // Query
                                                $query = "UPDATE `users_statistics` SET `federal_level` = '" . $newLevel . "' WHERE `users_statistics`.`uid` = '" . $this->rID . "' LIMIT 1";

                                                // Run query
                                                if (!$GLOBALS['database']->execute_query($query))
                                                {
                                                    $this->error_out("Federal support status could not be upgraded: ".$newLevel." - ".$this->rID);
                                                }
                                                else{
                                                    $this->test_out("Successfully updated federal support.");
                                                }
                                            }
                                            else{
                                                $this->error_out('Calculated price of '.$price." is above the paid amount ".$this->paypal_post_vars['payment_gross'] );
                                            }
                                        }
                                        else{
                                            $this->error_out('Calculated price of '.$price." is not valid");
                                        }
                                    }
                                    else{
                                        $this->error_out('Trying to update federal support negative seconds left of current support');
                                    }
                                } catch (Exception $ex) {
                                    $this->error_out( $ex->getMessage() );
                                }
                            }
                            else{
                                $this->error_out('Trying to update federal support when timer is currently 0');
                            }
                        }
                        else{
                            $this->error_out('Could not determine user federal support data');
                        }
                    }
                    else{
                        $this->error_out('Payment needs to be cleared first!');
                    }
                }
                else{
                    $this->error_out('The currency paid is not USD. Someone is trying to trick us?');
                }
            } else {
                $this->error_out('The Business e-mail ('.$this->paypal_post_vars['business'].') did not match the paypal address! ('.$this->email.')');
            }
        } else {
            $this->error_out("The item number does not match federal support");
        }

    }

    private function debug() {
        if (!empty($_POST)) {
            $data = "Data block: \r\n" . print_r($_POST, true);
        } else {
            $data = "POST was empty!";
        }
        $GLOBALS['database']->execute_query("INSERT INTO `ipn_tests` ( `vars` , `time` )VALUES ('DATA BLOCK!: " . $data . "', '" . time() . "');");
    }

    // Send a response using curl instead of fsocket open
    private function curlPost() {

        // Get host
        if( self::SANDBOX == true ){
            $paypal_url = self::SSL_SAND_URL;
        }
        else{
            $paypal_url = self::SSL_P_URL;
        }

        // read raw POST data to prevent serialization issues w/ $_POST
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($myPost as $key => $value) {
            if (get_magic_quotes_gpc()) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        $ch = curl_init($paypal_url);
        if ($ch == false) {
            return false;
        }


        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        // Poodle Update
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

        // Set TCP timeout to 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: Studie-Tech ApS'));

        // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
        // of the certificate as shown below. Ensure the file is readable by the webserver.
        // This is mandatory for some environments.
        // curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/../paypalCert.pem");

        $this->paypal_response = curl_exec($ch);
        $this->response_status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->paypal_response === false || $this->response_status == '0') {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            $this->error_out("cURL error: [".$errno."] ".$errstr);
        }

        curl_close($ch);
    }

    //  Check if the payment is verified
    function is_verified() {
        if (strpos($this->paypal_response,'VERIFIED') !== false) {
            return true;
        }
        else{
            return false;
        }
    }

}