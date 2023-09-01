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

        public function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Choose Between IP Check Form or IP Check Submission
                (!isset($_POST['Submit'])) ? self::ipCheckForm() : self::ipCheckDo();
            }
            catch (Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "IP Check", 'id='.$_REQUEST['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        private function ipCheckForm() {
            // Create the input form
            $GLOBALS['page']->UserInput("Here you can do IP checks on usernames or user IDs. To search directly on IP, use the users module of the admin panel.", "IP Check",
                array(
                    // IP Check Search Value
                    array(
                        "inputFieldName" => "ip_search",
                        "type" => "input",
                        "inputFieldValue" => ""
                    ),
                    // IP Check Search Selection
                    array(
                        "inputFieldName" => "ip_select",
                        "type" => "select",
                        "inputFieldValue" => array(
                            'Username' => 'Username',
                            'UID' => 'UID',
                            'IP Address' => 'IP Address'
                        )
                    )
                ),
                array(
                    "href" => "?id=".$_REQUEST['id'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                "?id=".$_GET['id'],
                "hireModForm"
            );
        }

        private function ipCheckDo() {

            if(!isset($_POST['ip_select'], $_POST['ip_search'])) {
                throw new Exception('One or both of the selections were missing!');
            }
            elseif(!in_array($_POST['ip_select'], array('Username', 'UID', 'IP Address'), true)) {
                throw new Exception ('The IP Search selection was an invalid choice!');
            }
            elseif(functions::ws_remove($_POST['ip_search']) === '') {
                throw new Exception('No proper or valid search value was specified!');
            }

            switch($_POST['ip_select']) {
                case('Username'): {
                    $condition = '`users`.`username` = "' . $_POST['ip_search'] . '"';
                    self::userSearch($condition);
                } break;
                case('UID'): {
                    if (ctype_digit($_POST['ip_search']) === false) {
                        throw new Exception('UID is not all numbers!');
                    }
                    $condition = '`users`.`id` = "' . $_POST['ip_search'] . '"';
                    self::userSearch($condition);
                } break;
                case('IP Address'): {
                    if(!(filter_var($_POST['ip_search'], FILTER_VALIDATE_IP))) { // Validate IPv4 and IPv6 Format
                        throw new Exception('Not a valid IP Address');
                    }
                    self::ipSearch(trim($_POST['ip_search']));
                } break;
            }
        }

        private function userSearch($condition) {

            // Return all consolidated data based on search of Original User's Last/Join/Past IP address
            if(!($user_search = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`,
                `users`.`join_ip`, `users`.`last_ip`, `users`.`past_IPs`, `users`.`perm_ban`
                FROM `users`
                WHERE '.$condition.' LIMIT 1'))) {
                throw new Exception('User Search Failed. Non-existant user or Incorrect username!');
            }
            elseif($user_search === '0 rows') {
                throw new Exception('User Search Failed. Non-existant user or Incorrect username!');
            }

            if(!($join_ip_search = $GLOBALS['database']->fetch_data('SELECT `join_users`.`username`, `join_users`.`id`,
                `join_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `join_users` ON (`join_users`.`join_ip` = `users`.`join_ip` AND `join_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Join IP Check Failed!');
            }

            if(!($last_ip_search = $GLOBALS['database']->fetch_data('SELECT `last_users`.`username`, `last_users`.`id`,
                `last_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `last_users` ON (`last_users`.`last_ip` = `users`.`last_ip` AND `last_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Last IP Check Failed!');
            }

            if(!($join_past_ip_search = $GLOBALS['database']->fetch_data('SELECT `old_join_users`.`username`, `old_join_users`.`id`,
                `old_join_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `old_join_users` ON (`old_join_users`.`past_IPs` LIKE CONCAT("%", `users`.`join_ip`, "%")
                        AND `old_join_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Past Join IP Check Failed!');
            }

            if(!($last_past_ip_search = $GLOBALS['database']->fetch_data('SELECT `old_past_users`.`username`, `old_past_users`.`id`,
                `old_past_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `old_past_users` ON (`old_past_users`.`past_IPs` LIKE CONCAT("%", `users`.`last_ip`, "%")
                        AND `old_past_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Past Last IP Check Failed!');
            }

            $ips = explode("|||",$user_search[0]['past_IPs']);
            $ips[] = $user_search[0]['last_ip'];
            $ips[] = $user_search[0]['join_ip'];
            $check_join_ip = "`join_ip` in ('".implode("','",$ips)."')";
            $check_last_ip = "`last_ip` in ('".implode("','",$ips)."')";
            $past_ips = array();
            foreach($ips as $key => $ip)
                if($ip != "")
                    $past_ips[$key] = "`past_IPs` LIKE '%{$ip}%'";
                else
                    unset($ips[$key]);

            $query = "SELECT `username`, `id`, `perm_ban`, `join_ip`, `last_ip`, `past_ips`
                        FROM `users`
                        WHERE 
                            (".implode(' OR ',$past_ips)." OR {$check_join_ip} or {$check_last_ip})
                            AND `join_ip` != ''
                            and `Last_ip` != ''
                            AND `users`.`id` != ".$user_search[0]['id'];

            if(!($super_search = $GLOBALS['database']->fetch_data($query))) {
                throw new Exception('Past Last IP Check Failed!');
            }

            $GLOBALS['template']->assign('user', $user_search);
            $GLOBALS['template']->assign('join_IPs', $join_ip_search);
            $GLOBALS['template']->assign('last_IPs', $last_ip_search);
            $GLOBALS['template']->assign('last_past_IPs', $last_past_ip_search);
            $GLOBALS['template']->assign('last_join_IPs', $join_past_ip_search);
            $GLOBALS['template']->assign('super_search', $super_search);
            $GLOBALS['template']->assign('super_search_ips', implode(', ',$ips));
            

            $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/ip_check/showData.tpl');

        }

        private function ipSearch($ip_addr) {

            // Return all consolidated data based on search of Original User's Last/Join/Past IP address
            if(!($join_ip_search = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`, `users`.`perm_ban`
                FROM `users` WHERE `users`.`join_ip` = "'.$ip_addr.'"'))) {
                throw new Exception('Join IP Check Failed!');
            }

            if(!($last_ip_search = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`, `users`.`perm_ban`
                FROM `users`
                WHERE `users`.`last_ip` = "'.$ip_addr.'"'))) {
                throw new Exception('Last IP Check Failed!');
            }

            if(!($past_ip_search = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`,
                `users`.`perm_ban`
                FROM `users`
                WHERE `users`.`past_IPs` LIKE CONCAT("%", "'.$ip_addr.'", "%")'))) {
                throw new Exception('Past IP Check Failed!');
            }

            $GLOBALS['template']->assign('ip_addr', $ip_addr);
            $GLOBALS['template']->assign('join_IPs', $join_ip_search);
            $GLOBALS['template']->assign('last_IPs', $last_ip_search);
            $GLOBALS['template']->assign('past_IPs', $past_ip_search);

            $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/ip_check/showIPData.tpl');

        }

    }

    new module();