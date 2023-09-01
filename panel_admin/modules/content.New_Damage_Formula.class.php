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

// Go above memory limit
ini_set('memory_limit', '-1');

// Test class
class battle_test {

    // Constructor
    public function __construct() {
        
        // Try it all
        try{
                        
            // Get formulas
            $this->setBattleFormula();
            
            // Test damage if requested
            if( isset($_POST['CalculationRun']) ){
                $this->run_tests();
            }

            // Load the template wrapper
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/DAM_calculator/newFormula.tpl');
            
        } catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'New Battle Formula System', 'id=' . $_GET['id'] );
        }
    }

    // Test the different damage formulaes
    private function run_tests() {
        
        
        // Get the users & their data
        $this->rankID = (isset($_POST['userRank']) && is_numeric($_POST['userRank'])) ? $_POST['userRank'] : 1;
        $this->getUsers(true );
        
        // Amount of damage tests to run
        $this->tests = 1;
        if( isset($_POST['accuracy']) && $_POST['accuracy'] > 0){
            $this->tests = ($_POST['accuracy'] > 2000) ? 2000 : $_POST['accuracy'];
            $this->tests = ($_POST['accuracy'] < 1) ? 1 : $this->tests;
        }
        $GLOBALS['template']->append('calcDebug', "Running with accuracy of ".$this->tests." in all tests" );
        
        // Optimal standard action only battle
        switch( $_POST['analysis'] ){
            case "standardActions": $this->standardActionBattle(); break;
            case "weaponActions": $this->weaponActionBattle(); break;
            case "jutsuActions": $this->jutsuActionBattle(); break;
            case "rsfAnalysis": $this->RSFAnalysis(); break;
        }
        
        // Run optimized battles
        // $this->optimizedBattles();
        
        
    }
    
    // Get damage percentage
    private function getDamagePerc( $dam, $health ){
        $damagePerc = $dam / $health;
        if( $damagePerc > 1 ){
            $damagePerc = 1;
        }
        return $damagePerc;
    }
    
    // Run optimized battle tests
    private function RSFAnalysis(){
        
        // Data to be plotted
        $plotData = array();
        
        // Pre-declate vars
        $this->user = $this->opponent = array();
 
        // Cal RSFs
        $weakNumber = 0;
        $weakSummedRSF = 0;
        $weakSummedSquareRSF = 0;
        $strongNumber = 0;
        $strongSummedRSF = 0;
        $strongSummedSquareRSF = 0;
        
        // Go through accuracy amount of battles
        for( $i=0; $i < $this->tests; $i++ ){

            // Get random user & opponent
            $this->setUsersForBattle();
            
            // Calc RSF
            $rsf = $this->user['strengthFactor'] / $this->opponent['strengthFactor'];
            
            // Weak or strong
            if( $rsf < 1){
                
                // Add damage percentage of max_health                        
                $this->addHistogramData( "rsf_weak", "Low Relative Strength Factor", $rsf );

                // Add
                $weakSummedRSF += $rsf;
                $weakSummedSquareRSF += $rsf*$rsf;
                $weakNumber += 1;
                
            }
            else{
                
                // Add damage percentage of max_health                        
                $this->addHistogramData( "rsf_strong", "Strong Relative Strength Factor", $rsf );

                // Add
                $strongSummedRSF += $rsf;
                $strongSummedSquareRSF += $rsf*$rsf;
                $strongNumber += 1;
                
            }
        }            
        
        // Plot of low RSF
        $weakAvgRSF = round($weakSummedRSF / $weakNumber, 2);
        $weakDeviationRSF = round(sqrt( $weakSummedSquareRSF/$weakNumber - $weakAvgRSF*$weakAvgRSF ), 2);
        $weakRsfDetail = "Avg RSF: ".$weakAvgRSF." +/- ".$weakDeviationRSF;

        // Add plot of damage distribution
        $GLOBALS['template']->append('calcDebug', $this->getGoogleChart().$this->getDistributionPlotHtml( 
            "rsf_weak","rsf_weak","RSF","Counts","Low Relative strength factor - ".$weakRsfDetail
        ) );
        
        // Plot of high RSF
        $strongAvgRSF = round($strongSummedRSF / $strongNumber, 2);
        $strongDeviationRSF = round(sqrt( $strongSummedSquareRSF/$strongNumber - $strongAvgRSF*$strongAvgRSF ), 2);
        $strongRsfDetail = "Avg RSF: ".$strongAvgRSF." +/- ".$strongDeviationRSF;

        // Add plot of damage distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( 
            "rsf_strong","rsf_strong","RSF","Counts","High Relative strength factor - ".$strongRsfDetail
        ) );
        
    }
    
    // Run optimized battle tests
    private function jutsuActionBattle(){
        
        // Data to be plotted
        $plotData = array();
        
        // Pre-declate vars
        $this->user = $this->opponent = array();
        $rounds = 0;
        
        // Make two sets, one for min damage and one for max
        foreach( array("Highest Damage", "Random Jutsu", "Rank-Matching Jutsu") as $set ){
            
            // Go through accuracy amount of battles
            for( $i=0; $i < $this->tests; $i++ ){

                // Get random user & opponent
                $this->setUsersForBattle();

                // Reset rounds
                $rounds = 0;

                // Run the battle
                while( $this->user['cur_health'] > 0 && $this->opponent['cur_health'] > 0 && $rounds < 50 ){

                    // Actions for user & opponent
                    foreach( array("user","opponent") as $side ){

                        // Get the other side
                        $otherSide = ($side == "user") ? "opponent" : "user";
                        
                        // Get the damage
                        $damage = $this->getJutsuDamage($side,$otherSide, $set);

                        // Reduce health of opponent
                        $this->{$otherSide}['cur_health'] -= $damage;
                        
                        // Add damage percentage of max_health                        
                        $this->addHistogramData( "jutsuActionDamage", $set, $this->getDamagePerc($damage, $this->{$otherSide}['max_health']) );
                    }    

                    // Increase rounds
                    $rounds++;
                }

                $this->addRoundData("jutsuActionRounds", $set, $rounds);
            }            
        }
        
        // Add plot of rounds
        $GLOBALS['template']->append('calcDebug', $this->getRoundPlotHtml( "jutsuActionRounds" ) );
        
        // Add plot of damage distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( "jutsuActionDamage" ) );
        
        // Add plot of level distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( 
                "jutsuActionLvl","lvl","Lvl","Counts","User Action Levels"
        ) );
        
        // Add plot of power distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( 
                "jutsuActionPower","power","Power Level","Counts","User Action Power"
        ) );
        
    }
    
    // Function for getting weapon damage
    // Sorting: "Random Weapon", "Random Item", "Highest Damage"
    private function getJutsuDamage( $side , $otherSide, $sorting = "Highest Damage"){
        
        // Define damage array
        $damages = array();
        
        // Get taijutsu damage
        $damages[] = calc::calc_double_damage(
            array(
                "user_data" => $this->{$side}, 
                "target_data" => $this->{$otherSide}, 
                "type1" => "nin", 
                "type2" => "gen", 
                "stat1" => "willpower", 
                "stat2" => "intelligence", 
                "power" => 1000,
                "scalePower" => 0.1
            )
        );
                
        // Go through the items
        if( isset($this->{$side}['jutsus']) && !empty($this->{$side}['jutsus']) && $this->{$side}['jutsus'] !== "0 rows" ){
            foreach( $this->{$side}['jutsus'] as $jutsu ){
                
                // Check both use1 and use2
                foreach( array("effect_1","effect_2","effect_3","effect_4") as $column ){
                    if( preg_match("/^DAM.+$/", $jutsu[$column])  ){
                        
                        // Check if it should be included
                        if( $jutsu['required_rank'] !== "Rank-Matching Jutsu" || 
                            $this->{$side}['rank_id'] == $jutsu['required_rank']){
                            
                            // Get modifiers 
                            $modifiers = explode(":", $jutsu[$column]);

                            // Get attack type
                            if ($jutsu['attack_type'] == 'taijutsu') {
                                $this->att_type = 'tai';
                            } elseif ($jutsu['attack_type'] == 'ninjutsu') {
                                $this->att_type = 'nin';
                            } elseif ($jutsu['attack_type'] == 'genjutsu') {
                                $this->att_type = 'gen';
                            } elseif ($jutsu['attack_type'] == 'weapon') {
                                $this->att_type = 'weap';
                            } elseif ($jutsu['attack_type'] == 'highest') {
                                $specialData = $this->get_user_specialization( $side );
                                if( $specialData !== false ){
                                    list( $ttype , $this->att_type ) = array_values($specialData);
                                }
                                else{
                                    $this->att_type = "weap";
                                } 
                            }

                            // Determine if weapon or item
                            $power = $modifiers[2] + ($jutsu['level'] * $modifiers[3]);
                            if ($modifiers[1] == 'STAT') {
                                $damages[] = $power;
                            } elseif ($modifiers[1] == 'PERC') {
                                $perc = $this->{$otherSide}['max_health'] / 100;
                                $damages[] = $perc * $power;
                            } elseif ($modifiers[1] == 'CALC') {
                                $damages[] = calc::calc_damage( 
                                    array(
                                        "user_data" => $this->{$side}, 
                                        "target_data" => $this->{$otherSide}, 
                                        "type" => $this->att_type, 
                                        "stat1" => $modifiers[4], 
                                        "stat2" => $modifiers[5], 
                                        "power" => $power,
                                        "jutsu" => $jutsu
                                    )
                                );
                            }
                            
                            // Add power value to array for plotting
                            $this->addHistogramData( "jutsuActionLvl", $sorting, $jutsu['level'] );
                            $this->addHistogramData( "jutsuActionPower", $sorting, $power );
                        }
                    }
                }
            }
        }
        
        // Return damage
        $damage = 0;
        switch( $sorting ){
            case "Highest Damage": 
                $damage = max( $damages );
                break;
            case "Random Jutsu":                 
            case "Rank-Matching Jutsu": 
                shuffle($damages);
                $damage = $damages[0];
                break;
            default: $GLOBALS['template']->append('calcDebug', "No specific set selected" ); break;
        }
        
        // Return the damage
        return $damage;
    }
    
    
    // Run optimized battle tests
    private function standardActionBattle(){
        
        // Data to be plotted
        $plotData = array();
        
        // Pre-declate vars
        $this->user = $this->opponent = array();
        $rounds = 0;
        
        // Make two sets, one for min damage and one for max
        foreach( array("Highest Damage", "Stamina Only", "Chakra Only") as $set ){
            
            // Go through accuracy amount of battles
            for( $i=0; $i < $this->tests; $i++ ){

                // Get random user & opponent
                $this->setUsersForBattle();

                // Reset rounds
                $rounds = 0;

                // Run the battle
                while( $this->user['cur_health'] > 0 && $this->opponent['cur_health'] > 0 && $rounds < 50 ){

                    // Actions for user & opponent
                    foreach( array("user","opponent") as $side ){

                        // Get the other side
                        $otherSide = ($side == "user") ? "opponent" : "user";

                        // Optimal damage: chakra or stamina
                        $damage1 = calc::calc_double_damage(
                            array(
                                "user_data" => $this->{$side}, 
                                "target_data" => $this->{$otherSide}, 
                                "type1" => "nin", 
                                "type2" => "gen", 
                                "stat1" => "willpower", 
                                "stat2" => "intelligence", 
                                "power" => 1000,
                                "scalePower" => 0.1
                            )
                        );
                        $damage2 = calc::calc_double_damage(
                            array(
                                "user_data" => $this->{$side}, 
                                "target_data" => $this->{$otherSide}, 
                                "type1" => "tai", 
                                "type2" => "weap", 
                                "stat1" => "strength", 
                                "stat2" => "speed", 
                                "power" => 1000,
                                "scalePower" => 0.1
                            )
                        );
                              
                        // Get min and max
                        $maxDamage = ($damage1 > $damage2) ? $damage1 : $damage2;

                        // Reduce health of opponent
                        switch( $set ){
                            case "Highest Damage": 
                                $this->{$otherSide}['cur_health'] -= $maxDamage; 
                                $this->addHistogramData( "standardActionDamage", $set, $this->getDamagePerc($maxDamage, $this->{$otherSide}['max_health']) );
                                break;
                            case "Stamina Only": 
                                $this->{$otherSide}['cur_health'] -= $damage2; 
                                $this->addHistogramData( "standardActionDamage", $set, $this->getDamagePerc($damage2, $this->{$otherSide}['max_health']) );
                                break;
                            case "Chakra Only": 
                                $this->{$otherSide}['cur_health'] -= $damage1; 
                                $this->addHistogramData( "standardActionDamage", $set, $this->getDamagePerc($damage1, $this->{$otherSide}['max_health']) );
                                break;
                        }
                    }    

                    // Increase rounds
                    $rounds++;
                }

                $this->addRoundData("standardActionRounds", $set, $rounds);
                $this->addHistogramData( "userArmorValues", $set, $this->{$otherSide}['armor'] );
            }            
        }
        
        // Add plot
        $GLOBALS['template']->append('calcDebug', $this->getRoundPlotHtml( "standardActionRounds" ) );
        
        // Add plot of damage distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( "standardActionDamage" ) );
        
        // Add plot of armor values
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( 
                "userArmorValues","armor","Armor","Counts","User Armor Levels"
        ) );
        
    }
    
    // Run optimized battle tests
    private function weaponActionBattle(){
        
        // Data to be plotted
        $plotData = array();
        
        // Pre-declate vars
        $this->user = $this->opponent = array();
        $rounds = 0;
        
        // Make two sets, one for min damage and one for max
        foreach( array("Highest Damage", "Random Item", "Random Weapon") as $set ){
            
            // Go through accuracy amount of battles
            for( $i=0; $i < $this->tests; $i++ ){

                // Get random user & opponent
                $this->setUsersForBattle();

                // Reset rounds
                $rounds = 0;

                // Run the battle
                while( $this->user['cur_health'] > 0 && $this->opponent['cur_health'] > 0 && $rounds < 50 ){

                    // Actions for user & opponent
                    foreach( array("user","opponent") as $side ){

                        // Get the other side
                        $otherSide = ($side == "user") ? "opponent" : "user";
                        
                        // Get the damage
                        $damage = $this->getWeaponDamage($side,$otherSide, $set);
                        
                        // Reduce health of opponent
                        $this->{$otherSide}['cur_health'] -= $damage;
                        
                        // Add damage for plotting
                        $this->addHistogramData( "weaponActionDamage", $set, $this->getDamagePerc($damage, $this->{$otherSide}['max_health']) );
                    }    

                    // Increase rounds
                    $rounds++;
                }

                $this->addRoundData("weaponActionRounds", $set, $rounds);
            }            
        }
        
        // Add plot
        $GLOBALS['template']->append('calcDebug', $this->getRoundPlotHtml( "weaponActionRounds" ) );
        
        // Add plot of damage distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( "weaponActionDamage" ) );
        
        // Add plot of power distribution
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( 
                "ItemActionPower","powerItem","Item Power Level","Counts","User Item Action Power"
        ) );
        
        $GLOBALS['template']->append('calcDebug', $this->getDistributionPlotHtml( 
                "WeaponActionPower","powerWeapon","Weapon Power Level","Counts","User Weapon Action Power"
        ) );
        
    }
    
    // Function for getting weapon damage
    // Sorting: "Random Weapon", "Random Item", "Highest Damage"
    private function getWeaponDamage( $side , $otherSide, $sorting = "Highest Damage"){
        
        // Define damage array
        $damages = array();
        
        // Get taijutsu damage
        $damages[] = calc::calc_double_damage(
            array(
                "user_data" => $this->{$side}, 
                "target_data" => $this->{$otherSide}, 
                "type1" => "tai", 
                "type2" => "weap", 
                "stat1" => "strength", 
                "stat2" => "speed", 
                "power" => 1000,
                "scalePower" => 0.1
            )
        );
                
        // Go through the items
        if( isset($this->{$side}['items']) && !empty($this->{$side}['items']) && $this->{$side}['items'] !== "0 rows" ){
            foreach( $this->{$side}['items'] as $item ){
                
                // Check both use1 and use2
                foreach( array("use","use2") as $column ){
                    if( stristr($item[$column], "DMG") ){
                        
                        // Get modifiers 
                        $modifiers = explode(":", $item[$column]);
                        
                        // Determine if weapon or item
                        switch( $item['type'] ){
                            case "item": 
                                
                                // ITEMS //
                                ///////////
                                if(in_array($sorting, array("Highest Damage","Random Item")) ){
                                    
                                    // Determine type
                                    if ($modifiers[1] == 'PERC') {
                                        if ($modifiers[3] == 'STR') {
                                            $perc = $item['strength'];
                                        } else {
                                            $perc = $modifiers[3];
                                        }
                                        $damages[] = (($this->{$otherSide}['max_health'] / 100) * $perc);

                                    } elseif ($modifiers[1] == 'STAT') {
                                        if ($modifiers[3] == 'STR') {
                                            $damages[] = $item['strength'];
                                        } else {
                                            $damages[] = $modifiers[3];
                                        }

                                    } elseif ($modifiers[1] == 'CALC') {

                                        // Damage calculation. First get power
                                        if ($modifiers[3] == 'STR') {
                                            $power = $item['strength'];
                                        } else {
                                            $power = $modifiers[3];
                                        }

                                        list( $type, $stat1, $stat2 ) = $this->translate_tag_type( $modifiers[2] );

                                        $damages[] = calc::calc_damage( 
                                            array(
                                                "user_data" => $this->{$side}, 
                                                "target_data" => $this->{$otherSide}, 
                                                "type" => $type, 
                                                "stat1" => $stat1, 
                                                "stat2" => $stat2, 
                                                "power" => $power,
                                                "scalePower" => 1
                                            )
                                        );
                                        
                                        // Item powers
                                        $this->addHistogramData( "ItemActionPower", $sorting, $power );
                                    }
                                }
                                

                            break;
                            case "weapon": 
                                
                                
                                // WEAPONS //
                                /////////////
                                if(in_array($sorting, array("Highest Damage","Random Weapon")) ){
                                    
                                    // Determine power modifier from tag
                                    $power = ($modifiers[2] === 'STR') ? $item['strength'] : $modifiers[2];

                                    // Get the types, $modifiers[2] == T|B|G|W
                                    list($off_1, $stat1, $stat2) = $this->translate_tag_type($modifiers[1]);

                                    // Use calculate for weapons
                                    $damages[] = calc::calc_double_damage(array(
                                        "user_data" => $this->{$side}, 
                                        "target_data" => $this->{$otherSide},  
                                        "type1" => $off_1, 
                                        "type2" => 'weap', 
                                        "stat1" => 'strength', 
                                        "stat2" => 'speed', 
                                        "power" => $power,
                                        "scalePower" => 1
                                    ));
                                    
                                    // Item powers
                                    $this->addHistogramData( "WeaponActionPower", $sorting, $power );
                                }
                                
                                
                           break;
                        }
                        
                    }
                }
            }
        }
        
        // Return damage
        $damage = 0;
        switch( $sorting ){
            case "Highest Damage": 
                $damage = max( $damages );
                break;
            case "Random Weapon":                 
            case "Random Item": 
                shuffle($damages);
                $damage = $damages[0];
                break;
            default: $GLOBALS['template']->append('calcDebug', "No specific set selected" ); break;
        }
        
        // Return the damage
        return $damage;
    }
    
    // Set users for battle
    private function setUsersForBattle(){
        
        // Check if user and opponent should be selected randomly
        if( !isset($_POST['userArrange']) || $_POST['userArrange'] == "random"){
            
            $this->user = $this->users[ random_int(0,count($this->users)-1 ) ];
            $this->opponent = $this->users[ random_int(0,count($this->users)-1 ) ];
        }
        elseif( $_POST['userArrange'] == "bestVsworst" ){
            
            // Count users
            $countUsers = count($this->users);
            if( $countUsers < 100 ){
                throw new Exception("At least 100 users must be in the selection to run the worst vs. best scenario. Currently there is only: ".$countUsers." users.");
            }
            
            // Selection margin
            $select = floor(0.1*$countUsers);
            
            // Select users
            $this->user = $this->users[ random_int(0,$select-1 ) ];
            $this->opponent = $this->users[ random_int($countUsers-$select, $countUsers-1) ];
            
        }
    }
    
    // Function for adding data to be plotted
    // Types: roundData
    private function addRoundData( $name, $set, $rounds ){
        
        // Check if var exists
        if( !isset( $this->{$name} ) ){
            $this->{$name} = array( $set => array() );
        }
        
        // Check if set exists
        if( !isset( $this->{$name}[ $set ] ) ){
            $this->{$name}[$set] = array(0=>0);
        }
        
        // Initialize rounds to each set
        foreach( $this->{$name} as $setI => $data ){           
            $countInDataset = count($data);
            if( $rounds >= $countInDataset ){
                for( $i = $countInDataset; $i <= $rounds; $i++ ){
                    $this->{$name}[$setI][ $i ] = 0;
                }
            }
        }
        
        // Add this round to data
        $this->{$name}[$set][ $rounds ]++;
        
        // Get the max number of rounds for any set
        $maxRounds = 0;
        foreach( $this->{$name} as $setI => $data ){
            $countInDataset = count($data);
            if( $countInDataset > $maxRounds ){
                $maxRounds = $countInDataset;
            }
        }
        
        // If any set is missing rounds, add them
        foreach( $this->{$name} as $setI => $data ){           
            $countInDataset = count($data);
            if( $maxRounds > $countInDataset ){
                for( $i = $countInDataset; $i < $maxRounds; $i++ ){
                    $this->{$name}[$setI][ $i ] = 0;
                }
            }
        }
    }
    
    // Function for adding data to be plotted
    // Types: roundData
    private function addHistogramData( $name, $set, $damage ){
        
        // Check if var exists
        if( !isset( $this->{$name} ) ){
            $this->{$name} = array( $set => array() );
        }
        
        // Check if set exists
        if( !isset( $this->{$name}[ $set ] ) ){
            $this->{$name}[$set] = array();
        }
        
        // Add this damage to data
        $this->{$name}[$set][] = $damage;
        
    }
    
    // Get damage distribution plot html
    private function getDistributionPlotHtml( 
        $dataName , 
        $key = "dam", 
        $hAxis = "%", 
        $vAxis = "Counts",
        $title = "Damage in % of opponent max health."
    ){
        if( isset( $this->{$dataName} ) ){
            
            // Set the data columns
            $dataString = "['".implode("','", array_keys($this->{$dataName}))."']";
            
            // Order the data properly
            $foundData = true;
            $i = 0;
            while( $foundData == true ){
                
                // Go through each set
                $roundValues = array();
                $foundData = false;
                foreach( $this->{$dataName} as $set => $data ){
                    if( isset($data[$i]) ){
                        $roundValues[] = $data[$i];
                        $foundData = true;
                    }
                    else{
                        $roundValues[] = "null";
                    }
                }
                
                // Start data string
                $dataString .= ", [ ".implode(",", $roundValues )." ]";
                
                // Increment
                $i++;                
            }

            return "<script type='text/javascript'>
                      google.setOnLoadCallback(draw".$key."Chart);
                      function draw".$key."Chart() {
                        var ".$key."Data = google.visualization.arrayToDataTable([
                            ".$dataString."
                        ]);

                        var ".$key."Options = {
                          title: '".$title."',
                          legend: { position: 'bottom' },
                          hAxis: {title: '".$hAxis."'},
                          vAxis: {title: '".$vAxis."'},
                          histogram: { bucketSize: 0.005 },
                          bar: {groupWidth: '70%' }
                        };

                        var ".$key."Chart = new google.visualization.Histogram(document.getElementById('".$key."ChartDiv'));
                        ".$key."Chart.draw(".$key."Data, ".$key."Options);
                      }
                    </script>
                    <div id='".$key."ChartDiv' style='width: 1400px; height: 400px;'></div>";
        }
        else{
            return "No Data";
        }
    }
    
    // Load google chart code
    private function getGoogleChart(  ){
        return "<script type='text/javascript'>
          google.load('visualization', '1.1', {packages:['bar','corechart']});
          google.setOnLoadCallback(drawChart);</script>";
    }
    
    // Get plot html
    private function getRoundPlotHtml( $dataName ){
        
        if( isset( $this->{$dataName} ) ){
            
            // Set the data columns
            $dataString = "['Rounds','".implode("','", array_keys($this->{$dataName}))."']";
            
            // Order the data properly
            $orderedData = array();
            foreach( $this->{$dataName} as $set => $data ){
                foreach( $data as $round => $value ){
                    if( !isset($orderedData[$round]) ){
                        $orderedData[$round] = array();
                    }
                    $orderedData[$round][] = $value;
                }
            }
            
            // Add ordered data
            foreach($orderedData as $round => $setsData ){
                $string = "".implode(",", $setsData)."";
                // $string = str_replace("0","",$string);
                $dataString .= "\n,['".$round."',".$string."]";
            }


            return "<script type='text/javascript'>
          google.load('visualization', '1.1', {packages:['bar','corechart']});
          google.setOnLoadCallback(drawChart);
          
          function drawChart() {
            var data = google.visualization.arrayToDataTable([
              ".$dataString."
            ]);

            var options = {
              title: 'Battle Rounds in ".$this->tests." battles between random users with rankID: ".$this->rankID.". Fights ordered by: ".$_POST['userArrange']."',
              chart: {
                subtitle: '".$dataName."',
              },
              width: 1400,
              bars: 'vertical',
              legend: { position: 'bottom' }
            };

            var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

            chart.draw(data, google.charts.Bar.convertOptions(options) );
          }
        </script>
        <div id='columnchart_material' style='width: 1400px; height: 400px;'></div>";
        }
        else{
            return "No Data";
        }
        
        
    }
    
    // Interface function for getting user data
    private function getUsers( $overwrite = false ){
        
        if($GLOBALS['memOn'] !== true) { 
            $data = $this->getUsersFromDB();             
        }
        elseif($overwrite) {
            $data = $this->getUsersFromDB(); 
            $GLOBALS['cache']->set(Data::$target_site."battleAnalysisData:".$this->rankID, $data, MEMCACHE_COMPRESSED, 600);            
        }
        else {
            if(!($data = $GLOBALS['cache']->get(Data::$target_site."battleAnalysisData:".$this->rankID))) {
                $data = $this->getUsersFromDB(); 
                $GLOBALS['cache']->set(Data::$target_site."battleAnalysisData:".$this->rankID, $data, MEMCACHE_COMPRESSED, 600);
            }
            else{
                $GLOBALS['template']->append('calcDebug', "User information collected from cache");
            }
        }
        $this->users = $data;
    }
    
    // Functions being used in calculations
    private function getUsersFromDB(){
        
        //  Fetch data from database
        $this->users = $GLOBALS['database']->fetch_data('SELECT 
                `users`.`id`, `user_rank`, `users_statistics`.`rank`, `rank_id`, 
                `users_loyalty`.`village`, `money`, `cur_health`, `cur_sta`,
                `cur_cha`, `max_health`, `max_cha`, `max_sta`, `tai_off`, `tai_def`,
                `nin_off`, `nin_def`, `gen_off`, `gen_def`, `weap_def`, `weap_off`,
                `speed`, `strength`, `willpower`, `intelligence`, `bloodline`, `specialization`,`strengthFactor`
            FROM `users`
                INNER JOIN `users_occupations` ON (`users_occupations`.`userid` = `users`.`id`)
                INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                INNER JOIN `users_missions` ON (`users_missions`.`userid` = `users`.`id`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
            
            WHERE `rank_id` = '.$this->rankID.' AND
                  `user_rank` IN ("Member","Paid")
            ORDER BY `users_statistics`.`experience` DESC ');
        $userCount = count($this->users);
        
        // Go through all users    
        for( $i=0; $i < $userCount; $i++ ){
            
            
            // Get all jutsus
            $this->users[$i]['jutsus'] = $GLOBALS['database']->fetch_data("
                SELECT 
                    `entry_id`,`jid`,`level`,`required_rank`,`element`,`max_level`,`attack_type`,`cost_type`,`jutsu_type`,`effect_1`,`effect_2`,`effect_3`,`effect_4`,`weapon_class`
                FROM `users_jutsu`
                INNER JOIN `jutsu` ON (
                    `jutsu`.`id` = `users_jutsu`.`jid` AND
                    (`effect_1` LIKE 'DAM%' OR `effect_2` LIKE 'DAM%' OR `effect_3` LIKE 'DAM%' OR `effect_4` LIKE 'DAM%')
                )
                WHERE `users_jutsu`.`uid` = ".$this->users[$i]['id']." AND locate(`users_statistics`.`taggedGroup`, `users_jutsu`.`tagged` ) > 0");
            
            // Get all user items
            $this->users[$i]['items'] = $GLOBALS['database']->fetch_data("
                SELECT `items`.`id`,`items`.`name`,`stack`,`items`.`type`,`items`.`use`,`items`.`use2`,`items`.`strength`,`items`.`element`,`items`.`required_rank`,`items`.`item_level`,`equipped`
                FROM `users_inventory` 
                INNER JOIN `items` ON ( `items`.`id` = `users_inventory`.`iid` AND (`items`.`type` = 'item' OR `items`.`type` = 'armor' OR ((`items`.`type` = 'weapon') AND `users_inventory`.`equipped` = 'yes') ) AND (`items`.`use` LIKE 'DMG%' OR `items`.`use2` LIKE 'DMG%' OR `items`.`type` = 'armor') ) 
                WHERE `users_inventory`.`uid` = ".$this->users[$i]['id']." AND `users_inventory`.`stack` > 0 AND `users_inventory`.`durabilityPoints` > 0");
            
            
            // Set armor to 0
            $this->users[$i]['armor'] = $this->calculate_armor( $i );
        }
        
        // Tell the user what is being tested
        $GLOBALS['template']->append('calcDebug', "Retrieved users of rank ".$this->rankID.": ".$userCount );
        
        // Return users
        return $this->users;
    }
    
    // Calculate Armor
    protected function calculate_armor( $i ) {

        // Loop over items and figure out armor
        $armor = 0;
        if( isset($this->users[$i]['items']) && $this->users[$i]['items'] !== "0 rows" ){
            foreach( $this->users[$i]['items'] as $itemID => $itemData ){
                if( $itemData['type'] == "armor" &&  $itemData['equipped'] == "yes" ){
                    $armor += $itemData['strength'];
                }
            }
        }

        // Set user armor
        return $armor;    
    }
    
    // Set the battle formula descriptions
    private function setBattleFormula(){
        $formula = isset($_POST['formula'] ) ? $_POST['formula'] : "none";
        
        switch( $formula ){
            case "old": 
                
                // Include neccesary libraries
                require('../libs/battleSystem/damage_calc.inc.php');
                
                // Show information about how it works
                $GLOBALS['template']->append('description', '
                    <h1>Here are some of the formulas used for jutsu calcs:</h1> <br><br>
                    <b>desperation</b> = (67 + life_perc / 5 + sqrt(0.29 * (user_willpower + user_intelligence + user_strength + user_speed) / 4))<br><br>
                    <b>balance</b> = (47 + sqrt(0.32 * (user_willpower + user_intelligence + user_strength + user_speed) / 4) + (20 - (life_perc / 5)))<br><br>
                    <b>randomFactor1</b> = random_int(balance, desperation) / 100<br><br>
                    <b>userStat1Factor</b> = sqrt(user_stat1 / 10)<br><br>
                    <b>userStat2Factor</b> = user_stat2 / 20<br><br>
                    <b>pureUserDamage</b> = randomFactor1 * (sqrt((user_Offence * (1 + sqrt(jutsu_power / 5)) ) * userStat1Factor) + userStat2Factor)<br><br>
                    <b>targetStatDefence</b> = target_stat1 / 5<br><br>
                    <b>totalTargetDefence</b> = round(sqrt(targetDefence * targetStat1Defence))<br><br>
                    <b>armorReducedDamage:</b><br>
                    -- <b>tempDam</b> = round(pureUserDamage - totalTargetDefence)<br>
                    -- <b>tempReduction</b> = (pow(60, -8) * pow(target_armor, 2) + pow(40, -4) * target_armor) * 2600;<br>
                    -- <b>armorReducedDamage</b> = tempDam - ((tempDam / 100) * tempReduction)<br><br>
                    <b>finalDamage</b> = 0.4 * (armorReducedDamage - random_int(46, 54) / 100 * armorReducedDamage * ( targetDefence / (userOffence + 200) ) + 60)
                '
                );
                $GLOBALS['template']->append('description', '
                    <h1>Here are some of the formulas used for weapon calcs:</h1> <br><br>
                    <b>desperation</b> = (67 + life_perc / 5 + sqrt(0.29 * (user_willpower + user_intelligence + user_strength + user_speed) / 4))<br><br>
                    <b>balance</b> = (47 + sqrt(0.32 * (user_willpower + user_intelligence + user_strength + user_speed) / 4) + (20 - (life_perc / 5)))<br><br>
                    <b>randomFactor1</b> = random_int(balance, desperation) / 100<br><br>
                    <b>userStat1Factor</b> = sqrt(user_stat1 / 20)<br><br>
                    <b>userStat2Factor</b> = user_stat2 / 40<br><br>
                    <b>userOffence</b> = (user_stat1_offence + user_stat2_offence) / 2<br><br>
                    <b>pureUserDamage</b> = randomFactor1 * (sqrt((user_Offence * (1 + sqrt(weap_power / 5)) ) * userStat1Factor) + userStat2Factor)<br><br>
                    <b>targetDefence</b> = (target_stat1_defence + target_stat2_defence) / 2<br><br>
                    <b>armorReducedDamage:</b><br>
                    -- <b>tempDam</b> = round(pureUserDamage - totalTargetDefence)<br>
                    -- <b>tempReduction</b> = (pow(60, -8) * pow(target_armor, 2) + pow(40, -4) * target_armor) * 2600;<br>
                    -- <b>armorReducedDamage</b> = tempDam - ((tempDam / 100) * tempReduction)<br><br>
                    <b>finalDamage</b> = 0.4 * (armorReducedDamage - random_int(46, 54) / 100 * armorReducedDamage * ( targetDefence / (userOffence + 200) ) + 60)
                '
                );
            break;
            case "terrs": 

                // Include neccesary libraries
                require('../libs/battleSystem/damage_calc_v2.inc.php');
                
                // Show information about how it works
                $GLOBALS['template']->assign('description', '
                    Soon To Come
                '
                );
                break;
            default: 
                $GLOBALS['template']->assign('description', 'Not yet selected' );
            break;
        }
    }
    
    // Get types from tag conventions, e.g. T/N/G/W => ...
    public function translate_tag_type($searchTerm) {
        // Search the term
        switch(true) {
            case($searchTerm === 'T'): $type = 'tai'; $stat1 = 'strength'; $stat2 = 'speed'; break;
            case($searchTerm === 'N'): $type = 'nin'; $stat1 = 'intelligence'; $stat2 = 'wisdom'; break;
            case($searchTerm === 'G'): $type = 'gen'; $stat1 = 'wisdom'; $stat2 = 'intelligence'; break;
            case($searchTerm === 'W'): $type = 'weap'; $stat1 = $stat2 = 'strength'; break;
        }

        // Return stuff
        return array($type, $stat1, $stat2);
    }
    
    // Get user specialization
    public function get_user_specialization( $side ) {
        if (isset($this->{$side}['specialization'])) {
            if($this->{$side}['specialization'] !== "0:0") {
                // Set the specialization
                $specialization = explode(":", $this->{$side}['specialization']);
                $returnData = array('ttype' => $specialization[0]);
                switch ($specialization[0]) {
                    case "T": $returnData['att_type'] = "tai"; break;
                    case "N": $returnData['att_type'] = "nin"; break;
                    case "G": $returnData['att_type'] = "gen"; break;
                    case "W": $returnData['att_type'] = "weap"; break;
                }
                return $returnData;
            }
        }
        return false;
    }
}

new battle_test();