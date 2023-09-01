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

// Create handler class based on the main library
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/trainingSystem/trainLib.php');
class jail extends trainLib {

    // Constructor
    function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        // Get user data
        $this->getUserData();

        // Check if signing out?
        if( isset( $_GET['action']) && $_GET['action'] == "signout" ){

            // Sign out of jail
            $this->signOut();

        }
        elseif( isset( $_GET['action']) && $_GET['action'] == "bailout" ){

            // Sign out of jail
            $this->setBailout();
            $this->bailOut();

        }
        else{

            // Set the bailout price
            $this->setBailout();

            // Setup a training system
            $this->setupTrainingSystem(array(
                "availableJutsuTypes" => false,
                "availableGenerals" => array("strength", "intelligence", "willpower", "speed"),
                "availableStats" => false,
                "jutsuAttackTypes" => false,
                "jutsuTypes" => false,
                "jutsuMastery" => false,
                "systemName" => "Village Jail"
            ));

            // Show the wrapper
            $this->showTrainingWrapper();

            // Set the release time (is shown in the training wrapper template
            $this->setReleaseTime();

        }

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }

    // Calculate the bailout price
    private function setBailout(){

        // Get release time & bailout price
        $hoursLeft = round(($this->user[0]['jail_timer'] - $GLOBALS['user']->load_time) / 3600);
        $this->bailOutPrice = floor( 0.15*$GLOBALS['userdata'][0]['max_health']*$hoursLeft );

        // Set minimum bailout price
        $minBail = $GLOBALS['userdata'][0]['max_health'] * 0.25;
        if( $minBail > $this->bailOutPrice ){
            $this->bailOutPrice = $minBail;
        }

    }

    // Main screen
    private function setReleaseTime() {
        $release_time = $this->user[0]['jail_timer'] - $GLOBALS['user']->load_time;
        if ($release_time <= 0) {
            $release = '<a href="?id=' . $_GET['id'] . '&amp;action=signout">Sign out</a>';
        }
        else if( $release_time >= 43200)
        {
            $release = functions::convert_time($release_time, 'jailtime', 'false');
        }
        else {
            $release = functions::convert_time($release_time, 'jailtime', 'false');
            $release .= ' - <a href="?id=' . $_GET['id'] . '&amp;action=bailout">Bail out</a> (costs '.$this->bailOutPrice.' ryo)';
        }
        $GLOBALS['template']->assign('release', $release);
    }

    // Sign out
    private function signOut() {

        // Get release time
        $release_time = $this->user[0]['jail_timer'] - $GLOBALS['user']->load_time;
        if ($release_time <= 0) {

            // Sign out
            $this->setOutOfJail( 0 );
            $GLOBALS['page']->Message( "You have signed out of jail, and are now free to go." , 'Jail', 'id=2', "Leave");

        }
        else {

            // Not yet
            $release = functions::convert_time($release_time, 'jailtime2', 'false');
            $GLOBALS['page']->Message( "You cannot sign out yet, please wait:".$release , 'Jail', 'id=2', "Leave");
        }
    }

    // Bail out of jail
    private function bailOut(){


        // Check if user can bail out
        if( $GLOBALS['userdata'][0]['money'] >= $this->bailOutPrice && $this->bailOutPrice > 0){

            // Get out
            $this->setOutOfJail($this->bailOutPrice);
            $GLOBALS['page']->Message( "You have bailed out of jail for ".$this->bailOutPrice." ryo, and are now free to go." , 'Jail', 'id=2', "Leave");

        }
        else{
            $GLOBALS['page']->Message( "Sorry, you need ".$this->bailOutPrice." ryo to bail out" , 'Jail', 'id=2', "Leave");
        }
    }

    // Set to out of jail
    private function setOutOfJail( $cost ){
        $query = "UPDATE
                    `users`,
                    `users_timer`,
                    `users_statistics`
                  SET
                    `jail_timer` = 0,
                    `status` = 'awake',
                    `battle_id` = 0 ,
                    `money` = `money` - ".$cost."
                  WHERE
                    `id` = '" . $_SESSION['uid'] . "' AND
                    `id` = `userid`  AND
                    `id` = `uid` ";
        $GLOBALS['database']->execute_query($query);
        $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));
        $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $cost));
        $GLOBALS['userdata'][0]['status'] = "awake";
        $GLOBALS['template']->assign('userStatus', 'awake');
        $GLOBALS['userdata'][0]['money'] -= $cost;
    }

}

new jail();