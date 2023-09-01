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

    require_once('./global_libs/Site/data.class.php');
    require_once(Data::$absSvrPath.'/global_libs/Site/database.class.php');

    $GLOBALS['database'] = new database;

    if(!isset($GLOBALS['Events']))
    {
        require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
        $closeEvents = true;
        $GLOBALS['Events'] = new Events();
    }

    function newsta($sp, $user) {
        $temp = (($sp / 100)) * $user[0]['max_sta'] + $user[0]['cur_sta'];
        if ($temp > $user[0]['max_sta']) {
            $temp = $user[0]['max_sta'];
        }
        return $temp;
    }

    function newcha($cp, $user) {
        $temp = (($cp / 100)) * $user[0]['max_cha'] + $user[0]['cur_cha'];
        if ($temp > $user[0]['max_cha']) {
            $temp = $user[0]['max_cha'];
        }
        return $temp;
    }

    function percentage($max, $rank) {
        switch ($rank) {
            case 1: $per = 1 * ceil(100 * (90 + (5000 / 50000) * $max) / $max); break;
            case 2: $per = 1 * ceil(100 * (90 + (5000 / 50000) * $max) / $max); break;
            case 3: $per = ceil(100 * (90 + (5000 / 50000) * $max) / $max); break;
            case 4: $per = 0.8 * ceil(100 * (90 + (5000 / 50000) * $max) / $max); break;
            case 5: $per = 0.65 * ceil(100 * (90 + (5000 / 50000) * $max) / $max);break;
        }
        return $per;
    }
    
    /* 		Apex Web Gaming		 */
    if (isset($_POST['i'])) {

        $uid = $GLOBALS['database']->db_escape_string($_POST['i']);

        $use = $GLOBALS['database']->fetch_data("SELECT * FROM `users_statistics` WHERE `uid` = '" . $uid . "' LIMIT 1");

        $newsta = newsta(percentage($use[0]['max_sta'], $use[0]['rank_id']), $use);
        $newcha = newcha(percentage($use[0]['max_cha'], $use[0]['rank_id']), $use);

        $check_voted = $GLOBALS['database']->fetch_data("SELECT `AWG` FROM `votes` WHERE `userID` = '$uid' LIMIT 1");

        if ($check_voted[0]['AWG'] < time() - 86400) {

            
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$newsta, 'old'=>$use[0]['cur_sta'] ));
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$newcha, 'old'=>$use[0]['cur_cha'] ));

            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `cur_sta` = '" . $newsta . "', `cur_cha` = '" . $newcha . "' WHERE `uid` = '" . $uid . "' LIMIT 1");
            $GLOBALS['database']->execute_query("UPDATE `votes` SET `AWG` = UNIX_TIMESTAMP() WHERE `userID` = '" . $uid . "' LIMIT 1");
        }
    }

    /*         Directory of Games        */
    if (isset($_GET['votedef'])) {

        $uid = $_GET['votedef'];

        $use = $GLOBALS['database']->fetch_data("SELECT * FROM `users_statistics` WHERE `uid` = '" . $uid . "' LIMIT 1");

        $newsta = newsta(percentage($use[0]['max_sta'], $use[0]['rank_id']), $use);
        $newcha = newcha(percentage($use[0]['max_cha'], $use[0]['rank_id']), $use);

        $check_voted = $GLOBALS['database']->fetch_data("SELECT `DOG` FROM `votes` WHERE `userID` = '$uid' LIMIT 1");

        if ($check_voted[0]['DOG'] < time() - 86400) {
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$newsta, 'old'=>$user[0]['cur_sta'] ));
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$newcha, 'old'=>$user[0]['cur_cha'] ));

            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `cur_sta` = '" . $newsta . "', `cur_cha` = '" . $newcha . "' WHERE `uid` = '" . $uid . "' LIMIT 1");
            $GLOBALS['database']->execute_query("UPDATE `votes` SET `DOG` = UNIX_TIMESTAMP() WHERE `userID` = '" . $uid . "' LIMIT 1");
        }
    }

    /*        Top Web Games        */
    if (isset($_GET['vuser'])) {

        //$GLOBALS['database']->execute_query("UPDATE `users` SET `pop_now` = `pop_now` + 10, `pop_ever` = `pop_ever` + 10 WHERE `username` = 'Terriator' LIMIT 1");

        $uid = $_GET['vuser'];

        $use = $GLOBALS['database']->fetch_data("SELECT * FROM `users_statistics` WHERE `uid` = '" . $uid . "' LIMIT 1");

        $newsta = newsta(percentage($use[0]['max_sta'], $use[0]['rank_id']), $use);
        $newcha = newcha(percentage($use[0]['max_cha'], $use[0]['rank_id']), $use);

        $check_voted = $GLOBALS['database']->fetch_data("SELECT `TWG` FROM `votes` WHERE `userID` = '$uid' LIMIT 1");

        if ($check_voted[0]['TWG'] < time() - 86400) {
            
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$newsta, 'old'=>$user[0]['cur_sta'] ));
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$newcha, 'old'=>$user[0]['cur_cha'] ));
            
            $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `cur_sta` = '" . $newsta . "', `cur_cha` = '" . $newcha . "' WHERE `uid` = '" . $uid . "' LIMIT 1");
            $GLOBALS['database']->execute_query("UPDATE `votes` SET `TWG` = UNIX_TIMESTAMP() WHERE `userID` = '" . $uid . "' LIMIT 1");
        }
    }

    if($closeEvents)
        $GLOBALS['Events']->closeEvents();