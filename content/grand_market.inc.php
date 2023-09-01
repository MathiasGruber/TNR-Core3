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

require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');
require_once(Data::$absSvrPath.'/libs/itemSystem/tradeLib.php');
class item_trade extends tradeLib {

    // Constructor
    public function __construct() {

        // Try phrase
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Trade setup
            $this->max_trades = 3; // Max trades per user
            $this->max_trade_items = 2; // Maximum items to be put up for trade
            $this->max_offers = 5; // Max offers per user
            $this->max_offer_items = 5; // Maximum items to be put up for trade
            $this->admin_fees = 100;

            $this->trade_type = $GLOBALS['userdata'][0]['village'];
            $this->availTradeTypes = array( strtolower($this->trade_type) );

            $this->trade_title = $GLOBALS['userdata'][0]['village'] . " Grand Market";
            $this->db_restriction = "grand_trade";
            $this->available_types = array('armor','weapon','item','artifact','special','process','material','tool','reduction','repair','furniture');

            // From the alliance, add all the villages that are allied & neutral
            foreach( $GLOBALS['userdata'][0]['alliance'][0] as $key => $value){
                if( is_numeric($value) && ($value == 1) && !in_array($key, $this->availTradeTypes) ){
                    $this->availTradeTypes[] = strtolower($key);
                }
            }

            // Decide what page to show
            if (!isset($_GET['act'])) {

                // Main menu
                $this->main_menu();

                // Return Link
                $link = ($GLOBALS['userdata'][0]['village'] == "Syndicate") ? "?id=78" : "?id=9";
                $GLOBALS['template']->assign("returnLink", $link);

            }
            else{

                // Determine page to show
                switch( $_GET['act'] ){
                    case "newtrade":
                        $trades = $this->getCurrentTradeCount();
                        if( $trades[0]['total'] < $this->max_trades  ){
                            if (!isset($_POST['SubmitTrade'])) {
                                $this->frm_make_trade();
                            } else {
                                $this->do_make_trade();
                            }
                        }
                        else{
                            throw new Exception("You already have the ".$this->max_trades." trades in the system.");
                        }
                    break;
                    case "mytrades":
                        $this->my_trades();
                    break;
                    case "rmv_trade":
                        if (isset($_POST['Submit'])) {
                            $this->do_remove_trade();
                        } else {
                           $GLOBALS['page']->Confirm("Are you sure you want to cancel this trade?", ucfirst($this->trade_type) . ' Trading', 'Cancel Now!');
                        }
                    break;
                    case "browse":
                        $this->browse_trades();
                    break;
                    case "viewtrade":
                        $this->view_trade();
                    break;
                    case "make_offer":
                        if (isset($_POST['SubmitOffer'])) {
                            $this->do_make_offer();
                        } else {
                            $this->make_offer_frm();
                        }
                    break;
                    case "myoffers":
                         $this->my_offers();
                    break;
                    case "rmv_offer":
                        if (isset($_POST['Submit'])) {
                            $this->do_withdraw_offer();
                        } else {
                           $GLOBALS['page']->Confirm("Are you sure you want to cancel this offer?", ucfirst($this->trade_type) . ' Trading', 'Cancel Now!');
                        }
                    break;
                    case "trade_offers":
                        $this->view_trade_offers();
                    break;
                    case "decline_offer":
                        if (isset($_POST['Submit'])) {
                            $this->do_withdraw_offer();
                        } else {
                           $GLOBALS['page']->Confirm("Are you sure you want to decline this offer?", ucfirst($this->trade_type) . ' Trading', 'Cancel Now!');
                        }
                    break;
                    case "accept_offer":
                        if (isset($_POST['Submit'])) {
                            $this->do_accept_offer();
                        } else {
                           $GLOBALS['page']->Confirm("Are you sure you want to accept this offer?", ucfirst($this->trade_type) . ' Trading', 'Accept Now!');
                        }
                    break;
                    case "search":
                        if (!isset($_POST['Submit'])) {
                            $this->search_form();
                        } else {
                            $this->search_results();
                        }
                    break;
                    case "history":
                        $this->history();
                    break;
                    default:
                        throw new Exception("Could not determine what action to perform");
                    break;
                }
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , ucfirst($this->trade_type) . ' Trading', 'id='.$_GET['id'],'Return');
        }
    }


}

new item_trade();