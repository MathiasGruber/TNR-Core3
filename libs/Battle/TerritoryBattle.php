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
 *Class: TerritoryBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 12.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');


class TerritoryBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = false;
        $this->no_cfh = true;
        $this->battle_type = '12';
        $this->balanceFlag = false;
        $this->force_database_fallback = true;
        $GLOBALS['template']->assign('battle_type','Territory');
        $GLOBALS['template']->assign('battle_type_pve',false);
        parent::__construct();
    }

    //methods changed by a TerritoryBattle

    function buildingWinUpdate($username)
    {
        $this->recordConclusion($username, 'territory', 'won');

        return ", `battles_won` = `battles_won` + 1";
    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'territory', 'loss');

        return ", `battles_lost` = `battles_lost` + 1";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'territory', 'fled');

        return ", `battles_fled` = `battles_fled` + 1";
    }

    //used to build the link for the end of combat summary
    function getReturnId($hospital)
    {
        if(!$hospital)
            return '30';
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
            return '51';
        else
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        if(!$hospital)
            return 'scout';
        else
            return 'the hospital';
    }

    //this is being overridden to end the territory battle after and hour.
    function endOfTurnInjectionPoint()
    {
        if($this->extra['end_time'] <= $GLOBALS['user']->load_time)
        {
            foreach($this->users as $username => $userdata)
            {
                if( $userdata['team'] == $this->extra['challenger'])
                {
                    $this->users[$username]['health'] = 0;
                    $this->users[$username]['remove'] = true;
                    $this->removeUserFromCombat($username, false);
                    $this->removeUserFromBattle($username);
                }
            }
        }
    }

    //this is being overridden to handle updating the territory battle as to what happened here
    function endOfCombatInjectionPoint()
    {
        //try
        try
        {
            //get lock on challenge
            $GLOBALS['database']->get_lock('battle',$this->extra['id'],__METHOD__);
            
            //start transaction
            $GLOBALS['database']->transaction_start();

            //finding results of battle
            $winner = false;
            $winner_village = '';
            $loser_village = '';

            //for each user that is being removed
            foreach($this->removed_users as $username => $userdata)
            {
                //if this user is not an ai
                if(!isset($userdata['ai']) || $userdata['ai'] !== true)
                {
                    if($userdata['win_lose'] === true)
                    {
                        //finding the winner
                        if($winner === false)
                        {
                            if($userdata['team'] == $this->extra['challenged'])
                                $winner = 'challenged';
                            else if($userdata['team'] == $this->extra['challenger'])
                                $winner = 'challenger';

                            $winner_village = $userdata['village'];
                        }
                        else
                            if($winner_village != $userdata['team'])
                                throw new Exception('team miss match');
                    }
                    else
                    {
                        $loser_village = $userdata['village'];

                        if($userdata['team'] == $this->extra['challenged'])
                        {
                            $winner = 'challenger';
                            $winner_village = $this->extra['challenger'];
                        }
                        else if($userdata['team'] == $this->extra['challenger'])
                        {
                            $winner = 'challenged';
                            $winner_village = $this->extra['challenged'];
                        }
                    }
                }
            }

            if($winner === false)
                $winner = 'challenged';

            //update territory battle information
            //update global message
            $rank = '';
            $nice_rank = '';
            if($GLOBALS['userdata'][0]['rank_id'] == 5)
            {
                $rank = 'e_jounin';
                $nice_rank = 'Elite Jounin';
            }
            else if($GLOBALS['userdata'][0]['rank_id'] == 4)
            {
                $rank = 'jounin';
                $nice_rank = 'Jounin';
            }
            else
            {
                $rank = 'chuunin';
                $nice_rank = 'Chuunin';
            }

            $query = 'UPDATE `territory_challenge`,`users` SET `'.$rank.'_status` = "'.$winner.'", `notifications` = '."CONCAT('id:16;duration:none;text:".functions::store_content('The '.$nice_rank.' territory battle has been won for '.$winner_village.'!').";dismiss:yes;buttons:none;select:none;//',`notifications`)".' WHERE `territory_challenge`.`id` = '.$this->extra['id'].' AND ( `village` = "'.$winner_village.'" OR `village` = "'.$loser_village.'" )';

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

            //update removed_users with result of this battle
            foreach($this->removed_users as $username => $userdata)
            {
                $this->removed_users[$username]['update']['territory_battle_result'] = $this->extra[$winner];
                $this->removed_users[$username]['update']['territory_battle_rank'] = $nice_rank;
            }

            //get territory battle information
            $query = "SELECT `chuunin_status`, `jounin_status`, `e_jounin_status` FROM `territory_challenge` WHERE `id` = '".$this->extra['id']."' LIMIT 1";
            try { if(! $challenge = $GLOBALS['database']->fetch_data($query)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(! $challenge = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $challenge = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error getting territory challenge information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            //check for end of challenge and handle it
            $challenged = 0;
            $challenger = 0;
            $completed = 0;
            foreach($challenge[0] as $status)
            {
                if($status == 'challenger')
                {
                    $challenger++;
                }
                else
                {
                    $challenged++;
                }

                if($status == 'challenger' || $status == 'challenged' || $status == 'pre')
                {
                    $completed++;
                }
            }

            if($completed >= 3)
            {
                $winner = '';

                if($challenged >= $challenger)
                    $winner = $this->extra['challenged'];
                else
                    $winner = $this->extra['challenger'];

                //update removed_users with result of this battle
                foreach($this->removed_users as $username => $userdata)
                {
                    $this->removed_users[$username]['update']['territory_challenge_result'] = $winner;
                    $this->removed_users[$username]['update']['territory_challenge_location'] = $this->extra['location'];
                }

                //update global message
                $query = 'UPDATE `users` SET `notifications` = '."CONCAT('id:19;duration:none;text:".functions::store_content('The territory challenge in '.$this->extra['location'].' has been won for '.$winner.'!').";dismiss:yes;buttons:none;select:none;//',`notifications`)".' WHERE `village` = "'.$this->extra['challenged'].'" OR `village` = "'.$this->extra['challenger'].'"';
                

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

                //do all the shit that needs to be done on conclusion of a battle
                if($winner == $this->extra['challenger'])
                {
                    $query = "UPDATE `locations` SET `owner` = '" . $winner . "' WHERE `name` = '" . $this->extra['location'] . "' LIMIT 1";

                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push('','there was an error updating location owner.', __METHOD__, __FILE__, __LINE__);
                                throw $e;
                            }
                        }
                    }

                    // Create new map
                    echo'create map wont work';

                    var_dump('MAP DATA HAS CHANGED AND THIS IS NO LONGER VALID');

                    $query = "UPDATE `map_data` SET 
	                    `-100`  = REGEXP_REPLACE(`-100`  ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-99`   = REGEXP_REPLACE(`-99`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-98`   = REGEXP_REPLACE(`-98`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-97`   = REGEXP_REPLACE(`-97`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-96`   = REGEXP_REPLACE(`-96`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-95`   = REGEXP_REPLACE(`-95`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-94`   = REGEXP_REPLACE(`-94`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-93`   = REGEXP_REPLACE(`-93`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-92`   = REGEXP_REPLACE(`-92`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-91`   = REGEXP_REPLACE(`-91`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-90`   = REGEXP_REPLACE(`-90`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-89`   = REGEXP_REPLACE(`-89`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-88`   = REGEXP_REPLACE(`-88`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-87`   = REGEXP_REPLACE(`-87`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-86`   = REGEXP_REPLACE(`-86`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-85`   = REGEXP_REPLACE(`-85`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-84`   = REGEXP_REPLACE(`-84`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-83`   = REGEXP_REPLACE(`-83`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-82`   = REGEXP_REPLACE(`-82`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-81`   = REGEXP_REPLACE(`-81`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-80`   = REGEXP_REPLACE(`-80`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-79`   = REGEXP_REPLACE(`-79`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-78`   = REGEXP_REPLACE(`-78`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-77`   = REGEXP_REPLACE(`-77`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-76`   = REGEXP_REPLACE(`-76`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-75`   = REGEXP_REPLACE(`-75`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-74`   = REGEXP_REPLACE(`-74`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-73`   = REGEXP_REPLACE(`-73`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-72`   = REGEXP_REPLACE(`-72`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-71`   = REGEXP_REPLACE(`-71`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-70`   = REGEXP_REPLACE(`-70`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-69`   = REGEXP_REPLACE(`-69`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-68`   = REGEXP_REPLACE(`-68`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-67`   = REGEXP_REPLACE(`-67`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-66`   = REGEXP_REPLACE(`-66`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-65`   = REGEXP_REPLACE(`-65`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-64`   = REGEXP_REPLACE(`-64`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-63`   = REGEXP_REPLACE(`-63`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-62`   = REGEXP_REPLACE(`-62`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-61`   = REGEXP_REPLACE(`-61`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-60`   = REGEXP_REPLACE(`-60`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-59`   = REGEXP_REPLACE(`-59`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-58`   = REGEXP_REPLACE(`-58`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-57`   = REGEXP_REPLACE(`-57`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-56`   = REGEXP_REPLACE(`-56`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-55`   = REGEXP_REPLACE(`-55`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-54`   = REGEXP_REPLACE(`-54`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-53`   = REGEXP_REPLACE(`-53`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-52`   = REGEXP_REPLACE(`-52`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-51`   = REGEXP_REPLACE(`-51`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-50`   = REGEXP_REPLACE(`-50`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-49`   = REGEXP_REPLACE(`-49`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-48`   = REGEXP_REPLACE(`-48`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-47`   = REGEXP_REPLACE(`-47`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-46`   = REGEXP_REPLACE(`-46`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-45`   = REGEXP_REPLACE(`-45`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-44`   = REGEXP_REPLACE(`-44`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-43`   = REGEXP_REPLACE(`-43`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-42`   = REGEXP_REPLACE(`-42`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-41`   = REGEXP_REPLACE(`-41`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-40`   = REGEXP_REPLACE(`-40`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-39`   = REGEXP_REPLACE(`-39`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-38`   = REGEXP_REPLACE(`-38`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-37`   = REGEXP_REPLACE(`-37`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-36`   = REGEXP_REPLACE(`-36`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-35`   = REGEXP_REPLACE(`-35`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-34`   = REGEXP_REPLACE(`-34`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-33`   = REGEXP_REPLACE(`-33`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-32`   = REGEXP_REPLACE(`-32`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-31`   = REGEXP_REPLACE(`-31`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-30`   = REGEXP_REPLACE(`-30`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-29`   = REGEXP_REPLACE(`-29`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-28`   = REGEXP_REPLACE(`-28`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-27`   = REGEXP_REPLACE(`-27`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-26`   = REGEXP_REPLACE(`-26`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-25`   = REGEXP_REPLACE(`-25`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-24`   = REGEXP_REPLACE(`-24`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-23`   = REGEXP_REPLACE(`-23`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-22`   = REGEXP_REPLACE(`-22`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-21`   = REGEXP_REPLACE(`-21`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-20`   = REGEXP_REPLACE(`-20`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-19`   = REGEXP_REPLACE(`-19`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-18`   = REGEXP_REPLACE(`-18`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-17`   = REGEXP_REPLACE(`-17`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-16`   = REGEXP_REPLACE(`-16`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-15`   = REGEXP_REPLACE(`-15`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-14`   = REGEXP_REPLACE(`-14`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-13`   = REGEXP_REPLACE(`-13`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-12`   = REGEXP_REPLACE(`-12`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-11`   = REGEXP_REPLACE(`-11`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-10`   = REGEXP_REPLACE(`-10`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-9`    = REGEXP_REPLACE(`-9`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-8`    = REGEXP_REPLACE(`-8`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-7`    = REGEXP_REPLACE(`-7`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-6`    = REGEXP_REPLACE(`-6`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-5`    = REGEXP_REPLACE(`-5`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-4`    = REGEXP_REPLACE(`-4`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-3`    = REGEXP_REPLACE(`-3`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-2`    = REGEXP_REPLACE(`-2`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `-1`    = REGEXP_REPLACE(`-1`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `0`     = REGEXP_REPLACE(`0`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `1`     = REGEXP_REPLACE(`1`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `2`     = REGEXP_REPLACE(`2`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `3`     = REGEXP_REPLACE(`3`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `4`     = REGEXP_REPLACE(`4`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `5`     = REGEXP_REPLACE(`5`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `6`     = REGEXP_REPLACE(`6`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `7`     = REGEXP_REPLACE(`7`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `8`     = REGEXP_REPLACE(`8`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `9`     = REGEXP_REPLACE(`9`     ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `10`    = REGEXP_REPLACE(`10`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `11`    = REGEXP_REPLACE(`11`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `12`    = REGEXP_REPLACE(`12`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `13`    = REGEXP_REPLACE(`13`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `14`    = REGEXP_REPLACE(`14`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `15`    = REGEXP_REPLACE(`15`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `16`    = REGEXP_REPLACE(`16`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `17`    = REGEXP_REPLACE(`17`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `18`    = REGEXP_REPLACE(`18`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `19`    = REGEXP_REPLACE(`19`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `20`    = REGEXP_REPLACE(`20`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `21`    = REGEXP_REPLACE(`21`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `22`    = REGEXP_REPLACE(`22`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `23`    = REGEXP_REPLACE(`23`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `24`    = REGEXP_REPLACE(`24`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `25`    = REGEXP_REPLACE(`25`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `26`    = REGEXP_REPLACE(`26`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `27`    = REGEXP_REPLACE(`27`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `28`    = REGEXP_REPLACE(`28`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `29`    = REGEXP_REPLACE(`29`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `30`    = REGEXP_REPLACE(`30`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `31`    = REGEXP_REPLACE(`31`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `32`    = REGEXP_REPLACE(`32`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `33`    = REGEXP_REPLACE(`33`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `34`    = REGEXP_REPLACE(`34`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `35`    = REGEXP_REPLACE(`35`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `36`    = REGEXP_REPLACE(`36`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `37`    = REGEXP_REPLACE(`37`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `38`    = REGEXP_REPLACE(`38`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `39`    = REGEXP_REPLACE(`39`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `40`    = REGEXP_REPLACE(`40`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `41`    = REGEXP_REPLACE(`41`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `42`    = REGEXP_REPLACE(`42`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `43`    = REGEXP_REPLACE(`43`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `44`    = REGEXP_REPLACE(`44`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `45`    = REGEXP_REPLACE(`45`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `46`    = REGEXP_REPLACE(`46`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `47`    = REGEXP_REPLACE(`47`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `48`    = REGEXP_REPLACE(`48`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `49`    = REGEXP_REPLACE(`49`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `50`    = REGEXP_REPLACE(`50`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `51`    = REGEXP_REPLACE(`51`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `52`    = REGEXP_REPLACE(`52`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `53`    = REGEXP_REPLACE(`53`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `54`    = REGEXP_REPLACE(`54`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `55`    = REGEXP_REPLACE(`55`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `56`    = REGEXP_REPLACE(`56`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `57`    = REGEXP_REPLACE(`57`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `58`    = REGEXP_REPLACE(`58`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `59`    = REGEXP_REPLACE(`59`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `60`    = REGEXP_REPLACE(`60`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `61`    = REGEXP_REPLACE(`61`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `62`    = REGEXP_REPLACE(`62`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `63`    = REGEXP_REPLACE(`63`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `64`    = REGEXP_REPLACE(`64`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `65`    = REGEXP_REPLACE(`65`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `66`    = REGEXP_REPLACE(`66`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `67`    = REGEXP_REPLACE(`67`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `68`    = REGEXP_REPLACE(`68`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `69`    = REGEXP_REPLACE(`69`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `70`    = REGEXP_REPLACE(`70`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `71`    = REGEXP_REPLACE(`71`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `72`    = REGEXP_REPLACE(`72`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `73`    = REGEXP_REPLACE(`73`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `74`    = REGEXP_REPLACE(`74`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `75`    = REGEXP_REPLACE(`75`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `76`    = REGEXP_REPLACE(`76`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `77`    = REGEXP_REPLACE(`77`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `78`    = REGEXP_REPLACE(`78`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `79`    = REGEXP_REPLACE(`79`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `80`    = REGEXP_REPLACE(`80`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `81`    = REGEXP_REPLACE(`81`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `82`    = REGEXP_REPLACE(`82`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `83`    = REGEXP_REPLACE(`83`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `84`    = REGEXP_REPLACE(`84`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `85`    = REGEXP_REPLACE(`85`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `86`    = REGEXP_REPLACE(`86`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `87`    = REGEXP_REPLACE(`87`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `88`    = REGEXP_REPLACE(`88`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `89`    = REGEXP_REPLACE(`89`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `90`    = REGEXP_REPLACE(`90`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `91`    = REGEXP_REPLACE(`91`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `92`    = REGEXP_REPLACE(`92`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `93`    = REGEXP_REPLACE(`93`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `94`    = REGEXP_REPLACE(`94`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `95`    = REGEXP_REPLACE(`95`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `96`    = REGEXP_REPLACE(`96`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `97`    = REGEXP_REPLACE(`97`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `98`    = REGEXP_REPLACE(`98`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `99`    = REGEXP_REPLACE(`99`    ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",'),
	                    `100`   = REGEXP_REPLACE(`100`   ,'(,". $this->extra['location'] .",)(.+?,|.+?$)', ',". $this->extra['location'] .",".strtolower($winner).",')";

                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update map information'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push('','there was an error updating map information.', __METHOD__, __FILE__, __LINE__);
                                throw $e;
                            }
                        }
                    }
                }

                //delete challenge
                $query = 'DELETE FROM `territory_challenge` WHERE `id` = '.$this->extra['id'];
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed to update user information'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error updating territory challenge information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
            }

            //commit transaction
            $GLOBALS['database']->transaction_commit();

            //release lock on challenge
            $GLOBALS['database']->release_lock('battle',$this->extra['id']);
        }

        //catch
        catch (Exception $e)
        {
            //rollback transaction
            $GLOBALS['database']->transaction_rollback();

            //release lock on chalange
            $GLOBALS['database']->release_lock('battle',$this->extra['id']);

            //throw exception
            throw new Exception('Please try again. '.$e);
        }

    }

}