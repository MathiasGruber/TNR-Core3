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

error_reporting(E_ALL);
ini_set('display_errors', 1);

class monthlyMission {

    function __construct() {
        
        try {
        
            if (!isset($_GET['act'])) {
                $this->main_page();
            } elseif ($_GET['act'] == "new") {
                if (!isset($_POST['Submit'])) {
                    $this->update_form();
                } else {
                    $this->update_list();
                }
            } elseif ($_GET['act'] == "end") {
                $this->delete_mission();
            } elseif ($_GET['act'] == "review") {
                $this->reviewList();
            }
        }
        catch(Exception $e) {             
           $GLOBALS['page']->Message($e->getMessage(), 'User Login Error', 'id=1');
        }
    }
    
    private function getMonthlyMission(){
        $this->mission = $GLOBALS['database']->fetch_data("SELECT `value` FROM `site_information` WHERE `option`='monthlyMission' LIMIT 1");
    }
            

    private function main_page(){

        // Show form
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("
            SELECT `log_monthlyMissions`.*, `users`.`username`
            FROM `log_monthlyMissions` 
            LEFT JOIN `users` ON (`log_monthlyMissions`.`uid` = `users`.`id`)
            ORDER BY `log_monthlyMissions`.`handleTime` DESC 
            LIMIT " . $min . ",10"
        );
        
        // Go through list and fix entries
        foreach( $edits as $key => $entry ){
            if( empty($entry['handler']) ){
                $edits[$key]['handler'] = "None / Unknown";
            }
            if( $entry['handleTime'] == "0" ){
                $edits[$key]['handleTime'] = "N/A";
            }
        }
        
        // Show the table
        tableParser::show_list(
                'log', 'Monthly Mission Log', 
                $edits, array(
                    'handler' => "Admin/Handler Name",
                    'username' => "Username",
                    'url' => "Submission",
                    'pop_points' => "Pop Points",
                    'handleTime' => "Handling Time"
                ), 
                false ,
            true, // Send directly to contentLoad
            true, // No newer/older links
            array(
                array("name" => "Start New Mission", "href" => "?id=" . $_GET['id'] . "&act=new"),
                array("name" => "End Old Mission", "href" => "?id=" . $_GET['id'] . "&act=end"),
                array("name" => "View Submissions", "href" => "?id=" . $_GET['id'] . "&act=review")
            ), // No top options links
            false, //  sorting on columns
            false, // No pretty options
            false // No top search field               
        );
    }
    
    function update_form() {
        $this->getMonthlyMission();
        if( $this->mission[0]['value'] == "" ){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `site_information` WHERE `option` = 'monthlyMission'");
            if ($data != '0 rows') {
                tableParser::parse_form('site_information', 'Monthly Mission', array('id', 'option'), $data);
            } else {
                $GLOBALS['page']->Message("No monthly mission exists.", 'Monthly Mission', 'id=' . $_GET['id']);
            }
        }
        else {
            $GLOBALS['page']->Message("Previous mission must first be ended", 'Monthly Mission', 'id=' . $_GET['id']);
        }
    }

    function update_list() {
        $this->getMonthlyMission();
        if( $this->mission[0]['value'] == "" ){
            if (tableParser::update_data('site_information', 'option', 'monthlyMission')) {
                
                $GLOBALS['database']->execute_query("
                        UPDATE `users_missions`, `users` 
                        SET `mission_monthly` = NULL,
                            `notifications` = CONCAT('id:18;duration:none;text:A new monthly mission has been posted;dismiss:yes;buttons:none;select:none;//',`notifications`)");
                            
                
                $GLOBALS['page']->Message("The mission has been updated.", 'Update Monthly Mission', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the list.", 'Update Monthly Mission', 'id=' . $_GET['id']);
            }
        }
        else {
            $GLOBALS['page']->Message("Previous mission must first be ended", 'Monthly Mission', 'id=' . $_GET['id']);
        }
    }
    
    private function delete_mission(){
        $this->users = $GLOBALS['database']->fetch_data("
            SELECT `mission_monthly` 
            FROM `users_missions` 
            WHERE `mission_monthly` IS NOT NULL AND `mission_monthly` != 'Completed' LIMIT 1");
        if( $this->users == "0 rows" ){
            $GLOBALS['database']->execute_query("UPDATE `site_information` SET `value` = '' WHERE `option` = 'monthlyMission' LIMIT 1");
            $GLOBALS['page']->Message("The mission has been ended", 'Monthly Mission', 'id=' . $_GET['id']);
        }
        else{
            $GLOBALS['page']->Message("All submissions must be reviewed before the mission can be ended.", 'Monthly Mission', 'id=' . $_GET['id']);
        }
    }
    
    private function reviewList(){
        
        // Test if giving points
        if( isset($_POST['Submit']) ){
            foreach( $_POST as $key => $post ){
                if( stristr( $key, "userID" ) ){
                    $split = explode(":::", $key);
                    
                    $user = $GLOBALS['database']->fetch_data("
                        SELECT `mission_monthly` 
                        FROM `users_missions` 
                        WHERE `userid` = '" . $split[1] . "' 
                        LIMIT 1");
                    if( $user !== "0 rows" ){
                        
                        // Check
                        if(is_array($post) ){
                            $post = "no";
                        }

                        if( $post == "no" || $post > 0 ){

                            $GLOBALS['database']->execute_query("
                            INSERT INTO `log_monthlyMissions`
                            (`handler`,`handleTime`, `uid`, `url`, `pop_points`) VALUES
                            ('".$GLOBALS['userdata'][0]['username']."', UNIX_TIMESTAMP(), '".$split[1]."', '".$user[0]['mission_monthly']."', '".$post."' ) ");

                            $decision = "";
                            if( $post == "no" ){
                                $decision = "Your submission for the monthly mission was rejected.";
                                $post = 0;
                            }
                            else{
                                $decision = "Your submission for the monthly mission was accepted. ".$post." popularity points awarded.";
                            }

                            $GLOBALS['database']->execute_query("
                                UPDATE `users_missions`, `users_statistics`, `users` 
                                SET 
                                    `mission_monthly` = 'Completed',
                                    `pop_now` = `pop_now` + ".$post.",
                                    `pop_ever` = `pop_ever` + ".$post."
                                WHERE 
                                    `users`.`id` = '" . $split[1] . "' AND
                                    `users`.`id` = `users_statistics`.`uid` AND
                                    `users`.`id` = `users_missions`.`userid`");

                            $users_notifications = new NotificationSystem('', $split[1]);

                            $users_notifications->addNotification(array(
                                                'duration' => 'none',
                                                'text' => $decision,
                                                'dismiss' => 'yes'
                                            ));

                            $users_notifications->recordNotifications();
                        }                        
                    }
                }
            }
        }
        
        // Show form
        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed(30);
        
        $users = $GLOBALS['database']->fetch_data("
            SELECT `mission_monthly`,`username`, `id` 
            FROM `users_missions`,`users`
            WHERE 
                `mission_monthly` IS NOT NULL AND 
                `mission_monthly` != 'Completed' AND 
                `users`.`id` = `users_missions`.`userid`
            ORDER BY `id` DESC LIMIT ".$min.",".$number);
        $GLOBALS['template']->assign('submissions', $users);
        
        
        $counter = $GLOBALS['database']->fetch_data("
            SELECT COUNT(`mission_monthly`) as `count`
            FROM `users_missions`
            WHERE 
                `mission_monthly` IS NOT NULL AND 
                `mission_monthly` != 'Completed'");
        $GLOBALS['template']->assign('total', $counter[0]['count']);
        
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/admin_monthlyMission/showSubmissions.tpl');

    }

}

new monthlyMission();