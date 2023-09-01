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

 // Get libraries
require_once(Data::$absSvrPath.'/libs/itemSystem/repairLib.php');

class townRepair extends repairLib {

    // Constructor
    public function __construct() {

         // Try phrase
        try{

            // Check session
            functions::checkActiveSession();

            // Run constructor on parent
            parent::__construct();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Decide what page to show
            if( !isset( $_GET['act'] ) || $_GET['act'] == "submit" ){
                $this->mainRepair();
            }
            else{
                if( $_GET['act'] == "weaponRepair" ){
                    $this->showBrokenItems( "weapon" );
                }
                elseif( $_GET['act'] == "armorRepair" ){
                    $this->showBrokenItems( "armor" );
                }
                elseif( $_GET['act'] == "offer" ){
                    if( isset($_POST['Submit']) ){
                        $this->setRepairOffer();
                    }
                    else{
                        $GLOBALS['page']->UserInput(
                            "Put up offer on repair",
                            "Village Repair Hall",
                            array(
                                array(
                                    "infoText"=>"Enter Ryo Amount",
                                    "inputFieldName"=>"ryo",
                                    "type" => "input",
                                    "inputFieldValue"=>""
                                )
                            ),
                            array(
                                "href"=>"?id=".$_GET['id']."&act=".$_GET['act']."&inv_id=".$_GET['inv_id']."&iid=".$_GET['iid'] ,
                                "submitFieldName"=>"Submit",
                                "submitFieldText"=>"Submit Offer"),
                            "Return"
                        );
                    }
                }
                elseif( $_GET['act'] == "doRepair" ){
                    $this->startRepairItem();
                }
                elseif( $_GET['act'] == "removeOffer" ){
                    $this->doRemoveOffer();
                }
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Repair System', 'id='.$_GET['id'],'Return');
        }
    }

}
new townRepair();