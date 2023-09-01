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
 *Class: TagsTester
 *  this class lets users test the battle system
 *
 */

require_once(Data::$absSvrPath.'/global_libs/Tags/Tags.php');
require_once(Data::$absSvrPath.'/global_libs/Tags/Tag.php');
require_once(Data::$absSvrPath.'/panel_event/modules/BattleTester/Battle.php');
require_once(Data::$absSvrPath.'/tools/DebugTool.php');

class BattleTester extends Battle
{
    function __construct()
    {
        $GLOBALS['DebugTool'] = new DebugTool();
        set_time_limit(2);

        //locking against battle id, encapsulates systems response to..
        //user action and encapsulates data fetch and save to cache..
        //the prevents damage to cache data and loss of/creation of bad data
        //this lock is critical, may only be removed with a change to..
        //a multi-threaded set up with a seperate persistant thread..
        //managing the battle.
        try
        {
            //locking against battle id which is the same as the cache_id
            $GLOBALS['database']->get_lock('battle',$_SESSION['battle_tester_id'],__METHOD__);

            //loading battle
            if(isset($_SESSION['battle_tester_id']) && $_SESSION['battle_tester_id'] != '')
            {
                parent::__construct($_SESSION['battle_tester_id'], 20, true);
                $GLOBALS['template']->assign('the_id', $_SESSION['battle_tester_id']);
            }


            //responding to button press
            if(isset($_POST['button']) && $_POST['button'] != 'takeTurn' && substr($_POST['button'], 0, 7) !== 'doJutsu' && substr($_POST['button'], 0, 9) !== 'useWeapon' && substr($_POST['button'], 0, 7) !== 'useItem' && substr($_POST['button'], 0, 7) !== 'tryFlee' && substr($_POST['button'], 0, 7) !== 'call_for_help')
            {
                self::{$_POST['button']}();
            }
            else if(isset($_POST['button']) && substr($_POST['button'], 0, 7) === 'doJutsu')
            {
                $temp = explode('|',$_POST['button']);
                if( $_POST['acting_user'] != '' && isset($this->users[$_POST['acting_user']]))
                    $this->doJutsu( $_POST['acting_user']);
                else
                    $this->doJutsu( $this->findFirstUser( $this->turn_counter ) );
            }
            else if(isset($_POST['button']) && substr($_POST['button'], 0, 9) === 'useWeapon')
            {
                $temp = explode('|',$_POST['button']);
                if( $_POST['acting_user'] != '' && isset($this->users[$_POST['acting_user']]))
                    $this->useWeapon( $_POST['acting_user']);
                else
                    $this->useWeapon( $this->findFirstUser( $this->turn_counter ) );
            }
            else if(isset($_POST['button']) && substr($_POST['button'], 0, 7) === 'useItem')
            {
                $temp = explode('|',$_POST['button']);
                if( $_POST['acting_user'] != '' && isset($this->users[$_POST['acting_user']]))
                    $this->useItem( $_POST['acting_user']);
                else
                    $this->useItem( $this->findFirstUser( $this->turn_counter ) );
            }
            else if(isset($_POST['button']) && substr($_POST['button'], 0, 7) === 'tryFlee')
            {
                $temp = explode('|',$_POST['button']);
                if( $_POST['acting_user'] != '' && isset($this->users[$_POST['acting_user']]))
                    $this->tryFlee( $_POST['acting_user']);
                else
                    $this->tryFlee( $this->findFirstUser( $this->turn_counter ) );
            }
            else if(isset($_POST['button']) && substr($_POST['button'], 0, 13) === 'call_for_help')
            {
                $temp = explode('|',$_POST['button']);
                if( $_POST['acting_user'] != '' && isset($this->users[$_POST['acting_user']]))
                    $this->callForHelp( $_POST['acting_user']);
                else
                    $this->callForHelp( $this->findFirstUser( $this->turn_counter ) );
            }

            //if this is the master user // not implemented yet
            //this processes the battle

            if(isset($_SESSION['battle_tester_id']) && $_SESSION['battle_tester_id'] != '' && count($this->users))
            {
                //after button press processing check to see if this turn is over.
            //if so auto process turn so that this page will display the correct user.
                if( ($_POST['button'] == 'takeTurn' || ! $this->findFirstUser( $this->turn_counter ) || time() > $this->turn_timer ) && count($this->users) >= 2)
                {
                    //process ai
                    $before = microtime();
                    $this->processAI($this->getTurnOrder());
                    $after = microtime();
                    //echo '<br>ai time cost: '.($after - $before).'<br>';
                    $before = $after;

                    //process in active users.
                    if(time() >= $this->turn_timer && $this->findFirstUser( $this->turn_counter ) !== false)
                    {
                        $this->checkForInactiveUsers();
                    }

                    //do the turn
                    $this->processTags($this->getTurnOrder());
                    $after = microtime();
                    //echo '<br>tags time cost: '.($after - $before).'<br>';
                    $before = $after;

                    //process battle log
                    $this->processBattleLog( $this->turn_counter - 1);
                    $after = microtime();
                    //echo '<br>battle log time cost: '.($after - $before).'<br>';
                    $before = $after;

                    //check for removal of users.
                    $this->checkUsersForRemove();
                    $after = microtime();
                    //echo '<br>user death time cost: '.($after - $before).'<br>';
                    $before = $after;

                    //updating dsr of users
                    $this->updateDSRs();
                    $after = microtime();
                    //echo '<br>udating dsr time cost: '.($after - $before).'<br>';
                    $before = $after;

                    //updating turn timer.
                    $this->UpdateTurnTimer();

                    //check for end of battle
                    $this->checkForBattleEnd();
                }
                else
                {
                    $this->updateTagsInEffect();
                }

                if($_POST['button'] != 'resetBattle' && $_POST['button'] != 'killBattle')
                    $this->updateCache();
            }
        }
        catch (Exception $e)
        {
            $GLOBALS['database']->release_lock('battle',$this->cache_id);
            $GLOBALS['page']->Message("Locking Error. Please try again. ". $e->getMessage(), 'Combat System', 'id='.$_GET['id'] );
        }

        //handle control panel stuffs
        {

            //passing this pages data entries to the next page.
            if(isset($_POST['battle_id_box']          )) { $GLOBALS['template']->assign('battle_id_box_default',          $_POST['battle_id_box']          );}
            if(isset($_POST['user_username_box']      )) { $GLOBALS['template']->assign('user_username_box_default',      $_POST['user_username_box']      );}
            if(isset($_POST['data_value_box']         )) { $GLOBALS['template']->assign('data_value_box_default',         $_POST['data_value_box']         );}
            if(isset($_POST['data_target_box']        )) { $GLOBALS['template']->assign('data_target_box_default',        $_POST['data_target_box']        );}
            if(isset($_POST['data_username_box']      )) { $GLOBALS['template']->assign('data_username_box_default',      $_POST['data_username_box']      );}
            if(isset($_POST['equipment_username_box'] )) { $GLOBALS['template']->assign('equipment_username_box_default', $_POST['equipment_username_box'] );}
            if(isset($_POST['equipment_id_box']       )) { $GLOBALS['template']->assign('equipment_id_box_default',       $_POST['equipment_id_box']       );}
            if(isset($_POST['acting_user']            )) { $GLOBALS['template']->assign('acting_user',                    $_POST['acting_user']            );}
            if(isset($_POST['allow_refresh']          )) { $GLOBALS['template']->assign('allow_refresh',                  $_POST['allow_refresh']          );}
        }

        //handle battle panel stuffs
        if( !isset($_POST['acting_user']) || $_POST['acting_user'] == "" || $_POST['acting_user'] == NULL || isset($this->users[$_POST['acting_user']]) )
        {
            $users = array();
            //building jutsu list
            foreach($this->users as $username => $userdata)
            {
                $users[$username] = array();
                $jutsu_weapon_selects = '';

                //passing jutsu data to page
                $users[$username]['jutsus'] = array();
                if( isset($userdata['jutsus']) && (($username == $this->findFirstUser( $this->turn_counter ) || ( isset($users[$_POST['acting_user']]) && $username == $_POST['acting_user'] && $this->checkForAction($_POST['acting_user'], $this->turn_counter) !== true)  )))
                {
                    foreach($userdata['jutsus'] as $jutsu_key => $temp_jutsu_data)
                    {
                        if($jutsu_key != 'cooldowns')
                        {
                            $jutsu_data = array_merge($this->jutsus[$jutsu_key], $temp_jutsu_data);

                            $users[$username]['jutsus'][$jutsu_key] = array_merge($jutsu_data, $this->jutsus[$jutsu_key]);

                            $cooldown = $this->checkJutsuCooldown($jutsu_key, $jutsu_data['cooldown_pool_check'], $username);

                            if($cooldown === false || $cooldown[1] === 0 )
                                $users[$username]['jutsus'][$jutsu_key]['cooldown_status'] = 'off';
                            else
                                $users[$username]['jutsus'][$jutsu_key]['cooldown_status'] = $cooldown[1];

                            $users[$username]['jutsus'][$jutsu_key]['reagent_status'] = $this->checkAndConsumeReagents($username, $jutsu_data['reagents'], false);

                            if($jutsu_data['weapons'] != '')
                            {

                                foreach( explode(',',$jutsu_data['weapons']) as $weapon_group_key => $required_weapon_group )
                                {

                                    if(!isset($GLOBALS['mf']) || $GLOBALS['mf'] != 'yes')
                                        $jutsu_weapon_selects .= '<select style="width:100%;border:1px solid black;" class="tableColumns select-wrapper" size="1" name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'"'.'id ="'.$jutsu_key.'-'.$weapon_group_key.'"><option selected disabled value="default">Select A Weapon</option>';
                                    else
                                        $jutsu_weapon_selects .= '<select class="page-drop-down-fill-dark select-wrapper" name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'"'.'id ="'.$jutsu_key.'-'.$weapon_group_key.'"><option selected disabled value="default">Select A Weapon</option>';

                                    foreach( $userdata['equipment'] as $equipment_id => $equipment_data )
                                    {
                                        $set_this_weapon = true;

                                        $weapon_types = explode(',', $equipment_data['weapon_classifications']);

                                        if($set_this_weapon === true)
                                        {
                                            if( is_numeric($required_weapon_group) )
                                            {
                                                if($required_weapon_group != $equipment_data['iid'])
                                                    $set_this_weapon = false;
                                            }

                                            else
                                            {
                                                foreach( explode('/',$required_weapon_group) as $required_type )
                                                    if(!in_array($required_type, $weapon_types))
                                                        $set_this_weapon = false;
                                            }
                                        }


                                        //checking element type
                                        if($set_this_weapon === true)
                                        {
                                            //getting elements for jutsu and weapon

                                            $jutsu_element =  $jutsu_data['element'];
                                            $weapon_element = $equipment_data['element'];

                                            if( $weapon_element === '' )
                                                $weapon_element = 'None';

                                            //check to make sure that you are past the em thresh hold to allow elemental weapon use.
                                            if( $jutsu_element != 'None' && $weapon_element != 'None' && $weapon_element != 'none' && $weapon_element != 'N' && !$jutsu_data['allow_elemental_weapons'] )
                                                $set_this_weapon = false;//flag that this weapon can not be used

                                            //if elements do not match...
                                            if($jutsu_element != $weapon_element && $weapon_element != 'None' && $weapon_element != 'none' && $weapon_element != 'N')
                                                //if jutsu_element is not a key in element heritage aka it is a base element...
                                                if(!isset($this->ELEMENTHERITAGE[$jutsu_element]))
                                                    $set_this_weapon = false;//flag that this weapon can not be used

                                                //otherwise check to make sure that the jutsu's element is a child of the weapon's elements...
                                                else if(!in_array($weapon_element, $this->ELEMENTHERITAGE[$jutsu_element]))
                                                    $set_this_weapon = false;//flag that this weapon can not be used
                                        }

                                        if($set_this_weapon === true)
                                        {
                                            $uses_left = $this->users[$username]['equipment_used'][ $this->users[$username]['equipment'][$equipment_id]['iid'] ]['max_uses'] - $this->users[$username]['equipment_used'][ $this->users[$username]['equipment'][$equipment_id]['iid'] ]['uses'];

                                            if( $uses_left > 0 )
                                                $jutsu_weapon_selects .= '<option title="uses left: '.$uses_left.'" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                            else
                                                $jutsu_weapon_selects .= '<option disabled title="uses left: '.$uses_left.'" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                        }

                                    }
                                    $jutsu_weapon_selects .= '</select>';
                                }
                            }
                        }
                    }
                }


                $users[$username]['health'] = $userdata['health'];
                $users[$username]['healthMax'] = $userdata['healthMax'];


                if(isset($userdata['stamina']))
                    $users[$username]['stamina'] = $userdata['stamina'];
                if(isset($userdata['staminaMax']))
                    $users[$username]['staminaMax'] = $userdata['staminaMax'];
                if(isset($userdata['chakra']))
                    $users[$username]['chakra'] = $userdata['chakra'];
                if(isset($userdata['chakraMax']))
                    $users[$username]['chakraMax'] = $userdata['chakraMax'];
                if(isset($userdata['equipment']))
                    $users[$username]['equipment'] = $userdata['equipment'];
                if(isset($userdata['equipment_used']))
                    $users[$username]['equipment_used'] = $userdata['equipment_used'];
                if(isset($userdata['avatar']))
                    $users[$username]['avatar'] = $userdata['avatar'];
                if(isset($userdata['display_rank']))
                    $users[$username]['display_rank'] = $userdata['display_rank'];
                if(isset($userdata['village']))
                    $users[$username]['village'] = $userdata['village'];

                $users[$username]['ai'] = $userdata['ai'];

                if(!isset($userdata['show_count']))
                    $users[$username]['show_count'] = 'yes';

                else
                    $users[$username]['show_count'] = $userdata['show_count'];

                if(isset($userdata['items']))
                {
                    $users[$username]['items'] = $userdata['items'];

                    if(isset($userdata['items_used']))
                        $users[$username]['items_used'] = $userdata['items_used'];
                }
                else
                {
                    $users[$username]['items'] = array();
                    $users[$username]['items_used'] = array();
                }

                $users[$username]['team'] = $userdata['team'];

                $users[$username][parent::ELEMENTMASTERIES] = $userdata[parent::ELEMENTMASTERIES];

                $users[$username][parent::ELEMENTS] = $userdata[parent::ELEMENTS];

                if(isset($jutsu_weapon_selects))
                    $users[$username]['jutsu_weapon_selects'] = $jutsu_weapon_selects;

                $users[$username]['DSR'] = $userdata['DSR'];

            }

            if( $_POST['acting_user'] != '' && isset($users[$_POST['acting_user']]))
            {
                $temp = $users[ $_POST['acting_user'] ];
                $temp['name'] = $_POST['acting_user'];
                $temp['waiting_for_next_turn'] = $this->checkForAction($_POST['acting_user'], $this->turn_counter);
            }
            else
            {
                $temp = $users[ $this->findFirstUser( $this->turn_counter ) ];
                $temp['name'] = $this->findFirstUser( $this->turn_counter );
            }

            $GLOBALS['template']->assign('owner',$temp);

            $GLOBALS['template']->assign('users',$users);

            $GLOBALS['template']->assign('battle_log', $this->battle_log);

            $GLOBALS['template']->assign('turn_counter', $this->turn_counter);

            $GLOBALS['template']->assign('turn_timer', $this->turn_timer);

            $DSR = array();
            foreach($this->users as $temp_user_data)
            {
                if(!isset($DSR[$temp_user_data['team']]))
                    $DSR[$temp_user_data['team']] = 0;

                $DSR[$temp_user_data['team']] += $temp_user_data['DSR'];
            }

            $friendlyDSR = $DSR[$temp['team']];

            unset($DSR[$temp['team']]);

            $opponentDSR = max($DSR);

            $GLOBALS['template']->assign('friendlyDSR',$friendlyDSR);

            $GLOBALS['template']->assign('opponentDSR',$opponentDSR);

            $GLOBALS['template']->assign('rng', $this->rng);


            $this_dump =
            '<details>'.
            '<summary>all data</summary>'.
            '<pre>'.
            var_export($this, true).
            '</pre>'.
            '</details>';

            $GLOBALS['template']->assign('this_dump',$this_dump);

            $users_dump =
            '<details>'.
            '<summary>users-data</summary>'.
            '<pre>'.
            var_export($this->users, true).
            '</pre>'.
            '</details>';

            $GLOBALS['template']->assign('users_dump',$users_dump);

            //adding template
            $GLOBALS['template']->assign('contentLoad', './panel_event/templates/BattleTester/BattleTester.tpl');
        }
        else if( (!isset($this->users[$_POST['acting_user']]) && $this->removed_users[$_POST['acting_user']]) || $this->turn_counter == -1 )
        {
            $owner = array();
            $owner['name'] = $_POST['acting_user'];

            $team = '';

            foreach($this->battle_log as $round_data)
            {
                foreach($round_data as $username => $userdata)
                {
                    if($username == $_POST['acting_user'])
                        $team = $userdata['team'];
                }
            }

            $changes = $this->processEndOfCombatForUser($_POST['acting_user']);

            $owner['team'] = $team;

            $owner['win_lose'] = $this->removed_users[$_POST['acting_user']]['win_lose'];

            $GLOBALS['template']->assign('owner', $owner);

            $GLOBALS['template']->assign('battle_log', $this->battle_log);

            //adding template
            $GLOBALS['template']->assign('contentLoad', './panel_event/templates/BattleTester/BattleSummary.tpl');
        }
        else
        {
            echo 'nothing here';
        }
    }

    //joinBattle
    //this method sets what battle this page is part of.
    function joinBattle()
    {
        if(isset($_POST['battle_id_box']))
        {
            $_SESSION['battle_tester_id'] = $_POST['battle_id_box'];
            parent::__construct($_SESSION['battle_tester_id'], 20, true);
            $this->rng = random_int(-11478,48129);
            $this->updateCache();
        }
    }

    //refreshBattle
    //does nothing but allow the user to reload the page.
    function refreshBattle(){}

    //killBattle
    //removes all data for this battle.
    //removes battle id.
    function killBattle()
    {
        if(isset($_SESSION['battle_tester_id']) && $_SESSION['battle_tester_id'] != '')
        {
            $_SESSION['battle_tester_id'] = '';
            $_POST['battle_id_box'] = '';
        }

        parent::purgeCache();
    }

    //resetBattle
    //removes all data for this battle.
    function resetBattle()
    {
        parent::purgeCache();
    }

    //addUsers
    //adds users and ai to the battle
    function addUsers()
    {
        //breaking data down from entry field.
        $users = explode('|',$_POST['user_username_box']);
        $humans = array();
        $ais = array();
        //for every user
        foreach($users as $i => $user)
        {
            //break data down into array
            $users[$i] = explode(',',$user);

            if(count($users[$i]) >= 3)
            {

                //if this is an ai
                if($users[$i][2] == 'ai')
                {
                    //make sure team slot is open
                    if(!isset($ais[$users[$i][1]]))
                        $ais[$users[$i][1]] = array();

                    //save username
                    $ais[$users[$i][1]][] = $users[$i][0];

                    //adding extras if needed
                    if( count($users[$i]) == 4 )
                    {
                        $count = (($users[$i][3]) - 1);
                        for($j = 0; $j < $count; $j++)
                        {
                            $ais[$users[$i][1]][] = $users[$i][0];
                        }
                    }

                }

                //if this is a human
                else if($users[$i][2] == 'human')
                {
                    //make sure team slot is open
                    if(!isset($humans[$users[$i][1]]))
                        $humans[$users[$i][1]] = array();

                    //save username
                    $humans[$users[$i][1]][] = $users[$i][0];
                }
            }
        }

        //for each team/group of humans
        foreach($humans as $team => $human_group)
        {
            //send that group to addUser in battle
            parent::addUser($human_group,$team);
        }

        //for each team/group of ais
        foreach($ais as $team => $ai_group)
        {
            //send that group to addAi in battle
            parent::addAI($ai_group, $team);
        }
    }

    //addUser
    //adds user to the battle
    //a and b should never be used
    function addUser($a=false,$b=false)
    {
        if(isset($_POST['user_username_box']) && $_POST['user_username_box'] != '' &&
            isset($_POST['user_team_id_box'])  && $_POST['user_team_id_box'] != '' &&
            !isset($this->users[$_POST['user_username_box']]))
                parent::addUser($_POST['user_username_box'],$_POST['user_team_id_box']);
    }

    //addAI
    //adds an ai to the battle
    //ignore stub a and b
    function addAI($a=false,$b=false)
    {
        if(isset($_POST['user_username_box']) && $_POST['user_username_box'] != '' &&
            isset($_POST['user_team_id_box'])  && $_POST['user_team_id_box'] != '' &&
            (!isset($this->users[$_POST['user_username_box']]) || $this->users[$_POST['user_username_box']]['ai'] ))
            parent::addAI($_POST['user_username_box'],$_POST['user_team_id_box']);
    }

    //removeUser
    //removes user from the battle
    //a is not used
    function removeUser($a=false)
    {
        if(isset($_POST['user_username_box']) && $_POST['user_username_box'] != '' &&
            isset($this->users[$_POST['user_username_box']]) )
                parent::removeUser($_POST['user_username_box']);
    }

    //restoreUser
    //sets a user's pools to their caps
    function restoreUser()
    {
        if(isset($_POST['user_username_box']) && $_POST['user_username_box'] != '' &&
            isset($this->users[$_POST['user_username_box']]) )
        {
            $this->users[$_POST['user_username_box']]['health'] = $this->users[$_POST['user_username_box']]['healthMax'];
            $this->users[$_POST['user_username_box']]['chakra'] = $this->users[$_POST['user_username_box']]['chakraMax'];
            $this->users[$_POST['user_username_box']]['stamina'] = $this->users[$_POST['user_username_box']]['staminaMax'];
        }
    }

    //killUser
    //sets a user's pools to zero
    function killUser()
    {
        if(isset($_POST['user_username_box']) && $_POST['user_username_box'] != '' &&
            isset($this->users[$_POST['user_username_box']]) )
        {
            $this->users[$_POST['user_username_box']]['health'] = 0;
        }
    }

    //setData
    //takes user input on button press and sets the data
    function setData()
    {
        if(isset($_POST['data_value_box']) &&
           isset($_POST['data_target_box']) && $_POST['data_target_box'] != '' &&
           isset($_POST['data_username_box']) && $_POST['data_username_box'] != '')
        {
            $value = $_POST['data_value_box'];
            $target_chain = explode(';', $_POST['data_target_box']);
            $target_user = $_POST['data_username_box'];

            if(count($target_chain) == 1)
                $this->users[$target_user]{$target_chain[0]} = $value;
            if(count($target_chain) == 2)
                $this->users[$target_user]{$target_chain[0]}{$target_chain[1]} = $value;
            if(count($target_chain) == 3)
                $this->users[$target_user]{$target_chain[0]}{$target_chain[1]}{$target_chain[2]} = $value;
            if(count($target_chain) == 4)
                $this->users[$target_user]{$target_chain[0]}{$target_chain[1]}{$target_chain[2]}{$target_chain[3]} = $value;
            if(count($target_chain) == 5)
                $this->users[$target_user]{$target_chain[0]}{$target_chain[1]}{$target_chain[2]}{$target_chain[3]}{$target_chain[4]} = $value;
        }
    }

    function doJutsu($owner_username , $stub1 = false, $stub2 = false, $stub3 = false)
    {
        $target_username = $_POST['target_select'];
        $jutsu_id = $_POST['jutsu_select'];

        $weapon_ids = array();
        foreach($_POST as $post_key => $post_data)
        {
            if(substr($post_key,0,20) === 'jutsu_weapon_select-')
                if($post_data != '')
                    $weapon_ids[] = $post_data;
        }

        $this->recordAction( $owner_username, $target_username, 'jutsu', $jutsu_id, $this->jutsus[$jutsu_id]['name']);
        parent::doJutsu($target_username, $owner_username, $jutsu_id, $weapon_ids);
    }

    function useWeapon($owner_username, $stub1 = false, $stub2 = false)
    {
        $target_username = $_POST['target_select'];
        $weapon_id = $_POST['weapon_attack_select'];

        $this->recordAction( $owner_username, $target_username, 'weapon', $weapon_id, $this->users[$owner_username]['equipment'][$weapon_id]['name']);
        parent::useWeapon($target_username, $owner_username, $weapon_id);
    }

    function useItem($owner_username, $stub1 = false, $stub2 = false)
    {
        $target_username = $_POST['target_select'];
        $item_id = $_POST['item_attack_select'];

        $this->recordAction($owner_username, $target_username, 'item', $item_id, $this->users[$owner_username]['items'][$item_id]['name']);
        parent::useItem($target_username, $owner_username, $item_id);
    }

    function tryFlee($owner_username)
    {
        $this->recordAction($owner_username, $owner_username, 'flee', false, false);
        parent::tryFlee($owner_username);
    }

    function callForHelp($owner_username)
    {
        //$this->recordAction($owner_username, $owner_username, 'call_for_help', false, false);
        parent::callForHelp($owner_username);
    }
}

new BattleTester();