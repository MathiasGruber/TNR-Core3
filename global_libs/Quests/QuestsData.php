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

/*Author: Tyler Smith
 *Class: QuestsData
 *  handles get and set of quest data to the cache and database
 *
 */

require_once(Data::$absSvrPath.'/global_libs/Quests/QuestContainer.php');
class QuestsData
{
    private $cache = false; //////////////////////////////////////////////////////////////////////////////////////////////set this back to true

    private $update_cache = false;

    public $quests; //holds QuestContainers for the quests

    public $active = 0; //holds the count of currently active quests.

    public static $requirement_skins = array('home'=>'apartment', 'location_name'=>'location', 'location_x'=>'longitude', 'location_y'=>'latitude'); //used to re-map the name of an event to the name of the column in the database / globals['userdata'].

    function __construct($uid = false)
    {
        if($uid == false && isset($_SESSION['uid'])) //if no user id was passed in use this sessions uid
            $this->uid = $_SESSION['uid'];
        else if(!is_null($uid))
            $this->uid = $uid;
        else
        {
            error_log('can not start quest data, no uid');
            return false;
        }

        //$this->uid = 1;/////temp

        //$this->cache = $GLOBALS['memOn'];

        if($this->cache) //if cache is set to true try to get quest data from the cache
            $quests_data_cache = @$GLOBALS['cache']->get(Data::$target_site.$this->uid.'quests');
        else
            $quests_data_cache = false;

        if(!isset($GLOBALS['DebugTool']))
        {
            require_once(Data::$absSvrPath.'/tools/DebugTool.php');
            $GLOBALS['DebugTool'] = new DebugTool();
        }

        if(!is_array($quests_data_cache)) //if no data was collected from the cache then get it from the database
        {

            $query = "SELECT * FROM `users_quests` INNER JOIN `quests` ON (`users_quests`.`qid` = `quests`.`qid`) LEFT JOIN `dialogs` ON (`dialogs`.`did` = `quests`.`did`) WHERE `state` = 'on' AND `uid` = ".$this->uid." ORDER BY CASE WHEN `status` = 1 THEN 1 ELSE 0 END DESC, CASE WHEN `timestamp_learned` >= `timestamp_updated` AND `timestamp_learned` >= `timestamp_turned_in` THEN `timestamp_learned` WHEN `timestamp_updated` >= `timestamp_turned_in` THEN `timestamp_updated` ELSE `timestamp_turned_in` END DESC";

            try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
            catch (Exception $e)
            {
                try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                catch (Exception $e)
                {
                    try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('uid: '.$this->uid,'cant pull quest data from database', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }


            if(is_array($data))//if data was collected from the database...
            {
                $this->quests = array();
                foreach($data as $quest) //...fill quests with QuestContainers built with the quest data from the database
				{                    
                    $this->quests[$quest['qid']] = new QuestContainer($quest);

                    if($quest['reset_status'] == 1)
                    {
                        $this->resetStatus($quest['qid']);
                    }
				}
            }


            if($this->cache) //if the cache should be working go ahead and save what we got to the cache
            {
				$this->cache_active = true;
                $this->updateCache();
            }
            else
            {
                $this->cache_active = false;
            }

        }
        else
        {
            $this->cache_active = true;
            $this->quests = $quests_data_cache;
        }



        if(is_array($this->quests)) //check if there are quests
        {
            foreach($this->quests as $quest) //go through each quest
            {
                if($quest->status == QuestContainer::$active) //if it is active
                    $this->active += 1; //add to the active quest counter
            }
        }

        //
        //$this->store_all_quest_data();
    }

    function tryEval($command)
    {
        try
        {
            return eval($command);
        }
        catch (exception $e)
        {
            var_dump($e);
            error_log($e);
            return false;
        }
    }

    //learns a quest....
    //this adds the quest to the users_quests database table
    function learnQuest($qid, $starting_status)
    {
        try
        {
            $GLOBALS['database']->transaction_start();

            
            if(!isset($this->quests[$qid])) //if this quest is not already known
            {
                //add this quest to users quests
                $query = "INSERT INTO `users_quests` (`uid`,            `qid`,    `status`,          `failed`, `turned_in`, `timestamp_learned`, `timestamp_updated`, `timestamp_turned_in`, `dialog_chain`, `data`) VALUES (".$this->uid.", ".$qid.", ".$starting_status.", 0, 0, ".time().", ".time().", 0, '', '')";
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('cant set quest data');}
                catch (Exception $e)
                {
                    try{  if(!$GLOBALS['database']->execute_query($query)) throw new exception('cant set quest data'); }
                    catch (Exception $e)
                    {
                        try{  if(!$GLOBALS['database']->execute_query($query)) throw new exception('cant set quest data');}
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                //getting data back from database with quest information
                $query = "SELECT * FROM `users_quests` INNER JOIN `quests` ON (`users_quests`.`qid` = `quests`.`qid`) LEFT JOIN `dialogs` ON (`dialogs`.`did` = `quests`.`did`) WHERE `state` = 'on' AND `users_quests`.`uid` = ".$this->uid." AND `users_quests`.`qid` = ".$qid;

                try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull quest data from database'); }
                catch (Exception $e)
                {
                    try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull quest data from database'); }
                    catch (Exception $e)
                    {
                        try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull quest data from database'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','cant pull quest data from database', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }

                if(isset($data[0])) //if the result from the database was good
                {
                    $this->quests[$qid] = new QuestContainer($data[0]); //add this quest to this class
                    ksort($this->quests); //re base the array so they are in the correct order

                    if($starting_status == QuestContainer::$active) //if this quest is already active...
                        $this->active += 1; //update the active quest counter.

                    $this->updateCache();
                }

                
                //$GLOBALS['Events']->placeEventInBuffer('quest_status', array('data'=>$qid, 'new'=>'known', 'old'=>'un_known' ));
            //$GLOBALS['Events']->recordBuffer();
                $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'learned', 'old'=>'un_known' ));
                $GLOBALS['Events']->closeEvents();
                //$GLOBALS['Events']->acceptEvent('quest', array('data'=>, 'new'=>, 'old'=> ));
            }
            

            $GLOBALS['database']->transaction_commit();
        }
        catch (exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            throw $e;
        }

    }



    //completes a quest....
    //this changes the quests status to complete
    function completeQuest($qid)
    {
        try
        {
            $GLOBALS['database']->transaction_start();

            
            //if this quest is active
            if($this->quests[$qid]->status == QuestContainer::$active)
            {
                //set this quest as known
                $this->quests[$qid]->status = QuestContainer::$completed; 

                //updating database
                $query = "UPDATE `users_quests` SET `status` = ".QuestContainer::$completed.", `timestamp_updated` = ".time()." WHERE `uid` = ".$this->uid." AND `qid` = ".$qid;

                try{ $GLOBALS['database']->execute_query($query);}
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try{ $GLOBALS['database']->execute_query($query);}
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                $this->active -= 1; //update the active quest counter.

                $this->updateCache();
            }
            else
                throw new exception("this quest is not completable.");
            

            $GLOBALS['database']->transaction_commit();
        }
        catch (exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            throw $e;
        }
    }



    //forgets a quest
    //meaning it removes it from the users_quests database table
    function forgetQuest($qid, $force = false)
    {
        try
        {
            $GLOBALS['database']->transaction_start();

            
            //if the user has the quest
        //if the quest is forgettable
        //if the quest has been failed you may not forget it.
            if(isset($this->quests[$qid]) && ($this->quests[$qid]->forgettable || $force) && !($this->quests[$qid]->hard_fail && $this->quests[$qid]->status == QuestContainer::$hard_failure)) /////////////////////////////////////////////////////////// update this to prevent all failed quests from being forgotten
            {
                $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'forgotten', 'old'=>QuestContainer::$statuses[$this->quests[$qid]->status]));

                //remove the quest from this class
                unset($this->quests[$qid]);

                //removing the quest from users data in the database
                $query = "DELETE FROM `users_quests` WHERE `uid` = ".$this->uid." AND `qid` = ".$qid;

                try{ $GLOBALS['database']->execute_query($query);}
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try{ $GLOBALS['database']->execute_query($query);}
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                $this->updateCache();
            }
            

            $GLOBALS['database']->transaction_commit();
        }
        catch (exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            throw $e;
        }
    }

    function resetStatus($qid)
    {
        if($this->quests[$qid]->status == QuestContainer::$known)
        {
            $this->forgetQuest($qid);
            $this->learnQuest($qid, 0);
        }
        else if($this->quests[$qid]->status == QuestContainer::$active)
        {
            $this->quitQuest($qid, true);
            $this->forgetQuest($qid);
            $this->learnQuest($qid, 0);
            $this->startQuest($qid);
        }
        else if($this->quests[$qid]->status == QuestContainer::$completed)
        {
            $this->forgetQuest($qid);
            $this->learnQuest($qid, 0);
            $this->startQuest($qid);
            $this->completeQuest($qid);
        }
        else
        {
            var_dump('notifiy koala: bad reset status!');
        }
    }

    //starts a quest.
    //maning it changes the status of the quest from known to active
    function startQuest($qid)
    {
        try
        {
            $GLOBALS['database']->transaction_start();

            
            if($this->quests[$qid]->status == QuestContainer::$known) //if the quests current stats is known you may start the quest
            {
                //updating php data
                $this->quests[$qid]->status = QuestContainer::$active; //set the quest as active in this class

                $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'started', 'old'=>'known'));

                //variables for processing completion and starting requirements
                $tracked_requirements = array();
                $tracked_check_list = array();
                $completion_check_list = array();
                $failure_check_list = array();
                $completion_tracked_gains = array();
                $completion_tracked_losses = array();
                $failure_tracked_gains = array();
                $failure_tracked_losses = array();

                if($this->quests[$qid]->failed && is_array($this->quests[$qid]->completion_requirements_post_failure))
                    $requirements = $this->quests[$qid]->completion_requirements_post_failure;
                else
                    $requirements = $this->quests[$qid]->completion_requirements;

                //if there are completion requirements
                if(is_array($requirements))
                {
                    //for each completion requirement
                    foreach($requirements as $key => $requirement_data)
                    {
                        //if this is a gain or loss based requirement
                        if(isset($requirement_data['gain'])||isset($requirement_data['loss']))
                        {
                            //save this requirement in the collection of tracked requirements
                            $tracked_requirements[$key] = $requirement_data;

                            //save this requirement in the collection of tracked check list items
                            $tracked_check_list[$key] = false;

                            //if this is a gain based requirement add it to the completion tracked gains list
                            if(isset($requirement_data['gain']))
                            {
                                if(isset($requirement_data))
                                $completion_tracked_gains[$key] = false;
                            }
                            else //otherwise add it to the completion tracked losses list
                            {
                                $completion_tracked_losses[$key] = false;
                            }
                        }

                        //add all completion requirements to the main completion check list
                        $completion_check_list[$key] = false;
                    }
                }
                else
                {
                    $this->quests[$qid]->status = QuestContainer::$completed; //set the quest as active in this class
                }

                if($this->quests[$qid]->failed && is_array($this->quests[$qid]->failure_requirements_post_failure))
                    $requirements = $this->quests[$qid]->failure_requirements_post_failure;
                else
                    $requirements = $this->quests[$qid]->failure_requirements;

                //if there are failure requirements
                if(is_array($requirements))
                {
                    //foreach failure requirement
                    foreach($requirements as $key => $requirement_data)
                    {
                        
                        //if this is a gain or loss based requirement
                        if(isset($requirement_data['gain'])||isset($requirement_data['loss']))
                        {
                            //save this requirement in the collection of tracked requirements
                            $tracked_requirements[$key] = $requirement_data;

                            //save this requirement in the collection of tracked check list items
                            $tracked_check_list[$key] = false;

                            //if this is a gain based requirement add it to the failure tracked gains list
                            if(isset($requirement_data['gain']))
                            {
                                $failure_tracked_gains[$key] = false;
                            }
                            else //otherwise add it to the failure tracked losses list
                            {
                                $failure_tracked_losses[$key] = false;
                            }
                        }

                        //add all failure requirements to the main failure check list
                        $failure_check_list[$key] = false;
                    }
                }

                //get data for all time events
                $date_information = array(  'unix_time' => time(),
                                            'year' => date('Y'),
                                            'month' => date('n'),
                                            'day_numeric' => date('j'),
                                            'day' => date('l'),
                                            'hour' => date('H'),
                                            'minute' => date('i'),
                                            'second' => date('s')
                                         );
                
                //get all needed data from the database
                $data = $this->getDatabaseData($tracked_check_list, $tracked_requirements);

                //if there are tracked requirements
                if(is_array($tracked_requirements))
                {
                    //for each tracked requirements
                    foreach($tracked_requirements as $key => $requirement_data)
                    {

                        $requirement = explode('~',$key)[0];

                        //getting requirement_skin if needed
                        if(isset(QuestsData::$requirement_skins[$requirement]))
                            $requirement_skin = QuestsData::$requirement_skins[$requirement];
                        else
                            $requirement_skin = $requirement;

                        //if this requirement's data is found in $GLOBALS['userdata'] get your data from there
                        if(in_array($requirement, Events::$userdata_events))
                        {
                            if(!is_numeric($GLOBALS['userdata'][0][$requirement_skin]))
                                $GLOBALS['userdata'][0][$requirement_skin] = 0;

                            //if this requirement's data should be in completion tracked gains put it there.
                            if(isset($completion_tracked_gains[$key]))
                                $completion_tracked_gains[$key] = array('start'=>$GLOBALS['userdata'][0][$requirement_skin],'gains'=>0, 'last'=>$GLOBALS['userdata'][0][$requirement_skin]);

                            //if this requirement's data should be in completion tracked losses put it there.
                            if(isset($completion_tracked_losses[$key]))
                                 $completion_tracked_losses[$key] = array('start'=>$GLOBALS['userdata'][0][$requirement_skin],'losses'=>0, 'last'=>$GLOBALS['userdata'][0][$requirement_skin]);
                            
                            //if this requirement's data should be in failure tracked gains put it there.
                            if(isset($failure_tracked_gains[$key]))
                                $failure_tracked_gains[$key] = array('start'=>$GLOBALS['userdata'][0][$requirement_skin],'gains'=>0, 'last'=>$GLOBALS['userdata'][0][$requirement_skin]);
                            
                            //if this requirement's data should be in failure tracked losses put it there.
                            if(isset($failure_tracked_losses[$key]))
                                 $failure_tracked_losses[$key] = array('start'=>$GLOBALS['userdata'][0][$requirement_skin],'losses'=>0, 'last'=>$GLOBALS['userdata'][0][$requirement_skin]);
                        }

                        //if this requirement's data is a time event get your data from there
                        else if(in_array($requirement, Events::$time_events))
                        {
                            if(!is_numeric($date_information[$requirement_skin]))
                                $date_information[$requirement_skin] = 0;

                            //if this requirement's data should be in completion tracked gains put it there.
                            if(isset($completion_tracked_gains[$key]))
                                $completion_tracked_gains[$key] = array('start'=>$date_information[$requirement_skin],'gains'=>0, 'last'=>$date_information[$requirement_skin]);

                            //if this requirement's data should be in completion tracked losses put it there.
                            if(isset($completion_tracked_losses[$key]))
                                 $completion_tracked_losses[$key] =array('start'=> $date_information[$requirement_skin],'losses'=>0, 'last'=>$date_information[$requirement_skin]);

                            //if this requirement's data should be in failure tracked gains put it there.
                            if(isset($failure_tracked_gains[$key]))
                                $failure_tracked_gains[$key] = array('start'=>$date_information[$requirement_skin],'gains'=>0, 'last'=>$date_information[$requirement_skin]);

                            //if this requirement's data should be in failure tracked losses put it there.
                            if(isset($failure_tracked_losses[$key]))
                                 $failure_tracked_losses[$key] = array('start'=>$date_information[$requirement_skin],'losses'=>0, 'last'=>$date_information[$requirement_skin]);
                        }

                        //if this requirement's data is found in the database get your data from there or if this requirements data is not findable start at zero
                        else if(in_array($requirement, Events::$non_userdata_events) || in_array($requirement, Events::$event_only))
                        {
                            //if this requirement's data should be in completion tracked gains put it there.
                            if(isset($completion_tracked_gains[$key]))
                            {
                                if(isset($requirement_data['context']))
                                {
                                    if(!is_array($requirement_data['context']))
                                        $contexts = array($requirement_data['context']);
                                    else
                                        $contexts = $requirement_data['context'];

                                    foreach($contexts as $context)
                                    {
                                        if(!isset($completion_tracked_gains[$key]))
                                            $completion_tracked_gains[$key] = array();

                                        if( !isset($data[0][$requirement_skin.'_'.$context]) || !is_numeric($data[0][$requirement_skin.'_'.$context]))
                                            $data[0][$requirement_skin.'_'.$context] = 0;

                                        if(in_array($requirement, Events::$non_userdata_events))
                                            $completion_tracked_gains[$key][$context] = array('start'=>$data[0][$requirement_skin.'_'.$context],'gains'=>0, 'last'=>$data[0][$requirement_skin.'_'.$context]);
                                        else
                                            $completion_tracked_gains[$key][$context] = array('start'=>0, 'gains'=>0, 'last'=>0);

                                        if(!isset($completion_tracked_gains[$key][$context]['start']))
                                        {
                                            $completion_tracked_gains[$key][$context]['start'] = 0;
                                            $completion_tracked_gains[$key][$context]['last'] = 0;
                                        }
                                    }
                                }
                                else
                                {
                                    if( !isset($data[0][$requirement_skin]) || !is_numeric($data[0][$requirement_skin]))
                                        $data[0][$requirement_skin] = 0;

                                    if(in_array($requirement, Events::$non_userdata_events))
                                        $completion_tracked_gains[$key] = array('start'=>$data[0][$requirement_skin],'gains'=>0, 'last'=>$data[0][$requirement_skin]);
                                    else
                                        $completion_tracked_gains[$key] = array('start'=>0,'gains'=>0, 'last'=>0);

                                    if(!isset($completion_tracked_gains[$key]['start']))
                                    {
                                        $completion_tracked_gains[$key]['start'] = 0;
                                        $completion_tracked_gains[$key]['last'] = 0;
                                    }
                                }
                            }

                            //if this requirement's data should be in completion tracked losses put it there.
                            if(isset($completion_tracked_losses[$key]))
                            {
                                if(isset($requirement_data['context']))
                                {
                                    if(!is_array($requirement_data['context']))
                                        $contexts = array($requirement_data['context']);
                                    else
                                        $contexts = $requirement_data['context'];
                                        
                                    foreach($contexts as $context)
                                    {
                                        if(!isset($completion_tracked_losses[$key]))
                                            $completion_tracked_losses[$key] = array();

                                        if(!isset($data[0][$requirement_skin.'_'.$context]) || !is_numeric($data[0][$requirement_skin.'_'.$context]))
                                            $data[0][$requirement_skin.'_'.$context] = 0;

                                        if(in_array($requirement, Events::$non_userdata_events))
                                            $completion_tracked_losses[$key][$context] = array('start'=>$data[0][$requirement_skin.'_'.$context],'losses'=>0, 'last'=>$data[0][$requirement_skin.'_'.$context]);
                                        else
                                            $completion_tracked_losses[$key][$context] = array('start'=>0, 'losses'=>0, 'last'=>0);

                                    }
                                }
                                else
                                {
                                    if(!isset($data[0][$requirement_skin]) || !is_numeric($data[0][$requirement_skin]))
                                        $data[0][$requirement_skin] = 0;

                                    if(in_array($requirement, Events::$non_userdata_events))
                                        $completion_tracked_losses[$key] =array('start'=> $data[0][$requirement_skin],'losses'=>0, 'last'=>$data[0][$requirement_skin]);
                                    else
                                        $completion_tracked_losses[$key] =array('start'=> 0, 'losses'=>0, 'last'=>0);

                                }
                            }

                            //if this requirement's data should be in failure tracked gains put it there.
                            if(isset($failure_tracked_gains[$key]))
                            {
                                if(in_array($requirement, Events::$non_userdata_events))
                                    $failure_tracked_gains[$key] = array('start'=>$data[0][$requirement_skin],'gains'=>0, 'last'=>$data[0][$requirement_skin]);
                                else
                                    $failure_tracked_gains[$key] = array('start'=>0, 'gains'=>0, 'last'=>0);

                            }

                            //if this requirement's data should be in failure tracked losses put it there.
                            if(isset($failure_tracked_losses[$key]))
                            {
                                if(in_array($requirement, Events::$non_userdata_events))
                                    $failure_tracked_losses[$key] = array('start'=>$data[0][$requirement_skin],'losses'=>0, 'last'=>$data[0][$requirement_skin]);
                                else 
                                    $failure_tracked_losses[$key] = array('start'=>0,'losses'=>0, 'last'=>0);

                            }
                        }
                    }
                }

                //priming quest data
                $this->quests[$qid]->data = array(  'completion_gains'=>$completion_tracked_gains,
                                                    'completion_losses'=>$completion_tracked_losses, 
                                                    'failure_gains'=>$failure_tracked_gains, 
                                                    'failure_losses'=>$failure_tracked_losses, 
                                                    'completion_check_list'=>$completion_check_list, 
                                                    'failure_check_list'=>$failure_check_list);
                

                //building serialized and compressed package for the database
                $package = str_replace("'", "\'", str_replace("\\", "backslash", str_replace('#', "hashtag", gzdeflate(serialize($this->quests[$qid]->data), 1 ))));

                //building the query to update the database
                $query = "UPDATE `users_quests` SET `status` = ".$this->quests[$qid]->status.", `data` = '".$package."', `timestamp_updated` = ".time()." WHERE `uid` = ".$this->uid." AND `qid` = ".$qid;

                //updating database
                try{ $GLOBALS['database']->execute_query($query);}
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try{ $GLOBALS['database']->execute_query($query);}
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                //updating active quest counter
                $this->active += 1;

                $this->updateCache();
            }
            //else
                //throw new exception("this quest is not startable. ".$this->quests[$qid]->status);
            

            $GLOBALS['database']->transaction_commit();
        }
        catch (exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            throw $e;
        }
    }

    //starts a quest.
    //maning it changes the status of the quest from known to active
    function quitQuest($qid, $ignore_failure = false)
    {
        try
        {
            $GLOBALS['database']->transaction_start();

            
            //if this quest is active
            if($this->quests[$qid]->status == QuestContainer::$active)
            {

                //clear out its data
                $this->quests[$qid]->data = ''; 

                $status = $this->quests[$qid]->status;

                //if the quest is failable
                if($this->quests[$qid]->failable && !$ignore_failure)
                {
                    //update attempts
                    $this->quests[$qid]->attempts += 1;

                    //checking attempts
                    if($this->quests[$qid]->attempts >= $this->quests[$qid]->chances)
                    {
                        //update the query so that database will also show the quest as failed
                        $failed = ', `failed` = 1, `attempts` = 0 ';

                        //call the method that will show the quests failure to the user
                        $GLOBALS['QuestsControl']->showFailure($qid);
                        $this->processPunishmentsOrRewards($qid, 'punishments');

                        //mark it as failed
                        $this->quests[$qid]->failed = true;

                        if(!$this->quests[$qid]->hard_fail)
                            $status = QuestContainer::$known;
                        else
                            $status = QuestContainer::$hard_failure;

                        $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'failed', 'old'=>'active'));
                    }
                    else
                    {
                        //call the method that will show the quests failure to the user
                        $GLOBALS['QuestsControl']->showFailure($qid, true);

                        $status = QuestContainer::$known;

                        $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'attempt', 'old'=>'active'));

                        $failed = ', `attempts` = '.$this->quests[$qid]->attempts.' ';
                    }
                }
                else
                {
                    $failed = '';

                    $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'quit', 'old'=>'active'));

                    if(!$this->quests[$qid]->hard_fail)
                        $status = QuestContainer::$known;
                    else
                        $status = QuestContainer::$hard_failure;
                }


                $this->quests[$qid]->status = $status;
                $this->quests[$qid]->timestamp_updated = time();
                $this->quests[$qid]->data = '';
                $this->quests[$qid]->track = 0;

                //updating database
                $query = "UPDATE `users_quests` SET `status` = ".$status.", `data` = '' ".$failed.", `timestamp_updated` = ".time().", `track` = 0 WHERE `uid` = ".$this->uid." AND `qid` = ".$qid;

                try{ $GLOBALS['database']->execute_query($query);}
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try{ $GLOBALS['database']->execute_query($query);}
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                $this->updateCache();
            }
            else
                throw new exception("this quest is not quitable.");
            

            $GLOBALS['database']->transaction_commit();
        }
        catch (exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            throw $e;
        }
    }

    function turnInQuest($qid)
    {
        try
        {
            if( $this->quests[$qid]->category == 'mission' || $this->quests[$qid]->category == 'crime')
                $this->mission_maintenance($this->quests[$qid]);

            $GLOBALS['database']->transaction_start();

            
            if($this->quests[$qid]->repeatable)
            {
                $status = QuestContainer::$known;
            }
            else
                $status = QuestContainer::$closed;

            $GLOBALS['Events']->acceptEvent('quest_status', array('context'=>$qid, 'new'=>'turned_in', 'old'=>'completed'));

            $this->processPunishmentsOrRewards($qid, 'rewards');

            $this->quests[$qid]->status   = $status;
            $this->quests[$qid]->timestamp_updated   = time();
            $this->quests[$qid]->timestamp_turned_in = time();
            $this->quests[$qid]->data     = '';
            $this->quests[$qid]->failed   = 0;
            $this->quests[$qid]->attempts = 0;
            $this->quests[$qid]->track = 0;

            //updating database
            $query = "UPDATE `users_quests` SET `status` = ".$status.", `data` = '', `timestamp_updated` = ".time().", `timestamp_turned_in` = ".time().", `failed` = 0, `attempts` = 0, `track` = 0 WHERE `uid` = ".$this->uid." AND `qid` = ".$qid;

            try{ $GLOBALS['database']->execute_query($query);}
            catch (Exception $e)
            {
                try{ $GLOBALS['database']->execute_query($query); }
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query);}
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $this->updateCache();
            

            $GLOBALS['database']->transaction_commit();
        }
        catch (exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            throw $e;
        }

    }

     //this gets all needed information from the database
     //this method is a bit of a nightmare
     //it builds a large query that will get all needed data from the database
     //it will get it from the database and return it to the user
    function getDatabaseData($check_list, $requirements)
    {

        //variables used to hold pieces of the query
        $select = '';
        $from = '';
        $join = '';
        $where = '';

        //flags that are used to know what to JOIN with in the query
        $users_statistics = false;
        $users_occupations = false;
        $bingo_book = false;
        $users_jutsu = array();
        $users_jutsu_any = false;
        $users_inventory = array();
        $users_inventory_any = false;
        $home_inventory = array();
        $home_inventory_any = false;
        $furniture_inventory = array();
        $furniture_inventory_any = false;


        ////////////////////////////////////////////////////////////////// users_statistics

        //'stats_element_mastery_1'
        $filter_result = array_filter($check_list, function($k, $s = 'stats_element_mastery_1'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `element_mastery_1` AS 'stats_element_mastery_1'";
            $users_statistics = true;
        }

        //'stats_element_mastery_2'
        $filter_result = array_filter($check_list, function($k, $s = 'stats_element_mastery_2'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `element_mastery_2` AS 'stats_element_mastery_2'";
            $users_statistics = true;
        }

        //'elements_primary'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_primary'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `element_affinity_1` AS 'elements_primary'";
            $users_statistics = true;
        }

        //'elements_secondary'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_secondary'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `element_affinity_2` AS 'elements_secondary'";
            $users_statistics = true;
        }

        //'elements_bloodline_primary'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_bloodline_primary'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `bloodline_affinity_1` AS 'elements_bloodline_primary'";
            $users_statistics = true;
        }

        //'elements_bloodline_secondary'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_bloodline_secondary'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `bloodline_affinity_2` AS 'elements_bloodline_secondary'";
            $users_statistics = true;
        }

        //'elements_bloodline_special'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_bloodline_special'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `bloodline_affinity_special` AS 'elements_bloodline_special'";
            $users_statistics = true;
        }

        //'elements_active_primary'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_active_primary'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", (case WHEN `rank_id` < 3 THEN '' WHEN `bloodline_affinity_1` != '' THEN `bloodline_affinity_1` ELSE `element_affinity_1` END) AS 'elements_active_primary'";
            $users_statistics = true;
        }

        //'elements_active_secondary'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_active_secondary'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", (case WHEN `rank_id` < 4 THEN '' WHEN `bloodline_affinity_2` != '' THEN `bloodline_affinity_2` WHEN `bloodline_affinity_1` != '' and `bloodline_affinity_1` = `element_affinity_2` THEN `element_affinity_1` ELSE `element_affinity_2` END) AS 'elements_active_secondary'";
            $users_statistics = true;
        }

        //'elements_active_special'
        $filter_result = array_filter($check_list, function($k, $s = 'elements_active_special'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", (case WHEN `rank_id` < 4 THEN '' ELSE `bloodline_affinity_special` END) AS 'elements_active_special'";
            $users_statistics = true;
        }


        ////////////////////////////////////////////////////////////////// users_occupations

        //'profession_exp'
        $filter_result = array_filter($check_list, function($k, $s = 'profession_exp'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`profession_exp` AS 'profession_exp'";
            $users_occuptaions = true;
        }

        //'occupation_level'
        $filter_result = array_filter($check_list, function($k, $s = 'occupation_level'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`level` AS 'occupation_level'";
            $users_occuptaions = true;
        }

        //'surgeon_sp_exp'
        $filter_result = array_filter($check_list, function($k, $s = 'surgeon_sp_exp'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`surgeonSP_exp` AS 'surgeon_sp_exp'";
            $users_occuptaions = true;
        }

        //'surgeon_cp_exp'
        $filter_result = array_filter($check_list, function($k, $s = 'surgeon_cp_exp'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`surgeonCP_exp` AS 'surgeon_cp_exp'";
            $users_occuptaions = true;
        }

        //'bounty_hunter_exp'
        $filter_result = array_filter($check_list, function($k, $s = 'bounty_hunter_exp'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`bountyHunter_exp` AS 'bounty_hunter_exp'";
            $users_occuptaions = true;
        }

        //'profession_change'
        $filter_result = array_filter($check_list, function($k, $s = 'profession_change'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`profession` AS 'profession_change'";
            $users_occuptaions = true;
        }

        //'occupation_change'
        $filter_result = array_filter($check_list, function($k, $s = 'occupation_change'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`occupation` AS 'occupation_change'";
            $users_occuptaions = true;
        }

        //'special_occupation_change'
        $filter_result = array_filter($check_list, function($k, $s = 'special_occupation_change'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`special_occupation` AS 'special_occupation_change'";
            $users_occuptaions = true;
        }

        //'bounty_hunter_tracking'
        $filter_result = array_filter($check_list, function($k, $s = 'bounty_hunter_tracking'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            $select .= ", `users_occupations`.`feature` AS 'bounty_hunter_tracking'";
            $users_occuptaions = true;
        }

        ////////////////////////////////////////////////////////////////// bingo book

        //'diplomacy_gain'
        $filter_result = array_filter($check_list, function($k, $s = 'diplomacy_gain'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $context = $requirements[$key]['context'];
                        $select .= ", `bingo_book`.`".$context."`    AS `diplomacy_gain_".$context."`";
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $context)
                        {
                            $select .= ", `bingo_book`.`".$context."`    AS `diplomacy_gain_".$context."`";
                        }
                    }

                    $bingo_book = true;
                }
                else
                {
                    $select .= ", greatest(`bingo_book`.`konoki`,`bingo_book`.`silence`,`bingo_book`.`samui`,`bingo_book`.`shroud`,`bingo_book`.`shine`,`bingo_book`.`syndicate`) AS `diplomacy_gain_any`";
                    $bingo_book = true;
                }
            }

        }

        //'diplomacy_loss'
        $filter_result = array_filter($check_list, function($k, $s = 'diplomacy_loss'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $context = $requirements[$key]['context'];
                        $select .= ", `bingo_book`.`".$context."`    AS `diplomacy_loss_".$context."`";
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $context)
                        {
                            $select .= ", `bingo_book`.`".$context."`    AS `diplomacy_loss_".$context."`";
                        }
                    }

                    $bingo_book = true;
                }
            }
        }



        ////////////////////////////////////////////////////////////////// users_jutsu

        //'jutsu_learned'
        $filter_result = array_filter($check_list, function($k, $s = 'jutsu_learned'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", count( distinct `users_jutsu_".$id."`.`id`) AS 'jutsu_learned_".$id."'";
                        $users_jutsu[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", count( distinct `users_jutsu_".$id."`.`id`) AS 'jutsu_learned_".$id."'";
                            $users_jutsu[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", count( distinct `users_jutsu_any`.`id`) as 'jutsu_learned'";
                    $users_jutsu_any = true;
                }
            }
        }

        //'jutsu_level'
        $filter_result = array_filter($check_list, function($k, $s = 'jutsu_level'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", `users_jutsu_".$id."`.`level` as 'jutsu_level_".$id."'";
                        $users_jutsu[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", `users_jutsu_".$id."`.`level` as 'jutsu_level_".$id."'";
                            $users_jutsu[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", max(`users_jutsu_any`.`level`) as 'jutsu_level'";
                    $users_jutsu_any = true;
                }
            }
        }

        //'jutsu_times_used'
        $filter_result = array_filter($check_list, function($k, $s = 'jutsu_times_used'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", `users_jutsu_".$id."`.`times_used` as 'jutsu_times_used_".$id."'";
                        $users_jutsu[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", `users_jutsu_".$id."`.`times_used` as 'jutsu_times_used_".$id."'";
                            $users_jutsu[$id] = $id;
                        }
                    }
                }
            }
        }



        ////////////////////////////////////////////////////////////////// users inventory

        //   count( distinct `users_inventory_".$id."`.`id`) AS 'item_person_".$id."'
        //   `users_inventory_".$id."`.`equipped` AS 'item_equip_".$id."'
        //   MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_".$id."_max'
        //   MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_".$id."_min'
        //   MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_".$id."_max'
        //   MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_".$id."_min'
        //   CAST( ((SUM(`users_inventory_".$id."`.`times_used`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) AS int) AS 'item_used_".$id."'
        //   CAST( ((SUM(`users_inventory_".$id."`.`stack`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) AS int) AS 'item_quantity_gain_".$id."'
        //   $CAST( ((SUM(`users_inventory_".$id."`.`stack`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) AS int) AS 'item_quantity_loss_".$id."'

        //'item_person'
        $filter_result = array_filter($check_list, function($k, $s = 'item_person'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", count( distinct `users_inventory_".$id."`.`id`) AS 'item_person_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", count( distinct `users_inventory_".$id."`.`id`) AS 'item_person_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", count( distinct `users_inventory_any`.`id`) AS 'item_person_any'";
                    $users_inventory_any = true;
                }
            }
        }

        //'item_equip'
        $filter_result = array_filter($check_list, function($k, $s = 'item_equip'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", `users_inventory_".$id."`.`equipped` AS 'item_equip_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", `users_inventory_".$id."`.`equipped` AS 'item_equip_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", sum( case when `users_inventory_any`.`equipped` = 'yes' then 1 else 0 end ) AS 'item_person_any'";
                    $users_inventory_any = true;
                }
            }
        }

        //'item_durability_gain'
        $filter_result = array_filter($check_list, function($k, $s = 'item_durability_gain'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_".$id."'";
                        //$select .= ", MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_max_".$id."'";
                //$select .= ", MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_min_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_".$id."'";
                            //$select .= ", MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_max_".$id."'";
                    //$select .= ", MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_gain_min_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
            }
        }

        //'item_durability_loss'
        $filter_result = array_filter($check_list, function($k, $s = 'item_durability_loss'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        //$select .= ", MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_max_".$id."'";
                //$select .= ", MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_min_".$id."'";
                        $select .= ", MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            //$select .= ", MAX(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_max_".$id."'";
                    //$select .= ", MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_min_".$id."'";
                            $select .= ", MIN(`users_inventory_".$id."`.`durabilityPoints`) AS 'item_durability_loss_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
            }
        }

        //'item_used'
        $filter_result = array_filter($check_list, function($k, $s = 'item_used'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", FLOOR( ((SUM(`users_inventory_".$id."`.`times_used`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) ) AS 'item_used_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", FLOOR( ((SUM(`users_inventory_".$id."`.`times_used`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) ) AS 'item_used_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", sum( `users_inventory_any`.`times_used`) AS 'item_person_any'";
                    $users_inventory_any = true;
                }
            }
        }

        //'item_quantity_gain'
        $filter_result = array_filter($check_list, function($k, $s = 'item_quantity_gain'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", FLOOR( ((SUM(`users_inventory_".$id."`.`stack`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) ) AS 'item_quantity_gain_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", FLOOR( ((SUM(`users_inventory_".$id."`.`stack`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) ) AS 'item_quantity_gain_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
            }
        }

        //'item_quantity_loss'
        $filter_result = array_filter($check_list, function($k, $s = 'item_quantity_loss'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", FLOOR( ((SUM(`users_inventory_".$id."`.`stack`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) ) AS 'item_quantity_loss_".$id."'";
                        $users_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", FLOOR( ((SUM(`users_inventory_".$id."`.`stack`)/count(`users`.`id`)) * count( distinct `users_inventory_".$id."`.`id`) ) ) AS 'item_quantity_loss_".$id."'";
                            $users_inventory[$id] = $id;
                        }
                    }
                }
            }
        }



        ////////////////////////////////////////////////////////////////// home inventory

        //'item_home'
        $filter_result = array_filter($check_list, function($k, $s = 'item_home'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", count( distinct `home_inventory_".$id."`.`id`) AS 'item_home_".$id."'";
                        $home_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", count( distinct `home_inventory_".$id."`.`id`) AS 'item_home_".$id."'";
                            $home_inventory[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", count( distinct `home_inventory_any`.`id`) AS 'item_home_any'";
                    $home_inventory_any = true;
                }
            }
        }



        ////////////////////////////////////////////////////////////////// furniture inventory(home inventory)

        //'item_furniture'
        $filter_result = array_filter($check_list, function($k, $s = 'item_furniture'){ return substr( $k, 0, strlen($s) ) == $s; }, ARRAY_FILTER_USE_KEY);
        $count = count($filter_result);
        $sum = array_sum($filter_result);
        if( $count > 0 && $sum != $count )
        {
            foreach($filter_result as $key => $status)
            {

                if($status !== true && isset($requirements[$key]['context']))
                {
                    if(!is_array($requirements[$key]['context']))
                    {
                        $id = $requirements[$key]['context'];
                        $select .= ", count( distinct `home_furniture_".$id."`.`id`) AS 'item_furniture_".$id."'";
                        $furniture_inventory[$id] = $id;
                    }
                    else
                    {
                        foreach($requirements[$key]['context'] as $id)
                        {
                            $select .= ", count( distinct `home_furniture_".$id."`.`id`) AS 'item_furniture_".$id."'";
                            $furniture_inventory[$id] = $id;
                        }
                    }
                }
                else
                {
                    $select .= ", count( distinct `home_furniture_any`.`id`) AS 'item_furniture_any'";
                    $furniture_inventory_any = true;
                }
            }
        }

        
        //building joins
        if($users_statistics)
        {
            $join .= " INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`) ";
        }

        if($users_occupations)
        {
            $join .= " INNER JOIN `users_occupations` ON (`users_occupations`.`userid` = `users`.`id`) ";
        }

        if($bingo_book)
        {
            $join .= " INNER JOIN `bingo_book` ON (`bingo_book`.`userID` = `users`.`id`) ";
        }

        if(count($users_jutsu) > 0)
        {
            foreach($users_jutsu as $id)
            {
                $join .= " LEFT JOIN `users_jutsu` as `users_jutsu_".$id."` ON ( `users_jutsu_".$id."`.`uid` = `users`.`id` AND `users_jutsu_".$id."`.`jid` = ".$id." ) ";
            }
        }

        if($users_jutsu_any)
            $join .= " LEFT JOIN `users_jutsu` as `users_jutsu_any` ON ( `users_jutsu_any`.`uid` = `users`.`id` ) ";

        if(count($users_inventory) > 0)
        {
            foreach($users_inventory as $id)
            {
                $join .= " LEFT JOIN `users_inventory` as `users_inventory_".$id."` ON ( `users_inventory_".$id."`.`uid` = `users`.`id` AND `users_inventory_".$id."`.`iid` = ".$id." ) ";
            }
        }

        if($users_inventory_any)
            $join .= " LEFT JOIN `users_inventory` as `users_inventory_any` ON ( `users_inventory_any`.`uid` = `users`.`id` ) ";

        if(count($home_inventory) > 0)
        {
            foreach($home_inventory as $id)
            {
                $join .= " LEFT JOIN `home_inventory` as `home_inventory_".$id."` ON ( `home_inventory_".$id."`.`uid` = `users`.`id` AND `home_inventory_".$id."`.`iid` = ".$id." ) ";
            }
        }

        if($home_inventory_any)
            $join .= " LEFT JOIN `home_inventory` as `home_inventory_any` ON ( `home_inventory_any`.`uid` = `users`.`id` ) ";

        if(count($furniture_inventory) > 0)
        {
            foreach($furniture_inventory as $id)
            {
                $join .= " LEFT JOIN `home_inventory` as `furniture_inventory_".$id."` ON ( `furniture_inventory_".$id."`.`uid` = `users`.`id` AND `furniture_inventory_".$id."`.`fid` = ".$id." ) ";
            }
        }

        if($furniture_inventory_any)
            $join .= " LEFT JOIN `home_inventory` as `furniture_inventory_any` ON ( `furniture_inventory_any`.`uid` = `users`.`id` ) ";

        $query = "SELECT `users`.`id` AS 'uid' ".$select." FROM `users` ".$from." ".$join." WHERE `users`.`id` = ".$this->uid." ".$where;

        try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
        catch (Exception $e)
        {
            try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
            catch (Exception $e)
            {
                try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push('','cant pull quest data from database', __METHOD__, __FILE__, __LINE__);
                    throw $e;
                }
            }
        }

        return $data;
    }

    //this function is called to update the quests if there is any new data to add to them(user specific data)
    function updateQuests()
    {

        if(is_array($this->quests) && count($this->quests) > 0)
        {
            //$quests_for_update = array();
            $qids = array();
            $case = '';

            //foreach quest
            foreach($this->quests as $qid => $quest_data)
            {
                //if the quest is marked for update
                if($quest_data->update)
                {
                    //mark the quest as no longer in need of update
                    $this->quests[$qid]->update = false;

                    //$quests_for_update[$qid] = $quest_data;
                    //put together a list of all quest id's
                    $qids[] = $qid;

                    //build a seralized and compressed package for the database of the quests data
                    $package = str_replace("'", "\'", str_replace("\\", "backslash", str_replace('#',"hashtag", gzdeflate( serialize( $quest_data->data ), 1))));

                    //build on top of the case variable to add to the query.
                    $case .= "WHEN `qid` = ".$qid." THEN '".$package."' ";
                }
            }

            //if atleast 1 quest was found go ahead with the update of the database and cache
            if(count($qids) > 0)
            {

                //doing the final build of the query
                $query = "UPDATE `users_quests` SET `data` = CASE ".$case." END, `timestamp_updated` = ".time()." WHERE `uid` = ".$this->uid." AND `qid` IN (".implode(',',$qids).")";
                
                //sending the query off to the database
                try{ $GLOBALS['database']->execute_query($query);}
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try{ $GLOBALS['database']->execute_query($query);}
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                $this->updateCache();
            }
        }

    }

    function startDialog($qid, $dialog)
    {
        if(strpos($GLOBALS['userdata'][0]['dialog'], "qid:".$qid.",dialog:".$dialog.",message:start|") === false)
        {
            if($this->quests[$qid]->failed && $this->quests[$qid]->{$dialog.'_post_failure'} != '')
                $dialog .= '_post_failure';
            
            $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
            $query = "  UPDATE `users` 
                        INNER JOIN `users_quests` ON (`users_quests`.`uid` = `users`.`id`) 
                        SET `users`.`status` = '{$status}', 
                            `users`.`dialog` = CONCAT(`dialog`, 'qid:".$qid.",dialog:".$dialog.",message:start|'), 
                            `users_quests`.`dialog_chain` = CONCAT(`users_quests`.`dialog_chain`, 'd:".$dialog.",m:start|')

                        WHERE `users`.`id` = ".$this->uid." 
                            AND `users_quests`.`qid` = ".$qid;
                            
                            $this->quests[$qid]->dialog_chain[] = array('d'=>$dialog, 'm'=>'start');
            $this->updateCache();
            
            $GLOBALS['Events']->acceptEvent('status', array('new'=>$status, 'old'=>$GLOBALS['userdata'][0]['status'] ));
            
            $GLOBALS['userdata'][0]['status'] = $status;
            $GLOBALS['template']->assign('userStatus', 'questing');
            try{ $GLOBALS['database']->execute_query($query);}
            catch (Exception $e)
            {
                try{ $GLOBALS['database']->execute_query($query); }
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query($query);}
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $query = "SELECT `dialog` FROM `users` WHERE `id` = ".$this->uid;
            $result='';
            try{ $result=$GLOBALS['database']->fetch_data($query);}
            catch (Exception $e)
            {
                try{ $result=$GLOBALS['database']->fetch_data($query); }
                catch (Exception $e)
                {
                    try{ $result=$GLOBALS['database']->fetch_data($query);}
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $GLOBALS['userdata'][0]['dialog'] = $result[0]['dialog'];

            $GLOBALS['QuestsControl']->showDialog(array('qid'=>$qid,'dialog'=>$dialog,'message'=>'start'));
        }
    }

    function trackQuest($qid)
    {
        $query = "UPDATE `users_quests` SET `track` = CASE WHEN `qid` = '".$qid."' THEN !`track` ELSE 0 END WHERE `uid` = ".$this->uid;

        try{ $GLOBALS['database']->execute_query($query);}
        catch (Exception $e)
        {
            try{ $GLOBALS['database']->execute_query($query); }
            catch (Exception $e)
            {
                try{ $GLOBALS['database']->execute_query($query);}
                catch (Exception $e)
                {
                    throw $e;
                }
            }
        }

        foreach($this->quests as $key => $record){
            if($record->track == $qid)
                $this->quests[$key]->track = 1;
            else
                $this->quests[$key]->track = 0;
        }

        $this->updateCache();
        $GLOBALS['template']->assign('tracked_quest', $this->quests[$qid]);
    }

    //this looks at what should be processed (punishements/rewards/post failure)
    //it gets what is being processed then itterates over each thing and calls a function to handle it
    function processPunishmentsOrRewards($qid, $type)
    {
        if($this->quests[$qid]->failed)
            $type .= '_post_failure';

        $categories = $this->quests[$qid]->{$type};

        if(is_array($categories) && count($categories) != 0)
        {
            foreach($categories as $category => $settings)
            {
                $category = explode('~',$category)[0];//category here in the explode call as a unset variable called key. not sure if category is correct.
                
                if(isset($settings[0]))
                {
                    foreach($settings as $setting)
                        $this->{'handle'.ucfirst($category)}($setting, $qid);
                }
                else
                    $this->{'handle'.ucfirst($category)}($settings, $qid);
            }
        }

        if(isset($this->gifts) && is_array($this->gifts) && count($this->gifts) > 0)
            $this->showGifts($this->quests[$qid]->name, $type);
    }

    function recordGifts( $category, $setting, $data, $result = true)
    {
        if(!isset($this->gifts))$this->gifts = array();
        if(!isset($this->gifts[$category]))$this->gifts[$category] = array();

        $this->gifts[$category][] = array('setting'=>$setting, 'data'=>$data, 'result'=>$result);
    }

    function showGifts($name, $type)
    {
        //helper functions for showGifts
        $PLURALITY = function ($var){return $var > 1  ? 's'         : '';        };
        $POLARITY  = function ($var){return $var > 0  ? 'increased' : 'reduced'; };
        $ARITY     = function ($var){return $var == 1 ? 'primary'   : 'secondary'; };
        $OPERATION = function ($var){return ($var == '+' || $var == '*') ? 'increased' : 'reduced'; };
        $UCFIRST   = function ($var){return ucfirst($var);};
        $BOLD      = function ($var){return "<b>{$var}</b>";};
        $ABS       = function ($var){return abs($var);};

        $SPECIALIZATION = function ($spec)
        {
            if      ($spec == 'T')  return 'Taijutsu';
            else if ($spec == 'N')  return 'Ninjutsu';
            else if ($spec == 'G')  return 'Genjutsu';
            else if ($spec == 'W')  return 'Bukijutsu';
            else                    return "({$spec} is unknown)";
        };

        $STATISTIC = function($stat)
        {
            if      ( $stat == 'cur_health' ) return 'Health';
            else if ( $stat == 'cur_sta'    ) return 'Stamina';
            else if ( $stat == 'cur_cha'    ) return 'Chakra';
            else if ( $stat == 'max_health' ) return 'Max health';
            else if ( $stat == 'max_sta'    ) return 'Max stamina';
            else if ( $stat == 'max_cha'    ) return 'Max chakra';
            else if ( $stat == 'tai_off'    ) return 'Taijutsu offence';
            else if ( $stat == 'nin_off'    ) return 'Ninjutsu offence';
            else if ( $stat == 'gen_off'    ) return 'Genjutsu offence';
            else if ( $stat == 'weap_off'   ) return 'Bukijutsu offence';
            else if ( $stat == 'tai_def'    ) return 'Taijutsu defence';
            else if ( $stat == 'nin_def'    ) return 'Ninjutsu defence';
            else if ( $stat == 'gen_def'    ) return 'Genjutsu defence';
            else if ( $stat == 'weap_def'   ) return 'Bukijutsu defence';
            else if ( $stat == 'rank'       ) return 'Rank';
            else if ( $stat == 'rank_id'    ) return 'Rank id';
            else if ( $stat == 'level'      ) return 'Rank level';
            else if ( $stat == 'level_id'   ) return 'Level';
            else if ( $stat == 'element_mastery_1' ) return 'Primary Elemental Mastery';
            else if ( $stat == 'element_mastery_2' ) return 'Secondary Elemental Mastery';
            
            else if ( in_array($stat,array('intelligence','willpower','speed','strength','experience') ) ) return ucfirst($stat);

            else return "({$stat} is unknown)";
        };
        //helper functions for showGifts

        $notification = "<b>{$name}</b><br><b>{$UCFIRST($type)}:</b><br><ul class='widget-notifications-notification-ul'>";

        //go through gifts and add them to display logic here
        foreach($this->gifts as $category => $gift)
        {
            foreach($gift as $contents)
            {
                $setting = $contents['setting'];
                $data = $contents['data'];

                if(is_array($data))
                    foreach($data as $var_name => $var_value)
                        ${$var_name} = $var_value;

                $notification .= '<li class="widget-notifications-notification-li">';

                if($category == "quest")
                {
                    if($setting == "learn")
                    {
                        $notification .= "Quest Learned: {$BOLD($data)}.";
                    }
                    else if($setting == "forget")
                    {
                        $notification .= "Quest Forgotten: {$BOLD($data)}.";
                    }
                    else if($setting == "start")
                    {
                        $notification .= "Quest Started: {$BOLD($data)}.";
                    }
                    else if($setting == "quit")
                    {
                        $notification .= "Quest Quit: {$BOLD($data)}.";
                    }
                    else if($setting == "turnIn")
                    {
                        $notification .= "Quest Turned In: {$BOLD($data)}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: quest -> ".$setting);
                    }
                }
                else if($category == "item")
                {
                    if($setting == "give")
                    {
                        $notification .= "Collected {$BOLD($diff)} ({$quantity}) {$name}{$PLURALITY($diff)}.";
                    }
                    else if($setting == "sold")
                    {
                        $notification .= "Sold {$BOLD($diff)} {$name}{$PLURALITY($diff)} for {$diff_ryo} ({$ryo}) ryo.";
                    }
                    else if($setting == "remove")
                    {
                        $notification .= "Removed {$BOLD($diff)} {$name}{$PLURALITY($diff)}.";
                    }
                    else if($setting == "durability")
                    {
                        if($diff != 'broken')
                            $notification .= "Durability {$POLARITY($diff)} by {$BOLD($ABS($diff))} ({$durability}) on {$name}.";
                        else
                            $notification .= "{$name} has been broken.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: item -> ".$setting);
                    }
                }
                else if($category == "lottery")
                {
                    if($setting == "give")
                    {
                        $notification .= "Gained {$BOLD($quantity)} ".($jackpot == 'yes' ? "jackpot " : "")."lottery ticket{$PLURALITY($quantity)}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: location -> ".$setting);
                    }
                }
                else if($category == "location")
                {
                    if($setting == "change")
                    {
                        $notification .= "Moved to {$BOLD($location)} in the {$region}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: location -> ".$setting);
                    }
                }
                else if($category == "bloodline")
                {
                    if($setting == "change")
                    {
                        $notification .= "Bloodline was changed from {$old} to {$BOLD($new)}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: bloodline -> ".$setting);
                    }
                }
                else if($category == "stat")
                {
                    if($setting == "change")
                    {
                        $notification .= "{$STATISTIC($stat)} has been {$BOLD($OPERATION($operation))} by {$BOLD($diff)} ({$result}).";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: stat -> ".$setting);
                    }
                }
                else if($category == "element")
                {
                    if($setting == "change")
                    {
                        if($old)
                            $notification .= "{$arity($affinity)} elemental affinity has been changed from {$old} to {$BOLD($new)}.";
                        else
                            $notification .= "{$arity($affinity)} elemental affinity {$BOLD($new)} has been acquired.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: element -> ".$setting);
                    }
                }
                else if($category == "specialization")
                {
                    if($setting == "change")
                    {
                        if($old)
                            $notification .= "Specialization has changed to {$BOLD($SPECIALIZATION($new))} from    {$SPECIALIZATION($old)}.";
                        else
                            $notification .= "Specialization {$BOLD($SPECIALIZATION($new))} gained.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: specialization -> ".$setting);
                    }
                }
                else if($category == "occupation")
                {
                    if($setting == "change")
                    {
                        if($old)
                            $notification .= "{$UCFIRST($type)} occupation changed from {$old} to {$BOLD($new)}.";
                        else
                            $notification .= "{$UCFIRST($type)} occupation {$BOLD($new)} started.";
                    }
                    else if($setting == "level")
                    {
                        $notification .= "Level increased in {$name} by {$BOLD($diff)} ({$current}).";
                    }
                    else if($setting == "promotion")
                    {
                        $notification .= "Promoted from {$old} ({$old_level}) to {$BOLD($new)} ({$new_level}).";
                    }
                    else if($setting == "collect")
                    {
                        $notification .= "Occupation gains collected.";
                    }
                    else if($setting == "quit")
                    {
                        $notification .= "{$UCFIRST($type)} occupation {$BOLD($name)} quit.";
                    }
                    else if($setting == "experience")
                    {
                        $notification .= "Experience {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ({$current}) for special  Occupation {$name}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: occupation -> ".$setting);
                    }
                }
                else if($category == "profession")
                {
                    if($setting == "change")
                    {
                        if($old)
                            $notification .= "Profession changed from {$old} to {$BOLD($new)}.";
                        else
                            $notification .= "Profession {$BOLD($new)} started.";
                    }
                    else if($setting == "experience")
                    {
                        $notification .= "Experience {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ({$current}) for profession   {$name}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: profession -> ".$setting);
                    }
                }
                else if($category == "diplomacy")
                {
                    if($setting == "change")
                    {
                        $notification .= "Diplomacy {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ($current) with {$village}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: diplomacy -> ".$setting);
                    }
                }
                else if($category == "jutsu")
                {
                    if($setting == "learn")
                    {
                        $notification .= "{$BOLD($name)} learned.";
                    }
                    else if($setting == "forget")
                    {
                        $notification .= "{$BOLD($name)} forgotten.";
                    }
                    else if($setting == "level")
                    {
                        $notification .= "Jutsu level {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ($current) for {$name}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: jutsu -> ".$setting);
                    }
                }
                else if($category == "currency")
                {
                    if($setting == "ryo")
                    {
                        $notification .= "Pocket ryo balance {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ({$current}).";
                    }
                    else if($setting == "bank")
                    {
                        $notification .= "Bank balance {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ({$current}).";
                    }
                    else if($setting == "pop")
                    {
                        $notification .= "Pop balance {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ({$current}).";
                    }
                    else if($setting == "rep")
                    {
                        $notification .= "Rep balance {$BOLD($POLARITY($diff))} by {$BOLD($ABS($diff))} ({$current}).";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: currency -> ".$setting);
                    }
                }
                else if($category == "home")
                {
                    if($setting == "sell")
                    {
                        $notification .= "Sold the home {$BOLD($name)} for {$BOLD($diff)} ({$current}).";
                    }
                    else if($setting == "change")
                    {
                        if($old)
                            $notification .= "Moved from {$old} to {$BOLD($new)}.";
                        else
                            $notification .= "Moved into {$BOLD($new)}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: home -> ".$setting);
                    }
                }
                else if($category == "tavern")
                {
                    if(in_array($setting, array('anbu', 'clan', 'marriage', 'Konoki', 'Shine', 'Samui', 'Silence',   'Shroud', 'Syndicate')))
                    {
                        if($sender == $GLOBALS['userdata'][0]['username'])
                            $notification .= "Sent message to {$BOLD($setting)}.";
                        else
                            $notification .= "Send message to {$BOLD($setting)} from {$BOLD($sender)}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: tavern -> ".$setting);
                    }
                }
                else if($category == "pm")
                {
                    if($setting == "receive")
                    {
                        $notification .= "New message from {$BOLD($sender)}.";
                    }
                    else if($setting == "send")
                    {
                        $notification .= "Message sent from {$BOLD($sender)} to {$BOLD($receiver)}.";
                    }
                    else
                    {
                        throw new exception("BAD SETTING TYPE FOR GIFT DISPLAY IN: pm -> ".$setting);
                    }
                }
                else
                {
                    throw new exception("BAD CATEGORY IN SHOW GIFTS");
                }

                $notification .= '</li>';
            }
        }

        $notification .= '</ul>';

        $GLOBALS['NOTIFICATIONS']->addNotification(array(
            'id' => 24,
            'duration' => 'none',
            'text' => $notification,
            'dismiss' => 'yes'
            ));
    }

    function missionRewardMultiplier($mission_scaling)
    {
        $mission_count = ($GLOBALS['userdata'][0]['mission_count'] != 0 ? $GLOBALS['userdata'][0]['mission_count'] : 1 ) % 4;
        
        if($mission_count === 0)
            $mission_count = 4;

        $rewardModification = 1.00 + ( $mission_scaling * ($mission_count - 1) / 100 );

        if( $event = functions::getGlobalEvent("DoubleMission") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $rewardModification *= round($event['data'] / 100,2);
            }
        }

        return $rewardModification;
    }


    function elementalMasteryRewardMultiplier()
    {
        $rewardModification = 1;

        if( $event = functions::getGlobalEvent("DoubleElementalMastery") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $rewardModification = round($event['data'] / 100,2);
            }
        }

        return $rewardModification;
    }

    function handleQuest($settings, $qid)
    {
        if(isset($settings['learn']))
        {
            $qid = $settings['learn'];

			if(is_array($qid))
				$qid = $qid[random_int(0, count($qid) - 1)];

            if(is_numeric($qid))
            {
                $this->learnQuest($qid, 0);
                $this->recordGifts('quest','learn',$this->quests[$qid]['name']);
            }
            else
                throw new exception('BAD SETTINGS ON quest->learn REWARD/PUNISHMENT: learn must be a quest id, but it was: '.$settings['learn']);
        }
        else if(isset($settings['forget']))
        {
            $qid = $settings['forget'];

			if(is_array($qid))
				$qid = $qid[random_int(0, count($qid) - 1)];

            if(is_numeric($qid))
            {
                $this->forgetQuest($qid);
                $this->recordGifts('quest','forget',$this->quests[$qid]['name']);
            }
            else
                throw new exception('BAD SETTINGS ON quest->forget REWARD/PUNISHMENT: learn must be a quest id, but it was: '.$settings['forget']);
        }
        else if(isset($settings['start']))
        {
            $qid = $settings['start'];
            
			if(is_array($qid))
				$qid = $qid[random_int(0, count($qid) - 1)];

            if(is_numeric($qid))
            {
                $this->startQuest($qid);
                $this->recordGifts('quest','start',$this->quests[$qid]['name']);
            }
            else
                throw new exception('BAD SETTINGS ON quest->start REWARD/PUNISHMENT: learn must be a quest id, but it was: '.$settings['start']);
        }
        else if(isset($settings['quit']))
        {
            $qid = $settings['quit'];
            
			if(is_array($qid))
				$qid = $qid[random_int(0, count($qid) - 1)];

            if(is_numeric($qid))
            {
                $this->quitQuest($qid);
                $this->recordGifts('quest','quit',$this->quests[$qid]['name']);
            }
            else
                throw new exception('BAD SETTINGS ON quest->quit REWARD/PUNISHMENT: learn must be a quest id, but it was: '.$settings['quit']);
        }
        else if(isset($settings['turn_in']))
        {
            $qid = $settings['turn_in'];
            
			if(is_array($qid))
				$qid = $qid[random_int(0, count($qid) - 1)];

            if(is_numeric($qid))
            {
                $this->turnInQuest($qid);
                $this->recordGifts('quest','turnIn',$this->quests[$qid]['name']);
            }
            else
                throw new exception('BAD SETTINGS ON quest->turn_in REWARD/PUNISHMENT: learn must be a quest id, but it was: '.$settings['turn_in']);
        }
        //else if(isset($settings['fail']))
        //{
        //    quiting is ~kinda failing so we are just going to use that for now
        //}
        else
            throw new exception('BAD SETTINGS ON quest REWARD/PUNISHMENT: setting must be (learn,forget,start,quit,turn_in). ');
    }

    function handleItem($settings, $qid)
    {
        if(isset($settings['give']['iid']))
        {
            //iid
            $iid = $settings['give']['iid'];
            
			if(is_array($iid))
				$iid = $iid[random_int(0, count($iid) - 1)];

            //quantity
            if(isset($settings['give']['quantity']))
            {
                $quantity = $settings['give']['quantity'];

				if(is_array($quantity))
					$quantity = $quantity[random_int(0, count($quantity) - 1)];
            }
            else
                $quantity = 1;

            if(isset($settings['give']['mission_scaling']))
                $quantity *= 1.00 + $this->missionRewardMultiplier($settings['give']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $quantity *= $this->elementalMasteryRewardMultiplier();

            //option stack
            if(isset($settings['give']['stacks']))
            {
                $stacks = $settings['give']['stacks'];

				if(is_array($stacks))
					$stacks = $stacks[random_int(0, count($stacks) - 1)];
            }
            else
                $stacks = 'no';

            if(isset($settings['give']['mission_scaling']))
                $stacks *= 1.00 + $this->missionRewardMultiplier($settings['give']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $stacks *= $this->elementalMasteryRewardMultiplier();


            $items = $GLOBALS['database']->fetch_data("SELECT `name`, `stack_size`,`durability`,`repairable`, (FLOOR(`stack_size` ) - FLOOR(`stack` )) AS 'top_off', `users_inventory`.`id` as 'rid', `stack` FROM `items` LEFT JOIN `users_inventory` ON (`items`.`id` = `users_inventory`.`iid` AND `users_inventory`.`uid` = ".$this->uid." AND `stack` != 0 AND `trading` IS NULL AND `finishProcessing` = 0 ) WHERE `items`.`id` = ".$iid.' ORDER BY `equipped` DESC');

            $before_stack = 0;
            $after_stack = 0;
            $before_quantity = 0;
            $after_quantity = 0;

            foreach($items as $item)
            {
                if(isset($item['stack']) && $item['stack'] !='')
                {
                    $before_stack++;
                    $after_stack++;
                    $before_quantity += $item['stack'];
                    $after_quantity += $item['stack'];
                }
            }

            if($stacks == 'no')
            {
                $after_quantity += $quantity;
                $this->recordGifts('item','give',array('diff'=>$quantity, 'quantity'=>$after_quantity, 'name'=>$items[0]['name']));
            }
            else
            {
                $after_quantity += ($quantity*$items['stack_size']);
                $this->recordGifts('item','give',array('diff'=>$quantity*$items['stack_size'], 'quantity'=>$after_quantity, 'name'=>$items[0]['name']));
            }

            //when adding items
            if(is_array($items) && isset($items[0]['stack_size']))
            {
                if($stacks == 'no' && $quantity > 0)
                {
                    //adding to stacks
                    $when_then = '';
                    $ids = array();
                    $top_off_count = 0;
                    foreach($items as $item)
                    {
                        if(isset($item['top_off']) && $item['top_off'] > 0 )
                        {
                            if($quantity < $item['top_off'])
                                $amount = $quantity;
                            else
                                $amount = $item['top_off'];

                            $quantity -= $amount;
                            $ids[] = $item['rid'];

                            $top_off_count += $amount;

                            $when_then .= ' WHEN `id` = '.$item['rid'].' THEN `stack` + '.$amount;
                        }
                    }

                    if($when_then != '')
                    {
                        if(!$GLOBALS['database']->execute_query('UPDATE `users_inventory` SET `stack` = CASE'.$when_then.' END WHERE `id` in ('.implode(',',$ids).')'))
                        {
                            throw new Exception('there was an issue updating item for give reward/punishment: '.'UPDATE `users_inventory` SET `stack` = CASE'.$when_then.' END WHERE `id` in ('.implode(',',$ids).')');
                        }
                    }

                    //building new stack
                    if($quantity <= $items[0]['stack_size'])
                    {
                        $query = 'INSERT INTO `users_inventory` (`uid`,`iid`,`equipped`,`stack`,`durabilityPoints`,`canRepair`,`timekey`) VALUES ('.$this->uid.','.$iid.',\'no\','.$quantity.','.$items[0]['durability'].',\''.$items[0]['repairable'].'\','.time().')';

                        $after_stack++;
                    }
                    //building new stacks
                    else
                    {
                        $query = 'INSERT INTO `users_inventory` (`uid`,`iid`,`equipped`,`stack`,`durabilityPoints`,`canRepair`,`timekey`) VALUES ';
                        $stack_count = 0;
                        while($quantity > 0)
                        {
                            if($quantity - $items[0]['stack_size'] > 0)
                            {
                                $quantity -= $items[0]['stack_size'];
                                $query .= '('.$this->uid.','.$iid.',\'no\','.$items[0]['stack_size'].','.$items[0]['durability'].',\''.$items[0]['repairable'].'\','.time().'), ';
                                $stack_count++;
                            }
                            else
                            {
                                $query .= '('.$this->uid.','.$iid.',\'no\','.$quantity.','.$items[0]['durability'].',\''.$items[0]['repairable'].'\','.time().')';
                                $quantity = 0;
                                $stack_count++;
                            }
                        }

                        $after_stack += $stack_count;
                    }

                }
                //when adding stacks of items
                else if($stacks == 'yes' && $quantity > 0)
                {
                    $query = 'INSERT INTO `users_inventory` (`uid`,`iid`,`equipped`,`stack`,`durabilityPoints`,`canRepair`,`timekey`) VALUES ';

                    for($i = 0; $i < $quantity; $i++)
                    {
                        if($i != 0)
                            $query .= ', ';
                        else
                            $query .= ' ';

                        $query .= '('.$this->uid.','.$iid.',\'no\','.$items[0]['stack_size'].','.$items[0]['durability'].',\''.$items[0]['repairable'].'\','.time().')';
                    }

                    $after_stack += $quantity;
                }
                else
                    throw new exception('BAD SETTINGS ON item REWARD/PUNISHMENT: give has bad stacks value: '.$stacks);

                if(is_array($query))
                {
                    foreach($query as $q)
                    {
                        if(!$GLOBALS['database']->execute_query($q))
                        {
                            throw new Exception('there was an issue updating item for give reward/punishment: '.$q);
                        }
                    }
                }
                else if(!$GLOBALS['database']->execute_query($query))
                {
                    throw new Exception('there was an issue updating item for give reward/punishment: '.$query);
                }

                if($after_stack != $before_stack)
                    $GLOBALS['Events']->acceptEvent('item_person', array('data'=>$iid, 'context'=>$iid, 'new'=>$after_stack, 'old'=>$before_stack));

                $GLOBALS['Events']->acceptEvent('item_quantity_gain', array('context'=>$iid, 'new'=>$after_quantity, 'old'=>$before_quantity));
            }
            else
                throw new exception('BAD SETTINGS ON item REWARD/PUNISHMENT: give has bad iid: '.$iid);

            //check over encumberment
            $inventory_raw = $GLOBALS['database']->fetch_data("
                SELECT `users_inventory`.*, `items`.`name`, `items`.`inventorySpace`
                FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                WHERE `uid` = '" . $this->uid . "'");


            $max = 6;
            if($GLOBALS['userdata'][0]['federal_level'] == "None")
                $max += 0;
            else if($GLOBALS['userdata'][0]['federal_level'] == "Normal")
                $max += 1;
            else if($GLOBALS['userdata'][0]['federal_level'] == "Silver")
                $max += 3;
            else if($GLOBALS['userdata'][0]['federal_level'] == "Gold")
                $max += 5;

            $current = 0;
            foreach($inventory_raw as $item)
            {
                if($item['equipped'] == 'yes')
                {
                    if($item['name'] == "Hunters Toolkit" || $item['name'] == "Herbalist Pouch" || $item['name'] == "Miners Toolkit")
                        $max += 3;
                }
                else
                {
                    if($item['inventorySpace'] == '1')
                        $current++;
                }

            }

            if($current > $max)
            {
                if(!$GLOBALS['userdata'][0]['over_encumbered'])
                    $GLOBALS['database']->overEncumbered();
            }
            else if($GLOBALS['userdata'][0]['over_encumbered'])
                $GLOBALS['database']->underEncumbered();
        }
        else if(isset($settings['remove']['iid']))
        {
            //iid
            $iid = $settings['remove']['iid'];

            if(is_array($iid))
				$iid = $iid[random_int(0, count($iid) - 1)];

            //quantity(all is an option here)
            if(isset($settings['remove']['quantity']))
            {
                $quantity = $settings['remove']['quantity'];

                if(is_array($quantity))
					$quantity = $quantity[random_int(0, count($quantity) - 1)];

                if($quantity == 'all')
                    $quantity = 999;
            }
            else
                $quantity = 1;

            if(isset($settings['remove']['mission_scaling']))
                $quantity *= 1.00 + $this->missionRewardMultiplier($settings['remove']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $quantity *= $this->elementalMasteryRewardMultiplier();

            //option stack
            //if(isset($settings['remove']['stack']))
            //{
            //    $stacks = $settings['remove']['stack'];
			//
			//	  if(is_array($stacks))
            //        $stacks = $stacks[random_int(0, count($stacks) - 1)];
            //}
            //else
            //    $stacks = 'no';

            //option sell
            if(isset($settings['remove']['sell']))
            {
                $sell = $settings['remove']['sell'];
                
				if(is_array($sell))	
					$sell = $sell[random_int(0, count($sell) - 1)];
            }
            else
                $sell = 'no';

            
            $items = $GLOBALS['database']->fetch_data("SELECT `name`, `stack_size`,`durability`,`repairable`, `stack`, `users_inventory`.`id` as 'rid', `users_inventory`.`trading`, `price` FROM `items` LEFT JOIN `users_inventory` ON (`items`.`id` = `users_inventory`.`iid` AND `users_inventory`.`uid` = ".$this->uid." AND `stack` != 0 AND `trading` IS NULL AND `finishProcessing` = 0 ) WHERE `items`.`id` = ".$iid.' ORDER BY `equipped` DESC');

            if($items[0]['rid'] == '')
                throw new exception('BAD SETTINGS ON item REWARD/PUNISHMENT: item due for deletion is missing.');

            if($sell == 'no' || $sell == 'yes' )
            {
                $needed = $quantity;
                $found = 0;
                $remove_list = array();
                $change_id = 0;
                $change_set = 0;

                for($check_trading = 0; $check_trading <= 1 && $found < $needed; $check_trading++ )
                {
                    foreach($items as $item)
                    {
                        if($check_trading == 1 && $item['trading'] != '')
                            throw exception('This quest is trying to remove an item that you are currently trading. You man not turn in this quest while the item it is trying to remove is trading.');

                        if($check_trading == 0 && $item['trading'] == '')
                        {
                            if($found != $needed && $found + $item['stack'] <= $needed)
                            {
                                $remove_list[] = $item['rid'];
                                $found += $item['stack'];
                            }
                            else if($found != $needed && $found + $item['stack'] > $needed)
                            {
                                $change_id = $item['rid'];
                                $change_set = $needed - $found;
                                $found += $change_set;
                            }
                        }
                    }
                }

                $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$iid, 'old'=>$found, 'new'=>0));

                if(count($remove_list) > 0)
                {
                    $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$iid, 'context'=>$iid, 'new'=>count($items) - count($remove_list), 'old'=>count($items) ));
                    $query = 'DELETE FROM `users_inventory` WHERE `id` in ('.implode(',',$remove_list).')';

                    if(!$GLOBALS['database']->execute_query($query))
                    {
                        throw new Exception('there was an issue updating user inventory(remove->delete).');
                    }
                }

                if($change_id != 0 && $change_set != 0)
                {
                    $query = 'UPDATE `users_inventory` SET `stack` = '.$change_set.' WHERE `id` = '.$change_id;

                    if(!$GLOBALS['database']->execute_query($query))
                    {
                        throw new Exception('there was an issue updating user inventory(remove->update)');
                    }
                }

                if($sell == 'yes')
                {
                    $sell_price = ($items[0]['price']/2) * $found;
                    if($sell_price != 0)
                    {
                        if( $event = functions::getGlobalEvent("ShopPrices") )
                        {
                            if( isset( $event['data']) && is_numeric( $event['data']) )
                            {
                                $sell_price *= round($event['data'] / 100,2);
                            }
                        }

                        if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
                            SET `users_statistics`.`money` = `users_statistics`.`money` + ".$sell_price."
                            WHERE `users_statistics`.`uid` = ".$this->uid." LIMIT 1") === false) {
                            throw new Exception("there was an issue updating user when selling items(remove->sell)");
                        }
                        else
                        {
                            $this->recordGifts('item','sold',array('diff'=>$quantity, 'diff_ryo'=>$sell_price, 'ryo'=>$GLOBALS['userdata'][0]['money'] + $sell_price, 'name'=>$items[0]['name']));                            
                            $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $sell_price));
                        }
                    }
                }
                else
                {
                    $this->recordGifts('item','remove',array('diff'=>$quantity, 'name'=>$items[0]['name']));
                }
            }
            else
                throw new exception('BAD SETTINGS ON item REWARD/PUNISHMENT: on remove, sell must be "no", "yes", or not set to anything.');

            //check over encumberment
            $inventory_raw = $GLOBALS['database']->fetch_data("
                SELECT `users_inventory`.*, `items`.`name`, `items`.`inventorySpace`
                FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                WHERE `uid` = '" . $this->uid . "'");


            $max = 6;
            if($GLOBALS['userdata'][0]['federal_level'] == "None")
                $max += 0;
            else if($GLOBALS['userdata'][0]['federal_level'] == "Normal")
                $max += 1;
            else if($GLOBALS['userdata'][0]['federal_level'] == "Silver")
                $max += 3;
            else if($GLOBALS['userdata'][0]['federal_level'] == "Gold")
                $max += 5;

            $current = 0;
            foreach($inventory_raw as $item)
            {
                if($item['equipped'] == 'yes')
                {
                    if($item['name'] == "Hunters Toolkit" || $item['name'] == "Herbalist Pouch" || $item['name'] == "Miners Toolkit")
                        $max += 3;
                }
                else
                {
                    if($item['inventorySpace'] == '1')
                        $current++;
                }

            }

            if($current > $max)
            {
                if(!$GLOBALS['userdata'][0]['over_encumbered'])
                    $GLOBALS['database']->overEncumbered();
            }
            else if($GLOBALS['userdata'][0]['over_encumbered'])
                $GLOBALS['database']->underEncumbered();
        }
        //else if(isset($settings['equip']['iid']))
        //{
        //    //iid
        //    $iid = $settings['equip']['iid'];
		//
		//    if(is_array($iid))
        //		  $iid = $iid[random_int(0, count($iid) - 1)];
        //
        //    //option "set" to yes or no or toggle
        //    if(isset($settings['equip']['set']))
        //    {
        //        $set = $settings['equip']['set'];
		//
		//		  if(is_array($set))
        //            $set = $set[random_int(0, count($set) - 1)];
        //    }
        //    else
        //        $set = 'toggle';
        //
        //    //do stuff
        //}
        else if(isset($settings['durability']['iid']))
        {
            //iid
            $iid = $settings['durability']['iid'];

			if(is_array($iid))
				$iid = $iid[random_int(0, count($iid) - 1)];

            //option operation (break,repair,*,-)
            if(isset($settings['durability']['operation']))
            {
                $operation = $settings['durability']['operation'];

				if(is_array($operation))
					$operation = $operation[random_int(0, count($operation) - 1)];
            }
            else
                $operation = '-';

            //option value
            if(isset($settings['durability']['value']))
            {
                $value = $settings['durability']['value'];

				if(is_array($value))
					$value = $value[random_int(0, count($value) - 1)];
            }
            else
                $value = '1';

            if(isset($settings['durability']['mission_scaling']))
                $value *= 1.00 + $this->missionRewardMultiplier($settings['durability']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

            $items = $GLOBALS['database']->fetch_data("SELECT `name`, `stack_size`, `durability`, `users_inventory`.`id` as 'rid', `durabilityPoints`, `equipped` FROM `items` LEFT JOIN `users_inventory` ON (`items`.`id` = `users_inventory`.`iid` AND `users_inventory`.`uid` = ".$this->uid." AND `stack` != 0 AND `trading` IS NULL AND `finishProcessing` = 0 ) WHERE `items`.`id` = ".$iid.' ORDER BY `equipped` DESC');

            if(is_array($items) && count($items) > 0)
            {

                $rids = array('update'=>array(),'delete'=>array());

                if($operation == 'break')
                {
                    $query = 'UPDATE `users_inventory` SET `durabilityPoints` = 1, `equipped` = \'no\'';

                    foreach($items as $item)
                    {
                        $rids['update'][] = $item['rid'];
                        $GLOBALS['Events']->acceptEvent('item_durability_loss', array('context'=>$iid, 'new'=>1, 'old'=>$item['durabilityPoints'] ));
                    }
                }
                else if($operation == 'repair')
                {
                    $query = 'UPDATE `users_inventory` SET `durabilityPoints` = '.$items[0]['durability'];

                    foreach($items as $item)
                    {
                        $rids['update'][] = $item['rid'];
                        $GLOBALS['Events']->acceptEvent('item_durability_gain', array('context'=>$iid, 'new'=>$item['durability'], 'old'=>$item['durabilityPoints'] ));
                    }
                }
                else if($operation == '+' || $operation == '-' || $operation == '*' || $operation == '/')
                {
                    $query = 'UPDATE `users_inventory` SET `durabilityPoints` = CASE WHEN (`durabilityPoints` '.$operation.' '.$value.') > '.$items[0]['durability'].' THEN '.$items[0]['durability'].' WHEN (`durabilityPoints` '.$operation.' '.$value.') < 0 THEN 0 ELSE (`durabilityPoints` '.$operation.' '.$value.') END';

                    foreach($items as $item)
                    {
                        if( $operation == '+')
                        {
                            $result = $item['durabilityPoints'] + $value;
                        }
                        else if( $operation == '-')
                        {
                            $result = $item['durabilityPoints'] - $value;
                        }
                        else if( $operation == '*')
                        {
                            $result = $item['durabilityPoints'] * $value;
                        }
                        else if( $operation == '/')
                        {
                            $result = $item['durabilityPoints'] / $value;
                        }

                        if($result < 0)
                            $result = 0;

                        if($result > $item['durability'])
                            $result = $item['durability'];

                        if($result != $item['durabilityPoints'] && $result != 0)
                        {
                            $rids['update'][] = $item['rid'];
                            
                            if($result > $item['durabilityPoints'])
                                $GLOBALS['Events']->acceptEvent('item_durability_gain', array('context'=>$iid, 'new'=>$result, 'old'=>$item['durabilityPoints'] ));
                            else
                                $GLOBALS['Events']->acceptEvent('item_durability_loss', array('context'=>$iid, 'new'=>$result, 'old'=>$item['durabilityPoints'] ));

                            $this->recordGifts('item','durability',array('diff'=>$result - $item['durabilityPoints'], 'durability'=>$result, 'name'=>$items[0]['name']));
                        }
                        else if($result == 0)
                        {
                            $rids['delete'][] = $item['rid'];

                            $items_temp = $GLOBALS['database']->fetch_data('SELECT * FROM `users_inventory` WHERE `iid` = '.$iid.' AND `uid` = '.$this->uid);

                            $stacks = 0;
                            $quantity = 0;
                            $quantity_removed = 0;

                            foreach($items_temp as $item_temp)
                            {
                                if(isset($item_temp['stack']) && count($item_temp['stack'] != 0))
                                {
                                    $stacks++;
                                    $quantity += $item['stack'];

                                    if($item_temp['id'] == $item['rid'])
                                        $quantity_removed = $item['stack'];
                                }
                            }

                            $this->recordGifts('item','durability',array('diff'=>'broken', 'name'=>$items[0]['name']));
                            $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$iid, 'new'=>$stacks-1, 'old'=>$stacks, 'context'=>$iid ));
                            $GLOBALS['Events']->acceptEvent('item_quanity_loss', array('context'=>$iid, 'new'=>$quantity-$quantity_removed, 'old'=>$quantity));
                        }
                    }
                }
                else
                    throw new exception('BAD SETTINGS ON ITEM REWARD/PUNISHMENT: operation must be + or - or * or / or break or repair');


                if(count($rids['update']) > 0)
                {
                    $query .= ' WHERE `id` IN ('.implode(',',$rids['update']).')';

                    if(!$GLOBALS['database']->execute_query($query))
                    {
                        throw new Exception('there was an issue updating user inventory(durability): '.$query);
                    }
                }

                if(count($rids['delete']) > 0)
                {
                    if(!$GLOBALS['database']->execute_query('DELETE FROM `user_inventory` WHERE `id` in ('.implode(',',$rids['delete']).')'))
                    {
                        throw new Exception('there was an issue updating user inventory(durability): '.$query);
                    }
                }

                //check over encumberment
                $inventory_raw = $GLOBALS['database']->fetch_data("
                    SELECT `users_inventory`.*, `items`.`name`, `items`.`inventorySpace`
                    FROM `users_inventory`
                    INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                    WHERE `uid` = '" . $this->uid . "'");


                $max = 6;
                if($GLOBALS['userdata'][0]['federal_level'] == "None")
                    $max += 0;
                else if($GLOBALS['userdata'][0]['federal_level'] == "Normal")
                    $max += 1;
                else if($GLOBALS['userdata'][0]['federal_level'] == "Silver")
                    $max += 3;
                else if($GLOBALS['userdata'][0]['federal_level'] == "Gold")
                    $max += 5;

                $current = 0;
                foreach($inventory_raw as $item)
                {
                    if($item['equipped'] == 'yes')
                    {
                        if($item['name'] == "Hunters Toolkit" || $item['name'] == "Herbalist Pouch" || $item['name'] == "Miners Toolkit")
                            $max += 3;
                    }
                    else
                    {
                        if($item['inventorySpace'] == '1')
                            $current++;
                    }

                }

                if($current > $max)
                {
                    if(!$GLOBALS['userdata'][0]['over_encumbered'])
                        $GLOBALS['database']->overEncumbered();
                }
                else if($GLOBALS['userdata'][0]['over_encumbered'])
                    $GLOBALS['database']->underEncumbered();

            }
            else
                throw new exception('BAD SETTINGS ON ITEM REWARD/PUNISHMENT: bad iid on durability');
        }
        else
        {
            throw new exception('BAD SETTINGS ON item REWARD/PUNISHMENT: give, take, durability');//equip
        }
    }

    function handleLottery($settings, $qid)
    {
        if(isset($settings['give']))
        {
            $give = $settings['give'];

            if(!isset($give['jackpot']))
                $jackpot = 'no';
            else if(strtolower($give['jackpot']) == 'yes' || $give['jackpot'] === 1 || $give['jackpot'] == '1')
                $jackpot = 'yes';
            else
                $jackpot = 'no';

            if(!isset($give['quantity']))
                $quantity = 1;
            else
                $quantity = $give['quantity'];

            //update database
            $query = "INSERT INTO `lottery`
                        ( `userid`, `jackpot` )
                      VALUES
                        ".implode(',',array_fill(0,$quantity,"( {$this->uid}, '{$jackpot}' )")).";";

            if(!$GLOBALS['database']->execute_query($query))
            {
                throw new Exception('there was an issue giving user lottery tickets. '.$query);
            }

            $this->recordGifts('lottery','give',array('quantity'=>$quantity, 'jackpot'=>$jackpot));
            return array('quantity'=>$quantity,'jackpot'=>$jackpot);
        }
        else
            return false;
    }

    function handleLocation($settings, $qid)
    {
        if(isset($settings['change']))
        {
            $change = $settings['change'];

            if(isset($change['x']) && isset($change['y']))
            {
                $x = $change['x'];
                $y = $change['y'];

				if(is_array($x))
					$x = $x[random_int(0,count($x)-1)];

				if(is_array($y))
                    $y = $y[random_int(0,count($y)-1)];
                    
                if(isset($settings['change']['mission_scaling']))
                {
                    $x *= 1.00 + $this->missionRewardMultiplier($settings['change']['mission_scaling']);
                    $y *= 1.00 + $this->missionRewardMultiplier($settings['change']['mission_scaling']);
                }

                if($this->quests[$qid]->category == 'elemental mastery')
                {
                    $x *= $this->elementalMasteryRewardMultiplier();
                    $y *= $this->elementalMasteryRewardMultiplier();
                }

                require_once(Data::$absSvrPath.'/global_libs/Travel/doTravel.php');
                $DoTravel = new DoTravel();
                $DoTravel->startPostMove('force', $x, $y);

                $this->recordGifts('location','change',array('location'=>$GLOBALS['userdata'][0]['location'], 'region'=>$GLOBALS['userdata'][0]['region']));
                return array('x'=>$x,'y'=>$y);
            }
            else
                return false;
        }
        else
            return false;
    }

    function handleBloodline($settings, $qid)
    {
        if(!isset($settings['change']['name']) || $settings['change']['name'] == '')
            throw new exception("BAD SETTINGS ON BLOODLINE REWARD/PUNISHMENT: name must be set.");

        if($GLOBALS['userdata'][0]['bloodline'] == 'None' || $GLOBALS['userdata'][0]['bloodline'] == 'none' || $GLOBALS['userdata'][0]['bloodline'] == '')
        {

            require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

            $split_type = false;
            if(isset($settings['change']['spec']))
                $split_type = $settings['change']['spec'];

            $name = $settings['change']['name'];

			if(is_array($name))
				$name = $name[random_int(0,count($name)-1)];

            $bloodline = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `name` = '".$name."'");

            if(!is_array($bloodline))
                throw new exception('BAD SETTINGS ON BLOODLINE REWARD/PUNISHMENT: cannot find the bloodline requested -> '.$name);

            if(is_array($split_type))
                $split_type = $split_type[random_int(0,count($split_type)-1)];

            // User update query
            $query = ($bloodline[0]['regen_increase'] > 0) ? ", `regen_rate` = `regen_rate` + '" . $bloodline[0]['regen_increase'] . "'" : "";

            // Update user information:
            if($split_type === false)
            {
                if(!$GLOBALS['database']->execute_query("UPDATE `users`, `users_statistics` SET `bloodline` = '" . $bloodline[0]['name'] . "' ".$query." WHERE `id` = '" . $this->uid . "' AND `uid` = `id`"))
                {
                    throw new Exception('there was an issue setting user bloodline data');
                }
                $GLOBALS['Events']->acceptEvent('bloodline', array('data'=>$bloodline[0]['name'], 'extra'=>$split_type ));
            }
            else if($split_type == 'Taijutsu' || $split_type == 'Ninjutsu' || $split_type == 'Genjutsu' || $split_type == 'Bukijutsu' || $split_type == 'Highest')
            {
                if(!$GLOBALS['database']->execute_query("UPDATE `users`, `users_statistics` SET `bloodline` = '" . $bloodline[0]['name']. ':' . $split_type . "' ".$query." WHERE `id` = '" . $this->uid . "' AND `uid` = `id`"))
                {
                    throw new Exception('there was an issue setting user bloodline data');
                }
                $GLOBALS['Events']->acceptEvent('bloodline', array('data'=>$bloodline[0]['name'], 'extra'=>$split_type ));
            }
            else
            {
                throw new Exception('bad split type: '.$split_type);
            }

            //setting affinities.
            $elements = new Elements();
            $affinities = $elements->getUserBloodlineAffinities();
            if($bloodline[0]['affinity_1'] != $affinities[0] || $bloodline[0]['affinity_2'] != $affinities[1] || $bloodline[0]['special_affinity'] != $affinities[2])
            {
                $elements->setUserBloodlineAffinities(array($bloodline[0]['affinity_1'],$bloodline[0]['affinity_2'],$bloodline[0]['special_affinity']));
            }

            $this->recordGifts('bloodline','change',array('new'=>$name, 'old'=>$GLOBALS['userdata'][0]['bloodline'], 'split_type'=>$split_type));
            return array('bloodline'=>$name);
        }
        else
            return false;
    }

    function handleStats($settings, $qid)
    {
        if(!isset($settings['change']))
            throw new exception("BAD SETTINGS ON STATS REWARD/PUNISHMENT");

        $stats = array();
        $changes = array();

        foreach($settings['change'] as $stat => $data)
        {
            if(!isset($data['operation']) || $data['operation'] == '' || !isset($data['value']) || $data['value'] == '' )
                throw new exception("BAD SETTINGS ON STATS REWARD/PUNISHMENT: operation and value must be set");
            else
            {
                $data['value'] = $data['value'];
                $data['operation'] = $data['operation'];

				if(is_array($data['value']))
                    $data['value'] = $data['value'][random_int(0,count($data['value'])-1)];

                if(isset($data['mission_scaling']))
                    $data['value'] *= 1.00 + $this->missionRewardMultiplier($data['mission_scaling']);

                if($this->quests[$qid]->category == 'elemental mastery')
                    $data['value'] *= $this->elementalMasteryRewardMultiplier();

				if(is_array($data['operation']))
                    $data['operation'] = $data['operation'][random_int(0,count($data['operation'])-1)];
                    
                //check for over cap and fix value as needed
                if( in_array($stat, array('cur_health','cur_sta','cur_cha')))
                {
                    if( $this->tryEval("{$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']}") > $GLOBALS['userdata'][0]['max'.ltrim($sta,'cur')] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $GLOBALS['userdata'][0]['max'.ltrim($sta,'cur')] - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( $stat == 'max_health') 
                {
                    $max = array(   1=>1000,
                                    2=>160000,
                                    3=>1600000,
                                    4=>2000000,
                                    5=>2500000,
                                    6=>2500000
                                );

                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > $max[$GLOBALS['userdata'][0]['rank_id']] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $max[$GLOBALS['userdata'][0]['rank_id']] - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( in_array($stat, array('max_sta','max_cha'))) 
                {
                    $max = array(   1=>1000,
                                    2=>32000,
                                    3=>160000,
                                    4=>200000,
                                    5=>250000,
                                    6=>250000
                                );

                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > $max[$GLOBALS['userdata'][0]['rank_id']] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $max[$GLOBALS['userdata'][0]['rank_id']] - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( in_array($stat, array('tai_off','tai_def','nin_off','nin_def','gen_off','gen_def','weap_off','weap_def')))
                {
                    $max = array(   1=>5000,
                                    2=>80000,
                                    3=>800000,
                                    4=>1000000,
                                    5=>1250000,
                                    6=>1250000
                                );
                    
                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > $max[$GLOBALS['userdata'][0]['rank_id']] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $max[$GLOBALS['userdata'][0]['rank_id']] - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( in_array($stat, array('intelligence','willpower','speed','strength')))
                {
                    $max = array(   1=>500,
                                    2=>16000,
                                    3=>160000,
                                    4=>200000,
                                    5=>250000,
                                    6=>250000
                                );

                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > $max[$GLOBALS['userdata'][0]['rank_id']] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $max[$GLOBALS['userdata'][0]['rank_id']] - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( $stat == 'rank_id') 
                {
                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > 5)
                    {
                        $data['operation'] = '+';
                        $data['value'] = 5 - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( $stat == 'level') 
                {
                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > 10)
                    {
                        $data['operation'] = '+';
                        $data['value'] = 10 - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( $stat == 'level_id') 
                {
                    if( $this->tryEval("return {$GLOBALS['userdata'][0][$stat]} {$data['operation']} {$data['value']};") > 50)
                    {
                        $data['operation'] = '+';
                        $data['value'] = 50 - $GLOBALS['userdata'][0][$stat];
                    }
                }
                else if( $stat == 'element_mastery_1')
                {
                    $max = array(   1 => 0, 
                                    2 => 0, 
                                    3 => 160000, 
                                    4 => 200000, 
                                    5 => 250000 );

                    require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
                    $elements = new Elements();
                    $pre = $elements->getUserElementMastery(1);

                    if( $this->tryEval("return {$pre} {$data['operation']} {$data['value']};") > $max[$GLOBALS['userdata'][0]['rank_id']] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $max[$GLOBALS['userdata'][0]['rank_id']] - $pre;
                    }

                            
                    $elements->updateUserElementMastery( ($data['operation'] != '-') ? $data['value'] : $data['value']*-1, 1);
                    $after = $elements->getUserElementMastery(1);

                    $changes[$stat] = array('old'=>$pre, 'new'=>$after);
                    $this->recordGifts('stat','change',array('stat'=>$stat, 'operation'=>$data['operation'], 'diff'=>$after - $pre, 'result'=>$after));
                }
                else if( $stat == 'element_mastery_2')
                {
                    $max = array(   1 => 0, 
                                    2 => 0, 
                                    3 => 160000, 
                                    4 => 200000, 
                                    5 => 250000 );

                    require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
                    $elements = new Elements();
                    $pre = $elements->getUserElementMastery(2);

                    if( $this->tryEval("return {$pre} {$data['operation']} {$data['value']};") > $max[$GLOBALS['userdata'][0]['rank_id']] )
                    {
                        $data['operation'] = '+';
                        $data['value'] = $max[$GLOBALS['userdata'][0]['rank_id']] - $pre;
                    }

                            
                    $elements->updateUserElementMastery( ($data['operation'] != '-') ? $data['value'] : $data['value']*-1, 2);
                    $after = $elements->getUserElementMastery(2);

                    $changes[$stat] = array('old'=>$pre, 'new'=>$after);
                    $this->recordGifts('stat','change',array('stat'=>$stat, 'operation'=>$data['operation'], 'diff'=>$after - $pre, 'result'=>$after));
                }

                if($stat != 'element_mastery_1' && $stat != 'element_mastery_2')
                    $stats[] = '`'.$stat.'` = `'.$stat.'` '.$data['operation'].' '.$data['value'];

                if(isset($GLOBALS['userdata'][0][$stat]))
                {
                    $pre = $GLOBALS['userdata'][0][$stat];
                    $GLOBALS['userdata'][0][$stat] = $this->tryEval('return '.$GLOBALS['userdata'][0][$stat].' '.$data['operation'].' '.$data['value'].';');
                    $changes[$stat] = array('old'=>$pre, 'new'=>$GLOBALS['userdata'][0][$stat]);
                    $this->recordGifts('stat','change',array('stat'=>$stat, 'operation'=>$data['operation'], 'diff'=>$GLOBALS['userdata'][0][$stat] - $pre, 'result'=>$GLOBALS['userdata'][0][$stat]));
                }
                else if( $stat != 'element_mastery_1' && $stat != 'element_mastery_2')
                    throw new exception("BAD SETTINGS ON STATS REWARD/PUNISHMENT: target stat({$stat}) must be in (cur_health, cur_sta, cur_cha, max_health, max_sta, max_cha, tai_off, nin_off, gen_off, weap_off, tai_def, nin_def, gen_def, weap_def, intelligence, willpower, speed, strength, rank, rank_id, level, level_id, experience)");
            }
        }

        $query = "UPDATE `users_statistics` SET ".implode(', ',$stats)." WHERE `uid` = ".$this->uid;

        try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE STATS REWARD/PUNISHMENT'); }
        catch (Exception $e)
        {
            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE STATS REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE STATS REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    throw $e;
                }
            }
        }

        return $changes;
    }

    function handleElement($settings, $qid)
    {
        if  (   
                !isset($settings['change']) || 
                !isset($settings['change']['element']) || 
                !isset($settings['change']['affinity']) || 
                $settings['change']['element'] == '' || 
                $settings['change']['affinity'] == ''
            )
            throw new exception("BAD SETTINGS ON ELEMENT REWARD/PUNISHMENT: element and affinity both need to be set.");

        $element = $settings['change']['element'];

		if(is_array($element))
			$element = $element[random_int(0, count($element)-1)];

        if(!in_array($element, array('fire','water','lightning','earth','wind')))
            throw new exception("BAD SETTINGS ON ELEMENT REWARD/PUNISHMENT: element has to be (fire, water, lightning, earth, wind).");

        $affinity = $settings['change']['affinity'];

		if(is_array($affinity))
			$affinity = $affinity[random_int(0, count($affinity)-1)];

        require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
        $elements = new Elements();

        if($settings['change']['affinity'] == 1)
        {
            $old = $elements->getUserBloodlineAffinities(1);
            $elements->setUserElementAffinityPrimary($element);
        }
        else if($settings['change']['affinity'] == 2)
        {
            $old = $elements->getUserBloodlineAffinities(2);
            $elements->setUserElementAffinitySecondary($element);
        }
        else 
            throw new exception('BAD SETTINGS ON ELEMENT REWARD/PUNISHMENT: affinity can only be set to 1 or 2.');

        $this->recordGifts('element','change',array('affinity'=>$settings['change']['affinity'],'new'=>$element, 'old'=>$old));
        return array('affinity'=>$affinity, 'element'=>$element);
    }

    function handleSpecialization($settings, $qid)
    {
        if(!isset($settings['change']['spec']) && $settings['change']['spec'] != '')
            throw new exception('BAD SETTINGS ON SPECIALIZATION REWARD/PUNISHMENT: spec must be set');

        $new = $settings['change']['spec'];

		if(is_array($new))
			$new = ucfirst($new[random_int(0, count($new)-1)]);

        if(!in_array($new, array('T','N','G','W')))
            throw new exception('BAD SETTINGS ON SPECIALIZATION REWARD/PUNISHMENT: spec must be in (T,N,G,W)');

        // Check current specialization
        $specialization = explode(":", $GLOBALS['userdata'][0]['specialization']);
        if(!isset($specialization[0]) || empty($specialization[0]) )
        {
            return false;
        }

        if($specialization[0] === $new) { return false; }

        // Update database
        if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `users_statistics`.`specialization` = '".$new.":".$specialization[1]."'
            WHERE `users_statistics`.`uid` = ".$this->uid." LIMIT 1") === false) {
            throw new Exception('There was an error updating specialization!');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('specialization', array('new'=>$new, 'old'=>$specialization[0] ));
            $this->recordGifts('specialization','change',array('new'=>$new, 'old'=>$specialization[0] ));
            return array('spec'=>$new);
        }
        return false;
    }

    function handleOccupation($settings, $qid)
    {
        require_once(Data::$absSvrPath.'/content/OccupationControl.php');
        $OccupationControl = new OccupationControl(true);

        //changing occupations
        if(isset($settings['change']) && $settings['change'] != '')
        {
            if(!isset($settings['change']['occupation']) || $settings['change']['occupation'] == '')
                throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: occupation must be set');

            if(!isset($settings['change']['type']) || $settings['change']['type'] == 'normal')
            {

                $occupation = $settings['change']['occupation'];

				if(is_array($occupation))
					$occupation = $occupation[random_int(0, count($occupation)-1)];

                //if you have a job quit it
                if($OccupationControl->OccupationData->hasNormalOccupation())
                    $OccupationControl->quitNormalOccupation();

                $old = $OccupationControl->normal_occupation['name'];
                $OccupationControl->aquireNormalOccupation($occupation);
                $new = $OccupationControl->normal_occupation['name'];

                $this->recordGifts('occupation','change',array('type'=>'normal','old'=>$old, 'new'=>$new));
                return array('normal'=>$occupation);
            }
            else
            {
                $occupation = $settings['change']['occupation'];

				if(is_array($occupation))
					$occupation = $occupation[random_int(0, count($occupation)-1)];

                //if you have a job quit it
                if($OccupationControl->OccupationData->hasSpecialOccupation())
                    $OccupationControl->quitSpecialOccupation();

                $old = $OccupationControl->special_occupation['name'];
                $OccupationControl->aquireSpecialOccupation($occupation);
                $new = $OccupationControl->special_occupation['name'];

                $this->recordGifts('occupation','change',array('type'=>'special','old'=>$old, 'new'=>$new));
                return array('special'=>$occupation);
            }
        }

        //leveling occupation and promoting if at max level
        else if(isset($settings['level']) && $settings['level'] != '')
        {
            if(!isset($settings['level']['type']) || $settings['level']['type'] == 'normal')
            {
                if($OccupationControl->normal_occupation['level'] < 10)
                {
                    $name = $OccupationControl->normal_occupation['name'];
                    $old = $OccupationControl->normal_occupation['level'];
                    $OccupationControl->normalLevelUp();
                    $new = $OccupationControl->normal_occupation['level'];

                    $this->recordGifts('occupation','level',array('name'=>$name, 'diff'=>$new-$current, 'current'=>$new));
                    return array('level'=>$OccupationControl->normal_occupation['level']);
                }
                else
                {
                    $next = $OccupationControl->OccupationData->getNextNormalOccupation($GLOBALS['userdata'][0]['rank_id']);
                    if($next !== false)
                    {
                        $old = $OccupationControl->normal_occupation['name'];
                        $old_level = $OccupationControl->normal_occupation['level'];

                        $level = $OccupationControl->normal_occupation['level'] - 4;
                        $OccupationControl->promoteNormalOccupation($next, $level);

                        $new = $OccupationControl->normal_occupation['name'];
                        $new_level = $OccupationControl->normal_occupation['level'];

                        $this->recordGifts('occupation','promotion',array('old'=>$old,'old_level'=>$old_level,'new'=>$new,'new_level'=>$new_level));
                        return array('promotion'=>$next['id'], 'level'=>$level);
                    }
                }
            }
            else
                throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: level does not work with this type.');
            return false;
        }

        //promoting
        else if(isset($settings['promote']) && $settings['promote'] != '')
        {
            if(!isset($settings['promote']['type']) || $settings['promote']['type'] == 'normal')
            {
                $next = $OccupationControl->OccupationData->getNextNormalOccupation($GLOBALS['userdata'][0]['rank_id']);
                if($next !== false)
                {
                    $level = $OccupationControl->normal_occupation['level'] - 4;
                    if($level < 1)
                        $level = 1;

                    $old = $OccupationControl->normal_occupation['name'];
                    $old_level = $OccupationControl->normal_occupation['level'];

                    $OccupationControl->promoteNormalOccupation($next, $level);

                    $new = $OccupationControl->normal_occupation['name'];
                    $new_level = $OccupationControl->normal_occupation['level'];

                    $this->recordGifts('occupation','promotion',array('old'=>$old,'old_level'=>$old_level,'new'=>$new,'new_level'=>$new_level));
                    return array('promotion'=>$next['id'], 'level'=>$level);
                }
            }
            else
                throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: promote does not work with this type.');

            return false;
        }

        //collecting gains
        else if(isset($settings['collect']) && $settings['collect'] != '')
        {
            if(!isset($settings['collect']['type']) || $settings['collect']['type'] == 'normal')
            {
                $OccupationControl->collectGain();
                $this->recordGifts('occupation','collect','collected');
                return array('gains'=>'collected');
            }
            else
                throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: collect does not work with this type.');

            return false;
        }

        //quiting
        else if(isset($settings['quit']) && $settings['quit'] != '')
        {
            if(!isset($settings['quit']['type']) || $settings['quit']['type'] == 'normal')
            {
                $name = $OccupationControl->normal_occupation['name'];
                $OccupationControl->quitNormalOccupation();
                $this->recordGifts('occupation','quit',array('type'=>'normal', 'name'=>$name));
                return array('normal'=>'quit');
            }
            else
            {
                $name = $OccupationControl->special_occupation['name'];
                $OccupationControl->quitSpecialOccupation();
                $this->recordGifts('occupation','quit',array('type'=>'special', 'name'=>$name));
                return array('special'=>'quit');
            }
        }

        //updating experience
        else if(isset($settings['experience']) && $settings['experience'] != '')
        {
            if(isset($settings['experience']['type']) && $settings['experience']['type'] != 'normal')
            {
                if(!isset($settings['experience']['value']) || $settings['experience']['value'] == '')
                    throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: value must be set');

                $exp = $settings['experience']['value'];

				if(is_array($exp))
                    $exp = $exp[random_int(0,count($exp)-1)];
                    
                if(isset($settings['experience']['mission_scaling']))
                    $exp *= 1.00 + $this->missionRewardMultiplier($settings['experience']['mission_scaling']);

                if($this->quests[$qid]->category == 'elemental mastery')
                    $exp *= $this->elementalMasteryRewardMultiplier();

                if(!isset($settings['experience']['type']) || $settings['experience']['type'] == '')
                    throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: type must be set');

                $type = $settings['experience']['type'];
                
				
				if(is_array($type))
                    $type = $type[random_int(0,count($type)-1)];
                    
                //making sure exp doesn't go over cap.
                //surgeonSP_exp / 10000
                //surgeonCP_exp / 10000
                //bountyHunter_exp / 1000

                $query = 'UPDATE `users_occupations` SET `'.$type.'_exp` = `'.$type.'_exp` + '.$exp." WHERE `userid` = ".$this->uid;

                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE SPECIAL OCCUPATION EXPERIENCE REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE SPECIAL OCCUPATION EXPERIENCE REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE SPECIAL OCCUPATION EXPERIENCE REWARD/PUNISHMENT'); }
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                $name = $OccupationControl->special_occupation['name'];
                $current = $OccupationControl->OccupationData->special_occupation[$type] + $exp;
                $OccupationControl->OccupationData->updateSpecialOccupationCache($current);
                
                $this->recordGifts('occupation','experience',array('diff'=>$exp, 'current'=>$current, 'name'=>$name));
                return array($type=>$exp);
            }
            else
                throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: experience does not work with this type.');

            return false;
        }

        else
            throw new exception('BAD SETTINGS ON OCCUPATION REWARD/PUNISHMENT: either change or level must be set');

        return false;
    }

    function handleProfession($settings, $qid)
    {
        if(isset($settings['change']['profession']) && $settings['change']['profession'] != '')
        {
            require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
            $ProfessionLib = new professionLib();
            $ProfessionLib->setupProfessionData();
            $ProfessionLib->fetch_user();

            $profession_id = $settings['change']['profession'];

            if(is_array($profession_id))
                $profession_id = $profession_id[random_int(0, count($profession_id)-1)];

            if($ProfessionLib->start_profession($profession_id, true))
            {
                $query = "SELECT * FROM `users_occupations` INNER JOIN `occupations` ON (`occupations`.`id` = `users_occupations`.`profession`) WHERE `userid` = ".$this->uid;
                $profession = $GLOBALS['database']->fetch_data($query);

                $this->recordGifts('profession','change',array('old'=>$ProfessionLib->user[0]['name'], 'new'=>$profession[0]['name']));
                return array('profession'=>$profession_id);
            }
            else
                return false;
        }
        else if(isset($settings['experience']['value']) && $settings['experience']['value'] != '')
        {
            $exp = $settings['experience']['value'];
            
			if(is_array($exp))
                $exp = $exp[random_int(0, count($exp)-1)];
                
            if(isset($settings['experience']['mission_scaling']))
                $exp *= 1.00 + $this->missionRewardMultiplier($settings['experience']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $exp *= $this->elementalMasteryRewardMultiplier();

            $query = "SELECT * FROM `users_occupations` INNER JOIN `occupations` ON (`occupations`.`id` = `users_occupations`.`profession`) WHERE `userid` = ".$this->uid;
            $profession = $GLOBALS['database']->fetch_data($query);

            if(!is_array($profession))
                throw new exception('failure to get profession data.');

            $max = array(   1=>25,
                            2=>75,
                            3=>150,
                            4=>300,
                            5=>450,
                            6=>450  );

            if($exp + $profession[0]['profession_exp'] > $max[$GLOBALS['userdata'][0]['rank_id']])
                $exp = $max[$GLOBALS['userdata'][0]['rank_id']] - $profession[0]['profession_exp'];

            $GLOBALS['database']->execute_query( "UPDATE `users_occupations` SET `profession_exp` = `profession_exp` + ".$exp." WHERE `userid` = ".$this->uid );

            $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$profession[0]['profession_exp'] + $exp, 'old'=> $profession[0]['profession_exp'] ));

            $this->recordGifts('profession',array('diff'=>$exp, 'current'=>$profession[0]['profession_exp'] + $exp, 'name'=>$profession[0]['name']));
            return array('experience'=>$exp);
        }
        else
            throw new exception('BAD SETTINGS ON PROFESSION REWARD/PUNISHMENT: either change and profession or experience and value must be set.');

        return false;
    }

    function handleDiplomacy($settings, $qid)
    {
        if(!isset($settings['change']['value']) || !isset($settings['change']['village']) || $settings['change']['value'] == '' || $settings['change']['village'] == '')
            throw new exception('BAD SETTINGS ON DIPLOMACY REWARD/PUNISHMENT: change -> value and change -> village must be set');

        $village = $settings['change']['village'];

        if(is_array($village))
			$village = $village[random_int(0, count($village)-1)];

        if($village == 'this')
            $village = $GLOBALS['userdata'][0]['village'];

        $village = ucfirst($village);

        $value = $settings['change']['value'];
        
		if(is_array($value))
            $value = intval($value[random_int(0, count($value)-1)]);
            
        if(isset($settings['change']['mission_scaling']))
            $value *= 1.00 + $this->missionRewardMultiplier($settings['change']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

        $query = 'UPDATE `bingo_book` SET `'.$village.'` = `'.$village.'` + '.$value.' WHERE `userID` = '.$this->uid;

        try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE DIPLOMACY REWARD/PUNISHMENT'); }
        catch (Exception $e)
        {
            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE DIPLOMACY REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE DIPLOMACY REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    throw $e;
                }
            }
        }

        if( (in_array($village,array('Konoki','Shine','Samui','Silence','Shroud')) && $value > 0) || $village == 'Syndicate' && $value < 0)
        {
            $fetch_query = 'SELECT `'.$village.'` as `diplomacy` FROM `bingo_book` WHERE `userID` = '.$this->uid;
            $result = $GLOBALS['database']->fetch_data($fetch_query);
            $GLOBALS['Events']->acceptEvent('diplomacy_gain', array('new'=>$result[0]['diplomacy'], 'old'=>$result[0]['diplomacy'] - $value, 'context'=> $village));
            $this->recordGifts('diplomacy','change',array('diff'=>$value, 'current'=>$result[0]['diplomacy'], 'village'=>$village));
            return array('diplomacy_gain'=>$value, 'village'=>$village);
        }
        else
        {
            $fetch_query = 'SELECT `'.$village.'` as `diplomacy` FROM `bingo_book` WHERE `userID` = '.$this->uid;
            $result = $GLOBALS['database']->fetch_data($fetch_query);
            $GLOBALS['Events']->acceptEvent('diplomacy_loss', array('new'=>$result[0]['diplomacy'], 'old'=>$result[0]['diplomacy'] - $value, 'context'=> $village));
            $this->recordGifts('diplomacy','change',array('diff'=>$value * -1, 'current'=>$result[0]['diplomacy'], 'village'=>$village));
            return array('diplomacy_loss'=>$value, 'village'=>$village);
        }

        return false;
    }

    function handleJutsu($settings, $qid)
    {
        if(isset($settings['learn']['jid']))
        {
            $jid = $settings['learn']['jid'];
            
			if(is_array($jid))
				$jid = $jid[random_int(0,count($jid)-1)];

            $query = "INSERT INTO `users_jutsu` ( `uid` , `jid` , `level` , `exp` , `tagged` )VALUES ('".$this->uid."', '".$jid."', '1', '0', 'no');";

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $GLOBALS['Events']->acceptEvent('jutsu_learned', array('data'=>$jid, 'context'=>$jid));
            $GLOBALS['Events']->acceptEvent('jutsu_level',   array('new'=>1, 'old'=>0, 'data'=>$jid, 'context'=>$jid));
            
            $jutsu = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` where `id` = {$jid}");
            $this->recordGifts('jutsu','learn',array('name'=>$jutsu[0]['name']));
            return array('learned'=>$jid);
        }
        else if(isset($settings['forget']['jid']))
        {
            $jid = $settings['forget']['jid'];
            
			if(is_array($jid))
				$jid = $jid[random_int(0,count($jid)-1)];

            $query = "DELETE FROM `users_jutsu` WHERE `uid` = ".$this->uid." AND `jid` = ".$jid;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $jutsu = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` where `id` = {$jid}");
            $this->recordGifts('jutsu','forget',array('name'=>$jutsu[0]['name']));
            return array('forgotten'=>$jid);
        }
        else if(isset($settings['level']['jid']) && isset($settings['level']['value']))
        {
            $jid = $settings['level']['jid'];
            
			if(is_array($jid))
				$jid = $jid[random_int(0,count($jid)-1)];

            $value = $settings['level']['value'];
            
			if(is_array($value))
                $value = $value[random_int(0,count($value)-1)];
                
            if(isset($settings['level']['mission_scaling']))
                $value *= 1.00 + $this->missionRewardMultiplier($settings['level']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

            $query = "SELECT * FROM `jutsu` LEFT JOIN `users_jutsu` on (`jutsu`.`id` = `users_jutsu`.`jid` AND `uid` = {$this->uid}) WHERE `jutsu`.`id` = ".$jid;

            $jutsu = $GLOBALS['database']->fetch_data($query);

            if(!isset($jutsu[0]['level']))
                $starting_level = 0;
            else
                $starting_level = $jutsu[0]['level'];

            //checking to make sure this doesn't go over cap
            if( $starting_level + $value > $jutsu[0]['max_level'] )
                $value = $jutsu[0]['max_level'] - $starting_level;

            $query = "UPDATE `users_jutsu` SET `level` = `level` + ".$value." WHERE `uid` = ".$this->uid." AND `jid` = ".$jid;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE JUTSU REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $GLOBALS['Events']->acceptEvent('jutsu_level', array('new'=>$starting_level + $value,'old'=>$starting_level,'data'=>$jid, 'context'=>$jid));

            $jutsu = $GLOBALS['database']->fetch_data("SELECT * FROM `users_jutsu` INNER JOIN `jutsu` ON (`jid` = `id`) where `uid` = {$this->uid} AND `jid` = {$jid}");
            $this->recordGifts('jutsu','level',array('name'=>$jutsu[0]['name'], 'diff'=>$value, 'current'=>$jutsu[0]['level']));
            return array('leveled'=>$jid, 'gain'=>$value);
        }
        else
            throw new exception('BAD SETTINGS ON JUTSU REWARD/PUNISHMENT: must be set (learn/forget -> jid) OR (level -> jid AND value) ');
    }

    function handleCurrency($settings, $qid)
    {
        if(isset($settings['ryo']['value']))
        {
            $value = $settings['ryo']['value'];

			if(is_array($value))
                $value = $value[random_int(0,count($value)-1)];

            if(isset($settings['ryo']['mission_scaling']))
                $value *= 1.00 + $this->missionRewardMultiplier($settings['ryo']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

            //making sure ryo doesn't go over cap
            if( $GLOBALS['userdata'][0]['money'] + $value > 200000000 )
                $value = 200000000 - $GLOBALS['userdata'][0]['money'];


            if(((float)$value) > 0)
                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $value));
            else
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $value));

            $GLOBALS['userdata'][0]['money']+=$value;

            if($value != 0)
            {
                $query = "UPDATE `users_statistics` SET `money` = `money` + ".$value." WHERE `uid` = ".$this->uid;

                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE RYO REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE RYO REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE RYO REWARD/PUNISHMENT'); }
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }
            }

            $this->recordGifts('currency','ryo',array('diff'=>$value, 'current'=>$GLOBALS['userdata'][0]['money']));
            return array('ryo'=>$value);
        }

        else if(isset($settings['bank']['value']))
        {
            $value = $settings['bank']['value'];

			if(is_array($value))
                $value = $value[random_int(0,count($value)-1)];
                
            if(isset($settings['bank']['mission_scaling']))
                $value *= 1.00 + $this->missionRewardMultiplier($settings['bank']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

            //making sure ryo doesn't go over cap
            if( $GLOBALS['userdata'][0]['bank'] + $value > 200000000 )
                $value = 200000000 - $GLOBALS['userdata'][0]['bank'];

            if(((float)$value) > 0)
                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['bank'],'new'=> $GLOBALS['userdata'][0]['bank'] + $value));
            else
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['bank'],'new'=> $GLOBALS['userdata'][0]['bank'] + $value));

            $GLOBALS['userdata'][0]['bank']+=$value;

            $query = "UPDATE `users_statistics` SET `bank` = `bank` + ".$value." WHERE `uid` = ".$this->uid;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE BANK REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE BANK REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE BANK REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $this->recordGifts('currency','bank',array('diff'=>$value, 'current'=>$GLOBALS['userdata'][0]['bank'] + $value));
            return array('bank'=>$value);
        }

        else if(isset($settings['pop']['value']))
        {
            $value = $settings['pop']['value'];

			if(is_array($value))
                $value = $value[random_int(0,count($value)-1)];
                
            if(isset($settings['pop']['mission_scaling']))
                $value *= 1.00 + $this->missionRewardMultiplier($settings['pop']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

            if(((float)$value) > 0)
                $GLOBALS['Events']->acceptEvent('pop_gain', array('old'=>$GLOBALS['userdata'][0]['pop_now'],'new'=> $GLOBALS['userdata'][0]['pop_now'] + $value));
            else
                $GLOBALS['Events']->acceptEvent('pop_loss', array('old'=>$GLOBALS['userdata'][0]['pop_now'],'new'=> $GLOBALS['userdata'][0]['pop_now'] + $value));

            $GLOBALS['userdata'][0]['pop_now']+=$value;

            $query = "UPDATE `users_statistics` SET `pop_now` = `pop_now` + ".$value.", `pop_ever` = `pop_ever` + ".$value." WHERE `uid` = ".$this->uid;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE POP REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE POP REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE POP REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $this->recordGifts('currency','pop',array('diff'=>$value, 'current'=>$GLOBALS['userdata'][0]['pop_now']));
            return array('pop'=>$value);
        }

        else if(isset($settings['rep']['value']))
        {
            $value = $settings['rep']['value'];
            
			if(is_array($value))
                $value = $value[random_int(0,count($value)-1)];
                
            if(isset($settings['rep']['mission_scaling']))
                $value *= 1.00 + $this->missionRewardMultiplier($settings['rep']['mission_scaling']);

            if($this->quests[$qid]->category == 'elemental mastery')
                $value *= $this->elementalMasteryRewardMultiplier();

            if(((float)$value) > 0)
                $GLOBALS['Events']->acceptEvent('rep_gain', array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] + $value));
            else
                $GLOBALS['Events']->acceptEvent('rep_loss', array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] + $value));

            $GLOBALS['userdata'][0]['rep_now']+=$value;

            $query = "UPDATE `users_statistics` SET `rep_now` = `rep_now` + ".$value.", `rep_ever` = `rep_ever` + ".$value." WHERE `uid` = ".$this->uid;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE REP REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE REP REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE REP REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $this->recordGifts('currency','rep',array('diff'=>$value, 'current'=>$GLOBALS['userdata'][0]['rep_now']));
            return array('rep'=>$value);
        }
    }
    
    function handleHome($settings, $qid)
    {
        if(isset($settings['sell']))    
        {
            require_once(Data::$absSvrPath.'/libs/home/home_helper.php');

            // Get user information
            if(!($user = $GLOBALS['database']->fetch_data('
                SELECT  `users`.`id`, `users`.`apartment`, `users`.`status`,
                        `users_statistics`.`money`,
                        `homes`.`married_home`, `homes`.`price`, `homes`.`name`,
                        `marriages`.`married`, `marriages`.`uid`, `marriages`.`oid`,
                        `spouse`.`status` as `spouseStatus`, `spouse_statistics`.`money` as `spouse_money`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `homes` ON (`homes`.`id` = `users`.`apartment`)
                    LEFT JOIN `marriages` ON (`marriages`.`married` = "Yes" AND (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`))
                    LEFT JOIN `users` AS `spouse` ON (`spouse`.`id` = IF(`users`.`id` = `marriages`.`uid`, `marriages`.`oid`, `marriages`.`uid`))
                    LEFT JOIN `users_statistics` AS `spouse_statistics` ON (`spouse_statistics`.`uid` =
                                                                  IF(`users`.`id` = `marriages`.`uid`, `marriages`.`oid`, `marriages`.`uid`))
                WHERE `users`.`id` = '.$this->uid.' LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive necessary information.');
            }

            // Check information was found
            if($user === '0 rows') {
                throw new Exception("Either you're not awake or you don't exist. Lets hope it's not the latter!");
            }

            // Check user status
            if($user[0]['status'] !== 'awake') {
                throw new Exception('You must be awake to sell a house!');
            }

            // Check that there is something to sell
            if($user[0]['apartment'] === NULL) {
                throw new Exception("You don't have a house to sell!");
            }

            // Check if it's a marriage home or a single-home
            if($user[0]['married_home'] === 'Yes') {

                // Check user status
                if($user[0]['spouseStatus'] !== 'awake') {
                    throw new Exception('Your spouse must be awake to sell marriage house!');
                }

                //here
                HomeHelper::MoveAllToStorageBox($user[0]['uid']);
                HomeHelper::MoveAllToStorageBox($user[0]['oid']);

                // Money
                $money = ($user[0]['price'] / 4);

                // Sell the home
                if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `users`
                    SET `users_statistics`.`money` = `users_statistics`.`money` + '.$money.', `users`.`apartment` = NULL
                    WHERE `users`.`id` IN ('.$user[0]['uid'].', '.$user[0]['oid'].') AND `users_statistics`.`uid` = `users`.`id`')) === false) {
                    throw new Exception('There was an error trying to sell the house for you and your spouse.');
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>'sold'));
                    $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=> $GLOBALS['userdata'][0]['money'] + $money));

                    require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                    $events = new Events($user[0]['oid']);
                    $events->acceptEvent('home', array('data'=>'sold'));
                    $events->acceptEvent('money_gain', array('old'=>$user[0]['spouse_money'], 'new'=> $user[0]['spouse_money'] + $money));
                    $events->closeEvents();
                }

            }
            else {

                HomeHelper::MoveAllToStorageBox($this->uid);

                // Money
                $money = ($user[0]['price'] / 2);

                // Sell home
                if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `users`
                    SET `users_statistics`.`money` = `users_statistics`.`money` + '.$money.', `users`.`apartment` = NULL
                    WHERE `users`.`id` = '.$user[0]['id'].' AND `users_statistics`.`uid` = `users`.`id`')) === false) {
                    throw new Exception('There was an error trying to sell the house.');
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>'sold'));
                    $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=> $GLOBALS['userdata'][0]['money'] + $money));
                }

                $this->recordGifts('home','sell',array('diff'=>$money, 'current'=>$GLOBALS['userdata'][0]['money'] + $money, 'name'=>$user[0]['name']));
                return array('apartment'=>'sold', 'ryo'=>$money);
            }
        }
        else if(isset($settings['change']['home']) && $settings['change']['home'] != '')
        {
            $home = $settings['change']['home'];
            
			if(is_array($home))
				$home = $home[random_int(0, count($home)-1)];

            $query = 'UPDATE `users` SET `apartment` = '.$home.' WHERE `id` = '.$this->uid;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE HOME REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception($e); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception($e); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $old = $GLOBALS['database']->fetch_data("SELECT * FROM `homes` WHERE `id` = ".$GLOBALS['userdata'][0]['apartment']);
            $new = $GLOBALS['database']->fetch_data("SELECT * FROM `homes` WHERE `id` = ".$home);

            $GLOBALS['Events']->acceptEvent('home', array('data'=>$home));
            $this->recordGifts('home','change',array('old'=>$old[0]['name'], 'new'=>$new[0]['name']));
        }
        else
            throw new exception('BAD SETTINGS ON HOME REWARD/PUNISHMENT: sell or change -> home must be set');
    }

    function handleTavern($settings, $qid)
    {
        if(isset($settings['message']))
        {
            //getting message
            $message = $settings['message'];
            
			if(is_array($message))
				$message = $message[random_int(0, count($message) - 1)];

            //getting location
            if(isset($settings['location']))
            {
                $location = $settings['location'];
                
				if(is_array($location))
					$location = $location[random_int(0, count($location) - 1)];
            }
            else
                $location = $GLOBALS['userdata'][0]['village'];

            //getting user
            if(isset($settings['username']))
            {
                $username = $settings['username'];
                
				if(is_array($username))
					$username = $username[random_int(0, count($username) - 1)];
            }
            else
            {
                $username = $GLOBALS['userdata'][0]['username'];
                $GLOBALS['Events']->acceptEvent('tavern_send', array('data'=>str_replace('\'','\\\'',strip_tags($message)), 'context'=>$GLOBALS['userdata'][0]['username']));
            }

            //getting user_data
            if(isset($settings['user_data']))
            {
                $user_data = $settings['user_data'];
                
				if(is_array($user_data))
					$user_data = $user_data[random_int(0, count($user_data) - 1)];
            }
            else
                $user_data = $GLOBALS['userdata'][0]['rank'];

            //getting user_group
            if(isset($settings['user_group']))
            {
                $user_group = $settings['user_group'];

				if(is_array($user_group))
					$user_group = $user_group[random_int(0, count($user_group) - 1)];
            }
            else
               $user_group = $GLOBALS['userdata'][0]['user_rank'];

            if(in_array($location, array('Konoki', 'Shine', 'Samui', 'Shroud', 'Silence', 'Syndicate')))
            {
                $query = "INSERT INTO `tavern` ( `village_name`,  `user`,          `user_data`,      `user_group`,      `message`,                                `time`,       `uid` ) VALUES 
                                               ( '".$location."', '".$username."', '".$user_data."', '".$user_group."', '".functions::store_content($message)."', '".time()."', '0')";
            }
            else if($location == 'anbu')
            {
                //getting user_group
                if(isset($settings['anbu']))
                {
                    $anbu = $settings['anbu'];
                    
					if(is_array($anbu))
						$anbu = $anbu[random_int(0, count($anbu) - 1)];
                }
                else
                   $anbu = $GLOBALS['userdata'][0]['anbu'];

                if(is_numeric($anbu))
                    $query = "INSERT INTO `tavern` ( `anbu_name`,  `user`,          `user_data`,      `user_group`,      `message`,      `time`,       `uid` ) VALUES 
                                                   ( '".$anbu."', '".$username."', '".$user_data."', '".$user_group."', '".functions::store_content($message)."', '".time()."', '0')";
            }
            else if($location == 'clan')
            {
                //getting user_group
                if(isset($settings['clan']))
                {
                    $clan = $settings['clan'];
                    
					if(is_array($clan))
						$clan = $clan[random_int(0, count($clan) - 1)];
                }
                else
                   $clan = $GLOBALS['userdata'][0]['clan'];

                if(is_numeric($clan))
                    $query = "INSERT INTO `tavern` ( `clan_name`,  `user`,          `user_data`,      `user_group`,      `message`,      `time`,       `uid` ) VALUES 
                                                   ( '".$clan."', '".$username."', '".$user_data."', '".$user_group."', '".functions::store_content($message)."', '".time()."', '0')";
            }
            //else if($location == 'kage')
            //{
            //    
            //}
            else if($location == 'marriage')
            {
                $query = "SELECT `mid` FROM `marriages` WHERE (`uid` = ".$this->uid." OR `oid` = ".$this->uid.") AND `married` = 'Yes'";

                $mid = $GLOBALS['database']->fetch_data($query);

                if(isset($mid[0]['mid']))
                {
                    $mid = $mid[0]['mid'];

                    $query = "INSERT INTO `tavern` ( `marriage_id`,  `user`,          `user_data`,      `user_group`,      `message`,      `time`,       `uid` ) VALUES 
                                                   ( '".$mid."', '".$username."', '".$user_data."', '".$user_group."', '".functions::store_content($message)."', '".time()."', '0')";
                }
            }

            if(isset($query) && $query != '')
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE TAVERN REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE TAVERN REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE TAVERN REWARD/PUNISHMENT'); }
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }

                $this->recordGifts('tavern',$location,array('sender'=>$username, 'message'=>functions::store_content($message)));
                return array('location'=>$location, 'message'=>$message, 'username'=>$username);
            }
            return false;
        }
        else
            throw new exception('BAD SETTINGS ON TAVERN REWARD/PUNISHMENT: tavern -> location and tavern -> message must be set');
        return false;
    }

    function handlePm($settings, $qid)
    {
        if(isset($settings['receive']['message']))
        {
            //sender_uid
            if(isset($settings['receive']['sender']))
            {
                $sender = $settings['receive']['sender'];
                
				if(is_array($sender))
					$sender = $sender[random_int(0, count($sender) - 1)];

                $sender_text = $sender;
            }
            else
            {
                $sender = $this->uid;
                $sender_text = $GLOBALS['userdata'][0]['username'];
            }

            $receiver = $this->uid;
            $receiver_text = $GLOBALS['userdata'][0]['username'];

            //in users set new_pm = + 1
            $query = "UPDATE `users` SET `new_pm` = `new_pm` + 1 WHERE `id` = ".$receiver;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            //message
            $message = $settings['receive']['message'];

			if(is_array($message))
				$message = $message[random_int(0, count($message) - 1)];

            //subject
            if(isset($settings['receive']['subject']))
            {
                $subject = $settings['receive']['subject'];
                
				if(is_array($subject))
					$subject = $subject[random_int(0, count($subject) - 1)];
            }
            else
                $subject = 'n/a';


            $query = "INSERT INTO `users_pm` (`sender_uid`,  `receiver_uid`,  `time`,       `message`,                                `subject`,                                `read`) 
                                      VALUES ('".$sender."', '".$receiver."', '".time()."', '".functions::store_content($message)."', '".functions::store_content($subject)."', 'no')";

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            $this->recordGifts('pm','receive',array('sender'=>$sender_text, 'receiver'=>$receiver_text, 'message'=>functions::store_content($message)));
            return array('receiver'=>$receiver_text, 'sender'=>$sender_text, 'message'=>$message);
        }
        else if(isset($settings['send']['message']))
        {
            //sender_uid
            if(isset($settings['send']['sender']))
            {
                $sender = $settings['send']['sender'];
                
				if(is_array($sender))
					$sender = $sender[random_int(0, count($sender) - 1)];

                $sender_text = $sender;
            }
            else
            {
                $sender = $this->uid;
                $sender_text = $GLOBALS['userdata'][0]['username'];
            }

            //receiver_uid
            if(isset($settings['send']['receiver']))
            {
                $receiver = $settings['send']['receiver'];
                
				if(is_array($receiver))
					$receiver = $receiver[random_int(0, count($receiver) - 1)];

                $receiver_text = $receiver;

                $query = "SELECT * FROM `users` WHERE `username` = '".$receiver."'";

                $receiver = $GLOBALS['database']->fetch_data($query);

                if(isset($receiver[0]['username']))
                    $receiver = $receiver[0]['id'];
                else
                    return false;
            }
            else
            {
                $query = "SELECT `id`, `username` FROM `users` INNER JOIN `users_timer` ON (`users`.`id` = `users_timer`.`userid`) WHERE ((UNIX_TIMESTAMP() - `last_activity`) < 300) AND (UNIX_TIMESTAMP() < `logout_timer`) AND `id` != ".$this->uid;

                $users = $GLOBALS['database']->fetch_data($query);

                if(is_array($users) && count($users) > 0)
                {
                    $user = $users[random_int(0, count($users) - 1)];

                    $receiver = $user['id'];
                    $receiver_text = $user['username'];
                }
                else
                {
                    $receiver = $this->uid;
                    $receiver_text = $GLOBALS['userdata'][0]['username'];
                }
            }

            //in users set new_pm = + 1
            $query = "UPDATE `users` SET `new_pm` = `new_pm` + 1 WHERE `id` = ".$receiver;

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            //message
            $message = $settings['send']['message'];
            
			if(is_array($message))
				$message = $message[random_int(0, count($message) - 1)];

            //subject
            if(isset($settings['send']['subject']))
            {
                $subject = $settings['send']['subject'];
                
				if(is_array($subject))
					$subject = $subject[random_int(0, count($subject) - 1)];
            }
            else
                $subject = 'n/a';


            $query = "INSERT INTO `users_pm` (`sender_uid`,  `receiver_uid`,  `time`,       `message`,                                `subject`,                                `read`) 
                                      VALUES ('".$sender."', '".$receiver."', '".time()."', '".functions::store_content($message)."', '".functions::store_content($subject)."', 'no')";

            try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
            catch (Exception $e)
            {
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }

            if(is_numeric($sender) && is_numeric($receiver))
            {
                $query = "INSERT INTO `users_outbox` (`pm_id`,                                       `sender_uid`,  `receiver_uid`,   `time`,       `subject`,                                `message`) 
                                              VALUES ('".$GLOBALS['database']->get_inserted_id()."', '".$sender."', '".$receiver."', '".time()."', '".functions::store_content($subject)."', '".functions::store_content($message)."')";
                
                try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                catch (Exception $e)
                {
                    try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                    catch (Exception $e)
                    {
                        try{ if(!$GLOBALS['database']->execute_query($query)) throw new exception('FAILED TO UPDATE PM REWARD/PUNISHMENT'); }
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                    }
                }
            }

            if($sender == $this->uid)
                $GLOBALS['Events']->acceptEvent('pm_send', array('data'=>str_replace('\'','\\\'',strip_tags($message)), 'context'=>$GLOBALS['userdata'][0]['username']));
            else if(is_numeric($sender))
            {
                $events = new Events($sender);
                $events->acceptEvent('pm_send', array('data'=>str_replace('\'','\\\'',strip_tags($message)), 'context'=>$sender_text));
                $events->closeEvents();
            }

            $this->recordGifts('pm','send',array('sender'=>$sender_text, 'receiver'=>$receiver_text, 'message'=>functions::store_content($message)));
            return array('receiver'=>$receiver_text, 'sender'=>$sender_text, 'message'=>$message);
        }
        else
            throw new exception('BAD SETTINGS ON PM REWARD/PUNISHMENT: PM -> receive/send must be set');
        return false;
    }



    function updateCache()
    {
        if( isset($this->cache_active) && $this->cache_active)
		{
            $this->update_cache = true;
		}
    }

    function updateCacheDo()
    {
        if($this->cache_active && $this->update_cache === true)
		{
            $GLOBALS['cache']->set(Data::$target_site.$this->uid.'quests',  $this->quests, MEMCACHE_COMPRESSED, 60*60);
		}
    }

    public function mission_maintenance($active_mission = true)
    {
        if($GLOBALS['userdata'][0]['mission_collection_time'] != 0 && date('Y-m-d', $GLOBALS['userdata'][0]['mission_collection_time']) != date('Y-m-d'))
        {
            if(!$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `mission_count` = 0, `mission_collection_time` = ".(new DateTime())->getTimestamp().", `missions_collected` = '".$active_mission->qid."', `missions_offered` = '' WHERE `userid` = ".$this->uid))
            {
                throw new Exception('There was an issue recording what missions have been randomly selected for you. ');
            }

            $GLOBALS['userdata'][0]['mission_count'] = 1;
            $GLOBALS['userdata'][0]['mission_collection_time'] = (new DateTime())->getTimestamp();
            $GLOBALS['userdata'][0]['missions_collected'] = $active_mission->qid;
            $GLOBALS['userdata'][0]['missions_offered'] = '';
        }
        else
        {
            $GLOBALS['userdata'][0]['mission_count'] += 1;
            $GLOBALS['userdata'][0]['missions_offered'] = '';

            if(!$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `mission_count` = ".$GLOBALS['userdata'][0]['mission_count'].", `missions_offered` = '' WHERE `userid` = ".$this->uid))
            {
                throw new Exception('There was an issue recording what missions have been randomly selected for you. ');
            }
        }

        if(isset($active_mission->level))
        {
            if($active_mission->level >= 41)
                $target = 'a_';

            else if($active_mission->level >= 31)
                $target = 'b_';

            else if($active_mission->level >= 21)
                $target = 'c_';

            else if($active_mission->level >= 11)
                $target = 'd_';



            if($GLOBALS['userdata'][0]['village'] != 'Syndicate')
                $target .= 'mission';

            else
                $target .= 'crime';



            $query = "UPDATE `users_missions` SET `$target` = `$target` + 1 where `userid` = ".$this->uid;

            if(!$GLOBALS['database']->execute_query($query))
            {
                throw new Exception('There was an issue updating you mission completion counter.');
            }
        }
    }
}