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
 *Class: PvpBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 04.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class PvpBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = false;
        $this->no_cfh = false;
        $this->battle_type = '04';
        $this->balanceFlag = true;
        $GLOBALS['template']->assign('battle_type','PvP');
        $GLOBALS['template']->assign('battle_type_pve',false);
        parent::__construct();
    }

    //methods changed by a TravelBattle
    function buildingWinUpdate($username)
    {
        $this->recordConclusion($username, 'pvp', 'won');

        return ", `battles_won` = `battles_won` + 1";
    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'pvp', 'loss');

        return ", `battles_lost` = `battles_lost` + 1";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'pvp', 'fled');

        //if user is in a village
        if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false)
        {
            //if this is the users village
            if($this->village_locations[$this->removed_users[$username]['location']] == $this->removed_users[$username]['village'])
            {
                return ", `battles_fled` = `battles_fled` + 1";
            }
            else
            {
                $lat = 0;
                $lon = 0;

                //randomly selecting lat or lon to be either -1 or 1.
                if(random_int(0,1) == 1) 
                    if(random_int(0,1) == 1) $lat = 1; else $lat = -1;
                else 
                    if(random_int(0,1) == 1) $lon = 1; else $lon = -1;

                return ", `battles_fled` = `battles_fled` + 1,
                          `users`.`latitude` = `users`.`latitude` + ".$lat.",
                          `users`.`longitude` = `users`.`longitude` + ".$lon;
            }
        }
        else
            return ", `battles_fled` = `battles_fled` + 1";
            
    }

    //used to build the link for the end of combat summary
    function getReturnId($hospital)
    {
        if(!$hospital)
            return '50';
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
            return '51';
        else
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        if(!$hospital)
            return 'the combat page';
        else
            return 'the hospital';
    }
}