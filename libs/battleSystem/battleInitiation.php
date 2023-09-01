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
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
require_once(Data::$absSvrPath.'/libs/Battle/Battle.php');

    class battleInitiation {

        // A function for setting the person being targeted
        public function setTarget($uid) {
            if (ctype_digit($uid)) { $this->oppId = $uid; }
            else {
                if (!($user = $GLOBALS['database']->fetch_data("SELECT `users`.`id` FROM `users` WHERE `users`.`username` = '".$uid."' LIMIT 1"))) {
                    throw new Exception('An error occurred trying to find target user for battle!');
                }

                if ($user !== "0 rows") { $this->oppId = $user[0]['id']; }
                else { throw new Exception("Could not find this user in the database"); }
            }
        }

        // A function for setting the person being targeted
        public function setType($type = 'combat') {
            if( !isset($this->battleType) ){
                $this->battleType = $type;
            }
        }

        // Try to initiate battle with the target
        public function initiate_fight($battleMessage = 'You have attacked ', $no_locks = false, $skipChance = false) {
            // Try everything
            try {
                // Make this thing transaction Safe
                $GLOBALS['database']->transaction_start();

                // Before allowing to watch, check for current battles
                $this->battle = functions::checkIfInBattle($this->oppId, false, true);

                if ($this->battle !== '0 rows') {
                    throw new Exception("This user is in another battle already.");
                }

                // Default battle type
                $this->setType();

                // First do a lot of checks. Function automatically throws errors if it finds any
                if(!($target = $this->can_go_to_battle("combat",$skipChance))) {
                    $GLOBALS['database']->transaction_rollback();
                    return;
                }

                if ( $GLOBALS['userdata'][0]['location'] != $target[0]['location'] && ($GLOBALS['userdata'][0]['latitude'] !== $target[0]['latitude'] || $GLOBALS['userdata'][0]['longitude'] !== $target[0]['longitude']) )
                {
                    if(isset($_GET['code']) && !isset($this->location_mismatch_chance))
                        $this->location_mismatch_chance = (hexdec(substr($_GET['code'],-3)) % 100) + 1;
                    else if(!isset($this->location_mismatch_chance))
                        $this->location_mismatch_chance = mt_rand(1,100);

                    if
                    (
                        (
                            abs($GLOBALS['userdata'][0]['latitude'] - $target[0]['latitude']) > 1 
                            || 
                            abs($GLOBALS['userdata'][0]['longitude'] - $target[0]['longitude']) > 1 
                            ||
                            $this->location_mismatch_chance >= 33
                        )
                        &&
                        !$skipChance
                    )
                    {
                        if
                        (
                            abs($GLOBALS['userdata'][0]['latitude'] - $target[0]['latitude']) > 1 
                            || 
                            abs($GLOBALS['userdata'][0]['longitude'] - $target[0]['longitude']) > 1
                        )
                            throw new Exception("Although, you believed someone to be here, you failed to find them.");
                        else
                            throw new Exception("Although, you glimpsed them, you failed to find your target.");
                    }
                }

                //if ($GLOBALS['userdata'][0]['latitude'] != $target[0]['latitude'] || $GLOBALS['userdata'][0]['longitude'] != $target[0]['longitude'])
                    //throw new Exception('You are no longer near enough to this user to engage in a battle with them.');

                if($this->battleType == 'kage')
                {
                    $challenger = array('id'=>$GLOBALS['userdata'][0]['id'], 'team_or_extra_data'=>'challenger');
                    $kage = array('id'=>$target[0]['id'], 'team_or_extra_data'=>'kage');
                    BattleStarter::startBattle( array($challenger, $kage), false, BattleStarter::kage, false, false, $no_locks );
                }
                else if($this->battleType == 'clan')
                {
                    $challenger = array('id'=>$GLOBALS['userdata'][0]['id'], 'team_or_extra_data'=>'challenger');
                    $leader = array('id'=>$target[0]['id'], 'team_or_extra_data'=>'leader');

                    BattleStarter::startBattle( array($challenger, $leader), false, BattleStarter::clan, false, false, $no_locks );
                }
                else
                {
                    $attacker_team = $GLOBALS['userdata'][0]['village'];
                    if($target[0]['village'] == $GLOBALS['userdata'][0]['village'])
                    {
                        if($GLOBALS['userdata'][0]['village'] != 'Syndicate')
                            $attacker_team = 'Traitor';
                        else
                            $attacker_team = 'Honorless';
                    }

                    $users = array(
                                    array('id'=>$target[0]['id'], 'team_or_extra_data'=>array('team'=>$target[0]['village'], 'defender'=>true)),
                                    array('id'=>$GLOBALS['userdata'][0]['id'], 'team_or_extra_data'=>array('team'=>$attacker_team, 'attacker'=>true, 'opponents_allegiance'=>$target[0]['village'], 'no_cfh'=>true))
                                  );

                    BattleStarter::startBattle( $users, false, BattleStarter::pvp, false, false, $no_locks );
                }

                // Message
                $GLOBALS['page']->Message($battleMessage . $target[0]['username'] . '', 'Combat System', 'id=113', "Go to battle");


                // At this point commit the transaction
                $GLOBALS['database']->transaction_commit();

                return true;
            }
            catch(Exception $e) {
                $GLOBALS['database']->transaction_rollback("Initiate battle error, transaction rollback");
                $GLOBALS['page']->Message("Error. ". $e->getMessage(), 'Combat System', 'id='.$_GET['id'] );
                return false;
            }


        }

        public function updateDSR($uid)
        {
            //start battle
            $battle = new Battle(0,false,false,true);

            //add user
            $battle->addUser($uid,"1");

            //update dsr
            $username = $battle->user_index[$uid];
            $dr = $battle->users[$username]['dr'];
            $sr = $battle->users[$username]['sr'];

            $update_query = 'UPDATE `users_statistics`
                             SET `dr` = '.$dr.',
                                 `sr` = '.$sr.'
                             WHERE `uid` = '.$uid;

            $GLOBALS['database']->execute_query($update_query);

            //try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception('query failed'); }
            //catch (Exception $e)
            //{
            //    try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
            //    catch (Exception $e)
            //    {
            //        try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed to update user information'); }
            //        catch (Exception $e)
            //        {
            //            error_log('error updating dsr: '.$update_query);
            //            throw new Exception('There was an error updating users dsr.');
            //        }
            //    }
            //}

            //end battle
            unset($battle);
        }

        // Aid user in battle
        public function aid_fight() {

            // Try everything
            try {

                //create lock on other
                $GLOBALS['database']->get_lock('battle',$this->oppId,__METHOD__);
                $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);

                // Make this thing transaction Safe
                $GLOBALS['database']->transaction_start();

                // First do a lot of checks. Function automatically throws errors if it finds any. Returned target is transaction safe
                if(!($target = $this->can_go_to_battle("help"))) {
                    $GLOBALS['database']->transaction_rollback();
                    return;
                }

                // Check if user called for help
                if ($target[0]['cfh'] == 'called' || $target[0]['cfh'] == '') {
                    throw new Exception("Some one has already responded to this call for help.");
                }

                // Check the target is awake
                if ($target[0]['status'] !== 'combat' && $target[0]['status'] !== 'exiting_combat') {
                    throw new Exception("The status of the target is currently ".$target[0]['status']." so you cannot attack.");
                }

                // Get the target CFH settings
                $blacklist = $GLOBALS['database']->fetch_data("SELECT * FROM `users_preferences`
                    WHERE `uid` = '" . $target[0]['id'] . "' LIMIT 1");

                if ($blacklist[0]['CFHsetting'] !== 'CFHblock_black' || stristr($blacklist[0]['pm_blacklist'], ';'.$_SESSION['uid'].';')) {
                    if($blacklist[0]['CFHsetting'] !== 'CFHoff') {
                        if($blacklist[0]['CFHsetting'] !== 'off') {
                            if($blacklist[0]['CFHsetting'] !== 'CFHwhite_only' || !stristr($blacklist[0]['pm_whitelist'], ';'.$_SESSION['uid'].';')) {
                                throw new Exception("The user does not wish to accept your help.");
                            }
                        }
                    }
                }

                //getting owner call for help stuffs.
                if(!($owner = $GLOBALS['database']->fetch_data("
                    SELECT
                        `users`.`cfh`, `users`.`username`,
                        `users_statistics`.`DSR`, `users_statistics`.`dr`,
                        `users_statistics`.`sr`, `users_statistics`.`cur_health`,
                        `users_statistics`.`max_health`, `users`.`status`,
                        `users`.`battle_id`
                    FROM `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    WHERE `id` = ".$_SESSION['uid'])))
                {
                    throw new Exception('There was an error trying to obtain users in the area!');
                }

                // Check aid conditions (Relative Strength Factor check)

                if(substr($target[0]['cfh'],0,4) == 'spar')
                    $aid_result = $this->aid_conditions($owner[0], $target[0], 'spar');
                else
                    $aid_result = $this->aid_conditions($owner[0], $target[0], 'pvp');

                if( $aid_result === false || is_array($aid_result) )
                {

                    if($aid_result === false && !is_array($aid_result))
                    {
                        throw new Exception("You cannot aid in this battle the fight is too close.");
                    }
                    else
                        throw new Exception("You cannot aid in this battle because your DSR out of range.");
                }

                //insert user into battle with lock
                try
                {
                    //getting lock on the battle
                    $GLOBALS['database']->get_lock('battle',$target[0]['battle_id'],__METHOD__);

                    //starting transaction
                    $GLOBALS['database']->transaction_start();

                    //opening the battle
                    $battle = new Battle($target[0]['battle_id'], 15, false);

                    if(count($battle->users) < 2)
                        throw new Exception("Not enought users in combat.");

                    //adding the user to the battle
                    $battle->addUser($_SESSION['uid'], array('team'=>$battle->users[$battle->user_index[$this->oppId]]['team'], 'respondent'=>true, 'no_cfh'=>true));

                    if(isset($battle->display_turn_order[max(array_keys($battle->turn_order))]))
                        array_unshift($battle->display_turn_order[max(array_keys($battle->turn_order))], $owner[0]['username']);

                    if(isset($battle->turn_order[max(array_keys($battle->turn_order))]))
                        array_unshift($battle->turn_order[max(array_keys($battle->turn_order))], $owner[0]['username']);

                    //checking to see if we are responding to the defender
                    if(isset($battle->users[$target[0]['username']]['defender']) && $battle->users[$target[0]['username']]['defender'] == true)
                    {
                        //going through all users in the battle
                        foreach($battle->users as $username => $userdata)
                        {
                            //if this user is the attacker
                            if(isset($userdata['attacker']) && $userdata['attacker'] == true)
                            {
                                //if this user is not a traitor
                                if($userdata['team'] != 'Traitor')
                                {
                                    //allow this user now call for help
                                    $battle->users[$username]['no_cfh'] = false;
                                    unset($battle->users[$username]['no_cfh']);
                                }
                            }
                        }
                    }

                    //setting first action by the respondent
                    //get all possible foes
                    $target_username = false;
                    $targets = array();
                    foreach($battle->users as $key => $value)
                    {
                        if($battle->users[$owner[0]['username']]['team'] != $value['team'])
                        {
                            $targets[] = $key;
                        }
                    }

                    //pick random foe
                    $temp = $targets[ random_int(0, count($targets) - 1 ) ];

                    //select basic attack against selected foe.
                    $battle->recordAction( $owner[0]['username'], $temp, 'respondent', -1, $battle->jutsus[-1]['name']);
                    $battle->doJutsu( $temp , $owner[0]['username'], -1, false, true);

                    //recording the changes made to the cache
                    $battle->updateCache();

                    if($GLOBALS['database']->execute_query('UPDATE `users`
                        SET `status` = "combat", `cfh` = "called", `battle_id` = '.$target[0]['battle_id'].'
                        WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1') === false) {
                        throw new Exception('Reinforcements Reset Update Failed!');
                    }

                    $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                    //setting cfh marker
                    if($GLOBALS['database']->execute_query('UPDATE `users`
                        SET `cfh` = "called"
                        WHERE `users`.`id` = '.$target[0]['id'].' LIMIT 1') === false) {
                        throw new Exception('Reinforcements Reset Update Failed!');
                    }

                    $GLOBALS['database']->release_lock('battle',$target[0]['battle_id']);
                    $GLOBALS['database']->transaction_commit();
                }
                catch(Exception $e)
                {
                    $GLOBALS['database']->release_lock('battle',$target[0]['battle_id']);
                    $GLOBALS['database']->transaction_rollback();
                }

                // Message
                $GLOBALS['page']->Message('You have joined ' . $target[0]['username'] . ' in combat' , 'Combat System', 'id=113', "Go to battle");

                // At this point commit the transaction
                $GLOBALS['database']->transaction_commit();
            }
            catch(Exception $e) {
                //release lock on self
                $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);

                //release lock on other
                $GLOBALS['database']->release_lock('battle',$this->oppId);
                $GLOBALS['database']->transaction_rollback("Call For Help Error, transaction rollback");
                $GLOBALS['page']->Message("There was an error dealing with your request: ". $e->getMessage(), 'Combat System', 'id='.$_GET['id'] );
            }

            //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);

            //release lock on other
            $GLOBALS['database']->release_lock('battle',$this->oppId);
        }

        // Check user can call for help
        protected function can_call_help($battle_type) {
            return !in_array($battle_type, array('arena', 'spar', 'kage', 'mission', 'crime'), true);
        }

        // Check if battle conditions are valid
        protected function battle_conditions($opponent) {
            if(  ($GLOBALS['user']->load_time - $opponent['battle_colldown']) > 15 ) {//making sure the user is not on battle cool down
                if ($opponent['rank_id'] >= 3) { // Is Opponent Chuunin+ ?
                    if ($opponent['id'] !== $_SESSION['uid']) { // Is Opponent Not You?
                        if ($GLOBALS['userdata'][0]['rank_id'] >= 3) // Is Your Rank Chuunin+?
                            if($opponent['rank_id'] <= ($GLOBALS['userdata'][0]['rank_id'] + 1)) { // Is Your Opponent Lower Rank by 1 or same?
                                // Is Your Opponent Higher Rank by 1 or same?
                                if($opponent['rank_id'] >= ($GLOBALS['userdata'][0]['rank_id'] - 1)) {
                                        return true;
                                }
                            }
                        elseif($opponent['rank'] === "ALL") { return true; } // Is Your Opponent Attackable by Everyone?
                        elseif($GLOBALS['userdata'][0]['rank'] === "ALL") { return true; } // Are You Attackable by Everyone?
                    }
                }
            }
            return false;
        }

        // Check if aid conditions are valid
        public function aid_conditions( $self, $target, $for = 'pvp' ) {
            if($target['cfh'] != '' && $target['cfh'] != 'called' && $self['status'] == 'awake' && $self['battle_id'] == 0)
            {
                $selfDSR = ($self['dr'] * ( ( $self['max_health'] * 0.5 + $self['cur_health'] * 0.5 ) / $self['sr'] ));
                //$selfDSR = ($self['dr'] * ( ( $self['cur_health'] ) / $self['sr'] ));

                $DSRs = explode('|',$target['cfh']);

                if($DSRs[0] != 'pvp' && $for == 'pvp')
                    return false;
                else if($DSRs[0] != 'spar' && $for == 'spar')
                    return false;

                $friendlyDSR = $DSRs[1];
                $nonFriendlyDSR = $DSRs[2];

                $gap = $nonFriendlyDSR - $friendlyDSR;

                if( ($gap / $nonFriendlyDSR) <= 0.15 )
                {
                    return false;
                }
                else if($gap > 0 && $selfDSR >= $gap * 0.55173 && $selfDSR <= $gap * 0.84784)
                {
                    return true;
                }
                else
                {
                    return array($gap * 0.55173, $gap * 0.84784);
                }

                //$gap_percentage = 1 - ($friendlyDSR / $nonFriendlyDSR);
                ////$new_gap_percentage = abs(1 - (($friendlyDSR + $selfDSR) / $nonFriendlyDSR));
                //$new_gap_percentage = 1 - (($friendlyDSR + $selfDSR) / $nonFriendlyDSR);
                //
                ////check gap to see if its big enough and that it would close some.
                ////if($friendlyDSR < $nonFriendlyDSR && 1 - ($friendlyDSR / $nonFriendlyDSR) > 0.15 && $gap_percentage > $new_gap_percentage)
                ////if( $gap_percentage > $new_gap_percentage)
                //if( $gap_percentage > abs($new_gap_percentage))
                //{
                //    //$gap_shrink = 1 - $new_gap_percentage/$gap_percentage;
                //    $gap_shrink = 1 - abs($new_gap_percentage)/$gap_percentage;
                //
                //    //check to see if gap is being closed enough
                //    //if($gap_shrink >= 0.5)
                //    if(($gap_shrink >= 0.5 && $new_gap_percengate > 0) || ($gap_shrink >= 0.75 && $new_gap_percengate > 0 && $new_gap_percentage < 0))
                //        return true;
                //}
            }

            return false;
        }

        // If the user attacked an ally, reduce respect points in village
        private function attackedAllyEffects() {

            // Not for clan & kage challenges
            if(!isset($this->battleType)) {
                return;
            }

            if( in_array($this->battleType, array('clan', 'kage', 'spar'), true) ) {
                return;
            }

            // Get user bingo book
            if(!($bingoBook = $GLOBALS['database']->fetch_data('SELECT `bingo_book`.* FROM `bingo_book`
                WHERE `bingo_book`.`userID` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE'))) {
                throw new Exception('An error occurred obtaining Respect information!');
            }

            if($bingoBook === "0 rows") {
                return;
            }

            // New respect
            $newRespect = floor($bingoBook[0][$GLOBALS['userdata'][0]['village']] / 2) - 1000;
            if($GLOBALS['database']->execute_query("UPDATE `bingo_book`
                SET `bingo_book`.".$GLOBALS['userdata'][0]['village']." = ".$newRespect."
                WHERE `bingo_book`.`userID` = ".$_SESSION['uid']." LIMIT 1") === false) {
                throw new Exception('There was an error trying to update village respect!');
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('diplomacy_loss', array('new'=>$newRespect, 'old'=>$bingoBook[0][$GLOBALS['userdata'][0]['village']], 'context'=>$GLOBALS['userdata'][0]['village']));
            }

            // Potentially turn outlaw
            if($newRespect >= 0) { return; }

            // Leave village
            require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
            $respectLib = new respectLib();
            $respectLib->turn_outlaw($_SESSION['uid'], true);

            // Log the change
            functions::log_village_changes(
                $_SESSION['uid'],
                $GLOBALS['userdata'][0]['village'],
                "Syndicate",
                "Kicked out of village pre-battle. Had ".$bingoBook[0][$GLOBALS['userdata'][0]['village']]." respect points, reduced to ".$newRespect."."
            );
        }

        // Test is the user can go into battle with this target
        protected function can_go_to_battle($actionType = "combat", $skipChance = false) {

            // Locally store alliance
            $alliance = $GLOBALS['userdata'][0]['alliance'];

            // Encryption Code for Last 15 Seconds
            $timeArr = array();
            for ($counter = 0; $counter <= 15; $counter++) {
                $timeArr[] = md5(($GLOBALS['user']->load_time - $counter) . "-" . $this->oppId. "-" .$GLOBALS['userdata'][0]['longitude'] . "-" .$GLOBALS['userdata'][0]['latitude']);
            }

            // Test Encryption code for the last 15 seconds
            if (!in_array($_GET['code'], $timeArr, true)) {
                throw new Exception("This attack link has expired. Each attack/challenge link must be clicked within 15 seconds to be active. Attack links also expire when you move your character.");
            }

            if ($GLOBALS['userdata'][0]['status'] !== 'awake') {
                throw new Exception("You are not awake and capable of doing this.");
            }

            // At this point, if it's a territory battle, then return true
            if($actionType === "territory") { return true; }

            // If normal battle, do a lot of opponent checks
            if (!isset($this->oppId) || $this->oppId === $_SESSION['uid']) {
                throw new Exception("This opponent ID is not valid");
            }

            // Get the data of the opponent
            if(!($target = $GLOBALS['database']->fetch_data('SELECT `users_statistics`.`money`,
                `users_statistics`.`reinforcements`, `users_statistics`.`rank_id`, `users_statistics`.`rank`, `users_statistics`.`cur_health`,
                `users_statistics`.`tai_def`, `users_statistics`.`nin_def`, `users_statistics`.`gen_def`, `users_statistics`.`weap_def`, `users_statistics`.`speed`, `users_statistics`.`willpower`, `users_statistics`.`strength`, `users_statistics`.`intelligence`,
                `users`.`username`, `users`.`id`, `users`.`status`, `users`.`latitude`, `users`.`longitude`, `users`.`location`, `users`.`last_ip`,`users`.`village`,
                `users_loyalty`.`village`,
                `users_timer`.`last_activity`, `users_timer`.`battle_colldown`, `users`.`cfh`, `users`.`battle_id`
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                WHERE '.(ctype_digit($this->oppId) ? '`users`.`id` = '.$this->oppId
                    : '`users`.`username` = "'.$this->oppId.'"').' LIMIT 1 FOR UPDATE'))) {
                throw new Exception("There was an issue trying to obtain target.");
            }

            if($target === "0 rows") {
                throw new Exception("This target could not be found");
            }

            if($target[0]['status'] !== "awake" && !in_array($actionType, array('help'), true)) {
                throw new Exception('The user is not awake to be attacked!');
            }

            if($target[0]['cur_health'] <= 0 ) {
                throw new Exception('The user is already completely beat up and at 0 HP');
            }

            if ((int)$alliance[0][$target[0]['village']] === 1) {
                if ($actionType !== "help") {
                    if($target[0]['village'] !== "Syndicate") {
                        if(!isset($_POST['Submit'])) {
                            $GLOBALS['page']->Confirm("This user is friendly, are you sure you wish to attack?", 'Combat System', 'Yes');
                            return false;
                        }
                    }
                }
            }

            if ($GLOBALS['userdata'][0]['last_ip'] !== 0) {
                if($target[0]['last_ip'] !== 0) {
                    if($target[0]['last_ip'] === $GLOBALS['userdata'][0]['last_ip']) {
                        if($GLOBALS['userdata'][0]['user_rank'] !== "Admin" &&
                           $GLOBALS['userdata'][0]['last_ip'] !== "83.92.99.25" &&
                           $GLOBALS['userdata'][0]['last_ip'] !== "24.254.139.88" &&
                           !stristr($GLOBALS['userdata'][0]['last_ip'], "5.57")
                        ) {
                            throw new Exception("You cannot attack people who have the same IP as you.");
                        }
                    }
                }
            }

            if ( $GLOBALS['userdata'][0]['location'] != $target[0]['location'] && ($GLOBALS['userdata'][0]['latitude'] !== $target[0]['latitude'] || $GLOBALS['userdata'][0]['longitude'] !== $target[0]['longitude']) )
            {
                if(isset($_GET['code']) && !isset($this->location_mismatch_chance))
                    $this->location_mismatch_chance = (hexdec(substr($_GET['code'],-3)) % 100) + 1;
                else if(!isset($this->location_mismatch_chance))
                    $this->location_mismatch_chance = mt_rand(1,100);

                if
                (
                    (
                        abs($GLOBALS['userdata'][0]['latitude'] - $target[0]['latitude']) > 1 
                        || 
                        abs($GLOBALS['userdata'][0]['longitude'] - $target[0]['longitude']) > 1 
                        ||
                        $this->location_mismatch_chance >= 33
                    )
                    &&
                    !$skipChance
                )
                {
                    if
                    (
                        abs($GLOBALS['userdata'][0]['latitude']  - $target[0]['latitude'])  > 1 
                        || 
                        abs($GLOBALS['userdata'][0]['longitude'] - $target[0]['longitude']) > 1
                    )
                        throw new Exception("Although, you believed someone to be here, you failed to find them.");
                    else
                        throw new Exception("Although, you glimpsed them, you failed to find your target.");
                }
            }

            if (!$this->battle_conditions($target[0])) {
                throw new Exception("The rank of this target makes this action invalid.");
            }

            $alliance_status = 0;
            if(isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])]))
                $alliance_status = $GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])];

            $activity_visibility = 0;
            if($alliance_status == 2)//war
                $activity_visibility = 60;
            else if($alliance_status == 0)//neutral
                $activity_visibility = 60*5;
            else if($alliance_status == 1 && !in_array($GLOBALS['userdata'][0]['location'],array('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')))//ally and not village
                $activity_visibility = 60*10;
            else//ally and village
                $activity_visibility = 60*15;

            if ($target[0]['last_activity'] <= ($GLOBALS['user']->load_time - $activity_visibility)) {
                throw new Exception("The target is not online.");
            }

            $cooldown = $GLOBALS['user']->load_time - $target[0]['battle_colldown']; // Target Cooldown
            $cooldown2 = $GLOBALS['user']->load_time - $GLOBALS['userdata'][0]['battle_colldown']; // User Cooldown

            if ($cooldown <= 15 && $cooldown >= 0) {
                throw new Exception('This user just came out of the hospital and can\'t be attacked in the next: '.
                    (15 - $cooldown).' seconds');
            }

            if ($cooldown2 <= 15 && $cooldown2 >= 0) {
                throw new Exception('You just came out of the hospital and can\'t attack the next: '.
                    (15 - $cooldown2).' seconds');
            }

            // If ally then negative effects
            if ($actionType !== "help") {
                if ((int)$alliance[0][$target[0]['village']] === 1) {
                    if ($target[0]['village'] !== "Syndicate") { $this->attackedAllyEffects(); }
                }
            }

            // Return the target
            return $target;
        }
    }