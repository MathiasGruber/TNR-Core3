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

    class bank {

        private $user;
        private $bank_name;

        public function __construct() {

            try {

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                if($GLOBALS['userdata'][0]['village'] == 'Syndicate')
                    $this->bank_name = "Strongbox";

                else if($GLOBALS['userdata'][0]['village'] == 'Konoki')
                    $this->bank_name = "Bank of Konoki";

                else if($GLOBALS['userdata'][0]['village'] == 'Shine')
                    $this->bank_name = "Bank of Shine";

                else if($GLOBALS['userdata'][0]['village'] == 'Shroud')
                    $this->bank_name = "Bank of Shroud";

                else if($GLOBALS['userdata'][0]['village'] == 'Samui')
                    $this->bank_name = "Bank of Samui";

                else if($GLOBALS['userdata'][0]['village'] == 'Silence')
                    $this->bank_name = "Bank of Silence";

                if(!isset($_POST['Submit'])) {
                    self::main_screen();
                }
                else {

                    $ryo_amount = false;
                    if( isset($_POST['action']) ){
                        switch($_POST['action']) {
                            case('send'): $ryo_amount = isset($_POST['sendAmount']) ? $_POST['sendAmount'] : 0; break;
                            default: $ryo_amount = isset($_POST['amount']) ? $_POST['amount'] : 0; break;
                        }
                    }


                    if($ryo_amount == false) {
                        throw new Exception("You did not enter an amount!");
                    }
                    else {
                        $ryo_amount = functions::ws_remove($ryo_amount);
                        if(empty($ryo_amount)) {
                            throw new Exception("You did not enter an amount!");
                        }
                    }

                    if($ryo_amount <= 0) {
                        throw new Exception("You cannot store/withdraw negative amounts or send nothing!");
                    }
                    elseif(!ctype_digit($ryo_amount)) {
                        throw new Exception('You must use a numeric value to specify ryo amounts!');
                    }
                    elseif(!isset($_POST['action'])) {
                        throw new Exception("You did not specify whether to deposit/withdraw!");
                    }

                    switch($_POST['action']) {
                        case('deposit'): self::deposit($ryo_amount); break;
                        case('withdraw'): self::withdraw($ryo_amount); break;
                        case('send'): {

                            if(!isset($_POST['target_username']) || functions::ws_remove($_POST['target_username']) === '') {
                                throw new Exception('You did not specify a recipient!');
                            }

                            self::send($ryo_amount);

                        } break;
                        default: throw new Exception("An action was performed that was not available!"); break;
                    }

                }

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }
            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), $this->bank_name, 'id='.$_GET['id'], 'Return');
            }
        }

        private function getUsrData($update = false) {

            if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users_statistics`.`money`, `users_statistics`.`bank`,
                `users_statistics`.`rank_id`, `users_statistics`.`uid`, `users_loyalty`.`village`, `users`.`username`
                FROM `users_statistics`
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users_statistics`.`uid`)
                    INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                WHERE `users_statistics`.`uid` = '.$_SESSION['uid'].' LIMIT 1 '.(($update === true) ? 'FOR UPDATE': '')))) {
                throw new Exception('There was an error trying to obtain necessary user information!');
            }
            elseif($this->user === '0 rows') {
                throw new Exception('There is no user information available!');
            }
        }

        private function deposit($ryo) {

            $GLOBALS['database']->transaction_start();

            self::getUsrData(true);

            if($this->user[0]['money'] < $ryo) {
                throw new Exception('You do not have enough ryo to deposit to the '.$this->bank_name.'!');
            }
            elseif(($this->user[0]['bank'] + $ryo) > Data::$MAX_BANK) {
                throw new Exception('You cannot hold more than '.Data::$MAX_BANK.' ryo in the '.$this->bank_name.'!');
            }

            if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
                SET `users_statistics`.`bank` = `users_statistics`.`bank` + '".$ryo."',
                    `users_statistics`.`money` = `users_statistics`.`money` - '".$ryo."'
                WHERE `users_statistics`.`uid` = '".$this->user[0]['uid']."' LIMIT 1") === false) {
                throw new Exception('An error occurred while depositing your ryo, please try again.');
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('deposit', array('data'=>$ryo));
            }

            $GLOBALS['page']->Message('You have deposited '.$ryo.' ryo.', $this->bank_name, 'id='.$_GET['id'].'');
            $this->user[0]['money'] -= $ryo;
            $this->user[0]['bank'] += $ryo;

            $GLOBALS['database']->transaction_commit();
        }

        private function withdraw($ryo) {

            $GLOBALS['database']->transaction_start();

            self::getUsrData(true);

            if($this->user[0]['bank'] < $ryo) {
                throw new Exception('You do not have enough ryo to withdraw from the '.$this->bank_name.'!');
            }
            elseif(($this->user[0]['money'] + $ryo) > Data::$MAX_BANK) {
                throw new Exception('You cannot hold more than '.Data::$MAX_BANK.' ryo in your pocket!');
            }

            if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
                SET `users_statistics`.`bank` = `users_statistics`.`bank` - '".$ryo."',
                    `users_statistics`.`money` = `users_statistics`.`money` + '".$ryo."'
                WHERE `users_statistics`.`uid` = '".$this->user[0]['uid']."' LIMIT 1") === false) {
                throw new Exception('An error occurred while withdrawing your ryo, please try again.');
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('withdraw', array('data'=>$ryo));
            }

            $GLOBALS['page']->Message('You have withdrawn '.$ryo.' ryo.', $this->bank_name, 'id='.$_GET['id'].'');
            $this->user[0]['money'] += $ryo;
            $this->user[0]['bank'] -= $ryo;

            $GLOBALS['database']->transaction_commit();
        }

        private function send($ryo) {

            $GLOBALS['database']->transaction_start();

            self::getUsrData(true);

            if($this->user[0]['rank_id'] < 2) {
                throw new Exception('You cannot send money as an academy student.');
            }
            elseif($this->user[0]['money'] < $ryo) {
                throw new Exception('You do not have enough ryo to do send to a user!');
            }
            elseif($ryo > 50000000) {
                throw new Exception('You cannot transfer that much ryo in one transaction.');
            }
            elseif($this->user[0]['username'] === $_POST['target_username']) {
                 throw new Exception('You cannot send ryo to yourself!');
            }


            if(!($target_user = $GLOBALS['database']->fetch_data("SELECT `users_statistics`.`uid`, `users_statistics`.`bank`,
                `users`.`ryoCheckLimit`, `users`.`username`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                WHERE `users`.`username` = '".$_POST['target_username']."' LIMIT 1 FOR UPDATE"))) {
                throw new Exception('There was an issue trying to obtain the targeted user information');
            }
            elseif($target_user === '0 rows') {
                throw new Exception('The targeted user does not exist within the system!');
            }
            elseif($target_user[0]['bank'] + $ryo > Data::$MAX_BANK) {
                throw new Exception("The amount of ryo you are sending is over the limit of the user's ".$this->bank_name."!");
            }


            if($GLOBALS['database']->execute_query("UPDATE `users_statistics` AS `sender`
                    INNER JOIN `users_statistics` AS `receiver` ON (`receiver`.`uid` = ".$target_user[0]['uid'].")
                    INNER JOIN `users` ON (`users`.`id` = `receiver`.`uid`)
                SET `sender`.`money` = `sender`.`money` - '".$ryo."',
                    `receiver`.`bank` = `receiver`.`bank` + '".$ryo."'
                WHERE `sender`.`uid` = '".$this->user[0]['uid']."'") === false) {
                throw new Exception('An error occurred while sending your ryo, please try again.');
            }

            $users_notifications = new NotificationSystem('', $target_user[0]['uid']);

            $users_notifications->addNotification(array(
                                                        'id' => 7,
                                                        'duration' => 'none',
                                                        'text' => $this->user[0]['username']." has sent you ".$ryo." ryo!",
                                                        'dismiss' => 'yes'
                                                    ));

            $users_notifications->recordNotifications();

            // Ryo Tracker
            if($ryo >= 10000 || (($target_user[0]['ryoCheckLimit'] !== '0') && ($ryo > $target_user[0]['ryoCheckLimit']))) {
                if($GLOBALS['database']->execute_query("INSERT INTO `ryo_track`
                        (`uid`, `time`, `r_uid`, `receiver`, `amount`, `s_uid`, `sender`)
                    VALUES
                        ('".$this->user[0]['uid']."', UNIX_TIMESTAMP(), '".$target_user[0]['uid']."',
                            '".$target_user[0]['username']."', '".$ryo."', '".$this->user[0]['uid']."',
                            '".$this->user[0]['username']."')") === false) {
                    throw new Exception('There was an issue trying to send your ryo information!');
                }
            }

            $this->user[0]['money'] -= $ryo;
            $GLOBALS['page']->Message('You have sent '.$ryo.' ryo to '.$target_user[0]['username'].'!', $this->bank_name, 'id='.$_GET['id'].'');

            $GLOBALS['database']->transaction_commit();
        }

        private function main_screen() {
            self::getUsrData();
            $GLOBALS['template']->assign('user', $this->user);
            $GLOBALS['template']->assign('bank_name', $this->bank_name);
            $GLOBALS['template']->assign('contentLoad', './templates/content/bank/bank_main.tpl');
        }
    }

    new bank();