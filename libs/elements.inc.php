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
abstract class elementSystem
{
    public static $mainElements = array( "fire", "wind", "lightning", "earth", "water" );
    
    public static $specialElements = array( 
        "scorching"     => array("wind", "fire"), 
        "tempest"       => array("lightning", "wind"), 
        "magnetism"     => array("earth", "lightning"), 
        "wood"          => array("water", "earth"), 
        "steam"         => array("water", "fire"), 
        "light"         => array("fire", "lightning"), 
        "dust"          => array("earth", "wind"), 
        "storm"         => array("water", "lightning"), 
        "lava"          => array("fire", "earth"), 
        "ice"           => array("water", "wind"),
        
        "fire"          => array("fire", "fire"),
        "wind"          => array("wind", "wind"),
        "lightning"     => array("lightning", "lightning"),
        "earth"         => array("earth", "earth"),
        "water"         => array("water", "water")
    );
    
    public static $strengthsWeaknesses = array(
        "fire"      => array("strong" => "wind", "weak" => "water"),
        "wind"      => array("strong" => "lightning", "weak" => "fire"),
        "lightning" => array("strong" => "earth", "weak" => "wind"),
        "earth"     => array("strong" => "water", "weak" => "lightning"),
        "water"     => array("strong" => "fire", "weak" => "earth"),
        
        "scorching" => array("strong" => "tempest", "weak" => "steam"),
        "tempest"   => array("strong" => "magnetism", "weak" => "scorching"),
        "magnetism" => array("strong" => "wood", "weak" => "tempest"),
        "wood"      => array("strong" => "steam", "weak" => "magnetism"),
        "steam"     => array("strong" => "scorching", "weak" => "wood"),
        "light"     => array("strong" => "dust", "weak" => "ice"),
        "dust"      => array("strong" => "storm", "weak" => "light"),
        "storm"     => array("strong" => "lava", "weak" => "dust"),
        "lava"      => array("strong" => "ice", "weak" => "storm"),
        "ice"       => array("strong" => "light", "weak" => "lava")
    );
    
    public static function get_random_element(){
        return elementSystem::$mainElements[ random_int(0, count(elementSystem::$mainElements)-1) ];
    }
      
    public static function createRandomUserAffinities() {
        
        // Convenience variable for the elements
        $elements = elementSystem::$mainElements;
        
        // Create the primary element
        $primary = $elements[ random_int(0, count($elements)-1 ) ];
        
        // Create the second element
        $secondary = $elements[ random_int(0, count($elements)-1 ) ];
        while( $primary == $secondary ){
            $secondary = $elements[ random_int(0, count($elements)-1 ) ];
        }
        
        // Return elemental affinities as string
        return $primary.":".$secondary;      
    }
    
    public static function getActiveAffinities( $rankID, $userAffinities, $bloodline ){
        
        // Get defaults
        $affinities = explode(":", $userAffinities );
        
        // Affinity 1: If bloodline, then set it. Otherwise, disable till Chuunin
        if( isset($bloodline[0]['affinity_1']) && $bloodline[0]['affinity_1'] !== ""){
            $affinities[0] = $bloodline[0]['affinity_1'];
        }
        else{
            if( $rankID < 3 ){ $affinities[0] = "";}
        }
        
        // Affinity 2: If bloodline, then set it. Otherwise, disable till Jounin
        if( isset($bloodline[0]['affinity_2']) && $bloodline[0]['affinity_2'] !== ""){
            $affinities[1] = $bloodline[0]['affinity_2'];
        }
        else{
            if( $rankID < 4 ){ $affinities[1] = "";}
        }
        
        // Return 
        return $affinities;
    }
    
    public static function getSpecialElement( $elem1, $elem2 , $bloodline ){
        $localList = elementSystem::$specialElements;
        $element = "";
        if( isset($bloodline[0]['affinity_2']) && $bloodline[0]['affinity_2'] !== ""){
            foreach( $localList as $key => $value ){
                if( ($value[0] == $elem1 && $value[1] == $elem2) || $value[0] == $elem2 && $value[1] == $elem1 ){
                    $element = $key;
                }
            }
        }
        return $element;
    }
    
    // Returns the element and its weakness
    public static function elementAndWeakness( $element ){
        return array( $element, elementSystem::$strengthsWeaknesses[ strtolower($element) ]['weak'] );
    }
    
    // Get a random element
    public static function getRandomElement(){
        $elements = elementSystem::$mainElements;
        $element = $elements[ random_int(0, count($elements)-1 ) ];
        return $element;
    }
    
    // Get special elemental mastery value
    public static function getSpecialElementaryMasteryValue( $specialElement, $rankID, $primary, $secondary ){
        $specialValue = 0;
        if( isset($specialElement) &&
            !empty($specialElement) )
        {
            if( $rankID > 3 ){
                $specialValue = floor(($primary + $secondary) * 0.5);
            }
            else{
                $specialValue = $primary;
            }
        }
        return $specialValue;
    }
    
    public static function checkMasteryBonus( $userData, $element, $check, $actionRank ){
        
        // Figure out the active value
        $masteryValue = 0;
        switch( $element ){
            case $userData['elemental_master_1']: 
                $masteryValue = $userData['primary_element_mastery'];
            break;
            case $userData['elemental_master_2']: 
                $masteryValue = $userData['secondary_element_mastery'];
            break;
            case $userData['elemental_master_special']: 
                $masteryValue = $userData['special_element_mastery'];
            break;
            default: 
                return false; 
            break;
        }
        
        // Set requirements
        $percentage = 0;
        switch( $actionRank ){
            case 3: 
                if( $masteryValue >= 160000 ){ $percentage = 100; }
                elseif( $masteryValue >= 144000 ){ $percentage = 90; }
                elseif( $masteryValue >= 128000 ){ $percentage = 80; }
                elseif( $masteryValue >= 112000 ){ $percentage = 70; }
                elseif( $masteryValue >= 96000 ){ $percentage = 60; }
                elseif( $masteryValue >= 80000 ){ $percentage = 50; }
                elseif( $masteryValue >= 64000 ){ $percentage = 40; }
                elseif( $masteryValue >= 48000 ){ $percentage = 30; }
                elseif( $masteryValue >= 32000 ){ $percentage = 20; }
                elseif( $masteryValue >= 16000 ){ $percentage = 10; }
            break;
            case 4: 
                if( $masteryValue >= 200000 ){ $percentage = 100; }
                elseif( $masteryValue >= 180000 ){ $percentage = 90; }
                elseif( $masteryValue >= 160000 ){ $percentage = 80; }
                elseif( $masteryValue >= 140000 ){ $percentage = 70; }
                elseif( $masteryValue >= 120000 ){ $percentage = 60; }
                elseif( $masteryValue >= 100000 ){ $percentage = 50; }
                elseif( $masteryValue >= 80000 ){ $percentage = 40; }
                elseif( $masteryValue >= 60000 ){ $percentage = 30; }
                elseif( $masteryValue >= 40000 ){ $percentage = 20; }
                elseif( $masteryValue >= 20000 ){ $percentage = 10; }
            break;
            case 5: 
                if( $masteryValue >= 250000 ){ $percentage = 100; }
                elseif( $masteryValue >= 225000 ){ $percentage = 90; }
                elseif( $masteryValue >= 200000 ){ $percentage = 80; }
                elseif( $masteryValue >= 175000 ){ $percentage = 70; }
                elseif( $masteryValue >= 150000 ){ $percentage = 60; }
                elseif( $masteryValue >= 125000 ){ $percentage = 50; }
                elseif( $masteryValue >= 100000 ){ $percentage = 40; }
                elseif( $masteryValue >= 75000 ){ $percentage = 30; }
                elseif( $masteryValue >= 50000 ){ $percentage = 20; }
                elseif( $masteryValue >= 25000 ){ $percentage = 10; }
            break;
        }
        
        // Figure out if the user has this element
        switch( $check ){
            case "STACHACOST": 
                if( $percentage === 100 ){ return 50; }
                elseif( $percentage >= 80 ){ return 35; }
                elseif( $percentage >= 60 ){ return 20; }
                elseif( $percentage >= 40 ){ return 10; }
                elseif( $percentage >= 20 ){ return 5; }
            break;
            case "SpecialJutsu": 
                if( $percentage >= 90 ){ return (45*60); }
                elseif( $percentage >= 60 ){ return (30*60); }
                elseif( $percentage >= 30 ){ return (15*60); }
            break;
            case "BloodlineJutsu": 
                if( $percentage >= 80 ){ return (30*60); }
                elseif( $percentage >= 50 ){ return (20*60); }
                elseif( $percentage >= 20 ){ return (10*60); }
            break;
            case "RyoReduction": 
                if( $percentage >= 90 ){ return 25; }
                elseif( $percentage >= 70 ){ return 20; }
                elseif( $percentage >= 50 ){ return 15; }
                elseif( $percentage >= 30 ){ return 10; }
                elseif( $percentage >= 10 ){ return 5; }
            break;
            case "WeaponUnlock": 
                if( $percentage >= 40 ){ return 1; }
            break;
            case "MaxUses": 
                if( $percentage >= 90 ){ return 0; }
                elseif( $percentage >= 70 ){ return 1; }
                elseif( $percentage >= 40 ){ return 2; }
                else{ return 3; }
            break;
        }
        return false;
    }
}