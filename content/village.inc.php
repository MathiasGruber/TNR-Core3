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

class townhall extends village {

    // Townhall constructor
    function __construct() {

        // Try doing stuff
        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Set important information neccesary
            $this->cpName = "village";
            $this->leaderName = "kage";
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
                    array("name" => "ANBU Squads", "href" => "?id=" . $_GET['id'] . "&act=anbuSquads"),
                    array("name" => "Bingo Book", "href" => "?id=" . $_GET['id'] . "&act=bBook"),
                    array("name" => "Bloodline Clinic", "href" => "?id=" . $_GET['id'] . "&act=clinic"),
                    array("name" => "Check Kage", "href" => "?id=" . $_GET['id'] . "&act=checkKage"),
                    array("name" => "Clans", "href" => "?id=" . $_GET['id'] . "&act=clans"),
                    array("name" => "Global Messages", "href" => "?id=" . $_GET['id'] . "&act=globalMessages"),
                    array("name" => "Grand Market", "href" => "?id=88"),
                    array("name" => "Kage Orders", "href" => "?id=" . $_GET['id'] . "&act=kOrders"),
                    array("name" => "Respect Bonuses", "href" => "?id=" . $_GET['id'] . "&act=respectBonus"),
                    array("name" => "Territories", "href" => "?id=" . $_GET['id'] . "&act=territories"),
                    array("name" => "Village Status", "href" => "?id=" . $_GET['id'] . "&act=vStatus"),
                    array("name" => "Villagers", "href" => "?id=" . $_GET['id'] . "&act=villagerList"),
                    array("name" => "Artisans", "href" => "?id=" . $_GET['id'] . "&act=artisansList"),
                    array("name" => "War Status", "href" => "?id=" . $_GET['id'] . "&act=warStatus")
                );

                // Add a link to the kage panel
                if( $this->isLeader ){
                    $menu[] = array("name" => "Kage Panel", "href" => "?id=" . $_GET['id'] . "&act=kagePanel");
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

                // Only for genin and up
                if( $GLOBALS['userdata'][0]['rank_id'] >= 2 ){
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
                else{
                    throw new Exception("At the bloodline clinic the doctors can help you unlock your genetic potential. They are required by law not to operate on anyone who has not yet achieved the rank of Genin at least.");
                }
            }
            elseif( $_GET['act'] == "bBook" ){
                if( !isset($_GET['act2']) || $_GET['act2'] == "" ){
                    $this->bingo_book();
                }
                elseif( $_GET['act2'] == "increase" ){
                    if( !isset($_POST['bounty_increase_amount']) ){
                        $this->input_bounty_increase();
                    }
                    else{
                        $this->do_increase_bounty( $GLOBALS['userdata'][0]['village'] );
                    }
                }
                elseif( $_GET['act2'] == "setTarget" ){
                    if( !isset($_POST['bounty_increase_amount']) ){
                        $this->input_set_target();
                    }
                    else{
                        if( $_POST['bounty_increase_amount'] >= 150000 ){
                            $this->do_increase_bounty( "SpecialBounty" );
                        }
                        else{
                            $GLOBALS['page']->Message("The amount specified was not large enough.", 'Bingo Book', 'id='.$_GET['id']);
                        }
                    }
                }
            }
            elseif( $_GET['act'] == "anbuSquads" ){
                if( isset( $_GET['aid'] ) && isset( $_GET['act2'] ) && $_GET['act2'] == "details" ){
                    $this->anbuDetails();
                }
                else{
                    $this->anbu();
                }
            }
            elseif( $_GET['act'] == "clans" ){
                if( isset( $_GET['cid'] ) && isset( $_GET['act2'] ) && $_GET['act2'] == "clandetail" ){
                    $this->clanDetails();
                }
                elseif( isset( $_GET['cid'] ) && isset( $_GET['act2'] ) && $_GET['act2'] == "agenda" ){
                    $this->clanAgenda();
                }
                else{
                    $this->clans();
                }
            }
            elseif( $_GET['act'] == "vStatus" ){ $this->status(); }
            elseif( $_GET['act'] == "hospitalSupply" ){ $this->userSupply("hospital"); }
            elseif( $_GET['act'] == "ramenSupply" ){ $this->userSupply("ramen"); }
            elseif( $_GET['act'] == "kOrders" ){ $this->orders(); }
            elseif( $_GET['act'] == "globalMessages" ){ $this->blueMessages(); }
            elseif( $_GET['act'] == "villagerList" ){ $this->users(); }
            elseif( $_GET['act'] == "artisansList" ){ $this->artisansList(); }
            elseif( $_GET['act'] == "territories" ){ $this->territoryList(); }
            elseif( $_GET['act'] == "respectBonus" ){ $this->respectBonuses(); }
            elseif( $_GET['act'] == "warStatus" ){ $this->warstatus(); }
            elseif( $_GET['act'] == "kagePanel" ){

                // Lock out anyone who's not kage
                if( $this->isLeader ){

                    // Extra try, so that we can send the back to the kage panel and not main hall
                    try{

                        // Get extra library
                        require_once(Data::$absSvrPath.'/libs/villageSystem/leaderOptions.php');
                        $this->leaderObject = new leaderOptions( $this );

                        // Decide which page to show
                        if( !isset( $_GET['act2'] ) ){

                            // Create the menu
                            $menu = array(
                                array("name" => "Edit Orders", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=orders"),
                                array("name" => "Village Upgrades", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=villagePoints"),
                                array("name" => "ANBU Squads", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=anbuHQ"),
                                //array("name" => "Kage Chat", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=kageChat"),
                                array("name" => "Village Clans", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=clanHQ"),
                                array("name" => "War Room", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=warRoom"),
                                //array("name" => "Territory Room", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=territoryRoom"),
                                array("name" => "Resign", "href" => "?id=" . $_GET['id'] . "&act=kagePanel&act2=kageResign")
                            );

                            $this->leaderObject->main_screen( $menu );
                        }
                        /*
                        elseif( $_GET['act2'] == "territoryRoom" ){
                            if ( isset($_POST['Submit']) ) {
                                $this->leaderObject->do_territory_challenge();
                            } else {
                                $this->leaderObject->show_territory_room();
                            }
                        }
                        */
                        elseif( $_GET['act2'] == "warRoom" ){

                            // Check global events if enabled
                            $globalSetting = $GLOBALS['database']->fetch_data("SELECT `character_cleanup` FROM `site_timer` WHERE `site_timer`.`script` = 'warsSwitch' LIMIT 1");
                            if( $globalSetting[0]['character_cleanup'] <= 0 ){
                                throw new Exception('War system has been temporarily disabled. Please try again later!');
                            }

                            // Show panel
                            if ( isset($_POST['Submit']) || isset($_GET['action']) ) {
                                $this->leaderObject->handle_war_submits();
                            } else {
                                $this->leaderObject->show_war_panel();
                            }
                        }
                        elseif( $_GET['act2'] == "orders" ){
                            if (!isset($_POST['Submit'])) {
                                $this->leaderObject->order_form();
                            } else {
                                $this->leaderObject->edit_orders();
                            }
                        }
                        elseif( $_GET['act2'] == "villagePoints" ){
                            $this->leaderObject->setAvailableOptions( array("regen","hospital","shop","anbu_bonus","wall_rob","wall_def") );
                            if( isset($_POST['Submit']) ) {
                                $this->leaderObject->villagePoint_spend();
                            }else {
                                $this->leaderObject->villagePoint_menu();
                            }
                        }
                        elseif( $_GET['act2'] == "kageResign" ){
                            if (!isset($_POST['Submit'])) {
                                $this->leaderObject->leader_resign_form();
                           } else {
                                $this->leaderObject->leader_resign_do();
                           }
                        }
                        elseif( preg_match("/(anbuHQ|createAnbu|details|editOrder|editSquad|removeSquad)/", $_GET['act2']) ){

                            // Get the ANBU library
                            if( $_GET['act2'] == "anbuHQ" ){
                                $this->leaderObject->showANBUforLeader();
                            }
                            elseif( $_GET['act2'] == "createAnbu" ){
                                if (!isset($_POST['Submit'])) {
                                     $this->leaderObject->createAnbu_form();
                                } else {
                                     $this->leaderObject->createAnbu_do();
                                }
                            }
                            elseif( $_GET['act2'] == "details" && isset( $_GET['aid'] )  ){
                                $this->anbuDetails();
                            }
                            elseif( $_GET['act2'] == "editOrder" && isset( $_GET['aid'] )  ){
                                if (!isset($_POST['Submit'])) {
                                     $this->leaderObject->anbu_order_form();
                                } else {
                                     $this->leaderObject->anbu_order_edit();
                                }
                            }
                            elseif( $_GET['act2'] == "editSquad" && isset( $_GET['aid'] )  ){
                                if (!isset($_POST['Submit'])) {
                                     $this->leaderObject->anbu_edit_form();
                                } else {
                                     $this->leaderObject->anbu_edit_do();
                                }
                            }
                            elseif( $_GET['act2'] == "removeSquad" && isset( $_GET['aid'] )  ){
                                if (!isset($_POST['Submit'])) {
                                     $this->leaderObject->anbu_remove_confirm();
                                } else {
                                     $this->leaderObject->anbu_remove_do();
                                }
                            }

                        }
                        elseif( preg_match("/(clanHQ|createClan|editClan|clandetail|agenda)/", $_GET['act2']) ){

                            // Get the ANBU library
                            if( $_GET['act2'] == "clanHQ" ){
                                $this->leaderObject->showClansForLeader();
                            }
                            elseif( $_GET['act2'] == "createClan" ){
                                if (!isset($_POST['Submit'])) {
                                     $this->leaderObject->createClan_form();
                                } else {
                                     $this->leaderObject->createClan_do();
                                }
                            }
                            elseif( $_GET['act2'] == "clandetail" && isset( $_GET['cid'] )  ){
                                $this->clanDetails();
                            }
                            elseif( $_GET['act2'] == "agenda" && isset( $_GET['cid'] )  ){
                                $this->clanAgenda();
                            }
                        }
                        /*
                        elseif( $_GET['act2'] == "kageChat" )
                        {

                            // Get libraries
                            require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
                            require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

                            // Instantiate chat class
                            $kageChat = new chatLib('tavern_leaders');

                            $kageChat->setupChatSystem(
                                array(
                                    "userTitleOverwrite" => $kageChat->getUserRank(),
                                    "tavernTable" => "tavern_leaders",
                                    "tableColumn" => "village",
                                    "tableSelect" => "KageUser",
                                    "chatName" => "Kage Chat",
                                    "smartyTemplate" => "contentLoad"
                                )
                            );

                            // Wrap the contentLoad in a page wrapper with this javascript library
                            if($GLOBALS['mf'] == 'yes')
                                $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts_mf.js");
                            else
                                $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts.js");

                        }
                        */
                    }
                    catch(Exception $e) {
                        $GLOBALS['database']->transaction_rollback( $e->getMessage() );
                        $GLOBALS['page']->Message( $e->getMessage() , 'village hall Error', 'id='.$_GET['id']."&act=".$_GET['act']);
                    }
                }
                else{
                    throw new Exception("You need to be kage to view these pages.");
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
            $GLOBALS['page']->Message( $e->getMessage() , 'village hall Error', 'id='.$_GET['id']);
        }
    }
}

new townhall();