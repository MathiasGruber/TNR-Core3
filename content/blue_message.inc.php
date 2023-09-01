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

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Check user rank. Only allow staff
            if (!in_array($GLOBALS['userdata'][0]['user_rank'], array('EventMod', 'Admin', 'Supermod', 'PRmanager'), true)) {
                throw new Exception("You do not have access to view this page");
            }

            // Handle message
            if (!isset($_POST['Submit'])) {
                $this->blue_message_form();
            } else {
                $this->insert_blue_message();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , "Blue Message", 'id='.$_GET['id'],'Return');
        }
    }

    //  Blue messages
    private function blue_message_form() {

        $min = tableParser::get_page_min();
        $chars = $GLOBALS['database']->fetch_data("SELECT * FROM `blueMessages` ORDER BY `time` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'log', 'Previous Blue Messages', $chars, array(
            'user' => "Author",
            'message' => "Message",
            'mask' => "Mask",
            'time' => "Time"
                ), false, false, true
        );
        $GLOBALS['template']->assign('contentLoad', './templates/content/eventPanel/blueMessages.tpl');
    }

    // Insert message
    private function insert_blue_message() {
        $selector = "";
        if( in_array($_POST['mask'], array("Konoki",'Syndicate','Silence','Shroud','Shine','Samui'), true) ){
            $selector = " WHERE `village` = '".$_POST['mask']."' ";
        }
        $message = '<b>'.functions::store_content($_POST['message']).'</b>';

        if(isset($_POST['message_link']) && $_POST['message_link'] != '')
            $message = $_POST['message_link'].','.$message;

        if ($GLOBALS['database']->execute_query("UPDATE `users` SET 
                                                                    `notifications` = CONCAT('id::3;duration::none;text::".$message.";text-color::RGB(31,97,141);dismiss::yes;buttons::none;select::none;//',`notifications`)"
                                                                    .$selector)) {
            $GLOBALS['database']->execute_query("INSERT INTO `blueMessages` (`user`,`message`,`time`,`mask`) VALUES ('" . $GLOBALS['userdata'][0]['username'] . "','" . $message . "','" . $GLOBALS['user']->load_time . "', '".$_POST['mask']."' );");
            $GLOBALS['page']->Message("The message has been uploaded.", 'Blue Messages', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured uploading the message.", 'Blue Messages', 'id=' . $_GET['id']);
        }
    }

}

new module();