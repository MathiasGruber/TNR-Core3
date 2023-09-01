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

class hijackBack {

    public function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        if (isset($_SESSION["backData"])) {
            $hash = hash("sha512", "secretTadaaa" . $_SESSION['backData'][0]);
            if ($hash == $_SESSION['backData'][1]) {
                $user = $GLOBALS['database']->fetch_data("SELECT `users`.`username` FROM `users` WHERE `users`.`id` = '" . $_SESSION['backData'][0] . "' LIMIT 1");

                $GLOBALS['page']->Message("You have been logged in as: " . $user[0]['username'], 'Hijack User', 'id=' . $_GET['id']);

                // Set session
                $_SESSION['uid'] = $_SESSION['backData'][0];
                unset($_SESSION['backData']);

                // Update User
                $GLOBALS['database']->execute_query("UPDATE `users` SET `login_id` = '" . $_COOKIE['PHPSESSID'] . md5($user[0]['username'] . "xXx") . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
            } else {
                $GLOBALS['page']->Message("Session details are invalid.", 'Hijack User', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You do not have permission to access this page.", 'User Privileges', 'id=' . $_GET['id']);
        }

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }
}

new hijackBack();