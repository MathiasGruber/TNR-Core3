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

class jutsu extends basicFunctions {

    public function jutsu(
            $master, 
            $user, 
            $userid, 
            $target, 
            $targetid, 
            $jutsudata, 
            $targetcompanionids, 
            $attackerCompanionIDs
    ) {
        
        // Save data in this class
        $this->master = $master;
        
        // Save the attacker in local variables
        $this->attackerSide = $user;
        $this->attackerID = $userid;
        $this->attacker = $this->master->{$user};
        $this->attackerFriends = $attackerCompanionIDs;
        $this->attacker_is_ai = $this->master->is_user_ai( $user , $userid );
        
        // Save the target in local variables
        $this->targetSide  = $target;
        $this->targetID = $targetid;
        $this->target = $this->master->{$target};
        $this->targetFriends = $targetcompanionids;
        
        // Check if the two sides are the same
        $this->isTheSameSide = ( $user == $target ) ? true : false;
        $this->isTheSameUser = ( $userid == $targetid ) ? true : false;
        
        // Save the jutsu data in local variables
        $this->jutsu_data = $jutsudata;
        $this->jid = $this->jutsu_data['id'];
        
        // Array for storing names of items being used by the jutsu
        $this->itemNames = array();
        
        // Set a local version of the battle
        $this->battleDATA = $this->master->battle;

        //	Commence jutsu checks and execution
        if ( $this->attacker_is_ai == 1 ) {
            $this->jutsu_data['level'] = $this->attacker['data'][ $this->attackerID ]['level'];
            $this->execute_jutsu();
        } else {
            
            // Fix overcap
            if ($this->jutsu_data['level'] > 200) {
                $this->jutsu_data['level'] = 200;
            }
            if ($this->jutsu_data['level'] > $this->jutsu_data['max_level']) {
                $this->jutsu_data['level'] = $this->jutsu_data['max_level'];
            }

            //	Jutsu execution by player, check if jutsu exists and retrieve level.
            if ($this->execute_jutsu()) {
                $this->increase_exp();
            }
        }
    }

    /*  Returns data to the master class     
     *  If target side and attacker side are different, return both. Otherwise only return attacker
     *  (which will also be the one updated.
     */
    public function return_data() {
        if( $this->isTheSameSide ){
            $this->master->set_battledata($this->attackerSide, $this->attacker);
        }
        else{
            $this->master->set_battledata($this->targetSide, $this->target);
            $this->master->set_battledata($this->attackerSide, $this->attacker);
        }
    }

    /*      Increment jutsu exp OR level after use      */
    private function increase_exp() {

        // Only if we're under the cap
        if ($this->jutsu_data['level'] < 200 && $this->jutsu_data['level'] < $this->jutsu_data['max_level']) {
            
            // Else. +1 is to avoid division by 1.
            if ($this->jutsu_data['level'] < 1) {
                $this->jutsu_data['level'] = 1;
            }
            
            // Set the experience increase based on battle type
            if ($this->battleDATA[0]['battle_type'] == "combat" || $this->battleDATA[0]['battle_type'] == "exiting_combat") {
                if( isset($this->attacker['data'][ $this->attackerID ]['location'] ) &&
                    stristr($this->attacker['data'][ $this->attackerID ]['location'], "village"))
                {
                    $exp_increase = 2000 / ( 1 * $this->jutsu_data['level']);
                    
                    // See if there's a global event increasing jutsu experience gain
                    if( $event = functions::getGlobalEvent("DoubleJutsuExperience") ){
                        if( isset( $event['data']) && is_numeric( $event['data']) ){
                            $exp_increase *= round($event['data'] / 100,2);
                        }
                    }
                }
                else{
                    $exp_increase = 1000 / ( 1 * $this->jutsu_data['level']);
                }
            }
            else{
                $exp_increase = 500 / ( 1 * $this->jutsu_data['level'] );
            }
                
            // Convenience definition, the experience needed
            $expneeded = $this->jutsu_data['expPerLvl'];

            //      Increase experience or level
            if ($this->jutsu_data['exp'] + $exp_increase >= $expneeded) {
                $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["exp"] = 0;
                $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["level"] += 1;
            } else {
                $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["exp"] += $exp_increase;
                $this->jutsu_data['exp'] += $exp_increase;
            }
        }
    }

    // Modify reduction based on jutsu rank & user rank
    private function adjust_jutsu_reduction(){
        if( $this->attacker['data'][ $this->attackerID ]['rank_id'] > $this->jutsu_data['required_rank'] ){
            $diff = $this->attacker['data'][ $this->attackerID ]['rank_id'] - $this->jutsu_data['required_rank'];
            $this->reduction *= ( 1 - 0.25 * $diff );
        }
    }
    
    // Checks if the user has the items for this jutsu. 
    // Sets messages for battle description, and potentially removes item
    //private function hasItemsForJutsu(){
    //    
    //    // Loop over the two requirements
    //    $requirements = array();
    //    for( $i=1 ; $i <= 2 ; $i++ ){
    //        if( $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["itemRequirement_".$i] != null ){
    //            
    //            // Get the tags
    //            $tags = explode(":",$this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["itemRequirement_".$i]);
    //            
    //            // Different actions for the two tags
    //            switch( $tags[0] ){
    //                case "class": 
    //                    $requirements[] = array( 
    //                        "identifier" => "weapon_class", 
    //                        "data" => explode(",",$tags[1]), 
    //                        "stack" => $tags[2], 
    //                        "remove" => $tags[3] 
    //                    );
    //                break;
    //                case "item":
    //                    $requirements[] = array( 
    //                        "identifier" => "id", 
    //                        "data" => explode(",",$tags[1]), 
    //                        "stack" => $tags[2], 
    //                        "remove" => $tags[3] 
    //                    );
    //                break;
    //            }
    //        }
    //    }
    //    
    //    // Check that the user has all the requirements
    //    $removeArray = array();
    //    if( !empty($requirements) ){
    //        foreach( $requirements as $requirement ){
    //
    //            // Default is not to pass
    //            $pass = false;
    //            
    //            // Go through user items
    //            if( isset($this->attacker['items'][ $this->attackerID ]) ){
    //                foreach( $this->attacker['items'][ $this->attackerID ] as $itemID => $itemData ){
    //
    //                    // Check identifier
    //                    $itemWeaponTypes = !empty($itemData['weapon_classifications']) ? explode( ",", $itemData['weapon_classifications'] ) : array();
    //                    $intersect = array_intersect($requirement['data'], $itemWeaponTypes);
    //                    
    //                    // Check Identifier
    //                    if( 
    //                        ( // In the case of ID
    //                          $requirement['identifier'] == "id" && 
    //                          in_array($itemData['id'], $requirement['data']) 
    //                        ) ||
    //                        (
    //                          $requirement['identifier'] == "weapon_class" && 
    //                          !empty( $intersect )
    //                        )
    //                    ){
    //                        // Check stack & durability
    //                        if( 
    //                            ($itemData['stack'] >= $requirement['stack'] &&
    //                             $itemData['durabilityPoints'] > 0) || 
    //                            in_array($this->battleDATA[0]['battle_type'], array("mission","crime","arena","mirror_battle","torn_battle","event","rand","quest") ) 
    //                        ){
    //                            
    //                            // If justu has an element, check that it's the same as the item
    //                            if( 
    //                               strtolower($this->jutsu_data['element']) == "none" ||
    //                               strtolower($this->jutsu_data['element']) == "" ||
    //                               strtolower($itemData['element']) == "none" ||
    //                               strtolower($itemData['element']) == "" ||
    //                               strtolower($this->jutsu_data['element']) == strtolower($itemData['element'])
    //                            ){
    //                                if( $requirement['remove'] == 1 && $itemData['type'] !== "item"){
    //                                    $removeArray[] = $itemID;
    //                                }
    //                                elseif( $itemData['type'] == "item"){
    //                                    $this->attacker['items'][ $this->attackerID ][ $itemID ]['uses'] += 1;
    //                                }
    //                                else{
    //
    //                                    // Get damage
    //                                    switch( $itemData['required_rank'] ){
    //                                        case 1: $durDamage = 1; break;
    //                                        case 2: $durDamage = 1; break;
    //                                        case 3: $durDamage = random_int(1,2); break;
    //                                        case 4: $durDamage = random_int(1,3); break;
    //                                        case 5: $durDamage = random_int(1,4); break;
    //                                        default: $durDamage = 1; break;
    //                                    }
    //
    //                                    // Decrease the durability
    //                                    $this->attacker['items'][ $this->attackerID ][ $itemID ]['durabilityDamage'] += $durDamage;
    //                                    $this->attacker['items'][ $this->attackerID ][ $itemID ]['durabilityPoints'] -= $durDamage;
    //                                }
    //
    //                                $pass = true;
    //                                $this->itemNames[] = $itemData['name'];
    //
    //                                break;
    //                            }
    //                        }
    //                    }
    //                }
    //            }
    //
    //            // If didn't pass, return false
    //            if( $pass == false ){
    //                return false;
    //            }
    //        }
    //    }
    //    
    //    // Destroy item
    //    if( !empty($removeArray) ){
    //        foreach( $removeArray as $itemID ){
    //            $durDamage = $this->attacker['items'][ $this->attackerID ][ $itemID ]['durabilityPoints'];
    //            $this->attacker['items'][ $this->attackerID ][ $itemID ]['durabilityDamage'] += $durDamage;
    //            $this->attacker['items'][ $this->attackerID ][ $itemID ]['durabilityPoints'] = 0;
    //        }
    //    }
    //    
    //    // If not returned false by this point, return true, everything passed
    //    return true;
    //}
    
    // The function to execute the jutsu functions
    //private function execute_jutsu() {
    //    
    //    //  Set Jutsu element
    //    if ( $this->jutsu_data['element'] == 'Random') {
    //        $this->jutsu_data['element'] = $this->random_element();
    //    }
    //    
    //    // As default allow the use of specialization
    //    $this->canUseSpecial = 1;
    //    $this->potentiallyUsesItems = 1;
    //    
    //    // Set type
    //    if ($this->jutsu_data['attack_type'] == 'taijutsu') {
    //        $ttype = 'T';
    //        $this->att_type = 'tai';
    //    } elseif ($this->jutsu_data['attack_type'] == 'ninjutsu') {
    //        $ttype = 'N';
    //        $this->att_type = 'nin';
    //    } elseif ($this->jutsu_data['attack_type'] == 'genjutsu') {
    //        $ttype = 'G';
    //        $this->att_type = 'gen';
    //    } elseif ($this->jutsu_data['attack_type'] == 'weapon') {
    //        $ttype = 'W';
    //        $this->att_type = 'weap';
    //    } elseif ($this->jutsu_data['attack_type'] == 'highest') {
    //        
    //        // "highest" means that the jutsu will use the thing the user is specialized in
            // If ai, then it will just use the "highest" stats.
    //        if( !$this->is_user_ai("attacker", $this->attackerID) ){
    //        
    //            // Figure out the user specialization
    //            $specialData = $this->get_user_specialization("attacker", $this->attackerID);
    //            if( $specialData !== false ){
    //                
    //                // Set variables used below to that of specialization
    //                list( $ttype , $this->att_type ) = array_values($specialData);
    //                if( $ttype !== "W" ){
    //                    $this->potentiallyUsesItems = 0;
    //                }
    //            }
    //            else{
    //                // No specialization, make useless
    //                $this->canUseSpecial = 0;
    //            }
    //        }
    //        else{
    //             // For AI, pick the highest one
                 // First Taijutsu
    //             $highest = $this->attacker['data'][ $this->attackerID ]['tai_off'];
    //             $ttype = 'T';
    //             $this->att_type = 'tai';
    //             // Check Nin
    //             if ($highest < $this->attacker['data'][ $this->attackerID ]['nin_off']) {
    //                $highest = $this->attacker['data'][ $this->attackerID ]['nin_off'];
    //                $ttype = 'N';
    //                $this->att_type = 'nin';
    //             }
    //             // Check Gen
    //             if ($highest < $this->attacker['data'][ $this->attackerID ]['gen_off']) {
    //                $highest = $this->attacker['data'][ $this->attackerID ]['gen_off'];
    //                $ttype = 'G';
    //                $this->att_type = 'gen';
    //             }
    //             // Check Weap
    //             if ($highest < $this->attacker['data'][ $this->attackerID ]['weap_off']) {
    //                $highest = $this->attacker['data'][ $this->attackerID ]['weap_off'];
    //                $ttype = 'W';
    //                $this->att_type = 'weap';
    //             }   
    //        }
    //    }
    //    
    //    
    //    // Check if user has the specialization used (if needed and not true, this is false)
    //    if( $this->canUseSpecial == 1 ){
    //        
    //        // Set the attack type
    //        $this->attacker['actionInfo'][ $this->attackerID ]['type'] = $ttype;
    //        
    //        //	Set chakra and stamina costs
    //        if ($this->jutsu_data['cost_type'] == 'stat') {
    //            $cha_cost = $this->jutsu_data['cha_cost'];
    //            $sta_cost = $this->jutsu_data['sta_cost'];
    //        } else {
    //            $cha_cost = ($this->attacker['data'][ $this->attackerID ]['max_cha'] / 100) * $this->jutsu_data['cha_cost'];
    //            $sta_cost = ($this->attacker['data'][ $this->attackerID ]['max_sta'] / 100) * $this->jutsu_data['sta_cost'];
    //        }
    //
    //
    //        /*
    //         * 	Modify Cha / sta cost using bloodline(s)
    //         * 	Go through all bloodline effects and check them
    //         */
    //        $doReduceCost = false;
    //        foreach( $this->attacker['data'][ $this->attackerID ]['bloodlineEffect'] as $effect ){
    //            if( preg_match("/(CHAU|CHAD|STAU|STAD)/", $effect[0]) ){
    //                $doReduceCost = true;
    //                break;
    //            }
    //        }
    //
    //        if ( $doReduceCost == true ) {
    //
    //            // Get library for bloodline effects
    //            $bloodline = new bloodline(
    //                    $this,              // Reference to the jutsu class
    //                    "attacker",         // Reference to the attacker from this class
    //                    $this->attackerID,  // Reference to the attacker ID from this class
    //                    "target",           // Reference to the target object form this class
    //                    3                   // These special effects are type 3
    //            );
    //
    //            // Loop over the user bloodline effects
    //            $i = 0;
    //            while ($i < count($this->attacker['data'][ $this->attackerID ]['bloodlineEffect'])) {
    //                $function = $this->attacker['data'][ $this->attackerID ]['bloodlineEffect'][$i][0];
    //                if ($function == 'CHAU' || $function == 'CHAD') {
    //                    if (method_exists($bloodline, $function)) {
    //                        $cha_cost = $bloodline->$function($this->attacker['data'][ $this->attackerID ]['bloodlineEffect'][$i], $cha_cost);
    //                    }
    //                } elseif ($function == 'STAU' || $function == 'STAD') {
    //                    if (method_exists($bloodline, $function)) {
    //                        $sta_cost = $bloodline->$function($this->attacker['data'][ $this->attackerID ]['bloodlineEffect'][$i], $sta_cost);
    //                    }
    //                }
    //                $i++;
    //            }
    //        }
    //        
    //        // Reduce costs based on elemental mastery if applicable
    //        $this->attacker['actionInfo'][ $this->attackerID ]['element'] = strtolower($this->jutsu_data['element']);
    //        if( $this->attacker['actionInfo'][ $this->attackerID ]['element'] !== "none" ){
    //            
    //            // Usage costs modification
    //            $perc = $this->getMasteryPercentage(
    //                    "attacker", $this->attackerID, 
    //                    $this->attacker['actionInfo'][ $this->attackerID ]['element'], 
    //                    "STACHACOST", 
    //                    $this->jutsu_data['required_rank']
    //            );
    //            if( $perc !== false && $perc > 0 ){
    //                $cha_cost -= round($cha_cost*0.01*$perc,2);
    //                $sta_cost -= round($sta_cost*0.01*$perc,2);
    //            }
    //            
    //            // Modify the max uses
    //            $perc = $this->getMasteryPercentage(
    //                    "attacker", $this->attackerID, 
    //                    $this->attacker['actionInfo'][ $this->attackerID ]['element'], 
    //                    "MaxUses", 
    //                    $this->jutsu_data['required_rank']
    //            );
    //            if( $perc !== false && $perc > 0 ){
    //                $this->jutsu_data['max_uses'] -= $perc;
    //            }
    //        }
    //
    //        // Increase Jutsu Uses
    //        if ( !isset($this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["uses"]) ){
    //            $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["uses"] = 0;
    //        }
    //        
    //        // Check chakra / stamina
    //        if (
    //            $this->attacker['data'][ $this->attackerID ]['cur_cha'] >= $cha_cost && 
    //            $this->attacker['data'][ $this->attackerID ]['cur_sta'] >= $sta_cost
    //        ) {
    //            
    //            // Count jutsu uses:
    //            if ( 
    //                $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["uses"] < $this->jutsu_data['max_uses'] ||
    //                $this->is_user_ai("attacker", $this->attackerID) 
    //            ) {
    //                
    //                // Remember that this was the last jutsu this user used
    //                $this->attacker['data'][ $this->attackerID ]["lastJutsu"] = $this->jid;
    //                
    //                // Implement the jutsu cooldown effect, i.e. insure that jutsus are not spammed
    //                if ( 
    //                     $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["curCooldown"] <= 0 ||
    //                     $this->is_user_ai("attacker", $this->attackerID) 
    //                ) {
    //                    
    //                    // Check if it's a clan jutsu. If it is, and user isn't in clan with the jutsu, then no-gp
    //                    if( $this->jutsu_data['jutsu_type'] == "clan" && $this->attacker_is_ai !== 1 ){
    //                        if( !isset($this->attacker['data'][ $this->attackerID ]['clanJutsu']) || 
    //                            empty($this->attacker['data'][ $this->attackerID ]['clanJutsu']) ||
    //                            !is_numeric($this->attacker['data'][ $this->attackerID ]['clanJutsu'] ) ||
    //                            $this->attacker['data'][ $this->attackerID ]['clanJutsu'] !== $this->jutsu_data['id'] ) 
    //                        {
    //                            $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tried to execute a clan jutsu, but is no longer member of the clan.');
    //                            return false;
    //                        }
    //                    }
    //                
    //                    // Increase uses
    //                    $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["uses"] += 1;
    //                    
    //                    // Set the cooldown for this jutsu
    //                    $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["curCooldown"] = $this->attacker['jutsus'][ $this->attackerID ][ $this->jid ]["iniCooldown"];
    //                    
    //                    // Check if the user has the items required for using this jutsu
    //                    if( $this->potentiallyUsesItems == 0 || $this->hasItemsForJutsu() ){
    //
    //                        //	Parse message
    //                        $message = $this->jutsu_data['battle_description'];
    //                        $message = $this->master->genderizeMessage( $message , $this->attacker['data'][ $this->attackerID ]['gender'], $this->attacker['data'][ $this->attackerID ]['username'] , "user" );
    //                        $message = $this->master->genderizeMessage( $message , $this->target['data'][ $this->targetID ]['gender'], $this->target['data'][ $this->targetID ]['username'] , "opponent" );
    //
    //                        // Put in item description
    //                        if( !empty( $this->itemNames ) ){
    //                            for( $i=0 ; $i<count($this->itemNames) ; $i++){
    //                                $message = str_replace('%jutsuItem_'.($i+1), $this->itemNames[$i], $message);
    //                            }
    //                        }
    //                        
    //                        //	Set action data:
    //                        $this->attacker['actionInfo'][ $this->attackerID ]['message'] = $this->attacker['data'][ $this->attackerID ]['username']." uses the jutsu ".$this->jutsu_data['name'];
    //                        $this->attacker['actionInfo'][ $this->attackerID ]['description'] = $message;
    //                        $this->attacker['actionInfo'][ $this->attackerID ]['sta_cost'] = $sta_cost;
    //                        $this->attacker['actionInfo'][ $this->attackerID ]['cha_cost'] = $cha_cost;
    //
    //                        //	Execute jutsu effects
    //                        $this->n = 1;
    //                        while ( 
    //                            isset($this->jutsu_data['effect_' . $this->n]) && 
    //                            $this->jutsu_data['effect_' . $this->n] != null
    //                        ) {
    //                            unset($temp);
    //                            $temp = explode(':', $this->jutsu_data['effect_' . $this->n]);
    //                            $function = $temp[0];
    //                            if (method_exists($this, $function)) {
    //                                $jutsucount = count($temp);
    //                                if ( $jutsucount >= 4 && $temp['' . ($jutsucount - 4) . ''] == "AOE") {
    //
    //                                    ## Test if we're targeting someone, or if it's a "user" effect
    //                                    if ($temp[1] == 'user' || $temp[0] == 'REC') {
    //                                        $aoeCompanions = $this->attackerFriends;
    //                                        $dataIdentifier = $this->attackerSide;
    //                                        $targetIDagain = $this->attackerID;
    //                                    } else {
    //                                        $aoeCompanions = $this->targetFriends;
    //                                        $dataIdentifier = $this->targetSide;
    //                                        $targetIDagain = $this->targetID;
    //                                    }
    //
    //                                    ############# AOE Jutsus
    //                                    $targetlist = array();
    //                                    foreach ($aoeCompanions as $id) {
    //                                        if ( $this->master->is_user_active( $dataIdentifier , $id ) ) {
    //                                            $targetlist[] = $id;
    //                                        }
    //                                    }
    //
    //                                    // Identify array-index of the center target
    //                                    $i = 0;
    //                                    foreach ($targetlist as $id) {
    //                                        if ($id == $targetIDagain) {
    //                                            $centertarget = $i;
    //                                        }
    //                                        $i++;
    //                                    }
    //
    //                                    // Define the minimum array index in target list to target
    //                                    if ($centertarget - $temp['' . ($jutsucount - 3) . ''] < 0) {
    //                                        $min = 0;
    //                                    } else {
    //                                        $min = $centertarget - $temp['' . ($jutsucount - 3) . ''];
    //                                    }
    //
    //                                    // Define the maximum array index in target list to target
    //                                    if ($centertarget + $temp['' . ($jutsucount - 3) . ''] > ($i - 1)) {
    //                                        $max = ($i - 1);
    //                                    } else {
    //                                        $max = $centertarget + $temp['' . ($jutsucount - 3) . ''];
    //                                    }
    //
    //                                    // Attack all the targets
    //                                    $i = $min;
    //                                    while ($i <= $max) {
    //
    //                                        // Set target ID
    //                                        $this->attack_id = $targetlist['' . $i . ''];
    //
    //                                        // Calculate the reduction (further away => less effective)
    //                                        $this->reduction = pow(
    //                                                ($temp['' . ($jutsucount - 2) . ''] / 100) +
    //                                                ($this->jutsu_data['level'] * $temp['' . ($jutsucount - 1) . '']) / 100, abs($i - $centertarget)
    //                                        );
    //
    //                                        // Reduce effect based on user rank
    //                                        $this->adjust_jutsu_reduction();
    //
    //                                        // Perform the function
    //                                        $this->$function($temp);
    //                                        $i++;
    //                                    }
    //                                } else {
    //
    //                                    // Standard Jutsu
    //                                    $this->attack_id = $this->targetID;
    //                                    $this->reduction = 1;
    //
    //                                    // Reduce effect based on user rank
    //                                    $this->adjust_jutsu_reduction();
    //
    //                                    // Execute the effect
    //                                    $this->$function($temp);
    //
    //                                }
    //                            } else {
    //                                $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ': Jutsu attack data glitched, function: '.$function.'.');
    //                            }
    //                            $this->n++;
    //                        }
    //                        return true;
    //                    } else {
    //                        // Did not have the required items
    //                        $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tried to execute a jutsu, but did not have the required instruments.');
    //                        return false;
    //                    }
    //                } else {
    //                    // The cooldown for this jutsu is not yet over
    //                    $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tried to execute a jutsu too soon after having used it before.');
    //                    return false;
    //                }
    //            } else {
    //                //    User used this jutsu too many times
    //                $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tried to execute a jutsu, but was too exhausted from already having used the same jutsu in this battle.');
    //                return false;
    //            }
    //        } else {
    //            //	User lacks chakra / stamina
    //            $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tried to execute a jutsu, but lacked the chakra / stamina.');
    //            return false;
    //        }
    //    } else {
    //        //	Cannot use specialization jutsu
            //    User used this item too many times
    //        $this->setUserActionInfo( "attacker", $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tried to execute a jutsu, but could not due to lack of specialization.');
    //        return false;
    //    }
    //}
    
    /*
     * 	In case of a effect directed at "user". If it's an AOE effect,
     *  then the attack_id is already set in $this->attack_id. Otherwise,
     *  target the user him/her-self.
     */
    private function userAOEid( $tagArray ) {
        if ( 
            count($tagArray) >= 4 && 
            $tagArray[ (count($tagArray) - 4)  ] == "AOE"
        ) {
            return $this->attack_id;
        } else {
            return $this->attackerID;
        }
    }
    
    /* 
     *  A lot of tags have a "user" effect, which means it should target the user
     *  him/her-self rather than the one targeted originally.
     */
    private function getTargetAndID(  $tagSide, $modifiers ){
        
        // Define effectSide & effectID
        $effectSide = $effectID = "";
        
        // Find the effectSide & effectID
        if ( $tagSide == 'opponent' ) {
            if ( $this->isTheSameSide ) {
                $effectSide = "attacker";
                $effectID = $this->attack_id;
            } else {
                $effectSide = "target";
                $effectID = $this->attack_id;
            }
        } else {
            $effectSide = "attacker";
            $effectID = $this->userAOEid($modifiers);
        }
        
        // Return stuff
        return array( $effectSide , $effectID );
    }

    /* Insert a new status effect into the user or opp data. */
    public function insert_status($side, $targetid, $tag, $postpone = false) {
        
        // Insert the tag
        $nextindex = count($this->{$side}['status'][ $targetid ]);
        $this->{$side}['status'][ $targetid ][$nextindex] = explode(':', $tag);
        
        // If this effect shouldn't be effective yet, add the POSTPONE effect to the status effect
        if ($postpone == true) {
            $nextTagIndex = count($this->{$side}['status'][ $targetid ][$nextindex]);
            $this->{$side}['status'][ $targetid ][$nextindex][$nextTagIndex] = "POSTPONE";
        }
    }

    // Ensure that a value is above 0
    private function overZero($value){
        if( $value < 0 ){
            $value = 0;
        }
        return $value;
    }
    
    /*
     * 		Jutsu effect functions
     * 		Following below are the functions for every jutsu effect.
     * 		Function names are identical to the TAG identifiers used in the database
     * 		for dynamic call use.
     */

    // Damage the opponent  
    // DAM:(CALC|STAT|PERC):(power-int):(levelInc-int):general1:general2
    private function DAM($modifiers) {
        
        // Calculate the power
        $power = $modifiers[2] + ($this->jutsu_data['level'] * $modifiers[3]);        
        
        // Check type
        if ($modifiers[1] == 'STAT') {
            $this->attacker['actionInfo'][ $this->attackerID ]['damage'][ $this->attack_id ] = $power;
        } elseif ($modifiers[1] == 'PERC') {
            $perc = $this->target['data'][ $this->targetID ]['max_health'] / 100;
            $this->attacker['actionInfo'][ $this->attackerID ]['damage'][ $this->attack_id ] = $perc * $power;
        } elseif ($modifiers[1] == 'CALC') {
            
            // Decrease armor durability of target
            switch( $this->att_type ){
                case "nin": $this->damageArmorDurability("target", $this->attack_id, 1, 5); break;
                case "weap": $this->damageArmorDurability("target", $this->attack_id, 3, 6); break;
                case "tai": $this->damageArmorDurability("target", $this->attack_id, 2, 5); break;
            }
            
            // Calculate damage
            $damage = calc::calc_damage( 
                array(
                    "user_data" => $this->attacker['data'][ $this->attackerID ], 
                    "target_data" => $this->target['data'][ $this->attack_id ], 
                    "type" => $this->att_type, 
                    "stat1" => $modifiers[4], 
                    "stat2" => $modifiers[5], 
                    "power" => $power,
                    "jutsu" => $this->jutsu_data
                )
            );
            
            // Adjust based on elemental mastery
            $damage = $this->adjustElementalDamage("attacker", $this->attackerID, $damage, $this->attacker['actionInfo'][ $this->attackerID ]['element'] );
            
            // Damage reduction
            $damage = $this->reduction * $damage;
            
            // Set to action info
            $this->attacker['actionInfo'][ $this->attackerID ]['damage'][ $this->attack_id ] = $damage;
        }
        
        $this->attacker['actionInfo'][ $this->attackerID ]['targetType'] = $this->targetSide;
        $this->attacker['actionInfo'][ $this->attackerID ]['targetIDs'][] = $this->attack_id;
    }

    // Recover life    
    // HEA:(user|opponent):(DAM|STAT|CALC|PERC):(power-int):(levelInc-int)
    private function HEA($modifiers) {
        
        // Get target & id for heal
        list($healtarget, $healID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Calculate the power
        $power = $this->overZero($modifiers[3] + ($this->jutsu_data['level'] * $modifiers[4]));

        // If no heal has been set yet
        if( !isset($this->{$healtarget}['actionInfo'][ $healID ]['healed']) ){
            $this->{$healtarget}['actionInfo'][ $healID ]['healed'] = 0;
        }
        
        // Determine heal
        if ($modifiers[2] == 'STAT') {
            $this->{$healtarget}['actionInfo'][ $healID ]['healed'] += $power;
        } elseif ($modifiers[2] == 'CALC') {
            $this->{$healtarget}['actionInfo'][ $healID ]['healed'] += calc::calc_value($this->attacker['data'][ $this->attackerID ], $this->att_type, $modifiers[5], $power);
        } elseif ($modifiers[2] == 'PERC') {
            $perc = $this->attacker['data'][ $this->attackerID ]['max_health'] / 100;
            $this->{$healtarget}['actionInfo'][ $healID ]['healed'] += $perc * $power;
        } elseif ($modifiers[2] == 'DAM') {
            if (isset($this->attacker['actionInfo'][ $this->attackerID ]['damage'])) {
                $totaldamagedeal = 0;
                foreach ($this->attacker['actionInfo'][ $this->attackerID ]['damage'] as $targerid => $targetamount) {
                    $totaldamagedeal += $targetamount;
                }
                $perc = $totaldamagedeal / 100;
                $this->{$healtarget}['actionInfo'][ $healID ]['healed'] += round($perc * $power, 2);
            }
        }
        $this->{$healtarget}['actionInfo'][ $healID ]['healed'] *= $this->reduction;
        
        // Log healing under the attacker rather than under the attacked
        $this->{$healtarget}['actionInfo'][ $healID ]['healLog'] = $this->attacker['data'][ $this->attackerID ]['username'];   
    }

    // Copy the last jutsu used by the opponent
    // Will not add jutsu to user and will only be executeable once
    // CJUTSU:opponent
    //private function CJUTSU($modifiers){
    //    
    //    // Figure Out who to steal from
    //    list($jtarget, $jtargetID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
    //    
    //    // Check if/what jutsu this target last used
    //    if( isset($this->{$jtarget}['data'][ $jtargetID ]["lastJutsu"]) ){
    //        
    //        // Get data for this jutsu from the opponent array (yay, we don't have to get it from the DB again)
    //        $prevJid = $this->{$jtarget}['data'][ $jtargetID ]["lastJutsu"];
    //        if( isset($this->{$jtarget}['jutsus'][ $jtargetID ][ $prevJid ]) ){
    //            
    //            // Local storage of the jutsu for readability
    //            $prevJutsu = $this->{$jtarget}['jutsus'][ $jtargetID ][ $prevJid ];
    //            
    //            // Count current jutsu effects
    //            $nextID = 1;
    //            while ( 
    //                isset($this->jutsu_data['effect_' . $nextID]) && 
    //                $this->jutsu_data['effect_' . $nextID] != null
    //            ){
    //                $nextID++;
    //            }
    //            
    //            // Insert new jutsu effects
    //            $loopID = 1;
    //            while ( 
    //                isset($prevJutsu['effect_' . $loopID]) && 
    //                $prevJutsu['effect_' . $loopID] != null
    //            ){
    //                $this->jutsu_data['effect_' . $nextID] = $prevJutsu['effect_' . $loopID];
    //                $loopID++;
    //                $nextID++;
    //            }
    //        }
    //    }       
    //}
    
    /*
     *  Seal opponents bloodline effects for the duration of the battle
     *  Will not seal opponent bloodline jutsu, use status effect for that.
     * 
     *  SEAL:(opponent|user):chance(1-100):increasePerLevel:minRounds:maxRounds
     */
    private function SEAL($modifiers) {
        
        // Figure Out who to seal
        list($sealtarget, $sealID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Check for bloodline effects
        if (
            isset($this->{$sealtarget}['data'][$sealID]['bloodlineEffect']) &&
            !empty($this->{$sealtarget}['data'][$sealID]['bloodlineEffect'])
        ) {
            
            // Set data for the seal
            $rand = random_int(1, 100);
            $rounds = random_int($modifiers[4], $modifiers[5]);
            $chance = $modifiers[2] + $this->jutsu_data['level'] * $modifiers[3];
            
            // Check if successful
            if ($rand <= $chance) {
                
                // Check if seal prevention is "on" for this user
                if ( !$this->is_user_preventSeal($sealtarget, $sealID) ) {
                    
                    // Seal is successfull
                    $this->{$sealtarget}['actionInfo'][ $sealID ]['seal'] = 'success';
                    $this->{$sealtarget}['actionInfo'][ $sealID ]['sealrounds'] = $rounds;
                    
                    
                    // Log seal under the attacker rather than under the attacked
                    $this->{$sealtarget}['actionInfo'][ $sealID ]['sealLog'] = $this->attacker['data'][ $this->attackerID ]['username']; 
                    
                    // Attach seal to all bloodline effects
                    $i = 0;
                    while ($i < count($this->{$sealtarget}['data'][$sealID]['bloodlineEffect'])) {
                        $this->{$sealtarget}['data'][$sealID]['bloodlineEffect'][$i][count($this->{$sealtarget}['data'][$sealID]['bloodlineEffect'][$i])] = "SEAL";
                        $this->{$sealtarget}['data'][$sealID]['bloodlineEffect'][$i][count($this->{$sealtarget}['data'][$sealID]['bloodlineEffect'][$i])] = ($rounds + 1); // +2 makes sure it's the right amount of rounds. Empirically determined
                        $i++;
                    }
                     
                } else {
                    $this->{$sealtarget}['actionInfo'][ $sealID ]['seal'] = 'failed';
                }
            } else {
                $this->{$sealtarget}['actionInfo'][ $sealID ]['seal'] = 'failed';
            }
        }
    }

    // Insert bloodline tag on given user
    // ADDE:(opponent|user):(bloodline tag with | instead of :)
    private function ADDE($modifiers) {
        
        // Figure Out who to seal
        list($bloodtarget, $bloodID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set array if not already set
        if( !isset($this->{$bloodtarget}['data'][$bloodID]['bloodlinesAdded']) ){
            $this->{$bloodtarget}['data'][$bloodID]['bloodlinesAdded'] = array();
        }
        
        // Add the bloodline, but only once
        if ( !isset($this->{$bloodtarget}['data'][$bloodID]['bloodlinesAdded'][ $modifiers[2] ]) ) 
        {
            $this->{$bloodtarget}['data'][$bloodID]['bloodlineEffect'][ count($this->{$bloodtarget}['data'][$bloodID]['bloodlineEffect']) ] = explode('|', $modifiers[2]);
            $this->{$bloodtarget}['data'][$bloodID]['bloodlinesAdded'][ $modifiers[2] ] = 1;
        }
    }

    // Recoil damage to user
    // REC:(DAM|STAT|PERC):(power-int):(levelInc-int)
    private function REC($modifiers) {

        // Get the user ID
        $userIDtemp = $this->userAOEid($modifiers);
        
        // Only if not preventing
        if ( !$this->is_user_preventRecoil("attacker", $userIDtemp) ) {
        
            // Get the jutsu power
            $power = $this->overZero($modifiers[2] + ($this->jutsu_data['level'] * $modifiers[3]));

            // Handle different state
            if ($modifiers[1] == 'STAT') 
            {
                $this->attacker['actionInfo'][ $userIDtemp ]['recoil'] = round($power, 1);
            } 
            elseif ($modifiers[1] == 'DAM') 
            {
                if (isset($this->attacker['actionInfo'][ $this->attackerID ]['damage'])) {
                    $totaldamagedeal = 0;
                    foreach ($this->attacker['actionInfo'][ $this->attackerID ]['damage'] as $targerid => $targetamount) {
                        $totaldamagedeal += $targetamount;
                    }
                    $perc = $totaldamagedeal / 100;
                    $this->attacker['actionInfo'][ $userIDtemp ]['recoil'] = round($perc * $power, 1);
                }
            } 
            elseif ($modifiers[1] == 'PERC') 
            {
                $perc = $this->attacker['data'][ $userIDtemp ]['max_health'] / 100;
                $this->attacker['actionInfo'][ $userIDtemp ]['recoil'] = round($perc * $power, 1);
            }
        }
    }

    //  Set "STUN" status effect: e.g.  	
    //  STUN:opponent|user:minRounds:maxRounds:chance:chancePerLevel
    private function STUN($modifiers) {

        // Figure Out who to stun
        list($stuntarget, $stunID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Check if he has stun resist
        if ( !$this->is_user_stunResist($stuntarget, $stunID) ) {
        
            // Random number check
            $chance = $modifiers[4] + $this->jutsu_data['level'] * $modifiers[5];
            if (random_int(1, 100) <= $chance) {

                // Random number of turns
                $turns = random_int($modifiers[2], $modifiers[3]);
                if ($turns > 0) {
                    
                    // Set stun tag & stun resist tag
                    $this->insert_status($stuntarget, $stunID, 'STUN:' . ($turns+1), true);
                    $this->insert_status($stuntarget, $stunID, 'STUNR:' . (3 + ($turns * 2)), true);
                    
                    // Mark the user as stunned instantly. Comment to have effect postponed to next round.
                    // $this->set_user_stunned( $stuntarget, $stunID, $turns, true );
                    
                    // Pass info to show
                    $this->{$stuntarget}['actionInfo'][ $stunID ]['stunInfo'] = "success";
                    $this->{$stuntarget}['actionInfo'][ $stunID ]['stunrounds'] = $turns;
                } else {
                    $this->{$stuntarget}['actionInfo'][ $stunID ]['stunInfo'] = "failed";
                }
            } else {
                $this->{$stuntarget}['actionInfo'][ $stunID ]['stunInfo'] = "failed";
            }
        } else {
            $this->{$stuntarget}['actionInfo'][ $stunID ]['stunInfo'] = "failed";
        }
        
        // Log stun under the attacker rather than under the attacked
        $this->{$stuntarget}['actionInfo'][ $stunID ]['stunLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }

    // Set the "STUNR" status effect
    // STUNR:opponent|user:minRounds:maxRounds:chance:chancePerLevel
    private function STUNR($modifiers) {
        
        // Figure Out who to stun
        list($stunRtarget, $stunID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Check if he has stun resist
        if ( !$this->is_user_stunResist($stunRtarget, $stunID) ) {
            
            // Test Chance
            $chance = $modifiers[4] + $this->jutsu_data['level'] * $modifiers[5];
            if (random_int(1, 100) <= $chance) {
                
                // Set turns
                $turns = random_int($modifiers[2], $modifiers[3]);
                if ($turns > 0) {
                    
                    // Insert status
                    $this->insert_status($stunRtarget, $stunID, 'STUNR:' . ($turns+1), true);
                    
                    // Update the user array
                    $this->set_user_stunResist( $stunRtarget, $stunID, $turns, true );
                    
                    // Set actionInfo status
                    $this->{$stunRtarget}['actionInfo'][ $stunID ]['stunRinfo'] = 'success';
                    
                } else {
                    $this->{$stunRtarget}['actionInfo'][ $stunID ]['stunRinfo'] = 'failed';
                }
            } else {
                $this->{$stunRtarget}['actionInfo'][ $stunID ]['stunRinfo'] = 'failed';
            }
        } else {
            $this->{$stunRtarget}['actionInfo'][ $stunID ]['stunRinfo'] = 'failed';
        }
        
        // Log stun resist under the attacker rather than under the attacked
        $this->{$stunRtarget}['actionInfo'][ $stunID ]['stunRLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }

    // Use 1HKO move
    // KO:chance(1-1000):increasePerLevel:(strength|speed|willpower|intelligence);
    private function KO($modifiers) {
        
        // Create & test success limit
        $min = $modifiers[1] + ($modifiers[2] * $this->jutsu_data['level']) + ($this->attacker['data'][ $this->attackerID ][$modifiers[3]] / 100);
        if (random_int(1, 100) < $min &&
            !$this->is_user_koResist("target", $this->targetID) 
        ) {
            $this->attacker['actionInfo'][ $this->attackerID ]['KO'] = 'hit';
        } else {
            $this->attacker['actionInfo'][ $this->attackerID ]['KO'] = 'miss';
        }
        $this->attacker['actionInfo'][ $this->attackerID ]['KOtargetType'] = $this->targetSide;
        $this->attacker['actionInfo'][ $this->attackerID ]['targetIDs'][] = $this->attack_id;
    }

    //  Flee from battle   
    // FLEE:chance(1-100):increasePerLevel
    private function FLEE($modifiers) {
        
        // Calculate chance
        $chance = $modifiers[1] + ($modifiers[2] * $this->jutsu_data['level']);
        
        // Attempt flee
        $this->try_fleeing("attacker", $this->attackerID, $chance );
        
    }

    //  Prevent the opponent from fleeing from battle
    // NFLE:(opponent|user):chance(1-100):increasePerLevel:minRounds:maxRounds
    private function NFLE($modifiers) {
        
        // Figure Out who to prevent from fleeing
        list($fleeRtarget, $fleeID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Check if success
        $chance = $modifiers[2] + ($modifiers[3] * $this->jutsu_data['level']);
        if (random_int(0, 100) < $chance ) {
            
            // Random amount of turns
            $turns = random_int($modifiers[4], $modifiers[5]);
            if ($turns > 0) {
                
                // Insert status
                $this->insert_status($fleeRtarget, $fleeID, 'FLEE:' . ($turns+1), true);

                // Update the user array
                $this->set_user_fleeLock( $fleeRtarget, $fleeID, $turns, true );

                // Set actionInfo status
                $this->{$fleeRtarget}['actionInfo'][ $fleeID ]['fleeRinfo'] = 'success';
                
                // If user is fleeing, stop them
                if( $this->is_user_fleeing($fleeRtarget, $fleeID) ){
                    $this->set_user_fleeing($fleeRtarget, $fleeID, false);
                }
                
            } else {
                $this->{$fleeRtarget}['actionInfo'][ $fleeID ]['fleeRinfo'] = 'failed';
            }
        } else {
            $this->{$fleeRtarget}['actionInfo'][ $fleeID ]['fleeRinfo'] = 'failed';
        }
        
        // Log flee resist under the attacker rather than under the attacked
        $this->{$fleeRtarget}['actionInfo'][ $fleeID ]['fleeRLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }

    //  Set "residual damage (DAM)" Status effect.
    //  RDAM:user|opponent:PERC|STAT|TSTA|TINC:minRounds:maxRounds:STAT|DAM:basePowerInt:levelPowerInt
    //  RDAM:user|opponent:PERC|STAT|TSTA|TINC:1:3:STAT|DAM:10:10.07
    private function RDAM($modifiers) {
        
        // Figure Out who to hit
        list($residualtarget, $residualID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Random amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Get the power
            if ($modifiers[5] == 'STAT') {
                $power = $modifiers[6] + $modifiers[7] * $this->jutsu_data['level'];
            } elseif ($modifiers[5] == 'DAM') {
                $damperc = $this->attacker['actionInfo'][ $this->attackerID ]['damage']['' . $residualID . ''] / 100;
                $power = $damperc * ($modifiers[6] + ($modifiers[7] * $this->jutsu_data['level']));
            }
            
            // Ensure it's not below zero
            $power = $this->overZero($power);
            
            // Ensure that power is above 0
            if( $power > 0 ){
                
                // Insert status
                $this->insert_status($residualtarget, $residualID, 'DAM:' . $rand . ':' . $modifiers[2] . ':' . strtolower($this->jutsu_data['element']) . ':' . $power);

                // Set actionInfo variable to inform battle log etc.
                $this->{$residualtarget}['actionInfo'][ $residualID ]['rdaInfo'] = "success";
                $this->{$residualtarget}['actionInfo'][ $residualID ]['rdaRounds'] = $rand;

                // Log residual damage under the attacker rather than under the attacked
                $this->{$residualtarget}['actionInfo'][ $residualID ]['rdaLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
            }
        }
    }

    //  Set "healing over time (HOT)" Status effect.
    //  HOT:user|opponent:PERC|STAT|HEA:minRounds:maxRounds:STAT|DAM|HEA:basePowerInt:levelPowerInt
    //  HOT:opponent:(PERC|STAT|HEA):1:3:(STAT|DAM|HEA):10:10.07     */
    private function HOT($modifiers) {
        
        // Figure Out who to hit
        list($residualtarget, $residualID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Random amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Get the power
            if ($modifiers[5] == 'STAT') {
                $power = $modifiers[6] + $modifiers[7] * $this->jutsu_data['level'];
            } elseif ($modifiers[5] == 'DAM') {
                $damperc = $this->attacker['actionInfo'][ $this->attackerID ]['damage'][ $this->attack_id ] / 100;
                $power = $damperc * ($modifiers[6] + ($modifiers[7] * $this->jutsu_data['level']));
                if( $modifiers[2] == "PERC" ){
                    $modifiers[2] = "STAT"; // Percentage already calculated
                }
            }
            
            // Make sure power is not below zero
            $power = $this->overZero($power);
            
            // Insert status
            $this->insert_status($residualtarget, $residualID, 'HEA:' . ($rand+1) . ':' . $modifiers[2] . ':' . strtolower($this->jutsu_data['element']) . ':' . $power, true);
            
            // Set actionInfo variable to inform battle log etc.
            $this->{$residualtarget}['actionInfo'][ $residualID ]['hotInfo'] = "success";
            $this->{$residualtarget}['actionInfo'][ $residualID ]['hotRounds'] = $rand;
            
            // Log healing over time under the attacker rather than under the attacked
            $this->{$residualtarget}['actionInfo'][ $residualID ]['hotLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
        }
    }

    //  Set "copy bloodline" effect.
    //  BCOPY:(user|opponent):roundsMinInt:roundsMaxInt:baseChance:levelChanceIncrease
    private function BCOPY($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Can't target yourself with this one
        if ( !$this->isTheSameUser ) {
            
            // Get the rounds
            $rand = random_int($modifiers[2], $modifiers[3]);
            if ($rand > 0) {
                
                // Calculate the chance
                $power = !isset($modifiers[5]) ? 0 : $modifiers[5];
                $chance = $modifiers[4] + $this->jutsu_data['level'] * $power;
                if (random_int(0, 100) < $chance ) {
            
                    $i = 0;
                    $tempArr = array();
                    $currentCount = count( $this->{$stattarget}['data'][$statID]['bloodlineEffect'] );
                    while ($i < $currentCount) {
                        if ($this->{$stattarget}['data'][$statID]['bloodlineEffect'][$i][(count($this->{$stattarget}['data'][$statID]['bloodlineEffect'][$i]) - 2)] !== "STOP") {
                            
                            // Copy bloodline effect
                            $tempArr = $this->{$stattarget}['data'][$statID]['bloodlineEffect'][$i];
                            $tempArr[count($tempArr)] = "STOP";
                            $tempArr[count($tempArr)] = ($rand + 1); // +1 makes sure it's the right amount of rounds
                            $this->attacker['data'][ $this->attackerID ]['bloodlineEffect'][count($this->attacker['data'][ $this->attackerID ]['bloodlineEffect'])] = $tempArr;

                            unset($tempArr);
                        }
                        $i++;
                    }

                    // If something was copied, then send to battle log
                    if ($i > 0) {
                        
                        // Set actionInfo variable to inform battle log etc.
                        $this->attacker['actionInfo'][ $this->attackerID ]['bcopyInfo'] = "success";
                        $this->attacker['actionInfo'][ $this->attackerID ]['bcopyRounds'] = $rand;
                        $this->attacker['actionInfo'][ $this->attackerID ]['BCOPYtargetType'] = $this->targetSide;
                        $this->attacker['actionInfo'][ $this->attackerID ]['targetIDs'][] = $this->attack_id;
                        
                        // Log stun resist under the attacker rather than under the attacked
                        $this->{$stattarget}['actionInfo'][ $statID ]['bcopyLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
                        
                    }
                }
            }
        }
    }

    //  Set reflection status effect, which reflects damage back at the user 
    //  REFL:(user|opponent):(STAT|PERC):roundsMinInt:roundsMaxInt:baseStrength:levelGain
    private function REFL($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Reflection Status effect
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Get the power
            $power = $this->overZero( $modifiers[5] + $modifiers[6] * $this->jutsu_data['level'] );
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'REFL:' . ((int) $rand-1) . ':' . $modifiers[2] . ':' . $power);
            
            // Set actionInfo variable to inform battle log etc.
            $this->{$stattarget}['actionInfo'][ $statID ]['reflInfo'] = "success";
            $this->{$stattarget}['actionInfo'][ $statID ]['reflRounds'] = $rand;
            
             // Log reflection under the attacker rather than under the attacked
            $this->{$stattarget}['actionInfo'][ $statID ]['reflLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
            
        }
    }
    
    //  Set reflection status effect  
    //  EREFL:(user|opponent):(STAT|PERC):(elem.elem.elem):roundsMinInt:roundsMaxInt:baseStrength:levelGain
    private function EREFL($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Set an elemental text
            $text = $this->getElementalText( $modifiers[3] );
            
            // Calculate the power
            $power = $this->overZero( $modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Set the status effect
            $this->insert_status($stattarget, $statID, 'EREFL:' . ((int) $rand) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
            // Set actionInfo variable to inform battle log etc.
            $this->{$stattarget}['actionInfo'][ $statID ]['reflInfo'] = "success";
            $this->{$stattarget}['actionInfo'][ $statID ]['reflRounds'] = $rand;
            $this->{$stattarget}['actionInfo'][ $statID ]['reflElement'] = $text;
            
             // Log reflection under the attacker rather than under the attacked
            $this->{$stattarget}['actionInfo'][ $statID ]['reflLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
        }
    }
    
    //  Remove set status effects
    //  CLEAR:(user|oppoenent)
    private function CLEAR($modifiers) {
        
        // Figure Out who to hit
        list($cleartarget, $clearID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Remove all non-permanent effects
        if (!empty($this->{$cleartarget}['status'][ $clearID ])) {
            
            // Go through all the effects
            foreach ($this->{$cleartarget}['status'][ $clearID ] as $key => $val) {
                if (!in_array("PERMANENT", $val)) {
                    unset($this->{$cleartarget}['status'][ $clearID ][$key]);
                }
            }
            
            // Rearrange status effects
            $this->{$cleartarget}['status'][ $clearID ] = array_values($this->{$cleartarget}['status'][ $clearID ]);
            
            // Set actionInfo variable to inform battle log etc
            $this->{$cleartarget}['actionInfo'][ $clearID ]['clearInfo'] = "success";

            // Log stun under the attacker rather than under the attacked
            $this->{$cleartarget}['actionInfo'][ $clearID ]['clearLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
        }
    }

    //  Rob opponent ryo (outlaw jutsu FTW)
    //  ROB:(baseChance):(PERC|STAT):(baseStealPower):(lvlIncStealPower):(lvlIncChance)
    private function ROB($modifiers) {
        
        // Figure Out who to hit. Always set to opponent
        list($sealtarget, $statID) = $this->getTargetAndID( "opponent",  $modifiers );

        // Can't target yourself with this one
        if ( !$this->isTheSameUser ) {
        
            // Check for success
            $rand = random_int(1, 100);
            if ($rand <= $this->overZero($modifiers[1] + ($modifiers[5] & $this->jutsu_data['level']))) {
                
                // Not for attacking yourself or poor people
                if (
                    $this->{$sealtarget}['data'][ $statID ]['money'] > 0 &&
                    $this->attack_id != $this->attackerID
                ) {
                        
                    // Calculate stolen amount
                    if ($modifiers[2] == 'PERC') {
                        if ($modifiers[3] > 100) {
                            $modifiers[3] = 100;
                        }
                        $perc = $this->{$sealtarget}['data'][ $statID ]['money'] / 100;
                        $this->attacker['actionInfo'][ $this->attackerID ]['stolen'] = floor($perc * ($modifiers[3] + ($this->jutsu_data['level'] * $modifiers[4])));
                    } elseif ($modifiers[2] == 'STAT') {
                        $stolen = ($this->{$sealtarget}['data'][ $statID ]['money'] >= $modifiers[3]) ? $modifiers[3] + ($this->jutsu_data['level'] * $modifiers[4]) : $this->{$sealtarget}['data'][ $statID ]['money'];
                        $this->attacker['actionInfo'][ $this->attackerID ]['stolen'] = $stolen;
                    }
                    
                    // Reduce money from the target
                    $this->{$sealtarget}['data'][ $statID ]['money'] -= $this->attacker['actionInfo'][ $this->attackerID ]['stolen'];
                    
                    // Set actionINfo information for the log
                    $this->attacker['actionInfo'][ $this->attackerID ]['stolenID'] = $this->attack_id;
                    
                }
            }
        }
    }
    
    // A convenience function for summons for creating an ID which is not currently in the database
    private function createNewID(){
        $id = 1500000;
        while ( 
            in_array($id, $this->master->user['ids']) ||
            in_array($id, $this->master->opponent['ids'])
        ) {
            $id = random_int(1500000, 1600000);
        }
        return $id;
    }

    //  Summon a summon. Summon
    //  SUM:(Name|LIST):(user|opponent|LISTID):(baseSumPower):(lvlIncSumPower):(trait):(act1):(act2):(act3)
    //  trait and acts use  with ; instead of :
    private function SUM($modifiers) {
        
        // Figure Out who to "hit" with summon attack. 
        list($summonside, $summonID) = $this->getTargetAndID( $modifiers[2], $modifiers );
        
        // Figure out how much difference there is in amount of characters between the user's side
        // of the battle and the other side
        $peopleDiff = count($this->master->{$this->attackerSide}['ids']) - count($this->master->{$this->get_other_side($this->attackerSide)}['ids']);
        if ( $peopleDiff < 2) {
            
            // User can only have one summon at a time
            if ( !isset($this->attacker['data'][ $this->attackerID ]['summons']) ||
                 $this->attacker['data'][ $this->attackerID ]['summons'] < 1
            ) {
                
                // Create the summon
                if ($modifiers[1] == "LIST") {
                    
                    // Get the opponent
                    $opponent = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = " . $modifiers[2] . " LIMIT 1");
                    
                    //Make AI
                    $opponent[0] = functions::make_ai($opponent[0]);
                    
                    // Final 
                    $opponent[0]['gender'] = 'none';
                    $opponent[0]['type'] = 'summon';
                    $opponent[0]['village'] = $this->attacker['data'][ $this->attackerID ]['village'];
                    
                } else {
                    
                    // Create based on percent of summonside values
                    $perc = ($modifiers[3] + $modifiers[4] * $this->jutsu_data['level']) / 100;
                    
                    // Define opponent array
                    $opponent = array( array() );
                    
                    // Go through summonside user data, and adjust everything accordingly 
                    foreach( $this->{$summonside}['data'][ $summonID ] as $key => $value){
                        
                        // Deal with the neccesary numbers
                        if( preg_match("/(tai_off|nin_off|gen_off|weap_off|tai_def|nin_def|gen_def|weap_def|strength|speed|willpower|intelligence|intelligence|armor|max_health|cur_health|max_cha|cur_cha|max_sta|cur_sta)/", $key)) 
                        {
                            $opponent[0][ $key ] = $value * $perc;
                        }
                    }
                    
                    $opponent[0]['name'] = $modifiers[1];
                    $opponent[0]['rank'] = 'Summon';
                    $opponent[0]['exp'] = 100;
                    $opponent[0]['money'] = 0;
                    $opponent[0]['level'] = 50;
                    $opponent[0]['type'] = 'summon';
                    $opponent[0]['location'] = 'In Battle';
                    $opponent[0]['is_ai'] = 1;
                    $opponent[0]['rank_id'] = 0;
                    $opponent[0]['username'] = $modifiers[1];
                    $opponent[0]['gender'] = 'none';
                    $opponent[0]['itemList'] = '';
                    $opponent[0]['ai_actions'] = "";
                    $opponent[0]['village'] = isset($this->attacker['data'][ $this->attackerID ]['village']) ? $this->attacker['data'][ $this->attackerID ]['village'] : "None";

                    if (isset($modifiers[5]) && $modifiers[5] !== "none") {
                        $opponent[0]['trait'] = str_replace(';', ':', $modifiers[5]);
                        $opponent[0]['trait'] = str_replace('!', ';', $opponent[0]['trait']);
                    }
                    if (isset($modifiers[6]) && $modifiers[6] !== "none") {
                        $opponent[0]['ai_actions'] .= str_replace(';', ':', $modifiers[6]).";";
                    }
                    if (isset($modifiers[7]) && $modifiers[7] !== "none") {
                        $opponent[0]['ai_actions'] .= str_replace(';', ':', $modifiers[7]).";";
                    }
                    if (isset($modifiers[8]) && $modifiers[8] !== "none") {
                        $opponent[0]['ai_actions'] .= str_replace(';', ':', $modifiers[8]).";";
                    }
                    if (isset($modifiers[9]) && $modifiers[9] !== "none") {
                        $opponent[0]['ai_actions'] .= str_replace(';', ':', $modifiers[9]).";";
                    }
                }

                // Enable the traits
                if ( isset($opponent[0]['trait']) && $opponent[0]['trait'] != '') {
                    $opponent[0]['bloodlineEffect'] = explode(';', $opponent[0]['trait']);
                    $i = 0;
                    while ($i < count($opponent[0]['bloodlineEffect'])) {
                        $opponent[0]['bloodlineEffect'][$i] = explode(':', $opponent[0]['bloodlineEffect'][$i]);
                        $i++;
                    }
                }
                
                // Add the summon to the battle array
                $newID = $this->createNewID();
                $opponent[0]['id'] = $newID;
                $opponent[0]['original_id'] = $newID;
                
                // Create new user
                $this->attacker['data'][ $newID ] = $opponent[0];
                $this->attacker['ids'][] = $newID;
                $this->attacker['alliances'][ $newID ] = $this->attacker['alliances'][ $this->attackerID ];
                
                // Prevent sealing in first round
                $this->attacker['actionInfo'][ $newID ]['prevent_seal'] = 1;
                
                // Set needed arrays
                $this->attacker['status'][ $newID ] = array(); 

                // Set the links between summon and summoner
                $this->attacker['data'][ $newID ]['summontype'] = $this->attackerSide;
                $this->attacker['data'][ $newID ]['summonerID'] = $this->attackerID;
                $this->attacker['data'][ $this->attackerID ]['summonedID'] = $newID;
                
                // Need to set this variable to avoid the reset stats function
                $this->attacker['data'][ $newID ]['just_summoned'] = true;

                // Transfer data to outside
                $this->attacker['data'][ $this->attackerID ]['summons'] = 1;
                
                // actionInfo for summoner to show up on battle log
                $this->attacker['actionInfo'][ $this->attackerID ]['sumInfo'] = "success";
                
                // Set a message for the new summon that he/she/it has entered
                $this->setUserActionInfo( "attacker", $newID, $this->attacker['data'][ $newID ]['username'] . ' enters the battlefield');
                
                
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['sumInfo'] = "cantControl";
            }
        } else {
            $this->attacker['actionInfo'][ $this->attackerID ]['sumInfo'] = "badOdds";
        }
    }
   
    
    //  Set reflection effect for user, effectively reflecting damage away from user to others
    //  REF:(user|opponent)|(PERC|STAT):TNGW:(earth.fire.ice|ALL):roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  REF:user:PERC:TNGW:ALL:1:5:10:5
    private function REF($modifiers){
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set an elemental text
        $text = $this->getStatText($modifiers[3]) . " and ".  strtolower($this->getElementalText( $modifiers[4] ))." elemental ";
        
        // Get the amount of turns
        $rand = random_int($modifiers[5], $modifiers[6]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[7] + $modifiers[8] * $this->jutsu_data['level']);
            
            // Insert the status 
            $this->insert_status($stattarget, $statID, 'REF:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $modifiers[4] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $text . ' damage',
                "effect" => "reflected",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }
    
    //  Set absorption status effect 
    //  ABS:(user|opponent):(PERC|STAT):TNGW:(earth.fire.ice|ALL):roundsMinInt:roundsMaxInt:(basePower):(lvlInc)
    //  ABS:opponent:PERC:TNGW:earth.fire:1:5:40:1
    private function ABS($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set an elemental text
        $text = $this->getStatText($modifiers[3]) . " and ".  strtolower($this->getElementalText( $modifiers[4] ))." elemental ";
        
        // Get the amount of turns
        $rand = random_int($modifiers[5], $modifiers[6]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[7] + $modifiers[8] * $this->jutsu_data['level']);
            
            // Insert the status 
            $this->insert_status($stattarget, $statID, 'ABS:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $modifiers[4] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $text . ' damage',
                "effect" => "absorbed",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }
    
    //  Set "Stat down" status effect
    //  STDE:opponent:(PERC|STAT):(speed|strength|...):roundsMinInt:roundsMaxInt:(basePower):(lvlInc)
    //  STDE:opponent:PERC:speed:2:4:10:0.1
    private function STDE($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
       
        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'STAD:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $modifiers[3],
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }
    
    //  Set "Stat up" status effect 
    //  STUP:opponent:(PERC|STAT):(speed|strength|...):roundsMinInt:roundsMaxInt:(basePower):(lvlInc)
    //  STUP:opponent:PERC:speed:2:4:10:0.1
    private function STUP($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'STAU:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $modifiers[3],
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }
    
    //  Set "Increase offense" status effect 
    //  OFFU:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function OFFU($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ATT:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $this->getStatText($modifiers[3]) . " offensive capability",
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Decrease offense" status effect 
    //  OFFD:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function OFFD($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ATTD:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $this->getStatText($modifiers[3]) . " offensive capability",
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    /*  Set "Increase defense" status effect     */
    //  DEFU:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function DEFU($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DEF:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $this->getStatText($modifiers[3]) . " defensive capability",
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    /*  Set "Decrease Defense" status effect     */
    //  DEFD:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function DEFD($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DEFD:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $this->getStatText($modifiers[3]) . " defensive capability",
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "increase damage done" status effect
    //  DINC:(user|opponent):TNGW:(PERC|STAT):roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function DINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DINC:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power , true );
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => "damage",
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Decrease damage done" status effect
    //  DDEC:(user|opponent):TNGW:(PERC|STAT):roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function DDEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DDEC:' . ( $rand +1 ) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => "damage",
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "increase elemental damage done" status effect
    //  EINC:(user|opponent):(PERC|STAT):element:roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  EINC:user:PERC:shadow:2:2:5:0.01375
    private function EINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set an elemental text
        $text = $this->getElementalText( $modifiers[3] );
        
        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'EINC:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $text . ' damage',
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "increase elemental damage done" status effect
    //  EDEC:(user|opponent):(PERC|STAT):element:roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function EDEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set an elemental text
        $text = $this->getElementalText( $modifiers[3] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + $modifiers[7] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'EDEC:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $text . ' damage',
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "increase healing done" status effect     
    //  HEAINC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function HEAINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[5] + $modifiers[6] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'HEAINC:' . $rand . ':' . $modifiers[2] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => 'healability',
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "decrease healing done" status effect     
    //  HEADEC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower:lvlInc
    private function HEADEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );

        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[5] + $modifiers[6] * $this->jutsu_data['level']);
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'HEADEC:' . $rand . ':' . $modifiers[2] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => 'healability',
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Increase elemental damage sustained" Status effect
    //  ESINC:(user|opponent):(PERC|STAT):element:roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  ESINC:opponent:PERC:gold:2:2:5:0.0125
    private function ESINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set an elemental text
        $text = $this->getElementalText( $modifiers[3] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + ($modifiers[7] * $this->jutsu_data['level']));
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ESI:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $text." elemental",
                "effect" => "sustain more",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Decrease elemental damage sustained" Status effect
    //  ESDEC:(user|opponent):(PERC|STAT):element:roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  ESDEC:opponent:PERC:gold:2:2:5:0.0125
    private function ESDEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Set an elemental text
        $text = $this->getElementalText( $modifiers[3] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + ($modifiers[7] * $this->jutsu_data['level']));
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ESD:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $text." elemental",
                "effect" => "sustain less",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Increase damage sustained" Status effect
    //  DSINC:(user|opponent):(PERC|STAT):TNGW:roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  DSINC:opponent:PERC:TNGW:5:5:100:50
    private function DSINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + ($modifiers[7] * $this->jutsu_data['level']));
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DSI:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Get offence type string
            $desc = $this->getStatText( $modifiers[3] );
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $desc,
                "effect" => "sustain more",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Decrease damage sustained" Status effect 
    //  DSDEC:(user|opponent):(PERC|STAT):TNGW:roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  DSDEC:opponent:PERC:TNGW:5:5:100:50
    private function DSDEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[6] + ($modifiers[7] * $this->jutsu_data['level']));
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DSD:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power, true);
            
            // Get offence type string
            $desc = $this->getStatText( $modifiers[3] );
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => $desc,
                "effect" => "sustain less",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Increase Armor" status effect 
    //  ARINC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  ARINC:opponent:PERC:3:3:100:50
    private function ARINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[5] + ($modifiers[6] * $this->jutsu_data['level']));
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'AINC:' . ($rand+1) . ':' . $modifiers[2] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => 'armor',
                "effect" => "increased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }

    //  Set "Decrease Armor" status effect
    //  ARDEC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower:lvlInc
    //  ARDEC:user:PERC:3:3:100:50
    private function ARDEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1],  $modifiers );
        
        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $this->overZero($modifiers[5] + ($modifiers[6] * $this->jutsu_data['level']));
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ADEC:' . ($rand+1) . ':' . $modifiers[2] . ':' . $power, true);
            
            // Set the message 
            $this->setLogMessage( "attacker" , $this->attackerID, array(
                "affectedName" => $this->{$stattarget}['data'][ $statID ]['username'],
                "affectedStat" => 'armor',
                "effect" => "decreased",
                "rounds" => $rand,
                "cssClass" => "logBlue"
            ));
        }
    }
}