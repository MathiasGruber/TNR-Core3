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

require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');

class tasks {

    public function __construct() {

        // A variable for storing information about rewards
        $this->rewardInformation = "";

        // An array for storing completed entries & their rewards
        $this->rewardInfo = array();

        // Stat gains
        $this->statGains = array();

        // getting combat history
        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `requirement_ignore` = 'no' AND `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' ORDER BY `time` DESC";

        try { if(! $this->battle_history = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $this->battle_history = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $this->battle_history = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error getting battle history information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
    }

    // Checks all tasks, orders, quests and missions to see if the user completed any.
    // Parameter array can have following keys:
    //
    // hook => e.g. "profile", the place from which the check is called
    // allTasks => an array of the tasks to check. If none supplied, it will look for all
    // userTasks => an array with the tasks-item from the users-missions table. Can be specified, or else will be retrieved
    // id => if you only want to check if a single mission has been completed
    public function checkTasks( $parameters ) {

        // Get Data information
        $this->getUserStats();

        // Set a value for how many times this task was performed previously
        // e.g. for missions/crimes, it's important to know how many times the mission
        // was performed during the day, since rewards are scaled accordingly.
        if( isset($parameters['timesPerformed']) ){
            $this->timesPerformed = $parameters['timesPerformed'];
        }

        // Get user entries
        if( isset($parameters['userTasks']) ){
            $this->userTasks = $parameters['userTasks'];
        }
        else{
            $this->userTasks = cachefunctions::getUserTasks( $_SESSION['uid'] );
            $this->userTasks = json_decode($this->userTasks[0]['tasks'] , true);
        }

        // All entries (entry = task, order, mission and quests)
        if( isset($parameters['allTasks']) ){
            $allEntries = $parameters['allTasks'];
        }
        else{
            $allEntries = cachefunctions::getTasksQuestsMissions();
        }

        // Delete mission log
        $this->deleteMissionLog = false;

        // Battle types to delete from battle log
        $this->deleteBattleLogTypes = array();
        $this->tempBattleLogTypes = array();

        // Return messages
        $rewardArray = array();

        // Something has happened
        $somethingHappen = false;

        foreach( $allEntries as $entry ){

            // Check if we are deleting a mission locally
            $localMissionDelete = $this->deleteMissionLog;

            // Check if we should delete battle types
            $this->tempBattleLogTypes = array();

            // Ignore all the entries already completed
            if(
                // If only checking single entry, only check that entry
                ( isset($parameters['id']) && $parameters['id'] == $entry['id']) ||
                // Check all entries if no single entry has been chosen
                ( !isset($parameters['id'] ) &&
                // Sort on level
                $this->user[0]['level_id'] >= $entry['levelReq'] &&
                // Sort on level
                $this->user[0]['level_id'] <= $entry['levelMax'] &&
                // Ignore all admin entries
                $entry['type'] !== "admin" &&
                (
                    // All tasks & orders that have not yet been completed
                    ( preg_match( "/(order|task)/i", $entry['type'] ) && (!isset( $this->userTasks[''.$entry['id'].''] ) || $this->userTasks[''.$entry['id'].''] != "c") ) ||
                    // All quests that have been activated, but not completed
                    ( preg_match( "/(quest)/i", $entry['type'] ) && isset( $this->userTasks[''.$entry['id'].''] ) && $this->userTasks[''.$entry['id'].''] != "c" )
                ))
            ){

                // Get all requirements for this quest
                if ($entry['requirements'] !== ""){

                    // Pass Variable. Set to false if check fails
                    $check = true;

                    // Check the restrictions. No need to run through items that don't pass these
                    if( isset($entry['restrictions']) && $entry['restrictions'] !== "" ){
                        $check = $this->checkRestrictions( $entry['restrictions'] );
                    }

                    if( $check == true ){

                         // If a quest with a timer, don't award if nothing happened
                        if( $entry['type'] == "quest" && $entry['questTime'] !== null ){
                            if( !isset($this->userTasks[ $entry['id']."_time" ]) ){
                                $this->userTasks[ $entry['id']."_time" ] = $GLOBALS['user']->load_time;
                                $this->updateUserTasks( $_SESSION['uid'], $this->userTasks );
                            }
                            $timeLeft = ($this->userTasks[ $entry['id']."_time" ]+$entry['questTime']) - $GLOBALS['user']->load_time;
                            if( $timeLeft < 0 ){
                                $check = false;
                                $somethingHappen = true;
                                unset( $this->userTasks[ $entry['id'] ] );
                            }
                        }

                        if( $check == true ){

                            // Loop threough all requirements
                            $requirements = explode(";", $entry['requirements'] );
                            foreach( $requirements as $requirement ){
                                $requirement = trim($requirement);
                                if( $requirement !== "" && $check == true ){
                                    $check = $this->checkRequirement( $requirement );
                                }
                            }

                            // See if the user still passes or not
                            if( $check == true ){

                                // Something passed
                                $somethingHappen = true;

                                // Give facebook achievement
                                if( $entry['facebookAchievment'] == "yes" ){

                                    // Include facebook library for facebook achievement upload
                                    if( !isset($this->facebook) ){
                                        require_once(Data::$absSvrPath.'/global_libs/General/facebook.class.php');
                                        $this->facebook = new FBinteract;
                                        $this->facebook->fbConnect();
                                    }

                                    // Give achievement
                                    $this->facebook->giveAchievement( $entry['id'],$entry['name'] );
                                }

                                // Add to array
                                if( !isset($this->rewardInfo[ $entry['id'] ]) ){
                                    $this->rewardInfo[ $entry['id'] ] = array("id" => $entry['id'], "name" => $entry['name'], "rewards" => array() );
                                }

                                // Add to users task array
                                $this->userTasks[$entry['id']] = "c";

                                // If quest, add finish time
                                if( $entry['type'] == "quest" ){
                                    $this->userTasks[$entry['id']."_time"] = $GLOBALS['user']->load_time;
                                }

                                // Add Rewards
                                if( isset($entry["rewards"]) && $entry["rewards"] !== "" ){
                                    $rewardArray[] = $entry["rewards"];
                                }

                                // Save for potential later user
                                if( isset($entry['simpleGuide']) ){
                                    $simpleGuide = $this->sortSimpleGuide( $entry['simpleGuide'] );
                                    $this->rewardInfo[ $entry['id'] ]['rewards'] = $simpleGuide['rew'];
                                }

                                // Add to the battle log deletion array if any set
                                if( !empty($this->tempBattleLogTypes) ){
                                    $this->deleteBattleLogTypes = array_merge($this->tempBattleLogTypes, $this->deleteBattleLogTypes);
                                }

                                // Clear cache
                                cachefunctions::deleteUserTasks( $_SESSION['uid'] );

                            }
                            else{
                                // User didn't pass the text. Disable deletion of mission log
                                if( $this->deleteMissionLog == true && $localMissionDelete == false ){
                                    $this->deleteMissionLog = false;
                                }
                            }
                        }
                        else{
                            // Quest timed out. Disable deletion of mission log
                            if( $this->deleteMissionLog == true && $localMissionDelete == false ){
                                $this->deleteMissionLog = false;
                            }
                        }
                    }
                    else{
                        // User didn't pass the restrictions.  Disable deletion of mission log
                        if( $this->deleteMissionLog == true && $localMissionDelete == false ){
                            $this->deleteMissionLog = false;
                        }
                    }
                }
                else{
                    // No Requirements: ".$entry['name']."
                }

            }
            else{
                // Not Available: ".$entry['name']."<br>";
            }
        }

        if( $somethingHappen == true ){

            // Give the user all his awards
            $this->addRewards( $rewardArray );

            // Delete mission log if needed
            if( $this->deleteMissionLog == true ){
                cachefunctions::setAllMissionsAsRewarded($_SESSION['uid']);
            }

            // Delete combat log if needed
            if( !empty($this->deleteBattleLogTypes) ){
                cachefunctions::deleteTypesFromCombatLog($_SESSION['uid'], $this->deleteBattleLogTypes);
            }

            // Now update users missions
            $GLOBALS['database']->execute_query("UPDATE `users_missions` SET `tasks` = '".json_encode( $this->userTasks )."' WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1");
        }

        // Return true/false if a mission was completed
        return $somethingHappen;
    }

    // Add rewards to user
    private function addRewards( $rewardArray ){

        // Reward multiplier (can be adjusted with the dailyRewardModification tag)
        $rewardModification = 1.0;

        // Variables for updating stats
        $statUpdates = array();

        // Variables for inserting jutsu & items
        $jutsuInserts = array();
        $itemInserts = array();

        // Variables for adding lottery tickets
        $lotteryInserts = array();

        // First loop over all the sets of rewards added
        foreach( $rewardArray as $rewardSet ){

            // Loop over each reward
            $rewards = explode(";", trim($rewardSet));
            foreach( $rewards as $reward ){
                if( $reward !== "" ){
                    $tags = explode(",", $reward );

                    switch( trim($tags[0]) ){
                        case "dailyRewardModification":
                            // Adjust gains according to how many times this
                            // entry/entries was performed.
                            if( isset( $this->timesPerformed ) ){

                                // Check if there's a fraction to be set here
                                if( isset($tags[ $this->timesPerformed+1 ]) ){

                                    // Adjust rewards
                                    $rewardModification = $tags[ $this->timesPerformed+1 ];
                                }
                            }

                            // Only missions/crimes have this tag. Reward modification can be further increased by global event for these
                            if( $event = functions::getGlobalEvent("DoubleMission") ){
                                if( isset( $event['data']) && is_numeric( $event['data']) ){
                                    $rewardModification *= round($event['data'] / 100,2);
                                }
                            }

                        break;
                        case "quest":
                            // Format is "quest,id"
                            // Can be used for quest chains
                            // Example for activating quest ID #5: "quest,5"
                            if( ctype_digit($tags[1]) ){
                                $quest = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `id` = '" . $tags[1] . "' LIMIT 1");
                                if( $quest !== "0 rows" ){
                                    if(
                                        !isset( $this->userTasks[ $tags[1] ]) ||
                                        ($this->userTasks[ $tags[1] ] == "c" && $quest[0]['questRepeatable'] == "yes" )
                                    ){
                                        // Update mission to database & delete cache
                                        $this->userTasks[$tags[1]] = "a";
                                        $this->userTasks[$tags[1]."_time"] = $GLOBALS['user']->load_time;

                                        // Return message
                                        $this->rewardInformation .= "The quest <i>".$quest[0]['name']."</i> has been initiated:
                                                <blockquote>
                                                    <span>
                                                        <b>New Quest</b><br>
                                                        " . nl2br(stripslashes($quest[0]['description'])) . "
                                                    </span>
                                                </blockquote>
                                                ";
                                    }
                                }
                                else{
                                    throw new Exception("Could not find this quest in the database anymore.");
                                }
                            }
                        break;
                        case "stats":
                            // Format is "stats,stat,amount"
                            // Following stats are supported:
                            // nin_def, gen_def, tai_def, weap_def, nin_off, gen_off, tai_off, weap_off, strength, intelligence, willpower, speed, experience, money, bank
                            // Example: "stats,experience,1000;stats,ryo,500"
                            if( preg_match( "/(max_health|max_cha|max_sta|experience|money|bank|nin_def|gen_def|tai_def|weap_def|nin_off|gen_off|tai_off|weap_off|strength|intelligence|willpower|speed|element_mastery_1|element_mastery_2)/i", $tags[1] ) ){
                                if( ctype_digit( $tags[2] ) ){

                                    // Adjust gain
                                    if( $rewardModification !== 1.0 ){
                                        $tags[2] = round($rewardModification*$tags[2]);
                                    }

                                    // Save gain
                                    $statUpdates[ $tags[1] ] = ( isset($statUpdates[ $tags[1] ]) ) ? $statUpdates[ $tags[1] ] + $tags[2] : $tags[2];

                                    // updating em cache data.
                                    if($tags[1] == 'element_mastery_1')
                                    {
                                        $elements = new Elements();
                                        $elements->updateUserElementMastery($statUpdates[ $tags[1] ],1,true);
                                    }
                                    else if($tags[1] == 'element_mastery_2')
                                    {
                                        $elements = new Elements();
                                        $elements->updateUserElementMastery($statUpdates[ $tags[1] ],2,true);
                                    }
                                    else if($tags[1] == 'max_health')
                                        $GLOBALS['Events']->acceptEvent('stats_max_health', array('new'=>$GLOBALS['userdata'][0]['max_health'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['max_health'] ));

                                    else if($tags[1] == 'max_cha')
                                        $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$GLOBALS['userdata'][0]['max_cha'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['max_cha'] ));

                                    else if($tags[1] == 'max_sta')
                                        $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$GLOBALS['userdata'][0]['max_sta'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['max_sta'] ));

                                    else if($tags[1] == 'experience')
                                        $GLOBALS['Events']->acceptEvent('experience', array('new'=>$GLOBALS['userdata'][0]['experience'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['experience'] ));



                                    else if($tags[1] == 'nin_def')
                                        $GLOBALS['Events']->acceptEvent('stats_nin_def', array('new'=>$GLOBALS['userdata'][0]['nin_def'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['nin_def'] ));

                                    else if($tags[1] == 'gen_def')
                                        $GLOBALS['Events']->acceptEvent('stats_gen_def', array('new'=>$GLOBALS['userdata'][0]['gen_def'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['gen_def'] ));

                                    else if($tags[1] == 'tai_def')
                                        $GLOBALS['Events']->acceptEvent('stats_tai_def', array('new'=>$GLOBALS['userdata'][0]['tai_def'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['tai_def'] ));

                                    else if($tags[1] == 'weap_def')
                                        $GLOBALS['Events']->acceptEvent('stats_weap_def', array('new'=>$GLOBALS['userdata'][0]['weap_def'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['weap_def'] ));



                                    else if($tags[1] == 'nin_off')
                                        $GLOBALS['Events']->acceptEvent('stats_nin_off', array('new'=>$GLOBALS['userdata'][0]['nin_off'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['nin_off'] ));

                                    else if($tags[1] == 'gen_off')
                                        $GLOBALS['Events']->acceptEvent('stats_gen_off', array('new'=>$GLOBALS['userdata'][0]['gen_off'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['gen_off'] ));

                                    else if($tags[1] == 'tai_off')
                                        $GLOBALS['Events']->acceptEvent('stats_tai_off', array('new'=>$GLOBALS['userdata'][0]['tai_off'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['tai_off'] ));

                                    else if($tags[1] == 'weap_off')
                                        $GLOBALS['Events']->acceptEvent('stats_weap_off', array('new'=>$GLOBALS['userdata'][0]['weap_off'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['weap_off'] ));





                                    else if($tags[1] == 'strength')
                                        $GLOBALS['Events']->acceptEvent('stats_strength', array('new'=>$GLOBALS['userdata'][0]['strength'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['strength'] ));

                                    else if($tags[1] == 'intelligence')
                                        $GLOBALS['Events']->acceptEvent('stats_intelligence', array('new'=>$GLOBALS['userdata'][0]['intelligence'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['intelligence'] ));

                                    else if($tags[1] == 'willpower')
                                        $GLOBALS['Events']->acceptEvent('stats_willpower', array('new'=>$GLOBALS['userdata'][0]['willpower'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['willpower'] ));

                                    else if($tags[1] == 'speed')
                                        $GLOBALS['Events']->acceptEvent('stats_speed', array('new'=>$GLOBALS['userdata'][0]['speed'] + $statUpdates[ $tags[1] ], 'old'=>$GLOBALS['userdata'][0]['speed'] ));
                                }
                            }
                        break;
                        case "item":
                            // Format is "item,itemID,action,[once | all]"
                            // actions are: add, remove_once and remove_all
                            if(ctype_digit($tags[1]) && preg_match( "/(add|remove_once|remove_all)/i", $tags[2] ) ){
                                $item = $GLOBALS['database']->fetch_data("SELECT `id`,`durability`,`stack_size` FROM `items` WHERE `id` = '".$tags[1]."' LIMIT 1");
                                if( $item !== "0 rows" ){
                                    switch( $tags[2] ){
                                        case "add":
                                            $query = "INSERT INTO `users_inventory`
                                                (`uid`, `iid`,`equipped`,`timekey`,`durabilityPoints`,`stack`) VALUE
                                                ('".$_SESSION['uid']."','".$tags[1]."','no','".$GLOBALS['user']->load_time."','".$item[0]['durability']."','1')";
                                                $GLOBALS['Events']->acceptEvent('item_person', array('data'=>$tags[1], 'count'=>1 ));

                                        break;
                                        case "remove_once":
                                            $query = "DELETE FROM `users_inventory` WHERE `uid` = '".$_SESSION['uid']."' AND `iid` = '".$tags[1]."' LIMIT 1 ";
                                            $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$tags[1], 'count'=>-1 ));

                                        break;
                                        case "remove_all":
                                            $query = "DELETE FROM `users_inventory` WHERE `uid` = '".$_SESSION['uid']."' AND `iid` = '".$tags[1]."' ";
                                            $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$tags[1], 'count'=>'all' ));

                                        break;
                                    }
                                    $GLOBALS['database']->execute_query($query);
                                }
                            }
                        break;
                        case "jutsu":
                            // Format is "jutsu,jutsuID,lvl"
                            // Example for jutsuID #30, lvl.50: "jutsu,30,50"
                            if(ctype_digit($tags[1]) && ctype_digit($tags[2]) ){
                                $jutsu = $GLOBALS['database']->fetch_data("SELECT `id` FROM `jutsu` WHERE `id` = '".$tags[1]."' LIMIT 1");
                                if( $jutsu !== "0 rows" ){
                                    $jutsu = $GLOBALS['database']->fetch_data("SELECT `jid`, `level` FROM `users_jutsu` WHERE `uid` = '".$_SESSION['uid']."' AND `jid` = '".$tags[1]."' LIMIT 1");
                                    if( $jutsu !== "0 rows" ){
                                        $GLOBALS['Events']->acceptEvent('jutsu_level', array('new'=>$jutsu[0]['level']+$tags[2],'old'=>$jutsu[0]['level'],'data'=>$tags[1], 'context'=>$tags[1]));
                                        $query = "UPDATE `users_jutsu` SET `level` = `level` + ".$tags[2]." WHERE `uid` = '".$_SESSION['uid']."' AND `jid` = '".$tags[1]."' LIMIT 1";
                                    }
                                    else{
                                        $GLOBALS['Events']->acceptEvent('jutsu_learned', array('data'=>$tags[1], 'context'=>$tags[1]));
                                        $GLOBALS['Events']->acceptEvent('jutsu_level', array('new'=>$tags[2], 'old'=>0, 'data'=>$tags[1], 'context'=>$tags[1]));
                                        $query = "INSERT INTO `users_jutsu` (`uid`, `jid`,`level`,`tagged`) VALUE ('".$_SESSION['uid']."','".$tags[1]."','".$tags[2]."','no')";
                                    }
                                    $GLOBALS['database']->execute_query($query);
                                }
                            }
                        break;
                        case "tickets":
                            // Format is "tickets,amount"
                            // Example for 50 tickets: "tickets,50"
                            if( ctype_digit( $tags[1] ) ){
                                // Build the Row Insertions for the Tickets
                                $values = "";
                                for($i = 0; $i < $tags[1]; $i++) {
                                    $values .= ( $values == "" ) ? "(".$_SESSION['uid'].", 'no')" : ", (".$_SESSION['uid'].", 'no')";
                                }
                                $query = "INSERT INTO `lottery` (`userid`, `jackpot`) VALUES ".$values."";

                                // Upload Tickets
                                $GLOBALS['database']->execute_query($query);
                            }
                        break;
                    }
                }
            }
        }

        // Run update of user statistics. Transaction shouldn't be neccesary, I don't see how this informaiton is sensitive?
        if( count($statUpdates) > 0 ){
            $query = "";
            foreach( $statUpdates as $key => $value ){

                // Add to query
                $query .= ( $query == "" ) ? " `".$key."` = `".$key."` + '".$value."' " : ", `".$key."` = `".$key."` + '".$value."' ";

                // Save to array
                $this->statGains[] = $value." ".str_replace("_", " ", $key);
            }
            $query = "UPDATE `users_statistics` SET ".$query." WHERE `uid` = '".$_SESSION['uid']."' LIMIT 1";
            $GLOBALS['database']->execute_query($query);

            // Delete caches
            cachefunctions::deleteUserMovements($_SESSION['uid']);
        }


    }

    // Checks if the user fulfills the restrictions on the given entry
    public function checkRestrictions( $restrictions ){
        $check = true;
        $restrictions = preg_replace( "/\r|\n/", "", $restrictions );
        // Loop through all restrictions
        $restrictionString = $restrictions;
        $restrictions = explode(";", $restrictions );
        foreach( $restrictions as $restriction ){
            if( $restriction !== "" && $check == true ){
                $tags = explode(",",$restriction);
                switch( $tags[0] ){
                    case "quest":
                        // Can be used for quest chains
                        // Format is "quest,id"
                        if( !isset( $this->userTasks[''.$tags[1].''] ) || (isset( $this->userTasks[''.$tags[1].''] ) && $this->userTasks[''.$tags[1].''] !== "c") ){
                            $check = false;
                        }
                    break;
                    case "occupation":
                        // Format is "occupation,occupationIdentifier[operator][value]"
                        // Valid operators are > and =, or both.
                        // Occupation identifiers are:  surgeon, bountyHunter, armorCraft, weaponCraft, chefCook, miner, herbalist
                        // Example requiring lvl1 of occupation 1: "occupation,1,1"
                        $occupation = $GLOBALS['database']->fetch_data("SELECT `level` FROM `users_occupations` WHERE `userid` = '".$_SESSION['uid']."' AND `occupation` = '".$tags[1]."' LIMIT 1");
                        if( $occupation !== "0 rows" ){
                            $equationPartition = preg_split( "/(>=|>|=)/", $tags[1] , -1, PREG_SPLIT_DELIM_CAPTURE);
                            if( !$this->evaluateExpression( $occupation[0]['level'] , $equationPartition[1], $equationPartition[2]) ){
                                $check = false;
                            }
                        }
                        else{
                            $check = false;
                        }
                    break;
                    case "village":
                        // Format is "village,[.-separated list of village names]"
                        // Available village names: Samui, Konoki, Syndicate, Silence, Shroud, Shine
                        $allowed = explode( ".", $tags[1] );
                        if( !in_array( $GLOBALS['userdata'][0]['village'] , $allowed, true) ){
                            $check = false;
                        }
                    break;
                    case "item":
                        // Requires the user to own an item to perform quest, i.e. quest items
                        // Format is "item,[itemID]"
                        $item = $GLOBALS['database']->fetch_data("SELECT `iid` FROM `users_inventory` WHERE `uid` = '".$_SESSION['uid']."' AND `iid` = '".$tags[1]."' LIMIT 1");
                        if( $item == "0 rows" ){
                            $check = false;
                        }
                    break;
                    case "war":
                        // Require the user village to be in war with another village. Always true for syndicate
                        // Format is "war,[any | .-separated list of village-identifiers]"

                        // Decide on query
                        if( $GLOBALS['userdata'][0]['village'] !== "Syndicate" ){

                            // Build query
                            $query = "SELECT * FROM `alliances` WHERE `village` = '".$GLOBALS['userdata'][0]['village']."' AND ";
                            if( $tags[1] == "any" ){
                                $query .= "(`Konoki` = 2 OR `Silence` = 2 OR `Shine` = 2 OR `Shroud` = 2 OR `Samui` = 2)";
                            }
                            else{
                                $query .= "(";
                                $villages = explode( ".", $tags[1] );
                                $i = 0;
                                while( $i < count($villages) ){
                                    $query .= ( $i > 0 ) ? " OR `".$villages[$i]."` = 2 " : " `".$villages[$i]."` = 2 ";
                                    $i++;
                                }
                                $query .= ")";
                            }
                            $query .= " LIMIT 1";

                            // Database check
                            $item = $GLOBALS['database']->fetch_data( $query );
                            if( $item == "0 rows" ){
                                $check = false;
                            }
                        }
                    break;
                    case "element":
                        if( !$this->checkElementalRestriction( $restrictionString ) ){
                            $check = false;
                        }
                    break;
                }
            }
        }

        return $check;
    }

    // Checks if the user completed the given requirement
    private function checkRequirement( $requirement ){
        $check = true;
        $tags = explode(",",$requirement);

        switch( $tags[0] ){
            case "stats":
                // Format: stats,[identifier-calculation][operator][value]
                // Operator: >, =, or both, and ||

                // First split on || in case of multiple expressions
                $expressions = explode("||", $tags[1]);

                // Check each expression
                $oneMustbeTrue = false;
                foreach( $expressions as $expression ){

                    // Separate based on operator
                    $equationPartition = preg_split( "/(>=|>|=)/", $expression , -1, PREG_SPLIT_DELIM_CAPTURE);

                    // Check that we ahve to parts of the expression, one on each of the operator
                    if( count( $equationPartition ) == 3 ){

                        // Calculate left Side of the equation
                        $left_side = 0;
                        if( preg_match( "/(\+|\-)/", $equationPartition[0] ) ){
                            $equationTerms = preg_split( "/(\+|\-)/", $equationPartition[0], -1, PREG_SPLIT_DELIM_CAPTURE);
                            $operation = "plus";
                            foreach( $equationTerms as $term){
                                if( $term !== "+" && $term !== "-" ){
                                    $left_side = ( $operation == "plus" ) ?
                                        $left_side + $this->user[0][$term] :
                                        $left_side - $this->user[0][$term];
                                }
                                else{
                                    $operation = ( $term == "+" ) ? "plus" : "minus";
                                }
                            }

                        }
                        else{
                            $left_side = $this->user[0][$equationPartition[0]];
                        }

                        // If a statement is true, break out of the expression loop. Only one needs to be true
                        if( $this->evaluateExpression( $left_side, $equationPartition[1], $equationPartition[2] ) == true ){
                            $oneMustbeTrue = true;
                            break 1;
                        }

                    }
                }
                if( !$oneMustbeTrue ){
                    $check = false;
                }

            break;
            case "errands":
                // Format: errands,[identifier-calculation][operator][value]
                // Operator: >, =, or both
                $equationPartition = preg_split( "/(>=|>|=)/", $tags[1] , -1, PREG_SPLIT_DELIM_CAPTURE);

                if( preg_match( "/(errands|scrimes)/", $equationPartition[0] ) ){
                    if( !$this->evaluateExpression( $this->user[0][ $equationPartition[0] ] , $equationPartition[1], $equationPartition[2]) ){
                        $check = false;
                    }
                }

            break;
            case "combat":
                // Format is "combat,[identifiers],[sub-identifiers],[sub-conditions][operator][sub_condition_value]"
                // identifiers: "anyAI", "mission", "crime", "normalArena", "tornArena", "mirrorArena", "anyArena", "mapAI", "eventAI", "PVP", "leaderPVP", "spars", "territory"
                // sub-identifiers: any, AIid:id
                // sub-conditions are used: wins, losses, draws, beatAID

                // Deal with wins/losses
                if( preg_match( "/(wins|losses|draws)/", $tags[3] ) )
                {

                    //echo'<pre>';
                    //var_dump($tags);
                    //echo'</pre><br>';

                    $count = 0;
                    $battleTypes = array();

                    if(is_array($this->battle_history))
                    {

                        //echo'battle history is array<br>';

                        foreach( $this->battle_history as $logEntry )
                        {

                            //echo'<pre>';
                            //var_dump($logEntry);
                            //echo'</pre><br>';

                            //if($_SESSION['uid'] == 2020996)
                            //{
                            //    echo'<pre>';
                            //    var_dump($logEntry);
                            //    echo'</pre>';
                            //    echo'<br>';
                            //    var_dump($tags);
                            //    echo'<br>';
                            //    echo'<br>';
                            //}

                            if(
                                (
                                    (  $logEntry['type'] == BattleStarter::travel                                             && $tags[1] == "mapAI" ) ||
                                    (  $logEntry['type'] == BattleStarter::event                                              && $tags[1] == "eventAI" ) ||
                                    (  $logEntry['type'] == BattleStarter::spar                                               && $tags[1] == "spars" ) ||
                                    (  $logEntry['type'] == BattleStarter::pvp                                                && $tags[1] == "PVP" ) ||
                                    ( ($logEntry['type'] == BattleStarter::mission )                                          && ( $tags[1] == "mission" || $tags[1] == "crime" ) ) ||
                                    ( ($logEntry['type'] == BattleStarter::kage || $logEntry['type'] == BattleStarter::clan ) && $tags[1] == "leaderPVP" ) ||
                                    (  $logEntry['type'] == BattleStarter::arena                                              && $tags[1] == "normalArena" ) ||
                                    (  $logEntry['type'] == BattleStarter::mirror                                             && $tags[1] == "mirrorArena" ) ||
                                    (  $logEntry['type'] == BattleStarter::torn                                               && $tags[1] == "tornArena" ) ||
                                    (  $logEntry['type'] == BattleStarter::territory                                          && $tags[1] == "territory" ) ||
                                    (  $logEntry['type'] == BattleStarter::quest                                              && $tags[1] == "quest" ) ||

                                    ( $tags[1] == "anyAI" && (  $logEntry['type'] == BattleStarter::travel       ||
                                                                $logEntry['type'] == BattleStarter::event        ||
                                                                $logEntry['type'] == BattleStarter::small_crimes ||
                                                                $logEntry['type'] == BattleStarter::mission      ||
                                                                $logEntry['type'] == BattleStarter::arena        ||
                                                                $logEntry['type'] == BattleStarter::mirror       ||
                                                                $logEntry['type'] == BattleStarter::torn         ||
                                                                $logEntry['type'] == BattleStarter::quest        )) ||

                                    ( $tags[1] == "anyPVP" && ( $logEntry['type'] == BattleStarter::spar      ||
                                                                $logEntry['type'] == BattleStarter::pvp       ||
                                                                $logEntry['type'] == BattleStarter::kage      ||
                                                                $logEntry['type'] == BattleStarter::clan      ||
                                                                $logEntry['type'] == BattleStarter::territory ))
                                )
                            ){

                                $census_record = array();
                                foreach( explode(',',$logEntry['census']) as $entry )
                                {
                                    $temp = explode('/',$entry);
                                    $census_record[$temp[0]] = $temp;
                                }

                                $status = '';
                                $team = '';

                                $status = $census_record[$GLOBALS['userdata'][0]['username']][3];
                                if($status == 'win')
                                    $status = 'wins';
                                else if($status == 'loss')
                                    $status = 'losses';

                                $team = $census_record[$GLOBALS['userdata'][0]['username']][1];

                                $opponents = array();

                                foreach($census_record as $record)
                                {
                                    if($record[0] != '' && $record[1] != $team)
                                    {
                                        $find_id = explode(':',$record[2]);
                                        if(isset($find_id[1]))
                                            $opponents[] = 'AIid:'.$find_id[1];
                                    }
                                }

                                //echo'if '.$status.' = '. substr($tags[3], 0, strlen($status)) .' and '.$tags[2].' in ';
                                //echo'<pre>opponents: ';
                                //var_dump($opponents);
                                //echo'</pre><br>';

                                //check for win or loss and check for ai
                                if( $status == substr($tags[3], 0, strlen($status)) && (in_array($tags[2], $opponents) || $tags[2] == 'any'))
                                {
                                    $count += 1;
                                    $battleTypes[] = $logEntry['type'];
                                }
                            }
                        }
                    }

                    //var_dump( $check );
                    // Check Count
                    $equationPartition = preg_split( "/(>=|>|=)/", $tags[3] , -1, PREG_SPLIT_DELIM_CAPTURE);
                    //echo' count: '.$count;
                    //echo' parse: ';
                    //var_dump($equationPartition);
                    if( !$this->evaluateExpression( $count , $equationPartition[1], $equationPartition[2]) ){
                        $check = false;
                    }
                    //var_dump( $check );

                    // Delete battle log
                    if( $check == true && !empty($battleTypes) ){
                        $this->tempBattleLogTypes = array_merge($this->tempBattleLogTypes, $battleTypes);
                    }
                }

            break;
            case "missions":
                // Format is "missions,type,rank,subCondition[operator][value]"
                // Following types are used: mission, crime
                // Following ranks are used: S, A, B, C, D
                // Sub-conditions are used: win, lose
                $missionLog = json_decode( cachefunctions::getMissionLog( $_SESSION['uid'] ), true );
                // Deal with wins/losses
                if( preg_match( "/(mission|crime|any)/", $tags[1] ) ){
                    $count = 0;

                    foreach( $missionLog as $logEntry ){
                        if( ($logEntry[0] == $tags[1] || $tags[1] == "any") && $logEntry[1] == $tags[2] && !isset($logEntry["reward"]) ){
                            $count++;
                        }
                    }
                    // Check Count
                    $equationPartition = preg_split( "/(>=|>|=)/", $tags[3] , -1, PREG_SPLIT_DELIM_CAPTURE);
                    if( !$this->evaluateExpression( $count , $equationPartition[1], $equationPartition[2]) ){
                        $check = false;
                    }

                    // Remove the mission log
                    if( $check == true ){
                        $this->deleteMissionLog = true;
                    }
                }

            break;
            case "jutsu":
                // Format is "jutsu,[jutsuID|any],action[operator][jutsu_level]"
                // // Operator: >, =, or both
                $equationPartition = preg_split( "/(>=|>|=)/", $tags[2] , -1, PREG_SPLIT_DELIM_CAPTURE);

                // Check whether jutsu ID or any jutsu
                if( ctype_digit( $tags[1] ) ){
                    $query = "SELECT `".$equationPartition[0]."` as `value` FROM `users_jutsu` WHERE `uid` = '".$_SESSION['uid']."' AND `jid` = '".$tags[1]."' LIMIT 1";
                }
                else {
                    $query = "SELECT `".$equationPartition[0]."` as `value` FROM `users_jutsu` WHERE `uid` = '".$_SESSION['uid']."' ORDER BY `".$equationPartition[0]."` DESC LIMIT 1";
                }

                // Get jutsu data & check it
                $data = $GLOBALS['database']->fetch_data($query);
                if( $data == "0 rows" || !$this->evaluateExpression( $data[0]['value'] , $equationPartition[1], $equationPartition[2]) ){
                    $check = false;
                }

            break;
            case "item":
                // Format is "item,[item ID|any],action([opeator][value])"
                // own_remove, own_keep, equip, times_used (note: no operator&value can be used for for equip, own_remove and own_keep)
                // Valid operators are > and =, or both.

                // Beginning of query
                $query = "SELECT `equipped`,`times_used` FROM `users_inventory` WHERE `uid` = '".$_SESSION['uid']."' AND `trading` IS NULL ";

                // Check whether item ID or any jutsu
                if( ctype_digit( $tags[1] ) ){
                    $query .= " AND `iid` = '".$tags[1]."' ";
                }

                // Check for equip action
                if( preg_match( "/(equip)/", $tags[2] ) ){
                    $query .= " AND `equipped` = 'yes' ";
                }

                // Check order by times_used in case we're looking for that. (in case of any)
                if( preg_match( "/(times_used)/", $tags[2] ) ){
                    $query .= " ORDER BY `times_used` DESC ";
                }

                // Only get one row
                $query .= " LIMIT 1";

                // Do checks
                $data = $GLOBALS['database']->fetch_data($query);
                if( $data !== "0 rows" ){
                    if( preg_match( "/(times_used)/", $tags[2] ) ){
                        $equationPartition = preg_split( "/(>=|>|=)/", $tags[2] , -1, PREG_SPLIT_DELIM_CAPTURE);
                        if( !$this->evaluateExpression( $data[0]['times_used'] , $equationPartition[1], $equationPartition[2]) ){
                            // This false check is for cases where user hasn't used the item enough
                            $check = false;
                        }
                    }
                }
                else{
                    // This false check should cover the simple actions; own_remove, own_keep, equip
                    $check = false;
                }


            break;
            case "lottery":
                // Format is "lottery,tickets[operator][value]"
                // Valid operators are > and =, or both
                $equationPartition = preg_split( "/(>=|>|=)/", $tags[1] , -1, PREG_SPLIT_DELIM_CAPTURE);
                $lotteryCount = $GLOBALS['database']->fetch_data("SELECT
                                    COUNT(`lottery`.`id`) AS `tickets_bought`
                                    FROM `lottery`
                                    WHERE `userid` = '".$_SESSION['uid']."'");
                if( $lotteryCount == "0 rows" || !$this->evaluateExpression( $lotteryCount[0]['tickets_bought'] , $equationPartition[1], $equationPartition[2]) ){
                    $check = false;
                }
            break;
            case "factions":
                // Format is "factions,faction,(join | action[operator][value])"
                // Valid factions are: anbu, kage, clan, surgeon, bountyHunter, armorCraft, weaponCraft, chefCook, miner, herbalist
                // Valid action for occupations are: level
                // Valid action for kage are: village
                // "join" action is valid for all, and simply marks that the user has been in the faction.

                // First check for all the common occupations
                if( preg_match( "/(surgeon|hunter|armorCraft|weaponCraft|chefCook|miner|herbalist)/", $tags[1] ) ){
                    // Just level and join
                    $occupation = $GLOBALS['database']->fetch_data("SELECT `level` FROM `users_occupations` WHERE `userid` = '".$_SESSION['uid']."' AND `occupation` = '".$tags[1]."' LIMIT 1");
                    if( $occupation !== "0 rows" ){
                        if( preg_match( "/(level)/", $tags[2] ) ){
                            $equationPartition = preg_split( "/(>=|>|=)/", $tags[2] , -1, PREG_SPLIT_DELIM_CAPTURE);
                            if( !$this->evaluateExpression( $occupation[0]['level'] , $equationPartition[1], $equationPartition[2]) ){
                                $check = false;
                            }
                        }
                    }
                    else{
                        $check = false;
                    }
                }
                elseif( preg_match( "/(kage)/", $tags[1] ) ){
                    // Village or join (which would be any village)
                    $query = "SELECT `leader` FROM `villages` WHERE `leader` = '".$GLOBALS['userdata'][0]['username']."' ";

                    // If village specified (and not any), then add query limit
                    if(  preg_match( "/(village)/", $tags[2] ) && !stristr($tags[3],"any") ){
                        $query .= " AND `name` = '".$tags[3]."' ";
                    }
                    $query .= " LIMIT 1";

                    $villageLeader = $GLOBALS['database']->fetch_data($query);

                    // Check return value
                    if( $villageLeader == "0 rows"){
                        $check = false;
                    }

                }
                elseif( preg_match( "/(anbu|clan)/", $tags[1] ) ){
                    // Village or join (which would be any village)
                    if( $this->user[0][''.$tags[1].''] !== "" &&
                        $this->user[0][''.$tags[1].''] !== "_none" &&
                        $this->user[0][''.$tags[1].''] !== "_disabled")
                    {
                        if(  preg_match( "/(village)/", $tags[2] ) ){

                            // Check if clan or anbu is in specific village
                            $query = "";
                            switch( $tags[1] ){
                                case "clan": $query = "SELECT `village` FROM `clans` WHERE `village` = '".$tags[3]."' AND `name` = '".$this->user[0]['clan']."' LIMIT 1"; break;
                                case "anbu": $query = "SELECT `village` FROM `squads` WHERE `village` = '".$tags[3]."' AND `name` = '".$this->user[0]['anbu']."' LIMIT 1"; break;
                            }
                            $faction = $GLOBALS['database']->fetch_data($query);
                            if( $faction == "0 rows"){
                                $check = false;
                            }
                        }
                    }
                    else{
                        $check = false;
                    }
                }

            break;
            case "move":
                // Used for tracking travel movements.
                // Format is "move,locationIdentifier,times"
                // Valid locationIdentifiers are: REGION(void), TERRITORY(territoryID), AREA(xmin,xmax,ymin,ymax)
                $check = $this->checkMove( $tags , $_SESSION['uid'] );
            break;
            case "page":
                // Format is "page,pageID"
                $check = false;
                $pageTrack = json_decode( cachefunctions::getUserPages( $_SESSION['uid'] ) , true);
                foreach( $pageTrack as $page => $status ){
                    if( $page == $tags[1] ){
                        $check = true;
                    }
                }

            break;
            case "initiateCombat":
                // Used for missions only
            break;
            case "createAI":
                // Used for missions only
            break;
        }

        unset($tags);
        return $check;
    }

    // Special Checks. Move action
    public function checkMove( $tags , $uid){
        $check = true;
        $userTrack = json_decode( cachefunctions::getMovements( $uid ), true );
        $count = 0;

        // Get stuff within paranthesis
        preg_match( "/\(([^)]+)\)/" , $tags[1] , $match );
        $info = $match[1];

        if( preg_match( "/(REGION)/", $tags[1] ) ){
            foreach( $userTrack as $position ){
                if( isset($position['region']) && $position['region'] == $info ){
                    $count += 1;
                }
            }
        }
        elseif( preg_match( "/(TERRITORY)/", $tags[1] ) ){
            foreach( $userTrack as $position ){
                if( isset($position['terr']) && stristr( $info, $position['terr'] ) ){
                    $count += 1;
                }
            }
        }
        elseif( preg_match( "/(AREA)/", $tags[1] ) ){
            $area = explode( ".", $info );
            foreach( $userTrack as $position ){
                if( $position['x'] >= $area[0] && $position['x'] <= $area[1] && $position['y'] >= $area[2] && $position['y'] <= $area[3]){
                    $count += 1;
                }
            }
        }

        // Chech Count
        $equationPartition = preg_split( "/(>=|>|=)/", $tags[1] , -1, PREG_SPLIT_DELIM_CAPTURE);
        if( !$this->evaluateExpression( $count , $equationPartition[1], $equationPartition[2]) ){
            $check = false;
        }

        return $check;
    }

    // Used to evaluate expression with different operators
    private function evaluateExpression( $leftSide, $operator, $rightSide ){
        switch( $operator ){
            case "=":
                return ( $leftSide == $rightSide ) ? true : false;
            break;
            case ">=":
                return ( $leftSide >= $rightSide ) ? true : false;
            break;
            case ">":
                return ( $leftSide > $rightSide ) ? true : false;
            break;
        }
        return false;
    }

    // Get needed user information
    private function getUserStats(){
        if( !isset($this->user) ){
            $this->user = $GLOBALS['database']->fetch_data("
                SELECT
                    `level_id`,
                    `tai_off`,`nin_off`,`weap_off`,`gen_off`,
                    `tai_def`,`nin_def`,`weap_def`,`gen_def`,
                    `experience`,`intelligence`,`willpower`, `strength`, `speed`,
                    `errands`,`scrimes`,
                    `anbu`, `clan`
                FROM `users_statistics`, `users_missions`,`users_preferences`
                WHERE
                    `users_statistics`.`uid` = '".$_SESSION['uid']."' AND
                    `users_statistics`.`uid` = `users_missions`.`userid` AND
                    `users_statistics`.`uid` = `users_preferences`.`uid`
                LIMIT 1");
        }
    }

    // Sort the simpleGuide array
    public function sortSimpleGuide( $guide ){
        // Create the simple guide
        $simpleGuide = array(
            "req" => array(),
            "rew" => array(),
            "complete" => array(),
            "info" => array()
        );
        if( isset($guide) ){
            $guide = explode(";", $guide);
            foreach( $guide as $guideline){
                if( $guideline !== "" ){
                    $tags = explode(":", trim($guideline));
                    switch( $tags[0] ){
                        case "req": $simpleGuide['req'][] = stripslashes($tags[1]); break;
                        case "rew": $simpleGuide['rew'][] = stripslashes($tags[1]); break;
                        case "complete": $simpleGuide['complete'][] = stripslashes($tags[1]); break;
                        case "info": $simpleGuide['info'][] = stripslashes($tags[1]); break;
                    }
                }
            }
        }
        return $simpleGuide;
    }

    // Update user tasks
    public function updateUserTasks( $userid, $userTasks ){

        // Delete cache
        cachefunctions::deleteUserTasks( $userid );

        // Update suer
        $GLOBALS['database']->execute_query("
             UPDATE `users_missions`
             SET `tasks` = '".json_encode( $userTasks )."'
             WHERE `userid` = '" . $userid . "'
             LIMIT 1"
        );
    }

    // Elemental restriction check
    private function checkElementalRestriction( $restrictions ){
        if( !empty($restrictions) ){
            $restrictions = explode(";", $restrictions );
            foreach( $restrictions as $restriction ){
                $tags = explode(",",$restriction);
                if( $tags[0] == "element" && count($tags) >= 3 ){

                    // Get elements of user
                    if(!isset($this->elements))
                        $this->elements = new Elements();

                    $affinities = $this->elements->getUserElements();

                    $userElements = array();
                    if( $tags[1] == "pri" && !empty($affinities[0])){
                        $userElements[] = $affinities[0];
                    }
                    if( $tags[1] == "sec" && !empty($affinities[1])){
                        $userElements[] = $affinities[1];
                    }
                    if( $tags[1] == "spe" && !empty($affinities[2])){
                        $userElements[] = $affinities[2];
                    }
                    if( $tags[1] == "all"){
                        if( !empty($affinities[0]) ){
                            $userElements[] = $affinities[0];
                        }
                        if( !empty($affinities[1]) ){
                            $userElements[] = $affinities[1];
                        }
                        if( !empty($affinities[2]) ){
                            $userElements[] = $affinities[2];
                        }
                    }

                    // Check if all the elements in element list are in elements list. If not, skip entry
                    $taskElements = explode( ",", $tags[2] );
                    foreach( $taskElements as $element ){
                        if( !in_array($element, $userElements) ){
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    // Available filters are:
    // meetRequirements: shows everything relevant for the user.
    // active: show all active relevant entries
    // completed: show all completed relevant entries
    // quests: show all relevant quests
    // orders: show all relevant orders
    // special: show all admin-set achievements
    public function filterEntries( $allEntries, $userTasks, $filter = "active", $skip = false ){

        // Get user if he hasn't been retrieved already
        $this->getUserStats();

        $returnEntries = array();
        $i = 0;
        foreach( $allEntries as $entry ){
            if(
                // Don't show higher level entries
                ( $this->user[0]['level_id'] >= $entry['levelReq'] ) && // || isset( $userTasks[''.$entry['id'].''] )
                // Don't show admin entries, unless they have been completed
                ($entry['type'] !== "admin" || (isset($userTasks[''.$entry['id'].'']) && $entry['type'] == "admin" && $userTasks[''.$entry['id'].''] == "c")) &&
                (
                    // All tasks & orders
                    preg_match( "/(order|task)/i", $entry['type'] ) ||
                    // All quests, missions and crimes that have been activated or completed
                    ( preg_match( "/(quest|mission|crime)/i", $entry['type'] ) && isset( $userTasks[''.$entry['id'].''] ) )
                )
            ){
                // Fix up a status for this entry
                if( !isset( $userTasks[''.$entry['id'].''] ) ||
                    ( $userTasks[''.$entry['id'].''] !== "c" &&
                      $userTasks[''.$entry['id'].''] !== "f")
                ){
                    $entry['status'] = "Active";
                }
                elseif( $userTasks[''.$entry['id'].''] == "f" ){
                    $entry['status'] = "Failed";
                }
                else{
                    $entry['status'] = "Completed";
                }

                // Fix up time left
                if( !empty($entry['questTime']) && isset($userTasks[''.$entry['id'].'_time']) ){
                    $entry['timeLeft'] = $userTasks[''.$entry['id'].'_time'] + $entry['questTime'] - $GLOBALS['user']->load_time;
                }
                else{
                    $entry['timeLeft'] = "disabled";
                }

                // Add timestamp to quest
                if( isset($userTasks[''.$entry['id'].'_time'])  ){
                    $entry['timeStamp'] = $userTasks[''.$entry['id'].'_time'];
                }

                // Quest Chains
                if(stristr($entry['restrictions'], "quest") ){
                    $entry['name'] = "QuestChain --> " . $entry['name'];
                }

                // Don't show elemental which are not valid
                if( stristr( $entry['restrictions'], "element," ) ){
                    if( !$this->checkElementalRestriction($entry['restrictions']) ){
                        continue;
                    }
                }

                // Check village restrictions
                if( stristr( $entry['restrictions'], "village," ) ){
                    if( !stristr( $entry['restrictions'], $GLOBALS['userdata'][0]['village'] ) ){
                        continue;
                    }
                }

                // Special Filters
                if(
                    $filter == "meetRequirements" ||
                    ($filter == "active" && $entry['status'] == "Active") ||
                    ($filter == "completed" && $entry['status'] == "Completed") ||
                    ($filter == "quests" && $entry['type'] == "quest") ||
                    ($filter == "orders" && $entry['type'] == "order") ||
                    ($filter == "special" && $entry['type'] == "admin")
                ){
                    // Limit the results returned
                    if( $skip == false || $i > $skip ){

                        // Fix Entry Type Name in case of "admin"
                        if( $entry['type'] == "admin"){ $entry['type'] = "Special";}
                        $entry['type'] = ucfirst($entry['type']);

                        // Add to return array
                        $returnEntries[] = $entry;
                        $i++;
                    }
                }


            }
        }
        return $returnEntries;
    }

    // Get logbook entry details
    public function setEntryDetails( $id, $smartyVar = "contentLoad" ){
        if( isset($id) && ctype_digit($id) ){

            // Get entry
            $entry = cachefunctions::getTasksQuestsMission($id);

            // Get User tasks
            $userTasks = cachefunctions::getUserTasks( $_SESSION['uid'] );
            $userTasks = json_decode($userTasks[0]['tasks'], true);

            // Check if user can view it
            if(
                // Check that exists
                $entry !== "0 rows" &&
                // Don't show higher level entries
                (
                 ($GLOBALS['userdata'][0]['level_id'] >= $entry[0]['levelReq'] &&
                  $GLOBALS['userdata'][0]['level_id'] <= $entry[0]['levelMax'] ) ||
                 isset( $userTasks[''.$entry[0]['id'].''] ) )&&
                // Don't show admin entries, unless they have been completed
                ($entry[0]['type'] !== "admin" || (isset($userTasks[''.$entry[0]['id'].'']) && $entry[0]['type'] == "admin" && $userTasks[''.$entry[0]['id'].''] == "c")) &&
                (
                    // All tasks & orders
                    preg_match( "/(order|task)/i", $entry[0]['type'] ) ||
                    // All quests, missions and crimes that have been activated or completed
                    ( preg_match( "/(quest|mission|crime)/i", $entry[0]['type'] ) && isset( $userTasks[''.$entry[0]['id'].''] ) )
                )
            ){
                // Remove slashes
                $entry[0]['description'] = stripslashes($entry[0]['description']);

                // Create the simple guide
                $simpleGuide = array( "req" => array(), "rew" => array() );
                if( isset($entry[0]['simpleGuide']) ){
                    $entry[0]['simpleGuide'] = explode(";", $entry[0]['simpleGuide']);
                    foreach( $entry[0]['simpleGuide'] as $guideline){
                        if( $guideline !== "" ){
                            $tags = explode(":", trim($guideline));
                            switch( $tags[0] ){
                                case "req": $simpleGuide['req'][] = $tags[1]; break;
                                case "rew": $simpleGuide['rew'][] = $tags[1]; break;
                            }
                        }
                    }
                }

                // If it's a quest, allow user to delete
                if( $entry[0]['type'] == "quest" &&
                    $userTasks[ $entry[0]['id'] ] !== "c"
                ){
                    $GLOBALS['template']->assign('quitLink', "&amp;act=".$_GET['act']."&amp;eid=".$id."&amp;act2=quitMission");

                     // If a timed quest, send that to the requirement
                    if( $entry[0]['questTime'] !== null ){

                        // If no time is set, set a time
                        if( !isset($userTasks[ $entry[0]['id']."_time" ]) ){
                            $userTasks[$entry[0]['id']."_time"] = $GLOBALS['user']->load_time;
                            $this->updateUserTasks($_SESSION['uid'], $userTasks);
                        }

                        $timeLeft = ($userTasks[ $entry[0]['id']."_time" ]+$entry[0]['questTime']) - $GLOBALS['user']->load_time;
                        $simpleGuide['req'][] = "Must be completed before: " . functions::convert_time( $timeLeft , 'TimeRequirement', 'false');
                    }
                }

                // Send to smarty
                $GLOBALS['template']->assign('simpleGuide', $simpleGuide);
                $GLOBALS['template']->assign('entry', $entry[0]);
                $GLOBALS['template']->assign($smartyVar, './templates/content/logbook/description.tpl');

                // Quit mission
                if( $entry[0]['type'] == "quest" &&
                    $userTasks[ $entry[0]['id'] ] !== "c" &&
                    isset($_GET['act2']) &&
                    $_GET['act2'] == "quitMission"
                ){
                    $this->quitQuest( $userTasks, $entry[0]['id'] );
                }

            }
            else{
                throw new Exception("You can not view this entry");
            }

        }
        else{
            throw new Exception("Specified entry is not valid");
        }
    }

    // Quit the quest the user is doing
    public function quitQuest( $userTasks,  $id ){

        // Remove the mission
        $this->removeQuest($userTasks, $id);

        // Show message
        $GLOBALS['page']->Message( "You have quit the quest",
                                   'Stop Quest',
                                   'id='.$_GET['id'],
                                   "Return to Logbook");
    }

    // Removing a mission from the user
    public function removeQuest( $userTasks, $id ){
        $userTasks[$id] = "f";
        $userTasks[$id."_time"] = $GLOBALS['user']->load_time;
        $this->updateUserTasks($_SESSION['uid'], $userTasks);
    }

    // Activate the quest the user is doing
    public function activateQuest( $userTasks,  $id ){
        $userTasks[$id] = "a";
        $userTasks[$id."_time"] = $GLOBALS['user']->load_time;
        $this->updateUserTasks($_SESSION['uid'],  $userTasks);
    }




}