<?php
/* ============== LICENSE INFO START ==============
 * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
 *buildingLossUpdate($username)
 * This file is part of the project www.TheNinja-RPG.com.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Studie-Tech ApS.
 * ============== LICENSE INFO END ============== */
?>
<?php

/*Author: Tyler Smith
 *Class: SparBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 03.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class SparBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = false;
        $this->no_cfh = false;
        $this->battle_type = '03';
        $this->balanceFlag = false;
        $GLOBALS['template']->assign('battle_type','Spar');
        $GLOBALS['template']->assign('battle_type_pve',false);
        parent::__construct();
    }

    //methods changed by a TravelBattle
    function buildingWinUpdate($username)
    {
        $this->recordConclusion($username, 'spar', 'won');

        return "";
    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'spar', 'loss');

        return "";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'spar', 'fled');

        return "";
    }

    function buildingMoneyUpdate($username)
    {
        return "";
    }

    //used to build the link for the end of combat summary
    function getReturnId($hospital)
    {
        if(!$hospital)
            return '43';
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
            return '51';
        else
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        if(!$hospital)
            return 'spar';
        else
            return 'the hospital';
    }

    //overridding this to make spar only update user status to awake.
    //
    function processEndOfCombatForUser($username)
    {
        try
        {
            $GLOBALS['database']->transaction_start();

            if( isset($this->removed_users[$username]['starting_status']) && $this->removed_users[$username]['starting_status'] !== false)
            {
                $query = "UPDATE `users` SET `battle_id` = 0, `status` = '".$this->removed_users[$username]['starting_status']."', `cfh` = '' WHERE `username` = '".$username."'";
                $GLOBALS['Events']->acceptEvent('status', array('new'=>$this->removed_users[$username]['starting_status'], 'old'=>$GLOBALS['userdata'][0]['status'] ));
                $GLOBALS['userdata'][0]['status'] = $this->removed_users[$username]['starting_status'];
                $GLOBALS['template']->assign('userStatus', $this->removed_users[$username]['starting_status']);
            }
            else
            {
                $query = "UPDATE `users` SET `battle_id` = 0, `status` = 'awake', `cfh` = '' WHERE `username` = '".$username."'";
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                $GLOBALS['userdata'][0]['status'] = 'awake';
                $GLOBALS['template']->assign('userStatus', 'awake');
            }

            //updates for win
            if($this->removed_users[$username]['win_lose'] === true && $this->removed_users[$username]['health'] > 0 )
            {
                $this->buildingStatusUpdateForWin($username);
            }
            else
            {
                //building status update information for query
                if( isset($this->removed_users[$username]['flee']) && $this->removed_users[$username]['flee'] == true)
                    $this->buildingStatusUpdateForWin($username); //special case for fleeing user.
                else
                    $this->buildingStatusUpdateForLoss($username);
            }

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
                        $GLOBALS['DebugTool']->push($query,'there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                    }
                }
            }

            $GLOBALS['database']->transaction_commit();
            return array('hospital'=>false, 'hide_changes'=>true);
        }
        catch (Exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            $GLOBALS['userdata'][0]['status'] = 'exiting_combat';
            $GLOBALS['template']->assign('userStatus', 'exiting_combat');
            //$GLOBALS['DebugTool']->push('','there was an error updating user information. please try again.', __METHOD__, __FILE__, __LINE__);
            throw $e;
            return false;
        }
    }
}