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
 *Class: MissionBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 06.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class MissionBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = false;
        $this->no_cfh = false;
        $this->battle_type = '06';
        $this->balanceFlag = false;

        if( isset($this->extra) && ($this->extra == '55' || $this->extra == '56' || $this->extra == '65' ))
            $GLOBALS['template']->assign('battle_type','Crime');
        else
            $GLOBALS['template']->assign('battle_type','Mission');

        $GLOBALS['template']->assign('battle_type_pve',true);
        parent::__construct();
    }

    //methods changed by a TravelBattle

    function buildingWinUpdate($username)
    {
        if(isset($this->extra) && ($this->extra == '55' || $this->extra == '56' || $this->extra == '65'))
            $this->recordConclusion($username, 'crime', 'won');
        else
            $this->recordConclusion($username, 'mission', 'won');

        return ", `AIwon` = `AIwon` + 1";
    }

    function buildingLossUpdate($username)
    {
        if(isset($this->extra) && ($this->extra == '55' || $this->extra == '56' || $this->extra == '65'))
            $this->recordConclusion($username, 'crime', 'loss');
        else
            $this->recordConclusion($username, 'mission', 'loss');;

        return ", `AIlost` = `AIlost` + 1";
    }

    function buildingFleeUpdate($username)
    {
        if(isset($this->extra) && ($this->extra == '55' || $this->extra == '56' || $this->extra == '65'))
            $this->recordConclusion($username, 'crime', 'fled');
        else
            $this->recordConclusion($username, 'mission', 'fled');

        return ", `AIfled` = `AIfled` + 1";
    }

    //used to build the link for the end of combat summary
    function getReturnId($hospital)
    {
        if(!$hospital)
        {
            if($this->extra === false)
                return '30';
            else if($this->extra == 32)
                return '32';

            else if($this->extra == 40)
                return '40';

            else if($this->extra == 54)
                return '54';

            else if($this->extra == 62)
                return '62';

            else if($this->extra == 77)
                return '77';

            else if($this->extra == 55)
                return '55';

            else if($this->extra == 56)
                return '56';

            else if($this->extra == 65)
                return '65';
        }
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
            return '51';
        else
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        if(!$hospital)
        {
            if($this->extra === false)
                return 'scout';

            else if($this->extra == 32)
                return 'Mission: D';

            else if($this->extra == 40)
                return 'Mission: C';

            else if($this->extra == 54)
                return 'Mission: B';

            else if($this->extra == 62)
                return 'Mission: A';

            else if($this->extra == 77)
                return 'Mission: S';

            else if($this->extra == 55)
                return 'Crime: C';

            else if($this->extra == 56)
                return 'Crime: B';

            else if($this->extra == 65)
                return 'Crime: A';
        }
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