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
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
class missionCrimeLib {

    // Set all information needed on the page.
    public function setPageInformation() {
        
        // Time page
        $this->timed = $GLOBALS['user']->load_time;
        
        // Number of missions allowed
        $this->allowedMissions = 8;
        
        // Get user.
        $this->user = $GLOBALS['database']->fetch_data("
            SELECT `last_supermission` 
            FROM `users_missions` 
            WHERE `userid` = '" . $_SESSION['uid'] . "'");
        
        // Get the user tasks stuff
        $this->userTasks = cachefunctions::getUserTasks( $_SESSION['uid'] );
        $this->userTasks = json_decode($this->userTasks[0]['tasks'] , true);
        
        // Determine whether this is a crime or mission and which rank.
        switch( $_GET['id'] ){
            case "32": $this->pageType="mission"; $this->pageRank="D"; $this->timeLimit = 1800; break;
            case "40": $this->pageType="mission"; $this->pageRank="C"; $this->timeLimit = 1800; break;
            case "54": $this->pageType="mission"; $this->pageRank="B"; $this->timeLimit = 1800; break;
            case "62": $this->pageType="mission"; $this->pageRank="A"; $this->timeLimit = 1800; break;
            case "77": $this->pageType="mission"; $this->pageRank="S"; $this->timeLimit = 1800; break;
            case "55": $this->pageType="crime"; $this->pageRank="C"; $this->timeLimit = 1800; break;
            case "56": $this->pageType="crime"; $this->pageRank="B"; $this->timeLimit = 1800; break;
            case "65": $this->pageType="crime"; $this->pageRank="A"; $this->timeLimit = 1800; break;
            default: $this->pageType="unknown"; $this->pageRank="-"; break;
        }
        $GLOBALS['template']->assign('pageType', $this->pageType);
        $GLOBALS['template']->assign('pageRank', $this->pageRank);
        
        // Get all the entries
        $this->allEntries = cachefunctions::getMissionsAndCrimes();
        
        // Do a count of how many times it's been completed
        $this->howManyTimes();
        
    }
    
    // Give the user the opportunity to take a mission
    protected function main_page() {
        
        // Confirmation Message
        $message = "";
        switch( $this->pageType ){
            case "mission": $message = "You walk to the Mission Board, looking for a mission to help out the village with.<br>"; 
            break;
            case "crime": $message = "Want to do some crime, eh?... There's a perfect deal for you here then<br>"; break;
        }
        
        // Show the user how many times he has completed this task.
        $message .= "You have performed this type of task ".$this->countCompletions." times today.<br>";
        
        // Confirm page
        $GLOBALS['page']->Confirm($message, $this->pageRank."-Ranked ".$this->pageType , "I'm ready for it!");
    }

    // Check if user is already in an active mission
    protected function isDoingMission(){
        if( $this->allEntries !== "0 rows" ){
            foreach( $this->allEntries as $entry ){
                if( isset( $this->userTasks[''.$entry['id'].''] ) && $this->userTasks[''.$entry['id'].''] == "m"  ){
                    $temp = explode("_",$entry['type']);
                    $this->pageType = strtolower($temp[0]);
                    $this->pageRank = strtoupper($temp[1]);
                    return $entry['id'];
                }
            }
        }
        return false;
    }
    
    // Pick a random mission for which the user fill the requirements
    protected function pickRandomMission(){
        
        // Check that the user cannot pick more than $this->allowedMissions missions a day
        if( $this->howManyTimes() >= $this->allowedMissions ){
            throw new Exception("Cannot perform more than ".$this->allowedMissions." missions a day. Please return tomorrow!");
        }
        
        // Find random mission
        if( $this->allEntries !== "0 rows" ){
            $permittedEntries = array();
            foreach( $this->allEntries as $entry ){
                if( 
                    $entry['type'] == $this->pageType."_".strtolower($this->pageRank) && 
                    $GLOBALS['userdata'][0]['level_id'] >= $entry['levelReq'] && 
                    $GLOBALS['userdata'][0]['level_id'] <= $entry['levelMax'] &&
                    $this->taskLibrary->checkRestrictions( $entry['restrictions'] )
                ){
                    $permittedEntries[] = $entry;
                }
            }            
            if( count($permittedEntries) > 0 ){
                return $permittedEntries[ random_int(0,count($permittedEntries)-1) ];
            }
        }
        return false;
    }
    
    // Initiate a mission
    protected function initiateMission(){
        if( $mission = $this->pickRandomMission() ){
            if ( $this->timed >= ($this->user[0]['last_supermission'] + $this->timeLimit)) {
                
                // Start the mission
                $this->startMission = true;
                
                // Ready to start mission. Check the requirements for specific entries
                $requirements = explode(";", $mission['requirements']);
                foreach( $requirements as $requirement ){
                    $tags = explode(",",trim($requirement));
                    switch( $tags[0] ){
                        case "createAI":
                            // createAI,[.-separated IDlist for single battle],territoryName,chance
                        break;
                        case "initiateCombat":
                            // Format: initiateCombat,aiList,30.55
                            $aiList = explode(".",$tags[2]);
                            $select = "";
                            foreach( $aiList as $ai ){
                                $select .= ($select == "") ? " `id` = '".$ai."'" : " OR `id` = '".$ai."'";
                            }
                            $opponent = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE ".$select." LIMIT ".count($aiList));
                            
                            // Start Battle with these opponents
                            if ($opponent !== '0 rows') {
                                
                                // Make AI  
                                //$oppIDs = array();
                                //for ($i=0; $i<count($opponent); $i++)
                                //{
                                //    $opponent[$i] = functions::make_ai( $opponent[$i] );
                                //    $oppIDs[] = $opponent[$i]['id'];
                                //} 
                                //
                                //// Update Database

                                try{
                                    $users = array();
                                    $users[] = array('id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village']);

                                    $ai = array();
                                    foreach($opponent as $opponent_data)
                                        $ai[] = array('id'=>$opponent_data['id'],'team'=>false);

                                    BattleStarter::startBattle( $users, $ai, BattleStarter::mission, false, $_GET['id']);

                                //    functions::insertIntoBattle(
                                //        array($GLOBALS['userdata'][0]['id']), 
                                //        $oppIDs, 
                                //        $this->pageType,
                                //        $this->pageRank, 
                                //        array(), 
                                //        $opponent
                                //    );
                                } catch (Exception $e) {
                                    $this->startMission = false;
                                    $GLOBALS['database']->transaction_rollback("Mission start error, transaction rollback");
                                    $GLOBALS['page']->Message("There was an error dealing with your request: ". $e->getMessage(), 'Mission System', 'id='.$_GET['id'] );
                                }
                            }
                        break;
                    }
                }
                
                // Update user data if mission is being successfully started
                if( $this->startMission == true ){
                    
                    // Update userdata
                    $this->userTasks[$mission['id']] = "m";
                    $GLOBALS['database']->execute_query("
                        UPDATE `users_missions` 
                        SET `last_supermission` = '" . $GLOBALS['user']->load_time . "', 
                            `tasks` = '".json_encode( $this->userTasks )."' 
                        WHERE `userid` = '" . $_SESSION['uid'] . "' 
                        LIMIT 1");

                    // Update Village Statistics
                    $GLOBALS['database']->execute_query("
                        UPDATE `villages` 
                        SET `".strtolower($this->pageRank)."_missions` = `".strtolower($this->pageRank)."_missions` + 1 
                        WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' 
                        LIMIT 1");

                    // Clear cache
                    cachefunctions::deleteUserTasks( $_SESSION['uid'] );
                    
                    // Get any combat requirements
                    $types = array_merge( 
                            array("mission", "crime") , 
                            $this->findCombatRequirementTypes( $mission['requirements'] )
                    );

                    // Delete combat log
                    cachefunctions::deleteTypesFromCombatLog( $_SESSION['uid'] , $types );//

                    // Show message to user
                    $this->showMission( $mission['id'] );
                }
            }
            else{
                
                // User has to wait to perform mission
                $time_remain = functions::convert_time(($this->timeLimit - ($this->timed - $this->user[0]['last_supermission'])), 'missiontimer');
                $GLOBALS['page']->Message("You can not yet perform another mission. Please wait ".$time_remain, 
                                          'Mission Error', 
                                          'id=2',
                                          "Return to Profile");
            }
        }
        else{
            $GLOBALS['page']->Message("Could not find any fitting missions for you. Please return later", 
                                      'No Missions', 
                                      'id=2',
                                      "Return to Profile");
        }
    }
    
    // Show Mission Details
    protected function showMission( $id ){
        
        // Get & sort entry data
        $entry = cachefunctions::getTasksQuestsMission( $id );
        $simpleGuide = $this->taskLibrary->sortSimpleGuide( $entry[0]['simpleGuide'] );
        $entry[0]['name'] = "Active ".$this->pageType.": ".$entry[0]['name'];
        $entry[0]['description'] = stripslashes($entry[0]['description']);
        
        // Send to template engine
        $GLOBALS['template']->assign('simpleGuide', $simpleGuide);
        $GLOBALS['template']->assign('quitLink', "&act=quitMission");
        $GLOBALS['template']->assign('entry', $entry[0]);
        $GLOBALS['template']->assign('contentLoad', './templates/content/logbook/description.tpl');
        
    }
    
    // Quit the mission the user is doing
    protected function quitMission( $id ){
        
        // Set to quit
        unset($this->userTasks[$id]);
        
        // Delete cache
        cachefunctions::deleteUserTasks( $_SESSION['uid'] );
        
        // Update suer
        $GLOBALS['database']->execute_query("
             UPDATE `users_missions` 
             SET `tasks` = '".json_encode( $this->userTasks )."' 
             WHERE `userid` = '" . $_SESSION['uid'] . "' 
             LIMIT 1"
        );
        
        // Show message
        $GLOBALS['page']->Message( "You have quit the ". $this->pageType, 
                                    ucfirst($this->pageType).' Quitting', 
                                   'id=2',
                                   "Return to Profile");
    }
    
    // Function for checking how many times the given type was performed during this day
    protected function howManyTimes(){
        
        // Get mission log
        $missionLog = json_decode( cachefunctions::getMissionLog( $_SESSION['uid'] ), true );
        
        // Get today
        $today = date("m.d.y", time());
        
        // Count
        $this->countCompletions = 0;
        
        // Go through entries
        if( !empty( $missionLog ) && $missionLog !== "0 rows"){
            foreach( $missionLog as $mission ){
                if( in_array($mission[0], array("mission","crime")) && isset($mission[3]) ){ //  && $mission[1] == $this->pageRank
                    $entryDate = date("m.d.y", $mission[3]);
                    if( $entryDate == $today ){
                        $this->countCompletions += 1;
                    }
                }
            }
        }
        
        // Return count
        return $this->countCompletions;        
    }
    
    // Finish mission and give rewards etc
    protected function finishMission(){
        
        // Count how many times completed
        $this->howManyTimes();
        
        // Get and check for completes tasks, missions, orders and quests
        $check = $this->taskLibrary->checkTasks( 
                array(
                    "hook"=>"mission",
                    "allTasks" => $this->allEntries,
                    "userTasks" => $this->userTasks,
                    "id" => $this->activeMission,
                    "timesPerformed" => $this->countCompletions
                ) 
        );
        
        // Update user cache
        if( $check ){
            
            // Loop through somple guide to get completion text
            $entry = cachefunctions::getTasksQuestsMission( $this->activeMission );
            $simpleGuide = $this->taskLibrary->sortSimpleGuide( $entry[0]['simpleGuide'] );
            
            // Get any combat requirements
            $types = array_merge( 
                    array("mission", "crime") , 
                    $this->findCombatRequirementTypes( $entry[0]['requirements'] )
            );
            
            // Delete combat log
            cachefunctions::deleteTypesFromCombatLog( $_SESSION['uid'] , $types );//
            
            // Insert mission win into mission log
            cachefunctions::updateMissionLog( $_SESSION['uid'] , $this->pageType, $this->pageRank, $entry[0]['name'] );
            
            // Set initial text
            if( isset($simpleGuide["complete"]) && $simpleGuide["complete"] !== "" ){
                $completionText = $simpleGuide["complete"][0];
            }
            else{
                $completionText = "Good job on completing this ".$this-pageRank."-ranked ".$this->pageType.". ";
            }
            
            // Update mission counter
            $GLOBALS['database']->execute_query("
                        UPDATE `users_missions` 
                        SET `".strtolower($this->pageRank)."_".$this->pageType."` = `".strtolower($this->pageRank)."_".$this->pageType."` + 1
                        WHERE `userid` = '" . $_SESSION['uid'] . "' 
                        LIMIT 1");
            
            // Set rewards
            $completionText .= ". <br>Completing this level of ".$this->pageType." ".$this->countCompletions." times previously today, you are rewarded: <br><br>";
            
            foreach( $this->taskLibrary->statGains as $reward ){
                $completionText .= $reward."<br>";
            } 
            
            // Show message
            $GLOBALS['page']->Message( $completionText, 
                                        ucfirst($this->pageType).' Completion', 
                                       'id=2',
                                       "Return to Profile");
        }
        else{
            $this->showMission( $this->activeMission );
        }   
    }
    
    // Extra the string of any combat requirements
    protected function findCombatRequirementTypes( $requirement ){
        $types = array();
        $requirements = explode(";", $requirement);
        
        if( !empty($requirements) ){
            foreach( $requirements as $requirement ){
                if( preg_match( "/^combat,([a-zA-Z]+),.+$/" , $requirement , $match ) ){
                    switch( $match[1] ){
                        case "mapAI": $types[] = "rand"; break;
                        case "normalArena": $types[] = "arena"; break;
                        case "spars": $types[] = "spar"; break;
                        case "PVP": $types[] = "combat"; break;
                        case "leaderPVP": $types[] = "kage"; break;
                        case "eventAI": $types[] = "event"; break;
                        case "tornArena": $types[] = "torn_battle"; break;
                        case "mirrorArena": $types[] = "mirror_battle"; break;
                        case "territory": $types[] = "territory"; break;
                        case "mission": 
                        case "crime":
                            $types[] = "mission";
                            $types[] = "crime";
                        break;
                        case "anyAI": 
                            $types = array_merge( $types, array("rand","mission","crime","arena","event","torn_battle","mirror_battle","quest") ); 
                            break;
                        case "anyPVP": 
                            $types = array_merge( $types, array("spar","combat","kage","territory") ); 
                            break;
                    }
                }
            }
        }
        return $types;
    }
}
