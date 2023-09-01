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

class warLib {
    
    // Constructor
    public function __construct(){
        
        // Used for saving log notes
        $this->logNotes = "";
        
        // Array of user IDs who should not get global "healed" messages
        $this->exemptMessageUsers = array();
    }
    
    // Function for getting the part of the query that exepts certain users for a message
    private function getDontMessageQuery(){
        if( empty($this->exemptMessageUsers) ){
            return "";
        }
        else{
            return " AND `users`.`id` NOT IN ('".implode("','",$this->exemptMessageUsers)."')";
        }
    }
    
    /* ===================================
     * Reduce Structure Points Function
     * This is the one used e.g. in battles to update war data
     * Reduce structure points, and possibly conclude the war
     * =================================== 
     */
    public function structures_reducePoints(
        $lose_village,      // the losing village
        $alliances,         // The alliance of the loser village
        $win_village,       // The winning village
        $type,              // Defensive/neutral/offensive
        $forceCost = null,  // Force a specific SP cost 
        $forceHeal = null   // Force a specific SP heal
    ) {
        
        $GLOBALS['database']->execute_query('SELECT `villages`.`name` FROM `village_structures` 
            INNER JOIN `villages` ON (`villages`.`name` = `village_structures`.`name`) FOR UPDATE');
        
        // Check for village structures
        if(!($this->structures = $GLOBALS['database']->fetch_data("SELECT `village_structures`.* FROM `village_structures` 
            WHERE `village_structures`.`name` = '".$lose_village."' LIMIT 1"))) {
            throw new Exception('There was an error locking village structures!');
        }
        
        if ($this->structures === "0 rows") {
            throw new Exception('There was an error retrieving village structure!');
        }
        
        // Get cost/heal data
        list($cost, $heal) = $this->structures_getHealCost( $type , $forceCost, $forceHeal );
           
        // If structures are still standing, kill some of them. If not, battle has been concluded
        if ( $this->structures[0]['cur_structurePoints'] - $cost > 0) {

            // Upload reduction
            if ($cost > 0) {
                $GLOBALS['database']->execute_query("UPDATE `village_structures` 
                    SET `village_structures`.`cur_structurePoints` = `village_structures`.`cur_structurePoints` - ".$cost .", 
                        `village_structures`.`".$win_village."Perc` = `village_structures`.`".$win_village."Perc` + 1 
                    WHERE `village_structures`.`name` = '".$lose_village."' LIMIT 1");
            }

            // Upload recreation of structure points
            if ($heal > 0) {
                
                // Update the points
                $GLOBALS['database']->execute_query("UPDATE `village_structures` 
                    SET `village_structures`.`cur_structurePoints` = `village_structures`.`cur_structurePoints` + ".$heal." 
                    WHERE 
                        `village_structures`.`name` = '".$win_village."' AND
                        `village_structures`.`cur_structurePoints` + ".$heal." < `village_structures`.`start_structurePoints`
                    LIMIT 1");
                
                // Enforce max points
                $GLOBALS['database']->execute_query("
                    UPDATE `village_structures` 
                    SET `cur_structurePoints` = `start_structurePoints` 
                    WHERE `cur_structurePoints` > `start_structurePoints`");
            }
        } 
        else {

            // Set all alliances if unset
            $this->setAlliances();

            // Determine all the villages with which the village is in war, and who therefore won over the village
            $enemy_count = count($enemies = $this->getVillagesWithStatus($lose_village, 2));
            
            // Get current territories owned by loser village
            $currentTerritories = $this->getCurrentTerritories( $lose_village );
            $this->transferTerritories("Syndicate", $lose_village, 20 );

            // Win the wars
            foreach($enemies as $winVillage) { 
                $this->win_a_war($winVillage, $lose_village, $enemy_count, true, true, $this->logNotes); 
            }

            // Tell the losers.. haha
            $GLOBALS['database']->execute_query("
                UPDATE `users`, `users_loyalty`
                SET 
                    `users`.`healed` = '".functions::store_content($lose_village." has lost the war.")."' 
                WHERE 
                    `users_loyalty`.`village` = '".$lose_village."' AND 
                    `users`.`id` = `users_loyalty`.`uid` 
                    ".$this->getDontMessageQuery()." ");
            
            // Update the structures of all villages
            $this->set_db_structures();

        }
    }
    
    /* ===================================
     * Get functions / Convenience Functions
     * Gets various information / data
     * =================================== 
     */
    
    // Used for definint cost/heals in regards to structure points
    public function structures_getHealCost( $type , $forceCost = null, $forceHeal = null ) {
        
        // Variables to be set
        $cost = 0;
        $heal = 0;
        
        // First base on type
        switch($type) {
            case "neutral": $cost = 0; $heal = 0; break;
            case "sabotage": $cost = 1; $heal = 0; break;
            default: $cost = 3; $heal = 0; break;
        }
        
        // Set foce values
        $cost = ($forceCost !== null) ? $forceCost : $cost;
        $heal = ($forceHeal !== null) ? $forceHeal : $heal;
        
        // Return the values
        return array($cost, $heal);
    }
    
    // Check if user is in war
    public function inWar( $allianceData ){
        $inWar = 0;
        foreach( $allianceData as $key => $value ){
            if( ctype_digit($value) && $value == 2 && in_array($key, array('Konoki','Samui','Silence','Shroud','Shine') ) ){
                $inWar += 1;
            }
        }
        if( $inWar > 0 ){
            return $inWar;
        }
        return false;
    }
    
    // Check if village has structures set
    public function hasStructures( $villageData ){
        if( $villageData['cur_structurePoints'] > 0 ){
            return true;
        }
        else{
            return false;
        }
    }
    
    // Get the village destruction in percentages between other villages
    public function getDestructionPerc( $villageVars ){
        
        // Find total destruction, loop over villages
        $total_destruction = 1;
        foreach( Data::$VILLAGES as $village ){
            $total_destruction += $villageVars[ $village."Perc" ];
        }
        
        // Calculate percentages
        $destructionPercs = array();
        foreach( Data::$VILLAGES as $village ){
            $destructionPercs[ $village ] = round( $villageVars[ $village."Perc" ] * 100 / $total_destruction );
        }
        
        // Should add up to 100, calculate excess
        $excess = 100;
        foreach( Data::$VILLAGES as $village ){
            $excess -= $destructionPercs[ $village ];
        }
        
        // If there's an excess, then add it to the first village
        if( $excess > 0 ){
            foreach( Data::$VILLAGES as $village ){
                if( $destructionPercs[ $village ] > 0 ){
                    $destructionPercs[ $village ] += $excess;
                    break;
                }
            }
        }
        
        // Return final destruction points
        return $destructionPercs;
    }
 
    // Get allies/enemies of village
    public function getOtherVillagesBasedOnStatus($allianceData, $status) {
        $villages = array();
        foreach($allianceData as $key => $value) {
            if(ctype_digit($value)) { // Status must be digit
                if((int)$value === $status) { // Select for the status we're looking for
                    if($key !== "Syndicate") { // Exclude Syndicate
                        if($key !== $allianceData['village']) { // Exclude the village we're looking up
                            if(in_array($key, Data::$VILLAGES, true)) {  // only include columns that are in the village array    
                                $villages[] = $key;
                            }
                        }
                    }
                }
            }
        }
        return $villages;
    }
    
    // For the passed $village, this function finds the array of villages with a given status
    public function getVillagesWithStatus($village, $status) {
        // Get the allies of the enemy. War will be declared on all these.
        $enemies = array();
        foreach($this->allAlliances as $alliance) {
            if($alliance['village'] === $village) { $enemies = $this->getOtherVillagesBasedOnStatus($alliance, $status); }
        }
        
        // Return list of enemies
        return $enemies;
    }
    
    // Get territories of village. Result is saved, unless reloaded
    private function getCurrentTerritories( $village, $refresh = false ){
        
        // Check array
        if( !isset( $this->villageTerritories ) ){ 
            $this->villageTerritories = array();
        }
        
        // Check village
        if( !isset( $this->villageTerritories[$village] ) ){
            $this->villageTerritories[$village] = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `locations` 
                WHERE `owner` = '" . $village . "' AND 
                      `identifier` != 'village' AND 
                      `identifier` LIKE 'AREA:%'");
        }
        
        // Return to user
        return $this->villageTerritories[$village];
        
    }
    
    // Check the war history if the village has a $action earlier than $seconds seconds ago
    public function hasJustBeenInWar($village, $action, $seconds) {
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                if($logEntry['action'] === $action) { 
                    if($logEntry['time'] > ($GLOBALS['user']->load_time - $seconds)) { 
                        if(stristr($logEntry['attached_info'], $village)) { 
                            return ($logEntry['time'] + $seconds) - $GLOBALS['user']->load_time;
                        }
                    }
                }
            }
        }
        return 0;
    }
    
     // Figure out who started the war on this village
    private function started_war_on($winVillage, $loseVillage) {
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                if($logEntry['action'] === "startWar") {
                    if($logEntry['attached_info'] === $winVillage."->".$loseVillage) {
                        $villages = explode("->",$logEntry['attached_info']);
                        return $villages[0];
                    }
                }
            }
        }
        return false;
    }
    
    // Figure out who put one village in war with the other the last
    private function who_started_war_last( $initiaterVillage, $receptorVillage ) {
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                if($logEntry['action'] === "startWar" || $logEntry['action'] === "joinWar") {
                    if($logEntry['attached_info'] === $initiaterVillage."->".$receptorVillage) {
                        if($logEntry['additional_info'] !== "kagePunished" ) {
                            return $logEntry;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    // Check if a village is a vassal
    private function isVassal($village) {
        foreach($this->allAlliances as $alliance) {
            if($alliance['vassal'] === $village) { return $alliance['name']; }
        }
        return false;
    }
    
    // Get other villages
    public function getOtherVillages( $notVillage ){
        $villages = array();
        foreach( Data::$VILLAGES as $village ){
            if( $village !== $notVillage && $village !== "Syndicate" ){
                $villages[] = array(
                    "name" => $village,
                    "warInfo" => $this->warInformation( $notVillage, $village ) ,
                    "peaceInfo" => $this->peaceInformation( $notVillage, $village ),
                );
            }
        }
        return $villages;
    }
    
    // Pick out village from array based on its name
    public function pick_out_village( $villageName, $villageArray , $key = "name"){
        $villageInfo = false;
        foreach( $villageArray as $temp ){
            if( $temp[ $key ] == $villageName ){
                $villageInfo = $temp;
            }
        }
        return $villageInfo;
    }
    
    // Check if alliance can be made
    public function can_form_alliance( $info, $toVillage ){
        if(empty($info['peaceInfo']['makePeace'])) {
            throw new Exception("Not possible to make an alliance with this village: ".$info['peaceInfo']['info']);
        }
        
        if(!in_array($toVillage, $info['peaceInfo']['makePeace'], true)) {
            throw new Exception("Cannot make an alliance with this village.");
        }
        
        return true;
    }
    
    // Check if it makes sense to surrender form village
    public function can_surrender($toVillage) {
        foreach($this->useralliance[0] as $key => $value) {
            if(ctype_digit($value)) {
                if((int)$value === 2) {
                    if($key !== "Syndicate") {
                        if($key === $toVillage) {
                            return true;
                        }
                    }
                }
            }
        }
        throw new Exception("It was not possible to find you in war with this village: ".$toVillage);
    }
    
    // Get a reqeust based on sender/receiver village & request type
    private function getRequest($id = "any", $userVillage=false, $otherVillage=false, $type=false) {
        if(ctype_digit($id)) {
            return $GLOBALS['database']->fetch_data("SELECT * FROM `alliance_requests` WHERE `alliance_requests`.`id` = ".$id." LIMIT 1");
        }
        elseif($id === "any") {
            if($userVillage !== false) {
                if($otherVillage !== false) {
                    if($type !== false) {
                        return $GLOBALS['database']->fetch_data("SELECT * FROM `alliance_requests` 
                            WHERE `user_village` = '" . $userVillage . "' AND `opponent_village` = '" . $otherVillage . "' 
                                AND `type` = '".$type."' LIMIT 1");
                    }
                }
            }
        }
        throw new Exception("Could not figure out how to retrieve the request.");
    }
    
    // Latest war entry for village
    private function latestWarEntry( $village ){
        $latestEntry = false;
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                if( in_array($logEntry['action'], array("surrenderWar","joinWar","winWar","startWar")) ) {
                    if( stristr( $logEntry['attached_info'], $village ) ) {
                        if( !isset($latestEntry) || $latestEntry['id'] < $logEntry['id'] ){
                            $latestEntry = $logEntry;
                        }
                    }
                }
            }
        }
        return $latestEntry;
    }
    
    // Check if village is aggressor in the active war they're in
    private function isWarAgressor( $checkVillage ){
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                
                // If user started war, good
                if( $logEntry['action'] == "startWar" && stristr( $logEntry['attached_info'], $checkVillage."->" ) ) {
                    return $logEntry;
                }
                
                // If user joined war, not aggressor
                // if other person attacked, not aggressor
                if( 
                    ( $logEntry['action'] == "joinWar" && stristr( $logEntry['attached_info'], $checkVillage."->" ) ) ||
                    ( $logEntry['action'] == "startWar" && stristr( $logEntry['attached_info'], "->".$checkVillage ) )
                ) {
                    return false;
                }
            }
        }
        return false;
    }
    
    // Check if village is victim in the active war they're in
    private function isWarVictim( $checkVillage ){
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                
                // If other started war, good
                if( $logEntry['action'] == "startWar" && stristr( $logEntry['attached_info'], "->".$checkVillage ) ) {
                    return $logEntry;
                }
                
                // If user joined war, not victim
                // if person attacked, not victim
                if( 
                    ( $logEntry['action'] == "joinWar" && stristr( $logEntry['attached_info'], $checkVillage."->" ) ) ||
                    ( $logEntry['action'] == "startWar" && stristr( $logEntry['attached_info'], $checkVillage."->" ) )
                ) {
                    return false;
                }
            }
        }
        return false;
    }
    
    // Check if village joined the war as the latest war action
    private function joinedAsAlly( $checkVillage ){
        if($this->warHistory !== "0 rows") {
            foreach($this->warHistory as $logEntry) {
                
                // Set to true if joined war
                if( $logEntry['action'] == "joinWar" && stristr( $logEntry['attached_info'], $checkVillage."->" ) ) {
                    return $logEntry;
                }
                
                // Set to false if village started war or was started upon
                if( $logEntry['action'] == "startWar" && stristr( $logEntry['attached_info'], $checkVillage ) ) {
                    return false;
                }
            }
        }
    }
    
    // Get query for reducing regen per destruction percentage
    private function getRegenReductionPerDestructionQuery( $percentDestroyed, $destructionPercPerRegen , $villageName ){
        if( $percentDestroyed >= $destructionPercPerRegen ){
            $regenReduction = floor($percentDestroyed/$destructionPercPerRegen);
            if( $regenReduction > 0){
                $this->logNotes .= $villageName . " regen reduced by ".$regenReduction.".<br>";
            }
            return ", `regen_level` = `regen_level` - '".$regenReduction."'";
        }
        else{
            return "";
        }
    }
    
    // get query for reducint structures per destruction percentage
    private function getStructureReductionPerDestruktionQuery( $percentDestroyed, $destructionPercPerStructure , $villageName ){
        
        // Query to construct
        $query = "";
        
        // Check that things should be run
        if( $percentDestroyed >= $destructionPercPerStructure ){
            
            // Variables to be used for easier access
            $villageData = $this->pick_out_village( $villageName , $this->allAlliances);
            $pointsToRemove = floor($percentDestroyed/$destructionPercPerStructure);
            $updates = array();
            
            // Set array values to zero in the beginning
            foreach( Data::$STRUCTURENAMES as $structure ){
                $updates[ $structure ] = 0;
            }
            
            // Get reductions. Don't loop more than 50 times though
            $i = 0;
            $reductions = 0;
            while( $i < 50 && $reductions < $pointsToRemove ){
                $key = Data::$STRUCTURENAMES[ random_int(0,3) ];
                if( $villageData[ $key."_level" ] - $updates[ $key ] > 0 ){
                    $updates[ $key ] += 1;
                    $reductions++;
                }
                $i++;
            }
            
            // Create the query
            foreach( $updates as $key => $value ){
                $query .= ", `".$key."_level` = `".$key."_level` - '".$value."'";
                if( $value > 0 ){
                    $this->logNotes .= $villageName." ".str_replace("_"," ",$key)." reduced by ".$value.".<br>";
                }
            }
        }   
        
        // Return query (empty if nothing happened)
        return $query;
    }
    
    /* ===================================
     * Set functions / Convenience Functions
     * Sets various simple information / data
     * =================================== 
     */
    
    // Reset the structure points of all in the village array
    public function reset_villagers_SP( $villageArray ){
        $GLOBALS['database']->execute_query("
                UPDATE `users_loyalty`,`users_missions`
                SET `users_missions`.`structureDestructionPoints` = DEFAULT,
                    `users_missions`.`structureGatherPoints` = DEFAULT, 
                    `users_missions`.`structurePointsActivity` = DEFAULT
                WHERE 
                    `users_loyalty`.`village` IN ('".implode("', '", $villageArray)."') AND 
                    `users_missions`.`userid` = `users_loyalty`.`uid`");
    }
    
    // Check if the vassal should be cleared
    private function removeVassal( $alliance ){
        if( $alliance['vassal_time'] < ($GLOBALS['user']->load_time-(7*24*3600)) ){
            $vassalAlliance = $this->pick_out_village( $alliance['vassal'] , $this->allAlliances);
            if( !$this->inWar($vassalAlliance) ){
                
                // No longer vassal
                $GLOBALS['database']->execute_query("
                    UPDATE `village_structures` 
                    SET `vassal` = '', `vassal_time` = '0'
                    WHERE `village_structures`.`name` = '" . $alliance['name'] . "'
                    LIMIT 1");
                
                // Remove cache
                cachefunctions::deleteAlliance($alliance['name']);
                cachefunctions::deleteAlliances();
                
                // Log the liberation
                functions::log_user_action($_SESSION['uid'], "liberation", $alliance['name']."->".$alliance['vassal']);
                
                // Return true so we know it was removed
                return true;
            }
        }
        return false;
    }
    
    // Function for setting userAlliance and allALliances array
    public function setAlliances( $refreshCache = false ) {
        
        // Refresh cache
        if( $refreshCache == true ){
            cachefunctions::deleteAlliances(); 
        }
        
        // Load allAlliances array
        if( !isset($this->allAlliances) || !isset($this->useralliance) || $refreshCache == true ) {
            
            // Get alliances
            $this->useralliance = $GLOBALS['userdata'][0]['alliance'];
            $this->allAlliances = cachefunctions::getAlliances();  

            // Get & check the vassals
            $this->vassals = array();
            foreach($this->allAlliances as $alliance){
                if(!empty($alliance['vassal'])) {
                    // Check & possibly remove vassal
                    if(!$this->removeVassal($alliance)) { 
                        $this->vassals[$alliance['name']] = $alliance['vassal'];                         
                    }
                }
            }
            
            // Get war/alliance history
            $this->setWarHistory();
        }
    }
    
    // Get war history
    private function setWarHistory(){
        $this->warHistory = $GLOBALS['database']->fetch_data("
            SELECT * FROM `users_actionLog` 
            WHERE `users_actionLog`.`action` IN('startWar','surrenderWar', 'winWar', 'joinWar', 'startTerritoryBattle') 
            ORDER BY `users_actionLog`.`time` DESC 
            LIMIT 50");
    }
    
    // Reset log notes
    private function resetLognotes(){
        $this->logNotes = "";
    }
    
    // Update the database with new alliance status
    protected function set_db_alliance($village1, $village2, $status, $userMessage = true , $becameVassal = false ) {
        
        // Switch for a user message to send out.
        switch ($status) {
            case "1": $message = $village1." and ".$village2." are now allied."; break;
            case "2": $message = $village1." has declared war on ".$village2."."; break;
            case "0": $message = $village1." and ".$village2." are now neutral towards each other."; break;
        }
        
        // Vassal message
        if( $becameVassal == true ){
            $message = $village2." has become the vassal of ".$village1.".";
        }

        // Update the database
        if( $userMessage == true ){
            $GLOBALS['database']->execute_query("UPDATE `users`, `users_loyalty`
                SET `users`.`healed` = '".functions::store_content($message)."' 
                WHERE `users_loyalty`.`village` IN('".$village1."', '".$village2."') AND `users`.`id` = `users_loyalty`.`uid`");
        }
        
        $GLOBALS['database']->execute_query("UPDATE `alliances` AS `vil_1`, `alliances` AS `vil_2`
            SET `vil_1`.`".$village1."` = ".$status.",
                `vil_2`.`".$village2."` = ".$status."
            WHERE `vil_1`.`village` = '".$village2."' AND `vil_2`.`village` = '".$village1."'");
        
        // Delete cache
        cachefunctions::deleteAlliance($village1);
        cachefunctions::deleteAlliance($village2);
        cachefunctions::deleteAlliances();
        
        // Check if the wars are over
        $this->allAlliances = cachefunctions::getAlliances();  
        $this->warLog_fixActivity();
    }
    
    // Update the structures of all villages
    // i.e. ensures that villages in war have structure points, and those who are not do not have structure points
    private function set_db_structures() {
        
        // Re-set the alliances
        $this->setAlliances( true );
        $notInWar = array();
        
        // Loop through all alliances
        foreach( $this->allAlliances as $alliance ){
            
            // Checks
            $inWar = $this->inWar( $alliance );
            $hasStructures = $this->hasStructures( $alliance );
            
            // If in war and no structures, then create the structures
            if( $inWar && !$hasStructures ){
                
                // Set structures to initial values
                $structurePoints = 10000;
                $structurePoints += $alliance['anbu_bonus_level'] * Data::$STRUCTURES['anbu'];
                $structurePoints += $alliance['hospital_level'] * Data::$STRUCTURES['hospital'];
                $structurePoints += $alliance['shop_level'] * Data::$STRUCTURES['shop'];
                $structurePoints += $alliance['wall_rob_level'] * Data::$STRUCTURES['wall_rob'];
                $structurePoints += $alliance['wall_def_level'] * Data::$STRUCTURES['wall_def'];
                $structurePoints += $alliance['regen_level'] * Data::$STRUCTURES['regen'];
                
                // Run update
                $GLOBALS['database']->execute_query("
                    UPDATE `village_structures` 
                    SET 
                        `cur_structurePoints` = '".$structurePoints."',
                        `start_structurePoints` = '".$structurePoints."'
                    WHERE `name` = '".$alliance['village']."'
                    LIMIT 1" );
                
            }
            
            // If has structures, but no longer in war, remove structures
            if( $inWar == false && $hasStructures ){
                
                // Upload the new levels
                $GLOBALS['database']->execute_query("
                    UPDATE `village_structures` 
                    SET `village_structures`.`cur_structurePoints` = 0, 
                        `village_structures`.`start_structurePoints` = 0,
                        `village_structures`.`counted_structurePoints` = 0,
                        `village_structures`.`KonokiPerc` = 0,
                        `village_structures`.`SilencePerc` = 0,
                        `village_structures`.`SamuiPerc` = 0,
                        `village_structures`.`ShinePerc` = 0,
                        `village_structures`.`ShroudPerc` = 0,
                        `village_structures`.`SyndicatePerc` = 0
                    WHERE `name` = '".$alliance['village']."'
                    LIMIT 1");
                
            }
            
            // Add to not-in-war array
            if( !$inWar ){
                $notInWar[] = $alliance['village'];
            }
        }
        
        // Reset the structure points of all villages not in war
        if( !empty($notInWar) ){
            
            // Remove the structure points on the users
            $GLOBALS['database']->execute_query("
            UPDATE `users_loyalty`,`users_missions`
            SET `users_missions`.`structureDestructionPoints` = DEFAULT,
                `users_missions`.`structureGatherPoints` = DEFAULT, 
                `users_missions`.`structurePointsActivity` = DEFAULT
            WHERE 
                `users_loyalty`.`village` IN ('".implode("','", $notInWar)."') AND 
                `users_missions`.`userid` = `users_loyalty`.`uid`");
        }
    }
    
    // Move territories from one village to another
    private function transferTerritories( $winVillage, $loseVillage, $limit ){
        
        // Update database
        $GLOBALS['database']->execute_query("
            UPDATE `locations` 
            SET `owner` = '".$winVillage."'
            WHERE `owner` = '" . $loseVillage . "' AND 
                  `identifier` != 'village' AND 
                  `identifier` LIKE 'AREA:%'
            LIMIT ".$limit."
        ");
        
        // Save in war history
        $this->logNotes .= "Territories transfered from ".$loseVillage." to ".$winVillage.".<br>";
        
        // Create new map
        require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');
        mapfunctions::create_map();
    }
    
    /* ===================================
     * Core Functions
     * Complex actions such as e.g. winning wars, getting war information etc.
     * =================================== 
     */
    
    // Function used in set_db_structures for generating query for reducing regen and structures of village losing war
    private function punishVillage( $villageName, $pointsLost, $regenPercLost, $regenMinLoss ){
        
        // Query temp
        $query = "";
        
        // Get structures
        $structures = $this->pick_out_village( $villageName, $this->allAlliances );
        
        // Lose $regenPercLost % max village regen, minimum of $regenMinLoss regeneration lost
        $regenLost = floor($regenPercLost * $structures['regen_level']);
        $regenLost = ( $regenLost > 2 ) ? $regenLost : 2;
        $query .= "`regen_level` = `regen_level` - '".$regenLost."'";
        $this->logNotes .= $villageName." lost ".$regenLost." regeneration.<br>";

        // Lose $pointsLost random structures
        $query += $this->getStructureReductionPerDestruktionQuery($pointsLost, 1, $villageName);
        
        // Run the query
        $GLOBALS['database']->execute_query("
            UPDATE `village_structures` 
            SET ".$query." 
            WHERE name = '".$villageName."'
            LIMIT 1");
    }
   
    // Wins a war for the given village
    private function win_a_war($winVillage, $loseVillage, $split, $checkVassal = true , $doPunish = true, $extraLogInfo = "" ) {
        
        // Reset notes
        $this->resetLognotes();
        
        // Extra log info
        if( $extraLogInfo !== "" ){
            $this->logNotes .= $extraLogInfo." .<br>";
        }
        
        /*
         * Run Calculations
         */
        
        // Calculate the total destruction of village (which is saved in $this->structures)
        $total_destruction = 1;
        foreach(Data::$VILLAGES as $tempVillage) {
            if($tempVillage !== "Syndicate") {
                $total_destruction += $this->structures[0][$tempVillage."Perc"];
            }
        }
        
        // Calculate points gained. $this->structures contains the losing village
        $reward = 20+250*($this->structures[0]["anbu_bonus_level"]+$this->structures[0]["hospital_level"]+$this->structures[0]["shop_level"]+$this->structures[0]["wall_rob_level"]+$this->structures[0]["wall_def_level"]);
        $points = round($this->structures[0][$winVillage."Perc"] * $reward / $total_destruction) / $split;

        // See if we can find who started the war
        $vassalQuery = "";
        $becameVassal = false;
        if( $this->started_war_on($winVillage, $loseVillage)) {
            
            // If win village started the war, get vassal
            if( $checkVassal == true ){   
                $becameVassal = true;
                $vassalQuery = ", `village_structures`.`vassal` = '".$loseVillage."', `village_structures`.`vassal_time` = ".$GLOBALS['user']->load_time." ";
                $this->logNotes .= $loseVillage." became the vassal of ".$winVillage.".<br>";
            }
            
        }
        else{
            
            // If winVillage didn't win, this is an ally village. Cut VF in 4
            $points = round( $points / 4 );
        }
        
        // Get alliance of the village
        $winVillageStructures = $this->pick_out_village( $winVillage, $this->allAlliances );
        $loseVillageStructures = $this->pick_out_village( $loseVillage, $this->allAlliances );
        
        // Calculate how much damage the winning village took, and how many structures they lose
        // Lose 1 regen / 20% SP lost during the war. Only if more than 20%
        $structureQuery = "";
        $percentDestroyed = $this->getDestructionPercentage($winVillage, false);
        if( $percentDestroyed >= 20 ){
            $structureQuery .= $this->getRegenReductionPerDestructionQuery($percentDestroyed, 20, $winVillage);
            $structureQuery .= $this->getStructureReductionPerDestruktionQuery($percentDestroyed, 30, $winVillage);
        }
        
        /*
         * Update Querires
         */
        
        // Update users
        $GLOBALS['database']->execute_query('
            SELECT `users`.`id` FROM `users_loyalty`
            INNER JOIN `users` ON (`users`.`id` = `users_loyalty`.`uid`)
            WHERE `users_loyalty`.`village` = "'.$winVillage.'" FOR UPDATE');
        
        // Set message for users & update their structure point recordings
        $GLOBALS['database']->execute_query("
            UPDATE `users`, `users_loyalty`,`users_missions` 
            SET 
                `users`.`healed` = '".functions::store_content($winVillage." has won the war against ".$loseVillage.", winning ".$points." village funds.")."' ,
                `users_missions`.`structureDestructionPoints` = DEFAULT,
                `users_missions`.`structureGatherPoints` = DEFAULT, 
                `users_missions`.`structurePointsActivity` = DEFAULT
            WHERE 
                `users_loyalty`.`village` = '".$winVillage."' AND
                `users`.`id` = `users_loyalty`.`uid` AND
                `users_missions`.`userid` = `users`.`id`");
        
        // Update village points
        $GLOBALS['database']->execute_query("
            UPDATE `villages`, `village_structures` 
            SET `villages`.`points` = `villages`.`points` + ".$points.",
                `village_structures`.`warRegenBoostTime` = ".$GLOBALS['user']->load_time.",
                `village_structures`.`counted_structurePoints` = `village_structures`.`cur_structurePoints` 
                ".$vassalQuery." ".$structureQuery."
            WHERE `villages`.`name` = '".$winVillage."' AND `village_structures`.`name` = `villages`.`name`");
        if( $points > 0 ){
            $this->logNotes .= $winVillage." won ".$points." village funds.<br>";
        }
        
        // Run punishmens also
        if( $doPunish == true ){
            
            // Punish losing village
            if( $this->isWarAgressor( $loseVillage ) ){
                $this->punishVillage( $loseVillage, 7, 0.05, 2 );
            }
            elseif( $this->isWarVictim( $loseVillage ) ){
                $this->punishVillage( $loseVillage, 4, 0.05, 1 );
            }
            elseif( $this->joinedAsAlly( $loseVillage ) ){
                $this->punishVillage( $loseVillage, 2, 0.05, 1 );
            }
            else{
                throw new Exception("Can't figure out how to punish ".$loseVillage."<br>");
            }
            
            // Punish the kage of the village who started the war
            $this->punish_kage( $loseVillage, $winVillage ); 
            
        }
        
        // Update actionLog
        functions::log_user_action(
            $_SESSION['uid'], 
            "winWar", 
            $winVillage."->".$loseVillage,
            $this->logNotes
        );
        
        // Update the status between these two villages
        $this->set_db_alliance($winVillage, $loseVillage, 0, false, $becameVassal );
        
    }
    
    // Set information on what going to war with otherVillage would mean for alliance Village
    public function warInformation( $userVillage, $otherVillage ){
        
        // Save the information
        $information = array(
            "info" => "",
            "toWar" => array(),
            "breakAlly" => array()
        );
        
        // Check if already in war
        if ( $this->useralliance[0][ $otherVillage ] != 2) {
            
            // Check if the other village is a vassal
            $otherVassal = $this->isVassal($otherVillage);
            if( empty($otherVassal) ){
                
                // Check if the user village is a vassal
                $userVassal = $this->isVassal($userVillage);
                if( empty($userVassal) ){
                    
                    // Check if this village has already been in this war
                    $hasBeenInWar = $this->warLog_getActiveWarID( $userVillage );
                    if( !$hasBeenInWar || $this->inWar($this->useralliance[0]) ){
                        
                        // Check user startWar cooldowns
                        $userCooldown = $this->hasJustBeenInWar( "->".$userVillage ,"winWar",14*24*3600);
                        if( $userCooldown == 0 ){

                            // Check opponent startWar cooldown
                            $otherCooldown = $this->hasJustBeenInWar("->".$otherVillage ,"startWar",7*24*3600);
                            if( $otherCooldown == 0 ){

                                // Check user winwar cooldown
                                $winCooldown = $this->hasJustBeenInWar($userVillage."->" ,"winWar",7*24*3600);
                                if( $winCooldown == 0 ){

                                    // Get alliances
                                    $otherAllies = $this->getVillagesWithStatus( $otherVillage, 1 );
                                    $otherEnemies = $this->getVillagesWithStatus( $otherVillage, 2 );
                                    $userAllies = $this->getVillagesWithStatus( $userVillage, 1 );
                                    $userEnemies = $this->getVillagesWithStatus( $userVillage, 2 );

                                    // Check all the user alliance allies. They should all be broken if they are not already in war with the otherVillage
                                    if( !empty($userAllies) ){
                                        foreach( $userAllies as $userAlly ){
                                            if( !in_array( $userAlly, $otherEnemies ) ){
                                                $information['breakAlly'][] = $userAlly;
                                            }
                                        }
                                        if( !empty($information['breakAlly']) ){
                                            $information['info'] .= "War with " . $otherVillage . " will break your alliance with: " . implode(", ", $information['breakAlly']) . "<br>";
                                        }
                                    }

                                    // Check the allies of the opponent. If not already in war with them, notify the user he will be
                                    $toWar = array_diff( $otherAllies, $userEnemies );

                                    // No need to include the user village
                                    $toWar = array_diff( $toWar, array($userVillage) );

                                    // Add the vassal of the userVillage to the array, unless it's already there.
                                    $villageStructure = $this->pick_out_village( $otherVillage, $this->allAlliances );
                                    if( !empty($villageStructure['vassal']) ){
                                        if( !in_array($villageStructure['vassal'],$toWar) ){
                                            $toWar[] = $villageStructure['vassal'];
                                        }
                                    }

                                    // Information on who we're going to war on
                                    if( !empty($toWar) ){
                                        $information['toWar'] = $toWar;
                                        $information['info'] .= "War with " . $otherVillage . " will put you in war with: " . implode(", ", $toWar);
                                    }
                                    else{
                                        $information['info'] .= "Going to war with " . $otherVillage . " will not put you in war with additional villages.";
                                    }

                                    // Add the other village to the toWar array
                                    $information['toWar'][] = $otherVillage;

                                }
                                else{
                                    $release = functions::convert_time($winCooldown, $userVillage.$otherVillage.'Wincooldown', 'false');
                                    $information['info'] .= "You cannot go to war again yet. You must wait: ".$release;
                                }
                            }
                            else{
                                $release = functions::convert_time($otherCooldown, $userVillage.$otherVillage.'immunity', 'false');
                                $information['info'] .= "This village has war immunity for another: ".$release;
                            }
                        }
                        else{
                            $release = functions::convert_time($userCooldown, $userVillage.$otherVillage.'cooldown', 'false');
                            $information['info'] .= "You cannot go to war again yet. You must wait: ".$release;
                        }                        
                    }
                    else{
                        $information['info'] .= "You have already been in this war and cannot re-join.";
                    }
                } else {
                    $information['info'] .= "Your village is currently the vassal of " . $userVassal;
                }
            } else {
                $information['info'] .= $otherVillage." is currently a vassal of " . $otherVassal;
            }
        } else {
            $information['info'] .= "You are already in war with " . $otherVillage;
        }
        
        // Return information
        return $information;
    }
    
    // Set information on what going to alliance with otherVillage would mean for alliance Village
    public function peaceInformation( $userVillage, $otherVillage ){
        
        // Save the information
        $information = array(
            "info" => "",
            "toWar" => array(),
            "breakAlly" => array(),
            "makePeace" => array()
        );
        
        // Get Alliances
        $otherAllies = $this->getVillagesWithStatus( $otherVillage , 1 );
        $otherEnemies = $this->getVillagesWithStatus( $otherVillage , 2 );
        $userAllies = $this->getVillagesWithStatus( $userVillage , 1 );
        $userEnemies = $this->getVillagesWithStatus( $userVillage, 2 );
        
        // Check SP left for user village and enemy village. Only allow peace treaties if both are above 45%
        $villagesToCheck = array_merge( array($userVillage),array($otherVillage), $otherAllies, $otherEnemies, $userAllies, $userEnemies);
        foreach( $villagesToCheck as $checkVillage ){
            $perc = $this->getDestructionPercentage( $checkVillage );
            if( $perc > 55 ){
                $information['info'] = "Cannot form an alliance with ".$otherVillage." because ".$checkVillage." has less than 45% structure points left (".$perc."%)";
            }
        }
        
        // Check that info not already set
        if( empty($information['info']) ){
            
            // Check if already allies
            if ( !in_array( $otherVillage, $userAllies ) ) {

                // Check if user is currently in war with otherVillage
                if( !in_array( $userVillage, $otherEnemies ) ){

                    // See if user wars involve any of otherVillage allies
                    $union = array_intersect( $userEnemies, $otherAllies );
                    if( empty($union) ){
                        
                        // Variable to check if this alliance is possible. Default = true
                        $canMakePeace = true;

                        // If the otherVillage has enemies, check it out
                        if( !empty($otherEnemies) ){

                            // If village was just in war, there's a cooldown to making alliances that break wars (7 days). 
                            // This needs to be enforced, and if it's in effect, village cannot perform this alliance
                            // Check user startWar cooldowns
                            $userCooldown = $this->hasJustBeenInWar( $userVillage."->" ,"startWar",7*24*3600);
                            $otherCooldown = $this->hasJustBeenInWar($otherVillage."->" ,"startWar",7*24*3600);
                            $winCooldown = $this->hasJustBeenInWar($userVillage."->" ,"winWar",14*24*3600);                           
                            $hasBeenInWar = $this->warLog_getActiveWarID( $userVillage );
                            
                            // Run checks
                            if( $userCooldown !== 0 ){
                                $canMakePeace = false;
                                $release = functions::convert_time($userCooldown, $userVillage.$otherVillage.'cooldown2', 'false');
                                $information['info'] .= "This alliance implies war. You cannot go to war again yet. You must wait: ".$release;
                            }
                            elseif( $hasBeenInWar && !$this->inWar($this->useralliance[0]) ){
                                $canMakePeace = false;
                                $information['info'] .= "This alliance implies re-joining a war you have already been in. You cannot do that";
                            }
                            elseif( $otherCooldown !== 0 ){
                                $canMakePeace = false;
                                $release = functions::convert_time($otherCooldown, $userVillage.$otherVillage.'immunity2', 'false');
                                $information['info'] .= "This alliance implies war. This opponent village has war immunity for another: ".$release;
                            }
                            elseif( $winCooldown !== 0 ){
                                $canMakePeace = false;
                                $release = functions::convert_time($winCooldown, $userVillage.$otherVillage.'Wincooldown2', 'false');
                                $information['info'] .= "This alliance implies war. You cannot go to war again yet. You must wait: ".$release;
                            }
                            else{
                                
                                // Get villages which are otherVillage enemies and userVillage allies
                                $union = array_intersect($otherEnemies, $userAllies);

                                // If the one they are trying to befriend has enemies that are friends, these alliances will be broken
                                if( !empty($union) ){
                                    $information['breakAlly'] = $union;
                                    $information['info'] = "An alliance with " . $otherVillage . " will break alliances with: " . implode(", ", $union);
                                }
                                else{
                                    $information['info'] = "An alliance with " . $otherVillage . " will not break any alliances.";
                                }

                                // If the other village has enemies that are not currently friends, they will become enemies
                                $information['toWar'] = $otherEnemies;
                                $information['info'] .= "<br>An alliance with " . $otherVillage . " will put you in war with: " . implode(", ", $otherEnemies);
                            }                            
                        }
                        else{
                            $information['info'] = "An alliance with " . $otherVillage . " will not break any alliances.";
                        }

                        // If user has enemies, he is basically asking for help, and this will cost him
                        if( !empty($userEnemies) ){
                            $information['info'] .= "<br>You are currently in war. Calling ".$otherVillage." for help will cost you 10% of your structure points and 1 random village upgrade";
                        }

                        // Add village to make peace array
                        if( $canMakePeace == true ){
                            $information['makePeace'][] = $otherVillage;
                        }
                    }
                    else{
                        $information['info'] = "Current wars with ".implode(",",$union)." conflict with making an alliance with " . $otherVillage;
                    }
                }
                else{
                    $information['info'] = "You first need to end the war with " . $otherVillage;
                }
            } else {
                $information['info'] = "You are already allied with " . $otherVillage;
            }
        }
        
        // Return information
        return $information;
    }
    
    // Function for punishing the kage fromVillage who initiated war on winVillage
    private function punish_kage( $fromVillage, $winVillage ){
        
        // Check who started the war
        $whoStartedWar = $this->who_started_war_last( $fromVillage, $winVillage );
        if( $whoStartedWar !== false and ctype_digit($whoStartedWar['uid']) ){
            
            // Query placeholder
            $query = "";
            
            // Get username of the person kage
            $user = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $whoStartedWar['uid'] . "' LIMIT 1");            
            if( $user !== "0 rows" ){
                
                // If more than 50% left in winning village, then kick. Otherwise, jail
                // Check structure points left; kick or jail
                $percentDestroyed = $this->getDestructionPercentage( $winVillage );
                if( $percentDestroyed >= 50 ){

                    // Turn outlaw
                    require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
                    $respectLib = new respectLib();
                    $message = $respectLib->turn_outlaw( $whoStartedWar['uid'] );

                    // Log change
                    $this->logNotes .= "The previous kage ".$user[0]['username']." was turned outlaw.<br>";

                    // Log the change
                    functions::log_village_changes(  
                        $whoStartedWar['uid'],
                        $fromVillage,
                        "Syndicate",
                        "Kicked for initiating lost war"
                    );
                }
                else{

                    // Update status
                    $query = ", `status` = 'jailed', `jail_timer` = '".(time()+24*3600)."'";
                    
                    // Log change
                    $this->logNotes .= "The previous kage ".$user[0]['username']." was sent to jail.<br>";

                    // Update/remove kage if he's still active
                    $GLOBALS['database']->execute_query('
                        UPDATE `villages` 
                        SET `villages`.`leader` = "'.Data::$VILLAGE_KAGENAMES[$fromVillage].'" 
                        WHERE `villages`.`name` = "'.$fromVillage.'" AND 
                              `villages`.`leader` = "'.$user[0]['username'].'"
                        LIMIT 1'
                    );
                }

                // Lose Respect points & Diplomacy
                $GLOBALS['database']->execute_query("
                    UPDATE `users`, `users_loyalty`, `users_timer`,`bingo_book`,`villages`
                    SET 
                        `users`.`healed` = '".functions::store_content("As a kage who initiated a war which was lost, you are punished by a reduction in village respect points and diplomacy points.")."' ,
                        `users_loyalty`.`vil_loyal_pts` = `users_loyalty`.`vil_loyal_pts` * 0.75,
                        `bingo_book`.`".$fromVillage."` = '0'
                        ".$query."
                    WHERE 
                        `users_loyalty`.`uid` = '".$whoStartedWar['uid']."' AND 
                        `users`.`id` = `users_loyalty`.`uid` AND
                        `bingo_book`.`userid` = `users_loyalty`.`uid` AND
                        `users_timer`.`userid` = `users_loyalty`.`uid`");
                
                // Don't give him a global lost message
                $this->exemptMessageUsers[] = $whoStartedWar['uid'];

                // Update the log entry
                $GLOBALS['database']->execute_query("
                    UPDATE `users_actionLog` 
                    SET `additional_info` = 'kagePunished'
                    WHERE id = '" . $whoStartedWar['id'] . "'
                    LIMIT 1");
                
            }
        }             
    }
    
    
    
    /* ===================================
     * View functions
     * Functions related to displaying data
     * =================================== 
     */
    
    // Return color formatted array for alliance
    public function prettyAlliance( $allianceData ){

        // Loop through the alliance
        $prettyArray = array();
        $vassalOvner = $vassalVillage = false;
        foreach( $allianceData as $key => $value ){
            if( ctype_digit($value) && in_array($key, Data::$VILLAGES) ){
                
                // Find the vassal owner
                $isVassal = false;
                if( in_array($key, $this->vassals)  ){
                    $vassalOvner = array_search($key, $this->vassals);
                    $isVassal = true;
                }
                
                // Check if vassal, or just normal ally/enemy/neutral
                if( $isVassal && $key == $allianceData['name']){
                    $vassalVillage = $key;
                    $prettyArray[] = array( "village" => $key, "status" => "<b><font color='orange'>".$vassalOvner."</font></b>" );
                }
                elseif( $isVassal && $vassalOvner == $allianceData['name'] ){
                    $prettyArray[] = array( "village" => $key, "status" => "<b><font color='orange'>Ally</font></b>" );
                }
                else{
                    if ( $value == 1) {
                        $prettyArray[] = array( "village" => $key, "status" => "<b><font color='green'>Ally</font></b>" );
                    } elseif ( $value == 0) {
                        $prettyArray[] = array( "village" => $key, "status" => "<b><font color='#333399'>Neutral</font></b>" );
                    } elseif ( $value == 2) {
                        $prettyArray[] = array( "village" => $key, "status" => "<b><font color='#993300'>War</font></b>" );
                    }
                }
            }
        }
        
        // Set vassal owner to ally with vassal
        if( $vassalOvner !== false && $vassalVillage !== false ){
            foreach( $prettyArray as $key => $entry ){
                if( $entry['village'] == $vassalOvner ){
                    $prettyArray[$key] = array( "village" => $entry['village'], "status" => "<b><font color='orange'>Ally</font></b>" );
                }
            }
        }
        
        
        return $prettyArray;
    }
    
    // Earlier name: getAllAlliancesOrderedAndPretty. Gets all alliances, prettifies them, and orders them properly.
    public function getAlliesForDisplay(){
        
        // Prettify output
        $prettyAllianceArray = array();
        foreach( $this->allAlliances as $alliance){
            $prettyAllianceArray[ $alliance['village'] ] = $this->prettyAlliance($alliance);
        }
        
        // Order output (so that village columns follow rows)
        $ordering = array();
        foreach( $prettyAllianceArray[ "Syndicate" ] as $value ){
            $ordering[] = $value['village'];
        }
        $orderedPrettyArray = array();
        foreach( $ordering as $entry ){
            $orderedPrettyArray[ $entry ] = $prettyAllianceArray[ $entry ];
        }
        
        // Return
        return $orderedPrettyArray;
    }
    
    // The control panel for wars of a given village
    public function war_panel( $village ){
        
        
        // Save the user alliance locally
        $this->setAlliances();
        
        // Get the other villages
        $otherVillages = $this->getOtherVillages( $GLOBALS['userdata'][0]['village'] );
        $GLOBALS['template']->assign('otherVillages', $otherVillages);
        
        // Get allies & enemies of user
        $userAllies = $this->getOtherVillagesBasedOnStatus($this->useralliance[0], 1);
        $userEnemies = $this->getOtherVillagesBasedOnStatus($this->useralliance[0], 2);
        $GLOBALS['template']->assign('userAllies', $userAllies);
        $GLOBALS['template']->assign('userEnemies', $userEnemies);
        
        // Get requests
        $requests = $GLOBALS['database']->fetch_data("SELECT * FROM `alliance_requests` WHERE `user_village` = '" . $GLOBALS['userdata'][0]['village'] . "' OR `opponent_village` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 10");
        
        // Set the links for the request entires. Can't use options because they depend on the sender/receiver
        if( $requests !== "0 rows" ){
            for( $i=0 ; $i < count($requests) ; $i++ ){
                if( $requests[$i]['opponent_village'] == $GLOBALS['userdata'][0]['village']){
                    $requests[$i]['accept'] = "<a href='?id=".$_GET['id']."&act=".$_GET['act']."&act2=".$_GET['act2']."&action=accept&rid=".$requests[$i]['id']."'>Accept</a>";
                }
                else{
                    $requests[$i]['accept'] = "N/A";
                }
            }
        }
        
        tableParser::show_list(
                'requests', // Smarty variable
                'Current Requests', // Title
                $requests, // Data
                array(
            'user_village' => "Request Sender",
            'opponent_village' => "Request Receiver",
                    'type' => "Request Type",
                    'accept' => "Accept"
                ), // Main fields
                array(
            array("name" => "Delete", "act" => $_GET['act'], "act2" => $_GET['act2'], "action" => "delete", "rid" => "table.id")
                ), // Option fields
                false, 
                false
        );
        
        // Alliance stuff
        $displayAlliances = $this->getAlliesForDisplay();
        $GLOBALS['template']->assign('allianceData', $displayAlliances);
        
        // Show the main template
        $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/warPanel.tpl');
    }
    
    
    /* ===================================
     * War Actions
     * Functions related to sending requests, declaring wars, surrendering, breaking/creating alliances etc.
     * =================================== 
     */
    
    // Function for accepting a request
    public function accept_request( $requestID ){
        
        // See if there are already requests in the db
        $currentRequests = $this->getRequest( $requestID );
        if( $currentRequests !== "0 rows" ){
            
            // Get the other villages of this user
            $otherVillages = $this->getOtherVillages( $GLOBALS['userdata'][0]['village'] );
            
            // Check that the request belongs to the village in question
            if( $GLOBALS['userdata'][0]['village'] == $currentRequests[0]['opponent_village'] ){
            
                // Get information on the village that sent the request
                $fromVillage = $currentRequests[0]['user_village'];
                $info = $this->pick_out_village( $fromVillage, $otherVillages );
                if( $info !== false ){

                    // Handle alliance/surrender accept differently
                    $type = $currentRequests[0]['type'];
                    switch( $type ){
                        case "Alliance":
                            if( $this->can_form_alliance( $info, $fromVillage ) ){
                                
                                // Handle war actions
                                $this->handlePeaceToWarInfo( $info, $fromVillage );
                                
                                // Check for alliances to break
                                if( !empty( $info['peaceInfo']['breakAlly'] ) ){
                                    foreach( $info['peaceInfo']['breakAlly']  as $village ){
                                        $this->set_db_alliance( $GLOBALS['userdata'][0]['village'] , $village, 0 );
                                        functions::log_user_action($_SESSION['uid'], "breakAlliance", $GLOBALS['userdata'][0]['village']."->".$village);
                                    }
                                }
                                
                                // Make Peace
                                if( !empty( $info['peaceInfo']['makePeace'] ) ){
                                    foreach( $info['peaceInfo']['makePeace']  as $village ){
                                        $this->set_db_alliance( $GLOBALS['userdata'][0]['village'] , $village, 1 );
                                        functions::log_user_action($_SESSION['uid'], "startAlliance", $GLOBALS['userdata'][0]['village']."->".$village);
                                    }
                                }
                            }
                        break;
                        case "Surrender":
                            if( $this->can_surrender( $fromVillage ) ){
                                
                                // Reset log notes
                                $this->resetLognotes();
                                
                                // Determine all the villages with which the village is in war, and who therefore won over the village
                                $enemies        = $this->getVillagesWithStatus($fromVillage, 2);
                                $enemy_count    = count($enemies);

                                // Get the village structures
                                if(!($this->structures = $GLOBALS['database']->fetch_data("
                                    SELECT `village_structures`.* FROM `village_structures` 
                                    WHERE `village_structures`.`name` = '".$fromVillage."' LIMIT 1"))) 
                                {
                                    throw new Exception('There was an error getting village structures!');
                                }
                                
                                // Punish the kage who submitted the surrender
                                $this->punish_kage($fromVillage, $GLOBALS['userdata'][0]['village']);
                                
                                // Update user village vassal
                                $GLOBALS['database']->execute_query("
                                    UPDATE `village_structures` 
                                    SET `village_structures`.`vassal` = '".$fromVillage."', 
                                        `village_structures`.`vassal_time` = ".$GLOBALS['user']->load_time." 
                                    WHERE `name` = '".$GLOBALS['userdata'][0]['village']."' 
                                    LIMIT 1");
                                
                                // Structure points lost for village
                                $percentDestroyed = $this->getDestructionPercentage($fromVillage, false);
                                
                                // Structure reductions
                                $structureQuery = "";
                                $structureQuery .= $this->getRegenReductionPerDestructionQuery($percentDestroyed, 20, $fromVillage);
                                $structureQuery .= $this->getStructureReductionPerDestruktionQuery($percentDestroyed, 10, $fromVillage);
                                
                                // Update losing village
                                $GLOBALS['database']->execute_query("
                                    UPDATE `village_structures` 
                                    SET `entry_id` = `entry_id`,
                                        `village_structures`.`counted_structurePoints` = `village_structures`.`cur_structurePoints` 
                                        ".$structureQuery."
                                    WHERE `name` = '".$fromVillage."' 
                                    LIMIT 1");
                                
                                $this->logNotes .= $fromVillage." became the vassal of ".$GLOBALS['userdata'][0]['village']."..<br>";
                                
                                // Get current territories owned by loser village
                                $currentTerritories = $this->getCurrentTerritories( $fromVillage );
                                $this->transferTerritories($GLOBALS['userdata'][0]['village'], $fromVillage, count($currentTerritories));
                                
                                // Log action
                                functions::log_user_action(
                                        $_SESSION['uid'], 
                                        "surrenderWar", 
                                        $fromVillage."->".$GLOBALS['userdata'][0]['village'],
                                        $this->logNotes
                                );
                                
                                // Win the wars
                                foreach($enemies as $winVillage) { 
                                    $this->win_a_war($winVillage, $fromVillage, $enemy_count, false, false ); 
                                }
                                
                                
                            }
                        break;
                        default: throw new Exception("Could not figure out what request type you're trying to accept");
                    }
                    
                    // Remove Request
                    $this->remove_request( $currentRequests[0]['id'] );

                    // Update all the DB structures after all alliances have been set. 
                    $this->set_db_structures();

                    // Message to user
                    $GLOBALS['page']->Message("The request has been accepted.", "War Panel", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']);
                
                    
                }
                else{
                    throw new Exception("Could not find information on this village.");
                }
            }
            else{
                throw new Exception("This request does not belong to your village.");
            }
        }
        else{
            throw new Exception("Could not find the request in the database");
        }
    }
    
    // Break alliance
    public function break_alliance( $village){
        
        // Get information in regard to other villages for this village
        $otherVillages = $this->getOtherVillages( $GLOBALS['userdata'][0]['village'] );
        $info = $this->pick_out_village( $village, $otherVillages );
        
        // Check if they are in alliance
        if( $info !== false && isset($this->useralliance[0][ $village ]) && $this->useralliance[0][ $village ] == 1 ){
            
            // Break alliance
            $this->set_db_alliance( $GLOBALS['userdata'][0]['village'] , $village, 0 );
            
            // Log action
            functions::log_user_action($_SESSION['uid'], "breakAlliance", $GLOBALS['userdata'][0]['village']."->".$village);
            
            // Message
            $GLOBALS['page']->Message("Your status towards ".$village." has been set to Neutral", "War Panel", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']);
            
        }
        else{
            throw new Exception("You do not seem to be allied with this village");
        }
    }
    
    // Declare war
    public function declare_war( $village ){
        
        // Get information in regard to other villages for this village
        $otherVillages = $this->getOtherVillages( $GLOBALS['userdata'][0]['village'] );
        $info = $this->pick_out_village( $village, $otherVillages );
        
        // Check if there are any villages to go to war with
        if( !empty( $info['warInfo']['toWar'] ) ){
            
            // Go to war with all these village
            foreach( $info['warInfo']['toWar'] as $village ){
                
                // Set alliance
                $this->set_db_alliance( $GLOBALS['userdata'][0]['village'] , $village, 2 );
                
                // Reset structure points of all villagers
                $this->reset_villagers_SP( array($GLOBALS['userdata'][0]['village'] , $village) );
                
                // Save in action log
                functions::log_user_action($_SESSION['uid'], "startWar", $GLOBALS['userdata'][0]['village']."->".$village);
            }
            
            // Set the users into the war
            $this->warLog_setVillagesToWar( array_merge( array($GLOBALS['userdata'][0]['village']), $info['warInfo']['toWar'] ) );
            
            // Message
            $GLOBALS['page']->Message("You have declared war upon: " . implode(", ", $info['warInfo']['toWar']), "War Panel", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']);
                        
            // Update all the DB structures after all alliances have been set. 
            $this->set_db_structures();
        }
        else{
            $extraInfo = (isset($info['warInfo']['info'])) ? "<br>".$info['warInfo']['info'] : "";
            throw new Exception("You cannot go to war with this village.".$extraInfo);
        }
    }
    
    // Function for declining a request
    public function decline_request( $requestID ){
        
        // See if there are already requests in the db
        $currentRequests = $this->getRequest( $requestID );
        if( $currentRequests !== "0 rows" ){
            
            // The person who sent this request
            $fromVillage = $currentRequests[0]['user_village'];
            $ownsRequest = ($GLOBALS['userdata'][0]['village'] == $fromVillage);
            
            // Check that the request belongs to the village in question
            if( $GLOBALS['userdata'][0]['village'] == $currentRequests[0]['opponent_village'] || $ownsRequest ){
            
                // Get information in regard to other villages for this village
                $otherVillages = $this->getOtherVillages( $GLOBALS['userdata'][0]['village'] );
                $info = $this->pick_out_village( $fromVillage, $otherVillages );
                if( $info !== false || $ownsRequest){

                    // Remove the request from the database
                    $this->remove_request( $currentRequests[0]['id'] );

                    // Message to user
                    $GLOBALS['page']->Message("The request has been deleted.", "War Panel", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']);
                
                }
                else{
                    throw new Exception("Could not find information on this village.");
                }
            }
            else{
                throw new Exception("You cannot tamper with this request.");
            }
        }
        else{
            throw new Exception("Could not find the request in the database");
        }
    }
    
    // Get the structure points percentage left for given village
    private function getDestructionPercentage( $village , $completeCount = true ){
        
        // Get the village data
        $villageStructure = $this->pick_out_village( $village, $this->allAlliances );
        
        // Check if it should be total current SP, or reduce the ones already punished
        $currentStructure = $villageStructure[ "cur_structurePoints" ];
        if( $completeCount == false && $villageStructure[ "counted_structurePoints" ] > 0 ){
            $currentStructure += ( $villageStructure[ "start_structurePoints" ] - $villageStructure[ "counted_structurePoints" ] );
        }
                
        // Do the things        
        if( $villageStructure['start_structurePoints'] > 0 ){
            return 100 - ( $currentStructure / $villageStructure[ "start_structurePoints" ]) * 100;
        }
        else{
            return 0;
        }
    }
    
    // Handle toWar information and handle toWar array
    private function handlePeaceToWarInfo( $info , $fromVillage = false ){
        
        // Check if there are any villages to go to war with
        if( !empty( $info['peaceInfo']['toWar'] ) ){
            
            // Set war status
            foreach( $info['peaceInfo']['toWar']  as $village ){
                $this->set_db_alliance( $GLOBALS['userdata'][0]['village'] , $village, 2 );
                functions::log_user_action($_SESSION['uid'], "joinWar", $GLOBALS['userdata'][0]['village']."->".$village);
            }
            
            // Add these villages to the war
            $this->warLog_setVillagesToWar( array_merge( array($GLOBALS['userdata'][0]['village']), $info['warInfo']['toWar'] ) );

            // Reset structure points of all villagers
            $this->reset_villagers_SP( array($GLOBALS['userdata'][0]['village']) );

            // If this village is coming to the help of $fromVillage. fromVillage needs to suffer for that
            if( $fromVillage !== false ){

                // Reduce one random updagrade, lose 10% SP
                $structures = array( "shop","hospital","anbu_bonus", "wall_rob","wall_def" );
                $randStructure = $structures[ random_int(0,4) ];

                // Run Query for structure
                $GLOBALS['database']->execute_query("
                        UPDATE `village_structures` 
                        SET `".$randStructure."_level` = `".$randStructure."_level` - 1  
                        WHERE 
                            `name` IN ('".$fromVillage."','".$GLOBALS['userdata'][0]['village']."') AND 
                            `".$randStructure."_level` - 1 > 0
                        LIMIT 2" );
                
                // Run query for points
                $GLOBALS['database']->execute_query("
                        UPDATE `village_structures` 
                        SET `cur_structurePoints` = `cur_structurePoints` * 0.75    
                        WHERE `name` IN ('".$fromVillage."','".$GLOBALS['userdata'][0]['village']."')
                        LIMIT 2" );
                
                // Log the help action
                if( isset($info['peaceInfo']['makePeace'][0]) &&
                    !empty($info['peaceInfo']['makePeace'][0]))
                {
                    functions::log_user_action(
                        $_SESSION['uid'], 
                        "callHelp", 
                        $fromVillage."->".$GLOBALS['userdata'][0]['village'],
                        $fromVillage." and ".$GLOBALS['userdata'][0]['village']." lost 25% structure points and 1 village ".  str_replace("_"," ", $randStructure )." upgrade."
                    );
                }
            }
        }
    }
    
    // Function for removing requests, either based on ID or all from user village
    private function remove_request( $id ){
        $GLOBALS['database']->execute_query("DELETE FROM `alliance_requests` WHERE `id` = '".$id."' LIMIT 1");
    }
    
    // Function for sending a request
    public function send_request( $toVillage, $type ){
        
        // Get information in regard to other villages for this village
        $otherVillages = $this->getOtherVillages( $GLOBALS['userdata'][0]['village'] );
        $info = $this->pick_out_village( $toVillage, $otherVillages );
        
        // See if there are already requests in the db
        $currentRequests = $this->getRequest( "any" , $GLOBALS['userdata'][0]['village'], $toVillage, $type );
        if( $currentRequests == "0 rows" ){
            if( 
                $type == "Alliance" && $this->can_form_alliance( $info, $toVillage ) ||
                $type == "Surrender" && $this->can_surrender( $toVillage ) 
            ){
                // If alliance request, put to war with villages as needed now!
                if( $type == "Alliance" ){
                    
                    // Handle war situations
                    $this->handlePeaceToWarInfo( $info );
                }
                
                // Insert request
                $GLOBALS['database']->execute_query("
                    INSERT INTO `alliance_requests` 
                    ( `user_village` , `opponent_village` , `time` , `type` )
                    VALUES 
                    ( '" . $GLOBALS['userdata'][0]['village'] . "', '" . $toVillage . "', '" . $GLOBALS['user']->load_time . "' , '".$type."');"
                );
                
                $GLOBALS['page']->Message("Your ".$type." request has been sent", "War Panel", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']);
            }  
            else{
                throw new Exception("Your request did not make sense");
            }
        }
        else{
            throw new Exception("This request is already in the database");
        }
    }
    
    /* ===================================
     * Territory challenge functions
     * Functions related to territory challenges
     * =================================== 
     */
    
    // Calculate territory challenge cost
    public function territoryChallengeCost( $territoryCount , $village, $funds ){
        $cost = 0;
        if( $village == "Syndicate" ){
            $perc = 0;
            switch( true ){
                case $territoryCount < 4: $perc = 0.15; break;
                case $territoryCount < 12: $perc = 0.20; break;
                case $territoryCount < 19: $perc = 0.25; break;
                default: $perc = 0.30; break;
            }
            $cost = 2000 + $perc * $funds;
        }
        else{
            $cost = 750 + 250 * ( $territoryCount +1 );
        }
        return ceil($cost);
    }
    
    // Do start the challenge
    public function territory_challenge( $village, $challengeFor ) {

        // Check that the proper values are set
        if ( $challengeFor !== "" ) {

            // Get the village data and the user territories
            $this->village = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `name` = '" . $village . "' LIMIT 1");
            $own_terr = $GLOBALS['database']->fetch_data("SELECT * FROM `locations` WHERE `owner` =  '" . $village . "' AND `identifier` != 'village'");
        
            // Check the cost
            $cost = $this->territoryChallengeCost( count($own_terr) ,  $village , $this->village[0]['points'] );
            if ( $this->village !== "0 rows" && $this->village[0]['points'] > $cost) {

                // Get the territort
                $challen_terr = $GLOBALS['database']->fetch_data("SELECT * FROM `locations` WHERE `owner` != 'Neutral' AND `owner` != '" . $village . "' AND `identifier` != 'village' AND `id` = '".$challengeFor."' LIMIT 1");
                
                // Check the challenge territory
                if ($challen_terr != "0 rows") {
                    
                    // Check the alliance
                    if ($this->useralliance[0][ $challen_terr[0]['owner'] ] !== 1 ) {

                        // Insert challenge
                        $terr_challenge = $GLOBALS['database']->fetch_data("SELECT * FROM `territory_challenge` WHERE `challenged` = '".$village."' OR `challenged` = '".$challen_terr[0]['owner']."' OR
                                                                                                                       `challenger` = '".$village."' OR `challenger` = '".$challen_terr[0]['owner']."' LIMIT 1");
                        if ($terr_challenge == "0 rows") {

                            // Check cooldown
                            $this->setWarHistory();
                            $cooldown = $this->hasJustBeenInWar($village, "startTerritoryBattle", 6*60*60);
                            if( $cooldown == 0 ){


                                $query = "INSERT INTO `territory_challenge` ( `id` , `challenger` , `challenged` , `location`, `start_time`, `chuunin_status`, `jounin_status`, `e_jounin_status`, `chuunin_challenger_starters`, `chuunin_challenged_starters`, `jounin_challenger_starters`, `jounin_challenged_starters`, `e_jounin_challenger_starters`, `e_jounin_challenged_starters` )
                                    VALUES ( '" . str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT) . "', '" . $village . "', '" . $challen_terr[0]['owner'] . "', '" . $challen_terr[0]['name'] . "', '" . ($GLOBALS['user']->load_time + 60*5) . "', 'pre', 'pre', 'pre', '', '', '', '', '', '') ";

                                // Insert challenge
                                $GLOBALS['database']->execute_query($query);


                                $GLOBALS['database']->execute_query("UPDATE `villages` SET `points` = `points` - '".$cost."' WHERE `name` = '" . $village . "' LIMIT 1");

                                $GLOBALS['database']->execute_query("UPDATE `users` SET `notifications` = " . "CONCAT('id:16;duration:none;text:".functions::store_content(" " . $village . " has challenged " . $challen_terr[0]['owner'] . " for " . $challen_terr[0]['name'] . ". The challenge starts in " . $challen_terr[0]['name'] . " in 5 minutes! ").";dismiss:yes;buttons:none;select:none;//',`notifications`)" . " WHERE `village` = '" . $village . "' OR `village` = '" . $challen_terr[0]['owner'] . "' ");

                                // Log action
                                functions::log_user_action($_SESSION['uid'], "startTerritoryBattle", $village."->".$challen_terr[0]['owner']);

                                // Message to user
                                $GLOBALS['page']->Message(' You have challenged ' . $challen_terr[0]['owner'] . ' for the territory <i>"' . $challen_terr[0]['name'] . '"</i>.', "War Panel", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']);

                            } else {
                                $release = functions::convert_time($cooldown, 'terrCooldown', 'false');
                                throw new Exception('You have to wait before you can challenge another territory: '.$release);
                            }
                        }
                        else{
                            throw new Exception('Another challenge is already in progress between ' . $terr_challenge[0]['challenger'] . ' and ' . $terr_challenge[0]['challenged'] . '. Please wait for it to complete.');
                        }
                    } else {
                        throw new Exception("You cannot challenge allied villages.");
                    }
                } else {
                    throw new Exception("You cannot challenge for this territory.");
                }
            } else {
                throw new Exception("You do not have the required amount of points.");
            }
        } else {
            throw new Exception("No information was entered.");
        }
    }
    
    // Functions for managing the log_wars table
    // This table is used as an additional log-table for
    // previous wars
    
    // Actively include villages in the warlog
    private function warLog_setVillagesToWar( $villages ){
        $activeWar = $this->warLog_getActiveWarID();
        if( $activeWar ){
            $this->warLog_updateParticipants($activeWar, $villages);
        }
        else{
            $this->warLog_insertNew($villages);
        }
    }
    
    private function warLog_insertNew( $villages ){
        $GLOBALS['database']->execute_query("
            INSERT INTO `log_wars` 
            ( `participants` , `start_time` )
            VALUES 
            ( '" . implode(",",$villages) . "', '" . $GLOBALS['user']->load_time . "');"
        );
        return $GLOBALS['database']->get_inserted_id();
    }
    
    private function warLog_updateParticipants( $warID, $villages ){
        $GLOBALS['database']->execute_query("
            UPDATE `log_wars` 
            SET `participants` = CONCAT(`participants`, ',".implode(",",$villages)."')
            WHERE `id` = '".$warID."' AND `status` = 'active'
            LIMIT 1");
    }
    
    private function warLog_getActiveWarID( $village = false ){
        
        // Get the warlog
        if( !isset($this->warLog) ){
            $this->warLog = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `log_wars`
                WHERE `status` = 'active'
                LIMIT 1
            ");
        }
        
        // Check if exists
        if( $this->warLog !== "0 rows" ){            
            if( $village == false ){
                // Row was found, no village check neccesary
                return true;
            }
            else{
                if( stristr( $this->warLog[0]['participants'], $village ) ){
                    // Row was found, village check passed
                    return $this->warLog[0]['id'];
                }
                else{
                    // Row was found, village check not passed
                    return false;
                }
            }
        }
        
        // No row found
        return false;
    }
    
    private function warLog_fixActivity(){
        $warExists = false;
        foreach( $this->allAlliances as $alliance ){
            if( $alliance['name'] !== "Syndicate" && $this->inWar( $alliance ) ){
                $warExists = true;
            }
        }
        if( $warExists == false ){
            $GLOBALS['database']->execute_query("
                UPDATE `log_wars` 
                SET `status` = 'ended'
            ");
        }
    }

    
}