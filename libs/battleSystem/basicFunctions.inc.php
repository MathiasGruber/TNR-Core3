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

    class basicFunctions {

        ////////////////////////////////
        ///     GET FUNCTIONS        ///
        ////////////////////////////////

        // Get AI intelligence type
        protected function ai_getIntelligenceType($aiside, $aiid){
            $intelligenceType = "random";
            if( isset($this->{$aiside}['data']['' . $aiid . '']['intelligenceType']) ){
                $intelligenceType = $this->{$aiside}['data']['' . $aiid . '']['intelligenceType'];
            }
            return $intelligenceType;
        }

        // Is the AI supposed to be learning
        protected function ai_isLiveLearning($aiside, $aiid){
            if( isset($this->{$aiside}['data']['' . $aiid . '']['liveLearning']) &&
                $this->{$aiside}['data']['' . $aiid . '']['liveLearning'] == "active"){
                return true;
            }
            return false;
        }

        // Is the AI saving its battle records
        protected function ai_isStoringData($aiside, $aiid){
            if( isset($this->{$aiside}['data']['' . $aiid . '']['storeData']) &&
                $this->{$aiside}['data']['' . $aiid . '']['storeData'] == "active"){
                return true;
            }
            return false;
        }

        // Check if ai has a battle history
        protected function ai_hasBattleHistory($aiside, $aiid){
            return isset($this->{$aiside}['data']['' . $aiid . '']['ai_history']) && !empty($this->{$aiside}['data']['' . $aiid . '']['ai_history']);
        }

        // Get his/her based on gender
        public function getHisHer( $gender ){
            switch(strtolower($gender)) {
                case("male"): return "his"; break;
                case("female"): return "her"; break;
                default: return "its"; break;
            }
        }

        // Get the other side, e.g. "user" <=> "opponent"
        public function get_other_side($side) {
            switch($side) {
                case("user"): return "opponent"; break;
                case("opponent"): return "user"; break;
                case("attacker"): return "target"; break;
                case("target"): return "attacker"; break;
                default: return $side; break;
            }
        }

        // Get user specialization
        public function get_user_specialization($side, $uid) {
            if (isset($this->{$side}['data'][$uid]['specialization'])) {
                if($this->{$side}['data'][$uid]['specialization'] !== "0:0") {
                    // Set the specialization
                    $specialization = explode(":", $this->{$side}['data'][$uid]['specialization']);
                    $returnData = array('ttype' => $specialization[0]);
                    switch ($specialization[0]) {
                        case "T": $returnData['att_type'] = "tai"; break;
                        case "N": $returnData['att_type'] = "nin"; break;
                        case "G": $returnData['att_type'] = "gen"; break;
                        case "W": $returnData['att_type'] = "weap"; break;
                    }
                    return $returnData;
                }
            }
            return false;
        }

        ////////////////////////////////
        ///     SET FUNCTIONS        ///
        ////////////////////////////////

        // Set special element number for user
        public function setSpecialElementalMastery( $side, $uid ){
            $elements = new Elements();

            // If ai, set to max
            if( $this->is_user_ai($side, $uid) ){
                $this->{$side}['data'][ $uid ]['element_mastery_1'] = 200000;
                $this->{$side}['data'][ $uid ]['element_mastery_2'] = 200000;
                $this->{$side}['data'][ $uid ]['element_affinity_special'] = "ai";
                $this->{$side}['data'][ $uid ]['element_affinity_1'] = "ai";
                $this->{$side}['data'][ $uid ]['element_affinity_2'] = "ai";
            }

            // Get special element mastery
            $this->{$side}['data'][ $uid ]['special_element_mastery'] = $elements->getUserElementMastery(3);
        }

        // Get the %-column, see http://www.theninja-forum.com/showthread.php?63743-Core-3-To-Do-List&p=1814439&viewfull=1#post1814439
        // Returns a bool on whether action should be taken
        // some actions may have ranks attached, such as e.g. using a Chuunin jutsu at a higher rank
        public function getMasteryPercentage( $side, $uid, $element, $check, $actionRank ){

            // Set special value if not already set
            if( !isset( $this->{$side}['data'][ $uid ]['special_element_mastery'] ) ){
                $this->setSpecialElementalMastery($side, $uid);
            }

            // For ai, set the element to ai
            if( $this->is_user_ai($side, $uid) ){
                $element = "ai";
                return Elements::checkMasteryBonusAi($this->{$side}['data'][ $uid ], $element, $check, $actionRank);
            }
            else
            {
                // Return whether bonus is available or not
                $elements = new Elements($uid);
                return $elements->checkMasteryBonus($element, $check, $actionRank);
            }
        }

        /*
        *  Some bloodline/jutsu passive affect e.g. the damage by adding/deducting a percentage of it.
        *  To prevent such effects from stacking, we use these two functions to save the original
        *  damage, and do modifications based on that instead
        */
       protected function setOriginalDamage( $side, $identifier = "damage", $userID, $targetID ){

           // Save the original damage if it's not saved yet
           if( !isset($this->{$side}['actionInfo'][ $userID ][ $identifier."Original" ][ $targetID ] ) ){
               // echo "\nSetting original damage: ".$this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ];
               $this->{$side}['actionInfo'][ $userID ][ $identifier."Original" ][ $targetID ] = $this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ];
           }

           // Always set active
           $this->{$side}['actionInfo'][ $userID ][ $identifier."Active" ][ $targetID ] = $this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ];

           // Set the damage (i.e. the one calculations are being run on) to the original
           $this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ] = $this->{$side}['actionInfo'][ $userID ][ $identifier."Original" ][ $targetID ];

       }

       protected function adjustOriginalDamage( $side, $identifier = "damage", $userID, $targetID ){

           // Only perform if original was set
           if( isset($this->{$side}['actionInfo'][ $userID ][ $identifier."Original" ][ $targetID ]) ){

               // Get the difference between the calculated value and the original
               $diff = $this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ] - $this->{$side}['actionInfo'][ $userID ][ $identifier."Original" ][ $targetID ];

               // Increase the active value (i.e. the one internal to this function for keeping track of how much damage has been dealt/reduced/changed)
               $this->{$side}['actionInfo'][ $userID ][ $identifier."Active" ][ $targetID ] += $diff;

               // Set the value returned by the system
               $this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ] = round($this->{$side}['actionInfo'][ $userID ][ $identifier."Active" ][ $targetID ],2);

               // echo "\nAdjusted damage by ".$diff.": ".$this->{$side}['actionInfo'][ $userID ][ $identifier ][ $targetID ];
           }
       }

       protected function setOriginalValue( $side, $identifier = "healed", $userID , $userVar = "actionInfo"){

           // Debug
           // echo"<br>".$userID." -  Start ".$identifier." value: ".$this->{$side}[$userVar][ $userID ][ $identifier ];

           // Save the original value if it's not saved yet
           if( !isset($this->{$side}[$userVar][ $userID ][ $identifier."Original" ] ) ){
               $this->{$side}[$userVar][ $userID ][ $identifier."Original" ] = $this->{$side}[$userVar][ $userID ][ $identifier ];
           }

           // Always set the active to what it was
           $this->{$side}[$userVar][ $userID ][ $identifier."Active" ] = $this->{$side}[$userVar][ $userID ][ $identifier ];

           // Set the damage (i.e. the one calculations are being run on) to the original
           $this->{$side}[$userVar][ $userID ][ $identifier ] = $this->{$side}[$userVar][ $userID ][ $identifier."Original" ];

           // Debug
           // echo "<br>".$userID." - Setting original value ".$identifier." to: ".$this->{$side}[$userVar][ $userID ][ $identifier."Original" ];

       }

       protected function adjustOriginalValue( $side, $identifier = "healed", $userID , $userVar = "actionInfo"){

           // Only perform if original was set
           if( isset($this->{$side}[$userVar][ $userID ][ $identifier."Original" ]) ){

               // Get the difference between the calculated value and the original
               $diff = $this->{$side}[$userVar][ $userID ][ $identifier ] - $this->{$side}[$userVar][ $userID ][ $identifier."Original" ];

               // Increase the active value (i.e. the one internal to this function for keeping track of how much damage has been dealt/reduced/changed)
               $this->{$side}[$userVar][ $userID ][ $identifier."Active" ] += $diff;

               // Set the value returned by the system
               $this->{$side}[$userVar][ $userID ][ $identifier ] = round($this->{$side}[$userVar][ $userID ][ $identifier."Active" ],2);

               // Debug
               // echo "<br>".$userID." - Adjusting value ".$identifier." by ".$diff." to: ".$this->{$side}[$userVar][ $userID ][ $identifier ];

           }
       }

        // Adjust damage based on elemental mastery
        public function adjustElementalDamage($side, $uid, $damage, $element) {
            //echo"warning elemental damage adjustment is dissabled.";
            /*
            // Not if AI
            if($this->is_user_ai($side, $uid)) { return $damage; }

            if(empty($element) || $element === "none") { return $damage; }

            // Re-confirm that the user has the element
            $userElements = $this->get_user_elements($side, $uid);

            if(!$this->is_element_good($userElements, $element)) { return 0; }

            // Figure out if element should be affected by primary or secondary master
            if($this->{$side}['data'][$uid]['elemental_master_1'] !== $element) {
                if($this->{$side}['data'][$uid]['elemental_master_special'] !== $element) {
                    if($this->{$side}['data'][$uid]['elemental_master_2'] === $element) { $type = "secondary"; }
                }
                else { $type = "primary"; }
            }
            else { $type = "primary"; }

            // If primary or secondary mastery is used, then adjust
            if(isset($type)) {
                // Reduction percentage
                $rankID = $this->{$side}['data'][$uid]['rank_id'];
                $perc = 0.5 + 0.5 * ($this->{$side}['data'][$uid][$type.'_element_mastery'] / Data::${'GEN_MAX_'.$rankID});

                // Adjust damage
                $damage = ceil($damage * $perc);
            } */
            return $damage;
        }

        // Used to set a message for the user action. This function only allows for a single user message, and
        // will throw an exception if it's called twice, or if a user message is already set. This function is
        // used e.g. to give "error messages" like "no more chakra" to the user.
        public function setUserActionInfo($side, $uid, $message) {
            // Check if Message is Being Overwritten
            if(isset($this->{$side}['actionInfo'][$uid]['message'])) {
                if(!empty($this->{$side}['actionInfo'][$uid]['message'])) {
                     throw new Exception('Trying to redefine user message. This means something is being overwritten.
                        Previous message: '.$this->{$side}['actionInfo'][$uid]['message'].". New message: ".$message);
                }
            }
            // Set Message
            if(isset($message) && !empty($message)) {
                $this->{$side}['actionInfo'][$uid]['message'] = $message;
            }

            // Set description message to nothing
            $this->{$side}['actionInfo'][$uid]['description'] = "";
        }

        // Set user flee status
        public function set_user_fleeing($side, $uid, $fleeStatus) {
            $this->{$side}['actionInfo'][$uid]['flee'] = $fleeStatus;
        }

        // Try to flee, given a specific percentage chance
        public function try_fleeing($side, $uid, $chance) {

            // Figure out if "his" or "her"
            $useritem = $this->getHisHer($this->{$side}['data'][$uid]['gender']);

            // Get the other side
            $opponentSide = $this->get_other_side($side);

            //  Variables for checking the stun/action status of the other side. Loop through the not-fleers
            $allInactive = 1;
            foreach ($this->{$opponentSide}['ids'] as $id) {
                // Check if OK
                if($this->has_submitted_action($opponentSide, $id) || $this->is_user_ai($opponentSide, $id)) {
                    if(!$this->is_stunned($opponentSide, $id)) {
                        if(!$this->is_user_fleeing($opponentSide, $id) ) {
                            if( !$this->is_user_action_flee($opponentSide, $id) ){
                                if( $this->is_user_alive($opponentSide, $id) ){
                                    $allInactive = 0;
                                }
                            }
                        }
                    }
                }
            }

            // Check if all opponents are also fleeing (actively trying to get out of battle)
            $allFleeing = 1;
            foreach ($this->{$opponentSide}['ids'] as $id) {
                if(!$this->is_user_fleeing($opponentSide, $id) ) {
                    if( !$this->is_user_action_flee($opponentSide, $id) ){
                        $allFleeing = 0;
                    }
                }
            }

            /*
            echo " <pre />Side: ".$opponentSide.
                 "\nID: ".$id.
                 "\n Chance: ".$chance.
                 "\nAll Inactive: ".$allInactive.
                 "\nOpponent Submitted Action:". $this->has_submitted_action($opponentSide, $id).
                 "\nIs Stunned: ".$this->is_stunned($opponentSide, $id).
                 "\nIs Fleeing: ".$this->is_user_fleeing($opponentSide, $id).
                 "\n\n";
                die("Terr Testing, don't report");*/

            // Check if the user is flee-locked - if he is, then he can't run
            if($this->is_user_fleeLocked($side, $uid)) {

                // ... unless everyone else is trying to flee as well
                if( $allFleeing == 0 ){
                    $this->set_user_fleeing($side, $uid, false);
                    return;
                }
            }

            // Finally, if all opponents were inactive, then allow fleeting
            if ($allInactive !== 0 ) {
                $this->set_user_fleeing($side, $uid, true);
                return;
            }

            if (random_int(0, 100) <= $chance) { $this->set_user_fleeing($side, $uid, true); }
            else { $this->set_user_fleeing($side, $uid, false); }



        }

        // Set user stun status
        public function set_user_stunned($side, $uid, $rounds, $stunStatus) {
            $this->{$side}['actionInfo'][ $uid ]['stun'] = $stunStatus;
            $this->{$side}['actionInfo'][ $uid ]['stunrounds'] = $rounds;
        }

        // Set stun resist for the user
        public function set_user_stunResist($side, $uid, $rounds, $stunStatus) {
            $this->{$side}['actionInfo'][$uid]['stun_resist'] = $stunStatus;
            $this->{$side}['actionInfo'][$uid]['stunresistrounds'] = $rounds;
        }

        // Set flee resist for the user
        public function set_user_fleeLock($side, $uid, $rounds, $stunStatus) {
            $this->{$side}['actionInfo'][$uid]['flee_lock'] = $stunStatus;
            $this->{$side}['actionInfo'][$uid]['fleeresistrounds'] = $rounds;
        }

        // Set gains max
        public function setGainCaps($side, $uid) {
            // Locally save rankID
            $rankID = $this->{$side}['data'][$uid]['rank_id'];

            // Array for the fixes
            $fixArray = array(
                array("gainName" => "health_gain", "cap" => Data::${"MAX_HP_".$rankID}, "maxValueColumn" => "max_health"),
                array("gainName" => "chakra_gain", "cap" => Data::${"MAX_".$rankID}, "maxValueColumn" => "max_cha"),
                array("gainName" => "stamina_gain", "cap" => Data::${"MAX_".$rankID}, "maxValueColumn" => "max_sta"),
                array("gainName" => "strength_gain", "cap" => Data::${"GEN_MAX_".$rankID}, "maxValueColumn" => "strength"),
                array("gainName" => "intelligence_gain", "cap" => Data::${"GEN_MAX_".$rankID}, "maxValueColumn" => "intelligence"),
                array("gainName" => "willpower_gain", "cap" => Data::${"GEN_MAX_".$rankID}, "maxValueColumn" => "willpower"),
                array("gainName" => "speed_gain", "cap" => Data::${"GEN_MAX_".$rankID}, "maxValueColumn" => "speed")
            );

            // Run the fixes
            foreach($fixArray as $entry) {
                $max_gain = $this->{$side}['data'][$uid][$entry['maxValueColumn']] + $this->{$side}['summary'][$uid][$entry['gainName']];
                if($max_gain > $entry['cap']) {
                    $newValue = $entry['cap'] - $this->{$side}['data'][$uid][$entry['maxValueColumn']];
                    if($newValue < $this->{$side}['summary'][$uid][$entry['gainName']]) {
                        $this->{$side}['summary'][$uid][$entry['gainName']] = $newValue;
                    }
                }
            }
        }

        // Return a list of user elements
        public function get_user_elements($side, $uid) {
            // Create array with elements
            $elements = new Elements($uid);
            $availableElements = $elements->getUserElements();
            $availableElements[] = 'none';
            // Return
            return (isset($availableElements) ? $availableElements : array());
        }

        // If user jutsus haven't already been loaded, do it.
        public function set_user_jutsus($side, $uid) {

            // Don't set it twice
            if(isset($this->{$side}['jutsus'][$uid])) { return; }

            // Get user elements
            $availableElements = $this->get_user_elements($side, $uid);

            // Show jutsus with those elements
            $jutsus = $GLOBALS['database']->fetch_data("
                SELECT * FROM `users_jutsu`
                INNER JOIN `jutsu` ON (`jutsu`.`id` = `users_jutsu`.`jid` AND `jutsu`.`element` IN('".implode("', '", $availableElements)."'))
                WHERE
                    `users_jutsu`.`uid` = ".$uid." AND
                    locate(`users_statistics`.`taggedGroup`, `users_jutsu`.`tagged` ) > 0
                ORDER BY `jutsu`.`name`");


            // Abandon if nothing was found
            if($jutsus === "0 rows") { return; }

            // Go through all found jutsus
            foreach($jutsus as $jutsu) {

                // If this is a village jutsu, only add to user array if user is actually in the village
                if(empty($jutsu['village']) || $this->{$side}['data'][$uid]['village'] === $jutsu['village']) {

                    // If this is a clan jutsu, only add to user array if user is actually in the clan
                    if( $jutsu['jutsu_type'] !== "clan" ||
                        (isset($this->{$side}['data'][$uid]['clanJutsu']) && $this->{$side}['data'][$uid]['clanJutsu'] == $jutsu['id'])
                    ){
                        // Check the start/end dates
                        if( functions::checkStartEndDates($jutsu) ){

                            // Set the cooldown & experience / level for this jutsu depending on its type
                            $jutsu = $this->set_jutsu_extraData($jutsu, $side, $uid);

                            // Save in the user array
                            $this->{$side}['jutsus'][$uid][$jutsu['id']] = $jutsu;

                        }
                    }
                }
            }
        }

        // Add a jutsu to a users jutsu-array (usufull for e.g. AI)
        public function add_user_jutsus($side, $uid, $jid) {
            if($this->know_jutsu($jid, $uid, $side)) { return; }

            // Set jutsu details not loaded from users_jutsu yet
            $jutsu = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `jutsu`.`id` = ".$jid." LIMIT 1");

            if($jutsu === "0 rows") { return ; }

            $jutsu[0]['level'] = 1;
            $jutsu[0]['exp'] = $jutsu[0]['times_used'] = 0;

            // Set the cooldown & experience / level for this jutsu depending on its type
            $jutsu[0] = $this->set_jutsu_extraData($jutsu[0], $side, $uid);

            // Save in user array
            $this->{$side}['jutsus'][$uid][$jutsu[0]['id']] = $jutsu[0];
            return $jutsu[0];
        }

        // Set the cooldown & experience data for a given jutsu and return it
        public function set_jutsu_extraData( $jutsu, $side, $uid ){

            // Define cooldown & expPerLvl
            switch(true) {
                case($jutsu['clan'] !== ""): $return = array(3, 1500); break;
                case($jutsu['village'] !== ""): $return = array(5, 2000); break;
                case($jutsu['bloodline'] !== ""): $return = array(3, 1500); break;
                case($jutsu['jutsu_type'] === "special"): $return = array(4, 2000); break;
                case($jutsu['jutsu_type'] === "loyalty"): $return = array(4, 2000); break;
                case($jutsu['jutsu_type'] === "forbidden"): $return = array(5, 5000); break;
                default: $return = array(2, 1000); break;
            }

            // Update the jutsu & return it
            $jutsu['iniCooldown'] = $return[0] + 1;
            $jutsu['curCooldown'] = 0;
            $jutsu['expPerLvl'] = $return[1];

            // If AI, set specialization according to jutsu type
            if($this->is_user_ai($side, $uid)) {
                switch($jutsu['attack_type']) {
                    case "ninjutsu": $this->{$side}['data'][$uid]['specialization'] = "N:1"; break;
                    case "taijutsu": $this->{$side}['data'][$uid]['specialization'] = "T:1"; break;
                    case "genjutsu": $this->{$side}['data'][$uid]['specialization'] = "G:1"; break;
                    case "weapon": $this->{$side}['data'][$uid]['specialization'] = "W:1"; break;
                    case "highest": $this->{$side}['data'][$uid]['specialization'] = "N:1"; break;
                }
            }

            // Split out jutsus based on specialization
            if(isset($this->{$side}['data'][ $uid ]['specialization'])) {
                $jutsu = jutsuBasicFunctions::fixUpJutsuData($jutsu, $this->{$side}['data'][$uid]['specialization']);
            }

            // Return the jutsu
            return $jutsu;
        }

        // If user items haven't already been loaded, do it.
        public function set_user_items($side, $uid) {
            if(isset($this->{$side}['items'][$uid])) { return; }
            $rankid = $this->{$side}['data'][$uid]['rank_id'];

            $items = $GLOBALS['database']->fetch_data("
                SELECT
                    `users_inventory`.*,
                    `users_inventory`.`id` as `inv_id`,
                    `items`.`id`, `inventorySpace`, `name`, `price`, `in_shop`,
                    `type`, `armor_types`, `weapon_classifications`,
                    `herb_location`, `use`, `use2`, `strength`, `element`,
                    `required_rank`, `stack_size`,
                    `consumable`, `durability`, `infinity_durability`,
                    `max_stacks`, `item_level`, `tradeable`, `global_trade`,
                    `grand_trade`, `village_restriction`, `max_uses`,
                    `event_item`, `equipable`, `craftable`, `craft_stack`,
                    `repairable`, `craft_recipe`, `processed_results`,
                    `professionRestriction`, `profession_level`,
                    `craftProcessMinutes`
                FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `items`.`required_rank` <= ".$rankid."  AND (`items`.`type` = 'item' OR
                        (`items`.`type` = 'weapon' AND `users_inventory`.`equipped` = 'yes') OR (`items`.`type` = 'armor' AND `users_inventory`.`equipped` = 'yes')))
                WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`stack` > 0 AND `users_inventory`.`durabilityPoints` > 0
                    AND `users_inventory`.`trading` IS NULL ORDER BY `items`.`type`");

            if($items !== "0 rows") {
                foreach($items as $item) {
                    $this->{$side}['items'][$uid][$item['inv_id']] = $item;
                    $this->{$side}['items'][$uid][$item['inv_id']]['uses'] = $this->{$side}['items'][$uid][$item['inv_id']]['durabilityDamage'] = 0;
                }
            }
        }

        // Set the items of an AI
        public function set_ai_items($side, $uid) {
            $itemIDs = str_replace(":", ",", str_replace(";", ",", $this->{$side}['data'][$uid]['itemList']));
            if($itemIDs === "") { return; }

            $items = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `items`.`id` IN (".$itemIDs.")");
            if($items === "0 rows") { return; }

            foreach($items as $item) {
                $this->{$side}['items'][$uid][$item['id']] = $item;
                $this->{$side}['items'][$uid][$item['id']]['inv_id'] = $item['id'];
                $this->{$side}['items'][$uid][$item['id']]['uses'] = $this->{$side}['items'][$uid][$item['id']]['durabilityDamage'] = 0;
                $this->{$side}['items'][$uid][$item['id']]['stack'] = $item['stack_size'];
                $this->{$side}['items'][$uid][$item['id']]['durabilityPoints'] = $this->{$side}['items'][$uid][$item['id']]['durability'];
            }
        }

        // Add a item to a users jutsu-array (usufull for e.g. AI)
        public function add_user_item($side, $uid, $iid) {
            if($this->have_item($iid, $uid, $side)) { return; }

            $item = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `items`.`id` = ".$iid." LIMIT 1");
            $item[0]['inv_id'] = $iid;
            $item[0]['uses'] = $item[0]['durabilityDamage'] = 0;
            $item[0]['stack'] = $item[0]['stack_size'];
            $item[0]['durabilityPoints'] = $item[0]['durability'];

            $this->{$side}['items'][$uid][$item[0]['id']] = $item[0];
            return $item[0];
        }

        // Reduce the durability of random equipped armor for user by a random amount
        protected function damageArmorDurability($side, $uid, $min, $max) {

            // Calculate the damage
            $damage = random_int($min, $max);
            if($damage <= 0) { return; }

            // Get random equipped armor part
            if(isset($this->{$side}['items'][$uid])) {
                if($this->{$side}['items'][$uid] !== "0 rows") {
                    foreach($this->{$side}['items'][$uid] as $itemID => $itemData) {
                        if($itemData['type'] === "armor") {
                            if($itemData['equipped'] === "yes") {
                                $partIDs[] = $itemID;
                            }
                        }
                    }
                }
            }

            // Check if parts were found
            if(!isset($partIDs)) { return; }

            // Get random part
            shuffle($partIDs);
            $randPartID = $partIDs[0];

            // Damage that part
            if (!$this->{$side}['items'][$uid][$randPartID]['infinity_durability']) {
                $this->{$side}['items'][$uid][$randPartID]['durabilityDamage'] += $damage;
                $this->{$side}['items'][$uid][$randPartID]['durabilityPoints'] -= $damage;
            }
        }

        // This is for setting messages like "X's Y have been (decreased|increased) for the next Z rounds"
        // Message is logged under $side and $id.
        // Effects: decreased, decreasing, increase, increasing
        protected function setLogMessage($side, $uid, $dataArray) {
            // If this one is empty, then set it
            if(empty($this->{$side}['actionInfo'][$uid]['statusEffects'])) { $this->{$side}['actionInfo'][$uid]['statusEffects'] = array(); }

            // Add this message
            $this->{$side}['actionInfo'][$uid]['statusEffects'][] = $dataArray;
        }

        // This is for setting messages full messages
        // Used for setting simple messages like "All status effects cleared" for $side with $uid
        protected function setLogTextMessage($side, $uid, $cssClass, $message) {
            // If this one is empty, then set it
            if(empty($this->{$side}['actionInfo'][$uid]['statusTxts'])) { $this->{$side}['actionInfo'][$uid]['statusTxts'] = array(); }

            // Add this message
            $this->{$side}['actionInfo'][$uid]['statusTxts'][] = array($cssClass, $message);
        }


        // Update battle table with new user/opponent data
        // settings => array( user=[true|false], opponent=[true|false], stage=[true|false], log=[true|false] )
        protected function update_battle_playerData($settings) {

            // Create Query : UPDATE `multi_battle`
            $query = "`battle_type` = '".$this->battle[0]['battle_type']."',
                      `removed_userids` = '".$this->battle[0]['removed_userids']."',
                      `last_rsf_update_round` = '".$this->battle[0]['last_rsf_update_round']."',
                      `cfh_user_strengthFactorLimit` = '".$this->get_join_StrengthFactorLimit("user")."',
                      `cfh_opponent_strengthFactorLimit` = '".$this->get_join_StrengthFactorLimit("opponent")."'";

            // Update time if we're on stage 3
            if( $this->stage == 3 ){
                $query .= ", `last_action` = '".$this->timeStamp."' ";
            }

            if( isset($settings['user']) && $settings['user'] == true ){
                $query .= ", `user_data` = '".base64_encode(serialize($this->user))."' ";
                $query .= ", `user_help` = '" . $this->battle[0]['user_help'] . "' ";
                $query .= ", `user_ids` = '|||";
                foreach( $this->user['ids'] as $id ){
                    $query .= $id."|||";
                }
                $query .= "' ";
            }
            if( isset($settings['opponent']) && $settings['opponent'] == true ){
                $query .= ", `opponent_data` = '".base64_encode(serialize($this->opponent))."' ";
                $query .= ", `opponent_help` = '" . $this->battle[0]['opponent_help'] . "' ";
                $query .= ", `opponent_ids` = '|||";
                foreach( $this->opponent['ids'] as $id ){
                    $query .= $id."|||";
                }
                $query .= "' ";
            }
            if( isset($settings['stage']) && $settings['stage'] == true ){
                $query .= ", `stage` = '".$this->battle[0]['stage']."' ";
            }
            if( isset($settings['log']) && $settings['log'] == true ){
                $query .= ", `log` = '".$this->battle[0]['log']."' ";
            }

            // If rankLimits were not set, check if we should set it
            if( $this->battle[0]['rankLimits'] == "" && $this->battle[0]['battle_type'] == "combat" ){
                $rankLimitations = "";
                foreach( array("user","opponent") as $side ){
                    foreach( $this->{$side}['data'] as $id => $user ){
                        if( $this->{$side}['data'][ $id ]['rank_id'] == "5" ){
                            $rankLimitations = "45";
                        }
                        elseif( $this->{$side}['data'][ $id ]['rank_id'] == "3" ){
                            $rankLimitations = "34";
                        }
                    }
                }
                $query .= ", `rankLimits` = '".$rankLimitations."' ";
            }

            // Run the query
            $query = "UPDATE `multi_battle` SET ".$query." WHERE `id` = '" . $this->battle[0]['id'] . "' LIMIT 1";
            $GLOBALS['database']->execute_query($query);

            // Commit now and start new for future queries
            //$this->restartTransaction();
        }

        // In-activate user
        protected function inactivateUser($side, $uid) {
            $this->{$side}["active"][$uid] = "REMOVED";
        }

        // Function for restarting transaction
        protected function restartTransaction(){
            $GLOBALS['database']->transaction_commit();
            $GLOBALS['database']->transaction_start();
        }


        ////////////////////////////////
        ///     CHECK FUNCTIONS      ///
        ////////////////////////////////

        // Is element valid
        protected function is_element_good($elementList, $element) {
            if(!in_array(strtolower($element), $elementList)) {
                if(
                    empty($elementList) ||
                    (strtolower($elementList[0]) !== 'all' && $element !== 'none') ||
                    $element == 'none'
                ){
                    return false;
                }
            }
            return true;
        }

        // Get a random element
        protected function random_element() {
            $jutsu_elements = array('fire', 'earth', 'wind', 'water', 'ice', 'lightning');
            return $jutsu_elements[array_rand($jutsu_elements, 1)];
        }

        // Check if bloodline tag is sealed
        public function is_bloodline_sealed($bloodlineTag) {
            $count = count($bloodlineTag);
            if ($count >= 2) {
                if($bloodlineTag[($count - 2)] === "SEAL") { return true; }
            }
            return false;
        }

         // Checks if a given user is active
        public function is_user_active($side , $uid) {
            if(isset($this->{$side}["active"][$uid])) {
                if($this->{$side}["active"][$uid] === 'REMOVED') { return false; }
            }
            return true;
        }

         // Checks if a given user is active
        public function is_user_alive($side, $uid) {
            if(!isset($this->{$side}["data"][$uid])) {
                if($this->{$side}["data"][$uid]["cur_health"] <= 0) { return false; }
            }
            return true;
        }

        // Checks if a given user is active
        public function is_user_ai($side, $uid) {
            if(isset($this->{$side}["data"][$uid]['is_ai'])) {
                if((int)$this->{$side}["data"][$uid]['is_ai'] === 1) {
                    return true;
                }
            }
            return false;
        }

        // Checks if a given user is outlaw
        public function is_user_outlaw($side, $uid) {
            if(isset($this->{$side}["data"][$uid]['village'])) {
                if($this->{$side}["data"][$uid]['village'] === "Syndicate") { return true; }
            }
            return false;
        }

        // Check how far the battle has been on, and force ends round if neccesary
        public function check_battle_time() {
            if ((int)$this->battle[0]['stage'] === 3) {
                return;
            }
            if(($this->battle[0]['last_action'] + $this->TURN_SWITCH) > $this->timeStamp) {
                return;
            }
            $GLOBALS['database']->execute_query("
                UPDATE `multi_battle`
                SET `multi_battle`.`stage` = 2,
                    `multi_battle`.`last_action` = ".$this->timeStamp."
                WHERE `multi_battle`.`id` = ".$this->battle[0]['id']." AND `multi_battle`.`stage` < '3' LIMIT 1");
            $this->restartTransaction();
            $this->battle[0]['stage'] = 2;
        }

        // Check if user has a stun effect status on him (not neccesarily activated yet)
        public function has_stun_effect($side, $uid) {
            if(isset($this->{$side}['status'][$uid])) {
                foreach($this->{$side}['status'][$uid] as $status) {
                    if($status[0] === "STUN") {
                        if($status[1] > 0) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        // Check if user is stunned
        public function is_stunned($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['stun'])) {
                if($this->{$side}['actionInfo'][$uid]['stun'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_poisonResist($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['prevent_poison'])) {
                if($this->{$side}['actionInfo'][$uid]['prevent_poison'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_preventRecoil($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['prevent_recoil'])) {
                if($this->{$side}['actionInfo'][ $uid ]['prevent_recoil'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_preventSeal($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['prevent_seal'])) {
                if($this->{$side}['actionInfo'][$uid]['prevent_seal'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_preventReflect($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['prevent_reflect'])) {
                if($this->{$side}['actionInfo'][$uid]['prevent_reflect'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_koResist($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['prevent_ko'])) {
                if($this->{$side}['actionInfo'][$uid]['prevent_ko'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_stunResist($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['stun_resist'])) {
                if($this->{$side}['actionInfo'][$uid]['stun_resist'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is flee locked
        public function is_user_fleeLocked($side, $uid) {
            if(isset($this->{$side}['actionInfo'][$uid]['flee_lock'])) {
                if($this->{$side}['actionInfo'][$uid]['flee_lock'] === true) { return true; }
            }
            return false;
        }

        // Check if this user is fleeing
        public function is_user_fleeing($side, $uid) {
            if( isset($this->{$side}['actionInfo'][$uid]['flee']) && $this->{$side}['actionInfo'][$uid]['flee'] === true ){
                return true;
            }
            return false;
        }

        // Check if user action is to flee
        public function is_user_action_flee($side, $uid) {
            if( isset($this->{$side}['action'][$uid]) && stristr($this->{$side}['action'][$uid], ":::FLEE") ){
                return true;
            }
            return false;
        }

        // Check if this user is in a anbu
        public function is_user_anbu($side, $uid) {
            if(isset($this->{$side}['data'][$uid]['anbu'])) {
                if($this->{$side}['data'][$uid]['anbu'] !== '_none') {
                    if($this->{$side}['data'][$uid]['anbu'] !== '_disabled') {
                        if($this->{$side}['data'][$uid]['anbu'] !== '') {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        // Check if this user is anbu leader
        public function is_user_anbuLeader($side, $uid) {
            return (($this->{$side}['data'][$uid]['anbuLeader_uid'] === $this->{$side}['data'][$uid]['id']) ? true : false);
        }

        // Check if this user is in a anbu
        public function is_user_kage($side, $uid) {
            return (($this->{$side}['data'][$uid]['leader'] === $this->{$side}['data'][$uid]['username']) ? true : false);
        }

         // Check if this user is a summoned creature
        public function is_user_summon($side, $uid) {
            if(isset($this->{$side}['data'][$uid]['summonerID'])) {
                if(!empty($this->{$side}['data'][$uid]['summonerID'])) { return true; }
            }
            return false;
        }

        // Check if this user is in a clan
        public function is_user_clan($side, $uid) {
            if(isset($this->{$side}['data'][$uid]['clan'])) {
                if($this->{$side}['data'][$uid]['clan'] !== '_none') {
                    if($this->{$side}['data'][$uid]['clan'] !== '_disabled') {
                        if(!empty($this->{$side}['data'][$uid]['clan'])) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        // Check if this user is clan leader
        public function is_user_clanLeader($side, $uid) {
            return (($this->{$side}['data'][$uid]['clanLeader_uid'] === $this->{$side}['data'][$uid]['id']) ? true : false);
        }

        // Check if two users are allies/neutral/war
        public function is_user_allianceStatus($side1, $uid1, $side2, $uid2, $status) {
            $alliance = $this->{$side1}['alliances'][$uid1];
            $village = $this->{$side2}['data'][$uid2]['village'];
            return (($alliance[$village] === $status) ? true : false);
        }

        // Check if this user is fleeing
        public function is_user_bountyHunter($side, $uid) {
            if(isset($this->{$side}['data'][$uid]['special_occupation'])) {
                if((int)$this->{$side}['data'][$uid]['special_occupation'] === 2) { return true; }
                elseif((int)$this->{$side}['data'][$uid]['special_occupation'] === 3) { return true; }
            }
            return false;
        }

        // Check if this user has been marked for battle removal by reset_user() function.
        // This would be if the user has successfully fled or is below 0 health
        public function is_marked_for_removal($side, $id) {
            return (in_array($id, $this->{$side.'_lostplayers'}));
        }

        // Check if the user has submitted an action
        public function has_submitted_action($side, $uid) {
            if(isset($this->{$side}['action'][$uid])) {
                if($this->{$side}['action'][$uid] !== 'NULL') {
                    if(!empty($this->{$side}['action'][$uid])) {
                        return true;
                    }
                }
            }
            return false;
        }

        /*      Checks if the user knows the jutsu    */
        public function know_jutsu($jutsuID, $userID, $userSide) {
            if(isset($this->{$userSide}['jutsus'][$userID][$jutsuID])) { return $this->{$userSide}['jutsus'][$userID][$jutsuID]; }
            return false;
        }

        /*      Checks if the user has a specific item ID    */
        public function have_item($itemID, $userID, $userSide) {
            if(isset($this->{$userSide}['items'][$userID])) {
                if(is_array($this->{$userSide}['items'][$userID])) {
                    foreach($this->{$userSide}['items'][$userID] as $item) {
                        if($item['id'] === $itemID) {
                            if(
                                !isset($item['uses']) ||
                                $item['stack_size'] == 1 ||
                                $item['stack'] > $item['uses']
                            ) {
                                return $item;
                            }
                        }
                    }
                }
            }
            return false;
        }

        // Check if the user can flee depending on what kinda battle this is
        protected function can_flee_battle($battle_type) {
            // $battle_type != "torn_battle" && $battle_type != "mirror_battle" &&
            switch($battle_type) {
                case('arena'): case('kage'): case('clan'): case('mission'): case('crime'): return false; break;
                default: return true; break;
            }
        }

        // Check if the user can call for help depending on what kinda battle this is
        protected function can_call_help($battle_type) {
            switch($battle_type) {
                case('arena'): case('torn_battle'): case('mirror_battle'): case('spar'): case('kage'): case('clan'):
                case('mission'): case('crime'): return false; break;
                default: return true; break;
            }
        }

        // Check if the user can call for help depending on the relative strengths of the users
        protected function can_user_call_help( $side, $uid ){

            // Get the other side
            $opponentSide = $this->get_other_side($side);

            // Check that we don't exceed the maximum amount of calls for the battl
            if ($this->battle[0]['' . $side . '_help'] < $this->MAX_HELP) {

                // Check if this is the type of battle where you can call for help
                if ($this->can_call_help($this->battle[0]['battle_type'])) {

                    // Check if the user is stunned
                    if ( !$this->is_stunned($side, $uid) ) {

                        // Set costs as 1% of total
                        $chakra_cost = floor($this->{$side}['data'][ $uid ]['max_cha'] * 0.01);
                        $stamina_cost = floor($this->{$side}['data'][ $uid ]['max_sta'] * 0.01);

                        // Check chakra/stamina costs
                        if (
                                $this->{$side}['data'][ $uid ]['cur_cha'] >= $chakra_cost &&
                                $this->{$side}['data'][ $uid ]['cur_sta'] >= $stamina_cost
                        ) {
                            // Only allow call for help depending on RSF
                            $rsf = $this->get_RelativeStrengthFactor($side);

                            // Get the RSF requirement
                            if( $this->round >= $this->battle[0]['last_rsf_update_round']+3 ){
                                $rsfRequirement = 0.88;
                            }  elseif( $this->round >= $this->battle[0]['last_rsf_update_round']+2 ){
                                $rsfRequirement = 0.75;
                            } elseif( $this->round >= $this->battle[0]['last_rsf_update_round']+1 ){
                                $rsfRequirement = 0.65; # 1.54
                            } else{
                                $rsfRequirement = 0.54; # 1.82
                            }

                            // Check the requirements
                            if( $rsf <= $rsfRequirement ){
                                if( !isset($this->{$side}['data'][ $uid ]['summonedID']) || !$this->is_user_active($side, $this->{$side}['data'][ $uid ]['summonedID']))
                                {
                                    // Call for help is allowed
                                    return "true";
                                }
                                else
                                    return "Can not call for help with a summon.";

                            } else {
                                return "RSF must be lower than ".$rsfRequirement;
                            }
                        } else {
                            return "Not enough chakra/stamina!";
                        }
                    }
                    else{
                        return "Can't because stunned.";
                    }
                } else {
                    return "Not in these types of battle.";
                }
            } else {
                return "No more in this battle.";
            }

            // Return empty
            return "";
        }

        // Check if the user ID is in this battle
        protected function id_in_battle($id) {
            if(!in_array($id, $this->user['ids'])) {
                if(!in_array($id, $this->opponent['ids'])) { return false; }
            }
            return true;
        }

        // Return the side (user or opponent) of the user ID
        protected function get_id_side($id) {
            if (in_array($id, $this->user['ids'])) { return "user"; }
            elseif (in_array($id, $this->opponent['ids'])) { return "opponent"; }
            return false;
        }

        // Check if the user is in the map
        protected function inMap($longitude, $latitude) {
            if ($longitude <= 25) {
                if($longitude > 0) {
                    if($latitude <= 20) {
                        if($latitude > 0) { return true; }
                    }
                }
            }
            return false;
        }

        // Check if the user is in the map. Doesn't take any parameters since everyone is assumed to be on the same spot
        protected function inVillage() {
            if (
                (isset($this->user['ids'][0]) && !stristr($this->user['data'][ $this->user['ids'][0] ]['location'], 'village')) ||
                (isset($this->opponent['ids'][0]) && !stristr($this->opponent['data'][ $this->opponent['ids'][0] ]['location'], 'village'))
            ) {
                return false;
            }
            return true;
        }

        ////////////////////////////////
        ///   MANIPULATE FUNCTIONS   ///
        ////////////////////////////////

        // Updates the owner of a battle message
        public function update_logMessage_owner($side, $uid, $searchTerm) {
            // Overwrite owner if set
            if(isset($this->{$side}['actionInfo'][$uid][$searchTerm])) {
                if(!empty($this->{$side}['actionInfo'][$uid][$searchTerm])) { return $this->{$side}['actionInfo'][$uid][$searchTerm]; }
            }
            return $this->{$side}['data'][$uid]["username"];
        }

        // Get types from tag conventions, e.g. T/N/G/W => ...
        public function translate_tag_type($searchTerm) {
            // Search the term
            switch(true) {
                case($searchTerm === 'T'): $type = 'tai'; $stat1 = 'strength'; $stat2 = 'speed'; break;
                case($searchTerm === 'N'): $type = 'nin'; $stat1 = 'intelligence'; $stat2 = 'wisdom'; break;
                case($searchTerm === 'G'): $type = 'gen'; $stat1 = 'wisdom'; $stat2 = 'intelligence'; break;
                case($searchTerm === 'W'): $type = 'weap'; $stat1 = $stat2 = 'strength'; break;
            }

            // Return stuff
            return array($type, $stat1, $stat2);
        }

        // Function to add slashes to array values
        // Usage: array_walk($array, "satitize");
        public function satitize(&$value, $key) { $value = addslashes($value); }

        // Genderize message
        public function genderizeMessage( $message , $gender , $username,  $genderSide ){
            if (strtolower($gender) === 'male') {
                $oppitem = "his";
                $opp1 = 'he';
                $oppgender = 'him';
                $oppself = 'himself';
            } elseif (strtolower($gender) === 'female') {
                $oppitem = "her";
                $opp1 = 'she';
                $oppgender = 'her';
                $oppself = 'herself';
            } else {
                $oppitem = "its";
                $opp1 = 'it';
                $oppgender = 'it';
                $oppself = 'itself';
            }

            $message = str_replace('%'.$genderSide.'item', $oppitem, $message);
            $message = str_replace('%'.$genderSide.'1', $opp1, $message);
            $message = str_replace('%'.$genderSide.'gender', $oppgender, $message);
            $message = str_replace('%'.$genderSide.'gender', $oppgender, $message);
            $message = str_replace('%'.$genderSide.'self', $oppself, $message);
            $message = str_replace('%'.$genderSide, $username , $message);

            return $message;
        }

        // Create award array
        public function create_user_arrays($side, $uid) {

            // Rewards Array. Order is important for the way they are ordered in battle summary page.
            $this->{$side}['summary'][$uid] = array(
                "status" => "",
                "battle_conclusion" => "",
                "ryo_gain" => 0,
                "health_gain" => 0,
                "chakra_gain" => 0,
                "stamina_gain" => 0,
                "strength_gain" => 0,
                "intelligence_gain" => 0,
                "willpower_gain" => 0,
                "speed_gain" => 0,
                "exp_gain" => 0,
                "pvp_streak" => 0,
                "bounty" => 0,
                "bounty_experience" => 0,
                "warActivity" => 0, // Points measuring performance in war
                "clanActivity" => 0, // Points measuring performance in clan
                "structure" => 0, // Destroying structure points
                "hstructure" => 0, // Healing structure points
                "opposing" => 0, // Members of other villages killed
                "allied" => 0, // Members of allied villages killed
                "syndicate" => 0, // Members of the syndicate killed
                "vassal" => "", // Killed on behalf of other village
                "ownFaction" => 0, // Slaying a member of own village/syndicate
                "squadA" => 0, // ANBU assult points earned
                "squadD" => 0, // ANBU defence points earned
                "clanpoints" => 0, // clan points earned
                "pvp" => 0, // PVP points earned
                "items" => array(),
                "itemLosses" => array(),
                "mission" => "",
                "end_status" => "",
                "turnOutlaw" => 0,
                "kage" => 0,
                "clanLeader" => 0,
                "AI_fled" => 0,
                "PVP_fled" => 0,
                "AI_lost" => 0,
                "PVP_lost" => 0,
                "AI_won" => 0,
                "PVP_won" => 0,
                "next_battle" => false,
                "respectLoss" => 0,
                "battle_rounds" => 0 // The number of rounds this user has been in the battle
            );

            // Lost reputation points array
            $this->{$side}['data'][$uid]['repLoss'] = array();
            foreach(Data::$VILLAGES as $val) { $this->{$side}['data'][$uid]['repLoss'] += array($val => 0); }

            // Alliance information for this user
            $alliance = cachefunctions::getAlliance($this->{$side}['data'][$uid]['village']);
            $this->{$side}['alliances'][$uid] = $alliance[0];
            $this->{$side}['alliances'][$uid]['syndicate'] = 2;
        }

        // Get strength factor of side
        protected function get_strengthFactor( $side ){
            return $this->{$side."StrengthFactor"};
        }

        // Get session side strength factor
        protected function get_session_strengthFactor(){
            return $this->get_strengthFactor($this->sessionside);
        }

        // Get session other side strength factor
        protected function get_sessionOpponent_strengthFactor(){
            return $this->get_strengthFactor( $this->get_other_side($this->sessionside) );
        }

        // Get relative strength for side
        protected function get_RelativeStrengthFactor( $side ){
            return $this->get_strengthFactor( $side ) / $this->get_strengthFactor( $this->get_other_side($side) );
        }

        // Get session side relative strength factor
        protected function get_session_RelativeStrengthFactor(){
            return $this->get_RelativeStrengthFactor( $this->sessionside );
        }

        // Get the join SF limit
        protected function get_join_StrengthFactorLimit( $side , $next = false ){
            $otherSide = $this->get_other_side($side);
            if( $this->get_strengthFactor($otherSide) > $this->get_strengthFactor($side) ){

                // Determine if we're trying to figure out who can join after another call, or currently
                $curHelp = ( $next == true ) ? $this->battle[0][$side.'_help'] : $this->battle[0][$side.'_help'];

                // The limit scales with the number of times the side has called for help
                //$limit = 1.32;
                //switch( $curHelp ){
                //    case "0": $limit = 1.81; break;
                //    case "1": $limit = 1.53; break;

                $limit = 1.80;
                //if(isset($curHelp))
                //{
                //$limit -= $curHelp * 0.15;
                //}

                // Return the requirement
                return ( $this->get_strengthFactor($otherSide)*$limit - $this->get_strengthFactor($side) ) * $this->get_RelativeStrengthFactor($side);
            }
            else{
                return 0;
            }
        }

        // Elemental list in pretty format
        protected function getElementalText($elementArray) {
            $elements = explode(".", $elementArray);
            $size = count($elements);
            $text = "";
            foreach ($elements as $elem) {
                if ($elem === $elements[$size - 1]) {
                    if($size > 1) { $text .= " and "; }
                }
                elseif (!empty($text)) { $text .= ", "; }
                $text .= $elem;
            }
            $text = strtolower($text);
            if( $text == "all" ){
                $text = "";
            }
            return $text;
        }

        // Stat list in pretty format
        protected function getStatText($statString) {
            $desc = (stristr($statString, "T")) ? "taijutsu" : ""; // Taijutsu
            $desc .= (stristr($statString, "N")) ? (empty($desc) ? "ninjutsu"  // Ninjutsu
                : ("N" === $statString[strlen($statString) - 1] ? " and ninjutsu" : ", ninjutsu")) : "";
            $desc .= (stristr($statString, "G")) ? (empty($desc) ? "genjutsu"  // Genjutsu
                : ("G" === $statString[strlen($statString) - 1] ? " and genjutsu" : ", genjutsu")) : "";
            $desc .= (stristr($statString, "W")) ? (empty($desc) ? "bukijutsu" // Weapon
                : ("W" === $statString[strlen($statString) - 1] ? " and bukijutsu" : ", bukijutsu")) : "";
            return $desc;
        }

        // Create a database string, e.g:
        // `user_id` = 1 OR `user_id` = 2 OR `user_id` = 3
        // `user_id` = 1, `user_id` = 2, `user_id` = 3
        public function idQueryString($idList, $coloumnName, $delimiter, $operator = "=") {
            $string = "";
            for ($i = 0, $size = count($idList); $i < $size; $i++) {
                $column = (is_array($coloumnName)) ? $coloumnName[$i] : $coloumnName;
                $string .= (empty($string)) ? "`".$column."` ".$operator." ".$idList[$i]
                    : " ".$delimiter." `".$column."` ".$operator." ".$idList[$i];
            }
            return $string;
        }

        // Create a database string, e.g:
        // `Konoki`,`Glacier`,`Shroud`
        // `Konoki`:`Glacier`:`Shroud`
        public function listQueryString($list, $delimiter) {
            $string = "";
            foreach($list as $item) { $string .= (empty($string)) ? "`".$item."`" : $delimiter."`".$item."`"; }
            return $string;
        }

        // Delete the active battle
        protected function delete_battle(){
            if(!isset($this->battle[0]['id']) || empty($this->battle[0]['id'])) { throw new Exception("Trying to delete unknown battle"); }
            if($GLOBALS['database']->execute_query("DELETE FROM `multi_battle`
                WHERE `multi_battle`.`id` = ".$this->battle[0]['id']." LIMIT 1") === false) {
                throw new Exception ('Deleting Unknown Battle Failed!');
            }
        }

        // Basic Force Battle End function for a user. Only used in case user is found to be in battle without actually being in battle
        protected function battle_end() {

            // If user in battle, remove him, otherwise leave his status be
            $query = "";
            if($GLOBALS['userdata'][0]['status'] === "combat") {
                $query = "`users`.`status` = 'awake',";
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                $GLOBALS['userdata'][0]['status'] = "awake";
                $GLOBALS['template']->assign('userStatus', 'awake');
            }

            // Update database
            $GLOBALS['database']->execute_query("UPDATE `users`, `users_statistics`
                SET ".$query." `users`.`battle_id` = 0,
                    `users_statistics`.`reinforcements` = 0
                WHERE `users`.`id` = ".$_SESSION['uid']." AND `users_statistics`.`uid` = `users`.`id`");
        }

        // Used by jutsu, bloodline etc to set the $this->[user|opponent|battle] variebles
        public function set_battledata($target, $data) { $this->{$target} = $data; }

        // Sometimes (e.g. simple actions such as chakra attack etc), it's nice with this function
        // to add a large array to the user actionInfo array without overwriting what's already in it.
        public function addActionInfoArrayToUser($side, $uid, $array) {
            if(!empty($array)) {
                foreach($array as $key => $entry) { $this->{$side}['actionInfo'][$uid][$key] = $entry; }
            }
        }
    }
