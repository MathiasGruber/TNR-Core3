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
class spar {

    private $__filters = array(
        'all' => array('name' => 'All', 'rank_id' => false),
        'elite_jounin' => array('name' => 'Elite Jounin', 'rank_id' => 5),
        'jounin' => array('name' => 'Jounin', 'rank_id' => 4),
        'chuunin' => array('name' => 'Chuunin', 'rank_id' => 3),
        'genin' => array('name' => 'Genin', 'rank_id' => 2),
        'academy_student' => array('name' => 'Academy Student', 'rank_id' => 1),
    );

    function __construct() {

        try{
            if (!isset($_GET['act']) && !isset($_GET['joining'])) {
                $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);
                $this->main_page();
            } elseif (isset($_GET['joining']) && is_numeric($_GET['joining'])) {
                $this->respond();
            } elseif ($_GET['act'] == 'challenge') {
                $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);
                $this->do_challenge();
            } elseif ($_GET['act'] == 'del') {
                $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);
                $this->delete_challenge();
            } elseif ($_GET['act'] == 'accept') {
                $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);
                $this->accept_challenge();
            }

            if($GLOBALS['database']->release_lock('battle',$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Spar System', 'id='.$_GET['id'], "Return");
        }
    }

    // The main page shown to the user
    private function main_page() {

        // Load the people
        $this->show_people();

        // Use the table parser library to show notes in system
        $spar_data = $GLOBALS['database']->fetch_data('SELECT `spar_challenges`.*, `users`.`username`
            FROM `spar_challenges`
                INNER JOIN `users` ON (`users`.`id` = IF(`spar_challenges`.`uid` = '.$_SESSION['uid'].', `spar_challenges`.`oid`, `spar_challenges`.`uid`))
            WHERE `spar_challenges`.`uid` = '.$_SESSION['uid'].' OR `spar_challenges`.`oid` = '.$_SESSION['uid'].' LIMIT 1');

        // Check if the user has any challenges
        if( $spar_data !== "0 rows" ){

            // Check if challenger or not
            $isChallenger = ($spar_data[0]['uid'] === $_SESSION['uid']) ? true : false;

            // Show different tables
            if( $isChallenger ){

                // Set challenger
                $spar_data[0]['challenger'] = $GLOBALS['userdata'][0]['username'];

                // Show the table
                tableParser::show_list(
                        'challenges', 'Your Challenges', $spar_data,
                        array(
                            'challenger' => "Challenger",
                            'username' => "Opponent",
                            'time' => "Time"
                        ), array(
                            array("name" => "Delete", "act" => "del", "cid" => "table.id")
                        ), false, false
                );
            }
            else{

                // Set challenger
                $spar_data[0]['challenged'] = $GLOBALS['userdata'][0]['username'];

                // Show the table
                tableParser::show_list(
                        'challenges', 'Your Challenges', $spar_data,
                        array(
                            'username' => "Challenger",
                            'challenged' => "Opponent",
                            'time' => "Time"
                        ), array(
                            array("name" => "Delete", "act" => "del", "cid" => "table.id"),
                            array("name" => "Accept", "act" => "accept", "cid" => "table.id"),
                        ), false, false
                );
            }
        }

        //getting current spars
        $current_spars = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users`.`status`, `users`.`battle_id`,
                                                                  `users`.`cfh`, `users`.`village`, `users_statistics`.`rank` 
                                                           FROM `users` INNER JOIN `users_statistics` ON (`users`.`id` = `users_statistics`.`uid`) 
                                                           WHERE `status` != 'exiting_combat' AND 
                                                                 SUBSTRING(`battle_id`, length(`battle_id`)-1, 2 ) = '03' AND 
                                                                 `location` = '".str_replace("'","\'",$GLOBALS['userdata'][0]['location'])."'");

        if(is_array($current_spars))
        {
            require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');
            $BattleInit = new battleInitiation();


            foreach($current_spars as $sparing_user_key => $sparing_user_data)
            {
                if( count(explode('|',$sparing_user_data['cfh'])) == 3 )
                {
                    $aid = $BattleInit->aid_conditions(array('dr'=>$GLOBALS['userdata'][0]['dr'],'sr'=>$GLOBALS['userdata'][0]['sr'],'cur_health'=>$GLOBALS['userdata'][0]['cur_health']),array('cfh'=>$sparing_user_data['cfh']),'spar');

                    if( $aid === true )
                    {
                        $code = md5($GLOBALS['user']->load_time . "-" . $sparing_user_data['id'] . "-" .str_replace("'","\'",$GLOBALS['userdata'][0]['location']));
                        $current_spars[$sparing_user_key]['cfh'] = '<a href="?id=' . $_GET['id'] . '&joining='.$sparing_user_data['id'].'&code='.$code.'">Respond</a>';
                    }
                    else if(is_array($aid))
                        $current_spars[$sparing_user_key]['cfh'] = 'CFH: '.base_convert(floor(sqrt($aid[0]+4+24789)), 10, 9).' to '.base_convert(floor(sqrt($aid[1]+4+24789)), 10, 9);
                    else
                        $current_spars[$sparing_user_key]['cfh'] = 'Cannot Respond';
                }

                else if($sparing_user_data['cfh'] == 'called')
                    $current_spars[$sparing_user_key]['cfh'] = 'Taken';

                else
                    $current_spars[$sparing_user_key]['cfh'] = 'None';
            }
        }

        //if there are spars to show.
        if(is_array($current_spars))
        {
            tableParser::show_list(
            'spars',
            'Sparing Users',
            $current_spars,
            array(
                'username' => "Name",
                'rank' => "Rank",
                'village' => "Village",
                'status' => "Status",
                'cfh' => "Call For Help"
            ),
            false,
            false,
            false
        );
        }


        $GLOBALS['template']->assign('pageID', $_GET['id']);

        // Load the main spar template
        $GLOBALS['template']->assign('contentLoad', './templates/content/spar/main.tpl');
    }

    private function respond()
    {
        require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');
        $BattleInit = new battleInitiation();
        
        $call = $GLOBALS['database']->fetch_data('SELECT `users`.`cfh` FROM `users` WHERE `id` = '.$_GET['joining']);

        if($BattleInit->aid_conditions(array('dr'=>$GLOBALS['userdata'][0]['dr'],'sr'=>$GLOBALS['userdata'][0]['sr'],'cur_health'=>$GLOBALS['userdata'][0]['cur_health']),array('cfh'=>$call[0]['cfh']),'spar') === true)
        {
            $BattleInit->setTarget($_GET['joining']);
            $BattleInit->aid_fight();
        }
    }

    private function do_challenge() {
        if (isset($_POST['username']) || (isset($_GET['cid']) && ctype_digit($_GET['cid']))) {
            // Get the one being challenged
            $target = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`,
                `users`.`status`, `users`.`location`, `users`.`village`,
                `users_statistics`.`rank_id`,
                `users_timer`.`last_activity`
                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                WHERE '.(isset($_GET['cid']) ? ('`users`.`id` = '.$_GET['cid'])
                    : ('`users`.`username` = "'.$_POST['username'].'"')).' LIMIT 1');

            // Check if found & more
            if ($target != '0 rows') {
                if ($target[0]['last_activity'] > ($GLOBALS['user']->load_time - 120)) {
                    if ($target[0]['status'] == 'awake' || ($target[0]['status'] == 'asleep' && $target[0]['village'] == $GLOBALS['userdata'][0]['village'])) {
                        $challenges = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `challenges`
                            FROM `spar_challenges`
                            WHERE (`uid` = '" . $_SESSION['uid'] . "' OR `oid` = '" . $_SESSION['uid'] . "')
                                OR (`uid` = '" . $target[0]['id'] . "' OR `oid` = '" . $target[0]['id'] . "')");
                        if ($challenges[0]['challenges'] == 0) {
                            if ($target[0]['id'] != $_SESSION['uid']) {
                                $GLOBALS['database']->execute_query("INSERT INTO `spar_challenges` ( `id` , `uid` , `oid` , `time` )VALUES (NULL , '" . $_SESSION['uid'] . "', '" . $target[0]['id'] . "', '" . $GLOBALS['user']->load_time . "');");
                                $GLOBALS['page']->Message("You have challenged " . $target[0]['username'] . " to a spar " , 'Spar Challenges', 'id='. $_GET['id']);
                            } else {
                                $GLOBALS['page']->Message("You cannot challenge yourself!" , 'Spar Challenges', 'id='. $_GET['id']);
                            }
                        } else {
                            $GLOBALS['page']->Message("Either you or your target has already been challenged." , 'Spar Challenges', 'id='. $_GET['id']);
                        }
                    } else {
                        $GLOBALS['page']->Message("This user can currently not be challenged because of his/her status, please try again later." , 'Spar Challenges', 'id='. $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("The target is not online" , 'Spar Challenges', 'id='. $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist" , 'Spar Challenges', 'id='. $_GET['id']);
            }
        }
    }

    private function delete_challenge() {
        $GLOBALS['database']->execute_query('DELETE FROM `spar_challenges`
            WHERE `spar_challenges`.`uid` = '.$_SESSION['uid'].'
                OR `spar_challenges`.`oid` = '.$_SESSION['uid'].' LIMIT 1');
        $GLOBALS['page']->Message("The challenge has been deleted." , 'Spar Challenges', 'id='. $_GET['id']);
    }

    private function accept_challenge() {

        // Try everything
        try {

            // Make this thing transaction Safe
            $GLOBALS['database']->transaction_start();

            $spar_data = $GLOBALS['database']->fetch_data('SELECT `spar_challenges`.*, `users`.`username`
                FROM `spar_challenges`
                    INNER JOIN `users` ON (`users`.`id` = IF(`spar_challenges`.`uid` = '.$_SESSION['uid'].', `spar_challenges`.`oid`, `spar_challenges`.`uid`))
                WHERE `spar_challenges`.`uid` = '.$_SESSION['uid'].' OR `spar_challenges`.`oid` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE');

            if ($spar_data !== '0 rows') {
                if (
                    $spar_data[0]['uid'] !== $_SESSION['uid'] &&
                    !empty($spar_data[0]['uid'])
                ) {
                    $opp_data = $GLOBALS['database']->fetch_data('SELECT `users`.`location`, `users`.`status`
                        FROM `users`
                        WHERE `users`.`id` = '.$spar_data[0]['uid'].' LIMIT 1');
                    if ($opp_data[0]['location'] == $GLOBALS['userdata'][0]['location']) {
                        if (($opp_data[0]['status'] == 'awake' || $opp_data[0]['status'] == 'asleep') && ($GLOBALS['userdata'][0]['status'] == 'awake' || $GLOBALS['userdata'][0]['status'] == 'asleep')) {
                             
                            $users = array(
                                            array('id'=>$spar_data[0]['uid'], 'team_or_extra_data'=> array('team'=>'challenger', 'starting_status'=>$opp_data[0]['status']) ),
                                            array('id'=>$GLOBALS['userdata'][0]['id'], 'team_or_extra_data'=> array('team'=>'challenged', 'starting_status'=>$GLOBALS['userdata'][0]['status']))
                                          );

                            BattleStarter::startBattle( $users, false, BattleStarter::spar, false, false, true );

                            // Delete the challenge
                            $GLOBALS['database']->execute_query('DELETE FROM `spar_challenges`
                                WHERE `spar_challenges`.`uid` = '.$_SESSION['uid'].'
                                    OR `spar_challenges`.`oid` = '.$_SESSION['uid'].' LIMIT 1');

                            // Message
                            $GLOBALS['page']->Message("You have accepted the challenge" , 'Spar Challenges', 'id=113', "Go to battle");

                        } else {
                            $GLOBALS['page']->Message("Your opponent is currently not capable of sparring (hospitalised, sleeping, etc)" , 'Spar Challenges', 'id='. $_GET['id']);
                        }
                    } else {
                        $GLOBALS['page']->Message("You must be at the same location as your opponent to accept a spar" , 'Spar Challenges', 'id='. $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("You cannot accept a challenge you have made." , 'Spar Challenges', 'id='. $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("You have not been challenged." , 'Spar Challenges', 'id='. $_GET['id']);
            }

            // At this point commit the transaction
            $GLOBALS['database']->transaction_commit();
        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback("Call For Help Error, transaction rollback");
            $GLOBALS['page']->Message("There was an error dealing with your request: ". $e->getMessage(), 'Combat System', 'id='.$_GET['id'] );
        }
    }

    private function show_people() {

        // Information for the user
        $guide = ((int)$GLOBALS['userdata'][0]['rank_id'] === 1) ?
            "As an academy student you are not yet ranked high enough for real combats. ".
            "You can however choose to challenge another player to a friendly sparring match. ".
            "The following people were found in the immediate area and are free to be challenged."

            : "The following people were found in the immediate area and are free to be challenged:";

        // Use the table parser library to show notes in system
        $min = tableParser::get_page_min();

        $topOptions = array();

        foreach ($this->__filters as $key => $value) {
            $topOptions[] = array(
                'name' => $value['name'],
                'href' => '?id=' . $_GET['id'] . '&filter=' . $key,
            );
        }

        // Decide on which filter to use
        $filter = 'all';
        if( isset($_GET['filter']) && preg_match( '/(' . implode('|', array_keys($this->__filters)) . ')/i', $_GET['filter'] ) ){
            $filter = $_GET['filter'];
        }

        $additionalConditions = '';

        if ($this->__filters[$filter]['rank_id'] !== false) {
            $additionalConditions = 'AND `users_statistics`.`rank_id` = ' . $this->__filters[$filter]['rank_id'];
        }

        if($GLOBALS['userdata'][0]['status'] != 'asleep')

            $users = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`, `users`.`status`,
                `users_statistics`.`rank`, `users_statistics`.`rank_id`, `users_statistics`.`reinforcements`,
                `users_loyalty`.`village`,
                `users_timer`.`last_activity`
                FROM `users_timer`
                    INNER JOIN `users` ON (`users`.`id` != '.$_SESSION['uid'].' AND `users`.`id` = `users_timer`.`userid`
                        AND `users`.`location` = \''.str_replace("'","\'",$GLOBALS['userdata'][0]['location']).'\' AND `users`.`status` = "awake" )
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                WHERE  `users_timer`.`last_activity` >= '.($GLOBALS['user']->load_time - 120).'
                '. $additionalConditions . '
                    ORDER BY `users_statistics`.`rank_id` DESC, `users_statistics`.`experience` DESC LIMIT '.$min.', 10');

        else
            
            $users = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`, `users`.`status`,
                `users_statistics`.`rank`, `users_statistics`.`rank_id`, `users_statistics`.`reinforcements`,
                `users_loyalty`.`village`,
                `users_timer`.`last_activity`
                FROM `users_timer`
                    INNER JOIN `users` ON (`users`.`id` != '.$_SESSION['uid'].' AND `users`.`id` = `users_timer`.`userid`
                        AND `users`.`location` = \''.str_replace("'","\'",$GLOBALS['userdata'][0]['location']).'\' AND (`users`.`status` = "awake" OR `users`.`status` = "asleep") AND `users`.`village` = "'.$GLOBALS['userdata'][0]['village'].'" )
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                WHERE  `users_timer`.`last_activity` >= '.($GLOBALS['user']->load_time - 120).'
                '. $additionalConditions . '
                    ORDER BY `users_statistics`.`rank_id` DESC, `users_statistics`.`experience` DESC LIMIT '.$min.', 10');


        // Show the table
        tableParser::show_list(
            'people',
            'Nearby Ninjas: ' . $this->__filters[$filter]['name'],
            $users,
            array(
                'username' => "Name",
                'rank' => "Rank",
                'village' => "Village",
                'last_activity' => "Last Activity",
                'status' => "Status"
            ),
            array(
                array("name" => "Challenge", "act" => "challenge", "cid" => "table.id")
            ),
            false,
            true,
            $topOptions
        );
    }
}
new spar();