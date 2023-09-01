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

    class module {

        private $staff_search;

        public function __construct() {
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Limit User Rank Searching
                $this->staff_search = ($GLOBALS['userdata'][0]['user_rank'] === 'Admin') ? "'Moderator', 'Supermod', 'Admin'" : "'Moderator', 'Supermod'";

                if (isset($_REQUEST['act'])) {
                    switch($_REQUEST['act']) {
                        case('trackbans'): self::trackBans(); break;
                        case('tracktavernbans'): self::trackTavernBans(); break;
                        case('trackwarnings'): self::trackWarnings(); break;
                        case('trackreports'): self::trackReports(); break;
                        default: {
                            (!isset($_REQUEST['moderator_track'])) ? self::moderator_form() : self::track_overview();
                        } break;
                    }
                }
                else {
                   (!isset($_REQUEST['moderator_track'])) ? self::moderator_form() : self::track_overview();
                }
            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), "Game Ban System", 'id='.$_REQUEST['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid']) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        private function moderator_form() {

            // Get the data
            if (!($mods = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`
                FROM `users_statistics`
                    INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                WHERE `users_statistics`.`user_rank` IN (".$this->staff_search.") ORDER BY `users`.`username` ASC"))) {
                throw new Exception('Could not retrieve the moderators from the database');
            }
            elseif($mods === '0 rows') {
                throw new Exception('There are no staff members in the system!');
            }

            $names = array();
            foreach($mods as $mod) {
                $names[ $mod['username'] ] = $mod['username'];
            }

            // Show the form
            $GLOBALS['page']->UserInput("Using this panel you can keep track of staff's activity like the number of reports handled, number of bans, as well as links to complete lists of acts by this particular staff.",
                "Staff Tracker",
                array(
                    // A select box
                    array(
                        "infoText" => "Moderator Name",
                        "inputFieldName" => "moderator_track",
                        "type" => "select",
                        "inputFieldValue" => $names
                    )
                ),
                array(
                    "href" => "?id=".$_REQUEST['id'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                "?id=".$_REQUEST['id'] ,
                "modTrackForm"
            );
        }

        private function getModData($name) {

            if(!($mod_data = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users_statistics`.`user_rank`, `users_loyalty`.`village`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id` AND `users_statistics`.`user_rank` IN (".$this->staff_search."))
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users_statistics`.`uid`)
                WHERE `users`.`username` = '" . $name . "' LIMIT 1"))) {
                throw new Exception('There was an error trying to obtain the staff member!');
            }
            elseif($mod_data === '0 rows') {
                throw new Exception('The staff member does not exist in the system!');
            }

            return $mod_data;
        }

        private function track_overview() {

            if (!isset($_REQUEST['moderator_track']) ) {
                throw new Exception('There was no moderator specified to track!');
            }

            $mod_data = self::getModData($_REQUEST['moderator_track']);

            //  All time
            if(!($mod_track_data = $GLOBALS['database']->fetch_data("SELECT (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "'
                        AND `moderator_log`.`action` IN ('ban', 'reduction', 'extension')
                ) AS `bans_ever`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "'
                        AND `moderator_log`.`action` = 'warning'
                ) AS `warning_ever`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "'
                        AND `moderator_log`.`action` = 'tavern-ban'
                ) AS `tbans_ever`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "' AND `moderator_log`.`action` IN ('ban', 'reduction', 'extension')
                        AND `moderator_log`.`time` >= (UNIX_TIMESTAMP() - 604800)
                ) AS `week_bans`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "' AND `moderator_log`.`action` = 'warning'
                        AND `moderator_log`.`time` >= (UNIX_TIMESTAMP() - 604800)
                ) AS `week_warning`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "' AND `moderator_log`.`action` = 'tavern-ban'
                        AND `moderator_log`.`time` >= (UNIX_TIMESTAMP() - 604800)
                ) AS `week_tbans`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "' AND `moderator_log`.`action` IN ('ban', 'reduction', 'extension')
                        AND `moderator_log`.`time` >= (UNIX_TIMESTAMP() - 2678400)
                ) AS `month_bans`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "' AND `moderator_log`.`action` = 'warning'
                        AND `moderator_log`.`time` >= (UNIX_TIMESTAMP() - 2678400)
                ) AS `month_warning`,
                (
                    SELECT COUNT(`moderator_log`.`time`) FROM `moderator_log`
                    WHERE `moderator_log`.`moderator` = '" . $mod_data[0]['username'] . "' AND `moderator_log`.`action` = 'tavern-ban'
                        AND `moderator_log`.`time` >= (UNIX_TIMESTAMP() - 2678400)
                ) AS `month_tbans`,
                (
                    SELECT COUNT(`user_reports`.`time`) FROM `user_reports`
                    WHERE `user_reports`.`processed_by` = '" . $mod_data[0]['username'] . "'
                ) AS `reports_ever`,
                (
                    SELECT COUNT(`user_reports`.`time`) FROM `user_reports`
                    WHERE `user_reports`.`processed_by` = '" . $mod_data[0]['username'] . "'
                        AND `user_reports`.`time` >= (UNIX_TIMESTAMP() - 604800)
                ) AS `week_reports`,
                (
                    SELECT COUNT(`user_reports`.`time`) FROM `user_reports`
                    WHERE `user_reports`.`processed_by` = '" . $mod_data[0]['username'] . "'
                        AND `user_reports`.`time` >= (UNIX_TIMESTAMP() - 2678400)
                ) AS `month_reports`"))) {
                throw new Exception('There was an issue trying to obtain all moderator tracking data!');
            }
            elseif($mod_track_data === '0 rows') {
                throw new Exception('The user has not done a single thing yet!');
            }

            $count_data = $week_count_data = $month_count_data = $report_data = $week_report_data = $month_report_data = array();

            $count_data[0]['bans_ever'] = $mod_track_data[0]['bans_ever'];
            $count_data[0]['warning_ever'] = $mod_track_data[0]['warning_ever'];
            $count_data[0]['tbans_ever'] = $mod_track_data[0]['tbans_ever'];

            $week_count_data[0]['bans_ever'] = $mod_track_data[0]['week_bans'];
            $week_count_data[0]['warning_ever'] = $mod_track_data[0]['week_warning'];
            $week_count_data[0]['tbans_ever'] = $mod_track_data[0]['week_tbans'];

            $month_count_data[0]['bans_ever'] = $mod_track_data[0]['month_bans'];
            $month_count_data[0]['warning_ever'] = $mod_track_data[0]['month_warning'];
            $month_count_data[0]['tbans_ever'] = $mod_track_data[0]['month_tbans'];

            $report_data[0]['reports_ever'] = $mod_track_data[0]['reports_ever'];
            $week_report_data[0]['reports_ever'] = $mod_track_data[0]['week_reports'];
            $month_report_data[0]['reports_ever'] = $mod_track_data[0]['month_reports'];

            $GLOBALS['template']->assign('mod_data', $mod_data);

            $GLOBALS['template']->assign('count_data', $count_data);
            $GLOBALS['template']->assign('week_count_data', $week_count_data);
            $GLOBALS['template']->assign('month_count_data', $month_count_data);


            $GLOBALS['template']->assign('report_data', $report_data);
            $GLOBALS['template']->assign('week_report_data', $week_report_data);
            $GLOBALS['template']->assign('month_report_data', $month_report_data);

            $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/mod_track/overview.tpl');
        }

        private function trackBans() {

            if (!isset($_REQUEST['mid']) || functions::ws_remove($_REQUEST['mid']) === '') {
                throw new Exception('There was no moderator specified to track bans!');
            }

            if(!($banned = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `moderator_log`.* FROM `moderator_log`
                    LEFT JOIN `users` ON (`users`.`id` = `moderator_log`.`uid`)
                WHERE `moderator_log`.`action` IN ('ban', 'extension', 'reduction')
                    AND `moderator_log`.`moderator` = '" . $_REQUEST['mid'] . "' ORDER BY `moderator_log`.`time` DESC"))) {
                throw new Exception('There was an error trying to receive the staff members bans!');
            }
            elseif($banned === '0 rows') {
                throw new Exception('There are no bans recorded on the staff member!');
            }

            foreach($banned as $key => $ban) {
                if(empty($ban['username'])) {
                    $banned[$key]['username'] = "User Deleted";
                }
            }

            // Show currently banned users
            tableParser::show_list('log', "Bans issued by: ".$banned[0]['moderator'],
                $banned,
                array(
                    'username' => "Username",
                    'time' => "Time",
                    'duration' => "Duration",
                    'reason' => "Reason",
                ),
                array(
                    array(
                        "name" => "Details",
                        "link" => Data::$domainName."/?id=98",
                        "act" => "logDetails",
                        "eid" => "table.id"
                    )
                ),
                true, // Send directly to contentLoad
                false,
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                ""
            );

            $GLOBALS['template']->assign('returnLink', true);
        }

        private function trackTavernBans() {

            if (!isset($_REQUEST['mid']) || functions::ws_remove($_REQUEST['mid']) === '') {
                throw new Exception('There was no moderator specified to track tavern bans!');
            }

            if(!($banned = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `moderator_log`.* FROM `moderator_log`
                    LEFT JOIN `users` ON (`users`.`id` = `moderator_log`.`uid`)
                WHERE `moderator_log`.`action` = 'tavern-ban' AND `moderator_log`.`moderator` = '" . $_REQUEST['mid'] . "'
                    ORDER BY `moderator_log`.`time` DESC"))) {
                throw new Exception('There was an error trying to receive the staff members tavern bans!');
            }
            elseif($banned === '0 rows') {
                throw new Exception('There are no tavern bans recorded on the staff member!');
            }

            foreach($banned as $key => $ban) {
                if(empty($ban['username'])) {
                    $banned[$key]['username'] = "User Deleted";
                }
            }

            // Show currently banned users
            tableParser::show_list('log', "Tavern Bans issued by: ".$banned[0]['moderator'],
                $banned,
                array(
                    'username' => "Username",
                    'time' => "Time",
                    'duration' => "Duration",
                    'reason' => "Reason",
                ),
                array(
                    array(
                        "name" => "Details",
                        "link" => Data::$domainName."/?id=98",
                        "act" => "logDetails",
                        "eid" => "table.id"
                    )
                ),
                true, // Send directly to contentLoad
                false,
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                ""
            );

            $GLOBALS['template']->assign('returnLink', true);
        }

        private function trackWarnings() {

            if (!isset($_REQUEST['mid']) || functions::ws_remove($_REQUEST['mid']) === '') {
                throw new Exception('There was no moderator specified to track warnings!');
            }

            if(!($banned = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `moderator_log`.* FROM `moderator_log`
                    LEFT JOIN `users` ON (`users`.`id` = `moderator_log`.`uid`)
                WHERE `moderator_log`.`action` = 'warning' AND `moderator_log`.`moderator` = '" . $_REQUEST['mid'] . "'
                    ORDER BY `moderator_log`.`time` DESC"))) {
                throw new Exception('There was an error trying to receive the staff members warnings!');
            }
            elseif($banned === '0 rows') {
                throw new Exception('There are no warnings recorded on the staff member!');
            }

            foreach($banned as $key => $ban) {
                if(empty($ban['username'])) {
                    $banned[$key]['username'] = "User Deleted";
                }
            }

            // Show currently banned users
            tableParser::show_list('log', "Warnings issued by: ".$banned[0]['moderator'],
                $banned,
                array(
                    'username' => "Username",
                    'time' => "Time",
                    'duration' => "Duration",
                    'reason' => "Reason",
                ),
                array(
                    array(
                        "name" => "Details",
                        "link" => Data::$domainName."/?id=98",
                        "act" => "logDetails",
                        "eid" => "table.id"
                    )
                ),
                true, // Send directly to contentLoad
                false,
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                ""
            );

            $GLOBALS['template']->assign('returnLink', true);
        }

        private function trackReports() {

            if (!isset($_REQUEST['mid']) || functions::ws_remove($_REQUEST['mid']) === '') {
                throw new Exception('There was no moderator specified to track reports!');
            }

            if(!($banned = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `user_reports`.* FROM `user_reports`
                    LEFT JOIN `users` ON (`users`.`id` = `user_reports`.`uid`)
                WHERE `user_reports`.`processed_by` = '" . $_REQUEST['mid'] . "' ORDER BY `user_reports`.`time` DESC"))) {
                throw new Exception('There was an error trying to receive the staff members reports!');
            }
            elseif($banned === '0 rows') {
                throw new Exception('There are no reports recorded on the staff member!');
            }

            foreach($banned as $key => $ban) {
                if(empty($ban['username'])) {
                    $banned[$key]['username'] = "User Deleted";
                }
            }

            // Show currently banned users
            tableParser::show_list('log', "Reports handled by: ".$banned[0]['processed_by'],
                $banned,
                array(
                    'username' => "Username",
                    'time' => "Time",
                    'reason' => "Reason"
                ),
                array(
                    array(
                        "name" => "Details",
                        "link" => Data::$domainName."/?id=98",
                        "act" => "reportDetails",
                        "eid" => "table.report_id"
                    )
                ),
                true, // Send directly to contentLoad
                false,
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                ""
            );

            $GLOBALS['template']->assign('returnLink', true);
        }
    }

    new module();