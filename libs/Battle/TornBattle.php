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
 *Class: TornBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 11.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class TornBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = true;
        $this->no_cfh = true;
        $this->battle_type = '11';
        $this->balanceFlag = false;
        $GLOBALS['template']->assign('battle_type','Torn');
        $GLOBALS['template']->assign('battle_type_pve',true);
        parent::__construct();
    }

    //methods changed by a TornBattle

    function buildingWinUpdate($username)
    {
        $this->recordConclusion($username, 'torn', 'won');

        return ", `AIwon` = `AIwon` + 1" .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+1800)."'";
    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'torn', 'loss');

        if(is_numeric($this->extra))
            return ", `AIwon` = `AIwon` + ".$this->extra .
                   ", `arena_cooldown` = '".($GLOBALS['user']->load_time+1800)."'";
        else
            return ", `arena_cooldown` = '".($GLOBALS['user']->load_time+1800)."'";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'torn', 'fled');

        return ", `AIfled` = `AIfled` + 1" .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+1800)."'";
    }

    //used to build the link for the end of combat summary
    function getReturnId($hospital)
    {
        if(!$hospital)
            return '35';
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
            return '51';
        else
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        if(!$hospital)
            return 'the Battle Arena';
        else
            return 'the hospital';
    }

    //override remove user from combat and update add the next opponent here.
    //also update torn record here and in the database.
    public function removeUserFromCombat($username, $status, $recursion = false)
    {
        //if an ai has died add in the next one and update the players record.
        if(isset($this->users[$username]['ai']) && $this->users[$username]['ai'] === true && count($this->users) > 1)
        {
            if($this->users[$username]['team'] != 'Konoki' && 
               $this->users[$username]['team'] != 'Samui' && 
               $this->users[$username]['team'] != 'Silence' && 
               $this->users[$username]['team'] != 'Shroud' && 
               $this->users[$username]['team'] != 'Shine' && 
               $this->users[$username]['team'] != 'Syndicate')
            {
                //updating counter for record
                if(is_numeric($this->extra))
                    $this->extra++;
                else
                    $this->extra = 1;

                $ai = '0 rows';
                while($ai == '0 rows')
                    $ai = $GLOBALS['database']->fetch_data("SELECT `id` FROM `ai` WHERE `type` = 'torn_battle' ORDER BY RAND() LIMIT 1");

                $ais = $this->addAI($ai[0]['id'],'Arena');
                $copyStatsTag = $this->parseTags('copyStats:(value>'.(random_int(25,125)/100).';)');
                $copyStatsTag[0]->owner = $ais[0];
                $copyStatsTag[0]->target = $GLOBALS['userdata'][0]['username'];
                $this->copyStats($copyStatsTag[0]);
                $this->users[$ais[0]]['health'] = $this->users[$ais[0]]['healthMax'];

                if(isset($this->users[$ais[0]]['chakra']) && isset($this->users[$ais[0]]['stamina']))
                {
                $this->users[$ais[0]]['chakra'] = $this->users[$ais[0]]['chakraMax'];
                $this->users[$ais[0]]['stamina'] = $this->users[$ais[0]]['staminaMax'];
                }

                //extending max uses
                if( $this->extra % 5 == 0)
                {

                    //for items
                    //$this->users[$username]['items'][$item['id']]
                    if(isset($this->users[$GLOBALS['userdata'][0]['username']]['items']))
                    {
                        foreach($this->users[$GLOBALS['userdata'][0]['username']]['items'] as $item_key => $item_data)
                        {
                            if(!isset($item_data['start_max_uses']))
                                $this->users[$GLOBALS['userdata'][0]['username']]['items'][$item_key]['start_max_uses'] = $item_data['max_uses'];
                            
                            if($this->users[$GLOBALS['userdata'][0]['username']]['items'][$item_key]['max_uses'] > 0)
                                $this->users[$GLOBALS['userdata'][0]['username']]['items'][$item_key]['max_uses'] += $this->users[$GLOBALS['userdata'][0]['username']][$item_key]['items']['start_max_uses'];
                        }
                    }

                    //for weapons
                    //$this->users[$username]['equipment']
                    if(isset($this->users[$GLOBALS['userdata'][0]['username']]['equipment']))
                    {
                        foreach($this->users[$GLOBALS['userdata'][0]['username']]['equipment'] as $equipment_key => $equipment_data)
                        {
                            if(!isset($equipment_data['start_max_uses']))
                                $this->users[$GLOBALS['userdata'][0]['username']]['equipment'][$equipment_key]['start_max_uses'] = $equipment_data['max_uses'];

                            if($this->users[$GLOBALS['userdata'][0]['username']]['equipment'][$equipment_key]['max_uses'] > 0)
                                $this->users[$GLOBALS['userdata'][0]['username']]['equipment'][$equipment_key]['max_uses'] += $this->users[$GLOBALS['userdata'][0]['username']]['equipment'][$equipment_key]['start_max_uses'];
                        }
                    }

                    //for jutsu
                    //$this->users[$username]['jutsus'][$jutsu['jid']]
                    if(isset($this->users[$GLOBALS['userdata'][0]['username']]['jutsus']))
                    {
                        foreach($this->users[$GLOBALS['userdata'][0]['username']]['jutsus'] as $jutsu_key => $jutsu_data)
                        {
                            if(!isset($jutsu_data['start_max_uses']))
                                $this->users[$GLOBALS['userdata'][0]['username']]['jutsus'][$jutsu_key]['start_max_uses'] = $jutsu_data['max_uses'];

                            if($this->users[$GLOBALS['userdata'][0]['username']]['jutsus'][$jutsu_key]['max_uses'] > 0)
                                $this->users[$GLOBALS['userdata'][0]['username']]['jutsus'][$jutsu_key]['max_uses'] += $this->users[$GLOBALS['userdata'][0]['username']]['jutsus'][$jutsu_key]['start_max_uses'];
                        }
                    }
                    

                }

                $this->updateDR_SR($ais[0]);
                $this->users[$ais[0]]['DSR'] = $this->findDSR($ais[0]);
                //$this->dude[0]['trait'] = "SCOPY:copy:".random_int(25,125).":1:1:1";
            }
        }
        else if(!isset($this->users[$username]['update']['torn']))
        {
            $record = '0 rows';
            while($record == '0 rows')
                $record = $GLOBALS['database']->fetch_data("SELECT `torn_record` FROM `users_missions` WHERE `userid` = ".$_SESSION['uid']);

            if($record[0]['torn_record'] < $this->extra && is_numeric($this->extra))
            {
                $GLOBALS['database']->execute_query('UPDATE `users_missions` SET `torn_record` = '.$this->extra.' WHERE `userid` = '.$_SESSION['uid']);
                $this->users[$username]['update']['torn'] = true;
                $this->users[$username]['update']['torn_record'] = $record[0]['torn_record'];
                $this->users[$username]['update']['torn_attempt'] = $this->extra;
            }
            else
            {
                $this->users[$username]['update']['torn'] = false;
                $this->users[$username]['update']['torn_record'] = $record[0]['torn_record'];
                $this->users[$username]['update']['torn_attempt'] = $this->extra;
            }
        }

        $this->users[$username]['win_lose'] = $status;

        $team = $this->users[$username][self::TEAM];

        //handling summon stuff
        //if this user has summons marked as linked
        if(isset($this->users[$username]['summons']))
        {
            foreach($this->users[$username]['summons'] as $key => $summon_name)
            {
                $this->removeUserFromCombat($summon_name, $status, true);
            }
        }

        //if this is a summon that is linked to a user remove this summon from that user's list of summons.
        if(isset($this->users[$username]['summoned']) && !$recursion)
        {
            unset($this->users[$this->users[$username]['summoned']]['summons'][$username]);
        }

        //going through all users
        foreach($this->users as $user_key => $user)
        {
            //going through all tags
            foreach($user[self::TAGS] as $tags_key => $tag)
                if($tag->owner == $username && $tag->persistAfterDeath == false)
                {
                    unset($this->users[$user_key][self::TAGS][$tags_key]);
                    unset($this->users[$user_key][self::TAGSINEFFECT][$tags_key]);
                }

            //going through all tags
            foreach($this->run_ready_array as $tags_key => $tag)
                if($tag->owner == $username && $tag->persistAfterDeath == false)
                {
                    unset($this->run_ready_array[$tags_key]);
                }
        }
    }


    //used to overridde the exp update method so that event battles dont return exp
    function buildingExpUpdate($username)
    { return ''; }

    //used to override the clan update method so that this battle type does not rewards clan stuffs.
    function buildingClanUpdateForWinAndNonTraitor($username)
    { return ''; }

    //used to override the clan update method so that this battle type does not reward clan stuffs.
    function buildingClanUpdateForLoss($username)
    { return ''; }

    //used to override the anbu update method so that this battle type does not reward anbu stuffs.
    function buildingAnbuUpdateForWinAndNonTraitor($username)
    { return ''; }

    //used to override the anbu update method so that this battle type does not reward anbu stuffs.
    function buildingAnbuUpdateForLoss($username)
    { return ''; }

    //used to override the pvp exp update method so that this battle type does not reward pvp exp stuffs.
    function buildingPvpExpUpdateForWinAndNonTraitor($username)
    { return ''; }
    
    //used to override the pvp exp for loss method so that this battle type does not punish pvp stuffs.
    function buildingPvpExpUpdateForLoss($username)
    { return ''; }

    //used to override the village points update method so that this battle type does not reward village points stuffs.
    function buildingVillagePointsUpdateForWinAndNonTraitor($username)
    { return ''; }

    //used to override the village points update method so that this battle type does not reward village points stuffs.
    function buildingVillagePointsUpdateForLoss($username)
    { return ''; }
}