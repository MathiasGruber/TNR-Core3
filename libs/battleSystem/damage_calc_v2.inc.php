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

// Damage calculations for battle
abstract class calc {
    
    // Function that does the calculation of damage
    private static function doCalculateDamage( $params ){
        
        // Adjustment Variables
        $genToStat = 10;        // Effectiveness of generals in dealing damage compared to stats
        $battleFactorExp = 3.5; // Scaling between offence and defence
        $typeRatio = 0.7;       // Ratio of main offence to second offence
        $genRatio = 0.7;        // Ratio of main gen to second gen
        $statsExp = 0.1;
        $genExp = 0.1;
        $offenceExp = 0.2;
        $damageExp = 0.05;
        $finalScale = 0.1; // Final scaling of damage
        $armorExp = -0.05;
        
        // Scaling of defence
        $defToOff = 351;
        $scaleDownDef = 0.48;
        
        // Fix variables
        $user_data = (isset($params['user_data'])) ? $params['user_data'] : false;
        $target_data = (isset($params['target_data'])) ? $params['target_data'] : false;        
        $stat1 = (isset($params['stat1'])) ? $params['stat1'] : false;
        $stat2 = (isset($params['stat2'])) ? $params['stat2'] : false;
        $power  = (isset($params['power'])) ? $params['power'] : 0;
        $powerScaling  = (isset($params['scalePower'])) ? $params['scalePower'] : 0;
        $jutsu  = ( isset( $params['jutsu'] ) ) ? $params['jutsu'] : false;
        $returnAll = (isset($params['returnAll'])) ? $params['returnAll'] : false;
        
        // Get types
        $type = ( isset( $params['type'] ) ) ? $params['type'] : null;  
        $type1 = (isset($params['type1'])) ? $params['type1'] : null;
        $type2 = (isset($params['type2'])) ? $params['type2'] : null;
                
        // Array for storing all the data
        $allData = array();
        
        // User/Target Offence/Defences       
        $allData['user_offence'] = (isset($type)) ? $user_data[$type . '_off'] : ($user_data[$type1 . '_off'] * $typeRatio + $user_data[$type2 . '_off'] * (1-$typeRatio)) ;
        $allData['target_defence'] = (isset($type)) ? $target_data[$type . '_def'] : ($target_data[$type1 . '_def'] * $typeRatio + $target_data[$type2 . '_def'] * (1-$typeRatio)) ;
        
        // Add armor value to defence       
        if( $target_data['armor'] !== 0 ){
            $allData['target_defence'] += $allData['target_defence'] * ($target_data['armor'] / 100);
        }
        
        // Scale power if required
        $allData['attackPower'] = $power * 100;
        if( $powerScaling !== 0 ){
            $allData['attackPower'] *= ( 1 + $powerScaling * $allData['user_offence'] / 5000000 );
        }
        
        // User Generals
        $allData['user_generels'] = $genRatio * $user_data[ $stat1 ] + (1-$genRatio) * $user_data[ $stat2 ];
        $allData['target_generels'] = $genRatio * $target_data[ $stat1 ] + (1-$genRatio) * $target_data[ $stat2 ];
        
        // Scale target defence & gens. This is done to increase its effect at high RSF, and decrease at low RSF
        $allData['target_generels'] = $defToOff * pow($allData['target_generels'], $scaleDownDef );
        $allData['target_defence'] = $defToOff * pow($allData['target_defence'], $scaleDownDef );
        
        // Calculate offence to defence ratios
        $allData['offToDefence'] = pow( $allData['user_offence'] / ($allData['target_defence']+1), $statsExp);
        $allData['genToGen'] = pow( $allData['user_generels'] / ($allData['target_generels']+1), $statsExp);
        $allData['battleFactor'] = $allData['offToDefence'] * $allData['genToGen'];
        
        // Calculate pure offence
        $allData['pureOffence'] = $allData['user_offence'] + $allData['user_generels'] * $genToStat + $allData['attackPower'];
        
        // Calculate initial damage
        $allData['initialDamage'] = pow( $allData['battleFactor'],  $battleFactorExp) * $allData['pureOffence'] * $offenceExp;
                
        // Final damage scaling
//        $allData['finalDamage'] = round($allData['initialDamage'] * $finalScale, 2);
        $allData['finalDamage'] = floor($allData['initialDamage'] * $finalScale);

        // Decide whether to return just damage or all the calculation data
        return (($returnAll === false) ? $allData['finalDamage'] : $allData);        
    }
    
    /* Damage calculation for dual offense attacks
     * These include the STCHA attack, as well as all weapons with a DMG modifier other than weap.
     */
    public static function calc_double_damage($params) {
        return self::doCalculateDamage($params);
    }
    
    /* Damage calculation, regular
     * Utilizes both the defensive and offensive factors of a single type.
     * Utilized by all CALC damage generating effects
     */
    public static function calc_damage($params) {
        return self::doCalculateDamage($params);
    }
    
    /* Calculates value without deducting a defensive value.
     * used for effects that ignore defense, or calculation of healing effects
     * Outcome is lower than that of the offensive part of the damage calculation
     */
    public static function calc_value($user_data, $type, $stat, $power = 0) {
        return self::doCalculateDamage($params);
    }
    
}