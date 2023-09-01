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
 *Class: Tag
 *  this class defines what a tag is and parses it from text.
 *
 */

class Tag
{
    public $debugging;

    //data not from fields
    public $name = '';
    public $origin = '';
    public $owner = '';
    public $equipment_id = false;
    public $age = false;
    public $effect_level = false; //any positive number and zero
    public $resultOfAoe = false; //yes, no
    public $weapon_boost = false;
    public $weapon_ids = false;


    //all possible fields and their default values.
    public $target = 'target'; //other, target, team, all
    public $value = true; //array(base, baseType, increment, incrementType) can be missing anything.
    public $duration = true; //any possitive int
    public $delay = false; //any possitive int
    public $cadence = false; //and possitive in.
    public $degrade = false; //any percentage and flat value. format: 100% also a flat limit can be added
    public $degradeEffect = false; 
    public $amplify = false; //any percentage and flat value. format: 100% also a flat limit can be added
    public $amplifyEffect = false; 
    public $recoil = false; //any percentage and flat value.
    public $leach = false; //and percentage and flat value.
    public $targetType = true; //Taijutsu, ninjutsu, genjutsu, bukijutsu, all
    public $targetElement = true; //water, earth, lightning, fire, air, none, all
    public $targetOrigin = true; //bloodline, armor, jutsu, weapon, item, all
    public $targetGeneral = true; //strength intelligence willpower speed, all
    public $targetImmunity = true; //damage, bleed, recoil, reflect
    public $targetPool = true; //health, stamina, charka
    public $targetStat = true; //all armor set stats
    public $targetInEffect = 'X'; //false or true or all
    public $targetPolarity = false; //+ or -
    public $targetAge = false; //any int
    public $targetTagCategory = false; //done know yet.
    public $targetTag = false; //name of a tag
    public $targetEquipment = false; //name of a type of equipment
    public $priority = false; //1, 2, 3
    public $noClear = false; //stops the clear tag
    public $noDelay = false; //stops the delay tag
    public $persistAfterDeath = false; //yes, no
    public $noStack = false; //yes, no, true, false, 1, 0
    public $noStackAoe = false; //yes, no, ture, flase, 1, 0
    public $override = false; // true or false
    public $missTargetChance = false; // anywhere between 100 and 0
    public $wrongTargetChance = false; // anywhere between 100 and 0
    public $failureChance = false; // anywhere between 100 and 0
    public $backfireChance = false; // anywhere between 100 and 0
    public $polaritySwitchChance = false; // anywhere between 100 and 0
    public $statBased = true; //1 yes true 0 no false
    public $aiName = true; //1 yes true 0 no false
    public $aiId = true; //1 yes true 0 no false
    public $targetRestriction = false; //can be uid or aid and username or ainame



    function __construct($name, $data, $extra_data = false, $debugging = false)
    {
        //setting name and debug status.
        $this->name = $name;
        $this->debugging = $debugging;

        //removing () from around that data in the tag.
        $data = rtrim(ltrim($data, '('), ')' );

        //breaking the data into a 2d array.
        $data = explode(';',$data);

        if($extra_data !== false)
        {
            $extra_data = explode(';',$extra_data);
            $data = array_merge($data, $extra_data);
        }

        //pulling all information out of $data and setting it
        foreach($data as $value)
        {
            if($value != '')
            {
                //breaks the new keys away from value
                $temp = explode('>', $value);

                $conversion = array(
                    'yes'=>true,
                    'true'=>true,
                    '1'=>true,
                    '1.0'=>true,
                    '1.00'=>true,

                    'no'=>false,
                    'false'=>false,
                    '0'=>false,
                    '0.0'=>false,
                    '0.00'=>false,

                    'all'=>'X',

                    'bloodline'=>'B',
                    'armor'=>'A',
                    'location'=>'L',
                    'jutsu'=>'J',
                    'weapon'=>'W',
                    'item'=>'I',

                    'water'=>'W',
                    'earth'=>'E',
                    'lightning'=>'L',
                    'fire'=>'F',
                    'air'=>'A',
                    'wind' => 'A',
                    'none'=>'N',

                    'ice' => 'IC',
                    'light' => 'LG',
                    'dust' => 'DS',
                    'storm' => 'SR',
                    'lava' => 'LV',

                    'scorching' => 'SC',
                    'tempest' => 'TM',
                    'magnetism' => 'MG',
                    'wood' => 'WD',
                    'steam' => 'ST',

                    'ninjutsu'=>'N',
                    'genjutsu'=>'G',
                    'taijutsu'=>'T',
                    'bukijutsu'=>'B',

                    'chest'=>'C',

                    'helmet'=>'H',
                    'head'=>'H',

                    'gloves'=>'G',
                    'hands'=>'G',

                    'belt'=>'W',
                    'waist'=>'W',

                    'shoes'=>'F',
                    'feet'=>'F',

                    'pants'=>'L',
                    'legs'=>'L'
                    );

                //checks if the value is a sub array.
                if(substr($temp[1],0, 1) == '(')
                {
                    //if so trim () from string and break it into an array.
                    $temp[1] = explode(',', rtrim(ltrim($temp[1], '('), ')' ));

                    //for each sub value
                    $extra_elements = array();
                    foreach($temp[1] as $sub_key => $sub_value)
                    {
                        //input conversion.
                        if(isset($conversion[$temp[1][$sub_key]]))
                            //if the matched conversion is not an array
                            if(!is_array($conversion[$temp[1][$sub_key]]))
                                $temp[1][$sub_key] = $conversion[$temp[1][$sub_key]];

                            //if the matched conversion is an array
                            else
                            {
                                //get the matched conversion
                                $sub_sub_array = $conversion[$temp[1][$sub_key]];

                                //remove the key for the match
                                unset ($temp[1][$sub_key]);

                                //save the matched conversion for addition later.
                                $extra_elements = array_merge($extra_elements,$sub_sub_array);
                            }


                        //else if sub value is numeric
                        else if(is_numeric($sub_value))

                            //if sub value has a . it is float
                            if(strpos($sub_value, '.') !== false)
                                $temp[1][$sub_key] = $sub_value;//leaving floats as strings because they are smaller(unless alot of precision is needed.)

                            //if sub value does not have a . it is a int
                            else
                                $temp[1][$sub_key] = (int)$sub_value;
                    }

                    $temp[1] = array_merge($temp[1],$extra_elements);

                }

                //if not a sub array
                else

                    //input conversion.
                    if(isset($conversion[$temp[1]]))
                        $temp[1] = $conversion[$temp[1]];

                    //checking to see if the value is numeric and converting it.
                    else if(is_numeric($temp[1]))

                        //check if is float
                        if(strpos($temp[1], '.') !== false)
                            $temp[1] = $temp[1];//leaving floats as strings because they are smaller(unless alot of precision is needed.)

                        //if not float is int
                        else
                            $temp[1] = (int)$temp[1];


                //if field exists
                if(isset($this->{$temp[0]}))
                    //set the value
                    $this->{$temp[0]} = $temp[1];

                else if($this->debugging)
                    $GLOBALS['DebugTool']->push($temp[0], 'un-known field', __METHOD__, __FILE__, __LINE__);
            }
        }

    }
}