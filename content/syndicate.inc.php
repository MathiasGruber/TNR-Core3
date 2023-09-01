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

require_once(Data::$absSvrPath.'/libs/villageSystem/villageLib.inc.php');

class syndicate extends village {

    //    Page constructor:
    function __construct() {

        // Try doing stuff
        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Set important information neccesary
            $this->cpName = "syndicate";
            $this->leaderName = "leader";
            $this->setSmartyDefinitions();

            // add user data to global array
            $this->addUserData();

            // Store village list locally
            $this->village_list = Data::$VILLAGES;

            // Check if user is leader
            $this->isLeader = 0;
            if ( $GLOBALS['userdata'][0]['leader'] === $GLOBALS['userdata'][0]['username']) {

                // Check is leader
                $this->isLeader = 1;

            }

            // Figure out which page to show
            if( !isset($_GET['act']) || $_GET['act'] == ""){
                // Create the menu
                $menu = array(
                    array("name" => "Bloodline Clinic", "href" => "?id=" . $_GET['id'] . "&act=clinic"),
                    array("name" => "Check Leader", "href" => "?id=" . $_GET['id'] . "&act=checkKage"),
                    array("name" => "Global Messages", "href" => "?id=" . $_GET['id'] . "&act=globalMessages"),
                    array("name" => "Grand Market", "href" => "?id=88"),
                    array("name" => "Leader Orders", "href" => "?id=" . $_GET['id'] . "&act=kOrders"),
                    array("name" => "Mercenary Work", "href" => "?id=" . $_GET['id'] . "&act=hits"),
                    array("name" => "Outlaws", "href" => "?id=" . $_GET['id'] . "&act=villagerList"),
                    array("name" => "Respect Bonuses", "href" => "?id=" . $_GET['id'] . "&act=respectBonus"),
                    array("name" => "Syndicate Status", "href" => "?id=" . $_GET['id'] . "&act=vStatus"),
                    array("name" => "Territories", "href" => "?id=" . $_GET['id'] . "&act=territories")
                );

                // Add a link to the kage panel
                if( $this->isLeader ){
                    $menu[] = array("name" => "Leader Panel", "href" => "?id=" . $_GET['id'] . "&act=leaderPanel");
                }

                // Show main screen
                $this->main_screen( $menu );
            }
            elseif( $_GET['act'] == "checkKage" ){
                if( isset($_GET['doChallenge']) && $_GET['doChallenge'] !== "" ){
                    $this->challenge();
                }
                else{
                    $this->check_kage();
                }
            }
            elseif( $_GET['act'] == "clinic" ){
                if( !isset($_GET['act2']) || $_GET['act2'] == "" ){
                    $this->show_clinic();
                }
                elseif( $_GET['act2'] == "removeBloodline" ){
                    $this->do_remove_bloodline();
                }
                elseif( $_GET['act2'] == "sealBloodline" ){
                    if( isset($_POST['SubmitItem']) ){
                        $this->do_user_bloodline_item();
                    }
                    else{
                        $this->do_choose_bloodline_item();
                    }
                }
            }
            elseif( $_GET['act'] == "hits" ){ $this->bingo_book(); }
            elseif( $_GET['act'] == "vStatus" ){ $this->status(); }
            elseif( $_GET['act'] == "hospitalSupply" ){ $this->userSupply("hospital"); }
            elseif( $_GET['act'] == "ramenSupply" ){ $this->userSupply("ramen"); }
            elseif( $_GET['act'] == "kOrders" ){ $this->orders(); }
            elseif( $_GET['act'] == "globalMessages" ){ $this->blueMessages(); }
            elseif( $_GET['act'] == "villagerList" ){ $this->users(); }
            elseif( $_GET['act'] == "territories" ){ $this->territoryList(); }
            elseif( $_GET['act'] == "respectBonus" ){ $this->respectBonuses(); }
            elseif( $_GET['act'] == "leaderPanel" ){

                // Lock out anyone who's not kage
                if( $this->isLeader ){

                    try{

                        // Get extra library
                        require_once(Data::$absSvrPath.'/libs/villageSystem/leaderOptions.php');
                        $this->leaderObject = new leaderOptions( $this );

                        // Decide which page to show
                        if( !isset( $_GET['act2'] ) ){

                            // Create the menu
                            $menu = array(
                                array("name" => "Edit Orders", "href" => "?id=" . $_GET['id'] . "&act=leaderPanel&act2=orders"),
                                array("name" => "Syndicate Upgrades", "href" => "?id=" . $_GET['id'] . "&act=leaderPanel&act2=syndicatePoints"),
                                //array("name" => "Territory Overview", "href" => "?id=" . $_GET['id'] . "&act=leaderPanel&act2=territoryRoom"),
                                array("name" => "Resign", "href" => "?id=" . $_GET['id'] . "&act=leaderPanel&act2=leaderResign")
                            );

                            $this->leaderObject->main_screen( $menu );
                        }
                        elseif( $_GET['act2'] == "territoryRoom" ){
                            if ( isset($_POST['Submit']) ) {
                                //$this->leaderObject->do_territory_challenge();
                            } else {
                                //$this->leaderObject->show_territory_room();
                            }
                        }
                        elseif( $_GET['act2'] == "orders" ){
                            if (!isset($_POST['Submit'])) {
                                $this->leaderObject->order_form();
                            } else {
                                $this->leaderObject->edit_orders();
                            }
                        }
                        elseif( $_GET['act2'] == "syndicatePoints" ){
                            $this->leaderObject->setAvailableOptions( array("wall_def","sabotage","damageInc") );
                            if( isset($_POST['Submit']) ) {
                                $this->leaderObject->villagePoint_spend();
                            }else {
                                $this->leaderObject->villagePoint_menu();
                            }
                        }
                        elseif( $_GET['act2'] == "leaderResign" ){
                            if (!isset($_POST['Submit'])) {
                                $this->leaderObject->leader_resign_form();
                           } else {
                                $this->leaderObject->leader_resign_do();
                           }
                        }
                    }
                    catch(Exception $e) {
                        $GLOBALS['database']->transaction_rollback( $e->getMessage() );
                        $GLOBALS['page']->Message( $e->getMessage() , 'Syndicate Error', 'id='.$_GET['id']."&act=".$_GET['act']);
                    }
                }
                else{
                    throw new Exception("You need to be leader to view these pages.");
                }

            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch(Exception $e) {

            // Rollback possible transactions
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );

            // Show error message
            $GLOBALS['page']->Message( $e->getMessage() , 'Syndicate Error', 'id='.$_GET['id']);
        }
    }
}

new syndicate();