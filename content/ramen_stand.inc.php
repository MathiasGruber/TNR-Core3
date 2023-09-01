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

// Get the eating library
require_once(Data::$absSvrPath.'/libs/hospitalSystem/eatingLib.inc.php');

class ramen_stand extends eatingLib {

    // Constructor
    public function __construct() {

        // Try phrase
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Set the menu
            $this->setRamenMenu();

            // What page to show
            if (!isset($_GET['act'])) {

                // Show main screen
                $this->main_screen();
            } elseif( $_GET['act'] == "order" ) {

                // Buy ramen
                $this->buy_ramen();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Ramen Shop', 'id='.$_GET['id'],'Return');
        }
    }

    // Set the available menu in this shop
    private function setRamenMenu(){

        // Basic menu
        $this->menu = array(
            array("id" => 1, "name" => "Standard Ramen", "heal" => 30, "cost" => 3),
            array("id" => 2, "name" => "Delicious Ramen", "heal" => 40, "cost" => 4),
            array("id" => 3, "name" => "Special Ramen", "heal" => 50, "cost" => 5),
            array("id" => 4, "name" => "Deluxe Edition", "heal" => 80, "cost" => 7),
            array("id" => 5, "name" => "Special Health Edition - Small", "heal" => 300, "cost" => 10),
            array("id" => 6, "name" => "Special Health Edition - Medium", "heal" => 900, "cost" => 13),
            array("id" => 7, "name" => "Special Health Edition - Large", "heal" => 1500, "cost" => 15)
        );
        $healthLoss = round($GLOBALS['userdata'][0]['max_health'] - $GLOBALS['userdata'][0]['cur_health'],1);
        $price = round($healthLoss / 25, 1);
        if( $price > 0 ){
            $this->menu[] = array("id" => 8, "name" => "All you can eat", "heal" => $healthLoss, "cost" => $price);
        }

        for( $i=0; $i<count($this->menu); $i++ ){
            $this->menu[$i]['cost'] = $this->menu[$i]['cost'] * 2 ;
        }

        // Get occupation and reduce cost based on this
        require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
        $professionLib = new professionLib();
        $professionLib->setJobType("profession");
        $professionLib->fetch_user();

        // Check for professions
        if( isset($professionLib->user[0]['name']) ){
            if( $professionLib->setGains($professionLib->user[0]['name']) ){
                foreach( $professionLib->gains as $gain ){
                    if( $gain['type'] == "ramen" && $gain['discount'] > 0 ){
                        for( $i=0; $i<count($this->menu); $i++ ){
                            $this->menu[$i]['cost'] = $this->menu[$i]['cost'] * ((100-$gain['discount']) / 100) ;
                        }
                    }
                }
            }
        }
    }

    // Main screen
    private function main_screen() {
        $this->showRamenMenu( "Buy your ramen here. You currently have ".$GLOBALS['userdata'][0]['money']." ryo on you." );
    }

    // Buy function
    private function buy_ramen() {
        if( isset( $_GET['orderID'] ) && is_numeric( $_GET['orderID'] ) ){
            $this->buyRamenFromMenu( $_GET['orderID'] );
        }
        else{
            throw new Exception("Could not identify the order ID");
        }
    }

}

new ramen_stand();