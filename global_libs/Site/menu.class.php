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

class menu {

    public function construct_menu($user) {
        // New Menu
        $menuEntries = cachefunctions::getMenu();
        $smartyMenu = array(
            "character" => array(),
            "map" => array(),
            "profile" => array(),
            "communication" => array(),
            "village" => array(),
            "training" => array(),
            "missions" => array(),
            "support" => array(),
            "admin" => array(),
            "general" => array()
        );

        // Update the variables in the page class (stuff may have happened during the page load
        $GLOBALS['page']->setCurrentDetails( $user );

        // Loop through entries
        $sleepPage = false;
        foreach ($menuEntries as $entry) {

            // Some entries are disabled
            if ( $entry['menu_name'] !== "NO" && !(isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true && $entry['mobile_access'] == "no") ) {

                // Check that the page is valid
                $pageCheck = $GLOBALS['page']->checkPage( $entry , $user[0]['rank_id'], true );
                if( $pageCheck === true ){

                    // Make sure we don't show join village in Syndicate
                    if( !(in_array($user[0]['location'], array("Gambler's Den", "Bandit's Outpost", "Poacher's Camp", "Pirate's Hideout")) && $entry['id'] == 44 ) && !($GLOBALS['page']->isOutlaw && $entry['id'] == 23) ){

                        // Add to proper entry
                        $smartyMenu[$entry['group']][] = array("link" => "?id=" . $entry['id'], "name" => $entry['menu_name']);

                        // Check if this is a place for sleeping/waking up
                        if( in_array( $entry['id'], array(23,19) ) )
                        {
                            $sleepPage = $entry['id'];
                            if($sleepPage != false && $user[0]['village'] == "Syndicate")
                                $sleepPage = 19;
                        }
                    }
                    else if(in_array($user[0]['location'], array("Gambler's Den", "Bandit's Outpost", "Poacher's Camp", "Pirate's Hideout"))
                            &&
                            $entry['id'] == 19
                            &&
                            $GLOBALS['page']->isOutlaw
                    )
                    {
                        $smartyMenu[$entry['group']][] = array("link" => "?id=" . $entry['id'], "name" => $entry['menu_name']);
                        $sleepPage = 19;
                    }
                }
            }
        }

        // Facebook Link
        $smartyMenu["communication"][] = array("link" => "https://www.facebook.com/TheNinjaRPG", "name" => "Facebook Group");
        $smartyMenu["communication"][] = array("link" => "https://twitter.com/TheNinjaRPG", "name" => "Twitter TNR");

        // Village Jump
        if ($user[0]['user_rank'] == 'Tester' || $user[0]['user_rank'] == 'Admin') {
            $smartyMenu["general"][] = array("link" => "?id=99", "name" => "Tool: Village Jump");
        }

        // Blue Message
        if ( $user[0]['user_rank'] == 'EventMod' || $user[0]['user_rank'] == 'Supermod' || $user[0]['user_rank'] == 'Admin' || $user[0]['user_rank'] == 'PRmanager') {
            $smartyMenu["general"][] = array("link" => "?id=101", "name" => "Tool: Blue Message");
        }

        // Hijack User
        if ( $user[0]['user_rank'] == 'Admin') {
            $smartyMenu["general"][] = array("link" => "?id=112", "name" => "Tool: Hijack User");
        }

        // If not on mobile, then show staff panels
        if( !isset($GLOBALS['returnJson']) ){

            // Admin Links
            if ($user[0]['user_rank'] == 'Admin') {
                $smartyMenu["general"][] = array("link" => "./panel_admin/", "name" => "Control Panel: Admin");
            }

            // Content team
            if ($user[0]['user_rank'] == 'Admin' || $user[0]['user_rank'] == 'ContentAdmin' || $user[0]['user_rank'] == 'ContentMember') {
                $smartyMenu["general"][] = array("link" => "./panel_content/", "name" => "Control Panel: Content");
            }

            // Event Links
            if ( ($user[0]['user_rank'] == 'EventMod' || $user[0]['user_rank'] == 'Admin') && $user[0]['baby_mode'] == 'no') {
                $smartyMenu["general"][] = array("link" => "./panel_event/", "name" => "Control Panel: Event");
            }

            // Mod Links
            if ($user[0]['user_rank'] == 'Moderator' || $user[0]['user_rank'] == 'Supermod' || $user[0]['user_rank'] == 'Admin') {
                $smartyMenu["general"][] = array("link" => "/panel_moderator/", "name" => "Control Panel: Moderator");
                $smartyMenu["general"][] = array("link" => "?id=98", "name" => "Moderator: User Reports");
            }
        }

        // Go back links
        if (isset($_SESSION["backData"]) && !functions::isAPIcall()) {
            $smartyMenu["general"][] = array("link" => "?id=66&amp;act=reclaim", "name" => "Back to Original Character");
        }

        // Set username color
        $rankColor = !empty($user[0]['visibleRank']) ? $user[0]['visibleRank'] : $user[0]['federal_level'];
        if( $rankColor == "Gold" ){
            $GLOBALS['template']->assign('userColor', "Goldenrod");
        }elseif( $rankColor == "Silver" ){
            $GLOBALS['template']->assign('userColor', "Gray");
        }else{
            $GLOBALS['template']->assign('userColor', "navy");
        }

        //five minute warning for logout timer
        if($user[0]['logout_timer'] - $GLOBALS['user']->load_time < 20)  //red
            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=1&act=logout",'<span style="color:#cc0000;">Logout Imminent!</span> <br> (click to logout)')));

        else if($user[0]['logout_timer'] - $GLOBALS['user']->load_time < 90) //orange
            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=1&act=logout",'<span style="color:#ff8000;">Logout Imminent!</span> <br> (click to logout)')));

        else if($user[0]['logout_timer'] - $GLOBALS['user']->load_time < 300) //black
            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=1&act=logout",'Logout Imminent! <br> (click to logout)')));

        // Logout Timer
        if($user[0]['status'] != 'combat' && $user[0]['status'] != 'exiting_combat')
            if($GLOBALS['layout'] == 'default')
            {
                $blaat = $user[0]['logout_timer'] - $GLOBALS['user']->load_time;
            }
            else
                $blaat = functions::convert_time($user[0]['logout_timer'] - $GLOBALS['user']->load_time, 'logouttime');
        else
            $blaat = 'n/a';

        $GLOBALS['template']->assign('logoutTimer', $blaat);

        // Send to Smarty
        $GLOBALS['template']->assign('menuArray', $smartyMenu);
        $GLOBALS['template']->assign('fbData', $user[0]['fbID']);

        // Get the version of the game
        $GLOBALS['template']->assign('gameVersion', cachefunctions::getGameVersion() );

        // Set the location of media files
        $GLOBALS['template']->assign('s3', MEDIA_ROOT);

        // Create Avatar, bars, rank etc and send to smarty
        $GLOBALS['template']->assign('user_rank', str_replace(" ", "<br>", $user[0]['rank']) );
        $GLOBALS['template']->assign('user_rank_id', $user[0]['rank_id'] );
        $GLOBALS['template']->assign('user_avatar', functions::getAvatar($user[0]['id']));
        $GLOBALS['template']->assign('user_name', $user[0]['username']);
        $GLOBALS['template']->assign('user_village', $user[0]['village']);
        $GLOBALS['template']->assign('user_factionType', ($user[0]['village'] == "Syndicate") ? "Syndicate" : "Village" );

        $GLOBALS['template']->assign('user_latitude', $user[0]['latitude']);
        $GLOBALS['template']->assign('user_longitude', $user[0]['longitude']);
        $GLOBALS['template']->assign('user_location', $user[0]['location']);
        $GLOBALS['template']->assign('user_region', $user[0]['region']);

        $GLOBALS['template']->assign('userStatus', $user[0]['status']);
        $GLOBALS['template']->assign('userMoney', $user[0]['money']);

        if($user[0]['sr'] != 0)
            $GLOBALS['template']->assign('strengthFactor', base_convert(floor(sqrt(($user[0]['dr'] * ( ( $user[0]['max_health'] ) / $user[0]['sr'] ))+4+24789)), 10, 9));

        $GLOBALS['template']->assign('max_cha', $user[0]['max_cha']);
        $GLOBALS['template']->assign('cur_cha', round($user[0]['cur_cha'],2) );
        $GLOBALS['template']->assign('cha_perc', floor(($user[0]['cur_cha'] / $user[0]['max_cha']) * 100));
        $GLOBALS['template']->assign('max_sta', $user[0]['max_sta']);
        $GLOBALS['template']->assign('cur_sta', round($user[0]['cur_sta'],2) );
        $GLOBALS['template']->assign('sta_perc', floor(($user[0]['cur_sta'] / $user[0]['max_sta']) * 100));
        $GLOBALS['template']->assign('max_health', $user[0]['max_health']);
        $GLOBALS['template']->assign('cur_health', round($user[0]['cur_health'],2));
        $GLOBALS['template']->assign('health_perc', floor(($user[0]['cur_health'] / $user[0]['max_health']) * 100));

        $GLOBALS['template']->assign('last_regen', $user[0]['last_regen']);

        // A wake/sleep link
        $this->sleepLink = false;
        if( $sleepPage !== false ){

            switch( $user[0]['status'] ){
                case "awake":
                    $this->sleepLink = "<a id='sleepLink' href='?id=".$sleepPage."&amp;act=sleep'>Sleep</a>";
                break;
                case "asleep":
                    $this->sleepLink = "<a id='sleepLink' href='?id=".$sleepPage."&amp;act=wake'>Wake Up</a>";
                break;
            }
            if( $this->sleepLink !== false ){
                $GLOBALS['template']->assign( 'sleepLink', $this->sleepLink );
            }
        }


        // Get regen rate in rate/min
        $regeneration = cachefunctions::getUserRegeneration($_SESSION['uid']);
        if( !empty($regeneration) ){

            // If not in combat, add it to the template for viewing
            if( $user[0]['status'] !== "combat" && $user[0]['status'] !== "exiting_combat" && $user[0]['status'] !== "hospitalized" && $user[0]['status'] !== "drowning"){

                // Update template to show regen rate
                $GLOBALS['template']->assign('currentRegenRate', $regeneration);

                // Adjust according to update frequency
                $regenPerSec = round($regeneration * $GLOBALS['user']->regenFrequency / 60,2);

                // Set the timer for the template
                $timeSinceRegen = $GLOBALS['user']->load_time - $user[0]['last_regen'];
                $timeSinceRegen = $timeSinceRegen < 0 ? 0 : $timeSinceRegen;
                $timer = $GLOBALS['user']->regenFrequency - $timeSinceRegen;
                $regenPerSec = functions::convert_time( $timer, 'widgetRegenTimer', 'updateWidget', $regenPerSec, "dontShow" );
                $GLOBALS['template']->assign('userRegeneration', $regenPerSec);
            }
            else if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes'){
                $GLOBALS['template']->assign('userRegeneration', "");
                $GLOBALS['template']->assign('currentRegenRate', "");
            }
            else{
                $GLOBALS['template']->assign('userRegeneration', "N/A");
                $GLOBALS['template']->assign('currentRegenRate', "N/A");
            }
        }

        // Check for standard Messages
        self::check_messages($user);

        // Create and send hash for jQuery ajax call
        $GLOBALS['template']->assign("pageToken", functions::getToken());

    }


    //	Set messages
    private static function check_messages($data) {
        // Only check if some user data is present
        if ($data != '0 rows') {
            // Check for spar challenges
            if ($data[0]['status'] != 'combat' && $data[0]['status'] != 'hospitalized' && $data[0]['status'] != 'exiting_combat') {
                //$spar = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count`
                //    FROM `spar_challenges`
                //    WHERE `oid` = '" . $_SESSION['uid'] . "'");
                //if ($spar[0]['count'] != 0 && $data[0]['silence_spar'] != 'yes') {
                //    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=43",'You have been Challenged')));
                //
                //}
                $spar_data = $GLOBALS['database']->fetch_data('SELECT `spar_challenges`.*, `users`.`username`
                                                               FROM `spar_challenges`
                                                                   INNER JOIN `users` ON (`users`.`id` = `spar_challenges`.`uid`)
                                                               WHERE `spar_challenges`.`oid` = '.$_SESSION['uid'].' LIMIT 1');
                    //array(1) { [0]=> array(5) { ["id"]=> string(1) "1" ["uid"]=> string(7) "2013353" ["oid"]=> string(4) "1096" ["time"]=> string(10) "1509460523" ["username"]=> string(5) "Koala" } }
                if(is_array($spar_data) && $data[0]['silence_spar'] != 'yes')
                    foreach($spar_data as $challenge)
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=43",$challenge['username'].' has challenged you!'),
                                                                              'buttons' => array(array('?id=43&act=del&cid='.$challenge['id'],'Decline','yes'),array('?id=43&act=accept&cid='.$challenge['id'],'Accept','yes'))));

            }

            if($GLOBALS['userdata'][0]['dialog'] != '')
            {
                if($GLOBALS['userdata'][0]['status'] == 'questing')
                {
                    if($_GET['id'] != '120')
                    {
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=120','A quest\'s dialog wants your attention!')));
                    }
                }
                else if( !in_array($GLOBALS['userdata'][0]['status'],array('awake','asleep','exiting_combat','combat')) )
                {
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=120','A quest\'s dialog is waiting for you but you are currently unavailable!')));
                }
                else
                {
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=120','A quest\'s dialog wants your attention!')));

                    $GLOBALS['Events']->acceptEvent('status', array('new'=>'questing', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                    $GLOBALS['template']->assign('userStatus', 'questing');

                    $GLOBALS['userdata'][0]['status'] = 'questing';

                    $data[0]['status'] = 'questing';

                    if(!$GLOBALS['database']->execute_query('UPDATE `users` SET `status` = "questing" WHERE `id` = '.$_SESSION['uid'].' LIMIT 1'))
                        throw new exception("failed to update status for questing: ".'UPDATE `users` SET `status` = "questing" WHERE `id` = '.$_SESSION['uid'].' LIMIT 1');
                }
            }
            else if($GLOBALS['userdata'][0]['dialog'] == '' && $GLOBALS['userdata'][0]['status'] == 'questing')
            {
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['template']->assign('userStatus', 'awake');

                $GLOBALS['userdata'][0]['status'] = 'awake';

                $data[0]['status'] = 'awake';

                if(!$GLOBALS['database']->execute_query('UPDATE `users` SET `status` = "awake" WHERE `id` = '.$_SESSION['uid'].' LIMIT 1'))
                        throw new exception("failed to update status for questing: ".'UPDATE `users` SET `status` = "questing" WHERE `id` = '.$_SESSION['uid'].' LIMIT 1');
            }

            // Status Notifications
            if (($data[0]['status'] == 'combat' || $data[0]['status'] == 'exiting_combat') && $_GET['id'] != '113') {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=113','You are in battle!')));
            }
            elseif ($data[0]['status'] == 'hospitalized' && $data[0]['village'] != 'Syndicate') {
                if($data[0]['hospital_timer'] == 0)
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=34','You have been hospitalized!')));
                else
                {
                    $time_left = ($data[0]['hospital_timer'] - $GLOBALS['user']->load_time);
                    if($time_left <= 0)
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=34','You are in the hospital'), 'buttons' => array('?id=34&act=release','Sign Out','no')));
                    else
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You are in the hospital!<br>'.functions::convert_time(
                                $time_left, 'hospitaltime', 'false', $regen = 1, $showTimer = "Show", $refreshLink = "reload", $endMobileTxts=false
                        ), 'buttons' => array(array('?id=34','Visit'),array('?id=34&act=bribe','Bribe','yes'))));

                }
            }
            elseif ($data[0]['status'] == 'hospitalized' && $data[0]['village'] == 'Syndicate') {
                if($data[0]['hospital_timer'] == 0)
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=51','You have been hospitalized!')));
                else
                {
                    $time_left = ($data[0]['hospital_timer'] - $GLOBALS['user']->load_time);
                    if($time_left <= 0)
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=51','You are in the hospital!'), 'buttons' => array('?id=51&act=release','Sign Out','no')));
                    else
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You are in the hospital!<br>'.functions::convert_time(
                                $time_left, 'hospitaltime', 'false', $regen = 1, $showTimer = "Show", $refreshLink = "reload", $endMobileTxts=false
                        ), 'buttons' => array( array('?id=51','Visit'), array('?id=51&act=bribe','Bribe','yes'))));
                }
            }
            elseif ($data[0]['status'] == 'jailed')
            {
                $time_left = ceil( $data[0]['jail_timer'] - $GLOBALS['user']->load_time );
                if( $time_left <= 0 )
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=38&action=signout",'You are in the jail!<br>Sign Out')));
                else
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=38",'You are in the jail!<br>'.functions::convert_time($time_left, 'jailtime', 'false'))));
            }
            elseif ($data[0]['status'] == 'drowning')
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=109",'You are unconscious!')));
            }
            elseif($data[0]['over_encumbered'])
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=11",'You are over-encumbered!')));
            }
            elseif($data[0]['drowning'] > 1 && $data[0]['region'] != 'ocean')
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=8",'You are tired, stay on land to rest.')));
            }
            elseif ($data[0]['drowning'] >= floor((8 + $data[0]['rank_id'] + floor(($data[0]['rank_id']-1)/2))/2) ) //half way to drowning
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=8",'You are getting tired, reach land to rest.')));
            }
            elseif ($data[0]['drowning'] >= 8 + $data[0]['rank_id'] + floor(($data[0]['rank_id']-1)/2) ) //8 + rank(1-5) + (1 if rank 3/4 and 2 if rank 5)
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=8",'You are drowning, hurry to land')));
            }

            // User is being deleted
            if ($data[0]['deletion_timer'] > 0) {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'Account flagged for deletion!', 'buttons' => array("?id=4&amp;act=delete",'delete','yes')));
            }

            // User is being reset
            if ($data[0]['reset_timer'] > 0) {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'Account flagged for reset!', 'buttons' => array("?id=4&amp;act=reset",'reset','yes'))); 
            }

            // User has personal message
            if ($data[0]['new_pm'] != 0 && $_GET['id'] != '3')
            {
                if($data[0]['new_pm'] != 1)
                    $plurality = 's';
                else
                    $plurality = '';

                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=3",'You have '.$data[0]['new_pm'].' unread PM'.$plurality)));
            }

            // Special Jutsu Notification
            if ($data[0]['jutsu_timer'] > 0) {
                $page = 39;
                switch( $data[0]['rank_id'] ){
                    case 1: $page = 18; break;
                    case 2: $page = 29; break;
                }
                if ($data[0]['jutsu_timer'] < $GLOBALS['user']->load_time) {
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=".$page."&amp;page=special",'Special jutsu training')));
                } else {
//                    $GLOBALS['template']->append('MSG',
//                            array("txt" => 'Training a special jutsu<br>'.functions::convert_time(($data[0]['jutsu_timer'] - $GLOBALS['user']->load_time), 'jutsutimer', 'false') , "href"=>"?id=".$page."&page=special")
//                    );
                }
            }

            // Moderator Messages
            if( !isset($GLOBALS['returnJson']) ){
                if ($data[0]['user_rank'] == 'Moderator' || $data[0]['user_rank'] == 'Supermod' || $data[0]['user_rank'] == 'Admin') {
                    $count = $GLOBALS['database']->fetch_data("SELECT `time`
                        FROM `user_reports`
                        WHERE `status` = 'unviewed' LIMIT 1");
                    if ($count !== "0 rows") {
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=98",'There are unviewed reports! ('.count($count).')')));
                    }
                }
            }

        }
    }
}