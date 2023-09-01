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
	
    public function __construct() {
        (filter_input(INPUT_GET, 'act', FILTER_SANITIZE_STRING) === 'draw') ? $this->draw_lottery() : $this->main_page();
    }


    private function main_page() {
        try {
            // Collect # of Tickets and Prize Pot
            if(!($total = $GLOBALS['database']->fetch_data('SELECT COUNT(`lottery`.`id`) AS `tickets`, `site_information`.`value` AS `prize_pot` 
                FROM `lottery` 
                    INNER JOIN `site_information` ON (`site_information`.`option` = "lottery_pot") LIMIT 1'))) {
                throw new Exception('Lottery Main Page Ticket Count Failed!');
            }
            elseif($total === '0 rows') { // No Results Returned
                throw new Exception ('No Tickets were returned to main page!');
            }
            
            // Combined Total Winnings
            $prize = $total[0]['tickets'] * 150 + $total[0]['prize_pot'];

            $GLOBALS['page']->Message($total[0]['tickets'].' tickets have currently been sold<br>
                The prize pot contains '.$prize.' ryo and the jackpot contains '.$total[0]['prize_pot'].' ryo.', 
                'Lottery System', 'id='.filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)."&act=draw", "Draw the Lottery");   
        }
        catch (Exception $e) { 
            $GLOBALS['page']->Message('Main Lottery Page Failed to Load: '.$e->getMessage(), 
                'Lottery System', 'id='.filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT), "Return");   
        } 
    }

    private function draw_lottery() {
        try {
            $GLOBALS['database']->transaction_start();

            //	Fetch 3 Lottery Winners
            if(!($tickets = $GLOBALS['database']->fetch_data("SELECT DISTINCT `lottery`.`userid`, `lottery`.`jackpot`, `users`.`username`
                FROM `lottery`
                    INNER JOIN `users` ON (`users`.`id` = `lottery`.`userid`) 
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`) ORDER BY RAND() LIMIT 3 FOR UPDATE"))) {
                throw new Exception("User Fetch/Lock Failed!");
            }
            elseif($tickets === '0 rows') { // No Results Returned
                throw new Exception('Failed to Obtain Winners!');
            }
            elseif(count($tickets) !== 3) { // Failed to Return 3 Winners
                throw new Exception("Lottery didn't obtain 3 winners!");
            }

            // Collect # of Tickets and Prize Pot
            if(!($total = $GLOBALS['database']->fetch_data('SELECT COUNT(`lottery`.`id`) AS `tickets`, `site_information`.`value` AS `prize_pot` 
                FROM `lottery` 
                    INNER JOIN `site_information` ON (`site_information`.`option` = "lottery_pot") LIMIT 1'))) {
                throw new Exception('Lottery Ticket Count Failed!');
            }
            elseif($total === '0 rows') { // No Results Returned
                throw new Exception('Failed to Load Lottery Prize Pot!');
            }
            
            //	Calculate first prize
            //      Prize -> 50% of #Tickets * 150 * Multipler + Prize Pot (Jackpot Only)
            $prize_1 = round(0.5 * ($total[0]['tickets'] * 150 + (($tickets[0]['jackpot'] === 'yes') ? $total[0]['prize_pot'] : 0)));

            //	Calculate second prize
            //      Prize -> 30% of #Tickets * 150 * Multipler + Prize Pot (Jackpot Only)
            $prize_2 = round(0.3 * ($total[0]['tickets'] * 150 + (($tickets[1]['jackpot'] === 'yes') ? $total[0]['prize_pot'] : 0)));

            //	Calculate third prize
            //      Prize -> 20% of #Tickets * 150 * Multipler + Prize Pot (Jackpot Only)
            $prize_3 = round(0.2 * ($total[0]['tickets'] * 150 + (($tickets[2]['jackpot'] === 'yes') ? $total[0]['prize_pot'] : 0)));

            // News Message for Submission
            $news_message = "<b>Lottery Drawing Time!</b><br>
                The lottery has been drawn, and the prize winners are:<br><br>
                1st Prize: ".$tickets[0]['username']." wins ".$prize_1." Ryo".
                    (($tickets[0]['jackpot'] === 'yes') ? ' with the Prize Pot of '.($total[0]['prize_pot'] * 0.5).' Ryo!' : '!')."<br>
                2nd Prize: ".$tickets[1]['username']." wins ".$prize_2." Ryo".
                    (($tickets[1]['jackpot'] === 'yes') ? ' with the Prize Pot of '.($total[0]['prize_pot'] * 0.3).' Ryo!' : '!')."<br>
                3rd Prize: ".$tickets[2]['username']." wins ".$prize_3." Ryo".
                    (($tickets[2]['jackpot'] === 'yes') ? ' with the Prize Pot of '.($total[0]['prize_pot'] * 0.2).' Ryo!' : '!')."<br><br>
                Our congratulations to the winners! For those of you that didn't win, better luck next time.";

            // Update Prize Pot (1 Million Ryo Base) with Base or Add on # of Tickets * 150
            $pot_update = ($tickets[0]['jackpot'] === 'yes' || $tickets[1]['jackpot'] === 'yes' || $tickets[2]['jackpot'] === 'yes') ? 
                '1000000' : '`site_information`.`value` + '.(round($total[0]['tickets'] * 150));
            
            //	Submit News Page
            if($GLOBALS['database']->execute_query('INSERT INTO `news` 
                    (`id`, `title`, `posted_by`, `content`, `time`) 
                VALUES 
                    (NULL, "Lottery Winners!", "Lottery System", "'.addslashes($news_message).'", UNIX_TIMESTAMP())') === false) {
                throw new Exception("Lottery News Insertion Failed!");
            }

            // 	Mass Lottery Update
            if($GLOBALS['database']->execute_query('UPDATE `users_statistics` AS `user_1`
                INNER JOIN `users_statistics` AS `user_2` ON (`user_2`.`uid` = '.$tickets[1]['userid'].')
                INNER JOIN `users_statistics` AS `user_3` ON (`user_3`.`uid` = '.$tickets[2]['userid'].')
                INNER JOIN `site_information` ON (`site_information`.`option` = "lottery_pot")
                INNER JOIN `site_timer` ON (`site_timer`.`script` = "lottery_timer")
                SET `user_1`.`bank` = `user_1`.`bank` + '.$prize_1.', 
                    `user_2`.`bank` = `user_2`.`bank` + '.$prize_2.',
                    `user_3`.`bank` = `user_3`.`bank` + '.$prize_3.',
                    `site_information`.`value` = '.$pot_update.',
                    `site_timer`.`pvp_reset_timer` = UNIX_TIMESTAMP()
                WHERE `user_1`.`uid` = '.$tickets[0]['userid']) === false) {
                throw new Exception("Lottery Update Failed!");
            }

            //	Empty All Lottery Tickets
            if($GLOBALS['database']->execute_query("TRUNCATE TABLE `lottery`") === false) {
                throw new Exception('Lottery Deletion Failed!');
            }

            $GLOBALS['page']->Message('The lottery has been drawn and reset!', 
                'Lottery System', 'id='.filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
            
            $GLOBALS['database']->transaction_commit();
        }
        catch (Exception $e) { $GLOBALS['database']->transaction_rollback("Lottery Drawing Failed: " . $e->getMessage()); } 
    }
}

new lottery;