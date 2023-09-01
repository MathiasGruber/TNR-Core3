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
            $this->main_screen();
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'TNR Mobile Error Log', 'id='.$_GET['id'],'Return');
        }
    }
    
    private function getLog(){
        
        // Used data
        $min = tableParser::get_page_min();
        $extra = isset($_POST["name"]) && !empty($_POST["name"]) ? " WHERE `username` = '".$_POST["name"]."' " : "";
        
        return $GLOBALS['database']->fetch_data("
            SELECT `log_mobileAdViews`.*, `users`.`username` 
            FROM `log_mobileAdViews` 
            LEFT JOIN `users` ON `users`.`id` = `log_mobileAdViews`.`uid`
            ".$extra."
            ORDER BY `time` DESC 
            LIMIT " . $min . ",10");
    }
    

    private function main_screen() {
        
        $log = $this->getLog();
        
        // Show table
        tableParser::show_list(
            'logins', 
            'TNR Mobile Adviews Log', 
             $log, 
             array(
                'time' => "Last Time",
                'uid' => "UID",
                'username' => "Username",
                'deviceID' => "deviceID",
                'platform' => "Platform"
            ), 
            false, 
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
            ) // top search field
        );
    }

}

new loginLogs();