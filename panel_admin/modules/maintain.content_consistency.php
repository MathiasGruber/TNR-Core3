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

// Links up to the development account, and 
// check the database consistency against the main one.
class DevelopmentConsistency {

    public function __construct() {
        
        // Try and catch them all
        try{
            
            // Array for the differences
            $this->differences = array();
            
            // ID columns
            $this->idColumns = array(
                "ai" => "id",
                "bloodlines" => "entry_id",
                "jutsu" => "id",
                "tasksAndQuests" => "id",
                "items" => "id"
            );
            
            // Tables for which data on two servers should be identical
            if (!isset($_GET['act'])) {
                $this->main_screen();
            } elseif (in_array($_GET['act'], array("ai","jutsu","bloodlines","tasksAndQuests","items")) ) {
                $this->identicalTables = array($_GET['act']);
                $this->mainPage();
            }
            
        } catch (Exception $ex) {
            
            // There was an error; probably connecting to DB
            $GLOBALS['page']->Message($ex->getMessage(), 'Development Server Consistency System', 'id=' . $_GET['id']);
        }
    }
    
    private function main_screen() {  
        
        $menu = array(
            array( "name" => "AI", "href" => "?id=".$_GET['id']."&act=ai"),
            array( "name" => "Jutsu", "href" => "?id=".$_GET['id']."&act=jutsu"),
            array( "name" => "Bloodline", "href" => "?id=".$_GET['id']."&act=bloodlines"),
            array( "name" => "Tasks/Quests/Missions", "href" => "?id=".$_GET['id']."&act=tasksAndQuests"),
            array( "name" => "Items", "href" => "?id=".$_GET['id']."&act=items"),
        );
        $GLOBALS['template']->assign('subHeader', 'Content Consistency');
        $GLOBALS['template']->assign('nCols', 2);
        $GLOBALS['template']->assign('nRows', 3);
        $GLOBALS['template']->assign('subTitle', 'This panel can be to check content consistency between development domain and main domain.');
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');        
    }
    
    // Do everything
    private function mainPage(){
        
        // Set data depending on if on dev or main
        if( isset(Data::$target_site) && Data::$target_site === 'TND_' ){
            $this->MYSQL_SERVER = 'a4d1a923d4265cb0f86ab3c2f5c374b3c95c473f.rackspaceclouddb.com';
            $this->MYSQL_USER = 'core3_user';
            $this->MYSQL_PASS = '4EYBBdTvE2uk4eg5cASz';
            $this->MYSQL_DEFAULT_DB = 'core3_db';
            $this->LOCAL_DEFAULT_DB = '860010_core3';
        }
        else{
            $this->MYSQL_SERVER = 'mysql51-002.wc2.dfw1.stabletransit.com';
            $this->MYSQL_USER = '860010_core3';
            $this->MYSQL_PASS = '5Erf2ZBXujvwt3v8kdtB';
            $this->MYSQL_DEFAULT_DB = '860010_core3';
            $this->LOCAL_DEFAULT_DB = 'core3_db';
        }
        
        // Get the tables from this database
        $information_schema = $GLOBALS['database']->fetch_data("
            SELECT * FROM `information_schema`.COLUMNS
            WHERE `table_schema` = '".$this->LOCAL_DEFAULT_DB."'
            ORDER BY `table_name`,`ordinal_position`");        
        $mainData = $this->formatData($information_schema);
        
        // Get the data from the development database        
        $mysqli = new mysqli($this->MYSQL_SERVER, $this->MYSQL_USER, $this->MYSQL_PASS);
        if (mysqli_connect_errno()) {
            throw new Exception("Connection to dev error: ".mysqli_connect_error());
        }
        $mysqli->select_db($this->MYSQL_DEFAULT_DB);
        
        // Get information schema on other server
        $information_schema = $mysqli->query("
            SELECT * FROM `information_schema`.COLUMNS
            WHERE `table_schema` = '".$this->MYSQL_DEFAULT_DB."'
            ORDER BY `table_name`,`ordinal_position`");
        
        // Get the identical tables data on other server
        $this->identicalTablesData = array();
        foreach( $this->identicalTables as $table ){
            
            // Get and save data
            $this->identicalTablesData[ $table ] = array();
            $data = $mysqli->query("SELECT * FROM ".$table);
            while($row = $data->fetch_assoc()){
                $this->identicalTablesData[ $table ][] = $row;
            }
        }
        
        $mysqli->close();
        $info = array();
        while($row = $information_schema->fetch_assoc()){
            $info[] = $row;
        }
        $devData = $this->formatData($info);
        
        // Get differences between two tables
        $this->setEntries( $mainData, $devData );
        
        // Show the data
        tableParser::show_list(
                "consistencyInfo", 
                "Content Consistency Check", 
                $this->differences,
                array(
                    'title' => "Message",
                    'mainInfo' => "Local Server",
                    'devInfo' => "External Server"
                ), 
                false, true, true, false, false, false, false, 
                "Checks differences between production server and dev server database."
        );
    }
    
    // Get differences between two databases
    private function setEntries( $mainData, $devData ){
       
        // Check the identical tables
        foreach( $this->identicalTables as $table ){
            
            // Get the data
            $localData = $GLOBALS['database']->fetch_data("SELECT * FROM ".$table);
            $externalData = $this->identicalTablesData[ $table ];
            
            // List of all the IDs
            $allIDs = array();
            
            // Sort the data based on DB-ids instead
            $sortedLocalData = array();
            foreach( $localData as $entry ){
                $id = $this->idColumns[$table];
                $allIDs[] = $entry[ $id ];
                $sortedLocalData[ $entry[ $id ] ] = $entry;
            }
            $sortedExternalData = array();
            foreach( $externalData as $entry ){
                $id = $this->idColumns[$table];
                $allIDs[] = $entry[ $id ];
                $sortedExternalData[ $entry[ $id ] ] = $entry;
            }
            
            // Go through the data, each way
            foreach( $allIDs as $id ){
                if( !isset($sortedExternalData[$id]) ){
                    $this->addEntry("Inconsistent data in table: ".$table, print_r($sortedLocalData[$id], true), "MISSING" , "darkred");
                }
                elseif( !isset($sortedLocalData[$id]) ){
                    $this->addEntry("Inconsistent data in table: ".$table, "MISSING" , print_r($sortedExternalData[$id], true), "darkred");
                }
                elseif ( $sortedLocalData[$id] !== $sortedExternalData[$id] ){
                    $this->addEntry(
                            "Inconsistent data in table: ".$table, 
                            $this->displayArrayDiffs( $sortedLocalData[$id], $sortedExternalData[$id]), 
                            $this->displayArrayDiffs( $sortedLocalData[$id], $sortedExternalData[$id], 2), 
                            "#F88017"
                    );
                }
            }
        }
    }
    
    // Get differences between two arrays, return values from either table 1 or 2
    private function displayArrayDiffs( $array1, $array2, $returnNr = 1 ){
        $returnHTML = "";
        foreach( $array1 as $key1 => $val1 ){
            if( $val1 !== $array2[ $key1 ] || in_array($key1,$this->idColumns) || $key1 == "name" ){
                if( $returnNr == 1 ){
                    $returnHTML .= $key1 . " - " . $val1 . "<br>";
                }  
                else{
                    $returnHTML .= $key1 . " - " . $array2[ $key1 ] . "<br>";
                }
            }
        }
        return $returnHTML;
    }
    
    // Function for adding a new difference
    private function addEntry( $name, $mainTxt, $devTxt, $color ){
        $this->differences[] = array(
            "title" => "<font color='".$color."'>".$name."</font>",
            "mainInfo" => "<font color='".$color."'>".$mainTxt."</font>",
            "devInfo" => "<font color='".$color."'>".$devTxt."</font>"
        );
    }
    
    // Format the data in a better format based on table
    private function formatData( $information_schema ){
        $data = array();
        foreach( $information_schema as $entry ){
            
            // Save table name
            if( !array_key_exists($entry['TABLE_NAME'], $data) ){
                $data[$entry['TABLE_NAME']] = array();
            }
            
            // Save entry
            $data[$entry['TABLE_NAME']][] = $entry;
        }
        return $data;
    }
}

new DevelopmentConsistency();