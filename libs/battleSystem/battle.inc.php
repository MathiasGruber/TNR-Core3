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

    /*
     * All core battle system functions.
     *
     * For debug purposes you can use:
     * $GLOBALS['template']->append('battleDebug', "MESSAGE");
     */

    require_once(Data::$absSvrPath.'/global_libs/General/static.inc.php');
    require_once(Data::$absSvrPath.'/libs/professionSystem/OccupationData.php');

    class battle extends basicFunctions {


        // BELOW WILL BE ALL DISPLAY SCREENS, e.g. main, wait, stunned etc.

        // Construct the main screen
        public function main_screen() {

            // Get the data used for the main screen
            $userList = $this->create_userList("user");
            $opponentList = $this->create_userList("opponent");

            // Send the data to the template
            $GLOBALS['template']->assign('userList', $userList);
            $GLOBALS['template']->assign('opponentList', $opponentList);

            //echo"<pre />CHECKING ACTIVE STATUS EFFECTS";
            //print_r($this->user['status']);
            //print_r($this->user['data']);
            //print_r($this->opponent['data']);


            // Local strength factor definiitions for ease
            $sessionSF = $this->get_session_strengthFactor();
            $otherSF = $this->get_sessionOpponent_strengthFactor();
            $relative = $this->get_session_RelativeStrengthFactor();
            $joinSFlimit = $this->get_join_StrengthFactorLimit( $this->sessionside , true );

            // Send to smarty
            $GLOBALS['template']->assign('yourSF', round($sessionSF,2) );
            $GLOBALS['template']->assign('otherSF', round($otherSF,2) );
            $GLOBALS['template']->assign('relativeSF', round($relative,2) );
            $GLOBALS['template']->assign('joinSFlimit', round($joinSFlimit,2) );

            // Set the time left
            $timeLeft = ($this->battle[0]['last_action'] + $this->TURN_SWITCH) - $this->timeStamp;

            // Send to smarty
            $GLOBALS['template']->assign('timeLeft', $timeLeft);
            $GLOBALS['template']->assign('timeLeftJs',
                    functions::convert_time(
                        $timeLeft,
                        'roundtime'.$this->timeStamp.random_int(1,10000),
                        'link'
                    )
            );

            // Show the main screen on main screen hook
            $GLOBALS['template']->assign('topScreen', './templates/content/combat/mainScreen.tpl');
        }

        // Show that the user is stunned on the secondary screen
        public function stun_screen() {
            $GLOBALS['template']->assign('secondaryScreen', './templates/content/combat/stunScreen.tpl');
        }

        // User must wait screen
        public function wait_screen() {
            $GLOBALS['template']->assign('secondaryScreen', './templates/content/combat/waitScreen.tpl');
        }

        // Show the current battle log
        public function battle_log( $templateVar = 'tertiaryScreen', $logTitle="Battle Log" ) {
            $GLOBALS['template']->assign('battleLogTitle', $logTitle );
            $GLOBALS['template']->assign('battleLog', $this->battle[0]['log'] );
            $GLOBALS['template']->assign($templateVar, './templates/content/combat/battleLog.tpl');
        }


        // Construct the screen for the battle options
        protected function battle_options() {

            // Start the count at 5. 4 first are: fist, chakra, flee & CFH
            $count = 5;

            /* Items Menu */
            if( isset($this->{$this->sessionside}['items'][ $_SESSION['uid'] ]) &&
                $this->{$this->sessionside}['items'][ $_SESSION['uid'] ] !== "0 rows"
            ){
                $itemArray = array();
                foreach( $this->{$this->sessionside}['items'][ $_SESSION['uid'] ] as $item ){

                    // Don't count armor & broken items
                    if( $item['type'] !== "armor" && $item['durabilityPoints'] > 0 ){

                        // Check if user has any left
                        if( ($item['uses'] < $item['stack']) || $item['type'] !== "item" ){

                            // Item or weapon
                            $value = ($item['type'] == 'item') ? 'ITM:' . $item['id'] : 'WPN:' . $item['id'];

                            // Chosen or not chosen
                            $check = (isset($this->action) && $this->action == $count) ? 'Checked' : '';

                            // Save for smarty
                            $itemArray[] = array(
                                "value" => $value.'|'.$count,
                                "check" => $check,
                                "name" => $item['name'],
                                "durabilityPoints" => $item['durabilityPoints'],
                                "infinity_durability" => $item['infinity_durability'],
                            );

                            $count++;
                        }
                    }
                }
                $GLOBALS['template']->assign('userItems', $itemArray );
            }


            /* Jutsu Menu    */
            if( isset($this->{$this->sessionside}['jutsus'][ $_SESSION['uid'] ]) &&
                $this->{$this->sessionside}['jutsus'][ $_SESSION['uid'] ] !== "0 rows"
            ){
                $jutsuArray = array();
                foreach( $this->{$this->sessionside}['jutsus'][ $_SESSION['uid'] ] as $jutsu ){
                    // Chosen or not chosen
                    $check = (isset($this->action) && $this->action == $count) ? 'Checked' : '';

                    // Save for smarty
                    $jutsuEntry = array(
                        "value" => 'JUT:' . $jutsu['id'].'|'.$count,
                        "check" => $check,
                        "name" => $jutsu['name']
                    );

                    // Add cooldown
                    if( isset($jutsu['curCooldown']) && $jutsu['curCooldown'] > 0 ){
                        $jutsuEntry[ "curCooldown" ] = $jutsu['curCooldown'];
                    }

                    // Add to array
                    $jutsuArray[] = $jutsuEntry;

                    $count++;
                }
                $GLOBALS['template']->assign('userJutsus', $jutsuArray );
            }

            // Create Friends List
            $friendsArray = array();
            foreach( $this->{$this->sessionside}['ids'] as $uid ){
                if( $this->is_user_active( $this->sessionside, $uid) ){
                    $friendsArray[] = array( "value" => $uid, "side" => $this->sessionside, "check" => "", "name" => $this->{$this->sessionside}['data'][$uid]['username'] );
                }
            }
            $GLOBALS['template']->assign('friendSide', $friendsArray );

            // Create Enemy List
            $enemyArray = array();
            $otherSide = $this->get_other_side($this->sessionside);
            foreach( $this->{$otherSide}['ids'] as $uid ){
                if( $this->is_user_active( $otherSide, $uid) ){
                    $enemyArray[] = array( "value" => $uid, "side" => $otherSide,"check" => "Checked", "name" => $this->{$otherSide}['data'][$uid]['username'] );
                }
            }
            $GLOBALS['template']->assign('enemySide', $enemyArray );

            // Check if any of the simple action radio buttons should be checked
            $simpleCheck = array("","","","");
            if( isset($this->action)  ){
                if ( $this->action <= 4) {
                    $simpleCheck[ ($this->action-1) ] = "Checked";
                }
            }
            else{
                $simpleCheck[ 0 ] = "Checked";
            }
            $GLOBALS['template']->assign('simpleChecks', $simpleCheck );

            // Check if we should show CFH button
            if ( $this->can_call_help( $this->battle[0]['battle_type'] ) ) {

                // Tell smarty to show CFH in options
                $GLOBALS['template']->assign('canCFH', 1 );

                // Pass more information to smarty
                $GLOBALS['template']->assign('canCFHinfo', $this->can_user_call_help($this->sessionside, $_SESSION['uid']) );
            }

            // Check if we should show Flee button
            if ( $this->can_flee_battle( $this->battle[0]['battle_type'] ) ) {
                $GLOBALS['template']->assign('canFLEE', 1 );
            }

            // Show the main screen on main screen hook
            $GLOBALS['template']->assign('battleID', $this->battle[0]['id'] );

            // Create a unique form variable to be remembered
            $_SESSION['form_token'] = uniqid();
            $GLOBALS['template']->assign('form_token', $_SESSION['form_token'] );

            // Show the battle options template
            $GLOBALS['template']->assign('secondaryScreen', './templates/content/combat/battleOptions.tpl');
        }

        // BELOW ARE HIGH-LEVEL BATTLE FUNCTIONS - I.E THOSE GETTING/EXECUTING USER DATA

        // Fetches and sets battle and user data
        protected function fetch_battle_data( $battleID = false ) {

            // Check if user is in battle and retrieve battle.
            $this->battle = functions::checkIfInBattle( $GLOBALS['userdata'][0]['id'], $GLOBALS['userdata'][0]['battle_id'], true);

            // Overwrite battle if specified
            if( $battleID !== false ){
                if (!($this->battle = $GLOBALS['database']->fetch_data( 'SELECT * FROM `multi_battle` WHERE multi_battle.id = ' . $this->battleID . ' LIMIT 1' ))) {
                    throw new Exception('An error occurred retrieving battle information!');
                }
            }

            // Set the timestamp for everything in this system
            $this->timeStamp = $GLOBALS['user']->load_time;

            if ($this->battle === '0 rows' || empty($this->battle)) { return false; }

            // Decode the battle log
            $this->battle[0]['log'] = unserialize(base64_decode($this->battle[0]['log']));
            if( empty($this->battle[0]['log']) ) { $this->battle[0]['log'] = array(); }

            // Figure out the round
            $this->round = count($this->battle[0]['log']) + 1;

            // Modify the amount of users and time in case of territory battles
            if ($this->battle[0]['battle_type'] == "territory") {
                $this->TURN_SWITCH = 30;    //90
                $this->MAX_HELP = 10;     // 10 vs. 10 max
            }

            // User & opponent start arrays
            foreach(array("user", "opponent") as $side) { $this->fetch_users($side ); }

            // Do pre-bloodline effects
            foreach ($this->user['ids'] as $id) { $this->parse_pre_bloodline("user", $id, "opponent"); }
            foreach ($this->opponent['ids'] as $id) { $this->parse_pre_bloodline("opponent", $id, "user"); }

            return true;
        }

        // Sets user data from battle-table or fetches from user table
        // User data takes following form
        // [users|opponent]->array(
        //      "data"['id'] => stats, chakra, stamina, health etc of user with ID
        //      "status"['id'] => array of statuses attached to user of user with ID.
        //      "action"['id'] => action(s) submitted with POST by the user with ID. Cleared after round.
        //      "actionInfo"['id'] => array with information pertaining to the action of user with ID. Cleared after round. Used to generate log
        //      "ids" => array of active user IDs in the battle
        //      "active"['id'] => array containing data for whether or not the user is active
        //      "rewards"['id'] => an array containing rewards for the user upon ended battle
        //      "summary"['id'] => an array containing summary to be shown to the user upon ended battle
        //      "repLoss"['id'] => an array containing reputation points lost in different villages
        //      "items"['id'] => array with all the user items
        //      "alliances"['id'] => array with all the user alliance information
        //      "jutsus"['id'] => array with all the user jutsus
        // )
        protected function fetch_users($side) {

            // Get what is currently in the database
            $this->{$side} = unserialize(base64_decode($this->battle[0][$side.'_data']));

            // Set important arrays if unset
            if( !isset( $this->{$side}['data'] ) ){ $this->{$side}['data'] = array(); }
            if( !isset( $this->{$side}['action'] ) ){ $this->{$side}['action'] = array(); }
            if( !isset( $this->{$side}['actionInfo'] ) ){ $this->{$side}['actionInfo'] = array(); }
            if( !isset( $this->{$side}['status'] ) ){ $this->{$side}['status'] = array(); }
            if( !isset( $this->{$side}['items'] ) ){ $this->{$side}['items'] = array(); }
            if( !isset( $this->{$side}['jutsus'] ) ){ $this->{$side}['jutsus'] = array(); }

            // Get the IDs signed in for the battle
            $ids = explode("|||", $this->battle[0][$side.'_ids']);

            // Check if the strength factors should be updated
            $updateStrengthFactor = false;

            // Find the IDs that need to be found in databse
            $i = 0;
            foreach ($ids as $id) {

                // ID must be something, and the user must be active.
                // Note: the user is considered active, as long as he's not been removed already
                if ( $id !== "" && $this->is_user_active( $side, $id ) ) {

                    // Set status array if unset
                    if( !isset( $this->{$side}['status'][ $id ] ) ){
                        $this->{$side}['status'][ $id ] = array();
                    }

                    // Data for this user has already been saved in the battle table
                    if ( isset( $this->{$side}['data'][$id] ) ) {

                        // If AI and effects are unparsed, parse them
                        if (
                                $this->is_user_ai($side, $id) &&
                                $this->{$side}['data'][ $id ]['trait'] !== "" &&
                                empty($this->{$side}['data'][ $id ]['bloodlineEffect'])
                        ) {
                            // Parse AI effects/traits
                            $this->parse_AIeffects( $side , $id );

                        }

                        // Set items
                        if( $this->is_user_ai($side, $id) ){
                            $this->set_ai_items( $side, $id );
                        }

                        // Update the armor of the user
                        $this->calculate_armor( $id, $side );
                    }
                    else {

                        //  Fetch data from database
                        $user = $GLOBALS['database']->fetch_data('SELECT `specialization`, `clan`,
                            users.id, `user_rank`, `username`, `gender`, users_statistics.rank, `rank_id`,
                            users_loyalty.village, `money`, `cur_health`, `cur_sta`,
                            `cur_cha`, `max_health`, `max_cha`, `max_sta`, `tai_off`, `tai_def`,
                            `nin_off`, `nin_def`, `gen_off`, `gen_def`, `weap_def`, `weap_off`,
                            `speed`, `strength`, `willpower`, `intelligence`, `bloodline`,
                            `occupation`, `special_occupation`, `torn_record`,
                            structurePointsActivity,
                            CEIL(`bountyHunter_exp` / 1000) as `bountyHunterLevel`, `bountyHunter_exp`,
                            `last_activity`, `reinforcements`, `time_in_vil`, `anbu`, `location`,
                            users.latitude, users.longitude,
                            vil_loyal_pts, activateBonuses,
                            bingo_book.Konoki, bingo_book.Silence, bingo_book.Samui,
                            bingo_book.Shroud, bingo_book.Shine, bingo_book.Syndicate,
                            users_statistics.level_id,users_statistics.pvp_streak,users_statistics.strengthFactor,

                            villages.latitude AS `village_lat`, villages.longitude AS `village_long`,
                            villages.leader AS `leader`,
                            village_structures.name AS vassal_owner, damageIncTimer,
                            squads.leader_uid as anbuLeader_uid,
                            clans.leader_uid as clanLeader_uid, clans.id as clanID,
                            clans.clan_jutsu as clanJutsu,
                            users_occupations.feature
                            FROM `users`
                                INNER JOIN `users_occupations` ON (users_occupations.userid = users.id)
                                INNER JOIN `users_timer` ON (users_timer.userid = users.id)
                                INNER JOIN `users_preferences` ON (users_preferences.uid = users.id)
                                INNER JOIN `users_statistics` ON (users_statistics.uid = users.id)
                                INNER JOIN `users_missions` ON (users_missions.userid = users.id)
                                INNER JOIN `users_loyalty` ON (users_loyalty.uid = users.id)
                                INNER JOIN `bingo_book` ON (bingo_book.userID = users.id)
                                INNER JOIN `villages` ON (villages.name = users_loyalty.village)
                                LEFT JOIN `village_structures` ON (village_structures.vassal = users_loyalty.village)
                                LEFT JOIN `squads` ON (squads.id = users_preferences.anbu)
                                LEFT JOIN `clans` ON (clans.id = users_preferences.clan)
                            WHERE users.id = '.$id.' LIMIT 1');

                        // User successfully collected
                        if ( $user !== '0 rows' ) {

                            // Save user data
                            $this->{$side}['data'][ $id ] = $user[0];
                            $this->{$side}['data'][ $id ]['bloodlineEffect'] = array();

                            // Set village location
                            $this->{$side}['data'][ $id ]['village_location'] =
                                ($this->{$side}['data'][ $id ]['village'] == "Syndicate") ?
                                    "City of Mei" :
                                    $this->{$side}['data'][ $id ]['village'] . " village";

                            // Save IDs
                            $this->{$side}['ids'][] = $id;

                            // Set items and jutsus
                            $this->set_user_items($side, $id);
                            $this->set_user_jutsus($side, $id);

                            // Create Arrays used for rewards and final battle summary
                            $this->create_user_arrays($side, $id);

                            // Get effects for user
                            $this->parse_effects( $side , $id );
                            $this->calculate_armor( $id, $side );

                        }
                        else{
                            return false;
                        }
                    }

                    // Ensure no max-hp = 0
                    if( $this->{$side}['data'][ $id ]["max_health"] <= 0 ){
                        $this->{$side}['data'][ $id ]["max_health"] = 1;
                    }

                    // NULL actions if they haven't been set
                    if( !isset( $this->{$side}['action'][ $id ] ) ){
                        $this->{$side}['action'][ $id ] = "NULL";
                    }

                    // Set to active, if not be set
                    if( !isset( $this->{$side}['active'][ $id ] ) ){
                        $this->{$side}['active'][ $id ] = 1;
                    }

                    // Set the contribution to strength factor in this battle if unset (set for both AI and user)
                    if( !isset( $this->{$side}['data'][ $id ]["battleStrengthFactor"] ) ){
                        $updateStrengthFactor = true;
                    }
                }
                $i++;
            }

            // Recalculate strength factors
            $this->update_battle_strengthFactors( $updateStrengthFactor );

            // Determine if this session (i.e. pageload) is on the user-side or opponent-side
            foreach ( $this->{$side}['ids'] as $id) {
                if ($_SESSION['uid'] == $id) {
                    $this->sessionside = $side;
                }
            }
        }

        // Update strength factors of all users in battle
        protected function update_battle_strengthFactors( $doRecalculate = false ){

            // Treat both sides
            foreach( array("user","opponent") as $side ){
                if( isset($this->{$side}) ){

                    // Get user IDs
                    $ids = explode("|||", $this->battle[0][$side.'_ids']);

                    // Variable for storing strength factor
                    $this->{$side."StrengthFactor"} = 1;

                    // Claculate strength factor
                    foreach ($ids as $id) {
                        if ( $id !== "" && $this->is_user_active( $side, $id ) ) {

                            if(!(isset($this->{$side}['data'][ $id ]['summontype']) && $this->{$side}['data'][ $id ]['summontype'] == 'user'))
                            {

                                // Only do something if set to recalculate or if battleStrengthFactor is unset
                                if( $doRecalculate == true || !isset($this->{$side}['data'][ $id ]["battleStrengthFactor"]) ){

                                    // Figure out what percentage to take off the user strength factor
                                    $lifePerc = 1;
                                    if( isset( $this->{$side}['data'][ $id ]['battle_rounds'] ) ){
                                        $lifePerc = $this->{$side}['data'][ $id ]["cur_health"] / $this->{$side}['data'][ $id ]["max_health"];
                                    }

                                    // Check if a strength factor has already been calculated for the user
                                    if( isset($this->{$side}['data'][ $id ]['strengthFactor']) ){
                                        $this->{$side}['data'][ $id ]["battleStrengthFactor"] = $this->{$side}['data'][ $id ]['strengthFactor'] * $lifePerc;
                                    }
                                    else{
                                        $this->{$side}['data'][ $id ]["battleStrengthFactor"] = functions::calc_strength_factor( $this->{$side}['data'][ $id ] ) * $lifePerc;
                                    }

                                    // Update the counter for when this was last update
                                    $this->battle[0]['last_rsf_update_round'] = $this->round;

                                }
                                $this->{$side."StrengthFactor"} += $this->{$side}['data'][ $id ]["battleStrengthFactor"];
                            }
                        }
                    }
                }
            }
        }


        // Check the actions of user and update object variables allDone and userDone
        protected function check_user_action( $uid, $side ){

            // If user performed action, stage is 2
            if ( $_SESSION['uid'] == $uid &&
                 $this->has_submitted_action($side, $uid)
            ) {
                $this->stage = 2;
            }

            // Check if user should get to move, so we can see if everyone whos should do something did something
            if (
                !$this->has_submitted_action($side, $uid) &&
                !$this->is_user_ai( $side, $uid ) &&
                $this->has_stun_effect($side, $uid) == false &&
                $this->is_user_active( $side, $uid )
            ) {
                $this->allDone = 0;
            }

            // Check if this user was removed from the battle and should see end-screen
            if (
                    $_SESSION['uid'] == $uid &&
                    !$this->is_user_active($side, $uid)
            ) {
                $this->userDone = 1;
            }
        }

        /*      Determine Battle Stage	 */
        protected function determine_stage() {

            // Stage 1: is the default
            $this->stage = 1;

            // Stage 2: check if the user has performed any actions
            // Loop through all users, and check also if all did something
            $this->allDone = 1;
            $this->userDone = 0;

            foreach ( $this->user['ids'] as $id ) {
                $this->check_user_action( $id, "user" );
            }

            foreach ( $this->opponent['ids'] as $id ) {
                $this->check_user_action( $id, "opponent" );
            }


            // Stage 3: If all are done, then go to stage three,
            // where things are calculated. Only if not territory battle
            if ( $this->allDone == 1 && $this->battle[0]['battle_type'] !== "territory") {
                $this->stage = 3;
            }

            // Stage TIME's UP, so go to stage 3 and calculate
            if ( $this->battle[0]['stage'] == 2 ) {
                $this->stage = 3;
            }

            // User is removed from battle, show final page
            if ( $this->userDone == 1) {
                $this->stage = 4;
            }

            // Battle is over, show end page
            if ($this->battle[0]['stage'] == 3) {
                $this->stage = 4;
            }
        }

        // Submit move to database
        protected function upload_move( $targetID ) {

            // Create / reset user action information array
            $this->{$this->sessionside}['actionInfo'][ $_SESSION['uid'] ] = array();

            // Update user health/chakra/stamina according to his user variable
            $this->{$this->sessionside}['data'][ $_SESSION['uid'] ]['cur_health'] = $GLOBALS['userdata'][0]['cur_health'];
            $this->{$this->sessionside}['data'][ $_SESSION['uid'] ]['cur_sta'] = $GLOBALS['userdata'][0]['cur_sta'];
            $this->{$this->sessionside}['data'][ $_SESSION['uid'] ]['cur_cha'] = $GLOBALS['userdata'][0]['cur_cha'];

            // Check the target exists
            if ( $this->id_in_battle($targetID) ) {

                // Update user array
                $this->{$this->sessionside}['action'][ $_SESSION['uid'] ] = $targetID . ':::' . $_REQUEST['action'];

                // Update the battle with this data
                $this->update_battle_playerData(array( $this->sessionside => true ));
            }
            else{

                // Send message to be shown in log
                $this->setUserActionInfo( $this->sessionside, $_SESSION['uid'], $this->{$this->sessionside}["data"][ $_SESSION['uid'] ]['username'] . ' tried to attack an invalid target');
            }
        }

        /*      Remember last action    */
        protected function remember_action() {

            // If action is unset, set to empty string
            if( !isset( $this->action ) ){
                $this->action = 1;
            }

            // If we have a POST action
            if( isset($_REQUEST['action']) ){
                $actiondata = explode("|", $_REQUEST['action']);
                $_REQUEST['action'] = $actiondata[0];
                if (isset($actiondata[1])) {
                    $this->action = $actiondata[1];
                }
            }

            // Send to smarty
            if( $this->action !== "" ){
                $GLOBALS['template']->assign('lastAction', "&amp;action=".$this->action );
            }
            else{
                $GLOBALS['template']->assign('lastAction', "" );
            }
        }

        // End of round, calculate everything
        protected function execute_moves() {

            // Pre-damage Status Effects
            foreach ($this->user['ids'] as $id) {
                $this->parse_pre_status('user', $id);
            }
            foreach ($this->opponent['ids'] as $id) {
                $this->parse_pre_status('opponent', $id);
            }

            // Make the first attacker random
            $this->attack_order_rand = random_int(1, 2);
            if ($this->battle[0]['log'] == "") {
                $this->attack_order_rand = 1;
            }


            // Execute attacks
            switch ($this->attack_order_rand) {
                case 1:
                    $this->execute_loop( "user", "first");
                    $this->execute_loop( "opponent", "second");
                    break;
                case 2:
                    $this->execute_loop( "opponent", "second");
                    $this->execute_loop( "user", "first");
                    break;
            }


            //	Post damage:
            foreach ($this->user['ids'] as $id) {
                $this->parse_post_bloodline('user', $id, 'opponent');
            }
            foreach ($this->opponent['ids'] as $id) {
                $this->parse_post_bloodline('opponent', $id, 'user');
            }

            // Post-damage Status Effects
            foreach ($this->user['ids'] as $id) {
                $this->parse_post_status('user', $id, 'opponent' );
            }
            foreach ($this->opponent['ids'] as $id) {
                $this->parse_post_status('opponent', $id, 'user' );
            }

            //    Update battle data:
            switch ($this->attack_order_rand) {
                case 1:
                    foreach ($this->user['ids'] as $id) { $this->update_userData('user', $id);}
                    foreach ($this->opponent['ids'] as $id) { $this->update_userData('opponent', $id);}
                break;
                case 2:
                    foreach ($this->opponent['ids'] as $id) { $this->update_userData('opponent', $id); }
                    foreach ($this->user['ids'] as $id) { $this->update_userData('user', $id); }
                break;
            }

            // Set the messages the users should see
            $this->roundBattleLog = array();
            switch ($this->attack_order_rand) {
                case 1:
                    $first = $this->user_messages('user');
                    $second = $this->user_messages('opponent');
                break;
                case 2:
                    $first = $this->user_messages('opponent');
                    $second = $this->user_messages('user');
                break;
            }

            // Add timestamp to the battle log entries
            if( !empty($this->roundBattleLog) ){
                $count = count($this->roundBattleLog);
                for( $i=0; $i<$count; $i++ ){
                    $this->roundBattleLog[$i]['time'] = $this->timeStamp;
                }
            }

            // Update Battle Status
            $this->figureNextBattleStage();
        }

        // Execute Actions for chosen side
        protected function execute_loop( $side, $turn ) {

            foreach( $this->{$side}['ids'] as $id ){

                // Count the number of rounds been in battle.
                $this->{$side}['data'][ $id ]['battle_rounds'] = isset($this->{$side}['data'][ $id ]['battle_rounds']) ? $this->{$side}['data'][ $id ]['battle_rounds'] + 1 : 1;

                // Is the user active
                if( $this->is_user_active($side, $id ) && $this->is_user_alive($side, $id) ){

                    // Is the use stunned
                    if( !$this->is_stunned($side, $id ) ){

                        // Check action
                        if(  $this->has_submitted_action($side, $id) ){

                            // Determine whether user is trying to hit user or opponent
                            $target_and_action = explode(":::", $this->{$side}['action'][ $id ] );

                            // Get the side of the target
                            if( $targetSide = $this->get_id_side( $target_and_action[0] ) ){

                                // Check if the action can be performed
                                if (
                                    !$this->do_battle_op(
                                            $side,                      // Side of the attacker
                                            $id,                        // ID of the attacker
                                            $targetSide,                // Side of the target
                                            $target_and_action[0],      // ID of the target
                                            $this->{$targetSide}['ids'],// IDs of other on target side
                                            $target_and_action[1],      // Action to be performed
                                            $this->{$side}['ids']       // Attacker companion IDs
                                    )
                                ) {
                                    $this->setUserActionInfo( $side, $id, $this->{$side}['data'][ $id ]['username'] . ': An invalid action tag was submitted');
                                }
                            }
                            else{
                                $this->setUserActionInfo( $side, $id, $this->{$side}['data'][ $id ]['username'] . ' tried to attack, but could not determine side of user with userID '.$target_and_action[0]);
                            }
                        }
                        elseif( $this->is_user_ai($side, $id) ){
                            $this->ai_move( $side , $id, $this->get_other_side($side) );
                        }
                        else{
                            $this->setUserActionInfo( $side, $id, $this->{$side}['data'][ $id ]['username'] . ' stood around and did nothing. ' );
                        }
                    }
                    else{
                        $this->setUserActionInfo( $side, $id, $this->{$side}['data'][ $id ]['username'] . ' is stunned and cannot move' );
                    }
                }
            }
        }

        // Perform an action
        protected function do_battle_op($user, $userid, $target, $targetid, $targetcompanionids, $action, $attackerCompanionIDs = null) {

            //    Store action in userdata array for future reference and DEBUG purposes
            $this->{$user}['data'][ $userid ]['tag'] = $action;

            //    Explode the tag for easy manipulation and checking
            $action = explode(':', $action);

            //    Determine action
            if ($action[0] == 'STTAI') {

                //    Use standard Tai
                $this->do_taijutsu($user, $userid, $target, $targetid);

            } elseif ($action[0] == 'STCHA') {

                //    Use standard chakra blast
                $this->do_chakra($user, $userid, $target, $targetid);

            } elseif ($action[0] == 'FLEE') {

                //    Attempt to flee
                $this->do_flee($user, $userid);

            } elseif ($action[0] == 'HELP') {

                //    Attempt to help
                $this->do_help($user, $userid);

            } elseif ( preg_match("/(JUT|ITM|WPN)/", $action[0]) && isset($action[1]) && is_numeric($action[1])) {
                // Get the jutsu or item
                switch( $action[0] ){
                    case "JUT":
                        $actionData = $this->know_jutsu( $action[1] , $userid , $user );
                        if( $this->is_user_ai($user, $userid) && !$actionData ){
                            $actionData = $this->add_user_jutsus($user, $userid, $action[1]);
                        }
                    break;
                    case "ITM":
                    case "WPN":
                        $actionData = $this->have_item( $action[1] , $userid , $user );
                        if( $this->is_user_ai($user, $userid) && !$actionData ){
                            $actionData = $this->add_user_item($user, $userid, $action[1]);
                        }
                    break;
                }

                // If jutsu or item was found, then continue
                if ( $actionData ) {

                    // Check if the user is active
                    if ( $this->is_user_active($target, $targetid) ) {

                        // Perform action with jutsu, item or weapon
                        switch( $action[0] ){
                            case "JUT":

                                // Create object
                                $jutsu = new jutsu(
                                        $this,                  // Reference to this object
                                        $user,                  // This user side
                                        $userid,                // This user ID
                                        $target,                // Target side
                                        $targetid,              // Target ID
                                        $actionData,             // Jutsu Data & User_jutsus data
                                        $this->{$target}['ids'], // IDs of targets
                                        $this->{$user}['ids']   // IDs of user friends
                                );

                                // Return information to $this
                                if (!$jutsu) {
                                    return false;
                                } else {
                                    $jutsu->return_data();
                                }
                            break;
                            case "ITM":

                                // Create object
                                $item = new item(
                                        $this,      // Reference to this object
                                        $user,      // This user side
                                        $userid,    // This user ID
                                        $target,    // Target side
                                        $targetid,  // Target ID
                                        $actionData // Item data
                                );

                                // Return to $this
                                if (!$item) {
                                    return false;
                                } else {
                                    $item->return_data();
                                }
                            break;
                            case "WPN":

                                // Create object
                                $wpn = new weapon(
                                        $this,      // Reference to this object
                                        $user,      // This user side
                                        $userid,    // This user ID
                                        $target,    // Target side
                                        $targetid,  // Target ID
                                        $actionData // Weapon data
                                );

                                // Return to $this
                                if (!$wpn) {
                                    return false;
                                } else {
                                    $wpn->return_data();
                                }
                            break;
                        }


                    } else {
                        $this->setUserActionInfo( $user, $userid, $this->{$user}['data'][ $userid ]['username'] . ' attacks the air.' );
                    }
                } else {
                    $this->setUserActionInfo( $user, $userid, $this->{$user}['data'][ $userid ]['username'] . ' tries and fails to use an unknown action. Info: '.print_r( $action , true ) );
                }
            }
            else {
                //$GLOBALS['template']->append('battleDebug', print_r($action, true) );
                return false;
            }
            return true;
        }


        // BELOW IS EVERYTHING RELATED TO PARSING USER INFORMATION FOR VIEWING

        // Create a userList for the main screen
        private function create_userList($side) {
            $userList = array();
            foreach ($this->{$side}['ids'] as $id) {

                 // Only show user if he is active
                 if ( $this->is_user_active($side, $id) ) {

                     // Create array for this user
                    $userList[$id] = array();

                     // Set avatar, which depends on wheather AI or not
                    if ( $this->is_user_ai($side, $id) && isset($this->{$side}['data'][ $id ]['original_id'])) {
                        $image = functions::getUserImage('/ai/', $this->{$side}['data'][ $id ]['original_id']);
                        if ( $image !== './images/default_avatar.png' ) {
                             $userList[$id]['avatar'] =  $image;
                        } else {
                            $userList[$id]['avatar'] = "AI";
                        }
                    } else {
                        $userList[$id]['avatar']  = functions::getAvatar( $this->{$side}['data'][ $id ]['id'] );
                    }

                    $userList[$id]['cur_health'] = $this->{$side}['data'][ $id ]['cur_health'];
                    $userList[$id]['max_health'] = $this->{$side}['data'][ $id ]['max_health'];
                    $userList[$id]['cur_cha'] = $this->{$side}['data'][ $id ]['cur_cha'];
                    $userList[$id]['max_cha'] = $this->{$side}['data'][ $id ]['max_cha'];
                    $userList[$id]['cur_sta'] = $this->{$side}['data'][ $id ]['cur_sta'];
                    $userList[$id]['max_sta'] = $this->{$side}['data'][ $id ]['max_sta'];

                    //  Set bars
                    $userList[$id]['lifeperc'] = ($this->{$side}['data'][ $id ]['cur_health'] / $this->{$side}['data'][ $id ]['max_health']) * 100;
                    if ($id == $_SESSION['uid']) {
                        $userList[$id]['chaperc'] = ($this->{$side}['data'][ $id ]['cur_cha'] / $this->{$side}['data'][ $id ]['max_cha']) * 100;
                        $userList[$id]['staperc'] = ($this->{$side}['data'][ $id ]['cur_sta'] / $this->{$side}['data'][ $id ]['max_sta']) * 100;
                    }

                    // Set Username, Rank & Village
                    if( $this->is_user_ai($side, $id) ){
                        $userList[$id]['name'] = array( "text" => $this->{$side}['data'][ $id ]['username'] );
                        $userList[$id]['rank'] = $this->{$side}['data'][ $id ]['rank'];
                        $userList[$id]['village'] = "AI";
                    }
                    else{
                        $userList[$id]['name'] = array( "text" => $this->{$side}['data'][ $id ]['username'], "href" => '?id=13&amp;page=profile&amp;name=' . $this->{$side}['data'][ $id ]['username'] );
                        $userList[$id]['rank'] = $this->{$side}['data'][ $id ]['rank'];
                        $userList[$id]['village'] = $this->{$side}['data'][ $id ]['village'];
                    }

                    // Armor
                    $userList[$id]['armor'] = $this->{$side}['data'][ $id ]['armor'];

                }
            }

            // Return user list
            return $userList;
        }

        // Create a single message to be added to the battle log. This one is used in the function
        // user_messages for creating the battle log based on what has happened during the battle
        // Primarily just a function used to reduce redundant code
        protected function create_message( $side, $uid, $message, $entryOwner, $cssClass, $ownerUpdate = null ){

            // UPdate owner
            if( $ownerUpdate !== null ){
                $entryOwner = $this->update_logMessage_owner( $side, $uid, $ownerUpdate );
            }

            // Add message to array
            $this->roundBattleLog[] = array(
                "type" => "subEntry",
                "owner" => $entryOwner,
                "cssClass" => $cssClass,
                "message" => $message,
                "tempLongMessage" => ""
            );
        }

        // Create messages for battle log based on user actions
        protected function user_messages( $side ) {

            // Get the other side
            $otherSide = $this->get_other_side($side);

            // Taken names
            $takenNames = array();

            // Loop over all users on the side
            foreach ($this->{$side}['ids'] as $id) {

                // Check if user is still active
                if ( $this->is_user_active($side, $id) ) {

                    // Set the default "owner" of the entry to the username. May be overwritten in some cases,
                    // in which case the battle round array is re-arranged later :)
                    $entryOwner = $this->{$side}['data'][ $id ]['username'];
                    if(in_array($entryOwner, $takenNames) ){
                        $entryOwner .= "-";
                    }
                    $takenNames[] = $entryOwner;

                    // Add action action message to battle log. Don't save it in the database in the end though
                    if( isset($this->{$side}['actionInfo'][ $id ]['message']) ){
                        $this->roundBattleLog[] = array(
                            "type" => "main",
                            "owner" => $entryOwner,
                            "message" => $this->{$side}['actionInfo'][ $id ]['message'],
                            "tempLongMessage" => $this->{$side}['actionInfo'][ $id ]['description']
                        );
                    }
                    // If any damage was performed
                    if (isset($this->{$side}['actionInfo'][ $id ]['damage'])) {

                        // Go through everyone hit
                        foreach ($this->{$side}['actionInfo'][ $id ]['damage'] as $targerid => $sideamount) {

                            // Check if target is still active
                            if ( $this->is_user_active( $this->{$side}['actionInfo'][ $id ]['targetType'] , $targerid ) ) {

                                // Figure out the damage message
                                $localMessage = "";
                                if ($sideamount > 0) {
                                    if ($this->{$side}['actionInfo'][ $id ]['element'] == 'none') {
                                        $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> deals ' . $sideamount . ' damage to <i>' . $this->{$this->{$side}['actionInfo'][ $id ]['targetType']}['data'][ $targerid ]['username'] . '</i>';
                                    } else {
                                        $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> deals ' . $sideamount . ' ' . $this->{$side}['actionInfo'][ $id ]['element'] . ' damage to <i>' . $this->{$this->{$side}['actionInfo'][ $id ]['targetType']}['data'][ $targerid ]['username'] . '</i>';
                                    }
                                    $cssBlack = "logRed";
                                } else {
                                    $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '\'s</i> attack was blocked by <i>' . $this->{$this->{$side}['actionInfo'][ $id ]['targetType']}['data'][ $targerid ]['username'] . '</i>';
                                    $cssBlack = "logBlack";
                                }

                                // Add message to array
                                $this->roundBattleLog[] = array(
                                    "type" => "subEntry",
                                    "owner" => $entryOwner,
                                    "cssClass" => $cssBlack,
                                    "message" => $localMessage,
                                    "tempLongMessage" => ""
                                );
                            }
                        }
                    }

                    // If Healing was performed
                    if ( isset($this->{$side}['actionInfo'][ $id ]['healed']) &&     // Heal is set
                         (  $this->{$side}['actionInfo'][ $id ]['healed'] > 0 ||            // Positive heal
                            ( isset($this->{$side}['actionInfo'][ $id ]['healeddead']) &&   // Dead before heal
                              $this->{$side}['actionInfo'][ $id ]['healeddead'] == 1
                    ))) {

                        // Figure out healing message
                        if ($this->{$side}['actionInfo'][ $id ]['healed'] > 0) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> heals ' . $this->{$side}['actionInfo'][ $id ]['healed'] . ' health';
                        } elseif ($this->{$side}['actionInfo'][ $id ]['healed'] == 0 && $this->{$side}['actionInfo'][ $id ]['healeddead'] == 1) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> died before being able to heal.';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen", "healLog" );

                    }

                    // If stamina refilling was performed
                    if ( isset($this->{$side}['actionInfo'][ $id ]['starestored']) ) {

                        // Figure out healing message
                        if ($this->{$side}['actionInfo'][ $id ]['starestored'] > 0) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> restores ' . $this->{$side}['actionInfo'][ $id ]['starestored'] . ' stamina';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen", "staminaLog" );

                    }

                    // If chakra refilling was performed
                    if ( isset($this->{$side}['actionInfo'][ $id ]['charestored']) ) {

                        // Figure out healing message
                        if ($this->{$side}['actionInfo'][ $id ]['charestored'] > 0) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> restores ' . $this->{$side}['actionInfo'][ $id ]['charestored'] . ' chakra';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen", "chakraLog" );

                    }

                    // User cannot be healed anymore
                    if (isset($this->{$side}['actionInfo'][ $id ]['healed_nomore'])) {

                        // Figure out absorption message
                        $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i>\'s cells have become so damaged from all the healing during this round, that they need longer to recover.';

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen", "clearLog" );

                    }

                    // If absorption took place
                    if ( isset($this->{$side}['actionInfo'][ $id ]['absorb']) &&     // Heal is set
                         (  $this->{$side}['actionInfo'][ $id ]['absorb'] > 0 ||            // Positive heal
                            ( isset($this->{$side}['actionInfo'][ $id ]['absorbdead']) &&   // Dead before heal
                              $this->{$side}['actionInfo'][ $id ]['absorbdead'] == 1
                    ))) {

                        // Figure out absorption message
                        if ($this->{$side}['actionInfo'][ $id ]['absorb'] > 0) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> absorbed ' . $this->{$side}['actionInfo'][ $id ]['absorb'] . ' damage and converted it to HP';
                        } elseif ($this->{$side}['actionInfo'][ $id ]['absorb'] == 0 && $this->{$side}['actionInfo'][ $id ]['absorbdead'] == 1) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> died before being able to absorb damage.';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen" );

                    }

                    // Siphoning health
                    if ( isset($this->{$side}['actionInfo'][ $id ]['leech']) &&     // Heal is set
                         (  $this->{$side}['actionInfo'][ $id ]['leech'] > 0 ||            // Positive heal
                            ( isset($this->{$side}['actionInfo'][ $id ]['leechdead']) &&   // Dead before heal
                              $this->{$side}['actionInfo'][ $id ]['leechdead'] == 1
                    ))) {

                        // Figure out leech message
                        if ($this->{$side}['actionInfo'][ $id ]['leech'] > 0) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> siphoned ' . $this->{$side}['actionInfo'][ $id ]['leech'] . ' health from ' .
                                            $this->{$this->{$side}['actionInfo'][ $id ]['targetType']}['data'][ $this->{$side}['actionInfo'][ $id ]['targetIDs'][0] ]['username'] . ".";
                        }
                        elseif ( $this->{$side}['actionInfo'][ $id ]['leechdead'] == 1) {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> died before being able to leech health.';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen" );
                    }


                    // If reflecting damage status effects
                    if (isset($this->{$side}['actionInfo'][ $id ]['reflInfo'])) {

                        // Figure out absorption message.
                        $localMessage = ' damage done by ' . $this->{$side}['data'][ $id ]['username'] . ' will reflected back for ' . $this->{$side}['actionInfo'][ $id ]['reflRounds'] . ' rounds, including this round';

                        // If elemental damage is set, add that to message
                        if( isset($this->{$side}['actionInfo'][ $id ]['reflElement']) ){
                            $localMessage = $this->{$side}['actionInfo'][ $id ]['reflElement'] . $localMessage;
                        }
                        else{
                            $localMessage = "Any" . $localMessage;
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue", "reflLog" );
                    }

                    // If cleared from status effects
                    if (isset($this->{$side}['actionInfo'][ $id ]['rdaInfo'])) {

                        // Figure out absorption message
                        $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> will receive residual damage for '.$this->{$side}['actionInfo'][ $id ]['rdaRounds'].' rounds';

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue","rdaLog" );

                    }

                    // Healing over time
                    if (isset($this->{$side}['actionInfo'][ $id ]['hotInfo'])) {

                        // Figure out absorption message
                        $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> will receive healing for '.$this->{$side}['actionInfo'][ $id ]['hotRounds'].' rounds';

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logGreen","hotLog" );

                    }

                    // If user sealed something
                    if (isset($this->{$side}['actionInfo'][ $id ]['seal'])) {

                        // Figure out seal message
                        if ($this->{$side}['actionInfo'][ $id ]['seal'] == 'success') {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i>\'s bloodline effects have been sealed for ' . $this->{$side}['actionInfo'][ $id ]['sealrounds'] . ' rounds.';
                        } else {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i>\'s bloodline effects could not be sealed.';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue","sealLog" );

                    }

                    // If recoil damage was set
                    if (isset($this->{$side}['actionInfo'][ $id ]['recoil'])) {

                        // Only do something if recoil damage is higher than 0
                        if ($this->{$side}['actionInfo'][ $id ]['recoil'] > 0) {
                            // Message
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> loses ' . $this->{$side}['actionInfo'][ $id ]['recoil'] . ' health from own jutsu';

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logRed" );
                        }
                    }

                    // Knockout
                    if (isset($this->{$side}['actionInfo'][ $id ]['KO'])) {

                        // Get the target username, for easier readability
                        $tempType = $this->{$side}['actionInfo'][ $id ]['KOtargetType'];
                        $tempID = $this->{$side}['actionInfo'][ $id ]['targetIDs'][0];
                        $tempUsername = $this->{$tempType}['data'][ $tempID ]['username'];

                        // Figure out KO message
                        if ($this->{$side}['actionInfo'][ $id ]['KO'] == 'hit') {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> takes out ' . $tempUsername . ' in one hit!';
                        } elseif ($this->{$side}['actionInfo'][ $id ]['KO'] == 'miss') {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> attempted to take out ' . $tempUsername . ' in one hit, but failed.';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue" );
                    }

                    // Resists Fleeing
                    if ( isset($this->{$side}['actionInfo'][ $id ]['fleeRinfo']) ) {
                        // Figure out resist flee message
                        if ($this->{$side}['actionInfo'][ $id ]['fleeRinfo'] == 'success') {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> can not flee during the next ' . $this->{$side}['actionInfo'][ $id ]['fleeresistrounds'] . ' rounds.';
                        } elseif ($this->{$side}['actionInfo'][ $id ]['fleeRinfo'] == 'failed') {
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> resists being disabled from fleeing.';
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue", "fleeRLog" );

                    }

                    // Resist being stunned
                    if (isset($this->{$side}['actionInfo'][ $id ]['bcopyInfo'])) {

                        // Only show something if the user's stun resist was a success
                        if ($this->{$side}['actionInfo'][ $id ]['bcopyInfo'] == 'success') {

                            // Get the target username, for easier readability
                            $tempType = $this->{$side}['actionInfo'][ $id ]['BCOPYtargetType'];
                            $tempID = $this->{$side}['actionInfo'][ $id ]['targetIDs'][0];
                            $tempUsername = $this->{$tempType}['data'][ $tempID ]['username'];

                            // Stn resist Message
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> has copied the bloodline effects of '.$tempUsername.'. Effect will last for the next ' . $this->{$side}['actionInfo'][ $id ]['bcopyRounds'] . ' rounds  ';

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue", "bcopyLog" );

                        }
                    }

                    // Resist being stunned
                    if (isset($this->{$side}['actionInfo'][ $id ]['stunRinfo'])) {

                        // Only show something if the user's stun resist was a success
                        if ($this->{$side}['actionInfo'][ $id ]['stunRinfo'] == 'success') {

                            // Stn resist Message
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> can not be stunned for the next ' . $this->{$side}['actionInfo'][ $id ]['stunresistrounds'] . ' rounds';

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue", "stunRLog" );

                        }
                    }

                    // Residual damage messages
                    if ( isset($this->{$side}['actionInfo'][ $id ]['poison']['damage']) ) {

                        // only show a message if the residual is above zero
                        if ($this->{$side}['actionInfo'][ $id ]['poison']['damage'] > 0) {

                            // Figure out the residual message
                            if (strtolower($this->{$side}['actionInfo'][ $id ]['poison']['element']) != 'none') {
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> loses ' . $this->{$side}['actionInfo'][ $id ]['poison']['damage'] . ' health from residual ' . $this->{$side}['actionInfo'][ $id ]['poison']['element'] . ' damage';
                            } else {
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> loses ' . $this->{$side}['actionInfo'][ $id ]['poison']['damage'] . ' health from residual damage';
                            }

                            // Residual damage messages should always appear under the user himself
                            $entryOwner = $this->{$side}['data'][ $id ]['username'];

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logRed" );

                        }
                    }

                    // Stun
                    if (isset($this->{$side}['actionInfo'][ $id ]['stunInfo'])) {

                        // Figure out stun message
                        if ($this->{$side}['actionInfo'][ $id ]['stunInfo'] == 'success') {

                            // Show stun message
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> will be stunned for the next ' . $this->{$side}['actionInfo'][ $id ]['stunrounds'] . " ". functions::pluralize("round", $this->{$side}['actionInfo'][ $id ]['stunrounds']);

                        } elseif ($this->{$side}['actionInfo'][ $id ]['stunInfo'] == 'failed') {

                            // Check if user is already stunned
                            if( $this->is_stunned( $side,$id ) ){
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> resists being stunned for extra time.';
                            }
                            else{
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> resists being stunned.';
                            }

                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue","stunLog" );

                    }

                    // Info about summoning
                    if (isset($this->{$side}['actionInfo'][ $id ]['sumInfo'])) {

                        // Only show something if the user's stun resist was a success
                        switch( $this->{$side}['actionInfo'][ $id ]['sumInfo'] ){
                            case "success":
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> has summoned ' . $this->{$side}['data'][ $this->{$side}['data'][ $id ]['summonedID'] ]['username'];
                            break;
                            case "cantControl":
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> failed to control the summon.';
                            break;
                            case "badOdds":
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> attempts a summoning technique. The summoned creature, however, refuses to aid in a battle with the current odds.';
                            break;
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue" );

                    }

                    // Reflected damage
                    if (isset($this->{$side}['actionInfo'][ $id ]['reflect'])) {

                        // Only if reflected damaage is above 0
                        if ($this->{$side}['actionInfo'][ $id ]['reflect'] > 0) {

                            // Message
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> is hit with ' . $this->{$side}['actionInfo'][ $id ]['reflect'] . ' damage from their own attack';

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logRed" );

                        }
                    }

                    // Flee Action
                    if (isset($this->{$side}['actionInfo'][ $id ]['flee'])) {
                        if (
                            isset($this->{$side}['actionInfo'][ $id ]['firstround']) &&
                            $this->{$side}['actionInfo'][ $id ]['firstround'] == 1
                        ) {

                            // Stunned before he/she could do anything
                            $this->set_user_fleeing($side, $id, false);
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> was stunned just before attempting to flee!';


                        } elseif (
                            $this->is_stunned ($side, $id)
                        ) {

                            // Stunned
                            $this->set_user_fleeing($side, $id, false);
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> was stunned and cannot flee!';


                        } elseif ($this->{$side}['data'][ $id ]['cur_health'] <= 0) {

                            // Health below zero
                            $this->set_user_fleeing($side, $id, false);
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> was knocked out and could not flee!';


                        } else {

                            // Trying to flee
                            if ( $this->is_user_fleeing($side, $id) ) {
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> flees from the scene of the battle';
                            } else {
                                $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> failed to flee from the scene of the battle';
                            }
                        }

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue" );

                    }

                    // If cleared from status effects
                    if (isset($this->{$side}['actionInfo'][ $id ]['clearInfo'])) {

                        // Figure out absorption message
                        $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> has been cleared from all status effects.';

                        // Create log message
                        $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue", "clearLog" );

                    }

                    // Money Stolen
                    if (isset($this->{$side}['actionInfo'][ $id ]['stolen'])) {

                        // Only if something was stolen
                        if ( $this->{$side}['actionInfo'][ $id ]['stolen'] > 0 ) {

                            // Message
                            $localMessage = '<i>' . $this->{$side}['data'][ $id ]['username'] . '</i> stole ' . $this->{$side}['actionInfo'][ $id ]['stolen'] . ' ryo!';

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue" );

                        }
                    }

                    // Get all the increase/decrease effects
                    if (isset($this->{$side}['actionInfo'][ $id ]['statusEffects']) && !empty($this->{$side}['actionInfo'][ $id ]['statusEffects'])) {

                        // Go through all the effects
                        foreach( $this->{$side}['actionInfo'][ $id ]['statusEffects'] as $effect ){

                            // Set the message
                            if( stristr($effect['effect'], "sustain") ){
                                $localMessage = $effect['affectedName'] . ' will ' . $effect['effect'] . ' ' . $effect['affectedStat'] . ' damage during the next ' . $effect['rounds'] . ' '.functions::pluralize("round", $effect['rounds'] );
                            }
                            elseif( isset($effect['rounds']) && $effect['rounds'] > 0 ){
                                $localMessage = $effect['affectedName'] . '\'s ' . $effect['affectedStat'] . ' will be ' . $effect['effect'] . ' during the next ' . $effect['rounds'] . ' '.functions::pluralize("round", $effect['rounds'] );
                            }
                            else{
                                $localMessage = $effect['affectedName'] . '\'s ' . $effect['affectedStat'] . ' is ' . $effect['effect'] . ' compared to normal.';
                            }

                            // Set the css class. Default is blue
                            // $cssClass = isset($effect['cssClass']) ? $effect['cssClass'] : "logBlue";

                            // Create log message
                            $this->create_message( $side, $id, $localMessage, $entryOwner, "logBlue" );
                        }
                    }

                    // Add additional messages
                    if (isset($this->{$side}['actionInfo'][ $id ]['statusTxts']) && !empty($this->{$side}['actionInfo'][ $id ]['statusTxts'])) {

                        // Go through all the messages
                        foreach( $this->{$side}['actionInfo'][ $id ]['statusTxts'] as $message ){

                            // Create log message. Index 1 = message, index 0 = css class.
                            $this->create_message( $side, $id, $message[1], $entryOwner, $message[0] );

                        }
                    }
                }
            }

        }

        // BELOW IS EVERYTHING RELATED TO MODIFYING/PARSING USER DATA

        // Calculate Armor
        protected function calculate_armor($uid, $side) {

            // Loop over items and figure out armor
            $armor = 0;
            if( isset($this->{$side}['items'][ $uid ]) ){
                foreach( $this->{$side}['items'][ $uid ] as $itemID => $itemData ){
                    if( $itemData['type'] == "armor" &&  $itemData['equipped'] == "yes" && $itemData['durabilityPoints'] > 0){
                        $armor += $itemData['strength'];
                    }
                }
            }

            // Set user armor
            $this->{$side}['data'][$uid]['armor'] = $armor;
        }

        // Parse Effects. This is run when the user is loaded, and not every round
        public function parse_effects($side, $id) {

            throw new exception('Let Koala know if you see this: 1483 battle inc parse_effects');

            ///*              Parse Bloodline Effects             */
            //if ($this->{$side}['data'][$id]['bloodline'] != '' && $this->{$side}['data'][$id]['bloodline'] != 'None') {
            //    $temp = $GLOBALS['database']->fetch_data("SELECT `trait_1`, `trait_2`, `trait_3`, `trait_4` FROM `bloodlines`
            //        WHERE `name` = '" . $this->{$side}['data'][$id]['bloodline'] . "' LIMIT 1");
            //    if ($temp != '0 rows') {
            //        if ($temp[0]['trait_1'] != null) {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][0] = explode(':', $temp[0]['trait_1']);
            //        }
            //        if ($temp[0]['trait_2'] != null) {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][1] = explode(':', $temp[0]['trait_2']);
            //        }
            //        if ($temp[0]['trait_3'] != null) {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][2] = explode(':', $temp[0]['trait_3']);
            //        }
            //        if ($temp[0]['trait_4'] != null) {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][3] = explode(':', $temp[0]['trait_4']);
            //        }
            //    }
            //}
            //
            //
            ///*              Do the elemental affinities. Only if the first one is set     */
            //$elements = new Elements($id);
            //$affinities = $elements->getUserElements();
            //
            //if( isset($affinities[0]) && $affinities[0] !== ""  ){
            //
            //    // Do the primary affinity
            //    $elemData = Elements::elementAndWeakness( $affinities[0] );
            //    $this->{$side}['data'][$id]['bloodlineEffect'][] = explode(':', 'EDINC:'.$elemData[0].':PERC:5');
            //    $this->{$side}['data'][$id]['bloodlineEffect'][] = explode(':', 'ESDEC:'.$elemData[1].':PERC:5');
            //
            //    // If the user has his second affinity
            //    if( isset($affinities[1]) && $affinities[1] !== "" ){
            //        $elemData = Elements::elementAndWeakness( $affinities[1] );
            //        $this->{$side}['data'][$id]['bloodlineEffect'][] = explode(':', 'EDINC:'.$elemData[0].':PERC:5');
            //        $this->{$side}['data'][$id]['bloodlineEffect'][] = explode(':', 'ESDEC:'.$elemData[1].':PERC:5');
            //    }
            //
            //    // Do the special affinity
            //    if( isset($affinities[2]) && $affinities[2] !== "" ){
            //        $elemData = Elements::elementAndWeakness( $affinities[2] );
            //        $this->{$side}['data'][$id]['bloodlineEffect'][] = explode(':', 'EDINC:'.$elemData[0].':PERC:5');
            //        $this->{$side}['data'][$id]['bloodlineEffect'][] = explode(':', 'ESDEC:'.$elemData[1].':PERC:5');
            //    }
            //}
            //
            ///*              Load Location based effect      */
            //if ( $this->inMap( $this->{$side}['data'][$id]['longitude'], $this->{$side}['data'][$id]['latitude'] )  ) {
            //
            //    // Get the territory
            //    if (!isset($this->territory)) {
            //
            //        $mapInformation = mapfunctions::getMapInformation();
            //        $locationInformation = cachefunctions::getLocations();
            //        $this->territory = mapfunctions::getTerritoryInformation(
            //                array( "x.y" => $this->{$side}['data'][$id]['longitude'].".".$this->{$side}['data'][$id]['latitude'] ) ,
            //                $mapInformation,
            //                $locationInformation
            //        );
            //    }
            //
            //    // Check territory stuff
            //    if ( $this->territory ) {
            //
            //        // Effects independent of user
            //        if ( !empty($this->territory['trait_1']) ) {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', $this->territory['trait_1']);
            //        }
            //        if ( !empty($this->territory['trait_2']) ) {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', $this->territory['trait_2']);
            //        }
            //
            //        // Count territories
            //        $terri_count =  cachefunctions::territory_count( $this->{$side}['data'][$id]['village'] );
            //
            //        // User dependent effects - only if village is owned by user
            //        if ($this->territory['owner'] == $this->{$side}['data'][$id]['village']) {
            //
            //            // Check the user is an anbu
            //            if( $this->is_user_anbu($side, $id) ) {
            //
            //                // Get the anbu squat
            //                $squad = $GLOBALS['database']->fetch_data("SELECT `leader_uid`,`pt_def`, `pt_rage` FROM `squads` WHERE `id` = '" . $this->{$side}['data'][$id]['anbu'] . "' LIMIT 1");
            //                if ($squad !== "0 rows") {
            //
            //                    if ($squad[0]['leader_uid'] == $this->{$side}['data'][$id]['id']) {
            //                        $perc = $terri_count / 15;
            //                        $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', 'DINC:TNGW:PERC:' . $perc);
            //                    } else {
            //                        $perc = $terri_count / 20;
            //                        $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', 'DINC:TNGW:PERC:' . $perc);
            //                    }
            //                }
            //
            //            } else {
            //
            //                // Check if kage and if yes, then increment damage
            //                if ( $this->is_user_kage($side, $id) ) {
            //                    $perc = $terri_count / 10;
            //                    $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', 'DINC:TNGW:PERC:' . $perc);
            //                }
            //            }
            //        } elseif ($this->territory['owner'] == "Syndicate" && $this->{$side}['data'][$id]['village'] == "Syndicate") {
            //            $perc = $terri_count / 25;
            //            $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', 'DINC:TNGW:PERC:' . $perc);
            //        }
            //
            //        /* Parse village damage increase bonus */
            //        if( $this->{$side}['data'][$id]['damageIncTimer'] > $this->timeStamp ){
            //            $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', 'DINC:TNGW:PERC:1.5');
            //        }
            //
            //        /* Parse village bonuses. Only if in same village */
            //        if (
            //                $this->{$side}['data'][$id]['village'] != 'Syndicate' &&
            //                $this->{$side}['data'][$id]['longitude'] == $this->{$side}['data'][$id]['village_long'] &&
            //                $this->{$side}['data'][$id]['latitude'] == $this->{$side}['data'][$id]['village_lat'] &&
            //                $this->battle[0]['battle_type'] != "kage"
            //        ) {
            //            $reduction = $GLOBALS['database']->fetch_data("SELECT `wall_def_level` FROM `village_structures` WHERE `name` = '" . $this->{$side}['data'][$id]['village'] . "' LIMIT 1");
            //            if ($reduction != '0 rows' && $reduction[0]['wall_def_level'] > 0) {
            //                $perc = ($reduction[0]['wall_def_level'] * 0.1);  //updated
            //                $bloodIndex = isset($this->{$side}['data'][$id]['bloodlineEffect']) ? count($this->{$side}['data'][$id]['bloodlineEffect']) : 0;
            //                $this->{$side}['data'][$id]['bloodlineEffect']['' . $bloodIndex . ''] = explode(':', 'DSDEC:TNGW:PERC:' . $perc);
            //            }
            //        }
            //    }
            //}
            //
            //// Parse armor bonuses
            //$userarmor = $GLOBALS['database']->fetch_data('SELECT items.use, items.use2
            //    FROM `users_inventory`
            //        INNER JOIN `items` ON (
            //            items.id = users_inventory.iid AND
            //            items.type = "armor" AND
            //            items.use IS NOT NULL
            //        )
            //    WHERE
            //        users_inventory.uid = '.$this->{$side}['data'][$id]['id'].' AND
            //        users_inventory.equipped = "yes" AND
            //        users_inventory.durabilityPoints > 0 ');
            //
            //if ($userarmor != '0 rows') {
            //    foreach ($userarmor as $armor) {
            //        if (isset($armor['use']) && $armor['use'] !== "") {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', $armor['use']);
            //        }
            //        if (isset($armor['use2']) && $armor['use2'] !== "") {
            //            $this->{$side}['data'][$id]['bloodlineEffect'][count($this->{$side}['data'][$id]['bloodlineEffect'])] = explode(':', $armor['use2']);
            //        }
            //    }
            //}
        }

        protected function parse_AIeffects($side, $id) {
            $this->{$side}['data'][$id]['bloodlineEffect'] = explode(';', $this->{$side}['data'][$id]['trait']);
            $i = 0;
            while ($i < count($this->{$side}['data'][$id]['bloodlineEffect'])) {
                $this->{$side}['data'][$id]['bloodlineEffect'][$i] = explode(':', $this->{$side}['data'][$id]['bloodlineEffect'][$i]);
                $i++;
            }
        }

        protected function parse_pre_status($side, $uid) {

            if (
                    isset($this->{$side}['status'][ $uid ] ) &&
                    count($this->{$side}['status'][ $uid ] ) > 0
            ) {

                //    Include status effect library
                $status = new status($this, $side, $uid, null, 0);

                //    Execute status effect code for each effect in the array
                $i = 0;
                while ($i < count( $this->{$side}['status'][ $uid ] )) {

                    // Get the function name
                    $function = $this->{$side}['status'][ $uid ][$i][0];

                    // Check that the function exists
                    if (method_exists($status, $function)) {

                        // Call the status-effect function
                        $status->$function( $this->{$side}['status'][ $uid ][$i] );


                    } else {
                        throw new Exception('Function for this tag does not exist: '.$function." - DEBUG1: ".print_r($this->{$side}['status'][ $uid ], true) );
                    }
                    $i++;
                }

                // Return data to this main class
                $status->return_data();
            }
        }

        protected function parse_post_status($side, $uid, $otherSide ) {
            if (
                    isset($this->{$side}['status'][ $uid ] ) &&
                    count($this->{$side}['status'][ $uid ] ) > 0
            ) {
                //    Include status effect library
                $status = new status($this, $side, $uid, $otherSide, 1);

                //    Execute status effect code for each effect in the array
                $i = 0;

                while ($i < count( $this->{$side}['status'][ $uid ] )) {

                    // Get the function name
                    $function = $this->{$side}['status'][ $uid ][$i][0];

                    // Check that the function exists
                    if (method_exists($status, $function)) {

                        // Call the status-effect function
                        $status->$function( $this->{$side}['status'][ $uid ][$i] );

                        // Decrement the turns left for this status effect
                        $status->decrement_turns($i);

                    } else {
                        throw new Exception('Function for this tag does not exist: '.$function." - DEBUG2: ".print_r($this->{$side}['status'][ $uid ], true));
                    }
                    $i++;
                }

                // Remove finished tags
                $status->removeFinishedEffects();

                // Return data to this main class
                $status->return_data();
            }
        }

        // Parse and execute bloodline effects for targeted entity.
        protected function parse_pre_bloodline($side, $id, $otherside) {

            // Placeholder function, currently empty since there are no pre-calc effects
            if (
                    isset($this->{$side}["data"][$id]['bloodlineEffect']) &&
                    count($this->{$side}["data"][$id]['bloodlineEffect']) > 0
            ) {

                // Do Bloodline Effects
                $bloodline = new bloodline(
                                $this,
                                $side,
                                $id,
                                $otherside,
                                0
                );
                $i = 0;
                while ($i < count($this->{$side}["data"][$id]['bloodlineEffect'])) {
                    $function = $this->{$side}["data"][$id]['bloodlineEffect'][$i][0];
                    if ( !$this->is_bloodline_sealed($this->{$side}['data'][ $id ]['bloodlineEffect'][$i]) ) {
                        if (method_exists($bloodline, $function)) {
                            $bloodline->$function($this->{$side}["data"][$id]['bloodlineEffect'][$i]);
                        } else {
                            echo"<pre />Invalid bloodline tag specified 1: " . $function . "<br>";
                            // print_r($this->{$side}['data'][ $id ]['bloodlineEffect'][$i]);
                        }
                    }
                    $i++;
                }
                $bloodline->return_data();
            }
        }

        protected function parse_post_bloodline( $user, $userid, $opponent ) {
            if (
                    isset($this->{$user}["data"][ $userid ]['bloodlineEffect']) &&
                    count($this->{$user}["data"][ $userid ]['bloodlineEffect'] > 0)
            ) {

                // Deal with the seal & stop tag
                // Seal tag: makes effect ineffective for x rounds
                // Stop tag: removes effect after x rounds
                $i = $deleted = 0;
                while ($i < count($this->{$user}['data'][ $userid ]['bloodlineEffect'])) {

                    // Set convenience variables for readability
                    $tagCount = count($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i]);

                    // Check if effect has seal
                    if ( $tagCount >= 2 && $this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][ ($tagCount - 2)] == "SEAL") {

                        // Reduce the seal round count
                        $this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][ ($tagCount - 1) ] -= 1;

                        // If count is zero, then remove seal
                        if ($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][ ($tagCount - 1) ] == 0) {
                            unset($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][ ($tagCount - 1)] );
                            unset($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][ ($tagCount - 1)] );
                        }
                    }

                    // Check if the effect has a stop
                    if ( $tagCount >= 2 && $this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][ ($tagCount - 2) ] == "STOP") {

                        // Reduce the stop round count
                        $this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][($tagCount - 1)] -= 1;

                        // If count is zero, then remove effect
                        if ($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][($tagCount - 1)] <= 0) {

                            // Remove the effect.
                            unset($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i]);
                            $deleted = 1;
                        }
                    }
                    $i++;
                }

                // If a bloodline effect was removed, then rearrange the array so the keys are 0,1,2,3...
                if ( $deleted == 1 ) {
                    $this->{$user}['data'][ $userid ]['bloodlineEffect'] = array_values($this->{$user}['data'][ $userid ]['bloodlineEffect']);
                }

                //    Load bloodline library
                $bloodline = new bloodline(
                        $this,
                        $user,
                        $userid,
                        $opponent,
                        1
                );

                // Loop over all the bloodline effects
                $i = 0;
                while ($i < count($this->{$user}['data'][ $userid ]['bloodlineEffect'])) {
                    $function = $this->{$user}['data'][ $userid ]['bloodlineEffect'][$i][0];
                    if ( !$this->is_bloodline_sealed($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i]) ) {
                        if (method_exists($bloodline, $function)) {
                            $bloodline->$function( $this->{$user}['data'][ $userid ]['bloodlineEffect'][$i] );
                        } else {
                            echo"<pre />Invalid bloodline tag specified 2: " . $function . "<br>";
                            print_r($this->{$user}['data'][ $userid ]['bloodlineEffect'][$i]);
                        }
                    }

                    $i++;
                }
                $bloodline->return_data();
            }
        }

        // BELOW ARE FUNCTIONS FOR BACKING UP USER STATS. USED TO RESET AFTER STATUS EFFECTS

        // Backup User Stats
        protected function backup_stats($side) {
            foreach ($this->{$side}['ids'] as $id) {
                $this->{$side}["backup"][$id]['tai_off'] = $this->{$side}["data"][$id]['tai_off'];
                $this->{$side}["backup"][$id]['nin_off'] = $this->{$side}["data"][$id]['nin_off'];
                $this->{$side}["backup"][$id]['gen_off'] = $this->{$side}["data"][$id]['gen_off'];
                $this->{$side}["backup"][$id]['weap_off'] = $this->{$side}["data"][$id]['weap_off'];
                $this->{$side}["backup"][$id]['tai_def'] = $this->{$side}["data"][$id]['tai_def'];
                $this->{$side}["backup"][$id]['nin_def'] = $this->{$side}["data"][$id]['nin_def'];
                $this->{$side}["backup"][$id]['gen_def'] = $this->{$side}["data"][$id]['gen_def'];
                $this->{$side}["backup"][$id]['weap_def'] = $this->{$side}["data"][$id]['weap_def'];
                $this->{$side}["backup"][$id]['strength'] = $this->{$side}["data"][$id]['strength'];
                $this->{$side}["backup"][$id]['intelligence'] = $this->{$side}["data"][$id]['intelligence'];
                $this->{$side}["backup"][$id]['willpower'] = $this->{$side}["data"][$id]['willpower'];
                $this->{$side}["backup"][$id]['speed'] = $this->{$side}["data"][$id]['speed'];
                $this->{$side}["backup"][$id]['armor'] = $this->{$side}["data"][$id]['armor'];
            }
        }

        protected function reset_stats( $side ) {

            // As default, don't continue battle for this side
            $this->{$side . 'continue'} = false;

            // An array of players that are done
            $this->{$side . '_lostplayers'} = array();
            $this->{$side . '_killedplayers'} = array();
            $this->{$side . '_fleeplayers'} = array();

            // As default, not all are stunned
            $this->all_stunned = 0;

            // Loop through all users
            foreach ($this->{$side}['ids'] as $id) {

                // Check if user is still active
                if ( $this->is_user_active($side, $id) ) {

                    // If this isn't a summon
                    if (
                            !isset($this->{$side}['data'][ $id ]['just_summoned']) ||
                            $this->{$side}['data'][ $id ]['just_summoned'] != true
                    ) {
                        $this->{$side}['data'][ $id ]['tai_off'] = $this->{$side}["backup"][$id]['tai_off'];
                        $this->{$side}['data'][ $id ]['nin_off'] = $this->{$side}["backup"][$id]['nin_off'];
                        $this->{$side}['data'][ $id ]['gen_off'] = $this->{$side}["backup"][$id]['gen_off'];
                        $this->{$side}['data'][ $id ]['weap_off'] = $this->{$side}["backup"][$id]['weap_off'];
                        $this->{$side}['data'][ $id ]['tai_def'] = $this->{$side}["backup"][$id]['tai_def'];
                        $this->{$side}['data'][ $id ]['nin_def'] = $this->{$side}["backup"][$id]['nin_def'];
                        $this->{$side}['data'][ $id ]['gen_def'] = $this->{$side}["backup"][$id]['gen_def'];
                        $this->{$side}['data'][ $id ]['weap_def'] = $this->{$side}["backup"][$id]['weap_def'];
                        $this->{$side}['data'][ $id ]['strength'] = $this->{$side}["backup"][$id]['strength'];
                        $this->{$side}['data'][ $id ]['intelligence'] = $this->{$side}["backup"][$id]['intelligence'];
                        $this->{$side}['data'][ $id ]['willpower'] = $this->{$side}["backup"][$id]['willpower'];
                        $this->{$side}['data'][ $id ]['speed'] = $this->{$side}["backup"][$id]['speed'];
                        $this->{$side}['data'][ $id ]['armor'] = $this->{$side}["backup"][$id]['armor'];
                    } else {
                        $this->{$side}['data'][ $id ]['just_summoned'] = false;
                    }

                    // Implement Jutsu cooldown; i.e. for all jutsus reduce the cooldown variable.
                    if( !empty( $this->{$side}['jutsus'][ $id ] ) ){
                        foreach( $this->{$side}['jutsus'][ $id ] as $key => $jutsu ){
                            $this->{$side}['jutsus'][ $id ][ $key ]['curCooldown']--;
                        }
                    }

                    // Check Stun. If all_stunned is 2, someone UN-stunned was already found
                    if ($this->all_stunned != 2) {
                        $this->all_stunned = 1;
                        if (!$this->is_stunned($side, $id)) {
                            $this->all_stunned = 2;
                        }
                    }

                    // Final determination based on flee-status & current health
                    if (
                            $this->is_user_fleeing($side, $id) &&
                            $this->{$side}['data'][ $id ]['cur_health'] > 0
                    ) {
                        // User has fled and survived
                        $this->{$side . '_lostplayers'}[] = $id;
                        $this->{$side . '_fleeplayers'}[] = $id;

                        //Remove summons of this user as well
                        if ( isset($this->{$side}['data'][ $id ]['summonedID']) ) {
                            if (
                                $this->is_user_active($side, $this->{$side}['data'][ $id ]['summonedID'])
                            ) {
                                $this->{$side . '_lostplayers'}[] = $this->{$side}['data'][ $id ]['summonedID'];
                            }
                        }
                    } elseif ($this->{$side}['data'][ $id ]['cur_health'] > 0) {

                        // If this is not a summoned created, then this side may continue
                        // since health is above 0
                        if (
                                !isset($this->{$side}['data'][ $id ]['type']) ||
                                $this->{$side}['data'][ $id ]['type'] != "summon"
                        ) {
                            $this->{$side . 'continue'} = true;
                        }
                    } else {

                        // This user is below 0 health, therefore remove
                        $this->{$side . '_lostplayers'}[] = $id;
                        $this->{$side . '_killedplayers'}[] = $id;

                        // Remove flee status from removed users
                        $this->set_user_fleeing($side, $id, false);

                        // Set battle conclusion
                        $this->{$side}['summary'][ $id ]['battle_conclusion'] = "lost";

                        //Remove summons of this user as well
                        if ( isset($this->{$side}['data'][ $id ]['summonedID']) ) {
                            if (
                                    $this->is_user_active($side, $this->{$side}['data'][ $id ]['summonedID'])
                            ) {
                                $this->{$side . '_lostplayers'}[] = $this->{$side}['data'][ $id ]['summonedID'];
                            }
                        }
                    }
                }
            }
        }


        // BELOW ALL FUNCTIONS INVOLVING MODIFICATION OF THE BATTLE/USER DATA

        //  Update data. Updates the user data-array according to which effects have been applied to him
        protected function update_userData($target, $id) {

            // Check if user is still active
            if ( $this->is_user_active($target, $id) ) {

                //    New opponent health
                if (isset($this->{$target}['actionInfo'][ $id ]['damage'])) {

                    // Loop through everyone this user attacked & deal the damage
                    foreach ($this->{$target}['actionInfo'][ $id ]['damage'] as $targerid => $targetamount) {

                        // Only deduct positive damage
                        if( $targetamount > 0){

                            // Get Target
                            $tagetType = $this->{$target}['actionInfo'][ $id ]['targetType'];

                            // Execute Damage
                            $this->{$tagetType}['data'][ $targerid ]['cur_health'] -= $targetamount;
                            if($this->{$tagetType}['data'][ $targerid ]['cur_health'] < 0){
                                $this->{$tagetType}['data'][ $targerid ]['cur_health'] = 0;
                            }
                        }

                    }
                }

                //    User heal effect
                if (isset($this->{$target}['actionInfo'][ $id ]['healed'])) {

                    // Only do something is the healing amount is positive
                    if( $this->{$target}['actionInfo'][ $id ]['healed'] > 0 ){

                        // Check current health
                        $old_health = $this->{$target}['data'][ $id ]['cur_health'];
                        if ($old_health > 0) {

                            // Healing will happen. Total healing during battle must be recorded
                            if( !isset( $this->{$target}['data'][ $id ]['total_healed'] ) ){
                                $this->{$target}['data'][ $id ]['total_healed'] = 0;
                            }

                            // Make sure we don't over-heal
                            if( $this->{$target}['data'][ $id ]['cur_health'] + $this->{$target}['actionInfo'][ $id ]['healed'] > $this->{$target}['data'][ $id ]['max_health'] ){
                                $this->{$target}['actionInfo'][ $id ]['healed'] = $this->{$target}['data'][ $id ]['max_health'] - $this->{$target}['data'][ $id ]['cur_health'];
                            }

                            // Make sure we don't heal user more than he's allowed to be healed. Set to 200% of max health
                            if( $this->{$target}['data'][ $id ]['total_healed'] + $this->{$target}['actionInfo'][ $id ]['healed'] >  $this->{$target}['data'][ $id ]['max_health']*2 ){
                                $this->{$target}['actionInfo'][ $id ]['healed'] = $this->{$target}['data'][ $id ]['max_health']*2 - $this->{$target}['data'][ $id ]['total_healed'];
                                $this->{$target}['actionInfo'][ $id ]['healed_nomore'] = 1;
                            }

                            // Update health
                            $this->{$target}['data'][ $id ]['cur_health'] += $this->{$target}['actionInfo'][ $id ]['healed'];
                            $this->{$target}['data'][ $id ]['total_healed'] += $this->{$target}['actionInfo'][ $id ]['healed'];

                            // Prevent over-capping
                            if ($this->{$target}['data'][ $id ]['cur_health'] > $this->{$target}['data'][ $id ]['max_health']) {
                                $this->{$target}['actionInfo'][ $id ]['healed'] = $this->{$target}['data'][ $id ]['max_health'] - $old_health;
                                $this->{$target}['data'][ $id ]['cur_health'] = $this->{$target}['data'][ $id ]['max_health'];
                            }

                        } else {

                            // User already dead
                            if ($this->{$target}['actionInfo'][ $id ]['healed'] > 0) {
                                $this->{$target}['actionInfo'][ $id ]['healeddead'] = 1;
                            }
                            $this->{$target}['actionInfo'][ $id ]['healed'] = 0;
                        }
                    }
                }

                //    User health leech
                if (isset($this->{$target}['actionInfo'][ $id ]['leech'])) {
                    if ($this->{$target}['data'][ $id ]['cur_health'] > 0) {
                        // Add health
                        $this->{$target}['data'][ $id ]['cur_health'] += $this->{$target}['actionInfo'][ $id ]['leech'];

                        // User isn't dead
                        $this->{$target}['actionInfo'][ $id ]['leechdead'] = 0;

                        // Prevent over-capping
                        if ($this->{$target}['data'][ $id ]['cur_health'] > $this->{$target}['data'][ $id ]['max_health']) {
                            $this->{$target}['actionInfo'][ $id ]['leech'] = $this->{$target}['data'][ $id ]['cur_health'] - $this->{$target}['data'][ $id ]['max_health'];
                            $this->{$target}['data'][ $id ]['cur_health'] = $this->{$target}['data'][ $id ]['max_health'];
                        }
                    } else {
                        // User already dead
                        $this->{$target}['actionInfo'][ $id ]['leech'] = 0;
                        $this->{$target}['actionInfo'][ $id ]['leechdead'] = 1;
                    }
                }

                //    User absorb effect
                if (isset($this->{$target}['actionInfo'][ $id ]['absorb'])) {
                    if ($this->{$target}['data'][ $id ]['cur_health'] > 0) {

                        // Add health
                        $this->{$target}['data'][ $id ]['cur_health'] += $this->{$target}['actionInfo'][ $id ]['absorb'];

                        // Prevent over-capping
                        if ($this->{$target}['data'][ $id ]['cur_health'] > $this->{$target}['data'][ $id ]['max_health']) {
                            $this->{$target}['data'][ $id ]['cur_health'] = $this->{$target}['data'][ $id ]['max_health'];
                        }
                    } else {
                        // User already dead
                        $this->{$target}['actionInfo'][ $id ]['absorb'] = 0;
                        $this->{$target}['actionInfo'][ $id ]['absorbdead'] = 1;
                    }
                }

                //    Recoil damage user
                if (isset($this->{$target}['actionInfo'][ $id ]['recoil'])) {
                    $this->{$target}['data'][ $id ]['cur_health'] -= $this->{$target}['actionInfo'][ $id ]['recoil'];
                }

                //    Reflected damage opponent
                if (isset($this->{$target}['actionInfo'][ $id ]['reflect'])) {
                    $this->{$target}['data'][ $id ]['cur_health'] -= $this->{$target}['actionInfo'][ $id ]['reflect'];
                }

                //    Poison damage opponent:
                if (isset($this->{$target}['actionInfo'][ $id ]['poison']['damage'])) {
                    $this->{$target}['data'][ $id ]['cur_health'] -= $this->{$target}['actionInfo'][ $id ]['poison']['damage'];
                }

                //    New stamina user
                if (isset($this->{$target}['actionInfo'][ $id ]['sta_cost'])) {
                    $this->{$target}['data'][ $id ]['cur_sta'] -= $this->{$target}['actionInfo'][ $id ]['sta_cost'];
                }

                //    New chakra user.
                if (isset($this->{$target}['actionInfo'][ $id ]['cha_cost'])) {
                    $this->{$target}['data'][ $id ]['cur_cha'] -= $this->{$target}['actionInfo'][ $id ]['cha_cost'];
                }

                //    One hit KO user
                if (isset($this->{$target}['actionInfo'][ $id ]['KO'])) {
                    if ($this->{$target}['actionInfo'][ $id ]['KO'] == 'hit') {

                        // Temporary variables for readability
                        $tempType = $this->{$target}['actionInfo'][ $id ]['KOtargetType'];
                        $tempID = $this->{$target}['actionInfo'][ $id ]['targetIDs'][0];

                        // Update the user specified in the tag with 0 HP
                        $this->{$tempType}['data'][ $tempID ]['cur_health'] = 0;
                    }
                }

                //    User rob ryo
                if (isset($this->{$target}['actionInfo'][ $id ]['stolen'])) {
                    if ($this->{$target}['actionInfo'][ $id ]['stolen'] > 0) {
                        if( $GLOBALS['database']->execute_query("UPDATE `users_statistics`
                            SET `money` = `money` - '" . $this->{$target}['actionInfo'][ $id ]['stolen'] . "'
                            WHERE `money` - '" . $this->{$target}['actionInfo'][ $id ]['stolen'] . "' >= 0
                                AND `uid` = '" . $this->{$target}['actionInfo'][ $id ]['stolenID'] . "' LIMIT 1") ){

                            // Update database now. TODO: move this to summary
                            $GLOBALS['database']->execute_query("UPDATE `users_statistics`
                                SET `money` = `money` + '" . $this->{$target}['actionInfo'][ $id ]['stolen'] . "'
                                WHERE `uid` = '" . $this->{$target}['data'][ $id ]['id'] . "'");
                        }
                    }
                }

                // Prevent negative numbers for user health
                if ($this->{$target}['data'][ $id ]['cur_health'] < 0) {
                    $this->{$target}['data'][ $id ]['cur_health'] = 0;
                }
            }
        }

        //  All actions performed. Determine next step in the battle
        protected function figureNextBattleStage() {

            // Reset user stats and determines whether anyone is alive to continue
            $this->reset_stats('user');
            $this->reset_stats('opponent');

            // Go through the lost users and handle removing them etc.
            $this->inactivate_users();

            if ($this->usercontinue == false && $this->opponentcontinue == false) {

                // All die simultaneously
                $this->update_doubleKO();

            } elseif ($this->usercontinue == false || $this->opponentcontinue == false) {

                // Upload win for remaining users on winning side
                switch( true ){
                    case $this->usercontinue == false:
                        $this->update_win('opponent');
                    break;
                    case $this->opponentcontinue == false:
                        $this->update_win('user');
                    break;
                }

            } elseif ($this->usercontinue == true && $this->opponentcontinue == true) {

                // Update battle
                $this->update_continue( 1 );

                // If all were stunned
                if ($this->all_stunned == 1) {
                    // Eh? shouldn't be neccesary to do anything special in this case.
                }
            }
            else{
                echo"<br><b>System is confused, no ideas what action to take</b><br>";
            }
        }

        // Set user as loser. Used in update_win and inactivate_users
        protected function set_user_as_loser( $side, $id, $status ){
            // Stuff to do for users but not AI
            if( !$this->is_user_ai($side, $id) ){

                // Set the final information for the user (uploaded on summary page)
                $this->{$side}['summary'][ $id ]['end_status'] = $status;

                // Health Check
                if ( $this->{$side}['data'][ $id ]['cur_health'] < 0 ) {
                    $this->{$side}['data'][ $id ]['cur_health'] = 0;
                }

                // Only give fled statistic to PVP battles
                if( $this->is_user_fleeing($side, $id) ){

                    // Set final status
                    $this->{$side}['summary'][ $id ]['battle_conclusion'] = "fled";

                    // Determine if PVP or AI battle
                    if( preg_match("/(kage|clan|combat|territory)/", $this->battle[0]['battle_type']) ){
                        $this->{$side}['summary'][ $id ]['PVP_fled'] = 1;
                    }
                    elseif( $this->battle[0]['battle_type'] == "spar" ){
                        // No counting
                    }
                    else{
                        $this->{$side}['summary'][ $id ]['AI_fled'] = 1;
                    }
                }
                else{

                    // Set final status
                    $this->{$side}['summary'][ $id ]['battle_conclusion'] = "lost";

                    // Determine if PVP or AI battle
                    if( preg_match("/(kage|clan|combat|territory)/", $this->battle[0]['battle_type']) ){
                        $this->{$side}['summary'][ $id ]['PVP_lost'] = 1;
                    }
                    elseif( $this->battle[0]['battle_type'] == "spar" ){
                        // No counting
                    }
                    else{
                        $this->{$side}['summary'][ $id ]['AI_lost'] = 1;
                    }
                }
            }
            else{
                // If the one finished is a summon
                if ($this->{$side}['data'][ $id ]['type'] == 'summon') {
                    $summonSide = $this->{$side}['data'][ $id ]['summontype'];
                    $summonId = $this->{$side}['data'][ $id ]['summonerID'];
                }
            }
        }

        // Function which effectively removes a user
        protected function removeUser( $side , $uid ){

            // In combat, check if all users got their combat log updated from this user
            if ($this->battle[0]['battle_type'] == 'combat' || $this->battle[0]['battle_type'] == 'exiting_combat' ) {

                // Call the cache udpate function, but only treat the single user
                $this->update_win_chacheCombatLog( $this->get_other_side($side), "wins", false, $uid );
                $this->update_win_chacheCombatLog( $side, "losses", $uid );
            }

            // Unset variables
            unset( $this->{$side}['data'][ $uid ] );
            unset( $this->{$side}['action'][ $uid ] );
            unset( $this->{$side}['actionInfo'][ $uid ] );
            unset( $this->{$side}['rewards'][ $uid ] );
            unset( $this->{$side}['summary'][ $uid ] );
            unset( $this->{$side}['items'][ $uid ] );
            unset( $this->{$side}['jutsus'][ $uid ] );

            // Add to removed users array
            $this->battle[0]['removed_userids'] .= $uid."|||";

            // Remove id from ID array
            for ($i = 0; $i < count($this->{$side}['ids']); $i++) {
                if( $this->{$side}['ids'][$i] == $uid){
                    // Unset this id
                    unset( $this->{$side}['ids'][$i] );
                    // Reset the indexes
                    $this->{$side}['ids'] = array_values($this->{$side}['ids']);
                }
            }
        }


        // This inactivates users who are dead, and updates exp to the winners etc
        private function inactivate_users() {

            // If it's a combat, we need to instantiate the war library
            if ($this->battle[0]['battle_type'] == 'combat' || $this->battle[0]['battle_type'] == 'exiting_combat' ) {
                $this->warLib = new warLib();
            }

            // Do both sides. The $side-variable stores the users currently being de-activated
            foreach( array("user","opponent") as $side ){

                // Get the other side. These people will get stuff from the losers
                $otherSide = $this->get_other_side($side);

                // Loop through the dead users of this side, get their exp etc
                $loserFactor = $winnerFactor = 0;
                foreach( $this->{$side . '_lostplayers'} as $id ){

                    // Only if user is currently active
                    if( $this->is_user_active($side, $id) ){

                        // Inactivate this user
                        $this->inactivateUser($side, $id);

                        // For experience calculation for the winners
                        if( !$this->is_user_summon($side, $id) ){
                            $loserFactor = $this->{$side}['data'][ $id ]['rank_id'] * 150;
                        }

                        // Update stuff used on the summary page for the given user
                        $this->set_user_as_loser($side, $id, "hospitalized");

                        // If anyone of these peolple called for reinforce, decrement the call
                        if( isset($this->{$side}['actionInfo'][ $id ]['reinforcementCall']) &&
                            $this->{$side}['actionInfo'][ $id ]['reinforcementCall'] > 0){
                            $this->battle[0][ $side . '_help' ]--;
                        }
                    }
                }

                // If this is an ordinary combat, we should award users with bounties etc.
                if ($this->battle[0]['battle_type'] == 'combat') {

                    // Only run this if someone has been set to "continue" in the battle. I.e. not if everyone fled etc
                    if( !($this->usercontinue == false && $this->opponentcontinue == false) ){

                        // Upload ANBU points for these lost users
                        $this->update_win_anbu($side, $otherSide);

                        // Upload bounty money for these lost users
                        $this->update_win_bounty($side, $otherSide);

                        // Upload clan points for these lost users
                        $this->update_win_clan($side, $otherSide);

                        // Upload Village Funds and PVP experience for these lost users
                        $this->update_win_villagePvp($side, $otherSide);

                    }
                }

                // Loop through other side, determine negative exp
                foreach( $this->{$otherSide}['ids'] as $id ){

                    // Update the winner factor
                    $winnerFactor += $this->{$otherSide}['data'][ $id ]['rank_id'] * 50;

                }

                // Determine final exp
                $exp_gain = ($loserFactor - $winnerFactor);
                if ($exp_gain <= 0) {
                    $exp_gain = 1;
                }

                // Loop through and upload the experience to the side not dieing
                foreach( $this->{$otherSide}['ids'] as $id ){
                    if ( !$this->is_user_ai($otherSide, $id) ) {

                        // Give experience
                        $this->{$otherSide}['summary'][ $id ]['exp_gain'] += $exp_gain;

                    }
                }


            }

        }

        // BELOW ARE ALL BATTLE ACTIONS

        /*        Standard Chakra attack      */
        protected function do_chakra($user, $userid, $target, $targetid) {
            // Figure out if "his" or "her"
            $useritem = $this->getHisHer( $this->{$user}['data'][ $userid ]['gender'] );

            // Damage
            $damage = calc::calc_double_damage(
                array(
                    "user_data" => $this->{$user}['data'][ $userid ],
                    "target_data" => $this->{$target}['data'][ $targetid ],
                    "type1" => 'nin',
                    "type2" => 'gen',
                    "stat1" => 'willpower',
                    "stat2" => 'intelligence',
                    "power" => 1000,
                    "scalePower" => 0.1
                )
            );

            // Set array with information for action
            $tempActionInfo = array(
                "element" => "none",
                "message" => $this->{$user}['data'][ $userid ]['username'] . ' attacks using ' . $useritem . ' chakra',
                "description" => "",
                "damage" => array($targetid => $damage + random_int(5, 15)),
                "targetType" => $target,
                "targetIDs" => array($targetid),
                "type" => "NG"
            );
            $this->addActionInfoArrayToUser( $user, $userid, $tempActionInfo );

        }

        /*        Standard Taijutsu attack      */
        protected function do_taijutsu($user, $userid, $target, $targetid) {
            // Figure out if "his" or "her"
            $useritem = $this->getHisHer( $this->{$user}['data'][ $userid ]['gender'] );

            // Damage
            $damage = calc::calc_double_damage(
                array(
                    "user_data" => $this->{$user}['data'][ $userid ],
                    "target_data" => $this->{$target}['data'][ $targetid ],
                    "type1" => 'tai',
                    "type2" => 'weap',
                    "stat1" => 'strength',
                    "stat2" => 'speed',
                    "power" => 1000,
                    "scalePower" => 0.1
                )
            );

            $tempActionInfo = array(
                "element" => "none",
                "message" => $this->{$user}['data'][ $userid ]['username'] . ' attacks using ' . $useritem . ' taijutsu',
                "description" => "",
                "damage" => array($targetid => $damage + random_int(5, 15)),
                "targetType" => $target,
                "targetIDs" => array($targetid),
                "type" => "T"
            );
            $this->addActionInfoArrayToUser( $user, $userid, $tempActionInfo );

        }

        /*      Standard Flee Action        */
        protected function do_flee($user, $userid) {

            // Try fleeing
            $this->try_fleeing($user, $userid, 5);

            // Output message
            $this->{$user}['actionInfo'][ $userid ]['message'] = $this->{$user}['data'][ $userid ]['username'] . ' attempted to flee from battle';
            $this->{$user}['actionInfo'][ $userid ]['description'] = "";
        }

        /*      Standard Call for Help Action   */
        protected function do_help($user, $userid) {

            // Figure out if "his" or "her"
            $useritem = $this->getHisHer( $this->{$user}['data'][ $userid ]['gender'] );
            $opponentSide = $this->get_other_side($user);

            // Get the other side
            $canCall = $this->can_user_call_help($user, $userid);
            if( $canCall === "true" ){

                // Set costs as 1% of total
                $chakra_cost = floor($this->{$user}['data'][ $userid ]['max_cha'] * 0.01);
                $stamina_cost = floor($this->{$user}['data'][ $userid ]['max_sta'] * 0.01);

                // Update user information
                $this->{$user}['actionInfo'][ $userid ]['message'] = $this->{$user}['data'][ $userid ]['username'] . ' calls for help. Calling for help drains 1% of ' . $this->{$user}['data'][ $userid ]['username'] . '\'s chakra and stamina and prevents ' . $this->{$user}['data'][ $userid ]['username'] . ' from healing properly for 5 rounds. Another player may now enter the battle.';
                $this->{$user}['data'][ $userid ]['battle_rounds'] = 1;
                $this->{$user}['data'][ $userid ]['cur_cha'] -= $chakra_cost;
                $this->{$user}['data'][ $userid ]['cur_sta'] -= $stamina_cost;

                // Update the user reinforcements information. This lets the outside system know the user called for battle
                // It is uploaded to the database on each round end, along with chakra & stamina. It is also reset to 0
                // automatically every time a user leaves a battle
                $this->{$user}['data'][ $userid ]['reinforcements']++;

                // Save the number of reinforcement calls
                if( !isset($this->{$user}['actionInfo'][ $userid ]['reinforcementCall']) ){
                    $this->{$user}['actionInfo'][ $userid ]['reinforcementCall'] = 0;
                }
                $this->{$user}['actionInfo'][ $userid ]['reinforcementCall']++;

                // Prevent user from summoning anything and hopefully summon
                foreach($this->{$user}['data'] as $key => $value)
                {
                    $this->{$user}['data'][ $key ]['summons'] = 1;
                }
                //echo'user: ';
                //print_r($user);
                //echo'<br><br>user data: ';
                //print_r($this->{$user}['data']);
                //echo'<br><br>$userid: '.$userid;
                //echo'<br><br>$_SESSION: '.$_SESSION['uid'];


                // Update the battle variable
                $this->battle[0][ $user . '_help' ]++;

            }
            else{
                // Show the user why he/she failed.
                $this->{$user}['actionInfo'][ $userid ]['message'] = $this->{$user}['data'][ $userid ]['username'] . ' tries to call for help: '.$canCall;
            }

            // Have to add a description to avoid errors
            $this->{$user}['actionInfo'][ $userid ]['description'] = "";
        }

        // Function for loading neural net library
        private function loadNeuralNet(){
            if( !isset($this->loadedNeuralNetwork) ){
                require_once(Data::$absSvrPath.'/global_libs/machineLearning/neuralnetwork.php');
                $this->loadedNeuralNetwork = true;
            }
        }



        /*      AI action     */
        public function ai_move($aiside, $aiid, $targettype ) {

            // Get a random number
            $rand = random_int(1, 4);

            // Pick a random target ID & make sure it exists
            $randomtargetid = $this->{$targettype}['ids'][ random_int(0, count($this->{$targettype}['ids']) - 1) ];
            if( !$this->is_user_active($targettype, $randomtargetid) ){
                $i = 0;
                while ($i < 50 && !$this->is_user_active($targettype, $randomtargetid) ) {
                    $randomtargetid = $this->{$targettype}['ids'][ random_int(0, count($this->{$targettype}['ids']) - 1) ];
                    $i++;
                }
            }

            // Add the input vector + output vector to the AI for saving
            if( !isset($this->{$aiside}['data']['' . $aiid . '']['ai_history']) ){
                $this->{$aiside}['data']['' . $aiid . '']['ai_history'] = array();
                $this->{$aiside}['data']['' . $aiid . '']['ai_predictions'] = array();
            }

            // Determine AI move
            $action = "";
            if ( !isset($this->{$aiside}['data']['' . $aiid . '']['ai_actions']) || empty($this->{$aiside}['data']['' . $aiid . '']['ai_actions']) ) {
                // Default AI move to STTAI
                $action = "STTAI";
            }
            else{

                // Figure out how to determine which action to perform
                $intelligenceType = $this->ai_getIntelligenceType($aiside, $aiid);

                // Get the action in question
                switch( $intelligenceType ){
                    case "random": $action = $this->aiAction_random( $aiside, $aiid ); break;
                    case "ANN-M1": $action = $this->aiAction_ANN_m1( $aiside, $aiid, $targettype, $randomtargetid ); break;
                    case "TerrAlgoV1": $action = $this->aiAction_AlgoV1( $aiside, $aiid, $targettype, $randomtargetid ); break;
                }
            }

            // Submit the action
            $this->do_battle_op(
                    $aiside,                        // Side of the attacker
                    $aiid,                          // ID of the attacker
                    $targettype,                    // Side of the target
                    $randomtargetid,                // ID of the target
                    $this->{$targettype}['ids'],    // IDs of other on target side
                    $action,                        // Action to be performed
                    $this->{$aiside}['ids']         // Attacker companion IDs
            );
        }

        // Rule-based AI
        private function aiAction_AlgoV1( $aiside, $aiid , $targetSide, $targetID ){

            // Get actions
            $actionChoices = explode(";",$this->{$aiside}['data']['' . $aiid . '']['ai_actions']);
            $actionChoices = array_filter($actionChoices);

            //echo"<pre />=============================================";
            //echo"<b>AI ID: </b>".$aiid."<br>";
            //echo"<b>ActionChoices</b><br><br>";
            //print_r($actionChoices);

            // Everything starts out with equal chances
            $predictions = array_fill( 0, count($actionChoices),1 );

            // Store the previous action
            $prevActions = array_reverse($this->{$aiside}['data']['' . $aiid . '']['ai_predictions']);
            //echo"<pre />";
            //print_r($prevActions);

            // Control restart of chain
            $restartChain = "nope";

            // Go through all the actions
            foreach( $actionChoices as $key => $action ){
                $action = explode(':', $action);

                // Jutsu chaining
                if( isset($action[2],$action[3]) && $action[2] == "chain"){
                    if( !empty($prevActions) ){
                        if( (int)$action[3] == 1 ){
                            $restartChain = $key;
                        }
                        $prevAction = explode(':', $actionChoices[$prevActions[0]] );
                        if( isset($prevAction[2],$prevAction[3]) && $prevAction[2] == "chain"){
                            if( (int)$action[3] !== (int)$prevAction[3]+1 ){
                                // Previous action was part of chain, so only allow the next action in the chain
                                $predictions[$key] = 0;
                            }
                            else{
                                $restartChain = "nope";
                            }
                        }
                        elseif( (int)$action[3] !== 1 ){
                            // Prev action was not part of chain, so allow only first key in chain
                            $predictions[$key] = 0;
                        }
                    }
                    elseif( (int)$action[3] !== 1 ){
                        // Only allow to use first element in chain if no history is defined yet
                        $predictions[$key] = 0;
                    }
                }

                // If the action has a trigger effect, only work if that effect is triggered
                if( isset($action[2],$action[3]) && $action[2] == "healthTrigger"){
                    if( 100*$this->{$aiside}['data']['' . $aiid . '']['cur_health'] / $this->{$aiside}['data']['' . $aiid . '']['max_health'] > $action[3] ){
                        $predictions[$key] = 0;
                    }
                }

                // Figure out if there's a cooldown on this action; if so, then chance = 0
                if( $action[0] == "JUT" ){
                    if( $actionData = $this->know_jutsu( $action[1] , $aiid , $aiside ) ){
                        if( $actionData > 0 ){
                            $predictions[$key] -= 10;
                        }
                    }
                }
            }

            // If we're restarting chain, do it
            //echo"<b>Resetting chain:</b> ".$restartChain."<br>";
            if( $restartChain !== "nope" ){
                $predictions[$restartChain] += 1;
            }


            // $prediction = $actionChoices[ random_int(0,count($actionChoices)-1) ];
            $maxs = array_keys($predictions, max($predictions));
            $prediction = $actionChoices[ $maxs[ random_int(0,count($maxs)-1) ] ];

            // Store AI moves throughout battle
            $this->{$aiside}['data']['' . $aiid . '']['ai_predictions'][] = $maxs[0];

            //echo"<pre /><b>Predictions</b><br><br>";
            //print_r($predictions);
            //die("TESTING: ".$prediction);
            //echo"PREDICTION: ".$prediction."<br><br>";

            return $prediction;
        }

        // Choose AI action based on ANN prediction
        private function aiAction_ANN_m1( $aiside, $aiid , $targetSide, $targetID ){

            // Load library
            $this->loadNeuralNet();

            // Get the action choices
            $actionChoices = explode(";",$this->{$aiside}['data']['' . $aiid . '']['ai_actions']);
            $actionChoices = array_filter($actionChoices);

            // Start the ANN
            $annModel      = new ANN_M1( $this->{$aiside}['data']['' . $aiid . ''] );

            // Make prediction on which action is best
            $predictKey    = $annModel->determineAction(
                $this->{$targetSide}['data']['' . $targetID . ''],
                $this->round,
                $this->{$aiside}['data']['' . $aiid . '']['ai_predictions']
            );
            $prediction    = $actionChoices[ $predictKey ];

            // Store predictions
            $this->{$aiside}['data']['' . $aiid . '']['ai_history'] = array_merge($this->{$aiside}['data']['' . $aiid . '']['ai_history'], $annModel->actions);
            $this->{$aiside}['data']['' . $aiid . '']['ai_predictions'][] = $predictKey;

            // Return the prediction to the battle system to act upon
            return $prediction;
        }

        // Choose AI action at random
        private function aiAction_random( $aiside, $aiid ){
            $actionChoices = explode(";",$this->{$aiside}['data']['' . $aiid . '']['ai_actions']);
            $actionChoices = array_filter($actionChoices);
            $prediction = $actionChoices[ random_int(0,count($actionChoices)-1) ];
            return $prediction;
        }

        // AI Training and/or storing of battle information
        private function ai_Training( $aiside, $aiid, $didWin ){
            if($this->ai_hasBattleHistory($aiside, $aiid)){

                // Get intelligence
                $intelligenceType = $this->ai_getIntelligenceType($aiside, $aiid);

                // Perform Training (Applicable to some AIs)
                if( $this->ai_isLiveLearning($aiside, $aiid) ){
                    switch( $intelligenceType ){
                        case "ANN-M1":
                            $this->loadNeuralNet();
                            $annModel = new ANN_M1( $this->{$aiside}['data']['' . $aiid . ''] );
                            $annModel->reinforceANN($this->{$aiside}['data']['' . $aiid . '']['ai_history'], $didWin);
                            $GLOBALS['database']->execute_query("UPDATE `ai` SET `NeuralNet` = '".$annModel->getAllData()."' WHERE `id` = '".$this->{$aiside}['data']['' . $aiid . '']['original_id']."' LIMIT 1 ");
                        break;
                    }
                }

                // Store the data from the battle
                if( $this->ai_isStoringData($aiside, $aiid) ){
                    $GLOBALS['database']->execute_query("
                        INSERT INTO `log_aiBattleData`
                            (`ai_id`,`time`,`battle_id`,`battleHistory`,`didWin`,`rsf`)
                            VALUES
                            ('".$aiid."',
                             UNIX_TIMESTAMP(),
                             '".$this->battle[0]['id']."',
                             '".serialize($this->{$aiside}['data']['' . $aiid . '']['ai_history'])."',
                             '".$didWin."',
                             '".$this->get_RelativeStrengthFactor( $aiside )."'
                     )");
                }
            }
        }


        // BELOW ARE EVERYTHING RELATED TO ROUND ACTIONS, I.E. THINGS TO HAPPEN AFTER ENDED ROUND

        // Battle Continues
        private function update_continue( $nextBattleStage ) {

            // Go through users side
            foreach(array("user", "opponent") as $side) {
                foreach($this->{$side}['ids'] as $id) {

                    // Reinforcement call
                    $reinforceUpdate = "";
                    if( isset($this->{$side}['actionInfo'][ $id ]['reinforcementCall']) ){
                        $reinforceUpdate = " users_statistics.reinforcements = users_statistics.reinforcements + ".$this->{$side}['actionInfo'][ $id ]['reinforcementCall'].", ";
                    }

                    // Stuff we don't need in next round
                    unset($this->{$side}["action"][ $id ]);
                    unset($this->{$side}["actionInfo"][ $id ]);

                    // For all users in the battle, update current health
                    if (!$this->is_user_ai($side, $id)) {

                        // Make sure we don't go to negatives
                        if ($this->{$side}['data'][ $id ]['cur_health'] < 0) {
                            $this->{$side}['data'][ $id ]['cur_health'] = 0;
                        }

                        // Update database
                        $GLOBALS['database']->execute_query("UPDATE `users_statistics`
                                SET users_statistics.cur_health = ".$this->{$side}['data'][ $id ]['cur_health'].",
                                    ".$reinforceUpdate."
                                    users_statistics.cur_sta = ".$this->{$side}['data'][ $id ]['cur_sta'].",
                                    users_statistics.cur_cha = ".$this->{$side}['data'][ $id ]['cur_cha']."
                                WHERE users_statistics.uid = ".$id." LIMIT 1");

                        // Update globals variable (for menu etc)
                        if($id === $_SESSION['uid']) {
                            $GLOBALS['userdata'][0]['cur_health'] = $this->{$side}['data'][ $id ]['cur_health'];
                            $GLOBALS['userdata'][0]['cur_sta'] = $this->{$side}['data'][ $id ]['cur_sta'];
                            $GLOBALS['userdata'][0]['cur_cha'] = $this->{$side}['data'][ $id ]['cur_cha'];
                        }
                    }
                }
            }

            // First we need to sort the battle round log. First
            // we create an array with all the sub-entries and main entries for each owner
            $subEntries = $mainEntries = array();
            foreach( $this->roundBattleLog as $entry ){
                if( $entry['type'] == "subEntry" ){

                    // Check if owner is already in the array. If not, add
                    if( !isset($subEntries[ $entry['owner'] ]) ){
                        $subEntries[ $entry['owner'] ] = array();
                    }

                    // Add entry under this owner
                    $subEntries[ $entry['owner'] ][] = $entry;
                }
                elseif( $entry['type'] == "main" ){

                    // Check if owner is already in the array. If not, add
                    if( !isset($mainEntries[ $entry['owner'] ]) ){
                        $mainEntries[ $entry['owner'] ] = array();
                    }

                    // Add entry under this owner
                    $mainEntries[ $entry['owner'] ][] = $entry;
                }
            }

            // Add this round log to the current battle log.
            // First put empty array with index of this round in front of current log
            $this->battle[0]['log'] = array( $this->round => array() ) + $this->battle[0]['log'];


            // Then loop through all main entries
            foreach( $mainEntries as $owner => $entry ){

                // Add main entry
                $this->battle[0]['log'][$this->round][] = $entry[0];

                // Check for subEntries
                if( isset($subEntries[ $owner ]) ){
                    foreach( $subEntries[ $owner ] as $s_entry ){
                        $this->battle[0]['log'][$this->round][] = $s_entry;
                    }
                }
            }


            // Encode the battle log
            $this->battle[0]['log'] = base64_encode(serialize( $this->battle[0]['log'] ));

            // New Battle Stage
            $this->battle[0]['stage'] = $nextBattleStage;

            // Update the battle with this data
            $this->update_battle_playerData(
                    array(
                        "user" => true ,
                        "opponent" => true,
                        "stage" => true,
                        "log" => true
                    )
            );
        }

        // Battle has been concluded with a winner
        private function update_win( $winnerSide ) {

            // Get the other side
            $losingSide = $this->get_other_side( $winnerSide );

            // Get the IDs of all users that are not AI
            $winnerIds = array();
            foreach( $this->{$winnerSide}['ids'] as $id ){
                if( !$this->is_user_ai($winnerSide, $id) ){
                    $winnerIds[] = $id;
                }
            }

            // Get the IDs of all opponents that are not AI
            $loserIds = array();
            foreach( $this->{$losingSide}['ids'] as $id ){
                if( !$this->is_user_ai($losingSide, $id) ){
                    $loserIds[] = $id;
                }
            }

            // Set the default status of the user after tha battle
            $status = 'hospitalized';

            // Handle the different battle situations
            switch( $this->battle[0]['battle_type'] ){
                case "spar":
                    $status = 'awake';
                    foreach ($this->{$losingSide}['ids'] as $id) {
                        if ( !$this->is_user_fleeing($losingSide, $id) && !(isset($this->{$side}['data'][ $id ]['summontype']) && $this->{$side}['data'][ $id ]['summontype'] == 'user') ) {
                            $this->{$losingSide}['data'][ $id ]['cur_health'] = 1;
                        }
                    }
                    break;
                case "kage":

                    // Get the kage ID
                    $kageID = $this->{$losingSide}['ids'][0];
                    $winnerID = $this->{$winnerSide}['ids'][0];
                    $kageVillage = $this->user['data'][ $this->user['ids'][0] ]['village'];

                    // Check if kage or attacker (user) won
                    if ($winnerSide == "user") {

                        // Only count if the kage/leader was acutally beat
                        if (
                                !$this->is_user_fleeing($losingSide, $kageID) ||
                                ($this->is_user_fleeing($losingSide, $kageID) && $this->has_submitted_action("user", $winnerID ) )
                        ) {

                            // Update the kage of the village
                            $query = "UPDATE `villages` SET `leader` = '" . $this->{$winnerSide}['data'][ $winnerID ]['username'] . "' WHERE `name` = '" . $kageVillage . "' LIMIT 1";
                            $GLOBALS['database']->execute_query($query);

                            //set kage title
                            if($kageVillage == "Konoki")
                                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Morikage' WHERE `uid` = ".$winnerID);
                            else if($kageVillage == "Samui")
                                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Kusakage' WHERE `uid` = ".$winnerID);
                            else if($kageVillage == "Shine")
                                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Sunakage' WHERE `uid` = ".$winnerID);
                            else if($kageVillage == "Shroud")
                                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Suikage' WHERE `uid` = ".$winnerID);
                            else if($kageVillage == "Silence")
                                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Yamakage' WHERE `uid` = ".$winnerID);
                            else if($kageVillage == "Syndicate")
                                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = 'Warlord' WHERE `uid` = ".$winnerID);

                            //remove kage title
                            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = '".functions::getRank($this->{$losingSide}['data'][ $kageID ]['rank_id'], $kageVillage)."' WHERE `uid` = ".$kageID);

                            // Set summary details
                            $this->{$winnerSide}['summary'][ $winnerID ]['kage'] += 1;
                        }
                    } else {

                        // Set the loser status to jailed
                        $status = 'jailed';

                        // Alive but in jail
                        foreach ($this->{$losingSide}['ids'] as $id) {
                            if ( !$this->is_user_fleeing($losingSide, $id) ) {
                                $this->{$losingSide}['data'][ $id ]['cur_health'] = 1;
                            }
                        }

                        // Get respect lost (village loyalty pts)
                        $respectLoss = $this->{$losingSide}['data'][ $kageID ]['vil_loyal_pts'] * 0.25;

                        // Summary screen information
                        $this->{$losingSide}['summary'][ $kageID ]['respectLoss'] += $respectLoss;
                    }

                    break;
                case "clan":

                    // Get the leader ID
                    $leaderID = $this->{$losingSide}['ids'][0];
                    $winnerID = $this->{$winnerSide}['ids'][0];
                    $clanID = $this->user['data'][ $this->user['ids'][0] ]['clanID'];

                    // Check if leader or attacker (user) won
                    if ($winnerSide == "user") {

                    // Only count if the leader was acutally beat
                        if (
                                !$this->is_user_fleeing($losingSide, $leaderID) ||
                                ($this->is_user_fleeing($losingSide, $leaderID) && $this->has_submitted_action("user", $winnerID ) )
                        ) {

                            // Update the leader of the village
                            $query = "UPDATE `clans` SET `leader_uid` = '" . $winnerID . "' WHERE `id` = '" . $clanID . "' LIMIT 1";
                            $GLOBALS['database']->execute_query($query);

                            // Set summary details
                            $this->{$winnerSide}['summary'][ $winnerID ]['clanLeader'] += 1;
                        }
                    } else {

                        // Keep the user on his feet
                        $status = 'awake';
                        foreach ($this->{$losingSide}['ids'] as $id) {
                            if ( !$this->is_user_fleeing($losingSide, $id) && !(isset($this->{$side}['data'][ $id ]['summontype']) && $this->{$side}['data'][ $id ]['summontype'] == 'user') ) {
                                $this->{$losingSide}['data'][ $id ]['cur_health'] = 1;
                            }
                        }

                        // Get respect lost
                        $respectLoss = $this->{$losingSide}['data'][ $leaderID ]['vil_loyal_pts'] * 0.10;

                        // Summary screen information
                        $this->{$losingSide}['summary'][ $leaderID ]['respectLoss'] += $respectLoss;
                    }

                    break;
                case "combat":
                    // Everything special for combat is already handled
                    // in the inactivate_users() function
                    break;
                case "arena":

                    // Get the winner ID
                    $winnerID = $this->{$winnerSide}['ids'][0];

                    if ($winnerSide == "user") {

                        // HP, CP and SP gains
                        $pool_gain = $cha_gain = $sta_gain = 0.10;
                        $gen_gain = 0.1 * $this->{$winnerSide}['data'][ $winnerID ]['rank_id'];
                        $ryo_gain = $this->{$winnerSide}['data'][ $winnerID ]['level_id'];

                        // Check for global event modifications
                        if( $event = functions::getGlobalEvent("IncreasedArenaAll") ){
                            if( isset( $event['data']) && is_numeric( $event['data']) ){
                                $pool_gain *= round($event['data'] / 100,2);
                                $gen_gain *= round($event['data'] / 100,2);
                                $ryo_gain *= round($event['data'] / 100,2);
                            }
                        }

                        // ryo_gain
                        $this->{$winnerSide}['summary'][ $winnerID ]['ryo_gain'] += $ryo_gain;

                        /* Update reward array
                        $this->{$winnerSide}['summary'][ $winnerID ]['health_gain'] += $pool_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['chakra_gain'] += $pool_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['stamina_gain'] += $pool_gain;

                        $this->{$winnerSide}['summary'][ $winnerID ]['strength_gain'] += $gen_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['intelligence_gain'] += $gen_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['willpower_gain'] += $gen_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['speed_gain'] += $gen_gain; */

                        // Cap stuff
                        $this->setGainCaps($winnerSide, $winnerID);
                    }
                    break;
                case "mirror_battle":

                    // Get the winner ID
                    $winnerID = $this->{$winnerSide}['ids'][0];

                    if ($winnerSide == "user") {

                        // HP, CP and SP gains
                        $pool_gain = $cha_gain = $sta_gain = 0.10;
                        $gen_gain = 0.1 * $this->{$winnerSide}['data'][ $winnerID ]['rank_id'];

                        // Update reward array
                        $this->{$winnerSide}['summary'][ $winnerID ]['health_gain'] += $pool_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['chakra_gain'] += $pool_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['stamina_gain'] += $pool_gain;

                        $this->{$winnerSide}['summary'][ $winnerID ]['strength_gain'] += $gen_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['intelligence_gain'] += $gen_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['willpower_gain'] += $gen_gain;
                        $this->{$winnerSide}['summary'][ $winnerID ]['speed_gain'] += $gen_gain;

                        // Cap stuff
                        $this->setGainCaps($winnerSide, $winnerID);
                    }
                    break;
                case "torn_battle":

                    // Get the winner ID
                    $winnerID = $this->{$winnerSide}['ids'][0];

                    if ($winnerSide == "user") {

                        //    Get opponent number
                        $this->{$winnerSide}['summary'][ $winnerID ]['next_battle'] = $this->opponent['ids'][0];

                    }
                    else{
                        $status = 'awake';
                        foreach ($this->user['ids'] as $id) {
                            if ( !$this->is_user_fleeing("user", $id) && !(isset($this->{$side}['data'][ $id ]['summontype']) && $this->{$side}['data'][ $id ]['summontype'] == 'user') ) {
                                $this->user['data'][ $id ]['cur_health'] = 1;
                            }
                        }
                    }


                    break;
                case "territory":

                    // Determine winner
                    $Tchallenge = ($winnerSide == "user") ? "challenger" : "challenged";

                    // Find the territory challenge
                    $terr_challenge = $GLOBALS['database']->fetch_data("SELECT * FROM `territory_challenges` LIMIT 1");
                    if ($terr_challenge != "0 rows") {

                        // Update the territory challenge with winner
                        $column = "";
                        switch( $this->battle[0]['mission_id'] ){
                            case 3: $column = "chuuninWinner"; break;
                            case 4: $column = "jouninWinner"; break;
                            case 5: $column = "specialjouninWinner"; break;
                        }

                        // Update
                        $GLOBALS['database']->execute_query("UPDATE `territory_challenges` SET `".$column."` = '".$Tchallenge."' WHERE `id` = '" . $terr_challenge[0]['id'] . "' LIMIT 1");

                    }

                    break;
                case "mission":
                case "crime":

                    // Set winner ID
                    $winnerID = $this->{$winnerSide}['ids'][0];

                    // Only relevant if user is the winning side
                    if ($winnerSide == "user") {

                        // Get the winner ID & tasks
                        $this->userTasks = cachefunctions::getUserTasks( $winnerID );
                        $this->userTasks = json_decode($this->userTasks[0]['tasks'] , true);

                        // Check the user tasks
                        if( !empty($this->userTasks) ){

                            // Get active mission ID
                            $missionID = "";
                            foreach( $this->userTasks as $key => $value ){
                                if( $value == "m" ){
                                    $missionID = $key;
                                }
                            }

                            //    Fetch mission stat gains and upload them:
                            $mission = cachefunctions::getTasksQuestsMission( $missionID );

                            if( $mission !== "0 rows" ){

                                // Check if there's an info message for this or if just to put a default one
                                if( $mission[0]['simpleGuide'] !== "" ){

                                    $mission[0]['simpleGuide'] = str_replace( "\n", "", $mission[0]['simpleGuide'] );
                                    $mission[0]['simpleGuide'] = str_replace( "\r", "", $mission[0]['simpleGuide'] );
                                    $mission[0]['simpleGuide'] = explode(";",$mission[0]['simpleGuide']);

                                    foreach( $mission[0]['simpleGuide'] as $entry ){
                                        $result = explode(":", $entry);
                                        if( $result[0] == "battle" ){
                                            $this->{$winnerSide}['summary'][ $winnerID ]['mission'] = $result[1];
                                        }
                                    }
                                }
                                if( $this->{$winnerSide}['summary'][ $winnerID ]['mission'] == "" ){
                                    $this->{$winnerSide}['summary'][ $winnerID ]['mission'] = "success";
                                }
                            }
                            else {
                                $this->{$winnerSide}['summary'][ $winnerID ]['mission'] = "fail";
                            }
                        }
                        else {
                            $this->{$winnerSide}['summary'][ $winnerID ]['mission'] = "fail";
                        }
                    } else {
                        $this->{$winnerSide}['summary'][ $winnerID ]['mission'] = "fail";
                    }

                    break;
                case "event":

                    // Get the winner ID
                    $winnerID = $this->{$winnerSide}['ids'][0];
                    $eventData = $GLOBALS['database']->fetch_data("SELECT * FROM `events_tiles` WHERE `id` = '" . $this->battle[0]['mission_id'] . "' LIMIT 1");
                    if ($eventData != '0 rows') {

                        // Check if winning battle has any consequences
                        if ($eventData[0]['data'] != '') {
                            $actions = explode(';', preg_replace('/\s+/', '', $eventData[0]['data']));
                            foreach( $actions as $action ){
                                $tmp_act = explode(":", $action);
                                if ($tmp_act[0] == 'GIVEITM' && is_numeric($tmp_act[1])) {
                                    $itmData = $GLOBALS['database']->fetch_data("SELECT `id`,`name`,`stack_size` FROM `items` WHERE `id` = '" . $tmp_act[1] . "' LIMIT 1");
                                    if ($itmData != '0 rows') {
                                        if ($itmData[0]['stack_size'] > 1) {
                                            foreach( $this->{$winnerSide}['ids'] as $id ){
                                                $GLOBALS['database']->execute_query("UPDATE `users_inventory` SET `stack` = `stack` + 1 WHERE `stack` < '" . $itmData[0]['stack_size'] . "' AND `iid` = '" . $itmData[0]['id'] . "' AND `uid` = '" . $id . "' LIMIT 1");
                                                if ($GLOBALS['database']->last_affected_rows !== 1) {
                                                    $GLOBALS['database']->execute_query("INSERT INTO `users_inventory` (`uid`, `iid`, `equipped`, `stack`, `timekey`) VALUES ('" . $id . "', '" . $itmData[0]['id'] . "', 'no', '1', '" . $this->timeStamp . "');");
                                                }
                                            }
                                        } else {
                                            foreach( $this->{$winnerSide}['ids'] as $id ){
                                                $GLOBALS['database']->execute_query("INSERT INTO `users_inventory` (`uid`, `iid`, `equipped`, `stack`, `timekey`) VALUES ('" . $id . "', '" . $itmData[0]['id'] . "', 'no', '1', '" . $this->timeStamp . "');");
                                            }
                                        }
                                        $this->{$winnerSide}['summary'][ $winnerID ]['items'][] = $itmData[0]['name'];
                                    }
                                }
                            }
                        }

                        // Log action if needed
                        if( $eventData[0]['enable_log'] == "yes" ){
                            functions::log_event_action($_SESSION['uid'], $eventData[0], "Won an event battle", $GLOBALS['user']->load_time);
                        }
                    }
                    break;
            }

            // First loop through all the losers
            foreach( $this->{$losingSide}['ids'] as $id ){

                if(!(isset($this->{$losingSide}['data'][ $id ]['summontype']) && $this->{$losingSide}['data'][ $id ]['summontype'] == 'user'))
                {
                    // Set this user as a loser and update battle summary details accordingly
                    $this->set_user_as_loser($losingSide, $id, $status);

                    // If AI, then train with negative example
                    if( $this->is_user_ai($losingSide, $id) ){
                        $this->ai_Training( $losingSide, $id, false );
                    }
                }
            }

            // Then loop through all the winners
            foreach( $this->{$winnerSide}['ids'] as $id ){

                //Set Status
                if ( !$this->is_user_ai($winnerSide, $id) && !(isset($this->{$winnerSide}['data'][ $id ]['summontype']) && $this->{$winnerSide}['data'][ $id ]['summontype'] == 'user')){
                    if($this->{$winnerSide}['summary'][ $id ]['battle_conclusion'] == "" ) {
                        $this->{$winnerSide}['summary'][ $id ]['battle_conclusion'] = 'won';
                    }
                }
                else{
                    // If AI, then train with negative example
                    $this->ai_Training( $winnerSide, $id, true );
                }
            }

            // Loop through all the winners again
            foreach( $this->{$winnerSide}['ids'] as $id ){

                // Only for non-ai
                if( !$this->is_user_ai($winnerSide, $id) && !(isset($this->{$winnerSide}['data'][ $id ]['summontype']) && $this->{$winnerSide}['data'][ $id ]['summontype'] == 'user') ){

                    // Check if user won
                    if ($this->{$winnerSide}['summary'][ $id ]['battle_conclusion'] == 'won') {

                        // Health Check
                        if ( $this->{$winnerSide}['data'][ $id ]['cur_health'] < 0 ) {
                            $this->{$winnerSide}['data'][ $id ] = 0;
                        }

                        // Only give won to PVP
                        if( preg_match("/(kage|clan|combat|territory)/", $this->battle[0]['battle_type']) ){
                            $this->{$winnerSide}['summary'][ $id ]['PVP_won'] = 1;
                        }
                        elseif( $this->battle[0]['battle_type'] == "spar" ){
                            // No counting
                        }
                        else{
                            $this->{$winnerSide}['summary'][ $id ]['AI_won'] = 1;
                        }
                    }
                }
            }


            // Set cache combat log
            $this->update_win_chacheCombatLog( $winnerSide, "wins" );
            $this->update_win_chacheCombatLog( $losingSide, "losses" );

            // Call the update_continue function and set the battle stage to 3 (meaning it's over)
            $this->update_continue(3);
        }

        // Update the combatLog for tasks etc
        private function update_win_chacheCombatLog( $side, $status, $sideUID = false, $otherSideUID = false ){

            // Get the other side
            $otherSide = $this->get_other_side($side);

            // If user ID is specified, only treat that single user
            $sideUids = ( $sideUID == false ) ? $this->{$side}['ids'] : array($sideUID);
            $otherSideUids = ( $otherSideUID == false ) ? $this->{$otherSide}['ids'] : array($otherSideUID);

            foreach($sideUids as $key => $value)
                if((isset($this->{$side}['data'][ $value ]['summontype']) && $this->{$side}['data'][ $value ]['summontype'] == 'user'))
                    unset($sideUids[$key]);

            foreach($otherSideUids as $key => $value)
                if((isset($this->{$otherSide}['data'][ $value ]['summontype']) && $this->{$otherSide}['data'][ $value ]['summontype'] == 'user'))
                    unset($otherSideUids[$key]);

            // Go through the specified side's users
            foreach( $sideUids as $side_id ){

                // Go through opponents
                foreach( $otherSideUids as $other_id ){

                    // Status to be set for this oppoenent
                    $setStatus = $status;

                    // If user is fleeing, overwrite
                    if( $this->is_user_fleeing($side, $side_id) ){
                        $setStatus = "fled";
                    }

                    // If losing, and opponent also lost, then give draw
                    if( $status == "losses" && $this->{$otherSide}['data'][ $other_id ]['cur_health'] <= 0){
                        $setStatus = "draws";
                    }

                    // Get other name
                    $otherName = $this->{$otherSide}['data'][ $other_id ]['username'];

                    // Switch for allowing multiple logging of this kill
                    $multipleKills = false;

                    // Get other ID
                    if( $this->is_user_ai($otherSide, $other_id) ){
                        if( isset($this->{$otherSide}['data'][ $other_id ]['original_id']) ){
                            $multipleKills = true;
                            $other_id = $this->{$otherSide}['data'][ $other_id ]['original_id'];
                        }
                    }

                    // Update cache
                    $this->updateCombatCacheLog( $side, $side_id , $this->battle[0]['battle_type'], $other_id, $setStatus, $otherName, $multipleKills );
                }
            }
        }

        // Update the user case log, but only once per user
        private function updateCombatCacheLog( $side, $sideID, $battleType, $otherSideID, $status, $otherName, $multipleKills ){

            // Array for logging awards
            if( !isset( $this->{$side}['data'][ $sideID ]['combatLog'] ) ){
                $this->{$side}['data'][ $sideID ]['combatLog'] = array();
            }

            // Check if this one is added, if not, do
            if( !in_array($otherSideID, $this->{$side}['data'][ $sideID ]['combatLog']) || $multipleKills == true ){
                cachefunctions::updateCombatLog( $sideID , $battleType, $otherSideID, $status, $otherName);
                $this->{$side}['data'][ $sideID ]['combatLog'][] = $otherSideID;
            }
        }

        // Update anbu information
        private function update_win_anbu($winnerSide, $loserSide) {

            // Current location
            $curLong = $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['longitude'];
            $curLat = $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['latitude'];

            // Only award ANBU points inside the map
            if ($this->inMap( $curLong, $curLat) ) {

                // Array for tracking which anbus have been awarded
                $awardArray = array();

                // Go through the anbu points for each winner
                foreach ($this->{$winnerSide}['ids'] as $wid) {

                    // Add to award array
                    $awardArray[$wid] = array();

                    // Check if the user is anbu
                    if ($this->is_user_anbu($winnerSide, $wid)) {

                        // Check each of the losers, and add points to the winners
                        foreach ($this->{$loserSide}['ids'] as $lid) {

                            // Only do something if this loser actually died
                            if( $this->is_marked_for_removal($loserSide, $lid) ){

                                // Only if not ai, not fleeing, and not same village, and don't award the same anbu twice for one loser kill
                                if(
                                        !$this->is_user_ai($loserSide, $lid) &&
                                        !$this->is_user_fleeing($loserSide, $lid) &&
                                        $this->{$winnerSide}['data'][ $wid ]['village'] !== $this->{$loserSide}['data'][ $lid ]['village'] &&
                                        !isset($awardArray[ $wid ][ $lid ])
                                ){
                                    // Add to award array
                                    $awardArray[$wid][$lid] = 1;

                                    // Check if we're in the village of the winner or not
                                    $inVillage = ( stristr($this->{$loserSide}['data'][ $lid ]['location'],$this->{$winnerSide}['data'][ $wid ]['village']) ) ? true : false;

                                    // Award rage or defence points
                                    if( $inVillage ){
                                        $query = "UPDATE `squads` SET `pt_def` = `pt_def` + 1 WHERE `id` = '" . $this->{$winnerSide}['data'][ $wid ]['anbu'] . "' LIMIT 1";
                                        $GLOBALS['database']->execute_query($query);
                                        $this->{$winnerSide}['summary'][ $wid ]['squadD'] += 1;
                                    }
                                    else{
                                        $query = "UPDATE `squads` SET `pt_rage` = `pt_rage` + 1 WHERE `id` = '" . $this->{$winnerSide}['data'][ $wid ]['anbu'] . "' LIMIT 1";
                                        $GLOBALS['database']->execute_query($query);
                                        $this->{$winnerSide}['summary'][ $wid ]['squadA'] += 1;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Update user bounty information
        private function update_win_bounty($winnerSide, $loserSide) {

            // Array to store the hunters in
            $hunterIds = $hunterVillages = array();

            // Check for all winning hunters
            foreach ($this->{$winnerSide}['ids'] as $id) {

                // Normal bounty hunters
                if ( $this->is_user_bountyHunter($winnerSide, $id) ) {

                    // Add this user as a hunter
                    $hunterIds[] = $id;

                    // Outlaws hunting special bounty
                    if( $this->is_user_outlaw($winnerSide, $id) ){
                        $hunterVillages[] = "SpecialBounty";
                    }
                    else{
                        $hunterVillages[] = $this->{$winnerSide}['data'][ $id ]['village'];
                    }
                }
            }

            // If there were any bounty hunters
            if ( count($hunterIds) > 0 && !empty($this->{$loserSide . '_killedplayers'}) ) {

                // Go through all winners & losers, see if the bounty-hunter targets of anyone should be reset
                foreach ($this->{$winnerSide}['ids'] as $id) {
                    if ( $this->is_user_bountyHunter($winnerSide, $id) ) {
                        foreach ($this->{$loserSide . '_killedplayers'} as $lid) {
                            if( $this->{$winnerSide}['data'][ $id ]['feature'] == $this->{$loserSide}['data'][ $lid ]['username'] ){
                                $this->{$winnerSide}['summary'][ $id ][ "feature" ] = "Reset";
                            }
                        }
                    }
                }

                // Select the total boundary for all the hunter villages, from all the dead opponents
                $query = "SELECT SUM(" . $this->listQueryString($hunterVillages, "+") . ") as `total_bounty`
                          FROM `bingo_book`
                          WHERE
                            (" . $this->idQueryString( array_fill(0, count($hunterVillages), 0) , $hunterVillages, " OR ", "<").") AND
                            (" . $this->idQueryString( $this->{$loserSide . '_killedplayers'} , "userID", " OR ") . ")";
                $bounty = $GLOBALS['database']->fetch_data($query);

                // Check if bounty was found
                if( $bounty !== "0 rows"){

                    // Give the people their bounty if it exists
                    if( $bounty[0]['total_bounty'] < 0 ){

                         // If bounty was found, remove from the criminals
                        $query = "UPDATE `bingo_book`
                                  SET ".$this->idQueryString( array_fill(0, count($hunterVillages), 0) , $hunterVillages, ",")."
                                  WHERE
                                    (" . $this->idQueryString( array_fill(0, count($hunterVillages), 0) , $hunterVillages, " AND ", "<").") AND
                                    (" . $this->idQueryString( $this->{$loserSide . '_killedplayers'} , "userID", " AND ") . ") LIMIT 10";
                        $GLOBALS['database']->execute_query($query);

                        // Make the bounty a positive number and split between hunters
                        $bounty[0]['total_bounty'] = -1 * $bounty[0]['total_bounty'] / count($hunterIds);

                        // Loop through all the hunters
                        foreach ($hunterIds as $id) {

                            // Give bounty to all hunters
                            $this->{$winnerSide}['summary'][ $id ][ "bounty" ] += $bounty[0]['total_bounty'];

                            // Figure out experience gain
                            $experience = 10;
                            foreach( $this->{$loserSide . '_killedplayers'} as $lid ){
                                $experience += $this->getBountyExperience($winnerSide, $id, $loserSide, $lid);
                            }

                            // Send this new experience to the user
                            $this->{$winnerSide}['summary'][ $id ][ "bounty_experience" ] += $experience;
                        }
                    }
                }
            }
        }

        // Function that calculates the amount of bounty hunter experience to award winner based on user
        private function getBountyExperience( $winnerSide, $id, $loserSide, $lid ){

            // The experience in this case
            $experience = 0;

            // Based on rank
            if( $this->{$loserSide}['data'][ $lid ]['rank_id'] < $this->{$winnerSide}['data'][ $id ]['rank_id'] ){
                $experience += 2;
            }
            elseif( $this->{$loserSide}['data'][ $lid ]['rank_id'] == $this->{$winnerSide}['data'][ $id ]['rank_id'] ){
                $experience += 5;
            }
            elseif( $this->{$loserSide}['data'][ $lid ]['rank_id'] > $this->{$winnerSide}['data'][ $id ]['rank_id'] ){
                $experience += 10;
            }

            // If enemy was kage
            if( $this->is_user_kage($loserSide, $lid) ){
                $experience += 50;
            }

            // Anbu stuff
            if( $this->is_user_anbu($loserSide, $lid) ){
                if( $this->is_user_anbuLeader($loserSide, $lid) ){
                    $experience += 15;
                }
                else{
                    $experience += 5;
                }
            }

            // Clan stuff
            if( $this->is_user_clan($loserSide, $lid) ){
                if( $this->is_user_clanLeader($loserSide, $lid) ){
                    $experience += 15;
                }
                else{
                    $experience += 5;
                }
            }

            // Outlaw or villager
            if( $this->is_user_outlaw($loserSide, $lid) ){
                $experience += 5;
            }
            else{

                // Check alliance status
                if( $this->is_user_allianceStatus($loserSide, $lid, $winnerSide, $id, 2) ){

                    // Enemy
                    $experience += 3;

                }
                elseif( $this->is_user_allianceStatus($loserSide, $lid, $winnerSide, $id, 0) ){

                    // Neutral
                    $experience += 3;

                }
                else{

                    // Neutral
                    $experience *= 0.75;

                }

            }

            // Return
            return $experience;
        }

        // Update user clan information
        private function update_win_clan($winnerSide, $loserSide) {

            // Current location
            $curLong = $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['longitude'];
            $curLat = $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['latitude'];

            // Only award points inside the map
            if ($this->inMap( $curLong, $curLat) ) {

                // Array for tracking which clans have been awarded
                $awardArray = array();

                // Go through all the winenrs
                foreach ($this->{$winnerSide}['ids'] as $wid) {

                    // Add to award array
                    $awardArray[$wid] = array();

                    // Check if in clan
                    if ( $this->is_user_clan($winnerSide, $wid) ) {

                        // Get the winner alliance
                        $alliances = $this->{$winnerSide}['alliances'][ $wid ];

                        // Go through all the users
                        foreach ($this->{$loserSide}['ids'] as $lid) {

                            // Only do something if this loser actually died
                            if( $this->is_marked_for_removal($loserSide, $lid) ){

                                // Only if not ai, not fleeing, and not same village, and don't award the same anbu twice for one loser kill
                                if(
                                        !$this->is_user_ai($loserSide, $lid) &&
                                        !$this->is_user_fleeing($loserSide, $lid) &&
                                        $this->{$winnerSide}['data'][ $wid ]['village'] !== $this->{$loserSide}['data'][ $lid ]['village'] &&
                                        !isset($awardArray[ $wid ][ $lid ])
                                ){

                                    // Add to award array
                                    $awardArray[$wid][$lid] = 1;

                                    // Store user village locally
                                    $loservil = $this->{$loserSide}['data'][ $lid ]['village'];

                                    // Award points to village in war or neutral
                                    if (
                                            $alliances[ $loservil ] == 0 ||
                                            $alliances[ $loservil ] == 2
                                    ) {
                                        // Update the points and activity
                                        $query = "UPDATE `clans` SET `points` = `points` + 1, `activity` = `activity` + 1 WHERE `id` = '" . $this->{$winnerSide}['data'][ $wid ]['clan'] . "' LIMIT 1";
                                        $GLOBALS['database']->execute_query($query);

                                        // Update the clan points for the summary page
                                        $this->{$winnerSide}['summary'][ $wid ]['clanpoints'] += 1;

                                        // User gets personal clan activity points as well
                                        $this->{$winnerSide}['summary'][ $wid ]['clanActivity'] += 1;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Update the village funds stuff & pvp experience
        private function update_win_villagePvp($winnerSide, $loserSide) {

            // Current location
            $curLong = $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['longitude'];
            $curLat = $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['latitude'];

            // Only award points inside the map
            if ( $this->inMap( $curLong, $curLat) || $GLOBALS['page']->inOutlawBase ) {

                // Factors used in calculation of PVP experience and village structure point (SP) destruction/healing during war
                $winnerMaxHP = array_fill_keys($this->{$winnerSide}['ids'], 0);
                $loserMaxHP = array_fill_keys($this->{$loserSide}['ids'], 0);
                $winnerTotalStats = array_fill_keys($this->{$winnerSide}['ids'], 0);
                $loserTotalStats = array_fill_keys($this->{$loserSide}['ids'], 0);

                // Winner
                foreach( $this->{$winnerSide}['ids'] as $id ){
                    $winnerMaxHP[$id] += $this->{$winnerSide}['data'][ $id ]['max_health'];
                    $winnerTotalStats[$id] += $this->{$winnerSide}['data'][ $id ]['tai_off'] + $this->{$winnerSide}['data'][ $id ]['nin_off'] +
                                         $this->{$winnerSide}['data'][ $id ]['gen_off'] + $this->{$winnerSide}['data'][ $id ]['weap_off'] +
                                         $this->{$winnerSide}['data'][ $id ]['tai_def'] + $this->{$winnerSide}['data'][ $id ]['nin_def'] +
                                         $this->{$winnerSide}['data'][ $id ]['gen_def'] + $this->{$winnerSide}['data'][ $id ]['weap_def'] +
                                         $this->{$winnerSide}['data'][ $id ]['strength'] + $this->{$winnerSide}['data'][ $id ]['intelligence'] +
                                         $this->{$winnerSide}['data'][ $id ]['speed'] + $this->{$winnerSide}['data'][ $id ]['willpower'];
                }

                // Loser
                foreach( $this->{$loserSide}['ids'] as $id ){
                    $loserMaxHP[$id] += $this->{$loserSide}['data'][ $id ]['max_health'];
                    $loserTotalStats[$id] += $this->{$loserSide}['data'][ $id ]['tai_off'] + $this->{$loserSide}['data'][ $id ]['nin_off'] +
                                         $this->{$loserSide}['data'][ $id ]['gen_off'] + $this->{$loserSide}['data'][ $id ]['weap_off'] +
                                         $this->{$loserSide}['data'][ $id ]['tai_def'] + $this->{$loserSide}['data'][ $id ]['nin_def'] +
                                         $this->{$loserSide}['data'][ $id ]['gen_def'] + $this->{$loserSide}['data'][ $id ]['weap_def'] +
                                         $this->{$loserSide}['data'][ $id ]['strength'] + $this->{$loserSide}['data'][ $id ]['intelligence'] +
                                         $this->{$loserSide}['data'][ $id ]['speed'] + $this->{$loserSide}['data'][ $id ]['willpower'];
                }

                /*
                echo"<pre />";
                echo "Winner Max HP & Total Stats";
                print_r($winnerMaxHP);
                print_r($winnerTotalStats);
                echo "Loser Max HP & Total Stats";
                print_r($loserMaxHP);
                print_r($loserTotalStats);
                echo"=============<br>";
                echo $winnerSide . " - vs. - ".$loserSide."<br>";
                echo $this->{$winnerSide}['data'][ $this->{$winnerSide}['ids'][0] ]['username']. " vs. " . $this->{$loserSide}['data'][ $this->{$loserSide}['ids'][0] ]['username']."<br>";
                */

                // Get number of active winners
                $activeWinners = 0;
                foreach( $this->{$winnerSide}['ids'] as $wid ){
                    if( !$this->is_marked_for_removal($winnerSide, $wid) &&
                        !$this->is_user_ai($winnerSide, $wid) &&
                        !$this->is_user_fleeing($winnerSide, $wid) &&
                        $this->{$winnerSide}['data'][ $wid ]['cur_health'] >= 0
                    ){
                        $activeWinners += 1;
                    }
                }
                if( $activeWinners < 1 ){ $activeWinners = 1; }

                /* Calculate the PVP experience to be gained from these losers. */

                // Only rewarded inside villages
                if( $this->inVillage() ){

                    // Go through the losers
                    /* Calculate the village funds */
                    foreach( $this->{$loserSide}['ids'] as $lid ){

                        // Only look at people who've been removed
                        if( $this->is_marked_for_removal($loserSide, $lid) &&
                            (!$this->is_user_ai($loserSide, $lid) || $this->is_user_summon($loserSide, $lid) ) &&
                            !$this->is_user_fleeing($loserSide, $lid)
                        ){
                            // Award each person on the winning side
                            foreach( $this->{$winnerSide}['ids'] as $wid ){

                                // Get winner alliance
                                $wAlliances = $this->{$winnerSide}['alliances'][ $wid ];

                                // Only look at people who're not being removed
                                if( !$this->is_marked_for_removal($winnerSide, $wid) &&
                                    !$this->is_user_ai($winnerSide, $wid) &&
                                    !$this->is_user_fleeing($winnerSide, $wid) &&
                                    $wAlliances[ $this->{$loserSide}['data'][ $lid ]['village'] ] !== "1" &&
                                    $this->{$winnerSide}['data'][ $wid ]['cur_health'] >= 0
                                ){
                                    // PVP Calculation
                                    $pvp_exp = ( $loserMaxHP[$lid] / $winnerMaxHP[$wid] ) * ( $loserTotalStats[$lid] /$winnerTotalStats[$wid] ) * 10;

                                    // Account for several winners
                                    $pvp_exp = $pvp_exp * ( 1 / $activeWinners );

                                    // Limits
                                    if( $pvp_exp < 1 ){ $pvp_exp = 1; }
                                    if( $pvp_exp > 20 ){ $pvp_exp = 20; }

                                    // Check for global event modifications
                                    if( $event = functions::getGlobalEvent("IncreasedPVP") ){
                                        if( isset( $event['data']) && is_numeric( $event['data']) ){
                                            // echo "Activating event PVP multiplier: ".($event['data'] / 100)."<br>";
                                            $pvp_exp *= $event['data'] / 100;
                                        }
                                    }

                                    // echo "Active Winners: ".$activeWinners.". Now awarding ".$pvp_exp." PVP exp to user: ".$this->{$winnerSide}['data'][ $wid ]['username']."<br>";
                                    $this->{$winnerSide}['summary'][ $wid ]['pvp'] += round( $pvp_exp , 2 );

                                    // Add to the streak
                                    $this->{$winnerSide}['summary'][ $wid ]['pvp_streak'] += 1;

                                    // Check for extra PVP conditions
                                    $newPvpStreak = $this->{$winnerSide}['data'][ $wid ]['pvp_streak'] + $this->{$winnerSide}['summary'][ $wid ]['pvp_streak'];

                                    // Add PVP if more than 1 streak
                                    if( $newPvpStreak > 0 ){

                                        // Calculate gain. Each streak is 10 long.
                                        $consecutiveStreaks = floor($newPvpStreak / 10);
                                        $killsInCurrentStreak = $newPvpStreak % 10;

                                        // Extra PVP during streak (10 kills)
                                        switch( $killsInCurrentStreak ){
                                            case 3: $this->{$winnerSide}['summary'][ $wid ]['pvp'] += 2; break;
                                            case 6: $this->{$winnerSide}['summary'][ $wid ]['pvp'] += 5; break;
                                            case 9: $this->{$winnerSide}['summary'][ $wid ]['pvp'] += 9; break;
                                            case 0: $this->{$winnerSide}['summary'][ $wid ]['pvp'] += 4; break;
                                        }

                                        // Extra pvp for finished streaks  after the first one (10 kills)
                                        if( $killsInCurrentStreak == 0 && $consecutiveStreaks > 1){
                                            $this->{$winnerSide}['summary'][ $wid ]['pvp'] += 10;
                                        }

                                        // If new PVP streak is above 50, reset everything
                                        if( $newPvpStreak >= 50 ){
                                            $this->{$winnerSide}['summary'][ $wid ]['pvp_streak'] = -$this->{$winnerSide}['data'][ $wid ]['pvp_streak'];
                                        }
                                    }
                                }
                            }

                        }
                    }
                }


                // Award Village Points array
                $villagePoints = array(
                    "Konoki" => 0,
                    "Silence" => 0,
                    "Samui" => 0,
                    "Shroud" => 0,
                    "Shine" => 0,
                    "Syndicate" => 0
                );

                // Ensure points are only given for each loser once. Contains an array of
                // losers for which points are already awarded, for each village
                $processedLosers = array(
                    "Konoki" => array(),
                    "Silence" => array(),
                    "Samui" => array(),
                    "Shroud" => array(),
                    "Shine" => array(),
                    "Syndicate" => array()
                );

                /* Calculate the village funds */
                foreach( $this->{$loserSide}['ids'] as $lid ){

                    // Only look at people who've been removed
                    if( $this->is_marked_for_removal($loserSide, $lid) &&
                        !$this->is_user_ai($loserSide, $lid) &&
                        !$this->is_user_fleeing($loserSide, $lid) &&
                        $this->{$loserSide}['data'][ $lid ]['structurePointsActivity'] > -75
                    ){
                        // Local capitalized copy of loser village
                        $loseVillage = ucfirst($this->{$loserSide}['data'][ $lid ]['village']);

                        // Get the loser alliance
                        $lAlliances = $this->{$loserSide}['alliances'][ $lid ];

                        // Go through each winner
                        foreach( $this->{$winnerSide}['ids'] as $wid ){

                            // Don't count in the case of a summon
                            if( !$this->is_user_summon($winnerSide, $wid) ){

                                // Get the winner alliance
                                $wAlliances = $this->{$winnerSide}['alliances'][ $wid ];

                                // Set the village funds gain for this user
                                $funds_gain = 1;
                                if( isset($this->{$winnerSide}['data'][ $wid ]['vil_loyal_pts']) ){
                                    if (
                                        $this->{$winnerSide}['data'][ $wid ]['vil_loyal_pts'] >= 365 ||
                                        $this->{$winnerSide}['data'][ $wid ]['vil_loyal_pts'] <= -365
                                    ) {
                                        if( $this->{$winnerSide}['data'][ $wid ]['activateBonuses'] == "yes" ){
                                            $funds_gain = 3;
                                        }
                                    }
                                }

                                // Increased funds from global event
                                if( $event = functions::getGlobalEvent("VFgain") ){
                                    if( isset( $event['data']) && is_numeric( $event['data']) ){
                                        $funds_gain += $event['data'];
                                    }
                                }

                                // Check if we are in city of Mei, if so, adjust funds gain
                                if( $GLOBALS['page']->inOutlawBase ){
                                    if( $this->is_user_outlaw($loserSide, $lid) ){
                                        $funds_gain = 2;
                                    }
                                    else{
                                        $funds_gain = 3;
                                    }
                                }

                                // Only do something if we have an alliance to check up
                                if ($lAlliances != '0 rows') {

                                    // Local capitalized copy of winner village
                                    $winVillage = ucfirst($this->{$winnerSide}['data'][ $wid ]['village']);

                                    // Check the different alliance statuses
                                    switch( $lAlliances[ $this->{$winnerSide}['data'][ $wid ]['village'] ] ){

                                        // Neutral
                                        case 0:

                                            // Make sure it exists
                                            if( !isset($this->{$winnerSide}['summary'][ $wid ]['opposing']) ){
                                                $this->{$winnerSide}['summary'][ $wid ]['opposing'] = 0;
                                            }

                                            // Update reputation loss (bingo book)
                                            $this->{$winnerSide}['data'][ $wid ]['repLoss'][ $loseVillage ] += random_int(5,100);

                                            // Summary Page
                                            $this->{$winnerSide}['summary'][ $wid ]['opposing'] += 1;

                                            // Add to village points array,
                                            // only if this loser hasn't already been added for this village
                                            if(!in_array($lid, $processedLosers[ $winVillage ]) ){

                                                // Figure out which village to award
                                                $pointVillage = $winVillage;
                                                if( isset($this->{$winnerSide}['data'][ $wid ]['vassal_owner']) &&
                                                    !empty($this->{$winnerSide}['data'][ $wid ]['vassal_owner']) &&
                                                    random_int(1,3)==3
                                                ){
                                                    $pointVillage = $this->{$winnerSide}['data'][ $wid ]['vassal_owner'];
                                                    $this->{$winnerSide}['summary'][ $wid ]['opposing'] *= -1;
                                                }
                                                $villagePoints[ $pointVillage ] += $funds_gain;

                                                // Remember
                                                $processedLosers[ $winVillage ][] = $lid;
                                            }
                                        break;

                                        // Ally
                                        case 1:

                                            // This whole thing only applies if it's the user side who's the winner
                                            if( $winnerSide == "user" || $winnerSide == $loserSide ){

                                                // If outlaw and kill outlaw, don't count either
                                                if( !($winVillage == "Syndicate" && $loseVillage == $winVillage) ){

                                                    // New respect points (bingo book)
                                                    $this->{$winnerSide}['data'][ $wid ]['repLoss'][ $loseVillage ] += $this->{$winnerSide}['data'][ $wid ][ $winVillage ] / 2 + 2500;

                                                    // Reputation loss and summary message depends on whether it's the same village or a different one
                                                    if( $winVillage == $loseVillage ){
                                                        $this->{$winnerSide}['summary'][ $wid ]['ownFaction'] += 1;
                                                    }
                                                    else{
                                                        $this->{$winnerSide}['summary'][ $wid ]['allied'] += 1;
                                                    }

                                                    // Remove points from village points array,
                                                    // only if this loser hasn't already been added for this village
                                                    if(!in_array($lid, $processedLosers[ $winVillage ]) ){
                                                        $villagePoints[ $winVillage ] -= 1;
                                                        $processedLosers[ $winVillage ][] = $lid;
                                                    }
                                                }
                                            }

                                        break;

                                        // Enemy
                                        case 2:

                                            // For wars, check if the winners target IDs include the loser ID
                                            /*if(
                                                    isset($this->{$winnerSide}['actionInfo'][ $wid ]['targetIDs']) &&
                                                    in_array( $lid, $this->{$winnerSide}['actionInfo'][ $wid ]['targetIDs'] )
                                            ){*/

                                                // Update reputation loss (bingo book)
                                                $this->{$winnerSide}['data'][ $wid ]['repLoss'][ $loseVillage ] += random_int(5,50);

                                                // Make sure it exists
                                                if( !isset($this->{$winnerSide}['summary'][ $wid ]['opposing']) ){
                                                    $this->{$winnerSide}['summary'][ $wid ]['opposing'] = 0;
                                                }

                                                // Summary Page
                                                $this->{$winnerSide}['summary'][ $wid ]['opposing'] += 1;

                                                // Add points to village points array,
                                                // only if this loser hasn't already been added for this village
                                                if( !in_array($lid, $processedLosers[ $winVillage ]) ){

                                                    // Figure out which village to award
                                                    $pointVillage = $winVillage;
                                                    if( isset($this->{$winnerSide}['data'][ $wid ]['vassal_owner']) &&
                                                        !empty($this->{$winnerSide}['data'][ $wid ]['vassal_owner']) &&
                                                        random_int(1,3)==3
                                                    ){
                                                        $pointVillage = $this->{$winnerSide}['data'][ $wid ]['vassal_owner'];
                                                        $this->{$winnerSide}['summary'][ $wid ]['opposing'] *= -1;
                                                    }

                                                    // Set village funds to be awarded & remember the user who awarded the points
                                                    $villagePoints[ $pointVillage ] += $funds_gain;
                                                    $processedLosers[ $winVillage ][] = $lid;

                                                    // For structure points (the rest of this case in the switch),
                                                    // the win/losing village is changed based on the user location and alliance,
                                                    // such that if the user is in an ally village, he destroys/heals that village
                                                    // and not his own

                                                    // Check if defensive/offensive/neutral
                                                    $isOffence = stristr($this->{$winnerSide}['data'][ $wid ]['location'], $loseVillage );
                                                    $isDefence = stristr($this->{$winnerSide}['data'][ $wid ]['location'], $winVillage );

                                                    // This may be overwritten if we're in a different village. Therefore store it in $originalLoserVillage
                                                    $originalLoserVillage = $loseVillage;

                                                    // If we are not in the winners or losers village, then we're in another village
                                                    if( empty($isOffence) && empty($isDefence) ){

                                                        // Get the location village
                                                        $locationVillage = explode(" ", $this->{$winnerSide}['data'][ $wid ]['location']);

                                                        // Validate that it's a village string (on the format "Konokti village")
                                                        if( isset($locationVillage[0]) && isset($locationVillage[1]) && $locationVillage[1] == "village" ){

                                                            // Set location village
                                                            $locationVillage = $locationVillage[0];

                                                            // Check the winner alliance to this village -
                                                            // if allied, then it's a defensive move - winVillage should be changed to location
                                                            // if enemy, then it's an offensive move - loseVillage should be changed to location
                                                            if( isset($wAlliances[ $locationVillage ]) ){

                                                                // Handle different alliance statuses
                                                                switch( $wAlliances[ $locationVillage ] ){

                                                                    // Allied case
                                                                    case 1:
                                                                        $isDefence = true;
                                                                        $winVillage = $locationVillage;
                                                                        break;

                                                                    // Enemy case
                                                                    case 2:
                                                                        $isOffence = true;
                                                                        $loseVillage = $locationVillage;
                                                                        break;
                                                                }
                                                            }
                                                        }
                                                    }


                                                    // Figure out how much SP is destroyed / healed depending on if it's offence / defence
                                                    $structurePointsChange = 0;
                                                    if( $isOffence ){

                                                        // Calculation with base 15, minimum of 5, and max of 25
                                                        $structurePointsChange = ( $loserMaxHP[$lid] / $winnerMaxHP[$wid] ) * ( $loserTotalStats[$lid] /$winnerTotalStats[$wid] ) * 15;
                                                        $structurePointsChange = $structurePointsChange * ( 1 / $activeWinners );
                                                        if( $structurePointsChange < 5 ){ $structurePointsChange = 5; }
                                                        if( $structurePointsChange > 25 ){ $structurePointsChange = 25; }
                                                    }
                                                    elseif( $isDefence ){

                                                        // Calculation with base 15, minimum of 5, and max of 25
                                                        $structurePointsChange = ( $loserMaxHP[$lid] / $winnerMaxHP[$wid] ) * ( $loserTotalStats[$lid] /$winnerTotalStats[$wid] ) * 5;
                                                        $structurePointsChange = $structurePointsChange * ( 1 / $activeWinners );
                                                        if( $structurePointsChange < 1 ){ $structurePointsChange = 1; }
                                                        if( $structurePointsChange > 10 ){ $structurePointsChange = 10; }
                                                    }

                                                    // Reduce the amount of SP destroyed/healed based on losers structurePointActivity
                                                    // i.e. a user who loses all the time will not award as many points as one who does not
                                                    if( $this->{$loserSide}['data'][ $lid ]['structurePointsActivity'] < 0 ){
                                                        $reductionPerc = ($this->{$loserSide}['data'][ $lid ]['structurePointsActivity']+100)/100;
                                                        if( $reductionPerc > 0 ){
                                                            $structurePointsChange *= $reductionPerc;
                                                        }
                                                        else{
                                                            $structurePointsChange = 0;
                                                        }
                                                    }

                                                    // Ceil the value
                                                    $structurePointsChange = ceil($structurePointsChange);

                                                    // Do structure damages & heals depending on situation
                                                    if( $originalLoserVillage !== "Syndicate" &&    // Not for syndicate
                                                        $winVillage !== "Syndicate" &&              // Not for syndicate
                                                        $this->{$winnerSide."continue"} == true     // Only upload for winner
                                                    ){

                                                        if ( $isOffence ) {

                                                            // Get the cost & potential heal. Force $structurePointsChange in cost
                                                            list($cost, $heal) = $this->warLib->structures_getHealCost( "offensive", $structurePointsChange, null );

                                                            // Update structure points
                                                            $this->warLib->structures_reducePoints( $loseVillage, $lAlliances, $winVillage, "offensive", $cost, $heal );

                                                            // save for user
                                                            $this->{$winnerSide}['summary'][ $wid ]['structure'] += $cost;
                                                            $this->{$winnerSide}['summary'][ $wid ]['warActivity'] += $cost;
                                                            $this->{$loserSide}['summary'][ $lid ]['warActivity'] -= $cost;
                                                        }
                                                        elseif ($isDefence) {

                                                            // Get the cost & potential heal. Force $structurePointsChange in heal
                                                            list($cost, $heal) = $this->warLib->structures_getHealCost( "defensive", null, $structurePointsChange );

                                                            // Update structure points
                                                            $this->warLib->structures_reducePoints( $loseVillage, $lAlliances, $winVillage, "defensive", $cost, $heal );

                                                            // save for user
                                                            $this->{$winnerSide}['summary'][ $wid ]['warActivity'] += $heal;
                                                            $this->{$loserSide}['summary'][ $lid ]['warActivity'] -= $cost;
                                                            $this->{$winnerSide}['summary'][ $wid ]['hstructure'] += $heal;
                                                        }
                                                        else {


                                                            // Get the cost & potential heal, and pass to final summary
                                                            list($cost, $heal) = $this->warLib->structures_getHealCost( "neutral");

                                                            // Update structure points
                                                            $this->warLib->structures_reducePoints( $loseVillage, $lAlliances, $winVillage, "neutral");

                                                            // Reduce for user
                                                            if( $cost > 0 ){
                                                                $this->{$winnerSide}['summary'][ $wid ]['structure'] += $cost;
                                                                $this->{$winnerSide}['summary'][ $wid ]['warActivity'] += $cost;
                                                                $this->{$loserSide}['summary'][ $lid ]['warActivity'] -= $cost;
                                                            }

                                                            // Heal own
                                                            if( $heal > 0 ){
                                                                $this->{$winnerSide}['summary'][ $wid ]['hstructure'] += $heal;
                                                            }
                                                        }
                                                    }
                                                }
                                            //}

                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                // Update the villages
                foreach( $villagePoints as $village => $points ){
                    if( $points !== 0 ){
                        $GLOBALS['database']->execute_query("UPDATE `villages` SET `points` = `points` + '".$points."' WHERE `name` = '" . $village . "' LIMIT 1");
                    }
                }
            }
        }

        // They killed each other
        private function update_doubleKO() {

            // Handle different battle types
            $userStatus = $oppStatus = 'hospitalized';
            switch( $this->battle[0]['battle_type'] ){
                case "spar":
                case "torn_battle":
                    $userStatus = $oppStatus = 'awake';
                    foreach( array("user","opponent") as $side ){
                        foreach ($this->{$side}['ids'] as $id) {
                            if ( !$this->is_user_fleeing($side, $id) ) {
                                $this->{$side}['data'][ $id ]['cur_health'] = 1;
                            }
                        }
                    }
                break;
                case "kage":
                    $oppStatus = 'jailed';
                    foreach ($this->opponent['ids'] as $id) {
                        if ( !$this->is_user_fleeing("opponent", $id) ) {
                            $this->opponent['data'][ $id ]['cur_health'] = 1;
                        }
                    }
                break;
            }

            // Set all as losers
            foreach( array("user","opponent") as $side ){
                foreach ($this->{$side}['ids'] as $id) {

                    // Set as loser
                    switch( $side ){
                        case "user": $this->set_user_as_loser($side, $id, $userStatus); break;
                        case "opponent": $this->set_user_as_loser($side, $id, $oppStatus); break;
                    }

                    // Set to count draws instead of default
                    if( preg_match("/(kage|clan|combat|territory)/", $this->battle[0]['battle_type']) ){
                        $this->{$side}['summary'][ $id ]['PVP_draw'] = 1;
                    }
                    elseif( $this->battle[0]['battle_type'] == "spar" ){
                        // No counting
                    }
                    else{
                        $this->{$side}['summary'][ $id ]['AI_draw'] = 1;
                    }
                }

                // Update the combat cache log
                $this->update_win_chacheCombatLog( $side, "draws" );
            }

            // Call the update_continue function and set the battle stage to 3 (meaning it's over)
            $this->update_continue(3);
        }


        // BELOW ARE LAST FEW FUNCTIONS. To be sorted later

        // Summary Screen (This should be on the bottom of the page in the end)
        public function summary_screen() {

            // Set the user ID to the session ID
            $uid = $_SESSION['uid'];

            // User update query
            $userQuery = "`reinforcements` = 0, `battle_id` = 0";

            // Decide whether to record last battle from this battle
            /* switch($this->battle[0]['battle_type']){
                case "mirror_battle":
                case "torn_battle":
                case "arena":
                    $userQuery .= ", `last_battle` = '".$this->timeStamp."'";
                break;
            } */
            $userQuery .= ", `last_battle` = '".$this->timeStamp."'";

            // Update arena
            if( in_array($this->battle[0]['battle_type'], array("mirror_battle","torn_battle","arena") ) ){
                $userQuery .= ", `arena_cooldown` = '".$this->timeStamp."'";
            }

            // Update user respect points (village loyalty points)
            if( $this->{$this->sessionside}['summary'][ $uid ]['allied'] > 0 ||
                $this->{$this->sessionside}['summary'][ $uid ]['ownFaction'] > 0)
            {
                if( $this->{$this->sessionside}['data'][ $uid ]['vil_loyal_pts'] > 0 ){

                    // Calculate loss as 50% + 25 points, but not below 0
                    $respectLoss = $this->{$this->sessionside}['data'][ $uid ]['vil_loyal_pts'] * 0.5 + 25;
                    if( $respectLoss > $this->{$this->sessionside}['data'][ $uid ]['vil_loyal_pts']){
                        $respectLoss = $this->{$this->sessionside}['data'][ $uid ]['vil_loyal_pts'];
                    }
                    $this->{$this->sessionside}['summary'][ $uid ]['respectLoss'] += $respectLoss;
                }
            }

            // Loop through the summary array for this user and perform queries etc for different cases
            foreach( $this->{$this->sessionside}['summary'][ $uid ] as $key => $value ){

                // Check that the value is not empty. Supposedly checks for both array(), false, 0 etc.
                if( !empty($value) ){

                    switch($key){
                        case "kage":
                            // Do stuff
                        break;
                        case "clan":
                            // Do stuff
                        break;
                        case "battle_conclusion":
                            switch($value){
                                case "won":
                                    $userQuery .= ", `status` = 'awake'";
                                    $GLOBALS['userdata'][0]['status'] = 'awake';

                                    break;
                                case "fled":
                                    $userQuery .= ", `status` = 'awake'";
                                    $GLOBALS['userdata'][0]['status'] = 'awake';
                                    $this->{$this->sessionside}['summary'][ $uid ]['end_status'] = "awake";
                                    break;
                                case "lost":

                                    // Update Status
                                    $status = $this->{$this->sessionside}['summary'][ $uid ]['end_status'];
                                    $userQuery .= ", `status` = '".$status."'";
                                    $GLOBALS['userdata'][0]['status'] = $status;

                                    // Jail timer
                                    if( $status == "jailed" ){
                                        $userQuery .= ", `jail_timer` = '".($this->timeStamp+12*3600)."'";
                                    }

                                    // Update Location to village Hospital
                                    if( $status !== "awake" ){

                                        // Outlaws are moved randomly, otherwise to village
                                        if( $this->{$this->sessionside}['data'][ $uid ]['village'] == "Syndicate" ){

                                            // Only update location if in village currently
                                            if( $this->inVillage() ){
                                                $distances = array( " - 1 ", " + 1 " );
                                                $userQuery .= ", `latitude` = `latitude` ".$distances[random_int(0,1)];
                                                $userQuery .= ", `longitude` = `longitude` ".$distances[random_int(0,1)];
                                            }

                                            // Update location
                                            $userQuery .= ", `location` = 'Disoriented'";

                                        }
                                        else{
                                            $userQuery .= ", `latitude` = '".$this->{$this->sessionside}['data'][ $uid ]['village_lat']."'";
                                            $userQuery .= ", `longitude` = '".$this->{$this->sessionside}['data'][ $uid ]['village_long']."'";
                                            $userQuery .= ", `location` = '".$this->{$this->sessionside}['data'][ $uid ]['village_location']."'";
                                        }
                                    }

                                    break;
                            }
                            break;
                        case "ryo_gain":
                            $userQuery .= ", `money` = `money` + '".$value."'";
                            break;
                        case "respectLoss":
                            $userQuery .= ", `vil_loyal_pts` = `vil_loyal_pts` - '".$value."'";
                            break;
                        case "structure":
                            $userQuery .= ", `structureDestructionPoints` = `structureDestructionPoints` + '".$value."'";
                            break;
                        case "hstructure":
                            $userQuery .= ", `structureGatherPoints` = `structureGatherPoints` + '".$value."'";
                            break;
                        case "warActivity":
                            $userQuery .= ", `structurePointsActivity` = `structurePointsActivity` + '".$value."'";
                            break;
                        case "clanActivity":
                            $userQuery .= ", `clan_activity` = `clan_activity` + '".$value."'";
                            break;
                        case "health_gain":
                            $userQuery .= ", `max_health` = `max_health` + '".$value."'";
                            break;
                        case "chakra_gain":
                            $userQuery .= ", `max_cha` = `max_cha` + '".$value."'";
                            break;
                        case "stamina_gain":
                            $userQuery .= ", `max_sta` = `max_sta` + '".$value."'";
                            break;
                        case "strength_gain":
                            $userQuery .= ", `strength` = `strength` + '".$value."'";
                            break;
                        case "intelligence_gain":
                            $userQuery .= ", `intelligence` = `intelligence` + '".$value."'";
                            break;
                        case "willpower_gain":
                            $userQuery .= ", `willpower` = `willpower` + '".$value."'";
                            break;
                        case "speed_gain":
                            $userQuery .= ", `speed` = `speed` + '".$value."'";
                            break;
                        case "exp_gain":
                            $userQuery .= ", `experience` = `experience` + '".$value."'";
                            break;
                        case "pvp":
                            $userQuery .= ", `pvp_experience` = `pvp_experience` + '".$value."'";
                            break;
                        case "pvp_streak":
                            $userQuery .= ", `pvp_streak` = `pvp_streak` + '".$value."'";
                            break;
                        case "AI_fled":
                            $userQuery .= ", `AIfled` = `AIfled` + '".$value."'";
                            break;
                        case "AI_lost":
                            if( !isset($this->{$this->sessionside}['summary'][ $uid ]['AI_draw']) ){
                                $userQuery .= ", `AIlost` = `AIlost` + '".$value."'";
                            }
                            break;
                        case "AI_won":
                            $userQuery .= ", `AIwon` = `AIwon` + '".$value."'";
                            break;
                        case "AI_draw":
                            $userQuery .= ", `AIdraw` = `AIdraw` + '".$value."'";
                            break;
                        case "PVP_fled":
                            $userQuery .= ", `battles_fled` = `battles_fled` + '".$value."'";
                            $userQuery .= ", `pvp_streak` = '0'";
                            break;
                        case "PVP_lost":
                            if( !isset($this->{$this->sessionside}['summary'][ $uid ]['PVP_draw']) ){
                                $userQuery .= ", `battles_lost` = `battles_lost` + '".$value."'";
                            }
                            $userQuery .= ", `pvp_streak` = '0'";
                            break;
                        case "PVP_won":
                            $userQuery .= ", `battles_won` = `battles_won` + '".$value."'";
                            break;
                        case "PVP_draw":
                            $userQuery .= ", `battles_draws` = `battles_draws` + '".$value."'";
                            $userQuery .= ", `pvp_streak` = '0'";
                            break;
                        case "bounty_experience":
                            $OccupationData = new OccupationData();
                            $OccupationData->updateSpecialOccupationCache($this->{$this->sessionside}['data'][ $uid ]['bountyHunter_exp'] + $value);
                            $userQuery .= ", `bountyHunter_exp` = `bountyHunter_exp` + '".$value."'";
                            break;
                        case "feature":
                            $userQuery .= ", `feature` = ''";
                            break;
                        case "bounty":

                            // Do some epixness based on level
                            $lvl = $this->{$this->sessionside}['data'][ $uid ]['bountyHunterLevel'];

                            if($lvl > 500)
                                $lvl = 500;

                            switch( true ){
                                case $lvl <= 100: $value *= 1.05; break;
                                case $lvl <= 200: $value *= 1.075; break;
                                case $lvl <= 300: $value *= 1.10; break;
                                case $lvl <= 400: $value *= 1.125; break;
                                case $lvl <= 500: $value *= 1.15; break;
                                default: $value *= 1.175; break;
                            }

                            // Calculate max bounty
                            $max = 0;
                            if( $lvl < 500 ){
                                $max = 1000 + ( 75 * $lvl );
                            }
                            else{
                                $max = 999999999999999999;
                            }

                            // For outlaws, the maxes are much higher
                            if( $this->is_user_outlaw($this->sessionside, $uid) ){
                                $max *= 100;
                            }

                            // Check if kage
                            if( $this->is_user_kage($this->sessionside, $uid) ){
                                $value *= 1.25;
                            }

                            // Check if anbu
                            if( $this->is_user_anbu($this->sessionside, $uid) ){
                                if( $this->is_user_anbuLeader($this->sessionside, $uid) ){
                                    $value *= 1.05;
                                }
                                else{
                                    $value *= 1.025;
                                }
                            }

                            // Check if clan
                            if( $this->is_user_clan($this->sessionside, $uid) ){
                                if( $this->is_user_clanLeader($this->sessionside, $uid) ){
                                    $value *= 1.05;
                                }
                                else{
                                    $value *= 1.025;
                                }
                            }

                            // Check max
                            if( $value > $max ){
                                $value = $max;
                            }
                            $value = ceil($value);

                            // Update stuff (which is shown also)
                            $this->{$this->sessionside}['summary'][ $uid ]['bounty'] = $value;

                            // Update query
                            $userQuery .= ", `money` = `money` + '".$value."'";
                            break;
                        case "next_battle":

                            // Check that the level is above 0
                            if( $value > 0 ){

                                // Find the next opponent
                                $this->dude = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `type` = 'torn_battle' ORDER BY RAND() LIMIT 1");
                                if ($this->dude !== '0 rows') {

                                    // Fix up new opponent stuff
                                    $this->dude[0] = functions::make_ai( $this->dude[0] );
                                    $this->dude[0]['trait'] = "SCOPY:copy:".random_int(25,125).":1:1:1";

                                    // Update Database
                                    $newBattleID = functions::insertIntoBattle(
                                            array($uid),
                                            array($this->dude[0]['id']),
                                            'torn_battle',
                                            ($this->battle[0]['mission_id'] + 1),
                                            array(),
                                            $this->dude,
                                            false,
                                            true
                                    );

                                    // Overwrite status
                                    $userQuery .= ", `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$newBattleID."'";
                                    $GLOBALS['userdata'][0]['status'] = 'combat';
                                    $GLOBALS['userdata'][0]['database_fallback'] = 0;

                                    // Check if record
                                    if( $this->battle[0]['mission_id'] > $this->{$this->sessionside}['data'][ $uid ]['torn_record'] ){
                                        $userQuery .= ", `torn_record` = '".$this->battle[0]['mission_id']."'";
                                    }

                                    // Set the name for the summary page for the new oppoenent
                                    $this->{$this->sessionside}['summary'][ $uid ][ $key ] = $this->dude[0]['name'];
                                }


                            }

                        break;
                    }
                }
            }

            // Deal with reputation point losses (bingo book losses)
            foreach( $this->{$this->sessionside}['data'][ $uid ]['repLoss'] as $village => $loss ){
                if( $loss > 0 ){
                    $userQuery .= ", `$village` = `$village` - '".$loss."' ";
                    $this->{$this->sessionside}['data'][ $uid ][ $village ] -= $loss;
                }
            }

            // Deal with respect status losses

            // Check if this should make them outlaw
            if(
                $this->{$this->sessionside}['data'][ $uid ]['village'] !== "Syndicate" &&
                $this->sessionside == "user" &&
                $this->battle[0]['battle_type'] !== "kage" &&
                $this->battle[0]['battle_type'] !== "clan"
            ){
                // Get Respect
                $respect = $this->{$this->sessionside}['data'][ $uid ][ $this->{$this->sessionside}['data'][ $uid ]["village"] ];

                // Do stuff if reputation in users own village is below 0
                if (
                        ( $respect < 0 ) ||
                        (
                            (
                                $this->{$this->sessionside}['summary'][ $uid ]['allied'] > 0 ||
                                $this->{$this->sessionside}['summary'][ $uid ]['ownFaction'] > 0
                            ) &&
                            (
                                ( $this->{$this->sessionside}['data'][ $uid ]['rank_id'] == 3 && $respect < 10000 ) ||
                                ( $this->{$this->sessionside}['data'][ $uid ]['rank_id'] == 4 && $respect < 15000 ) ||
                                ( $this->{$this->sessionside}['data'][ $uid ]['rank_id'] == 5 && $respect < 17500 )
                            )
                        )
                ) {

                    // Turn outlaw
                    $respectLib = new respectLib();
                    $message = $respectLib->turn_outlaw( $_SESSION['uid'] , true );
                    if( stristr($message, "has joined") ){

                        // Outlaw variable
                        $this->{$this->sessionside}['summary'][ $uid ][ "turnOutlaw" ] = 1;

                        // Log the change
                        functions::log_village_changes(
                            $_SESSION['uid'],
                            $this->{$this->sessionside}['data'][ $uid ]['village'],
                            "Syndicate",
                            "Kicked out of village after battle. Had ".$respect." respect points."
                        );

                    }
                }
            }

            // Update the item & jutsus used
            $this->updateUsedItems( $uid );
            $this->updateUsedJutsus( $uid );

            // Pass information on what to show to smarty
            $GLOBALS['template']->assign('summary', $this->{$this->sessionside}['summary'][ $uid ] );
            $GLOBALS['template']->assign('userVillage', $this->{$this->sessionside}['data'][ $uid ]['village'] );

            // Show the main screen on main screen hook & battle log on optional hook
            $GLOBALS['template']->assign('battleLog', $this->battle[0]['log'] );
            $GLOBALS['template']->assign('topScreen', './templates/content/combat/summaryScreen.tpl');
            $GLOBALS['template']->assign('optionalScreen', './templates/content/combat/battleLog.tpl');

            // Loop through users and opponents and see if anyone is left
            $left = 0;
            foreach( array("user","opponent") as $side ){
                foreach( $this->{$side}['ids'] as $id ){
                    if( !$this->is_user_ai($side, $id) && $id !== $uid ){
                        // Some other user is still left
                        $left++;
                    }
                    elseif( $id == $uid ){
                        // Remove everything related to this user
                        $this->removeUser( $side, $id );
                    }
                }
            }

            // Update the user
            $query = "UPDATE
                        `users`,
                        `users_statistics`,
                        `users_missions`,
                        `users_timer`,
                        `bingo_book`,
                        `users_loyalty`,
                        `users_occupations`
                      SET
                        ".$userQuery."
                      WHERE
                        users.id = '".$uid."' AND
                        users.id = users_statistics.uid AND
                        users.id = users_missions.userid AND
                        users.id = users_timer.userid AND
                        users.id = users_loyalty.uid AND
                        users.id = bingo_book.userID AND
                        users.id = users_occupations.userid
                    ";
            $GLOBALS['database']->execute_query($query );

            // Update the battle depending on whether anyone is left
            if( $left > 0 ){
                $this->update_battle_playerData(
                    array( "user" => true , "opponent" => true )
                );
            }
            else{
                $query = "DELETE FROM `multi_battle` WHERE `id` = '" . $this->battle[0]['id'] . "' LIMIT 1";
                $GLOBALS['database']->execute_query($query);
            }
        }

        // This function goes through the items used by the user in the battle, increased the times_used parameter, and reduces the stack.
        private function updateUsedItems( $uid ){

            if( isset( $this->{$this->sessionside}['items'][ $uid ]) ){
                $itemUpdates = $stackItems = $durDamage = array();
                $removeFinished = "";
                foreach( $this->{$this->sessionside}['items'][ $uid ] as $item ){

                    // For updating the stack and the amount of user uses
                    if( $item['uses'] > 0){
                        $itemUpdates[ $item['inv_id'] ] = $item['uses'];
                        if( $item['type'] == "item" ){
                            $stackItems[ $item['inv_id'] ] = $item['uses'];
                            if( $item['stack'] - $item['uses'] <= 0 ){
                                $removeFinished .= ($removeFinished=="") ? $item['inv_id'] : ",".$item['inv_id'];
                            }
                        }
                    }

                    //For updating the durability thisthisthis
                    if( !in_array($this->battle[0]['battle_type'], array("mission","crime","arena","mirror_battle","torn_battle","event","rand","quest") )  ){
                        if( $item['durabilityDamage'] > 0 ){
                            if( $item['durabilityPoints'] <= 0 ){
                                $this->{$this->sessionside}['summary'][ $uid ]['itemLosses'][] = $item['name']." was destroyed during the battle";
                                $removeFinished .= empty($removeFinished) ? $item['inv_id'] : ",".$item['inv_id'];
                            }
                            else{
                                $this->{$this->sessionside}['summary'][ $uid ]['itemLosses'][] = $item['name']." lost ".$item['durabilityDamage']." durability ".functions::pluralize("point", $item['durabilityDamage'])." during the battle. ".$item['durabilityPoints']." points left.";
                            }
                            $durDamage[ $item['inv_id'] ] = $item['durabilityDamage'];
                        }
                    }
                    //else{
                        // Don't deduce stack / remove items in AI battles
                    //    $removeFinished = $stackItems = "";
                    //    throw new Exception("(do you see this)this is battle type: ". $this->battle[0]['battle_type']);

                    //    }
                }

                if( !empty($itemUpdates) || !empty($durDamage) || !empty($stackItems) ){

                    // Begin the query
                    $query = "";

                    // Update the times usedd
                    if( !empty($itemUpdates) ){
                        $query .= "`times_used` = `times_used` + CASE ";
                        foreach( $itemUpdates as $key => $value ){
                            $query .= " WHEN `id` = ".$key." THEN ".$value;
                        }
                        $query .= " ELSE 0 END";
                    }

                    // Reduce the stack
                    if( !empty($stackItems) ){
                        $query .= ($query !== "") ? ", " : "";
                        $query .= "`stack` = `stack` - CASE ";
                        foreach( $stackItems as $key => $value ){
                            $query .= " WHEN `id` = ".$key." THEN ".$value;
                        }
                        $query .= " ELSE 0 END";
                    }

                    // Reduce the durability
                    if( !empty($durDamage) ){
                        $query .= ($query !== "") ? ", " : "";
                        $query .= "`durabilityPoints` = `durabilityPoints` - CASE ";
                        foreach( $durDamage as $key => $value ){
                            $query .= " WHEN `id` = ".$key." THEN ".$value;
                        }
                        $query .= " ELSE 0 END";
                    }

                    // Finish it up
                    $query = "UPDATE `users_inventory` SET " . $query . " WHERE `uid` = '".$uid."'";
                    $GLOBALS['database']->execute_query($query);

                }
            }

        }

        // This function goes through the jutsus used by the user in the battle and increases the times_used parameter
        private function updateUsedJutsus( $uid ){
            if( isset( $this->{$this->sessionside}['jutsus'][ $uid ]) ){
                $jutsuUpdates = array();
                foreach( $this->{$this->sessionside}['jutsus'][ $uid ] as $jutsu ){
                    if( isset($jutsu['uses']) && $jutsu['uses'] > 0){
                        $jutsuUpdates[ $jutsu['id'] ] = array( $jutsu['uses'], $jutsu['exp'], $jutsu['level'] );
                    }
                }
                if( !empty($jutsuUpdates) ){

                    // Begin the query
                    $query = "UPDATE `users_jutsu`";

                    // Update the times used
                    $query .= "SET `times_used` = `times_used` + CASE ";
                    foreach( $jutsuUpdates as $key => $value ){
                        $query .= " WHEN `jid` = ".$key." THEN ".$value[0];
                    }
                    $query .= " ELSE 0 END";

                    // Update the experience
                    $query .= ", `exp` = CASE ";
                    foreach( $jutsuUpdates as $key => $value ){
                        $query .= " WHEN `jid` = ".$key." THEN ".$value[1];
                    }
                    $query .= " ELSE `exp` END";

                    // Update the level
                    $query .= ", `level` = CASE ";
                    foreach( $jutsuUpdates as $key => $value ){
                        $query .= " WHEN `jid` = ".$key." THEN ".$value[2];
                    }
                    $query .= " ELSE `level` END";

                    // Finish it up
                    $query .= " WHERE `uid` = '".$uid."'";
                    $GLOBALS['database']->execute_query($query);
                }
            }
        }

    }
