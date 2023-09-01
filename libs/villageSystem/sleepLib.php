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

class sleepLibrary {

    // Get user information for update on sleep
    private function getUserdataForUpdate($apartment, $action) {

        // Status Check Dependent on Action
        $stat_chk = ($action === 'wakeup') ? 'asleep' : 'awake';

        // Get user information
        switch($apartment) {
            case(true): { // User has a house they are trying to do something in
                if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users`.`apartment`,
                    `users`.`id`, `users`.`status`, `users_statistics`.`regen_rate`, `homes`.`regen`
                    FROM `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                        INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                        INNER JOIN `homes` ON (`homes`.`id` = `users`.`apartment`)
                    WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` = "'.$stat_chk.'"
                        AND `users`.`location` = `users_loyalty`.`village` LIMIT 1 FOR UPDATE'))) {
                    throw new Exception('An error occurred during user data fetch for sleeping!');
                }

                // Check to make sure Query Didn't Fail
                if($this->user === '0 rows' || empty($this->user)) {
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array("You probably already have done what you are doing right now ".
                        "or trying to do something in your house outside the village!"));
                    return false;
                }

            } break;
            case(false): { // User is trying to do something with the camp
                if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users`.`apartment`,`users`.`village`,
                    `users`.`location`, `users`.`id`, `users`.`status`, `users_statistics`.`regen_rate`, `users_statistics`.`rank_id`
                    FROM `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                        INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` = "'.$stat_chk.'"
                        AND `users`.`location` != CONCAT(`users_loyalty`.`village`, " village") LIMIT 1 FOR UPDATE'))) {
                    throw new Exception('An error occurred during user data fetch for sleeping!');
                }

                // Check to make sure Query Didn't Fail
                if($this->user === '0 rows' || empty($this->user)) {
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array("You probably already have done what you are doing right now ".
                        "or trying to do something in your camp inside the village!"));
                    return false;
                }

                // Check location
                if( 
                    (
                        in_array($this->user[0]['location'], ['Shroud','Shine','Samui','Silence','Konoki'])
                        ||
                        (
                            $this->user[0]['village'] != 'Syndicate'
                            &&
                            in_array($this->user[0]['location'], ["Gambler's Den","Bandit's Outpost","Poacher's Camp","Pirate's Hideout"])
                        )
                    )
                    &&
                    $action !== 'wakeup'
                )
                {
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You cannot set up camp inside a village!'));
                    return false;
                }
            } break;
            default: throw new Exception('There was an error trying to determine sleeping in a house or camping!'); break;

        }
        
        // Set Regen Update Based on Home or Village
        $this->regen = (isset($this->user[0]['regen'])) ? $this->user[0]['regen'] : self::getCampRegeneration($this->user[0]['rank_id']);
        return true;
    }

    // Calcualte the gained regeneration
    public function getCampRegeneration($rankID) {

        // Base regen
        if( strtolower($GLOBALS['userdata'][0]['village']) != 'syndicate')
            $regen = $rankID;
        else
            $regen = ($GLOBALS['userdata'][0]['level_id']/2);

        // Loyalty bonus
        if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
            if( $GLOBALS['userdata'][0]['vil_loyal_pts'] <= -300 ){
                $regen += 10;
            }
        }

        // Return the regen
        return $regen;
    }

    // Go to sleep function
    public function sleep($apartment = false) {

        try
        {

            $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);

            // Get/set user info
            if(self::getUserdataForUpdate($apartment, "sleep"))
            {
                // Check that the user is awake, otherwise he can't go to sleep
                if($this->user[0]['status'] !== 'awake') {
                    throw new Exception('You must be awake to go to sleep!');
                }

                if(isset($GLOBALS['page']->inOcean) && $GLOBALS['page']->inOcean)
                {
                    throw new Exception('You must be on land to go to sleep!');
                }

                // Check if the user is harvesting
                if(cachefunctions::getHarvest($_SESSION['uid'])) {
                    throw new Exception("You cannot go to sleep while picking up resources.");
                }

                // Update User to Asleep Status And Updated Regen
                if(($GLOBALS['database']->execute_query('UPDATE `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    SET `users_statistics`.`regen_rate` = `users_statistics`.`regen_rate` + '.$this->regen.',
                        `users`.`status` = "asleep"
                    WHERE `users`.`id` = '.$this->user[0]['id'].' AND `users`.`status` = "awake"')) === false) {
                    throw new Exception('There was an error updating the user information');
                }
                $GLOBALS['Events']->acceptEvent('status', array('new' => 'asleep', 'old' => $GLOBALS['userdata'][0]['status']));


                // Instant update
                $GLOBALS['userdata'][0]['status'] = "asleep";
                $GLOBALS['template']->assign('userStatus', 'asleep');
                $GLOBALS['userdata'][0]['regen_rate'] += $this->regen;
                $GLOBALS['template']->assign("userdata", $GLOBALS['userdata'][0]);

                // Show message to the user
                if(isset($_GET['id']))
                    $GLOBALS['page']->Message("You start relaxing and your regeneration is increased by ".
                        $this->regen." points.", 'Relaxing', 'id='.$_GET['id']);
            }
    
        }
        catch(Exception $e)
        {
            //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);

            throw $e;
        }
        //release lock on self
        $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);
    }

    // Wakeup function
    public function wakeup($apartment = false) {

        // Get/set user info
        if(self::getUserdataForUpdate($apartment, "wakeup"))
        {
            // Check that the user is asleep
            if($this->user[0]['status'] !== 'asleep') {
                throw new Exception('You must be asleep to wake up!');
            }

            // Make sure the user is not crafting / processing anything
            if(!($currentCrafting = $GLOBALS['database']->fetch_data('
                SELECT `users_inventory`.`trade_type`, `items`.`name`, `items`.`type`, COUNT(`users_inventory`.`id`) as `processingCount`
                FROM `users_inventory`
                    INNER JOIN `items` ON (
                        `items`.`id` = `users_inventory`.`iid` AND
                        (`items`.`craftable` = "Yes" OR `items`.`type` = "process" OR `items`.`repairable` = "yes" )
                    )
                WHERE
                    (
                        (
                            `users_inventory`.`uid` = '.$_SESSION['uid'].' AND
                            `users_inventory`.`equipped` = "no" AND
                            (`users_inventory`.`trade_type` IS NULL OR `users_inventory`.`trade_type` != "repair")
                        ) OR
                        (
                            `users_inventory`.`trade_type` = "repair" AND
                            `users_inventory`.`trading` = '.$_SESSION['uid'].'
                        )
                    )AND
                    `users_inventory`.`finishProcessing` != 0')
            )){
                throw new Exception ("Crafting/Processing/Repairing Information Fetch Failed!");
            }

            // Processing/Crafting Finish Status Indicator
            if($currentCrafting[0]['processingCount'] > 0) {
                if( $currentCrafting[0]['type'] == "process"){
                    $this->returnLink = "id=86&page=process";
                        $this->returnMessage = "Go check if you have finished your processing.";
                }
                elseif( $currentCrafting[0]['trade_type'] == "repair" ){
                    $this->returnLink = "id=87";
                    $this->returnMessage = "Go check if you have finished your repairment work.";
                }
                else{
                    $this->returnLink = "id=86&page=crafting";
                    $this->returnMessage = "Go check if you have finished your crafting.";
                }


                throw new Exception("You cannot wake up while crafting, processing or repairing items" );
            }

            // Update User to Awake Status And Updated Regen
            if(($GLOBALS['database']->execute_query('UPDATE `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                SET `users_statistics`.`regen_rate` = `users_statistics`.`regen_rate` - '.$this->regen.',
                    `users`.`status` = "awake"
                WHERE `users`.`id` = '.$this->user[0]['id'].' AND `users`.`status` = "asleep"')) === false) {
                throw new Exception('There was an error updating the user information');
            }
            $GLOBALS['Events']->acceptEvent('status', array('new' => 'awake', 'old' => $GLOBALS['userdata'][0]['status']));

            // Instant update
            $GLOBALS['userdata'][0]['status'] = "awake";
            $GLOBALS['template']->assign('userStatus', 'awake');
            $GLOBALS['userdata'][0]['regen_rate'] -= $this->regen;
            $GLOBALS['template']->assign("userdata", $GLOBALS['userdata'][0]);

            // Message
            if(isset($_GET['id']))
                $GLOBALS['page']->Message("You've gotten up and decided to go outside. No more resting for you.", 'Waking up', 'id='.$_GET['id']);
        }
    }
}