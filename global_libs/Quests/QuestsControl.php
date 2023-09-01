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
 *Class: QuestsControl
 *  this class handles everything quest related except for the get and set of the data.
 *  
 */

require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsData.php');

//helper functions
function startsWith($haystack, $needle) {
    return $haystack[0] === $needle[0] ? strncmp($haystack, $needle, strlen($needle)) === 0 : false;
}

function userData($target){
    return $GLOBALS['userdata'][0][$target];
}
//helper functions

class QuestsControl
{ //dont for get to add locking for this stuffs

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

        $this->QuestsData = new QuestsData($this->uid);
    }

    function tryEval($command)
    {
        try
        {   
            //echo'<br><br>TRY EVAL:';var_dump(array('$command'=>str_replace(" = "," == ",$command),'return'=>eval(str_replace(" = "," == ",$command))));echo'<br><br>';
            //error_log(print_r(array('command'=>str_replace(" = "," == ",$command),'return'=>eval(str_replace(" = "," == ",$command))),true));
            return eval(str_replace(" = "," == ",$command));
        }
        catch (exception $e)
        {
            var_dump($e);
            var_dump($command);
            error_log($e);
            error_log($command);
            return false;
        }
    }

    // ///////////////////////// message starting methods \\\\\\\\\\\\\\\\\\\\\\\\\

    //learn quest
    function learnQuest($qid, $starting_status)
    {
        $this->QuestsData->learnQuest($qid, $starting_status);
        $this->showMessage($qid, 'learn');
    }

    //display failure message
    function showFailure($qid, $try_again = false)
    {
        if($try_again === true && !$this->QuestsData->quests[$qid]->failed)
            $this->showMessage($qid, 'fail_try_again');
        else
            $this->showMessage($qid, 'fail');
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\ message starting methods /////////////////////////



    // /////////////////////////      "start" methods     \\\\\\\\\\\\\\\\\\\\\\\\\

    //trys to start a quest
    function tryStart($qid)
    {
        if(isset($this->QuestsData->quests[$qid]) && !$this->QuestsData->quests[$qid]->failed)
        {
            if($this->QuestsData->quests[$qid]->dialog_start == '')
            {
                $this->startQuest($qid);

                if($this->QuestsData->quests[$qid]->status == 1)
                {
                    $this->checkRequirements($this->QuestsData->quests[$qid]->completion_requirements, $qid, 'completion_requirements');
                    $this->checkRequirements($this->QuestsData->quests[$qid]->failure_requirements, $qid, 'failure_requirements');
                }

                return true;
            }
            else
            {
                $this->QuestsData->startDialog($qid,'dialog_start');

                return false;
            }
        }
        else if(isset($this->QuestsData->quests[$qid]) && !$this->QuestsData->quests[$qid]->hard_fail)
        {
            if($this->QuestsData->quests[$qid]->dialog_start_post_failure == '' && $this->QuestsData->quests[$qid]->dialog_start == '')
            {
                $this->startQuest($qid);

                if($this->QuestsData->quests[$qid]->failed && is_array($this->QuestsData->quests[$qid]->completion_requirements_post_failure))
                {
                    if($this->QuestsData->quests[$qid]->status == 1)
                        $this->checkRequirements($this->QuestsData->quests[$qid]->completion_requirements_post_failure, $qid, 'completion_requirements_post_failure');
                }
                else
                {
                    if($this->QuestsData->quests[$qid]->status == 1)
                        $this->checkRequirements($this->QuestsData->quests[$qid]->completion_requirements, $qid, 'completion_requirements');
                }

                if($this->QuestsData->quests[$qid]->failed && is_array($this->QuestsData->quests[$qid]->completion_requirements_post_failure))
                {
                    if($this->QuestsData->quests[$qid]->status == 1)
                        $this->checkRequirements($this->QuestsData->quests[$qid]->failure_requirements_post_failure, $qid, 'failure_requirements_post_failure');
                }
                else
                {
                    if($this->QuestsData->quests[$qid]->status == 1)
                        $this->checkRequirements($this->QuestsData->quests[$qid]->failure_requirements, $qid, 'failure_requirements');
                }

                return true;
            }
            else
            {
                $this->QuestsData->startDialog($qid,'dialog_start');

                return false;
            }
        }
    }

    //start quest
    function startQuest($qid, $check_status = true)
    {
        //check starting requirements
        if($this->canStart($qid, $check_status))
        {
            //if all is good make the quest active
            $this->QuestsData->startQuest($qid);
        }
    }

    //checks to see if you can start this quest
    function canStart($argument, $check_status = false)
    {
        $second = 1;
        $minute = 60;
        $hour = $minute * 60;
        $day = $hour * 24;
        $week = $day * 7;

        if(is_numeric($argument))
            $quest = $this->QuestsData->quests[$argument];
        else
            $quest = $argument;

        if($quest->time_gap_requirement != '' && strpos(rtrim($quest->time_gap_requirement, ';'), ';') === false && strpos($quest->time_gap_requirement, 'eval') === false)
        {
            $stamp = str_replace('\'','\\\'',$quest->timestamp_turned_in - 18000);
            $time_gap_requirement = str_replace('time()', ' ( time() - 18000 ) ',str_replace('"value"', $stamp, str_replace('\'value\'', $stamp, $quest->time_gap_requirement)));
        }
        else 
            $time_gap_requirement = false;

        //check active count
        if  ($this->QuestsData->active < 10 && 
                (
                    (
                        !$quest->failed &&
                            (
                                (
                                    is_array($quest->starting_requirements) && 
                                    $this->checkRequirements($quest->starting_requirements, $quest->qid, 'starting_requirements') === true
                                )
                                ||
                                $quest->starting_requirements == ''
                            )
                    )
                    ||
                    (
                        $quest->failed &&
                            (
                                (
                                    is_array($quest->starting_requirements_post_failure) && 
                                    $this->checkRequirements($quest->starting_requirements_post_failure, $quest->qid, 'starting_requirements_post_failure') === true
                                )
                                ||
                                $quest->starting_requirements_post_failure == ''
                            )
                    )
                )
                &&
                    ($time_gap_requirement === false || $this->tryEval("return ".$time_gap_requirement.";") )
            )
        {
            if($check_status && ($GLOBALS['userdata'][0]['status'] != 'awake' && $GLOBALS['userdata'][0]['status'] != 'questing'))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
        return false;
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\      "start" methods     /////////////////////////


    // /////////////////////////       "quit" methods     \\\\\\\\\\\\\\\\\\\\\\\\\

    //trys to start a quest
    function tryQuit($qid)
    {
        if(isset($this->QuestsData->quests[$qid]) && !$this->QuestsData->quests[$qid]->failed)
        {
            if($this->QuestsData->quests[$qid]->dialog_quit == '')
            {
                $this->quitQuest($qid);
                return true;
            }
            else
            {

                $this->QuestsData->startDialog($qid,'dialog_quit');

                return false;
            }
        }
        else
        {
            if($this->QuestsData->quests[$qid]->dialog_quit_post_failure == '' && $this->QuestsData->quests[$qid]->dialog_quit == '')
            {
                $this->quitQuest($qid);
                return true;
            }
            else
            {

                $this->QuestsData->startDialog($qid,'dialog_quit');

                return false;
            }
        }
    }

    //quit quest
    function quitQuest($qid)
    {
        //if all is good make the quest active
        if(isset($this->QuestsData->quests[$qid]) && $this->QuestsData->quests[$qid]->status == QuestContainer::$active)
        {
            $this->QuestsData->quitQuest($qid);
        }
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\       "quit" methods      /////////////////////////


    // /////////////////////////     "forget" methods     \\\\\\\\\\\\\\\\\\\\\\\\\

    //trys to start a quest
    function tryForget($qid)
    {
        if(isset($this->QuestsData->quests[$qid]) && !$this->QuestsData->quests[$qid]->failed)
        {
            if($this->QuestsData->quests[$qid]->dialog_forget == '')
            {
                $this->forgetQuest($qid);
                return true;
            }
            else
            {

                $this->QuestsData->startDialog($qid,'dialog_forget');

                return false;
            }
        }
        else
        {
            if($this->QuestsData->quests[$qid]->dialog_forget_post_failure == '' && $this->QuestsData->quests[$qid]->dialog_forget == '')
            {
                $this->forgetQuest($qid);
                return true;
            }
            else
            {

                $this->QuestsData->startDialog($qid,'dialog_forget');

                return false;
            }
        }
    }

    //quit quest
    function forgetQuest($qid)
    {
        //if all is good make the quest active
        if(isset($this->QuestsData->quests[$qid]) && ($this->QuestsData->quests[$qid]->status == QuestContainer::$known || $this->QuestsData->quests[$qid]->status == QuestContainer::$completed))
        {
            $this->QuestsData->forgetQuest($qid);
        }
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\     "forget" methods     /////////////////////////



    // /////////////////////////    "complete" methods    \\\\\\\\\\\\\\\\\\\\\\\\\

    //complete quest
    function completeQuest($qid)
    {
        $this->QuestsData->completeQuest($qid);
        $this->showMessage($qid, 'complete');
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\    "complete" methods    /////////////////////////



    // /////////////////////////    "turn_in" methods    \\\\\\\\\\\\\\\\\\\\\\\\\

    //checks to see if you can start this quest
    function canTurnIn($qid, $check_status = false)
    {
        $quest = $this->QuestsData->quests[$qid];
        if($quest->status == QuestContainer::$completed && 
            (
                (
                    !$quest->failed && 
                    (
                        (
                            is_array($quest->turn_in_requirements) && 
                            $this->checkRequirements($quest->turn_in_requirements, $quest->qid, 'turn_in_requirements') === true
                        )

                        || 

                        $quest->turn_in_requirements == ''
                    )
                )

                ||

                $quest->failed && 
                (
                    (
                        is_array($quest->turn_in_requirements_post_failure) && 
                        $this->checkRequirements($quest->turn_in_requirements_post_failure, $quest->qid, 'turn_in_requirements_post_failure') === true
                    )

                    || 

                    $quest->turn_in_requirements_post_failure == ''
                )
            )
        )
        {
            if($check_status && ($GLOBALS['userdata'][0]['status'] != 'awake' && $GLOBALS['userdata'][0]['status'] != 'questing'))
                return false;
            else
                return true;
        }
        else
            return false;
        return false;
    }


    // need a "tryTurnIn"
    //trys to start a quest
    function tryTurnIn($qid)
    {
        if(isset($this->QuestsData->quests[$qid]) && !$this->QuestsData->quests[$qid]->failed)
        {
            if($this->QuestsData->quests[$qid]->dialog_turn_in == '')
            {
                $this->turnInQuest($qid);
                return true;
            }
            else
            {
                $this->QuestsData->startDialog($qid,'dialog_turn_in');
                return false;
            }
        }
        else
        {
            if($this->QuestsData->quests[$qid]->dialog_turn_in_post_failure == '' && $this->QuestsData->quests[$qid]->dialog_turn_in == '')
            {
                $this->turnInQuest($qid);
                return true;
            }
            else
            {
                $this->QuestsData->startDialog($qid,'dialog_turn_in');
                return false;
            }
        }
    }

    // need a "turnInQuest" (here and in QuestsData)
    function turnInQuest($qid, $check_status = true)
    {
        //if all is good make the quest active
        if(isset($this->QuestsData->quests[$qid]) && $this->QuestsData->quests[$qid]->status == QuestContainer::$completed && $this->canTurnIn($qid, $check_status))
        {
            $this->QuestsData->turnInQuest($qid);
        }
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\    "turn_int" methods    /////////////////////////



    // /////////////////////////     "dialog" methods     \\\\\\\\\\\\\\\\\\\\\\\\\
    
    function showDialog($temp_info = false)
    {
        //checking for data passed to show dialog
        //this is for passing data on first load of dialog that will be stored
        //in the database then user data next time around
        if(!$temp_info)
        {
            $temp_info = explode('|',$GLOBALS['userdata'][0]['dialog']);
            $temp_info = explode(',',$temp_info[0]);

            $dialog_info = array();
            foreach($temp_info as $info)
            {
                $temp_info = explode(':',$info);
                $dialog_info[$temp_info[0]] = $temp_info[1];
            }
        }
        else
        {
            $dialog_info = $temp_info;
        }

        $starting_dialog_info = $dialog_info;

        //checking to see that the user still has this quest, if not purge dialog in userdata and return
        if(!isset($GLOBALS['QuestsControl']->QuestsData->quests[$dialog_info['qid']]))
        {
            $query = "UPDATE `users` SET `status` = 'awake', `dialog` = '' WHERE `id` = ".$_SESSION['uid'];
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

            return true;
        }

        //getting quest and dialog data
        $quest = $GLOBALS['QuestsControl']->QuestsData->quests[$dialog_info['qid']];
        $dialog = $quest->{$dialog_info['dialog']};
        $show_main_screen = false;
        $force_dialog_view = false;
        $force_dialog_redirect = false;

        //processing user action
        if(isset($_GET['dialog_option']))
        {
            $current_message = $dialog[$dialog_info['message']];
            
            //validating selection
            $valid_request = false;
            $next = '';
            $actions = '';
            foreach($current_message['options'] as $option_key => $option)
            {
                if($option_key == $_GET['dialog_option'])
                {
                    $valid_request = true;
                    if(isset($option['next']))
                        $next = $option['next'];
                    if(isset($option['action']))
                        $actions = $option['action'];
                }
            }

            //responding to request
            if($valid_request)
            {
                $dialog_option = $_GET['dialog_option'];

                
                //if request results in an action being taken
                if($actions != '')
                {
                    if(!is_array($actions))
                        $actions = [$actions => 'this'];
                        
                    $release = false;

                    foreach($actions as $action => $target)
                    {
                        $action = explode('~',$action)[0];

                        if($target == 'this')
                            $target = $dialog_info['qid'];


                        if( $action == 'learn')
                        {
                            $this->learnQuest($target, 0);
                        }

                        if( $action == 'track')
                        {
                            $GLOBALS['QuestsControl']->QuestsData->trackQuest($target);
                        }

                        else if( $action == 'fail' )
                        {
                            $this->QuestsData->quitQuest($target);

                            if($release !== null)
                                $release = true;
                        }

                        else if($action == 'complete' )
                        {
                            $this->completeQuest($target);
                            if($release !== null)
                                $release = true;
                        }



                        if($action == 'forget')
                        {
                            if($target == $dialog_info['qid'])
                            {
                                $this->forgetQuest($target);
                                if($release !== null)
                                    $release = true;
                            }
                            else
                            {
                                unset($_GET['dialog_option']);

                                //if there is a new dialog
                                if(!$this->tryForget($target, $this->QuestsData->quests[$target]))
                                {
                                    if(!$this->QuestsData->quests[$target]->failed)
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_forget','message'=>'start');
                                    else
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_forget_post_failure','message'=>'start');

                                    $force_dialog_view = true;


                                    if($release !== null)
                                        $release = null;
                                    else
                                        throw new exception('A dialog must only proc 1 new dialog, not multiple! dialog_info:'.print_r($dialog_info,true).' actions: '.print_r($actions, true));

                                    $GLOBALS['userdata'][0]['status'] = $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
                                    $query = "UPDATE `users` SET `status` = '{$status}', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];
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
                                }

                                else //if there is not a new dialog
                                {
                                    if($release !== null)
                                        $release = true;
                                }
                            }
                        }



                        if($action == 'quit')
                        {
                            if($target == $dialog_info['qid'])
                            {
                                $this->quitQuest($target);
                                if($release !== null)
                                    $release = true;
                            }
                            else
                            {
                                unset($_GET['dialog_option']);

                                //if there is a new dialog
                                if(!$this->tryQuit($target, $this->QuestsData->quests[$target]))
                                {
                                     if(!$this->QuestsData->quests[$target]->failed)
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_quit','message'=>'start');
                                    else
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_quit_post_failure','message'=>'start');

                                    $force_dialog_view = true;
                                    
                                    if($release !== null)
                                        $release = null;
                                    else
                                        throw new exception('A dialog must only proc 1 new dialog, not multiple! dialog_info:'.print_r($dialog_info,true).' actions: '.print_r($actions, true));

                                    $GLOBALS['userdata'][0]['status'] = $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
                                    $query = "UPDATE `users` SET `status` = '{$status}', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];
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
                                }

                                else //if there is not a new dialog
                                {
                                    if($release !== null)
                                        $release = true;
                                }
                            }
                        }



                        if($action == 'start')
                        {
                            if($target == $dialog_info['qid'])
                            {
                                $this->startQuest($target, false);
                                if($release !== null)
                                    $release = true;
                            }
                            else if (isset($this->QuestsData->quests[$target]))
                            {
                                unset($_GET['dialog_option']);

                                //if there is a new dialog
                                if(!$this->tryStart($target, $this->QuestsData->quests[$target]))
                                {
                                    if(! $this->QuestsData->quests[$target]->failed)
                                    {
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_start','message'=>'start');
                                    }

                                    else if(!$this->QuestsData->quests[$target]->hard_fail)
                                    {
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_start_post_failure','message'=>'start');
                                    }

                                    $GLOBALS['userdata'][0]['status'] = $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
                                    $query = "UPDATE `users` SET `status` = '{$status}', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];
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

                                    $force_dialog_view = true;
                                    
                                    if($release !== null)
                                        $release = null;
                                    else
                                        throw new exception('A dialog must only proc 1 new dialog, not multiple! dialog_info:'.print_r($dialog_info,true).' actions: '.print_r($actions, true));
                                }

                                else //if there is not a new dialog
                                {
                                    if($release !== null)
                                        $release = true;
                                }

                            }

                        }



                        if($action == 'turn_in')
                        {

                            if($target == $dialog_info['qid'])
                            {

                                $this->turnInQuest($target, false);
                                if($release !== null)
                                    $release = true;
                            }
                            else if (isset($this->QuestsData->quests[$target]))
                            {
                                unset($_GET['dialog_option']);

                                //if there is a new dialog
                                if(!$this->tryTurnIn($target, $this->QuestsData->quests[$target]))
                                {
                                    if(! $this->QuestsData->quests[$target]->failed)
                                    {
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_turn_in','message'=>'start');
                                    }

                                    else if(!$this->QuestsData->quests[$target]->hard_fail)
                                    {
                                        $force_dialog_redirect = array('qid'=>$target,'dialog'=>'dialog_turn_in_post_failure','message'=>'start');
                                    }

                                    $GLOBALS['userdata'][0]['status'] = $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
                                    $query = "UPDATE `users` SET `status` = '{$status}', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];
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

                                    $force_dialog_view = true;
                                    
                                    if($release !== null)
                                        $release = null;
                                    else
                                        throw new exception('A dialog must only proc 1 new dialog, not multiple! dialog_info:'.print_r($dialog_info,true).' actions: '.print_r($actions, true));
                                }

                                else //if there is not a new dialog
                                {
                                    if($release !== null)
                                        $release = true;
                                }

                            }

                        }



                        if($action == 'cancel')
                        {
                            if($release !== null)
                                $release = true;
                        }
                    }

                    if($release)
                    {
                        if(substr_count($GLOBALS['userdata'][0]['dialog'],'|') <= 1)
                        {
                            $query = "UPDATE `users` SET `status` = 'awake', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];
                            $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                            $GLOBALS['userdata'][0]['status'] = 'awake';
                            $GLOBALS['userdata'][0]['dialog'] = '';
                            $GLOBALS['template']->assign('userStatus', 'awake');

                            if($next == '')
                                $show_main_screen = true;
                        }
                        else
                        {
                            $GLOBALS['userdata'][0]['status'] = $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
                            $query = "UPDATE `users` SET `status` = '{$status}', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];

                            $temp_info = explode('|',$GLOBALS['userdata'][0]['dialog']);
                            $temp_info = explode(',',$temp_info[1]);

                            $dialog_info = array();
                            foreach($temp_info as $info)
                            {
                                $temp_info = explode(':',$info);
                                $dialog_info[$temp_info[0]] = $temp_info[1];
                            }

                            if(isset($this->QuestsData->quests[$dialog_info['qid']]))
                            {
                                $quest = $this->QuestsData->quests[$dialog_info['qid']];
                                $dialog = $quest->{$dialog_info['dialog']};
                                $current_message = $dialog[$dialog_info['message']];
                            }
                            else
                            {
                                $query = "UPDATE `users` SET `status` = 'awake', `dialog` = SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1) WHERE `id` = ".$_SESSION['uid'];
                                $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                                $GLOBALS['userdata'][0]['status'] = 'awake';
                                $GLOBALS['userdata'][0]['dialog'] = '';
                                $GLOBALS['template']->assign('userStatus', 'awake');

                                if($next == '')
                                    $show_main_screen = true;
                            }
                        }

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
                    }
                }



                //if request results in it moving to the next dialog message
                if($next != '' && isset($dialog[$next]))
                {
                    $GLOBALS['userdata'][0]['status'] = $status = in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep')) ? 'questing' : $GLOBALS['userdata'][0]['status'];
                    $query = "  UPDATE `users` 
                                INNER JOIN `users_quests` ON (`users_quests`.`uid` = `users`.`id`) 
                                SET `users`.`status` = '{$status}', 
                                    `users`.`dialog` = CONCAT( 'qid:".$dialog_info['qid'].",dialog:".$dialog_info['dialog'].",message:".$next."|', SUBSTRING(`dialog`, LOCATE('|',`dialog`) + 1)), 
                                    `users_quests`.`dialog_chain` = CONCAT(SUBSTR(`users_quests`.`dialog_chain`, 1, length(`users_quests`.`dialog_chain`)-1), ',o:".$dialog_option."|', 'd:".$dialog_info['dialog'].",m:".$next."|')

                                WHERE `id` = ".$_SESSION['uid']." 
                                    AND `users_quests`.`qid` = ".$dialog_info['qid'];


                    $this->QuestsData->quests[$starting_dialog_info['qid']]->dialog_chain[key(end($this->QuestsData->quests[$starting_dialog_info['qid']]->dialog_chain))]['o'] = $dialog_option;
                    $this->QuestsData->quests[$dialog_info['qid']]->dialog_chain[] = array('d'=>$dialog_info['dialog'], 'm'=>$next);
                    $this->QuestsData->updateCache();

                    $GLOBALS['Events']->acceptEvent('status', array('new'=>'questing', 'old'=>$GLOBALS['userdata'][0]['status'] ));
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

                    $dialog_info['message'] = $next;
                    $current_message = $dialog[$dialog_info['message']];
                }
                else if($next != '' && !isset($dialog[$next]))
                    throw new exception('Bad dialog message key: '.$next);
                else
                {
                    if(isset($this->QuestsData->quests[$starting_dialog_info['qid']]))
                    {
                        $query = "  UPDATE `users` 
                                    INNER JOIN `users_quests` ON (`users_quests`.`uid` = `users`.`id`) 
                                    SET `users_quests`.`dialog_chain` = CONCAT(SUBSTR(`users_quests`.`dialog_chain`, 1, length(`users_quests`.`dialog_chain`)-1), ',o:".$dialog_option."|')
                                    WHERE `id` = ".$_SESSION['uid']." 
                                        AND `users_quests`.`qid` = ".$starting_dialog_info['qid'];


                        $this->QuestsData->quests[$starting_dialog_info['qid']]->dialog_chain[key(end($this->QuestsData->quests[$starting_dialog_info['qid']]->dialog_chain))]['o'] = $dialog_option;
                        $this->QuestsData->updateCache();

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
                    }
                }
            }
        }
        else
        {
            $current_message = $dialog[$dialog_info['message']];
        }



        if( (!$show_main_screen || $force_dialog_view) && in_array($GLOBALS['userdata'][0]['status'], array('awake','asleep','questing')))
        {

            if($force_dialog_redirect)
            {
                $quest = $GLOBALS['QuestsControl']->QuestsData->quests[$force_dialog_redirect['qid']];
                $dialog = $quest->{$force_dialog_redirect['dialog']};
                $current_message = $dialog[$force_dialog_redirect['message']];
            }

            $GLOBALS['template']->assign('quest', $quest);
            $GLOBALS['template']->assign('dialog', $dialog);
            $GLOBALS['template']->assign('current_message', $current_message);
            $GLOBALS['template']->assign('contentLoad', './templates/content/quests/Dialog.tpl');
            return false;
        }
        else
            return true;
    }

    // \\\\\\\\\\\\\\\\\\\\\\\\\     "dialog" Methods     /////////////////////////

    // /////////////////////////  "requirement" methods   \\\\\\\\\\\\\\\\\\\\\\\\\

    //called to confirm current status of requirements
    function checkRequirements($requirements, $qid, $requirement_key)
    {
        if(is_array($requirements))
        {

            // putting together check list
            $check_list = $requirements;

            foreach($check_list as $check => $data)
                $check_list[$check] = false;

            //these need checked here from "date" data
            $date_information = array(  'unix_time' => time(),
                                        'year' => date('Y'),
                                        'month' => date('n'),
                                        'day_numeric' => date('j'),
                                        'day' => date('l'),
                                        'hour' => date('H'),
                                        'minute' => date('i'),
                                        'second' => date('s')
                                     );

            //can not be checked, if they are flagged in quest data as good, then they are good
            //'bounty_collected'
            //'item_repair'
            //'jutsu_leveled'
            //'jutsu_used'
            //'message_owner'
            //'page'
            //'surgeon_heal'



            ////////////////////////////////////////////////////////////// dont want this anymore
            ////checking current events
            //if(count($GLOBALS['Events']->events) > 0)
            //{
            //
            //}
            //
            ////check if all are true
            //if(!in_array(false,$check_list))
            //    return true;

            ////////////////////////////////////////////////////////////// checking things that are stored in GLOBALS
            //checking globals userdata
            foreach($requirements as $key => $requirement_data)//for each requirement
            {
                $requirement = explode('~',$key)[0];

                //getting requirement_skin if needed
                if(isset(QuestsData::$requirement_skins[$requirement]))
                {
                    $requirement_skin = QuestsData::$requirement_skins[$requirement];
                }
                else if( substr($requirement,0,6) == 'stats_' )
                {
                    $requirement_skin = substr($requirement,6);
                }
                else
                {
                    $requirement_skin = $requirement;
                }

                if(in_array($requirement, Events::$userdata_events))//if this requirement is checkable through globals data...
                {
                    $check_list[$key] = $this->checkRequirement($requirement_skin, $requirement_data, $GLOBALS['userdata'][0]);//...do so
                }
                else if(in_array($requirement, Events::$time_events))
                {
                    $check_list[$key] = $this->checkRequirement($requirement_skin, $requirement_data, $date_information);
                }
                else if(in_array($requirement, Events::$quest_events))
                {
                    
                    $quest_information = array();

                    foreach($this->QuestsData->quests as $quest)
                    {
                        $quest_information['quest_status_'.$quest->qid] = QuestContainer::$statuses[$quest->status];

                        if( ($quest_information['quest_status_'.$quest->qid] == 'active' || $quest_information['quest_status_'.$quest->qid] == 'active') && $quest->failed )
                            $quest_information['quest_status_'.$quest->qid] = 'failed';
                    }

                    $check_list[$key] = $this->checkRequirement($requirement_skin, $requirement_data, $quest_information);
                }
            }

            $or_true = [];
            //recording status in requirement for quest details page.
            //and updating the actual requirement data if this is a completion/failure requirement
            foreach($check_list as $key => $status)
            {
                if(isset($this->QuestsData->quests[$qid]) && (!isset($requirements[$key]['joined']) || !in_array($requirements[$key]['joined'], $or_true)))
                {
                    $this->QuestsData->quests[$qid]->{$requirement_key}[$key]['status'] = $status;

                    $check_list_key = ($requirement_key == 'completion_requirements' || $requirement_key == 'completion_requirements_post_failure') ?
                            'completion_check_list'
                        :
                            'failure_check_list';

                    if( in_array($requirement_key, array('completion_requirements','completion_requirements_post_failure','failure_requirements','failure_requirements_post_failure') ) )
                    {
                        if(
                            !isset($this->QuestsData->quests[$qid]->data) ||
                            !is_array($this->QuestsData->quests[$qid]->data) ||
                            !isset($this->QuestsData->quests[$qid]->data[$check_list_key])||
                            !is_array($this->QuestsData->quests[$qid]->data[$check_list_key])||
                            !isset($this->QuestsData->quests[$qid]->data[$check_list_key][$key])
                        )
                        {
                            var_dump('please report to koala on discord. copy paste plox. <3<br><br>');
                            $temp = array(
                                'uid'=>$_SESSION['uid'],
                                'check_list_key'=>$check_list_key,
                                'key'=>$key,
                                'qid'=>$qid,
                                'quest'=>$this->QuestsData->quests[$qid]
                            );
                            echo'<pre>';
                            var_dump($temp);
                            echo'</pre>';
                            error_log(print_r($temp,true));
                        }
                        else
                            $this->QuestsData->quests[$qid]->data[$check_list_key][$key] = $status;




                        $this->QuestsData->quests[$qid]->update = true;

                        if( $this->QuestsData->quests[$qid]->data[$check_list_key][$key] && isset($requirements[$key]['or']) && $requirements[$key]['or'] && isset($requirements[$key]['joined']) && !in_array($requirements[$key]['joined'], $or_true))
                        {
                            $or_true[] = $requirements[$key]['joined'];
                            
                            foreach($requirements as $temp_key => $temp_requirement_data)
                            {
                                if(isset($temp_requirement_data['joined']) && in_array($temp_requirement_data['joined'], $or_true))
                                {
                                    $this->QuestsData->quests[$qid]->data[$check_list_key][$temp_key] = true;
                                }
                            }
                        }
                    }
                }
            }

            //check if all are true
            if(!in_array(false,$check_list))
                return true;


            ////////////////////////////////////////////////////////////// checking things that are stored on the database only
            //checking from database
            //build query
            //get data from database
            $data = $this->QuestsData->getDatabaseData($check_list, $requirements);

            //check data from database against requirements
            foreach($requirements as $key => $requirement_data)
            {
                $requirement = explode('~',$key)[0];

                if(in_array($requirement, Events::$non_userdata_events))
                {
                    //getting requirement_skin if needed
                    if(isset(QuestsData::$requirement_skins[$requirement]))
                        $requirement_skin = QuestsData::$requirement_skins[$requirement];
                    else
                        $requirement_skin = $requirement;

                    $check_list[$key] = $this->checkRequirement($requirement_skin, $requirement_data, $data[0]);
                }
            }

            $or_true = [];
            //recording status in requirement for quest details page.
            //and updating the actual requirement data if this is a completion/failure requirement
            foreach($check_list as $key => $status)
            {
                if(isset($this->QuestsData->quests[$qid]) && (!isset($requirements[$key]['joined']) || !in_array($requirements[$key]['joined'], $or_true)))
                {
                    $this->QuestsData->quests[$qid]->{$requirement_key}[$key]['status'] = $status;

                    $check_list_key = ($requirement_key == 'completion_requirements' || $requirement_key == 'completion_requirements_post_failure') ?
                            'completion_check_list'
                        :
                            'failure_check_list';

                    if( in_array($requirement_key, array('completion_requirements','completion_requirements_post_failure','failure_requirements','failure_requirements_post_failure') ) )
                    {
                        $this->QuestsData->quests[$qid]->data[$check_list_key][$key] = $status;
                        $this->QuestsData->quests[$qid]->update = true;

                        if( $this->QuestsData->quests[$qid]->data[$check_list_key][$key] && isset($requirements[$key]['or']) && $requirements[$key]['or'] && isset($requirements[$key]['joined']) && !in_array($requirements[$key]['joined'], $or_true))
                        {
                            $or_true[] = $requirements[$key]['joined'];
                            
                            foreach($requirements as $temp_key => $temp_requirement_data)
                            {
                                if(isset($temp_requirement_data['joined']) && in_array($temp_requirement_data['joined'], $or_true))
                                {
                                    $this->QuestsData->quests[$qid]->data[$check_list_key][$temp_key] = true;
                                }
                            }
                        }
                    }
                }
            }

            //check if all are true
            if(!in_array(false,$check_list))
            {
                return true;
            }
        }

        return false;
    }

    function requirementAlert($old, $new, $requirement_data)
    {
        //if this requirement changed
        if($old != $new)
        {
            //getting the message to display
            $message = false;
            if($new && isset($requirement_data['alert']))
                $message = $requirement_data['alert'];

            else if(!$new && isset($requirement_data['failure_alert']))
                $message = $requirement_data['failure_alert'];

            //if a message was found display a notification.
            if($message)
                $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => $message, 'popup' => ($GLOBALS['userdata'][0]['QuestingMode'] == 'alert' ? 'yes' : 'no') ) );
        }
    }

    //checks a requirement that is stored in user data
    function checkRequirement($requirement, $requirement_data, $userdata, $current_status = false)
    {
        if( isset($requirement_data['sticky']) && $requirement_data['sticky'] === true && $current_status)
            return true;

        if(isset($requirement_data['eval'])) ////////////////////////////////////////////////////// add support for context
        {
            $requirement_data['eval'] = str_replace( "'win'", "'won'", $requirement_data['eval']);
            $requirement_data['eval'] = str_replace( "'lose'", "'loss'", $requirement_data['eval']);

            if(!isset($requirement_data['context']))
            {
                $command = "return ".str_replace('\'value\'','\''.str_replace('\'','\\\'',$userdata[$requirement]).'\'',$requirement_data['eval']).";";
            }
            else
            {

                if(!is_array($requirement_data['context']))
                {
                    $context = $requirement_data['context'];
                    $command = "return ".str_replace('\'value_'.$context.'\'','\''.str_replace('\'','\\\'',( isset($userdata[$requirement."_".$context]) ? $userdata[$requirement."_".$context] : "n/a") ).'\'',$requirement_data['eval']).";";
                }
                else
                {
                    $temp_string = $requirement_data['eval'];
                    foreach($requirement_data['context'] as $context)
                    {
                        $temp_string = str_replace('\'value_'.$context.'\'','\''.str_replace('\'','\\\'',( isset($userdata[$requirement."_".$context]) ?  $userdata[$requirement."_".$context] : "n/a")).'\'',$temp_string);
                    }

                    $command = "return ".$temp_string.";";
                }
            }

            if( strpos(rtrim($command, ';'), ';') === false && strpos($command, 'eval') === false)
            {
                $result = $this->tryEval($command);
                $this->requirementAlert($current_status, $result, $requirement_data);
                return $result;
            }
            else
            {
                $this->requirementAlert($current_status, false, $requirement_data);
                return false;
            }
        }
        else if(isset($requirement_data['gain']))
        {
            if(!isset($requirement_data['context']))
            {
                $command = "return ".str_replace('\'value\'','\''.str_replace('\'','\\\'',$userdata[$requirement]).'\'',$requirement_data['gain']).";";
            }
            else
            {

                if(!is_array($requirement_data['context']))
                {
                    $context = $requirement_data['context'];
                    $command = "return ".str_replace('\'value_'.$context.'\'','\''.str_replace('\'','\\\'',( isset($userdata[$requirement."_".$context]) ? $userdata[$requirement."_".$context] : "n/a") ).'\'',$requirement_data['gain']).";";
                }
                else
                {
                    $temp_string = $requirement_data['gain'];
                    foreach($requirement_data['context'] as $context)
                    {
                        $temp_string = str_replace('\'value_'.$context.'\'','\''.str_replace('\'','\\\'',( isset($userdata[$requirement."_".$context]) ? $userdata[$requirement."_".$context] : "n/a") ).'\'',$temp_string);
                    }

                    $command = "return ".$temp_string.";";
                }
            }

            if( strpos(rtrim($command, ';'), ';') === false && strpos($command, 'eval') === false)
            {
                $result = $this->tryEval($command);
                $this->requirementAlert($current_status, $result, $requirement_data);
                return $result;
            }
            else
            {
                $this->requirementAlert($current_status, false, $requirement_data);
                return false;
            }
        }
        else if(isset($requirement_data['loss']))
        {
            if(!isset($requirement_data['context']))
            {
                $command = "return ".str_replace('\'value\'','\''.str_replace('\'','\\\'',$userdata[$requirement]).'\'',$requirement_data['loss']).";";
            }
            else
            {

                if(!is_array($requirement_data['context']))
                {
                    $context = $requirement_data['context'];
                    $command = "return ".str_replace('\'value_'.$context.'\'','\''.str_replace('\'','\\\'',( isset($userdata[$requirement."_".$context]) ? $userdata[$requirement."_".$context] : "n/a") ).'\'',$requirement_data['loss']).";";
                }
                else
                {
                    $temp_string = $requirement_data['loss'];
                    foreach($requirement_data['context'] as $context)
                    {
                        $temp_string = str_replace('\'value_'.$context.'\'','\''.str_replace('\'','\\\'',( isset($userdata[$requirement."_".$context]) ? $userdata[$requirement."_".$context] : "n/a") ).'\'',$temp_string);
                    }

                    $command = "return ".$temp_string.";";
                }
            }

            if( strpos(rtrim($command, ';'), ';') === false && strpos($command, 'eval') === false)
            {
                $result = $this->tryEval($command);
                $this->requirementAlert($current_status, $result, $requirement_data);
                return $result;
            }
            else
            {
                $this->requirementAlert($current_status, false, $requirement_data);
                return false;
            }
        }
        else
            return false;

        return false;
    }


    function recordEvents($events)
    {   

        //if this user has a quest
        if(is_array($this->QuestsData->quests) && count($this->QuestsData->quests) > 0)
        {
            
            $active_quests = array();

            //building an array of the active quests
            foreach($this->QuestsData->quests as $qid => $quest_container)
            {
                if($quest_container->status == QuestContainer::$active)
                {
                    $active_quests[$qid] = $quest_container;
                }
            }

            //if there are active_quests and events
            if (is_array($this->QuestsData->quests) && count($this->QuestsData->quests) > 0 &&
                is_array($active_quests)            && count($active_quests) > 0)
            {
                //foreach active quest
                foreach($active_quests as $qid => $quest_container)
                {

                    $this->recordEventsHelper('completion', $qid, $quest_container, $events);

                    $this->recordEventsHelper('failure', $qid, $quest_container, $events);

                }
            }
        }

    }



    function recordEventsHelper($type, $qid, $quest_container, $events)
    {
        if($type == "completion")
        {
            //getting the requirements for this quest
            if($quest_container->failed && is_array($quest_container->completion_requirements_post_failure))
                $requirements = $quest_container->completion_requirements_post_failure;
            else
                $requirements = $quest_container->completion_requirements;
        }
        else if($type == "failure")
        {
            if($quest_container->failed && is_array($quest_container->failure_requirements_post_failure))
                $requirements = $quest_container->failure_requirements_post_failure;
            else
                $requirements = $quest_container->failure_requirements;
        }
        else
            throw new exception('bad type sent to function recordEventsHelper. Must be completion or failure got: '.$type);

        //if there are requirements
        if(is_array($requirements) && count($requirements) > 0)
        {
            //checking for sticky joins that have been completed and should be ignored.
            $sticky_joins = array();
            $new_requirements_list = array();

            foreach($requirements as $key => $requirement_data) //building 2 new arrays, one for sticky joins, and one for everything else
            {
                if(isset($requirement_data['joined']) && isset($requirement_data['sticky']) && $requirement_data['joined'] === $requirement_data['sticky'])
                {
                    if( !isset($sticky_joins[$requirement_data['joined']]) )
                        $sticky_joins[$requirement_data['joined']] = array();

                    $sticky_joins[$requirement_data['joined']][$key] = $requirement_data;
                }
                else
                    $new_requirements_list[$key] = $requirement_data;
            }

            foreach($sticky_joins as $join_key => $joined_items)//going over all sticky joins found
            {
                $sticky_join_status = true;
                $rollback_status = false;
                foreach ($joined_items as $item_key => $item_value)//checking to see if sticky joins has been fully completed or not
                {
                    if($quest_container->data[$type.'_check_list'][$item_key] === false) //if there is a false member of the join
                    {
                        $sticky_join_status = false; //mark the join as false

                        if(!isset($item_value['gain']) && !isset($item_value['loss'])) //if this requirement is not a gain or loss and is false
                            $rollback_status = true; //request a rollback
                    }
                }

                if(!$sticky_join_status)//if this join was not fully completed
                {
                    foreach ($joined_items as $item_key => $item_value)//add these back to the requirements list
                    {
                        if($rollback_status) //if there is a request for a rollback mark this for rollback
                            $item_value['rollback'] = true;
                        
                        $new_requirements_list[$item_key] = $item_value;
                    }
                }
                //else if(isset(array_values($joined_items)[0]['join_alert']))
                //{
                //    $temp_requirement = array_values($joined_items)[0];
                //    $temp_requirement['alert'] = $temp_requirement['join_alert'];
                //    $this->requirementAlert(false, true, $temp_requirement);
                //}
            }

            //replace the requirements with the list of requirements that has had completed sticky joins removed.
            $requirements = $new_requirements_list;
            
            //foreach $type requirement
            foreach($requirements as $key => $requirement_data)
            {
                $or_true = [];

                $requirement = explode('~',$key)[0];

                //check to see if there is a event for this requirement
                if(isset($events[$requirement]))
                {
                    
                    //go through each piece of the event's data
                    foreach($events[$requirement] as $event)
                    {

                        if(isset($event['context']))
                            $context = '_'.$event['context'];
                        else
                            $context = '';

                        if(isset($requirement_data['eval']))
                        {

                            if(isset($event['new']))
                            {
                                $userdata[$requirement.$context] = $event['new'];
                                $userdata[$requirement] = $event['new'];
                            }
                            else if(isset($event['count']))
                            {
                                $userdata[$requirement.$context] = $event['count'];
                                $userdata[$requirement] = $event['count'];                            }
                            else
                            {
                                $userdata[$requirement.$context] = $event['data'];
                                $userdata[$requirement] = $event['data'];
                            }

                            // $quest_container->data[$type.'_check_list'][$key] is the current status
                            if(!isset($requirement_data['joined']) || !in_array($requirement_data['joined'], $or_true))
                                $quest_container->data[$type.'_check_list'][$key] = $this->checkRequirement($requirement, $requirement_data, $userdata, $quest_container->data[$type.'_check_list'][$key]);

                            if( $quest_container->data[$type.'_check_list'][$key] && isset($requirement_data['or']) && $requirement_data['or'] && isset($requirement_data['joined']) && !in_array($requirement_data['joined'], $or_true))
                            {
                                $or_true[] = $requirement_data['joined'];

                                foreach($requirements as $temp_key => $temp_requirement_data)
                                {
                                    if(isset($temp_requirement_data['joined']) && in_array($temp_requirement_data['joined'], $or_true))
                                    {
                                        $quest_container->data[$type.'_check_list'][$temp_key] = true;
                                    }
                                }
                            }

                            $this->QuestsData->quests[$qid]->update = true;

                            
                            $results = $this->check_for_join_completion_message($quest_container, $requirement_data, $requirements, $type, $key );     
                            $quest_container = $results['quest_container'];
                            $requirements = $results['requirements'];
                        }
                        else
                        {

                            if(isset($requirement_data['gain']))
                                $polarity = '_gains';
                            else
                                $polarity = '_losses';
                            

                            //if a rollback is required and there is rollback data handle it.
                            if(isset($requirement_data['rollback']) && $requirement_data['rollback'] === true && isset($quest_container->data[$type.$polarity][$key]['rollback']))
                            {
                                $requirement_data['rollback'] = false;
                                $quest_container->data[$type.$polarity][$key] = $quest_container->data[$type.$polarity][$key]['rollback'];
                            }

                            //unsetting rollback data before saving new rollback data to prevent bloat.
                            if(isset($quest_container->data[$type.$polarity][$key]['rollback']))
                                unset($quest_container->data[$type.$polarity][$key]['rollback']);

                            //setting rollback
                            $quest_container->data[$type.$polarity][$key]['rollback'] = $quest_container->data[$type.$polarity][$key];

                            if(isset($event['context']) && isset($quest_container->data[$type.$polarity][$key][$event['context']]['last']))
                                $old = $quest_container->data[$type.$polarity][$key][$event['context']]['last'];
                            else if(isset($quest_container->data[$type.$polarity][$key]['last']))
                                $old = $quest_container->data[$type.$polarity][$key]['last'];
                            else
                                $old = 0;

                            
                            
                            

                            if(isset($event['old']) && $event['old'] < $old)
                                $old = $event['old'];

                            if(isset($event['new']) && is_numeric($event['new']))
                                $new = $event['new'];
                            else if(isset($event['count']) && is_numeric($event['count']))
                                $new = $event['count'] + $old;
                            else if(isset($event['data']) && !is_numeric($event['data']))
                            {
                                if( in_array($event['data'], array('loss','failure')) ) //negitive
                                {
                                    $new = $old - 1;
                                }
                                else if( in_array($event['data'], array('fled')) ) //nuetral
                                {
                                    $new = $old;
                                }
                                else //positive
                                {
                                    $new = $old + 1;
                                }
                            }
                            else
                                $new = $old + 1;

                                
                            $change = $new - $old;


                            if(isset($requirement_data['gain']))
                                $polarity = "_gains";
                            else
                                $polarity = "_losses";

                            if(isset($event['context']) && isset($quest_container->data[$type.$polarity][$key]))
                            {
                                if(isset($quest_container->data[$type.$polarity][$key][$event['context']]))
                                {
                                    $quest_container->data[$type.$polarity][$key][$event['context']][ltrim($polarity,'_')] += $change;
                                    
                                    foreach($quest_container->data[$type.$polarity][$key] as $context_key => $context_data)
                                    {
                                        if(isset($context_data[ltrim($polarity,'_')]))
                                        {
                                            $userdata[$requirement.'_'.$context_key] = $context_data[ltrim($polarity,'_')];
                                            $userdata[$requirement] = $context_data[ltrim($polarity,'_')];
                                        }
                                    }
                                    
                                    $quest_container->data[$type.$polarity][$key][$event['context']]['last'] = $new;
                                }
                                else if(isset($quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')]))
                                {
                                    $quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')] += $change;
                                    $userdata[$requirement.$context] = $quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')];
                                    $userdata[$requirement] = $quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')];
                                    $quest_container->data[$type.$polarity][$key]['last'] = $new;
                                }
                            }
                            else if(isset($quest_container->data[$type.$polarity][$key]['last']))
                            {
                                $quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')] += $change;
                                $userdata[$requirement.$context] = $quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')];
                                $userdata[$requirement] = $quest_container->data[$type.$polarity][$key][ltrim($polarity,'_')];
                                $quest_container->data[$type.$polarity][$key]['last'] = $new;
                            }
                            else
                                throw new exception('there is a problem with the quest\'s '.ltrim($polarity,'_').' requirement tracking data');

                            if(isset($userdata))
                            {
                                if(!isset($requirement_data['joined']) || !in_array($requirement_data['joined'], $or_true))
                                    $quest_container->data[$type.'_check_list'][$key] = $this->checkRequirement($requirement, $requirement_data, $userdata, $quest_container->data[$type.'_check_list'][$key]);

                                if( $quest_container->data[$type.'_check_list'][$key] && isset($requirement_data['or']) && $requirement_data['or'] && isset($requirement_data['joined']) && !in_array($requirement_data['joined'], $or_true))
                                {
                                    $or_true[] = $requirement_data['joined'];
    
                                    foreach($requirements as $temp_key => $temp_requirement_data)
                                    {
                                        if(isset($temp_requirement_data['joined']) && in_array($temp_requirement_data['joined'], $or_true))
                                        {
                                            $quest_container->data[$type.'_check_list'][$temp_key] = true;
                                        }
                                    }
                                }

                                $this->QuestsData->quests[$qid]->update = true;

                                $results = $this->check_for_join_completion_message($quest_container, $requirement_data, $requirements, $type, $key );     
                                $quest_container = $results['quest_container'];
                                $requirements = $results['requirements'];
                            }
                        }
                    }
                }
            }
        }
    }

    //recordEventsHelperHelper
    function check_for_join_completion_message($quest_container, $requirement_data, $requirements, $type, $key )
    {
        //if this requirement was true and this requirement is joined and this requirement has a completion message and the completion message has not been sent
        if($quest_container->data[$type.'_check_list'][$key] && isset($requirement_data["joined"]) && isset($requirement_data["join_alert"]) 
        && $requirement_data["joined"] != '' && $requirement_data["join_alert"] != '' && !$requirement_data["join_alert_sent"])
        {
            $join_key = $requirement_data["joined"];
            $send_message = true;
            //for each requirement in this quest
            foreach($requirements as $searching_key => $searching_requirement_data)
            {
                //if this requirement is in the join and is false set send message flag to false and break
                if(isset($searching_requirement_data['joined']) && $searching_requirement_data['joined'] == $join_key && !$quest_container->data[$type.'_check_list'][$searching_key])
                {
                    $send_message = false;
                    break; 
                }
            }

            //if send message flag
            if($send_message)
            {
                //send message
                $temp_requirement = $requirement_data;
                $temp_requirement['alert'] = $temp_requirement['join_alert'];
                $this->requirementAlert(false, true, $temp_requirement);

                //mark this message as being sent
                foreach($requirements as $searching_key => $searching_requirement_data)
                {
                    if(isset($searching_requirement_data['joined']) && $searching_requirement_data['joined'] == $join_key)
                    {
                        $requirements[$searching_key]['join_alert_sent'] = true;

                        if($type == "completion")
                        {
                            if($quest_container->failed && is_array($quest_container->completion_requirements_post_failure))
                                $quest_container->completion_requirements_post_failure[$searching_key]['join_alert_sent'] = true;
                            else
                                $quest_container->completion_requirements[$searching_key]['join_alert_sent'] = true;
                        }
                        else if($type == "failure")
                        {
                            if($quest_container->failed && is_array($quest_container->failure_requirements_post_failure))
                                $quest_container->failure_requirements_post_failure[$searching_key]['join_alert_sent'] = true;
                            else
                                $quest_container->failure_requirements[$searching_key]['join_alert_sent'] = true;
                        }
                    }
                }
            }
        }

        return array("quest_container"=>$quest_container,"requirements"=>$requirements);
    }



    // \\\\\\\\\\\\\\\\\\\\\\\\\  "requirement" methods   /////////////////////////
    

    
    // methods for the quick preference change on the quest journal page.
    // just flips between quiet and alert settings
    function questingMode($mode)
    {
        if($mode == $GLOBALS['userdata'][0]['QuestingMode'])
        {
            if($mode == 'alert')
                $mode = 'quiet';
            else
                $mode = 'alert';

            $GLOBALS['userdata'][0]['QuestingMode'] = $mode;

            $query = "UPDATE `users_preferences` SET `QuestingMode` = '".$mode."' WHERE `uid` = ".$_SESSION['uid'];

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
        }
    }

    

    function checkForFailureAndCompletion()
    {
        if(is_array($this->QuestsData->quests))
        {
            foreach($this->QuestsData->quests as $qid => $quest_data)
            {
                if($quest_data->status == QuestContainer::$active)
                {
                    if( is_array($quest_data->data['failure_check_list']) && count($quest_data->data['failure_check_list']) > 0 && !in_array(false,$quest_data->data['failure_check_list']))
                    {
                        if(($quest_data->dialog_fail != '' && !$quest_data->failed) || (($quest_data->dialog_fail_post_failure != '' || $quest_data->dialog_fail != '') && $quest_data->failed))
                            $this->QuestsData->startDialog($qid,'dialog_fail');
                        else
                            $this->QuestsData->quitQuest($qid);
                    }
                    else if( is_array($quest_data->data['completion_check_list']) && count($quest_data->data['completion_check_list']) > 0 && !in_array(false,$quest_data->data['completion_check_list']))
                    {
                        if(($quest_data->dialog_complete != '' && !$quest_data->failed) || (($quest_data->dialog_complete_post_failure != '' || $quest_data->dialog_complete != '') && $quest_data->failed))
                            $this->QuestsData->startDialog($qid,'dialog_complete');
                        else
                            $this->completeQuest($qid);
                    }

                }
            }
        }
    }





    //record events for quests

    //check quests for completion / failure
        //re check if needed and repair if needed


    //quest tracker

    //quest history/log


    //this sets the user preference that decides to what extent a user wants to be...
    // ... bothered or left alone in regards to quests.
    //this should get a quick swap button somewhere.
    //and this should get a toggle in the user pref menu
    function setQuestMessageMode($mode)
    {
        //set user preference for alert vs quiet
    }

    //send a message to the user from the quest system.
    function showMessage($qid, $message_category, $force_alert = false)
    {
        $message_category = 'message_'.$message_category;
        $quest = $this->QuestsData->quests[$qid]; //putting quest into a easier to read format

        $popup = false;
        $duration = false;

        //picking message
        $message = '';
        if($GLOBALS['userdata'][0]['QuestingMode'] == 'alert' || $force_alert)
        {
            if($quest->failed && $quest->{$message_category.'_post_failure_alert'} != '')
            {
                $message = $quest->{$message_category.'_post_failure_alert'};
                $popup = true;
            }
            else if($quest->{$message_category.'_alert'} != '')
            {
                $message = $quest->{$message_category.'_alert'};
                $popup = true;
            }
        }
        else if($GLOBALS['userdata'][0]['QuestingMode'] == 'quiet')
        {
            if($quest->failed && $quest->{$message_category.'_post_failure_quiet'} != '')
            {
                $message = $quest->{$message_category.'_post_failure_quiet'};
                $duration = time() + 30;
            }
            else if($quest->{$message_category.'_quiet'} != '')
            {
                $message = $quest->{$message_category.'_quiet'};
                $duration = time() + 30;
            }
        }


        //if there is a message
        if($message != '')
        {
            //working variables
            $notification = array('text' => $message['message']);
            $extras = array();
            $extras['dismiss'] = 'yes';

            //cleaning up message data

            if($duration !== false)
            {
                $extras['duration'] = $duration;
                $extras['id'] = 22;
            }

            if($popup === true)
            {
                $extras['hide'] = 'yes';
                $extras['popup'] = 'yes';
            }

            if(!isset($message['details']))
                $message['details'] = 'Quest Details';

            //put together yes button
            if  (   
                    (
                        $message_category == 'message_learn' ||
                        $message_category == 'message_fail' ||
                        $message_category == 'message_fail_try_again'
                    )
                    &&
                    $this->canStart($qid, true)
                )
            {
                if(!isset($message['yes']))
                    $message['yes'] = 'Start Quest.';

                $notification['buttons'] = array( array( '?id=120&start='.$qid, $message['yes']       ));//, array( '?id='.$_GET['id'].'&reject_quest='.$qid, $message['no'] ) );
            }

            else if ( $message_category == 'message_complete' && $this->canTurnIn($qid, true) )
            {
                if(!isset($message['yes']))
                    $message['yes'] = 'Turn In Quest.';

                $notification['buttons'] = array( array( '?id=120&turn_in='.$qid, $message['yes']     ));//, array( '?id='.$_GET['id'].'&reject_quest='.$qid, $message['no'] ) );
            }

            else
            {
                if(!isset($message['yes']))
                    $message['yes'] = 'Quest Details.';

                $notification['buttons'] = array( array( '?id=120&details='.$qid, $message['details'] ));//, array( '?id='.$_GET['id'].'&reject_quest='.$qid, $message['no'] ) );
            }


            //putting together notification
            if(count($extras) > 0)
                $notification = array_merge($notification, $extras);

            if(isset($extras['id']))
                $GLOBALS['NOTIFICATIONS']->addNotification( $notification );
            else
                $GLOBALS['NOTIFICATIONS']->addTempNotification( $notification );
        }
    }

    public static function getActions($qid, $quest)
    {
        if(!isset($quest->actions))
        {

            if($quest->status == QuestContainer::$known)
            {
            
                if( $GLOBALS['QuestsControl']->canStart($qid, true) )
                {
                    $quest->actions[] = array('link'=>'?id=120&start='.$qid, 'text'=>'Start');
                }
                else if($quest->time_gap_requirement_text != '' && $GLOBALS['userdata'][0]['status'] == 'awake')
                {
                    $quest->actions[] = array('link'=>'?id=120', 'text'=>'Repeat in '.$quest->time_gap_requirement_text);
                }
                else if($GLOBALS['userdata'][0]['status'] != 'awake')
                {
                    $id = ($GLOBALS['userdata'][0]['village'] == $GLOBALS['userdata'][0]['location'] ? 23 : 19);
                    $quest->actions[] = array('link'=>"?id={$id}&act=wake", 'text'=>'Wake Up');
                }


                if( $GLOBALS['QuestsControl']->QuestsData->quests[$qid]->forgettable )
                {
                    $quest->actions[] = array('link'=>'?id=120&forget='.$qid, 'text'=>'Forget');
                }
            }

            if($quest->status == QuestContainer::$active)
            {
                $quest->actions[] = array('link'=>'?id=120&quit='.$qid, 'text'=>'Quit');
            }

            if($quest->status == QuestContainer::$completed)
            {
                if( $GLOBALS['QuestsControl']->canTurnIn($qid, true) )
                {
                    $quest->actions[] = array('link'=>'?id=120&turn_in='.$qid, 'text'=>'Turn In');
                }
                else if($GLOBALS['userdata'][0]['status'] != 'awake')
                {
                    $id = ($GLOBALS['userdata'][0]['village'] == $GLOBALS['userdata'][0]['location'] ? 23 : 19);
                    $quest->actions[] = array('link'=>"?id={$id}&act=wake", 'text'=>'Wake Up');
                }

                if( $GLOBALS['QuestsControl']->QuestsData->quests[$qid]->forgettable )
                {
                    $quest->actions[] = array('link'=>'?id=120&forget='.$qid, 'text'=>'Forget');
                }
            }
        }

        return $quest;
    }
}