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

require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/QuestContainer.php');

class MissionsAndCrimes{

    public $missions_per_day = 4;

    public function __construct()
    {


        // Try running the page
        try{
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            if( $event = functions::getGlobalEvent("IncreasedMissionCount") ){
                if( isset( $event['data']) && is_numeric( $event['data']) ){
                    $this->missions_per_day += $event['data'];
                }
            }

            if(!isset($GLOBALS['QuestsControl']))
                $GLOBALS['QuestsControl'] = new QuestsControl();

            //checking to see if there is a mission currently active
            $active_mission = false;
            $known_mission = false;
            foreach($GLOBALS['QuestsControl']->QuestsData->quests as $qid => $quest)
            {
                if( ($quest->category == 'mission' || $quest->category == 'crime') && in_array($quest->status, array(1,2)) )
                    $active_mission = $quest;

                if( ($quest->category == 'mission' || $quest->category == 'crime') && in_array($quest->status, array(0)) )
                {
                    if($known_mission === false)
                    {
                        $known_mission = $quest;
                    }
                    else if (!is_array($known_mission))
                    {
                        $known_mission = array($known_mission,$quest);
                    }
                    else if(is_array($known_mission))
                    {
                        $known_mission[] = $quest;
                    }

                }
            }


            //check if it is a new day from last mission time
            if($GLOBALS['userdata'][0]['mission_collection_time'] != 0 && date('Y-m-d', $GLOBALS['userdata'][0]['mission_collection_time']) != date('Y-m-d'))
            {
                if($active_mission !== false)
                {
                    if(!$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `mission_count` = 0, `mission_collection_time` = ".(new DateTime())->getTimestamp().", `missions_collected` = '".$active_mission->qid."', `missions_offered` = '' WHERE `userid` = ".$_SESSION['uid']))
                    {
                        throw new Exception('There was an issue recording what missions have been randomly selected for you. '."UPDATE `users_timer` SET `mission_count` = 0, `mission_collection_time` = ".(new DateTime())->getTimestamp().", `missions_collected` = '".$active_mission->qid."', `missions_offered` = '' WHERE `userid` = ".$_SESSION['uid']);
                    }

                    $GLOBALS['userdata'][0]['mission_count'] = 0;
                    $GLOBALS['userdata'][0]['mission_collection_time'] = (new DateTime())->getTimestamp();
                    $GLOBALS['userdata'][0]['missions_collected'] = $active_mission->qid;
                    $GLOBALS['userdata'][0]['missions_offered'] = '';
                }
                else
                {
                    if(!$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `mission_count` = 0, `mission_collection_time` = 0, `missions_collected` = '', `missions_offered` = '' WHERE `userid` = ".$_SESSION['uid']))
                    {
                        throw new Exception('There was an issue recording what missions have been randomly selected for you. '."UPDATE `users_timer` SET `mission_count` = 0, `mission_collection_time` = 0, `missions_collected` = '', `missions_offered` = '' WHERE `userid` = ".$_SESSION['uid']);
                    }

                    $GLOBALS['userdata'][0]['mission_count'] = 0;
                    $GLOBALS['userdata'][0]['mission_collection_time'] = 0;
                    $GLOBALS['userdata'][0]['missions_collected'] = '';
                    $GLOBALS['userdata'][0]['missions_offered'] = '';
                }
            }

            if(!$active_mission)
            {
                //mission counter is incremented on turn in (if this mission is a chain then increment by the chain's total count)
                //everything else including rewards should be handled by the quest system.
                $this->show_missions();

                if( isset($_GET['qid']) && (in_array($_GET['qid'], explode(',',$GLOBALS['userdata'][0]['missions_offered'])) || in_array($_GET['qid'], array('a','b','c','d'))) &&
                    $GLOBALS['userdata'][0]['mission_count'] < $this->missions_per_day && 
                    //$GLOBALS['userdata'][0]['mission_count'] < 8 && 
                    //$GLOBALS['userdata'][0]['mission_count'] < 100 && 
                    ( $GLOBALS['userdata'][0]['mission_collection_time'] == 0 || ($GLOBALS['userdata'][0]['mission_collection_time'] + (60*30)) < (new DateTime())->getTimestamp() ))
                    //( $GLOBALS['userdata'][0]['mission_collection_time'] == 0 || $GLOBALS['userdata'][0]['mission_collection_time'] < (new DateTime())->getTimestamp() ))
                    //( $GLOBALS['userdata'][0]['mission_collection_time'] == 0 || ($GLOBALS['userdata'][0]['mission_collection_time'] ) < (new DateTime())->getTimestamp() ))
                {
                    if(in_array($_GET['qid'], array('a','b','c','d')))
                    {
                        if($_GET['qid'] == 'a')
                        {
                            $level = 41;
                            if( $GLOBALS['userdata'][0]['rank_id'] < 5)
                                throw new exception("You can't obtain an A-rank mission until you are rank 5");
                        }
                        else if($_GET['qid'] == 'b')
                        {
                            $level = 31;
                            if( $GLOBALS['userdata'][0]['rank_id'] < 4)
                                throw new exception("You can't obtain an B-rank mission until you are rank 4");
                        }
                        else if($_GET['qid'] == 'c')
                        {
                            $level = 21;
                            if( $GLOBALS['userdata'][0]['rank_id'] < 3)
                                throw new exception("You can't obtain an C-rank mission until you are rank 3");
                        }
                        else if($_GET['qid'] == 'd')
                        {
                            $level = 11;
                            if( $GLOBALS['userdata'][0]['rank_id'] < 2)
                                throw new exception("You can't obtain an D-rank mission until you are rank 2");
                        }

                        $query = "SELECT * FROM `quests` WHERE `state` = 'on' and `category` in ('mission','crime') and `qid` in (".trim(str_replace(",,",",",$GLOBALS['userdata'][0]['missions_offered']),',').") and `level` >= $level and `level` < $level + 10";

                        try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
                        catch (Exception $e)
                        {
                            try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                            catch (Exception $e)
                            {
                                try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                                catch (Exception $e)
                                {
                                    $GLOBALS['DebugTool']->push($query,'cant pull quest data from database', __METHOD__, __FILE__, __LINE__);
                                    throw $e;
                                }
                            }
                        }
                    
                        $options = array();
                        if(is_array($data))//if data was collected from the database...
                        {
                            foreach($data as $quest_data) //...fill quests with QuestContainers built with the quest data from the database
                            {
                                if($quest_data['mission_chain'] + $GLOBALS['userdata'][0]['mission_count'] > $this->missions_per_day){}
                                //if($quest_data['mission_chain'] + $GLOBALS['userdata'][0]['mission_count'] > 8){}
                                //if($quest_data['mission_chain'] + $GLOBALS['userdata'][0]['mission_count'] > 100){}
                                
                                else
                                    $options[] = $quest_data['qid'];
                            }
                        }

                        $_GET['qid'] = $options[mt_rand(0,count($options)-1)];
                    }

                    //learn quest / set to known
                    if(!isset($GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]))
                    {
                        $GLOBALS['QuestsControl']->QuestsData->learnQuest($_GET['qid'], 0);
                    }
                    else
                    {
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->attempts = 0;
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->failed = 0;
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->turned_in = 0;
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->timestamp_turned_in = 0;
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->status = 0;
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->track = 0;
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->dialog_chain = array();
                        $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]->data = '';
                    }

                    //handle known quest
                    if($known_mission !== false)
                    {
                        if(!is_array($known_mission))
                            $GLOBALS['QuestsControl']->forgetQuest($known_mission->qid);
                        else
                            foreach($known_mission as $for_removal)
                                $GLOBALS['QuestsControl']->forgetQuest($for_removal->qid);
                    }

                    //start quest
                    $GLOBALS['QuestsControl']->startQuest($_GET['qid']);
                    $active_mission = $GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']];

                    //update quest time collected and quests_collected
                    $GLOBALS['userdata'][0]['mission_collection_time'] = (new DateTime())->getTimestamp();

                    if($active_mission->mission_chain > 1)
                        $GLOBALS['userdata'][0]['mission_collection_time'] += ($active_mission->mission_chain - 1) * (60*30);
                        //$GLOBALS['userdata'][0]['mission_collection_time'] += ($active_mission->mission_chain - 1);

                    $GLOBALS['userdata'][0]['missions_collected'] = ltrim($GLOBALS['userdata'][0]['missions_collected'] . ',' . $_GET['qid'],',');
                    $query = "UPDATE `users_timer` SET `mission_collection_time` = '" . $GLOBALS['userdata'][0]['mission_collection_time'] . "', `missions_collected` = '".$GLOBALS['userdata'][0]['missions_collected']."' WHERE `userid` = ".$_SESSION['uid'];

                    if(!$GLOBALS['database']->execute_query($query))
                    {
                        throw new Exception('There was an issue recording what missions have been randomly selected for you. '.$query);
                    }

                    $this->show_mission($GLOBALS['QuestsControl']->QuestsData->quests[$_GET['qid']]);
                }
            }
            else
                $this->show_mission($active_mission);
                
            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false)
            {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch (Exception $e) 
        {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Mission System', 'id='.$_GET['id'],'Return');
        }
    }

    public function show_mission($quest)
    {
        $GLOBALS['template']->assign('quest', $quest);
        $GLOBALS['template']->assign('statuses', QuestContainer::$statuses);
        $GLOBALS['template']->assign('mission_count',$GLOBALS['userdata'][0]['mission_count']);
        $GLOBALS['template']->assign('mission_count_per_day',$this->missions_per_day);
        $GLOBALS['template']->assign('contentLoad', './templates/content/quests/QuestDetails.tpl');
    }
        
    public function show_missions()
    {
        //if( ($GLOBALS['userdata'][0]['mission_collection_time'] != 0 && ($GLOBALS['userdata'][0]['mission_collection_time'] ) > (new DateTime())->getTimestamp()) || $GLOBALS['userdata'][0]['mission_count'] >= 4 )
        if( ($GLOBALS['userdata'][0]['mission_collection_time'] != 0 && ($GLOBALS['userdata'][0]['mission_collection_time'] + (60*30)) > (new DateTime())->getTimestamp()) || $GLOBALS['userdata'][0]['mission_count'] >= $this->missions_per_day )
        //if( ($GLOBALS['userdata'][0]['mission_collection_time'] != 0 && ($GLOBALS['userdata'][0]['mission_collection_time'] + (60*30)) > (new DateTime())->getTimestamp()) || $GLOBALS['userdata'][0]['mission_count'] >= 8 )
        //if( ($GLOBALS['userdata'][0]['mission_collection_time'] != 0 && ($GLOBALS['userdata'][0]['mission_collection_time']) > (new DateTime())->getTimestamp()) || $GLOBALS['userdata'][0]['mission_count'] >= 100 )
        {
            if($GLOBALS['userdata'][0]['mission_count'] < $this->missions_per_day)
            //if($GLOBALS['userdata'][0]['mission_count'] < 8)
            //if($GLOBALS['userdata'][0]['mission_count'] < 100)
                $time_stamp = ($GLOBALS['userdata'][0]['mission_collection_time'] + (60*30)) - (new DateTime())->getTimestamp();
                //$time_stamp = ($GLOBALS['userdata'][0]['mission_collection_time']) - (new DateTime())->getTimestamp();
            else
                $time_stamp = ((new DateTime())->setTime(24,0))->getTimestamp()  - (new DateTime())->getTimestamp();

            $timer = functions::convert_time( $time_stamp, 'missionTimer');
            $GLOBALS['page']->Message( "The mission board is currently empty.<br>Please wait for more missions to be posted.<br>".$timer, 'Missions System '.$GLOBALS['userdata'][0]['mission_count'].'/'.$this->missions_per_day);
            //$GLOBALS['page']->Message( "The mission board is currently empty.<br>Please wait for more missions to be posted.<br>".$timer, 'Missions System '.$GLOBALS['userdata'][0]['mission_count'].'/8');
            //$GLOBALS['page']->Message( "The mission board is currently empty.<br>Please wait for more missions to be posted.<br>".$timer, 'Missions System '.$GLOBALS['userdata'][0]['mission_count'].'/100');
        }

        else
        {
            //getting quests
            $query = "SELECT * FROM `quests` WHERE `state` = 'on' and `category` in ('mission','crime')";

            try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
            catch (Exception $e)
            {
                try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                catch (Exception $e)
                {
                    try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push($query,'cant pull quest data from database', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            $a = $b = $c = $d = array();
            if(is_array($data))//if data was collected from the database...
            {
                foreach($data as $quest) //...fill quests with QuestContainers built with the quest data from the database
                {
                    if($quest['mission_chain'] + $GLOBALS['userdata'][0]['mission_count'] > $this->missions_per_day){}
                    //if($quest['mission_chain'] + $GLOBALS['userdata'][0]['mission_count'] > 8){}
                    //if($quest['mission_chain'] + $GLOBALS['userdata'][0]['mission_count'] > 100){}

                    else if($quest['level'] >= 41)
                        $a[$quest['qid']] = new QuestContainer($quest);

                    else if($quest['level'] >= 31)
                        $b[$quest['qid']] = new QuestContainer($quest);

                    else if($quest['level'] >= 21)
                        $c[$quest['qid']] = new QuestContainer($quest);

                    else if($quest['level'] >= 11)
                        $d[$quest['qid']] = new QuestContainer($quest);
                }
            }

            $missions = array('D' => $d, 'C' => $c, 'B' => $b, 'A' => $a);
            $missions_selected = array('A' => array(), 'B' => array(), 'C' => array(), 'D' => array());

            //if you have ranked up or you do not have any current offered missions get new ones.
            if( substr_count($GLOBALS['userdata'][0]['missions_offered'],',') < ( ($GLOBALS['userdata'][0]['rank_id'] - 1) * 2) - 1 )
            {
                $qids = array();
                foreach($missions as $rank => $rank_data)
                {
                    $mission_keys = array_keys($rank_data);

                    if($GLOBALS['userdata'][0]['missions_collected'] != '')
                    {
                        $missions_collected = explode(',',$GLOBALS['userdata'][0]['missions_collected']);
                        $mission_keys_temp = array_diff($mission_keys,$missions_collected);

                        foreach($mission_keys_temp as $mission_keys_key => $mission_key)
                        {
                            if(!$GLOBALS['QuestsControl']->canStart($missions[$rank][$mission_key]))
                                unset($mission_keys_temp[$mission_keys_key]);
                        }

                        if(count($mission_keys_temp) >= 2)
                        {
                            $mission_keys = $mission_keys_temp;
                        }

                        else
                        {
                            $GLOBALS['userdata'][0]['missions_collected'] = '';

                            foreach($mission_keys as $mission_keys_key => $mission_key)
                            {
                                if(!$GLOBALS['QuestsControl']->canStart($missions[$rank][$mission_key]))
                                    unset($mission_keys[$mission_keys_key]);
                            }
                        }
                    }
                    else
                    {
                        foreach($mission_keys as $mission_keys_key => $mission_key)
                        {
                            if(!$GLOBALS['QuestsControl']->canStart($missions[$rank][$mission_key]))
                                unset($mission_keys[$mission_keys_key]);
                        }
                    }

                    $trys = 0;
                    while(count($missions_selected[$rank]) < 2 && count($mission_keys) >= 2 && $trys < 200)
                    {
                        $trys++;

                        $qid = $mission_keys[random_int(0,count($mission_keys)-1)];
                        
                        if(!in_array($qid, $qids))
                        {
                            $missions_selected[$rank][$qid] = $rank_data[$qid];
                            $qids[] = $qid;
                        }
                    }

                    if(count($mission_keys) < 1)
                        throw new exception('could not find a mission for you. please report. rank('.$rank.')');

                    if(count($missions_selected[$rank]) < 1)
                    {
                        $qids[] = 0;
                    }

                    if(count($missions_selected[$rank]) < 2)
                    {
                        $qids[] = 0;
                    }

                }

                $GLOBALS['userdata'][0]['missions_offered'] = implode(',',$qids);

                if(!$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `missions_offered` = '" . $GLOBALS['userdata'][0]['missions_offered'] . "', `missions_collected` = '".$GLOBALS['userdata'][0]['missions_collected']."' WHERE `userid` = ".$_SESSION['uid']))
                {
                    throw new Exception('There was an issue recording what missions have been randomly selected for you. '."UPDATE `users_timer` SET `missions_offered` = '" . $GLOBALS['userdata'][0]['missions_offered'] . "', `missions_collected` = '".$GLOBALS['userdata'][0]   ['missions_collected']."' WHERE `userid` = ".$_SESSION['uid']);
                }
            }

            //if you currently have offered missions just use those
            else
            {
                foreach( explode(',',$GLOBALS['userdata'][0]['missions_offered']) as $key => $qid )
                {
                    if($key <= 1 && isset($missions['D'][$qid]))
                        $missions_selected['D'][$qid] = $missions['D'][$qid];

                    else if($key <= 3 && isset($missions['C'][$qid]))
                        $missions_selected['C'][$qid] = $missions['C'][$qid];

                    else if($key <= 5 && isset($missions['B'][$qid]))
                        $missions_selected['B'][$qid] = $missions['B'][$qid];

                    else if($key <= 7 && isset($missions['A'][$qid]))
                        $missions_selected['A'][$qid] = $missions['A'][$qid];
                }
            }

            if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
                unset($missions_selected['D']);

            $GLOBALS['template']->assign('missions', $missions_selected);
            $GLOBALS['template']->assign('mission_count',$GLOBALS['userdata'][0]['mission_count']);
            $GLOBALS['template']->assign('mission_count_per_day',$this->missions_per_day);
            $GLOBALS['template']->assign('contentLoad', './templates/content/missions/missions.tpl');
        }
    }
}

// instantiate
$page = new MissionsAndCrimes();