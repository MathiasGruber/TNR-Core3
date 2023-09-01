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
class diplomacy extends quickRunLib {

    // Constructore
    function __construct() {

        // Set data for this quickrun system
        if( strtolower($GLOBALS['userdata'][0]['village']) == 'syndicate')
            $this->setupSystem(array(
                "stamina_cost" => 10,
                "chakra_cost" => 10,
                "system_name" => "Diplomacy System",
                "entryColumn" => "diplomacy_runs",
                "ryoMin" => 0,
                "ryoMax" => 0,
                "dipMin" => 0,
                "dipMax" => 12));
        else
            $this->setupSystem(array(
                "stamina_cost" => 10,
                "chakra_cost" => 10,
                "system_name" => "Diplomacy System",
                "entryColumn" => "diplomacy_runs",
                "ryoMin" => 0,
                "ryoMax" => 0,
                "dipMin" => 2,
                "dipMax" => 5));

        // Use exceptions
        try{

            // Check user session
            functions::checkActiveSession();

            // Obtain lock on this class
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Check diplomacy conditions
            $this->requireNoWar();

            // Decide on page
            if (!isset($_GET['page'])) {

                // Main page
                $this->main_screen();

            } else {

                // Do diplomacy
                if ($_GET['page'] == 'do_diplomacy') {

                    // Set a clan reduction of the cost
                    $this->setClanIncrease();

                    // Set federal increase
                    $this->setFedIncrease();

                    // Check if amount is set
                    if (isset($_POST['times'])) {

                        // Do the diplomacy
                        $this->run_diplomacy();

                    } else {

                        // Pick an amount
                        $this->formRunAmount();
                    }
                }
            }

            // Release lock
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

        // Get reputation data
        $rep_data = $GLOBALS['database']->fetch_data('SELECT * FROM `bingo_book`
            WHERE `bingo_book`.`userid` = '.$_SESSION['uid'].' LIMIT 1');

        // Get information about all the villages
        $village_data = $GLOBALS['database']->fetch_data('
            SELECT `villages`.`name`, `villages`.`leader`, `villages`.`points` FROM `villages`');

        // Add the two together
        for($i = 0, $size = count($village_data); $i < $size; $i++) {
            $village_data[$i]['rep'] = $rep_data[0][ $village_data[$i]['name'] ];
        }

        // Set counts to be shown
        $memberCount = array();
        $village_count = cachefunctions::getVillageCount();
        $active_count = cachefunctions::getVillageACount();

        foreach(array("shroud", "konoki", "silence", "samui", "shine") as $val) {
            $memberCount[$val] = $village_count[0][$val.'_vcount'];
            $memberCount['a'.$val] = $active_count[0][$val.'_vcount'];
        }

        // Send to smarty
        $GLOBALS['template']->assign('memberCount', $memberCount);
        $GLOBALS['template']->assign('hideReturnLink', true);

        // Show the table of users
        tableParser::show_list(
            'reputation',
            "Your Diplomacy in Seichi",
            $village_data,
            array(
                'name' => "Village",
                'rep' => "Diplomacy",
                'leader' => "Kage / Leader",
                'points' => "Funds"
            ),
            false,
            false,   // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            false // Top information
        );



        // Check if village or not
        $description = $subTitle = "";
        if( $GLOBALS['userdata'][0]['village'] == "Syndicate" && !$GLOBALS['page']->inTown ){
            $description = "There are all kind of small jobs and errands to run for the syndicate. Doing these favors, you will gain respect by the syndicate. Do you wish to run a few small errands for the syndicate?";
            $subTitle = "Helping out the Syndicate";
        }
        elseif( $GLOBALS['page']->inTown ){
            $description = "You walk around in the village when suddenly a stranger asks you if you could do him a couple of favors. Do you wish to help this stranger for free, and thereby gain fame in this village?";
            $subTitle = "Helping out in " .str_replace( " village", "", $GLOBALS['userdata'][0]['location'] );
        }
        else{
            throw new Exception("You can not do diplomacy here!");
        }

        $GLOBALS['template']->assign('msg', $description);
        $GLOBALS['template']->assign('subHeader', $subTitle);
        $GLOBALS['template']->assign('returnLabel', "Do Diplomacy");
        $GLOBALS['template']->assign('returnLink', 'id=' . $_GET['id'] . '&page=do_diplomacy');

        // Send to content
        $GLOBALS['template']->assign('contentLoad', './templates/content/reputation/reputationMain.tpl');

        // Show a message
//        $GLOBALS['page']->Message($description, $subTitle, 'id=' . $_GET['id'] . '&page=do_diplomacy', "Do Diplomacy");
    }

    // Function for running diplomacy
    private function run_diplomacy(){

        // Check if run is successfull
        if( $this->doQuickRun() )
        {

            // Get random message
            $message = "The stranger introduces himself to you. ";
            switch( random_int(1,3) ){
                case 1: $message .= "He wants you to go deliver a package to a house in the other end of
		                            the village. You successfully deliver the package, and therefore you are awarded:"; break;
                case 2: $message = "He wants you to help him move some heavy boxes from the street into his house.
		                            You help the man and are awarded:"; break;
                case 3: $message = "He wants you to go do some shopping for him. You do the shopping and are
		                            afterwards you are awarded:"; break;
            }

            // Check for gains
            if( $this->diplo_award > 0){
                $message .= "<br><b>".$this->diplo_award. "</b> Diplomacy points in ".$this->location;
            }

            // Costs
            $message .= "<br>This has cost you <b>".$this->staminaCost." stamina</b> and <b>".$this->chakraCost." chakra</b>";

            // Show a message
            $GLOBALS['page']->Message($message, $this->system_name, 'id=' . $_GET['id'] , "Return");

        }
    }

    // A function which, if the user is in a clan, reduces the cost if the clan has the upgrade
    private function setClanIncrease(){

        // Get the clan lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();

        // Check if user is in clan
        if( $clanLib->isUserClan($GLOBALS['userdata'][0]['clan']) ){

            // Get the clan
            $clan = $clanLib->getClan( $GLOBALS['userdata'][0]['clan'] );

            // Check the price reduction
            if( $clan[0]['diplomacy_increase'] > $GLOBALS['user']->load_time ){
                $this->ryoMin += 1;
                $this->ryoMax += 1;
                $this->dipMin += 1;
                $this->dipMax += 1;
            }

        }

    }

    // A function to increase the diplomacy in the case of high federal support
    private function setFedIncrease(){
        switch( $GLOBALS['userdata'][0]['federal_level'] ){
            case "Silver":
                $this->ryoMin = ceil($this->ryoMin*1.25);
                $this->ryoMax = ceil($this->ryoMax*1.25);
                $this->dipMin = ceil($this->dipMin*1.25);
                $this->dipMax = ceil($this->dipMax*1.25);
            break;
            case "Gold":
                $this->ryoMin = ceil($this->ryoMin*1.5);
                $this->ryoMax = ceil($this->ryoMax*1.5);
                $this->dipMin = ceil($this->dipMin*1.5);
                $this->dipMax = ceil($this->dipMax*1.5);
            break;
        }
    }
}

new diplomacy();