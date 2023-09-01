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

class jutsuBasicFunctions {

    // Fix up jutsu data
    public static function fixUpJutsuData( $jutsu , $specialization ){
    
        // If jutsu is not split, simply return as is
        if( $jutsu['splitJutsu'] == "yes" ){
    
            // Get specialiation
            $specialization = explode(":", $specialization);
    
            if($specialization[0] == 'W')
                $specialization[0] = 'B';
    
            // Update the jutsu
            if ($specialization[0] !== "0"){
    
                // Go through fields
                foreach( $jutsu as $key => $data ){
                    if( !empty($jutsu[ $key ]) ){
                        if( $key !== "description" && $key !== "battle_description"){
                            $jutsu[ $key ] = functions::ws_remove( $jutsu[ $key ]);
                        }
    
                        $first_split = explode(']', $data);
    
                        if(count($first_split) > 1)
                        {
                            try
                            {
                                $second_split = array();
                                foreach($first_split as $temp)
                                {
                                    if($temp != '')
                                    {
                                        $split_temp = explode('[', $temp);
                                        $second_split[$split_temp[1]] = $split_temp[0];
                                    }
                                }
    
                                if(isset($second_split[$specialization[0]]))
                                    $jutsu[ $key ] = $second_split[$specialization[0]];
                                else
                                    $jutsu[ $key ] = 'unknown.';
                            }
                            catch (Exception $e)
                            {
                                $jutsu[ $key ] = 'unknown.';
                                $error_log('bad split jutsu_data: '.$data);
                            }
                        }
                    }
                }
            }
            else{
                $jutsu['description'] = "Unknown. Need specialization.";
                $jutsu['battle_description'] = "Unknown. Need specialization.";
                $jutsu['effect_1'] = "";
                $jutsu['effect_2'] = "";
                $jutsu['effect_3'] = "";
                $jutsu['effect_4'] = "";
            }
    
            // Disable more splitting
            $jutsu['splitJutsu'] = "no";
        }
    
        // Return
        return $jutsu;
    }

    // Get all the users jutsu
    protected function getAllUserJutsu( $uid , $sortColumn = "`required_rank` desc, `element`"){
        $this->jutsu = $GLOBALS['database']->fetch_data("
            SELECT
                `users_jutsu`.*,`jutsu`.`name`,`jutsu`.`attack_type`,`jutsu`.`jutsu_type`,
                `jutsu`.`required_rank` , `jutsu`.`element` , `jutsu`.`start_date` , `jutsu`.`end_date`
            FROM
                `users_jutsu`,`jutsu`
            WHERE
                `jutsu`.`id` = `users_jutsu`.`jid` AND
                `users_jutsu`.`uid` = '" . $uid . "'
            ORDER BY ".$sortColumn);
    }

    // Parse bloodline effect
    public function parseBloodline($effect) {
        return 'tell koala you found this. parseBloodline - jutsufunctions';
    }

    // Parse jutsu effect
    public static function parseEffects($tags)
    {
        $effects = '<pre>';
        $nl = "-new-line-";
        $tab = '    ';

        //spliting on global groupings "|"
        $global_groups = explode('|', $tags);
        foreach($global_groups as $global_group)
        {
            //spliting global fields from tags "{}"
            $temp = explode('}', $global_group);
            if(count($temp) == 2)
            {
                $global_fields = jutsuBasicFunctions::parseFields(ltrim($temp[0],'{'));

                //splitting tags from each other "~"
                $tags = explode('~', $temp[1]);
            }
            else
            {
                $global_fields = array();

                //splitting tags from each other "~"
                $tags = explode('~', $temp[0]);
            }

            foreach($tags as $temp_tag)
            {
                if($temp_tag != '')
                {
                    //splitting tag name from tag data
                    $temp = explode(':', $temp_tag);
                    $tag_name = trim($temp[0]);
                    if(isset($temp[1]))
                        $tag_fields = array_merge(jutsuBasicFunctions::parseFields( str_replace(')', '', str_replace('(', '', $temp[1]))), $global_fields);
                    else
                        $tag_fields = array();


                    //tag specific proccessing
                    /*
                    else if($tag_name == '')
                    {
                        $effects .= $tag_name.$nl;

                        if(isset($tag_fields[''][0]))
                            $effects .= $tab.''.$tag_fields[''][0].$nl;
                        else
                            $effects .= $tab.''.$nl;
                    }
                    */

                    if($tag_name == 'effectArmor')
                    {
                        $effects .= $tag_name.': modifies the armor stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectMastery')
                    {
                        $effects .= $tag_name.': modifies the mastery stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectStability')
                    {
                        $effects .= $tag_name.': modifies the stability stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectAccuracy')
                    {
                        $effects .= $tag_name.': modifies the accuracy stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectExpertise')
                    {
                        $effects .= $tag_name.': modifies the expertise stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectChakraPower')
                    {
                        $effects .= $tag_name.': modifies the chakra power stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectCriticalStrike')
                    {
                        $effects .= $tag_name.': modifies the critical strike stat'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectOffense')
                    {
                        $effects .= $tag_name.': modifies the offense stats'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                        {
                            $effects .= $tab.'from type: ';

                            foreach($tag_fields['targetType'] as $key => $general)
                            {
                                $effects .= $general;
                                if(count($tag_fields['targetType']) != $key + 1)
                                    $effects .= ', ';
                            }

                            $effects .= $nl;
                        }
                        else
                            $effects .= $tab.'from type: all'.$nl;
                    }

                    else if($tag_name == 'effectDefense')
                    {
                        $effects .= $tag_name.': modifies the defense stats'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                        {
                            $effects .= $tab.'from type: ';

                            foreach($tag_fields['targetType'] as $key => $general)
                            {
                                $effects .= $general;
                                if(count($tag_fields['targetType']) != $key + 1)
                                    $effects .= ', ';
                            }

                            $effects .= $nl;
                        }
                        else
                            $effects .= $tab.'from type: all'.$nl;
                    }

                    else if($tag_name == 'effectGeneralStat')
                    {
                        $effects .= $tag_name.': modifies the general stats'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'modification: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's stat";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's stat".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetGeneral'][0]))
                        {
                            $effects .= $tab.'from general: ';

                            foreach($tag_fields['targetGeneral'] as $key => $general)
                            {
                                $effects .= $general;
                                if(count($tag_fields['targetGeneral']) != $key + 1)
                                    $effects .= ', ';
                            }
                        }
                        else
                            $effects .= $tab.'from general: all'.$nl;
                    }

                    else if($tag_name == 'copyOrigin')
                    {
                        $effects .= $tag_name.': copies tags from an origin of the target'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                        {
                            $effects .= $tab.'origin: ';

                            foreach($tag_fields['targetOrigin'] as $key => $origin)
                            {
                                $effects .= $origin;
                                if(count($tag_fields['targetOrigin']) != $key + 1)
                                    $effects .= ', ';
                            }

                            $effects .= $nl;
                        }
                        else
                            $effects .= $tab.'origin: all'.$nl;

                        if(isset($tag_fields['override'][0]))
                        {
                            if($tag_fields['override'][0] === true)
                                $effects .= $tab."replaces the owner's tags with the target's".$nl;
                            else
                                $effects .= $tab.'adds tags from the target to the owner'.$nl;
                        }
                        else
                            $effects .= $tab.'adds tags from the target to the owner'.$nl;

                        if(isset($tag_fields['targetAge'][0]))
                        {
                            $effects .= $tab.'age: from '.$tag_fields['targetAge'][0].' to ';
                            if(isset($tag_fields['targetAge'][1]))
                                $effects .= $tag_fields['targetAge'][1].$nl;
                            else
                                $effects .= $tag_fields['targetAge'][0].$nl;
                        }
                        else
                            $effects .= $tab.'age: from 0 to 0'.$nl;


                    }

                    else if($tag_name == 'mirrorOrigin')
                    {
                        $effects .= $tag_name.': mirrors tags from an origin of the target'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                        {
                            $effects .= $tab.'origin: ';

                            foreach($tag_fields['targetOrigin'] as $key => $origin)
                            {
                                $effects .= $origin;
                                if(count($tag_fields['targetOrigin']) != $key + 1)
                                    $effects .= ', ';
                            }

                            $effects .= $nl;
                        }
                        else
                            $effects .= $tab.'origin: all'.$nl;

                        if(isset($tag_fields['override'][0]))
                        {
                            if($tag_fields['override'][0] === true)
                                $effects .= $tab."replaces the owner's tags with the target's".$nl;
                            else
                                $effects .= $tab.'adds tags from the target to the owner'.$nl;
                        }
                        else
                            $effects .= $tab.'adds tags from the target to the owner'.$nl;

                        if(isset($tag_fields['targetAge'][0]))
                        {
                            $effects .= $tab.'age: from '.$tag_fields['targetAge'][0].' to ';
                            if(isset($tag_fields['targetAge'][1]))
                                $effects .= $tag_fields['targetAge'][1].$nl;
                            else
                                $effects .= $tag_fields['targetAge'][0].$nl;
                        }
                        else
                            $effects .= $tab.'age: from 0 to 0'.$nl;
                    }

                    else if($tag_name == 'copyStats')
                    {
                        $effects .= $tag_name.': copys the stats of the target in some way'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                        {
                            if($tag_fields['targetPolarity'][0] == '=')
                                $effects .= $tab."copying the target's stats".$nl;

                            else if($tag_fields['targetPolarity'][0] == '+')
                                $effects .= $tab."adding stats to the owner based on the target's stats".$nl;

                            else if($tag_fields['targetPolarity'][0] == '-')
                                $effects .= $tab."subtracting stats to the owner owner based on the target's stats".$nl;

                            else
                                $effects .= $tab.'bad data'.$nl;
                        }
                        else
                            $effects .= $tab."copying the target's stats".$nl;

                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'amount: '.($tag_fields['value'][0] * 100).'%'.$nl;
                        else
                            $effects .= $tab.'1%'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                        {
                            if($tag_fields['targetType'][0] === true)
                                $effects .= $tab.'offence/defence: all'.$nl;

                            else if ($tag_fields['targetType'][0] === false)
                                $effects .= $tab.'offence/defence: none'.$nl;

                            else
                            {
                                $effects .= $tab.'offence/defence: ';

                                foreach($tag_fields['targetType'] as $key => $type)
                                {
                                    $effects .= $type;
                                    if(count($tag_fields['targetType']) != $key + 1)
                                        $effects .= ', ';
                                }

                                $effects .$nl;
                            }
                        }
                        else
                            $effects .= $tab.'offence/defence: all'.$nl;

                        if(isset($tag_fields['targetPool'][0]))
                        {
                            if($tag_fields['targetPool'][0] === true)
                                $effects .= $tab.'pool: all'.$nl;

                            else if ($tag_fields['targetPool'][0] === false)
                                $effects .= $tab.'pool: none'.$nl;

                            else
                            {
                                $effects .= $tab.'pool: ';

                                foreach($tag_fields['targetPool'] as $key => $pool)
                                {
                                    $effects .= $pool;
                                    if(count($tag_fields['targetPool']) != $key + 1)
                                        $effects .= ', ';
                                }

                                $effects .$nl;
                            }
                        }
                        else
                            $effects .= $tab.'pool: all'.$nl;

                        if(isset($tag_fields['targetStat'][0]))
                        {
                            if($tag_fields['targetStat'][0] === true)
                                $effects .= $tab.'stat: all'.$nl;

                            else if ($tag_fields['targetStat'][0] === false)
                                $effects .= $tab.'stat: none'.$nl;

                            else
                            {
                                $effects .= $tab.'stat: ';

                                foreach($tag_fields['targetStat'] as $key => $stat)
                                {
                                    $effects .= $stat;
                                    if(count($tag_fields['targetStat']) != $key + 1)
                                        $effects .= ', ';
                                }

                                $effects .$nl;
                            }
                        }
                        else
                            $effects .= $tab.'stat: all'.$nl;

                    }

                    else if($tag_name == 'effectChakra')
                    {
                        $effects .= $tag_name.": modifies the target's chakra".$nl;
                        
                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            $effects .= $tab.'modification amount: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% flat boost boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of max chakra";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . '% of max chakra'.$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectChakraCost')
                    {
                        $effects .= $tag_name.": modifies how much chakra the target's jutsu cost".$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            $effects .= $tab.'modification amount: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% flat boost boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of chakra cost";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . '% of chakra cost'.$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectStamina')
                    {
                        $effects .= $tag_name.": modifies the target's stamina".$nl;
                        
                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            $effects .= $tab.'modification amount: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% flat boost boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of stamina";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . '% of stamina'.$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectStaminaCost')
                    {
                        $effects .= $tag_name.": modifies how much stamina the target's jutsu cost".$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            $effects .= $tab.'modification amount: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% flat boost boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of stamina cost";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . '% of stamina cost'.$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectHealth')
                    {
                        $effects .= $tag_name.": modifies the target's health".$nl;
                        
                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            $effects .= $tab.'modification amount: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% flat boost boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . '% of health'.$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectHealthCost')
                    {
                        $effects .= $tag_name.": modifies how much health the target's jutsu cost".$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            $effects .= $tab.'modification amount: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% flat boost boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of health cost";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . '% of health cost'.$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'damage')
                    {
                        $effects .= $tag_name.': deals damage to the target'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            if(isset($tag_fields['statBased']) && $tag_fields['statBased'] == false)
                                $effects .= $tab.'damage amount: ';
                            else
                                $effects .= $tab.'damage power: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";
                            else
                                $effects.= "wut: ".$value[0][0];

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                            $effects .= $tab.'using type: '.$tag_fields['targetType'][0].$nl;
                        else
                            $effects .= $tab.'using type: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'using element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'using element: none'.$nl;

                        if(isset($tag_fields['targetGeneral'][0]))
                        {
                            $effects .= $tab.'from general: ';

                            if(isset($tag_fields['targetGeneral'][1]))
                                $effects .= $tag_fields['targetGeneral'][0].' and '.$tag_fields['targetGeneral'][1].$nl;
                            else
                                $effects .= $tag_fields['targetGeneral'][0].$nl;
                        }
                        else
                            $effects .= $tab.'from general: all'.$nl;
                    }

                    else if($tag_name == 'damageOverTime')
                    {
                        $effects .= $tag_name.': deals damage to the target over time'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            if(isset($tag_fields['statBased']) && $tag_fields['statBased'] == false)
                                $effects .= $tab.'damage amount: ';
                            else
                                $effects .= $tab.'damage power: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";
                            else
                                $effects.= "wut: ".$value[0][0];

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                            $effects .= $tab.'using type: '.$tag_fields['targetType'][0].$nl;
                        else
                            $effects .= $tab.'using type: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'using element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'using element: none'.$nl;

                        if(isset($tag_fields['targetGeneral'][0]))
                        {
                            $effects .= $tab.'from general: ';

                            if(isset($tag_fields['targetGeneral'][1]))
                                $effects .= $tag_fields['targetGeneral'][0].' and '.$tag_fields['targetGeneral'][1].$nl;
                            else
                                $effects .= $tag_fields['targetGeneral'][0].$nl;
                        }
                        else
                            $effects .= $tab.'from general: all'.$nl;
                    }

                    else if($tag_name == 'effectDamageIn')
                    {
                        $effects .= $tag_name.': effects the damage taken by the target'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'power: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                            $effects .= $tab.'from type: '.$tag_fields['targetType'][0].$nl;
                        else
                            $effects .= $tab.'from type: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'from origin: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'from element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'from element: all'.$nl;
                    }

                    else if($tag_name == 'effectDamageOut')
                    {
                        $effects .= $tag_name.': effects the damage delt by the target'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'power: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                            $effects .= $tab.'from type: '.$tag_fields['targetType'][0].$nl;
                        else
                            $effects .= $tab.'from type: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'from origin: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'from element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'from element: all'.$nl;
                    }

                    else if($tag_name == 'immunity')
                    {
                        $effects .= $tag_name.': this sets or removed immunity from a type of damage'.$nl;

                        if(isset($tag_fields['value'][0]))
                            if($tag_fields['value'][0] == 1)
                                $effects .= $tab.'setting immunity'.$nl;
                            else if($tag_fields['value'][0] == 0)
                                $effects .= $tab.'removing immunity'.$nl;
                            else
                                $effects .= $tab.'bad data'.$nl;
                        else
                            $effects .= $tab.'bad data'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                            $effects .= $tab.'from type: '.$tag_fields['targetType'][0].$nl;
                        else
                            $effects .= $tab.'from type: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'from origin: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'from element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'from element: all'.$nl;

                        if(isset($tag_fields['targetImmunity'][0]))
                            $effects .= $tab.'from damage type: '.$tag_fields['targetImmunity'][0].$nl;
                        else
                            $effects .= $tab.'from damage type: all'.$nl;
                    }

                    else if($tag_name == 'reflectDamage')
                    {
                        $effects .= $tag_name.': reflects damage back to the dealer'.$nl;

                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'amount: '.$tag_fields['value'][0].'%'.$nl;
                        else
                            $effects .= $tab.'amount: 1%'.$nl;

                        if(isset($tag_fields['targetType'][0]))
                            $effects .= $tab.'from type: '.$tag_fields['targetType'][0].$nl;
                        else
                            $effects .= $tab.'from type: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'from Origin: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'from Element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'from Element: all'.$nl;
                    }

                    else if($tag_name == 'oneHitKill')
                    {
                        $effects .= $tag_name.': will kill the target'.$nl;
                    }

                    else if($tag_name == 'heal')
                    {
                        $effects .= $tag_name.': heals the target'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            
                            if(isset($tag_fields['statBased']) && $tag_fields['statBased'] == false)
                                $effects .= $tab.'healing amount: ';
                            else
                                $effects .= $tab.'healing power: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectHealOut')
                    {
                        $effects .= $tag_name.': effects the healing delt by the target'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'power: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'effectHealIn')
                    {
                        $effects .= $tag_name.': effects the healing taken by the target'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);
                            $effects .= $tab.'power: ';
                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'healOverTime')
                    {
                        $effects .= $tag_name.': heals the target over time'.$nl;

                        if(isset($tag_fields['value']))
                        {
                            $value = jutsuBasicFunctions::parseValue($tag_fields['value']);

                            if(isset($tag_fields['statBased']) && $tag_fields['statBased'] == false)
                                $effects .= $tab.'healing amount: ';
                            else
                                $effects .= $tab.'healing power: ';

                            if($value[0][0] == 'FB')
                                $effects.= ceil(sqrt($value[0][1]));
                            else if ($value[0][0] == 'BP')
                                $effects.= $value[0][1] ."% boost";
                            else if ($value[0][0] == 'PP')
                                $effects.= $value[0][1] ."% of target's max health";

                            if(isset($value[1][0]) && $value[1][0] == 'FB')
                                $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'BP')
                                $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                            else if (isset($value[1][0]) && $value[1][0] == 'PP')
                                $effects.= ' + ' . $value[1][1] . "% of target's max health per level".$nl;
                            else
                                $effects.= $nl;

                        }
                        else
                            $effects .= $tab.'bad data'.$nl;
                    }

                    else if($tag_name == 'noRob')
                    {
                        $effects .= $tag_name.': prevents target from being robbed'.$nl;
                        if(isset($tag_fields['value'][0]))
                        {
                            if($tag_fields['value'][0] == -1)
                            {
                                $effects .= $tab.'prevents robbing for: infinity'.$nl;
                            }
                                
                            else if($tag_fields['value'][0] == -9)
                            {
                                $effects .= $tab.'prevents robbing for: infinity'.$nl;
                                $effects .= $tab.'cannot be removed'.$nl;
                            }

                            else
                            {
                                $effects .= $tab.'prevents robbing for: '.$tag_fields['value'][0].$nl;
                            }
                        }

                        else
                        {
                            $effects .= $tab.'prevents robbing for: 1'.$nl;
                        }
                    }

                    else if($tag_name == 'yesRob')
                    {
                        $effects .= $tag_name.': allows the target to be robed'.$nl;
                    }

                    else if($tag_name == 'rob')
                    {
                        $effects .= $tag_name.': attempts to rob the target'.$nl;

                        if(isset($tag_fields['value'][0]) && isset($tag_fields['value'][1]) && isset($tag_fields['value'][2]) && isset($tag_fields['value'][3]))
                        {
                            $effects .= $tab.'chance: '.$tag_fields['value'][0].' + '.$tag_fields['value'][1].' per level'.$nl;
                            $effects .= $tab.'amount: '.$tag_fields['value'][0].'% + '.$tag_fields['value'][1].'% per level'.$nl;
                        }
                        else
                            $effects .= $tab.'bad data.'.$nl;
                    }

                    else if($tag_name == 'noFlee')
                    {
                        $effects .= $tag_name.': prevents target from fleeing'.$nl;
                        if(isset($tag_fields['value'][0]))
                        {
                            if($tag_fields['value'][0] == -1)
                            {
                                $effects .= $tab.'prevents fleeing for: infinity'.$nl;
                            }
                                
                            else if($tag_fields['value'][0] == -9)
                            {
                                $effects .= $tab.'prevents fleeing for: infinity'.$nl;
                                $effects .= $tab.'cannot be removed'.$nl;
                            }

                            else
                            {
                                $effects .= $tab.'prevents fleeing for: '.$tag_fields['value'][0].$nl;
                            }
                        }

                        else
                        {
                            $effects .= $tab.'prevents fleeing for: 1'.$nl;
                        }
                    }

                    else if($tag_name == 'yesFlee')
                    {
                        $effects .= $tag_name.': allows the target to flee'.$nl;
                    }

                    else if($tag_name == 'effectFleeChance')
                    {
                        $effects .= $tag_name.": effects the target's chance to flee".$nl;

                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'chance to flee modifier: '.$tag_fields['value'][0].'%'.$nl;
                        else
                            $effects .= $tab.'chance to flee modifier: 1%'.$nl;
                    }

                    else if($tag_name == 'flee')
                    {
                        $effects .= $tag_name.': makes target attempt to flee'.$nl;
                    }

                    else if($tag_name == 'noStun')
                    {
                        $effects .= $tag_name.': prevents target from being stunned'.$nl;
                        if(isset($tag_fields['value'][0]))
                        {
                            if($tag_fields['value'][0] == -1)
                            {
                                $effects .= $tab.'prevents stunning for: infinity'.$nl;
                            }
                                
                            else if($tag_fields['value'][0] == -9)
                            {
                                $effects .= $tab.'prevents stunning for: infinity'.$nl;
                                $effects .= $tab.'cannot be removed'.$nl;
                            }

                            else
                            {
                                $effects .= $tab.'prevents stunning for: '.$tag_fields['value'][0].$nl;
                            }
                        }

                        else
                        {
                            $effects .= $tab.'prevents stunning for: 1'.$nl;
                        }
                    }

                    else if($tag_name == 'yesStun')
                    {
                        $effects .= $tag_name.': allows target to be stunned'.$nl;
                    }

                    else if($tag_name == 'stun')
                    {
                        $effects .= $tag_name.': prevents the target from taking their turn'.$nl;

                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'stuns for: '.$tag_fields['value'][0].$nl;
                        else
                            $effects .= $tab.'stuns for: 1'.$nl;
                    }

                    else if($tag_name == 'noStagger')
                    {
                        $effects .= $tag_name.': prevents target from being staggered'.$nl;
                        if(isset($tag_fields['value'][0]))
                        {
                            if($tag_fields['value'][0] == -1)
                            {
                                $effects .= $tab.'prevents staggering for: infinity'.$nl;
                            }
                                
                            else if($tag_fields['value'][0] == -9)
                            {
                                $effects .= $tab.'prevents staggering for: infinity'.$nl;
                                $effects .= $tab.'cannot be removed'.$nl;
                            }

                            else
                            {
                                $effects .= $tab.'prevents staggering for: '.$tag_fields['value'][0].$nl;
                            }
                        }

                        else
                        {
                            $effects .= $tab.'prevents staggering for: 1'.$nl;
                        }
                    }

                    else if($tag_name == 'yesStagger')
                    {
                        $effects .= $tag_name.': allows target to be staggered'.$nl;
                    }

                    else if($tag_name == 'stagger')
                    {
                        $effects .= $tag_name.": removes target's stats from equipment".$nl;

                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'staggers for: '.$tag_fields['value'][0].$nl;
                        else
                            $effects .= $tab.'staggers for: 1'.$nl;
                    }

                    else if($tag_name == 'noDisable')
                    {
                        $effects .= $tag_name.': prevents target from being disabled'.$nl;
                        if(isset($tag_fields['value'][0]))
                        {
                            if($tag_fields['value'][0] == -1)
                            {
                                $effects .= $tab.'prevents disabling for: infinity'.$nl;
                            }
                                
                            else if($tag_fields['value'][0] == -9)
                            {
                                $effects .= $tab.'prevents disabling for: infinity'.$nl;
                                $effects .= $tab.'cannot be removed'.$nl;
                            }

                            else
                            {
                                $effects .= $tab.'prevents disabling for: '.$tag_fields['value'][0].$nl;
                            }
                        }

                        else
                        {
                            $effects .= $tab.'prevents disabling for: 1'.$nl;
                        }

                    }

                    else if($tag_name == 'yesDisable')
                    {
                        $effects .= $tag_name.': allows target to be disabled'.$nl;
                    }

                    else if($tag_name == 'disable')
                    {
                        $effects .= $tag_name.': prevents jutsu use'.$nl;
                        
                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'disables for: '.$tag_fields['value'][0].$nl;
                        else
                            $effects .= $tab.'disabled for: 1'.$nl;

                    }

                    else if($tag_name == 'clear')
                    {
                        $effects .= $tag_name.$nl;
                        
                        if(isset($tag_fields['targetTag'][0]))
                            $effects .= $tab.'clearing tag: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'clearing tag: all'.$nl;

                        if(isset($tag_fields['targetElement'][0]))
                            $effects .= $tab.'clearing tag of element: '.$tag_fields['targetElement'][0].$nl;
                        else
                            $effects .= $tab.'clearing tag of element: all'.$nl;

                        if(isset($tag_fields['targetGeneral'][0]))
                            $effects .= $tab.'clearing tag of general: '.$tag_fields['targetGeneral'][0].$nl;
                        else
                            $effects .= $tab.'clearing tag of element: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'clearing tags from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'clearing tags from origin: anywhere'.$nl;

                        if(isset($tag_fields['targetCategory'][0]))
                            $effects .= $tab.'clearing tags from category: '.$tag_fields['targetCategory'][0].$nl;
                        else
                            $effects .= $tab.'clearing tag from category: anywhere'.$nl;

                        if(isset($tag_fields['targetInEffect'][0]))
                            $effects .= $tab.'clearing tags that are in effect: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'clearing tags that are either in effect or not.'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                            $effects .= $tab.'clearing tags that are: '.$tag_fields['targetPolarity'][0].$nl;
                        else
                            $effects .= $tab.'clearing tags that are: + or -'.$nl;
                    }

                    else if($tag_name == 'noClear')
                    {
                        $effects .= $tag_name.$nl;
                        
                        if(isset($tag_fields['targetTag'][0]))
                            $effects .= $tab.'prevents clearing tag: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'prevents clearing tag: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'prevents clearing tags from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'prevents clearing tags from origin: anywhere'.$nl;

                        if(isset($tag_fields['targetCategory'][0]))
                            $effects .= $tab.'prevents clearing tags from category: '.$tag_fields['targetCategory'][0].$nl;
                        else
                            $effects .= $tab.'prevents clearing tag from category: anywhere'.$nl;

                        if(isset($tag_fields['targetInEffect'][0]))
                            $effects .= $tab.'prevents clearing tags that are in effect: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'prevents clearing tags that are either in effect or not.'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                            $effects .= $tab.'prevents clearing tags that are: '.$tag_fields['targetPolarity'][0].$nl;
                        else
                            $effects .= $tab.'prevents clearing tags that are: + or -'.$nl;
                    }

                    else if($tag_name == 'yesClear')
                    {
                        $effects .= $tag_name.$nl;
                        
                        if(isset($tag_fields['targetTag'][0]))
                            $effects .= $tab.'allows clearing tag: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'allows clearing tag: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'allows clearing tags from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'allows clearing tags from origin: anywhere'.$nl;

                        if(isset($tag_fields['targetCategory'][0]))
                            $effects .= $tab.'allows clearing tags from category: '.$tag_fields['targetCategory'][0].$nl;
                        else
                            $effects .= $tab.'allows clearing tag from category: anywhere'.$nl;

                        if(isset($tag_fields['targetInEffect'][0]))
                            $effects .= $tab.'allows clearing tags that are in effect: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'allows clearing tags that are either in effect or not.'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                            $effects .= $tab.'allows clearing tags that are: '.$tag_fields['targetPolarity'][0].$nl;
                        else
                            $effects .= $tab.'allows clearing tags that are: + or -'.$nl;
                    }

                    else if($tag_name == 'delay')
                    {
                        $effects .= $tag_name.$nl;
                        
                        if(isset($tag_fields['value'][0]))
                            $effects .= $tab.'delays by: '.$tag_fields['value'][0].$nl;
                        else
                            $effects .= $tab.'delays by: 1'.$nl;

                        if(isset($tag_fields['targetTag'][0]))
                            $effects .= $tab.'delaying tag: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'delaying tag: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'delaying tags from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'delaying tags from origin: anywhere'.$nl;

                        if(isset($tag_fields['targetCategory'][0]))
                            $effects .= $tab.'delaying tags from category: '.$tag_fields['targetCategory'][0].$nl;
                        else
                            $effects .= $tab.'delaying tag from category: anywhere'.$nl;

                        if(isset($tag_fields['targetInEffect'][0]))
                            $effects .= $tab.'delaying tags that are in effect: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'delaying tags that are either in effect or not.'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                            $effects .= $tab.'delaying tags that are: '.$tag_fields['targetPolarity'][0].$nl;
                        else
                            $effects .= $tab.'delaying tags that are: + or -'.$nl;
                    }

                    else if($tag_name == 'noDelay')
                    {
                        $effects .= $tag_name.$nl;
                        
                        if(isset($tag_fields['targetTag'][0]))
                            $effects .= $tab.'prevents delaying tag: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'prevents delaying tag: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'prevents delaying tags from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'prevents delaying tags from origin: anywhere'.$nl;

                        if(isset($tag_fields['targetCategory'][0]))
                            $effects .= $tab.'prevents delaying tags from category: '.$tag_fields['targetCategory'][0].$nl;
                        else
                            $effects .= $tab.'prevents delaying tag from category: anywhere'.$nl;

                        if(isset($tag_fields['targetInEffect'][0]))
                            $effects .= $tab.'prevents delaying tags that are in effect: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'prevents delaying tags that are either in effect or not.'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                            $effects .= $tab.'prevents delaying tags that are: '.$tag_fields['targetPolarity'][0].$nl;
                        else
                            $effects .= $tab.'prevents delaying tags that are: + or -'.$nl;
                    }

                    else if($tag_name == 'yesDelay')
                    {
                        $effects .= $tag_name.$nl;
                        
                        if(isset($tag_fields['targetTag'][0]))
                            $effects .= $tab.'allows delaying tag: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'allows delaying tag: all'.$nl;

                        if(isset($tag_fields['targetOrigin'][0]))
                            $effects .= $tab.'allows delaying tags from origin: '.$tag_fields['targetOrigin'][0].$nl;
                        else
                            $effects .= $tab.'allows delaying tags from origin: anywhere'.$nl;

                        if(isset($tag_fields['targetCategory'][0]))
                            $effects .= $tab.'allows delaying tags from category: '.$tag_fields['targetCategory'][0].$nl;
                        else
                            $effects .= $tab.'allows delaying tag from category: anywhere'.$nl;

                        if(isset($tag_fields['targetInEffect'][0]))
                            $effects .= $tab.'allows delaying tags that are in effect: '.$tag_fields['targetTag'][0].$nl;
                        else
                            $effects .= $tab.'allows delaying tags that are either in effect or not.'.$nl;

                        if(isset($tag_fields['targetPolarity'][0]))
                            $effects .= $tab.'allows delaying tags that are: '.$tag_fields['targetPolarity'][0].$nl;
                        else
                            $effects .= $tab.'allows delaying tags that are: + or -'.$nl;
                    }

                    else if($tag_name == 'noOneHitKill')
                    {
                        $effects .= $tag_name.$nl;
                        
                    }

                    else if($tag_name == 'yesOneHitKill')
                    {
                        $effects .= $tag_name.$nl;
                        
                    }

                    else if($tag_name == 'summon')
                    {
                        $effects .= $tag_name.': ';
                        if(isset($tag_fields['aiName']))
                            $effects .= $tag_fields['aiName'][0].$nl;
                        else
                        {
                            $result = $GLOBALS['database']->fetch_data('SELECT `name` FROM `ai` WHERE `id` = '.$tag_fields['aiId'][0]);
                            $effects .= $result[0]['name'].$nl;
                        }

                        $effects .= $tab.'Stat Copy: %';
                        if(isset($tag_fields['value']))
                        {
                            $effects .= $tag_fields['value'][0];

                            if(isset($tag_fields['value'][1]))
                                $effects .= ' + '.($tag_fields['value'][1]).' per level'.$nl;
                            else
                                $effects .= $nl;
                        }
                        else
                            $effects .= '100';
                    }

                    else
                        $effects .= 'unknown tag: "'.$tag_name.'"'.$nl;


                    //parse out and display general use fields
                    //target
                    if(isset($tag_fields['target']))
                    {
                        if(
                          $tag_fields['target'][0] == 'self' || 
                          $tag_fields['target'][0] == 'opponent' || 
                          $tag_fields['target'][0] == 'ally' || 
                          $tag_fields['target'][0] == 'other' || 
                          $tag_fields['target'][0] == 'target' )
                        {
                            $effects.= $tab.'targeting: '.$tag_fields['target'][0].$nl;
                        }
                        else
                        {
                            $effects.= $tab.'targeting: '.$tag_fields['target'][0].$nl;
                            $effects.= $tab.$tab.'max number of targets: '.$tag_fields['target'][0].$nl;
                            $effects.= $tab.$tab.'chance to hit per target: '.$tag_fields['target'][0].'%'.$nl;
                            $effects.= $tab.$tab.'minimum effect: '.$tag_fields['target'][0].'%'.$nl;
                            $effects.= $tab.$tab.'max effect: '.$tag_fields['target'][0].'%'.$nl;
                        }
                    }

                    //duration
                    if(isset($tag_fields['duration']))
                    {
                        if(count($tag_fields['duration']) == 1)
                            $effects.= $tab.'duration: '.$tag_fields['duration'][0].$nl;
                        else
                            $effects.= $tab.'duration: '.$tag_fields['duration'][0].' to '.$tag_fields['duration'][0].$nl;
                    }
                    else
                    {
                        $effects.= $tab.'duration: 1'.$nl;
                    }

                    //delay
                    if(isset($tag_fields['delay']))
                    {
                        $effects.= $tab.'delayed by: '.$tag_fields['delay'][0].$nl;
                    }

                    //cadence
                    if(isset($tag_fields['cadence']))
                    {
                        $effects.= $tab.'cadence: ';

                        foreach($tag_fields['cadence'] as $key => $number)
                        {
                            $effects .= $number;
                            if($key+1 != count($tag_fields['cadence']))
                                $effects .= ', ';
                            else
                                $effects .= '...'.$nl;
                        }
                    }

                    //degrade
                    if(isset($tag_fields['degrade']))
                    {
                        $limit = array_pop($tag_fields['degrade']);
                        $effects.= $tab.'degrading: ';
                        foreach($tag_fields['degrade'] as $key => $number)
                        {
                            $effects .= $number;
                            if($key+1 != count($tag_fields['degrade']))
                                $effects .= ', ';
                            else
                                $effects .= '...'.$nl;
                        }

                        $effects.= $tab.$tab.'limit: '.$limit.$nl;
                    }

                    //amplify
                    if(isset($tag_fields['amplify']))
                    {
                        $limit = array_pop($tag_fields['amplify']);
                        $effects.= $tab.'amplifying: ';
                        foreach($tag_fields['amplify'] as $key => $number)
                        {
                            $effects .= $number;
                            if($key+1 != count($tag_fields['amplify']))
                                $effects .= ', ';
                            else
                                $effects .= '...'.$nl;
                        }

                        $effects.= $tab.$tab.'limit: '.$limit.$nl;
                    }

                    //degrade
                    if(isset($tag_fields['degradeEffect']))
                    {
                        $limit = array_pop($tag_fields['degradeEffect']);
                        $effects.= $tab.'degrading effect: ';
                        foreach($tag_fields['degradeEffect'] as $key => $number)
                        {
                            $effects .= $number;
                            if($key+1 != count($tag_fields['degradeEffect']))
                                $effects .= ', ';
                            else
                                $effects .= '...'.$nl;
                        }

                        $effects.= $tab.$tab.'limit: '.$limit.$nl;
                    }

                    //amplify
                    if(isset($tag_fields['amplifyEffect']))
                    {
                        $limit = array_pop($tag_fields['amplifyEffect']);
                        $effects.= $tab.'amplifying Effect: ';
                        foreach($tag_fields['amplifyEffect'] as $key => $number)
                        {
                            $effects .= $number;
                            if($key+1 != count($tag_fields['amplifyEffect']))
                                $effects .= ', ';
                            else
                                $effects .= '...'.$nl;
                        }

                        $effects.= $tab.$tab.'limit: '.$limit.$nl;
                    }

                    //recoil
                    if(isset($tag_fields['recoil']))
                    {
                        $effects.= $tab.'recoil: ';

                        $value = jutsuBasicFunctions::parseValue($tag_fields['recoil']);
                        

                        if($value[0][0] == 'FB')
                            $effects.= ceil(sqrt($value[0][1]));
                        else if ($value[0][0] == 'BP')
                            $effects.= $value[0][1] ."% boost";
                        else if ($value[0][0] == 'PP')
                            $effects.= $value[0][1] ."% of damage";
                        else
                            $effects.= "wut: ".$value[0][0];

                        if(isset($value[1][0]) && $value[1][0] == 'FB')
                            $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                        else if (isset($value[1][0]) && $value[1][0] == 'BP')
                            $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                        else if (isset($value[1][0]) && $value[1][0] == 'PP')
                            $effects.= ' + ' . $value[1][1] . '% of damage per level'.$nl;
                        else
                            $effects.= $nl;
                    }

                    //leach
                    if(isset($tag_fields['leach']))
                    {
                        $effects.= $tab.'leach: ';

                        $value = jutsuBasicFunctions::parseValue($tag_fields['leach']);
                        

                        if($value[0][0] == 'FB')
                            $effects.= ceil(sqrt($value[0][1]));
                        else if ($value[0][0] == 'BP')
                            $effects.= $value[0][1] ."% boost";
                        else if ($value[0][0] == 'PP')
                            $effects.= $value[0][1] ."% of damage";
                        else
                            $effects.= "wut: ".$value[0][0];

                        if(isset($value[1][0]) && $value[1][0] == 'FB')
                            $effects.= ' + ' . ceil(sqrt($value[1][1])) . ' per level'.$nl;
                        else if (isset($value[1][0]) && $value[1][0] == 'BP')
                            $effects.= ' + ' . $value[1][1] - 100 . '% boost per level'.$nl;
                        else if (isset($value[1][0]) && $value[1][0] == 'PP')
                            $effects.= ' + ' . $value[1][1] . '% of damage per level'.$nl;
                        else
                            $effects.= $nl;
                    }

                    //priority
                    if(isset($tag_fields['priority']) && $tag_fields['priority'][0] != 2)
                    {
                        if($tag_fields['priority'][0] == 1)
                            $effects.= $tab.'speed: slow'.$nl;
                        else if($tag_fields['priority'][0] == 3)
                            $effects.= $tab.'speed: fast'.$nl;
                        else
                            $effects.= $tab.'speed: instant'.$nl;
                    }

                    //noClear
                    if(isset($tag_fields['noClear']))
                    {
                        if($tag_fields['noClear'][0] === true)
                            $effects.= $tab.'clear: can not be cleared'.$nl;
                    }

                    //noDelay
                    if(isset($tag_fields['noDelay']))
                    {
                        if($tag_fields['noDelay'][0] === true)
                            $effects.= $tab.'delay: can not be delayed'.$nl;
                    }

                    //persistAfterDeath
                    if(isset($tag_fields['persistAfterDeath']))
                    {
                        if($tag_fields['persistAfterDeath'][0] === true)
                            $effects.= $tab.'death: persists after death'.$nl;
                    }

                    //noStack
                    if(isset($tag_fields['noStack']))
                    {
                        if($tag_fields['noStack'][0] === true)
                            $effects.= $tab.'stacking: can not stack'.$nl;
                    }

                    //noStackAoe
                    if(isset($tag_fields['noStackAoe']))
                    {
                        if($tag_fields['noStack'][0] === true)
                            $effects.= $tab.'stacking: can not stack via area of effect'.$nl;
                    }

                    //missTargetChance
                    if(isset($tag_fields['missTargetChance']))
                    {
                        $effects.= $tab.'chance to miss target: '.$tag_fields['missTargetChance'][0].'%';
                        if(isset($tag_fields['missTargetChance'][1]))
                            $effects.=$tab.' + '.$tag_fields['missTargetChance'][1].'% per level'.$nl;
                        else
                            $effects.=$nl;
                    }

                    //wrongTargetChance
                    if(isset($tag_fields['wrongTargetChance']))
                    {
                        $effects.= $tab.'chance to hit wrong target on miss: '.$tag_fields['wrongTargetChance'][0].'%'.$nl;
                        if(isset($tag_fields['wrongTargetChance'][1]))
                            $effects.=$tab.' + '.$tag_fields['wrongTargetChance'][1].'% per level'.$nl;
                        else
                            $effects.=$nl;
                    }

                    //failureChance
                    if(isset($tag_fields['failureChance']))
                    {
                        $effects.= $tab.'chance to fail each round: '.$tag_fields['failureChance'][0].'%'.$nl;
                        if(isset($tag_fields['failureChance'][1]))
                            $effects.=$tab.' + '.$tag_fields['failureChance'][1].'% per level'.$nl;
                        else
                            $effects.=$nl;
                    }

                    //backfireChance
                    if(isset($tag_fields['backfireChance']))
                    {
                        $effects.= $tab.'chance to back fire: '.$tag_fields['backfireChance'][0].'%'.$nl;
                        if(isset($tag_fields['backfireChance'][1]))
                            $effects.=$tab.' + '.$tag_fields['backfireChance'][1].'% per level'.$nl;
                        else
                            $effects.=$nl;
                    }

                    //polaritySwitchChance
                    if(isset($tag_fields['polaritySwitchChance']))
                    {
                        $effects.= $tab.'chance for the tags value to invert: '.$tag_fields['backfireChance'][0].'%'.$nl;
                        if(isset($tag_fields['polaritySwitchChance'][1]))
                            $effects.=$tab.' + '.$tag_fields['polaritySwitchChance'][1].'% per level'.$nl;
                        else
                            $effects.=$nl;
                    }
                }
            }
        }

        return $effects.'</pre>';
    }

    public static function parseValue($value)
    {
        $ALLVALUETYPE = array('FB', 'BP', 'PP');



        if(!is_array($value))
            $value = array($value);

        //get array length
        $value_count = count($value);

        $return = array();

        //if the array is 4 long and all values are good
        if($value_count == 4 && (is_numeric($value[0]) || substr($value[0],-1) == '%' || $value[0] === true || $value[0] === false) && (is_numeric($value[2]) || $value[2] == true  || $value[0] === false) && in_array($value[1], $ALLVALUETYPE) && in_array($value[3], $ALLVALUETYPE))
        {
            //if the value types do not equal.
            if($value[1] != $value[3])
            {
                //set them
                $return[] = array($value[1] , $value[0]);
                $return[] = array($value[3] , $value[2]);
            }
            //if value types are equal merge them.
            else
            {
                $return[] = array($value[1] , $value[0]);
                $return[] = array($value[3] , $value[2]);
            }
        }

        //else if length is two and all values are good
        else if($value_count == 2 && (is_numeric($value[0]) || substr($value[0],-1) == '%' || $value[0] === true || $value[0] === false) && (is_numeric($value[1]) || $value[1] === true || $value[1] === false))
        {
            $return[] = array('FB' , $value[0]);
            $return[] = array('FB' , $value[1]);
        }

        //else if length is two and all values are good
        else if($value_count == 2 && (is_numeric($value[0]) || substr($value[0],-1) == '%'  || $value[0] === true || $value[0] === false) && in_array($value[1], $ALLVALUETYPE))
        {
            $return[] = array( $value[1] , $value[0]);
            $return[] = array();
        }

        //else if length is three and all values are good
        else if($value_count == 3 && (is_numeric($value[0]) || substr($value[0],-1) == '%'  || $value[0] === true || $value[0] === false) && (is_numeric($value[2])  || $value[0] === true  || $value[0] === false ) && in_array($value[1], $ALLVALUETYPE) )
        {
            //if the value types dont match
            if($value[1] != 'FB')
            {
                //set them
                $return[] = array( $value[1] , $value[0]);
                $return[] = array( 'FB' , $value[2]);
            }
            //if the value types match merge them.
            else
            {
                $return[] = array( 'FB' , $value[0]);
                $return[] = array( 'FB' , $value[2]);
            }

        }

        //if length is one and the value is good.
        else if($value_count == 1 && (is_numeric($value[0]) || $value[0] === true || substr($value[0],-1) == '%'))
        {
            $return[] = array( 'FB' , $value[0]);
            $return[] = array();
        }

        return $return;
    }

    public static function parseFields($fields)
    {
        $fields_array = array();

        //breaking fields into array;
        $fields = explode(';', $fields);
        foreach( $fields as $field )
        {
            if($field !='')
            {
                $temp = explode('>', $field);
                $field_name = $temp[0];
                $field_data = explode(',', str_replace(')', '', str_replace('(', '', $temp[1])));

                $fields_array[$field_name] = $field_data;
            }
        }


        return $fields_array;
    }

    // A function for converting item requirement to nice readable string.
    private function readItemRequirement( $id, $tag ){
        return 'tell koala if you find this. readItemRequirment - jutsufunctions';
    }

    // Show jutsu details
    public function show_details( $jid , $superPermission = false) {

        $elements = new Elements();

        // Check that it's numeric
        if (is_numeric($jid)) {

            // Get the jutsu data
            $data = $GLOBALS['database']->fetch_data("
                SELECT `jutsu`.*, `users_jutsu`.*
                FROM `jutsu`
                LEFT JOIN `users_jutsu` ON (`users_jutsu`.`jid` = `jutsu`.`id` AND `users_jutsu`.`uid` = '" . $_SESSION['uid'] . "')
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = '" . $_SESSION['uid'] . "')
                WHERE `jutsu`.`id` = '" . $jid . "'"
            );
            if ($data != '0 rows') {

                // Fix up the jutsu in case of specialization
                $specialization = explode(":", $GLOBALS['userdata'][0]['specialization']);
                $data[0] = $this->fixUpJutsuData( $data[0] , $GLOBALS['userdata'][0]['specialization'] );

                if( $data[0]['jid'] !== null || $superPermission){


                    // Set the required items
                    $data[0]['required_weapons'] =  str_replace('/', ' or ', str_replace(',' , ', and ', $data[0]['weapons']));

                    $reagents = '';
                    if($data[0]['reagents'] != '')
                    {
                        $temp_items = explode(',', $data[0]['reagents']);
                        $items = array();

                        foreach($temp_items as $item)
                        {
                            $temp_item = explode('(',$item);
                            if(isset($temp_item[1]))
                                $temp_item[1] = rtrim($temp_item[1], ')');
                            else
                                $temp_item[1] = 1;

                            $items[$temp_item[0]] = $temp_item[1];
                        }

                        $result = $GLOBALS['database']->fetch_data('SELECT `id`,`name` FROM `items` WHERE `id` in ('.implode(',', array_keys($items)).')');
                        foreach($result as $temp_data)
                        {
                            if($reagents != '')
                                $reagents .= ', ';

                            $reagents .= $temp_data['name'];

                            if($items[$temp_data['id']] != 1)
                                $reagents .= '('.$items[$temp_data['id']].')';
                        }

                    }


                    $data[0]['required_reagents'] = $reagents;

                    if($data[0]['required_weapons'] == '')
                        $data[0]['required_weapons'] = "N/A";

                    if($data[0]['required_reagents'] == '')
                        $data[0]['required_reagents'] = "N/A";

                    // If highest & special isn't weapon
                    if( $data[0]['attack_type'] == "highest" && $specialization[0] !== "W" ){
                        $data[0]['required_items'] = "N/A";
                    }

                    if ($data[0]['attack_type'] == "highest" && isset($specialization[0]))
                    {
                        if($specialization[0] == '0')
                        {
                            $data[0]['attack_type'] = 'n/a';
                            $data[0]['tags'] = '';
                        }

                        else if($specialization[0] == 'T')
                        {
                            $data[0]['attack_type'] = 'Taijutsu';

                        }

                        else if($specialization[0] == 'N')
                        {
                            $data[0]['attack_type'] = 'Ninjutsu';
                        }

                        else if($specialization[0] == 'G')
                        {
                            $data[0]['attack_type'] = 'Genjutsu';
                        }

                        else if($specialization[0] == 'W')
                        {
                            $data[0]['attack_type'] = 'Bukijutsu';
                        }
                    }

                    // Set the village
                    $data[0]['village'] = ($data[0]['village'] == null) ? "All" : ucfirst(strtolower($data[0]['village']));

                    // Set description
                    $data[0]['description'] = functions::parse_BB($data[0]['description'],false);

                    // Set some nice information about mastery reduction
                    $GLOBALS['userdata'][0]['element_mastery_1'] = $elements->getUserElementMastery(1);
                    $GLOBALS['userdata'][0]['element_mastery_2'] = $elements->getUserElementMastery(2);;

                    // Get special element mastery
                    $GLOBALS['userdata'][0]['element_mastery_special'] = $elements->getUserElementMastery(3);

                    // Reduce max uses based on elemental mastery
                    $perc = $elements->checkMasteryBonus($data[0]['element'], "MaxUses",$data[0]['required_rank']);
                    if( $perc !== false && $perc > 0 ){
                        $data[0]['max_uses'] = $data[0]['max_uses']." <span style='color:red;'>(Low mastery: -".$perc.")</span>";
                    }

                    // Check if jutsu is time-limited
                    if( !functions::checkStartEndDates($data[0]) ){
                        $data[0]['max_uses'] = 0;
                        $data[0]['specialNote'] = "<span style='color:red;'>Available from ".date("m/d/Y",$data[0]['start_date'])." to ".date("m/d/Y",$data[0]['end_date'])."</span>";
                    }

                    // Set the rank
                    $data[0]['required_rank'] = Data::$RANKNAMES[ $data[0]['required_rank'] ];

                    // Pass to smarty
                    $GLOBALS['template']->assign('data', $data[0]);

                    $GLOBALS['template']->assign('effects',
                            $this->parseEffects($data[0]['tags'])
                    );

                    $GLOBALS['template']->assign('contentLoad', './templates/content/myjutsu/jutsuDetails.tpl');

                    // Return Link
                    $GLOBALS['template']->assign("returnLink", true);
                } else {
                    throw new Exception("You cannot view this jutsu because you do not know it.");
                }
            } else {
                throw new Exception("This jutsu does not exist, or you do not know this jutsu");
            }
        } else {
            throw new Exception('Incorrect variable passed on: ' . $jid);
        }
    }

    // Do jutsu forget
    public function jutsu_do_forget() {
        if (is_numeric($_GET['jid'])) {
            if( $_GET['jid'] !== $GLOBALS['userdata'][0]['jutsu'] ){
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_jutsu` WHERE `users_jutsu`.`jid`  = '" . $_GET['jid'] . "' AND `uid` = '" . $_SESSION['uid'] . "'");
                if ($data != '0 rows') {
                    if ($data[0]['level'] <= 50) {
                        $GLOBALS['database']->execute_query("DELETE FROM `users_jutsu` WHERE `uid` = '" . $_SESSION['uid'] . "' AND `jid` = '" . $_GET['jid'] . "' LIMIT 1");
                        $GLOBALS['page']->Message('The jutsu has been forgotten.', 'Forget Jutsu Success', 'id=' . $_GET['id']);
                    } else {
                        throw new Exception('You cannot forget jutsu at lvl 50 or above.');
                    }
                } else {
                    throw new Exception('You do not know this jutsu, and thus cannot forget it.');
                }
            }
            else{
                throw new Exception("You cannot forget a jutsu which you are currently training");
            }

        } else {
            throw new Exception('Incorrect variable passed on: ' . $_GET['jid']);
        }
    }

}
