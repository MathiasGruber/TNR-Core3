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
<?php class online {
    private $userdata;
    private $usercount;

    function __construct() {
        $this->show_user_count();
    }

    function show_user_count() {
        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            $min = $newminm = 0;
            $newmini = 20;

            if(isset($_GET['min']) && ctype_digit($_GET['min'])) {
                if((int)$_GET['min'] >= 20) {
                    $min = (int)$_GET['min'];
                    $newminm = $min - 20;
                    $newmini = $min + 20;
                }
            }

            if(!($this->usercount = $GLOBALS['database']->fetch_data('SELECT `site_timer`.`character_cleanup`,
                COUNT(`users_timer`.`userid`) AS `user_count`,
                COUNT(`mod_users`.`uid`) AS `mod_count`
                FROM `site_timer`
                    LEFT JOIN `users_timer` ON (`users_timer`.`last_activity` >= '.($GLOBALS['user']->load_time - 300).')
                    LEFT JOIN `users_statistics` AS `mod_users` ON (`mod_users`.`uid` = `users_timer`.`userid` AND
                        `mod_users`.`user_rank` IN("Moderator", "Supermod"))
                WHERE `site_timer`.`script` = "online_users" LIMIT 1'))) {
                throw new Exception("There was an issue trying to obtain User Count Data!");
            }

            if(!($this->userdata = $GLOBALS['database']->fetch_data('SELECT `users`.`username`
                FROM `users_timer`
                    INNER JOIN `users` ON (`users`.`id` = `users_timer`.`userid`)
                WHERE `users_timer`.`last_activity` >= '.($GLOBALS['user']->load_time - 300).'
                    ORDER BY `users`.`username` ASC LIMIT '.$min.', 20'))) {
                throw new Exception("There was an issue trying to obtain User Data!");
            }

            if($this->usercount[0]['character_cleanup'] < $this->usercount[0]['user_count']) {
                $GLOBALS['database']->execute_query('UPDATE `site_timer`
                    SET `site_timer`.`character_cleanup` = "'.(int)$this->usercount[0]['user_count'].'"
                    WHERE `site_timer`.`script` = "online_users" LIMIT 1');
                $this->usercount[0]['character_cleanup'] = (int)$this->usercount[0]['user_count'];
            }

            $GLOBALS['template']->assign('users_count', (int)$this->usercount[0]['user_count']);
            $GLOBALS['template']->assign('mod_count', (int)$this->usercount[0]['mod_count']);
            $GLOBALS['template']->assign('max_users', (int)$this->usercount[0]['character_cleanup']);
            $GLOBALS['template']->assign('user', $this->userdata);
            $GLOBALS['template']->assign('newminm', $newminm);
            $GLOBALS['template']->assign('newmini', $newmini);
            $GLOBALS['template']->assign('contentLoad', './templates/content/online/online_show_users.tpl');

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch(Exception $e) {
            $GLOBALS['page']->Message($e->getMessage(), 'Users Online Error', 'id='.$_GET['id']);
        }
    }
}
new online();