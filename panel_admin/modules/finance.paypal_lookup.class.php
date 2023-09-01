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
class paypalSystem{
    
    public function __construct(){
        
        if( !isset($_GET['act']) ){
            // Update the Database
            if( isset($_POST['to']) ){
               $this->updateDatabase();
            }

            // Lookup the record, or show the search form
            if( isset($_POST['txtid']) ){
               $this->lookup();
            }
            else{
               $this->search_form();
            }
        }
        elseif( $_GET['act'] == "insertEntry" ){
            $this->showNewEntryForm($_GET['entry'], $_GET['gross']);
        }
        
        
    }
    
    // Search transactions
    private function search_form(){
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "inputFieldName"=>"account",
                "type"=>"select",
                "inputFieldValue"=> array(
                    "Core3" => "Core3",
                    "Core2" => "Core2"
                )
            ),
            array("infoText"=>"Lookup transaction ID","inputFieldName"=>"txtid", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Search transaction ID against Paypal Database (and compares to TNR database).", // Information
            "Paypal DB Lookup", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] , "submitFieldName" => "Submit","submitFieldText" => "Search"), // Submit button
            "Return" // Return link name
        );
    }
    
    // Lookup on paypal
    private function lookup(){
        
        try{
            
            // Save the ID locally
            $txtid = $_POST['txtid'];

            // Pass data into class for processing with PayPal and load the response array into $PayPalResult
            $PayPalResult = $this->getPaypalInfo($txtid);

            // Write the contents of the response array to the screen for demo purposes.
            $showInformation = "";
            if( !empty( $PayPalResult ) ){
                $showInformation .= '<h1>Data from Paypal</h1>';
                foreach( $PayPalResult as $key => $value ){
                    if( $key !== "RAWREQUEST" && $key !== "RAWRESPONSE"&& $key !== "REQUESTDATA" ){
                        $showInformation .= '<b>'.$key.'</b>: '. print_r($value,true)." <br>";
                    }
                }
            }
            else{
                throw new Exception("Nothing returned from paypal");
            }

            // Update the transaction ID
            if( isset($PayPalResult['L_TRANSACTIONID0']) ){
                $txtid = $PayPalResult['L_TRANSACTIONID0'];

                // See if it's in the TNR database
                $transaction = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `txn_id` = '".$txtid."' LIMIT 1");

                if( $transaction !== "0 rows" ){
                    $showInformation .= '<h1>Transaction is in TNR database</h1>';
                    $showInformation .= 'Transaction was inserted on: '.date("l jS \of F Y h:i:s A", $transaction[0]['time'])."<br>";
                    $showInformation .= print_r($transaction[0], true);
                }
                else{
                    $showInformation .= '<h1>Transaction not found in TNR database</h1>';
                    if( $PayPalResult['L_STATUS0'] == "Completed" || $PayPalResult['L_STATUS0'] == "Cleared" ){
                        $showInformation .= 'This is not supposed to happen; somehow the payment has gone through <br>
        is registered with paypal, but it is not registered on TNR. Unfortunately, and<br>
        the paypal API does not allow us to directly retrieve the users that this <br>
        payment belongs to, and as such you can now chose to add this entry to the <br>
        TNR database, by assigning the username of the user who bought the reps, <br>
        and the user who the reps are for.<br><br>
        <a href="?id='.$_GET['id'].'&act=insertEntry&entry='.$txtid.'&gross='.$PayPalResult['L_AMT0'].'">Click here to add entry to TNR database manually</a><br>';
                        //$this->showNewEntryForm( $txtid, $PayPalResult['L_AMT0'] );

                        // Check if we can find the original server request, and get extra data from there
                        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_tests` WHERE `vars` LIKE '%".$txtid."%' LIMIT 1");
                        if( $data !== "0 rows" ){
                            $showInformation .= '<h1>Paypal has tried to send following information:</h1>';
                            $sentInfo = str_replace("DATA BLOCK!: Data block: ", "", $data[0]['vars']);
                            $showInformation .= $sentInfo."<br>";
                        }

                        // Check if we can find an error
                        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_errors` WHERE `txn_id` = '".$txtid."' LIMIT 1");
                        if( $data !== "0 rows" ){
                            $showInformation .= '<h1>Our system found following error attached to the original paypal request</h1>';
                            $showInformation .= $data[0]['error'];
                        }

                    }
                    else{
                        $showInformation .= 'But it is not marked as "Cleared" or "Completed" anyways, so we dont care. Status: '.$PayPalResult['L_STATUS0'];
                    }
                }
            }
            
            
            
            $GLOBALS['page']->Message( $showInformation , 'Paypal Lookup System', 'id='.$_GET['id'],'Return');
            
        } catch (Exception $e) {
            $GLOBALS['page']->Message( $e->getMessage() , 'Paypal Lookup System', 'id='.$_GET['id'],'Return');
        }
    }
    
    // Get transaction from Paypal
    private function getPaypalInfo( $txtid ){
        
        // Include required library files.
        require_once('../global_libs/paypalApi/includes/config.php');
        require_once('../global_libs/paypalApi/autoload.php');

        // Create PayPal object.
        $PayPalConfig = array(
                            'Sandbox' => $sandbox,
                            'APIUsername' => $api_username,
                            'APIPassword' => $api_password,
                            'APISignature' => $api_signature
                            );

        $PayPal = new angelleye\PayPal\PayPal($PayPalConfig);

        // Prepare request arrays
        $TSFields = array(
                            'startdate' => '2008-08-30T05:00:00.00Z',   // Required.  The earliest transaction date you want returned.  Must be in UTC/GMT format.
                            'transactionid' => $txtid, 			// Search by the PayPal transaction ID.
                        );

        $PayerName = array();

        $PayPalRequestData = array(
                                'TSFields' => $TSFields, 
                                'PayerName' => $PayerName
                                );

        // Pass data into class for processing with PayPal and load the response array into $PayPalResult
        $PayPalResult = $PayPal->TransactionSearch($PayPalRequestData);
        
        return $PayPalResult;
    }
        
    // Add entry to database
    private function showNewEntryForm( $txtid , $gross ){
        
        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Reputation Points For","inputFieldName"=>"to", "type" => "input", "inputFieldValue" => ""),
            array("infoText"=>"Reputation Points For","inputFieldName"=>"from", "type" => "input", "inputFieldValue" => ""),
            array("type"=>"hidden", "inputFieldName"=>"txtid", "inputFieldValue"=>$txtid),
            array("type"=>"hidden", "inputFieldName"=>"gross", "inputFieldValue"=>$gross)
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Assign '.$txtid.' to the following users. Remember, reputation points are not awarded by this system, that has to be done manually!", // Information
            "insert Paypal Entry to TNR database", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] , "submitFieldName" => "Submit","submitFieldText" => "Search"), // Submit button
            "Return" // Return link name
        );
        
    }
    
    // Update the database with the entry
    private function updateDatabase(){
    
        try{
            
            // Locally define
        $toUser = $_POST['to'];
        $fromUser = $_POST['from'];
        $txtid = $_POST['txtid'];
        
        // Get the users & the transaction record and check
        $toUser = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `username` = '".$toUser."' LIMIT 1");
        $fromUser = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `username` = '".$fromUser."' LIMIT 1");
        $transaction = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `txn_id` = '".$txtid."' LIMIT 1");
        
        // Do checks
        if( $toUser !== "0 rows" ){
            if( $fromUser !== "0 rows" ){
                if( $transaction == "0 rows" ){
                    
                    // Figure out item name
                    $item = "";
                    switch( intval($_POST['gross']) ){
                        case "2": $item = "1 Reputation point"; break;
                        case "8": $item = "5 Reputation points"; break;
                        case "10": $item = "10 Reputation points"; break;
                        case "7":  $item = "10 Reputation points - Deal"; break;
                        case "1":  $item = "1 Reputation points - Deal"; break;
                        case "15": $item = "20 Reputation points"; break;
                        case "30": $item = "40 Reputation points"; break;
                        case "50": $item = "75 Reputation Point"; break;
                        case "70": $item = "100 Reputation points"; break;
                    }
                    
                    if( $item !== "" ){
                        
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
                                '" . $toUser[0]['id'] . "', 
                                '" . $toUser[0]['username'] . "', 
                                UNIX_TIMESTAMP(), 
                                '" . $_POST['gross'] . "', 
                                '" . $item . "', 
                                '" . $txtid . "',
                                'Admin Panel Set',
                                'Completed', 
                                'Admin Panel Set', 
                                '" . $fromUser[0]['id'] . "',
                                '" . $fromUser[0]['username'] . "',
                                '" . date("l jS \of F Y h:i:s A", time()) . "'
                            )";
                        $GLOBALS['database']->execute_query( $query );
                        
                        // Print message
                        $GLOBALS['page']->Message( '<h2>Assigning '.$txtid.'; '.$item.'<br>
                                                 To '.$toUser[0]['username'].' -
                                                 From '.$fromUser[0]['username'].'</h2>' , 'Paypal Lookup System', 'id='.$_GET['id'],'Return');
                    }
                    else{
                        throw new Exception('Could not identify item type from value of: '.$_POST['gross'].'.');
                    }
                }
                else{
                    throw new Exception('The transaction is already in the database.');
                }
            }
            else{
                throw new Exception('The user '.$_POST['from'].' does not exist.');
            }
        }
        else{
            throw new Exception('The user '.$_POST['to'].' does not exist.');
        }
            
        } catch (Exception $e) {
            $GLOBALS['page']->Message( $e->getMessage() , 'Paypal Lookup System', 'id='.$_GET['id'],'Return');
        }
    }
    
    
	
    

}
new paypalSystem();