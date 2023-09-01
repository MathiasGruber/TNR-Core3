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

class loginLogs {

    public function loginLogs() {
        
        try{
            
            
            
            if( !isset($_GET['act']) || $_GET['act'] == "main" ){
                $this->main_screen();
            }
            else{
                switch( $_GET['act'] ){
                    case "showResponseData": 
                        $this->showData();
                    break;
                }
            }
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'TNR Mobile Payments Log', 'id='.$_GET['id'],'Return');
        }
    }
    
    private function showData(){
        if( isset($_GET['eid']) ){
            $error = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `log_mobilePayments` 
                WHERE `id` = '".$_GET['eid']."' LIMIT 1");
            if( $error !== "0 rows" ){
                $GLOBALS['page']->UserInput(
                    "", // Information
                    "ResponseData Log", // Title
                    array(
                        array(
                            "infoText" => "Message",
                            "inputFieldName" => "message", 
                            "type" => "textarea", 
                            "inputFieldValue" => $error[0]['responseData']
                        )
                    ), // input fields
                    array(
                        "href" => "?id=" . $_REQUEST['id'] , 
                        "submitFieldName" => "Return",
                        "submitFieldText" => "Return"
                    ), // Submit button
                    false, // Return link name
                    "sendForm"
                );
                
                
              
            }
            else{
                throw new Exception("Could not find the specified error");
            }
        }
        else{
            throw new Exception("No error ID set");
        }
    }
    
    private function getLog(){
        
        // Used data
        $min = tableParser::get_page_min();
        $extra = isset($_POST["name"]) && !empty($_POST["name"]) ? " WHERE `username` = '".$_POST["name"]."' " : "";
        
        return $GLOBALS['database']->fetch_data("
            SELECT `log_mobilePayments`.*, `users`.`username` 
            FROM `log_mobilePayments` 
            LEFT JOIN `users` ON `users`.`id` = `log_mobilePayments`.`uid`
            ".$extra."
            ORDER BY `time` DESC 
            LIMIT " . $min . ",10");
    }
    

    private function main_screen() {
        
        $log = $this->getLog();
        
        // Show table
        tableParser::show_list(
            'logins', 
            'TNR Mobile Payments Log', 
             $log, 
             array(
                'time' => "Purchase Time",
                'uid' => "UID",
                'username' => "Username",
                'deviceID' => "deviceID",
                'platform' => "Platform",
                'itemname' => "ItemName",
                'reps' => "RepsGiven",
                'verified' => "verified"
            ), 
            array( 
                array( "id" => $_GET['id'], "name" => "Show ResponseData", "act" => "showResponseData", "eid" => "table.id")
            ), 
            true, // Send directly to contentLoad
            true, // No newer/older links
            array(
                array("name" => "Show Overview", "href" => "?id=" . $_GET['id'] . "&act=main" )
            ), //top options links
            false, // No sorting on columns
            false, // No pretty options
            array(
                array(
                    "infoText"=>"Search by username",
                    "postField"=>"name", 
                    "postIdentifier"=>"postIdentifier", 
                    "inputName"=>"Search User",
                    "href" => "?id=".$_GET['id']
                )
            ) // No top search field
        );
    }

}

new loginLogs();