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
            
            // Tables for which data on two servers should be identical
            $this->identicalTables = array("pages","site_information","site_timer","levels");
            
            // Show the main page
            $this->mainPage();
            
        } catch (Exception $ex) {
            
            // There was an error; probably connecting to DB
            $GLOBALS['page']->Message($ex->getMessage(), 'Development Server Consistency System', 'id=' . $_GET['id']);
        }
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
                "Development Database Server Consistency", 
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
        
        // Check the lengths of the two first
        if( count($mainData) !== count($devData) ){
            $this->addEntry("Inconsistent number of tables", "Tables on main: ".count($mainData), "Tables on dev: ".count($devData), "darkred" );
        }
        else{
            $this->addEntry("Consistent number of tables", "Tables on main: ".count($mainData), "Tables on dev: ".count($devData), "darkgreen" );
        }
        
        // Get differences looping through production data
        $mainToDev = $this->getDatabaseDifferences($mainData, $devData, "Main", "Dev");
        foreach( $mainToDev as $entry ){
            $this->addEntry($entry["title"], $entry["data1"], $entry["data2"] , "darkred");
        }
        
        // Get differences looping through development data
        $devToMain = $this->getDatabaseDifferences($devData, $mainData, "Dev", "Main");
        foreach( $devToMain as $entry ){
            $this->addEntry($entry["title"], $entry["data2"], $entry["data1"] , "darkred");
        }    
        
        // Check the identical tables
        foreach( $this->identicalTables as $table ){
            
            // Get the data
            $localData = $GLOBALS['database']->fetch_data("SELECT * FROM ".$table);
            $externalData = $this->identicalTablesData[ $table ];
            
            // Go through the data, each way
            foreach( $localData as $key => $value ){
                if( !isset($externalData[$key]) ){
                    $this->addEntry("Inconsistent data in table: ".$table, print_r($value, true), "MISSING" , "darkred");
                }
                elseif ( $value !== $externalData[$key] ){
                    $this->addEntry("Inconsistent data in table: ".$table, print_r($value, true), print_r($externalData[$key], true) , "#F88017");
                }
            }
            
        }
    }
    
    // Get differences between two datasets
    private function getDatabaseDifferences( $data1, $data2, $data1name, $data2name ){
        
        // Save differences between two datasets here
        $differences = array();
        
        // Go through the data1 and compare to data2
        foreach($data1 as $data1table => $tableColumns){
            
            // Check if the table exists in data2
            if( array_key_exists($data1table, $data2) ){
                
                // Go through all table columns in data1
                foreach($tableColumns as $key => $columnData){
                    
                    // Check if column exists in data2
                    if( array_key_exists($key, $data2[$data1table]) ){
                        
                        // Check a set of the columns
                        foreach( array("COLUMN_NAME","ORDINAL_POSITION","COLUMN_TYPE","IS_NULLABLE","COLUMN_DEFAULT","DATA_TYPE") as $entryKey ){
                            if( $columnData[$entryKey] !== $data2[$data1table][$key][$entryKey] ){
                                $differences[] = array("title" => "Mitchmatch! Table: ".$data1table.", column: ".$columnData['COLUMN_NAME'], "data1" => $columnData[$entryKey], "data2" => $data2[$data1table][$key][$entryKey]);
                            }
                        }
                    }
                    else{
                        
                        // Missing a column here
                        $differences[] = array("title" => "Column Missing! Table: ".$data1table.", column: ".$columnData['COLUMN_NAME'], "data1" => implode(" ",$columnData), "data2" => "MISSING!");
                    }
                }
            }
            else{
                $differences[] = array("title" => "Non-existance table on: ".$data2name, "data1" => $data1table, "data2" => "N/A");
            }
        }
        return $differences;
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