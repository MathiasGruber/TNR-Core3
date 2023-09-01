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

class tradeLib extends itemBasicFunctions{

    // Show the main menu
    protected function main_menu() {

        $menu = array(
                array("name" => "Search trades", "href" => "?id=" . $_GET['id'] . "&act=search"),
                array("name" => "New trade", "href" => "?id=" . $_GET['id'] . "&act=newtrade"),
                array("name" => "Your Offers", "href" => "?id=" . $_GET['id'] . "&act=myoffers"),
                array("name" => "Browse trades", "href" => "?id=" . $_GET['id'] . "&act=browse"),
                array("name" => "Your Trades", "href" => "?id=" . $_GET['id'] . "&act=mytrades"),
                array("name" => "Trading History", "href" => "?id=" . $_GET['id'] . "&act=history")
            );
            $GLOBALS['template']->assign('subHeader', $this->trade_title);
            $GLOBALS['template']->assign('nCols', 3);
            $GLOBALS['template']->assign('nRows', 2);
            $GLOBALS['template']->assign('subTitle_info', 'Here you can trade items with other players');
            $GLOBALS['template']->assign('linkMenu', $menu);
            $GLOBALS['template']->assign('buttonLayout', "SUBMIT");
            $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');

    }

    // Form for making a new trade
    protected function frm_make_trade() {

        // Select items for the suers
        $items = $this->getTradeableItems();

        // Set up the data for the checkboxes
        $checkBoxData = array();
        if ($items != '0 rows') {

            // Go through all the items
            for( $i=0; $i<count($items); $i++ ){
                $checkBoxData[] = array(
                    "id" => $items[$i]['id'],
                    "name" => $items[$i]['name'],
                    "description" => $items[$i]['type']
                );
            }
        }

        // Create the fields to be shown
        $inputFields = array(
            array("infoText" => "Item to trade", "inputFieldName" => "items", "type" => "checkBox", "inputFieldValue" => $checkBoxData ),
            array("infoText" => "Attach short message", "inputFieldName" => "message", "type" => "textarea", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            array('message'=>"The items you select here will be put up for trade, each trade will cost you <b>".$this->admin_fees."</b> ryo in administrative fees.", 'hidden'=>'yes'), // Information
            "Create a new trade", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "SubmitTrade","submitFieldText" => "Create Trade"), // Submit button
            "Return" // Return link name
        );
    }

    // Create a new trade
    protected function do_make_trade() {

        // Check money in globals
        if ($GLOBALS['userdata'][0]['money'] >= $this->admin_fees) {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get the items, lock row
            $items = $this->getTradeableItems( true );
            if ($items != '0 rows') {

                // Loop through items, get the ones being traded
                $trading = $itemNames = $itemTypes = array();
                for( $i=0; $i<count($items); $i++ ){
                    if( isset( $_POST[$items[$i]['id']] ) && $_POST[$items[$i]['id']] == "on"){
                        $trading[] = $items[$i]['id'];
                        $itemNames[] = $items[$i]['name'];
                        $itemTypes[] = $items[$i]['type'];
                    }
                }

                // Must be at least one
                if (count($trading) >= 1) {

                    // Maximum of two
                    if (count($trading) <= $this->max_trade_items) {

                        // Store & check message
                        $message = functions::store_content( $_POST['message'] );
                        if (( strlen($message) >= 0 && strlen($message) < 200)) {

                            // Update user money
                            $GLOBALS['database']->execute_query("
                                UPDATE `users_statistics`
                                SET `money` = `money` - ".$this->admin_fees."
                                WHERE
                                    `money` - ".$this->admin_fees." >= 0 AND
                                    `uid` = '" . $_SESSION['uid'] . "'
                                LIMIT 1");

                            $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $this->admin_fees));

                            // Insert entry
                            $this->createEntry( "trades", array(
                                "uid" => "'".$_SESSION['uid']."'",
                                "time" => "'".$GLOBALS['user']->load_time."'",
                                "message" => "'".$message."'",
                                "trade_type" => "'".$this->trade_type."'",
                                "trade_name" => "'".str_replace("'","\'",implode(", ",$itemNames))."'",
                                "item_types" => "'".implode(", ",$itemTypes)."'"
                            ));
                            $trade_id = $GLOBALS['database']->get_inserted_id();

                            // Update the user items
                            $GLOBALS['database']->execute_query("
                                UPDATE `users_inventory`
                                SET
                                    `trade_type` = 'trade',
                                    `equipped` = 'no',
                                    `trading` = '" . $trade_id . "'
                                WHERE
                                    `id` IN (" . implode(",", $trading) . ") AND
                                    `uid` = '" . $_SESSION['uid'] . "' AND
                                    `trading` IS NULL AND
                                    `finishProcessing` = 0");

                            if( $GLOBALS['database']->last_affected_rows > 0 ){

                                // Message
                                $GLOBALS['page']->Message( "You have successfully created the trade" , $this->trade_title, 'id='.$_GET['id'],'Return');

                                // End transaction
                                $GLOBALS['database']->transaction_commit();

                            }
                            else{
                                throw new Exception("There was an error creating the trade");
                            }
                        } else {
                            throw new Exception("Your message does not meet the size requirements of < 200 characters");
                        }
                    } else {
                        throw new Exception("You can only trade ".$this->max_trade_items." items at a time.");
                    }
                } else {
                    throw new Exception("You did not select any items to trade.");
                }
            } else {
                throw new Exception("You do not have any items to trade.");
            }
        } else {
            throw new Exception("You do not have enough ryo to create a trade.");
        }
    }

    // Show current trades
    protected function my_trades() {

        // Get current trades /with item data
        $currenTrades = $this->getCurrentEntries( "trades", array( "`uid`" => $_SESSION['uid'], "`trade_type`" => $this->trade_type ) );

        // Show the list
        tableParser::show_list(
            "myTrades",
            $this->trade_title. ", My Trades",
            $currenTrades,
            array(
                'id' => "Identification Nr.",
                'trade_name' => "Description"
            ), // Main fields
            array(
                array("name" => "See Offers", "id" => $_GET['id'], "act" => "trade_offers", "tid" => "table.id"),
                array("name" => "Remove", "id" => $_GET['id'], "act" => "rmv_trade", "tid" => "table.id")
            ), // option links
            true, // Send directly to contentLoad
            true, // Show previous/next links
            false, // Category links
            true, // Allow sorting on columns
            false, // pretty-hide options
            false, // No search box on top
            array('message'=>"Here you can see your trades and their status.",'hidden'=>'yes') // Description at top
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // Cancel a trade
    protected function do_remove_trade() {

        // Check the trade ID
        if ( is_numeric($_GET['tid']) ) {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Remove trade, and offers
            $this->deleteTradeClean( $_SESSION['uid'], $_GET['tid'] );

            // Commit transaction
            $GLOBALS['database']->transaction_commit();

            // Message
            $GLOBALS['page']->Message( "The trade has been cancelled." , $this->trade_title, 'id='.$_GET['id'],'Return');

        } else {
            throw new Exception("Could not identify which trade you're trying to remove");
        }
    }

    // Delete trade, reset items related to trade, return money & items to offers.
    public function deleteTradeClean( $uid, $tid, $forceDelete = false ){

        // Get the trade with transaction lock
        $trade_data = $this->getTrade(array(
                "`uid`" => $uid,
                "`trades`.`id`" => $tid,
                "`trade_type`" => $this->trade_type
            ), true
        );
        if ($trade_data != '0 rows' || $forceDelete == true ) {

            // Get the items being traded with transaction lock
            $tradingItems = $this->getTradingItems(array(
                    "uid" => $uid,
                    "trading" => $tid,
                ), true, $this->max_trade_items
            );
            if( $tradingItems !== "0 rows" ){

                // Update user inventory
                $GLOBALS['database']->execute_query("
                     UPDATE `users_inventory`
                     SET
                        `trading` = NULL,
                        `trade_type` = NULL
                     WHERE
                        `trading` = '" . $tid . "'
                     LIMIT ".$this->max_trade_items."
                ");
            }

            // Remove trade offer and return money
            $this->removeAllOffers( $tid );

            // Delete the trade
            if( !$GLOBALS['database']->execute_query("DELETE FROM `trades` WHERE `trades`.`id` = '".$tid."' LIMIT 1") ){
                throw new Exception("An error occured trying to delete the trade");
            }
        } else {
            throw new Exception("The trade could not be found in the database. UID: ".$uid." - TID: ".$tid." - Type:".$this->trade_type);
        }
    }

    // Browse the trades currently in the system
    protected function browse_trades() {

        // Minimum
        $min =  tableParser::get_page_min();

        // Selector on type
        $selector = "";
        if( isset($_GET['type']) && in_array( strtolower($_GET['type']), $this->available_types ) ){
            $selector = " AND `item_types` LIKE '%".$_GET['type']."%' ";
        }

        // Get the trades
        $trades = $GLOBALS['database']->fetch_data("
             SELECT
                `trades`.*,
                `users`.`username`
             FROM `trades`,`users`
             WHERE
                `users`.`id` = `trades`.`uid` AND
                `trades`.`uid` != '".$_SESSION['uid']."' AND
                `trade_type` IN ('". implode("','", $this->availTradeTypes) ."')
                ".$selector."
             ORDER BY `time` DESC
             LIMIT ".$min.",10 ");

        // Top options
        $topOptions = false;
        if (isset($this->available_types) && count($this->available_types) > 1) {
            $topOptions = array();
            foreach ($this->available_types as $type) {
                $topOptions[] = array("name" => str_replace("'","",$type)."s", "href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&type=" . $type);
            }
        }

        // Show the list
        tableParser::show_list(
            "myTrades",
            $this->trade_title,
            $trades,
            array(
                'username' => "User",
                'trade_name' => "Items"
            ), // Main fields
            array(
                array("name" => "View Details", "id" => $_GET['id'], "act" => "viewtrade", "tid" => "table.id"),
                array("name" => "Make Offer", "id" => $_GET['id'], "act" => "make_offer", "tid" => "table.id")
            ), // option links
            true, // Send directly to contentLoad
            true, // Show previous/next links
            $topOptions, // Category links
            true, // Allow sorting on columns
            false, // pretty-hide options
            false // No search box on top
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // View specific trade
    protected function view_trade() {

        // Check ID
        if (isset($_GET['tid']) && is_numeric($_GET['tid'])) {

            // Get the trade
            $trade = $this->getTrade(array("`trades`.`id`" => $_GET['tid']));
            if ($trade != '0 rows') {

                // Check that the user can view this type of trade
                if( in_array( strtolower($trade[0]['trade_type']), $this->availTradeTypes ) ){

                    // Message
                    if ($trade[0]['message'] == "") {
                        $trade[0]['message'] = "None";
                    }

                    // Get items
                    $items = $GLOBALS['database']->fetch_data("
                         SELECT `name`,`required_rank`,`armor_types`,`type`
                         FROM `items`,`users_inventory`
                         WHERE
                            `uid` = '" . $trade[0]['uid'] . "' AND
                            `trading` = '" . $trade[0]['id'] . "' AND
                            `trade_type` = 'trade' AND
                            `users_inventory`.`iid` = `items`.`id`;
                    ");

                    // Did it find items?
                    if( $items !== "0 rows" ){

                        // Fix up items
                        for( $i=0; $i<count($items); $i++ ){

                            // Set the type
                            if ($items[$i]['type'] == 'armor') {
                                $items[$i]['type'] = $items[$i]['armor_types'];
                            }

                            // Set the rank
                            $items[$i]['rank'] = Data::$RANKNAMES[ $items[$i]['required_rank'] ];

                        }

                        // Set the list of items
                        tableParser::show_list(
                            "items",
                            "Trade Items",
                            $items,
                            array(
                                'name' => "Item Name",
                                'type' => "Type",
                                'rank' => "Required Rank"
                            ), // Main fields
                            false, // option links
                            false, // Send directly to contentLoad
                            false, // Show previous/next links
                            false, // Category links
                            false, // Allow sorting on columns
                            false, // pretty-hide options
                            false, // No search box on top
                            "These are the items up for trade" // Description at top
                        );

                        // Show some information to the user
                        $GLOBALS['template']->assign('trade', $trade);

                        // Show smarty template
                        $GLOBALS['template']->assign( "contentLoad" , './templates/content/trading/show_details.tpl');

                    }
                    else{
                        $this->deleteTradeClean( $trade[0]['uid'], $_GET['tid'] );
                        throw new Exception("No items attached to this trade, something is very wrong. Deleting trade.");
                    }
                }
                else{
                    throw new Exception("You cannot view this trade type. Trade type: ".$trade[0]['trade_type'].". Available: ".implode(",", $this->availTradeTypes ));
                }
            } else {
                throw new Exception("This trade does not exist");
            }
        } else {
            throw new Exception("An invalid trade was specified.");
        }
    }

    //  Make offer
    protected function make_offer_frm() {

        // Check the ID is reasonable
        if ( isset($_GET['tid']) && is_numeric($_GET['tid']) ) {

            // Get the trade
            $trade = $this->getTrade( array("`trades`.`id`" => $_GET['tid']) );
            if( $trade !== "0 rows" ){

                if( $trade[0]['uid'] !== $_SESSION['uid'] ){

                    // Check that the user can view this type of trade
                    if( in_array( strtolower($trade[0]['trade_type']), $this->availTradeTypes ) ){

                        // Select items for the suers
                        $items = $this->getTradeableItems();

                        // Set up the data for the checkboxes
                        $checkBoxData = array();
                        if ($items != '0 rows') {

                            // Go through all the items
                            for( $i=0; $i<count($items); $i++ ){
                                $checkBoxData[] = array(
                                    "id" => $items[$i]['id'],
                                    "name" => $items[$i]['name'],
                                    "description" => $items[$i]['type']
                                );
                            }
                        }

                        // Create the fields to be shown
                        $inputFields = array(
                            array("infoText" => "Items to offer", "inputFieldName" => "items", "type" => "checkBox", "inputFieldValue" => $checkBoxData ),
                            array("infoText"=>"Ryo to trade","inputFieldName"=>"trade_ryo", "type" => "input", "inputFieldValue" => "" )
                        );

                        // Show user prompt
                        $GLOBALS['page']->UserInput(
                            array('message'=>"The items you select will be put up as an offer to the trade you previously selected. You can choose to offer items, ryo, or even both. Item offers are limited to ".$this->max_offer_items." items at a time", 'hidden'=>'yes'), // Information
                            "Make an Offer", // Title
                            $inputFields, // input fields
                            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] . "&tid=" . $_GET['tid'] , "submitFieldName" => "SubmitOffer","submitFieldText" => "Submit Offer"), // Submit button
                            "Return" // Return link name
                        );
                    }
                    else{
                        throw new Exception("You cannot view this trade type. Trade type: ".$trade[0]['trade_type'].". Available: ".implode(",", $this->availTradeTypes ));
                    }
                }
                else{
                    throw new Exception("You can not offer on your own trade. Your UID: ".$_SESSION['uid']." - Trader UID: ".$trade[0]['uid']);
                }
            }
            else{
                throw new Exception("Could not find this trade in the database anymore.");
            }
        } else {
            throw new Exception("Could not make sense of the trade ID you're looking up");
        }
    }

    // Do enter an offer
    protected function do_make_offer() {

        // Check the ID is reasonable
        if ( isset($_GET['tid']) && is_numeric($_GET['tid']) ) {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get the trade
            $trade = $this->getTrade( array("`trades`.`id`" => $_GET['tid']) , true );
            if( $trade !== "0 rows" ){

                // Check the trade ID vs the session ID
                if( $trade[0]['uid'] !== $_SESSION['uid'] ){

                    // Check that the user can view this type of trade
                    if( in_array( strtolower($trade[0]['trade_type']), $this->availTradeTypes ) ){

                        // Check other offers
                        $offr_data = $this->getOffer( array("`uid`" => $_SESSION['uid']) , false, $this->max_offers );
                        if ( count($offr_data) < $this->max_offers) {

                            // Check that the user doesn't already have an offer on this trade
                            if( $offr_data !== "0 rows" ){
                                for( $i=0; $i<count($offr_data); $i++ ){
                                    if( $offr_data[$i]['tid'] == $_GET['tid'] ){
                                        throw new Exception("You can only make one offer per trade");
                                    }
                                }
                            }


                            // Get the amount of ryo offered
                            $ryo = ( is_numeric($_POST['trade_ryo']) && $_POST['trade_ryo'] > 0 ) ? $_POST['trade_ryo'] : 0;

                            if($ryo < 1)
                                $ryo = 1;

                            // Verify the user has the money
                            if( $ryo > 0 ){

                                // Get money
                                $user = $GLOBALS['database']->fetch_data("
                                    SELECT `money`
                                    FROM `users_statistics`
                                    WHERE `uid` = '" . $_SESSION['uid'] . "'
                                    LIMIT 1 FOR UPDATE");
                                if( $user[0]['money'] < $ryo ){
                                    throw new Exception("You do not have the money required for this offer");
                                }
                            }

                            // Check the items
                            $items = $this->getTradeableItems( true );
                            $trading = $itemNames = array();
                            if( $items !== "0 rows" ){
                                for( $i=0; $i<count($items); $i++ ){
                                    if( isset( $_POST[$items[$i]['id']] ) && $_POST[$items[$i]['id']] == "on"){
                                        $trading[] = $items[$i]['id'];
                                        $itemNames[] = $items[$i]['name'];
                                    }
                                }
                                if( count($trading) > $this->max_offer_items ){
                                    throw new Exception("You can only trade ".$this->max_offer_items." items at a time");
                                }
                            }

                            // Check if anything should be done
                            if( !empty($trading) || $ryo > 0 ){

                                // Update ryo
                                if( $ryo > 0 ){
                                    $GLOBALS['database']->execute_query("
                                        UPDATE `users_statistics`
                                        SET `money` = `money` - ".$ryo."
                                        WHERE
                                            `money` - ".$ryo." >= 0 AND
                                            `uid` = '" . $_SESSION['uid'] . "'
                                        LIMIT 1");
                                }

                                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $ryo));

                                // Update items
                                if( !empty($trading) ){
                                    $GLOBALS['database']->execute_query("
                                        UPDATE `users_inventory`
                                        SET
                                            `trade_type` = 'offer',
                                            `equipped` = 'no',
                                            `trading` = '" . $trade[0]['id'] . "'
                                        WHERE
                                            `id` IN (" . implode(",", $trading) . ") AND
                                            `uid` = '" . $_SESSION['uid'] . "' AND
                                            `trading` IS NULL AND
                                            `finishProcessing` = 0");
                                }

                                // Insert the offer
                                $this->createEntry( "trade_offers", array(
                                    "uid" => "'".$_SESSION['uid']."'",
                                    "time" => "'".$GLOBALS['user']->load_time."'",
                                    "tid" => "'".$trade[0]['id']."'",
                                    "ryo" => "'".$ryo."'",
                                    "offer_name" => "'".implode(", ",$itemNames)."'",
                                    "offer_type" => "'".$this->trade_type."'"
                                ));

                                // Message
                                $GLOBALS['page']->Message( "You have successfully created the offer" , $this->trade_title, 'id='.$_GET['id'],'Return');

                                // End transaction
                                $GLOBALS['database']->transaction_commit();

                            }
                            else{
                                throw new Exception("You did not select any items to trade.");
                            }
                        }
                        else{
                            throw new Exception("You can only have ".$this->max_offers." active offers at a time");
                        }
                    }
                    else{
                        throw new Exception("You can not trade that type of item here");
                    }
                }
                else{
                    throw new Exception("You can not offer on your own trade");
                }
            }
            else{
                throw new Exception("Could not find this trade in the database anymore.");
            }
        } else {
            throw new Exception("Could not make sense of the trade ID you're looking up");
        }
    }

    // Browse offers
    protected function my_offers() {

        // Get current trades / with item data
        $currenTrades = $this->getCurrentEntries( "trade_offers", array("`uid`" => $_SESSION['uid'], "`offer_type`" => $this->trade_type) );

        // Fix up stuff
        if( $currenTrades !== "0 rows" ){
            for( $i=0; $i<count($currenTrades); $i++ ){
                $currenTrades[$i]['ryo'] .= " Ryo";
                if( $currenTrades[$i]['offer_name'] == "" ){
                    $currenTrades[$i]['offer_name'] = "N/A";
                }
            }
        }


        // Show the list
        tableParser::show_list(
            "myTrades",
            $this->trade_title. ", My Offers",
            $currenTrades,
            array(
                'offer_name' => "Offered Items",
                'ryo' => "Offered Money"
            ), // Main fields
            array(
                array("name" => "View Trade", "id" => $_GET['id'], "act" => "viewtrade", "tid" => "table.tid"),
                array("name" => "Remove", "id" => $_GET['id'], "act" => "rmv_offer", "oid" => "table.id")
            ), // option links
            true, // Send directly to contentLoad
            true, // Show previous/next links
            false, // Category links
            true, // Allow sorting on columns
            false, // pretty-hide options
            false, // No search box on top
            array('message'=>"Here you can see your standing offers. Offers which have been declined will no longer show up.",'hidden'=>'yes') // Description at top
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // Remove an offer from the system
    protected function do_withdraw_offer() {

        // Check the trade ID
        if ( is_numeric($_GET['oid']) ) {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get the trade FOR UPDATE
            $offer_data = $this->getOffer(array(
                    "id" => $_GET['oid'],
                    "offer_type" => $this->trade_type
                ), true
            );
            if ($offer_data != '0 rows') {

                // Check that the trade exists
                $trade_data = $this->getTrade(array("`trades`.`id`" => $offer_data[0]['tid'] ) );
                if( $trade_data !== "0 rows" ){

                    // Confirm that the user is either the owner of the offer or the trade
                    if( $trade_data[0]['uid'] == $_SESSION['uid'] || $offer_data[0]['uid'] == $_SESSION['uid'] ){

                        // Get the items being traded FOR UPDATE
                        $tradingItems = $this->getTradingItems(array(
                                "`uid`" => $offer_data[0]['uid'],
                                "`trading`" => $offer_data[0]['tid'],
                                "`trade_type`" => "offer"
                            ), true, $this->max_trade_items
                        );
                        if( $tradingItems !== "0 rows" ){

                            // Update user inventory
                            $GLOBALS['database']->execute_query("
                                 UPDATE `users_inventory`
                                 SET
                                    `trading` = NULL,
                                    `trade_type` = NULL
                                 WHERE
                                    `trading` = '" . $offer_data[0]['tid'] . "' AND
                                    `uid` = '".$offer_data[0]['uid']."'
                                 LIMIT ".$this->max_offer_items."
                            ");
                        }

                        // Remove trade offer and return money
                        $this->removeTradeOffer($offer_data[0]['id']);

                        // Commit transaction
                        $GLOBALS['database']->transaction_commit();

                        // Message
                        $GLOBALS['page']->Message( "The offer has been removed." , $this->trade_title, 'id='.$_GET['id'],'Return');


                    }
                    else{
                        throw new Exception("You are not eligible to delete this offer");
                    }
                }
                else{
                    throw new Exception("Could not find the trade that this offer is associated with");
                }
            } else {
                throw new Exception("The offer could not be found in the database");
            }
        } else {
            throw new Exception("Could not identify which offer you're trying to remove");
        }
    }

    // View trade offers
    protected function view_trade_offers() {

        // Check the ID
        if (isset($_GET['tid']) && is_numeric($_GET['tid'])) {

            // Get the trade and check that it exists
            $trade = $this->getTrade(array("`trades`.`id`" => $_GET['tid'], "`uid`"=>$_SESSION['uid'] ) );
            if( $trade !== "0 rows") {

                // Get current offers
                $currenTrades = $this->getCurrentEntries( "trade_offers", array("`tid`" => $_GET['tid'] ) );

                // Fix up stuff
                $toShow = array();
                if( $currenTrades !== "0 rows" ){
                    for( $i=0; $i<count($currenTrades); $i++ ){
                        if( in_array( strtolower($currenTrades[$i]['offer_type']), $this->availTradeTypes ) ){
                            $currenTrades[$i]['ryo'] .= " Ryo";
                            $currenTrades[$i]['offer_name'] = str_replace(", ", "<br>", $currenTrades[$i]['offer_name']);
                            if( $currenTrades[$i]['offer_name'] == "" ){
                                $currenTrades[$i]['offer_name'] = "N/A";
                            }
                            $toShow[] = $currenTrades[$i];
                        }
                    }
                }

                // Show the list
                tableParser::show_list(
                    "myTrades",
                    $this->trade_title. ", Offers",
                    $toShow,
                    array(
                        'offer_name' => "Offered Items",
                        'ryo' => "Offered Money"
                    ), // Main fields
                    array(
                        array("name" => "Check User", "id" => "13", "page" => "profile", "uid" => "table.uid"),
                        array("name" => "Decline", "id" => $_GET['id'], "act" => "decline_offer", "oid" => "table.id"),
                        array("name" => "Accept", "id" => $_GET['id'], "act" => "accept_offer", "oid" => "table.id", "tid" => "table.tid")
                    ), // option links
                    true, // Send directly to contentLoad
                    true, // Show previous/next links
                    false, // Category links
                    true, // Allow sorting on columns
                    false, // pretty-hide options
                    false, // No search box on top
                    array('message'=>"Here you can see your standing offers. Offers which have been declined will no longer show up.",'hidden'=>'yes') // Description at top
                );

                // Return Link
                $GLOBALS['template']->assign("returnLink", true);

            }
            else{
                throw new Exception("You are not associated with this trade");
            }
        }
        else{
            throw new Exception("Could not make sense of the trade ID");
        }
    }

    // Accept and offer and end the trade
    protected function do_accept_offer() {

        // Check the trade ID
        if ( is_numeric($_GET['oid']) && is_numeric($_GET['tid']) ) {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Get ALL the offers related to this trade FOR UPDATE
            $offer_data = $this->getOffer(array(
                    "`tid`" => $_GET['tid']
                ), true, 20
            );
            if ($offer_data != '0 rows') {

                // Check if the offer we're trying to accept is in the offer array
                $acceptedOffer = false;
                $rejectedIDs = array();
                for( $i=0; $i<count($offer_data); $i++ ){
                    if( $offer_data[$i]['id'] == $_GET['oid'] ){
                        if( in_array( strtolower($offer_data[$i]['offer_type']), $this->availTradeTypes ) ){
                            $acceptedOffer = $offer_data[$i];
                        }
                    }
                    else{
                        $rejectedIDs[] = $offer_data[$i]['uid'];
                    }
                }

                // Check that the offer was found
                if( $acceptedOffer !== false ){

                    // Check that the trade exists
                    $trade_data = $this->getTrade(array("`trades`.`id`" => $acceptedOffer['tid'] , "`uid`" => $_SESSION['uid'] ) );
                    if( $trade_data !== "0 rows" ){

                        // Switch the items:
                        $GLOBALS['database']->execute_query("
                            UPDATE `users_inventory`
                            SET
                                `equipped` = 'no',
                                `uid` = CASE
                                WHEN `trade_type` = 'offer' THEN '".$_SESSION['uid']."'
                                WHEN `trade_type` = 'trade' THEN '".$acceptedOffer['uid']."'
                                ELSE `uid` END,
                                `trading` = NULL,
                                `trade_type` = NULL
                            WHERE
                                `trading` = '" . $trade_data[0]['id'] . "' AND
                                `uid` IN (".$_SESSION['uid'].", ".$acceptedOffer['uid'].")"
                        );

                        // Upload ryo to trader & return to other offeres
                        if( !$GLOBALS['database']->execute_query("
                            UPDATE `users`,`users_statistics`,`trade_offers`
                            SET
                                `money` = `money` + `ryo`
                            WHERE
                                `trade_offers`.`tid` = '" . $acceptedOffer['tid'] . "' AND
                                `users`.`id` = `users_statistics`.`uid` AND
                                ((
                                    `users_statistics`.`uid` = `trade_offers`.`uid` AND
                                    `users_statistics`.`uid` != '".$acceptedOffer['uid']."'
                                ) OR (
                                    `trade_offers`.`uid` = '".$acceptedOffer['uid']."' AND
                                    `users_statistics`.`uid` = '".$_SESSION['uid']."'
                                ))
                        ")){
                            throw new Exception("There was an error updating the user information");
                        }

                        $users_notifications = new NotificationSystem('', $acceptedOffer['uid']);

                        $users_notifications->addNotification(array(
                                                                    'id' => 20,
                                                                    'duration' => 'none',
                                                                    'text' => $GLOBALS['userdata'][0]['username'] . " has accepted your offer.",
                                                                    'dismiss' => 'yes'
                                                                ));

                        $users_notifications->recordNotifications();

                        // Set all the rejected IDs as not trading
                        $GLOBALS['database']->execute_query("
                            UPDATE `users_inventory`
                            SET
                               `trading` = NULL,
                               `trade_type` = NULL
                            WHERE
                               `trading` = '" . $trade_data[0]['id'] . "'
                        ");

                        // Insert log entry
                        if( !$GLOBALS['database']->execute_query("
                            INSERT INTO `trade_log` (`user1`,`user2`,`rejected`,`time`,`items1`,`items2`)
                            VALUES
                            ('" . $_SESSION['uid'] . "',
                             '" . $acceptedOffer['uid'] . "',
                             '|" . implode("|",$rejectedIDs) . "|',
                             '" . $GLOBALS['user']->load_time . "',
                             '" . str_replace("'","\'",$trade_data[0]['trade_name']) . "',
                             '" . $acceptedOffer['offer_name'] . ", and " . $acceptedOffer['ryo'] . " ryo')") )
                        {
                            throw new Exception("There was an error logging the trade transaction");
                        }

                        // Delete trade entry & ALL offers
                        $this->doDeleteOffers($trade_data[0]['id']);

                        // Delete the trade
                        if( !$GLOBALS['database']->execute_query("DELETE FROM `trades` WHERE `trades`.`id` = '".$trade_data[0]['id']."' LIMIT 1") ){
                            throw new Exception("An error occured trying to delete the trade");
                        }

                        // Commit transaction
                        $GLOBALS['database']->transaction_commit();

                        // Message
                        $GLOBALS['page']->Message( "The offer has been accepted." , $this->trade_title, 'id='.$_GET['id'],'Return');

                    }
                    else{
                        throw new Exception("You are not the owner of this trade, and can not accept the offer");
                    }
                }
                else{
                    throw new Exception("The offer you specified could not be attached to this trade");
                }
            } else {
                throw new Exception("The offer could not be found in the database");
            }
        } else {
            throw new Exception("Could not identify which offer you're trying to remove");
        }
    }

    //  Search the trades
    protected function search_form() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Search for Item","inputFieldName"=>"itemName", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            array('message' => "If you're looking for a specific item, then you can search for it here.", 'hidden'=>'yes'), // Information
            "Search the Trade", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Search"), // Submit button
            "Return" // Return link name
        );
    }

    // Do show search results
    protected function search_results() {

        // Is a seach item set?
        if ( isset($_POST['itemName']) && $_POST['itemName'] !== "" ) {

            // Minimum
            $min =  tableParser::get_page_min();

            // Selector on type
            $selector = "";
            if( isset($_GET['type']) && in_array( strtolower($_GET['type']), $this->available_types ) ){
                $selector = " AND `item_types` LIKE '%".$_GET['type']."%' ";
            }

            // Get the trades
            $trades = $GLOBALS['database']->fetch_data("
                 SELECT
                    `trades`.*,
                    `users`.`username`
                 FROM `trades`,`users`
                 WHERE
                    `users`.`id` = `trades`.`uid` AND
                    `trades`.`uid` != '".$_SESSION['uid']."' AND
                    `trade_type` IN ('". implode("','", $this->availTradeTypes) ."')
                    ".$selector." AND
                    `trade_name` LIKE '%".$_POST['itemName']."%'
                 ORDER BY `time` DESC
                 LIMIT ".$min.",10 ");

            // Show the list
            tableParser::show_list(
                "myTrades",
                $this->trade_title. " Search Results",
                $trades,
                array(
                    'username' => "User",
                    'trade_name' => "Items"
                ), // Main fields
                array(
                    array("name" => "View Details", "id" => $_GET['id'], "act" => "viewtrade", "tid" => "table.id"),
                    array("name" => "Make Offer", "id" => $_GET['id'], "act" => "make_offer", "tid" => "table.id")
                ), // option links
                true, // Send directly to contentLoad
                true, // Show previous/next links
                false, // Category links
                true, // Allow sorting on columns
                false, // pretty-hide options
                false, // No search box on top
                false // Description at top
            );

            // Return Link
            $GLOBALS['template']->assign("returnLink", true);

        } else {
            throw new Exception("You did not search for anything?");
        }
    }

    // Show the user trade history
    protected function history(){

        // Minimum
        $min =  tableParser::get_page_min();

        // Gather
        $entries = $GLOBALS['database']->fetch_data( "SELECT  *
                  FROM `trade_log`
                  WHERE
                    `user1` = '".$_SESSION['uid']."' OR
                    `user2` = '".$_SESSION['uid']."' OR
                    `rejected` LIKE '%|".$_SESSION['uid']."|%'
                  ORDER BY `time` DESC
                  LIMIT ".$min.",10 ");

        // Fix entries
        if( $entries !== "0 rows" ){
            for( $i=0; $i<count($entries); $i++ ){

                // What was won:
                if( preg_match("/^, and /", $entries[$i]['items2']) ){
                    $entries[$i]['items2'] = str_replace(", and ", "", $entries[$i]['items2']);
                }

                //
                switch( $_SESSION['uid'] ){
                    case $entries[$i]['user1']:
                        $entries[$i]['traded'] = $entries[$i]['items1'];
                        $entries[$i]['received'] = $entries[$i]['items2'];
                        $entries[$i]['status'] = "Completed";
                    break;
                    case $entries[$i]['user2']:
                        $entries[$i]['traded'] = $entries[$i]['items2'];
                        $entries[$i]['received'] = $entries[$i]['items1'];
                        $entries[$i]['status'] = "Completed";
                    break;
                    default:
                        $entries[$i]['traded'] = "N/A";
                        $entries[$i]['received'] = $entries[$i]['items1'];
                        $entries[$i]['status'] = "Offer Declined";
                    break;
                }
            }
        }

        // Show the list
        tableParser::show_list(
            "myTrades",
            $this->trade_title. ", Trade History",
            $entries,
            array(
                'traded' => "You Traded",
                'received' => "You Received",
                'status' => "Status"
            ), // Main fields
            false, // option links
            true, // Send directly to contentLoad
            true, // Show previous/next links
            false, // Category links
            true, // Allow sorting on columns
            false, // pretty-hide options
            false, // No search box on top
            false // Description at top
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // BELOW ALL FUNCTIONS RELATED TO PUTTING THING IN/OUT AND UPDATING THE DATABASE

    // Delete all offers for given trade ID
    private function doDeleteOffers( $tradeID ){
        $GLOBALS['database']->execute_query("DELETE FROM `trade_offers` WHERE `trade_offers`.`tid` = '".$tradeID."'");
    }

    // Remove trade offer and return money
    protected function removeAllOffers( $tradeID ){

        // Return money to people who made offers
        $GLOBALS['database']->execute_query("
            UPDATE `users_statistics`,`trade_offers`
            SET `money` = `money` + `ryo`
            WHERE
                `trade_offers`.`tid` = '" . $tradeID . "' AND
                `users_statistics`.`uid` = `trade_offers`.`uid`
        ");

        // Delete entries in offer table
        $this->doDeleteOffers($tradeID);
    }

    // Remove trade offer and return money
    protected function removeTradeOffer( $offerID ){

        // Return money to people who made offers
        $GLOBALS['database']->execute_query("
            UPDATE `users_statistics`,`trade_offers`
            SET `money` = `money` + `ryo`
            WHERE
                `trade_offers`.`id` = '" . $offerID . "' AND
                `users_statistics`.`uid` = `trade_offers`.`uid`
        ");

        // Delete entries in offer table
        $GLOBALS['database']->execute_query("DELETE FROM `trade_offers` WHERE `trade_offers`.`id` = '".$offerID."' LIMIT 1");

    }

    // Get currently traded items
    protected function getTradingItems( $whereArray , $lock = false, $limit = 1 ){

        // Gather
        $query = "SELECT  *
                  FROM `users_inventory`
                  WHERE " . $this->createWhereQuery($whereArray) . "
                  LIMIT ".$limit;

        // Lock if needed
        if( $lock == true ){
            $query .= " FOR UPDATE";
        }

        // Return result
        return $GLOBALS['database']->fetch_data( $query );
    }

    // Get an offer
    protected function getOffer( $whereArray , $lock = false, $limit = 1 ){

        // Gather
        $query = "SELECT *
                 FROM `trade_offers`
                 WHERE " . $this->createWhereQuery($whereArray) . "
                 LIMIT ".$limit;

        // Lock if needed
        if( $lock == true ){
            $query .= " FOR UPDATE";
        }

        // Return result
        return $GLOBALS['database']->fetch_data( $query );
    }

    // Get a trade
    protected function getTrade( $whereArray , $lock = false, $limit = 1){

        // Gather
        $query = "SELECT
                    `trades`.*,
                    `users`.`username`
                 FROM `trades`,`users`
                 WHERE
                    `users`.`id` = `trades`.`uid` AND
                    " . $this->createWhereQuery($whereArray) . "
                  LIMIT ".$limit;

        // Lock if needed
        if( $lock == true ){
            $query .= " FOR UPDATE";
        }

        // Return result
        return $GLOBALS['database']->fetch_data( $query );
    }

    // Get current trade count
    protected function getCurrentTradeCount(){
        if( isset($this->trade_type) ){
            $trades = $GLOBALS['database']->fetch_data("
                SELECT
                    COUNT(`uid`) AS `total`
                FROM `trades`
                WHERE
                    `uid` = '" . $_SESSION['uid'] . "' AND
                    `trade_type` = '".$this->trade_type."'
            ");
            return $trades;
        }
        else{
            throw new Exception("No trade type was set.");
        }
    }

    // Get current trade count
    protected function getCurrentEntries( $table, $whereArray ){
        if( isset($this->trade_type) ){
            // Get the entries
            $entries = $GLOBALS['database']->fetch_data("
                SELECT *
                FROM `".$table."`
                WHERE ".$this->createWhereQuery($whereArray)."
            ");
            return $entries;
        }
        else{
            throw new Exception("No trade type was set.");
        }
    }

    // Get tradeable items for this user
    private function getTradeableItems( $lock = false ){

        // Selector
        $types = array();
        foreach($this->available_types as $type){
            $types[] = "'".$type."'";
        }

        $query = "
            SELECT
                `items`.`name`, `users_inventory`.`id`, `items`.`type`, `users_inventory`.`stack`
            FROM
                `users_inventory`,`items`
            WHERE
               `items`.`id` = `users_inventory`.`iid` AND
               `users_inventory`.`uid` = '" . $_SESSION['uid'] . "' AND
               `tradeable` = 'yes' AND
               `equipped` = 'no' AND
               `stack` > 0 AND
               `durabilityPoints` > 0 AND
               `trading` IS NULL AND
               `finishProcessing` = 0 AND
               (`canRepair` = 'yes' OR (`type` != 'weapon' AND `type` != 'armor')) AND
               `type` IN (".implode(",",$types).")
               ".( isset($this->db_restriction) ? " AND `{$this->db_restriction}` = 'yes'" : '' )."
            ORDER BY `type`,`name` ASC";
        if( $lock == true ){
            $query .= " FOR UPDATE";
        }

        // Get items & attach stack volumes
        $items = $GLOBALS['database']->fetch_data($query);
        if( $items !== "0 rows" && !empty($items) ){
            foreach( $items as $key => $item ){
                $items[$key]["name"] = $items[$key]["name"] . " (".$items[$key]["stack"].")";
            }
        }

        return $items;
    }

    // Insert trade entry
    private function createEntry( $table, $dataArray ){

        $keys = array_keys($dataArray);

        $GLOBALS['database']->execute_query("
            INSERT INTO `".$table."` (".implode(",",$keys).")
            VALUES ( ".implode(",",$dataArray)." )");
    }

    // Create query from where array
    private function createWhereQuery( $whereArray ){
        $result = "";
        foreach($whereArray as $key => $value){
            $result .= ($result == "") ? "".$key." = '".$value."'" : " AND ".$key." = '".$value."'";
        }
        return $result;
    }


}
