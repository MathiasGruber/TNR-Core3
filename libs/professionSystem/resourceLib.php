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

class resourceLib {
    
    // Class data
    //public $herbTypes = array();
    //public $oreTypes = array();
    //public $unchartedResources = 4000;
    
    // Set the ores
    //private function setOres() {
    //    if(empty($this->oreTypes)) {
    //        // Ore types
    //        $this->oreTypes = array(
    //            "copper" => array( "min" => 2, "max" => 5 ,"iid" => 77 ),
    //            "tin" => array( "min" => 1, "max" => 5 ,"iid" => 79 ),
    //            "iron" => array( "min" => 1, "max" => 4 ,"iid" => 81 ),
    //            "titanium" => array( "min" => 1, "max" => 3 ,"iid" => 82 ),
    //            "radiant" => array( "min" => 2, "max" => 2 ,"iid" => 83 )
    //        );
    //        
    //        $ore_ids = '';
    //        $i = 0;
    //        
    //        foreach($this->oreTypes as $key => $val) { // Create Ore Ids for DB Query (Ordering Matters)
    //            $ore_ids .= ($val === end($this->oreTypes)) ? ($val['iid']) : ($val['iid'].', ');
    //        }
    //        
    //        // Note: Ordering does matter based on oreTypes
    //        $dbOre = $GLOBALS['database']->fetch_data('SELECT items.profession_level 
    //            FROM `items` 
    //            WHERE items.id IN ('.$ore_ids.') LIMIT 5');
    //        
    //        // Loop through the ores & set extra information
    //        foreach( $this->oreTypes as $key => $val){
    //            $this->oreTypes[$key] = array_merge($this->oreTypes[$key], array('profession_level' => $dbOre[$i]['profession_level']));
    //            $i++;
    //        }
    //        
    //        // Ore finding chances
    //        $this->oreChances = array(
    //            "Shroud" =>     array( "copper" => 20, "tin" => 20, "iron" => 20, "titanium" => 20, "radiant" => 20 ),
    //            "Konoki" =>     array( "copper" => 15, "tin" => 40, "iron" => 15, "titanium" => 15, "radiant" => 15 ),
    //            "Silence" =>    array( "copper" => 15, "tin" => 15, "iron" => 40, "titanium" => 15, "radiant" => 15 ),
    //            "Samui" =>      array( "copper" => 15, "tin" => 15, "iron" => 15, "titanium" => 40, "radiant" => 15 ),
    //            "Shine" =>      array( "copper" => 40, "tin" => 15, "iron" => 15, "titanium" => 15, "radiant" => 15 ),
    //            "Syndicate" =>  array( "copper" => 35, "tin" => 35, "iron" => 20, "titanium" => 10, "radiant" => 0 ),
    //            "NorthWest" =>  array( "copper" => 25, "tin" => 25, "iron" => 0, "titanium" => 50, "radiant" => 0 ),
    //            "NorthEast" =>  array( "copper" => 25, "tin" => 25, "iron" => 50, "titanium" => 0, "radiant" => 0 ),
    //            "SouthWest" =>  array( "copper" => 35, "tin" => 35, "iron" => 0, "titanium" => 0, "radiant" => 30 ),
    //            "SouthEast" =>  array( "copper" => 50, "tin" => 25, "iron" => 25, "titanium" => 0, "radiant" => 0 )
    //        );     
    //    }
    //}
    
    // Get the types of herbs from the database
    //private function setHerbs() {
    //    if(empty($this->herbTypes)) {
    //        $this->herbTypes = $GLOBALS['database']->fetch_data('SELECT * FROM `items` 
    //            WHERE items.professionRestriction = "Herbalist" 
    //                AND items.herb_location IS NOT NULL 
    //                AND items.type IN("process", "material")');  
    //    }
    //}
    
    // Check database if resource exists at x.y
    public function checkIfResourceExist($array, $lock = false) {
        // Create query
        $query = "SELECT * FROM `resourceMap` WHERE `x.y` IN ('".implode( "' , '" , $array )."')";
        if($lock === true){ $query .= " FOR UPDATE"; }
        
        // Run & check query
        $dbLoc = $GLOBALS['database']->fetch_data($query);  
        return (($dbLoc !== "0 rows") ? $dbLoc : false);
    }
    
    // Pickup resource
    public function pickupResource($x, $y, $fields, $key) {
        // Check that the key exists
        if(isset($fields[$key])) {
            // Remove the entry and rearrange array
            unset($fields[$key]);
            $fields = array_values($fields);
            
            // See if the field should be deleted OR updated
            if(!isset($fields[0])) { $this->deleteResource($x, $y); }
            else { $this->updateResource($x, $y, $fields); }
        }
        else { throw new Exception("Could not find this resource in the field"); }
    }
    
    // Delete 1000 random resources from resource map
    public function deleteRandomResources() {
        $GLOBALS['database']->execute_query("DELETE FROM `resourceMap` ORDER BY RAND() LIMIT 1000");
    }
    
    // Delete resource
    private function deleteResource($x, $y) {
        $GLOBALS['database']->execute_query("DELETE FROM `resourceMap` WHERE `x.y` = '".$x.".".$y."' LIMIT 1");
    }
    
    // Update resource
    private function updateResource($x, $y, $field) {
        $GLOBALS['database']->execute_query("UPDATE `resourceMap` 
            SET `data` = '".base64_encode(serialize($field))."' 
            WHERE `x.y` = '".$x.".".$y."' LIMIT 1");
    }
    
    // Insert resource
    private function insertResource($x, $y, $field) {
        if($GLOBALS['database']->execute_query("INSERT INTO `resourceMap` (`x.y`, `data`) 
            VALUES ('".$x.".".$y."', '".base64_encode(serialize($field))."');") === false) {
            throw new Exception('Error inserting Resource!');
        }
    }

    //insert resources
    private function insertResources($resources)
    {
        $query = 'INSERT INTO `resourceMap` (`x.y`, `data` ) VALUES ';

        foreach($resources as $y => $row)
        {
            foreach($row as $x => $data)
            {
                if($query != 'INSERT INTO `resourceMap` (`x.y`, `data` ) VALUES ')
                    $query .=', ';

                $query .= "('".$x.".".$y."', '".base64_encode(serialize($data))."')";
            }
        }

        if($GLOBALS['database']->execute_query('DELETE FROM `resourceMap`') === false) {
            throw new Exception('Error inserting Resource!');
        }

        if($GLOBALS['database']->execute_query($query) === false) {
            throw new Exception('Error inserting Resource!');
        }
    }
    
    // Get a random ore based on location
    //private function getOre(){
    //    if(!empty($this->oreLocation)) {  
    //        // Store locally
    //        $chances = $this->oreChances[$this->oreLocation];
    //        $rand = random_int(1, 100);
    //        
    //        // Find the one
    //        $sum = 0;
    //        foreach($chances as $resource => $chance) {
    //            $sum += $chance;
    //            if($rand <= $sum) { return $resource; }
    //        }
    //    }
    //}
    
    // Function for getting a random herb of given location
    //private function getHerb($locations) {
    //    $validHerbs = array();
    //    foreach($this->herbTypes as $herb){           
    //        if(in_array($herb['herb_location'], $locations, true)) {
    //            if($herb['village_restriction'] === "ALL") { $validHerbs[] = $herb; } // Add global and whatever locations
    //        }
    //        
    //        if(in_array($herb['village_restriction'], $locations, true)) {
    //            if(random_int(1, 100) <= 5) { 
    //                return $herb; 
    //            } // 5% chance for special field
    //        }
    //        
    //        if($herb['village_restriction'] === "uncharted") {
    //            if(random_int(1,100) <= 15){ 
    //                return $herb; 
    //            } // 15% chance for uncharted
    //        }
    //    }
    //    
    //    // Return Valid Herb or False
    //    return ((!empty($validHerbs)) ? $validHerbs[random_int(0, count($validHerbs) - 1)] : false );
    //}
    
    // Function for creating a map (charted + uncharted) of resource fields
    public function reloadResourceMap()
    {    
        
        //$this->setHerbs(); // Set the herbs
        //$this->setOres(); // Set the ores
        //
        //// Create fields on the map
        //echo'get map information and get ocean tiles wont work';
        //$mapInformation = mapfunctions::getMapInformation();
        //$oceanTiles = mapfunctions::getOceanTiles();
        //
        //// Get village locations
        //$villageCoordinates = array();
        //foreach($mapInformation as $location) {
        //    if( $location["id"] == "village" ){
        //        foreach( $location['positions'] as $pos ){
        //            $villageCoordinates[] = $pos;
        //        }
        //    }
        //}
        //
        //// Place resources on map
        //foreach($mapInformation as $location) {
        //    // Get a random position of this location
        //    $randPosition = $location['positions'][random_int(0, count($location['positions']) - 1)];
        //    list($x, $y) = explode(".", $randPosition);
        //    
        //    // Check if already in database
        //    $dbLoc = $GLOBALS['database']->fetch_data("SELECT `id` 
        //        FROM `resourceMap` 
        //        WHERE `x.y` IN ('".implode("' , '", $location['positions'])."') LIMIT 1");  
        //    
        //    if( $dbLoc === "0 rows" && !in_array( $randPosition , $villageCoordinates ) ) {
        //        // Only work with some types
        //        switch(true) {
        //            case($location['id'] === "villageLand"): { 
        //                $this->oreLocation = $location['owner']; // For village starting territories
        //                $field = $this->createResourceField(array($location['owner'], "global"));
        //                
        //            } break;
        //            case(ctype_digit($location['id'])): {
        //                $this->setOreLocations($x, $y); // Set ore location for search in array
        //                $owner = isset($location['startOwner']) ? $location['startOwner'] : "";
        //                $field = $this->createResourceField(array("map", "global",$owner));                  
        //            } break;
        //        }
        //        
        //        // insert into database
        //        if(isset($field)) {
        //            $this->insertResource($x, $y, $field);
        //            unset($field);
        //        }
        //    }
        //}
        //
        //// First create 4000 fields for uncharted
        //$dbLoc = $GLOBALS['database']->fetch_data("SELECT COUNT(resourceMap.id) as `resourceCount` FROM `resourceMap` LIMIT 1");  
        //
        ////echo "Inserting resources: <br>".$left;
        //for($i = 1, $left = $this->unchartedResources - $dbLoc[0]['resourceCount']; $i <= $left; $i++) {
        //    // Start with 0 x/y
        //    $x = $y = 0;
        //    
        //    // Find random x & y coordinate
        //    while(($x > 0 && $x < 30 && $y > 0 && $y < 25) 
        //    || $this->checkIfResourceExist(array($x.".".$y))) {
        //        $x = random_int(-100, 100);
        //        $y = random_int(-100, 100);
        //    }
        //    
        //    // Set ore location
        //    $this->setOreLocations($x, $y);
        //    
        //    // Create resource field
        //    echo"ocean tile needs fixed";
        //    if( mapfunctions::isOceanTile($oceanTiles, $x, $y) ){
        //        $field = $this->createResourceField(array("ocean"));
        //    }
        //    else{
        //        $field = $this->createResourceField(array("global", "uncharted"));
        //    }
        //    
        //    // insert into database
        //    if(isset($field)){
        //        $this->insertResource($x, $y, $field);
        //        unset($field);
        //    }
        //}

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //try
        try
        {
            //lock
            $GLOBALS['database']->get_lock('resources','resources','__METHOD__');


            //start transaction
            $GLOBALS['database']->transaction_start();

            //getting map information
            $map_data_strip = $GLOBALS['database']->fetch_data('SELECT * FROM `map_data` WHERE `map` = \'seichi\'');
            $map_data = array();
            foreach($map_data_strip as $data)
            {
                if(!isset($map_data[$data['y']]))
                    $map_data[$data['y']] = array();

                $map_data[$data['y']][$data['x']] = explode(',',$data['data']);
            }

            set_time_limit(10);

            //getting rules
            $rules = $GLOBALS['database']->fetch_data('SELECT * FROM `resource_rules`');

            //formating rules
            foreach($rules as $rule_key => $rule)
            {
                $rules[$rule_key]['items'] = array();

                if($rule != '')foreach(explode('~',$rule['items']) as $item_key => $item)
                {
                    $rules[$rule_key]['items'][$item_key] = array();

                    if($item != '')foreach(explode(';',$item) as $field)
                    {
                        $pieces = explode('>',$field);
                        if(count($pieces) == 2)
                        {
                            $rules[$rule_key]['items'][$item_key][$pieces[0]] = $pieces[1];
                        }
                    }
                }
            }

            $eventMod = 1;

            $GLOBALS['globalevents'] = cachefunctions::getAllGlobalEvents();
            // Check for global event modifications
            if( $event = functions::getGlobalEvent("ModifyResourceSpawnRate")){
                if( isset( $event['data']) && is_numeric( $event['data']) ){
                    $eventMod = round($event['data'] / 100,2);
                }
            }

            $query_array = array();

            //loop over rules
            foreach($rules as $rule_key => $rule)
            {
                $applicable_tiles = array();
                if($rule['xs'] != '') {$rule['xs'] = explode(',',$rule['xs']); $rule['!xs'] = [];}
                if($rule['ys'] != '') {$rule['ys'] = explode(',',$rule['ys']); $rule['!ys'] = [];}
                if($rule['names'] != '') {$rule['names'] = explode(',',$rule['names']); $rule['!names'] = [];}
                if($rule['regions'] != '') {$rule['regions'] = explode(',',$rule['regions']); $rule['!regions'] = [];}
                if($rule['owners'] != '') {$rule['owners'] = explode(',',$rule['owners']); $rule['!owners'] = [];}

                foreach($rule as $temp_key => $temp_values)
                {
                    if(is_array($temp_values) && count($temp_values) > 0 && in_array($temp_key,['xs','ys','names','regions','owners']))
                        foreach($temp_values as $temp_sub_key => $temp_value)
                        {
                            if(substr($temp_value,0,1) == '!')
                            {
                                $rule['!'.$temp_key][] = ltrim($temp_value,'!');
                                unset($rule[$temp_key][$temp_sub_key]);
                            }
                        }
                }

                //loop over tiles collecting applicable tiles
                foreach($map_data as $y => $map_row)
                {
                    foreach($map_row as $x => $tile)
                    {
                        //check if this tile is applicable to the rule
                        if  (   
                                ($rule['xs'] == '' || (in_array($x, $rule['xs']) || (!in_array($x, $rule['!xs']) && count($rule['!xs']) > 0) ) ) && //check xs
                                ($rule['ys'] == '' || (in_array($y, $rule['ys']) || (!in_array($y, $rule['!ys']) && count($rule['!ys']) > 0) ) ) && //check ys
                                ($rule['names'] == '' || (in_array($tile[0], $rule['names']) || (!in_array($tile[0], $rule['!names']) && count($rule['!names']) > 0) ) ) && //check names
                                ($rule['regions'] == '' || (in_array($tile[1], $rule['regions']) || (!in_array($tile[1], $rule['!regions']) && count($rule['!regions']) > 0) ) ) && //check regions
                                ($rule['owners'] == '' || (isset($tiles[2]) && in_array($tiles[2], $rule['owners']) || (!in_array($tiles[2], $rule['!owners']) && count($rule['!owners']) > 0) ) ) &&    //check owners
                                (!in_array($tile[0],['Konoki','Silence','Shroud','Shine','Samui',"Gambler's Den","Bandit's Outpost","Poacher's Camp","Pirate's Hideout","Emiko's Meatery", "Stillwater's Chateau Barge", "Black Lodge", "Skyview Restaurant", "Shaded Rest Inn"])) //make sure this is not a village or ramen stand
                            )
                        {
                            //if this tile is applicable to the rule then add to array
                            $applicable_tiles[] = array('y'=>$y, 'x'=>$x);
                        }
                    }
                }

                //process each item for this rule
                foreach($rule['items'] as $item)
                {
                    //finding number to apply based on max, min, and a normalization curve
                    $max = $item['maxSpawns'] + 1;
                    $min = $item['minSpawns'];
                    
                    $mu = ($min + $max)/2;
                    $sigma = ($max - $min - 1)/5;
                    $array = array();
                    $number = 0;
                    $quanity = 0;
                    for($x = $min; $x < $max; $x++)
                    {
                    	$number += round((exp(-0.5 * ($x - $mu) * ($x - $mu) / ($sigma*$sigma)) / ($sigma * sqrt(2.0 * M_PI)))*10000);
                    	$array[$number] = $x;
                    }
                    
                    $rand = random_int(1,$number);
                    
                    foreach($array as $chance => $value)
                    {
                    	if(($chance) >= $rand)
                        {
                    		$quantity = $value;
                            break;
                        }
                    }

                    //building the array of locations for this item
                    $application_tiles = array();
                    for($i = 0; $i < $quantity * $eventMod; $i++)
                    {
                        if(count($applicable_tiles) >= 1)
                            $application_tiles[] = $applicable_tiles[random_int(0,count($applicable_tiles) - 1)];
                    }

                    //adding to query array
                    foreach($application_tiles as $x_y)
                    {
                        if(!isset($query_array[$x_y['y']]))
                            $query_array[$x_y['y']] = array();

                        if(!isset($query_array[$x_y['y']][$x_y['x']]))
                            $query_array[$x_y['y']][$x_y['x']] = array();

                        $query_array[$x_y['y']][$x_y['x']][] = $item;
                    }
                }
            }

            //sending resources to db
            $this->insertResources($query_array);

            //comit
            $GLOBALS['database']->transaction_commit();

            //release
            $GLOBALS['database']->release_lock('resources','resources',__METHOD__);
        }
        //catch
        catch(exception $e)
        {
            //roll back
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );

            //release
            $GLOBALS['database']->release_lock('resources','resources',__METHOD__);
        }
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    }
    
    // Function for setting ore locations
    //private function setOreLocations($x, $y){
    //    if($x <= 0 && $y <= 0) { $this->oreLocation = "NorthWest"; }
    //    elseif($x < 0 && $y > 0) { $this->oreLocation = "SouthWest"; }
    //    elseif($x > 0 && $y < 0) { $this->oreLocation = "NorthEast"; }
    //    elseif($x > 0 && $y > 0) { $this->oreLocation = "SouthEast"; }
    //}

    // Function for generating a resource field
    //private function createResourceField($locations) {        
    //    $field = array(); // Field
    //    
    //    // Only put ore & animal field if not in ocean
    //    if(!in_array("ocean", $locations) ){
    //        // Ore
    //        $oreType = $this->getOre(); 
    //        $field[] = array(
    //            "type" => "ore", 
    //            "subType" => $oreType, 
    //            "iid" => $this->oreTypes[$oreType]['iid'],                 
    //            "level" => $this->oreTypes[$oreType]['profession_level'], 
    //            "min" => $this->oreTypes[$oreType]['min'],
    //            "max" => $this->oreTypes[$oreType]['max']
    //        );
    //
    //        // Hunting field
            
            //generate a number to control what level of herd to spawn and wether or not it is a flock or not.
    //        $type_chance = random_int(0, 100);
    //        $spawn_chance = random_int(1,100);
    //        //if the number generated is a 0 make this a flock
    //        if($spawn_chance <= 45)
    //        {
    //            if($type_chance == 0)
    //            {
    //                $field[] = array(
    //                    "type" => "hunter",
    //                    "subType" => "pristine flock",
    //                    "iid" => 84,
    //                    "level" => 0,
    //                    "flock" => true);
    //            }
    //
    //            //otherwise if the number is 30 or less make this a level 1 herd
    //            else if($type_chance <=30 )
    //            {
    //                $field[] = array(
    //                    "type" => "hunter",
    //                    "subType" => "level I animal herd",
    //                    "iid" => 84,
    //                    "level" => 1,
    //                    "flock" => false);
    //            }
    //
    //            //otherwise if the number is 55 or less make this a level 2 herd
    //            else if($type_chance <=55 )
    //            {
    //                $field[] = array(
    //                    "type" => "hunter",
    //                    "subType" => "level II animal herd",
    //                    "iid" => 84,
    //                    "level" => 85,
    //                    "flock" => false);
    //            }
    //
    //            //otherwise if the number is 80 or less make this a level 3 herd
    //            else if($type_chance <=80 )
    //            {
    //                $field[] = array(
    //                    "type" => "hunter",
    //                    "subType" => "level III animal herd",
    //                    "iid" => 84,
    //                    "level" => 235,
    //                    "flock" => false);
    //            }
    //
    //            //otherwise make this a level 4 herd. (range 81 to 100)
    //            else
    //            {
    //                $field[] = array(
    //                    "type" => "hunter",
    //                    "subType" => "level IV animal herd",
    //                    "iid" => 84,
    //                    "level" => 385,
    //                    "flock" => false);
    //            }
    //        }
    //    }
    //        
    //    // Herb field
    //    for($i = 0, $size = random_int(1, 2); $i < $size; $i++) {
    //        $randHerb = $this->getHerb($locations);
    //        $field[] = array(
    //            "type" => "herb", 
    //            "subType" => $randHerb['name'], 
    //            "iid" => $randHerb['id'], 
    //            "level" => $randHerb['profession_level']
    //        );
    //    }
    //    
    //     
    //    return $field;
    //} 
}