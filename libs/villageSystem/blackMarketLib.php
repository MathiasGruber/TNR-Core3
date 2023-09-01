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

class blackMarketLib {
    
    // constructor
    public function __construct(){
        
    }
    
    // Get the fields not to be shown
    protected function noShowFields( $isProfessionBag = false ){
        $dontShow = array("id","isProfessionEntry");
        if( $GLOBALS['userdata'][0]['user_rank'] !== 'Admin' ){
            $dontShow[] = "event_item";
        }
        if( $isProfessionBag == false ){
            $dontShow[] = "profession";
            $dontShow[] = "requiredProfessionLvl";
            $dontShow[] = "maxProfessionLvl";
        }
        return $dontShow;
    }
    
    // Check item
    protected function checkItem( $iid ){
        
        // Check if the item can be found
        $rewardName = "";
        if( isset( $iid ) && !empty( $iid ) ){
            $item = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '".$iid."' LIMIT 1");
            if( $item !== "0 rows" ){
                $rewardName = $item[0]['name'];
            }
        }

        // Check if an reward was found
        if( empty($rewardName) ){
            throw new Exception("The item you specified (iid ".$iid.") is not valid.");
        }
        else{
            return $rewardName;
        }
    }
    
    // Get category / grouping index of entry
    protected function getGroup( $entry ){
        return $entry['cost_type']."-".$entry['cost_amount']."-".$entry['cost_item_Number']."-".$entry['profession'].( $entry['solo'] == 'yes' ? ('-'.$entry['id']) : '' );
    }
    
    // Get title
    protected function getTableTitle( $isProfession , $packID ){
        if( $isProfession ){
            return array("id"=>0, "TP_subtitle" => "Profession Pack #".$packID );
        }
        else{
            return array("id"=>0, "TP_subtitle" => "Special Surprise Pack #".$packID );
        }
    }
    
    // Get the current special surprises
    public function getSpecialSurprises( $isProfession = false , $professionData = false , $adminPanel = false ){
        
        // Is profession or no
        $isProfessionEntry = $isProfession ? "yes" : "no";
        
        // Select statement
        $professionSelect = "";
        if( !empty($professionData) ){            
            $professionSelect = " AND 
                `requiredProfessionLvl` <= '".($professionData[0]['profession_exp']+1)."' AND
                `maxProfessionLvl` >= '".$professionData[0]['profession_exp']."' AND 
                `profession` = '".$professionData[0]['name']."'
            ";
        }
        
        // Extra selection
        $extraSelection = "";
        if( $adminPanel == true && $this->isEventTeam() ){
            $extraSelection .= " AND `blackmarket_surprises`.`event_item` = 'yes' ";
        }
        
        // Get all the entries
        $surprises = $GLOBALS['database']->fetch_data("
                SELECT 
                    `blackmarket_surprises`.*, 
                    `items`.`name` as `itemname`,
                    SUM( `log_specialSurprisePurchases`.`reward_count` ) as `currentCount`
                FROM `blackmarket_surprises`
                LEFT JOIN `items` ON (`items`.`id` = `blackmarket_surprises`.`reward_item_id`) 
                LEFT JOIN `log_specialSurprisePurchases` ON (`log_specialSurprisePurchases`.`reward_id` = `blackmarket_surprises`.`id`) 
                WHERE 
                    `isProfessionEntry` = '".$isProfessionEntry."'".$professionSelect." ".$extraSelection."
                GROUP BY `blackmarket_surprises`.`id`
                ORDER BY `cost_type`,`cost_amount`,`cost_item_Number`,`profession` ASC");               
               
        // The array to be shown
        $showArray = array();
        
        // Do calculations on all the entries
        if( $surprises !== "0 rows" ){
            
            // Group ID
            $gID = 1;
            $showArray[] = $this->getTableTitle($isProfession, $gID);
            
            // Run a pre-screen
            $lastGroup = $this->getGroup($surprises[0]);           
            $totalPoints = array();
            
            // Go through all the surprises
            foreach( $surprises as $key => $surprise ){
                
                // Get the active group of this surprise
                $activeGroup = $this->getGroup($surprise);
                
                // Get total active points
                if( functions::checkStartEndDates($surprise) ){
                    if( !array_key_exists($activeGroup, $totalPoints) ){
                        $totalPoints[ $activeGroup ] = 0;
                    }
                    $totalPoints[ $activeGroup ] += $surprise['frequency'];
                }
                
                // Check if new grouping
                if( $activeGroup !== $lastGroup ){
                    
                    // Insert a subtitle
                    $gID += 1;
                    $showArray[] = $this->getTableTitle($isProfession, $gID);
                }

                
                // Update latest group
                $lastGroup = $activeGroup;
                
                // Add to show array
                $showArray[] = $surprises[$key];
            }
            
            // Run a correction step on final array
            foreach( $showArray as $key => $surprise ){
                
                // Dont do anything to subtitles
                if( !array_key_exists("TP_subtitle", $surprise) ){
                    
                    // Get the active group of this surprise
                    $activeGroup = $this->getGroup($surprise);

                    // Base chance is 0
                    $showArray[$key]['chance'] = 0;
                    $showArray[$key]['cost_id'] = false;
                    
                    // Check dates
                    if( functions::checkStartEndDates($surprise) ){
                        $showArray[$key]['chance'] = round(100*$surprise['frequency'] / $totalPoints[ $activeGroup ]);                        
                    }

                    // If the cost is an item, show the item name
                    if( $surprise['cost_type'] == "item" ){
                        try{
                            $costName = $this->checkItem( $surprise['cost_amount'] );
                            $showArray[$key]['cost_id'] = $surprise['cost_amount'];
                            $showArray[$key]['cost_amount'] = $costName;                            
                        } catch (Exception $ex) {
                            $showArray[$key]['cost_amount'] = "<font color='red'>INVALID ID</font>";;
                        }
                    }
                    
                    // Check how many left
                    if( $surprise['currentCount'] >= $surprise['total_limit'] ){
                        
                        // 0% chance
                        $showArray[$key]['chance'] = 0;                        
                        $showArray[$key]['currentCount'] = "<font color='red'>".$showArray[$key]['currentCount']."</font>";
                    }
                    
                    // Change chance display to 0 if needed
                    if( $showArray[$key]['chance'] == 0){
                        $showArray[$key]['chanceDisplay'] = "<font color='red'>0%</font>";
                    }
                    else{
                        $showArray[$key]['chanceDisplay'] = "<font color='green'>".$showArray[$key]['chance']."%</font>";
                    }
                }
            }            
        }
        
        // return the final array
        return $showArray;
    }
    
    // Check if event is live
    protected function isTeamMember(){
        return in_array(
            $GLOBALS['userdata'][0]['user_rank'], 
            array("Admin","Event","EventMod","ContentAdmin")
        );
    }
    
    // Check if user is event team
    protected function isEventTeam(){
        return in_array(
            $GLOBALS['userdata'][0]['user_rank'], 
            array("Event","EventMod","ContentAdmin")
        );
    }
    
    // Get a cleaned list of black market entries, containing only valid entries
    public function getSurprisePacks( $isProfession = false , $professionData = false ){
        
        // Get uncleaned list
        $surprises = $this->getSpecialSurprises( $isProfession , $professionData );
        $surprisePacks = array();
        if( !empty($surprises) ){

            // Get surprise IDs
            $sIDs = array();
            $keyMap = array();
            foreach( $surprises as $key => $s ){
                if(!array_key_exists("TP_subtitle", $s) ){
                    $sIDs[] = $s['id'];
                    $keyMap[ $s['id'] ] = $key;
                }
            }
            
            // Get user counts
            $usersurprises = $GLOBALS['database']->fetch_data("
                SELECT 
                    `reward_id`, 
                    SUM( `log_specialSurprisePurchases`.`reward_count` ) as `sessionCount`
                FROM `log_specialSurprisePurchases`
                WHERE 
                    `uid` = '".$_SESSION['uid']."' AND 
                    `reward_id` IN ('".implode("','", $sIDs )."')
                GROUP BY `reward_id`");
            if( $usersurprises !== "0 rows"){
                foreach( $usersurprises as $userCount ){
                    $surprises[ $keyMap[$userCount['reward_id']] ]['sessionCount'] = $userCount['sessionCount'];
                }
            }
            
            // Get the surprise packs
            $groupID = 0;
            foreach( $surprises as $s ){
                if(array_key_exists("TP_subtitle", $s) ){
                    $groupID += 1;
                    $surprisePacks[ $groupID ] = array();                    
                }
                elseif( 
                    $s['chance'] > 0 && // Chance above 0                    
                    (   // No more items than limit
                        !isset($s['sessionCount'] ) || 
                        $s['sessionCount'] + $s['min_amount'] <= $s['user_limit'] 
                    ) &&
                    (   // Check if item is live or not
                        $s['isLive'] == 'yes' ||
                        ( $this->isTeamMember() )
                    )
                ){
                    $surprisePacks[ $groupID ][] = $s;
                }
            }
        }
        
        // Remove empty groups
        $surprisePacks = array_filter($surprisePacks);
        
        // echo"<pre />";
        // print_r($surprisePacks);
        
        // Return the surprises
        return $surprisePacks;
    }
}