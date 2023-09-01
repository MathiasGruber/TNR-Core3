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
 * 							Kings and queens
 * 		Simple "guess the card's location" game, boring as hell
 */

class kings {

    private $output_buffer;
    private $user;
    private $cards = array('C11', 'C12', 'C13');

    public function kings() {
        $this->output_buffer .= '<div style="text-align:center;color:darkred;font-weight:bold;">Notice: For some strange reason this game only works under firefox. We apologise for the inconvenience.</div>';
        $this->getUserData();
        if (!isset($_POST['location'])) {
            $this->do_bet();
        } else {
            $this->check_bet();
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
        <td colspan="3" align="center" style="border-top:none;" class="subHeader">Kings and queens </td>
      	</tr><tr><td colspan="3" align="center"><i>The bet is always 5 ryo.</i></td></tr><tr>
        <td colspan="3" align="center">The goal is to pick the queen out of the three cards.</td></tr><tr>
        <td colspan="3" align="center"><b>Pick a card:</b></td></tr><tr>
        <td width="33%" align="center" style="padding-top:5px;padding-bottom:5px;"><input type="image" name="location" value="0" src="./images/casino/cards/Bsmall.png" style="height:209px; width:150px; border:none; background:none;"></td>
        <td width="33%" align="center" style="padding-top:5px;padding-bottom:5px;"><input type="image" name="location" value="1" src="./images/casino/cards/Bsmall.png" style="height:209px; width:150px; border:none; background:none;"></td>
        <td width="33%" align="center" style="padding-top:5px;padding-bottom:5px;"><input type="image" name="location" value="2" src="./images/casino/cards/Bsmall.png" style="height:209px; width:150px; border:none; background:none;"></td>
      	</tr></table></form><br><br></div>';
    }

    private function check_bet() {
        if ($this->user[0]['money'] >= 10) {
            if (is_numeric($_POST['location']) && $_POST['location'] >= 0 && $_POST['location'] <= 2) {
                shuffle($this->cards);
                if ($this->cards[$_POST['location']] == 'C12') {
                    // Player wins
                    $outcome = 'You guessed right and won 10 ryo';
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` + 5 WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                } elseif ($this->cards[$_POST['location']] == 'C11') {
                    //	Player loses double their bet
                    $outcome = 'You guessed wrong and lost 10 ryo';
                    //	Deduct extra bet
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` - 10 WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                } else {
                    // Player loses
                    $outcome = 'You guessed wrong and lost 5 ryo';
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` - 5 WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                }
                $this->output_buffer .= '<div align="center"><br>
				<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        		<td colspan="3" align="center" style="border-top:none;" class="subHeader">Kings and queens </td>
      			</tr><tr><td colspan="3" align="center"><b>outcome</b></td></tr><tr><td width="33%" align="center" style="padding-top:5px;padding-bottom:5px;">';
                if ($_POST['location'] == 0) {
                    // Red border
                    $this->output_buffer .= '<img src="./images/casino/cards/' . $this->cards[0] . '.png" style="border:2px solid red; height:216px; width:155px;">';
                } else {
                    $this->output_buffer .= '<img src="./images/casino/cards/' . $this->cards[0] . '.png" style="height:216px; width:155px;">';
                }
                $this->output_buffer .= '</td>
        		<td width="33%" align="center" style="padding-top:5px;padding-bottom:5px;">';
                //	Card 2
                if ($_POST['location'] == 1) {
                    // Red border
                    $this->output_buffer .= '<img src="./images/casino/cards/' . $this->cards[1] . '.png" style="border:2px solid red; height:216px; width:155px;">';
                } else {
                    $this->output_buffer .= '<img src="./images/casino/cards/' . $this->cards[1] . '.png" style="height:216px; width:155px;">';
                }
                $this->output_buffer .= '</td>
        		<td width="33%" align="center" style="padding-top:5px;padding-bottom:5px;">';
                //	Card 3
                if ($_POST['location'] == 2) {
                    // Red border
                    $this->output_buffer .= '<img src="./images/casino/cards/' . $this->cards[2] . '.png" style="border:2px solid red; height:216px; width:155px;">';
                } else {
                    $this->output_buffer .= '<img src="./images/casino/cards/' . $this->cards[2] . '.png" style="height:216px; width:155px;">';
                }
                $this->output_buffer .= '</td></tr><tr>
        		<td colspan="3" align="center">' . $outcome . '</td></tr><tr>
        		<td colspan="3" align="center"><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Play again</a></td>
        		</tr></table><br><br></div>';
            } else {
                //	Invalid guess data
                $this->output_buffer .= '<div align="center" style="padding:4px;">Invalid bet-data was detected, please try playing again. <br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Return</a></div>';
            }
        } else {
            //	Not enough ryo
            $this->output_buffer .= '<div align="center" style="padding:4px;">You do not have enough ryo left to play this game. <br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Return</a></div>';
        }
    }

}
