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

// Used for chaining territories// Include map library
require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');

// Leader options class
class leaderOptions {

    //  Constructor. Create reference to master object
    public function __construct($master) {
        $this->master = $master;
    }

    //  Show the main screen overview
    public function main_screen($menu) {

        // Show the menu
        $GLOBALS['template']->assign('subHeader', ucfirst($this->master->cpName) . ' ' . $this->master->leaderName . ' Hall');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', 3);
        $GLOBALS['template']->assign('subTitle_info', "This is where you can manage the structure of the " . $this->master->cpName . ".");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('buttonLayout', "SUBMIT");
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
        $GLOBALS['template']->assign('returnLink', '?id=' . $_GET['id'] . "&act" . $_GET['act']);
    }

    //  Orders Form
    public function order_form() {
        $GLOBALS['page']->UserInput(
            "Write the orders for " . $this->master->cpName . " in the field below.", "Edit " . $this->master->cpName . " Orders",
            array(
                array("infoText" => "",
                      "inputFieldName" => "orders",
                      "type" => "textarea",
                      "inputFieldValue" => $GLOBALS['userdata'][0]['orders'],
                      "maxlength" => 1500
                )
            ), array(
            "href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2'],
            "submitFieldName" => "Submit",
            "submitFieldText" => "Submit Orders"), "Return"
        );
    }

    //  Do edit orders
    public function edit_orders() {
        if (isset($_POST['orders'])) {
            if (strlen($_POST['orders']) < 3000) {
                $order_text = functions::store_content($_POST['orders']);
                if ($order_text != '') {
                    $orders = $GLOBALS['database']->fetch_data("SELECT `orders` FROM `villages` WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "'");
                    if ($orders != '0 rows') {
                        //	Update:
                        $GLOBALS['database']->execute_query("UPDATE `villages` SET `orders` = '" . $order_text . "' WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");
                        $GLOBALS['page']->Message("Your orders have been updated", "Edit " . $this->master->cpName . " Orders", 'id=' . $_GET['id'] . "&act=" . $_GET['act']);
                    } else {
                        throw new Exception("Orders could not be found in database for update, please report to coder.");
                    }
                } else {
                    //	Update Orders:
                    $GLOBALS['database']->execute_query("UPDATE `squads` SET `orders` = '' WHERE `leader_uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                    $GLOBALS['page']->Message("The orders have been cleared", "Edit " . $this->master->cpName . " Orders", 'id=' . $_GET['id'] . "&act=" . $_GET['act']);
                }
            } else {
                throw new Exception("Your orders are too long.");
            }
        } else {
            throw new Exception("No orders were specified.");
        }
    }

    //  Calculate prices of the different updates
    public function calcUpdatePrices($levels) {

        // Array to store information
        $updatePrices = array();

        // First regen
        cachefunctions::deleteVillageActiveMemberCount( $GLOBALS['userdata'][0]['village'] );
        $userCounts = cachefunctions::getVillageActiveMemberCount($GLOBALS['userdata'][0]['village']);
        $totalUsers = $userCounts[0]['chuunin_count'] + $userCounts[0]['jounin_count'] + $userCounts[0]['sj_count'];
        $allStructure = $GLOBALS['database']->fetch_data("SELECT SUM(`regen_level`) as `totalRegen` FROM `village_structures`");

        // Level factor
        $usersFactor =
            0.8 * $userCounts[0]['chuunin_count'] +
            1.0 * $userCounts[0]['jounin_count'] +
            1.2 * $userCounts[0]['sj_count'];

        // Do Calculation
        $partA = $usersFactor * 5;
        $partB = (( $partA*2 ) + ( 250 * $GLOBALS['userdata'][0]['owned_territories'] )) * 0.5;
        $partC = $allStructure[0]["totalRegen"] * $levels[0]['regen_level'] * 0.25;

        $regenprice = ($partA + $partB + $partC) * 0.5 + $levels[0]['regen_level'] * $levels[0]['regen_level'] / 4;
        $updatePrices["regen"] = array( "lvl" => $levels[0]['regen_level'], "name" => "Village regeneration. ", "price" => floor($regenprice), "down" => 0);

        // Hospital
        $price = ($levels[0]['hospital_level'] < 12) ? floor($levels[0]['hospital_level'] * $usersFactor + 250) : 0;
        $updatePrices["hospital"] = array( "lvl" => $levels[0]['hospital_level'], "name" => "Improve village hospital", "price" => floor($price), "down" => floor( $price * 0.5 ));

        // Shop
        $price = ($levels[0]['shop_level'] < 5) ? floor($levels[0]['shop_level'] * $usersFactor + 100) : 0;
        $updatePrices["shop"] = array("lvl" => $levels[0]['shop_level'], "name" => "Improve village shop", "price" => floor($price), "down" => floor( $price * 0.5 ));

        // Anbu squads
        $price = floor( $levels[0]['anbu_bonus_level'] * $usersFactor + 50)  ;
        $updatePrices["anbu_bonus"] = array("lvl" => $levels[0]['anbu_bonus_level'], "name" => "Increase max ANBU squads", "price" => floor($price), "down" => floor( $price * 0.5 ) );

        // Sabotage
        $updatePrices["sabotage"] = array("lvl" => 0, "name" => "Sabotage non-regen structure of random village", "price" => 2000, "down" => 0 );

        // Damage increase
        if( $levels[0]['damageIncTimer'] > $GLOBALS['user']->load_time ){
            $wait = "<br>Time left: ".functions::convert_time(($levels[0]['damageIncTimer'] - $GLOBALS['user']->load_time), "damageTimer");
            $updatePrices["damageInc"] = array("lvl" => 0, "name" => "Damage Increase is active! ".$wait, "price" => 0, "down" => 0 );
        }
        else{
            $updatePrices["damageInc"] = array("lvl" => 0, "name" => "Increase all outlaw damage for 24 hours", "price" => ceil($totalUsers / 2), "down" => 0 );
        }

        // Walls
        if ($levels[0]['wall_rob_level'] + $levels[0]['wall_def_level'] < 10) {

            $price = floor( $levels[0]['wall_rob_level'] * $usersFactor + 200);
            $updatePrices["wall_rob"] = array("lvl" => $levels[0]['wall_rob_level'], "price" => floor($price), "down" => floor( $price * 0.5 ));

            $price = floor( $levels[0]['wall_def_level'] * $usersFactor + 200);
            $updatePrices["wall_def"] = array("lvl" => $levels[0]['wall_def_level'], "price" => floor($price), "down" => floor( $price * 0.5 ));
        } else {
            $updatePrices["wall_rob"] = array("lvl" => $levels[0]['wall_rob_level'], "name" => "Village walls<br>(Lower the chances of robbing)", "price" => 0, "down" => 0);
            $updatePrices["wall_def"] = array("lvl" => $levels[0]['wall_def_level'], "name" => "Village walls<br>(Increase defense inside the village)", "price" => 0, "down" => 0);
        }

        // Wall descriptions
        if( $GLOBALS['userdata'][0]['village'] == "Syndicate" ){
            $updatePrices["wall_def"]['name'] = "Sydicate walls<br>(Increase defense)";
            $updatePrices["wall_rob"]['name'] = "Sydicate walls<br>(Lower the chances of robbing)";
        }
        else{
            $updatePrices["wall_def"]['name'] = "Village walls<br>(Increase defense inside the village)";
            $updatePrices["wall_rob"]['name'] = "Village walls<br>(Lower the chances of robbing)";
        }

        // Reduce price based on territories owned by village
        $reducePerc = $GLOBALS['userdata'][0]['owned_territories'] * 2.5;
        if( $reducePerc > 25 ){
            $reducePerc = 25;
        }
        $reducePerc = (100 - (25 - $reducePerc))/100;

        // Remove the options that are not available
        $returnOptions = array();
        foreach( $this->options as $option ){
            if( isset( $updatePrices[$option] ) ){

                // Reduce the prices
                $updatePrices[$option]['price'] = ceil( $reducePerc * $updatePrices[$option]['price'] );
                $updatePrices[$option]['down'] = ceil( ($reducePerc * $updatePrices[$option]['down'] / 2) );

                // Set the option to the return array
                $returnOptions[$option] = $updatePrices[$option];
            }
        }

        // Return the options
        return $returnOptions;
    }

    //  Function for setting the available options for upgrades
    public function setAvailableOptions( $options ){
        $this->options = $options;
    }

    // Update the syndicate regeneration
    private function updateSyndicateRegeneration(){
        $allStructure = $GLOBALS['database']->fetch_data("SELECT AVG(`regen_level`) as `avg_regen` FROM `village_structures` WHERE `name` != 'Syndicate'");
        if( $allStructure !== "0 rows" ){
            $GLOBALS['database']->execute_query("UPDATE `village_structures` SET `regen_level` = '".$allStructure[0]['avg_regen']."' WHERE `name` = 'Syndicate' LIMIT 1");

        }
    }

    // Update territory count
    private function updateTerritoryCount(){
        $own_terr = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) as `count` FROM `locations` WHERE `owner` = '" . $GLOBALS['userdata'][0]['village'] . "'");
        if( $own_terr[0]['count'] !== $GLOBALS['userdata'][0]['owned_territories'] ){
            $GLOBALS['database']->execute_query("UPDATE `villages` SET `owned_territories` = '" . $own_terr[0]['count'] . "' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");
        }
    }

    //  Village point menu:
    public function villagePoint_menu() {
        if ($GLOBALS['userdata'][0]['status'] == "awake") {

            // Update the territory count
            $this->updateTerritoryCount();

            // Get the levels of the structures
            $levels = $GLOBALS['database']->fetch_data("SELECT * FROM `village_structures` WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");
            $GLOBALS['template']->assign('totalPoints', $GLOBALS['userdata'][0]['points']);
            $GLOBALS['template']->assign('totalTerritories', $GLOBALS['userdata'][0]['owned_territories']);

            // Calculate prices
            $updates = $this->calcUpdatePrices($levels);
            $GLOBALS['template']->assign('updates', $updates);

            // Get the template
            $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/villagePoints.tpl');
        } else {
            throw new Exception("You have to be awake to use village points. You are currently " . $GLOBALS['userdata'][0]['status']);
        }
    }

    //  Spend village points
    public function villagePoint_spend() {
        if ($GLOBALS['userdata'][0]['status'] == "awake") {

            // Update syndicate regen at this point
            $this->updateSyndicateRegeneration();

            // Check for main point purchases
            if (isset($_POST['radio'])) {

                // Get the levels of the structures
                $levels = $GLOBALS['database']->fetch_data("SELECT * FROM `village_structures` WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");
                $totalPoints = $GLOBALS['userdata'][0]['points'];

                // Calculate prices
                $updates = $this->calcUpdatePrices($levels);

                // Go through updates and see if radio-submission matches any of them
                $foundUpdate = false;
                foreach( $updates as $key => $value ){
                    if( stristr($_POST['radio'], $key ) ){

                        // Determine if up or down
                        $direction = false;
                        if( $_POST['radio'] == $key."_up" ){
                            $direction = 1;
                            $price = $value['price'];
                            $newLevel = $value['lvl']+1;
                            $descriptor = "upgrade";
                        }
                        elseif( $_POST['radio'] == $key."_down"  ){
                            $direction = -1;
                            $price = ($value['down']/(-2));
                            $newLevel = $value['lvl']-1;
                            $descriptor = "upgrade";
                        }
                        else{
                            throw new Exception("Could not determine whether to increase or decrease update");
                        }

                        // We found the udpate, yay
                        $foundUpdate = true;

                        // Check the udpate
                        if ( $GLOBALS['userdata'][0]['points'] >= $price ) {

                            // Update & Set message
                            $message = "";
                            switch( $key ){
                                case "sabotage":
                                    $message = $this->sabotageRandom( $price );
                                break;
                                case "damageInc":
                                    $message = $this->increaseDamageTimer( $price );
                                break;
                                default:
                                    $GLOBALS['database']->execute_query("UPDATE `village_structures` SET `".$key."_level` = '".$newLevel."' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");
                                    $GLOBALS['database']->execute_query("UPDATE `villages` SET `points` = `points` - '" . $price . "' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");
                                    if($price > 0)
                                        $message = "You have purchased this ".$descriptor.": ".$value['name'];
                                    else
                                        $message = "You have sold this ".$descriptor.": ".$value['name'];
                                    break;
                            }

                            // Message for the user
                            $GLOBALS['page']->Message($message, "Upgrades", 'id=' . $_GET['id'] . "&act=" . $_GET['act']. "&act2=" . $_GET['act2'] );

                        } else {

                            throw new Exception("You do not have enough points for this upgrade");
                        }
                    }
                }

                // Check if the udpate was set
                if( $foundUpdate == false ){
                    throw new Exception("Could not identify the upgrade you're trying to update");
                }
            }
            else{
                throw new Exception("Could not intepret your request.");
            }
        } else {
            throw new Exception("You have to be awake to use village points. You are currently " . $GLOBALS['userdata'][0]['status']);
        }
    }

    // A function that will increase the damage of the village for 24 hours
    private function increaseDamageTimer( $price ){

        // Update database
        $GLOBALS['database']->execute_query("UPDATE `village_structures` SET `damageIncTimer` = '".($GLOBALS['user']->load_time+24*3600)."' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");
        $GLOBALS['database']->execute_query("UPDATE `villages` SET `points` = `points` - '" . $price . "' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");

        // Return message
        return "The damage of ".$GLOBALS['userdata'][0]['name']." will be increase for the next 24 hours.";

    }

    // A function that will sabotage a random village
    private function sabotageRandom( $price ){

        // Get random village
        $village = Data::$VILLAGES[ random_int(1,count(Data::$VILLAGES)-1) ];

        // Check previous sabotages
        $previous = $GLOBALS['database']->fetch_data("SELECT * FROM `users_actionLog` WHERE `action` = 'sabotageVillage' AND `time` > '".($GLOBALS['user']->load_time-12*3600)."' LIMIT 1");
        if( $previous == "0 rows" ){

            // Get the structure
            $structure = "regen";
            $max = count(Data::$STRUCTURENAMES)-1;
            while( $structure == "regen" ){
                $structure = Data::$STRUCTURENAMES[ random_int(0, $max) ];
            }

            // Get a pretty name
            $prettyName = $structure;
            switch( $structure ){
                case "wall_def": $prettyName = "wall defences"; break;
                case "wall_def": $prettyName = "robbing defences"; break;
            }

            // Tell everyone
            $GLOBALS['database']->execute_query("UPDATE `users` SET `notifications` = CONCAT('id:17;duration:none;text:Syndicate has sabotaged ".$village."\'s ".$prettyName.";dismiss:yes;buttons:none;select:none;//',`notifications`) WHERE `village` = '" . $village . "' OR `village` = 'Syndicate' ");

            // Update database
            $GLOBALS['database']->execute_query("UPDATE `village_structures` SET `".$structure."_level` = `".$structure."_level` - 1 WHERE `name` = '" . $village . "' AND `".$structure."_level` > 0 LIMIT 1");
            $GLOBALS['database']->execute_query("UPDATE `villages` SET `points` = `points` - '" . $price . "' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");

            // Log an action
            functions::log_user_action($_SESSION['uid'], "sabotageVillage", $GLOBALS['userdata'][0]['village']."->".$village );

            // Return message
            return "You have sabotaged the ".$prettyName." of ".$village.".";
        }
        else{
            // Return message
            return "You need to wait at least 12 hours between sabotages.";
        }
    }

    // Check the amount of ANBU squads in village
    public function setClanLimitData(){
        $this->villageCount = cachefunctions::getVillageCount();
        $this->currentCount = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) as vcount FROM `clans` WHERE `village` = '" . $GLOBALS['userdata'][0]['village'] . "'");
        $this->currentCount = $this->currentCount[0]['vcount'];
        $this->maxCount = (floor($this->currentCount / 1000) + 1) + 5;
        $this->clanCost = 1000;
    }

    // Show Clans
    public function showClansForLeader( ){

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();

        // Set data related to clan creation
        $this->setClanLimitData();

        // Show list of ANBUs
        $clanLib->showClanList(
                $GLOBALS['userdata'][0]['village'] ,
                true ,
                false,
                'Using '.$this->clanCost.' village funds you can create a new clan in your village. There are currently '.$this->currentCount." clans in your village, and you can have no more than ".$this->maxCount. " given the number of villagers."

        );
    }

    // Form for creating a new ANBU squad
    public function createClan_form(){

        // Set data related to clan creation
        $this->setClanLimitData();

        // Check numbers
        if ( $this->currentCount < $this->maxCount ) {

            // Get the levels of the structures
            if( $GLOBALS['userdata'][0]['points'] > $this->clanCost ){

                // Get element
                require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
                $elements = array_map(function($word) {
                    return ucfirst($word);
                }, Elements::$mainElements);

                // Create the fields to be shown
                $inputFields = array(
                    array("infoText" => "Clan Name", "inputFieldName" => "clan", "type" => "input", "inputFieldValue" => ""),
                    array("infoText" => "Leader Name", "inputFieldName" => "leader", "type" => "input", "inputFieldValue" => ""),
                    array(
                        "infoText" => "Clan Element",
                        "inputFieldName"=>"clanElement",
                        "type"=>"select",
                        "inputFieldValue"=> $elements
                    ),
                    array("infoText" => "Agenda", "inputFieldName" => "agenda", "type" => "textarea", "inputFieldValue" => "")

                );

                // Show user prompt
                $GLOBALS['page']->UserInput(
                    array('message'=>"Here you can create a new clan and appoint it's leader. The leader is in charge of picking the members in the clan etc.",'hidden'=>'yes'), // Information
                    "Create Village Clan", // Title
                    $inputFields, // input fields
                    array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2'], "submitFieldName" => "Submit","submitFieldText" => "Create Clan"), // Submit button
                    "Return" // Return link name
                );

            }
            else{
                throw new Exception("You do not have the required ".$this->clanCost." village funds.");
            }
        }
        else{
            throw new Exception("Your village already has the maximum permissible number of clans");
        }
    }

    // Do create new ANBU squad
    public function createClan_do(){

        // Set data related to clan creation
        $this->setClanLimitData();

        // Get element
        require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

        // Check that chosen element exists
        if(array_key_exists($_POST['clanElement'], Elements::$mainElements)){
            $_POST['clanElement'] = Elements::$mainElements[ $_POST['clanElement'] ];
        }
        else{
            throw new Exception( "Could not identify the clan element" );
        }

        // Check numbers
        if ( $this->currentCount < $this->maxCount ) {

            // Get the levels of the structures
            if( $GLOBALS['userdata'][0]['points'] > $this->clanCost ){

                // get the Leader information
                $leader = $GLOBALS['database']->fetch_data("
                    SELECT `id`,`username`,`rank_id`,`clan`,`users_loyalty`.`village`
                    FROM `users`,`users_loyalty`, `users_statistics`, `users_preferences`
                    WHERE
                        `username` = '" . $_POST['leader'] . "' AND
                        `users_loyalty`.`uid` = `users`.`id` AND
                        `users_statistics`.`uid` = `users`.`id` AND
                        `users_preferences`.`uid` = `users`.`id`
                    LIMIT 1");
                if ($leader != '0 rows') {

                    // Fix the orders & squad name
                    if( isset( $_POST['clan'] ) && isset( $_POST['agenda'] ) ){

                        // Fix up for storage
                        $_POST['squad'] = functions::store_content( $_POST['clan'] );
                        $_POST['orders'] = functions::store_content( $_POST['agenda'] );

                        // Make sure squad doesn't exist already
                        $clan = $GLOBALS['database']->fetch_data("SELECT `id` FROM `clans` WHERE `name` = '" . $_POST['clan'] . "' LIMIT 1");
                        if( $clan == "0 rows" ){

                            // Check rank ID
                            if ($leader[0]['rank_id'] >= 3) {

                                // Get the anbu lib
                                require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
                                $clanLib = new clanLib();

                                // Check if already in clan
                                if( !$clanLib->isUserClan($leader[0]['clan']) ) {

                                    // Check village
                                    if ( $leader[0]['village'] == $GLOBALS['userdata'][0]['village']) {

                                        // Do create the new squad
                                        $squadID = $clanLib->insertClan( $leader[0]['village'], $_POST['squad'] , $leader[0]['id'], $_POST['orders'], $_POST['clanElement']);

                                        // Set the leader to be part of this ANBU
                                        $clanLib->updateClanData( $leader[0]['id'] , $squadID );

                                        // Reduce points
                                        $GLOBALS['database']->execute_query("UPDATE `villages` SET `points` = `points` - '" . $this->clanCost . "' WHERE `name` = '" . $GLOBALS['userdata'][0]['name'] . "' LIMIT 1");

                                        // Show message to the kage
                                        $GLOBALS['page']->Message("The clan has been created", "Clan Created", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] );

                                    }
                                    else{
                                        throw new Exception( "The leader you specified is not in this village." );
                                    }
                                }
                                else{
                                    throw new Exception( "The leader you specified is already in a clan, or has chosen not to be in clans" );
                                }
                            }
                            else{
                                throw new Exception( "The leader you specified is not high enough in rank." );
                            }
                        }
                        else{
                            throw new Exception( "A clan with this name already exists." );
                        }
                    }
                    else{
                        throw new Exception( "You must specify both a clan name and agenda." );
                    }
                }
                else{
                    throw new Exception( "Could not find any user with the username: ".$_POST['leader'] );
                }

            }
            else{
                throw new Exception("You do not have the required ".$this->clanCost." village funds.");
            }
        }
        else{
            throw new Exception("Your village already has the maximum permissible number of clans");
        }
    }

    // Check the amount of ANBU squads in village
    public function setAnbuLimitData(){
        $this->villageCount = cachefunctions::getVillageCount();
        $this->levels = $GLOBALS['database']->fetch_data("SELECT * FROM `village_structures` WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");
        $this->squadno = (floor($this->villageCount[0][ strtolower( $GLOBALS['userdata'][0]['village'] ).'_vcount'] / 1000) + 1) + $this->levels[0]['anbu_bonus_level'];
    }

    // Show ANBU squads
    public function showANBUforLeader( ){

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
        $anbuLib = new anbuLib();

        // Get users in village & calculate number of squads available
        $this->setAnbuLimitData();

        // Show list of ANBUs
        $anbuLib->showAnbuList(
                $GLOBALS['userdata'][0]['village'] ,
                true ,
                'Each village can have a certain number of ANBU squads based on their population<br>
                Your village has ' . $this->villageCount[0][ strtolower( $GLOBALS['userdata'][0]['village'] ).'_vcount' ] . ' inhabitants and can have <b>' . $this->squadno . '</b> Squad(s) '
        );
    }

    // Form for creating a new ANBU squad
    public function createAnbu_form(){

        // Get users in village & calculate number of squads available
        $this->setAnbuLimitData();

        // Get the number of current squads
        $squads = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `squads` FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['village'] . "'");

        if ( $squads[0]['squads'] < $this->squadno ) {

            // Create the fields to be shown
            $inputFields = array(
                array("infoText" => "Squad Name", "inputFieldName" => "squad", "type" => "input", "inputFieldValue" => ""),
                array("infoText" => "Leader Name", "inputFieldName" => "leader", "type" => "input", "inputFieldValue" => ""),
                array("infoText" => "Orders", "inputFieldName" => "orders", "type" => "textarea", "inputFieldValue" => "")
            );

            // Show user prompt
            $GLOBALS['page']->UserInput(
                array('message'=>"Here you can create a new ANBU squad and appoint it's leader. The ANBU leader is in charge of picking the members in the squad.",'hidden'=>'yes'), // Information
                "Create ANBU squad", // Title
                $inputFields, // input fields
                array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2'], "submitFieldName" => "Submit","submitFieldText" => "Create ANBU"), // Submit button
                "Return" // Return link name
            );
        }
        else{
            throw new Exception("Your village already has the maximum number of ANBU squads in use");
        }
    }

    // Do create new ANBU squad
    public function createAnbu_do(){

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
        $anbuLib = new anbuLib();

        // Get users in village & calculate number of squads available
        $this->setAnbuLimitData();

        // Get the number of current squads
        $squads = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `squads` FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['village'] . "'");
        if ( $squads[0]['squads'] < $this->squadno ) {

            // get the Leader information
            $leader = $GLOBALS['database']->fetch_data("
                SELECT `id`,`username`,`rank_id`,`anbu`,`users_loyalty`.`village`
                FROM `users`,`users_loyalty`, `users_statistics`, `users_preferences`
                WHERE
                    `username` = '" . $_POST['leader'] . "' AND
                    `users_loyalty`.`uid` = `users`.`id` AND
                    `users_statistics`.`uid` = `users`.`id` AND
                    `users_preferences`.`uid` = `users`.`id`
                LIMIT 1");
            if ($leader != '0 rows') {

                // Fix the orders & squad name
                if( isset( $_POST['squad'] ) && isset( $_POST['orders'] ) ){

                    // Fix up for storage
                    $_POST['squad'] = functions::store_content( $_POST['squad'] );
                    $_POST['orders'] = functions::store_content( $_POST['orders'] );

                    // Make sure squad doesn't exist already
                    $squad = $GLOBALS['database']->fetch_data("SELECT `id` FROM `squads` WHERE `name` = '" . $_POST['squad'] . "' LIMIT 1");

                    if( $squad == "0 rows" ){

                        // Check rank ID
                        if ($leader[0]['rank_id'] >= 3) {

                            // Check ANBU
                            if( !$anbuLib->isUserAnbu($leader[0]['anbu']) ) {

                                // Check village
                                if ( $leader[0]['village'] == $GLOBALS['userdata'][0]['village']) {

                                    // Check username
                                    if ( $leader[0]['username'] != $GLOBALS['userdata'][0]['username']) {

                                        // Do create the new squad
                                        $squadID = $anbuLib->createAnbuSquad( $leader[0]['village'], $_POST['squad'] , $leader[0]['id'], $_POST['orders']);

                                        // Set the leader to be part of this ANBU
                                        $anbuLib->setAnbuSquad( $leader[0]['id'] , $squadID );

                                        // Show message to the kage
                                        $GLOBALS['page']->Message("The ANBU squad has been created", "ANBU Created", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] );

                                    }
                                    else{
                                        throw new Exception( "The kage cannot be the leader or a member of an ANBU squad." );
                                    }
                                }
                                else{
                                    throw new Exception( "The leader you specified is not in this village." );
                                }
                            }
                            else{
                                throw new Exception( "The leader you specified is already in an ANBU, or has chosen not to allow ANBU" );
                            }
                        }
                        else{
                            throw new Exception( "The leader you specified is not high enough in rank." );
                        }
                    }
                    else{
                        throw new Exception( "A squad with this name already exists." );
                    }
                }
                else{
                    throw new Exception( "You must specify both a squad name and squad orders." );
                }
            }
            else{
                throw new Exception( "Could not find any user with the username: ".$_POST['leader'] );
            }
        }
        else{
            throw new Exception("Your village already has the maximum number of ANBU squads in use");
        }
    }

    //	Edit ANBU orders form
    public function anbu_order_form() {
        $squads = $GLOBALS['database']->fetch_data("SELECT `orders`,`name` FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['name'] . "' AND `id` = '" . $_GET['aid'] . "' LIMIT 1");
        if ($squads != '0 rows') {
            $GLOBALS['page']->UserInput(
                "Write the orders for " .  $squads[0]['name'] . " in the field below.",
                "Edit " .  $squads[0]['name'] . " Orders",
                    array(
                array("infoText" => "", "inputFieldName" => "orders", "type" => "textarea", "inputFieldValue" => $squads[0]['orders'] )
                    ), array(
                "href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']. "&aid=" . $_GET['aid'],
                "submitFieldName" => "Submit",
                "submitFieldText" => "Submit Orders"), "Return"
            );
        } else {
            throw new Exception("This squad does not exist, or it\'s data could not be retrieved");
        }
    }

    // Do edit the orders
    public function anbu_order_edit() {
        if (isset($_POST['orders']) && isset($_GET['aid'])) {
            $squads = $GLOBALS['database']->fetch_data("SELECT `orders`,`name` FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['village'] . "' AND `id` = '" . $_GET['aid'] . "' LIMIT 1");
            if ($squads != '0 rows') {
                if ($GLOBALS['database']->execute_query("UPDATE `squads` SET `orders` = '" . functions::store_content($_POST['orders']) . "' WHERE `id` = '" . $_GET['aid'] . "' AND `village` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1")) {

                    // Show message to the kage
                    $GLOBALS['page']->Message("The orders for this squad have been updated", "Orders Updated", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] );

                } else {
                    throw new Exception("An error occured while updating the orders");
                }
            } else {
                throw new Exception("This squad does not exist, or it\'s data could not be retrieved");
            }
        }
        else{
            throw new Exception("Could not figure out which orders your're trying to udpate.");
        }
    }

    //	Edit ANBU form
    public function anbu_edit_form() {

        // Check that squad ID is set
        if (isset($_GET['aid'])) {

            // Check if squad exists
            $squads = $GLOBALS['database']->fetch_data("SELECT `orders`,`name` FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['name'] . "' AND `id` = '" . $_GET['aid'] . "' LIMIT 1");
            if ($squads != '0 rows') {

                $GLOBALS['page']->UserInput(
                    "Change the leader for the squad '" .  $squads[0]['name'] . "' in the field below. The current leader will be kicked out of the squad.",
                    "Edit " .  $squads[0]['name'] ,
                        array(
                    array("infoText" => "", "inputFieldName" => "newLeader", "type" => "input", "inputFieldValue" => "" )
                        ), array(
                    "href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&act2=" . $_GET['act2']. "&aid=" . $_GET['aid'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Change Leader"),
                    "Return"
                );

            } else {
                throw new Exception("This squad does not exist, or it\'s data could not be retrieved");
            }
        } else {
            throw new Exception("No squad was specified");
        }
    }

    // Function for updating squad leader
    public function anbu_edit_do() {

        // Check that squad ID is set
        if (isset($_GET['aid'])) {

            // Check if the new leader is set
            if (isset($_POST['newLeader'])) {

                // Check if squad exists
                $squads = $GLOBALS['database']->fetch_data("SELECT * FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['name'] . "' AND `id` = '" . $_GET['aid'] . "' LIMIT 1");
                if ($squads != '0 rows') {

                    // Get the anbu lib
                    require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
                    $anbuLib = new anbuLib();

                    // Pass the squad data
                    $anbuLib->setSquadData($squads);

                    // Set the new leader. contentLoad is set by anbuLib, which also throws errors
                    $anbuLib->inviteUser( $_POST['newLeader'], "leader_uid" );

                } else {
                    throw new Exception("The specified squad does not exist.");
                }
            } else {
                throw new Exception("No new leader was specified.");
            }
        } else {
            throw new Exception("No squad was specified.");
        }
    }

    // Check if user can remove this anbu
    public function can_remove_anbu(){

        // Check that squad ID is set
        if (isset($_GET['aid'])) {

            // Check if squad exists
            $squads = $GLOBALS['database']->fetch_data("SELECT * FROM `squads` WHERE `village` = '" . $GLOBALS['userdata'][0]['name'] . "' AND `id` = '" . $_GET['aid'] . "' LIMIT 1");
            if ($squads != '0 rows') {

                // Limit
                $limit = 50;
                if( $squads[0]['pt_rage'] + $squads[0]['pt_def'] < $limit){
                    return $squads;
                }
                else{
                    throw new Exception("This ANBU has more than ".$limit." activity points, and cannot be deleted until it becomes less active.");
                }
            } else {
                throw new Exception("The specified squad does not exist.");
            }
        } else {
            throw new Exception("No squad was specified.");
        }
    }

    // Confirm deleting this ANBU
    public function anbu_remove_confirm(){
        if( $this->can_remove_anbu() ){
            $GLOBALS['page']->Confirm("If you abolish the squad everyone in the squad will cease to be an ANBU members.", 'ANBU System', 'Delete now!');
        }
    }

    // Do delete ANBU
    public function anbu_remove_do(){
        if( $squads = $this->can_remove_anbu() ){

             // Remove the squad completely
            $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `anbu` = '_none' WHERE `anbu` = '" . $squads[0]['id'] . "' LIMIT 11");
            $GLOBALS['database']->execute_query("DELETE FROM `squads` WHERE `id` = '" . $squads[0]['id'] . "' LIMIT 1");

            // Show message to the kage
            $GLOBALS['page']->Message("The ANBU squad has been disbanded.", "ANBU Deleted", 'id=' . $_GET['id'] . "&act=" . $_GET['act'] );
        }
    }

    // Resign from kage form
    public function leader_resign_form(){
        $GLOBALS['page']->Confirm("You must confirm that you want to resign", $this->master->leaderName . ' Resignation', 'Resign now!');
    }

    // Do resign from kage
    public function leader_resign_do(){
        $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `rank` = '".functions::getRank($GLOBALS['userdata'][0]['rank_id'], $GLOBALS['userdata'][0]['village'])."' WHERE `uid` = ".$_SESSION['uid']);
        $GLOBALS['database']->execute_query("UPDATE `villages` SET `leader` = '".Data::$VILLAGE_KAGENAMES[ $GLOBALS['userdata'][0]['village'] ]."' WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");
        $GLOBALS['Events']->acceptEvent('kage', array('data'=>'removed' ));

        $GLOBALS['page']->Message('You have resigned as the '.$this->master->leaderName.' of the ' . $this->master->cpName . '', $this->master->leaderName . ' Resignation', 'id=' . $_GET['id'] );
    }

    // A show the war panel
    public function show_war_panel(){

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
        $warLib = new warLib();

        // Show the panel for this village
        $warLib->war_panel( $GLOBALS['userdata'][0]['village'] );

    }

    // Handle war panel submits
    public function handle_war_submits(){

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
        $warLib = new warLib();

        // Save the alliances locally
        $warLib->setAlliances();

        // Handle the different requests
        if( isset( $_POST['breakalliance'] ) && $_POST['Submit'] == "Break Alliance" ){
            $warLib->break_alliance( $_POST['breakalliance'] );
        }
        elseif( isset( $_POST['wardeclaration'] ) && $_POST['Submit'] == "Declare War" ){
            $warLib->declare_war( $_POST['wardeclaration'] );
        }
        elseif( isset( $_POST['surrenderrequest'] ) && $_POST['Submit'] == "Request Surrender" ){
            $warLib->send_request( $_POST['surrenderrequest'] , "Surrender" );
        }
        elseif( isset( $_POST['requestalliance'] ) && $_POST['Submit'] == "Request Alliance" ){
            $warLib->send_request( $_POST['requestalliance'] , "Alliance");
        }
        elseif( isset( $_GET['action'] ) && isset( $_GET['rid'] ) && is_numeric($_GET['rid']) ){
            if( $_GET['action'] == "accept"){
                $warLib->accept_request( $_GET['rid'] );
            }
            elseif( $_GET['action'] == "delete" ){
                $warLib->decline_request( $_GET['rid'] );
            }
            else{
                throw new Exception("No idea what action you want handled, you can only accept or delete.");
            }
        }
        else{
            throw new Exception("No idea what action you want handled" );
        }
    }

    // Get owned & available territoried
    private function get_territory_info(){

        // Get own territories and all available territory IDs
        $this->own_terr = $GLOBALS['database']->fetch_data("SELECT * FROM `locations` WHERE `owner` =  '" . $GLOBALS['userdata'][0]['village'] . "'");
        $availIDlist = "";
        if( $this->own_terr !== "0 rows" ){
            foreach( $this->own_terr as $territory ){
                $availIDlist .= ($availIDlist == "") ? $territory['attackableNeighboursIDlist'] : ", ".$territory['attackableNeighboursIDlist'];
            }
        }
        $GLOBALS['template']->assign('own_terr', $this->own_terr);

        // Get available territories
        if( !empty($availIDlist) ){
            $this->avail_terr = $GLOBALS['database']->fetch_data("
                    SELECT *
                    FROM `locations`
                    WHERE `owner` != 'Neutral' AND
                        `owner` != '" . $GLOBALS['userdata'][0]['village'] . "' AND
                        `identifier` != 'village' AND
                        `id` IN (".$availIDlist.")"
            );
            $GLOBALS['template']->assign('avail_terr', $this->avail_terr);
        }
    }

    // Show the territory room
    public function show_territory_room(){

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
        $warLib = new warLib();

        // Save the alliances locally
        $warLib->setAlliances();

        // Set territories
        $this->get_territory_info();

        // Calculate the cost
        $villagePoints = $GLOBALS['database']->fetch_data("SELECT `points` FROM `villages` WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "' LIMIT 1");
        $cost = $warLib->territoryChallengeCost( count($this->own_terr) ,  $GLOBALS['userdata'][0]['village'] , $villagePoints[0]['points'] );
        $GLOBALS['template']->assign('challengeCost', $cost );

        // Alliance stuff
        $displayAlliances = $warLib->getAlliesForDisplay();
        $GLOBALS['template']->assign('allianceData', $displayAlliances);

        // Show the main template
        $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/territoryRoom.tpl');

    }

    // Handle a territory challenge
    public function do_territory_challenge(){

        //// Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
        $warLib = new warLib();

        // Save the alliances locally
        $warLib->setAlliances();

        // Do challenge
        $warLib->territory_challenge( $GLOBALS['userdata'][0]['village'], $_POST['challenge'] );
    }

}