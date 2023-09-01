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

class bloodline extends basicFunctions {

    public function bloodline(
            $master,    // Reference to parent class
            $user,      // User side
            $userid,    // User ID
            $opponent,  // Opponent Side
            $type       // Type of bloodline effect (Pre / Post) (0 / 1)
    ) {
        
        // Get multi_battle class
        $this->master = $master;

        // Get activeSide
        $this->activeSide = $user;
        $this->activeID = $userid;
        $this->active = $this->master->{$user};
        $this->activeIDs = $this->master->{$user}['ids'];
        
        // Get targetSide
        $this->targetSide = $opponent;
        $this->target = $this->master->{$opponent};
        $this->targetIDs = $this->master->{$opponent}['ids'];
        
        // Set the bloodline type
        $this->type = $type;
        
        // Array to contain temporary targets
        // This means targets from the users own side who
        // decided to attack the user
        $this->temporary_opps = array();
    }

    /*
     * 	Return data to the calling class.
     */

    public function return_data() {
        
        // First remove the temporary opponents
        if (isset($this->temporary_opps)) {
            foreach ($this->temporary_opps as $id) {
                if ($id != $this->activeID) {
                    $this->active['data'][ $id ] = $this->target['data'][ $id ];
                }
                unset($this->target['data'][ $id ]);
            }
        }

        // Then update battle data
        $this->master->set_battledata($this->activeSide, $this->active );
        $this->master->set_battledata($this->targetSide, $this->target);
    }
    
    /* Check type
     * Used to check and potentially change the type of a bloodline tag 
     * if that tag type is H. Changed to specialization
     */
    private function checkAddSpecialization( $tagElement ){
        if( $tagElement == "H" ){
            $specialData = $this->get_user_specialization("active", $this->activeID);
            if( $specialData !== false ){
                // Return specialization
                return $specialData['ttype'];
            }
            else{
                // Make useless, user doesn't have specialization
                return "X";
            }
        }
        else{
            // Return original
            return $tagElement;
        }
    }

    /*
     * 	Bloodline Effect codes
     * 	Code for each individual bloodline effect follows below
     * 	Function names are identical to the bloodline effect TAG identifier
     * 	As precribed in the database.
     */

    public function strongest_opp( $kind ) {
        $finalid = false;
        if ($kind == "offense") {
            $total = 0;
            foreach ($this->targetIDs as $id) {
                if ($total < $this->target['data'][ $id ]['tai_off']) {
                    $total = $this->target['data'][ $id ]['tai_off'];
                    $finalid = $id;
                }
                if ($total < $this->target['data'][ $id ]['nin_off']) {
                    $total = $this->target['data'][ $id ]['nin_off'];
                    $finalid = $id;
                }
                if ($total < $this->target['data'][ $id ]['gen_off']) {
                    $total = $this->target['data'][ $id ]['gen_off'];
                    $finalid = $id;
                }
                if ($total < $this->target['data'][ $id ]['weap_off']) {
                    $total = $this->target['data'][ $id ]['weap_off'];
                    $finalid = $id;
                }
            }
        } elseif ($kind == "bloodline") {
            $total = 0;
            foreach ($this->targetIDs as $id) {
                $count = count($this->target['data'][ $id ]['bloodlineEffect']);
                if ($total < $count) {
                    $total = $count;
                    $finalid = $id;
                }
            }
        } elseif ($kind == "damage") {
            $finalid = array();
            
            // Get all the users on the other side who hit this user
            foreach ($this->targetIDs as $id) {
                if ( isset($this->target['actionInfo'][ $id ]['damage'][ $this->activeID ]) &&
                     $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0
                ) {
                    $finalid[] = $id;
                }
            }
            
            // Loop through all the users on this side. See if anyone attacked own side.
            foreach ($this->activeIDs as $id) {
                if (
                        isset($this->active['actionInfo'][ $id ]['damage'][ $this->activeID ]) &&
                        $this->active['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0
                ) {
                    if (!in_array($id, $this->temporary_opps)) {
                        $finalid[] = $id;
                        // Set temporary opponent
                        $this->target['data'][ $id ] = $this->active['data'][ $id ];
                        $this->temporary_opps[] = $id;
                    }
                }
            }
        } else {
            $finalid = $this->targetIDs[0];
        }
        return $finalid;
    }


    /*
     *  AI trait only
     *  SCOPY:(add|copy):(int1):(int2):(int3):(int4)
     * 
     *  Add: added to existing stats
     *  Copy: overwrites existing stats
     *  Int1: Percentage of stats taken
     *  Int2: if set to 1 copies HP
     *  Int3: if set to 1 copies TNGW offense and defense
     *  Int4: if set to 1 copies generals
     */
    public function SCOPY($modifiers) {
        
        // We only want to run this tag once for the user in question
        if ( !isset($this->active['data'][ $this->activeID ]['scopyIni'])) {
            $this->active['data'][ $this->activeID ]['scopyIni'] = 1;

            // Convenience arrays with the different things we copy
            $statArray = array( "tai_off", "nin_off", "gen_off", "weap_off", "tai_def", "nin_def", "gen_def", "weap_def" );
            $generalArray = array( "strength", "intelligence", "willpower", "speed" );

            // Determine Strongest Opponent
            $finalid = $this->strongest_opp('offense');
            
            if( $finalid ){
                
                // Copy HP
                if ($modifiers[3] == "1") {
                    $this->active['data'][ $this->activeID ]['cur_health'] += (($this->target['data'][ $finalid ]['max_health'] / 100) * $modifiers[2]);
                    $this->active['data'][ $this->activeID ]['max_health'] += (($this->target['data'][ $finalid ]['max_health'] / 100) * $modifiers[2]);
                }

                // Set stats
                if ($modifiers[4] == "1") {
                    
                    // If set to copy, reset initial stats
                    if ($modifiers[1] == 'copy') {
                        foreach( $statArray as $key ){
                            $this->active['data'][ $this->activeID ][ $key ] = 0;
                        }
                    }
                    
                    // Copy stats
                    foreach( $statArray as $key ){
                        $this->active['data'][ $this->activeID ][ $key ] += (($this->target['data'][ $finalid ][ $key ] / 100) * $modifiers[2]);
                        $this->active['backup'][ $this->activeID ][ $key ] = $this->active['data'][ $this->activeID ][ $key ];
                    }
                }

                // Generals
                if ($modifiers[5] == "1") {

                    // If set to copy, reset initial general
                    if ($modifiers[1] == 'copy') {
                        foreach( $generalArray as $key ){
                            $this->active['data'][ $this->activeID ][ $key ] = 0;
                        }
                    }

                    // Copy the stats
                    foreach( $generalArray as $key ){
                        $this->active['data'][ $this->activeID ][ $key ] += (($this->target['data'][ $finalid ][ $key ] / 100) * $modifiers[2]);
                        $this->active['backup'][ $this->activeID ][ $key ] = $this->active['data'][ $this->activeID ][ $key ];
                    }
                }
                
                // Set the log message
                $this->setLogTextMessage("active", $this->activeID, "logBlue", "<i>".$this->active['data'][ $this->activeID ]['username']."</i> absorbs knowledge from <i>".$this->target['data'][ $finalid ][ "username" ]."</i>");
            }
        }
    }

    // Copy the opponent's bloodline effects! 
    // Usage: BCOPY
    public function BCOPY($modifiers) {
        
        // We only want to run this tag once for the user in question
        if ( $this->type == 1 && !isset($this->active['data'][ $this->activeID ]['bcopyIni'])) {
            $this->active['data'][ $this->activeID ]['bcopyIni'] = 1;
            
            // Get the strongest opponent
            $finalid = $this->strongest_opp('bloodline');
            if( $finalid ){
            
                // Copy all bloodline effects
                $i = 0;
                while ($i < count($this->target['data'][ $finalid ]['bloodlineEffect'])) {
                    
                    // Copy all except a select few
                    if( !preg_match("/(SCOPY)/", $this->active['data'][ $this->activeID ]['bloodlineEffect'][$i][0]) ){
                        $count = count($this->active['data'][ $this->activeID ]['bloodlineEffect']);
                        $this->active['data'][ $this->activeID ]['bloodlineEffect'][ $count ] = $this->target['data'][ $finalid ]['bloodlineEffect'][$i];
                    }
                    $i++;
                }
                
                // Show log message
                if( $i > 0 ){
                    // Set the log message
                    $this->setLogTextMessage("active", $this->activeID, "logBlue", "<i>".$this->active['data'][ $this->activeID ]['username']."</i> imitates the bloodline effects of <i>".$this->target['data'][ $finalid ][ "username" ]."</i>");
                }
            }
        }
    }
    

    /*
     * 	Following below are all post-battle bloodline effects
     */

    //	Damage sustained decreased
    //	DSDEC:TNGW:(PERC|STAT):powerInt
    public function DSDEC($modifiers) {

        if ($this->type == 1) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            $finalids = $this->strongest_opp('damage');
            foreach ($finalids as $id) {
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0) {
                    if (stristr($modifiers[1], $this->target['actionInfo'][ $id ]['type']) !== false) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "target", "damage", $id, $this->activeID );
                            
                            //    Percentual decrease
                            $perc = $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] / 100;
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] -= $modifiers[3] * $perc;
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = round($this->target['actionInfo'][ $id ]['damage'][ $this->activeID ]);
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "target", "damage", $id, $this->activeID );
                            
                        } else {
                            //    Static decrease
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] -= $modifiers[3];
                            
                        }
                        
                        // No damage below 0
                        if ($this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] < 0) {
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = 0;
                        }
                    }
                }
            }
        }
    }

    //  Damage sustained increased
    //	DSINC:TNGW:(PERC|STAT):powerInt
    public function DSINC($modifiers) {
        if ($this->type == 1) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            $finalids = $this->strongest_opp('damage');
            foreach ($finalids as $id) {
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0) {
                    if (stristr($modifiers[1], $this->target['actionInfo'][ $id ]['type']) !== false) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "target", "damage", $id, $this->activeID );
                            
                            //    Percentual increase
                            $perc = $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] / 100;
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] += $modifiers[3] * $perc;
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = round($this->target['actionInfo'][ $id ]['damage'][ $this->activeID ]);
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "target", "damage", $id, $this->activeID );
                            
                            // No damage below 0
                            if ($this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] < 0) {
                                $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = 0;
                            }
                            
                        } else {
                            
                            //    Static increase
                            $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] += $modifiers[3];
                            
                        }
                    }
                }
            }
        }
    }

    //	Damage done decreased
    //	DDEC:TNGW:(PERC|STAT):powerInt
    public function DDEC($modifiers) {
        if ($this->type == 1) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
                foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                    if (stristr($modifiers[1], $this->active['actionInfo'][ $this->activeID ]['type']) !== false) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            //	Percentual decrease
                            $perc = $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $modifiers[3] * $perc;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] = round($this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . '']);
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            
                        } else {
                            //	Static decrease
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $modifiers[3];
                        }
                        
                        // No damage below 0
                        if ($this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] < 0) {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] = 0;
                        }
                    }
                }
            }
        }
    }

    //	Damage done increased
    //	DINC:TNGW:(PERC|STAT):powerInt
    public function DINC($modifiers) {
        if ($this->type == 1) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage'])) {
                foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                    if (stristr($modifiers[1], $this->active['actionInfo'][ $this->activeID ]['type']) !== false) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            //	Percentual decrease
                            $perc = $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $modifiers[3] * $perc;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] = round($this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . '']);
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                        } else {
                            //	Static increase
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $modifiers[3];
                        }
                    }
                }
            }
        }
    }

    //	Reflect damage
    //  REFL:TNGW:(PERC|STAT):powerInt
    public function REFL($modifiers) {
        if ($this->type == 1) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            $finalids = $this->strongest_opp('damage');
            foreach ($finalids as $id) {
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0) {
                    if (stristr($modifiers[1], $this->target['actionInfo'][ $id ]['type']) !== false) {
                        if( !isset($this->target['actionInfo'][ $id ]['reflect']) ){
                            $this->target['actionInfo'][ $id ]['reflect'] = 0;
                        }
                        if ($modifiers[2] == 'PERC') {
                            $perc = $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] / 100;
                            if (round($modifiers[3] * $perc) > 0) {
                                $this->target['actionInfo'][ $id ]['reflect'] += round($modifiers[3] * $perc);
                            }
                        } else {
                            $this->target['actionInfo'][ $id ]['reflect'] += $modifiers[3];
                        }
                    }
                }
            }
        }
    }

    //	Elemental damage done increased
    //  EDINC:fire.water:(PERC|STAT):powerInt
    public function EDINC($modifiers) {
        if ($this->type == 1) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage']) && $this->active['actionInfo'][ $this->activeID ]['element'] != '') {
                $elements = explode(".", $modifiers[1]);
                if ( $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] ) ) {
                    foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            // Do damage calc
                            $perc = $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $modifiers[3] * $perc;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] = round($this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . '']);
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                        } else {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] += $modifiers[3];
                        }
                    }
                }
            }
        }
    }

    //	Elemental damage done decreased
    //  EDDEC:fire.water:(PERC|STAT):powerInt
    public function EDDEC($modifiers) {
        if ($this->type == 1) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['damage']) && $this->active['actionInfo'][ $this->activeID ]['element'] != '') {
                $elements = explode(".", $modifiers[1]);
                if ( $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] ) ) {
                    foreach ($this->active['actionInfo'][ $this->activeID ]['damage'] as $targerid => $targetamount) {
                        if ($modifiers[2] == 'PERC') {
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->setOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                            // Do damage calc
                            $perc = $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] / 100;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $modifiers[3] * $perc;
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] = round($this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . '']);
                            
                            // Work on original damage (effects stack and don't multiply
                            $this->adjustOriginalDamage( "active", "damage", $this->activeID, $targerid );
                            
                        } else {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] -= $modifiers[3];                            
                        }
                        
                        // No damage below 0
                        if ($this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] < 0) {
                            $this->active['actionInfo'][ $this->activeID ]['damage']['' . $targerid . ''] = 0;
                        }
                    }
                }
            }
        }
    }
    
    //	Healing done increased
    //  HEAINC:PERC|STAT:basePowerInt
    public function HEAINC($modifiers) {
        if ($this->type == 1) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['healed'])) {
                if ($modifiers[1] == 'PERC') {
                    
                    // Work on the original heal
                    $this->setOriginalValue( "active", "healed", $this->activeID );
                    
                    // Calculate heal
                    $perc = $this->active['actionInfo'][ $this->activeID ]['healed'] / 100;
                    $this->active['actionInfo'][ $this->activeID ]['healed'] += $perc * $modifiers[2];
                    
                    // Work on the original heal (effects don't stack and multiply)
                    $this->adjustOriginalValue( "active", "healed", $this->activeID );
                    
                } elseif ($modifiers[1] == 'STAT') {
                    $this->active['actionInfo'][ $this->activeID ]['healed'] += $modifiers[2];
                }
            }
        }
    }
    
    //	Healing done increased
    //  HEADEC:PERC|STAT:basePowerInt
    public function HEADEC($modifiers) {
        if ($this->type == 1) {
            if (isset($this->active['actionInfo'][ $this->activeID ]['healed'])) {
                if ($modifiers[1] == 'PERC') {
                    
                    // Work on the original heal
                    $this->setOriginalValue( "active", "healed", $this->activeID );
                    
                    // Calculate heal
                    $perc = $this->active['actionInfo'][ $this->activeID ]['healed'] / 100;
                    $this->active['actionInfo'][ $this->activeID ]['healed'] -= $perc * $modifiers[2];
                    
                    // Work on the original heal (effects don't stack and multiply)
                    $this->adjustOriginalValue( "active", "healed", $this->activeID );
                    
                } elseif ($modifiers[1] == 'STAT') {
                    $this->active['actionInfo'][ $this->activeID ]['healed'] -= $modifiers[2];
                }
                
                // No heal below 0
                if( $this->active['actionInfo'][ $this->activeID ]['healed'] < 0 ){
                    $this->active['actionInfo'][ $this->activeID ]['healed'] = 0;
                }
            }
        }
    }
    
    // A convenience function used in ESDEC, ESINC, EABS
    private function testConvenience( $modifiers , $curValue, $addSubtract, $overwrite=false){
        $multiplier = ($addSubtract == true) ? 1 : -1;
        if ($modifiers[2] == 'PERC') {
            $perc = $curValue / 100;
            switch($overwrite){
                case true: $curValue = $modifiers[3] * $perc * $multiplier; break;
                case false: $curValue += $modifiers[3] * $perc * $multiplier; break;
            }
        } else {
            switch($overwrite){
                case true: $curValue = $modifiers[3] * $multiplier; break;
                case false: $curValue += $modifiers[3] * $multiplier; break;
            }
        }
        $curValue = round($curValue);
        if ($curValue < 0) {
            $curValue = 0;
        }
        return $curValue;
    }
    
    //	Elemental damage sustained decreased
    //  ESDEC:fire.water:(PERC|STAT):powerInt
    public function ESDEC($modifiers) {
        
        if ($this->type == 1) {
            
            // Get the elements
            $elements = explode(".", $modifiers[1]);
            
            // Go through all opponents
            $finalids = $this->strongest_opp('damage');
            foreach ($finalids as $id) {
                
                // Regular damage
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0 && $this->is_element_good( $elements, $this->target['actionInfo'][ $id ]['element'] ) ) {
                    
                    // Work on original damage (effects stack and don't multiply
                    $this->setOriginalDamage( "target", "damage", $id, $this->activeID );
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = $this->testConvenience($modifiers,$this->target['actionInfo'][ $id ]['damage'][ $this->activeID ],false);
                    $this->adjustOriginalDamage( "target", "damage", $id, $this->activeID );
                }
            }

            //	Poison damage
            if (isset($this->active['actionInfo'][ $this->activeID ]['poison']['damage']) && $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] > 0 && $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['poison']['element'] ) ) {
                $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] = $this->testConvenience($modifiers,$this->active['actionInfo'][ $this->activeID ]['poison']['damage'],false);
            }

            //  Recoil damage
            if ( isset($this->active['actionInfo'][ $this->activeID ]['recoil']) && $this->active['actionInfo'][ $this->activeID ]['recoil'] > 0 && isset($this->active['actionInfo'][ $this->activeID ]['element']) && $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] )) {
                $this->active['actionInfo'][ $this->activeID ]['recoil'] = $this->testConvenience($modifiers,$this->active['actionInfo'][ $this->activeID ]['recoil'],false);
            }
        }
    }

    //	Elemental damage sustained increased
    //  ESINC:fire.water:(PERC|STAT):powerInt
    public function ESINC($modifiers) {
        if ($this->type == 1) {
            
            // Get the elements
            $elements = explode(".", $modifiers[1]);
                
            $finalids = $this->strongest_opp('damage');
            foreach ($finalids as $id) {
                
                // Regular damage
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0 && $this->is_element_good( $elements, $this->target['actionInfo'][ $id ]['element'] ) ) {
                    
                    // Work on original damage (effects stack and don't multiply
                    $this->setOriginalDamage( "target", "damage", $id, $this->activeID );
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = $this->testConvenience($modifiers,$this->target['actionInfo'][ $id ]['damage'][ $this->activeID ],true);
                    $this->adjustOriginalDamage( "target", "damage", $id, $this->activeID );
                }
            }
            
            //	Poison damage
            if (isset($this->active['actionInfo'][ $this->activeID ]['poison']['damage']) && $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] > 0 && $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['poison']['element'] ) ) {
                $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] = $this->testConvenience($modifiers,$this->active['actionInfo'][ $this->activeID ]['poison']['damage'],true);
            }

            //  Recoil damage
            if (isset($this->active['actionInfo'][ $this->activeID ]['recoil']) && $this->active['actionInfo'][ $this->activeID ]['recoil'] > 0 && $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] )) {
                $this->active['actionInfo'][ $this->activeID ]['recoil'] = $this->testConvenience($modifiers,$this->active['actionInfo'][ $this->activeID ]['recoil'],true);
            }
        }
    }

    //	Elemental damage absorbed
    //  ESABS:fire.water:(PERC|STAT):powerInt
    public function ESABS($modifiers) {
        if ($this->type == 1) {
            
            // Get the elements
            $elements = explode(".", $modifiers[1]);
            
            // Get the opponent ids
            $finalids = $this->strongest_opp('damage');
            
            // Set it if unset
            if( !isset($this->active['actionInfo'][ $this->activeID ]['absorb']) ){
                $this->active['actionInfo'][ $this->activeID ]['absorb'] = 0;
            }
            
            //	 Regular damage
            foreach ($finalids as $id) {
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0 && $this->is_element_good( $elements, $this->target['actionInfo'][ $id ]['element'] ) ) {
                    $this->active['actionInfo'][ $this->activeID ]['absorb'] += $this->testConvenience($modifiers,$this->target['actionInfo'][ $id ]['damage'][ $this->activeID ],true, true);
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = 0;
                }
            }
            
            //	Poison damage
            if (isset($this->active['actionInfo'][ $this->activeID ]['poison']['damage']) && $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] > 0 && $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['poison']['element'] ) ) {
                $this->active['actionInfo'][ $this->activeID ]['absorb'] += $this->testConvenience($modifiers,$this->active['actionInfo'][ $this->activeID ]['poison']['damage'],true, true);
                $this->active['actionInfo'][ $this->activeID ]['poison']['damage'] = 0;
            }

            //  Recoil damage
            if (isset($this->active['actionInfo'][ $this->activeID ]['recoil']) && $this->active['actionInfo'][ $this->activeID ]['recoil'] > 0 && $this->is_element_good( $elements, $this->active['actionInfo'][ $this->activeID ]['element'] )) {
                $this->active['actionInfo'][ $this->activeID ]['absorb'] += $this->testConvenience($modifiers,$this->active['actionInfo'][ $this->activeID ]['recoil'],true, true);
                $this->active['actionInfo'][ $this->activeID ]['recoil'] = 0;
            }
        }
    }

    //	Reflect elemental damage
    //  EREFL:fire.water:(PERC|STAT):powerInt
    public function EREFL($modifiers) {
        if ($this->type == 1) {
            $elements = explode(".", $modifiers[1]);
            $finalids = $this->strongest_opp('damage');
            foreach ($finalids as $id) {
                if( !isset($this->target['actionInfo'][ $id ]['reflect']) ){
                    $this->target['actionInfo'][ $id ]['reflect'] = 0;
                }
                if (isset($this->target['actionInfo'][ $id ]['damage']) && $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0) {
                    if( $this->is_element_good( $elements, $this->target['actionInfo'][ $id ]['element'] )){
                        if ($modifiers[2] == 'PERC') {
                            $perc = $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] / 100;
                            if (round($modifiers[3] * $perc) > 0) {
                                $this->target['actionInfo'][ $id ]['reflect'] += round($modifiers[3] * $perc);
                            }
                        } else {
                            $this->target['actionInfo'][ $id ]['reflect'] += $modifiers[3];
                        }
                    }
                }
            }
        }
    }

    //	It's a bird, it's a plane, it's admin hax! Run both post and pre damage
    //  INVINCIBLE:noDamage:noResidual:noRecoil:noKO:noReflect:noSeal:noStun
    public function INVINCIBLE($modifiers) {
        if ($this->type == 0 || $this->type == 1) {
            if ($modifiers[1] == 1) {
                // Prevent damage
                $finalids = $this->strongest_opp('damage');
                foreach ($finalids as $id) {
                    if (isset($this->target['actionInfo'][ $id ]['damage'][ $this->activeID ]) &&
                        $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] > 0) {
                        $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = 0;
                    }
                }
            }
            if ($modifiers[2] == 1) {
                // Prevent Poison
                $this->active['actionInfo'][ $this->activeID ]['prevent_poison'] = true;
            }
            if ($modifiers[3] == 1) {
                // Prevent recoil
                $this->active['actionInfo'][ $this->activeID ]['prevent_recoil'] = true;
            }
            if ($modifiers[4] == 1) {
                // Prevent KO
                $this->active['actionInfo'][ $this->activeID ]['prevent_ko'] = true;
            }
            if ($modifiers[5] == 1) {
                // Prevent reflected damage
                $this->active['actionInfo'][ $this->activeID ]['prevent_reflect'] = true;
            }
            if ($modifiers[6] == 1) {
                // Stop Seal
                $this->active['actionInfo'][ $this->activeID ]['prevent_seal'] = true;
            }
            if ($modifiers[7] == 1) {
                // Stop Seal
                $this->active['actionInfo'][ $this->activeID ]['stun_resist'] = true;
            }
        }
    }

    //	Weakness against specific attacks
    //  WEAK: (JUT | ITM | WPN | ELM) : (id | element-list | ALL) : (PERC|DAM|STAT) : (int)
    public function WEAK($modifiers) {

        // Get all opponents who hit the user
        $finalids = $this->strongest_opp('damage');
        foreach ($finalids as $id) {
            
            // Do the effect
            if(  
                ( $modifiers[1] == "JUT" &&
                  $this->target['data'][ $id ]['tag'] == 'JUT:' . $modifiers[2] ||
                  (stristr($this->target['data'][ $id ]['tag'], 'JUT:') && $modifiers[2] == "ALL")
                ) ||
                ( $modifiers[1] == "ITM" &&
                  $this->target['data'][ $id ]['tag'] == 'ITM:' . $modifiers[2] ||
                  (stristr($this->target['data'][ $id ]['tag'], 'ITM:') && $modifiers[2] == "ALL")
                ) ||
                ( $modifiers[1] == "WPN" &&
                  $this->target['data'][ $id ]['tag'] == 'WPN:' . $modifiers[2] ||
                  (stristr($this->target['data'][ $id ]['tag'], 'WPN:') && $modifiers[2] == "ALL")
                ) ||
                ( $modifiers[1] == "ELM" &&
                  $this->is_element_good( explode(".", $modifiers[2]) , $this->target['actionInfo'][ $id ]['element']) 
                ) ||
                ( $modifiers[1] == "STTAI" &&
                  $this->target['data'][ $id ]['tag'] == 'STTAI'
                ) ||
                ( $modifiers[1] == "STCHA" &&
                  $this->target['data'][ $id ]['tag'] == 'STCHA'
                )
            ){
                if ($modifiers[3] == 'PERC') {
                    $perc = $this->target['data'][ $id ]['max_health'];
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = $perc * $modifiers[4];
                } elseif ($modifiers[3] == 'DAM') {
                    $perc = $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] / 100;
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = $perc * $modifiers[4];
                } elseif ($modifiers[3] == 'STAT') {
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = $modifiers[4];
                }
            }
        }
    }
    
    //	Strength against specific attacks
    //  STRONG: (JUT | ITM | WPN | ELM) : (id | element-list | ALL) : (PERC|DAM|STAT) : (int)
    public function STRONG($modifiers) {

        // Get all opponents who hit the user
        $finalids = $this->strongest_opp('damage');
        foreach ($finalids as $id) {
            
            // Do the effect
            if(  
                ( $modifiers[1] == "JUT" &&
                  $this->target['data'][ $id ]['tag'] == 'JUT:' . $modifiers[2] ||
                  (stristr($this->target['data'][ $id ]['tag'], 'JUT:') && $modifiers[2] == "ALL")
                ) ||
                ( $modifiers[1] == "ITM" &&
                  $this->target['data'][ $id ]['tag'] == 'ITM:' . $modifiers[2] ||
                  (stristr($this->target['data'][ $id ]['tag'], 'ITM:') && $modifiers[2] == "ALL")
                ) ||
                ( $modifiers[1] == "WPN" &&
                  $this->target['data'][ $id ]['tag'] == 'WPN:' . $modifiers[2] ||
                  (stristr($this->target['data'][ $id ]['tag'], 'WPN:') && $modifiers[2] == "ALL")
                ) ||
                ( $modifiers[1] == "ELM" &&
                  $this->is_element_good( explode(".", $modifiers[2]) , $this->target['actionInfo'][ $id ]['element']) 
                ) ||
                ( $modifiers[1] == "STTAI" &&
                  $this->target['data'][ $id ]['tag'] == 'STTAI'
                ) ||
                ( $modifiers[1] == "STCHA" &&
                  $this->target['data'][ $id ]['tag'] == 'STCHA'
                )
            ){
                if ($modifiers[3] == 'DAM') {
                    $perc = $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] / 100;
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] -= $perc * $modifiers[4];
                } elseif ($modifiers[3] == 'STAT') {
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] -= $modifiers[4];
                }
                if( $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] < 0 ){
                    $this->target['actionInfo'][ $id ]['damage'][ $this->activeID ] = 0;
                }
            }
        }
    }

    /*
     * 	Following below are all special bloodline effects which are executed at
     * 	special places in the code, rather than at parse_pre or parse_post calculation
     */

    //  Stamina cost up
    //  STAU:TNGW:(PERC|STAT):powerInt
    public function STAU($modifiers, $sta_cost = 0) {
        if ($this->type == 3) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            if (stristr($modifiers[1], $this->active['actionInfo'][ $this->activeID ]['type']) !== false) {
                if ($modifiers[2] == 'PERC') {
                    $perc = $sta_cost / 100;
                    $sta_cost = round($sta_cost + ($perc * $modifiers[3]));
                } else {
                    $sta_cost += $modifiers[3];
                }
            }
        }
        return $sta_cost;
    }

    //  Stamina cost down
    //  STAD:TNGW:(PERC|STAT):powerInt
    public function STAD($modifiers, $sta_cost = 0) {
        if ($this->type == 3) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            if (stristr($modifiers[1], $this->active['actionInfo'][ $this->activeID ]['type']) !== false) {
                if ($modifiers[2] == 'PERC') {
                    $perc = $sta_cost / 100;
                    $sta_cost = round($sta_cost - ($perc * $modifiers[3]));
                    if ($sta_cost < 0) {
                        $sta_cost = 0;
                    }
                } else {
                    $sta_cost -= $modifiers[3];
                    if ($sta_cost < 0) {
                        $sta_cost = 0;
                    }
                }
            }
        }
        return $sta_cost;
    }

    //  Chakra cost up
    //  CHAU:TNGW:(PERC|STAT):powerInt
    public function CHAU($modifiers, $cha_cost = 0) {
        if ($this->type == 3) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            if (stristr($modifiers[1], $this->active['actionInfo'][ $this->activeID ]['type']) !== false) {
                if ($modifiers[2] == 'PERC') {
                    $perc = $cha_cost / 100;
                    $cha_cost = round($cha_cost + ($perc * $modifiers[3]));
                } else {
                    $cha_cost += $modifiers[3];
                }
            }
        }
        return $cha_cost;
    }

    //  Chakra cost down
    //  CHAD:TNGW:(PERC|STAT):powerInt
    public function CHAD($modifiers, $cha_cost = 0) {
        if ($this->type == 3) {
            $modifiers[1] = $this->checkAddSpecialization( $modifiers[1] );
            if (stristr($modifiers[1], $this->active['actionInfo'][ $this->activeID ]['type']) !== false) {
                if ($modifiers[2] == 'PERC') {
                    $perc = $cha_cost / 100;
                    $cha_cost = round($cha_cost - ($perc * $modifiers[3]));
                    if ($cha_cost < 0) {
                        $cha_cost = 0;
                    }
                } else {
                    $cha_cost -= $modifiers[3];
                    if ($cha_cost < 0) {
                        $cha_cost = 0;
                    }
                }
            }
        }
        return $cha_cost;
    }

    // Non-battle effects
    
    // repel:chance
    public function repel($modifiers) {
        
    }
}