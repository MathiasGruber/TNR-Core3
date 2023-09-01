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

class repshop {

    public function __construct() {

        // Check if this user is in a squad
        try {

            $enable = true;

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Send discount data to display page
            $GLOBALS['template']->assign("extraRepsPerc", data::$REP_EXTRA);

            // Every time the user checks the rep shop,
            // load all un-analyzed transactions and send them to universal analytics
            $GLOBALS['page']->sendTransactionsToAnalytics();

            // Do stuff if the shop is enabled
            if ($enable) {
                if( isset($_GET['act']) && $_GET['act'] == "records" ){
                    $this->showRecords();
                }
                elseif( isset($_GET['act']) && $_GET['act'] == "unsubscribe" ){
                    $this->unsubscribe();
                }
                elseif( isset($_GET['utm_nooverride']) && $_GET['utm_nooverride'] ){
                    $this->thankYouPage();
                }
                elseif( isset($_GET['utm_nooverride']) && $_GET['utm_nooverride'] ){
                    $this->thankYouPage();
                }elseif( isset($_POST['mobile_receipt']) && !empty($_POST['mobile_receipt']) ){
                    $this->validateMobilePayment();
                }
                else{
                    $this->show_forms();
                }
            } else {
                throw new Exception("Reputation shop is disabled due to difficulties with paypal's IPN system.<br>We'll have it back up ASAP and apologize for the inconvenience.");
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Reputation Shop', 'id=2');
        }
    }

    // Show users how to unsubscribe
    private function unsubscribe(){
        $GLOBALS['page']->Message("
               Unsubscribing federal support is done through your paypal account. Paypal makes it easy for you to get an overview of your active subscriptions.
               For more details on how to cancel subscription, click the following links: <a href='https://www.paypal.com/us/webapps/helpcenter/helphub/article/?articleID=FAQ2327&m=SRE'>PayPal Guide</a><br>",
                'Reputation Shop',
                'id='.$_GET['id']);

    }

    // Show a thank you page
    private function thankYouPage(){

        // Show message to user
        $GLOBALS['page']->Message("
             Thank you for your payment. Your transaction has been completed,
             and a receipt for your purchase has been emailed to you.
             You may log into your account at www.paypal.com to
             view details of this transaction.", "Transaction Complete", "id=".$_GET['id']);
    }

    // Show the purchase form
    private function show_forms() {

        // Check if inabled
        $current = $GLOBALS['database']->fetch_data("SELECT * FROM `site_timer` WHERE `script` = 'paypalPayments'");
        if( $current[0]['character_cleanup'] == "enabled"){

            // User Data
            $supp_data = $GLOBALS['database']->fetch_data("
                SELECT `user_rank`,`federal_timer`,`subscr_id`,`federal_level`
                FROM `users`,`users_statistics`
                WHERE `id` = `uid` AND `id` = '" . $_SESSION['uid'] . "'");

            $GLOBALS['template']->assign('supp_data', $supp_data);
            // $GLOBALS['template']->assign('paypalSandbox', true );

            // Show transaction history
            $GLOBALS['template']->assign('showHistory', true);

            // Calculate update prices
            if( $supp_data[0]['federal_timer'] !== "0" ){

                // Run calculations for price
                require_once(Data::$absSvrPath.'/libs/profileFunctions/fedsupportLib.inc.php');

                // Get time left
                $secondsLeft = fedsupportLib::getTimeLeft( $supp_data[0]['federal_timer'] );
                $GLOBALS['template']->assign('currentDaysLeft', round($secondsLeft / (24*3600),2) );

                // Set upgrade prices
                if( in_array($supp_data[0]['federal_level'], array("Normal","Silver")) ){
                    if( $supp_data[0]['federal_level'] == "Normal" ){
                        $GLOBALS['template']->assign('silverUpgradePrice', fedsupportLib::getUpgradePrice("Normal", "Silver", $secondsLeft) );
                        $GLOBALS['template']->assign('goldUpgradePrice', fedsupportLib::getUpgradePrice("Normal", "Gold", $secondsLeft) );
                    }
                    else{
                        $GLOBALS['template']->assign('goldUpgradePrice', fedsupportLib::getUpgradePrice("Silver", "Gold", $secondsLeft) );
                    }
                }
            }

            // User Data
            $customField = $_SESSION['uid']."|".$GLOBALS['userdata'][0]['username']."|".$_SESSION['uid']."|".$GLOBALS['userdata'][0]['username'];
            $GLOBALS['template']->assign('customField', $customField);

            // Load template
            $GLOBALS['template']->assign('contentLoad', './templates/content/rep_shop/repshop_show.tpl');
        }
        else{
            throw new Exception("Reputation shop is currently disabled.");
        }
    }

    // Show user transaction record
    private function showRecords(){

        // Get data
        $min = tableParser::get_page_min();
        $paypalPayments = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `r_uid` = '" . $_SESSION['uid'] . "' AND `txn_id` != '' AND `item` != '' LIMIT 100");

        // Modify data
        if ($paypalPayments !== "0 rows") {
            $i = 0;
            while ($i < count($paypalPayments)) {
                if ($paypalPayments[$i]['sender'] == "") {
                    $paypalPayments[$i]['sender'] = "Unregistered";
                }
                if ($paypalPayments[$i]['recipient'] == "") {
                    $paypalPayments[$i]['recipient'] = "Unregistered";
                }
                $i++;
            }
        }

        // Show form
        tableParser::show_list(
            'paypal', 'PayPal Payments', $paypalPayments,
            array(
                'time' => "Time of Transaction",
                'item' => "Item",
                'sender' => "Sender",
                'recipient' => "Receiver",
                'status' => "Status",
                'price' => "\$ USD"
            ), false, false
        );

        // Mobile transactions
        $mobilePayments = $GLOBALS['database']->fetch_data("SELECT * FROM `log_mobilePayments` WHERE `uid` = '".$_SESSION['uid']."' LIMIT 100");

        tableParser::show_list(
            'mobile', 'Mobile Payments', $mobilePayments,
            array(
                'time' => "Time of Transaction",
                'platform' => "Platform",
                'itemname' => "Item Name",
                'verified' => "Validated"
            ), false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('contentLoad', './templates/content/rep_shop/repshop_transactions.tpl');
    }


    //-- FUNCTIONS FOR MOBILE PAYMENTS --//
    private function validateMobilePayment(){
        if( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true ){

            // Perform Verification
            $verified = 0;
            $transactionID = 0;
            $receipt = json_decode($_POST['mobile_receipt'], true);
            switch( $_GET['platform'] ){
                case "Android": case "WindowsEditor":
                    $public_key_base64 = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmvfmEmWRcYUFOffScd1LE2T54CrJdRyWWNYUq62ysTL/I9wPQsHAa1vELO5NEs8rJ51iOyUVjVFIRH/5qZyXllXJG4n+nQlWhBmARue511mKzuUsJU3W3pR9xazpuZ8rhshITUTszktbWzxHCp6T3B4rcL9BuT3/IrLE1B4BxWlrcXp9/iNQCBqLcbozhg2PareAAhICZDcdWyu3z2ilW2hkuzVNZ5HnbCHoNoLuqvw4YUPB/f9yOI/AaFOmOwa1wPxeTGHSaPKHWteDm+dxay8DiPbGgZ1ziaDJk6MaaHgYELvQNTce1wYlCWQBbvux+PXtzI1nc6VySkAYxXdVNwIDAQAB";
                    $transactionID = $receipt['TransactionID'];
                    $payload = json_decode($receipt['Payload'], true);
                    if( isset($payload['json'],$payload['signature']) ){
                        $signed_data = $payload['json'];
                        $signature = $payload['signature'];
                        $verified = $this->verify_market_in_app($signed_data, $signature, $public_key_base64);
                    }
                    break;
                case "iPhone":
                    $verified = false;
                    break;
                default:
                    throw new Exception("Could not determine which platform you're trying to purchase from: ".$_GET['platform']);
                    break;
            }

            // Check database for previous entries of this record
            $transaction = $GLOBALS['database']->fetch_data("SELECT * FROM `log_mobilePayments` WHERE `transactionID` = '".$transactionID."' AND `verified` = '0' LIMIT 1");
            if( $transaction == "0 rows" || $transactionID == 0 ){
                $reps = 0;
                if( $verified == 1 ){
                    switch( $_POST['mobile_itemname'] ){
                        case "10reps": $reps = 10; break;
                        case "20reps": $reps = 20; break;
                        case "50reps": $reps = 50; break;
                        case "100reps": $reps = 100; break;
                        default: throw new Exception("A product with this identified has not been created: ".$_POST['mobile_itemname']);
                    }
                }

                $GLOBALS['database']->execute_query("
                    INSERT INTO `log_mobilePayments`
                        (`platform`,`time`, `deviceID`, `transactionID`, `itemname`, `reps`, `uid`, `responseData`, `verified`)
                    VALUES
                        ('".addslashes($_GET['platform'])."',
                         '".time()."',
                         '".addslashes($_GET['deviceID'])."',
                         '".addslashes($transactionID)."',
                         '".addslashes($_POST['mobile_itemname'])."',
                         '".addslashes($reps)."',
                         '".$_SESSION['uid']."',
                         '".addslashes($_POST['mobile_receipt'])."',
                         '".$verified."');");



                // Message for user
                if( $verified == 1 && $reps > 0 ){
                    if ( $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rep_ever` = `rep_ever` + " . $reps . ", `rep_now` = `rep_now` + " . $reps . " WHERE `uid` = '" . $_SESSION['uid'] . "'"))
                    {
                        $GLOBALS['page']->Message('Your purchase has been processed and added successfully to your account', 'Reputation Shop', 'id=' . $_GET['id'] . '');
                        $GLOBALS['Events']->acceptEvent('rep_gain', array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] + $reps));
                    }
                    else{
                        $GLOBALS['page']->Message('There was an error updating your account with reputation points. Please contact support with receipt information.', 'Reputation Shop', 'id=' . $_GET['id'] . '');
                    }
                }
                else{
                    $GLOBALS['page']->Message('We were not able to validate your purchase against google servers. Please contact support with receipt information.', 'Reputation Shop', 'id=' . $_GET['id'] . '');
                }
            }
            else{

                throw new Exception("This transaction has already been registered in our database, and cannot be claimned twice!");
            }
        }
        else{
            throw new Exception("Tried to validate mobile payment outside of API call");
        }
    }

    private function verify_market_in_app($signed_data, $signature, $public_key_base64) {
	$key =	"-----BEGIN PUBLIC KEY-----\n".
		chunk_split($public_key_base64, 64,"\n").
		'-----END PUBLIC KEY-----';
	//using PHP to create an RSA key
	$key = openssl_get_publickey($key);
	//$signature should be in binary format, but it comes as BASE64.
	//So, I'll convert it.
	$signature = base64_decode($signature);
	//using PHP's native support to verify the signature
	$result = openssl_verify(
			$signed_data,
			$signature,
			$key,
			OPENSSL_ALGO_SHA1);
	if (0 === $result)
	{
		return 0;
	}
	else if (1 !== $result)
	{
		return 0;
	}
	else
	{
		return 1;
	}
    }

    private function verify_app_store_in_app($receipt, $is_sandbox) {
	//$sandbox should be TRUE if you want to test against itunes sandbox servers
	if ($is_sandbox)
		$verify_host = "ssl://sandbox.itunes.apple.com";
	else
		$verify_host = "ssl://buy.itunes.apple.com";

	$json='{"receipt-data" : "'.$receipt.'" }';
	//opening socket to itunes
	$fp = fsockopen ($verify_host, 443, $errno, $errstr, 30);
	if (!$fp)
	{
		// HTTP ERROR
		return false;
	}
	else
	{
		//iTune's request url is /verifyReceipt
		$header = "POST /verifyReceipt HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($json) . "\r\n\r\n";
		fputs ($fp, $header . $json);
		$res = '';
		while (!feof($fp))
		{
			$step_res = fgets ($fp, 1024);
			$res = $res . $step_res;
		}
		fclose ($fp);
		//taking the JSON response
		$json_source = substr($res, stripos($res, "\r\n\r\n{") + 4);
		//decoding
		$app_store_response_map = json_decode($json_source);
		$app_store_response_status = $app_store_response_map->{'status'};
		if ($app_store_response_status == 0)//eithr OK or expired and needs to synch
		{
			//here are some fields from the json, btw.
			$json_receipt = $app_store_response_map->{'receipt'};
			$transaction_id = $json_receipt->{'transaction_id'};
			$original_transaction_id = $json_receipt->{'original_transaction_id'};
			$json_latest_receipt = $app_store_response_map->{'latest_receipt_info'};
			return true;
		}
		else
		{
			return false;
		}
	}
    }


}

new repshop();