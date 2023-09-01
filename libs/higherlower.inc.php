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
 *							Higher / Lower game library
 *			User guesses if the next card on the stack is higher or lower
 */
class higherlower{
	private $output_buffer;
	
	//		Presets
	private $DECK = array('C1','C2','C3','C4','C5','C6','C7','C8','C9','C10','C11','C12','C13',
					   'D1','D2','D3','D4','D5','D6','D7','D8','D9','D10','D11','D12','D13',
					   'H1','H2','H3','H4','h5','H6','H7','H8','H9','H10','H11','H12','H13',
					   'S1','S2','S3','S4','S5','S6','S7','S8','S9','S10','S11','S12','S13');
					   
	//		Game variables
	private $play_deck;			//	Deck user is playing with
	private $cards_guessed;		//	Number of cards guessed correctly
	private $payout;			//	Payout if the user quits now
	private $prev_card;			//	Previous card (for comparison)
	private $cur_card;			//	Current card (for comparison)
	private $outcome;			//	Comparison outcome
	
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//		Store the game in the session
	
	private function storeGame(){
		session_register('higherlower');
		$_SESSION['higherlower'] = serialize($this);
	}
	
	//		Magic function called when class is serialized, provides cleanup.
	public function __sleep(){
		$this->output_buffer = null;
		return array('play_deck','cards_guessed','payout','prev_card','cur_card');
	}
	
	//		Game ended, remove from session
	private function stopGame(){
		if(isset($_SESSION['higherlower'])){
			session_unregister('higherlower');
		}
	}
	
	//		Create game instance and then run:
	public function higherlower(){
		$this->play_deck = $this->DECK;
		//		Generate new card
		$this->cur_card = $this->generateCard();
		$this->run();
	}
	
	//		Run through the game script
	public function run(){
		//		Check against previous card and with bet (if appliccable)
		if(isset($_POST['Submit']) && $_POST['Submit'] != 'Quit'){
			//		Mark current card as the previous card before drawing a new one
			$this->prev_card = $this->cur_card;
			//		Generate new card
			$this->cur_card = $this->generateCard();
			if($_POST['Submit'] == 'Higher' && $this->cur_card[1] > $this->prev_card[1]){
				//	Proceed (payout increase)
				$this->cards_guessed++;
				$this->outcome = 'correct';
			}
			elseif($_POST['Submit'] == 'Lower' && $this->cur_card[1] < $this->prev_card[1]){
				//	Proceed (payout increase)
				$this->cards_guessed++;
				$this->outcome = 'correct';
			}
			elseif($this->cur_card[1] == $this->prev_card[1]){
				//	Proceed (no payout increase)
			}
			else{
				//	User loses
				$this->outcome = 'lost';
			}
		}
		//		Show main screen
		$this->main_screen();
		if($this->outcome == 'lost'){
			$this->lost();
		}
		elseif($this->outcome == 'correct'){
			$this->correct();
		}
		//		Return output
		$this->return_stream();
		//		Cleanup / Game storage
		if($this->outcome != 'lost' && count($this->play_deck) != 0){
			$this->storeGame();
		}
		else{
			$this->stopGame();
		}
	}
	
	/*
	 *		Game functions
	 */
	
	//		User lost
	private function lost(){
		
	}
	
	private function correct(){
		
	}
	
	//		Generate next card
	private function generateCard(){
		$num = random_int(0,(count($this->play_deck) - 1));
		$temp = str_split($this->play_deck[$num],1);
		$value = $temp[1].$temp[2];
		$return = array($this->play_deck[$num],$value,$num);
		$this->removeCard($num);
		return $return;
		
	}
	
	private function removeCard($num){
		array_splice($this->play_deck,$num,1);
	}
	
	/*
	 *		Game screens
	 */
	
	private function main_screen(){
		$this->output_buffer .= '<div align="center">
				<form name="form1" method="post" action="">
				  <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="3" align="center" style="border-bottom:1px solid 1px;font-weight:bold;background-color:#724B3F;color:white;font-size:16px;">Higher / Lower </td>
                    </tr>
                    <tr>
                      <td width="50%" style="text-align:center;padding:2px;">Current card: </td>
                      <td colspan="2" style="text-align:center;padding:2px;">Statistics</td>
                    </tr>
                    <tr>
                      <td rowspan="12" align="center"><img src="./images/casino/cards/'.$this->cur_card[0].'.png" /></td>
                      <td width="21%" align="left">Cards left: </td>
                      <td width="29%" align="left">'.count($this->play_deck).'</td>
                    </tr>
                    <tr>
                      <td align="left">Correct guesses: </td>
                      <td align="left">'.$this->cards_guessed.'</td>
                    </tr>
                    <tr>
                      <td align="left">Payout:</td>
                      <td align="left">'.$this->payout.'</td>
                    </tr>';
		if($this->outcome != 'lost'){
			$this->output_buffer .= '
            <tr>
               <td colspan="2" align="center" style="padding:3px;">If you quit the game you receive your current payout. </td>
            </tr>
            <tr>
               <td colspan="2" align="center" style="padding:3px;"><input type="submit" name="Submit" value="Quit"></td>
            </tr>';
		}
        $this->output_buffer .= '<tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="3" align="center">&nbsp;</td>
                    </tr>';
		if($this->outcome != 'lost'){
			$this->output_buffer .= '<tr>
                      <td colspan="3" align="center" style="padding:3px;"><input type="submit" name="Submit" value="Higher">&nbsp;
                      <input type="submit" name="Submit" value="Lower"></td>
                    </tr>';
		}
		else{
			$this->output_buffer .= '<tr>
                      <td colspan="3" align="center">&nbsp;</td>
                    </tr>';
		}
     	$this->output_buffer .= '<tr>
                      <td colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
                          <td style="padding-left:5px;font-weight:bold;">&nbsp;</td>
                        </tr>
                      </table></td>
                    </tr>
                  </table>
				</form>
				</div>';
	}

	private function loss_screen(){
		
	}
	
	private function take_winnings(){
		
	}
}