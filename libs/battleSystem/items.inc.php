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

class item extends basicFunctions {

    public function item (
            $master, 
            $user, 
            $userid, 
            $target, 
            $targetid, 
            $itemdata
    ) {
        
        // Save data in this class
        $this->master = $master;
        
        // Save the attacker in local variables
        $this->attackerSide = $user;
        $this->attackerID = $userid;
        $this->attacker = $this->master->{$user};
        $this->attackerFriends = $this->attacker['ids'];
        $this->attacker_is_ai = $this->master->is_user_ai( $user , $userid );
        
        // Save the target in local variables
        $this->targetSide  = $target;
        $this->targetID = $targetid;
        $this->target = $this->master->{$target};
        $this->targetFriends = $this->target['ids'];
        
        // Check if the two sides are the same
        $this->isTheSameSide = ( $user == $target ) ? true : false;
        
        // Save the jutsu data in local variables
        $this->item_data = $itemdata;
        $this->iid = $this->item_data['id'];
    
        // Call execute weapon function. 
        if( isset($this->item_data) ){
            $this->execute_item();
        }
        else{
            $this->setUserActionInfo( $this->attackerSide, $this->attackerID, $this->attacker['data'][ $this->attackerID ]['username'] . ' tries and fails to use an unknown action. Item could not be found.');
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
            $this->master->set_battledata($this->attackerSide, $this->attacker);
            $this->master->set_battledata($this->targetSide, $this->target);
        }
    }

    // The function to execute the item functions
    private function execute_item() {
        
        // Set uses array
        if( !isset($this->attacker['items'][ $this->attackerID ][ $this->item_data['inv_id'] ]['uses']) ){
            $this->attacker['items'][ $this->attackerID ][ $this->item_data['inv_id'] ]['uses'] = 0;
        }
        
        // Set his/its/her
        $useritem = $this->master->getHisHer( $this->attacker['data'][ $this->attackerID ]['gender'] );
        
        // Check if the user has any of this item left
        if( $this->attacker['items'][ $this->attackerID ][ $this->item_data['inv_id'] ]['uses'] < $this->item_data['stack'] ){
            
            // Count the uses of this item
            if (!$this->attacker['items'][ $this->attackerID ][ $this->item_data['inv_id'] ]['infinity_durability']) {
                $this->attacker['items'][ $this->attackerID ][ $this->item_data['inv_id'] ]['uses'] += 1;
            }

            // Check that the user hasn't used the item more than the max times
            if ($this->attacker['items'][ $this->attackerID ][ $this->item_data['inv_id'] ]['uses'] <= $this->item_data['max_uses']) {

                // Set attack message & data
                $this->attacker['actionInfo'][ $this->attackerID ]['message'] = "<i>".$this->attacker['data'][ $this->attackerID ]['username'] . ' </i> used ' . $useritem . ' ' . $this->item_data['name'];
                $this->attacker['actionInfo'][ $this->attackerID ]['description'] = "";
                $this->attacker['actionInfo'][ $this->attackerID ]['element'] = strtolower($this->item_data['element']);
                $this->attacker['actionInfo'][ $this->attackerID ]['targetType'] = $this->targetSide;

                // Use 1
                $modifiers = explode(':', $this->item_data['use']);
                $function = $modifiers[0];
                if (method_exists($this, $function)) {
                    $this->$function($modifiers);
                } else {
                    $this->setUserActionInfo( $this->attackerSide, $this->attackerID, 
                            "<i>".$this->attacker['data'][ $this->attackerID ]['username'] . ' </i>: 
                            Item data glitched on use-1. Report bug in forum.');
                    return false;
                }

                // Use 2. Only if set though
                if ( 
                    isset($this->item_data['use2']) &&
                    $this->item_data['use2'] !== ""
                ) {
                    $modifiers = explode(':', $this->item_data['use2']);
                    $function = $modifiers[0];
                    if (method_exists($this, $function)) {
                        $this->$function($modifiers);
                    } else {
                         $this->setUserActionInfo( $this->attackerSide, $this->attackerID, 
                            "<i>".$this->attacker['data'][ $this->attackerID ]['username'] . ' </i>: 
                            Item data glitched on use-2. Report bug in forum.');
                         return false;
                    }
                }
            } else {
                //    User used this item too many times
                $this->setUserActionInfo( "attacker", $this->attackerID, "<i>".$this->attacker['data'][ $this->attackerID ]['username'] . '</i> tried to use an item, but has already used the item too many times during this battle.');
            }
        }
        else{
            $this->setUserActionInfo( "attacker", $this->attackerID, "<i>".$this->attacker['data'][ $this->attackerID ]['username'] . '</i> tried to use an item that is no longer in '.$useritem.' inventory.');
        }
    }

    /*
     * 		Insert a new status effect into the user or opp data.
     * 		Used by all jutsu that induce status effects.
     */
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
   
    
    /* 
     *  In case user targeted himself, set target and ID to that
     */
    private function getTargetAndID( $tagTarget = "opponent" ){
        
        // Define effectSide & effectID
        $effectSide = $effectID = "";
        
        // Find the effectSide & effectID
        if ( $tagTarget == 'opponent' ) {
            if ( $this->isTheSameSide ) {
                $effectSide = "attacker";
                $effectID = $this->targetID;
            } else {
                $effectSide = "target";
                $effectID = $this->targetID;
            }
        } else {
            $effectSide = "attacker";
            $effectID = $this->attackerID;
        }
        
        // Return stuff
        return array( $effectSide , $effectID );
    }

    /*
     * 	Item effect functions
     * 	Following below are all item effect functions
     * 	Function names are identical to the effect tag identifiers in the database
     */

    // Repel effect - used in travel system.
    private function repel($modifiers) {
        // N/A
    }

    //	Damage opponent
    //  DMG:(PERC|STAT|CALC):TNWG:STR|STAT
    private function DMG($modifiers) {
        if ($modifiers[1] == 'PERC') {
            
            // Percentage-based damage
            if ($modifiers[3] == 'STR') {
                $perc = $this->item_data['strength'];
            } else {
                $perc = $modifiers[3];
            }
            $damage = (($this->target['data'][ $this->targetID ]['max_health'] / 100) * $perc);
            
        } elseif ($modifiers[1] == 'STAT') {
            
            // Static damage
            if ($modifiers[3] == 'STR') {
                $damage = $this->item_data['strength'];
            } else {
                $damage = $modifiers[3];
            }
            
        } elseif ($modifiers[1] == 'CALC') {
            
            // Damage calculation. First get power
            if ($modifiers[3] == 'STR') {
                $power = $this->item_data['strength'];
            } else {
                $power = $modifiers[3];
            }
            
            list( $type, $stat1, $stat2 ) = $this->translate_tag_type( $modifiers[2] );
            
            $damage = calc::calc_damage( 
                array(
                    "user_data" => $this->attacker['data'][ $this->attackerID ], 
                    "target_data" => $this->target['data'][ $this->targetID ], 
                    "type" => $type, 
                    "stat1" => $stat1, 
                    "stat2" => $stat2, 
                    "power" => $power,
                    "scalePower" => true
                )
            );
        }
        
        // Adjust based on elemental mastery
        $damage = $this->adjustElementalDamage("attacker", $this->attackerID, $damage, $this->attacker['actionInfo'][ $this->attackerID ]['element'] );
        
        // Set the damage and type in the actionInfo variable
        $this->attacker['actionInfo'][ $this->attackerID ]['damage'][ $this->targetID ] = $damage;
        
        // Set the target type and target ID
        $this->attacker['actionInfo'][ $this->attackerID ]['type'] = $modifiers[2];
        $this->attacker['actionInfo'][ $this->attackerID ]['targetIDs'][] = $this->targetID;
    }

    //	Heal self
    // HEA:(STAT|PERC|):(STR|STAT)
    private function HEA($modifiers) {
        
        // If no heal has been set yet
        if( !isset($this->attacker['actionInfo'][ $this->attackerID ]['healed']) ){
            $this->attacker['actionInfo'][ $this->attackerID ]['healed'] = 0;
        }
        
        // Calculate heal
        if ($modifiers[1] == 'STAT') {
            if ($modifiers[2] == 'STR') {
                $this->attacker['actionInfo'][ $this->attackerID ]['healed'] += $this->item_data['strength'];
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['healed'] += $modifiers[2];
            }
        } elseif ($modifiers[1] == 'PERC') {
            $perc = $this->attacker['data'][ $this->attackerID ]['max_health'] / 100;
            if ($modifiers[2] == 'STR') {
                $this->attacker['actionInfo'][ $this->attackerID ]['healed'] += $this->item_data['strength'] * $perc;
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['healed'] += $modifiers[2];
            }
        }
        
        // Log healing under the attacker rather than under the attacked
        $this->attacker['actionInfo'][ $this->attackerID ]['healLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }
    
    //	Stamina restore
    // STA:(STAT|PERC):(STR|STAT)
    private function STA($modifiers) {
        
        // If no heal has been set yet
        if( !isset($this->attacker['actionInfo'][ $this->attackerID ]['starestored']) ){
            $this->attacker['actionInfo'][ $this->attackerID ]['starestored'] = 0;
        }
        
        // Calculate heal
        if ($modifiers[1] == 'STAT') {
            if ($modifiers[2] == 'STR') {
                $this->attacker['actionInfo'][ $this->attackerID ]['starestored'] += $this->item_data['strength'];
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['starestored'] += $modifiers[2];
            }
        } elseif ($modifiers[1] == 'PERC') {
            $perc = $this->attacker['data'][ $this->attackerID ]['max_sta'] / 100;
            if ($modifiers[2] == 'STR') {
                $this->attacker['actionInfo'][ $this->attackerID ]['starestored'] += $this->item_data['strength'] * $perc;
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['starestored'] += $modifiers[2];
            }
        }
        
        // Log healing under the attacker rather than under the attacked
        $this->attacker['actionInfo'][ $this->attackerID ]['starestoredLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }
    
    //	Chakra restore
    // CHA:(STAT|PERC):(STR|STAT)
    private function CHA($modifiers) {
        
        // If no heal has been set yet
        if( !isset($this->attacker['actionInfo'][ $this->attackerID ]['charestored']) ){
            $this->attacker['actionInfo'][ $this->attackerID ]['charestored'] = 0;
        }
        
        // Calculate heal
        if ($modifiers[1] == 'STAT') {
            if ($modifiers[2] == 'STR') {
                $this->attacker['actionInfo'][ $this->attackerID ]['charestored'] += $this->item_data['strength'];
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['charestored'] += $modifiers[2];
            }
        } elseif ($modifiers[1] == 'PERC') {
            $perc = $this->attacker['data'][ $this->attackerID ]['max_cha'] / 100;
            if ($modifiers[2] == 'STR') {
                $this->attacker['actionInfo'][ $this->attackerID ]['charestored'] += $this->item_data['strength'] * $perc;
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['charestored'] += $modifiers[2];
            }
        }
        
        // Log healing under the attacker rather than under the attacked
        $this->attacker['actionInfo'][ $this->attackerID ]['charestoredLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }

    //  Set "healing over time (HOT)" Status effect.
    //  HOT:user|opponent:PERC|STAT|HEA:minRounds:maxRounds:STAT|DAM|HEA:basePowerInt
    //  HOT:opponent:(PERC|STAT|HEA):1:3:(STAT|DAM|HEA):10     */
    private function HOT($modifiers) {
        
        // Figure Out who to hit
        list($residualtarget, $residualID) = $this->getTargetAndID( $modifiers[1] );
        
        // Random amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Get the power
            if ($modifiers[5] == 'STAT') {
                $power = $modifiers[6];
            } elseif ($modifiers[5] == 'DAM') {
                $damperc = $this->attacker['actionInfo'][ $this->attackerID ]['damage'][ $residualID ] / 100;
                $power = $damperc * $modifiers[6];
            } elseif ($modifiers[5] == 'HEA') {
                $damperc = $this->{$residualtarget}['actionInfo'][ $residualID ]['healed'] / 100;
                $power = $damperc * $modifiers[6];
            }
            
            // Insert status
            $this->insert_status($residualtarget, $residualID, 'HEA:' . ($rand+1) . ':' . $modifiers[2] . ':' . strtolower($this->item_data['element']) . ':' . $power, true);
            
            // Set actionInfo variable to inform battle log etc.
            $this->{$residualtarget}['actionInfo'][ $residualID ]['hotInfo'] = "success";
            $this->{$residualtarget}['actionInfo'][ $residualID ]['hotRounds'] = $rand;
            
            // Log healing over time under the attacker rather than under the attacked
            $this->{$residualtarget}['actionInfo'][ $residualID ]['hotLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
        }
    }
    
    //  Set "increase healing done" status effect     
    //  HEAINC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower
    private function HEAINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[5];
            
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
    //  HEADEC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower
    private function HEADEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[5];
            
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
    
    
    //	Stun opponent
    // STUN:chance:(turns-min):(turns-max)
    private function STUN($modifiers) {
        
        // Get target & ID
        list($stuntarget, $stunID) = $this->getTargetAndID();

        // Check if he has stun resist
        if ( !$this->is_user_stunResist($stuntarget, $stunID) ) {
            
            // Random Number Check
            $rand = random_int(0, 100);
            if ($rand > (100 - $modifiers[1])) {
                
                // Random Amount of Stun Turns
                $turns = random_int($modifiers[2], $modifiers[3]);
                
                // If more than one, do stun
                if ($turns > 0) {
                    
                    // Set stun tag & stun resist tag
                    $this->insert_status($stuntarget, $stunID, 'STUN:' . ($turns+1), true);
                    $this->insert_status($stuntarget, $stunID, 'STUNR:' . (3 + ($turns * 2)), true);
                    
                    // Mark the user as stunned instantly. Comment to have effect postponed to next round.
                    // $this->set_user_stunned( $stuntarget, $stunID, $turns, true );
                    
                    // Set stun
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

    //  Stun resistance
    // STUNR:chance:(turns-min):(turns-max)
    private function STUNR($modifiers) {
        
        // See if succeeded
        $rand = random_int(1, 100);
        if ($rand <= $modifiers[1]) {
            
            // Get the number of turns
            $turns = random_int($modifiers[2], $modifiers[3]);
            if ($turns > 0) {
                
                // Set stun resist status
                $this->insert_status('attacker', $this->attackerID, 'STUNR:' . $turns);
                
                // Update the user array
                $this->set_user_stunResist( "attacker", $this->attackerID, $turns, true );
                
                // Set actionInfo status
                $this->attacker['actionInfo'][ $this->attackerID ]['stunRinfo'] = 'success';
                
            } else {
                $this->attacker['actionInfo'][ $this->attackerID ]['stunRinfo'] = 'failed';
            }
        } else {
            $this->attacker['actionInfo'][ $this->attackerID ]['stunRinfo'] = 'failed';
        }
        
    }

    //	Flee from battle
    // FLE:chance
    private function FLE($modifiers) {
        
        // Try fleeing: $modifiers[1] == chance
        $this->try_fleeing("attacker", $this->attackerID, $modifiers[1] );

    }

    //  Remove non-permanent status effects
    //  CLEAR:user|oppoenent
    private function CLEAR($modifiers) {
        
        // Figure Out who to hit
        list($cleartarget, $clearID) = $this->getTargetAndID( $modifiers[1] );
        
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
    

    //	Residual damage
    // RDA:(turns-min):(turns-max):(PERC|STAT|TSTA):(int)
    private function RDA($modifiers) {
        
        // Get target & ID
        list($rdatarget, $rdaID) = $this->getTargetAndID();
        
        // Set random number of turns
        $rand = random_int($modifiers[1], $modifiers[2]);
        
        // Insert status effect tag
        $this->insert_status($rdatarget, $rdaID, 'DAM:' . $rand . ':' . $modifiers[3] . ':' . strtolower($this->item_data['element']) . ':' . $modifiers[4]);
        
        // Set actionInfo variable to inform battle log etc.
        $this->{$rdatarget}['actionInfo'][ $rdaID ]['rdaInfo'] = "success";
        $this->{$rdatarget}['actionInfo'][ $rdaID ]['rdaRounds'] = $rand;
    
        // Log stun under the attacker rather than under the attacked
        $this->{$rdatarget}['actionInfo'][ $rdaID ]['rdaLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
    }
    
    //  Set "Increase offense" status effect 
    //  OFFU:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower
    private function OFFU($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[6];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ATT:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
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
    //  OFFD:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower
    private function OFFD($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[6];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ATTD:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
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
    //  DEFU:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower
    private function DEFU($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[6];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DEF:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
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
    //  DEFD:(user|opponent):(PERC|STAT|TSTA):TNGW:roundsMinInt:roundsMaxInt:basePower
    private function DEFD($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[6];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'DEFD:' . ($rand+1) . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
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

    //  Set "Stat down" status effect
    //  STDE:opponent:(PERC|STAT):(speed|strength|...):roundsMinInt:roundsMaxInt:basePower
    private function STDE($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );
       
        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[6];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'STAD:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
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
    //  STUP:opponent:(PERC|STAT):(speed|strength|...):roundsMinInt:roundsMaxInt:basePower
    private function STUP($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );

        // Get the amount of turns
        $rand = random_int($modifiers[4], $modifiers[5]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[6];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'STAU:' . $rand . ':' . $modifiers[2] . ':' . $modifiers[3] . ':' . $power);
            
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
    
    //  Set "Increase Armor" status effect 
    //  ARINC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower
    private function ARINC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );
        
        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[5];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'AINC:' . $rand . ':' . $modifiers[2] . ':' . $power, true);
            
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
    //  ARDEC:(user|opponent):(PERC|STAT):roundsMinInt:roundsMaxInt:basePower
    private function ARDEC($modifiers) {
        
        // Figure Out who to hit
        list($stattarget, $statID) = $this->getTargetAndID( $modifiers[1] );
        
        // Get the amount of turns
        $rand = random_int($modifiers[3], $modifiers[4]);
        if ($rand > 0) {
            
            // Calculate the power
            $power = $modifiers[5];
            
            // Insert the status
            $this->insert_status($stattarget, $statID, 'ADEC:' . $rand . ':' . $modifiers[2] . ':' . $power, true);
            
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