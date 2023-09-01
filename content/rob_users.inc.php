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

class robbing extends battleInitiation {

    // Constructor
    public function __construct() {

        // Run in try
        try {

            // Obtain lock

            // Check the last time the user robbed someone
            $cooldown = $GLOBALS['user']->load_time - $GLOBALS['userdata'][0]['last_robbing'];
            if ($cooldown > 15) {

                // Before allowing to rob, check if user is in battle
                $cooldown2 = $GLOBALS['user']->load_time - $GLOBALS['userdata'][0]['battle_colldown'];
                if ($cooldown2 > 15) {



                    // Before allowing to watch, check for current battles
                    $this->battle = functions::checkIfInBattle(
                        $GLOBALS['userdata'][0]['id'],
                        $GLOBALS['userdata'][0]['battle_id']
                    );

                    // If in battle && Awake
                    if ($this->battle == '0 rows') {

                        // Decide what page to show
                        if (!isset($_GET['act'])) {
                            $this->show_people();
                        } elseif ($_GET['act'] == 'rob') {
                            $this->do_rob();
                        }

                    } else {
                        if ($GLOBALS['userdata'][0]['status'] == "awake") {
                            $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                            $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$this->battle[0]['id']."' WHERE `id` = '" . $_SESSION['uid'] . "' AND `status` = 'awake' LIMIT 1");
                            $GLOBALS['userdata'][0]['database_fallback'] = 0;
                        }
                        $GLOBALS['page']->Message("The system has found your character to be engaged in battle", 'Battle System', 'id=113', "Go to battle");
                    }
                }
                else{
                    $GLOBALS['page']->Message('You cannot rob anyone during the next: ' . (15 - $cooldown2) . ' seconds.', 'Combat', 'id=' . $_GET['id'] . '');
                }
             }
            else{
                $GLOBALS['page']->Message('You cannot rob anyone during the next: ' . (15 - $cooldown) . ' seconds.', 'Combat', 'id=' . $_GET['id'] . '');
            }

        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), 'Robbing System', 'id=2');
        }
    }

     // Show people in the area you can attack
    private function show_people() {

        // Limit this page for macros
        $GLOBALS['page']->updateCaptchaPageCounter( 5, 2 );

        // Locally store alliance
        $this->alliance = $GLOBALS['userdata'][0]['alliance'];

        // Get users list
        $users = $GLOBALS['database']->fetch_data("
                        SELECT `id`,`rank`,`rank_id`,`username`,`status`,`village`,`last_activity`,`money`,`battle_colldown`
                        FROM `users`,`users_timer`,`users_statistics` WHERE
                                `users_statistics`.`user_rank` != 'Admin' AND
                                `users`.`id` = `users_timer`.`userid` AND
                                `users`.`id` = `users_statistics`.`uid` AND
                                (
                                    (
                                        `users`.`longitude` = {$GLOBALS['userdata'][0]['longitude']}
                                        AND
                                        `users`.`latitude` = {$GLOBALS['userdata'][0]['latitude']}
                                    )
                                    OR
                                    `users`.`location` = \"{$GLOBALS['userdata'][0]['location']}\"
                                )
                                AND
                                `last_activity` >= '" . ($GLOBALS['user']->load_time - 120) . "' AND
                                `status` = 'awake' AND
                                ((`rank_id` > 2 AND
                                  `rank_id` <= " . ($GLOBALS['userdata'][0]['rank_id'] + 1) . " AND
                                  `rank_id` >= " . ($GLOBALS['userdata'][0]['rank_id'] - 1) . ") OR
                                  (`rank` = 'ALL')
                                )
                         ORDER BY `money` DESC LIMIT 100");

        // Parse data in the users array
        if ($users !== "0 rows") {
            for ($i = 0; $i < count($users); $i++) {

                // Check if this is the user him/her-self
                if ($users[$i]['id'] != $_SESSION['uid']) {

                    // Set the standing
                    switch ($this->alliance[0][ $users[$i]['village'] ]) {
                        case 0: $users[$i]["standing"] = '<font color="#0000FF">Neutral</font>';
                            break;
                        case 1: $users[$i]["standing"] = '<font color="#008000">Ally</font>';
                            break;
                        case 2: $users[$i]["standing"] = '<font color="#800000">Enemy</font>';
                            break;
                    }

                    // Encryption Code
                    $code = md5($GLOBALS['user']->load_time . "-" . $users[$i]['id']. "-" .$GLOBALS['userdata'][0]['longitude'] . "-" .$GLOBALS['userdata'][0]['latitude']);

                    // Set the potential link to attack/aid/nothing
                    if ( $this->battle_conditions($users[$i]) ) {
                        $users[$i]["status"] = '<a href="?id=' . $_GET['id'] . '&act=rob&uid=' . $users[$i]['id'] . '&code=' . $code . '"><b>Rob User</b></a>';
                    } else {
                        $users[$i]["status"] = 'Awake';
                    }
                } else {
                    $users[$i]["standing"] = '<font color="#008000">You</font>';
                    $users[$i]["status"] = 'Unrobbable';
                }
            }
        }

        // Show the table of users
        tableParser::show_list(
                'users', 'People to Rob in your Area ('.$GLOBALS["userdata"][0]['longitude'].','.$GLOBALS["userdata"][0]['latitude'].')', $users, array(
            'username' => "Name",
            'rank' => "Rank",
            'village' => "Village",
            'last_activity' => "Last Activity",
            'standing' => "Standing",
            'status' => "Status"
                ),
                false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            array('message'=>"As an outlaw you can attempt to rob other users",'hidden'=>'yes')
        );
    }

    // Do initiate robbery
    private function do_rob() {

        try{

            //create lock on other
            $GLOBALS['database']->get_lock('battle',$_GET['uid'],__METHOD__);

            //create lock on self
            $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);

            // Make this thing transaction Safe
            $GLOBALS['database']->transaction_start();

            $this->battle = functions::checkIfInBattle( $_GET['uid'] );
            if ($this->battle == '0 rows') {

                // Set target. Returns transaction safe target
                $this->setTarget($_GET['uid']);
                if( $target = $this->can_go_to_battle( "combat" ) ){

                    // Stop harvesting
                    cachefunctions::endHarvest( $_GET['uid'] );

                    // Rob chance
                    $robChance = 5;
                    $wall_rob_level = 0;

                    // Get walls effect
                    $villageData = $GLOBALS['database']->fetch_data("SELECT `wall_rob_level`, `wall_def_level` FROM `village_structures`,`villages` WHERE `latitude` = '" . $target[0]['latitude'] . "' AND `longitude` = '" . $target[0]['longitude'] . "' AND `village_structures`.`name` = `villages`.`name`");
                    if( $villageData !== "0 rows" ){
                        if(isset($villageData[0]['wall_rob_level']))
                        {
                            $wall_rob_level = $villageData[0]['wall_rob_level'];
                            $robChance -= $villageData[0]['wall_rob_level']*2.5;
                        }
                        else
                            $wall_rob_level = 0;
                    }
                    else
                        $wall_rob_level = 0;


                    // Check for loyalty reductions
                    if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
                        switch( true ){
                            case $GLOBALS['userdata'][0]['vil_loyal_pts'] <= 40: $robChance += 10; break;
                            case $GLOBALS['userdata'][0]['vil_loyal_pts'] <= 20: $robChance += 7.5; break;
                            case $GLOBALS['userdata'][0]['vil_loyal_pts'] <= 10: $robChance += 5; break;
                        }
                    }


                    $targetDefense = $target[0]['tai_def'] + $target[0]['nin_def'] + $target[0]['gen_def'] + $target[0]['weap_def'];
                    $userDefense =  $GLOBALS['userdata'][0]['tai_def'] + $GLOBALS['userdata'][0]['nin_def'] + $GLOBALS['userdata'][0]['gen_def'] + $GLOBALS['userdata'][0]['weap_def'];

                    $add= (45-2*$villageData[0]['wall_rob_level'])*(1-( $targetDefense -$userDefense)/5000000)*(1-(($target[0]['speed']+$target[0]['willpower']+$target[0]['strength']+$target[0]['intelligence'])-($GLOBALS['userdata'][0]['speed']+$GLOBALS['userdata'][0]['willpower']+$GLOBALS['userdata'][0]['intelligence']+$GLOBALS['userdata'][0]['strength']))/1000000);

                    if($add > 85)
                        $add = 85;

                    $robChance += $add;

                    // Money stolen
                    $minPerc = 5;
                    if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
                        switch( true ){
                            case $GLOBALS['userdata'][0]['vil_loyal_pts'] <= 160: $minPerc += 10; break;
                            case $GLOBALS['userdata'][0]['vil_loyal_pts'] <= 80: $minPerc += 5; break;
                        }
                    }

                    // Get rob percentage
                    $money_perc = ( $GLOBALS['page']->inOutlawBase ) ? random_int(35, 75) : random_int($minPerc, 50);

                    // Money gain
                    $money_gain = floor(($target[0]['money'] / 100) * $money_perc);

                    // Check if successfull
                    if( $robChance >= random_int(1,100) && $money_gain > 0 ){

                        $result = $GLOBALS['database']->fetch_data("SELECT `".$target[0]['village']."` as `diplomacy` FROM `bingo_book` WHERE `userID` = ".$GLOBALS['userdata'][0]['id']);

                        $GLOBALS['Events']->acceptEvent('diplomacy_loss', array('new'=>$result[0]['diplomacy'] - ($money_gain/10), 'old'=>$result[0]['diplomacy'], 'context'=>$target[0]['village']));

                        $extra = ",`".$target[0]['village']."` = (`".$target[0]['village']."` - (".$money_gain."/10))";
                        // Update the database
                        if ($GLOBALS['database']->execute_query("
                            UPDATE `users`,`users_statistics`
                            SET
                                `money` = `money` - '" . $money_gain . "'
                            WHERE
                                `users`.`id` = '" . $target[0]['id'] . "' AND `users_statistics`.`uid` = `users`.`id`") !== false)
                        {

                            $users_notifications = new NotificationSystem('', $target[0]['id']);
                            $events = new Events($target[0]['id']);

                            $users_notifications->addNotification(array(
                                                                        'id' => 9,
                                                                        'duration' => 'none',
                                                                        'text' => $GLOBALS['userdata'][0]['username'] . " has stolen " . $money_gain . " ryo.",
                                                                        'dismiss' => 'yes'
                                                                    ));

                            $result = $GLOBALS['database']->fetch_data('SELECT `money` FROM `users_statistics` WHERE `uid` = '.$target[0]['id']);

                            $events->acceptEvent('money_loss', array('old'=>$result[0]['money'],'new'=> $result[0]['money'] - $money_gain));

                            $events->closeEvents();

                            $users_notifications->recordNotifications();

                            if(!
                            $GLOBALS['database']->execute_query("
                                UPDATE
                                    `users`,`users_statistics`,`users_timer`,`bingo_book`
                                SET
                                    `money` = `money` + '" . $money_gain . "',
                                    `last_robbing` = '".$GLOBALS['user']->load_time."'
                                    ".$extra."
                                WHERE
                                    `users`.`id` = '" . $GLOBALS['userdata'][0]['id'] . "' AND
                                    `id` = `uid` AND
                                    `users_timer`.`userid` = `id` AND
                                    `bingo_book`.`userID` = `id`"))

                            {
                                throw new Exception('There was an issue with updating your information. '."
                                UPDATE
                                    `users`,`users_statistics`,`users_timer`,`bingo_book`
                                SET
                                    `money` = `money` + '" . $money_gain . "',
                                    `last_robbing` = '".$GLOBALS['user']->load_time."'
                                    ".$extra."
                                WHERE
                                    `users`.`id` = '" . $GLOBALS['userdata'][0]['id'] . "' AND
                                    `id` = `uid` AND
                                    `users_timer`.`userid` = `id` AND
                                    `bingo_book`.`userID` = `id`");
                            }
                            else
                            {
                                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $money_gain));
                            }

                            $GLOBALS['page']->Message('You successfully robbed the user and stole ' . $money_gain . ' ryo!', 'Rob Users', 'id=' . $_GET['id'] . '');
                        }
                        else{

                            // Database error
                            $GLOBALS['page']->Message('There was an error updating the database: '."
                            UPDATE `users`,`users_statistics`
                            SET
                                `money` = `money` - '" . $money_gain . "',
                            WHERE
                                `users`.`id` = '" . $target[0]['id'] . "' AND `users_statistics`.`uid` = `users`.`id`", 'Rob Users', 'id=' . $_GET['id'] . '');
                        }
                    } else {
                        //	Theft failed, initiate battle!
                        $this->initiate_fight("Your attempt at robbery has failed and you have been caught by ", 'hard', true);
                    }
                }
            }
            else{
                throw new Exception("Could not rob user because the character was found to be in battle already.");
            }

            // At this point commit any hanging transactions
            $GLOBALS['database']->transaction_commit();

            //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);

            //release lock on other
            $GLOBALS['database']->release_lock('battle',$_GET['uid']);

        } catch (Exception $e) {
            //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);

            //release lock on other
            $GLOBALS['database']->release_lock('battle',$_GET['uid']);

            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), "Robbing System", 'id='.$_GET['id'], 'Return');
        }
    }

}

new robbing();