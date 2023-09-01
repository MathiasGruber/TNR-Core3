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
 *Class: MirrorBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 10.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class MirrorBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = true;
        $this->no_cfh = true;
        $this->battle_type = '10';
        $this->balanceFlag = false;
        $GLOBALS['template']->assign('battle_type','Mirror');
        $GLOBALS['template']->assign('battle_type_pve',true);
        parent::__construct();
    }

    //methods changed by a MirrorBattle

    function buildingWinUpdate($username)
    {

        $this->recordConclusion($username, 'mirror', 'won');

        $health_gain   = round($this->removed_users[$username]['rank'] * (( 5.472 + ( random_int(0,4928) / 1000 ) ) ** 1.5 ) , 2 );
        $gen_pool_gain = round($this->removed_users[$username]['rank'] * (( 1.872 + ( random_int(0,1684) / 1000)  ) ** 1.5 ) , 2 );

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedArenaAll") ){
            if( isset( $event['data']) && is_numeric( $event['data'] ) ){
                $health_gain *= round($event['data'] / 100,2);
                $gen_pool_gain *= round($event['data'] / 100,2);
            }
        }

        $this->return_data['health_gain'] = round($health_gain, 2);
        $this->return_data['gen_pool_gain'] = round($gen_pool_gain, 2);

        $GLOBALS['Events']->acceptEvent('stats_max_health'   , array('new'=> $this->removed_users[$username]['healthMax']    + $health_gain,   'old'=> $this->removed_users[$username]['healthMax']    ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha'      , array('new'=> $this->removed_users[$username]['chakraMax']    + $gen_pool_gain, 'old'=> $this->removed_users[$username]['chakraMax']    ));
        $GLOBALS['Events']->acceptEvent('stats_max_sta'      , array('new'=> $this->removed_users[$username]['staminaMax']   + $gen_pool_gain, 'old'=> $this->removed_users[$username]['staminaMax']   ));
        $GLOBALS['Events']->acceptEvent('stats_strength'     , array('new'=> $this->removed_users[$username]['strength']     + $gen_pool_gain, 'old'=> $this->removed_users[$username]['strength']     ));
        $GLOBALS['Events']->acceptEvent('stats_intelligence' , array('new'=> $this->removed_users[$username]['intelligence'] + $gen_pool_gain, 'old'=> $this->removed_users[$username]['intelligence'] ));
        $GLOBALS['Events']->acceptEvent('stats_willpower'    , array('new'=> $this->removed_users[$username]['willpower']    + $gen_pool_gain, 'old'=> $this->removed_users[$username]['willpower']    ));
        $GLOBALS['Events']->acceptEvent('stats_speed'        , array('new'=> $this->removed_users[$username]['speed']        + $gen_pool_gain, 'old'=> $this->removed_users[$username]['speed']        ));

        return  ", `AIwon` = `AIwon` + 1" .
                ", `arena_cooldown` = '".($GLOBALS['user']->load_time+3600)."'".
                ", `max_health` = `max_health` + '".$health_gain."'".
                ", `max_cha` = `max_cha` + '".$gen_pool_gain."'".
                ", `max_sta` = `max_sta` + '".$gen_pool_gain."'".
                ", `strength` = `strength` + '".$gen_pool_gain."'".
                ", `intelligence` = `intelligence` + '".$gen_pool_gain."'".
                ", `willpower` = `willpower` + '".$gen_pool_gain."'".
                ", `speed` = `speed` + '".$gen_pool_gain."'";
    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'mirror', 'loss');

        return ", `AIlost` = `AIlost` + 1" .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+3600)."'";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'mirror', 'fled');

        return ", `AIfled` = `AIfled` + 1" .
               ", `arena_cooldown` = '".($GLOBALS['user']->load_time+3600)."'";
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

        if($value >= 1)
        {
            $this->return_data['exp'] = $value;

            //building update.
            $GLOBALS['Events']->acceptEvent('experience', array('new'=>$GLOBALS['userdata'][0]['experience'] + $value, 'old'=>$GLOBALS['userdata'][0]['experience'] ));
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