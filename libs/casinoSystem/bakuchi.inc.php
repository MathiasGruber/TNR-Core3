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

/*
 * 						Cho / Han Bakuchi
 * 		Dice game, user bets ryo on the outcome of a double dice roll (even / uneven)
 */

class bakuchi {

    private $output_buffer;
    private $user;

    public function bakuchi() {
        $this->getUserData();
        if (!isset($_POST['bet'])) {
            $this->do_bet();
        } else {
            $this->bet_outcome();
        }
        $this->return_stream();
    }

    private function getUserData() {
        $this->user = $GLOBALS['database']->fetch_data("SELECT `money` FROM `users` WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
    }

    private function return_stream() {
        $GLOBALS['page']->insert_page_data('[CONTENT]', $this->output_buffer);
    }

    private function do_bet() {
        $this->output_buffer .= '<div align="center"><br>
  		<form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        <td colspan="2" align="center" style="border-top:none;" class="subHeader">Cho-Han Bakuchi</td>
      	</tr><tr>
        <td colspan="2" align="center">The goal of the game is to guess whether the outcome of the dice roll will be even, or uneven.</td>
      	</tr><tr><td colspan="2" align="center">You currently have: ' . $this->user[0]['money'] . ' ryo. </td></tr><tr>
        <td align="center" style="font-weight:bold;">Bet:</td><td align="center" style="font-weight:bold;">Payout:</td></tr>
      	<tr><td width="50%" align="center">5 ryo </td><td width="50%" align="center">10 ryo </td></tr><tr>
        <td colspan="2" align="center"><select name="bet"><option>even</option><option>uneven</option>
        </select>&nbsp;<input type="submit" name="Submit" value="Submit"></td></tr><tr>
        <td colspan="2" align="center">&nbsp;</td></tr></table></form><br><br></div>';
    }

    private function bet_outcome() {
        if ($this->user[0]['money'] >= 5) {
            if ($_POST['bet'] == 'even' || $_POST['bet'] == 'uneven') {
                $dice1 = random_int(1, 6);
                $dice2 = random_int(1, 6);
                $outcome = ($dice1 + $dice2 ) % 2;
                if ($dice1 == 1 && $dice2 == 1) {
                    $result = 'Snake eyes! you lose 10 ryo';
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` - 10 WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                } elseif (($outcome == 0 && $_POST['bet'] == 'even') || ($outcome != 0 && $_POST['bet'] == 'uneven')) {
                    //	 User wins
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` + 5 WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                    $result = 'You guessed correctly and won 10 ryo';
                } else {
                    //	 User loses
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` - 5 WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                    $result = 'You guessed wrong and lost 5 ryo';
                }
                $this->output_buffer .= '<div align="center"><br>
    			<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        		<td width="100%" colspan="2" align="center" style="border-top:none;" class="subHeader">Cho-Han Bakuchi</td>
      			</tr><tr><td colspan="2" align="center">Outcome:</td></tr><tr>
        		<td align="center" width="50%"><img src="./images/casino/dice/d' . $dice1 . '.png"></td>
        		<td align="center" width="50%"><img src="./images/casino/dice/d' . $dice2 . '.png"></td></tr><tr>
        		<td colspan="2" align="center">The outcome was: ';
                if ($outcome == 0) {
                    $this->output_buffer .= 'Even';
                } else {
                    $this->output_buffer .= 'Uneven';
                }
                $this->output_buffer .= '</td></tr><tr>
        	<td colspan="2" align="center">You guessed: ';
                if ($_POST['bet'] == 'even') {
                    $this->output_buffer .= 'Even';
                } else {
                    $this->output_buffer .= 'Uneven';
                }
                $this->output_buffer .='</td></tr><tr>
        	<td colspan="2" align="center">' . $result . '</td></tr><tr>
       	 	<td colspan="2" align="center"><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Play again</a></td>
      		</tr><tr><td colspan="2" align="center">&nbsp;</td></tr></table><br><br></div>';
            } else {
                //	Invalid bet
                $this->output_buffer .= '<div align="center" style="padding:4px;">You made an invalid bet, please try again <br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Return</a></div>';
            }
        } else {
            $this->output_buffer .= '<div align="center" style="padding:4px;">You do not have enough ryo left to play this game. <br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Return</a></div>';
        }
    }

}