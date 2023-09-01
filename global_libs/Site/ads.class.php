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

    class Ads {

        private $ad_array = array('adsense'); //  List of vendor functions (name the same as functions below)
        private $ad_code;                     //  Ad Code Storage

        //  Constructor    
        public function __construct() {
            $ad_type = array_rand($this->ad_array, 1);
            $this->{$this->ad_array[$ad_type]}();
        }

        //  Return the advertisement block to the page class
        public function returnAd() {
            return $this->ad_code;
        }

        /*
         *              Functions for separate advertisement vendors here.
         */

        private function adsense() {
            $ad_loc = Data::$absSvrPath.'/files/layout_' . (isset($GLOBALS['layout']) ? $GLOBALS['layout'] : 'default') . '/adsense.inc';
            if($ad_data = fopen($ad_loc, 'rb')) {
                $this->ad_code = fread($ad_data, filesize($ad_loc));
                fclose($ad_data);
            }
        }
    }