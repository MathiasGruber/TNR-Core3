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

//Define your GCM server key here 
define('API_ACCESS_KEY', 'AIzaSyBELCC0QPMRXTNCk2tJ8n9XGM3iaA__-mo');

class module {

    public function module() {
        
        try{
            
            functions::checkActiveSession();
            
            if($GLOBALS['database']->get_lock($_SESSION['uid'], get_class($this)) === false) {
                throw new Exception('There was a duplicate request being made at the same time! This might mean you have been pressing the buttons too quickly for the server to keep up. Please try again.');
            }
            
            // Check user rank. Only allow staff 
            if (!in_array($GLOBALS['userdata'][0]['user_rank'], array('EventMod', 'Admin', 'Supermod', 'PRmanager'), true)) {
                throw new Exception("You do not have access to view this page");
            }
            
            // Handle message
            if (!isset($_POST['Submit'])) {
                $this->pushNotificationForm();
            } else {
                $this->sendPushNotification();
            }
            
            if($GLOBALS['database']->release_lock($_SESSION['uid'], get_class($this)) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
            
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , "Mobile Push Notification System", 'id='.$_GET['id'],'Return');
        }
    }

    //  Blue messages
    private function pushNotificationForm() {
        
        // Setup Village Array and Names
        $villages = array("All","All");            
        foreach(Data::$VILLAGES as $village) {
            $villages[$village] = $village;
        }
        
        // Platforms
        $platforms = array("All"=>"All","Android"=>"Android","iOS"=>"iOS");
        
        // Input fields
        $inputFields = array(
            array("infoText" => "Message", "inputFieldName" => "message", "type" => "textarea", "inputFieldValue" => ""),
            array("infoText" => "Village","inputFieldName" => "villageMask","type" => "select","inputFieldValue" => $villages),
            array("infoText" => "Platforms","inputFieldName" => "notificationPlatform","type" => "select","inputFieldValue" => $platforms)
            
        );
        
        // Create the input form
        $GLOBALS['page']->UserInput("Push notification to mobile devices. Please do not abuse this feature!", 
            "Mobile Push Notification System", 
            $inputFields, 
            array(
                "href" => "?id=".$_REQUEST['id'] ,
                "submitFieldName" => "Submit", 
                "submitFieldText" => "Submit"),
            false ,
            "pushForm"
        );
    }

    // Insert message
    private function sendPushNotification() {
        
        // Devices to test
        $platforms = array();
        if( $_POST['notificationPlatform'] == "All" || $_POST['notificationPlatform'] == "Android" ){
            $platforms[] = "Android";
        }
        if( $_POST['notificationPlatform'] == "All" || $_POST['notificationPlatform'] == "iOS" ){
            $platforms[] = "iOS";
        }
        
        // Village selector
        $selector = "";
        if( in_array($_POST['villageMask'], array("Konoki",'Syndicate','Silence','Shroud','Shine','Samui'), true) ){
            $selector = " AND `village` = '".$_POST['villageMask']."' ";
        }
        
        // Go through each platform
        $userMessage = "";
        foreach($platforms as $platform){
            
            // Do the query for push IDs
            $eligibleDevices = $GLOBALS['database']->fetch_data("
                SELECT 
                    `log_mobileLogins`.`pushID`
                FROM `log_mobileLogins` 
                LEFT JOIN `users` ON (`log_mobileLogins`.`uid` = `users`.`id`)
                WHERE `pushID` != '' ".$selector." AND `platform` = '".$platform."'
                GROUP BY `pushID`

            ");        
            
            if($eligibleDevices !== "0 rows"){
                
                // Get message and device push IDs
                $message = functions::store_content($_POST['message']);
                $registration_ids = array_column($eligibleDevices, 'pushID');
                
                // Send push notification to users
                switch($platform){
                    case "Android": $this->sendAndroidPushNotification($registration_ids, $message); break;
                }  
                
                // Message
                $userMessage .= "The message has been uploaded to ".$platform.".<br>";
            }
            else{
                $userMessage .= "No eligible devices found matching your criteria for ".$platform.".<br>";
            }
        }
        
        // The message has been pushed
        $GLOBALS['page']->Message($userMessage, 'Blue Messages', 'id=' . $_GET['id']);
    }
    
    // For sending android push notifications
    private function sendAndroidPushNotification($registration_ids, $message) {

        $msg = array
        (
            'text' => $message,
            'title' => 'TheNinja-RPG'
        );

        $fields = array
        (
            'registration_ids' => $registration_ids,
            'data' => $msg,
            'time_to_live' => 43200
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($result);
        $flag = $res->success;
        if($flag >= 1){
            header('Location: index.php?success');
        }else{
            header('Location: index.php?failure');
        }
    }
}

new module();