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
require_once(Data::$absSvrPath.'/libs/itemSystem/shopLib.inc.php');

class itemshop extends shopLib {

    function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        // Village Data
        $village = cachefunctions::getVillage( $GLOBALS['userdata'][0]['village'] );

        // Setup the shop
        $this->setupShopSystem(
            array(
                "required_rank" => $GLOBALS['userdata'][0]['rank_id'],
                "in_shop" => array( "normal" ),
                "types" => array(
                    "weapon",
                    "armor",
                    "item" ,
//                    "special",
//                    "material",
//                    "tool",
                ),
                "item_level" => $village[0]['shop_level'],
                "shopName" => "Traveling Merchant",
                "smartyTemplate" => "contentLoad",
                "shopDescriptions" => "Welcome to the Traveling Merchant, see anything you like?",
                "itemToShop" => 10,
                'is_map' => true,
            )
        );

        // Wrap the contentLoad in a page wrapper with this javascript library
        $GLOBALS['page']->createPageWrapper("./content/item_shop/Scripts/shopScripts.js");

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }

    }

}
new itemshop();