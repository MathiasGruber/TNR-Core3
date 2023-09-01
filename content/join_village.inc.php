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

require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
class join extends respectLib{

    // Constructore
    function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        $this->fetch_userdata();
        if (!isset($_GET['act'])) {
            $this->main_page();
        } elseif ($_GET['act'] == 'join') {
            $this->do_join();
        }

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }

    // Fetch needed data about the user
    private function fetch_userdata() {
        $this->user = $GLOBALS['database']->fetch_data("SELECT `bingo_book`.* FROM `bingo_book` WHERE `bingo_book`.`userID` = '" . $_SESSION['uid'] . "' LIMIT 1");
    }

    // Check if user can join village
    private function canJoinVillage(){

        // Get location
        $location = ucwords(str_replace(" village", "", $GLOBALS['userdata'][0]['location']));

        // Check syndicate
        if( $location !== "Syndicate" && !in_array($location, array('Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')) ){

            // Check location against village array
            if( in_array($location, Data::$VILLAGES) ){

                // Check reputation in village
                $respect = $this->user[0][ $location ];

                // Requirement
                switch( $GLOBALS['userdata'][0]['rank_id'] ){
                    case 3: $requirement = 5000; break;
                    case 4: $requirement = 9000; break;
                    case 5: $requirement = 14000; break;
                    default: $requirement = 20000; break;
                }

                // Check if valid for uptake
                if( $respect >= $requirement ){

                    // Only allow joining if not in war
                    $this->alliance = cachefunctions::getAlliance( $location );
                    require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
                    $this->warLib = new warLib();
                    if( !$this->warLib->inWar( $this->alliance[0] ) ){
                        return $location;
                    }
                    else{
                        $GLOBALS['page']->Message( "You cannot join a village in war. You will have to wair." , 'Join Village', 'id=31', "Do Diplomacy in ".$location);
                    }
                }
                else{
                    $GLOBALS['page']->Message( "You need to earn more respect in ".$location." before you can join it. Amount needed: ".$requirement , 'Join Village', 'id=31', "Do Diplomacy in ".$location);
                }
            }
            else{
                $GLOBALS['page']->Message( "You are currently not located in a village. To join a village, you must first travel to it." , 'Join Village', 'id=8', "Go to Travel");
            }
        }
        else{
            $GLOBALS['page']->Message( "Asking around, you are told that the Gambler's Den is not a village you can join. This is where outlaws go." , 'Join Village', 'id=8', "Return");
        }
    }

    // Main page
    function main_page() {
        if( $location = $this->canJoinVillage() ){
            $GLOBALS['page']->Message( 'You have earned enough respect in this village to join it.', 'Join Village', 'id='.$_GET['id'].'&act=join', 'Join '.$location);
        }
    }

    // Do join village
    function do_join() {
        if( $location = $this->canJoinVillage() ){
            $message = $this->join_village( $location );

            // Log the change
            functions::log_village_changes(
                $_SESSION['uid'],
                "Syndicate",
                $location,
                "Standard join village"
            );

            $GLOBALS['page']->Message( $message, 'Join Village', 'id=2', 'Return');
        }
    }

}

new join();