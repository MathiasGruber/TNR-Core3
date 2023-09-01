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
require_once(Data::$absSvrPath.'/global_libs/Tags/Tag.php');

class arena extends functions {

    function __construct() {

        // Check if this user is in a squad
        try{

            // Check session
            functions::checkActiveSession();

            //create lock on self
            $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);

            // Get the user Data
            $this->getUsrData();

            // Get all battles having the users ID registered with them
            $this->battle = $this->checkIfInBattle(
                $GLOBALS['userdata'][0]['id'],
                $GLOBALS['userdata'][0]['battle_id']
            );

            // Check if user is already present in some battle
            if ($this->battle !== '0 rows' && $GLOBALS['userdata'][0]['status'] == "awake") {

                // User was found to be in battle
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'combat', `battle_id` = '".$this->battle[0]['id'].", `database_fallback` = 0' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                $GLOBALS['userdata'][0]['database_fallback'] = 0;
                $GLOBALS['page']->Message("The system has found your character to be engaged in battle.", 'Combat', 'id=113');

            } else {

                // Decide what screen to show
                if (!isset($_GET['act'])) {
                    $this->main_screen();
                } elseif ($_GET['act'] == $this->code) {
                    $this->do_fight("Ordinary");
                } elseif ($_GET['act'] == $this->code2) {
                    $this->do_leave();
                } elseif ($_GET['act'] == $this->code3) {
                    $this->do_fight("Torn");
                } elseif ($_GET['act'] == $this->code4) {
                    $this->do_fight("Mirror");
                }
            }

        } catch (Exception $e) {
            //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Arena', 'id='.$_GET['id']);
        }

        //release lock on self
        $GLOBALS['database']->release_lock('battle',$_SESSION['uid']);
    }

    // Set secret codes
    private function getUsrData() {
        $this->code = md5("" . $GLOBALS['userdata'][0]['experience'] . "do-fight" . $_SESSION['uid'] . ""); //enter
        $this->code2 = md5("" . $GLOBALS['userdata'][0]['experience'] . "do-flee" . $_SESSION['uid'] . ""); //leave
        $this->code3 = md5("" . $GLOBALS['userdata'][0]['experience'] . "do-super" . $_SESSION['uid'] . ""); // torn Battle ring
        $this->code4 = md5("" . $GLOBALS['userdata'][0]['experience'] . "do-mirror" . $_SESSION['uid'] . ""); // mirror
    }

    function main_screen() {

        // Currently we just have 5 "seemingly" random pictures with "accept/flee" being loaded.
        // This should be made more secure in the future.
        switch (random_int(1, 5)) {
            case 1: $file1 = "7fj4k9sw"; break;
            case 2: $file1 = "hd83j6ht"; break;
            case 3: $file1 = "jguhyewl"; break;
            case 4: $file1 = "kiuhbs73"; break;
            case 5: $file1 = "lokjrewh"; break;
        }
        switch (random_int(1, 5)) {
            case 1: $file2 = "isuhjel"; break;
            case 2: $file2 = "jghydsu"; break;
            case 3: $file2 = "kishyewj"; break;
            case 4: $file2 = "kisuehq1"; break;
            case 5: $file2 = "lokw72hu"; break;
        }

        // Create the two links
        $option1 = '<a href="?id=' . $_GET['id'] . '&act=' . $this->code . '"><img src=./images/antibot/' . $file1 . '.gif></a>'; // Standard
        $option2 = '<a href="?id=' . $_GET['id'] . '&act=' . $this->code2 . '"><img src=./images/antibot/' . $file2 . '.gif></a>'; // Leave
        $option3 = '<a href="?id=' . $_GET['id'] . '&act=' . $this->code3 . '"><img src=./images/antibot/' . $file1 . '.gif></a>'; // Torn
        $option4 = '<a href="?id=' . $_GET['id'] . '&act=' . $this->code4 . '"><img src=./images/antibot/' . $file1 . '.gif></a>'; // Mirror

        // Send codes to smarty
        $GLOBALS['template']->assign('code1', $this->code);
        $GLOBALS['template']->assign('code3', $this->code3);
        $GLOBALS['template']->assign('code4', $this->code4);

        // Randomly order the two links
        if (random_int(1, 2) == 1) {
            $optionsStandard = "" . $option1 . " <img src=./images/antibot/or.gif> " . $option2 . "";
            $optionsTorn = "" . $option3 . " <img src=./images/antibot/or.gif> " . $option2 . "";
            $optionsMirror = "" . $option4 . " <img src=./images/antibot/or.gif> " . $option2 . "";
        } else {
            $optionsStandard = "" . $option2 . " <img src=./images/antibot/or.gif> " . $option1 . "";
            $optionsTorn = "" . $option2 . " <img src=./images/antibot/or.gif> " . $option3 . "";
            $optionsMirror = "" . $option2 . " <img src=./images/antibot/or.gif> " . $option4 . "";
        }

        // Get village information (arena counts are used)
        $this->village = cachefunctions::getVillage($GLOBALS['userdata'][0]['village']);

        // Assign smarty information
        $GLOBALS['template']->assign('village', $this->village);
        $GLOBALS['template']->assign('optionsStandard', $optionsStandard);
        $GLOBALS['template']->assign('optionsTorn', $optionsTorn);
        $GLOBALS['template']->assign('optionsMirror', $optionsMirror);
        $GLOBALS['template']->assign('contentLoad', './templates/content/arena/arena_main.tpl');
    }

    // Do start fight
    function do_fight($type) {

        // Save the time locally
        $time = $GLOBALS['user']->load_time;

        // Limit this page for macros
        $GLOBALS['page']->updateCaptchaPageCounter( 5, 60, 60 );

        // Check what kind of arena we're going for
        switch( $type ){
            case "Ordinary":

                // Number of opponents
                $rand = random_int(1,$GLOBALS['userdata'][0]['rank_id']);

                // Get $rand opponents. Two queries, since the same AI should be allowed
                $opponent = array();
                for( $i=0; $i < $rand; $i++ ){
                    $ai = $GLOBALS['database']->fetch_data("SELECT `id`, `name`
                          FROM `ai`
                          WHERE
                            (`type` = 'arena' OR `type` = 'random') AND
                            (`level` > '" . ($GLOBALS['userdata'][0]['level_id'] - 3) . "' AND `level` <  '" . ($GLOBALS['userdata'][0]['level_id'] + 3) . "') AND
                            `location` != 'uncharted'
                          ORDER BY RAND() LIMIT 1");

                    if( $ai !== "0 rows" ){
                        $opponent[] = $ai[0];
                    }
                }
                if( empty($opponent) ){
                    $opponent = "0 rows";
                }

                $timeCheck = $time >= $GLOBALS['userdata'][0]['arena_cooldown'] ? "pass" : 30;
                $tags = false;
            break;
            case "Torn":
                $query = "SELECT `id`, `name` FROM `ai` WHERE `type` = 'torn_battle' ORDER BY RAND() LIMIT 1";
                $opponent = $GLOBALS['database']->fetch_data($query);
                $timeCheck = $time >= $GLOBALS['userdata'][0]['arena_cooldown'] ? "pass" : 1800;
                $copyStatsTag = 'copyStats:(value>'.(random_int(25,125)/100).';)';
                // Set the opponent trait
                //$opponent[0]['trait'] = "SCOPY:copy:".random_int(25,125).":1:1:1";

            break;
            case "Mirror":
                $query = "SELECT `id`, `name` FROM `ai` WHERE `type` = 'mirror_battle' ORDER BY RAND() LIMIT 1";
                $opponent = $GLOBALS['database']->fetch_data($query);
                $timeCheck = $time >= $GLOBALS['userdata'][0]['arena_cooldown'] ? "pass" : 3600; //21600
                $copyStatsTag  = 'copyStats:(value>'.(random_int(25,175)/100).';)~';
                $copyOriginTag = 'copyOrigin:(targetOrigin>bloodline;targetAge>0;override>false;priority>999;)~';

                $tags_self  = 'immunity:(targetType>all;targetOrigin>all;targetElement>all;targetImmunity>(recoil,reflect,absorb,leach);priority>9999;)~'.
                               'noOneHitKill:(priority>9999;)~';
                // Set the opponent trait & status effects
                //$opponent[0]['trait'] = "SCOPY:copy:".random_int(25,175).":1:1:1;INVINCIBLE:0:1:1:1:1:1:1;BCOPY";

            break;
        }

        // Check if enough time has pased
        if( $timeCheck == "pass" ){

            //	Create battle and echo screen:
            if ($opponent !== '0 rows') {

                //getting user ready for start
                $users = array( array( 'id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village'] ) );

                //getting ai ready for start
                $ais = array();
                $oppNames = array();
                foreach($opponent as $ai)
                {
                    $ais[] = array('id'=>$ai['id'], 'team'=>'Arena');
                    $oppNames[] = $ai['name'];
                }

                if($type == 'Ordinary')
                {
                    /*$battle = */ BattleStarter::startBattle( $users, $ais, BattleStarter::arena, false, false, true);
                    //$battle->updateCache();
                }
                else if($type == 'Torn')
                {
                    $battle = BattleStarter::startBattle( $users, $ais, BattleStarter::torn, false, false, true);

                    foreach($oppNames as $owner)
                    {
                        $copyStatsTag = $battle->parseTags($copyStatsTag);
                        $copyStatsTag[0]->owner = $owner;
                        $copyStatsTag[0]->target = $GLOBALS['userdata'][0]['username'];
                        $battle->copyStats($copyStatsTag[0]);
                        $battle->users[$owner]['health'] = $battle->users[$owner]['healthMax'];

                        if(isset($battle->users[$owner]['chakraMax']) && $battle->users[$owner]['staminaMax'])
                        {
                            $battle->users[$owner]['chakra'] = $battle->users[$owner]['chakraMax'];
                            $battle->users[$owner]['stamina'] = $battle->users[$owner]['staminaMax'];
                        }

                        $battle->updateDR_SR($owner);
                        $battle->users[$owner]['DSR'] = $battle->findDSR($owner);
                    }

                    $battle->updateCache();
                }
                else if($type == 'Mirror')
                {
                    $battle = BattleStarter::startBattle( $users, $ais, BattleStarter::mirror, false, false, true);

                    $copyStatsTag = $battle->parseTags($copyStatsTag);
                    $copyStatsTag[0]->owner = $oppNames[0];
                    $copyStatsTag[0]->target = $GLOBALS['userdata'][0]['username'];
                    $battle->copyStats($copyStatsTag[0]);
                    $battle->users[$oppNames[0]]['health'] = $battle->users[$oppNames[0]]['healthMax'];
                    if(isset($battle->users[$oppNames[0]]['chakraMax']) && isset($battle->users[$oppNames[0]]['staminaMax']))
                    {
                    $battle->users[$oppNames[0]]['chakra'] = $battle->users[$oppNames[0]]['chakraMax'];
                    $battle->users[$oppNames[0]]['stamina'] = $battle->users[$oppNames[0]]['staminaMax'];
                    }

                    $battle->addTags( $battle->parseTags($copyOriginTag), $GLOBALS['userdata'][0]['username'], $oppNames[0], 'L');

                    $battle->addTags( $battle->parseTags($tags_self), $oppNames[0], $oppNames[0], 'L' );
                    $battle->updateDR_SR($oppNames[0]);
                    $battle->users[$oppNames[0]]['DSR'] = $battle->findDSR($oppNames[0]);

                    $battle->updateCache();
                }
                else
                    throw new Exception('Battle type "'.$type.'" does not exist.');

                // Update village arena counts
                $GLOBALS['database']->execute_query("UPDATE `villages` SET `arena_fights` = `arena_fights` + 1 WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");

                //  Send opponent name to smarty
                $GLOBALS['template']->assign('oppName', implode(", ",$oppNames) );

                $GLOBALS['template']->assign('contentLoad', './templates/content/arena/arena_fight.tpl');
            }
            else{
                $GLOBALS['page']->Message("There are currently no opponents for you to fight" , 'Arena', 'id=35');
            }
        }
        else{
            $GLOBALS['page']->Message("You have not recovered enough for your next battle.<br> Please wait ".functions::convert_time(($GLOBALS['userdata'][0]['arena_cooldown'] - $time), "arenaTimer"), 'Arena', 'id=35');
        }

    }

    // Flee the arena. Don't allow user to battle soon again
    function do_leave() {
        $query = "UPDATE `users_timer` SET `arena_cooldown` = '" . ($GLOBALS['user']->load_time + 285) . "' WHERE `userid` = '" . $_SESSION['uid'] . "' and `arena_cooldown` < ".($GLOBALS['user']->load_time + 285)." LIMIT 1";
        $GLOBALS['database']->execute_query($query);
        $GLOBALS['page']->Message("Right before entering the arena, you change your mind and flee. The guards do not condone of such behavior and chase you out, but you manage to lose them. It will take you 5 minutes to recover from the shame and be able to enter the arena again.", 'Arena', 'id='.$_GET['id']);
    }

}

new arena();