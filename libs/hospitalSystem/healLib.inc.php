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

class hospitalFunctions {

    public function setHospitalDataWpar() {
        $this->setHospitalData();
    }

    // Set hospital data & fix various errors
    protected function setHospitalData() {

        // Get information about this village hospital
        $this->hospital = $GLOBALS['database']->fetch_data('SELECT `village_structures`.`hospital_level`,
            `village_structures`.`hospital_bonus`, `villages`.`owned_territories`
            FROM `village_structures`
                INNER JOIN `villages` ON (`villages`.`name` = `village_structures`.`name`)
            WHERE `village_structures`.`name` = "'.$GLOBALS['userdata'][0]['village'].'" LIMIT 1');

        // Test luck variable
        $this->testLuckVar = false;

        // Get healtime
        $healTime = $this->calculateHealtime();

        // Situations to check if the user is not in battle
        if( $GLOBALS['userdata'][0]['status'] !== "combat" && $GLOBALS['userdata'][0]['status'] !== "exiting_combat"){

            // If hospital timer is 0 and current health < 0, set hospital timer
            if ($GLOBALS['userdata'][0]['cur_health'] <= 0 && (int)$GLOBALS['userdata'][0]['hospital_timer'] === 0) {
                $GLOBALS['database']->execute_query('UPDATE `users_timer`
                    SET `users_timer`.`hospital_timer` = '.($GLOBALS['user']->load_time + $healTime).'
                    WHERE `users_timer`.`userid` = '.$_SESSION['uid'].' LIMIT 1');
                $GLOBALS['userdata'][0]['hospital_timer'] = $GLOBALS['user']->load_time + $healTime;
                $this->testLuckVar = true;
            }

            // User health is below zero but not hospitalized. Fix.
            if ($GLOBALS['userdata'][0]['cur_health'] <= 0 && $GLOBALS['userdata'][0]['status'] !== 'hospitalized') {
                $GLOBALS['database']->execute_query('UPDATE `users`
                    SET `users`.`status` = "hospitalized", `users`.`battle_id` = 0
                    WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1');
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status'] ));
                $GLOBALS['userdata'][0]['status'] = "hospitalized";
                $GLOBALS['template']->assign('userStatus', 'hospitalized');
            }
        }
    }

    // Set hospital data for drowning
    protected function setHospitalDataDrowning()
    {
        $this->testLuckVar = false;

        // Get healtime
        if((int)$GLOBALS['userdata'][0]['hospital_timer'] === 0)
        {
            $healTime = $this->calculateHealtime(600);
            $GLOBALS['userdata'][0]['hospital_timer'] = $GLOBALS['user']->load_time + $healTime;
            $GLOBALS['database']->execute_query('UPDATE `users_timer`
                    SET `users_timer`.`hospital_timer` = '.($GLOBALS['user']->load_time + $healTime).'
                    WHERE `users_timer`.`userid` = '.$_SESSION['uid'].' LIMIT 1');
        }
    }

    // The main screen show to the user
    protected function main_screen() {

        // Check if instant-heal
        if ($this->testLuckVar && $this->testLuck()) {

            // Heal the user
            $this->heal_user(1.0, 0, false);

            // Smarty variable
            $GLOBALS['template']->assign('random_heal', true);
        }
        else {

            // Time left for healed
            $time = ($GLOBALS['userdata'][0]['hospital_timer'] - $GLOBALS['user']->load_time < 0) ?
                0 : $GLOBALS['userdata'][0]['hospital_timer'] - $GLOBALS['user']->load_time;

            // Calcualte the cost of bribing
            if($GLOBALS['userdata'][0]['status'] != "drowning")
            {
                $cost = $this->calculateBribe();
            }

            // Assign neccesary smarty variables
            if($GLOBALS['userdata'][0]['status'] != "drowning")
            {
                $GLOBALS['template']->assign('random_heal', false);
                $GLOBALS['template']->assign('cost', $cost);
            }

            $GLOBALS['template']->assign('timeLeft', $time);
            $GLOBALS['template']->assign('waiting_time', functions::convert_time($time, 'hospotaltimer', 'false'));
        }

        // Send user data
        $GLOBALS['template']->assign('user', $GLOBALS['userdata']);
    }

    // Natural release from hospital after timer expires
    protected function release() {

        // Check the timer
        if($GLOBALS['userdata'][0]['status'] != 'hospitalized')
        {
            $GLOBALS['page']->Message("The hospital is for the sick, the wounded, and their visitors. You have no business here. Please leave.",
                $this->hospitalName, 'id='.$_GET['id']);
        }
        else if (
            $GLOBALS['user']->load_time >= $GLOBALS['userdata'][0]['hospital_timer'] ||
            $GLOBALS['userdata'][0]['cur_health'] > 0
        ) {

            // Heal the user. Either give 1% health or let keep current
            $this->heal_user((($GLOBALS['userdata'][0]['cur_health'] > 0) ? false : 1), 0);

            // Show congrats message
            $GLOBALS['page']->Message("Congratulations, your body has healed and though still a little beat up you are able to move.",
                $this->hospitalName, 'id=2');
        }
        else {
            //	Not yet recovered message
            $GLOBALS['page']->Message("You cannot leave the hospital, as your body has not yet recovered.",
                $this->hospitalName, 'id='.$_GET['id']);
        }
    }



    // Natural release from hospital after timer expires
    protected function releaseDrowning() {

        // Check the timer
        if (
            $GLOBALS['user']->load_time >= $GLOBALS['userdata'][0]['hospital_timer'] ||
            $GLOBALS['userdata'][0]['cur_health'] > 0
        ) {

            // Heal the user. Either give 1% health or let keep current
            $this->heal_user(0.2, 0, 0);
            $GLOBALS['userdata'][0]['drowning'] = 0;
            $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
            $GLOBALS['userdata'][0]['status'] = "awake";
            $GLOBALS['template']->assign('userStatus', 'awake');
            $GLOBALS['userdata'][0]['hospital_timer'] = 0;
            $GLOBALS['database']->execute_query("UPDATE `users`, `users_timer`
                                                 SET `users`.`drowning` = 0,
                                                     `users_timer`.`hospital_timer` = 0,
                                                     `users`.`status` = 'awake'
                                                 WHERE `users`.`id` = '" . $GLOBALS['userdata'][0]['id'] . "' AND `users_timer`.`userid` = `users`.`id`");
            // Show congrats message
            $GLOBALS['page']->Message("Miraculously, you wake up.  Exhausted and freezing, but somehow still alive, you find you've been washed ashore.",
                $this->hospitalName, 'id=2');
        }
        else {
            //	Not yet recovered message
            $GLOBALS['page']->Message("...Darkness...",
                $this->hospitalName, 'id='.$_GET['id']);
        }
    }




    // User pays the doctor to get free
    protected function bribe() {

        // Check that user is hospitalized
        if( $GLOBALS['userdata'][0]['status'] == "hospitalized" ){

            // Get the cost
            $cost = $this->calculateBribe();

            // Check if the user has enough money
            if ($GLOBALS['userdata'][0]['money'] >= $cost) {

                // Heal the user
                $this->heal_user(1.0, $cost);

                // Update the message to the user
                $GLOBALS['page']->Message("You bribe the doctor and are healed completely.", $this->hospitalName, 'id=2');

            }
            else {
                $GLOBALS['page']->Message("You do not have enough ryo to bribe the doctor.", $this->hospitalName, 'id=' . $_GET['id'] . '');
            }
        }
        else{
            $GLOBALS['page']->Message("You are not hospitalized.", $this->hospitalName, 'id=' . $_GET['id'] . '');
        }
    }

    // Function for healing the user. $percent is in the range from 0.0 to 1.0.
    protected function heal_user($percent, $cost, $status = true ) {

        // Query
        $query = 'UPDATE `users`, `users_timer`, `users_statistics`
                  SET `users_statistics`.`money` = `users_statistics`.`money` - '.$cost;

        $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $cost));

        // Health update
        if($percent !== false) {
            $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>floor($GLOBALS['userdata'][0]['max_health'] * $percent), 'old'=>$GLOBALS['userdata'][0]['cur_health'] ));

            $GLOBALS['userdata'][0]['cur_health'] = floor($GLOBALS['userdata'][0]['max_health'] * $percent);
            $query .= ' , `users_statistics`.`cur_health` = (`users_statistics`.`max_health` * '.$percent.') ';
        }

        # Status & timer update
        if( $status == true ){
            $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
            $GLOBALS['userdata'][0]['status'] = "awake";
            $GLOBALS['template']->assign('userStatus', 'awake');
            $query .= ' , `users`.`status` = "awake"
                        , `users`.`drowning` = 0
                        , `users`.`battle_id` = 0
                        , `users_timer`.`hospital_timer` = 0
                        , `users_timer`.`battle_colldown` = '.$GLOBALS['user']->load_time;
        }

        // Query
        $query .= ' WHERE
             `users`.`id` = '.$_SESSION['uid'].' AND
             `users_timer`.`userid` = `users`.`id` AND
             `users_statistics`.`uid` = `users`.`id`';

        // Ensure cost thing
        if( $cost > 0 ){
            $query .= " AND `users_statistics`.`money` - ".$cost." > 0";
        }

        // Run query
        $GLOBALS['database']->execute_query($query);

        cachefunctions::endHarvest( $_SESSION['uid'] );
    }

    // Calculate time
    function calculateHealtime($time = 900) {
        // Base time


        // Check for loyalty reductions

        if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes"  && $GLOBALS['userdata'][0]['status'] != "drowning"){
            switch($GLOBALS['userdata'][0]['village']) {
                case('Syndicate'): { // Syndicate (Negative Respect)
                    switch(true) {
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] <= -400): $time -= 10 * 60; break; // Reduce 10 Minutes
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] <= -240): $time -= 7.5 * 60; break; // Reduce 7.5 Minutes
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] <= -120): $time -= 5 * 60; break; // Reduce 5 Minutes
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] <= -60): $time -= 2.5 * 60; break; // Reduce 2.5 Minutes
                    }
                } break;
                default: { // Village (Positive Respect)
                    switch(true) {
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 400): $time -= 7.5 * 60; break; // Reduce 7.5 Minutes
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 150): $time -= 5 * 60; break; // Reduce 5 Minutes
                        case($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 120): $time -= 2.5 * 60; break; // Reduce 2.5 Minutes
                    }
                } break;
            }
        }

        // For AS and G, reduce time by 10 minutes unless drowning then reduce by 5
        if( $GLOBALS['userdata'][0]['rank_id'] < 3 )
        {
            if($GLOBALS['userdata'][0]['status'] != "drowning")
            {
                $time -= 10*60;
            }
            else
            {
                $time -= 5*60;
            }
        }

        // Check that time is not below 0
        if( $time < 0 ){
            $time = 0;
        }

        // Return the time
        return ceil($time);
    }

    // Calculate the cost of getting bribed out
    protected function calculateBribe() {
        // Calculate cost
        $cost = $this->loyalty_reduce(round(($GLOBALS['userdata'][0]['max_health'] - $GLOBALS['userdata'][0]['cur_health'])
            / (3 + $this->hospital[0]['hospital_level'])));

        // Get profession and reduce cost based on this
        require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
        $professionLib = new professionLib();
        $professionLib->setJobType("profession");
        $professionLib->fetch_user();

        // Check for professions
        if(isset($professionLib->user[0]['name'])) {
            if($professionLib->setGains($professionLib->user[0]['name'])) {
                foreach($professionLib->gains as $gain) {
                    if($gain['type'] === "hospital" && $gain['discount'] > 0) {
                        $cost *= ((100 - $gain['discount']) / 100);
                    }
                }
            }
        }

        // Get clan and reduce cost
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();
        if($clanLib->isUserClan($GLOBALS['userdata'][0]['clan'])) {
            $clan = $clanLib->getClan($GLOBALS['userdata'][0]['clan']);
            if($clan[0]['hospital_reduction'] > $GLOBALS['user']->load_time) {
                $cost *= 0.9;
            }
        }

        // Reduce cost based on Loyalty Reductions
        switch($GLOBALS['userdata'][0]['village']) {
            case('Syndicate'): { // Syndicate (Negative Respect)
                switch(true) {
                    case($GLOBALS['userdata'][0]['vil_loyal_pts'] <= -260): $cost *= 0.75; break;
                }
            } break;
            default: { // Village (Positive Respect)
                switch(true) {
                    case($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 250): $cost *= 0.75; break;
                    case($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 170): $cost *= 0.85; break;
                }
            } break;
        }

        // Price reduction from global event
        if( $event = functions::getGlobalEvent("HospitalPrice") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $cost *= round($event['data'] / 100,2);
            }
        }

        // Reduce cost based on village territories
        $percReduction = ($this->hospital[0]['owned_territories'] > 15) ? 15 : $this->hospital[0]['owned_territories'];

        // Return Rounded Result
        return ceil($cost * ((100 - $percReduction) / 100));
    }

    // Luck testing function
    protected function testLuck() {

        // Hospital bonus, set to 33% chance
        if($this->hospital[0]['hospital_bonus'] > $GLOBALS['user']->load_time) {
            return (random_int(1, 100) < 33);
        }

        // Use hospital level to check success
         return (random_int(1, 240) < $this->hospital[0]['hospital_level']);
    }

    // Reduce coste of heal based on loyalty
    protected function loyalty_reduce($cost) {
        return (($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 250) ? 0.75 * $cost
            : (($GLOBALS['userdata'][0]['vil_loyal_pts'] >= 170) ? 0.85 * $cost : $cost));
    }
}