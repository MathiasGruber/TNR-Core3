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

// Uses item functions to give user items
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');

// Based on itemBasicFunctions for giving users items
class black_market extends itemBasicFunctions {

    // Constructor
    public function __construct() {

        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            $GLOBALS['database']->transaction_start();

            // Send data to analytics
            $GLOBALS['page']->sendTransactionsToAnalytics();

            // Run constructor on parent
            parent::__construct();

            // get userdata
            $this->getUserData();

            // get the BM library
            require_once(Data::$absSvrPath.'/libs/villageSystem/blackMarketLib.php');
            $this->bmLib = new blackMarketLib();

            // Get any special prices
            $this->surprisePacks = $this->bmLib->getSurprisePacks( false );
            $GLOBALS['template']->assign('specialsurprises', $this->surprisePacks);

            // Get any profession bags
            $this->professionPacks = $this->bmLib->getSurprisePacks( true, $this->user );
            $GLOBALS['template']->assign('professionPacks', $this->professionPacks);

            // Var for storing item price
            $this->purchasePrice = 0;

            // Set data
            $this->temporaryRegenData = array(
                array("days" => 7, "minor" => 30, "moderate" => 35, "major" => 40, "giant" => 45),
                array("days" => 15, "minor" => 60, "moderate" => 70, "major" => 80, "giant" => 90),
                array("days" => 30, "minor" => 110, "moderate" => 130, "major" => 150, "giant" => 170)
            );

            // Decide on page to show
            if (!isset($_GET['act'])) {

                // Overview page
                $this->main_page();

            }
            elseif ($_GET['act'] === 'buy') {

                // Treat different purchases differently
                if( isset($_GET['iid']) && in_array($_GET['iid'], array(13,14,15,16)) ){

                    // Confirm number of days with the user
                    if( !isset($_POST['days']) || !isset($_POST['Submit']) ){
                        $this->confirmRegenDays();
                    }
                    elseif ($_POST['Submit'] === 'Submit Request') {
                        $this->buyNow();
                    }
                }
                elseif( $_GET['iid'] == 20 ){

                    // Village change, either couples change or single-only
                    if ( !isset($_POST['newVillage']) || !isset($_POST['type']) || !isset($_POST['Submit']) ) {
                        $this->confirmVillageChange();
                    }
                    elseif ($_POST['Submit'] === 'Submit Request') {
                        $this->buyNow();
                    }
                }
                elseif( $_GET['iid'] == 30 ){

                    // Village change, either couples change or single-only
                    if ( !isset($_POST['type']) || !isset($_POST['Submit']) ) {
                        $this->confirmElementalRoll();
                    }
                    elseif ($_POST['Submit'] === 'Submit Request') {
                        $this->buyNow();
                    }
                }
                elseif( $_GET['iid'] == 31 ){

                    // Village change, either couples change or single-only
                    if ( !isset($_GET['pack']) || !isset($_POST['Submit']) ) {
                        $this->confirmPackPurchase( false );
                    }
                    elseif ($_POST['Submit'] === 'Confirm Purchase') {
                        $this->buyNow();
                    }
                }elseif( $_GET['iid'] == 32 ){

                    // Village change, either couples change or single-only
                    if ( !isset($_GET['pack']) || !isset($_POST['Submit']) ) {
                        $this->confirmPackPurchase( true );
                    }
                    elseif ($_POST['Submit'] === 'Confirm Purchase') {
                        $this->buyNow();
                    }
                }
                else{

                    // Get confirmation before trying purchase
                    if ( !isset($_POST['Submit']) ) {
                        $this->confirmStandard();
                    }
                    elseif ($_POST['Submit'] === 'Yes') {
                        $this->buyNow();
                    }
                }
            }
            else {
                throw new Exception("Could not identify the action.");
            }

            $GLOBALS['database']->transaction_commit();

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), "Black Market", 'id='.$_GET['id'], 'Return');
        }
    }


    // Convert black market ID to name
    private function getMarketEntryName( $id ){

        // Get the entry from list of market entries
        $entry = $GLOBALS['database']->fetch_data("SELECT * FROM `market_purchases` WHERE `market_id` = '".$id."' LIMIT 1");
        if( $entry !== "0 rows" ){
            return $entry[0]['name'];
        }
        else{
            return "Unknown Market ID";
        }
    }

    // Get reputation points of user
    private function getUserData() {
        $this->user = $GLOBALS['database']->fetch_data("SELECT
                `users_statistics`.`rep_now`, `users_statistics`.`pop_now`,
                `users_statistics`.`specialization`, `occupations`.`name`,
                `users`.`regen_boost`,`users`.`gender`,
                `users_occupations`.`profession_exp`
            FROM `users`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                INNER JOIN `users_occupations` ON (`users_occupations`.`userid` = `users_statistics`.`uid`)
                LEFT JOIN `occupations` ON (`occupations`.`id` = `users_occupations`.`profession`)
            WHERE `users`.`id` = ".$_SESSION['uid']." LIMIT 1");
    }

    // Get any potential spouse data (uid & name)
    private function getSpouse(){
        $marriage = $GLOBALS['database']->fetch_data('
                SELECT
                    `user_1_table`.`username` AS `username1`,
                    `user_2_table`.`username` AS `username2`,
                    `user_1_table`.`id` AS `uid1`,
                    `user_2_table`.`id` AS `uid2`,
                    `marriages`.`married`
                FROM `users`
                    LEFT JOIN `marriages` ON (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`)
                    LEFT JOIN `users` AS `user_1_table` ON (`user_1_table`.`id` = `marriages`.`uid`)
                    LEFT JOIN `users` AS `user_2_table` ON (user_2_table.id = `marriages`.`oid`)
                WHERE `users`.`id` = '.$_SESSION['uid']);

        if( $marriage !== "0 rows" && $marriage[0]['married'] == "Yes" && isset($marriage[0]['uid1']) && isset($marriage[0]['uid2']) ){
            switch( $_SESSION['uid'] ){
                case $marriage[0]['uid1']:
                    return array( "id" => $marriage[0]['uid2'], "username" => $marriage[0]['username2'] );
                break;
                case $marriage[0]['uid2']:
                    return array( "id" => $marriage[0]['uid1'], "username" => $marriage[0]['username1'] );
                break;
            }
        }
        return false;
    }

    // Confirmation dialogs
    private function confirmPackPurchase( $isProfession ) {

        // Packs
        $this->packs = $isProfession ? $this->professionPacks : $this->surprisePacks;

        // Special Notices. Help people understand what they don't to prevent complaints
        if( isset($_GET['pack']) && isset( $this->packs[ $_GET['pack'] ] ) ){

            // Show Contens
            $extraPacks = 0;
            $special = "This pack contains the following items: <br>";
            $names = array();
            foreach( $this->packs[ $_GET['pack'] ] as $key => $item){
                $names[] = $item['itemname'];
                if( $item['extraRewards'] > $extraPacks ){
                    $extraPacks = $item['extraRewards'];
                }
            }
            $special .= implode( ", " , $names);

            // Shorthand vars
            $pType = $this->packs[ $_GET['pack'] ][0]['cost_type'];
            $pCost = $this->packs[ $_GET['pack'] ][0]['cost_amount'];
            $pNumber = $this->packs[ $_GET['pack'] ][0]['cost_item_Number'];

            // Check the costs
            $this->checkCosts(
                $pType,
                $pCost,
                $this->packs[ $_GET['pack'] ][0]['cost_id'],
                $pNumber
            );

            // Notice
            if( count($names) > 1 ){
                $special .= "<br><br><b>At most ".($extraPacks+1)." ".($extraPacks>0?"different items are":" item is")." selected at random upon purchase!</b>";
            }

            // Show price to confirm
            $special .= "<br>Price of this package is: ";
            switch( $pType ){
                case "item": $special .= $pCost." (".$pNumber.")";  break;
                case "ryo": $special .= $pCost." ryo";  break;
                case "pop": $special .= $pCost." popularity points";  break;
                case "rep": $special .= $pCost." reputation points";  break;
            }

            // Show dialog
            $GLOBALS['page']->Confirm($special."<br><br>Are you sure you want to make this purchase?", 'Confirm Purchase', 'Confirm Purchase');

        }
        else{
            throw new Exception("This is not a valid pack anymore.");
        }
    }

    // Confirmation dialogs
    private function confirmStandard() {

        // Special Notices. Help people understand what they don't to prevent complaints
        $special = "";
        switch( $_GET['iid'] ){
            case "18": $special = "<br>Remember to check if you already have namechanges <br>available in the <a href='?id=4&act=nameChange'>preferences menu</a>!"; break;
        }

        // Show dialog
        $GLOBALS['page']->Confirm("Are you sure you want to make this purchase?". $special, 'Confirm Purchase', 'Yes');
    }

    // Confirmation for how many regeneration days
    private function confirmRegenDays(){

        // Get type for description
        switch( $_GET['iid'] ){
            case 13: $type = "minor"; break;
            case 14: $type = "moderate"; break;
            case 15: $type = "major"; break;
            case 16: $type = "giant"; break;
        }

        // Get options
        $options = array(
            "7" => "7 days, ".$this->temporaryRegenData[ 0 ][ $type ]. " Reputation Points",
            "15" => "15 days, ".$this->temporaryRegenData[ 1 ][ $type ]. " Reputation Points",
            "30" => "30 days, ".$this->temporaryRegenData[ 2 ][ $type ]. " Reputation Points"
        );

        // Show user form
        $GLOBALS['page']->UserInput(
                "You are looking to purchase a ".$type." regeneration increase. <br>For how many days do you wish to buy this effect?",
                'Confirm Purchase',
                array(
                    // A select box
                    array(
                        "infoText"=>"",
                        "inputFieldName"=>"days",
                        "type"=>"select",
                        "inputFieldValue"=> $options
                    )
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id']."&act=buy&iid=".$_REQUEST['iid'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit Request"),
                false ,
                "daysForm"
        );
    }

    // Confirmation for village changes
    private function confirmVillageChange(){

        // Get global events
        $singleEvent = functions::getGlobalEvent("VillageSwitch");
        $spouseEvent = functions::getGlobalEvent("CoupleVillageSwitch");
        $spouse = $this->getSpouse();

        // Create options & list prices
        $priceOptions = array();
        if( $singleEvent ){
            $priceOptions["singleMove"] =  "Move your character, 30 Reputation Points";
        }
        if( $spouseEvent && $spouse ){
            $priceOptions["coupleMove"] = "Move you and your Spouse, 55 Reputation Points";
        }

        // Check if any options are available
        if( empty($priceOptions) ){
            throw new Exception("No village change options are applicable for this character");
        }

        // Show user form
        $GLOBALS['page']->UserInput(
                "Please confirm that you wish to move villages. Doing so provides no penalties to you in terms of village respect, loyalty etc. You will however lose any students, clan, anbu, trade requests and trade offers in the process.",
                'Confirm Purchase',
                array(

                    // A select box
                    array(
                        "infoText"=>"",
                        "inputFieldName"=>"type",
                        "type"=>"select",
                        "inputFieldValue"=> $priceOptions
                    ),
                    // A select box
                    array(
                        "infoText"=>"",
                        "inputFieldName"=>"newVillage",
                        "type"=>"select",
                        "inputFieldValue"=> Data::$VILLAGES
                    )
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id']."&act=buy&iid=".$_REQUEST['iid'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit Request"),
                false ,
                "villageForm"
        );
    }

    // Confirmation for elemental re-roll
    private function confirmElementalRoll(){

        //initializing elements class
        $elements = new Elements();

        // Get types (primary or secondary)
        $types = array();

        // If user does not have a bloodline with primary, let him change that
        if( !$elements->isBloodlinePrimaryAffinityActive() ){
            $types["primary"] = "Primary Affinity";
        }

        // If user is above rank 3, and does not have a bloodline with secondary element, let him change that
        if( $GLOBALS['userdata'][0]['rank_id'] > 3 && !$elements->isBloodlineSecondaryAffinityActive() ){
            $types["secondary"] = "Secondary Affinity";
        }

        // Check that user is applicable (no bloodline or no elemental bloodline)
        if( empty( $types ) ){
            throw new Exception("You cannot change your elemental affinity when it's based on your bloodline");
        }

        $currentElements = $elements->getUserElements();

        // Show user form
        $GLOBALS['page']->UserInput(
            "Please confirm below that you wish to re-roll an elemental affinity.
             Only elemental affinities that have already been unlocked can be re-rolled,
             and bloodline affinities cannot be changed. Our system ensures cyclic re-rolling,
             so you will not roll the same affinity twice in a row.<br><br>Remember that you can not have matching elements.<br>Primary Natural Element: {$currentElements[0]}".( $GLOBALS['userdata'][0]['rank_id'] >= 4 ? "<br>Secondary Natural Element: {$currentElements[1]}" : ''),
            'Confirm Purchase',
            array(
                // A select box
                array(
                    "infoText"=>"",
                    "inputFieldName"=>"type",
                    "type"=>"select",
                    "inputFieldValue"=> $types
                )
            ),
            array(
                "href"=>"?id=".$_REQUEST['id']."&act=buy&iid=".$_REQUEST['iid'] ,
                "submitFieldName"=>"Submit",
                "submitFieldText"=>"Submit Request"),
            false ,
            "daysForm"
        );
    }

    // Main page
    private function main_page() {

        // Send user data to template
        $GLOBALS['template']->assign('user', $this->user);

        // Check if village changing is possible
        if( functions::getGlobalEvent("CoupleVillageSwitch") || functions::getGlobalEvent("VillageSwitch") ){
            $GLOBALS['template']->assign('villageChanging', true);
        }

        // Check for regen end time
        if( isset($GLOBALS['userdata'][0]['regen_endtime']) &&
            !empty($GLOBALS['userdata'][0]['regen_endtime']) &&
            $GLOBALS['userdata'][0]['regen_endtime'] > 0)
        {
            $blaat = functions::convert_time($GLOBALS['userdata'][0]['regen_endtime'] - $GLOBALS['user']->load_time, 'regenBoostTimer');
            $GLOBALS['template']->assign('regenBoostTimer', $blaat);
            $GLOBALS['template']->assign('regenBoostAmount', $GLOBALS['userdata'][0]['regen_boost']);
        }

        $GLOBALS['template']->assign('village', $GLOBALS['userdata'][0]['village']);


        //processing blooline
        if($GLOBALS['userdata'][0]['bloodline'] != '')
        {
            if(!($bloodline = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `name` = '".explode(':',$GLOBALS['userdata'][0]['bloodline'])[0] . "'"))) {
                throw new Exception('There was an issue obtaining the bloodline information!');
            }

            if(is_array($bloodline) && strpos($bloodline[0]['tags'],'[T]') !== false)
            {
                $GLOBALS['template']->assign('current_bloodline_type', explode(':',$GLOBALS['userdata'][0]['bloodline'])[1]);
            }
        }



        // Show template
        $GLOBALS['template']->assign('contentLoad', './templates/content/black_market/black_market_main.tpl');
    }

    // Do purchase item
    private function buyNow() {

        // Handler for the items
        switch ($_GET['iid']) {

            // Bloodline items with reputation points
            case(1): case(2): case(3): case(4): case(5):
            case(982): case(983): case(984): case(985): case(986):
            case(987): case(988): case(989):
            case(990): case(991): case(992):
            case(993): case(994): case(995):
            case(996): case(997): case(998):
            case(1032): case(1033): case(1034): case(1035):
            $this->buyBloodlineItem($_GET['iid']); break;

            // Specialization change
            case(6): case(7): case(8): case(9): $this->buySpecializationChange($_GET['iid']); break;

            // Profession bags: DEPRECATED FUNCTION
            // case(10): case(11): case(12): $this->buyProfessionBag($_GET['iid']); break;

            // Reputation regeneration
            case(13): case(14): case(15): case(16): $this->buyTemporaryRegeneration($_GET['iid'], $_POST['days']); break;

            // Get random popularity point item: DEPRECATED FUNCTION
            // case(17): $this->getRandomPopularityPointItem(); break;

            // Get namechange
            case(18): $this->getNamechange(); break;

            // Get genderchange
            case(19): $this->getGenderchange(); break;

            // Get village change
            case(20): $this->getVillagechange( $_POST['newVillage'], $_POST['type'] ); break;

            // Elemental affinity change
            case(30): $this->getElementalChange( $_POST['type'] ); break;

            // Special surprises
            case(31): $this->buyAdminPack( $_GET['pack'] , false ); break;

            // New profession bags
            case(32): $this->buyAdminPack( $_GET['pack'] , true ); break;
        }

        // Update the logs
        if( ($_GET['iid'] >= 1 && $_GET['iid'] <= 31) || ($_GET['iid'] >= 982 && $_GET['iid'] <= 998) || ($_GET['iid'] >= 1032 && $_GET['iid'] <= 1035) ){

            // Update the overall log
            if($GLOBALS['database']->execute_query("UPDATE `market_purchases`
                SET `market_purchases`.`purchases` = `market_purchases`.`purchases` + 1
                WHERE `market_purchases`.`market_id` = ".$_GET['iid']." LIMIT 1") === false) {
                throw new Exception('Error updating market purchase data!');
            }

            // Insert log entry
            if ($GLOBALS['database']->execute_query("INSERT INTO `log_blackMarket`
                (`uid`, `blackMarketID`, `blackMarketName`, `repPrice`,`time`)
                VALUES
                    (".$_SESSION['uid'].", ".$_GET['iid'].", '".str_replace("'","\'",$this->getMarketEntryName($_GET['iid']))."', ".str_replace("'","\'",$this->purchasePrice)." , ".$GLOBALS['user']->load_time.")") === false)
            {
                throw new Exception("An error occurred when trying to insert log entry.");
            }
        }
    }

    // Check purchase
    /* cost array looks like
     * array(
     *  rep => X,
     *  pop => x,
     *  ryo => X,
     *  item => array( "name" => s, "iid" => x, "number" => y )
     * )
     */
    private function checkCosts( $type, $cost, $itemID = "", $numberOfItems = 1 ){
        switch( $type ){
            case "ryo":
                if( $GLOBALS['userdata'][0]['money'] < $cost ){
                    throw new Exception("You do not have the required ".$cost." ryo to pay for this pack");
                }
            break;
            case "rep":
                if( $GLOBALS['userdata'][0]['rep_now'] < $cost ){
                    throw new Exception("You do not have the required ".$cost." reputation points to pay for this pack");
                }
            break;
            case "pop":
                if( $GLOBALS['userdata'][0]['pop_now'] < $cost ){
                    throw new Exception("You do not have the required ".$cost." popularity points to pay for this pack");
                }
            break;
            case "item":
                if( (int) $numberOfItems > (int) $this->countUserItems( $itemID ) ){
                    throw new Exception("You do not have the required items, ".$numberOfItems." ".$cost.", which are required to pay for this pack");
                }
            break;
        }
    }

    // Buy a bloodline item
    private function buyBloodlineItem($pageID) {
        // Match id selection to cost and real iid

        switch($pageID) {
            case 1: $iid = 5; $price = 40; break;
            case 2: $iid = 4; $price = 30; break;
            case 3: $iid = 3; $price = 20; break;
            case 4: $iid = 2; $price = 10; break;
            case 5: $iid = 1; $price = 5; break;

            case 982: if($GLOBALS['userdata'][0]['village'] != 'Shine') throw new Exception('bad village'); $iid = $pageID; $price = 50; break;
            case 983: if($GLOBALS['userdata'][0]['village'] != 'Silence') throw new Exception('bad village'); $iid = $pageID; $price = 50; break;
            case 984: if($GLOBALS['userdata'][0]['village'] != 'Samui') throw new Exception('bad village'); $iid = $pageID; $price = 50; break;
            case 985: if($GLOBALS['userdata'][0]['village'] != 'Konoki') throw new Exception('bad village'); $iid = $pageID; $price = 50; break;
            case 986: if($GLOBALS['userdata'][0]['village'] != 'Shroud') throw new Exception('bad village'); $iid = $pageID; $price = 50; break;

            case 987: $iid = $pageID; $price = 50; break;
            case 988: $iid = $pageID; $price = 40; break;
            case 989: $iid = $pageID; $price = 30; break;

            case 990: $iid = $pageID; $price = 50; break;
            case 991: $iid = $pageID; $price = 40; break;
            case 992: $iid = $pageID; $price = 30; break;

            case 993: $iid = $pageID; $price = 50; break;
            case 994: $iid = $pageID; $price = 40; break;
            case 995: $iid = $pageID; $price = 30; break;

            case 996: $iid = $pageID; $price = 50; break;
            case 997: $iid = $pageID; $price = 40; break;
            case 998: $iid = $pageID; $price = 30; break;

            case 1032: $iid = $pageID; $price = 120; break;
            case 1033: $iid = $pageID; $price = 120; break;
            case 1034: $iid = $pageID; $price = 120; break;
            case 1035: $iid = $pageID; $price = 120; break;

            default: throw new Exception("Could not figure out what bloodline item you're looking for."); break;
        }

        // Save the price
        $this->purchasePrice = $price;

        // Check reputation points
        if ($this->user[0]['rep_now'] < $price) { throw new Exception("You do not have enough reputation points to buy this."); }

        if(!($item = $GLOBALS['database']->fetch_data("SELECT `items`.`name`, `users_inventory`.* FROM `items` LEFT JOIN `users_inventory` ON (`users_inventory`.`iid` = `items`.`id` AND `users_inventory`.`uid` = ".$_SESSION['uid'].") WHERE `items`.`id` = ".$iid))) {
            throw new Exception('There was an issue obtaining the bloodline item!');
        }

        if($item === "0 rows") { throw new Exception("Could not figure out what item you're looking for."); }

        if ($GLOBALS['database']->execute_query("INSERT INTO `users_inventory`
                (`iid`, `uid`, `equipped`, `stack`, `timekey`)
            VALUES
                (".$iid.", ".$_SESSION['uid'].", 'no', 1, ".$GLOBALS['user']->load_time.")") === false) {
            throw new Exception("An error occurred giving you the item, please try again and contact support if problems persist.");
        }

        $stack = 0;
        $quantity = 0;
        foreach($item as $itm)
        {
            if(isset($itm['stack']) && $itm['stack'] != '')
            {
                $stack++;
                $quantity += $itm['stack'];
            }    
        }


        $GLOBALS['Events']->acceptEvent('item_person', array('data'=>$iid, 'new'=>$stack+1, 'old'=>$stack, 'context'=>$iid ));
        $GLOBALS['Events']->acceptEvent('item_quantity_gain', array('context'=>$iid, 'new'=>$quantity+1, 'old'=>$quantity ));

        if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price."
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']." LIMIT 1") === false) {
            throw new Exception("There was an error updating your user with the reduced reputation point amount.");
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        // Show message
        $GLOBALS['page']->Message('You have bought a "'.$item[0]['name'].'".', 'Black Market', 'id='.$_GET['id']);
    }

    // Buy specialization change
    private function buySpecializationChange($pageID) {

        // Match id selection to cost and real iid
        $price = 15;
        switch($pageID) {
            case 6: $new = "T"; break;
            case 7: $new = "N"; break;
            case 8: $new = "G"; break;
            case 9: $new = "W"; break;
            default: throw new Exception("Could not figure out what specialization you're looking for."); break;
        }

        // Save the price
        $this->purchasePrice = $price;

        if ($this->user[0]['rep_now'] < $price) {
            throw new Exception("You do not have enough reputation points to buy this.");
        }

        // Check current specialization
        $specialization = explode(":", $this->user[0]['specialization']);
        if(!isset($specialization[0]) || empty($specialization[0]) ) {
            throw new Exception("Can only use reputation points to switch specialization after a specialization has been chosen.");
        }

        if($specialization[0] === $new) { throw new Exception("You already have this specialization"); }

        // Update database
        if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price.",
                `users_statistics`.`specialization` = '".$new.":".$specialization[1]."'
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']." LIMIT 1") === false) {
            throw new Exception('There was an error updating specialization!');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('specialization', array('new'=>$new, 'old'=>$specialization[0] ));
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        // Success message
        $GLOBALS['page']->Message("You have changed specialization.", 'Specialization Success', 'id='.$_GET['id']);
    }

    // get random item from pack based on frequency
    protected function getRandomItemKey( $pack ){
        $total = 0;
        $keyMap = array();
        foreach( $pack as $key => $entry){
            $min = $total;
            $total += $entry['frequency'];
            $keyMap[ $key ] = array("min"=>$min,"max"=>$total);
        }
        $rand = random_int(0,$total);
        foreach( $keyMap as $key => $minmax ){
            if( $rand >= $minmax['min'] && $rand <= $minmax['max'] ){
                return $key;
            }
        }
        throw new Exception("Could not determine random item");
    }

    // Add to reward array / get final reward array for user
    protected function addReward( $itemName, $itemNumber ){

        // Instantiate if not already
        if( !isset($this->rewardArray) ){
            $this->rewardArray = array();
        }

        // Add items
        if(array_key_exists($itemName, $this->rewardArray) ){
            $this->rewardArray[$itemName] += $itemNumber;
        }
        else{
            $this->rewardArray[$itemName] = $itemNumber;
        }
    }
    protected function getRewardText(){
        $string = "";
        if( isset($this->rewardArray) ){
            $i = 1;
            $count = count($this->rewardArray);
            foreach( $this->rewardArray as $itemName => $number ){
                if( $count > 1 && $i>1){
                    $string .= ($i == $count) ? " and " : ", ";
                }
                $string .= $number." ".$itemName;
                $i++;
            }
        }
        return $string;
    }

    // Buy limited edition item
    private function buyAdminPack( $packID , $isProfession ){
        // Packs
        $this->packs = $isProfession ? $this->professionPacks : $this->surprisePacks;

        // Check that the pack exists
        if( !isset($_GET['pack']) || !isset( $this->packs[ $_GET['pack'] ] ) ){
            throw new Exception("This is not a valid pack anymore.");
        }

        // Check the price
        $pType = $this->packs[ $_GET['pack'] ][0]['cost_type'];
        $pCost = $this->packs[ $_GET['pack'] ][0]['cost_amount'];

        // Check the cost
        $this->checkCosts($pType, $pCost, $this->packs[ $_GET['pack'] ][0]['cost_id']);

        // Determine reward from pack - chose only 1!
        $randItem = $this->getRandomItemKey( $this->packs[ $_GET['pack'] ] );
        $rewardItem = $this->packs[ $_GET['pack'] ][ $randItem ];
        $number = random_int( $rewardItem['min_amount'], $rewardItem['max_amount'] );

        // Subtract cost
        $query = "";
        $costText = "";
        switch( $pType ){
            case "ryo":
                $query = "UPDATE `users_statistics` SET `money` = `money` - '".$pCost."' WHERE uid = ".$_SESSION['uid']." LIMIT 1";
                $costText = "The item cost you ".$pCost." ryo.";

                if($pCost > 0)
                    $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=>$GLOBALS['userdata'][0]['money'] - $pCost));
                else
                    $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=>$GLOBALS['userdata'][0]['money'] - $pCost));
            break;
            case "rep":
                $query = "UPDATE `users_statistics` SET `rep_now` = `rep_now` - '".$pCost."' WHERE uid = ".$_SESSION['uid']." LIMIT 1";
                $costText = "The item cost you ".$pCost." reputation points.";

                if($pCost > 0)
                    $GLOBALS['Events']->acceptEvent('rep_loss', array('old'=>$GLOBALS['userdata'][0]['rep_now'], 'new'=>$GLOBALS['userdata'][0]['rep_now'] - $pCost));
                else
                    $GLOBALS['Events']->acceptEvent('rep_gain', array('old'=>$GLOBALS['userdata'][0]['rep_now'], 'new'=>$GLOBALS['userdata'][0]['rep_now'] - $pCost));
            break;
            case "pop":
                $query = "UPDATE `users_statistics` SET `pop_now` = `pop_now` - '".$pCost."' WHERE uid = ".$_SESSION['uid']." LIMIT 1";
                $costText = "The item cost you ".$pCost." popularity points.";

                if($pCost > 0)
                    $GLOBALS['Events']->acceptEvent('pop_loss', array('old'=>$GLOBALS['userdata'][0]['pop_now'], 'new'=>$GLOBALS['userdata'][0]['pop_now'] - $pCost));
                else
                    $GLOBALS['Events']->acceptEvent('pop_gain', array('old'=>$GLOBALS['userdata'][0]['pop_now'], 'new'=>$GLOBALS['userdata'][0]['pop_now'] - $pCost));
            break;
            case "item":
                $this->reduceNumberOfItems(
                    $_SESSION['uid'],
                    $rewardItem['cost_id'],
                    $rewardItem['cost_item_Number']
                );
            break;
        }

        // Execute query
        if( !empty($query) ){
            $GLOBALS['database']->execute_query($query);
        }

        // Add item to user
        $this->addItemToUser( $_SESSION['uid'], $rewardItem['reward_item_id'], $number, 'buyAdminPack' );
        $this->addReward($rewardItem['itemname'], $number);

        // Add potential extra items to user
        if( $rewardItem['extraRewards'] > 0 ){
            $i = 0;
            while( $i < $rewardItem['extraRewards'] ){

                // New random item, reward user
                $randItem2 = $this->getRandomItemKey( $this->packs[ $_GET['pack'] ] );
                $rewardItem2 = $this->packs[ $_GET['pack'] ][ $randItem2 ];
                $number2 = random_int( $rewardItem2['min_amount'], $rewardItem2['max_amount'] );

                // Do reward
                $this->addItemToUser( $_SESSION['uid'], $rewardItem2['reward_item_id'], $number2, 'buyAdminPack' );
                $this->addReward($rewardItem2['itemname'], $number2);
                $i++;
            }
        }

        // Add log entry
        if ($GLOBALS['database']->execute_query("INSERT INTO `log_specialSurprisePurchases`
                (`uid`,
                 `reward_id`,
                 `reward_iid`,
                 `reward_name`,
                 `reward_count`,
                 `cost_type`,
                 `cost_amount`,
                 `time`
            ) VALUES (
                 '".$_SESSION['uid']."',
                 '".$rewardItem['id']."',
                 '".$rewardItem['reward_item_id']."',
                 '".str_replace("'","\'",$rewardItem['itemname'])."',
                 '".$number."',
                 '".$pType."',
                 '".$pCost."',
                 '".$GLOBALS['user']->load_time."'
             )") === false
        ){
            throw new Exception("An error occurred when trying to insert log entry.");
        }

        // Message for user in the end
        $GLOBALS['page']->Message('You received: '.$this->getRewardText()."<br>".$costText , 'Black Market', 'id='.$_GET['id']);

    }

    // Temporary regeneration
    private function buyTemporaryRegeneration( $pageID , $days ) {

        // Check the amount of days is valid
        $regenData = false;
        foreach( $this->temporaryRegenData as $entry ){
            if( $entry['days'] == $days ){
                $regenData = $entry;
            }
        }
        if( $regenData == false ){
            throw new Exception("You can not purchase extra regeneration for this number of days: ".$days);
        }

        // Update GET iids, since these are used for logging later
        switch( $regenData['days'] ){
            case 15: $_GET['iid'] += 8; break;
            case 30: $_GET['iid'] += 12; break;
        }

        // Match id selection to cost and real iid
        switch($pageID) {
            case 13: $regen = 7.5; $price = $regenData['minor']; break;
            case 14: $regen = 10; $price = $regenData['moderate']; break;
            case 15: $regen = 12.5; $price = $regenData['major']; break;
            case 16: $regen = 15; $price = $regenData['giant']; break;
            default: throw new Exception("Could not figure out what regen pack you're looking for."); break;
        }

        // Save the price
        $this->purchasePrice = $price;

        if ($this->user[0]['rep_now'] < $price) {
            throw new Exception("You do not have enough reputation points to buy this. You have ".$this->user[0]['rep_now']." points and the cost for ".$days." days is ".$price." points.");
        }

        if ((int)$this->user[0]['regen_boost'] !== 0) {
            throw new Exception("A regeneration boost is already present.");
        }

        // Run update of DB
        if ($GLOBALS['database']->execute_query("UPDATE `users`, `users_statistics`
             SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price.",
                 `users`.`regen_boost` = ".$regen.",
                 `users`.`regen_endtime` = ".($GLOBALS['user']->load_time + $days * 24 * 3600)."
             WHERE `users`.`id` = ".$_SESSION['uid']." AND `users_statistics`.`uid` = `users`.`id`") === false)
        {
             throw new Exception("An error occurred while buying the item, please try again and contact support if problems persist.");
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        // User message
        $GLOBALS['page']->Message('You have bought a regeneration pack for '.$price.' reputation points. Your regeneration will be increased by '.$regen."% for ".$regenData['days']." days",
            'Black Market', 'id='.$_GET['id']);
    }

    // Buy name change
    private function getNamechange() {

        // Match id selection to cost and real iid
        $price = 20;

        // Save the price
        $this->purchasePrice = $price;


        if ($this->user[0]['rep_now'] < $price) { throw new Exception("You do not have enough reputation points to buy this."); }

        // Update database
        if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price.",
                `users_statistics`.`nameChanges` = `users_statistics`.`nameChanges` + 1
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']." LIMIT 1") === false) {
            throw new Exception('An error occurred purchasing a name change!');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        // Success message
        $GLOBALS['page']->Message("You have purchased a name change. Go to user preferences to use it.", 'Purchase Complete', 'id='.$_GET['id']);
    }

    // Buy gender change
    private function getGenderchange() {

        // Match id selection to cost and real iid
        $price = 5;

        // Save the price
        $this->purchasePrice = $price;

        // Check points
        if ($this->user[0]['rep_now'] < $price) {
            throw new Exception("You do not have enough reputation points to buy this.");
        }

        // Check current gender
        switch( $this->user[0]['gender'] ){
            case "Male": $newGender = "Female"; break;
            case "Female": $newGender = "Male"; break;
            default: throw new Exception("Did not recognize current gender: ".$this->user[0]['gender']);
        }

        // Update database
        if($GLOBALS['database']->execute_query("UPDATE `users_statistics`,`users`
            SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price.",
                `users`.`gender` = '".$newGender."'
            WHERE
                `users`.`id` = `users_statistics`.`uid` AND
                `users_statistics`.`uid` = ".$_SESSION['uid']
        ) === false) {
            throw new Exception('An error occurred purchasing a name change!');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        // Success message
        $GLOBALS['page']->Message("You have changed your gender to: ".$newGender, 'Purchase Complete', 'id='.$_GET['id']);
    }

    // Buy village change
    private function getVillagechange( $newVillage, $type ){

        // Check the types
        if( !in_array($type, array("singleMove", "coupleMove")) ){
            throw new Exception("Can't buy this type of village change: ".$type );
        }

        // Check the village
        if( !array_key_exists( $newVillage, Data::$VILLAGES ) ){
            throw new Exception("Can't join this village ID: ".$newVillage );
        }

        // Change from village ID to village name
        $newVillage = Data::$VILLAGES[ $newVillage ];

        // Cannot jump if village is in war
        require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
        $this->warLib = new warLib();
        $this->warLib->setAlliances();

        // Get alliance arrays
        $current = $this->warLib->pick_out_village( $GLOBALS['userdata'][0]['village'] , $this->warLib->allAlliances);
        $new = $this->warLib->pick_out_village( $newVillage , $this->warLib->allAlliances);

        // Own village war test
        if( $this->warLib->inWar( $current ) && $current['name'] !== "Syndicate" ){
            throw new Exception("You cannot transfer villages, when your village is in war!");
        }

        // Jump to village war test
        if( $this->warLib->inWar( $new ) && $new['name'] !== "Syndicate" ){
            throw new Exception("You cannot transfer villages, when the village you are trying to join is in war!");
        }


        // Get the price depending on its a couple transfer or not
        $price = ($type == "coupleMove") ? 55 : 30;

        // Save the price
        $this->purchasePrice = $price;

        // Check points
        if ($this->user[0]['rep_now'] < $price) {
            throw new Exception("You do not have enough reputation points to buy this.");
        }

        // Load the respect library
        require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
        $respectLib = new respectLib();

        // Get the spouse
        $spouse = $this->getSpouse();

        // If couple move, first try to move spouse
        if( $type == "coupleMove" ){

            // Get global events
            $event = functions::getGlobalEvent("CoupleVillageSwitch");

            // Must have found both, otherwise no dice
            if( $event && $spouse ){

                // Move spouse
                $respectLib->switch_user_village( $newVillage, array($_SESSION['uid'], $spouse['id']) );

                // Log it
                functions::log_village_changes(
                    $spouse['id'],
                    $GLOBALS['userdata'][0]['village'],
                    $newVillage,
                    "Spouse moved village using Black Market"
                );

            }
            else{
                throw new Exception("Could not determine if event was active, or find spouse");
            }

            // Update the ID logged for black market purchases
            $_GET['iid'] = 29;

        }
        else{
            if( functions::getGlobalEvent("VillageSwitch") ){
                if( empty($spouse) ){
                    $respectLib->switch_user_village( $newVillage, array($_SESSION['uid']) );
                }
                else{
                    throw new Exception("You cannot leave your village without your spouse. Please divorce first.");
                }

            }
            else{
                throw new Exception("The village change option has been disabled.");
            }
        }

        // Log the user moving village
        functions::log_village_changes(
            $_SESSION['uid'],
            $GLOBALS['userdata'][0]['village'],
            $newVillage,
            "Moved village using Black Market"
        );

        // Deduct reputation points
        if($GLOBALS['database']->execute_query("
            UPDATE `users_statistics`
            SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price."
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']
        ) === false) {
            throw new Exception('An error occurred purchasing a village change!');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        // Instant update village name
        $GLOBALS['userdata'][0]['village'] = $newVillage;

        // Success message
        $message = ($type == "coupleMove") ? "You have changed the village of yourself and your spouse to " : "You have changed your village to ";
        $GLOBALS['page']->Message( $message.$newVillage, 'Purchase Complete', 'id='.$_GET['id']);

    }

    //get elemental re-roll
    private function getElementalChange($type)
    {
        //initalizing elements
        $elements = new Elements();

        //check type
        if( $type != 'primary' && $type != 'secondary')
            throw new Exception('Cant buy this type of elemental change: '.$type);

        // Check rank
        if( $GLOBALS['userdata'][0]['rank_id'] < 4 && $type == "secondary" || $GLOBALS['userdata'][0]['rank_id'] < 3 && $type == "primary"){
            throw new Exception("Can't change that element since it's not been unlocked yet" );
        }

        // If user does not have a bloodline with primary, let him change that
        if( $type == "primary" && $elements->isBloodlinePrimaryAffinityActive() ||
            $type == "secondary" &&  $elements->isBloodlineSecondaryAffinityActive()
        ){
            throw new Exception("You cannot change your elemental affinity when it's based on your bloodline");
        }

        // Set the price
        $price = 5;

        // Save the price
        $this->purchasePrice = $price;

        // Check points
        if ($this->user[0]['rep_now'] < $price) {
            throw new Exception("You do not have enough reputation points to buy this.");
        }

        // Get the elements of the user, both the one he's currently trying to change
        // and the other (i.e. if the user chose to change his primary, get the secondary element)
        $currentElements = $elements->getUserElements();

        if($type == "primary")
        {
            $currentElement = $currentElements[0];
            $otherElement = $currentElements[1];
        }
        else if($type == "secondary" )
        {
            $currentElement = $currentElements[1];
            $otherElement = $currentElements[0];
        }

        // Get a random element which is not the current primary or secondary element
        $elementAffinity = $otherElement;
        while( $elementAffinity == $otherElement || $elementAffinity == $currentElement){
            $elementAffinity = Elements::getRandomElement();
        }

        // getting previous rolls to try to save the user from frustrating re rolls
        $previous_rolls = $GLOBALS['database']->fetch_data("
            SELECT `element`, COUNT(`element`) as `count`
                FROM `element_rolls`
                    WHERE `uid` = {$_SESSION['uid']}
                        GROUP BY `element`
                            ORDER BY `count` ASC"
        );

        //if the user has rolled before check previous rolls find a new element
        if (is_array($previous_rolls))
        {
            //getting element names in previous rolls
            $names = array();
            foreach($previous_rolls as $previous_roll)
                $names[] = $previous_roll['element'];

            //adding missing entries to previous rolls based on names vs list of elements.
            foreach(Elements::$mainElements as $name)
                if(!in_array($name, $names))
                    $previous_rolls[] = array( 'element' => $name, 'count' => 0);


            //finding elements that are suitable to pick from as a re-role
            $lowest = -1; // holds the count for the rolls with the least re-rolls
            $suitable = ''; //holds an array of the rolls with the least re-rolls
            shuffle($previous_rolls); //shufling previous_rolls to create a random first pick element.
            foreach($previous_rolls as $previous_roll)
            {
                if($previous_roll['element'] != $currentElement && $previous_roll['element'] != $otherElement)
                {
                    if( $previous_roll['count'] < $lowest || $lowest == -1)
                    {
                        $lowest = $previous_roll['count'];
                        $suitable = $previous_roll['element'];
                    }
                }
            }

            $elementAffinity = $suitable;
        }

        // Deduct reputation points
        if($GLOBALS['database']->execute_query("INSERT INTO `element_rolls` (`uid`, `time`, `element`, `category`) VALUES ({$_SESSION['uid']}, ".time().", '{$elementAffinity}', '{$type}')") === false) 
            throw new Exception('An error occurred purchasing a Element Re-Roll!');

        // Deduct reputation points
        if($GLOBALS['database']->execute_query("
            UPDATE `users_statistics`
            SET `users_statistics`.`rep_now` = `users_statistics`.`rep_now` - ".$price."
            WHERE `users_statistics`.`uid` = ".$_SESSION['uid']
        ) === false) {
            throw new Exception('An error occurred purchasing a Element Re-Roll!');
        }
        else
        {
            $GLOBALS['Events']->acceptEvent('rep_loss',array('old'=>$GLOBALS['userdata'][0]['rep_now'],'new'=> $GLOBALS['userdata'][0]['rep_now'] - $price));
        }

        if($type == 'primary')
            $elements->setUserElementAffinityPrimary($elementAffinity);
        else
            $elements->setUserElementAffinitySecondary($elementAffinity);

        $GLOBALS['page']->Message( "You have rerolled your ".$type." elemental affinity to ".$elementAffinity, 'Purchase Complete', 'id='.$_GET['id']);
    }

}
new black_market();