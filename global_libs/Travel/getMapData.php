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
 *Class: GetMapData
 *  this class is called on to get the map's data from the database
 *
 */

class GetMapData
{
    public static function get($range = 6)
    {
        //$x_list = array();
        //$y_list = array();

        //for($i = $range * -1; $i <= $range; $i++)
        //{
        //    if($GLOBALS['userdata'][0]['longitude'] + $i >= -125 && $GLOBALS['userdata'][0]['longitude'] + $i <= 125) $x_list[] = '`'.($GLOBALS['userdata'][0]['longitude'] + $i).'`';
        //    if($GLOBALS['userdata'][0]['latitude'] + $i >= -100 && $GLOBALS['userdata'][0]['latitude'] + $i <= 100) $y_list[] = $GLOBALS['userdata'][0]['latitude'] + $i;
        //}

        $x_low  = ($GLOBALS['userdata'][0]['longitude'] - $range >= -125) ? $GLOBALS['userdata'][0]['longitude'] - $range : -125;
        $x_high = ($GLOBALS['userdata'][0]['longitude'] + $range <=  125) ? $GLOBALS['userdata'][0]['longitude'] + $range : 125;
        $y_low  = ($GLOBALS['userdata'][0]['latitude']  - $range >= -100) ? $GLOBALS['userdata'][0]['latitude']  - $range : -100;
        $y_high = ($GLOBALS['userdata'][0]['latitude']  + $range <=  100) ? $GLOBALS['userdata'][0]['latitude']  + $range : 100;

        if($x_low <= $x_high && $y_low <= $y_high )
        {
            $map_query = "SELECT * FROM `map_data` WHERE `x` >= {$x_low} AND `x` <= {$x_high} AND `y` >= {$y_low} AND `y` <= {$y_high}";
            $map_data_strip = $GLOBALS['database']->fetch_data($map_query);

            $map_data = array();
            //table-ify map data
            foreach($map_data_strip as $data)
            {
                if(!isset($map_data[$data['y']]))
                    $map_data[$data['y']] = array();

                $map_data[$data['y']][$data['x']] = $data['data'];
            }

            $map_query = 'SELECT * FROM `map_region_data`';
            $map_region_data = $GLOBALS['database']->fetch_data($map_query);

            if(is_array($map_data))
                $map_data = array_values($map_data);

            if(count($map_data) == ( $y_high - $y_low ) + 1)
            {
                $temp_map_data = array();
                for($i = 0; $i < count($map_data); $i++)
                {
                    foreach($map_data[$i] as $temp_data_key => $temp_data)
                    {
                        $map_data[$i][$temp_data_key] = explode(',',$temp_data);

						foreach($map_data[$i][$temp_data_key] as $tile_data_key => $tile_data)
						{
							if(strpos($tile_data,'/'))
							{
								$temp_tile_data = explode('/',$tile_data);

								if($temp_tile_data[1] == 'village_false' && strtolower($GLOBALS['userdata'][0]['village']) != strtolower($temp_tile_data[2]))
								{
									$map_data[$i][$temp_data_key][$tile_data_key] = $temp_tile_data[0];
								}
								else if($temp_tile_data[1] == 'village_true' && strtolower($GLOBALS['userdata'][0]['village']) == strtolower($temp_tile_data[2]))
								{
									$map_data[$i][$temp_data_key][$tile_data_key] = $temp_tile_data[0];
								}
								else if ($temp_tile_data[1] == 'quest_false')
								{
									if(!isset($GLOBALS['QuestsControl']))
										$GLOBALS['QuestsControl'] = new QuestsControl();

									//check if the user has the quest
									if(isset($GLOBALS['QuestsControl']->QuestsData->quests[$temp_tile_data[2]]))
										$quest_status = QuestContainer::$statuses[$GLOBALS['QuestsControl']->QuestsData->quests[$temp_tile_data[2]]->status];
									else
										$quest_status = 'unknown';

									if(!in_array($quest_status,$temp_tile_data))
										$map_data[$i][$temp_data_key][$tile_data_key] = $temp_tile_data[0];
									else
										unset($map_data[$i][$temp_data_key][$tile_data_key]);
								}
								else if ($temp_tile_data[1] == 'quest_true')
								{
									if(!isset($GLOBALS['QuestsControl']))
										$GLOBALS['QuestsControl'] = new QuestsControl();

									//check if the user has the quest
									if(isset($GLOBALS['QuestsControl']->QuestsData->quests[$temp_tile_data[2]]))
										$quest_status = QuestContainer::$statuses[$GLOBALS['QuestsControl']->QuestsData->quests[$temp_tile_data[2]]->status];
									else
										$quest_status = 'unknown';

									if(in_array($quest_status,$temp_tile_data))
										$map_data[$i][$temp_data_key][$tile_data_key] = $temp_tile_data[0];
									else
										unset($map_data[$i][$temp_data_key][$tile_data_key]);
								}
								//else if ($temp_tile_data[1] == 'item')
								//{
								//}
								else
								{
									unset($map_data[$i][$temp_data_key][$tile_data_key]);
								}
							}
						}

                        $region = $map_data[$i][$temp_data_key][1];
                        foreach($map_region_data as $data)
                        {
                            if($data['region'] == $region)
                            {
                                if($data['claimable'] == '1')
                                    $map_data[$i][$temp_data_key][] = 'claimable';
                                else
                                    $map_data[$i][$temp_data_key][] = 'unclaimable';

                                if($data['owner'] == '')
                                    $map_data[$i][$temp_data_key][] = 'none';
                                else
                                    $map_data[$i][$temp_data_key][] = $data['owner'];

                                break;
                            }

                        }

                    }

                    $temp_map_data[$y_low + $i] = $map_data[$i];
                }

                $map_data = $temp_map_data;
            }
            else
                throw new exception("Failed to pull good map data. Size miss match.");

            $GLOBALS['map_data'] = $map_data;
            $GLOBALS['map_region_data'] = $map_region_data;
            foreach($map_region_data as $region_data)
            {
                if($region_data['region'] == $GLOBALS['userdata'][0]['region'])
                {
                    $GLOBALS['userdata'][0]['owner'] =  $region_data['owner'];
                    $GLOBALS['userdata'][0]['claimable'] = $region_data['claimable']; 
                }
            }
            $GLOBALS['current_tile'] = $map_data[$GLOBALS['userdata'][0]['latitude']][$GLOBALS['userdata'][0]['longitude']];
        }
    }
}