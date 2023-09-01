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

    // Get the battle initiation library
    require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');

    // Fight Class
    class fight extends battleInitiation {

        // Constructor class
        public function __construct() {

            // wrap in try
            try {

                // Check session
                functions::checkActiveSession();

                // Before allowing to watch, check for current battles
                $this->battle = functions::checkIfInBattle($GLOBALS['userdata'][0]['id'], $GLOBALS['userdata'][0]['battle_id'], false);

                // If in battle && Awake
                if ($this->battle === '0 rows' || empty($this->battle)) {
                    if (!isset($_GET['act'])) {
                        $this->show_people();
                    }
                    elseif ($_GET['act'] === 'fight')
                    {

                        $this->setTarget($_GET['uid']);
                        $this->initiate_fight();

                    }
                    elseif ($_GET['act'] === 'aid') {
                        $this->setTarget($_GET['uid']);
                        $this->aid_fight();
                    }
                }
                else {
                    if($GLOBALS['userdata'][0]['status'] === "awake") {

                        // User is in battle, set him as such
                        try {
                            $GLOBALS['database']->transaction_start();
                            if($GLOBALS['database']->execute_query('
                                SELECT `users`.`id`
                                FROM `users`
                                WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` = "awake"
                                LIMIT 1 FOR UPDATE'
                            ) === false) {
                                throw new Exception('There was an issue obtaining user lock for battle!');
                            }
                            if($GLOBALS['database']->execute_query('
                                UPDATE `users`
                                SET `users`.`status` = "combat", `users`.`battle_id` = '.$this->battle[0]['id'].'
                                WHERE `users`.`id` = '.$_SESSION['uid'].'
                                LIMIT 1'
                            ) === false) {
                                throw new Exception('There was an issue putting your character into battle!');
                                $GLOBALS['database']->transaction_commit();
                            }
                        }
                        catch(Exception $e) {
                            $GLOBALS['database']->transaction_rollback($e->getMessage());
                            $GLOBALS['page']->Message($e->getMessage(), "Battle System", 'id='.$_GET['id'], 'Return');
                        }
                    }
                    else {
                        if($GLOBALS['userdata'][0]['status'] === 'combat' || $GLOBALS['userdata'][0]['status'] === 'exiting_combat') {
                            throw new Exception("The system has found your character to be engaged in battle!");
                        }
                        try {
                            $GLOBALS['database']->transaction_start();
                            if($GLOBALS['database']->execute_query('
                                SELECT `users`.`id` FROM `users`
                                WHERE `users`.`id` = '.$_SESSION['uid'].'
                                LIMIT 1 FOR UPDATE'
                            ) === false) {
                                throw new Exception('There was an issue obtaining user lock for battle removal!');
                            }
                            if($GLOBALS['database']->execute_query('
                                DELETE FROM `multi_battle`
                                WHERE `multi_battle`.`id` = '.$GLOBALS['userdata'][0]['battle_id'].'
                                LIMIT 1'
                            ) === false) {
                                throw new Exception("An error occurred removing the battle data!");
                            }
                            if($GLOBALS['database']->execute_query("
                                UPDATE `users`
                                SET `users`.`battle_id` = 0
                                WHERE `users`.`id` = ".$_SESSION['uid']."
                                LIMIT 1"
                            ) === false) {
                                throw new Exception('There was an issue putting your character out of battle!');
                            }
                            $GLOBALS['database']->transaction_commit();
                        }
                        catch(Exception $e) {
                            $GLOBALS['database']->transaction_rollback($e->getMessage());
                            $GLOBALS['page']->Message($e->getMessage(), "Battle System", 'id='.$_GET['id'], 'Return');
                        }
                    }
                }

            }
            catch(Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "Battle System", 'id='.$_GET['id'], 'Return');
            }
        }

        // Function for checking if in war, and if so, destroy SP points whenever no SP points have been claimed for 60 seconds
        private function destroy_SP() {

            // Get the current location & check if in enemy village
            $currentLocation = ucwords(str_replace(" village", "", $GLOBALS['userdata'][0]['location']));
            if(!in_array($currentLocation, Data::$VILLAGES, true)) {
                return false;
            }

            // Check if enemy
            if( (int)$this->alliance[0][ $currentLocation ] !== 2 || $GLOBALS['userdata'][0]['village'] == "Syndicate") {
                return false;
            }

            // Check last time user village lost SP
            if(!($userHistory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_actionLog`
                WHERE
                    (`users_actionLog`.`action` = 'warKill' AND `users_actionLog`.`attached_info` LIKE '%".$GLOBALS['userdata'][0]['village']."%') OR
                    (`users_actionLog`.`uid` = ".$GLOBALS['userdata'][0]['id']." AND `users_actionLog`.`action` = 'SPsabotage' AND
                     `users_actionLog`.`attached_info` = '".$GLOBALS['userdata'][0]['village']."->".$currentLocation."')
                ORDER BY `users_actionLog`.`time` DESC LIMIT 1")))
            {
                throw new Exception('An error occurred obtaining User Action Log!');
            }

            // Set the time since last
            $time = ($userHistory !== "0 rows") ?
                $GLOBALS['user']->load_time - $userHistory[0]['time'] : 60;

            // The time must actually only be since the users last move action. So figure out what that is
            $movements = json_decode(cachefunctions::getMovements($_SESSION['uid']), true);
            if(!empty($movements)) {
                $lastMovement = $movements[0];
                if(isset($lastMovement['time'])) {
                    $timeSinceMovement = $GLOBALS['user']->load_time - $lastMovement['time'];
                    if($timeSinceMovement < $time) { $time = $timeSinceMovement; }
                }
            }

            // The timer should also not be shorter than the time since last battle
            $timeSinceBattle = $GLOBALS['user']->load_time - $GLOBALS['userdata'][0]['last_battle'];
            if( $timeSinceBattle > 0 && $timeSinceBattle < 60 ){
                if( $timeSinceBattle < $time ){
                    $time = $timeSinceBattle;
                }
            }

            // Fix time
            $time = $time < 0 ? 0 : $time;
            $time = $time > 60 ? 60 : $time;

            // Set message for user
            if($time < 60) {
                 return 'As long as nobody attacks you, you can stay on this page and sabotage structure points in this village.'.
                    ' Time to Sabotage: '.functions::convert_time(60 - $time, 'SabotageCooldown', 'false');
            }

            try {
                $GLOBALS['database']->transaction_start();

                // Get the loser village alliance
                $lAlliances = cachefunctions::getAlliance($currentLocation);

                // Get the war library to reduce points
                require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
                $warLib = new warLib();
                $warLib->structures_reducePoints($currentLocation, $lAlliances, $GLOBALS['userdata'][0]['village'], "sabotage");

                // Insert action log
                functions::log_user_action($GLOBALS['userdata'][0]['id'], "SPsabotage", $GLOBALS['userdata'][0]['village']."->".$currentLocation);

                if($GLOBALS['database']->execute_query("SELECT `users_missions`.`userid` FROM `users_missions`
                    WHERE `users_missions`.`userid` = ".$_SESSION['uid']." LIMIT 1 FOR UPDATE") === false) {
                    throw new Exception('There was an error trying to lock User mission data!');
                }

                // Update user variables
                if($GLOBALS['database']->execute_query("UPDATE `users_missions`
                    SET `users_missions`.`structureDestructionPoints` = `users_missions`.`structureDestructionPoints` + 1,
                        `users_missions`.`structurePointsActivity` = `users_missions`.`structurePointsActivity` + 1
                    WHERE `users_missions`.`userid` = ".$_SESSION['uid']." LIMIT 1") === false) {
                    throw new Exception('An error occurred while sabotaging structure points!');
                }

                $GLOBALS['database']->transaction_commit();

                // Return message
                return 'You have sabotaged a structure point in '.$currentLocation.'!';
            }
            catch(Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                return $e->getMessage();
            }
        }

        // Show people in the area you can attack
        private function show_people() {

            // Limit this page for macros
            $GLOBALS['page']->updateCaptchaPageCounter( 5, 2 );

            // On API call, allow traveling on this page
            if( functions::isAPIcall() && (isset($_GET['move'])) ){
                require_once(Data::$absSvrPath.'/libs/travelSystem/travelLib.php');
                require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
                $travelBackend = new travelLib();
                $travelBackend->set_data();
                $travelBackend->load_travel();
            }

            // Locally store alliance
            $this->alliance = $GLOBALS['userdata'][0]['alliance'];

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

            // Get users list
            if(!($users = $GLOBALS['database']->fetch_data("
                SELECT
                    `users`.`id`, `users_statistics`.`rank`, `users_statistics`.`rank_id`,
                    `users`.`latitude`, `users`.`longitude`,
                    `users`.`username`, `users`.`status`, `users_loyalty`.`village`,
                    `users_timer`.`last_activity`, `users`.`cfh`, `users`.`battle_id`,
                    `structurePointsActivity`, `users_timer`.`battle_colldown`,
                    `users_statistics`.`DSR`, `users_statistics`.`dr`,
                    `users_statistics`.`sr`, `users_statistics`.`cur_health`,
                    `users_statistics`.`max_health`, `users`.`location`
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_timer`.`userid`)
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users_statistics`.`uid`)
                    INNER JOIN `users_missions` ON (`users_loyalty`.`uid` = `users_missions`.`userid`)
                WHERE 

                    (
                        (
                            `users`.`longitude` <= ({$GLOBALS['userdata'][0]['longitude']} + 1)
                            AND 
                            `users`.`longitude` >= ({$GLOBALS['userdata'][0]['longitude']} - 1)
                            AND 
                            `users`.`latitude` <= ({$GLOBALS['userdata'][0]['latitude']} + 1)
                            AND 
                            `users`.`latitude` >= ({$GLOBALS['userdata'][0]['latitude']} - 1)
                            AND
                            `users`.`location` not like '%outskirt%'
                            AND
                            `users`.`location` not in ( \"Konoki\",\"Silence\",
                                                    \"Shroud\",\"Shine\",
                                                    \"Samui\",
                                                    
                                                    \"Gambler's Den\",\"Bandit's Outpost\",
                                                    \"Poacher's Camp\",\"Pirate's Hideout\")
                        )
                        OR
                        (
                            `users`.`location` = '".str_replace("'","\'",$GLOBALS['userdata'][0]['location'])."'
                            AND
                            (
                                `users`.`location` not in ('ocean','unknown','uncharted','lake','dead lake','shore','river')
                                OR
                                (
                                    `users`.`longitude` = {$GLOBALS['userdata'][0]['longitude']}
                                    AND
                                    `users`.`latitude` = {$GLOBALS['userdata'][0]['latitude']}
                                )
                            )
                        )
                    ) AND

                    `users`.`status` NOT IN('asleep', 'hospitalized', 'jailed', 'drowning', 'questing', 'exiting_combat')  AND
                    `users_timer`.`last_activity` >= ".($GLOBALS['user']->load_time - $activity_visibility)." AND
                    `users_statistics`.`cur_health` > 0 AND
                    ".( $GLOBALS['userdata'][0]['user_rank'] != 'Admin' ? "(`users_statistics`.`user_rank` NOT IN('Admin') OR `users_statistics`.`uid` = ".$_SESSION['uid']." )AND" : "")."
                    (
                        ( `users_statistics`.`rank_id` > 2 AND
                          `users_statistics`.`rank_id` <= ".($GLOBALS['userdata'][0]['rank_id'] + 1)." AND
                          `users_statistics`.`rank_id` >= ".($GLOBALS['userdata'][0]['rank_id'] - 1)."
                        ) OR (`users_statistics`.`rank` = 'ALL')
                    )
                    ORDER BY (`users_statistics`.`dr` * ((`users_statistics`.`max_health`) / `users_statistics`.`sr`) ) DESC, `users_statistics`.`experience` DESC
                    LIMIT 40")))
            {
                throw new Exception('There was an error trying to obtain users in the area!');
            }


            $self = array();
            if(is_array($users))
                foreach( $users as $userdata )
                    if ($userdata['id'] == $_SESSION['uid'])
                        $self = $userdata;

            $can_attack = $cant_attack = array();

            // Parse data in the users array
            if ($users !== "0 rows") {
                for ($i = 0, $size = count($users); $i < $size; $i++) {

                    // The two vars to determine
                    $standing = "Unknown";

                    //getting dsr
                    if($users[$i]['sr'] != 0)
                        $users[$i]['DSR'] = base_convert(floor(sqrt(($users[$i]['dr'] * ( ( $users[$i]['cur_health']             *   0.5 + $users[$i]['max_health'] * 0.5 ) / $users[$i]['sr'] ))+4+24789)), 10, 9);
                    else
                        $users[$i]['DSR'] = 0;

                    $users[$i]['direction'] = '';

                    if( $users[$i]['longitude'] == $GLOBALS['userdata'][0]['longitude'] && 
                        $users[$i]['latitude'] == $GLOBALS['userdata'][0]['latitude'])
                    {
                        $users[$i]['direction'] = 'X';
                    }
                    else
                    {
                        if($users[$i]['latitude'] > $GLOBALS['userdata'][0]['latitude'])
                            $users[$i]['direction'] .= 'N';
                        else if($users[$i]['latitude'] < $GLOBALS['userdata'][0]['latitude'])
                            $users[$i]['direction'] .= 'S';

                        if($users[$i]['longitude'] > $GLOBALS['userdata'][0]['longitude'])
                            $users[$i]['direction'] .= 'E';
                        else if($users[$i]['longitude'] < $GLOBALS['userdata'][0]['longitude'])
                            $users[$i]['direction'] .= 'W';
                    }

                    // Check if this is the user him/her-self
                    if ($users[$i]['id'] !== $_SESSION['uid']) {

                        // Set the standing
                        switch ($this->alliance[0][$users[$i]['village']]) {
                            case 0:
                                $standing = "Neutral";
                            break;
                            case 1:
                                $standing = "Ally";
                            break;
                            case 2:
                                $standing = "Enemy";
                            break;
                        }

                        // Syndicate fix
                        if($users[$i]['village'] === "Syndicate" && $GLOBALS['userdata'][0]['village'] === "Syndicate") {
                            $standing = "Neutral";
                        }

                        // Profile link
                        //if( $users[$i]['structurePointsActivity'] < -100 &&
                        //    (int) $this->alliance[0][$users[$i]['village']] !== 1 &&
                        //    $users[$i]['village'] !== $GLOBALS['userdata'][0]['village'])
                        //{
                        //    $color = "#800080";
                        //}

                        // Encryption Code
                        $code = md5($GLOBALS['user']->load_time . "-" . $users[$i]['id'] . "-" .$GLOBALS['userdata'][0]['longitude'] . "-" .$GLOBALS['userdata'][0]['latitude']);

                        // Set the standing, and username colors
                        if( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true )
                        {
                            $users[$i]["username"] = $users[$i]["username"];
                            $users[$i]["standing"] = $standing;
                        }
                        else
                        {
                            $users[$i]["username"] = "<a href='?id=13&amp;page=profile&amp;name=".$users[$i]["username"]."'>".$users[$i]["username"]."</a>";
                            $users[$i]["standing"] = $standing;
                        }

                        // Set the potential link to attack/aid/nothing
                        if ($users[$i]['status'] === 'combat' || $users[$i]['status'] === 'exiting_combat' )
                        {
                            if ($users[$i]['cfh'] != '' && $users[$i]['cfh'] != 'called') 
                            {
                                $aid = $this->aid_conditions($self, $users[$i]);

                                if ( $aid === true)
                                {
                                    if( $users[$i]['village'] === $GLOBALS['userdata'][0]['village'] || (int)$this->alliance[0][$users[$i]['village']] === 1 )
                                    {
                                        $users[$i]["action-text"] = '';
                                        $users[$i]["action-link"] = ' <a style="width:100%;display:inline-block;" href="?id='.$_GET['id'].'&amp;act=aid&amp;uid='.$users[$i]['id'].'&amp;code='.$code.'"><b>Calling for Help</b></a>';
                                        $users[$i]["action-link-type"] = 'Help';
                                        $can_attack[] = $users[$i];
                                    }
                                    else
                                    {
                                        $users[$i]["action-text"] = 'In battle';
                                        $cant_attack[] = $users[$i];
                                    }
                                }
                                else if( is_array($aid) && ($users[$i]['village'] === $GLOBALS['userdata'][0]['village'] || (int)$this->alliance[0][$users[$i]['village']] === 1) )
                                { 
                                    $users[$i]["action-text"] = 'CFH: '.base_convert(floor(sqrt($aid[0]+4+24789)), 10, 9).' to '.base_convert(floor(sqrt($aid[1]+4+24789)), 10, 9); 
                                    $cant_attack[] = $users[$i];
                                }
                                else
                                {
                                    $users[$i]["action-text"] = 'In battle';
                                    $cant_attack[] = $users[$i];
                                }
                            }
                            else
                            {
                                $users[$i]["action-text"] = 'In battle';
                                $cant_attack[] = $users[$i];
                            }
                        }
                        elseif ($this->battle_conditions($users[$i]))
                        {
                            if($users[$i]['village'] == $GLOBALS['userdata'][0]['village'])
                            {
                                $users[$i]["action-link"] = '<a style="width:100%;display:inline-block;" href="?id='.$_GET['id'].'&amp;act=fight&amp;uid='.$users[$i]['id'].'&amp;code='.$code.'"><i>Betray</i></a>';
                                $users[$i]["action-link-type"] = 'Betray';
                                $can_attack[] = $users[$i];
                            }
                            else if($users[$i]['location'] == $GLOBALS['userdata'][0]['location'])
                            {
                                $users[$i]["action-link"] = '<a style="width:100%;display:inline-block;" href="?id='.$_GET['id'].'&amp;act=fight&amp;uid='.$users[$i]['id'].'&amp;code='.$code.'"><b>Attack</b></a>';
                                $users[$i]["action-link-type"] = 'Attack-'.$users[$i]['standing'];
                                $can_attack[] = $users[$i];
                            }
                            else
                            {
                                $users[$i]["action-link"] = '<a style="width:100%;display:inline-block;" href="?id='.$_GET['id'].'&amp;act=fight&amp;uid='.$users[$i]['id'].'&amp;code='.$code.'">Chase</a>';
                                $users[$i]["action-link-type"] = 'Chase-'.$users[$i]['standing'];
                                $can_attack[] = $users[$i];
                            }
                        }
                        else
                        {
                            $users[$i]["action-text"] = 'Awake';
                            $cant_attack[] = $users[$i];
                        }
                    }
                    else
                    {
                        $standing = "You";
                        $users[$i]["standing"] = 'Self';
                        $users[$i]["action-text"] = 'Unattackable';
                        $can_attack[] = $users[$i];
                        //$cant_attack[] = $users[$i];
                    }

                }
            }

            if(count($can_attack) == 0)
                $can_attack = array();

            if(count($cant_attack) == 0)
                $cant_attack = array();

            //sorting array
            $users = array_merge($can_attack,$cant_attack);

            // Check for structure points sabotage
            $topMessage = $this->destroy_SP();

            // Determine which fields to show
            if( functions::isAPIcall() ){
                $GLOBALS['template']->assign('preventStretch', true);
                $showFields = array(
                    'username' => "Name",
                    'standing' => "Standing",
                    'status' => "Status"
                );
            }
            else{
                $showFields = array(
                    'username' => "Name",
                    'rank' => "Rank",
                    'village' => "Village",
                    'last_activity' => "Activity",
                    'standing' => "Standing",
                    'status' => "Status"
                );
            }

            $settings = json_decode($GLOBALS['userdata'][0]['fight_settings'], true);
            if(is_null($settings) || strlen($GLOBALS['userdata'][0]['fight_settings']) < 6)
                $settings = json_decode('{"village":true,"rank":true,"activity":false,"dsr":true,"directions":true,"all_text_color_match_alliance":false,"rank_compress":false,"hide_syndicate_ranks":false,"hide_self":false,"hide_ally":false,"hide_betray":true,"hide_call_for_help":false,"hide_glimpseable":false,"hide_chase":false,"colors":{"Ally":"#6aa84f","Self":"#6aa84f","Neutral":"#3c78d8","Enemy":"#a61c00","Betray":"#a64d79","Faint":"#999999","Attack-Neutral":"#3c78d8","Attack-Enemy":"#a61c00","Chase-Neutral":"#3c78d8","Chase-Enemy":"#a61c00","Help":"#6aa84f"}}', true);

            $GLOBALS['template']->assign('settings', $settings);
            $GLOBALS['template']->assign('contentLoad', './templates/content/fight/fight.tpl');
            $GLOBALS['template']->assign('users', $users);
            $GLOBALS['template']->assign('userdata', $GLOBALS['userdata'][0]);
        }
    }

    new fight();