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

class monthlyMission extends functions {

    // Constructor
    public function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        $this->getMonthlyMission();
        if (!isset($_GET['act'])) {
            $this->main_page();
        } elseif ($_GET['act'] == "submitMission") {
            $this->submit_mission();
        }

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }

    private function getMonthlyMission(){
        $this->mission = $GLOBALS['database']->fetch_data("SELECT `value` FROM `site_information` WHERE `option`='monthlyMission' LIMIT 1");
    }

    private function getUserMission(){
        $this->user = $GLOBALS['database']->fetch_data("SELECT `mission_monthly` FROM `users_missions` WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1");
        if( $this->user[0]['mission_monthly'] == "Completed" ){
            $this->user[0]['mission_monthly'] = null;
        }
    }

    // Main page
    public function main_page() {

        // Get the mission
        $this->getUserMission();

        $GLOBALS['template']->assign('missionText', $this->mission[0]['value']);
        $GLOBALS['template']->assign('didMission', $this->user[0]['mission_monthly']);

        $GLOBALS['template']->assign('contentLoad', './templates/content/missions/monthlyMission/mainForm.tpl');
    }

    private function submit_mission(){
        $url_pattern = '/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#& ]+\.([a-zA-Z0-9\.\/\?\:@\-_=#& ])*/';
        if( preg_match( $url_pattern , $_POST['url']) )
        {
            $this->getUserMission();
            if( $this->user[0]['mission_monthly'] !== "Completed" ){
                $this->user = $GLOBALS['database']->execute_query("UPDATE `users_missions` SET `mission_monthly` = '".$_POST['url']."' WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                $GLOBALS['page']->Message("Your result has been submitted and will be reviewed by an admin shortly. Thank you for your support.", 'Monthly Mission', 'id='.$_GET['id']);
            }
            else{
                $GLOBALS['page']->Message("You already completed this mission. Thank you for your support.", 'Monthly Mission', 'id='.$_GET['id']);
            }
        }
        else{
            $GLOBALS['page']->Message("This URL could not be recognised by the system:<br>".$_POST['url'] , 'Monthly Mission', 'id='.$_GET['id']);
        }
    }


}

new monthlyMission();