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
class lottery {
    private $data;
    private $tickets_left;

    // GLOBALS
    private $JACKPOT = 300;
    private $NORMAL = 100;
    private $TOTAL_TICKETS = 200000;
    private $TICKET_USER_MAX = 500;

    function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        // Determine action
        (!isset($_POST['Submit'])) ? $this->main_screen() : $this->buy_tickets();


        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }

    }

    // Pages
    private function buy_tickets() {
        try {
            $GLOBALS['database']->transaction_start();
            if(!($this->data = $GLOBALS['database']->fetch_data('SELECT
                `users_statistics`.`uid` AS `user_id`, `users_statistics`.`money` AS `user_money`,
                `site_timer`.`pvp_reset_timer` AS `lottery_timer`
                FROM `users_statistics`
                    INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid` AND `users`.`status` = "awake")
                    INNER JOIN `site_timer` ON (`site_timer`.`script` = "lottery_timer")
                WHERE `users_statistics`.`uid` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE'))) {
                throw new Exception('1');
            }

            if(!($lottery_data = $this->lottery_info())) {
                throw new Exception('2');
            }

            $this->tickets_left = $this->TOTAL_TICKETS - $lottery_data[0]['tickets_bought'];
            $user_tickets = $lottery_data[0]['user_tickets'];

            if(is_numeric($_POST['tickets']) && $_POST['tickets'] > 0) {
                if(isset($_POST['jackpot'])) {
                    $cost = ceil($_POST['tickets']) * (($_POST['jackpot'] === "yes") ? $this->JACKPOT : $this->NORMAL);
                    if(($user_tickets + ceil($_POST['tickets'])) <= $this->TICKET_USER_MAX) {
                        if($cost <= $this->data[0]['user_money']) {
                            if(ceil($_POST['tickets']) < $this->tickets_left) {
                                if(ceil($_POST['tickets']) <= '100') {
                                    $values = '';
                                    // Build the Row Insertions for the Tickets
                                    for($i = 0; $i < ceil($_POST['tickets']); $i++) {
                                        $values .= ($i !== (int)(ceil($_POST['tickets']) - 1)) ?
                                            ('('.$this->data[0]['user_id'].', "'.$_POST['jackpot'].'"), ')
                                            : ('('.$this->data[0]['user_id'].', "'.$_POST['jackpot'].'")');
                                    }

                                    // Insert all tickets at once
                                    if(($GLOBALS['database']->execute_query('INSERT INTO `lottery` (`userid`, `jackpot`) VALUES '.$values)) === false) {
                                        throw new Exception('3');
                                    }

                                    // Check Lottery Timer in case Batch Process didn't check
                                    if($this->data[0]['lottery_timer'] < $GLOBALS['user']->load_time) {
                                        if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `site_timer`
                                            SET `users_statistics`.`money` = `users_statistics`.`money` - '.$cost.',
                                                `site_timer`.`pvp_reset_timer` = '.($GLOBALS['user']->load_time + 345600).'
                                            WHERE `users_statistics`.`uid` = '.$this->data[0]['user_id'].'
                                                AND `site_timer`.`script` = "lottery_timer"')) === false) {
                                            throw new Exception("4");
                                        }
                                        else
                                        {
                                            $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $cost));
                                        }
                                    }
                                    else {
                                        if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`
                                            SET `users_statistics`.`money` = `users_statistics`.`money` - '.$cost.'
                                            WHERE `users_statistics`.`uid` = '.$this->data[0]['user_id'].' LIMIT 1')) === false) {
                                            throw new Exception("5");
                                        }
                                        else
                                        {
                                            $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $cost));
                                        }
                                    }
                                    $GLOBALS['page']->Message("You have bought ".ceil($_POST['tickets'])." ticket(s) for ".$cost." ryo!", 'Lottery System', 'id='.$_GET['id'].'');
                                }
                                else { throw new Exception("6"); }
                            }
                            else { throw new Exception("7"); }
                        }
                        else { throw new Exception("8"); }
                    }
                    else { throw new Exception("9"); }
                }
                else { throw new Exception("10"); }
            }
            else { throw new Exception("11"); }
            $GLOBALS['database']->transaction_commit();
        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback("Lottery Error Message Exception: " . $e->getMessage());
            switch($e->getMessage()) {
                case("1"): case("2"): case("3"): case("4"): case("5"): {
                    $GLOBALS['page']->Message("An error occurred from ticket purchasing. Please try again!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                case("6"): {
                    $GLOBALS['page']->Message("You can only purchase 100 tickets at a time!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                case("7"): {
                    $GLOBALS['page']->Message("There's not enough tickets left to buy!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                case("8"): {
                    $GLOBALS['page']->Message("You do not have enough ryo to buy these tickets!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                case("9"): {
                    $GLOBALS['page']->Message("You can't purchase more than 500 tickets!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                case("10"): {
                    $GLOBALS['page']->Message("You have not specified the ticket you wanted to buy!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                case("11"): {
                    $GLOBALS['page']->Message("This is an invalid number of tickets!", 'Lottery System', 'id='.$_GET['id'].'');
                } break;
                default: $GLOBALS['page']->Message("An error occurred. Please try again!", 'Lottery System', 'id='.$_GET['id'].''); break;
            }
        }
    }

    private function main_screen() {
        // Fetch necessary data utilizing subqueries
        $this->data = $this->lottery_info();

        $this->tickets_left = $this->TOTAL_TICKETS - $this->data[0]['tickets_bought'];

        $GLOBALS['template']->assign('tickets', $this->tickets_left);
        $GLOBALS['template']->assign('User_tickets', $this->data[0]['user_tickets']);
        $GLOBALS['template']->assign('Jackpot', $this->JACKPOT);
        $GLOBALS['template']->assign('Normal', $this->NORMAL);
        $GLOBALS['template']->assign('contentLoad', './templates/content/lottery/lottery_main.tpl');
    }

    private function lottery_info() {
        return $GLOBALS['database']->fetch_data('SELECT COUNT(`lottery`.`id`) AS `user_tickets`,
            (SELECT COUNT(`lottery`.`id`) FROM `lottery`) AS `tickets_bought`
            FROM `lottery` WHERE `lottery`.`userid` = '.$_SESSION['uid']);
    }
}
new lottery;