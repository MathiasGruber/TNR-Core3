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
 *Class: Hooks
 *  this class works with events and quests control to find needed hooks and act on them
 */

require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/QuestContainer.php');
require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

class Hooks
{
    public $hooks_fired = array();
    public $combat_hooks = array();
    public $combat_started = false;

    function __construct($uid = false, $events)
    {
        if($uid)
            $this->uid = $uid;
        else
            $this->uid = $_SESSION['uid'];

        $this->events = $events;
    }

    function tryEval($command)
    {
        try
        {
            return eval($command);
        }
        catch (exception $e)
        {
            var_dump("Bad Eval:");
            echo'<br>';
            var_dump($e);
            echo'<br>';
            error_log($e);
            echo'<br>';
            echo'<br>';
            return false;
        }
    }

    //processing events for hooks.
    public function checkHooks()
    {
        $events_pre_hooks = $this->events;

        if(count($this->events) > 8 && is_array($this->events))
        {
            $query = "SELECT `hooks`.`id`,`hooks`.`category`,`hooks`.`action`, `hooks`.`user_data_restraints`, `hooks`.`item_restraints`, `hooks`.`quest_restraints` FROM `hooks` WHERE `hooks`.`state` = 'on' AND ";
            $and_or = '';
            $end = '';

            $used_int_triggers = array();
            $used_varchar_triggers = array();

            foreach($this->events as $event_key => $event)
            {
                $event_key = str_replace("'","\'",$event_key);

                $query .=   '(';

                foreach($event as $case)
                {
                    if((isset($case['data']) && $case['data'] !== '') || (isset($case['new']) && $case['new'] !== '') )
                    {
                        if(!isset($case['data']) || $case['data'] === '')
                        {
                            $case['data'] = $case['new'];
                        }

                        $case['data'] = str_replace("'","\'",$case['data']);

                        if(in_array($event_key, 'Events'::$int_events) && in_array($event_key, array_keys('Events'::$mod_list_for_events_to_hooks_table)))
                        {
                            $alt_key = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];

                            $query .= ' ((( `'.$event_key.'_<=` = `'.$event_key.'_>=` AND `'.$event_key.'_<=` = '.$case['data'].' AND `'.$event_key.'_>=` = '.$case['data'].') OR ( `'.$event_key.'_<=` < `'.$event_key.'_>=` AND `'.$event_key.'_<=` <= '.$case['data'].' AND `'.$event_key.'_>=` >= '.$case['data'].') OR ( `'.$event_key.'_<=` > `'.$event_key.'_>=` AND (`'.$event_key.'_<=` <= '.$case['data'].' OR `'.$event_key.'_>=` >= '.$case['data'].'))) AND (`'.$alt_key.'` LIKE \'%"'.$case['Events'::$mod_list_for_events_to_hooks_table[$event_key][$alt_key]].'"%\' OR `'.$alt_key.'` = \'any\' ) ) OR ';
                        }
                        else if(in_array($event_key, 'Events'::$int_events))
                        {
                            $query .= ' (( `'.$event_key.'_<=` = `'.$event_key.'_>=` AND `'.$event_key.'_<=` = '.$case['data'].' AND `'.$event_key.'_>=` = '.$case['data'].') OR ( `'.$event_key.'_<=` < `'.$event_key.'_>=` AND `'.$event_key.'_<=` <= '.$case['data'].' AND `'.$event_key.'_>=` >= '.$case['data'].') OR ( `'.$event_key.'_<=` > `'.$event_key.'_>=` AND (`'.$event_key.'_<=` <= '.$case['data'].' OR `'.$event_key.'_>=` >= '.$case['data'].'))) OR ';
                        }
                        else if($event_key == 'tavern_send' || $event_key == 'tavern_receive' || $event_key == 'pm_send' || $event_key == 'pm_receive')
                        {
                            $alt_key = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];

                            $query .= ' ((\''.$case['data'].'\' LIKE CONCAT(\'%\',`'.$event_key.'`,\'%\') OR `'.$event_key.'` = \'any\' ) AND (`'.$alt_key.'` LIKE \'%"'.$case['Events'::$mod_list_for_events_to_hooks_table[$event_key][$alt_key]].'"%\' OR `'.$alt_key.'` = \'any\' ) ) OR ';
                        }
                        else if(in_array($event_key, array_keys('Events'::$mod_list_for_events_to_hooks_table)))
                        {

                            $alt_key = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];

                            $query .= ' ((`'.$event_key.'` LIKE \'%"'.$case['Events'::$mod_list_for_events_to_hooks_table[$event_key][$event_key]].'"%\' OR `'.$event_key.'` = \'any\' ) AND (`'.$alt_key.'` LIKE \'%"'.$case['Events'::$mod_list_for_events_to_hooks_table[$event_key][$alt_key]].'"%\' OR `'.$alt_key.'` = \'any\' ) ) OR ';
                        }
                        else if( !in_array($event_key,['location_region','location_owner','location_claimable']) || !in_array($GLOBALS['userdata'][0]['location'], ["Konoki","Silence","Shroud","Shine","Samui","Gambler's Den","Bandit's Outpost","Poacher's Camp","Pirate's Hideout"]))
                        {
                            $query .= ' ( (!(POSITION(\'!\' IN `'.$event_key.'`)) AND `'.$event_key.'` LIKE \'%"'.$case['data'].'"%\') OR ( POSITION(\'!\' IN `'.$event_key.'`) AND `'.$event_key.'` NOT LIKE \'%"'.$case['data'].'"%\') OR `'.$event_key.'` = \'any\' ) OR ';
                        }
                    }
                }

                if(in_array($event_key, 'Events'::$int_events))
                {
                    $query .= '( `'.$event_key.'_<=` is NULL AND `'.$event_key.'_>=` is NULL )  ) AND ';
                    $used_int_triggers[] = $event_key;

                    if(isset('Events'::$mod_list_for_events_to_hooks_table[$event_key]))
                    {
                        if(in_array('Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'], 'Events'::$varchar_events))
                            $used_varchar_triggers[] = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];
                        else if(in_array('Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'], 'Events'::$int_events))
                            $used_int_triggers[] = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];
                    }

                }
                else
                {
                    $query .= '`'.$event_key.'`  = \'\' ) AND ';
                    $used_varchar_triggers[] = $event_key;

                    if(isset('Events'::$mod_list_for_events_to_hooks_table[$event_key]))
                    {
                        if(in_array('Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'], 'Events'::$varchar_events))
                            $used_varchar_triggers[] = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];
                        else if(in_array('Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'], 'Events'::$int_events))
                            $used_int_triggers[] = 'Events'::$mod_list_for_events_to_hooks_table[$event_key]['alt_key'];
                    }

                }

                if($end != '')
                    $end .= ' OR ';


                if(in_array($event_key, 'Events'::$int_events))
                    $end .= ' (`'.$event_key.'_<=` is not NULL AND `'.$event_key.'_>=` is not NULL ) ';
                else
                    $end .= '`'.$event_key.'` != \'\' ';
            }

            foreach('Events'::$int_events as $int_trigger)
            {
                if(!in_array($int_trigger, $used_int_triggers))
                {
                    if($and_or != '')
                        $and_or .= ' AND ';

                    $and_or .= ' (`'.$int_trigger.'_<=` is NULL AND `'.$int_trigger.'_>=` is NULL ) ';
                }
            }

            foreach('Events'::$varchar_events as $varchar_trigger)
            {
                if(!in_array($varchar_trigger, $used_varchar_triggers))
                {
                    if($and_or != '')
                        $and_or .= ' AND ';

                    $and_or .= '`'.$varchar_trigger.'` = \'\' ';
                }
            }

            $query .= '((`logic` = \'and\' AND '.$and_or.') OR `logic` = \'or\' ) AND ( '.$end.' );';

            
            $before = $query;
            $after = str_replace("\\\\'","\\'",$query);
            while($before != $after)
            {
                $before = $after;
                $after = str_replace("\\\\'","\\'",$query);
            }

            $query = $after;

            $hooks = $GLOBALS['database']->fetch_data($query);

            if(is_array($hooks))
            {
                foreach($hooks as $hook)
                {
                    $restraints_good = true;

                    if($hook['user_data_restraints'] != '')
                    {
                        $user_data_restraints = json_decode($hook['user_data_restraints'], true);

                        if(is_null($user_data_restraints) && strlen($hook['user_data_restraints']) > 6)
                            throw new Exception('JSON returned null! hook: '.$hook['id']);

                        $elements = new Elements();
                        $currentElements = $elements->getUserElements();
                        $GLOBALS['userdata'][0]['elements_active_primary'] = $currentElements[0];
                        $GLOBALS['userdata'][0]['elements_active_secondary'] = $currentElements[1];
                        $GLOBALS['userdata'][0]['elements_active_special'] = $currentElements[2];

                        foreach($user_data_restraints as $user_data_restraint_key => $restraint_eval)
                        {
                            if( strlen($restraint_eval) > 6)
                            {
                                if(isset($GLOBALS['userdata'][0][$user_data_restraint_key]))
                                {
                                    $data = $GLOBALS['userdata'][0][$user_data_restraint_key];
                                    if(!is_numeric($data))
                                        $data = '\''.str_replace('\'','\\\'',$data).'\'';

                                    $restraint_eval = str_replace('user_data',$data,$restraint_eval);
                                    $restraint_eval = str_replace(' = ',' == ',$restraint_eval);
                                    $restraint_eval = str_replace('OR','||',$restraint_eval);
                                    $restraint_eval = str_replace('Or','||',$restraint_eval);
                                    $restraint_eval = str_replace('or','||',$restraint_eval);
                                    $restraint_eval = str_replace('AND','&&',$restraint_eval);
                                    $restraint_eval = str_replace('And','&&',$restraint_eval);
                                    $restraint_eval = str_replace('and','&&',$restraint_eval);

                                    $result = $this->tryEval('return '.$restraint_eval.';');

                                    if($result === false)
                                    {
                                        $restraints_good = false;
                                        break;
                                    }
                                }
                                else
                                    throw new exception('no user data that matches: '.$user_data_restraint_key);
                            }
                        }
                    }

                    if($hook['quest_restraints'] != '' && $restraints_good)
                    {
                        $restraints_good = false;
                        $quest_restraint_flag = true;
                        $statuses = array_flip(QuestContainer::$statuses);

                        $quest_restraints = json_decode($hook['quest_restraints'], true);

                        if(is_null($quest_restraints) && strlen($hook['quest_restraints']) > 6)
                            throw new Exception('JSON returned null! hook: '.$hook['id']);

                        foreach($quest_restraints as $qid => $data)
                        {
                            $qid = explode('~',$qid)[0];

                            if(!is_numeric($qid))
                                throw new exception('bad qid: '.$qid);

                            if(!is_array($data))
                            {
                                $status = $data;

                                if($status != 'unknown' && !isset($statuses[$status]))
                                    throw new exception('bad quest status: '.$status.' vs '.print_r(array_keys($statuses),true));

                                if(
                                    ($status == 'unknown' && isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                    ||
                                    ($status == 'failed' && !$GLOBALS['QuestsControl']->QuestsData->quests[$qid]->failed)
                                    ||
                                    ($status == 'not failed' && $GLOBALS['QuestsControl']->QuestsData->quests[$qid]->failed)
                                    ||
                                    (!in_array($status, array('unknown','failed','not failed')) && !isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                    ||
                                    (!in_array($status, array('unknown','failed','not failed')) && $GLOBALS['QuestsControl']->QuestsData->quests[$qid]->status != $statuses[$status])
                                )
                                    $quest_restraint_flag = false;
                            }
                            else
                            {
                                if(!isset($data['requirement']))
                                    throw new exception('hook with quest restraint missing quest requirement: '.$hook['id']);
                                else
                                    $requirement = $data['requirement'];
                                
                                if(!isset($data['status']))
                                    $status = 1;
                                else
                                    $status = $data['status'];

                                if(!isset($data['type']))
                                    $type = "completion";
                                else if($data['type'] != 'completion' && $data['type'] != 'failure')
                                    throw new exception('hook with bad type: '.$data['type']);
                                else if(in_array($data['status'], [0,'0',false,'false','no','off','!','']))
                                    $status = false;
                                else
                                    $status = true;
                                
                                if(substr($requirement,0,4) != 'join')
                                {
                                    if( !isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]->data[$type.'_check_list'][$requirement]) || $GLOBALS['QuestsControl']->QuestsData->quests[$qid]->data[$type.'_check_list'][$requirement] !== $status )
                                        $quest_restraint_flag = false;
                                }
                                else
                                {
                                    $join_status = true;
                                    if(isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]->{$type.'_requirements'}))
                                    {
                                        foreach($GLOBALS['QuestsControl']->QuestsData->quests[$qid]->{$type.'_requirements'} as $req_key => $req)
                                        {
                                            if(isset($req['joined']) && $req['joined'] == $requirement && (!isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]->data[$type.'_check_list'][$req_key]) || !$GLOBALS['QuestsControl']->QuestsData->quests[$qid]->data[$type.'_check_list'][$req_key]))
                                            {
                                                $join_status = false;
                                                break;
                                            }
                                        }
                                    }

                                    if($join_status !== $status)
                                        $quest_restraint_flag = false;
                                }
                            }
                        }

                        if($quest_restraint_flag)
                            $restraints_good = true;
                    }

                    if($hook['item_restraints'] != '' && $restraints_good)
                    {
                        //SELECT count(*) as 'result' FROM `users_inventory` WHERE `uid` = '1096' AND ( ( `iid` = 48 AND `equipped` = 'yes' ) OR (`iid` = 226 AND `equipped` = 'no'))
                        $query = 'SELECT `iid`, SUM(`stack`) as \'count\' FROM `users_inventory` WHERE `uid` = '.$_SESSION['uid'].' AND ( ';

                        //$items = explode(';',$hook['item_restraint']);

                        $item_restraints = json_decode($hook['item_restraints'], true);

                        if(is_null($item_restraints) && strlen($hook['item_restraints']) > 6)
                            throw new Exception('JSON returned null! hook: '.$hook['id']);

                        if(isset($item_restraints['eval']))
                        {
                            $items_eval = $item_restraints['eval'];
                            unset($item_restraints['eval']);
                        }
                        else
                            $items_eval = false;

                        foreach($item_restraints as $iid => $item_restraint_data)
                        {
                            if(is_numeric($iid))
                            {
                                $query .= '( `iid` = '.$iid;

                                if( isset($item_restraint_data['equipped']) && ($item_restraint_data['equipped'] == 'yes' || $item_restraint_data['equipped'] === true))
                                {
                                    $query .= ' AND `equipped` = \'yes\'';
                                }
                                else if( isset($item_restraint_data['equipped']) && ($item_restraint_data['equipped'] == 'no' || $item_restraint_data['equipped'] === false))
                                {
                                    $query .= ' AND `equipped` = \'no\'';
                                }

                                $query .= ' ) OR ';
                            }
                            else
                                throw new exception('Bad item id: "'.$iid.'"');
                        }

                        $query = rtrim($query,' OR ').' ) GROUP BY `iid`';

                        $temp_results = $GLOBALS['database']->fetch_data($query);

                        $results = array();
                        foreach($temp_results as $result)
                        {
                            if(isset($result['iid']))
                                $results[$result['iid']] = $result['count'];
                        }
                        
                        //if using eval
                        if($items_eval !== false)
                        {
                            foreach($item_restraints as $iid => $item_restraint_data)
                            {
                                if(is_numeric($iid) && !isset($results[$iid]))
                                {
                                    $results[$iid] = 0;
                                }
                            }

                            //update results to contain an entry for each (set 0 if missing)
                            foreach($item_restraints as $iid => $item_restraint_data)
                            {
                                if(!isset($results[$iid]))
                                    $results[$iid] = 0;
                            }

                            //replace eval words with contents of results
                            foreach($results as $iid => $count)
                            {
                                $items_eval = str_replace('item_count_'.$iid,$count,$items_eval);
                            }

                            //and eval
                            if(!$this->tryEval('return '.$items_eval.';'))
                                $restraints_good = false;
                        }
                        //if not using eval
                        else
                        {
                            //make sure we have atleast 1 of each
                            foreach($item_restraints as $iid => $item_restraint_data)
                            {
                                if(!isset($results[$iid]))
                                    $restraints_good = false;
                            }
                        }
                    }

                    if($restraints_good === true)
                    {
                        if(!in_array($hook['id'], $this->hooks_fired))
                        {
                            if(!substr(preg_replace('/\s+/', ' ', $hook['action']),0,10) == '{"combat":')
                                $this->handleHook($hook);
                            else
                                $this->combat_hooks[] = $hook;

                            $this->hooks_fired[] = $hook['id'];
                        }
                    }
                }
            }

            if(count($this->combat_hooks) == 1 && !$this->combat_started && isset($this->combat_hooks[0]))
            {
                $this->handleHook($this->combat_hooks[0]);
                unset($this->combat_hooks[0]);
            }
            else if(count($this->combat_hooks) != 0 && isset($this->combat_hooks[0]))
            {
                shuffle($this->combat_hooks);
                usort($this->combat_hooks, function ($a, $b)
                {
                    $categorys = ['story'=>0,'mission'=>1,'crime'=>1,'elemental mastery'=>1,'forbidden'=>1,'travel'=>2,'misc'=>3];
                    $a = $a['category'];
                    $b = $b['category'];

                    if($a == $b || (!isset($categorys[$a]) && !isset($categorys[$b])))
                        return 0;
                    else if( !isset($categorys[$b]) )
                        return -1;
                    else if( !isset($categorys[$a]) )
                        return -1;
                    else if( $categorys[$a] < $categorys[$b] )
                        return -1;
                    else if( $categorys[$a] > $categorys[$b] )
                        return 1;
                });
                $this->combat_hooks = array_values($this->combat_hooks);

                $category = $this->combat_hooks[0]['category'];
                foreach($this->combat_hooks as $key => $hook)
                {
                    if($hook['category'] == $category)
                    {
                        $this->handleHook($hook, $key);
                        unset($this->combat_hooks[$key]);
                    }
                    else
                        break;
                }
            }

            if( strpos($_SERVER['SERVER_NAME'], 'theninja-development') && !in_array($GLOBALS['userdata'][0]['user_rank'], ['Member','Paid']))
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => '<details><summary>events</summary><p><pre>'.print_r($this->events,true).'</pre></p></details>'));
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => '<details><summary>query</summary><p><pre>'.$query.'</pre></p></details>'));
            }
            //error_log(print_r($this->events,true));
            //error_log(print_r($hooks,true));
            //error_log($query);
            //error_log(' ');
        }

        //checking to see if ther eis any difference between old events and current
        if(isset($GLOBALS['Events']->events))
        {
            $new_events_keys = array_diff(array_keys($GLOBALS['Events']->events), array_keys($events_pre_hooks));
            if(count($new_events_keys) > 0)
            {
                $this->events = $GLOBALS['Events']->events;
                $this->checkHooks();

                $new_events = $this->events;
                foreach($new_events as $new_events_key => $new_event)
                    if(!in_array($new_events_key, $new_events_keys))
                        unset($new_events[$new_events_key]);

                $GLOBALS['QuestsControl']->recordEvents($new_events);
                
                //checking quests
                $GLOBALS['QuestsControl']->QuestsData->updateQuests();
                $GLOBALS['QuestsControl']->checkForFailureAndCompletion();
            }
        }
    }



    private function handleHook($hook, $combat_addition = false)
    {
        if( strpos($_SERVER['SERVER_NAME'], 'theninja-development') )
        {
            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => '<details><summary>hook</summary><p><pre>'.print_r($hook,true).'</pre></p></details>'));
        }

        if($hook['action'] != '') // checking to make sure that action is not empty
        {
            $actions = json_decode($hook['action'], true);//parsing json

            if(is_null($actions) && strlen($hook['action']) > 6)//checking for failed parse.
                throw new Exception('JSON returned null!');

            foreach($actions as $action_type => $action_data)//foreach action listed.
            {
                if(substr( $action_type, 0, 5 ) === "quest")//if this is a quest action
                {
                    if(isset($action_data['qid']))//if qid is set
                    {
                        if(!isset($action_data['chance']) || random_int(1,100) < $action_data['chance'])//if there is not a chance or the chance roll is good
                        {
                            if(isset($action_data['all']))
                                $all = $action_data['all'];
                            else
                                $all = false;

                            if(isset($action_data['force']))
                                $force = $action_data['force'];
                            else
                                $force = false;

                            if(is_array($action_data['qid']) && $all != true)//if qid is an array
                                $qids = $action_data['qid'][random_int(0,count($action_data['qid'])-1)];
                            
                            else//if quid is not an array
                                $qids = $action_data['qid'];

                            if(!is_array($qids))
                                $qids = array($qids);

                            if(isset($action_data['action']))
                                $action = $action_data['action'];
                            else
                                $action = 'start';

                            foreach($qids as $qid)
                            {
                                //forget
                                if($action == 'forget' && !$force)
                                    $GLOBALS['QuestsControl']->tryForget($qid);
                                else if($action == 'forget' && $force && isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                    $GLOBALS['QuestsControl']->QuestsData->forgetQuest($qid, true);

                                //learn
                                else if($action == 'learn' && !$force && !isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                    $GLOBALS['QuestsControl']->learnQuest($qid,0);
                                else if($action == 'learn' && $force && !isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                    $GLOBALS['QuestsControl']->QuestsData->learnQuest($qid, 0);

                                //start
                                else if($action == 'start' && !$force)
                                {
                                    if(!isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                    {
                                        $GLOBALS['QuestsControl']->learnQuest($qid,0);
                                    }

                                    $GLOBALS['QuestsControl']->tryStart($qid);
                                }
                                else if($action == 'start' && $force)
                                {
                                    if(!isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                                        $GLOBALS['QuestsControl']->QuestsData->learnQuest($qid, 0);

                                    $GLOBALS['QuestsControl']->QuestsData->startQuest($qid);
                                }

                                //quit
                                else if($action == 'quit' && !$force)
                                    $GLOBALS['QuestsControl']->tryQuit($qid);
                                else if($action == 'quit' && !$force)
                                    $GLOBALS['QuestsControl']->QuestsData->quitQuest($qid, true);
                            }
                        }
                    }

                    else if( isset($action_data[0]) && is_array($action_data[0]))//if qid is not set, but there is an array
                    {
                        foreach($action_data as $action)//for each sub quest action
                        {
                            if(!isset($action['chance']) || random_int(1,100) < $action['chance'])//if there is no chance or the chance roll is good
                            {
                                if(is_array($action['qid']))//if qid is an array
                                    $qid = $action['qid'][random_int(0,count($action['qid'])-1)];

                                else//if qid is not an array
                                    $qid = $action['qid'];

                                $GLOBALS['QuestsControl']->learnQuest($qid, 0);//give the quest
                                $GLOBALS['QuestsControl']->tryStart($qid);//try to start the quest
                            }
                        }
                    }

                    else//throw error for bad action.
                        var_dump('Bad learn quest action on hook: '.$hook['id']);
                    
                }

                else if(substr( $action_type, 0, 4 ) === "item")
                {
                    $items = array();//place to hold all items that should be given to the user.

                    if(isset($action_data['iid']))//if iid is set
                    {
                        if(!isset($action_data['chance']) || random_int(1,100) < $action_data['chance'])//if there is no chance or the chance roll is good
                        {
                            $iid;//place to hold an iid and count
                            $count;

                            if(is_array($action_data['iid']))//if iid is an array
                                $iid = $action_data['iid'][random_int(0,count($action_data['iid'])-1)];//pick an iid from the array

                            else//if iid is not an array
                                $iid = $action_data['iid'];//record the iid



                            if(!isset($action_data['count']))//if there is no count set
                                $count = 1;//assume 1

                            else if(is_array($action_data['count']))//if count is an array
                                $count = $action_data['count'][random_int(0,count($action_data['count'])-1)];//pick an iid from the array and record it.
                                
                            else//if count is set and count is not an array
                                $count = $action_data['count'];//record the count



                            if(!isset($items[$iid]))//if this iid had not been added yet
                                $items[$iid] = $count;//record it and its count

                            else//if this iid has been added
                                $items[$iid] += $count;//add the count of this to the current item.
                        }
                    }

                    else if( isset($action_data[0]) && is_array($action_data[0]))//if iid is not set, but there is something set at index 0 and it is an array
                    {
                        foreach($action_data as $action)//for each index found
                        {
                            if(!isset($action['chance']) || random_int(1,100) < $action['chance'])//if there is no chance or the chance roll is good.
                            {
                                $iid;//place to hold an iid and count
                                $count;

                                if(is_array($action['iid']))//if iid is an array
                                    $iid = $action['iid'][random_int(0,count($action['iid'])-1)];//pick an iid from the array and record it.

                                else//if iid is not an array
                                    $iid = $action['iid'];//record it

                                
                                if(!isset($action['count']))//if count is not set
                                    $count = 1;//assume it should be 1 and record it

                                else if(is_array($action['count']))//if count is an array
                                    $count = $action['count'][random_int(0,count($action['count'])-1)];//pick a count from the array and record it.

                                else//if count is set and it is not an array
                                    $count = $action['count'];//record it

                                if(!isset($items[$iid]))//if this iid has not been set in the items array
                                    $items[$iid] = $count;//set it and record its count

                                else//if this iid has been set in the items array
                                    $items[$iid] += $count;//add this count to the existing count
                            }
                        }
                    }

                    if(count($items) > 0)
                    {
                        $item_basic_functions = new itemBasicFunctions();//getting item functions up and running


                        foreach($items as $iid => $count)
                            if($count > 0)
                                $item_basic_functions->addItemToUser( $_SESSION['uid'], $iid, $count );

                            else if($count < 0)
                                $item_basic_functions->reduceNumberOfItems( $_SESSION['uid'], $iid, $count );


                        $item_names = $GLOBALS['database']->fetch_data("SELECT `id`, `name` FROM `items` WHERE `id` IN (".implode(",",array_keys($items)).")");

                        $message = "You have <br>";
                        foreach($item_names as $result)
                        {
                            if($items[$result['id']] > 0)
                                $message .= "gained ";
                            else if($items[$result['id']] < 0)
                                $message .= "lost ";

                            $message .= $items[$result['id']]."<br>\"".$result['name']."\",<br><br>";
                        }

                        $message = rtrim($message,",<br><br>").".";

                        $GLOBALS['NOTIFICATIONS']->addNotification( array(
                                                                        'id' => 23,
                                                                        'duration' => time() + 60 * 15,
                                                                        'text' => $message,
                                                                        'dismiss' => 'yes'
                                                                    ));
                    }
                }

                else if(substr( $action_type, 0, 6 ) === "combat" && (!$combat_addition || !isset($this->battle)) && $GLOBALS['userdata'][0]['status'] != 'combat' )
                {
                    if(isset($action_data['type']))
                    {
                        $users = array( array( 'id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village'] ) );

                        $ais = array();

                        //type
                        $combat_type = $action_data['type'];

                        //chance
                        if(isset($action_data['chance']))
                            $combat_chance = $action_data['chance'];
                        else
                            $combat_chance = 100;


                        $rand_for_description_and_link = 0;
                        $rand_size = 0;

                        //description
                        if(isset($action_data['description']))
                        {
                            if(is_array($action_data['description']))
                            {
                                $rand_size = count($action_data['description'])-1;
                                $rand_for_description_and_link = random_int(0,$rand_size);
                                $combat_description = $action_data['description'][$rand_for_description_and_link];
                            }
                            else
                                $combat_description = $action_data['description'];
                        }
                        else
                            $combat_description = "You have been attacked.";

                        //link
                        if(isset($action_data['link']))
                        {
                            if(is_array($action_data['link']))
                                if($rand_size == count($action_data['link'])-1)
                                    $combat_link = $action_data['link'][$rand_for_description_and_link];
                                else
                                    $combat_link = $action_data['link'][random_int(0,count($action_data['link'])-1)];
                            else
                                $combat_link = $action_data['link'];
                        }
                        else
                            $combat_link = "Fight.";

                        //allies
                        if(isset($action_data['allies']))
                        {
                            if(isset($action_data['allies']['id']))
                            {
                                $action_data['allies'] = array(0=>$action_data['allies']);
                            }

                            foreach($action_data['allies'] as $ally)
                            {
                                if(isset($ally['id']))
                                {
                                    //ally count
                                    if(isset($ally['count']))
                                    {
                                        if(is_array($ally['count']))
                                            $ally_count = $ally['count'][random_int(0,count($ally['count'])-1)];
                                        else
                                            $ally_count = $ally['count'];
                                    }
                                    else if(isset($ally['count_range']) && count($ally['count_range'] == 2))
                                        $ally_count = random_int($ally['count_range'][0],$ally['count_range'][1]);
                                    else
                                        $ally_count = 1;

                                    if(is_array($ally['id']) && ( !isset($ally['mixed']) || $ally['mixed'] != 'yes' ) )
                                        $ally_id = $ally['id'][random_int(0,count($ally['id'])-1)];
                                    else
                                        $ally_id = $ally['id'];


                                    if($ally_count != 0)
                                    {
                                        for($i = 0; $i < $ally_count; $i++)
                                        {
                                            if(is_array($ally_id))
                                                $ais[] = array('id'=>$ally_id[random_int(0,count($ally_id)-1)], 'team'=>$GLOBALS['userdata'][0]['village']);
                                            else
                                                $ais[] = array('id'=>$ally_id, 'team'=>$GLOBALS['userdata'][0]['village']);
                                        }
                                    }
                                }
                            }
                        }

                        //enemy
                        if(isset($action_data['enemies']))
                        {
                            if(isset($action_data['enemies']['id']))
                            {
                                $action_data['enemies'] = array(0=>$action_data['enemies']);
                            }

                            foreach($action_data['enemies'] as $enemy)
                            {
                                if(isset($enemy['id']))
                                {
                                    //enemy count
                                    if(isset($enemy['count']))
                                    {
                                        if(is_array($enemy['count']))
                                            $enemy_count = $enemy['count'][random_int(0,count($enemy['count'])-1)];
                                        else
                                            $enemy_count = $enemy['count'];
                                    }
                                    else if(isset($enemy['count_range']) && count($enemy['count_range'] == 2))
                                        $enemy_count = random_int($enemy['count_range'][0],$enemy['count_range'][1]);
                                    else
                                        $enemy_count = 1;
                                
                                    
                                    if(isset($enemy['team']) && $enemy['team'] != 'default')
                                    {
                                        if(is_array($enemy['team']))
                                            $enemy_team = $enemy['team'][random_int(0,count($enemy['team'])-1)];
                                        else
                                            $enemy_team = $enemy['team'];
                                    }
                                    else
                                        $enemy_team = false;

                                    if(is_array($enemy['id']) && (!isset($enemy['mixed']) || $enemy['mixed'] != 'yes'))
                                        $enemy_id = $enemy['id'][random_int(0,count($enemy['id'])-1)];
                                    else
                                        $enemy_id = $enemy['id'];
                                
                                
                                    if($enemy_count != 0)
                                    {
                                        for($i = 0; $i < $enemy_count; $i++)
                                        {
                                            if(is_array($enemy_id))
                                                $ais[] = array('id'=>$enemy_id[random_int(0,count($enemy_id)-1)], 'team'=>$enemy_team);
                                            else
                                                $ais[] = array('id'=>$enemy_id, 'team'=>$enemy_team);
                                        }
                                    }
                                }
                            }
                        }

                        if($combat_type == 'travel')
                        {
                            //getting users base repel
                            $repel = 100 - $GLOBALS['userdata'][0]['repel_effect'];

                            //getting users item repel
                            if( $GLOBALS['userdata'][0]['repel_endtime'] > $GLOBALS['user']->load_time )
                                $repel -= $GLOBALS['userdata'][0]['repel_chance'];

                            //getting users loyalty repel
                            if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
                                if( $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 225 || $GLOBALS['userdata'][0]['vil_loyal_pts'] <= -220 ){
                                    $repel -= 15;
                                }
                                if( $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 275 ){
                                    $repel -= 15;
                                }
                            }

                            //$testing = 'repel multiplier: '.$repel.'<br>hook chance: '.$combat_chance.'<br>';
                            $combat_chance = (($combat_chance/100) * ($repel/100))*100;

                            // Check for global event modifications
                            if( $event = functions::getGlobalEvent("ModifyTravelAiChance")){
                                if( isset( $event['data']) && is_numeric( $event['data']) ){
                                    $combat_chance *= round($event['data'] / 100,2);
                                }
                            }
                            
                            //$rand = random_int(100,10000)/100;
                            //$testing .= 'combined chance: '.$combat_chance.'<br>roll: '.$rand.'<br>';
                            //$GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => $testing) );
                        }

                        //if($rand < $combat_chance && count($ais) > 0)
                        if( (random_int(100,10000)/100) < $combat_chance && count($ais) > 0)
                        {
                            try
                            {
                                $this->battle = BattleStarter::startBattle( $users, $ais, constant("BattleStarter::".$combat_type), false, false, true);
                            }
                            catch (exception $e)
                            {
                                error_log('user already in combat error? look into this more?');
                                error_log(print_r($this,true));
                            }

                            $this->combat_started = true;
                            $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => $combat_description, 'buttons' => array('?id=113',$combat_link), 'popup' => 'yes') );
                            //$GLOBALS['page']->Message($combat_description, ucfirst($combat_type), 'id=113', $combat_link);
                        }
                    }
                }
                else if(substr( $action_type, 0, 6 ) === "combat" && $combat_addition && (!isset($action_data['chance']) || random_int(1,100) < $action_data['chance']))
                {
                    $ais = array();

                    //allies
                    if(isset($action_data['allies']))
                    {
                        if(isset($action_data['allies']['id']))
                        {
                            $action_data['allies'] = array(0=>$action_data['allies']);
                        }

                        foreach($action_data['allies'] as $ally)
                        {
                            if(isset($ally['id']))
                            {
                                //ally count
                                if(isset($ally['count']))
                                {
                                    if(is_array($ally['count']))
                                        $ally_count = $ally['count'][random_int(0,count($ally['count'])-1)];
                                    else
                                        $ally_count = $ally['count'];
                                }
                                else if(isset($ally['count_range']) && count($ally['count_range'] == 2))
                                    $ally_count = random_int($ally['count_range'][0],$ally['count_range'][1]);
                                else
                                    $ally_count = 1;

                                if(is_array($ally['id']) && ( !isset($ally['mixed']) || $ally['mixed'] != 'yes' ) )
                                    $ally_id = $ally['id'][random_int(0,count($ally['id'])-1)];
                                else
                                    $ally_id = $ally['id'];


                                if($ally_count != 0)
                                {
                                    for($i = 0; $i < $ally_count; $i++)
                                    {
                                        if(is_array($ally_id))
                                            $ais[] = array('id'=>$ally_id[random_int(0,count($ally_id)-1)], 'team'=>$GLOBALS['userdata'][0]['village']);
                                        else
                                            $ais[] = array('id'=>$ally_id, 'team'=>$GLOBALS['userdata'][0]['village']);
                                    }
                                }
                            }
                        }
                    }

                    //enemy
                    if(isset($action_data['enemies']))
                    {
                        if(isset($action_data['enemies']['id']))
                        {
                            $action_data['enemies'] = array(0=>$action_data['enemies']);
                        }

                        foreach($action_data['enemies'] as $enemy)
                        {
                            if(isset($enemy['id']))
                            {
                                //enemy count
                                if(isset($enemy['count']))
                                {
                                    if(is_array($enemy['count']))
                                        $enemy_count = $enemy['count'][random_int(0,count($enemy['count'])-1)];
                                    else
                                        $enemy_count = $enemy['count'];
                                }
                                else if(isset($enemy['count_range']) && count($enemy['count_range'] == 2))
                                    $enemy_count = random_int($enemy['count_range'][0],$enemy['count_range'][1]);
                                else
                                    $enemy_count = 1;
                            
                                
                                if(isset($enemy['team']) && $enemy['team'] != 'default')
                                {
                                    if(is_array($enemy['team']))
                                        $enemy_team = $enemy['team'][random_int(0,count($enemy['team'])-1)];
                                    else
                                        $enemy_team = $enemy['team'];
                                }
                                else
                                    $enemy_team = false;

                                if(is_array($enemy['id']) && (!isset($enemy['mixed']) || $enemy['mixed'] != 'yes'))
                                    $enemy_id = $enemy['id'][random_int(0,count($enemy['id'])-1)];
                                else
                                    $enemy_id = $enemy['id'];
                            
                            
                                if($enemy_count != 0)
                                {
                                    for($i = 0; $i < $enemy_count; $i++)
                                    {
                                        if(is_array($enemy_id))
                                            $ais[] = array('id'=>$enemy_id[random_int(0,count($enemy_id)-1)], 'team'=>$enemy_team);
                                        else
                                            $ais[] = array('id'=>$enemy_id, 'team'=>$enemy_team);
                                    }
                                }
                            }
                        }
                    }

                    
                    //if there are ai
                    if(count($ais) > 0)
                    {
                        //add ai
                        foreach($ais as $ai)
                            $this->battle->addAI($ai['id'], $ai['team']);

                        //recording the changes made to the cache
                        $this->battle->updateCache();
                    }
                }
                else if(substr( $action_type, 0, 6 ) !== "combat" || !$combat_addition)
                {
                    //throw new exception('un-known action type: '.$action_data[0]);
                    error_log(print_r(array(
                        'action_type'=>$action_type,
                        'action_data'=>$action_data
                    ),true));
                }

            }
        }
    }
}