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

// Get basic library for stuff like this
require_once(Data::$absSvrPath.'/libs/professionSystem/quickRunLib.php');
class errands extends quickRunLib {

    // Constructore
    function __construct() {

        // Set data for this quickrun system
        $this->setupSystem(array(
            "stamina_cost" => 5,
            "chakra_cost" => 5,
            "system_name" => "Errand System",
            "entryColumn" => "errands",
            "ryoMin" => 3+$GLOBALS['userdata'][0]['rank_id'],
            "ryoMax" => 7+$GLOBALS['userdata'][0]['rank_id']
        ));

        // Use exceptions
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Decide on page
            if (!isset($_GET['page'])) {

                // Main page
                $this->main_screen();

            } else {

                // Do diplomacy
                if ($_GET['page'] == 'do_errand') {

                    // Check if amount is set
                    if (isset($_POST['times'])) {

                        // Do the diplomacy
                        $this->run_errands();

                    } else {

                        // Pick an amount
                        $this->formRunAmount();
                    }
                }
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , $this->system_name, 'id='.$_GET['id'],'Return');
        }
    }

    // The main screen for this given system
    private function main_screen() {

        // Check if village or not
        $description = $subTitle = "";
        if( $GLOBALS['userdata'][0]['village'] == "Syndicate" && !$GLOBALS['page']->inTown ){
            $description = "There are all kind of small jobs and errands to run for the syndicate. Doing these favors, you will gain respect by the syndicate. Do you wish to run a few small errands for the syndicate?";
            $subTitle = "Helping out the Syndicate";
        }
        elseif( $GLOBALS['page']->inTown ){
            $description = "You walk around in the village when suddenly a stranger asks you if you could do him a couple of favors. Do you wish to help this stranger for a modest sum of money?";
            $subTitle = "Helping out in " .str_replace( " village", "", $GLOBALS['userdata'][0]['location'] );
        }
        else{
            throw new Exception("You can not do diplomacy here!");
        }

        // Show a message
        $GLOBALS['page']->Message($description, $subTitle, 'id=' . $_GET['id'] . '&page=do_errand', "Run Errands");
    }

    // Function for running diplomacy
    private function run_errands(){

        // Check if run is successfull
        if( $this->doQuickRun() ){

            // Get random message
            $message = "The stranger introduces himself to you. ";
            switch( random_int(1,4) ){
                case 1: $message .= "He wants you to go deliver a package to a house in the other end of the village. You successfully deliver the package, and therefore you are awarded:"; break;
                case 2: $message = "He wants you to help him move some heavy boxes from the street into his house. You help the man and are awarded:"; break;
                case 3: $message = "He wants you to go deliver a package to a house in the other end of the village. You successfully deliver the package, and therefore you are awarded:"; break;
                case 4: $message = "He wants you to go do some shopping for him. You do the shopping and are afterwards allowed to keep the spare money:"; break;
            }

            $GLOBALS['Events']->acceptEvent('errand', array('data'=>'success', 'count'=>$_POST['times'], 'diplomacy'=>array('points'=>$this->diplo_award,'village'=>$this->location), 'ryo'=>$this->ryo_award ));

            // Check for gains
            if( $this->ryo_award > 0){
                $message .= "<br><b>".$this->ryo_award."</b> Ryo";
            }
            if( $this->diplo_award > 0){
                $message .= ", and <b>".$this->diplo_award. "</b> Diplomacy points in ".$this->location;
            }

            // Costs
            $message .= "<br>This has cost you <b>".$this->staminaCost." stamina</b> and <b>".$this->chakraCost." chakra</b>";

            // Show a message
            $GLOBALS['page']->Message($message, $this->system_name, 'id=' . $_GET['id'] , "Return");

        }
    }
}
new errands();