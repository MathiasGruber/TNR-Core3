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
 * 				Blackjack game library
 * 		Blackjack class is used in the casino to play blackjack
 */

class blackjack {

    //		Script function data
    private $output_buffer;
    private $user_data;

    //		Game data
    const DECKS = 2;   //	Number of decks

    private $player_cards;  //	Array with the players cards.
    private $player_value;  //	Value of the players cards
    private $player_ace;  //	Second value of the players cards, if Ace is in play
    private $dealer_cards;  //	Array with the dealers cards.
    private $dealer_value;  //	Value of the dealers cards.
    private $dealer_ace;  //	Second value of dealer cards, if Ace is in play
    private $bet;    //	Amount of ryo bet by the user.

    private function return_stream() {
        $GLOBALS['page']->insert_page_data('[CONTENT]', $this->output_buffer);
    }

    private function getUserData() {
        $this->user_data = $GLOBALS['database']->fetch_data("SELECT `money` FROM `users` WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
    }

    public function blackjack() {
        $this->generateCard('dealer');
        $this->generateCard('dealer');
        $this->generateCard('player');
        $this->generateCard('player');
        $this->run();
    }

    public function run() {
        $this->getUserData();
        if (!isset($this->bet)) {
            if (!isset($_POST['bet'])) {
                $this->set_bet();
            } else {
                $this->validate_bet();
            }
        } else {
            //	Do previous action
            if ($_POST['Submit'] == 'Hit') {
                $this->hit();
                if ($this->player_value > 21) {
                    $outcome = 'lose';
                }
            } elseif ($_POST['Submit'] == 'Stand') {
                $this->dealer();
                $outcome = $this->check_outcome();
            } elseif ($_POST['Submit'] == 'Surrender') {
                $this->surrender();
                $outcome = 'surrender';
            }
            //	Show outcomes
            $this->main_screen();
            if ($outcome == null) {
                $this->options();
            } elseif ($outcome == 'win') {
                $this->win();
            } elseif ($outcome == 'lose') {
                $this->lost();
            } elseif ($outcome == 'tie') {
                $this->tie();
            } elseif ($outcome == 'surrender') {

            }
        }
        $this->return_stream();
        if ($outcome == null) {
            $this->storeGame();
        }
    }

    public function __sleep() {
        $this->output_buffer = null;
        return array('player_cards', 'player_value', 'player_ace', 'dealer_cards', 'dealer_value', 'dealer_ace', 'bet');
    }

    //		Store the game in the session
    private function storeGame() {
        session_register('blackjack');
        $_SESSION['blackjack'] = serialize($this);
    }

    //		Clear the game from the session
    private function stopGame() {
        if (isset($_SESSION['blackjack'])) {
            session_unregister('blackjack');
        }
    }

    /*
     * 				Game functions
     */

    //	Check if the user can make the bet
    private function validate_bet() {
        if ($_POST['bet'] >= 0 && is_numeric($_POST['bet'])) {
            if ($this->user_data[0]['money'] >= $_POST['bet']) {
                $this->bet = $_POST['bet'];
                // deduce Bet from money
                $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` - '" . $this->bet . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $this->bet));
                $this->output_buffer .= '<div align="center" style="padding:5px;">You have bet ' . $_POST['bet'] . ' ryo!<br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Continue</a></div>';
            } else {
                $this->output_buffer .= '<div align="center" style="padding:5px;">You cannot afford to bet this much.<br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Return</a></div>';
            }
        } else {
            $this->output_buffer .= '<div align="center" style="padding:5px;">Invalid bet.<br><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Return</a></div>';
        }
    }

    //	Generate a card for the designated target
    private function generateCard($target) {
        $loopcount = 0;
        do {
            $loopcount++;
            /* 				Generate card				 */
            switch (random_int(1, 4)) {
                case 1:
                    $card = 'H';
                    break;
                case 2:
                    $card = 'D';
                    break;
                case 3:
                    $card = 'S';
                    break;
                case 4:
                    $card = 'C';
                    break;
            }
            $value = random_int(1, 13);
            $card .= $value;
            if ($value > 10) {
                $value = 10;
            }
            /* 				Validate card				 */
            $count++;
            //	Player cards
            $i = 0;
            while ($i < count($this->player_cards)) {
                if ($this->player_cards[$i] == $card) {
                    $count++;
                }
                $i++;
            }
            //	Dealer cards
            $i = 0;
            while ($i < count($this->dealer_cards)) {
                if ($this->dealer_cards[$i] == $card) {
                    $count++;
                }
                $i++;
            }
            if ($count <= self::DECKS) {
                $loop = true;
            } else {
                if ($value == 1) {
                    if ($this->{$target . '_value'} + 11 <= 21) {
                        $value = 11;
                        $this->{$target . '_ace'} ++;
                    } else {
                        $value = 1;
                    }
                }
                if ($this->{$target . '_value'} + $value > 21 && $this->{$target . '_ace'} >= 1) {
                    if (in_array('S1', $this->{$target . '_cards'}) || in_array('C1', $this->{$target . '_cards'}) || in_array('H1', $this->{$target . '_cards'}) || in_array('D1', $this->{$target . '_cards'})) {
                        $this->{$target . '_value'} -= 10;
                        $this->{$target . '_ace'} --;
                    }
                }
                $this->{$target . '_value'} += $value;
                $nextindex = count($this->{$target . '_cards'});
                $this->{$target . '_cards'}[$nextindex] = $card;
                $loop = false;
            }
        } while ($loop == true && $loopcount < 10);
    }

    //	User surrenders
    private function surrender() {
        $this->stopGame();
    }

    //	Give the user a card
    private function hit() {
        $this->generateCard('player');
    }

    //	Check for the outcome
    private function check_outcome() {
        if (($this->dealer_value > 21 && $this->player_value > 21) || $this->player_value > 21) {
            //	User loses
            return 'lose';
        } elseif ($this->dealer_value > 21 && $this->player_value <= 21) {
            //	User wins
            return 'win';
        } elseif ($this->dealer_value == 21) {
            //	Dealer wins
            return 'lose';
        } elseif ($this->user_value == 21) {
            //	User wins
            return 'win';
        } elseif ($this->player_value == $this->dealer_value) {
            //	Neither win
            return 'lose';
        } elseif ($this->dealer_value > $this->player_value) {
            return 'lose';
        } elseif ($this->player_value > $this->dealer_value) {
            return 'win';
        }
    }

    //	Dealer action
    private function dealer() {
        if ($this->dealer_value < 17) {
            while ($this->dealer_value < 17) {
                $this->generateCard('dealer');
            }
        }
    }

    /*
     * 				Game screens
     */

    private function main_screen() {
        $this->output_buffer .= '<div align="center"><br>
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
      	<tr>
	        <td colspan="3" align="center" style="border-top:none;" class="subHeader">Blackjack!</td>
      	</tr>
      	<tr>
	        <td colspan="3" align="center">Dealers cards: </td>
      	</tr>
      	<tr>
	        <td colspan="3" align="center">';
        if ($_POST['Submit'] == 'Stand') {
            //	Show dealers hidden card
            if ($this->dealer_cards[0] != null) {
                $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[0] . '.png" width="120" height="166">';
            }
        } else {
            //	Do not show dealers card
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/B1.png" width="120" height="166">';
        }
        //	Show other cards
        if ($this->dealer_cards[1] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[1] . '.png" width="120" height="166">';
        }
        if ($this->dealer_cards[2] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[2] . '.png" width="120" height="166">';
        }
        if ($this->dealer_cards[3] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[3] . '.png" width="120" height="166"><br>';
        }
        if ($this->dealer_cards[4] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[4] . '.png" width="120" height="166">';
        }
        if ($this->dealer_cards[5] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[5] . '.png" width="120" height="166">';
        }
        if ($this->dealer_cards[6] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[6] . '.png" width="120" height="166">';
        }
        if ($this->dealer_cards[7] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[7] . '.png" width="120" height="166"><br>';
        }
        if ($this->dealer_cards[8] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[8] . '.png" width="120" height="166">';
        }
        if ($this->dealer_cards[9] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->dealer_cards[9] . '.png" width="120" height="166">';
        }
        $this->output_buffer .= '</td>
      	</tr>
      	<tr>
	        <td colspan="3" align="center">value: ';
        if ($_POST['Submit'] == 'Stand') {
            $this->output_buffer .= $this->dealer_value;
        } else {
            $this->output_buffer .= '???';
        }
        $this->output_buffer .= '</td>
      	</tr>
      	<tr>
	        <td colspan="3" align="center">Your cards: </td>
      	</tr>
      	<tr>
	        <td colspan="3" align="center">';
        if ($this->player_cards[0] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[0] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[1] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[1] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[2] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[2] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[3] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[3] . '.png" width="120" height="166"><br>';
        }
        if ($this->player_cards[4] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[4] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[5] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[5] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[6] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[6] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[7] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[7] . '.png" width="120" height="166"><br>';
        }
        if ($this->player_cards[8] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[8] . '.png" width="120" height="166">';
        }
        if ($this->player_cards[9] != null) {
            $this->output_buffer .= '&nbsp;<img src="./images/casino/cards/' . $this->player_cards[9] . '.png" width="120" height="166">';
        }
        $this->output_buffer .= '</td>
      	</tr>
      	<tr>
	        <td colspan="3" align="center">value: ' . $this->player_value . '</td>
      	</tr>
    	</table><br></div>';
    }

    private function options() {
        $this->output_buffer .= '<div align="center"><br>
  		<form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        <td align="center" style="border-top:none;" class="subHeader">Options</td></tr><tr>
        <td align="center" style="padding-top:2px;"><input type="submit" name="Submit" value="Hit"></td>
      	</tr><tr>
        <td align="center" style="padding-top:2px;"><input name="Submit" type="submit" id="Submit" value="Stand"></td>
      	</tr><tr>
        <td align="center" style="padding-top:2px;"><input name="Submit" type="submit" id="Submit" value="Surrender"></td>
      	</tr></table></form><br><br></div>';
    }

    private function win() {
        //	Calculate profit:
        $prize = round($this->bet * 1.5);
        //	Update gains:
        $GLOBALS['database']->execute_query("UPDATE `users` SET `money` = `money` + '" . $prize . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
        $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $prize));
        //	Show screen:
        $this->output_buffer .= '<div align="center"><br>
  		<form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        <td align="center" style="border-top:none;" class="subHeader">Blackjack!</td>
      	</tr><tr><td align="center">You won! </td></tr><tr>
        <td align="center">You have won ' . $prize . ' ryo!</td></tr><tr>
        <td align="center" style="padding-top:2px;padding-bottom:2px;"><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Play again</a></td>
      	</tr></table></form><br><br></div>';
        $this->stopGame();
    }

    private function lost() {
        $this->output_buffer .= '<div align="center"><br>
  		<form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        <td align="center" style="border-top:none;" class="subHeader">Blackjack!</td>
      	</tr><tr><td align="center">You lost! </td></tr><tr>
        <td align="center">you lost your bet of ' . $this->bet . ' ryo </td></tr><tr>
        <td align="center" style="padding-top:2px;padding-bottom:2px;"><a href="?id=' . $_GET['id'] . '&game=' . $_GET['game'] . '">Play again</a></td>
      	</tr></table></form><br><br></div>';
        $this->stopGame();
    }

    private function set_bet() {
        $this->output_buffer .= '<div align="center"><br>
  		<form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        <td align="center" style="border-top:none;" class="subHeader">Blackjack!</td></tr>
      	<tr><td align="center">Place your bet! </td></tr>
      	<tr><td align="center">You currently have ' . $this->user_data[0]['money'] . ' Ryo</td></tr>
      	<tr><td align="center"><select name="bet">';
        if ($this->user_data[0]['money'] >= 10) {
            $this->output_buffer .= '<option value="10">10 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 20) {
            $this->output_buffer .= '<option value="20">20 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 50) {
            $this->output_buffer .= '<option value="50">50 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 100) {
            $this->output_buffer .= '<option value="100">100 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 1000) {
            $this->output_buffer .= '<option value="1000">1000 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 10000) {
            $this->output_buffer .= '<option value="10000">10000 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 100000) {
            $this->output_buffer .= '<option value="100000">100000 ryo</option>';
        }
        if ($this->user_data[0]['money'] >= 1000000) {
            $this->output_buffer .= '<option value="1000000">1000000 ryo</option>';
        }

        $this->output_buffer .= '</select></td></tr><tr>
        <td align="center" style="padding-top:2px;padding-bottom:2px;"><input type="submit" name="Submit" value="Submit"></td>
      	</tr></table></form><br><br></div>';
    }

}