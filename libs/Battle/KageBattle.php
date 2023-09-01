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
 *Class: KageBattle
 *  this class exists purely to make changes to the battle system
 *  needed by this battle type.
 *  the changes are achieved through overwritting methods.
 *
 *  id for this battle type is 07.
 */

require_once(Data::$absSvrPath.'/libs/Battle/BattlePage.php');

class KageBattle extends BattlePage
{
    //just passing the construct call up
    function __construct()
    {
        $this->no_flee = true;
        $this->no_cfh = true;
        $this->battle_type = '07';
        $this->balanceFlag = true;
        $GLOBALS['template']->assign('battle_type','Kage');
        $GLOBALS['template']->assign('battle_type_pve',false);
        parent::__construct();
    }

    //methods changed by a TravelBattle
    function buildingWinUpdate($username)
    {
        $this->recordConclusion($username, 'kage', 'won');

        return ", `battles_won` = `battles_won` + 1";
    }

    function buildingLossUpdate($username)
    {
        $this->recordConclusion($username, 'kage', 'loss');

        return ", `battles_lost` = `battles_lost` + 1";
    }

    function buildingFleeUpdate($username)
    {
        $this->recordConclusion($username, 'kage', 'fled');

        return ", `battles_fled` = `battles_fled` + 1";
    }

    //used to build the link for the end of combat summary
    function getReturnId($hospital)
    {
        if(!$hospital)
            return '2';
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
            return '51';
        else
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        if(!$hospital)
            return 'your profile';
        else
            return 'the hospital';
    }

    //need to override the status updating methods for kage battles.
    //will user team name to know if this is the challenger or not


    function buildingLoyaltyUpdateForTraitor($username){return '';}
    function buildingDiplomacyUpdateForTraitor($username){return '';}
    function buildingDiplomacyUpdateForNonTraitor($username){return '';}
    function checkDiplomacyForOutlawConversion($username){return '';}
    function buildingVillagePointsUpdateForWinAndNonTraitor($username){return '';}

    function buildingPoolUpdate($username)
    {
        $health  = $this->removed_users[$username][ parent::HEALTH];
        $stamina = $this->removed_users[$username][parent::STAMINA];
        $chakra  = $this->removed_users[$username][ parent::CHAKRA];

        if( $health  > $this->removed_users[$username][parent::HEALTHMAX] )
            $health  = $this->removed_users[$username][ parent::HEALTHMAX];

        if ($health < 1 && $this->removed_users[$username]['team'] != 'kage')
            $health = 1;

        if( $stamina > $this->removed_users[$username][parent::STAMINAMAX] )
            $stamina = $this->removed_users[$username][parent::STAMINAMAX];

        if( $chakra  > $this->removed_users[$username][parent::CHAKRAMAX] )
            $chakra  = $this->removed_users[$username][ parent::CHAKRAMAX];

        if($GLOBALS['userdata'][0]['cur_health'] != $health)
            $GLOBALS['Events']->acceptEvent('stats_cur_health', array( 'new'=>$health, 'old'=>$GLOBALS['userdata'][0]['cur_health']));

        if($GLOBALS['userdata'][0]['cur_sta'] != $stamina)
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array( 'new'=>$stamina, 'old'=>$GLOBALS['userdata'][0]['cur_sta']));

        if($GLOBALS['userdata'][0]['cur_cha'] != $chakra)
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array( 'new'=>$chakra, 'old'=>$GLOBALS['userdata'][0]['cur_cha']));

        return  ", `users_statistics`.`cur_health` = ".$health.
                ", `users_statistics`.`cur_sta` = ".$stamina.
                ", `users_statistics`.`cur_cha` = ".$chakra;
    }


    function buildingStatusUpdateForWin($username)
    {
        $this->return_data['hospital'] = false;
        $temp = ", `status` = 'awake'";
        $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status']));
        $GLOBALS['userdata'][0]['status'] = 'awake';
        $GLOBALS['template']->assign('userStatus', 'awake');

        // check for flee
        if( isset($this->removed_users[$username]['flee']) && $this->removed_users[$username]['flee'] === true)
            //mark flee
            $temp .= $this->buildingFleeUpdate($username);

        // else
        else
        {
            //mark win
            $temp .= $this->buildingWinUpdate($username);

            //if this is the challenger and they defeated the kage
            if($this->removed_users[$username]['team'] == 'challenger')
            {
                $this->return_data['kage_replaced'] = true;

                // Update the kage of the village
                $query = "UPDATE `villages` SET `leader` = '" . $username . "' WHERE `name` = '" . $this->removed_users[$username]['village'] . "' LIMIT 1";
                $GLOBALS['Events']->acceptEvent('kage', array('data'=>$this->removed_users[$username]['village'] ));

                //sending query to database to update kage of village
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

                //set kage title
                if($this->removed_users[$username]['village'] == "Konoki")
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Morikage' WHERE `uid` = ".$this->removed_users[$username]['uid']);
                else if($this->removed_users[$username]['village'] == "Samui")
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Kusakage' WHERE `uid` = ".$this->removed_users[$username]['uid']);
                else if($this->removed_users[$username]['village'] == "Shine")
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Sunakage' WHERE `uid` = ".$this->removed_users[$username]['uid']);
                else if($this->removed_users[$username]['village'] == "Shroud")
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Suikage' WHERE `uid` =  ".$this->removed_users[$username]['uid']);
                else if($this->removed_users[$username]['village'] == "Silence")
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Yamakage' WHERE `uid` = ".$this->removed_users[$username]['uid']);
                else if($this->removed_users[$username]['village'] == "Syndicate")
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Warlord' WHERE `uid` =  ".$this->removed_users[$username]['uid']);

            }
            else
                $this->return_data['kage_replaced'] = false;
        }

        return $temp;
    }







    function buildingStatusUpdateForLoss($username)
    {
        //if this is the challenger and they lost to the kage
        if($this->removed_users[$username]['team'] == 'challenger')
        {
            $this->return_data['kage_replaced'] = false;
            $this->return_data['jailed'] = true;
            $this->return_data['hospital'] = false;
            $this->return_data['loyalty'] = floor($this->removed_users[$username]['vil_loyal_pts'] * 0.25);
            $GLOBALS['Events']->acceptEvent( 'status', array( 'new'=>'jailed', 'old'=>$GLOBALS['userdata'][0]['status']));
            $GLOBALS['userdata'][0]['status'] = 'jailed';
            $GLOBALS['template']->assign('userStatus', 'jailed');
            $GLOBALS['userdata'][0]['jail_timer'] = ($GLOBALS['user']->load_time+12*3600);

            $GLOBALS['Events']->acceptEvent('village_loyalty_loss', array('new'=>ceil($this->removed_users[$username]['vil_loyal_pts'] * 0.75), 'old'=>$this->removed_users[$username]['vil_loyal_pts'] ));

            //setting user status to jailed
            //setting user jail timer to 12 hours from now
            //reducing user loyalty points by 25%
            return ", `users`.`status` = 'jailed',
                      `users_timer`.`jail_timer` = '".($GLOBALS['user']->load_time+12*3600)."',
                      `vil_loyal_pts` = ".ceil($this->removed_users[$username]['vil_loyal_pts'] * 0.75);
        }

        else
        {
            $this->return_data['kage_replaced'] = true;
            $GLOBALS['Events']->acceptEvent('kage', array('data'=>'removed' ));

            //if the user is not Syndicate
            //set them to hospitalized and send them to their village
            if( $this->removed_users[$username]['village'] != 'Syndicate' )
            {
                $this->return_data['hospital'] = true;

                //mark loss
                $temp = $this->buildingLossUpdate($username);

                //set message for non Syndicate hospitalized
                $GLOBALS['Events']->acceptEvent( 'status', array( 'new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status']));
                $GLOBALS['userdata'][0]['status'] = 'hospitalized';
                $GLOBALS['template']->assign('userStatus', 'hospitalized');

                $hospital = new hospitalFunctions();

                $healTime = $hospital->calculateHealtime();

                $this->return_data['heal_time'] = ceil($healTime/60);

                return ", `users_statistics`.`rank` = '".functions::getRank($this->removed_users[$username]['rank'], $this->removed_users[$username]['village'])."',
                          `users`.`status` = 'hospitalized',
                          `users`.`latitude` = `villages`.`latitude`,
                          `users`.`longitude` = `villages`.`longitude`,
                          `users`.`location` = `villages`.`name`,
                          `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime);
            }

            //if the user is Syndicate
            //set them to hospitalized and move them from their current location if
            //they are currently in a village
            else
            {
                $this->return_data['hospital'] = true;

                //mark loss
                $temp = $this->buildingLossUpdate($username);

                //check to see if the user is currently in a village
                //if so make sure that they will not be in a village when hospitalized.
                //set status to hospitalized and location to disoriented
                if( in_array($this->removed_users[$username]['location'], array_keys( $this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
                {
                    $new_latitude = $this->removed_users[$username]['latitude'];
                    $new_longitude = $this->removed_users[$username]['longitude'];

                    while( $new_latitude == $this->removed_users[$username]['latitude'] && $new_longitude == $this->removed_users[$username]['longitude'] )
                    {
                        $new_latitude = $this->removed_users[$username]['latitude'] + random_int(-2,2);
                        $new_longitude = $this->removed_users[$username]['longitude'] + random_int(-2,2);
                    }

                    //set message for Syndicate hospitalized
                    $GLOBALS['Events']->acceptEvent( 'status', array( 'new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status']));
                    $GLOBALS['userdata'][0]['status'] = 'hospitalized';
                    $GLOBALS['template']->assign('userStatus', 'hospitalized');
                    $GLOBALS['userdata'][0]['latitude'] = $new_latitude;
                    $GLOBALS['userdata'][0]['longitude'] = $new_longitude;
                    $GLOBALS['userdata'][0]['location'] = 'Disoriented';

                    $hospital = new hospitalFunctions();

                    $healTime = $hospital->calculateHealtime();

                    $this->return_data['heal_time'] = ceil($healTime/60);

                    return  ", `users_statistics`.`rank` = '".functions::getRank($this->removed_users[$username]['rank'], $this->removed_users[$username]['village'])."',
                               `users`.`status` = 'hospitalized',
                               `users`.`latitude` = ".$new_latitude.",
                               `users`.`longitude` = ".$new_longitude.",
                               `users`.`location` = 'Disoriented',
                               `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime);
                }

                //if the Syndicate user is not currently in a village
                //just set status to hospitalized and location to disoriented
                else
                {
                    //set message for Syndicate hospitalized
                    $GLOBALS['Events']->acceptEvent( 'status', array( 'new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status']));
                    $GLOBALS['userdata'][0]['status'] = 'hospitalized';
                    $GLOBALS['template']->assign('userStatus', 'hospitalized');
                    $GLOBALS['userdata'][0]['location'] = 'Disoriented';

                    $hospital = new hospitalFunctions();

                    $healTime = $hospital->calculateHealtime();

                    $this->return_data['heal_time'] = ceil($healTime/60);

                    return  ", `users_statistics`.`rank` = '".functions::getRank($this->removed_users[$username]['rank'], $this->removed_users[$username]['village'])."',
                               `users`.`status` = 'hospitalized',
                               `users`.`location` = 'Disoriented',
                               `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime);
                }
            }
        }
    }
}