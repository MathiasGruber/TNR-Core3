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

class registrationLib
{
    public function registerUser( $userData ){

        // Set username
        if( isset( $userData['username'] ) ){
            $this->username = $userData['username'];
        } else { throw new Exception("Must have username"); }

        // Set password
        if( isset( $userData['saltedPassword'] ) ){
            $this->saltedassword = $userData['saltedPassword'];
            $this->password = "";
        } elseif( isset( $userData['password'] ) ) {
            $this->password = $userData['password'];
            $this->saltedassword = "";
        } else {
            throw new Exception("Must have password");
        }

        // Set email
        if( isset( $userData['mail'] ) ){
            $this->email = $userData['mail'];
        } else { throw new Exception("Must have mail"); }

        // Set registrant gender
        if( isset( $userData['gender'] ) ){
            $this->gender = $userData['gender'];
        } else { throw new Exception("Must have gender"); }

        // Set registrant IP address
        if( isset( $userData['pageTime'] ) ){
            $this->pageTime = $userData['pageTime'];
        } else {
            $this->pageTime = $this->time();
        }

        // Set registrant IP address
        if( isset( $userData['ip'] ) ){
            $this->ip = $userData['ip'];
        } else {
            $this->ip = $GLOBALS['user']->real_ip_address();
        }

        // Set registrant village, or pick random village
        if( isset( $userData['village'] ) ){
            if(in_array($userData['village'], Data::$VILLAGES) ){
                $this->villageName = $userData['village'];
            }
            else{
                $this->villageName = $this->get_random_village();
            }
        } else {
            $this->villageName = $this->get_random_village();
        }

        // Set starting reputation points
        if( isset( $userData['startReps'] ) ){
            $this->startReps = $userData['startReps'];
        } else {
            $this->startReps = 0;
        }

        // Set registrant clan element
        if( isset( $userData['clanElement'] ) ){
            $this->clanElement = $userData['clanElement'];
        } else {
            $this->clanElement = Elements::getRandomElement();
        }

        // Set layout
        if( isset( $userData['layout'] ) ){
            $this->layout = $userData['layout'];
        } else {
            $this->layout = "default";
        }

        // Facebook
        if( isset( $userData['facebook'] ) ){
            $this->facebookSetting = $userData['facebook'];
        } else {
            $this->facebookSetting = 0;
        }

        // Set activation
        if( isset( $userData['activation'] ) ){
            $this->activation = $userData['activation'];
        } else {
            if( $this->facebookSetting !== false && $this->facebookSetting !== 0 ){
                $this->activation = 1;
            }
            else{
                $this->activation = 0;
            }
        }

        // Overwrite user ID
        $this->userID = "NULL";
        if( isset( $userData['uid'] ) ){
            $this->userID = $userData['uid'];
        }

        // Do the registration
        return $this->do_register();

    }

    // Get random village
    private function get_random_village(){
        return Data::$VILLAGES[ random_int(1, count(Data::$VILLAGES) ) ];
    }

    // Do the actual registration
    private function do_register() {

        // Get Village Data
        $village_data = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `name` = '" . $this->villageName . "' AND `registration_choice` = 'yes' LIMIT 1");
        if ($village_data !== '0 rows') {

            // Clan Data
            $clan_data = $GLOBALS['database']->fetch_data("SELECT * FROM `clans` WHERE `village` = '" . $this->villageName  . "' AND `clan_type` = 'core' AND `element` = '" . $this->clanElement . "' LIMIT 1");
            if ($clan_data !== "0 rows") {

                // Elemental Affinities
                $elementAffinity = Elements::createRandomUserAffinities();

                if ($GLOBALS['database']->execute_query("
                    INSERT INTO `users`
                        (`id`,`username`, `password`, `salted_password`,
                        `mail`, `join_date`, `join_ip`, `last_ip`,
                        `last_ua`, `fbID`, `gender`, `village`,
                        `latitude`, `longitude`, `location`, `activation`)
                        VALUES
                        ('".$this->userID."', '" . $this->username . "', '" . $this->password . "',
                         '" . $this->saltedassword . "', '" . $this->email . "',
                         '" . $this->pageTime . "', '" . $this->ip . "', `join_ip`,
                         '" . $_SERVER['HTTP_USER_AGENT'] . "', '" . $this->facebookSetting . "',
                         '" . $this->gender . "', '" . $village_data[0]['name'] . "',
                         '" . $village_data[0]['latitude'] . "', '" . $village_data[0]['longitude'] . "',
                         '" . $village_data[0]['name'] . " village', '" . $this->activation . "');")) {

                    // Set user ID
                    $this->id = $GLOBALS['database']->get_inserted_id();

                    // Facebook profile picture
                    if ( isset($this->facebookSetting) && $this->facebookSetting !== 0 ) {
                        $fbUserInfo = $GLOBALS['facebook']->getUserInfo();
                        $content = file_get_contents("https://graph.facebook.com/" . $this->facebookSetting . "/picture?width=100&height=100");
                        file_put_contents('./images/avatars/' . $this->id . '.jpg', $content);
                    }

                    if ($GLOBALS['database']->execute_query(
                            "INSERT INTO `users_statistics`
                            (`uid`, `element_affinity_1`, `element_affinity_2`, `rep_ever`,`rep_now`) VALUES
                            ('" . $this->id . "', '" . $elementAffinity[0] . "', '". $elementAffinity[1] ."',
                             '" . $this->startReps . "', '" . $this->startReps . "' );")) {

                        if ($GLOBALS['database']->execute_query("INSERT INTO `users_preferences` (`uid`, `layout`,`clan`) VALUES ('" . $this->id . "','" . $this->layout . "', '" . $clan_data[0]['id'] . "');")) {

                            if ($GLOBALS['database']->execute_query("INSERT INTO `users_loyalty` (`uid`, `village`, `time_in_vil`, `vil_pts_timer`) VALUES ('" . $this->id . "', '" . $village_data[0]['name'] . "', UNIX_TIMESTAMP(), `time_in_vil`);")) {

                                if ($GLOBALS['database']->execute_query("INSERT INTO `users_missions` (`userid`) VALUES ('" . $this->id . "');")) {

                                    if ($GLOBALS['database']->execute_query("INSERT INTO `users_timer` (`userid`) VALUES ('" . $this->id . "');")) {

                                        if ($GLOBALS['database']->execute_query("INSERT INTO `users_occupations` (`userid`) VALUES ('" . $this->id . "');")) {

                                            if ($GLOBALS['database']->execute_query("INSERT INTO `bingo_book` (`userID`, `" . $village_data[0]['name'] . "`) VALUES ('" . $this->id . "', 100);")) {
                                                return true;
                                            } else {
                                                throw new Exception("Error inserting bingo book data.");
                                            }
                                        } else {
                                            throw new Exception("Error occupation data.");
                                        }
                                    } else {
                                        throw new Exception("Error inserting timer data.");
                                    }
                                } else {
                                    throw new Exception("Error inserting mission data..");
                                }
                            } else {
                                throw new Exception("Error inserting loyalty data.");
                            }
                        } else {
                            throw new Exception("Error inserting preference data.");
                        }
                    } else {
                        throw new Exception("Error inserting statistic data.");
                    }
                } else {
                    throw new Exception("Error inserting user data.");
                }
            } else {
                throw new Exception("Could not find a matching clan");
            }
        } else {
            throw new Exception("No messing with the form data.");
        }
    }

}