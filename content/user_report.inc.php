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

class report {

    function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        if ($_GET['act'] == 'tavern') {
            //	Report tavern message
            if (!isset($_POST['Submit'])) {
                $this->tavern_report();
            } else {
                $this->file_tavern_report();
            }
        } elseif ($_GET['act'] == 'nindo') {
            //    Report nindo
            if (!isset($_POST['Submit'])) {
                $this->nindo_report();
            } else {
                $this->file_nindo_report();
            }
        } elseif ($_GET['act'] == 'kageorders') {
            //    Report nindo
            if (!isset($_POST['Submit'])) {
                $this->kageorder_report();
            } else {
                $this->file_kageorder_report();
            }
        } elseif ($_GET['act'] == 'pm') {
            //	Report PM
            if (!isset($_POST['Submit'])) {
                $this->PM_report();
            } else {
                $this->file_PM_report();
            }
        } elseif ($_GET['act'] == 'user') {
            //	Report generic rule violation
            if (!isset($_POST['Submit'])) {
                $this->user_report();
            } else {
                $this->file_user_report();
            }
        }

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }

    // Get the tavern in question
    private function getReportTavernMessage(){
        $tavern = "tavern";
        if( isset($_GET['tavern']) &&
            preg_match( "/^(tavern|tavern_anbu|tavern_clan|tavern_leaders|tavern_marriage|tavern_mod)$/", $_GET['tavern'] )
        ){
            $tavern = $_GET['tavern'];
            $this->reportIdentifier = "village_name";
            switch($_GET['tavern']){
                case "tavern_anbu":     $this->reportIdentifier = "anbu_name";   break;
                case "tavern_clan":     $this->reportIdentifier = "clan_name";      break;
                case "tavern_leaders":  $this->reportIdentifier = "user_group";   break;
                case "tavern_marriage": $this->reportIdentifier = "marriage_id";   break;
                case "tavern_mod":      $this->reportIdentifier = "user_group";   break;
            }
        }
        $message = $GLOBALS['database']->fetch_data("SELECT * FROM `".$tavern."`,`users` WHERE `time` = '" . $_GET['mt'] . "' AND `uid` = '" . $_GET['uid'] . "' LIMIT 1");
        return $message;
    }

    // Report tavern message
    private function tavern_report() {
        if (isset($_GET['mt']) && is_numeric($_GET['mt'])) {
            if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
                $message = $this->getReportTavernMessage();
                if ($message != '0 rows') {
                    $previousreport = $GLOBALS['database']->fetch_data("SELECT * FROM `user_reports` WHERE `message` = '" . addslashes($message[0]['message']) . "' AND `uid` = '" . $_GET['uid'] . "' LIMIT 1");
                    if ($previousreport == '0 rows') {
                        // Fix up array for template
                        $message[0]['message'] = functions::parse_BB($message[0]['message']);
                        $message[0]['type'] = "Tavern Message";

                        // Show template
                        $GLOBALS['template']->assign('message', $message);
                        $GLOBALS['template']->assign('reportBy', $GLOBALS['userdata'][0]['username']);
                        $GLOBALS['template']->assign('contentLoad', './templates/content/report/report_main.tpl');
                    } else {
                        $GLOBALS['page']->Message("The message has already been reported", 'Report System', 'id=24');
                    }
                } else {
                    $GLOBALS['page']->Message("The message you are trying to report doesn't exist", 'Report System', 'id=24');
                }
            } else {
                $GLOBALS['page']->Message("You are trying to report an invalid message", 'Report System', 'id=24');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid message", 'Report System', 'id=24');
        }
    }

    // File the tavern report:
    private function file_tavern_report() {
        if (isset($_GET['mt']) && is_numeric($_GET['mt'])) {
            if ($_GET['uid'] != $_SESSION['uid'] || true ) {
                if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
                    $message = $this->getReportTavernMessage();
                    if ($message != '0 rows') {
                        $test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '" . $message[0]['uid'] . "' AND `message` = '" . addslashes($message[0]['message']) . "'");
                        if ($test[0]['count'] == 0) {
                            if ($_POST['reason'] != 'other' && $_POST['reason'] != '') {
                                $reason = $_POST['reason'];
                            } elseif ($_POST['reason_text'] != '') {
                                $reason = functions::store_content($_POST['reason_text']);
                            }
                            if ($reason != '') {
                                $GLOBALS['database']->execute_query("
                                    INSERT INTO `user_reports`
                                        ( `time` , `uid` , `rid` ,`village`,
                                          `reason` , `message` , `status` ,
                                          `processed_by` , `type`, `mt`
                                        ) VALUES (
                                           '" . $GLOBALS['user']->load_time . "', '" . $message[0]['uid'] . "',
                                           '" . $_SESSION['uid'] . "', '" . $message[0][ $this->reportIdentifier ]. "',
                                           '" . $reason . "', '" . functions::store_content($message[0]['message']) . "',
                                           'unviewed', '', 'tavern', ".$_GET['mt'].") "
                                );
                                $GLOBALS['page']->Message("Your report has been submitted, a moderator will review it as soon as possible", 'Report System', 'id=24');
                            } else {
                                $GLOBALS['page']->Message("You did not submit a valid reason for reporting this user /  message", 'Report System', 'id=24');
                            }
                        } else {
                            $GLOBALS['page']->Message("This message was already reported.", 'Report System', 'id=24');
                        }
                    } else {
                        $GLOBALS['page']->Message("The message you are trying to report doesn't exist", 'Report System', 'id=24');
                    }
                } else {
                    $GLOBALS['page']->Message("You are trying to report an invalid message", 'Report System', 'id=24');
                }
            } else {
                $GLOBALS['page']->Message("You cannot report yourself", 'Report System', 'id=24');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid message", 'Report System', 'id=24');
        }
    }

    // Nindo report:
    private function nindo_report() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $message = $GLOBALS['database']->fetch_data("SELECT `users`.`nindo`, `users`.`username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($message != '0 rows') {
                if (strlen($message[0]['nindo']) > 0) {
                    // Fix up array for template
                    $message[0]['message'] = functions::parse_BB($message[0]['nindo']);
                    $message[0]['user'] = $message[0]['username'];
                    $message[0]['type'] = "Nindo Message";

                    // Show template
                    $GLOBALS['template']->assign('message', $message);
                    $GLOBALS['template']->assign('reportBy', $GLOBALS['userdata'][0]['username']);
                    $GLOBALS['template']->assign('contentLoad', './templates/content/report/report_main.tpl');
                } else {
                    $GLOBALS['page']->Message("You cannot report an emmpty nindo", 'Report System', 'id=13');
                }
            } else {
                //    This message does not exist
                $GLOBALS['page']->Message("This user does not exist, so you cannot report their nindo", 'Report System', 'id=13');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid nindo", 'Report System', 'id=13');
        }
    }

    //  File nindo report
    private function file_nindo_report() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            if ($_GET['uid'] != $_SESSION['uid']) {
                $message = $GLOBALS['database']->fetch_data("SELECT `id`, `nindo`, `username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                if ($message != '0 rows') {
                    $test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '" . $message[0]['id'] . "' AND `message` = '" . addslashes($message[0]['nindo']) . "'");
                    if ($test[0]['count'] == 0) {
                        if ($_POST['reason'] != 'other' && $_POST['reason'] != '') {
                            $reason = $_POST['reason'];
                        } elseif ($_POST['reason_text'] != '') {
                            $reason = functions::store_content($_POST['reason_text']);
                        }
                        if ($reason != '') {
                            $GLOBALS['database']->execute_query(" INSERT INTO `user_reports` ( `time` , `uid` , `rid` , `reason` , `message` , `status` , `processed_by` , `type` )VALUES ('" . $GLOBALS['user']->load_time . "', '" . $message[0]['id'] . "', '" . $_SESSION['uid'] . "', '" . $reason . "', '" . functions::store_content($message[0]['nindo']) . "', 'unviewed', '', 'nindo') ");
                            $GLOBALS['page']->Message("Your report has been submitted, a moderator will review it as soon as possible.", 'Report System', 'id=13');
                        } else {
                            $GLOBALS['page']->Message("You did not submit a valid reason for reporting this user /  nindo", 'Report System', 'id=13');
                        }
                    } else {
                        $GLOBALS['page']->Message("This nindo was already reported", 'Report System', 'id=13');
                    }
                } else {
                    $GLOBALS['page']->Message("The nindo you are trying to report doesn't exist", 'Report System', 'id=13');
                }
            } else {
                $GLOBALS['page']->Message("You cannot report your own nindo", 'Report System', 'id=13');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid nindo", 'Report System', 'id=13');
        }
    }

    // Kage order report:
    private function kageorder_report() {
        if (isset($_GET['uname'])) {
            $user = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `username` = '" . $_GET['uname'] . "'");
            if ($user != "0 rows") {
                $message = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `leader` = '" . $user[0]['username'] . "'");
                if ($message != "0 rows") {
                    if (strlen($message[0]['orders']) > 0) {

                        // Fix up array for template
                        $message[0]['message'] = functions::parse_BB($message[0]['orders']);
                        $message[0]['user'] = $user[0]['username'];
                        $message[0]['type'] = "Village Order Message";

                        // Show template
                        $GLOBALS['template']->assign('message', $message);
                        $GLOBALS['template']->assign('reportBy', $GLOBALS['userdata'][0]['username']);
                        $GLOBALS['template']->assign('contentLoad', './templates/content/report/report_main.tpl');
                    } else {
                        $GLOBALS['page']->Message("You cannot report an emmpty order", 'Report System', 'id=9');
                    }
                } else {
                    $GLOBALS['page']->Message("This user isn't in charge of a village", 'Report System', 'id=9');
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'Report System', 'id=9');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid kage order", 'Report System', 'id=9');
        }
    }

    // File kage order report
    private function file_kageorder_report() {
        if (isset($_GET['uname'])) {
            $user = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `username` = '" . $_GET['uname'] . "'");
            if ($user != "0 rows") {
                $this->village = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `leader` = '" . $user[0]['username'] . "'");
                if ($this->village != "0 rows") {
                    $test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '" . $user[0]['id'] . "' AND `message` = '" . addslashes($this->village[0]['orders']) . "'");
                    if ($test[0]['count'] == 0) {
                        if ($_POST['reason'] != 'other' && $_POST['reason'] != '') {
                            $reason = $_POST['reason'];
                        } elseif ($_POST['reason_text'] != '') {
                            $reason = functions::store_content($_POST['reason_text']);
                        }
                        if ($reason != '') {
                            $GLOBALS['database']->execute_query(" INSERT INTO `user_reports` ( `time` , `uid` , `rid` , `reason` , `message` , `status` , `processed_by` , `type` )VALUES
                            ('" . $GLOBALS['user']->load_time . "', '" . $user[0]['id'] . "', '" . $_SESSION['uid'] . "', '" . $reason . "', '" . functions::store_content($this->village[0]['orders']) . "', 'unviewed', '', 'kageorder') ");
                            $GLOBALS['page']->Message("Your report has been submitted, a moderator will review it as soon as possible", 'Report System', 'id=9');
                        } else {
                            $GLOBALS['page']->Message("You did not submit a valid reason for reporting this user /  nindo", 'Report System', 'id=9');
                        }
                    } else {
                        $GLOBALS['page']->Message("This order was already reported", 'Report System', 'id=9');
                    }
                } else {
                    $GLOBALS['page']->Message("User isn't in charge of any village", 'Report System', 'id=9');
                }
            } else {
                $GLOBALS['page']->Message("The user doesn't exist", 'Report System', 'id=9');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid order", 'Report System', 'id=9');
        }
    }

    //  PM report
    private function PM_report() {
        if (isset($_GET['pmid']) && is_numeric($_GET['pmid']) && isset($_GET['uid'])) {
            $message = $GLOBALS['database']->fetch_data("SELECT `users_pm`.* FROM `users_pm` WHERE `time` = '" . $_GET['pmid'] . "' AND `sender_uid` = '" . $_GET['uid'] . "' AND `receiver_uid` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
            if ($message != '0 rows') {
                $temp = 'Subject: "' . $message[0]['subject'] . '"<br>Message: "' . $message[0]['message'] . '"';
                $crim = $GLOBALS['database']->fetch_data("SELECT `id`,`username` FROM `users` WHERE `id` LIKE '" . $message[0]['sender_uid'] . "' LIMIT 1");
                if ($crim != '0 rows') {
                    $temp_query = "SELECT * FROM `user_reports` WHERE `message` = '" . addslashes($temp) . "' AND `uid` = '" . $crim[0]['id'] . "' LIMIT 1";
                    $previousreport = $GLOBALS['database']->fetch_data($temp_query);
                    if ($previousreport == '0 rows') {

                        // Fix up array for template
                        $message[0]['message'] = functions::parse_BB($message[0]['message']);
                        $message[0]['user'] = $crim[0]['username'];
                        $message[0]['type'] = "Private Message";

                        // Show template
                        $GLOBALS['template']->assign('message', $message);
                        $GLOBALS['template']->assign('reportBy', $GLOBALS['userdata'][0]['username']);
                        $GLOBALS['template']->assign('contentLoad', './templates/content/report/report_main.tpl');
                    } else {
                        $GLOBALS['page']->Message("The message has already been reported", 'Report System', 'id=3');
                    }
                } else {
                    $GLOBALS['page']->Message("The reported user could not be found", 'Report System', 'id=3');
                }
            } else {
                $GLOBALS['page']->Message("The message you are trying to report doesn't exist, or the PM does not belong to you", 'Report System', 'id=3');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid PM", 'Report System', 'id=3');
        }
    }

    // File PM report
    private function file_PM_report() {
        if (isset($_GET['pmid']) && is_numeric($_GET['pmid']) && isset($_GET['uid'])) {
            $message = $GLOBALS['database']->fetch_data("SELECT `users_pm`.* FROM `users_pm` WHERE `time` = '" . $_GET['pmid'] . "' AND `sender_uid` = '" . $_GET['uid'] . "' AND `receiver_uid` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
            if ($message != '0 rows') {
                if (strtolower($message[0]['sender_uid']) != strtolower($_SESSION['uid'])) {
                    $test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '" . $message[0]['sender_uid'] . "' AND `message` = '" . addslashes($message[0]['message']) . "'");
                    if ($test[0]['count'] == 0) {
                        if ($_POST['reason'] != 'other' && $_POST['reason'] != '') {
                            $reason = $_POST['reason'];
                        } elseif ($_POST['reason_text'] != '') {
                            $reason = functions::store_content($_POST['reason_text']);
                        }
                        if ($reason != '') {
                            $temp = 'Subject: "' . $message[0]['subject'] . '"<br>Message: "' . $message[0]['message'] . '"';
                            $user = $GLOBALS['database']->fetch_data("SELECT `id`,`username` FROM `users` WHERE `id` = '" . $message[0]['sender_uid'] . "' LIMIT 1");
                            $previousreport = $GLOBALS['database']->fetch_data("SELECT * FROM `user_reports` WHERE `message` = '" . addslashes($temp) . "' AND `uid` = '" . $user[0]['id'] . "'  LIMIT 1");
                            if ($previousreport == '0 rows') {
                                $GLOBALS['database']->execute_query(" INSERT INTO `user_reports` ( `time` , `uid` , `rid` , `reason` , `message` , `status` , `processed_by` , `type`, `mt` )VALUES ('" . $GLOBALS['user']->load_time . "', '" . $user[0]['id'] . "', '" . $_SESSION['uid'] . "', '" . $reason . "', '" . addslashes($temp) . "', 'unviewed', '', 'PM', ".$_GET['pmid'].") ");
                                $GLOBALS['page']->Message("Your report has been submitted, a moderator will review it as soon as possible.", 'Report System', 'id=3');
                            } else {
                                $GLOBALS['page']->Message("The message has already been reported", 'Report System', 'id=3');
                            }
                        } else {
                            $GLOBALS['page']->Message("You did not submit a valid reason for reporting this user /  message", 'Report System', 'id=3');
                        }
                    } else {
                        $GLOBALS['page']->Message("This message was already reported", 'Report System', 'id=3');
                    }
                } else {
                    $GLOBALS['page']->Message("You cannot report yourself", 'Report System', 'id=3');
                }
            } else {
                $GLOBALS['page']->Message("The message you are trying to report doesn't exist", 'Report System', 'id=3');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid message", 'Report System', 'id=3');
        }
    }

    // User report
    private function user_report() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $message = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($message != '0 rows') {

                // Fix up array for template
                $message[0]['message'] = "";
                $message[0]['type'] = "";
                $message[0]['user'] = $message[0]['username'];

                // Show template
                $GLOBALS['template']->assign('message', $message);
                $GLOBALS['template']->assign('reportBy', $GLOBALS['userdata'][0]['username']);
                $GLOBALS['template']->assign('contentLoad', './templates/content/report/report_main.tpl');
            } else {
                $GLOBALS['page']->Message("The user you are trying to report doesn't exist", 'Report System', 'id=13');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid user", 'Report System', 'id=13');
        }
    }

    // File user report
    private function file_user_report() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            if ($_GET['uid'] != $_SESSION['uid']) {
                $message = $GLOBALS['database']->fetch_data("SELECT `id`,`username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                if ($message != '0 rows') {
                    if ($_POST['reason'] != 'other' && $_POST['reason'] != '') {
                        $reason = $_POST['reason'];
                    } elseif ($_POST['reason_text'] != '') {
                        $reason = functions::store_content($_POST['reason_text']);
                    }
                    $test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '" . $message[0]['id'] . "' AND `reason` = '" . addslashes($reason) . "' AND `type` = 'user'");
                    if ($test[0]['count'] == 0) {
                        if ($reason != '') {
                            $GLOBALS['database']->execute_query(" INSERT INTO `user_reports`
                                ( `time` , `uid` , `rid` , `reason` ,
                                  `status` , `processed_by` , `type`
                                ) VALUES (
                                  '" . $GLOBALS['user']->load_time . "', '" . $_GET['uid'] . "',
                                  '" . $_SESSION['uid'] . "', '" . $reason . "',
                                  'unviewed', '', 'user') ");
                            $GLOBALS['page']->Message("Your report has been submitted, a moderator will review it as soon as possible", 'Report System', 'id=13');
                        } else {
                            $GLOBALS['page']->Message("You did not submit a valid reason for reporting this user /  message", 'Report System', 'id=13');
                        }
                    } else {
                        $GLOBALS['page']->Message("This message was already reported.", 'Report System', 'id=13');
                    }
                } else {
                    $GLOBALS['page']->Message("The user you are trying to report doesn't exist", 'Report System', 'id=13');
                }
            } else {
                $GLOBALS['page']->Message("You cannot report yourself", 'Report System', 'id=13');
            }
        } else {
            $GLOBALS['page']->Message("You are trying to report an invalid user", 'Report System', 'id=13');
        }
    }

}

new report();