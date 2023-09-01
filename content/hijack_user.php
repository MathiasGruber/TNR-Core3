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
        
        try{
            
            functions::checkActiveSession();
            
            if($GLOBALS['database']->get_lock($_SESSION['uid'], get_class($this)) === false) {
                throw new Exception('There was a duplicate request being made at the same time! This might mean you have been pressing the buttons too quickly for the server to keep up. Please try again.');
            }
            
            // Check user rank. Only allow staff 
            if (!in_array($GLOBALS['userdata'][0]['user_rank'], array('Admin'), true)) {
                throw new Exception("You do not have access to view this page");
            }
            
            // Handle message
            if (!isset($_POST['Submit'])) {
                $this->hijack_form();
            } else {
                $this->do_hijack();
            }
            
            if($GLOBALS['database']->release_lock($_SESSION['uid'], get_class($this)) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
            
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , "Hijack System", 'id='.$_GET['id'],'Return');
        }
    }

    //  Blue messages
    private function hijack_form() {
        
        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Character Name","inputFieldName"=>"user", "type" => "input", "inputFieldValue" => ""),
            array("infoText"=>"Hijack Reason","inputFieldName"=>"reason", "type" => "textarea", "inputFieldValue" => ""),
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Please enter username and reason for controlling his/her character:", 
            "Character Hijack Tool", 
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'], "submitFieldName" => "Submit","submitFieldText" => "Submit"), // Submit button
            "Return" // Return link name
        );
    }

    // Insert message
    private function do_hijack() {
        $user = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `username` = '" . $_POST['user'] . "' LIMIT 1");
        if ($user != '0 rows') {
            if (isset($_POST['reason']) && $_POST['reason'] !== "") {

                // Log Changes
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` 
                (`time` ,`aid` ,`uid` ,`changes`,`IP`)
                VALUES (
                    '" . time() . "', 
                    '" . $GLOBALS['userdata'][0]['username'] . "', 
                    '" . $user[0]['username'] . "', 
                     'User controlled with following reason:<br> " . $_POST['reason'] . "', 
                     '" . $GLOBALS['user']->real_ip_address() . "'
                );");

                // Update session stuff
                session_regenerate_id();

                // Allow for easy go-back
                $hash = hash("sha512", "secretTadaaa" . $_SESSION['uid']);
                $_SESSION['backData'] = array($_SESSION['uid'], $hash);

                // Set session
                $_SESSION['uid'] = $user[0]['id'];
                $_SESSION['override'] = true;
                $GLOBALS['template']->assign("sessionID", session_id());

                // Update user table
                $loginID = session_id() . md5($user[0]['username'] . "xXx");
                $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_login` = '" . time() . "'      WHERE `userid` = '" . $user[0]['id'] . "' LIMIT 1");
                $GLOBALS['database']->execute_query("UPDATE `users` SET `logout_timer` = '" . (time() + 7200) . "', `login_id` = '".$loginID."' WHERE `id` = '" . $user[0]['id'] . "' LIMIT 1");

                // Use message
                $GLOBALS['page']->Message("You have taken control of the character.", 'Hijack System',"?id=2","Return to Profile");
            } else {
                throw new Exception("No reason for controlling this user was supplied");
            }
        } else {
            throw new Exception("The submitted username is not valid");
        }
    }
    
}

new module();