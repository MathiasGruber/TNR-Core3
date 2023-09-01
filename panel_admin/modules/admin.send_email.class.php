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

class email_sender{
    
    public function __construct(){
        
        try{
            
            if(!isset($_POST['Submit'])){
                $this->main_screen();   
            }
            else{
                $this->doSend();
            }
        } catch (Exception $e) {
            $GLOBALS['page']->Message( $e->getMessage() , 'Emailer Error', 'id='.$_GET['id']);
        }       
    }
    
    // Show the sending form
    private function main_screen(){  
        
        // Create the fields to be shown
        $inputFields = array(
            array("infoText" => "Subject", "inputFieldName" => "subject", "type" => "input", "inputFieldValue" => "" ),
            array("infoText" => ";-separated list of emails", "inputFieldName" => "emailList", "type" => "textarea", "inputFieldValue" => ""),
            array("infoText" => "Email Message (html allowed)", "inputFieldName" => "message", "type" => "textarea", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Send email to user emails using the TNR smtp server. Beware that sending many emails may take a long time.", // Information
            "Send Emails", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] , "submitFieldName" => "Submit","submitFieldText" => "Send Emails"), // Submit button
            "Return" // Return link name
        );
    }
    
    // Do send the emails
    private function doSend(){  
        
        // Send verification e-mail:
        require '../vendor/autoload.php';
        require('../libs/mail.inc.php');
        $mail = new Mail();        
        
        if ($mail->Send($_POST['emailList'], $_POST['subject'], $_POST['message'], $_POST['message'])) {
            $GLOBALS['page']->Message( "Email has been sent successfully" , 'Emailer Success', 'id='.$_GET['id']);
        } else {
            throw new Exception("There was an error sending the email");
        }
        
    }
}

new email_sender();