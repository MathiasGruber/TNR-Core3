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
 *Class: BattlePage
 *  this class responds to the actions of a user on the battle page
 *  it is inbetween the user and the battle system
 */

require_once(Data::$absSvrPath.'/global_libs/Tags/Tags.php');
require_once(Data::$absSvrPath.'/global_libs/Tags/Tag.php');
require_once(Data::$absSvrPath.'/libs/Battle/Battle.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
require_once(Data::$absSvrPath.'/tools/DebugTool.php');
require_once(Data::$absSvrPath.'/libs/hospitalSystem/healLib.inc.php');

class BattlePage extends Battle
{
    function __construct()
    {
        //$GLOBALS['DebugTool'] = new DebugTool();
        set_time_limit(2);

        $this->microtimeMessages = false;
        if($this->microtimeMessages)$this->before = microtime();


        //if button is not set, set it to empty to avoid errors.
        if(!isset($_POST['button']))
        {
            $_POST['button'] = '';
        }

        //getting battle_id in as a local var just for better readability
        $battle_id = $GLOBALS['userdata'][0]['battle_id'];
        
        //checking for battle.
        //if not else under this for message to user
        if($battle_id != 0)
        {

            //locking against battle id, encapsulates systems response to..
                //user action and encapsulates data fetch and save to cache..
                //the prevents damage to cache data and loss of/creation of bad data
                //this lock is critical, may only be removed with a change to..
                //a multi-threaded set up with a seperate persistant thread..
                //managing the battle.
            try
            {
                //locking against battle id which is the same as the cache_id
                $GLOBALS['database']->get_lock('battle',$battle_id,__METHOD__);

                //loading battle
                if(isset($battle_id) && $battle_id != '')
                {
                    parent::__construct($battle_id, 20, true);

                    //getting battle type
                    $temp = strrev($GLOBALS['userdata'][0]['battle_id']);
                    $battle_type_code = $temp[1] . $temp[0];

                    //if there are no users active or to be removed then there is no combat.
                    if(count($this->users) == 0 && count($this->removed_users) == 0)
                    {
                        //if the battle code is for territory then this is likely caused by the user trying to go to the battle before it starts.
                        if($battle_type_code == BattleStarter::territory)
                            $GLOBALS['page']->Message("No combat here. Please return to <a href='?id=81'>Territory Challenges</a>. <br> If you are not waiting for a territory battle please contact an admin.", 'Combat System', 'id='.$_GET['id'] );

                        //otherwise then the user is likely here by mistake
                        else
                        {

                            //check to see if this is the only user in this combat.
                            $select_query = "SELECT count(*) as 'count' FROM `users` where `battle_id` = ".$battle_id;

                            try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
                            catch (Exception $e)
                            {
                                try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                                    catch (Exception $e)
                                    {
                                        $GLOBALS['DebugTool']->push('','there was an error getting battle_id count.', __METHOD__, __FILE__, __LINE__);
                                        throw $e;
                                    }
                                }
                            }


                            //if they are the only user then this tells us that the cache did not fail while they were in a pvp battle
                            //so they likely dodged a battle at this point.
                            if($result[0]['count'] == '1')
                            {
                                //put the user in the hospital
                                $GLOBALS['Events']->acceptEvent('status', array('new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                                $GLOBALS['userdata'][0]['status'] = 'hospitalized';
                                $GLOBALS['template']->assign('userStatus', 'hospitalized');
                                $hospital = new hospitalFunctions();
                                $healTime = $hospital->calculateHealtime();
                                $GLOBALS['userdata'][0]['hospital_timer'] = ($GLOBALS['user']->load_time + $healTime);

                                $query = "UPDATE `users`, `users_timer`, `users_statistics`, `villages` SET
                                                 `users`.`status` = 'hospitalized',
                                                 `users`.`latitude` = `villages`.`latitude`,
                                                 `users`.`longitude` = `villages`.`longitude`,
                                                 `users`.`location` = `villages`.`name`,
                                                 `users`.`battle_id` = 0,
                                                 `users_statistics`.`cur_health` = 0,
                                                 `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime).
                                                    " WHERE `users`.`id` = ".$_SESSION['uid']." AND users.id = users_timer.userid AND users.id = users_statistics.uid AND users.village = villages.name";

                                //sending query to database to updated user status and location/lat/lon
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

                                $GLOBALS['page']->Message("No combat was found. This was likely caused by not finishing a pve battle.<br> You have been hospitalized and your combat status has been cleared.", 'Combat System', 'id='.$_GET['id'] );
                            }
                            else
                            {
                                $script = ' <script id="pageScript">
                                                window.location.href = window.location.href.split("?")[0] + "?id=50";
                                            </script>';

                                $GLOBALS['userdata'][0]['status'] = 'awake';
                                $GLOBALS['template']->assign('userStatus', 'awake');

                                $GLOBALS['page']->Message("No combat was found. Please try again. If you are stuck here please contact an admin.<br> Error Code: B-PAVIPL".$script, 'Combat System', 'id='.$_GET['id'] );

                                $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                    'id' => 26,
                                    'duration' => time() + 30,
                                    'text' => "No combat was found. Please try again. If you are stuck here please contact an admin.<br> Error Code: B-PAVIPL",
                                    'dismiss' => 'yes'
                                ));
                            }
                        }
                        return;
                    }

                    //check to see if the battle exists and if the user is "exiting_combat".
                    if( 
                        (
                            count($this->users) == 0
                            &&
                            count($this->removed_users) == 0
                            &&
                            $GLOBALS['userdata'][0]['status'] == 'exiting_combat'
                        ) 
                        ||
                        ( 
                            (
                                isset($this->users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'])
                                &&
                                $this->users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'] + 15*60 < time()
                            )
                            ||
                            (
                                isset($this->removed_users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'])
                                &&
                                $this->removed_users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'] + 15*60 < time()
                            )
                        )
                    )
                    {
                        $this->battle_dodger();
                        return;
                    }

                    //pulling username of this user into local var for readability and indexing of other variables.
                    $this->acting_user = $this->user_index[$_SESSION['uid']];

                    //sending battle id to template for javascript use
                    $GLOBALS['template']->assign('the_id', $battle_id);
                }

                //checking for valid link
                //responding to user action
                if(isset($_POST['button']) && $_POST['button'] != '')
                {
                    $explode = explode('|',$_POST['button']);
                    //so long as the explode found one | to split on
                    if(count($explode) == 2 )
                    {

                        //if the link code matches
                        if($explode[1] == $this->users[$this->acting_user]['link_code'])
                        {
                            //generate new link code
                            $this->users[$this->acting_user]['link_code'] = random_int(0,PHP_INT_MAX);

                            //responding to button press - this catches misc button presses required by the battle tester and the normal battle
                            if(isset($_POST['button']) && $_POST['button'] != '' && $_POST['button'] != 'takeTurn' && substr($_POST['button'], 0, 7) !== 'doJutsu' && substr($_POST['button'], 0, 9) !== 'useWeapon' && substr($_POST['button'], 0, 7) !== 'useItem' && substr($_POST['button'], 0, 7) !== 'tryFlee' && substr($_POST['button'], 0, 13) !== 'call_for_help')
                            {
                                if($_POST['button'] == 'refreshBattle' || ($_POST['button'] == 'killBattle' && $this->debugging ))
                                    self::{$_POST['button']}();
                                else
                                    throw new exception('No backend response for this submission.');
                                    
                            }
                            else if(isset($_POST['button']) && substr($_POST['button'], 0, 7) === 'doJutsu' && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                            {
                                if( $this->acting_user != '' && isset($this->users[$this->acting_user]))
                                    $this->doJutsu( $this->acting_user);
                                else
                                    throw new exception('No backend response for this submission.');
                            }
                            else if(isset($_POST['button']) && substr($_POST['button'], 0, 9) === 'useWeapon' && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                            {
                                if( $this->acting_user != '' && isset($this->users[$this->acting_user]))
                                    $this->useWeapon( $this->acting_user);
                                else
                                    throw new exception('No backend response for this submission.');
                            }
                            else if(isset($_POST['button']) && substr($_POST['button'], 0, 7) === 'useItem' && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                            {
                                if( $this->acting_user != '' && isset($this->users[$this->acting_user]))
                                    $this->useItem( $this->acting_user);
                                else
                                    throw new exception('No backend response for this submission.');
                            }
                            else if(isset($_POST['button']) && substr($_POST['button'], 0, 7) === 'tryFlee' && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                            {
                                if( $this->acting_user != '' && isset($this->users[$this->acting_user])  && !$this->no_flee )
                                    $this->tryFlee( $this->acting_user);
                                else
                                    throw new exception('No backend response for this submission.');
                            }
                            else if(isset($_POST['button']) && substr($_POST['button'], 0, 13) === 'call_for_help' && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                            {
                                if( $this->acting_user != '' && isset($this->users[$this->acting_user]) && !$this->no_cfh && (!isset($this->users[$this->acting_user]['no_cfh']) || $this->users[$this->acting_user]['no_cfh'] !== true))
                                {
                                    $this->callForHelp( $this->acting_user);
                                }
                                else
                                    throw new exception('No backend response for this submission.');
                            }
                        }  
                    }
                }
                //action processing for app
                else if(isset($_POST['appButton']) && true)//check if app here
                {
                    
                    if($_POST['action_select'] == "Jutsu" && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                    {
                        if( $this->acting_user != '' && isset($this->users[$this->acting_user]))
                            $this->doJutsu( $this->acting_user);
                        else
                            throw new exception('No backend response for this submission.');
                    }
                    else if($_POST['action_select'] == "Weapons" && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                    {
                        if( $this->acting_user != '' && isset($this->users[$this->acting_user]))
                            $this->useWeapon( $this->acting_user);
                        else
                            throw new exception('No backend response for this submission.');
                    }
                    else if($_POST['action_select'] == "Items" && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                    {
                        if( $this->acting_user != '' && isset($this->users[$this->acting_user]))
                            $this->useItem( $this->acting_user);
                        else
                            throw new exception('No backend response for this submission.');
                    }
                    else if($_POST['action_select'] == "Flee" && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                    {
                        if( $this->acting_user != '' && isset($this->users[$this->acting_user])  && !$this->no_flee )
                            $this->tryFlee( $this->acting_user);
                        else
                            throw new exception('No backend response for this submission.');
                    }
                    else if($_POST['action_select'] == "Call_For_Help" && (!isset($this->users[$this->acting_user]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0) && !$this->checkForAction($this->acting_user, $this->turn_counter))
                    {
                        if( $this->acting_user != '' && isset($this->users[$this->acting_user]) && !$this->no_cfh && (!isset($this->users[$this->acting_user]['no_cfh']) || $this->users[$this->acting_user]['no_cfh'] !== true))
                        {
                            $this->callForHelp( $this->acting_user);
                        }
                        else
                            throw new exception('No backend response for this submission.');
                    }
                    else
                        throw new exception('un-known-action: '.$_POST['action_select']);

                }



                //this processes the battle
                if(isset($battle_id) && $battle_id != '' && count($this->users))
                {
                    //checking for stunned users as submitting their "actions"
                    $this->processStunnedUsers();

                    //after button press processing check to see if this turn is over.
                    //if so auto process turn so that this page will display the correct user.
                    if( ($_POST['button'] == 'takeTurn' || ! $this->findFirstUser( $this->turn_counter ) || time() > $this->turn_timer ) && count($this->users) >= 2)
                    {
                        //can be overridden by battle types for what ever purpose is needed.
                        $this->startOfTurnInjectionPoint();
                        if($this->microtimeMessages)error_log('after start of turn injection point: '.(microtime() - $this->before));


                        //process ai
                        $this->processAI($this->getTurnOrder());
                        if($this->microtimeMessages)error_log('after process ai: '.(microtime() - $this->before));

                        //process inactive users.
                        if(time() >= $this->turn_timer && $this->findFirstUser( $this->turn_counter ) !== false)
                        {
                            $this->checkForInactiveUsers();
                        }
                        if($this->microtimeMessages)error_log('after check for inactive users: '.(microtime() - $this->before));

                        //do the turn
                        $this->processTags($this->getTurnOrder());
                        if($this->microtimeMessages)error_log('after process tags'.(microtime() - $this->before));

                        //process battle log
                        $this->processBattleLog( $this->turn_counter - 1);
                        if($this->microtimeMessages)error_log('after process battle log: '.(microtime() - $this->before));

                        //check for removal of users.
                        $this->checkUsersForRemove();
                        if($this->microtimeMessages)error_log('after check users for remove: '.(microtime() - $this->before));

                        //updating dsr of users
                        $this->updateDSRs();
                        if($this->microtimeMessages)error_log('after udating dsr: '.(microtime() - $this->before));

                        //updating turn timer.
                        $this->UpdateTurnTimer();
                        if($this->microtimeMessages)error_log('after update turn timer: '.(microtime() - $this->before));

                        //can be overridden by battle types for what ever purpose is needed.
                        $this->endOfTurnInjectionPoint();
                        if($this->microtimeMessages)error_log('after end of turn injection: '.(microtime() - $this->before));

                        //check for end of battle
                        $this->checkForBattleEnd();
                        if($this->microtimeMessages)error_log('after check for end of battle: '.(microtime() - $this->before));
                    }
                    //this only needs to be done for debugging.
                    else if($this->debugging)
                    {
                        $this->updateTagsInEffect();
                    }

                    //if killing the battle do not update the cache;
                    if($_POST['button'] != 'killBattle')
                        $this->updateCache();
                }
            }
            //if the lock was not able to be captured display an error message.
            catch (Exception $e)
            {
                
                $GLOBALS['database']->release_lock('battle',$battle_id);
                $GLOBALS['page']->Message("Locking Error(likely). Please try again. ", 'Combat System', 'id='.$_GET['id'] );
                return;
            }


			//showing page
            //calls the method in the battle class that displayes the battle page to the user.
			$this->showBattlePage();

            $GLOBALS['database']->release_lock('battle',$battle_id);

            if($this->microtimeMessages)error_log('after show battle page: '.(microtime() - $this->before));
            if($this->microtimeMessages)error_log('');
        }
        //if there is no battle
        //show an error message for the user.
        else
        {
            if($GLOBALS['userdata'][0]['status'] != 'exiting_combat')
            {
                $GLOBALS['page']->Message("As you enter the battle-field you discover that this battle has already been concluded.<br>
                            There is nothing left here for you to do.", 'Battle', 'id=2','Return to Profile');
            }
            else
            {
                $this->battle_dodger();
                return;
            }
        }
    }


    //refreshBattle
    //does nothing but allow the user to reload the page.
    //called by js to refresh the page.
    function refreshBattle ( ) { }

    //killBattle - for debugging
    //removes all data for this battle.
    //removes battle id.
    function killBattle()
    {
        parent::purgeCache();
    }

    //this function pulls all the needed information to call the doJutsu and recordAction methods of the battle class.
    //it then calls them.
    function doJutsu($owner_username , $stub1 = false, $stub2 = false, $stub3 = false, $stub4 = false)
    {
        $weapon_ids = array();
        foreach($_POST as $post_key => $post_data)
        {
            if(substr($post_key,0,20) === 'jutsu_weapon_select-')
                if($post_data != '')
                    $weapon_ids[] = $post_data;
        }

        if(isset($_POST['target_select']) && $_POST['target_select'] != '' && $owner_username != '' && isset($_POST['jutsu_select']) && $_POST['jutsu_select'] != '' && $weapon_ids != '')
        {
            if( (!isset($this->users[$owner_username]['status_effects']['disabled']) || $this->users[$owner_username]['status_effects']['disabled'] == 0) || $_POST['jutsu_select'] == -1 )
            {
                $target_username = $_POST['target_select'];
                $jutsu_id = $_POST['jutsu_select'];

                parent::doJutsu($target_username, $owner_username, $jutsu_id, $weapon_ids);
            }
        }
        else
            throw new exception('bad data');

        if($this->microtimeMessages)error_log('after doJutsu: '.(microtime() - $this->before));
    }

    //this function pulls all the needed information to call the useWeapon and recordAction methods of the battle class.
    //it then calls them.
    function useWeapon($owner_username, $stub1 = false, $stub2 = false)
    {
        if(isset($_POST['target_select']) && isset($_POST['weapon_attack_select']))
        {
            $target_username = $_POST['target_select'];
            $weapon_id = $_POST['weapon_attack_select'];

            $this->recordAction( $owner_username, $target_username, 'weapon', $weapon_id, $this->users[$owner_username]['equipment'][$weapon_id]['name']);
            parent::useWeapon($target_username, $owner_username, $weapon_id);
        }

        if($this->microtimeMessages)error_log('after use weapon: '.(microtime() - $this->before));
    }

    //this function pulls all the needed information to call the useItem and recordAction methods of the battle class.
    //it then calls them.
    function useItem($owner_username, $stub1 = false, $stub2 = false)
    {
        if(isset($_POST['target_select']) && isset($_POST['item_attack_select']))
        {
            $target_username = $_POST['target_select'];
            $item_id = $_POST['item_attack_select'];

            $this->recordAction($owner_username, $target_username, 'item', $item_id, $this->users[$owner_username]['items'][$item_id]['name']);
            parent::useItem($item_id, $owner_username, $target_username);
        }

        if($this->microtimeMessages)error_log('after use item: '.(microtime() - $this->before));
    }

    //this function pulls all the needed information to call the tryFlee and recordAction methods of the battle class.
    //it then calls them.
    function tryFlee($owner_username)
    {
        $this->recordAction($owner_username, $owner_username, 'flee', false, false);
        parent::tryFlee($owner_username);

        if($this->microtimeMessages)error_log('after try flee: '.(microtime() - $this->before));
    }

    //this function pulls all the needed information to call the callForHelp and recordAction methods of the battle class.
    //it then calls them.
    function callForHelp($owner_username)
    {
        $this->recordAction($owner_username, $owner_username, 'call_for_help', false, false);
        parent::callForHelp($owner_username);

        if($this->microtimeMessages)error_log('after call for help: '.(microtime() - $this->before));
    }
}
