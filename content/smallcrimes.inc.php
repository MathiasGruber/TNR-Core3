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
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');

class crimes extends quickRunLib {

    // Constructore
    function __construct() {

        // Set data for this quickrun system
        $this->setupSystem(array(
            "stamina_cost" => 5,
            "chakra_cost" => 5,
            "system_name" => "Small Crimes",
            "entryColumn" => "scrimes",
            "ryoMin" => 5+$GLOBALS['userdata'][0]['rank_id'],
            "ryoMax" => 8+$GLOBALS['userdata'][0]['rank_id'],
            "dipMin" => -2,
            "dipMax" => -1
        ));

        // Use exceptions
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Check conditions
            $this->requireNoWar();

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
                        $this->run_crimes();

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
        $description = "You walk around in the village, when you suddenly get the craving for some snacks.
                        However, your pockets are empty and you see a helpless grandma with a barrel of ryo.
                        Do you want to steal from the helpless grandma?";

        $subTitle = "Small Crimes";

        // Show a message
        $GLOBALS['page']->Message($description, $subTitle, 'id=' . $_GET['id'] . '&page=do_errand', "Do Small Crimes");
    }

    // Function for running diplomacy
    private function run_crimes(){

        // Check if run is successfull
        if( $this->doQuickRun() ){

            // 15% chance to go to battle with city guard (AI 77)
            if( random_int(1,100) > 15 || !$GLOBALS['page']->inOutlawBase ){

                $GLOBALS['Events']->acceptEvent('crime', array('data'=>'success', 'count'=>$_POST['times'], 'ryo'=>$this->ryo_award, 'diplomacy'=>array('points'=>$this->diplo_award*-1,'village'=>$this->location) ));

                // Get random message
                $message = "The stranger introduces himself to you. ";
                switch( random_int(1,3) ){
                    case 1: $message .= "You pickpocket an old woman without her noticing. Success, you stole:"; break;
                    case 2: $message = "You run into the ramen shop and steal something before they notice you and run away. You got:"; break;
                    case 3: $message = "You pickpocket some random person and steal:"; break;
                }

                // Check for gains
                if( $this->ryo_award > 0){
                    $message .= " <b>".$this->ryo_award."</b> Ryo";
                }

                // Costs
                $message .= "<br><br>This has cost you <b>".$this->staminaCost." stamina</b> and <b>".$this->chakraCost." chakra</b> and
                            <br><span style='color:red;'><b>".($this->diplo_award*-1). "</b> Diplomacy points in ".$this->location."</span><br>";

                // Show a message
                $GLOBALS['page']->Message($message, $this->system_name, 'id=' . $_GET['id'] , "Return");
            }
            else{

                $GLOBALS['Events']->acceptEvent('crime', array('data'=>'failure', 'count'=>$_POST['times'], 'ryo'=>0, 'diplomacy'=>array('points'=>0,'village'=>$this->location)));

                // Go to battle
                $opponent = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = 77 LIMIT 1");
                if( $opponent !== "0 rows" ){

                    // Fix up AI
                    //$opponent[0] = functions::make_ai( $opponent[0] );

                    // Update Database
                    //functions::insertIntoBattle(
                            //array($GLOBALS['userdata'][0]['id']),
                            //array($opponent[0]['id']),
                            //"rand",
                            //"1",
                            //array(),
                            //$opponent
                    //);

                    BattleStarter::startBattle( array(array('id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village'])),
                                                array(array('id'=>$opponent[0]['id'],'team'=>false)),
                                                BattleStarter::small_crimes);

                    // Message to user
                    $GLOBALS['page']->Message("You fail doing your small crimes and are attacked by the village guard" , 'Small Crimes', 'id=113', "Go to Battle");
                }
                else{
                    throw new Exception("You fail doing your small crimes");
                }
            }
        }
    }
}

new crimes();