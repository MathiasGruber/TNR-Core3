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

// A library with a bunch of convenience functions for federal support
abstract class fedsupportLib {
    
    // Returns value in USD
    public static function getSupportValue( $fedLevel ){
        switch( $fedLevel ){
            case "Normal": return 5; break;
            case "Silver": return 10; break;
            case "Gold": return 15; break;
            default: throw new Exception("This is not a valid federal level: ".$fedLevel); break;
        }
    }
    
    // Get the total upgrade price from one fed to another
    public static function getUpgradePrice( $oldFedLvl, $newFedLvl, $secondsLeft ){
        
        // Get total cost
        $totalCost = self::getSupportValue($newFedLvl) - self::getSupportValue($oldFedLvl);
        
        // Time left fraction of month
        $fractionLeft = $secondsLeft / ( 30*24*3600 );
        
        // Return price
        return round($totalCost * $fractionLeft,2);
        
    }
    
    // Return time left given the current federal_timer
    public static function getTimeLeft( $federalTimer ){
        return floor( $federalTimer - time() );
    }
}