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

require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
require_once(Data::$absSvrPath.'/libs/professionSystem/OccupationData.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');


class respectLib {

    // constructor
    public function __construct(){

        // Extra information
        $this->extraInfo = "";
    }

    // Get list of loyalties
    public function getRewardList(){
        $listOfReqards = array(
            "-10" => "Increase rob chance by 5%",
            "-20" => "Increase rob chance by 7.5%",
            "-40" => "Increase rob chance by 10%",
            "-60" => "Decrease hospital Timer by 2.5 minutes",
            "-80" => "Increase minimum robbed amount by 5%",
            "-90" => "Ability to challenge the current Warlord",
            "-120" => "Decrease hospital Timer by 2.5 minutes(5 minutes total reduction)",
            "-160" => "Increase minimum robbed amount by another 5%",
            "-200" => "+10% base syndicate regeneration",
            "-220" => "AI encounter rate reduced by 15%",
            "-240" => "Decrease hospital Timer by 2.5 minutes(7.5 minutes total reduction)",
            "-260" => "Hospital bills decreased by 25%",
            "-300" => "+10 regeneration during camping",
            "-365" => "3 times more syndicate funds per kill.",
            "-400" => "Reduced Hospital timer by 2.5 minutes.(10 minutes total reduction)",
            "10" => "Standard occupation gains increased by 40%",
            "15" => "Ability to learn a chuunin healing jutsu.",
            "30" => "Standard occupation gains increased by 80%",
            "50" => "Ability to learn village specific jutsu.",
            "75" => "Standard occupation gains increased by 120%",
            "90" => "Ability to challenge the Kage.",
            "120" => "Reduced hospital Timer by 2.5 minutes",
            "140" => "Standard occupation gains increased by 200%",
            "150" => "Reduced hospital Timer by an additional 2.5 minutes (5 minutes total reduction)",
            "170" => "Hospital bills decreased by 15%",
            "200" => "+10% base village regeneration",
            "225" => "AI encounter rate reduced by 15%",
            "250" => "Hospital bills decreased by 25%",
            "275" => "AI encounter rate reduced by an additional 15% (30% reduction total)",
            "300" => "Permission to buy a new class of homes for Elite Jounin.",
            "365" => "3 times more village funds per kill.",
            "400" => "Reduced Hospital timer by an additional 2.5 minutes (7.5minutes total reduction)"
        );
        return $listOfReqards;
    }

    // Moderator Jump Ability
    public function moderator_jump($villageName) {
        try {
            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get user data
            $this->get_user_for_jump($villageName, $_SESSION['uid']);

            // Check user rank
            if($this->user[0]['user_rank'] === null) {
                throw new Exception('User is not a staff member!');
            }

            // If user is asleep, and is jumping to syndicate, he's going to have a bad time
            if( $villageName == "Syndicate" || $GLOBALS['userdata'][0]['village'] == "Syndicate" ){
                if( $this->user[0]['status'] == "asleep" ){
                    throw new Exception("User cannot be asleep when moving to/from Syndicate.");
                }
            }


            // Set diplomacy update
            $this->setDiplomacyUpdate( $villageName );

            // Call update query
            $this->update_user_for_jump();

            // Call commit & return message
            $GLOBALS['database']->transaction_commit();

            // Update instant
            $GLOBALS['userdata'][0]['village'] = $villageName;

            return $this->user[0]['username'].' has joined '.$villageName.' village';

        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback("Error Message: " . $e->getMessage());
            return $e->getMessage();
        }
    }



    // Switch user village with no penalty
    // Does not catch exceptions internally!
    public function switch_user_village( $villageName, $uids ){

        // Start transaction
        $GLOBALS['database']->transaction_start();

        // Do it for each user ID
        foreach( $uids as $uid ){

            // Get user data
            $this->get_user_for_jump($villageName, $uid );

            // If user is asleep, and is jumping to syndicate, he's going to have a bad time
            if( $this->user[0]['status'] == "asleep" ){
                throw new Exception("User is currently asleep? You need to wake up first.");
            }

            // Set diplomacy update
            $this->setDiplomacyUpdate( $villageName );

            // Change respect points as needed
            if( ($this->user[0]['vil_loyal_pts'] > 0 && $villageName == "Syndicate") ||
                ($this->user[0]['vil_loyal_pts'] < 0 && $villageName != "Syndicate"))
            {
                $this->user[0]['vil_loyal_pts'] *= -1;
            }

            // Reset home if jumping to syndicate
            if( $villageName == "Syndicate" ){
                $this->resetHome( $uid );
            }

            // Reset Clan/ANBU/kage/trades/students settings
            $this->resetClan( $uid );
            $this->resetANBU( $uid );
            $this->resetKage();
            $this->resetTrades();
            $this->resetStudents();

            // Call update query
            $this->update_user_for_jump();
        }

        // Call commit & return message
        $GLOBALS['database']->transaction_commit();

    }

    // Get user data for jumping. Transaction safe
    private function get_user_for_jump($newVillage, $uid) {

        // Lock the appropriate tables to perform the jump, so conflicts don't arise
        // If they are trying to jump to a village that doesn't exist, it'll jump to
        // Syndicate instead. Query will fail if in combat.
        if(!($this->user = $GLOBALS['database']->fetch_data('SELECT
                `users`.`username`, `users`.`status`, `users`.`id`, `users`.`apartment`,
                `users`.`location`,
                `users`.`student_1`, `users`.`student_2`, `users`.`student_3`,
                `users_timer`.`regen_cooldown`,
                `users_statistics`.`rank_id`, `users_statistics`.`rank`, `users_statistics`.`user_rank`, `users_statistics`.`regen_rate`,
                `users_occupations`.`occupation`, `users_occupations`.`special_occupation`,
                `users_occupations`.`bountyHunter_exp`, `users_occupations`.`surgeonSP_exp`, `users_occupations`.`surgeonCP_exp`,
                `users_occupations`.`last_gain`, `users_occupations`.`promotion`, `users_occupations`.`level`,
                `users_preferences`.`anbu`, `users_preferences`.`clan`,
                `villages`.`longitude`, `villages`.`latitude`, `villages`.`name` AS `jump_village`,
                `users`.`longitude` As `userLongitude`,
                `users`.`latitude` AS `userLatitude`,
                `users_loyalty`.`village` AS `loyalty_village`, `users_loyalty`.`time_in_vil`, `users_loyalty`.`vil_loyal_pts`,
                `bingo_book`.*,
                `currentVillage`.`leader` As `oldLeader`,
                `spouse_user`.`apartment` AS `spouse_apartment`,
                `spouse_user`.`status` AS `spouse_status`,
                `spouse_user`.`id` AS `spouse_id`,
                `trades`.`id` AS `trade_id`
            FROM `users`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                INNER JOIN `users_occupations` ON (`users_occupations`.`userid` = `users`.`id`)
                INNER JOIN `bingo_book` ON (`bingo_book`.`userid` = `users`.`id`)
                INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                INNER JOIN `villages` AS `currentVillage` ON (`currentVillage`.`name` = `users_loyalty`.`village`)
                INNER JOIN `villages` ON (`villages`.`name` = "'.$newVillage.'")
                LEFT JOIN `trades` ON (`trades`.`uid` = `users`.`id`)
                LEFT JOIN `marriages` ON (`marriages`.`married` = "Yes" AND (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`))
                LEFT JOIN `users` AS `spouse_user` ON (`spouse_user`.`id` = IF(`marriages`.`uid` != `users`.`id`, `marriages`.`uid`, `marriages`.`oid`))
            WHERE `users`.`id` = '.$uid.' AND `users`.`status` IN ("asleep", "awake") LIMIT 1 FOR UPDATE')))
        {
            throw new Exception('1. User (uid '.$uid.') is in battle or hospitalized! Please try again later!');
        }

        if($this->user === '0 rows') {
            throw new Exception('2. User (uid '.$uid.') is in battle or hospitalized! Please try again later!');
        }

        // Set the new user rank
        if(in_array($this->user[0]['user_rank'], array('Member', 'Paid', 'Tester', 'ContentMember'), true)) { // Keep Staff Rank Names
            switch($this->user[0]['rank_id']) { // Change the Rank Title
                case(3): $this->user[0]['rank'] = ($this->user[0]['jump_village'] === 'Syndicate') ? 'Lower Outlaw' : 'Chuunin'; break;
                case(4): $this->user[0]['rank'] = ($this->user[0]['jump_village'] === 'Syndicate') ? 'Higher Outlaw' : 'Jounin'; break;
                case(5): $this->user[0]['rank'] = ($this->user[0]['jump_village'] === 'Syndicate') ? 'Elite Outlaw' : 'Elite Jounin'; break;
            }
        }
    }

    // Update user with new village stuff
    private function update_user_for_jump($keepLocation = false) {

        // Figure out location
        if($keepLocation === true) {
            $location = $this->user[0]['location'];
        }
        else {
            $location = ($this->user[0]['jump_village'] === "Syndicate") ? 'Gambler\'s Den' : $this->user[0]['jump_village'];
        }

        // Update the appropriate fields
        if(($GLOBALS['database']->execute_query('
            UPDATE
                `users`,
                `users_statistics`,
                `users_occupations`,
                `users_loyalty`,
                `bingo_book`,
                `users_preferences`,
                `users_timer`,
                `users_missions`
            SET `users_statistics`.`rank` = "'.$this->user[0]['rank'].'",
                `users_occupations`.`occupation` = '.$this->user[0]['occupation'].',
                `users_occupations`.`special_occupation` = '.$this->user[0]['special_occupation'].',
                `users_occupations`.`last_gain` = '.$this->user[0]['last_gain'].',
                `users_occupations`.`promotion` = '.$this->user[0]['promotion'].',
                `users_occupations`.`level` = '.$this->user[0]['level'].',
                `users_occupations`.`bountyHunter_exp` = '.$this->user[0]['bountyHunter_exp'].',
                `users_occupations`.`surgeonSP_exp` = '.$this->user[0]['surgeonSP_exp'].',
                `users_occupations`.`surgeonCP_exp` = '.$this->user[0]['surgeonCP_exp'].',
                `users_statistics`.`regen_rate` = '.$this->user[0]['regen_rate'].',
                `users_loyalty`.`village` = "'.$this->user[0]['jump_village'].'",
                `users_loyalty`.`time_in_vil` = '.$this->user[0]['time_in_vil'].',
                `users_loyalty`.`vil_loyal_pts` = '.$this->user[0]['vil_loyal_pts'].',
                `users`.`longitude` = '.$this->user[0]['longitude'].',
                `users`.`status` = "'.$this->user[0]['status'].'",
                `users`.`latitude` = '.$this->user[0]['latitude'].',
                `users`.`village` = "'.$this->user[0]['jump_village'].'",
                `users_timer`.`regen_cooldown` = '.$this->user[0]['regen_cooldown'].',
                `users`.`apartment` = '.(empty($this->user[0]['apartment']) ? "NULL" : $this->user[0]['apartment']).',
                `users`.`location` = "'.$location.'",
                `users`.`student_1` = "'.$this->user[0]['student_1'].'",
                `users`.`student_2` = "'.$this->user[0]['student_2'].'",
                `users`.`student_3` = "'.$this->user[0]['student_3'].'",
                `users_preferences`.`anbu` = "'.$this->user[0]['anbu'].'",
                `users_preferences`.`clan` = "'.$this->user[0]['clan'].'",
                `users_missions`.`structureDestructionPoints` = DEFAULT,
                `users_missions`.`structureGatherPoints` = DEFAULT,
                `users_missions`.`structurePointsActivity` = DEFAULT,
                `bingo_book`.`'.$this->user[0]['loyalty_village'].'` = '.$this->user[0][$this->user[0]['loyalty_village']].'
                '. (isset($this->newDipQuery) ? $this->newDipQuery : "") .'
            WHERE `users`.`id` = '.$this->user[0]['id'].' AND
                `users_statistics`.`uid` = `users`.`id` AND
                `users_timer`.`userid` = `users`.`id` AND
                `users_loyalty`.`uid` = `users`.`id` AND
                `bingo_book`.`userid` = `users`.`id` AND
                `users_preferences`.`uid` = `users`.`id` AND
                `users_missions`.`userid` = `users`.`id` AND
                `users_occupations`.`userid` = `users`.`id`'
        )) === false) {
            throw new Exception('There was an error when jumping faction, please try again.');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('village', array('new'=>$this->user[0]['jump_village'], 'old'=>$GLOBALS['userdata'][0]['village'] ));
            $GLOBALS['Events']->acceptEvent('clan', array('new'=>$this->user[0]['clan'], 'old'=>$GLOBALS['userdata'][0]['clan'] ));
            $GLOBALS['Events']->acceptEvent('anbu', array('new'=>$this->user[0]['anbu'], 'old'=>$GLOBALS['userdata'][0]['anbu'] ));
        }
    }

    // Join Village as an outlaw
    public function join_village($villageName) {
        try {
            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get user data
            $this->get_user_for_jump($villageName, $_SESSION['uid']);

            // Reset occupation
            if((int)$this->user[0]['occupation'] !== 0 || (int)$this->user[0]['special_occupation'] !== 0){

                $in = [];
                if((int)$this->user[0]['occupation'] !== 0)
                    $in[] = (int)$this->user[0]['occupation'];

                if((int)$this->user[0]['special_occupation'] !== 0)
                    $in[] = (int)$this->user[0]['special_occupation'];

                if(!($occupation_data = $GLOBALS['database']->fetch_data("SELECT * FROM `occupations`
                    WHERE `occupations`.`id` in (".implode(',',$in).")"))) {
                    throw new Exception('Error obtaining user occupation');
                }

                if($occupation_data !== '0 rows') {
                    $this->user[0]['occupation'] = $this->user[0]['special_occupation'] = $this->user[0]['last_gain'] = $this->user[0]['promotion'] = $this->user[0]['level'] = 0;
                    $this->OccupationData = new OccupationData();
                    
                    if((int)$this->user[0]['occupation'] !== 0)
                        $this->OccupationData->quitNormalOccupation();
                    
                    if((int)$this->user[0]['special_occupation'] !== 0)
                        $this->OccupationData->swapSpecialOccupation('Village');

                    $this->OccupationData->updateCacheForJoinVillage(0,0,0,0,0);

                    //clearing tracking against this user

                    //finding all users that are tracking this user
                    $select_statement = "SELECT `userid` from `users_occupations` WHERE `feature` = '".$this->user[0]['username']."' AND `special_occupation` = '2'";
                    try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push('','there was an error getting battle dodge information.', __METHOD__, __FILE__, __LINE__);
                                throw $e;
                            }
                        }
                    }

                    if( is_array($user_list) && count($user_list) > 0)
                    {
                        //updating the cache of all users that are tracking this user
                        foreach($user_list as $userid)
                        {
                            $OccupationData = new OccupationData($userid['userid']);
                            $OccupationData->setFeature('',true);
                        }

                        //clearing this user from other users tracking in the database
                        $query = "UPDATE `users_occupations` SET `feature` = '' WHERE `feature` = '".$this->user[0]['username']."' AND `special_occupation` = '2'";
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                            catch (Exception $e)
                            {
                                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                                catch (Exception $e)
                                {
                                    $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                                    throw $e;
                                }
                            }
                        }
                    }
                }
            }

            // Update time in village
            $this->user[0]['time_in_vil'] = $GLOBALS['user']->load_time;

            // Remove as warlord
            $this->resetKage();

            // Warning
            $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                                        'id' => 13,
                                                        'duration' => 'none',
                                                        'text' => "You have joined ".$villageName.", your regeneration will be reduced for 4 days!",
                                                        'dismiss' => 'yes'
                                                    ));

            // Regeneration cooldown
            $this->user[0]['regen_cooldown'] = $GLOBALS['user']->load_time + 4*24*3600;

            //purge home inventory to storage box
            HomeHelper::MoveAllToStorageBox($GLOBALS['userdata'][0]['id']);

            //set all diplomacy to 0 in
            $villages = array('Konoki','Silence','Samui','Shroud','Shine','Syndicate');

            $set_string = '';
            foreach($villages as $key => $value)
                if($value == $villageName)
                    unset($villages[$key]);
                else
                    $set_string .= '`bingo_book`.`'.$value.'` = 0 ,';


            $GLOBALS['database']->execute_query('
                                UPDATE
                                    `bingo_book`
                                SET
                                    '.rtrim($set_string,  ',').'
                                WHERE
                                   `bingo_book`.`userid` = '.$_SESSION['uid']);

            // Call update query
            $this->update_user_for_jump();

            // Call commit & return message
            $GLOBALS['database']->transaction_commit();
            return $this->user[0]['username'].' has joined '.$villageName.' village';
        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback("Error Message: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    // Jail the user
    public function jail_user( $userid , $villageJail ){
        try {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get user data
            $this->get_user_for_jump($villageJail, $userid);

            // Reset occupation
            if((int)$this->user[0]['occupation'] !== 0){
                if(!($occupation_data = $GLOBALS['database']->fetch_data("SELECT * FROM `occupations`
                    WHERE `occupations`.`id` = ".$this->user[0]['occupation']." LIMIT 1"))) {
                    throw new Exception('Error obtaining user occupation');
                }

                if($occupation_data !== '0 rows') {
                    $this->user[0]['last_gain'] = $this->user[0]['promotion'] = $GLOBALS['user']->load_time + 86400;
                    $this->OccupationData = new OccupationData();
                    $this->OccupationData->updateCacheForJail($GLOBALS['user']->load_time + 86400);
                }
            }

            // Update time in village
            $this->user[0]['status'] = "jailed";

            // Warning
            $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                                        'id' => 13,
                                                        'duration' => 'none',
                                                        'text' => "You have joined ".$villageName.", your regeneration will be reduced for 4 days!",
                                                        'dismiss' => 'yes'
                                                    ));

            // Regeneration cooldown
            $this->user[0]['regen_cooldown'] = $GLOBALS['user']->load_time + 4*24*3600;

            // Call update query
            $this->update_user_for_jump();

            // Call commit & return message
            $GLOBALS['database']->transaction_commit();
            return $this->user[0]['username'].' has joined '.$villageName.' village';
        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback("Error Message: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    //	Turns the specified user into an outlaw:
    public function turn_outlaw($userid, $keepLocation = false) {
        try {
            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get user data
            $this->get_user_for_jump("Syndicate", $userid);

            // Check user information
            if($this->user[0]['rank_id'] < 3) {
                throw new Exception('User is not high enough rank to be an outlaw. '.
                    'Rank ID must be higher than or equal to 3 and yours is: '.$this->user[0]['rank_id']);
            }

            // Update respect points
            if($this->user[0]['vil_loyal_pts'] > 0) {
                $this->user[0]['vil_loyal_pts'] = ($this->user[0]['vil_loyal_pts'] * 0.5) - 25;
                if( $this->user[0]['vil_loyal_pts'] < 0 ){
                    $this->user[0]['vil_loyal_pts'] = 0;
                }
            }

            $this->user[0]['time_in_vil'] = $GLOBALS['user']->load_time;

            // Update village reputation
            $this->user[0][$this->user[0]['loyalty_village']] = 0;

            // If the user needs to keep location
            if($keepLocation === true) {
                $this->user[0]['longitude'] = $this->user[0]['userLongitude'];
                $this->user[0]['latitude'] = $this->user[0]['userLatitude'];
            }

            // If the user is sleeping, then wake up and change regen
            if( $this->user[0]['status'] == "asleep" ){

                // Figure out the regen
                $regen = 0;
                if( !empty( $this->user[0]['apartment']) && in_array( $this->user[0]['location'], array('Shroud','Shine','Samui','Silence','Konoki') ) ){
                    $home = $GLOBALS['database']->fetch_data("SELECT * FROM `homes` WHERE `homes`.`id` = ".$this->user[0]['apartment']." LIMIT 1");
                    if( $home !== "0 rows" ) {
                        $regen = $home[0]['regen'];
                    }
                }

                // Must be camping then
                if( $regen == 0 ){
                    require_once(Data::$absSvrPath.'/libs/villageSystem/sleepLib.php');
                    $sleepLib = new sleepLibrary();
                    $regen = $sleepLib->getCampRegeneration($this->user[0]['rank_id']);
                }

                // Update the user
                $this->user[0]['status'] = "awake";
                $this->user[0]['regen_rate'] -= $regen;

            }


            // Reset trades & occupation & students
            $this->resetTrades();
            $this->resetOccupation();
            $this->resetStudents();

            //clearing tracking against this user

            //finding all users that are tracking this user
            $select_statement = "SELECT `userid` from `users_occupations` WHERE `feature` = '".$this->user[0]['username']."' AND `special_occupation` = '2'";
            try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error getting battle dodge information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            if(is_array($user_list) && count($user_list) > 0)
            {
                //updating the cache of all users that are tracking this user
                foreach($user_list as $userdata)
                {
                    $OccupationData = new OccupationData($userdata['userid']);
                    $OccupationData->setFeature('',true);
                }

                //clearing this user from other users tracking in the database
                $query = "UPDATE `users_occupations` SET `feature` = '' WHERE `feature` = '".$this->user[0]['username']."' AND `special_occupation` = '2'";
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push($query,'there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
            }

            if(!($marriage = $GLOBALS['database']->fetch_data('
            SELECT *
            FROM `marriages`
            WHERE (`oid` = '.$_SESSION['uid'].' OR `uid` = '.$_SESSION['uid'].') LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive marriage information.');
            }

            // Remove the marriage
            if( isset($this->user[0]['spouse_status']) ){
                $GLOBALS['database']->execute_query('
                    DELETE FROM `marriages`
                    WHERE
                        (`marriages`.`uid` = '.$_SESSION['uid'].' OR
                         `marriages`.`oid` = '.$_SESSION['uid'].')
                    LIMIT 1');

            $GLOBALS['Events']->acceptEvent('marriage', array('data'=>'divorced'));

            $id = 0;
            if($_SESSION['uid'] = $marriage[0]['uid'])
                $id = $marriage[0]['oid'];
            else
                $id = $marriage[0]['uid'];

            $events = new Events($id);
            $events->acceptEvent('marriage', array('data'=>'divorced'));
            $events->closeEvents();
            }

            // Reset Clan/ANBU/kage settings
            $this->resetHome( $userid );
            $this->resetClan( $userid );
            $this->resetANBU( $userid );
            $this->resetKage();

            //purge home inventory to storage box
            HomeHelper::MoveAllToStorageBox($GLOBALS['userdata'][0]['id']);

            // Call update query
            $this->update_user_for_jump($keepLocation);

            // Call commit & return message
            $GLOBALS['database']->transaction_commit();
            return $this->user[0]['username'].' has joined '.$this->user[0]['jump_village'];

        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback("Error Message: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    // Update user bingo book / diplomacy
    private function setDiplomacyUpdate( $villageName ){

        // Switch around diplomacy
        $curOwnDip = $this->user[0][ $this->user[0]['loyalty_village'] ];
        $curNewVilDip = $this->user[0][ $villageName ];

        // If from or to syndicate, switch signs
        if( $this->user[0]['loyalty_village'] == "Syndicate" || $villageName == "Syndicate" ){
            $curOwnDip *= -1;
            $curNewVilDip *= -1;
        }

        // Set query
        $this->newDipQuery = ", `bingo_book`.`".$villageName."` = ".$curOwnDip." ";
        $this->newDipQuery .= ", `bingo_book`.`".$this->user[0]['loyalty_village']."` = ".$curNewVilDip." ";
    }

    // Reset homes
    private function resetHome( $userid ){
        if( !empty( $this->user[0]['apartment'] ) ){

            if($userid == $_SESSION['uid'])
            {
                $GLOBALS['Events']->acceptEvent('home', array('data'=>'lost'));
            }
            else
            {
                $events = new Events($userid);
                $events->acceptEvent('home', array('data'=>'lost'));
                $events->closeEvents();
            }

            // Get the home
            if(!($home = $GLOBALS['database']->fetch_data("SELECT * FROM `homes`
                WHERE `homes`.`id` = ".$this->user[0]['apartment']." LIMIT 1"))) {
                throw new Exception('Error obtaining user home');
            }

            // Remove home from user & married person
            $this->user[0]['apartment'] = "NULL";

            // If married, remove home from spouse as well
            if( $home[0]['married_home'] == "Yes" &&
                !empty( $this->user[0]['spouse_apartment'] ) &&
                $this->user[0]['spouse_apartment'] = $this->user[0]['apartment']
            ){

                // Check if the spuse is asleep. If yes, add to query
                $query = "";
                if( isset($this->user[0]['spouse_status']) && $this->user[0]['spouse_status'] == "asleep" ){
                    if( isset($home[0]['regen']) ){
                        $query = " , `status` = 'awake', `users_statistics`.`regen_rate` = `users_statistics`.`regen_rate` - ".$home[0]['regen']." ";
                        $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                    }
                    else{
                        throw new Exception("Could not identify the house regen of spouse");
                    }
                }

                if($this->user[0]['spouse_id'] == $_SESSION['uid'])
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>'lost'));
                }
                else
                {
                    $events = new Events($this->user[0]['spouse_id']);
                    $events->acceptEvent('home', array('data'=>'lost'));
                    $events->closeEvents();
                }

                // Run query
                $GLOBALS['database']->execute_query("
                    UPDATE `users`,`users_statistics`
                    SET
                        `apartment` = NULL ".$query."
                    WHERE
                        `id` = '" . $this->user[0]['spouse_id'] . "' AND
                        `id` = `uid`" );
            }

            //die("Terr is testing, please don't report and hold on while I fix epix stuff :) ");
        }
        else{
            $this->user[0]['apartment'] = "'".$this->user[0]['apartment']."'";
        }
    }

    // Reset Clan Stuff
    private function resetClan( $userid ){
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();
        if($clanLib->isUserClan($this->user[0]['clan'])) {
            try {
                $clanLib->doResign($userid);
                $this->user[0]['clan'] = "_none";
            }
            catch(Exception $e) {
                // Do nothing with exceptions
            }
        }
        $clanLib->removeUserApplications($userid);
    }

    // Reset ANBu stuff
    private function resetANBU( $userid ){
        require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
        $anbuLib = new anbuLib();
        if( $anbuLib->isUserAnbu($this->user[0]['anbu'])) {
            try {
                $anbuLib->ANBUresign($userid, $this->user[0]['anbu']);
                $this->user[0]['anbu'] = "_none";
            }
            catch(Exception $e) {
                // Do nothing with exceptions
                $this->extraInfo .= "<br>".$e->getMessage();
                echo $this->extraInfo;
            }
        }
    }

    // Reset Kage stuff
    private function resetKage(){
        if($this->user[0]['oldLeader'] === $this->user[0]['username']) {
            $GLOBALS['database']->execute_query('UPDATE `villages`
                SET `villages`.`leader` = "'.Data::$VILLAGE_KAGENAMES[$this->user[0]['loyalty_village']].'"
                WHERE `villages`.`name` = "'.$this->user[0]['loyalty_village'].'" LIMIT 1');

            $events = new Events($GLOBALS['userdata'][0]['leader']);
            $events->acceptEvent('kage', array('data'=>'removed'));
            $events->closeEvents();
        }
    }

    // Reset trades
    private function resetTrades(){
        if( $this->user[0]['trade_id'] !== null ){

            // Remove trades
            if(($GLOBALS['database']->execute_query('DELETE FROM `trades`
                WHERE `trades`.`uid` = '.$this->user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove trades information!');
            }

            // Update inventory
            $GLOBALS['database']->execute_query("
                 UPDATE `users_inventory`
                 LEFT JOIN `trades` ON `trades`.`id` = `users_inventory`.`trading`
                 SET `trading` = NULL,
                     `users_inventory`.`trade_type` = NULL
                 WHERE
                    `users_inventory`.`trading` IS NOT NULL AND
                    `users_inventory`.`trade_type` != 'repair' AND
                    `trades`.`id` IS NULL");

            // Remove offers without trade
            $GLOBALS['database']->execute_query("DELETE `trade_offers`.*
                                            FROM `trade_offers`
                                            LEFT JOIN `trades` ON `trades`.`id` = `trade_offers`.`tid`
                                            WHERE `trades`.`id` is null");
        }
    }

    private function resetOccupation(){
        if((int)$this->user[0]['special_occupation'] !== 0 || (int)$this->user[0]['occupation'] !== 0)
        {
            $id=0;
            $this->OccupationData = new OccupationData();

            if((int)$this->user[0]['special_occupation'] !== 0)
            {
                if(!($occupation_data = $GLOBALS['database']->fetch_data("SELECT * FROM `occupations`
                WHERE `occupations`.`id` = ".$this->user[0]['special_occupation']." LIMIT 1"))) {
                    throw new Exception('Error obtaining user special occupation');
                }

                if($occupation_data !== '0 rows') {
                    $this->user[0]['occupation'] = $this->user[0]['last_gain'] = $this->user[0]['promotion'] = $this->user[0]['level'] = 0;
                    $id = $this->OccupationData->swapSpecialOccupation('Syndicate');
                    $this->user[0]['special_occupation'] = $id;
                }
            }
            
            if((int)$this->user[0]['occupation'] !== 0){
                if(!($occupation_data = $GLOBALS['database']->fetch_data("SELECT * FROM `occupations`
                WHERE `occupations`.`id` = ".$this->user[0]['occupation']." LIMIT 1"))) {
                    throw new Exception('Error obtaining user occupation');
                }
                
                if($occupation_data !== '0 rows') {
                    $this->user[0]['occupation'] = $this->user[0]['last_gain'] = $this->user[0]['promotion'] = $this->user[0]['level'] = 0;
                    $this->OccupationData->quitNormalOccupation();
                }
            }

            $this->OccupationData->updateCacheForJoinVillage(0,$id,0,0,0);
        }
    }

    private function resetStudents(){
        foreach( array("student_1","student_2","student_3") as $sPos ){
            if ($this->user[0][$sPos] != '_none') {
                $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `sensei` = '' WHERE `uid` = '" . $this->user[0][$sPos] . "' LIMIT 1");
                $this->user[0][ $sPos ] = "_none";
            }
        }
    }

}