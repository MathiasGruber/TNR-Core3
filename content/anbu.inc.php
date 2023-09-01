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

require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');

class ANBU extends anbuLib {

    //	Constructor
    public function __construct() {

        // A variable set to true when page performs transaction
        $this->hasTransaction = false;

        // Check if this user is in a squad
        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Check if user is marked as part of ANBU
            $anbuID = $this->isUserAnbu( $GLOBALS['userdata'][0]['anbu'] );
            if( $anbuID ){

                // Get Squad
                $this->squad = $this->getAnbuSquad( $anbuID );
                if ($this->squad != '0 rows') {

                    // Decide what page to show
                    if ( !isset($_GET['act']) )
                    {
                        $this->squadMain();
                    }
                    elseif ($_GET['act'] == 'resign')
                    {
                        if (!isset($_POST['Submit'])) {
                            $this->resignForm();
                        } else {
                            $this->ANBUresign( $_SESSION['uid'] );
                        }
                    }
                    elseif ($_GET['act'] == 'shop')
                    {

                        // Get libraries
                        require_once(Data::$absSvrPath.'/libs/itemSystem/shopLib.inc.php');

                        // Instantiate chat class
                        $anbuShop = new shopLib();

                        // The itemlevel for this user
                        $itemLevel = $this->getItemLevel();

                        // Setup the shop
                        $anbuShop->setupShopSystem(
                            array(
                                "required_rank" => $GLOBALS['userdata'][0]['rank_id'],
                                "in_shop" => array( "anbu" ),
                                "types" => array( "weapon", "armor", "item" ),
                                "item_level" => $itemLevel,
                                "shopName" => "ANBU Itemshop",
                                "smartyTemplate" => "contentLoad",
                                "shopDescriptions" => "Items in this shop are exclusive to ANBU squad members.",
                                "itemToShop" => 10
                            )
                        );

                        // Wrap the contentLoad in a page wrapper with this javascript library
                        $GLOBALS['page']->createPageWrapper("./content/item_shop/Scripts/shopScripts.js");

                    }
                    elseif ( $this->squad[0]['leader_uid'] == $_SESSION['uid'] )
                    {
                        if ($_GET['act'] == 'kick') {
                            if (!isset($_POST['Submit'])) {
                                $this->kickUserForm();
                            } else {
                                $this->kickUser();
                            }
                        } elseif ($_GET['act'] == 'invite') {
                            if (!isset($_POST['Submit'])) {
                                $this->inviteUserForm();
                            } else {
                                $this->inviteUser( $_POST['username'] );
                            }
                        } elseif ($_GET['act'] == 'orders') {
                            if (!isset($_POST['Submit'])) {
                                $this->ANBUOrders();
                            } else {
                                $this->edit_orders();
                            }
                        }
                    }
                }
                else{
                    $this->setAnbuSquad( $_SESSION['uid'], "_none" );
                    throw new Exception("Your ANBU squad has been disbanded.");
                }
            }
            else{
                throw new Exception("You are not marked as part of an ANBU. ANBU Squads are managed by the village kage.");
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {

            // Rollback possible transactions
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );

            // Show error message
            $GLOBALS['page']->Message( $e->getMessage() , 'ANBU HQ', 'id='.$_GET['id']);
        }
    }


}

new ANBU();