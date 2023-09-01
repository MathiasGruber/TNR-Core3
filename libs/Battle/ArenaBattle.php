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
 *Class: ArenaBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 09.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class ArenaBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = true;
        $this->no_cfh = true;
        $this->battle_type = '09';
        $this->balanceFlag = false;
        $GLOBALS['template']->assign('battle_type','Arena');
        $GLOBALS['template']->assign('battle_type_pve',true);
        parent::__construct();
    }

    //methods changed by a ArenaBattle

    //adding ryo gain to win from arena battle
    function buildingWinUpdate($username)
    {
        $this->recordConclusion($username, 'arena', 'won');

        $ryo_gain = ($this->removed_users[$username]['rank'] * 2) ** ( 2 + (random_int(0,100)/100) );

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedArenaAll") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $ryo_gain *= round($event['data'] / 100,2);
            }
        }

        $this->return_data['ryo_gain'] = round($ryo_gain);

        $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $ryo_gain));

        return ", `AIwon` = `AIwon` + 1".
               ", `money` = `money` + " . round($ryo_gain) .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+30)."'";

    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'arena', 'loss');

        return ", `AIlost` = `AIlost` + 1" .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+300)."'";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'arena', 'fled');

        return ", `AIfled` = `AIfled` + 1" .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+300)."'";
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

    //used to overridde the exp update method so that event battles dont return asmuch exp
    function buildingExpUpdate($username)
    {
        //if the user gained no exp give them 1 exp
        if(!isset($this->removed_users[$username]['update']['exp']))
            $this->removed_users[$username]['update']['exp'] = 0;

        //if the user gained 0 or less exp give them 1 exp
        if($this->removed_users[$username]['update']['exp'] < 1)
            $this->removed_users[$username]['update']['exp'] = 0;

        $value = intval( ($this->removed_users[$username]['update']['exp'] / 4) / (1 + (random_int(0,100) / 100) ) );

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedArenaAll") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $value *= round($event['data'] / 100,2);
            }
        }

        if($value >= 1)
        {
            $this->return_data['exp'] = $value;

            //building update.
            $GLOBALS['Events']->acceptEvent('experience', array('new' => $GLOBALS['userdata'][0]['experience'] + $value, 'old' => $GLOBALS['userdata'][0]['experience']));
            return ", `experience` = `experience` + '".$value."'";
        }
    }

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