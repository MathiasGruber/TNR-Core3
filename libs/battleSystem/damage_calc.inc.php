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

abstract class calc {
    /*
     * 		Calculate PERC damage of health, useless function but just in case
     * 		We want to elaborate on it, it's been standardized here.
     */
    public static function calc_perc_damage($target_data, $percent, $perc_of = 'max') {
        return (($target_data[$perc_of.'_health'] / 100) * $percent);
    }

    /*
     * 		Damage calculation for dual offense attacks
     * 		These include the STCHA attack, as well as all weapons with a DMG modifier other than weap.
     */
    public static function calc_double_damage($params) {
        
        // Fix variables
        $user_data = (isset($params['user_data'])) ? $params['user_data'] : false;
        $target_data = (isset($params['target_data'])) ? $params['target_data'] : false;
        $type1 = (isset($params['type1'])) ? $params['type1'] : false;
        $type2 = (isset($params['type2'])) ? $params['type2'] : false;
        $stat1 = (isset($params['stat1'])) ? $params['stat1'] : false;
        $stat2 = (isset($params['stat2'])) ? $params['stat2'] : false;
        $power  = (isset($params['power'])) ? $params['power'] : 0;
        $returnAll = (isset($params['returnAll'])) ? $params['returnAll'] : false;
        
        /*
         * 				Offensive side
         */
        $allData = array(); // Array for storing all the data
        
        //	Set balance / desparation
        $allData['life_perc'] = round(($user_data['cur_health'] / $user_data['max_health']) * 100);
        $allData['desparation'] = calc::calculate_desparation($allData['life_perc'], $user_data);
        $allData['balance'] = calc::calculate_balance($allData['life_perc'], $user_data);
        
        //	Calculate random factor:
        $allData['randomFactor1'] = (($allData['balance'] > $allData['desparation']) ? random_int($allData['desparation'], $allData['balance'])
            :  random_int($allData['balance'], $allData['desparation'])) / 100;

        //	 Set factors
        $allData['userStat1Factor'] = sqrt($user_data[$stat1] / 20);
        $allData['userStat2Factor'] = ceil($user_data[$stat2] / 40);
        $allData['userOffence'] = ($user_data[$type1 . '_off'] + $user_data[$type2 . '_off']) / 2;
        
        //	Calculate power factor
        $power = 1 + sqrt($power / 5);
        
        //	Calculate damage
        $allData['pureUserDamage'] = round(($allData['randomFactor1'] * (sqrt(($allData['userOffence'] * $power)
            * $allData['userStat1Factor']) + $allData['userStat2Factor'])) * 2);
        /*
         * 				Defensive side
         */
        $allData['targetDefence'] = ($target_data[$type1 . '_def'] + $target_data[$type2 . '_def']) / 2;
        
        // Do the armor reduction of the damage
        $damage = calc::calc_armorbonus(round($allData['pureUserDamage'] - $allData['targetDefence']), $target_data['armor']);
        $allData['armorReducedDamage'] = $damage;
        
        // Calculate the final damage
        if ($damage > 0) {
            $damage = $damage - random_int(46, 54) / 100 * $damage * ( (($target_data[$type1 . '_def'] + $target_data[$type2 . '_def']) / 2) / (($user_data[$type1 . '_off'] + $user_data[$type2 . '_off']) / 2 + 200) ) + 60;
            $damage = 0.4 * round($damage, 1);
        }

        if ($damage < 0) {
            $damage = 0;
        }
        
        // Save the final damage
        $allData['finalDamage'] = $damage;
        
        // Decide whether to return just damage or all the calculation data
        return (($returnAll === false) ? $damage : $allData);
    }

    /*
     * 		Calculate and return the entity's balance, used in damage calculations
     * 		Seperate function to simplify modifications.
     */

    public static function calculate_balance($life_perc, $user_data) {
        $balance = (47 + sqrt(0.32 * ($user_data['willpower'] + $user_data['intelligence'] + $user_data['strength'] 
            + $user_data['speed']) / 4) + (20 - ($life_perc / 5)));
        //echo"Balance: ".$balance."<br>";
        return $balance;
    }

    /*
     * 		Calculate and return entity's desparation, used in damage calculations
     * 		Seperate function to simplify modifications.
     */

    public static function calculate_desparation($life_perc, $user_data) {
        $desperation = (67 + $life_perc / 5 + sqrt(0.29 * 
            ($user_data['willpower'] + $user_data['intelligence'] + $user_data['strength'] + $user_data['speed']) / 4));
        //echo"Desperation: ".$desperation."<br>";
        return $desperation;
    }

    /*
     * 		Calculates value without deducting a defensive value.
     * 		used for effects that ignore defense, or calculation of healing effects
     * 		Outcome is lower than that of the offensive part of the damage calculation
     */

    public static function calc_value($user_data, $type, $stat, $power = 0) {
        //	Set balance / desparation
        $life_perc = round(($user_data['cur_health'] / $user_data['max_health']) * 100);
        $desparation = calc::calculate_desparation($life_perc, $user_data);
        $balance = calc::calculate_balance($life_perc, $user_data);
        
        //	Calculate random factor:
        $rand = random_int($balance, $desparation) / 100;
        
        //	Calculate / set factors
        $factor1 = $user_data[$stat] / 5;
        $offense = $user_data[$type . '_off'];
        
        //	Calculate power factor
        $power = 1 + sqrt($power / 5);
        
        //	Calculate value
        $value = $rand * (sqrt(($offense * $power) * $factor1));
        return (($value < 0) ? 0 : $value);
    }

    /*
     * 		Damage calculation, regular
     * 		Utilizes both the defensive and offensive factors of a single type.
     * 		Utilized by all CALC damage generating effects
     */

    public static function calc_damage($params) {
        // Fix variables
        $user_data = ( isset( $params['user_data'] ) ) ? $params['user_data'] : false;
        $target_data = ( isset( $params['target_data'] ) ) ? $params['target_data'] : false;
        $type = ( isset( $params['type'] ) ) ? $params['type'] : false;
        $stat1 = ( isset( $params['stat1'] ) ) ? $params['stat1'] : false;
        $stat2 = ( isset( $params['stat2'] ) ) ? $params['stat2'] : false;
        $power  = ( isset( $params['power'] ) ) ? $params['power'] : 0;
        $returnAll = ( isset( $params['returnAll'] ) ) ? $params['returnAll'] : false;
        $jutsu  = ( isset( $params['jutsu'] ) ) ? $params['jutsu'] : false;
        
        /*
         * 					Offensive part
         */
        // Array for storing all the data
        $allData = array();
        
        //	Set balance / desparation
        $allData['life_perc'] = round(($user_data['cur_health'] / $user_data['max_health']) * 100);
        $allData['desparation'] = calc::calculate_desparation( $allData['life_perc'], $user_data );
        $allData['balance'] = calc::calculate_balance( $allData['life_perc'], $user_data );
        
        //	Calculate random factor:
        $allData['randomFactor1'] = random_int($allData['balance'], $allData['desparation']) / 100;

        //	 Set factors
        $allData['userStat1Factor'] = sqrt($user_data[$stat1] / 10);
        $allData['userStat2Factor'] = $user_data[$stat2] / 20;
        $allData['userOffence'] = $user_data[$type . '_off'];

        //	Calculate power rating
        $power = 1 + sqrt($power / 5);

        //	Calculate damage
        $allData['pureUserDamage'] = $allData['randomFactor1'] * (sqrt(($allData['userOffence'] * $power) * $allData['userStat1Factor']) + $allData['userStat2Factor']);
        $allData['pureUserDamage'] = round($allData['pureUserDamage'] * 2);

        /*
         * 					Defensive part
         */
        $allData['targetDefence'] = $target_data[$type . '_def'];
        $allData['targetStat1Defence'] = $target_data[$stat1] / 5;

        //	Calculate Defense
        $allData['totalTargetDefence'] = round(sqrt($allData['targetDefence'] * $allData['targetStat1Defence']));

        $damage = calc::calc_armorbonus(round($allData['pureUserDamage'] - $allData['totalTargetDefence']), $target_data['armor']);
        $allData['armorReducedDamage'] = $damage;
        
        if ($damage > 0) {
            $damage = $damage - random_int(46, 54) / 100 * $damage * ( $target_data[$type . '_def'] / ($user_data[$type . '_off'] + 200) ) + 60;
            $damage = 0.4 * round($damage, 1);
        }

        if ($damage < 0) {
            $damage = 0;
        }
        
        // Correct if jutsu rank is below user rank
        if( $jutsu !== false ){
            if( isset( $jutsu['requiredRank'] ) && isset( $user_data['rank_id'] ) ){
                $diff = $user_data['rank_id'] - $jutsu['requiredRank'];
                if( $diff > 0){
                    for( $i=1 ; $i <= $diff; $i++ ){
                        $damage *= 0.5;
                    }
                }
            }
        }
        
        // Save the final damage
        $allData['finalDamage'] = $damage;
        
        // Decide whether to return just damage or all the calculation data
        return (($returnAll === false) ? $damage : $allData);
    }

    /*
     *      Deduct armor damage reduction bonus from the damage
     *      Returns reduced damage, called internally
     */

    private static function calc_armorbonus($damage, $armor) {
        $reduction = (pow(60, -8) * pow($armor, 2) + pow(40, -4) * $armor) * 2600;
        return round($damage - (($damage / 100) * $reduction));
    }
}