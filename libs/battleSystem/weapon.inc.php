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

require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

class weapon extends basicFunctions {

    public function weapon ($master, $user, $userid, $target, $targetid, $itemdata) {
        // Save data in this class
        $this->master = $master;

        // Save the attacker in local variables
        $this->attackerSide = $user;
        $this->attackerID = $userid;
        $this->attacker = $this->master->{$user};
        $this->attackerFriends = $this->attacker['ids'];
        $this->attacker_is_ai = $this->master->is_user_ai($user, $userid);

        // Save the target in local variables
        $this->targetSide  = $target;
        $this->targetID = $targetid;
        $this->target = $this->master->{$target};
        $this->targetFriends = $this->target['ids'];

        // Check if the two sides are the same
        $this->isTheSameSide = ($user === $target) ? true : false;
        $this->isTheSameUser = ($userid === $targetid) ? true : false;

        // Save the jutsu data in local variables
        $this->item_data = $itemdata;
        $this->iid = $this->item_data['id'];

        // Call execute weapon function.
        if(isset($this->item_data)) { $this->execute_weapon(); }
        else {
            $this->setUserActionInfo($this->attackerSide, $this->attackerID, $this->{$this->attacker}['data'][$this->attackerID]['username'].
                ' tries and fails to use an unknown action. Item could not be found.');
        }
    }

    /*      Returns data to the master class
     * If target side and attacker side are different, return both. Otherwise only return attacker
     * (which will also be the one updated.
     */
    public function return_data() {
        if($this->isTheSameSide) { $this->master->set_battledata($this->attackerSide, $this->attacker); }
        else {
            $this->master->set_battledata($this->attackerSide, $this->attacker);
            $this->master->set_battledata($this->targetSide, $this->target);
        }
    }


    // The function to execute the weapon functions
    private function execute_weapon() {
        $elements = new Elements($this->attackerID);
        $affinities = $elements->getUserElements();
        // Check element of weapon
        if(!empty($this->item_data['element'])) {
            if( $this->item_data['element'] == $affinities[0] ||
                $this->item_data['element'] == $affinities[1] ||
                $this->item_data['element'] == $affinities[2] )
            {
                // Check that the elemental affinity is OK
                $perc = $this->getMasteryPercentage(
                    "attacker", $this->attackerID,
                    $this->item_data['element'],
                    "WeaponUnlock",
                    $this->item_data['required_rank']
                );
                if( $perc == false || $perc !== 1 ){
                    $this->setUserActionInfo("attacker", $this->attackerID, "<i>".$this->attacker['data'][$this->attackerID]['username'].
                    '</i> tried to use an item, but does not possess the elemental mastery to use it properly.');
                    return false;
                }
            }
            else{
                $this->setUserActionInfo("attacker", $this->attackerID, "<i>".$this->attacker['data'][$this->attackerID]['username'].
                '</i> tried to use an item, but did not have the required elemental affinity.');
                return false;
            }
        }

        // Set uses array
        if(!isset($this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['uses'])) {
            $this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['uses'] = 0;
        }

        // Count the uses of this item
        $this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['uses']++;

        // Check that the user hasn't used the item more than the max times
        if ($this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['uses'] > $this->item_data['max_uses']) {
            $this->setUserActionInfo("attacker", $this->attackerID, "<i>".$this->attacker['data'][$this->attackerID]['username'].
                '</i> tried to use an item which has already been used too much in this battle.');
            return false;
        }

        // Check that the weapon isn't broken
        if($this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['durabilityPoints'] <= 0) {
            $this->setUserActionInfo("attacker", $this->attackerID, "<i>".$this->attacker['data'][$this->attackerID]['username'].
                '</i> tried to use a broken item.');
            return false;
        }

        // Check if it's a weapon
        if($this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['type'] === "weapon") {

            // Check that it's not in AI battle
            if( !in_array($this->master->battle[0]['battle_type'], array("mission","crime","arena","mirror_battle","torn_battle","event","rand","quest") )  ){

                // Calculate durability daamge
                switch($this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['required_rank']) {
                    case 3: $durDamage = random_int(1, 2); break;
                    case 4: $durDamage = random_int(1, 3); break;
                    case 5: $durDamage = random_int(1, 4); break;
                    default: $durDamage = 1; break;
                }

                // Decrease the durability
                if (!$this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['infinity_durability']) {
                    $this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['durabilityDamage'] += $durDamage;
                    $this->attacker['items'][$this->attackerID][$this->item_data['inv_id']]['durabilityPoints'] -= $durDamage;
                }
            }
        }

        // Set his/its/her
        $useritem = $this->master->getHisHer($this->attacker['data'][$this->attackerID]['gender']);

        // Set attack message
        $this->attacker['actionInfo'][$this->attackerID]['message'] = "<i>".$this->attacker['data'][$this->attackerID]['username'].
            ' </i> attacks using '.$useritem.' '.$this->item_data['name'];
        $this->attacker['actionInfo'][$this->attackerID]['description'] = "";
        $this->attacker['actionInfo'][$this->attackerID]['element'] = strtolower($this->item_data['element']);

        // Use 1
        $modifiers = explode(':', $this->item_data['use']);
        $function = $modifiers[0];
        if (method_exists($this, $function)) { $this->$function($modifiers); }
        else {
            // Something went wrong. Call setUserActionInfo with message.
            $this->attacker['actionInfo'][$this->attackerID]['message'] = "<i>".$this->attacker['data'][$this->attackerID]['username'].
                ' </i>: Item data glitched on use-1. Report bug in forum.';
            return false;
        }

        // Use 2. Only if set though
        if (isset($this->item_data['use2']) && $this->item_data['use2'] !== "") {
            $modifiers = explode(':', $this->item_data['use2']);
            $function = $modifiers[0];
            if (method_exists($this, $function)) { $this->$function($modifiers); }
            else {
                // Something went wrong. Call setUserActionInfo with message.
                $this->attacker['actionInfo'][$this->attackerID]['message'] = "<i>".$this->attacker['data'][$this->attackerID]['username'].
                    ' </i>: Item data glitched on use-2. Report bug in forum.';
                return false;
            }
        }
    }

    /*
     * 		Insert a new status effect into the user or opp data.
     * 		Used by all jutsu that induce status effects.
     */
    public function insert_status($side, $targetid, $tag, $postpone = false) {
        // Insert the tag
        $nextindex = count($this->{$side}['status'][$targetid]);
        $this->{$side}['status'][$targetid][$nextindex] = explode(':', $tag);

        // If this effect shouldn't be effective yet, add the POSTPONE effect to the status effect
        if ($postpone === true) {
            $nextTagIndex = count($this->{$side}['status'][$targetid][$nextindex]);
            $this->{$side}['status'][$targetid][$nextindex][$nextTagIndex] = "POSTPONE";
        }
    }

    /*
     *  In case user targeted himself, set target and ID to that
     */
    private function getTargetAndID(){
        return (($this->isTheSameSide) ? array("attacker", $this->attackerID) : array("target", $this->targetID));
    }

    /*
     * 	Weapon effect functions
     * 	Following below are all weapon effect functions
     * 	Function names are identical to the effect tag identifiers in the database
     */

    //	Deal damage
    //	DMG:(T|N|G|W):int
    private function DMG($modifiers) {

        // Determine power modifier from tag
        $power = ($modifiers[2] === 'STR') ? $this->item_data['strength'] : $modifiers[2];

        // Get the types, $modifiers[2] == T|B|G|W
        list($off_1, $stat1, $stat2) = $this->translate_tag_type($modifiers[1]);

        // Calculate damage
        $damage = calc::calc_double_damage(array(
            "user_data" => $this->attacker['data'][$this->attackerID],
            "target_data" => $this->target['data'][$this->targetID],
            "type1" => $off_1,
            "type2" => 'weap',
            "stat1" => 'strength',
            "stat2" => 'speed',
            "power" => $power,
            "scalePower" => 1
        ));

        // Adjust based on elemental mastery
        $damage = $this->adjustElementalDamage("attacker", $this->attackerID, $damage, $this->attacker['actionInfo'][$this->attackerID]['element']);

        // Set the type
        $this->attacker['actionInfo'][$this->attackerID]['type'] = $modifiers[1];

        // Save the damage
        $this->attacker['actionInfo'][$this->attackerID]['damage'][$this->targetID] = $damage;

        // Damage the opponents armor
        $this->damageArmorDurability("target", $this->targetID, 1, 1);

        // Save the targets
        $this->attacker['actionInfo'][ $this->attackerID ]['targetType'] = $this->targetSide;
        $this->attacker['actionInfo'][ $this->attackerID ]['targetIDs'][] = $this->targetID;
    }

    //    Stun opponent
    //    STN:(int):(int):(int)
    private function STN($modifiers) {
        // Get target & ID
        list($stuntarget, $stunID) = $this->getTargetAndID();

        // Check if he has stun resist
        if ($this->is_user_stunResist($stuntarget, $stunID)) {
            $this->{$stuntarget}['actionInfo'][ $stunID ]['stunInfo'] = "failed";
            // Log stun under the attacker rather than under the attacked
            $this->{$stuntarget}['actionInfo'][ $stunID ]['stunLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
            return;
        }

        // Random Number Check
        if (random_int(0, 100) <= (100 - $modifiers[1])) {
            $this->{$stuntarget}['actionInfo'][ $stunID ]['stunInfo'] = "failed";
            // Log stun under the attacker rather than under the attacked
            $this->{$stuntarget}['actionInfo'][ $stunID ]['stunLog'] = $this->attacker['data'][ $this->attackerID ]['username'];
            return;
        }

        // Random Amount of Stun Turns
        $turns = random_int($modifiers[2], $modifiers[3]);

        // If more than one, do stun
        if ($turns <= 0) {
            $this->{$stuntarget}['actionInfo'][$stunID]['stunInfo'] = "failed";
            // Log stun under the attacker rather than under the attacked
            $this->{$stuntarget}['actionInfo'][$stunID]['stunLog'] = $this->attacker['data'][$this->attackerID]['username'];
            return;
        }

        // Set stun tag & stun resist tag
        $this->insert_status($stuntarget, $stunID, 'STUN:'.($turns + 1), true);
        $this->insert_status($stuntarget, $stunID, 'STUNR:'.(3 + ($turns * 2)), true);

        // Mark the user as stunned instantly. Comment to have effect postponed to next round.
        // $this->set_user_stunned( $stuntarget, $stunID, $turns, true );

        // Set stun
        $this->{$stuntarget}['actionInfo'][$stunID]['stunInfo'] = "success";
        $this->{$stuntarget}['actionInfo'][$stunID]['stunrounds'] = $turns;

        // Log stun under the attacker rather than under the attacked
        $this->{$stuntarget}['actionInfo'][$stunID]['stunLog'] = $this->attacker['data'][$this->attackerID]['username'];
    }

    //	Leech life
    //	LCH:(STR|STAT|PERC):(int):(int)
    private function LCH($modifiers) {

        // Get the amount of damage already dealt
        $damage = $this->attacker['actionInfo'][$this->attackerID]['damage'][$this->targetID];

        // Get the percentage to leech
        $result = 0;
        switch( $modifiers[1] ){
            case "STR":
                $perc = $this->item_data['strength'];
                $result = ($perc/100) * $damage;
                break;
            case "STAT":
                $result = random_int($modifiers[2], $modifiers[3]);
                break;
            case "PERC":
                $perc = random_int($modifiers[2], $modifiers[3]);
                $result = ($perc/100) * $damage;
                break;
        }

        // Set the actionInfo Leech data
        if(!isset($this->attacker['actionInfo'][$this->attackerID]['leech'])) {
            $this->attacker['actionInfo'][$this->attackerID]['leech'] = 0;
        }
        $this->attacker['actionInfo'][$this->attackerID]['leech'] += round( $result );

        // Set the rest of the actionINfo data
        $this->attacker['actionInfo'][$this->attackerID]['type'] = 'W';
        $this->attacker['actionInfo'][$this->attackerID]['targetType'] = $this->targetSide;
        $this->attacker['actionInfo'][$this->attackerID]['targetIDs'][] = $this->targetID;
    }

    //  Set "residual damage (DAM)" Status effect.
    //  RDAM:PERC|STAT|TSTA|TINC:minRounds:maxRounds:STAT|DAM:basePowerInt
    //  RDAM:PERC|STAT|TSTA|TINC:1:3:STAT|DAM:10:10.07
    private function RDAM($modifiers) {
        // Figure Out who to hit
        list($residualtarget, $residualID) = $this->getTargetAndID();

        // Random amount of turns
        $rand = random_int($modifiers[2], $modifiers[3]);
        if ($rand > 0) {
            // Get the power
            if ($modifiers[4] === 'STAT') { $power = $modifiers[5]; }
            elseif ($modifiers[4] === 'DAM') {
                $power = ($this->attacker['actionInfo'][$this->attackerID]['damage'][$residualID] / 100) * $modifiers[5];
            }

            // Insert status
            $this->insert_status($residualtarget, $residualID, 'DAM:'.$rand.':'.$modifiers[1].':'.
                strtolower($this->item_data['element']).':'.$power);

            // Set actionInfo variable to inform battle log etc.
            $this->{$residualtarget}['actionInfo'][$residualID]['rdaInfo'] = "success";
            $this->{$residualtarget}['actionInfo'][$residualID]['rdaRounds'] = $rand;

            // Log residual damage under the attacker rather than under the attacked
            $this->{$residualtarget}['actionInfo'][$residualID]['rdaLog'] = $this->attacker['data'][$this->attackerID]['username'];
        }
    }

    // Prevent the opponent from fleeing from battle
    // NFLE:chance:minRounds:maxRounds
    private function NFLE($modifiers) {
        // Figure Out who to prevent from fleeing
        list($fleeRtarget, $fleeID) = $this->getTargetAndID();

        // Random amount of turns
        $turns = random_int($modifiers[2], $modifiers[3]);

        // Check if successful or not
        if (random_int(0, 100) >= $modifiers[1] || $turns <= 0) {
            $this->{$fleeRtarget}['actionInfo'][$fleeID]['fleeRinfo'] = 'failed';

            // Log flee resist under the attacker rather than under the attacked
            $this->{$fleeRtarget}['actionInfo'][$fleeID]['fleeRLog'] = $this->attacker['data'][$this->attackerID]['username'];
            return;
        }

        // Insert status
        $this->insert_status($fleeRtarget, $fleeID, 'FLEE:'.$turns);

        // Update the user array
        $this->set_user_fleeLock($fleeRtarget, $fleeID, $turns, true);

        // Set actionInfo status
        $this->{$fleeRtarget}['actionInfo'][$fleeID]['fleeRinfo'] = 'success';

        // If user is fleeing, stop them
        if($this->is_user_fleeing($fleeRtarget, $fleeID)) { $this->set_user_fleeing($fleeRtarget, $fleeID, false); }

        // Log flee resist under the attacker rather than under the attacked
        $this->{$fleeRtarget}['actionInfo'][$fleeID]['fleeRLog'] = $this->attacker['data'][$this->attackerID]['username'];
    }

    //  Rob opponent ryo (outlaw jutsu FTW)
    //  ROB:chance:PERC|STAT:stealPower
    private function ROB($modifiers) {
        // Figure Out who to hit. Always set to opponent
        list($sealtarget, $statID) = $this->getTargetAndID();

        // Can't target yourself with this one
        if (!$this->isTheSameUser) {
            // Check for success
            if (random_int(1, 100) <= $modifiers[1]) {
                // Not for attacking yourself or poor people
                if ($this->{$sealtarget}['data'][ $statID ]['money'] > 0) {
                    // Calculate stolen amount
                    if ($modifiers[2] === 'PERC') {
                        if ($modifiers[3] > 100) { $modifiers[3] = 100; }
                        $perc = $this->{$sealtarget}['data'][$statID]['money'] / 100;
                        $this->attacker['actionInfo'][$this->attackerID]['stolen'] = floor($perc * $modifiers[3]);
                    }
                    elseif ($modifiers[2] === 'STAT') {
                        $stolen = ($this->{$sealtarget}['data'][$statID]['money'] >= $modifiers[3]) ? $modifiers[3]
                            : $this->{$sealtarget}['data'][$statID]['money'];
                        $this->attacker['actionInfo'][$this->attackerID]['stolen'] = $stolen;
                    }

                    // Reduce money from the target
                    $this->{$sealtarget}['data'][$statID]['money'] -= $this->attacker['actionInfo'][$this->attackerID]['stolen'];

                    // Set actionINfo information for the log
                    $this->attacker['actionInfo'][$this->attackerID]['stolenID'] = $statID;
                }
            }
        }
    }
}