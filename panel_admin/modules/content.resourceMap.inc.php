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

// Get the resource library
require('../libs/professionSystem/resourceLib.php');
require('../global_libs/Site/map.inc.php');

// Extend the resource library
class resourceMap extends resourceLib{

    // Constructor
    public function __construct() {
        
        // Handle clear/insert actions
        $this->handleActions();

        // Get all the reosurces
        $allResources = cachefunctions::getAllResources();
        
        // Create an image -125 to 125 by -100 to 100 pixels at a 4x scale
        $gd = imagecreatetruecolor(1000, 800);
        
        // The color we use for drawing
        $red    = imagecolorallocate($gd, 255, 0, 0); 
        $green    = imagecolorallocate($gd, 0, 120, 0); 
        $yellow  = imagecolorallocate($gd, 255, 255, 0);

        $grey = imagecolorallocate($gd, 64,64,64);

        // Color map
        //imagefilledrectangle($gd, 200, 200, 260, 250, $green);
        imagefilledrectangle($gd, 0, 399, 1000, 401, $grey);
        imagefilledrectangle($gd, 499, 0, 501, 800, $grey);
        
        // Total number of fields
        $fields = 200*200;
        
        // Names of Fields
        $typeNames = array(  
            "oreNames" => array(),
            "herbNames" => array(),
            "herdNames" => array()
        );
        
        // List of fields belonging to certain resource name
        $fieldLists = array();
        
        // Loop through resources and color time
        if( $allResources !== "0 rows" ){
            
            foreach( $allResources as $resource ){
                
                // Get position
                list($x,$y) = explode(".", $resource['x.y']);
                
                // Get data
                $data = unserialize(base64_decode($resource['data']));
                if( !empty($data) ){
                    foreach( $data as $entry ){
                        
                        // Assign option names
                        switch( $entry['type'] ){
                            case "ore": 
                                if( !in_array( $entry['subType'], $typeNames['oreNames'] ) ){
                                    $typeNames['oreNames'][] = $entry['subType'];
                                }
                            break;
                            case "herb": 
                                if( !in_array( $entry['subType'], $typeNames['herbNames'] ) ){
                                    $typeNames['herbNames'][] = $entry['subType'];
                                }
                            break;
                            case "hunter": 
                                if( !in_array( $entry['subType'], $typeNames['herdNames'] ) ){
                                    $typeNames['herdNames'][] = $entry['subType'];
                                }
                            break;
                        }
                        
                        // Send to list
                        if( !isset($fieldLists[ $entry['subType'] ]) ){
                            $fieldLists[ $entry['subType'] ] = array();
                        }
                        $fieldLists[ $entry['subType'] ][] = $resource['x.y'];
                    }
                }
                
                // Do drawing
                imagefilledrectangle($gd, $x*4+501, $y * -4 + 401, $x * 4 + 503, $y * -4 + 403, $red);
            }
        }
        
        // Set top options
        $topOptions = array();
        foreach( $typeNames as $typeList ){
            foreach( $typeList as $option ){
                $topOptions[] = array("name" => ucfirst($option), "href" =>"?id=".$_GET["id"]."&view=".$option);
            }
        }
        
        // Set table entries
        $tableEntries = array();
        if( isset($_GET['view']) && isset($fieldLists[ $_GET['view'] ]) ){
            foreach( $fieldLists[ $_GET['view'] ] as $entry ){
                
                // Add to table entries
                $tableEntries[] = array( 
                    "name" => $_GET['view'],
                    "location" => $entry
                );
                
                // Color map
                list($x,$y) = explode(".", $entry);
                imagefilledrectangle($gd, $x * 4 + 501, $y * -4 + 401, $x * 4 + 503, $y * -4 + 403, $yellow);
            }
        }
        
        // Create Table of Options
        tableParser::show_list(
            'resources',
            '<pre> Resource Lists'.( (isset($_GET['view']) && $_GET['view'] != '') ? '    -    ;viewing: <b style="color:lime">'.$_GET['view'].'</b>    -    count: <b style="color:lime">'.count($tableEntries).'</b> </pre>' : ' </pre>' ), 
            $tableEntries,
            array(
                'name' => "Resource Name",
                'location' => "Location"
            ), 
            false,
            false, // Send directly to contentLoad
            false,
            $topOptions
        ); 
        
        ob_start();
        imagejpeg($gd, null, 100);
        $rawImageBytes = ob_get_clean();
        $image = "<img src='data:image/jpeg;base64," . base64_encode( $rawImageBytes ) . "' />";
        $GLOBALS['template']->assign('resourceImage', $image);
        imagedestroy($gd);
        
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/resourceMap/main.tpl');
        
    }
    
    // Function for handling insert & clear actions
    private function handleActions(){
        
        // Check action isset
        if( isset($_GET['act']) ){
            
            // Clear cache
            cachefunctions::deleteAllResources();
            
            // Clear action
            if( $_GET['act'] == "clear" ){
                
                // Dump table
                $GLOBALS['database']->execute_query("DELETE FROM `resourceMap`");
            
            }
            elseif(  $_GET['act'] == "create" ){
                
                // Create new resource map
                $this->reloadResourceMap();
            }
        }
    }
}

new resourceMap();