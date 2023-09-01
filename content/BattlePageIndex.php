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
 *Class: BattlePageIndex
 *  this class looks at the battle id and
 *  based on the last two digits of the id
 *  it will route to the correct battle page
 *  this is used for overriding methods as 
 *  needed for different types of battles.
 *
 *  00: default
 *  01: travel
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
require_once(Data::$absSvrPath.'/libs/Battle/TravelBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/EventBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/SparBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/PvpBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/SmallCrimesBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/MissionBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/KageBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/ClanBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/ArenaBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/MirrorBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/TornBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/TerritoryBattle.php');
require_once(Data::$absSvrPath.'/libs/Battle/QuestBattle.php');

class BattlePageIndex
{
    function __construct()
    {
        try
        {
            $GLOBALS['database']->get_lock("battle",$_SESSION['uid'],__METHOD__);
        
            //processing battle history reports if there are any
            if(isset($_POST['details']) && $_POST['details'] != '')
            {
                $battle_summary_links = '';
                $battle_history_ids = array();
                foreach($_POST as $post_key => $post_data)
                {
                    if( substr( $post_key, 0, 6 ) === 'report' && $post_data != ' Report ')
                    {
                        $battle_summary_links .= '<a href=\'?id=113&history_id='.$post_data.'\'>history: '.$post_data.'</a><br><br>';
                        $battle_history_ids[] = $post_data;
                    }
                }

                //creating report
                $query = 'INSERT INTO `user_reports`
                                    (`time`, `uid`, `rid`, `village`, `reason`, `message`, `status`, `processed_by`, `type`, `refer_mod`, `reference_reason`, `tnrVersion`, `mt`)
                            VALUES  ("'.time().'", "000", "'.$_SESSION['uid'].'", "'.$GLOBALS['userdata'][0]['village'].'", "'.$_POST['details'].'", "'.$battle_summary_links.'", "unviewed", "",             "battle", "",          "",                 "core3",      "0")';

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

                //marking historys as keep
                $query = 'UPDATE `battle_history` SET `keep` = "yes" WHERE `id` in ('.(implode(', ',$battle_history_ids)).')';

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

            //showing the battle if there is one
            if(isset(strrev($GLOBALS['userdata'][0]['battle_id'])[1]))
            {
                //parsing the battle type id out from the battle id
                $battle_type_code = (strrev($GLOBALS['userdata'][0]['battle_id'])[1] . strrev($GLOBALS['userdata'][0]['battle_id'])[0]);

                //sending the server time to the template
                $GLOBALS['template']->assign('time', date('jS \of F Y h:i:s A'));

                //calling the battle type class
                if( $battle_type_code == BattleStarter::generic)
                {
                    new BattlePage();
                }
                else if( $battle_type_code == BattleStarter::travel )
                {
                    new TravelBattle();
                }
                else if( $battle_type_code == BattleStarter::event )
                {
                    new EventBattle();
                }
                else if( $battle_type_code == BattleStarter::spar )
                {
                    new SparBattle();
                }
                else if( $battle_type_code == BattleStarter::pvp )
                {
                    new PvpBattle();
                }
                else if( $battle_type_code == BattleStarter::small_crimes )
                {
                    new SmallCrimesBattle();
                }
                else if( $battle_type_code == BattleStarter::mission )
                {
                    new MissionBattle();
                }
                else if( $battle_type_code == BattleStarter::kage )
                {
                    new KageBattle();
                }
                else if( $battle_type_code == BattleStarter::clan )
                {
                    new ClanBattle();
                }
                else if( $battle_type_code == BattleStarter::arena )
                {
                    new ArenaBattle();
                }
                else if( $battle_type_code == BattleStarter::mirror )
                {
                    new MirrorBattle();
                }
                else if( $battle_type_code == BattleStarter::torn )
                {
                    new TornBattle();
                }
                else if( $battle_type_code == BattleStarter::territory )
                {
                    new TerritoryBattle();
                }
                else if( $battle_type_code == BattleStarter::quest )
                {
                    new QuestBattle();
                }
                else
                {
                    new BattlePage();
                } 
            }

            //if there is no battle show the battle history page
            else
            {
                //if there is no history_id then show the list of all battles
                if(!isset($_GET['history_id']))
                {
                    $base = true;

                    //getting battle history list
                    //sort was used until the page was changed to use jquery to handle this.
                    if(!isset($_GET['sort']))
                        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4*2).") ORDER BY `time` DESC";

                    else if($_GET['sort'] == 'pvp')
                    {
                        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`type` = '03' OR `type` = '04') AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4*2).") ORDER BY `time` DESC";
                        $GLOBALS['template']->assign('pvp',true);
                        $base = false;
                    }

                    else if($_GET['sort'] == 'pve')
                    {
                        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`type` = '01' OR `type` = '02' OR `type` = '05' OR `type` = '06' OR `type` = '13' ) AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4*2).") ORDER BY `time` DESC";
                        $GLOBALS['template']->assign('pve',true);
                        $base = false;
                    }

                    else if($_GET['sort'] == 'arena')
                    {
                        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`type` = '09' OR `type` = '10' OR `type` = '11' ) AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4*2).") ORDER BY `time` DESC";
                        $GLOBALS['template']->assign('arena',true);
                        $base = false;
                    }

                    else if($_GET['sort'] == 'challenges')
                    {
                        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`type` = '07' OR `type` = '08' OR `type` = '12' ) AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4*2).") ORDER BY `time` DESC";
                        $GLOBALS['template']->assign('challenges',true);
                        $base = false;
                    }

                    else 
                        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4*2).") ORDER BY `time` DESC";

                    //running query to get battle histories
                    try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push('','there was an error getting battle history information.', __METHOD__, __FILE__, __LINE__);
                                throw $e;
                            }
                        }
                    }

                    //if nothing was found
                    if($result == '0 rows')
                    {
                        // tell the user they do not have anything in their battle history
                        if($base === true)
                        {
                            $GLOBALS['page']->Message("Your Battle History is empty.", 'Battle History', 'id=2','Return to Profile.');
                            return;
                        }
                        else
                        {
                            $GLOBALS['page']->Message("Your Battle History is empty here.", 'Battle History', 'id=113','Return to Battle History.');
                            return;
                        }
                    }

                    //pulling out teams from census
                    foreach($result as $key => $data)
                    {
                        $result[$key]['teams'] = array();

                        foreach(explode(',',$data['census']) as $users)
                            if($users != '')
                            {
                                $username_team = explode('/',$users);

                                if(!isset($result[$key]['teams'][$username_team[1]]))
                                    $result[$key]['teams'][$username_team[1]] = array();

                                $result[$key]['teams'][$username_team[1]][] = array ($username_team[0], $username_team[2]);

                                if($username_team[0] == $GLOBALS['userdata'][0]['username'])
                                {
                                    $result[$key]['result'] = $username_team[3];
                                }
                            }
                    }

                    //sending data to page
                    $GLOBALS['template']->assign('result',$result);

                    //building page
                    $GLOBALS['template']->assign('contentLoad', './templates/content/Battle/BattleHistory.tpl');
                }

                //if there is a battle history id(aka trying to view a specific battle history)
                else
                {
                    //getting battle history data for the specific battle
                    $select_query = "SELECT * FROM `battle_history` WHERE `id` = ".$_GET['history_id']." AND `census` like '%,".$GLOBALS['userdata'][0]['username']."/%'";

                    try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push('','there was an error getting battle history information.', __METHOD__, __FILE__, __LINE__);
                                throw $e;
                            }
                        }
                    }

                    //if no results then that means the current user was not in that battle
                    if($result == '0 rows')
                    {
                        //get battle history again.
                        $select_query = "SELECT * FROM `battle_history` WHERE `id` = ".$_GET['history_id'];

                        try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
                            catch (Exception $e)
                            {
                                try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                                catch (Exception $e)
                                {
                                    $GLOBALS['DebugTool']->push('','there was an error getting battle history information.', __METHOD__, __FILE__, __LINE__);
                                    throw $e;
                                }
                            }
                        }

                        //if there were results this time
                        if($result != '0 rows')
                        {
                            //process and display summary
                            $result[0]['battle_log'] = unserialize(gzinflate(base64_decode($result[0]['battle_log'])));
                            $battle_log = $result[0]['battle_log'];    

                            $GLOBALS['template']->assign('battle_log', $battle_log);

                            $GLOBALS['template']->assign('hide_changes', true); // because this user was not in the battle

                            $GLOBALS['template']->assign('hide_top', true);

                            $GLOBALS['template']->assign('hide_extra_link', true);

                            $GLOBALS['template']->assign('return_name', 'battle history');

                            $GLOBALS['template']->assign('time', date('jS \of F Y h:i:s A', $result[0]['time']));

                            //adding template
                            $GLOBALS['template']->assign('contentLoad', './templates/content/Battle/BattleSummary.tpl');
                            return;
                        }

                        //if there were still no results then the history does not exist.
                        else
                        {
                            $GLOBALS['page']->Message("There is no battle history here.", 'Battle History', 'id=113','Return to Battle History.');
                            return;
                        }
                    }

                    //if the current user was in the combat
                    //display the summary for the battle.

                    //getting the battle log
                    $result[0]['battle_log'] = unserialize(gzinflate(base64_decode($result[0]['battle_log'])));
                    $battle_log = $result[0]['battle_log'];



                    //getting the changes
                    $changes = '';
                    $set_false = true;
                    foreach(explode('~', $result[0]['changes']) as $changes_for_user)
                    {
                        if($changes_for_user != '')
                        {
                            $temp = explode('|', $changes_for_user);

                            if($temp[0] == $GLOBALS['userdata'][0]['username'])
                            {
                                $result[0]['changes'] = unserialize(gzinflate(base64_decode($temp[1])));
                                $changes = $result[0]['changes'];
                                $set_false = false;
                                break;
                            }
                        }
                    }

                    $team = '';
                    $win_loss = false;

                    //finding the win loss status
                    foreach(explode(',',$result[0]['census']) as $users)
                        if($users != '')
                        {
                            $username_team = explode('/',$users);

                            if($username_team[0] == $GLOBALS['userdata'][0]['username'])
                            {

                                if($username_team[3] == 'flee')
                                    $win_loss = 'flee';
                                else if($username_team[3] == 'win')
                                    $win_loss = true;
                                else
                                    $win_loss = false;

                                $team = $username_team[1];
                            }
                        }

                    //if set false is true purge changes
                    if($set_false)
                        $result[0]['changes'] = false;

                    $owner = array();
                    $owner['name'] = $GLOBALS['userdata'][0]['username'];

                    $owner['team'] = $team;

                    $owner['win_lose'] = $win_loss;

                    if($owner['win_lose'] === 'flee')
                    {
                        $owner['flee'] = true;
                        $owner['win_lose'] = false;
                    }

                    $GLOBALS['template']->assign('owner', $owner);

                    $GLOBALS['template']->assign('village', $GLOBALS['userdata'][0]['village']); //if changes should be hidden

                    //this very compact line calls for the processing of end of combat for the user and collects..
                    //..all the changes made to the user, it then takes those changes and assigns them to the template..
                    //..for viewing on the summary page.
                    $GLOBALS['template']->assign('changes', $changes);
                    $GLOBALS['template']->assign('battle_log', $battle_log);

                    if( ( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== false || $owner['team'] != 'kage' ) && 
		    	    	( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== true  || $owner['team'] != 'challenger' ) && 
                        ( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== false || $owner['team'] != 'leader' ) && 
		    	    	( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== true  || $owner['team'] != 'challenger' ) && 
		    	    	( !isset( $changes['pvp_experience'] )             || $changes['pvp_experience']             == NULL ) && 
		    	    	( !isset( $changes['pvp_streak'] )                 || $changes['pvp_streak']                 == NULL || $changes['pvp_streak'] === false ) && 
		    	    	( !isset( $changes['clan'] )                       || $changes['clan']                       == NULL ) && 
		    	    	( !isset( $changes['anbu'] )                       || $changes['anbu']                       == NULL ) && 
		    	    	( !isset( $changes['village_points'] )             || $changes['village_points']             == NULL ) && 
		    	    	( !isset( $changes['jutsus']['level'] )            || count($changes['jutsus']['level'] )    == 0 ) && 
		    	    	( !isset( $changes['jutsus']['exp'] )              || count($changes['jutsus']['exp'] )      == 0 ) && 
		    	    	( !isset( $changes['exp'] )                        || $changes['exp']                        == NULL ) && 
		    	    	( !isset( $changes['bounty'] )                     || $changes['bounty']                     == NULL ) && 
		    	    	( !isset( $changes['bounty_exp'] )                 || $changes['bounty_exp']                 == NULL ) &&
                        ( !isset( $changes['health_gain'] )                || $changes['health_gain']                == NULL ) &&
                        ( !isset( $changes['gen_pool_gain'] )              || $changes['gen_pool_gain']              == NULL ) &&
                        ( !isset( $changes['ryo_gain'] )                   || $changes['ryo_gain']                   == NULL ) &&
                        ( !isset( $changes['torn'] )                       || $changes['torn']                       == NULL || $changes['torn'] == false ) &&
                        ( !isset( $changes['territory_battle_result'] )    || $changes['territory_battle_result']    == NULL || $changes['territory_battle_result'] != $owner['team'] ) &&
                        ( !isset( $changes['territory_challenge_result'] ) || $changes['territory_challenge_result'] == NULL || $changes['territory_challenge_result'] != $owner['team'] ) &&
                        ( !isset( $changes['money'] )                      || $changes['money']                      == NULL || $changes['money'] <= 0 ) )
                
                        $GLOBALS['template']->assign('no_positive_changes', true);
                    else
                        $GLOBALS['template']->assign('no_positive_changes', false);

                    $durability_warning = false;
                    if(isset( $changes['durability']) )
                    {
                        foreach($changes['durability'] as $name => $amount)
                            if($amount < 50)
                                $durability_warning = true;
                    }

                    if( ( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== true  || $owner['team'] != 'kage' ) && 
		    	    	( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== false || $owner['team'] != 'challenger' ) && 
                        ( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== true  || $owner['team'] != 'leader' ) && 
		    	    	( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== false || $owner['team'] != 'challenger' ) && 
		    	    	( !isset( $changes['jailed'] )                     || $changes['jailed']                     != true ) && 
		    	    	( !isset( $changes['turn_outlaw'] )                || $changes['turn_outlaw']                != true ) && 
		    	    	( !isset( $changes['heal_time'] )                  || $changes['heal_time']                  == NULL ) && 
		    	    	( !isset( $changes['diplomacy'] )                  || $changes['diplomacy']                  == NULL ) && 
		    	    	( !isset( $changes['loyalty'] )                    || $changes['loyalty']                    == NULL ) && 
		    	    	( !isset( $changes['pvp_streak'] )                 || $changes['pvp_streak']                !== false ) && 
		    	    	( !isset( $changes['remove'] )                     || count($changes['remove'])              == 0 ) && 
		    	    	( !isset( $changes['durability'] )                 || count($changes['durability'])          == 0 || !$durability_warning) && 
		    	    	( !isset( $changes['stack']  )                     || $changes['stack']                      == NULL ) && 
                        ( !isset( $changes['torn'])                        || $changes['torn']                       == NULL || $changes['torn'] == true ) &&
                        ( !isset( $changes['territory_battle_result'] )    || $changes['territory_battle_result']    == NULL || $changes['territory_battle_result'] == $owner['team'] ) &&
                        ( !isset( $changes['territory_challenge_result'] ) || $changes['territory_challenge_result'] == NULL || $changes['territory_challenge_result'] == $owner['team'] )  &&
                        ( !isset( $changes['money'] )                      || $changes['money']                      == NULL || $changes['money'] >= 0 ) )

                        $GLOBALS['template']->assign('no_negative_changes', true);
                    else
                        $GLOBALS['template']->assign('no_negative_changes', false);


                    $GLOBALS['template']->assign('hide_extra_link', true);

                    $GLOBALS['template']->assign('return_name', 'battle history');

                    $GLOBALS['template']->assign('time', date('jS \of F Y h:i:s A', $result[0]['time']));

                    //adding template
                    $GLOBALS['template']->assign('contentLoad', './templates/content/Battle/BattleSummary.tpl');
                }
            }

            $GLOBALS['database']->release_lock("battle",$_SESSION['uid'],__METHOD__);
        }
        catch (Exception $e)
        {
            
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);
            $GLOBALS['page']->Message("Locking Error(likely). Please try again. ", 'Combat System', 'id='.$_GET['id'] );
            return;
        }
    }
}

new BattlePageIndex();