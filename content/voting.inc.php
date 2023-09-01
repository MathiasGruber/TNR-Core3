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

// Class definition
class vote {

    // Constructore
    public function __construct() {

        // Run in try
        try {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Set settings
            $this->calcSettings();

            // Send data to analytics
            $GLOBALS['page']->sendTransactionsToAnalytics();

            // Load page
            if ((isset($_GET['act'])) && ($_GET['act'] == 'GALAXY') ) {
                $this->galaxy();
            } elseif ((isset($_GET['act'])) && ($_GET['act'] == 'OGLAB') ) {
                $this->OGLAB();
            } elseif ((isset($_GET['act'])) && ($_GET['act'] == 'mobile_advertisement') && functions::isApiCall() ) {
                $this->mobileAd();
            } else{
                $this->show_form();
            }


            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), 'Voting System', 'id=2');
        }
    }

    // Calculate gain percentage
    private function percentage( $max ) {
        switch ( $GLOBALS['userdata'][0]['rank_id'] ) {
            case 1: $per = 1 * ceil(100 * (90 + (5000 / 50000) * $max) / $max);
                break;
            case 2: $per = 1 * ceil(100 * (90 + (5000 / 50000) * $max) / $max);
                break;
            case 3: $per = ceil(100 * (90 + (5000 / 50000) * $max) / $max);
                break;
            case 4: $per = 0.8 * ceil(100 * (90 + (5000 / 50000) * $max) / $max);
                break;
            case 5: $per = 0.65 * ceil(100 * (90 + (5000 / 50000) * $max) / $max);
                break;
            default: $GLOBALS['error']->handle_error('500', 'You do not have a valid rank recognizible by the system', '0');
                break;
        }
        return $per;
    }

    // Calculate new stats
    private function calcSettings() {

        // Set percentages
        $this->staPer = $this->percentage($GLOBALS['userdata'][0]['max_sta'] );
        $this->chaPer = $this->percentage($GLOBALS['userdata'][0]['max_cha'] );

        // Set new stats based on percentages
        $this->newSta = $this->newsta();
        $this->newCha = $this->newcha();
    }

    // Calculate new STA
    private function newsta() {
        $temp = (($this->staPer / 100)) * $GLOBALS['userdata'][0]['max_sta'] + $GLOBALS['userdata'][0]['cur_sta'];
        if ($temp > $GLOBALS['userdata'][0]['max_sta']) {
            $temp = $GLOBALS['userdata'][0]['max_sta'];
        }
        return $temp;
    }

    // Calculate new CHA
    private function newcha() {
        $temp = (($this->chaPer / 100)) * $GLOBALS['userdata'][0]['max_cha'] + $GLOBALS['userdata'][0]['cur_cha'];
        if ($temp > $GLOBALS['userdata'][0]['max_cha']) {
            $temp = $GLOBALS['userdata'][0]['max_cha'];
        }
        return $temp;
    }

    // Get added value
    private function getAddedSta(){
        return round($this->newSta - $GLOBALS['userdata'][0]['cur_sta'],2);
    }
    private function getAddedCha(){
        return round($this->newCha - $GLOBALS['userdata'][0]['cur_cha'],2);
    }

    // Show form with the voting sites
    private function show_form() {

        // Get timers
        $timers = $GLOBALS['database']->fetch_data("SELECT * FROM `votes` WHERE `userID` = '" . $_SESSION['uid'] . "' LIMIT 1");
        if ($timers == '0 rows') {
            $GLOBALS['database']->execute_query("INSERT INTO `votes` ( `userID` ) VALUES (" . $_SESSION['uid'] . ")");
            $timers = $GLOBALS['database']->fetch_data("SELECT * FROM `votes` WHERE `userID` = '" . $_SESSION['uid'] . "' LIMIT 1");
        }

        $GLOBALS['template']->assign('chakra', $this->chaPer);
        $GLOBALS['template']->assign('stamina', $this->staPer);
        $GLOBALS['template']->assign('timer', $timers);

        // Show form
        $GLOBALS['template']->assign('contentLoad', './templates/content/voting/voting.tpl');
    }

    // OG labs site voting
    private function OGLAB() {

        $check_voted = $GLOBALS['database']->fetch_data("SELECT `OGLAB` FROM `votes` WHERE `userID` = '" . $_SESSION['uid'] . "' LIMIT 1");

        if ($check_voted[0]['OGLAB'] < $GLOBALS['user']->load_time - 86400) {
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$this->newSta, 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$this->newCha, 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));

            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `cur_sta` = '" . $this->newSta . "', `cur_cha` = '" . $this->newCha . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            $GLOBALS['database']->execute_query("UPDATE `votes` SET `OGLAB` = " . $GLOBALS['user']->load_time . " WHERE `userID` = '" . $_SESSION['uid'] . "' LIMIT 1");
            header('Location: http://www.oglabs.de/oglabs.php?s=vote&game=424');
        }
    }

    // Galaxy site voting
    private function galaxy() {
        $check_voted = $GLOBALS['database']->fetch_data("SELECT `GALAXY` FROM `votes` WHERE `userID` = '" . $_SESSION['uid'] . "' LIMIT 1");

        if ($check_voted[0]['GALAXY'] < $GLOBALS['user']->load_time - 86400) {
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$this->newSta, 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$this->newCha, 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));

            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `cur_sta` = '" . $this->newSta . "', `cur_cha` = '" . $this->newCha . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            $GLOBALS['database']->execute_query("UPDATE `votes` SET `GALAXY` = " . $GLOBALS['user']->load_time . " WHERE `userID` = '" . $_SESSION['uid'] . "' LIMIT 1");
            header('Location: http://www.mmofacts.com/ninja-mmorpg-3756');
        }
    }

    // Mobile Rewarded Ad
    private function mobileAd() {
        $check_voted = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count` FROM `log_mobileAdViews` WHERE `uid` = '" . $_SESSION['uid'] . "' AND `time` > '".($GLOBALS['user']->load_time-24*3600)."' ");

        if ( $check_voted == "0 rows" || $check_voted[0]['count'] < 5) {
            if( $this->getAddedCha() > 0 || $this->getAddedSta() > 0 ){
                $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$this->newSta, 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
                $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$this->newCha, 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));

                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `cur_sta` = '" . $this->newSta . "', `cur_cha` = '" . $this->newCha . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                $GLOBALS['database']->execute_query("INSERT INTO `log_mobileAdViews`  (`uid`, `time`, `platform`, `deviceID`, `data`)  VALUES ('".$_SESSION['uid']."','".time()."','".$_GET["platform"]."','".$_GET["deviceID"]."','')");
                $GLOBALS['page']->Message("Thank you for your support! ".$this->getAddedCha()." chakra and ".$this->getAddedSta()." stamina has been added to your character.", 'Rewarded Ads', 'id=2');
            }
            else{
                $GLOBALS['page']->Message("Thank you for your support! No more chakra or stamina could be added to your character though. Feel free to watch another ad once you need more chakra or stamina for your character.", 'Rewarded Ads', 'id=71');
            }
        }
        else{
            $check_voted = $GLOBALS['database']->fetch_data("SELECT `time`,`id` FROM `log_mobileAdViews` WHERE `uid` = '" . $_SESSION['uid'] . "' ORDER BY `time` DESC LIMIT 1");
            $timeLeft = (24*3600)-($GLOBALS['user']->load_time - $check_voted[0]['time']);
            $GLOBALS['page']->Message("We are extremely grateful for your support. However, you have already viewed 5 ads within the last 24h, and so must wait before you can claim your next reward. Time till rewards are re-enabled: ".functions::convert_time( $timeLeft , "adTimer")." ", 'Rewarded Ads', 'id=71');
        }
    }





}

new vote();