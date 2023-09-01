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

// Include neccesary libraries
require('../libs/battleSystem/damage_calc_v2.inc.php');

// Test class
class battle_test {
/*
    // Constructor
    public function __construct() {
        
        // Construct data
        $this->constructUserData();
        
        // Test damage if requested
        if( isset($_POST['JutsuSubmit']) ){
            $this->test_dam('Jutsu');
        }
        if( isset($_POST['AttSubmit']) ){
            $this->test_dam( $_POST['AttSubmit'] );
        }
        if( isset($_POST['WeaponSubmit']) ){
            $this->test_dam('Weapon');
        }
        
        // Load the template wrapper
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/DAM_calculator/main.tpl');
    }

    // Define the user data from either POST or default
    private function constructUserData() {

        // Define arrays
        $this->attacker = array();
        $this->target = array();
        
        // Do both
        foreach( array("attacker", "target") as $side ){
            if( isset($_POST['JutsuSubmit']) || isset($_POST['AttSubmit']) || isset($_POST['WeaponSubmit']) ){

                // Set data based on submit data
                $this->{$side}['max_health'] = $_POST[$side . '_max_health'];
                $this->{$side}['cur_health'] = $_POST[$side . '_cur_health'];
                $this->{$side}['strength'] = $_POST[$side . '_strength'];
                $this->{$side}['intelligence'] = $_POST[$side . '_intelligence'];
                $this->{$side}['speed'] = $_POST[$side . '_speed'];
                $this->{$side}['willpower'] = $_POST[$side . '_willpower'];
                $this->{$side}['tai_off'] = $_POST[$side . '_tai_off'];
                $this->{$side}['tai_def'] = $_POST[$side . '_tai_def'];
                $this->{$side}['nin_off'] = $_POST[$side . '_nin_off'];
                $this->{$side}['nin_def'] = $_POST[$side . '_nin_def'];
                $this->{$side}['gen_off'] = $_POST[$side . '_gen_off'];
                $this->{$side}['gen_def'] = $_POST[$side . '_gen_def'];
                $this->{$side}['weap_off'] = $_POST[$side . '_weap_off'];
                $this->{$side}['weap_def'] = $_POST[$side . '_weap_def'];
                $this->{$side}['armor'] = $_POST[$side . '_armor'];
                $this->{$side}['rank_id'] = $_POST[$side . '_rank_id'];
            }
            else{
                // Set the default to capped Elite Jounin
                $this->{$side}['max_health'] = Data::$MAX_HP_1;
                $this->{$side}['cur_health'] = Data::$MAX_HP_1;
                $this->{$side}['strength'] = Data::$GEN_MAX_1;
                $this->{$side}['intelligence'] = Data::$GEN_MAX_1;
                $this->{$side}['speed'] = Data::$GEN_MAX_1;
                $this->{$side}['willpower'] = Data::$GEN_MAX_1;
                $this->{$side}['tai_off'] = Data::$ST_MAX_1;
                $this->{$side}['tai_def'] = Data::$ST_MAX_1;
                $this->{$side}['nin_off'] = Data::$ST_MAX_1;
                $this->{$side}['nin_def'] = Data::$ST_MAX_1;
                $this->{$side}['gen_off'] = Data::$ST_MAX_1;
                $this->{$side}['gen_def'] = Data::$ST_MAX_1;
                $this->{$side}['weap_off'] = Data::$ST_MAX_1;
                $this->{$side}['weap_def'] = Data::$ST_MAX_1;
                $this->{$side}['armor'] = 0;
                $this->{$side}['rank_id'] = 1;
            }
            
            // Push to smarty
            $GLOBALS['template']->assign($side.'_data', $this->{$side});
        }
        
        // Send stuff from data.class to smarty
        for( $i = 1; $i <= 5; $i++ ){
            $GLOBALS['template']->assign('HP_'.$i, Data::${'MAX_HP_'.$i});
            $GLOBALS['template']->assign('ST_'.$i, Data::${'ST_MAX_'.$i});
            $GLOBALS['template']->assign('GEN_'.$i, Data::${'GEN_MAX_'.$i});
        }
        
        // Get jutsu power values for each rank
        for( $rankID=1; $rankID <= 5; $rankID++ ){
            
            // For calculating average
            $number = 0;
            $totalPower = 0;
            $squareTotalPower = 0;
            $totalIncrease = 0;
            $squareTotalInc= 0;
            
            // Get jutsus
            $jutsus = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE (`effect_1` LIKE 'DAM%' OR `effect_2` LIKE 'DAM%' OR `effect_3` LIKE 'DAM%' OR `effect_4` LIKE 'DAM%') AND `required_rank` = '".$rankID."'");
            foreach( $jutsus as $jutsu ){
            
                // Go through each effect
                foreach( array("effect_1","effect_2","effect_3","effect_4") as $column ){
                    if( preg_match("/^DAM.CALC.+$/", $jutsu[$column])  ){
                        $temp = explode( ":", $jutsu[$column] );
                        $number++;
                        $totalPower += $temp[2];
                        $squareTotalPower += $temp[2] * $temp[2];
                        $totalIncrease += $temp[3];
                        $squareTotalInc += $temp[3] * $temp[3];
                    }
                }
            }
            
            $avgPower = round($totalPower / $number, 2);
            $deviationPower = round(sqrt( $squareTotalPower/$number - $avgPower*$avgPower ), 2);
            $GLOBALS['template']->assign('jutsu_'.$rankID."_power" , "Rank".$rankID.": Avg Power: ".$avgPower."&plusmn;".$deviationPower);
            
            $avgInc = round($totalIncrease / $number, 2);
            $deviationInc = round( sqrt( $squareTotalInc/ $number - $avgInc * $avgInc) , 2 );
            $GLOBALS['template']->assign('jutsu_'.$rankID."_inc" , "Rank".$rankID.": Avg Increment: ".$avgInc."&plusmn;".$deviationInc);
        }
        
        // Get weapon  power values for each rank
        for( $rankID=1; $rankID <= 5; $rankID++ ){
            
            // For calculating average
            $number = 0;
            $totalPower = 0;
            $squareTotalPower = 0;
            $totalIncrease = 0;
            $squareTotalInc= 0;
            
            // Get jutsus
            $items = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE (items.use LIKE 'DMG%' OR items.use2 LIKE 'DMG%') AND `required_rank` = '".$rankID."'");
            foreach( $items as $item ){
            
                // Go through each effect
                foreach( array("use","use2") as $column ){
                    if( preg_match("/^DMG.+$/", $item[$column]) && ($item['type'] == "weapon" || stristr($item[$column], "CALC"))  ){
                        
                        $temp = explode( ":", $item[$column] );
                        
                        // Get the base
                        $base = 0;
                        switch( $item['type'] ){
                            case "weapon": 
                                $base = $temp[2]; 
                            break;
                            case "item": 
                                $base = $temp[3];                                 
                            break;
                        }
                        
                        // If strength
                        if( $base == "STR" ){
                            $base = $item['strength'];
                        }
                        
                        $number++;
                        $totalPower += $base;
                        $squareTotalPower += $base*$base;
                    }
                }
            }
            
            $avgPower = round($totalPower / $number, 2);
            $deviationPower = round(sqrt( $squareTotalPower/$number - $avgPower*$avgPower ), 2);
            $GLOBALS['template']->assign('item_'.$rankID."_power" , "Rank".$rankID.": Avg Power: ".$avgPower."&plusmn;".$deviationPower);
            
        }
        
        // Get Armor values
        $armorText = "<br>";
        for( $rankID=1; $rankID <= 5; $rankID++ ){
            
            $armors = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `type` = 'armor' AND `required_rank` = '".$rankID."'");

            $totalPower = 0;
            $squareTotalPower = 0;
            foreach( $armors as $armor ){
                $totalPower += $armor['strength'];
                $squareTotalPower += $armor['strength']*$armor['strength'];
            }
            $avgPower = round($totalPower / count($armors), 2);
            $deviationPower = round(sqrt( $squareTotalPower/count($armors) - $avgPower*$avgPower ), 2);
            $armorText .= " - RankID".$rankID.": Avg Armor: ".$avgPower."&plusmn;".$deviationPower;
        }
        $GLOBALS['template']->assign('armors_power' , $armorText );
    }

    // Test the different damage formulaes
    private function test_dam($type) {
        
        // Tell the user what is being tested
        $GLOBALS['template']->append('calcDebug', "Now Checking Type: ".$type);
        
        // Amount of damage tests to run
        $tests = 1;
        if( isset($_POST['jutTests']) && $_POST['jutTests'] > 0){
            $tests = ($_POST['jutTests'] > 10) ? 10 : $_POST['jutTests'];
            $tests = ($_POST['jutTests'] < 1) ? 1 : $tests;
        }
        if( isset($_POST['weapTests']) && $_POST['weapTests'] > 0){
            $tests = ($_POST['weapTests'] > 10) ? 10 : $_POST['weapTests'];
            $tests = ($_POST['weapTests'] < 1) ? 1 : $tests;
        }
        $GLOBALS['template']->append('calcDebug', "Amount of tests being run: ".$tests.". Average is shown. ");
        
        if ($type == 'Jutsu') {
            
            // Calculate jutsu power
            $power = ($_POST['jutLvl'] * $_POST['jutIncrease']) + $_POST['jutPower'];
            $GLOBALS['template']->append('calcDebug', "Jutsu power (lvl*increase+base) being calculated. <b>Result = ".$power."</b>");
            
            // Jutsu variable
            $jutsuDBentry = array( "requiredRank" => $_POST['jutRank'] );
            
            // Calculate the damage
            $GLOBALS['template']->append('calcDebug', "Initiating damage calculation with <i>".$_POST['statSelect'].", ".$_POST['stat1'].", and ".$_POST['stat2']."</i>");
            $calcArray = array();
            for( $i=0 ; $i < $tests ; $i++ ){
                
                $damage = calc::calc_damage( 
                    array(
                        "user_data" => $this->attacker, 
                        "target_data" => $this->target, 
                        "type" => $_POST['statSelect'], 
                        "stat1" => $_POST['stat1'], 
                        "stat2" => $_POST['stat2'], 
                        "power" => $power, 
                        "jutsu" => $jutsuDBentry,
                        "returnAll" => true
                    )
                );
                
                $calcArray[] = $damage;
            }
            // Loop over all the elements
            $totalArray = $calcArray[0];
            for( $i=1 ; $i<count($calcArray) ; $i++ ){
                foreach( $calcArray[$i] as $key=>$value ){
                    $totalArray[ $key ] += $value;
                }
            }
            foreach( $totalArray as $key=>$value ){
                $totalArray[ $key ] /= $tests;
            }
            
            // Print all variables in calculation
            foreach( $totalArray as $key => $value ){
                $GLOBALS['template']->append('calcDebug', "Damage calc variable '".$key."' = <b>".$value."</b>");
            }
            
            // Description
            $GLOBALS['template']->assign('description', '
                Here are some of the formulas used: <br><br>
                http://www.theninja-forum.com/index.php?/topic/45707-battle-formula/page-2#entry554803
            '
            );
            
            
            
            /*
            $GLOBALS['template']->assign('description', '
                Here are some of the formulas used: <br><br>
                <b>desperation</b> = (67 + life_perc / 5 + sqrt(0.29 * (user_willpower + user_intelligence + user_strength + user_speed) / 4))<br><br>
                <b>balance</b> = (47 + sqrt(0.32 * (user_willpower + user_intelligence + user_strength + user_speed) / 4) + (20 - (life_perc / 5)))<br><br>
                <b>randomFactor1</b> = random_int(balance, desperation) / 100<br><br>
                <b>userStat1Factor</b> = sqrt(user_stat1 / 10)<br><br>
                <b>userStat2Factor</b> = user_stat2 / 20<br><br>
                <b>pureUserDamage</b> = randomFactor1 * (sqrt((user_Offence * (1 + sqrt(jutsu_power / 5)) ) * userStat1Factor) + userStat2Factor)<br><br>
                <b>targetStatDefence</b> = target_stat1 / 5<br><br>
                <b>totalTargetDefence</b> = round(sqrt(targetDefence * targetStat1Defence))<br><br>
                <b>armorReducedDamage:</b><br>
                -- <b>tempDam</b> = round(pureUserDamage - totalTargetDefence)<br>
                -- <b>tempReduction</b> = (pow(60, -8) * pow(target_armor, 2) + pow(40, -4) * target_armor) * 2600;<br>
                -- <b>armorReducedDamage</b> = tempDam - ((tempDam / 100) * tempReduction)<br><br>
                <b>finalDamage</b> = 0.4 * (armorReducedDamage - random_int(46, 54) / 100 * armorReducedDamage * ( targetDefence / (userOffence + 200) ) + 60)
            '
            ); *\
        } 
        else{
            switch( $type ){
                case "Chakra": 
                    $stat1 = "nin";
                    $stat2 = "gen";
                    $gen1 = "willpower";
                    $gen2 = "intelligence";
                    $power = 1000;
                    $scaleFactor = 0.1;
                break;
                case "Tai": 
                    $stat1 = "tai";
                    $stat2 = "weap";
                    $gen1 = "strength";
                    $gen2 = "speed";
                    $power = 1000;
                    $scaleFactor = 0.1;
                break;
                case "Weapon": 
                    $stat1 = $_POST['weapSelect'];
                    $stat2 = "weap";
                    $gen1 = "strength";
                    $gen2 = "speed";
                    $power = $_POST['weapPower'];
                    $scaleFactor = 1;
                break;
            }
            
            // Calculate the damage
            $GLOBALS['template']->append('calcDebug', "Initiating damage calculation with <i>".$stat1.", ".$stat2.", ".$gen1.", and ".$gen2."</i>");
            $calcArray = array();
            for( $i=0 ; $i < $tests ; $i++ ){
                $calcArray[] = calc::calc_double_damage(
                    array(
                        "user_data" => $this->attacker, 
                        "target_data" => $this->target, 
                        "type1" => $stat1, 
                        "type2" => $stat2, 
                        "stat1" => $gen1, 
                        "stat2" => $gen2, 
                        "power" => $power,
                        "scalePower" => $scaleFactor, 
                        "returnAll" => true
                    )
                );
            }
            
            // Loop over all the elements
            $totalArray = $calcArray[0];
            for( $i=1 ; $i<count($calcArray) ; $i++ ){
                foreach( $calcArray[$i] as $key=>$value ){
                    $totalArray[ $key ] += $value;
                }
            }
            foreach( $totalArray as $key=>$value ){
                $totalArray[ $key ] /= $tests;
            }
            
            
            // Print all variables in calculation
            foreach( $totalArray as $key => $value ){
                $GLOBALS['template']->append('calcDebug', "Damage calc variable '".$key."' = <b>".$value."</b>");
            }
            
            // Formulas
            /*
            $GLOBALS['template']->assign('description', '
                Here are some of the formulas used: <br><br>
                <b>desperation</b> = (67 + life_perc / 5 + sqrt(0.29 * (user_willpower + user_intelligence + user_strength + user_speed) / 4))<br><br>
                <b>balance</b> = (47 + sqrt(0.32 * (user_willpower + user_intelligence + user_strength + user_speed) / 4) + (20 - (life_perc / 5)))<br><br>
                <b>randomFactor1</b> = random_int(balance, desperation) / 100<br><br>
                <b>userStat1Factor</b> = sqrt(user_stat1 / 20)<br><br>
                <b>userStat2Factor</b> = user_stat2 / 40<br><br>
                <b>userOffence</b> = (user_stat1_offence + user_stat2_offence) / 2<br><br>
                <b>pureUserDamage</b> = randomFactor1 * (sqrt((user_Offence * (1 + sqrt(weap_power / 5)) ) * userStat1Factor) + userStat2Factor)<br><br>
                <b>targetDefence</b> = (target_stat1_defence + target_stat2_defence) / 2<br><br>
                <b>armorReducedDamage:</b><br>
                -- <b>tempDam</b> = round(pureUserDamage - totalTargetDefence)<br>
                -- <b>tempReduction</b> = (pow(60, -8) * pow(target_armor, 2) + pow(40, -4) * target_armor) * 2600;<br>
                -- <b>armorReducedDamage</b> = tempDam - ((tempDam / 100) * tempReduction)<br><br>
                <b>finalDamage</b> = 0.4 * (armorReducedDamage - random_int(46, 54) / 100 * armorReducedDamage * ( targetDefence / (userOffence + 200) ) + 60)
            '
            ); *\
        }
        
        $tab = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $GLOBALS['template']->assign('description', '
            First, the total user and target offences/defences and generals are calculated: <br>
            '.$tab.'<b>User_Offence</b> = <br>'.$tab.''.$tab.'Either one offence (e.g. off_nin) or two (e.g. 0.7*off_nin + 0.3 * off_weap)<br>
            '.$tab.'<b>User_Generels</b> = <br>'.$tab.''.$tab.'Calculated from two generals (e.g. 0.7 * strength + 0.3*willpower)<br>
            '.$tab.'<b>Target_Defence</b> = <br>'.$tab.''.$tab.'Either one defence (e.g. def_nin) or two (e.g. 0.7*def_nin + 0.3 * def_weap) * (1+armor/100)<br>
            '.$tab.'<b>Target_Generels</b> = <br>'.$tab.''.$tab.'Calculated from two generals (e.g. 0.7 * strength + 0.3*willpower)<br><br>  

            Target scale. This is to ensure that for weak opponents, defence matters more, and avoid excessive damage against weak users<br>
            '.$tab.'<b>Target_Defence</b> = 350 * Target_Defence ^ 0.48<br>
            '.$tab.'<b>Target_Generels</b> = 350 * Target_Generels ^ 0.48<br><br>

            Then calculate the ratios between user stats/gens and target stats/gens:<br>                
            '.$tab.'<b>offToDefence</b> = (User_Offence / Target_Defence)^0.1<br>     
            '.$tab.'<b>genToGen</b> = (User_Generels / Target_Generels)^0.1<br><br>  

            From the ratios, calculate a "battle factor", which describes the difference in strength between the characters, and the pure offence <br>
            '.$tab.'<b>battleFactor</b> = offToDefence * genToGen<br><br>      

            Then calculate the pure offensive power of the attacker as:<br>
            '.$tab.'<b>pureOffence</b> = User_Offence + User_Generels * 10 + attackPower * 100 <br><br>      

            The final damage is not calculated from the battleFactor between attacker and target, and the offensive strength of the attacker<br>
            '.$tab.'<b>initialDamage</b> = battleFactor^3.5 * pureOffence * 0.2<br><br>      

            Finally, the final damage dealt is scaled down, which is done to accomodate ~10 rounds of battle.<br>
            '.$tab.'<b>finalDamage</b> = 0.1 * initialDamage<br><br> 
        '
        );
    }*/
}

new battle_test();