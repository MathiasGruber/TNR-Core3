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
                self::mass_ip_check();
            }
            catch (Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "IP Check", 'id='.$_REQUEST['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        private function mass_ip_check() {

            // Return all consolidated data based on search of Original User's Last/Join/Past IP address
            if(!($users = $GLOBALS['database']->fetch_data(
                "SELECT * FROM 
                (
                    SELECT `users`.`id`,`users`.`username`, count(`users`.`username`) as `count`, GROUP_CONCAT(`users2`.`username`) as `names` 
                        FROM `users` 
                        inner join `users` as `users2` on 
                        (
                            (
                                `users2`.`join_ip` = `users`.`join_ip`
                                OR
                                `users2`.`join_ip` = `users`.`last_ip`
                                OR
                                `users2`.`last_ip` = `users`.`join_ip`
                                OR
                                `users2`.`last_ip` = `users`.`last_ip`
                                OR
                                `users2`.`past_ips` = CONCAT('%', `users`.`join_ip`, '%')
                                OR
                                `users2`.`past_ips` = CONCAT('%', `users`.`last_ip`, '%')
                            )
                            AND
                            `users2`.`id` != `users`.`id`
                            AND
                            `users`.`perm_ban` in (0,'0')
                            AND
                            `users2`.`perm_ban` in (0,'0')
                            AND
                            `users`.`logout_timer` >= (UNIX_TIMESTAMP() - (60*60*24*30))
                            AND
                            `users2`.`logout_timer` >= (UNIX_TIMESTAMP() - (60*60*24*30))
                        )
                        inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`)
                        inner join `users_statistics` as `users_statistics2` on (`users2`.`id` = `users_statistics2`.`uid`)
                        WHERE `users_statistics`.`user_rank` != 'admin' and `users_statistics2`.`user_rank` != 'admin'
                        GROUP BY `users`.`username`
                ) as `thang`
                WHERE `count` >= 2"))) {
                throw new Exception('User Search Failed. Non-existant user or Incorrect username!');
            }
            elseif($users === '0 rows') {
                throw new Exception('User Search Failed. Non-existant user or Incorrect username!');
            }

            
            tableParser::show_list(
                'Mass_Ip_Check',
                'Mass Ip Check',
                $users,
                array(
                    'username' => "User Name",
                    "count" => "Count",
                    "names" => "List"
                ),
                false,
                true
            );

        }
    }

    new module();