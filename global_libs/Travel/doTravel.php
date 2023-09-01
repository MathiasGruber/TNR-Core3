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
 *Class: DoTravel
 *  this class is called on to handle travel events
 *
 */

class DoTravel
{
    function __construct(){ }

    function startPostMove($move, $new_x = false, $new_y = false, $new_map = false)
    {
        $abort = false;
        $x_low_bound = -125;
        $y_low_bound = -100;
        $x_high_bound = 125;
        $y_high_bound = 100;
        //checking user status
        if( ($GLOBALS['userdata'][0]['status'] == 'awake' || $move == 'force') && (!$GLOBALS['userdata'][0]['over_encumbered'] || in_array($GLOBALS['userdata'][0]['user_rank'], array('Admin'), true)) )
        {
            $x = 0;
            $y = 0;

            if($move == "N")
                $y = 1;

            else if ($move == "S")
                $y = -1;

            else if ($move == "E")
                $x = 1;

            else if ($move == "W")
                $x = -1;

            else if ($move == "NW")
            {
                $y = 1;
                $x = -1;
            }

            else if ($move == "NE")
            {
                $y = 1;
                $x = 1;
            }

            else if ($move == "SW")
            {
                $y = -1;
                $x = -1;
            }

            else if ($move == "SE")
            {
                $y = -1;
                $x = 1;
            }

            else if ($move == "ENTER")
            {
                $new_map = '';
                $new_x = 0;
                $new_y = 0;
            }

            else if ($move == "force")
            {
                $x = $new_x - $GLOBALS['userdata'][0]['longitude'];
                $y = $new_y - $GLOBALS['userdata'][0]['latitude'];
            }

            //checking to make sure you are staying im map bounds
            if( $GLOBALS['userdata'][0]['longitude'] + $x < $x_low_bound || $GLOBALS['userdata'][0]['longitude'] + $x > $x_high_bound || $GLOBALS['userdata'][0]['latitude'] + $y < $y_low_bound || $GLOBALS['userdata'][0]['latitude'] + $y > $y_high_bound )
            {
                $abort = true;
                error_log("trying to move out of bounds: x".($GLOBALS['userdata'][0]['longitude'] + $x).", y".($GLOBALS['userdata'][0]['latitude'] + $y));
            }

            //checking for multi_location tiles
            if($abort === false)
            {
                //$i = 0;
                //$base_x = $x;
                //$base_y = $y;
                //while(!in_array($GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])][0],array('ocean','unknown','uncharted','lake','dead lake','shore','river')) && $GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]//['longitude'])][0] == $GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'] + $y)][($GLOBALS['userdata'][0]['longitude'] + $x)][0] && $GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])][1] == $GLOBALS['map_data'][($GLOBALS//['userdata'][0]['latitude'] + $y)][($GLOBALS['userdata'][0]['longitude'] + $x)][1] && $i != 3)
                //{
                //    //error_log(print_r($GLOBALS['map_data'],true));
                //    //error_log("!in_array(".$GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])][0].",array('ocean','unknown')) && ".$GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])][0] ."== ".$GLOBALS//['map_data'][($GLOBALS['userdata'][0]['latitude'] + $y)][($GLOBALS['userdata'][0]['longitude'] + $x)][0]." && ".$i." != 3");
                //    //error_log(!in_array($GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])][0],array('ocean','unknown'))." && ".($GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])][0] == $GLOBALS//['map_data'][($GLOBALS['userdata'][0]['latitude'] + $y)][($GLOBALS['userdata'][0]['longitude'] + $x)][0])." && ".($i != 3));
//
                //    $x+=$base_x;
                //    $y+=$base_y;
                //    $i++;
                //}
            }
            else
            {
                return false;
            }
            //^checking for multi_location tiles^

            //checking for abort, and impassable, and running query
            if( $abort === true || 
                in_array("impassable",$GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'] + $y)][($GLOBALS['userdata'][0]['longitude'] + $x)]) || 
                (in_array("impassable_north",$GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])]) && $y > 0) ||
                (in_array("impassable_south",$GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])]) && $y < 0) ||
                (in_array("impassable_east", $GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])]) && $x > 0) ||
                (in_array("impassable_west", $GLOBALS['map_data'][($GLOBALS['userdata'][0]['latitude'])][($GLOBALS['userdata'][0]['longitude'])]) && $x < 0) ||
                (
                    (
                    $move != 'force' &&
                    !$GLOBALS['database']->execute_query("UPDATE `users` SET   `longitude` = ".($GLOBALS['userdata'][0]['longitude'] + $x).
                                                                            ", `latitude` = ".($GLOBALS['userdata'][0]['latitude'] + $y).
                                                                            ", `location` = '".str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][0])."'".
                                                                            ", `region` = '".str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][1])."'".
                                                                       " WHERE `id` = ".$_SESSION['uid']." AND `status` = 'awake'")
                    )
                    ||
                    (
                    $move == 'force' &&
                    !$GLOBALS['database']->execute_query("UPDATE `users` SET   `longitude` = ".($GLOBALS['userdata'][0]['longitude'] + $x).
                                                                            ", `latitude` = ".($GLOBALS['userdata'][0]['latitude'] + $y).
                                                                            ", `location` = '".str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][0])."'".
                                                                            ", `region` = '".str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][1])."'".
                                                                       " WHERE `id` = ".$_SESSION['uid'])
                    )
                )
            )
            {
                return false;
            }
            else
            {
                //handling drowning
                $current_tile = $GLOBALS['current_tile'];
                $next_tile = $GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude']+$y][$GLOBALS['userdata'][0]['longitude']+$x];
                $drowning = $GLOBALS['userdata'][0]['drowning'];

                //if in the ocean
                if($next_tile[1] == 'ocean')
                {
                    $drowning++;
                }

                //if not in the ocean and drowing counter is not zeron
                else if($next_tile[1] != 'ocean' && $drowning != 0)
                {
                    $drowning--;
                }

                //if drowning counter has changed
                if($drowning != $GLOBALS['userdata'][0]['drowning'])
                {
                    $query = "UPDATE `users` SET `drowning` = ".$drowning." WHERE `id` = ".$_SESSION['uid'];
                    $GLOBALS['database']->execute_query($query);
                }

                //if the user is drowning
                if($drowning >= 8 + $GLOBALS['userdata'][0]['rank_id'] + floor(($GLOBALS['userdata'][0]['rank_id']-1)/2)) //8 + rank(1-5) + (1 if rank 3/4 and 2 if rank 5)
                {
                    //setting health
                    $old_health = $GLOBALS['userdata'][0]['cur_health'];
                    $GLOBALS['userdata'][0]['cur_health'] -= floor($GLOBALS['userdata'][0]['max_health'] * 0.09);

                    if($GLOBALS['userdata'][0]['cur_health'] < 0)
                        $GLOBALS['userdata'][0]['cur_health'] = 0;

                    //updating health on the database
                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `users_statistics`.`cur_health` = " . $GLOBALS['userdata'][0]['cur_health'] . " WHERE `users_statistics`.`uid` = " . $_SESSION['uid']);

                    //procing health change event.
                    $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>$GLOBALS['userdata'][0]['cur_health'], 'old'=>$old_health ));
                }

                //if the users has drowned
                if( $GLOBALS['userdata'][0]['cur_health'] == 0 )
                {
                    $x = 0;
                    $y = 0;

                    $five = false;
                    $eleven = false;
                    $twentythree = false;
                    $fourtyseven = false;

                    for($n = 1, $notLand = true; $notLand; $n++)
                    {

                        for($i = 0; $i <= $n && $notLand; $i++)
                        {

                            if( (abs($i) > 5 || abs($n) > 5) && !$five)
                            {
                                GetMapData::get(12);
                                $five = true;
                            }
                            else if( (abs($i) > 11 || abs($n) > 11) && !$eleven)
                            {
                                GetMapData::get(24);
                                $eleven = true;
                            }
                            else if( (abs($i) > 23 || abs($n) > 23) && !$twentythree)
                            {
                                GetMapData::get(48);
                                $twentythree = true;
                            }
                            else if( (abs($i) > 47 || abs($n) > 47) && !$fourtyseven)
                            {
                                GetMapData::get(96);
                                $fourtyseven = true;
                            }

                                 if($i != 0 && $i != $n && is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n][1],array('ocean','shore'))){ $notLand = false; $x += $n; $y += $i; }
                            else if($i != 0 && $i != $n && is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n][1],array('ocean','shore'))){ $notLand = false; $x -= $n; $y -= $i; }
                            else if($i != 0 && $i != $n && is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n][1],array('ocean','shore'))){ $notLand = false; $x -= $i; $y += $n; }
                            else if($i != 0 && $i != $n && is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n][1],array('ocean','shore'))){ $notLand = false; $x += $i; $y -= $n; }

                            else if(is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n][1],array('ocean','shore'))){ $notLand = false; $x += $n; $y -= $i; }
                            else if(is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n][1],array('ocean','shore'))){ $notLand = false; $x -= $n; $y += $i; }
                            else if(is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y + $i][$GLOBALS['userdata'][0]['longitude'] + $x + $n][1],array('ocean','shore'))){ $notLand = false; $x += $i; $y += $n; }
                            else if(is_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n]) && !in_array($GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y - $i][$GLOBALS['userdata'][0]['longitude'] + $x - $n][1],array('ocean','shore'))){ $notLand = false; $x -= $i; $y -= $n; }
                        }
                    }

                    if(!$GLOBALS['database']->execute_query("
                        UPDATE `users`
                        SET `longitude` = '" . ($GLOBALS['userdata'][0]['longitude'] + $x) . "',
                            `latitude`= '" . ($GLOBALS['userdata'][0]['latitude'] + $y) . "',
                            `location` = '" . str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][1]) . "',
                            `status` = '" . 'drowning' . "',
                            `region` = '" . str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][1]) . "',
                            `drowning` = '" . 0 . "'
                        WHERE `id` = '" . $GLOBALS['userdata'][0]['id'] . "'
                        LIMIT 1"))
                    {
                        error_log("
                        UPDATE `users`
                        SET `longitude` = '" . ($GLOBALS['userdata'][0]['longitude'] + $x) . "',
                            `latitude`= '" . ($GLOBALS['userdata'][0]['latitude'] + $y) . "',
                            `location` = '" . str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][1]) . "',
                            `status` = '" . 'drowning' . "',
                            `region` = '" . str_replace("'","\'",$GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude'] + $y][$GLOBALS['userdata'][0]['longitude'] + $x][1]) . "',
                            `drowning` = '" . 0 . "'
                        WHERE `id` = '" . $GLOBALS['userdata'][0]['id'] . "'
                        LIMIT 1");
                    }

                    $GLOBALS['Events']->acceptEvent('status', array('new'=>'drowning', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => 'You are unconscious!', 'buttons' => array('?id=109','Drift'), 'popup' => 'yes') );
                    $GLOBALS['userdata'][0]['status'] = 'drowning';
                }

                //updating globals
                $former_latitude = $GLOBALS['userdata'][0]['latitude'];
                $former_longitude = $GLOBALS['userdata'][0]['longitude'];
                $former_location = $GLOBALS['userdata'][0]['location'];
                $former_region = $GLOBALS['userdata'][0]['region'];
                $former_owner = (isset($GLOBALS['userdata'][0]['owner'])) ? $GLOBALS['userdata'][0]['owner'] : '';
                $former_claimable = $GLOBALS['userdata'][0]['claimable'];
                $GLOBALS['userdata'][0]['latitude'] = $GLOBALS['userdata'][0]['latitude'] + $y;
                $GLOBALS['userdata'][0]['longitude'] = $GLOBALS['userdata'][0]['longitude'] + $x;
                $GLOBALS['userdata'][0]['location'] = $GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude']][$GLOBALS['userdata'][0]['longitude']][0];
                $GLOBALS['userdata'][0]['region'] = $GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude']][$GLOBALS['userdata'][0]['longitude']][1];

                foreach($GLOBALS['map_region_data'] as $region_data)
                {
                    if($region_data['region'] == $GLOBALS['userdata'][0]['region'])
                    {
                        $GLOBALS['userdata'][0]['owner'] =  $region_data['owner'];
                        $GLOBALS['userdata'][0]['claimable'] = $region_data['claimable']; 
                    }
                }

                $GLOBALS['Events']->acceptEvent('location_name', array('new'=>$GLOBALS['userdata'][0]['location'], 'old'=>$former_location ));
                $GLOBALS['Events']->acceptEvent('location_region', array('new'=>$GLOBALS['userdata'][0]['region'], 'old'=>$former_region ));
                $GLOBALS['Events']->acceptEvent('location_owner', array('new'=>$GLOBALS['userdata'][0]['owner'], 'old'=>$former_owner ));
                $GLOBALS['Events']->acceptEvent('location_claimable', array('new'=>$GLOBALS['userdata'][0]['claimable'], 'old'=>$former_claimable ));
                $GLOBALS['Events']->acceptEvent('location_x', array('new'=>$GLOBALS['userdata'][0]['longitude'], 'old'=>$former_longitude ));
                $GLOBALS['Events']->acceptEvent('location_y', array('new'=>$GLOBALS['userdata'][0]['latitude'], 'old'=>$former_latitude ));

                $GLOBALS['current_tile'] = $GLOBALS['map_data'][$GLOBALS['userdata'][0]['latitude']][$GLOBALS['userdata'][0]['longitude']];

                if(abs($y) >= 3 || abs($x) >= 3)
                {
                    require_once(Data::$absSvrPath.'/global_libs/Travel/getMapData.php');
                    GetMapData::get();
                }

                cachefunctions::endHarvest( $_SESSION['uid'] );

                return true;
            }
        }

        return false;
    }
}