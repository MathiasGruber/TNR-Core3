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
require_once(Data::$absSvrPath.'/libs/hospitalSystem/eatingLib.inc.php');
class scavenge extends eatingLib {

    // Constructore
    function __construct () {

        // System name yo
        $this->system_name = 'Scavenge System';

        // Use exceptions
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            if( !isset($_POST['Submit']) ){

                // Confirm action
                $GLOBALS['page']->Confirm("Scavenge for food in your current location..", $this->system_name, 'Look for food');
            }
            else{

                // Do the scavenging
                $this->doScavenge();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , $this->system_name, 'id='.$_GET['id'],'Return');
        }
    }

    // Function for healing the user
    private function heal( $itemFound, $healPect ){

        // Figure out how much to heal
        $healAmount = ceil( ($healPect/100)*$GLOBALS['userdata'][0]['max_health'] );

        // Do the healing
        $this->heal_user( $healAmount, 0 );

        // Show message to user
        $GLOBALS['page']->Message( "You manage to find: ".$itemFound.". Eating it heals ".$healPect."% of your HP." , $this->system_name, 'id='.$_GET['id'],'Return');
    }

    // Function for running diplomacy
    private function doScavenge(){

        // Random number
        $rand = random_int(1,100);

        // Determine action
        if( $rand <= 35 ){
            throw new Exception("You did not manage to find any food");
        }
        elseif( $rand <= 60 ){
            $this->heal( "edible herbs", 1 );
        }
        elseif( $rand <= 75 ){
            $this->heal( "a rabbit", 5 );
        }
        elseif( $rand <= 85 ){
            $this->heal( "a deer", 35 );
        }
        elseif( $rand <= 93 ){
            $this->heal( "a boar", 45 );
        }
        else{

            // Instantiate travel library
            require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');
            require_once(Data::$absSvrPath.'/libs/travelSystem/travelLib.php');
            $travelSys = new travelLib();

            // Go to battle
            $check = $travelSys->check_attack(
                $GLOBALS['userdata'][0]['id'] ,
                $GLOBALS['userdata'][0]['level_id'] ,
                $GLOBALS['userdata'][0]['location'] ,
                $GLOBALS['userdata'][0]['region'] ,
                true
            );

            if( $check ){
                $GLOBALS['page']->Message( "While looking for food, you were attacked. " , $this->system_name, 'id=113','To Battle');
            }
            else{
                throw new Exception("You did not manage to find any food, but avoided an attack");
            }
        }
    }
}

new scavenge();