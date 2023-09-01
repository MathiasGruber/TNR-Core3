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

class status extends basicFunctions {
    /*
     * 	Constructor to pass important data to the class instance
     * 	Abstract classes would have been preferred, but do not work due to the manipulative nature
     * 	of the needed functions.
     */
    public function status(
            $master,    // Reference to the previous object
            $target,    // Target Side
            $targetid,  // Target ID
            $opponent,  // Opponent Side
            $type       // Type, 0=pre-round-status, 1=post-round-status
    ) {
        // Save data in this class
        $this->master = $master;
        
        // Save the attacker in local variables
        $this->activeSide = $target;
        $this->activeID = $targetid;
        $this->active = $this->master->{$target};
        
        // The type of status
        $this->type = $type;
        
        // Index used for tracking which status effect to decrement after the turn
        $this->backrecord = 0;
        
        // Set the opponents (only used for post-statuses)
        if ($opponent != null) {
            $this->otherSide = $opponent;
            $this->otherIDs = $this->master->{$opponent}['ids'];
            $this->other = $this->master->{$opponent};
        }
    }

    /*  Function to return the local copy of the $battle data to the master class	 */
    public function return_data() {
        if (isset($this->otherSide)) { $this->master->set_battledata($this->otherSide, $this->other); }
        $this->master->set_battledata($this->activeSide, $this->active);
    }

    /*  Decrement turns left for the status effect     */
    public function decrement_turns($i) {
        // Get the index of the POSTPONE-tag and remove it. 
        $postPoneIndex = count($this->active['status'][$this->activeID][$i]) - 1;
        if ($this->active['status'][$this->activeID][$i][$postPoneIndex] === "POSTPONE") {
            unset($this->active['status'][$this->activeID][$i][$postPoneIndex]);
        }
        
        // Reduce amount of turns left
        if(isset($this->active['status'][$this->activeID][$i][1])) { 
            $this->active['status'][$this->activeID][$i][1]--; 
        }
    }
    
    // Go through all user status effects and remove those that are over
    public function removeFinishedEffects() {
        for ($i = 0, $size = count($this->active['status'][$this->activeID]); $i < $size; $i++) {
            // Only remove when count is below 0. Otherwise effects can be removed on their creation round
            if(isset($this->active['status'][$this->activeID][$i][1])) {
                if($this->active['status'][$this->activeID][$i][1] <= 0) {
                    unset($this->active['status'][ $this->activeID ][$i]);
                }
            }
        } 
        // Readjust array indices
        $this->active['status'][$this->activeID] = array_values($this->active['status'][$this->activeID]);
    }

    // Check the status effect type & if the postpone effect is active
    private function checkTypeAndPostpone($requireType, $modifiers) {
        if($this->type === $requireType) {
            if($modifiers[count($modifiers) - 1] !== "POSTPONE") {
                return true;
            }
        }
        return false;
    }
    
    /*
     * 	Status effects code
     * 	Code of each individual status effect follows below
     * 	Functions are named after the status effect's status tag, as prescribed in the database
     */

    //	Stun target
    public function STUN($modifiers) {
        if ($this->checkTypeAndPostpone(0, $modifiers)) {
            $this->active['actionInfo'][$this->activeID]['stun'] = true;
            $this->active['actionInfo'][$this->activeID]['stunrounds'] = $modifiers[1];
        }
    }

    //  Resist stun effects
    public function STUNR($modifiers) {
        if ($this->checkTypeAndPostpone(0, $modifiers)) {
            $this->active['actionInfo'][$this->activeID]['stun_resist'] = true;
            $this->active['actionInfo'][$this->activeID]['stunresistrounds'] = $modifiers[1];
        }
    }

    //  Prevent fleeing from the battle
    public function FLEE($modifiers) {
        if ($this->checkTypeAndPostpone(0, $modifiers)) {
            $this->active['actionInfo'][$this->activeID]['flee_lock'] = true;
            $this->active['actionInfo'][$this->activeID]['fleeresistrounds'] = $modifiers[1];
        }
    }

    //    Decrease stat
    public function STAD($modifiers) {
        if ($this->type == 0) {
            
            // Do the reduction
            if ($modifiers[2] == 'STAT') {
                $this->reduct = $modifiers[4];
                $this->active['data'][ $this->activeID ][$modifiers[3]] -= $this->reduct;
            } elseif ($modifiers[2] == 'PERC') {
                
                // Ensure that effects don't stack 
                $this->setOriginalValue( "active", $modifiers[3], $this->activeID, "data" );
                
                // DO calculation
                $perc = $this->active['data'][ $this->activeID ][$modifiers[3]] / 100;
                $this->reduct = $perc * $modifiers[4];
                $this->active['data'][ $this->activeID ][$modifiers[3]] -= $this->reduct;
                
                // Ensure that effects don't stack finish
                $this->adjustOriginalValue( "active", $modifiers[3], $this->activeID, "data" );
            }
            
            // Set the message 
            $this->setLogMessage( "active" , $this->activeID, array(
                "affectedName" => $this->active['data'][ $this->activeID ]['username'],
                "affectedStat" => ucwords($modifiers[3]),
                "effect" => "reduced",
                "cssClass" => "logRed"
            ));
        }
        if ($this->active['data'][ $this->activeID ][$modifiers[3]] < 0) {
            $this->active['data'][ $this->activeID ][$modifiers[3]] = 0;
        }
    }

    //    Increase stat
    public function STAU($modifiers) {
        if ($this->type == 0) {
            
            // Do the increase
            if ($modifiers[2] == 'STAT') {
                $this->reduct = $modifiers[4];
                $this->active['data'][ $this->activeID ][$modifiers[3]] += $this->reduct;
            } elseif ($modifiers[2] == 'PERC') {
                
                // Ensure that effects don't stack 
                $this->setOriginalValue( "active", $modifiers[3], $this->activeID, "data" );
                
                // DO calculation
                $perc = $this->active['data'][ $this->activeID ][$modifiers[3]] / 100;
                $this->reduct = $perc * $modifiers[4];
                $this->active['data'][ $this->activeID ][$modifiers[3]] += $this->reduct;
                
                // Ensure that effects don't stack finish
                $this->adjustOriginalValue( "active", $modifiers[3], $this->activeID, "data" );
            }
            
            
            // Set the message 
            $this->setLogMessage( "active" , $this->activeID, array(
                "affectedName" => $this->active['data'][ $this->activeID ]['username'],
                "affectedStat" => ucwords($modifiers[3]),
                "effect" => "increased",
                "cssClass" => "logGreen"
            ));
        }
    }
    
    /*  Convenience function used in Attack/Defence Increase/Decrease */
    private function do_update_stat( $modifiers, $effectString, $typeDef ){
        
        // Set variables for readability.
        $statString = $modifiers[3];
        $calcType = $modifiers[2];
        
        // css Class. Set inside the loop
        $cssClass = $typePretty = $action = "";
        
        // Loop through all the strings in the statString, e.g. "TNGW"
        for ($i = 0, $size = strlen($statString); $i < $size; $i++) {
            
            // Set variables needed depending on T,N,G, or W
            $column = "";
            switch( $statString[$i] ){
                case "T": $column = "tai_".$typeDef; break;
                case "N": $column = "nin_".$typeDef; break;
                case "G": $column = "gen_".$typeDef; break;
                case "W": $column = "weap_".$typeDef; break;
            }
            
            // Ensure that effects don't stack 
            $this->setOriginalValue( "active", $column, $this->activeID, "data" );
            
            // Do calculation
            if ($calcType === 'STAT') { $change = $modifiers[4]; } 
            elseif ($calcType === 'PERC') { $change = ($this->active['data'][$this->activeID][$column] / 100) * $modifiers[4]; } 
            elseif ($calcType === 'TSTA') { $change = $modifiers[4] / $modifiers[1]; }
            
            // Make a nicer number
            $change = round($change, 1);
            if($change > 0) {
                
                // Add or subtract
                switch($effectString) {
                    case "increased": {
                        $this->active['data'][ $this->activeID ][ $column ] += $change; 
                        $cssClass = "logGreen";
                    } break;
                    case "decreased": {
                        $this->active['data'][ $this->activeID ][ $column ] -= $change; 
                        $cssClass = "logRed";
                    } break;
                }
                
                // Make sure we don't go negative
                if ($this->active['data'][$this->activeID][$column] < 0) { $this->active['data'][$this->activeID][$column] = 0; }
            }
            
            // Ensure that effects don't stack finish
            $this->adjustOriginalValue( "active", $column, $this->activeID, "data" );
        } 
        
        // Set the type
        switch($typeDef) {
            case "off": $typePretty = "offensive"; break;
            case "def": $typePretty = "defensive"; break;
        }

        // Set the message 
        $this->setLogMessage("active", $this->activeID, array(
            "affectedName" => $this->active['data'][ $this->activeID ]['username'],
            "affectedStat" => $typePretty . " " . $this->getStatText($statString),
            "effect" => $effectString,
            "cssClass" => $cssClass
        ));       
    }
    
    //    Increase Attack
    public function ATT($modifiers) {
        if ((int)$this->type === 0) { $this->do_update_stat($modifiers, "increased", "off"); }
    }

    //    Decrease Attack
    public function ATTD($modifiers) {
        if ((int)$this->type === 0) { $this->do_update_stat($modifiers, "decreased", "off"); }
    }

    //    Increase Defense
    public function DEF($modifiers) {
        if ((int)$this->type === 0) { $this->do_update_stat($modifiers, "increased", "def"); }
    }

    //    Decrease Defense
    public function DEFD($modifiers) {
        if ((int)$this->type === 0) { $this->do_update_stat($modifiers, "decreased", "def"); }
    }

    /*
     * 	Following below are the status effects executed POST damage calculation.
     */

    //    Damage
    public function DAM($modifiers) {
        if ($this->type == 1) {
             if ( !$this->is_user_poisonResist("active", $this->activeID) ) {
                if( !isset($this->active['actionInfo'][ $this->activeID ]['poison']['damage']) ){
                    $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] = 0;
                }
                
                $this->active['actionInfo'][ $this->activeID ]['poison']['element'] = $modifiers[3];
                if ($modifiers[2] == 'PERC') {
                    $perc = $this->active['data'][ $this->activeID ]['max_health'] / 100;
                    $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] += $perc * $modifiers[4];
                } elseif ($modifiers[2] == 'STAT') {
                    $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] += $modifiers[4];
                } elseif ($modifiers[2] == 'TSTA' && $modifiers[1] > 0) {
                    $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] += $modifiers[4] / $modifiers[1];
                } elseif ($modifiers[2] == 'TINC') {
                    $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] += $modifiers[4] * $modifiers[1];
                }
             }
        }
    }

    //    Healing
    public function HEA($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            
            if( !isset($this->active['actionInfo'][ $this->activeID ]['healed']) ){
                $this->active['actionInfo'][ $this->activeID ]['healed'] = 0;
            }
            if ($modifiers[2] == 'PERC') {
                $perc = $this->active['data'][ $this->activeID ]['max_health'] / 100;
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $perc * $modifiers[4];
            } elseif ($modifiers[2] == 'STAT') {
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[4];
            } elseif ($modifiers[2] == 'TSTA' && $modifiers[1] > 0) {
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[4] / $modifiers[1];
            } elseif ($modifiers[2] == 'TINC') {
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[4] * $modifiers[1];
            }
            //echo"Terr Testing, don't report";
            //print_r($this->active['actionInfo'][ $this->activeID ]);
        }
    }
    
    //    Absorption effect
    //    ABS:turns:PERC|STAT:TNGW:elements:power
    public function ABS($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            $elements = explode(".", $modifiers[4]);
            foreach ($this->otherIDs as $id) {
                if (isset($this->other['actionInfo'][ $id ]['damage']) && $this->other['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0 ) {
                    $lElement = $this->other['actionInfo'][ $id ]['element'];
                    if ( 
                         ($this->is_element_good( $elements, $lElement ) || $lElement == "none" ) &&
                         stristr($modifiers[3], $this->other['actionInfo'][ $id ]['type'])
                    ) {
                        if ($modifiers[2] == 'PERC') {
                            $abs = ( $this->other['actionInfo'][ $id ]['damage'][$this->activeID] / 100 ) * $modifiers[5];
                        } elseif ($modifiers[2] == 'STAT') {
                            $abs = $modifiers[5];
                        }
                        
                        // Update damage & absorption
                        if( !isset($this->active['actionInfo'][ $this->activeID ]['absorb']) ){
                            $this->active['actionInfo'][ $this->activeID ]['absorb'] = 0;
                        }
                        $this->active['actionInfo'][ $this->activeID ]['absorb'] += $abs;
                        $this->other['actionInfo'][ $id ]['damage'][ $this->activeID ] -= $abs;
                    }
                }
            }
        }
    }
    
    //    Reflection effect
    //    REF:turns:PERC|STAT:TNGW:elements:power
    public function REF($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            $elements = explode(".", $modifiers[4]);
            foreach ($this->otherIDs as $id) {
                if (isset($this->other['actionInfo'][ $id ]['damage']) && $this->other['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0 ) {
                    $lElement = $this->other['actionInfo'][ $id ]['element'];
                    if ( 
                         ($this->is_element_good( $elements, $lElement ) || $lElement == "none" ) &&
                         stristr($modifiers[3], $this->other['actionInfo'][ $id ]['type'])
                    ) {
                        if ($modifiers[2] == 'PERC') {
                            $ref = ( $this->other['actionInfo'][ $id ]['damage'][$this->activeID] / 100 ) * $modifiers[5];
                        } elseif ($modifiers[2] == 'STAT') {
                            $ref = $modifiers[5];
                        }
                        
                        // Update damage & absorption
                        if( !isset($this->other['actionInfo'][ $id ]['reflect']) ){
                            $this->other['actionInfo'][ $id ]['reflect'] = 0;
                        }
                        $this->other['actionInfo'][ $id ]['reflect'] += $ref;
                    }
                }
            }
        }
    }

    //    Reflection of damage given
    public function REFL($modifiers) {
        if ($this->type == 1) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
               if( !$this->is_user_preventReflect("active", $this->activeID) ){
                   if( !isset($this->active['actionInfo'][ $this->activeID ]['reflect']) ){
                       $this->active['actionInfo'][ $this->activeID ]['reflect'] = 0;
                   }
                   if ($modifiers[2] == 'PERC') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            $perc = $targetamount / 100;
                            $this->active['actionInfo'][ $this->activeID ]['reflect'] += $perc * $modifiers[3];
                        }
                   } elseif ($modifiers[2] == 'STAT') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            $this->active['actionInfo'][ $this->activeID ]['reflect'] += $modifiers[3];
                        }
                   }
               }
            }
        }
    }
    
    //    Elemental Reflection of damage given
    public function EREFL($modifiers) {
        if ($this->type == 1) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
               if( !$this->is_user_preventReflect("active", $this->activeID) ){
                   $elements = explode(".", $modifiers[3]);
                   if ( $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] ) ) { 
                        if( !isset($this->active['actionInfo'][ $this->activeID ]['reflect']) ){
                            $this->active['actionInfo'][ $this->activeID ]['reflect'] = 0;
                        }
                        if ($modifiers[2] == 'PERC') {
                            foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                                $perc = $targetamount / 100;
                                $this->active['actionInfo'][ $this->activeID ]['reflect'] += $perc * $modifiers[4];
                            }
                        } elseif ($modifiers[2] == 'STAT') {
                            foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                                $this->active['actionInfo'][ $this->activeID ]['reflect'] += $modifiers[4];
                            }
                        }
                   }
               }
            }
        }
    }

    //    Damage done increased
    public function DINC($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
                if (stristr($modifiers[2], $this->active['actionInfo'][ $this->activeID ]['type'])) {
                    if ($modifiers[3] == 'PERC') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            // Do damage calculation
                            $perc = $targetamount / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $perc * $modifiers[4];
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                        }
                    } elseif ($modifiers[3] == 'STAT') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $modifiers[4];
                        }
                    }
                }
            }
        }
    }

    //    Damage done decreased
    public function DDEC($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
                if (stristr($modifiers[2], $this->active['actionInfo'][ $this->activeID ]['type'])) {
                    if ($modifiers[3] == 'PERC') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            // Do damage calculation
                            $perc = $targetamount / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $perc * $modifiers[4];
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                        }
                    } elseif ($modifiers[3] == 'STAT') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $modifiers[4];
                        }
                    }
                }
                if ($this->active['actionInfo'][ $this->activeID ]['damage'] < 0) {
                    $this->active['actionInfo'][ $this->activeID ]['damage'] = 0;
                }
            }
        }
    }

    //    Elemental Damage done increased
    public function EINC($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
                $elements = explode(".", $modifiers[3]);
                if ( $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] ) ) {
                    if ($modifiers[2] == 'PERC') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            // Do damage calculation
                            $perc = $targetamount / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $perc * $modifiers[4];
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                        }
                    } elseif ($modifiers[2] == 'STAT') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $modifiers[4];
                        }
                    }
                }
            }
        }
    }

    //    Elemental Damage done increased
    public function EDEC($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
                $elements = explode(".", $modifiers[3]);
                if ( $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] ) ) {    
                    if ($modifiers[2] == 'PERC') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            // Do calculation
                            $perc = $targetamount / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $perc * $modifiers[4];
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                        }
                    } elseif ($modifiers[2] == 'STAT') {
                        foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $modifiers[4];
                        }
                    }
                }
            }
        }
    }
    
    //    Healing done increased
    public function HEAINC($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['healed'])) {
                if ($modifiers[2] == 'PERC') {
                    
                    // Work on the original heal
                    $this->setOriginalValue( "active", "healed", $this->activeID );
                    
                    // Do calculation                    
                    $perc = $this->active['actionInfo'][ $this->activeID ]['healed'] / 100;
                    $this->active['actionInfo'][ $this->activeID ]['healed'] += $perc * $modifiers[3];
                    
                    // Work on the original heal (effects don't stack and multiply)
                    $this->adjustOriginalValue( "active", "healed", $this->activeID );
                    
                } elseif ($modifiers[2] == 'STAT') {
                    $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[3];
                }
            }
        }        
    }

    //    Healing done decreased
    public function HEADEC($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            if (
                isset($this->active['actionInfo'][ $this->activeID ]['healed']) &&
                $this->active['actionInfo'][ $this->activeID ]['healed'] !== 0 && 
                $this->active['actionInfo'][ $this->activeID ]['healed'] !== ""
            ) {
                if ($modifiers[2] == 'PERC') {
                    
                    // Work on the original heal
                    $this->setOriginalValue( "active", "healed", $this->activeID );
                    
                    // Do calculation
                    $perc = $this->active['actionInfo'][ $this->activeID ]['healed'] / 100;
                    $this->active['actionInfo'][ $this->activeID ]['healed'] -= $perc * $modifiers[3];
                    
                    // Work on the original heal (effects don't stack and multiply)
                    $this->adjustOriginalValue( "active", "healed", $this->activeID );
                    
                } elseif ($modifiers[2] == 'STAT') {
                    $this->active['actionInfo'][ $this->activeID ]['healed'] -= $modifiers[3];
                }
            }
        }        
    }

    //    Elemental damage sustained increased
    public function ESI($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            foreach ($this->otherIDs as $id) {
                if (isset($this->other['actionInfo'][ $id ]['damage'])) {
                    $elements = explode(".", $modifiers[3]);
                    if ( $this->is_element_good( $elements, $this->other['actionInfo'][ $id ]['element'] ) ) {
                        if ($modifiers[2] == 'PERC') {
                            if ($this->other['actionInfo'][ $id ]['damage'][$this->activeID] > 0) {
                                
                                // Work on original damage (effects stack and don't multiply
                                $this->setOriginalDamage( "other", "damage", $id, $this->activeID );
                            
                                // Do calculation
                                $perc = $this->other['actionInfo'][ $id ]['damage'][$this->activeID] / 100;
                                $this->other['actionInfo'][ $id ]['damage'][ $this->activeID ] += $perc * $modifiers[4];
                                
                                // Work on original damage (effects stack and don't multiply
                                $this->adjustOriginalDamage( "other", "damage", $id, $this->activeID );
                            }
                        } elseif ($modifiers[2] == 'STAT') {
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] += $modifiers[4];
                        }
                    }
                }
            }
        }        
    }

    //    Elemental damage sustained decreased
    public function ESD($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            foreach ($this->otherIDs as $id) {
                if (isset($this->other['actionInfo'][ $id ]['damage'])) {
                    $elements = explode(".", strtolower($modifiers[3]));
                    if ( $this->is_element_good( $elements, $this->other['actionInfo'][ $id ]['element'] ) ) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "other", "damage", $id, $this->activeID );
                            
                            // Do calculation
                            $perc = $this->other['actionInfo'][ $id ]['damage'][$this->activeID] / 100;
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] -= $perc * $modifiers[4];
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "other", "damage", $id, $this->activeID );
                            
                        } elseif ($modifiers[2] == 'STAT') {
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] -= $modifiers[4];
                        }
                        if ($this->other['actionInfo'][ $id ]['damage'][$this->activeID] < 0) {
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] = 0;
                        }
                    }
                }
            }
        }
    }

    //    Damage sustained increased
    public function DSI($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            foreach ($this->otherIDs as $id) {
                if (isset($this->other['actionInfo'][ $id ]['damage']) &&
                    isset($this->other['actionInfo'][ $id ]['damage'][$this->activeID]) 
                ) {
                    if ( stristr($modifiers[3], $this->other['actionInfo'][ $id ]['type']) || $modifiers[3] == "ALL" ) {
                        if ($modifiers[2] == 'PERC') {
                            if ( $this->other['actionInfo'][ $id ]['damage'][$this->activeID] > 0) {
                                
                                // Work on original damage (effects stack and don't multiply
                                $this->setOriginalDamage( "other", "damage", $id, $this->activeID );
                            
                                // Do calculation
                                $perc = $this->other['actionInfo'][ $id ]['damage'][$this->activeID] / 100;
                                $this->other['actionInfo'][ $id ]['damage'][$this->activeID] += $perc * $modifiers[4];
                                
                                // Work on original damage (effects stack and don't multiply
                                $this->adjustOriginalDamage( "other", "damage", $id, $this->activeID );
                            }
                        } elseif ($modifiers[2] == 'STAT') {
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] += $modifiers[4];
                        }
                    }
                }
            }
        }
    }

    //    Damage sustained decreased
    public function DSD($modifiers) {
        if ( $this->checkTypeAndPostpone( 1, $modifiers ) ) {
            foreach ($this->otherIDs as $id) {
                if (isset($this->other['actionInfo'][ $id ]['damage']) &&
                    isset($this->other['actionInfo'][ $id ]['damage'][$this->activeID])
                ) {
                    if ( stristr($modifiers[3], $this->other['actionInfo'][ $id ]['type']) || $modifiers[3] == "ALL") {
                        if ($modifiers[2] == 'PERC') {
                            if ( $this->other['actionInfo'][ $id ]['damage'][$this->activeID] > 0) {
                                
                                // Work on original damage (effects stack and don't multiply
                                $this->setOriginalDamage( "other", "damage", $id, $this->activeID );
                            
                                // Do calculation
                                $perc = $this->other['actionInfo'][ $id ]['damage'][$this->activeID] / 100;
                                $this->other['actionInfo'][ $id ]['damage'][$this->activeID] -= $perc * $modifiers[4];
                                
                                // Work on original damage (effects stack and don't multiply
                                $this->adjustOriginalDamage( "other", "damage", $id, $this->activeID );
                            }
                        } elseif ($modifiers[2] == 'STAT') {
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] -= $modifiers[4];
                        }
                        if ( $this->other['actionInfo'][ $id ]['damage'][$this->activeID] < 0) {
                            $this->other['actionInfo'][ $id ]['damage'][$this->activeID] = 0;
                        }
                    }
                }
            }
        }
    }

    //	Regenerate life
    public function REG($modifiers) {
        if ($this->type == 1) {
            if ($modifiers[2] == 'PERC') {
                $perc = $this->active['data'][ $this->activeID ]['max_health'] / 100;
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $perc * $modifiers[3];
            } elseif ($modifiers[2] == 'STAT') {
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[3];
            } elseif ($modifiers[2] == 'TSTA') {
                $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[3] / $modifiers[1];
            }
        }
    }
    
    //  Increase armor
    public function AINC($modifiers) {
        if ( $this->checkTypeAndPostpone( 0, $modifiers ) ) {
            
            // Calculate the increase
            if ($modifiers[2] == 'STAT') {
                $this->active['data'][ $this->activeID ]['armor'] += $modifiers[3];
            } elseif ($modifiers[2] == 'PERC') {
            
                // Work on the original armor
                $this->setOriginalValue( "active", "armor", $this->activeID , "data" );
                
                // Do calculation
                $this->active['data'][ $this->activeID ]['armor'] += ($this->active['data'][ $this->activeID ]['armor'] / 100) * $modifiers[3];
                
                // Work on the original armor (effects don't stack and multiply)
                $this->adjustOriginalValue( "active", "armor", $this->activeID , "data" );
            }
            
            
            // Set the message 
            $this->setLogMessage( "active" , $this->activeID, array(
                "affectedName" => $this->active['data'][ $this->activeID ]['username'],
                "affectedStat" => "armor",
                "effect" => "increased",
                "cssClass" => "logGreen"
            )); 
        }
    }
    
    //  Decrease armor
    public function ADEC($modifiers) {
        if ( $this->checkTypeAndPostpone( 0, $modifiers ) ) {
            
            // Calcualte the decrease
            if ($modifiers[2] == 'STAT') {
                $this->active['data'][ $this->activeID ]['armor'] -= $modifiers[3];
            } elseif ($modifiers[2] == 'PERC') {
                
                // Work on the original armor
                $this->setOriginalValue( "active", "armor", $this->activeID , "data" );
                
                // Do calculation
                $this->active['data'][ $this->activeID ]['armor'] -= ($this->active['data'][ $this->activeID ]['armor'] / 100) * $modifiers[3];
                
                // Work on the original armor (effects don't stack and multiply)
                $this->adjustOriginalValue( "active", "armor", $this->activeID , "data" );
            }
            
            // Make sure it doesn't go below zero
            if( $this->active['data'][ $this->activeID ]['armor'] < 0){
                $this->active['data'][ $this->activeID ]['armor'] = 0;
            }
            
            // Set the message 
            $this->setLogMessage( "active" , $this->activeID, array(
                "affectedName" => $this->active['data'][ $this->activeID ]['username'],
                "affectedStat" => "armor",
                "effect" => "decreased",
                "cssClass" => "logRed"
            ));  
        }
    }

}