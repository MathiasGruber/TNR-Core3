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

require_once(Data::$absSvrPath.'/libs/profileFunctions/profileLib.inc.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

class profile extends profileFunctions {

    // Decide on what to do.
    public function __construct() {

        try {

            // Obtain lock
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get elemental library
            require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

            // Decide on what page to show
            if (!isset($_GET['act'])) {
                $this->setCharData( "main" , $_SESSION['uid']);
                $this->main_profile();
            }
            elseif ($_GET['act'] === 'claim_level') {
                $this->setCharData("level" , $_SESSION['uid']);
                $this->claim_level();
            }
            elseif ($_GET['act'] === 'do_exam') {
                $this->setCharData("exam" , $_SESSION['uid']);
                $this->check_exam();
            }

            // Release the lock
            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), 'Profile System', 'id=2');
        }
    }

    //      EVERYTHING REGARDING REGENERATION      //

    // Main Profile
    public function main_profile() {

        // Add data from global array to char_data
        $this->char_data[0] = array_merge($GLOBALS['userdata'][0], $this->char_data[0]);

        // Set ANBU
        $this->setAnbuClanStatus();

        // Set sensei
        $this->setSensei();

        // Set regeneration
        $this->set_regen();

        // Check levelup:
        $this->char_data[0]['required_exp'] = 0;
        if($this->char_data[0]['level_id'] < Data::${'max_level_id'} && $this->checkLevelUp()) {
            if(isset($this->char_data[0]['show_level_up_button']) && $this->char_data[0]['show_level_up_button']) {
                $messageArray = array();
                if ($this->char_data[0]['status'] === 'awake') {
                    if($this->char_data[0]['next_user_level'] !== null ){
                        if ($this->char_data[0]['rank_id'] !== $this->char_data[0]['newRankid']) {
                            $newRank = strtolower($this->char_data[0]['next_user_level']);
                            if($this->char_data[0]['village'] === "Syndicate") {
                                $newRank = functions::getRank($this->char_data[0]['newRankid'], $this->char_data[0]['village']);
                            }
                            $messageArray = array( "info" => 'Click here to take the ' . $newRank . ' test',
                                "href" => '?id=' . $_GET['id'] . '&amp;act=do_exam');
                        }
                        else {
                            $messageArray = array( "info" => 'You have gained a level. Click here to claim it!',
                                "href" => '?id=' . $_GET['id'] . '&amp;act=claim_level');
                        }
                    }
                }
                else { $messageArray = array( "info" => 'You can now level up. You must be awake and healed first!'); }
                $GLOBALS['template']->assign('levelInfo', $messageArray);
            }
        }

        // Check Avatar
        $this->char_data[0]['avatar'] = functions::getAvatar($this->char_data[0]['id']);

        //    Set Percentages
        $this->char_data[0]['lifePerc'] = floor(($this->char_data[0]['cur_health'] / $this->char_data[0]['max_health']) * 100);
        $this->char_data[0]['chaPerc'] = floor(($this->char_data[0]['cur_cha'] / $this->char_data[0]['max_cha']) * 100);
        $this->char_data[0]['staPerc'] = floor(($this->char_data[0]['cur_sta'] / $this->char_data[0]['max_sta']) * 100);

        //getting dsr
        if($this->char_data[0]['sr'] != 0)
            $this->char_data[0]['DSR'] = base_convert(floor(sqrt(($this->char_data[0]['dr'] * ( ( $this->char_data[0]['cur_health'] * 0.5 + $this->char_data[0]['max_health'] * 0.5 ) / $this->char_data[0]['sr'] ))+4+24789)), 10, 9);
        else
            $this->char_data[0]['DSR'] = 0;

        // Set token for statistics
        $this->char_data[0]['token'] = md5("Hsu7453Ks" . $this->char_data[0]['id']);

        // Game time
        $this->char_data[0]['gameTime'] = date('jS \of F Y, h:i A', $GLOBALS['user']->load_time);

        // Set elemental affinities
        $this->setElementalAffinities();

        $temp = explode(':',$this->char_data[0]['bloodline']);
        if(isset($temp[1]))
        {
            $this->char_data[0]['bloodline'] = $temp[0];
            $this->char_data[0]['bloodline_offense'] = $temp[1];
        }

        // Send to smarty
        $GLOBALS['template']->assign('charInfo', $this->char_data[0]);

        // Force statistics on API call
        if( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true ){
            $_GET['load'] = "statistics";
        }

        // Player statistics, if requested.
        if (isset($_GET['load'])) {
            if($_GET['load'] === "statistics") {
                require_once(Data::$absSvrPath."/ajaxLibs/backendLibs/profileBackend.php");
                new profileBackend($_GET['load']);
            }
        }

        // Set tutorial teacher for API calls
        if( functions::isAPIcall() ){
            $currentOrder = cachefunctions::getOrder($this->char_data[0]['level_id']);
            if( $currentOrder !== "0 rows" ){
                $GLOBALS['template']->assign('teacherURL', '?id=120&amp;act=details&amp;eid='.$currentOrder[0]['id']."&amp;returnID=2");
            }
        }

        //set charecter join date.
        if($GLOBALS['userdata'][0]['username'] == $this->char_data[0]['username'])
            $GLOBALS['template']->assign('join_date', date("d F Y", $GLOBALS['userdata'][0]['join_date']));


        // Load the template
        $GLOBALS['template']->assign('contentLoad', './templates/content/profile/mainProfile.tpl');

    }

    // A function for checking whether or not the user has completed the neccesary order
    private function checkOrderCompletion() {

        //get the users order and its status/description
        $query = "SELECT `users_quests`.*, `quests`.`description` FROM `users_quests` INNER JOIN `quests` on (`users_quests`.`qid` = `quests`.`qid`) WHERE `category` = 'order' AND `level` = {$this->char_data[0]['level_id']} AND `uid` = {$_SESSION['uid']}";
        try { if(!$nextOrder = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
        catch (Exception $e)
        {
            throw new exception('unable to collect order.');
        }
        
        //if the user does not have an order
        if(!is_array($nextOrder))
        {

            //find an order
            $order_query = "SELECT * FROM `quests` WHERE `category` = 'order' AND `level` = ".$this->char_data[0]['level_id'];
            try { if(!$nextOrder = $GLOBALS['database']->fetch_data($order_query)) throw new Exception('cant pull battle data from database'); }
            catch (Exception $e)
            {
                throw new exception('unable to collect order.');
            }

            if(!is_array($nextOrder))
            {
                throw new exception('There is no order for your level.');
            }

            //give the user that order
            if(!isset($GLOBALS['QuestsControl']))
                $GLOBALS['QuestsControl'] = new QuestsControl();

            $GLOBALS['QuestsControl']->learnQuest($nextOrder[0]['qid'], 0);
            $GLOBALS['QuestsControl']->startQuest($nextOrder[0]['qid']);

            //get the users order and its status/description 
            try { if(!$nextOrder = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
            catch (Exception $e)
            {
                throw new exception('unable to collect order.');
            }

            if(!is_array($nextOrder))
            {
                throw new exception('There is no order for your level.');
            }
        }
        
        if($nextOrder[0]['status'] == 3) //if the order is complete return true
        {
            return true;
        }
        else //if the order is not complete return false and display a message.
        {
            // Show logbook message on web, on mobile it's not properly formatted
            $logMessage = functions::isAPIcall() ? "" : "<blockquote><span><b>Your Current Order</b><br>" . nl2br($nextOrder[0]['description']) . "</span></blockquote>";

            $GLOBALS['page']->Message(
                "The order for your current level has not yet been signed for in your logbook. This either means you have not yet completed it, or simply that you must go to your logbook first to get the order signed by the officials. ".$logMessage,
                    'Level up Error',
                    'id=120',
                    "Go to QuestJournal");

            return false;
        }
    }


    //      EVERYTHING REGARDING LEVELUP      //
    private function checkLevelUp(){
        $this->char_data[0]['required_exp'] = max(0, ($this->char_data[0]['experience_required'] - $this->char_data[0]['experience']));
        return ((int)$this->char_data[0]['required_exp'] === 0) ? true : false;
    }

    private function claim_level() {

        // Check if awake
        if ($this->char_data[0]['status'] !== 'awake') {
            $GLOBALS['page']->Message("You must be awake / out of battle to claim your level", 'Level up: Error', 'id=' . $_GET['id']);
            return;
        }

        // Check level sanity
        if($this->char_data[0]['level_id'] >= Data::${'max_level_id'} || !$this->checkLevelUp()) {
            $GLOBALS['page']->Message("You do not have enough experience to claim your next level", 'Level up: Error', 'id=' . $_GET['id']);
            return;
        }

        // Get the next level
        $newLevel = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levels`.`levelID` = ".($this->char_data[0]['level_id'] + 1)." LIMIT 1");

        if ($newLevel === '0 rows' || $newLevel[0]['rank'] !== $this->char_data[0]['levelRank']) {
            $GLOBALS['page']->Message("Your next level is a rank up, please follow the link in your profile", 'Level up: Error', 'id=' . $_GET['id']);
            return;
        }

        // Check order
        if(!($this->checkOrderCompletion())) { return; }

        // New Data
        $newHealth = $this->char_data[0]['max_health'] + $newLevel[0]['health_gain'];
        $newChakra = $this->char_data[0]['max_cha'] + $newLevel[0]['chakra_gain'];
        $newStamina = $this->char_data[0]['max_sta'] + $newLevel[0]['stamina_gain'];

        $newHealth = ($newHealth > Data::${'MAX_HP_' . $this->char_data[0]['rank_id']} ) ?
            Data::${'MAX_HP_' . $this->char_data[0]['rank_id']} : $newHealth;
        $newChakra = ($newChakra > Data::${'MAX_' . $this->char_data[0]['rank_id']} ) ?
            Data::${'MAX_' . $this->char_data[0]['rank_id']} : $newChakra;
        $newStamina = ($newStamina > Data::${'MAX_' . $this->char_data[0]['rank_id']} ) ?
            Data::${'MAX_' . $this->char_data[0]['rank_id']} : $newStamina;

        // Set the rank properly
        $newLevel[0]['rank'] = functions::getRank($this->char_data[0]['rank_id'], $GLOBALS['userdata'][0]['village']);

        $GLOBALS['Events']->acceptEvent('level_id',         array('new'=>$newLevel[0]['levelID'], 'old'=>$GLOBALS['userdata'][0]['level_id'] ));
        $GLOBALS['Events']->acceptEvent('level',            array('new'=>$GLOBALS['userdata'][0]['level']+1, 'old'=>$GLOBALS['userdata'][0]['level'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_health', array('new'=>$newHealth, 'old'=>$GLOBALS['userdata'][0]['max_health'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_sta',    array('new'=>$newChakra, 'old'=>$GLOBALS['userdata'][0]['max_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha',    array('new'=>$newStamina, 'old'=>$GLOBALS['userdata'][0]['max_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>$newHealth, 'old'=>$GLOBALS['userdata'][0]['cur_health'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta',     array('new'=>$newChakra, 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_cha',     array('new'=>$newStamina, 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));

        // Update user
        $GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `users_statistics`.`level_id` = ".$newLevel[0]['levelID'].",
                `users_statistics`.`level` = `users_statistics`.`level` + 1,
                `users_statistics`.`max_health` = ".$newHealth.",
                `users_statistics`.`max_cha` = ".$newChakra.",
                `users_statistics`.`max_sta` = ".$newStamina.",
                `users_statistics`.`cur_sta` = `users_statistics`.`max_sta`,
                `users_statistics`.`cur_cha` = `users_statistics`.`max_cha`,
                `users_statistics`.`cur_health` = `users_statistics`.`max_health`
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']." LIMIT 1");
            
        // Next order
        $GLOBALS['userdata'][0]['level_id']++;
        $GLOBALS['userdata'][0]['level']++;
        $query = "SELECT `description` FROM `quests` WHERE `category` = 'order' AND `level` = ".$GLOBALS['userdata'][0]['level_id'];
        try { if(!$nextOrder = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
        catch (Exception $e)
        {
            throw new exception('unable to collect order.');
        }
        if(!is_array($nextOrder))
        {
            throw new exception('There is no order for your level.');
        }
        

        // Load the template
        $GLOBALS['template']->assign('charInfo', $this->char_data[0]);
        $GLOBALS['template']->assign('newLevel', $newLevel[0]);
        $GLOBALS['template']->assign('nextOrderDescription', $nextOrder[0]['description']);
        $GLOBALS['template']->assign('contentLoad', './templates/content/profile/levelUp.tpl');
    }

    private function getNewLevel() {
        return $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levels`.`levelID` = ".($this->char_data[0]['level_id'] + 1)." LIMIT 1");
    }


    //      EVERYTHING REGARDING EXAMS      //
    private function check_exam(){
        if ($this->char_data[0]['status'] !== 'awake') {
            $GLOBALS['page']->Message("You must be awake to rank up", 'Rank up: Error', 'id=' . $_GET['id']);
            return;
        }

        $new_level_data = $this->getNewLevel();
        if($new_level_data[0]['rank_id'] === $this->char_data[0]['rank_id']) {
            $GLOBALS['page']->Message("You don't have the level to rank up yet", 'Rank up: Error', 'id=' . $_GET['id']);
            return;
        }

        if($this->char_data[0]['experience'] < $this->char_data[0]['experience_required']) {
            $GLOBALS['page']->Message("You don't have the experience to rank up yet", 'Rank up: Error', 'id=' . $_GET['id']);
            return;
        }

        if(!($this->checkOrderCompletion())) { return; }

        if (!isset($_POST['Submit'])) {
            $GLOBALS['page']->Confirm("You must confirm that you want to advance to the next rank.", "Confirm Ranking Up", "Rank up");
            return;
        }

        switch($this->char_data[0]['rank_id']) {
            case('1'): $this->genin_exam(); break;
            case('2'): $this->chuunin_exam(); break;
            case('3'): $this->jounin_exam(); break;
            case('4'): $this->elite_jounin_exam(); break;
            default: $GLOBALS['page']->Message("You cannot rank up at this point.", 'Rank up: Error', 'id=' . $_GET['id']); break;
        }
    }

    // Genin Exam
    private function genin_exam() {
        // Jutsu Test
        $jutsu_data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_jutsu`
            WHERE (`jid` = '1' OR `jid` = '2' OR `jid` = '3') AND `uid` = '" . $_SESSION['uid'] . "' ORDER BY `jid`");

        if ($jutsu_data === '0 rows') {
            $GLOBALS['page']->Message("You enter the classroom as the genin exam starts. ".
                "The exam consists of four tests which decide whether you graduate or not!".
                "<br><br><b><u>1st Test</u></b><br>You go to your sensei and you try ".
                "perform the Bunshin no Jutsu technique, but can\'t do it at all!<br>".
                "You <b>fail</b> the first test!!<br>", 'Genin Exam: Failed', 'id=' . $_GET['id']);
            return;
        }

        // The Tests
        $resultReturn = array();
        $resultReturn[] = $jutsu_data[0]['level'] >= 5 ? "yes" : "no";
        $resultReturn[] = $jutsu_data[1]['level'] >= 5 ? "yes" : "no";
        $resultReturn[] = $jutsu_data[2]['level'] >= 5 ? "yes" : "no";
        $resultReturn[] = $this->char_data[0]['intelligence'] >= 12 ? "yes" : "no";

        if ($resultReturn[0] === "yes") {
            if($resultReturn[1] === "yes") {
                if($resultReturn[2] === "yes") {
                    if($resultReturn[3] === "yes") {
                        $GLOBALS['template']->assign('ceremoni', $this->do_rankup());
                    }
                }
            }
        }

        $GLOBALS['template']->assign('resultReturn', $resultReturn);
        $GLOBALS['template']->assign('contentLoad', './templates/content/profile/geninExam.tpl');
    }

    // Chuunin Exam
    private function chuunin_exam() {
        $resultReturn = array();
        $resultReturn[] = ($this->char_data[0]['intelligence'] + $this->char_data[0]['strength'] +
            $this->char_data[0]['speed'] + $this->char_data[0]['willpower']) >= 500 ? "yes" : "no";
        $resultReturn[] = $this->char_data[0]['gen_def'] + $this->char_data[0]['nin_def'] +
            $this->char_data[0]['tai_def'] + $this->char_data[0]['weap_def'] >= 3000 ? "yes" : "no";
        $resultReturn[] = max(array($this->char_data[0]['gen_off'], $this->char_data[0]['nin_off'],
            $this->char_data[0]['tai_off'], $this->char_data[0]['weap_off'])) >= 3000 ? "yes" : "no";

        if ($resultReturn[0] === "yes") {
            if($resultReturn[1] === "yes") {
                if($resultReturn[2] === "yes") {
                    $GLOBALS['template']->assign('ceremoni', $this->do_rankup());
                }
            }
        }

        $GLOBALS['template']->assign('resultReturn', $resultReturn);
        $GLOBALS['template']->assign('contentLoad', './templates/content/profile/chuuninExam.tpl');
    }

    // Jounin Exam
    private function jounin_exam() {
        $newrank = $this->char_data[0]['rank'] === 'Lower Outlaw' ? "Higher Outlaw" : "Jounin";
        $GLOBALS['template']->assign('newRank', $newrank);

        $resultReturn = array();
        $resultReturn[] = min(array($this->char_data[0]['intelligence'], $this->char_data[0]['strength'],
            $this->char_data[0]['speed'], $this->char_data[0]['willpower']) ) >= 5200 ? "yes" : "no";

        $resultReturn[] = $this->char_data[0]['gen_def'] + $this->char_data[0]['nin_def'] +
            $this->char_data[0]['tai_def'] + $this->char_data[0]['weap_def'] >= 50000 ? "yes" : "no";

        $resultReturn[] = max(array($this->char_data[0]['gen_off'], $this->char_data[0]['nin_off'],
            $this->char_data[0]['tai_off'], $this->char_data[0]['weap_off']) ) >= 50000 ? "yes" : "no";

        if ($resultReturn[0] === "yes") {
            if($resultReturn[1] === "yes") {
                if($resultReturn[2] === "yes") {
                    $GLOBALS['template']->assign('ceremoni', $this->do_rankup());
                }
            }
        }

        $GLOBALS['template']->assign('resultReturn', $resultReturn);
        $GLOBALS['template']->assign('newRank', $newrank);
        $GLOBALS['template']->assign('contentLoad', './templates/content/profile/jouninExam.tpl');
    }

    // S-Jounin Exam
    private function elite_jounin_exam() {
        $newrank = $this->char_data[0]['rank'] == 'Higher Outlaw' ? "Elite Outlaw" : "Elite Jounin";

        $resultReturn[] = min(array($this->char_data[0]['intelligence'], $this->char_data[0]['strength'],
            $this->char_data[0]['speed'], $this->char_data[0]['willpower']) ) >= 18800 ? "yes" : "no";

        $resultReturn[] = $this->char_data[0]['gen_def'] + $this->char_data[0]['nin_def'] +
            $this->char_data[0]['tai_def'] + $this->char_data[0]['weap_def'] >= 150000 ? "yes" : "no";

        $resultReturn[] = max( array($this->char_data[0]['gen_off'], $this->char_data[0]['nin_off'],
            $this->char_data[0]['tai_off'], $this->char_data[0]['weap_off']) ) >= 150000 ? "yes" : "no";

        if ($resultReturn[0] === "yes") {
            if($resultReturn[1] === "yes") {
                if($resultReturn[2] === "yes") {
                    $GLOBALS['template']->assign('ceremoni', $this->do_rankup());
                }
            }
        }

        $GLOBALS['template']->assign('resultReturn', $resultReturn);
        $GLOBALS['template']->assign('newRank', $newrank);
        $GLOBALS['template']->assign('contentLoad', './templates/content/profile/specialJouninExam.tpl');
    }

    // Do the rankup
    private function do_rankup() {
        $new_level_data = $this->getNewLevel();

        if ($new_level_data[0]['rank'] === $this->char_data[0]['rank']) {
            if($this->char_data[0]['rank_id'] <= $new_level_data[0]['rank_id']) {
                $GLOBALS['page']->Message("You already ranked up.", 'Rank up: Error', 'id=' . $_GET['id']);
                return;
            }
        }

        // Get the regeneration increase
        $regen_increase = 0;
        if( isset(DATA::$RANK_REGEN_GAIN[ $new_level_data[0]['rank_id'] ]) ){
            $regen_increase = DATA::$RANK_REGEN_GAIN[ $new_level_data[0]['rank_id'] ];
        }
        else{
            $GLOBALS['page']->Message("Could not determine the correct regeneration gain.", 'Rank up: Error', 'id=' . $_GET['id']);
            return;
        }

        // Create some ceremonial texts
        $ceremony_text = $extraQuery = "";
        switch( $new_level_data[0]['rank'] ){
            case "Genin": {
                $ceremony_text =
                    'You have passed all the tests in the genin exam and are therefore now recognized as a genin of your village. You are awarded a headband proving that you are now a ninja of your village<br>You can now find a sensei who will teach you in the ways of the ninja. <br>
                    <br><u><b>As a genin:</b></u>
                    <br>You will be able to change your personal picture
                    <br>You will be able to go on missions<br><br>';
            } break;
            case "Chuunin": {
                // Reset student/sensei stuff
                if ($this->char_data[0]['sensei'] !== '' && $this->char_data[0]['sensei'] !== '_disabled') {
                    $GLOBALS['database']->execute_query("UPDATE `users`
                        SET `student_1` = '_none'
                        WHERE `id` = '" . $this->char_data[0]['sensei'] . "' AND `student_1` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
                    $GLOBALS['database']->execute_query("UPDATE `users`
                        SET `student_2` = '_none'
                        WHERE `id` = '" . $this->char_data[0]['sensei'] . "' AND `student_2` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
                    $GLOBALS['database']->execute_query("UPDATE `users`
                        SET `student_3` = '_none'
                        WHERE `id` = '" . $this->char_data[0]['sensei'] . "' AND `student_3` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
                    $GLOBALS['database']->execute_query("UPDATE `users_preferences`
                        SET `sensei` = '' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                }

                // Text
                $ceremony_text = 'You have passed all the tests in the chuunin exam and are therefore now recognized as a chuunin of your village.
                                You are no longer assigned to a sensei and must now seek your own way of the ninja. <br><br><b>As a Chuunin:</b>
                                <br>1. You will be able to engage in real-life or death PvP combats
                                <br>2. You will be able to go on C-ranked missions
                                <br>3. You will be able to do serious crimes (and may go to jail)
                                <br>4. You have unlocked your primary elemental affinity.
                                <br>5. Much more..<br><br>';
            } break;
            case "Jounin": {
                $elements = new Elements();
                $elements->updateUserElementMastery( floor($elements->getUserElementMastery(1)/2) ,2);
            } break;
        }

        $GLOBALS['Events']->acceptEvent('rank', array('new'=>functions::getRank($new_level_data[0]['rank_id'], $this->char_data[0]['village']), 'old'=> $GLOBALS['userdata'][0]['rank'] ));
        $GLOBALS['Events']->acceptEvent('rank_id', array('new'=>$new_level_data[0]['rank_id'], 'old'=>$GLOBALS['userdata'][0]['rank_id'] ));
        $GLOBALS['Events']->acceptEvent('level_id', array('new'=>$new_level_data[0]['levelID'], 'old'=>$GLOBALS['userdata'][0]['level_id'] ));
        $GLOBALS['Events']->acceptEvent('level', array('new'=>1, 'old'=>$GLOBALS['userdata'][0]['level'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_health', array('new'=>$GLOBALS['userdata'][0]['max_health'] + $new_level_data[0]['health_gain'], 'old'=>$GLOBALS['userdata'][0]['max_health'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$GLOBALS['userdata'][0]['max_sta'] +    $new_level_data[0]['stamina_gain'], 'old'=>$GLOBALS['userdata'][0]['max_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$GLOBALS['userdata'][0]['max_cha'] +    $new_level_data[0]['chakra_gain'], 'old'=>$GLOBALS['userdata'][0]['max_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>$GLOBALS['userdata'][0]['max_health'] + $new_level_data[0]['health_gain'], 'old'=>$GLOBALS['userdata'][0]['cur_health'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$GLOBALS['userdata'][0]['max_sta'] +    $new_level_data[0]['stamina_gain'], 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$GLOBALS['userdata'][0]['max_cha'] +    $new_level_data[0]['chakra_gain'], 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));


        $GLOBALS['userdata'][0]['rank'] = functions::getRank($new_level_data[0]['rank_id'], $this->char_data[0]['village']);
        $GLOBALS['userdata'][0]['rank_id'] = $new_level_data[0]['rank_id'];
        $GLOBALS['userdata'][0]['level_id'] = $new_level_data[0]['levelID'];
        $GLOBALS['userdata'][0]['level'] = 1;

        if($GLOBALS['userdata'][0]['rank_id'] == 3)
        {
            //toss event for primary elemen
            $elements = new Elements();
            $affinity = $elements->getUserElements()[0];
            $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$affinity, 'old'=>'none' ));
        }
        else if($GLOBALS['userdata'][0]['rank_id'] == 4)
        {
            //toss event for secondary element
            $elements = new Elements();
            $affinity = $elements->getUserElements()[1];
            $GLOBALS['Events']->acceptEvent('elements_active_secondary', array('new'=>$affinity, 'old'=>'none' ));
        }

        // Run DB update
        $GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `users_statistics`.`regen_rate` = `users_statistics`.`regen_rate` + ".$regen_increase.",
                `users_statistics`.`rank` = '".functions::getRank($new_level_data[0]['rank_id'], $this->char_data[0]['village'])."',
                `users_statistics`.`level_id` = ".$new_level_data[0]['levelID'].",
                `users_statistics`.`level` = 1,
                ".$extraQuery."
                `users_statistics`.`rank_id` = '".$new_level_data[0]['rank_id']."',
                `users_statistics`.`max_health` = `users_statistics`.`max_health` + ".$new_level_data[0]['health_gain'].",
                `users_statistics`.`max_cha` = `users_statistics`.`max_cha` + ".$new_level_data[0]['chakra_gain'].",
                `users_statistics`.`max_sta` = `users_statistics`.`max_sta` + ".$new_level_data[0]['stamina_gain'].",
                `users_statistics`.`cur_sta` = `users_statistics`.`max_sta`,
                `users_statistics`.`cur_cha` = `users_statistics`.`max_cha`,
                `users_statistics`.`cur_health` = `users_statistics`.`max_health`
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']." LIMIT 1");

        return  $ceremony_text . '<b>You have gained:</b>
                <br>' . $new_level_data[0]['health_gain'] . ' health
                <br>' . $new_level_data[0]['chakra_gain'] . ' chakra
                <br>' . $new_level_data[0]['stamina_gain'] . ' stamina
                <br>25 regeneration';
    }

    // Set the elemental affinities of the user
    private function setElementalAffinities() {

        $elements = new Elements();
        $affinities = $elements->getUserElements();

        // Update database unless they are already correct
        $this->char_data[0]['element_affinity_1'] = $affinities[0];
        $this->char_data[0]['element_affinity_2'] = $affinities[1];
        $this->char_data[0]['element_affinity_special'] = $affinities[2];

        return;
    }

}
new profile();
