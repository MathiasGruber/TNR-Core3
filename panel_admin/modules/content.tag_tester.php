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

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require('../libs/jutsuSystem/jutsuFunctions.php');

class admin_tagChecker {
       
    // Constructor
    public function __construct() {
        
        // Decide what to check
        if (!isset($_GET['act']) ) {
            $this->check_tags( "jutsu" ); 
        } elseif (
            $_GET['act'] == 'bloodline' ||
            $_GET['act'] == "jutsu" || 
            $_GET['act'] == 'item'|| 
            $_GET['act'] == 'weapon'||
            $_GET['act'] == 'armor'|| 
            $_GET['act'] == 'artifact'|| 
            $_GET['act'] == 'ai'|| 
            $_GET['act'] == 'objectives'|| 
            $_GET['act'] == 'itemLinks'
        ) {
            $this->check_tags( $_GET['act'] ); 
        } 
        
        // Load the template wrapper
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/tagTester/main.tpl');
    }
    
    // Test all tags in the database
    private function check_tags( $type ) {
        
        // Get all the tags in the database
        switch( $type ){
            case "jutsu": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu`"); 
                
                // Fix up jutsus that may be split
                foreach( $data as $key => $jutsu ){
                    if( $jutsu['splitJutsu'] == "yes" ){
                        $data[] = jutsuBasicFunctions::fixUpJutsuData($jutsu, "N");
                        $data[] = jutsuBasicFunctions::fixUpJutsuData($jutsu, "T");
                        $data[] = jutsuBasicFunctions::fixUpJutsuData($jutsu, "G");
                        $data[] = jutsuBasicFunctions::fixUpJutsuData($jutsu, "W");
                        unset( $data[ $key ] );
                    }
                }
                $data = array_values( $data );
                
                $columns = array( "effect_1","effect_2","effect_3","effect_4","itemRequirement_1","itemRequirement_2" );
                $modUrlData = array("act" => "jutsu_modify", "id" => "21", "jid" => "table.id");
                $this->searchTerm = "id";
            break;
            case "bloodline": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines`"); 
                $columns = array( "tags" );
                $modUrlData = array("act" => "edit_bloodline", "id" => "8", "bid" => "table.name");
                $this->searchTerm = "name";
            break;
            case "itemLinks": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` "); 
                $columns = array( "dummy" );
                $modUrlData = array("act" => "modify", "id" => "10", "iid" => "table.id");
                $this->allItemIDs = array();
                if( $data !== "0 rows" ){
                    for( $i=0 ; $i < count($data) ; $i++ ){
                        $data[$i]['dummy'] = $data[$i]['processed_results'] ." ".$data[$i]['craft_recipe'];
                        $this->allItemIDs[] = $data[$i]['id'];
                    }
                }
                $this->searchTerm = "id";
            break;
            case "ai": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ai`"); 
                $columns = array();
                if( $data !== "0 rows" ){
                    for( $i=0 ; $i < count($data) ; $i++ ){
                        if( !empty( $data[$i]['trait'] ) ){
                            $traits = explode(";", $data[$i]['trait']);
                            $n = 0;
                            for( $n=0 ; $n < count($traits) ; $n++ ){
                                $data[$i]['trait_'.$n] = $traits[$n];
                                if(!in_array('trait_'.$n, $columns) ){
                                    $columns[] = 'trait_'.$n;
                                }
                            }
                        }
                        if( !empty( $data[$i]['ai_actions'] ) ){                            
                            $actions = explode(";", $data[$i]['ai_actions']);
                            $n = 0;
                            for( $n=0 ; $n < count($actions) ; $n++ ){
                                $data[$i]['act_'.$n] = $actions[$n];
                                if(!in_array('act_'.$n, $columns) ){
                                    $columns[] = 'act_'.$n;
                                }
                            }
                        }
                    }
                }
                $modUrlData = array("act" => "edit", "id" => "7", "oid" => "table.id");
                $this->searchTerm = "name";
            break;
            case "objectives": 
                
                // Get data
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests`"); 
                
                // $columns = array( "requirements", "simpleGuide", "restrictions", "rewards" );
                if( $data !== "0 rows" ){
                    
                    // Location function to reduce code duplication
                    function addToColumn( $columns , $data, $i, $name , $required){
                        if( !empty( $data[$i][ $name ] ) ){
                            $traits = explode(";", $data[$i][ $name ]);
                            $n = 0;
                            for( $n=0 ; $n < count($traits) ; $n++ ){
                                if( !empty($traits[$n]) ){
                                    $traits[$n] = str_replace("\t", '', $traits[$n]); // remove tabs
                                    $traits[$n] = str_replace("\n", '', $traits[$n]); // remove new lines
                                    $traits[$n] = str_replace("\r", '', $traits[$n]); // remove carriage returns
                                    $data[$i][$name.'_'.$n] = $traits[$n];
                                    if( !in_array( $name.'_'.$n, $columns ) ){
                                        $columns[] = $name.'_'.$n;
                                    }
                                    
                                }
                            }
                        }
                        elseif( $required == true ){
                            $data[$i][ 'error.'.$i ] = "Empty required field: ".$name;
                            $columns[] = 'error.'.$i;
                        }
                        return array( $data, $columns );
                    }
                    
                    $columns = array();
                    for( $i=0 ; $i < count($data) ; $i++ ){
                        list( $data, $columns ) = addToColumn($columns, $data, $i, "requirements", true);
                        list( $data, $columns ) = addToColumn($columns, $data, $i, "simpleGuide", true);
                        list( $data, $columns ) = addToColumn($columns, $data, $i, "restrictions", false);
                        list( $data, $columns ) = addToColumn($columns, $data, $i, "rewards", false);
                        list( $data, $columns ) = addToColumn($columns, $data, $i, "locationReq", false);
                    }
                }
                $modUrlData = array("act" => "edit", "id" => "28", "oid" => "table.id");
                $this->searchTerm = "id";
            break;
            case "item": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `type` = 'item' OR `type` = 'repair'"); 
                $columns = array( "use","use2" );
                $modUrlData = array("act" => "modify", "id" => "10", "iid" => "table.id");
                $this->searchTerm = "id";
            break;
            case "armor": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `type` = 'armor'"); 
                $columns = array( "use","use2" );
                $modUrlData = array("act" => "modify", "id" => "10", "iid" => "table.id");
                $this->searchTerm = "id";
            break;
            case "artifact": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `type` = 'artifact'"); 
                $columns = array( "use","use2" );
                $modUrlData = array("act" => "modify", "id" => "10", "iid" => "table.id");
                $this->searchTerm = "id";
            break;
            case "weapon": 
                $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `type` = 'weapon'"); 
                $columns = array( "use","use2","weapon_classifications" );
                $modUrlData = array("act" => "modify", "id" => "10", "iid" => "table.id");
                $this->searchTerm = "id";
            break;
        }
        
        // Create array for all the not-working tags
        $this->brokeTags = array();
        
        // Save tag templates
        $this->set_data( $type );
        
        // Loop through all data entries
        if( $data !== "0 rows" ){
            foreach( $data as $dataEntry ){

                // Loop through all tag columns
                foreach( $columns as $column ){
                    if( (isset($dataEntry[ $column ]) && $dataEntry[ $column ] != null) || $type == "itemLinks"){

                        // Set variables
                        $this->curEntry = $dataEntry;
                        $this->curColumn = $column;
                        $this->template = "N/A";

                        // Validate the tag
                        switch( $type ){
                            case "objectives": $this->validateTaskTag(); break;
                            case "itemLinks": $this->validateItemLinks(); break;
                            default: $this->validateTag(); break;
                        }
                        
                    }
                }
            }
        }
        
        // Show the broken tags
        tableParser::show_list(
            'tags',
            'Current Tags expected to be Broken', 
            $this->brokeTags,
            array(
                'name' => "Name", 
                "effectID" => "Effect #",
                'current' => "Current Tag", 
                'template' => "Identified Template",
                'message' => "System Comment"
            ), 
            array( 
                array_merge( array("name" => "Modify") , $modUrlData )
            ) ,
            false,   // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false,
            "Do note that this system is not absolute. It is not guarenteed to find all bugs in tags. 
             If a tag is shown to give an error though you think it is correct, please report to coder.
             In any case it is strongly recommended that every task is manually tested and validated in the game"
        );  
        
        // Show the tags
        tableParser::show_list(
            'availableTags',
            'Current Tags in the system', 
            $this->TemplateTagArray,
            array(
                'note' => "Identification",
                'tag' => "Available Tags",
                'info' => "Info about tag"
            ), 
            array() ,
            false,
            false
        );  
    }
    
    // Function for checking complex task/quest/mission tags
    private function validateItemLinks(){
        
        // Check rows
        if( $this->curEntry['type'] == "process" && empty($this->curEntry['processed_results']) ){
            $this->setBrokenTag( "Not specified what to process into" );
        }
        elseif( 
            $this->curEntry['herb_location'] != null && 
            $this->curEntry['type'] != "process" &&
            $this->curEntry['type'] != "material" 
        ){
            $this->setBrokenTag( "Not a material or process but has herb location: ".$this->curEntry['herb_location'] );
        }
        elseif( $this->curEntry['type'] != "process" && !empty($this->curEntry['processed_results']) ){
            
            // Check if a process without processed_results field
            $this->setBrokenTag( "Processed into something, but not process" );
        }
        elseif( $this->curEntry['type'] == "process" && $this->curEntry['professionRestriction'] == "none" ){
            
            // Check if a process without processed_results field
            $this->setBrokenTag( "Process not linked to profession" );
        }
        
        elseif(  $this->curEntry['craftable'] == "Yes" && $this->curEntry['professionRestriction'] == "none" ){
            
            // Check if a process without processed_results field
            $this->setBrokenTag( "Craftable item not linked to profession" );
        }
        elseif( $this->curEntry['craftable'] == "Yes" && empty($this->curEntry['craft_recipe']) ){
            
            // Check if a process without processed_results field
            $this->setBrokenTag( "Craft recipe lacking" );
        }
        elseif( $this->curEntry['craftable'] == "No" && !empty($this->curEntry['craft_recipe']) ){
            
            // Check if a process without processed_results field
            $this->setBrokenTag( "Craft recipe but not craftable" );
            
        }
        elseif( $this->curEntry['craftable'] == "Yes" && !empty($this->curEntry['craft_recipe']) ){
            
            // Check
            $explodeTag = explode(";",$this->curEntry['craft_recipe']);
            foreach( $explodeTag as $tempTag ){
                $this->template = $this->TemplateTagArray[1]['tag'];
                if( !preg_match("/^\d+,\d+$/", $tempTag) ){
                    $this->setBrokenTag( "Wrong Format: ".$tempTag );
                }
                $resultData = explode(",",$tempTag);
                if( !in_array( $resultData[0] , $this->allItemIDs) ){
                    $this->setBrokenTag( "Requirement IID not valid" );
                }
            }
        }
        elseif( $this->curEntry['type'] == "process" && !empty($this->curEntry['processed_results']) ){
            
            // Check
            $explodeTag = explode(";",$this->curEntry['processed_results']);
            foreach( $explodeTag as $tempTag ){
                $this->template = $this->TemplateTagArray[0]['tag'];
                if( !preg_match("/^\d+,\d+,\d+,\d+(\.\d+)?$/", $tempTag) ){
                    $this->setBrokenTag( "Wrong Format: ".$tempTag );
                }
                $resultData = explode(",",$tempTag);
                if( !in_array($resultData[0],$this->allItemIDs) ){
                    $this->setBrokenTag( "Processed IID not valid: ".$resultData[0] );
                }
            }
        } 
        elseif( 
            $this->curEntry['type'] != "process" && 
            empty($this->curEntry['processed_results']) &&
            $this->curEntry['craftable'] == "No" && 
            empty($this->curEntry['craft_recipe'])
        ){
            // Do nothing
        }
        else{
            
            // Weird
            $this->setBrokenTag( "Tag Not Identified" );
        }
        
        // Set variables
        unset($this->curEntry);
        unset($this->curColumn);
        unset($this->template);
    }
    
    
    // Function for checking complex task/quest/mission tags
    private function validateTaskTag(){
        $tag = str_replace(" ","",$this->curEntry[$this->curColumn] );
        // Try to identify the tag
        if(
            preg_match("/^req:.+$/", $tag) ||   // Requirement
            preg_match("/^rew:.+$/", $tag) ||   // Reward
            preg_match("/^info:.+$/", $tag) ||    // Guide
            preg_match("/^battle:.+$/", $tag) ||    // Guide
            preg_match("/^complete:.+$/", $tag)     // Complete
        ){
            // Do nothing for these
            $this->template = "Simple Text";
            
            // For missions & crimes, check that there is a complete-statement
            if( preg_match("/(crime|mission)/", $this->curEntry[ "type" ]) &&
                !preg_match("/complete:/", $this->curEntry[ "simpleGuide" ])
            ){
                $this->setBrokenTag( "Missing complete-statement for mission/crime" );
            }
        }
        elseif( preg_match("/^item,\d+,(add|remove_once|remove_all)$/", $tag) ){
            $this->template = $this->TemplateTagArray[13]['tag'];
        }
        elseif( preg_match("/^(AREA|REGION|TERRITORY)/", $tag) ){
            $this->template = $this->TemplateTagArray[21]['tag'];
            if( !preg_match("/^(REGION\(\D+\)|TERRITORY\(\D+\)|AREA\(-?\d+\.-?\d+\.-?\d+\.-?\d+\))$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
            $this->checkAreaTag($tag);
        }
        elseif( preg_match("/^item,\d+$/", $tag) ){
            $this->template = $this->TemplateTagArray[19]['tag'];
        }
        elseif( preg_match("/^stats,\D+,\d+/", $tag) ){
            $this->template = $this->TemplateTagArray[12]['tag'];
            if( !preg_match("/^stats,(experience|nin_def|gen_def|tai_def|weap_def|nin_off|gen_off|tai_off|weap_off|strength|intelligence|willpower|speed|experience|money|bank|max_health|max_cha|max_sta|element_mastery_1|element_mastery_2),\d+/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^missions,/", $tag) ){
            $this->template = $this->TemplateTagArray[22]['tag'];
            if( !preg_match("/^missions,(mission|crime|any),(A|B|C|D|S),(win|lose)((>|=|>=)\d+)?/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^jutsu,\d+,\d+/", $tag) ){
            $this->template = $this->TemplateTagArray[14]['tag'];
        }
        elseif( preg_match("/^tickets,\d+/", $tag) ){
            $this->template = $this->TemplateTagArray[15]['tag'];
        }
        elseif( preg_match("/^page,\d+$/", $tag) ){
            $this->template = $this->TemplateTagArray[8]['tag'];
        }
        elseif( preg_match("/^quest,\d+$/", $tag) ){
            $this->template = $this->TemplateTagArray[11]['tag'];
        }
        elseif(preg_match("/^stats,/", $tag) ){
            $this->template = $this->TemplateTagArray[0]['tag'];
            $stats = "nin_def|gen_def|tai_def|weap_def|nin_off|gen_off|tai_off|weap_off|strength|intelligence|willpower|speed|element_mastery_1|element_mastery_2";
            if( !preg_match("/^stats,(((".$stats.")(\+)?)+(>|=|>=)\d+(\.[0-9]{1,2})?(\|\|)?)+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }        
        elseif(preg_match("/^dailyRewardModification,/", $tag) ){
            $this->template = $this->TemplateTagArray[0]['tag'];
            $roundFractions = "(,\d+(\.\d+)?)+";
            if( !preg_match("/^dailyRewardModification".$roundFractions."$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif(preg_match("/^occupation,/", $tag) ){
            $this->template = $this->TemplateTagArray[17]['tag'];
            $data = "surgeon|hunter|bhunter|armorCraft|weaponCraft|chefCook|miner|herbalist";
            if( !preg_match("/^occupation,(".$data.")(>|=|>=)\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif(preg_match("/^jutsu,/", $tag) ){
            $this->template = $this->TemplateTagArray[3]['tag'];
            if( !preg_match("/^jutsu,(any|\d+),(level|times_used)(>|=|>=)\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^errands,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[1]['tag'];
            $data = "errands|scrimes|lcrimes";
            if( !preg_match("/^errands,(".$data.")(>|=|>=)\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^factions,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[6]['tag'];
            if( !preg_match("/^factions,(anbu|kage|clan|surgeon|hunter|bhunter|armorCraft|weaponCraft|chefCook|miner|herbalist),(join|village)(,.+)?/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^element,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[23]['tag'];
            if( !preg_match("/^element,(all|pri|sec|spe),((fire|wind|lightning|earth|water|scorching|tempest|magnetism|wood|steam|light|dust|storm|lava|ice|gold|silver|ALL)(\.)?)+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^combat,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[2]['tag'];
            $data = "anyAI|mission|crime|quest|normalArena|tornArena|mirrorArena|anyArena|mapAI|eventAI|PVP|leaderPVP|spars|territory";
            if( !preg_match("/^combat,(".$data.")(,(any|AIid:\d+))?,(wins|losses|draws|beatAID)(>|=|>=)\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^item,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[4]['tag'] . " <br><b>or</b><br> " . 
                              $this->TemplateTagArray[13]['tag']. " <br><b>or</b><br> " .
                              $this->TemplateTagArray[19]['tag'];
            if( !preg_match("/^item,(any|\d+),(own|equip|times_used)((>|=|>=)\d+)?$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^initiateCombat,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[9]['tag'];
            if( !preg_match("/^initiateCombat,aiList,(\d+.?)+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^lottery,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[5]['tag'];
            if( !preg_match("/^lottery,tickets(>|=|>=)\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^move,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[7]['tag'];
            if( !preg_match("/^move,(REGION\(\D+\)|TERRITORY\(\D+\)|AREA\(-?\d+\.-?\d+\.-?\d+\.-?\d+\))(>|=|>=)\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
            $this->checkAreaTag($tag);
        }
        elseif( preg_match("/^village,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[18]['tag'];
            $villages = implode("|",Data::$VILLAGES);
            if( !preg_match("/^village,((".$villages.")\.?)+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
        }
        elseif( preg_match("/^createAI,/", $tag) ){
            // Requirement errands
            $this->template = $this->TemplateTagArray[10]['tag'];
            $locationSelector = "(REGION\(\D+\)|TERRITORY\(\D+\)|AREA\(-?\d+\.-?\d+\.-?\d+\.-?\d+\))";
            if( !preg_match("/^createAI,(\d+.?)+,".$locationSelector.",\d+$/", $tag) ){
                $this->setBrokenTag( "Tag didn't pass PCRE" );
            }
            $this->checkAreaTag($tag);
        }
        
        // If we reach this point, the tag has failed
        if( $this->template == "N/A" ){
            $this->setBrokenTag( "Tag Not Identified: ".$tag );
        }
        
        // Set variables
        unset($this->curEntry);
        unset($this->curColumn);
        unset($this->template);
    }
    
    private function checkAreaTag( $tag ){
        preg_match( "/\(([^)]+)\)/" , $tag , $match );
        if( !empty($match) && preg_match( "/(AREA)/", $tag ) ){
            $area = explode( ".", $match[1] );
            if( $area[0] > $area[1] || $area[2] > $area[3] ){
                $this->setBrokenTag( "AREA tag is incorrect" );
            }
        }
    }
    
    private function validateTag(){
        
        // Figure out which tag we're looking at
        $tags = explode( ":", $this->curEntry[$this->curColumn] );
        
        // Special overwrite for certain columns
        switch( $this->curColumn ){
            case "weapon_classifications": 
                $tags = explode(":", "weapon_classifications:".$tags[0]);
            break;
        }
        
        // Get the template
        foreach( $this->explodedTemplates as $tagTemplate ){
            if( $tags[0] == $tagTemplate[0] ){
                
                // Template identified
                $this->template = implode(":", $tagTemplate);
                
                // Check AOE
                $Tagcount = count($tags);
                if ( $Tagcount >= 4 && $tags[ $Tagcount - 4 ] == "AOE") {
                    if( $this->checkNumber($tags[ $Tagcount - 1 ], 0, 10) ){
                        unset($tags[ $Tagcount - 1 ]);
                        if( $this->checkNumber($tags[ $Tagcount - 2 ], 0, 200) ){
                            unset($tags[ $Tagcount - 2 ]);
                            if( $this->checkNumber($tags[ $Tagcount - 3 ], 0, 200) ){
                                unset($tags[ $Tagcount - 3 ]);
                                unset($tags[ $Tagcount - 4 ]);
                            }
                        }
                    }
                }
                
                // Check if the tag-count matches
                if( 
                    count($tags) == count($tagTemplate) ||
                    ( in_array($tags[0], array("SUM","JUT","WPN","ITM")) && count($tags) < count($tagTemplate) )
                ){
                    
                    // Check the tag elements element for element
                    for( $i = 1; $i < count($tags); $i++ ){
                        
                        // Do checks
                        if( 
                            $tagTemplate[$i] == "user|opponent" ||
                            $tagTemplate[$i] == "armor|weapon" ||
                            $tagTemplate[$i] == "CALC|STAT|PERC" ||
                            $tagTemplate[$i] == "PERC|STAT|TSTA" ||
                            $tagTemplate[$i] == "DAM|STAT|CALC|PERC" ||
                            $tagTemplate[$i] == "DAM|STAT|PERC" ||
                            $tagTemplate[$i] == "DAM|STAT" ||
                            $tagTemplate[$i] == "PERC|STAT|TSTA|TINC" ||
                            $tagTemplate[$i] == "PERC|DAM|STAT" ||
                            $tagTemplate[$i] == "PERC|STAT" ||
                            $tagTemplate[$i] == "STAT|DAM" ||
                            $tagTemplate[$i] == "STAT|PERC" ||
                            $tagTemplate[$i] == "STAT|DAM|HEA" ||
                            $tagTemplate[$i] == "user|opponent|LISTID" ||
                            $tagTemplate[$i] == "PERC|STAT|TSTA|TINC" ||
                            $tagTemplate[$i] == "add|copy"
                        ){
                            // Do all the checks where it's simply gotta be in the string
                            $array = explode( "|", $tagTemplate[$i] );
                            if( !in_array( $tags[$i], $array ) ){
                                $this->setBrokenTag( "Element mismatch: <br>".$tags[$i]." vs. ".$tagTemplate[$i] );
                            }
                        }
                            // Number checks
                        elseif( $tagTemplate[$i] == "basePowerInt" ){ $this->checkNumber( $tags[$i] , -300000, 300000); }
                        elseif( $tagTemplate[$i] == "levelPowerInt" ){ $this->checkNumber( $tags[$i] , -5000, 5000); }
                        elseif( $tagTemplate[$i] == "chance" ){ $this->checkNumber( $tags[$i] , -5, 100); }
                        elseif( $tagTemplate[$i] == "chancePerLevel" ){ $this->checkNumber( $tags[$i] , 0, 10); }
                        elseif( $tagTemplate[$i] == "minRounds" ){ $this->checkNumber( $tags[$i] , 1, 10); }
                        elseif( $tagTemplate[$i] == "itemID" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( $tagTemplate[$i] == "itemAmount" ){ $this->checkNumber( $tags[$i] , 1, 100); }
                        elseif( $tagTemplate[$i] == "maxRounds" ){ $this->checkNumber( $tags[$i] , 1, 10); }
                        elseif( $tagTemplate[$i] == "minInt" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( $tagTemplate[$i] == "maxInt" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( $tagTemplate[$i] == "jutID" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( $tagTemplate[$i] == "craftLevel" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( $tagTemplate[$i] == "amountINT" ){ $this->checkNumber( $tags[$i] , 1, 500); }
                        elseif( $tagTemplate[$i] == "questID" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( $tagTemplate[$i] == "stealPower" ){ $this->checkNumber( $tags[$i] , 1, 10000); }
                        elseif( $tagTemplate[$i] == "actionSpecific" ){ $this->checkNumber( $tags[$i] , 1, 100); }
                        elseif( $tagTemplate[$i] == "increasePercentage" ){ $this->checkNumber( $tags[$i] , 1, 1000); }
                        elseif( preg_match("/(noDamage|noResidual|noRecoil|noKO|noReflect|noSeal|noStun|doRemoveItem|doGenerals|doStats|doHealth)/", $tagTemplate[$i]) ){
                            $this->checkNumber( $tags[$i] , 0, 1);
                        }
                        elseif( $tagTemplate[$i] == "general" ){ 
                            // Check validity
                            if( !preg_match("/(strength|intelligence|speed|willpower)/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown general: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "action" ){ 
                            // Check validity
                            if( !preg_match("/(chain|healthTrigger)/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown action: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "bloodRank" ){ 
                            // Check validity
                            if( !preg_match("/^(A|B|C|D|R|S)$/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown rank: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == ",-separated list of valid itemIDs" ){
                            // Check validity
                            if( !preg_match("/^(\d+,?)+$/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown types: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == ",-separated list of valid weaponTypes" ){ 
                            // Check validity
                            if( !preg_match("/^((Axe|Staff|Fist Weapon|Sickle|Dagger|Sword|Polearm|Fan|Flail|Chain|Ranged),?)+$/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown types: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "elementList" ){ 
                            // Check validity
                            if( !preg_match("/(fire|wind|lightning|earth|water|scorching|tempest|magnetism|wood|steam|light|dust|storm|lava|ice|gold|silver|ALL)/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown element: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "id|elementList|ALL" ){ 
                            // Check validity
                            if( !is_numeric($tags[$i]) ){
                                if( !preg_match("/(fire|wind|lightning|earth|water|scorching|tempest|magnetism|wood|steam|light|dust|storm|lava|ice|gold|silver|ALL)/", $tags[$i]) ){
                                    $this->setBrokenTag( "Unknown element: <br>".$tags[$i] );
                                }
                            }
                        }
                        elseif( $tagTemplate[$i] == "JUT|ITM|WPN|ELM|STTAI|STCHA" ){ 
                            // Check validity
                            if( !preg_match("/(JUT|ITM|WPN|ELM|STTAI|STCHA)/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown element: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "STR|STAT|PERC" ){ 
                            if( !preg_match("/(STR|STAT|PERC)/", $tags[$i]) ){
                                $this->setBrokenTag( 'Must be either "STR","STAT", or "PERC": <br>'.$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "TNGW" ){ 
                            // Check validity
                            for( $x=0; $x<strlen($tags[$i]); $x++ ){
                                if( !stristr( "TNGW", $tags[$i][$x] )){
                                    $this->setBrokenTag( "Unknown off/def: <br>".$tags[$i]." vs. ".$tagTemplate[$i] );
                                }
                            }
                        } elseif( $tagTemplate[$i] == ",-separated list of Axe,Staff,Fist Weapon,Sickle,Dagger,Sword,Polearm,Fan,Flail,Chain,Ranged" ){ 
                            if( !preg_match("/^((Axe|Staff|Fist Weapon|Sickle|Dagger|Sword|Polearm|Fan|Flail|Chain|Ranged),?)+$/", $tags[$i]) ){
                                $this->setBrokenTag( "Unknown general: <br>".$tags[$i] );
                            }
                        }
                        elseif( $tagTemplate[$i] == "TNGW|H" ){ 
                            // Check validity
                            if( $tags[$i] !== "H" ){
                                for( $x=0; $x<strlen($tags[$i]); $x++ ){
                                    if( !stristr( "TNGW", $tags[$i][$x] )){
                                        $this->setBrokenTag( "Unknown off/def: <br>".$tags[$i]." vs. ".$tagTemplate[$i] );
                                    }
                                }
                            }
                        }
                        elseif( 
                            $tagTemplate[$i] == "Name|LIST" ||
                            $tagTemplate[$i] == "trait" ||
                            $tagTemplate[$i] == "act" ||
                            $tagTemplate[$i] == "bloodlineTag" 
                        ){ 
                            // Do nothing for these
                        }
                        else{
                            $this->setBrokenTag( "Unidentified Elements: <br>".$tags[$i]." vs. ".$tagTemplate[$i] );
                        }
                        
                    }
                }
                else{
                    $this->setBrokenTag( "Element mismatch-: ".$Tagcount." vs. ".count($tagTemplate) );
                }
            }
        }
        
        // If we reach this point, the tag has failed
        if( $this->template == "N/A" ){
            $this->setBrokenTag( "Tag Not Found" );
        }
        
        // Set variables
        unset($this->curEntry);
        unset($this->curColumn);
        unset($this->template);
    }
           
    private function setBrokenTag( $message ){
        $this->brokeTags[] = array( 
            "current" => $this->curEntry[ $this->curColumn ] , 
            "template" => $this->template, 
            "message" => $message,
            "id" => $this->curEntry[ $this->searchTerm ],
            "effectID" => $this->curColumn,
            "name" => $this->curEntry['name'] 
        );
    }
  
    private function set_data( $type ){
        
        switch( $type ){
            case "jutsu":
                $this->TemplateTagArray = array(
                    array( "note" => "itemRequirement", "tag" => "item:,-separated list of valid itemIDs:itemAmount:doRemoveItem"),
                    array( "note" => "itemRequirement", "tag" => "class:,-separated list of valid weaponTypes:itemAmount:doRemoveItem"),
                    array("tag" => "DAM:CALC|STAT|PERC:basePowerInt:levelPowerInt:general:general"),
                    array("tag" => "HEA:user|opponent:DAM|STAT|CALC|PERC:basePowerInt:levelPowerInt"),
                    array("tag" => "CJUTSU:user|opponent"),
                    array("tag" => "SEAL:user|opponent:chance:chancePerLevel:minRounds:maxRounds"),
                    array("tag" => "ADDE:user|opponent:bloodlineTag", "description" => "user | for bloodline instead of :"),
                    array("tag" => "REC:DAM|STAT|PERC:basePowerInt:levelPowerInt"),
                    array("tag" => "STUN:user|opponent:minRounds:maxRounds:chance:chancePerLevel"),
                    array("tag" => "STUNR:user|opponent:minRounds:maxRounds:chance:chancePerLevel"),
                    array("tag" => "KO:chance:chancePerLevel:general"),
                    array("tag" => "FLEE:chance:chancePerLevel"),
                    array("tag" => "NFLE:user|opponent:chance:chancePerLevel:minRounds:maxRounds"),
                    array("tag" => "RDAM:user|opponent:PERC|STAT|TSTA|TINC:minRounds:maxRounds:STAT|DAM:basePowerInt:levelPowerInt"),
                    array("tag" => "HOT:user|opponent:PERC|STAT|TSTA|TINC:minRounds:maxRounds:STAT|DAM|HEA:basePowerInt:levelPowerInt"),
                    array("tag" => "BCOPY:user|opponent:minRounds:maxRounds:chance:chancePerLevel"),
                    array("tag" => "EREFL:user|opponent:STAT|PERC:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "CLEAR:user|opponent"),
                    array("tag" => "ROB:chance:PERC|STAT:basePowerInt:levelPowerInt:chancePerLevel"),
                    array("tag" => "SUM:Name|LIST:user|opponent|LISTID:basePowerInt:levelPowerInt:trait:act:act:act",
                          "info" => "For trait, use ';' instead of ':' for the tag, and '!' to separate multiple traits. All non-used acts must be set to 'none' "),
                    array("tag" => "REF:user|opponent:PERC|STAT:TNGW:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "ABS:user|opponent:PERC|STAT:TNGW:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "STDE:user|opponent:PERC|STAT:general:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "STUP:user|opponent:PERC|STAT:general:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "OFFU:user|opponent:PERC|STAT|TSTA:TNGW:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "OFFD:user|opponent:PERC|STAT|TSTA:TNGW:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "DEFU:user|opponent:PERC|STAT|TSTA:TNGW:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "DEFD:user|opponent:PERC|STAT|TSTA:TNGW:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "DINC:user|opponent:TNGW:PERC|STAT:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "DDEC:user|opponent:TNGW:PERC|STAT:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "EINC:user|opponent:PERC|STAT:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "EDEC:user|opponent:PERC|STAT:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "HEAINC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "HEADEC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "ESINC:user|opponent:PERC|STAT:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "ESDEC:user|opponent:PERC|STAT:elementList:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "DSINC:user|opponent:PERC|STAT:TNGW:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "DSDEC:user|opponent:PERC|STAT:TNGW:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "ARINC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt:levelPowerInt"),
                    array("tag" => "ARDEC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt:levelPowerInt")
                );
            break;
            case "bloodline":
            case "ai":
                $this->TemplateTagArray = array(
                    array("tag" => "SCOPY:add|copy:increasePercentage:doHealth:doStats:doGenerals"),
                    array("tag" => "BCOPY"),
                    array("tag" => "RBLOOD:MIX|SINGLE:NumberOfTags"),
                    array("tag" => "DSDEC:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "DSINC:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "DDEC:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "DINC:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "REFL:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "EDINC:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "EDDEC:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "ESDEC:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "ESINC:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "ESABS:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "EREFL:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "INVINCIBLE:noDamage:noResidual:noRecoil:noKO:noReflect:noSeal:noStun"),
                    array("tag" => "WEAK:JUT|ITM|WPN|ELM|STTAI|STCHA:id|elementList|ALL:PERC|DAM|STAT:basePowerInt"),
                    array("tag" => "STRONG:JUT|ITM|WPN|ELM|STTAI|STCHA:id|elementList|ALL:DAM|STAT:basePowerInt"),
                    array("tag" => "STAU:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "STAD:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "CHAU:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "CHAD:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "HEAINC:PERC|STAT:basePowerInt"),
                    array("tag" => "HEADEC:PERC|STAT:basePowerInt")
                );
            break;
        
            case "itemLinks":
                $this->TemplateTagArray = array(
                    0 => array("note" => "Process Into", "tag" => "itemID,minAmount,maxAmount,chance"),
                    1 => array("note" => "Crafting Recipe", "tag" => "itemID,amount")
                );
            break;
            case "objectives":
                $this->TemplateTagArray = array(
                    0 => array("note" => "requirement", "tag" => "stats,[identifier-calculation][operator][value]"),
                    1 => array("note" => "requirement", "tag" => "errands,[identifier-calculation][operator][value]"),
                    2 => array("note" => "requirement", "tag" => "combat,[identifiers],[sub-identifier],[conditions][operator][sub_condition_value]"),
                    3 => array("note" => "requirement", "tag" => "jutsu,[jutsuID|any],action[operator][value]"),
                    4 => array("note" => "requirement", "tag" => "item,[item ID|any],action([opeator][value])"),
                    5 => array("note" => "requirement", "tag" => "lottery,tickets[operator][value]"),
                    6 => array("note" => "requirement", "tag" => "factions,faction,(join | action[operator][value])"),
                    7 => array("note" => "requirement", "tag" => "move,locationIdentifier[operator]times"),
                    8 => array("note" => "requirement", "tag" => "page,pageID"),
                    9 => array("note" => "requirement", "tag" => "initiateCombat,aiList,[.-separated IDlist]"),
                    10 => array("note" => "requirement", "tag" => "createAI,[.-separated IDlist for single battle],locationIdentifiers,chance"),
                    22 => array("note" => "requirement", "tag" => "missions,type,rank,subCondition[operator][value]"),
                    
                    11 => array("note" => "reward", "tag" => "quest,id"),
                    12 => array("note" => "reward", "tag" => "stats,(experience|nin_def|gen_def|tai_def|weap_def|nin_off|gen_off|tai_off|weap_off|strength|intelligence|willpower|speed|experience|money|bank|max_health|max_cha|max_sta|element_mastery_1|element_mastery_1),1000"),
                    13 => array("note" => "reward", "tag" => "item,itemID,action"),
                    14 => array("note" => "reward", "tag" => "jutsu,jutsuID,lvl"),
                    15 => array("note" => "reward", "tag" => "tickets,amount"),
                    24 => array("note" => "reward", "tag" => "dailyRewardModification,1stTimeFraction,2ndTimeFraction,3rdTimeFraction,...",
                                "info" => "ONLY FOR MISSIONS/CRIMES. If used, any rewards from tags after this tag will be modified by the series of fractions, depending on what time the type of task (e.g. C mission) was performed that day."),
                    
                    16 => array("note" => "restriction", "tag" => "quest,id"),
                    17 => array("note" => "restriction", "tag" => "occupation,occupationIdentifier[operator][value]"),
                    18 => array("note" => "restriction", "tag" => "village,[.-separated list of village names]"),
                    19 => array("note" => "restriction", "tag" => "item,[itemID]"),
                    20 => array("note" => "restriction", "tag" => "war,[any|.-separated list of village-identifiers]"),
                    23 => array("note" => "restriction", "tag" => "element,[all|pri|sec|spe]elementList"),                   
                    
                    21 => array("note" => "locationReq", "tag" => "locationIdentifier"),
                );
            break;
            case "item":
                $this->TemplateTagArray = array(
                    array("note" => "Repair Kits", "tag" => "repair:armor|weapon:craftLevel"),
                    array("tag" => "DMG:CALC|STAT|PERC:TNGW:STR|statInt"),
                    array("tag" => "HEA:PERC|STAT:STR|statInt"),
                    array("tag" => "STA:PERC|STAT:STR|statInt"),
                    array("tag" => "CHA:PERC|STAT:STR|statInt"),
                    array("tag" => "STUN:chance:minRounds:maxRounds"),
                    array("tag" => "STUNR:chance:minRounds:maxRounds"),
                    array("tag" => "FLE:chance"),
                    array("tag" => "REPEL:chance"),
                    array("tag" => "CLEAR:user|opponent"),
                    array("tag" => "STDE:user|opponent:PERC|STAT:general:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "STUP:user|opponent:PERC|STAT:general:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "OFFD:user|opponent:PERC|STAT:TNGW:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "OFFU:user|opponent:PERC|STAT:TNGW:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "DEFD:user|opponent:PERC|STAT:TNGW:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "DEFU:user|opponent:PERC|STAT:TNGW:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "HOT:user|opponent:PERC|STAT|HEA:minRounds:maxRounds:STAT|DAM|HEA:basePowerInt"),
                    array("tag" => "RDA:minRounds:maxRounds:PERC|STAT|TSTA:basePowerInt"),
                    array("tag" => "HEAINC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "HEADEC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "ARINC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt"),
                    array("tag" => "ARDEC:user|opponent:PERC|STAT:minRounds:maxRounds:basePowerInt")
                );
            break;
            case "artifact":
                $this->TemplateTagArray = array(
                    array("tag" => "JUTSU_QUEST:questID"),
                    array("tag" => "REGEN:amountINT"),
                    array("tag" => "BLOOD:bloodRank")
                );
            break;
            case "armor":
                $this->TemplateTagArray = array(
                    array("tag" => "REPEL:chance"),
                    array("tag" => "HEAINC:PERC|STAT:basePowerInt"),
                    array("tag" => "HEADEC:PERC|STAT:basePowerInt"),
                    array("tag" => "DSDEC:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "DDEC:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "REFL:TNGW|H:PERC|STAT:basePowerInt"),
                    array("tag" => "ESDEC:elementList:PERC|STAT:basePowerInt"),
                    array("tag" => "INVINCIBLE:noDamage:noResidual:noRecoil:noKO:noReflect:noSeal:noStun"),
                    array("tag" => "WEAK:JUT|ITM|WPN|ELM|STTAI|STCHA:id|elementList|ALL:PERC|DAM|STAT:basePowerInt"),
                    array("tag" => "STRONG:JUT|ITM|WPN|ELM|STTAI|STCHA:id|elementList|ALL:DAM|STAT:basePowerInt")
                );
            break;
            case "weapon":
                $this->TemplateTagArray = array(
                    array("tag" => "DMG:TNGW:basePowerInt"),
                    array("tag" => "STN:chance:minRounds:maxRounds"),
                    array("tag" => "LCH:STR|STAT|PERC:minInt:maxInt"),
                    array("tag" => "RDAM:PERC|STAT|TSTA|TINC:minRounds:maxRounds:STAT|DAM:basePowerInt"),
                    array("tag" => "NFLE:chance:minRounds:maxRounds"),
                    array("tag" => "ROB:chance:PERC|STAT:stealPower"),
                    array("note" => "Weapon Classifications", "tag" => "weapon_classifications:,-separated list of Axe,Staff,Fist Weapon,Sickle,Dagger,Sword,Polearm,Fan,Flail,Chain,Ranged")
                );
            break;
        }
        
        // If type is AI then add a few action tags
        if( $type == "ai" ){
            $this->TemplateTagArray[] = array("tag" => "STTAI", "note" => "Action");
            $this->TemplateTagArray[] = array("tag" => "STCHA", "note" => "Action");
            $this->TemplateTagArray[] = array("tag" => "JUT:jutID:action:actionSpecific", "note" => "Action");
            $this->TemplateTagArray[] = array("tag" => "WPN:itemID:action:actionSpecific", "note" => "Action");
            $this->TemplateTagArray[] = array("tag" => "ITM:itemID:action:actionSpecific", "note" => "Action");
            $this->TemplateTagArray[] = array("note" => "Action Specifications", "tag" => "You can add either ':chain:chainInt' or ':healthTrigger:healthPercentage' to action tags, to control use in combat");
        }
        
        // Make sure all in template array has a note & info
        for( $i=0 ; $i < count($this->TemplateTagArray) ; $i++ ){
            if( !isset( $this->TemplateTagArray[$i]['note'] ) ){
                $this->TemplateTagArray[$i]['note'] = "Tag";
            }
            if( !isset( $this->TemplateTagArray[$i]['info'] ) ){
                $this->TemplateTagArray[$i]['info'] = "Request if Needed";
            }
        }
        
        $this->explodedTemplates = array();
        foreach( $this->TemplateTagArray as $template ){
            $this->explodedTemplates[] = explode( ":", $template["tag"] );
        }
    }
    
    // Below are all the different test functions. 
    private function checkNumber( $value, $lowerLimit, $higherLimit ){
        if( is_numeric($value) || is_float($value) || $value == 0 ){
            if( $value <= $higherLimit+1 && $value >= $lowerLimit-1 ){
                return true;
            }
            else{
                $this->setBrokenTag( "Value ".$value." seems unrealistic." );
            }
        }
        else{
            $this->setBrokenTag( "Invalid Digit: ".$value .". Is Digit: ".  is_numeric($value) );
        }
        return false;
    }
    
}

new admin_tagChecker();