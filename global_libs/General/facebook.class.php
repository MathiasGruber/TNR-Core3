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

    class FBinteract {

        public function fbConnect() {

            // Get facebook SDK
            require_once(Data::$absSvrPath.'/global_libs/FB_src/facebook.php');

            // Connect to facebook
            $this->config = array(
                'appId' => Data::$fbAppID,
                'secret' => Data::$fbAppSecret,
                'fileUpload' => false // OPTIONAL
            );

            $this->facebook = new Facebook($this->config);
        }

        public function getUser(){
            $uid = $this->facebook->getUser();
            if ($uid) {
              try {
                $user_profile = $this->facebook->api('/me');
              } catch (FacebookApiException $e) {
                  echo $e->getMessage();
                $uid = 0;
              }
            }
            return $uid;
        }

        public function getScore(){

            // Connnect
            $this->fbConnect();

            try {
                $ref = $this->facebook->api('/me/scores', 'GET' );
                //print_r($ref );
            } catch (FacebookApiException $e) {
                echo $e->getMessage();
            }
        }

        public function postScore( $score){
            // Connnect
            $this->fbConnect();

            try {
                $ref = $this->facebook->api('/me/scores', 'POST', array('score' => $score) );
                //print_r($ref );
            } catch (FacebookApiException $e) {
                echo $e->getMessage();
            }
        }

        public function registerAchievement( $id ){
            $achievement = Data::$domainName.'/clusterup/achievement.php?id='.$id;
            $app_token = $this->config['appId'].'|'.$this->config['secret'];

            $result = $this->facebook->api(
                    '/'.$this->config['appId'].'/achievements',
                    'post',
                    array(
                        'access_token' => $app_token,
                        'achievement' => $achievement,
                        'display_order' => 1
                   )
            );
        }

        public function deleteAchievement( $id ){
            $achievement = Data::$domainName.'/clusterup/achievement.php?id='.$id;
            $app_token = $this->config['appId'].'|'.$this->config['secret'];

            $result = $this->facebook->api(
                    '/'.$this->config['appId'].'/achievements',
                    'delete',
                    array(
                        'access_token' => $app_token,
                        'achievement' => $achievement
                   )
            );
        }


         public function giveAchievement( $id, $name ){
            try {
                $achievement = Data::$domainName.'/clusterup/achievement.php?id='.$id;
                $result = $this->facebook->api(
                        '/me/achievements',
                        'post',
                        array(
                            'achievement' => $achievement
                       )
                );
                return true;
            } catch (FacebookApiException $e) {
                return false;
            }
        }

        public function getAchievement( $title ){
            try {
                $ref = $this->facebook->api('/'.$this->config['appId'].'/achievements/', 'GET' );
                foreach( $ref['data'] as $entry ){
                    if( $entry['title'] == $title ){
                        return true;
                    }
                }
                return false;
            } catch (FacebookApiException $e) {
                echo $e->getMessage();
                return false;
            }
        }

        public function getAchievements(){
            try {
                return $this->facebook->api('/'.$this->config['appId'].'/achievements/', 'GET' );
            } catch (FacebookApiException $e) {
                echo $e->getMessage();
                return false;
            }
        }

        public function getAccessToken(){
            return $this->facebook->getAccessToken();
        }

        public function getLoginStatusUrl(){
            return $this->facebook->getLoginStatusUrl();
        }

        public function getLogoutUrl(){
            return $this->facebook->getLogoutUrl();
        }

        public function api($blah){
            return $this->facebook->api($blah);
        }

        public function getLoginUrl(){
            return $this->facebook->getLoginUrl();
        }

        public function getUserInfo(){
            try {
                return $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                return false;
            }
        }

        public function registerUserWithFB($uid) {
            if ($GLOBALS['userdata'][0]['fbID'] == 0) {
                // Set fbID for user
                $GLOBALS['userdata'][0]['fbID'] = $uid;
                $GLOBALS['database']->execute_query("UPDATE `users` SET `fbID` = '0'
                                                     WHERE `fbID` = '" . $uid . "' ");
                $GLOBALS['database']->execute_query("UPDATE `users` SET `fbID` = '" . $uid . "', `activation` = '1'
                                                     WHERE `username` = '" . $GLOBALS['userdata'][0]['username'] . "'
                                                     LIMIT 1");

                // Check if any rewards should be given
                $requests = $GLOBALS['database']->fetch_data("SELECT * FROM `fbRequests` WHERE `fbID` = '" . $uid . "' AND `status` = '' LIMIT 1");
                if ($requests !== "0 rows") {
                    $newStatus = "";

                    // User who invited this user
                    $inviter = $GLOBALS['database']->fetch_data("SELECT `id`,`last_ip` FROM `users`,`users_timer` WHERE `username` = '" . $requests[0]['username'] . "' AND `id` = `userid` LIMIT 1");
                    if ($inviter !== "0 rows") {

                        // Check IP
                        if ( $GLOBALS['user']->real_ip_address() !== $inviter[0]['last_ip'] || 1 == 1) {
                            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `pop_now` = `pop_now` + 2, `pop_ever` = `pop_ever` + 2 WHERE `uid` = '" . $inviter[0]['id'] . "' LIMIT 1");
                            $GLOBALS['Events']->acceptEvent('pop_gain', array('old'=>$GLOBALS['userdata'][0]['pop_now'],'new'=> $GLOBALS['userdata'][0]['pop_now'] + 2));
                            $newStatus = "Rewarded";
                        } else {
                            $newStatus = "IpDeny";
                        }
                    } else {
                        $newStatus = "Error";
                    }

                    // Update the fbRequests table
                    $GLOBALS['database']->execute_query("UPDATE `fbRequests` SET `status` = '" . $newStatus . "' WHERE `fbID` = '" . $uid . "' LIMIT 1");
                }
            }
        }
    }                   