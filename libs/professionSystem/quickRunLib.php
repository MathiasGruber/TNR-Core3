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

class quickRunLib {

    // Setup the system
    protected function setupSystem( $params ){

        // Standard parameters
        $this->chakraCost = (isset($params['chakra_cost'])) ? $params['chakra_cost'] : 10;
        $this->staminaCost = (isset($params['stamina_cost'])) ? $params['stamina_cost'] : 10;
        $this->system_name = (isset($params['system_name'])) ? $params['system_name'] : "Run System";

        // Reward specifications
        $this->ryoMin = (isset($params['ryoMin'])) ? $params['ryoMin'] : 8;
        $this->ryoMax = (isset($params['ryoMax'])) ? $params['ryoMax'] : 13;
        $this->dipMin = (isset($params['dipMin'])) ? $params['dipMin'] : 1;
        $this->dipMax = (isset($params['dipMax'])) ? $params['dipMax'] : 3;

        // Which entry are we updating
        $this->missionEntry = (isset($params['entryColumn'])) ? $params['entryColumn'] : "errands";

        // Set location
        $this->setLocation();
    }

    protected function requireNoWar(){
        // Don't allow to do diplo in warring village
        if(array_key_exists($this->location, $GLOBALS['userdata'][0]['alliance'][0]) && $GLOBALS['userdata'][0]['village'] !== "Syndicate" ){
            if( (int) $GLOBALS['userdata'][0]['alliance'][0][ $this->location ] == 2 ){
                throw new Exception("You cannot do this here since your village is in war.");
            }
        }
    }

    // Some standard checks
    private function canDoQuickRun(){

        // Check basic pools
        if (
            (floor($GLOBALS['userdata'][0]['cur_cha']) >= $this->chakraCost) ||
            (floor($GLOBALS['userdata'][0]['cur_sta']) >= $this->staminaCost)
        ) {

            // Check location
            if ( $GLOBALS['page']->inTown || (!$GLOBALS['page']->inTown && $GLOBALS['userdata'][0]['village'] == "Syndicate") ) {

                // Check status
                if( $GLOBALS['userdata'][0]['status'] == "awake" ){
                    return true;
                }
                else{
                    throw new Exception("You must be awake to perform this action");
                }
            } else {
                throw new Exception("You cannot do this at this location.");
            }
        } else {
            throw new Exception("You do not have enough chakra/stamina to do this");
        }
    }

    // Calculate max times & pool limits
    private function calcPoolLimits( $stamina, $chakra ){
        $cha_times = floor( $chakra / $this->chakraCost);
        $sta_times = floor( $stamina / $this->staminaCost);
        $this->max_times = ($cha_times > $sta_times) ? $sta_times : $cha_times;
    }

    // Function for showing amount options
    protected function formRunAmount(){

        if( $this->canDoQuickRun() ){

            // Select options in the input form
            $selectOptions = array();

            // Calculate max entries
            $this->calcPoolLimits( $GLOBALS['userdata'][0]['cur_sta'], $GLOBALS['userdata'][0]['cur_cha'] );

            // Add max to options
            $selectOptions[ $this->max_times ] = $this->max_times . " times";

            // Create the input form
            $GLOBALS['page']->UserInput(
                    "Doing this requires ".$this->staminaCost." stamina and ".$this->chakraCost." chakra points for each action
                     <br>With your current stamina and chakra you will therefore have following options:",
                    $this->system_name,
                    array(
                        array(
                            "infoText"=>"How many times would you like to do this action?",
                            "inputFieldName"=>"times",
                            "type"=>"range",
                            'inputFieldValue' => $this->max_times ? $this->max_times : 0,
                            'inputFieldMin' => 0,
                            'inputFieldMax' => $this->max_times,
                            'inputFieldDisabled' => $this->max_times == 0
                        )
                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&page=".$_GET['page'] ,
                        "submitFieldName"=>"Submit",
                        "submitFieldText"=>"Submit"),
                    "Return"
            );
        }
    }

    // Do perform the run
    protected function doQuickRun() {

        // Standard checks
        if( $this->canDoQuickRun() ){

            $GLOBALS['database']->transaction_start();

            // Check if Errand Times are Numbers Only
            if( ctype_digit($_POST['times']) && $_POST['times'] > 0 ) {

                // Obtain/Lock Necessary User Information
                $user = $GLOBALS['database']->fetch_data('SELECT
                    `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`
                    FROM `users_statistics`, `users_missions`, `bingo_book`
                    WHERE `users_statistics`.`uid` = '.$_SESSION['uid'].' AND
                        `users_missions`.`userid` = `users_statistics`.`uid` AND
                        `bingo_book`.`userid` = `users_statistics`.`uid`
                    LIMIT 1
                    FOR UPDATE');
                if( $user !== "0 rows" ) {

                    // Calc & test pool limits
                    $this->calcPoolLimits( $user[0]['cur_sta'], $user[0]['cur_cha'] );
                    if( $_POST['times'] <= $this->max_times ){

                        // Set Necessary Data Variables
                        $times = intval($_POST['times']);

                        // Update costs
                        $this->staminaCost *= $times;
                        $this->chakraCost *= $times;

                        // Calculate rewards
                        $this->ryo_award = $this->diplo_award = 0;
                        for($i = 0; $i < $times; $i++) {
                            $this->ryo_award += random_int($this->ryoMin,$this->ryoMax);
                            $this->diplo_award += random_int($this->dipMin,$this->dipMax);
                        }

                        // Check for global event modifications
                        if( $event = functions::getGlobalEvent("IncreasedCrimeAndErrandRyo")){
                            if( isset( $event['data']) && is_numeric( $event['data']) ){
                                $this->ryo_award *= round($event['data'] / 100,2);
                            }
                        }

                        if(
                            ($GLOBALS['database']->execute_query('
                                UPDATE
                                    `users_statistics`, `users_missions`, `bingo_book`
                                SET
                                    `users_statistics`.`money` = `users_statistics`.`money` + '.$this->ryo_award.',
                                    `bingo_book`.`'.$this->location.'` = `bingo_book`.`'.$this->location.'` + '.$this->diplo_award.',
                                    `users_missions`.`'.$this->missionEntry.'` = `users_missions`.`'.$this->missionEntry.'` + '.$times.',
                                    `users_statistics`.`cur_cha` = `users_statistics`.`cur_cha` - '.$this->chakraCost.',
                                    `users_statistics`.`cur_sta` = `users_statistics`.`cur_sta` - '.$this->staminaCost.'
                                WHERE
                                    `users_statistics`.`uid` = '.$_SESSION['uid'].'
                                    AND `users_missions`.`userid` = `users_statistics`.`uid`
                                    AND `bingo_book`.`userid` = `users_statistics`.`uid`')
                            ) === false)
                        {
                            throw new Exception('There was an error updating the user information to the database.');
                        }
                        else
                        {
                            $fetch_query = 'SELECT `'.$this->location.'` as `diplomacy` FROM `bingo_book` WHERE `userID` = '.$_SESSION['uid'];
                            $result = $GLOBALS['database']->fetch_data($fetch_query);
                            $GLOBALS['Events']->acceptEvent('diplomacy_gain', array('new'=>$result[0]['diplomacy'], 'old'=>$result[0]['diplomacy']-$this->diplo_award, 'context'=>$this->location));
                            $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $this->ryo_award));
                        }

                        // Quick update
                        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$GLOBALS['userdata'][0]['cur_sta'] - $this->staminaCost, 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
                        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$GLOBALS['userdata'][0]['cur_cha'] - $this->chakraCost, 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));

                        $GLOBALS['userdata'][0]['cur_sta'] -= $this->staminaCost;
                        $GLOBALS['userdata'][0]['cur_cha'] -= $this->chakraCost;

                        // Commit transaction
                        $GLOBALS['database']->transaction_commit();

                        // Return true
                        return true;
                    }
                    else{
                        throw new Exception("You do not have enough chakra / stamina to do this.");
                    }
                }
                else{
                    throw new Exception("There was an error trying to obtain necessary user information");
                }
            }
            else{
                throw new Exception("Doing this 0 times is a waste of clicking buttons");
            }
        }
    }

    // Set the location
    private function setLocation(){
        if( in_array($GLOBALS['userdata'][0]['location'], array('Shroud','Shine','Samui','Silence','Konoki')) ){
            $this->location = ucfirst($GLOBALS['userdata'][0]['location']);
        }
        elseif( in_array($GLOBALS['userdata'][0]['location'], array("Gambler's Den","Bandit's Outpost","Poacher's Camp","Pirate's Hideout") ) ){
            $this->location = "Syndicate";
        }
        else{
            throw new Exception("In order to do this you must be in either a village or syndicate hideout.");
        }
    }

}