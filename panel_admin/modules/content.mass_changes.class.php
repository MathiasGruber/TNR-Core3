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

class mass_edits {

    public function __construct() {
        try{
            if (!isset($_GET['act'])) {
                $this->main_screen();
            } elseif ($_GET['act'] == 'jutsuBase') {
                if (!isset($_POST['Submit'])) {
                    $this->generalChangeForm("Change the base value in DAM tag of ALL jutsus");
                } else {
                    $this->changeJutsuBase();
                }
            } elseif ($_GET['act'] == 'jutsuIncrement') {
                if (!isset($_POST['Submit'])) {
                    $this->generalChangeForm("Change the increment value in the DAM tag of ALL jutsus");
                } else {
                    $this->changeJutsuIncrement();
                }
            } elseif ($_GET['act'] == 'missionRewards') {
                if (!isset($_POST['Submit'])) {
                    $this->missionForm();
                } else {
                    $this->changeMissionRewards("rank");
                }
            } elseif ($_GET['act'] == 'tasksAndQuests') {
                if (!isset($_POST['Submit'])) {
                    $this->taskForm();
                } else {
                    $this->changeMissionRewards("task");
                }
            } elseif ($_GET['act'] == 'aiStats') {
                if (!isset($_POST['Submit'])) {
                    $this->aiChangeForm();
                } else {
                    $this->changeAiStats();
                }
            } elseif ($_GET['act'] == 'excelUpload') {
                if (!isset($_POST['Submit'])) {
                    $this->excelUploadForm();
                } else {
                    $this->excelUploadReview();
                }
            }
        }  catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Mass Content Changes', 'id=' . $_GET['id'] );
        }
    }

    private function main_screen() {  
        
        $menu = array(
            array( "name" => "Jutsu DAM Base Values", "href" => "?id=".$_GET['id']."&act=jutsuBase"),
            array( "name" => "Jutsu DAM Increment Values", "href" => "?id=".$_GET['id']."&act=jutsuIncrement"),
            array( "name" => "Jutsu Excel Sheet Upload", "href" => "?id=".$_GET['id']."&act=excelUpload"),
            array( "name" => "Mission/Crimes Rewards", "href" => "?id=".$_GET['id']."&act=missionRewards"),
            array( "name" => "AI Stats", "href" => "?id=".$_GET['id']."&act=aiStats"),
            array( "name" => "Tasks N' Quests", "href" => "?id=".$_GET['id']."&act=tasksAndQuests")
        );
        $GLOBALS['template']->assign('subHeader', 'Mass Content Edits');
        $GLOBALS['template']->assign('nCols', 2);
        $GLOBALS['template']->assign('nRows', 3);
        $GLOBALS['template']->assign('subTitle', 'This panel can be used for large content edits; i.e. system-wide changes such as change base jutsu values');
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');        
    }
    
    // Change the jutsu base of all jutsus
    private function excelUploadReview(){

        if(isset($_FILES['excelFile'])){
            if($_FILES['excelFile']['tmp_name']){
                if(!$_FILES['excelFile']['error'])
                {
                    
                    // Get PHPExcel
                    require_once(Data::$absSvrPath.'/global_libs/excelReader/PHPExcel.php');
                    
                    // Input file data
                    $inputFile = $_FILES['excelFile']['tmp_name'];
                    $inputName = $_FILES['excelFile']['name'];
                    $extension = strtoupper(pathinfo($inputName, PATHINFO_EXTENSION));
                    if($extension == 'XLSX' || $extension == 'XLS' || $extension == 'ODS'){

                        // IOnfo for user
                        $buffer = "<i>Retrieving jutsu name in column 'A' and effects from columns named 'ActiveEffect1','ActiveEffect2','ActiveEffect3' and 'ActiveEffect4'</i><br>"
                                . "ALSO note that the element should not contain FORMULAS. E.g. paste use paste-as-value, see following link:<br>"
                                . "https://www.ablebits.com/office-addins-blog/2013/12/13/excel-convert-formula-to-value/<br><br>"
                                . "<i>The entries found are color coded as follows:</i><br>"
                                . "Black: new effect from file, replacing effect in database<br>"
                                . "<span style='color:blue;'>Blue: no new effect from file, keeping effect from database</span><br>"
                                . "<span style='color:green;'>Green: effect from file and database are the same</span><br>"
                                . "<span style='color:red;'>Red: effect on file seems suspecious and might be wrong</span>";
                        
                        //Read spreadsheeet workbook                        
                        $inputFileType = PHPExcel_IOFactory::identify($inputFile);
                        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                        $objReader->setReadDataOnly(true);
                        //$objReader->setLoadAllSheets();
                        $objPHPExcel = $objReader->load($inputFile);
                        
                        // Get all jutsus
                        $jutsus = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu`");
                        
                        // Go through all the worksheets
                        $worksheetNames = $objPHPExcel->getSheetNames($inputFile);
                        $return = array();
                        //print_r($worksheetNames);
                        //echo":====";
                        foreach($worksheetNames as $key => $sheetName){
                            
                            //set the current active worksheet by name
                            $buffer .= "<br><h1>Handling the sheet: ".$sheetName."</h1>";
                            $objPHPExcel->setActiveSheetIndexByName($sheetName);
                            
                            //create an assoc array with the sheet name as key and the sheet contents array as value
                            $sheet = $objPHPExcel->getActiveSheet()->toArray(null, true,true,true);
                            
                            // Check the first row
                            $name = $effect1 = $effect2 = $effect3 = $effect4 = "";
                            foreach( $sheet[1] as $k => $v ){
                                switch( $v ){
                                    case "ActiveEffect1": $effect1 = $k; break;
                                    case "ActiveEffect2": $effect2 = $k; break;
                                    case "ActiveEffect3": $effect3 = $k; break;
                                    case "ActiveEffect4": $effect4 = $k; break;
                                }
                            }
                            
                            // Go through all the rows
                            foreach( $sheet as $row ){
                                $name = $row["A"];
                                
                                // Check if has jutsu
                                $hasJutsu = false;
                                foreach( $jutsus as $jutsu ){
                                    if( $jutsu['name'] === $name ){
                                        $hasJutsu = $jutsu;
                                    }
                                }
                                      
                                // Run the check
                                if( $hasJutsu !== false ){

                                    $buffer .= "<br><br>Jutsu <b>".$name."</b> identified. <br>";
                                    for( $i=1; $i<=4; $i++ ){
                                        
                                        // New and old effect
                                        $oldEffect = !empty($hasJutsu['effect_'.$i]) ? $hasJutsu['effect_'.$i] : "";
                                        $newEffect = isset($row[${"effect".$i}]) && !empty($row[${"effect".$i}]) ? $row[${"effect".$i}] : "";
                                        
                                        // Check method
                                        if( !empty($newEffect) && strlen($newEffect) <= 5 ){
                                            $buffer .= "<span style='color:red;'>- OldEffect ".$i.": <i>".$oldEffect."</i> - NewEffect ".$i.": <i>".$newEffect."</i></span><br>";
                                        }elseif( empty($newEffect) && $oldEffect !== $newEffect ){
                                            $buffer .= "<span style='color:blue;'>- OldEffect ".$i.": <i>".$oldEffect."</i> - NewEffect ".$i.": <i>".$newEffect."</i></span><br>";
                                        }                                        
                                        elseif( $oldEffect == $newEffect ){
                                            $buffer .= "<span style='color:green;'>- OldEffect ".$i.": <i>".$oldEffect."</i> - NewEffect ".$i.": <i>".$newEffect."</i></span><br>";
                                            $hasJutsu['effect_'.$i] = $newEffect;
                                        }
                                        elseif( !empty($newEffect) && $oldEffect !== $newEffect ){
                                            $buffer .= "- OldEffect ".$i.": <i>".$oldEffect."</i> - NewEffect ".$i.": <i>".$newEffect."</i><br>";
                                            $hasJutsu['effect_'.$i] = $newEffect;
                                        }
                                        
                                        // Do the database update
                                        $GLOBALS['database']->execute_query("
                                            UPDATE `jutsu` 
                                            SET `effect_1` = '".$hasJutsu['effect_1']."',
                                                `effect_2` = '".$hasJutsu['effect_2']."',
                                                `effect_3` = '".$hasJutsu['effect_3']."',
                                                `effect_4` = '".$hasJutsu['effect_4']."'
                                            WHERE `id` = '".$hasJutsu['id']."'
                                            LIMIT 1
                                        ");                                        
                                    }
                                }                                
                            }                            
                        }
                        
                        // Show message
                        $GLOBALS['page']->Message($buffer, 'Mass Content Changes', 'id=' . $_GET['id'] );
                    }
                    else{
                        throw new Exception("File: ".$inputFile.". Extension uploaded: ".$extension.". Only support extensions are: XLS, XLSX, and ODS");
                    }
                }
                else{
                    throw new Exception("Error: ".$_FILES['excelFile']['error']);
                }
            }
            else{
                throw new Exception("No temp name of file found?");
            }
        }
        else{
            throw new Exception("Did not submit a file?");
        }
        
        // Get the reader
        
        // $xls = new Spreadsheet_Excel_Reader("example.xls");

        
    }
    
    // Change the jutsu base of all jutsus
    private function changeMissionRewards( $type ){

        // Show buffer
        $buffer = "";
        
        // Get the missions
        if( $type == "rank" ){
            $desc = "Rank: ".$_POST['rank'];
            $entries = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `type` = 'mission_".$_POST['rank']."' OR  `type` = 'crime_".$_POST['rank']."'");
        }
        elseif( $type == "task" ){
            $desc = "Ids: ".$_POST['tasks'];
            $ids = explode(",", str_replace(" ","",$_POST['tasks']));
            $entries = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `id` IN (".implode(",",$ids).")");
        }
        
        foreach( $entries as $entry ){
            
            // First do "rewards"-column
            $buffer .= "<br><br><b>".$entry['name']."</b><br>"
                    . "<b>Old rewards-entry:</b> ".$entry['rewards']."<br>"
                    . "<b>New rewards-entry:</b> ".$_POST['rewardsColumn'];
            
            // Then do the simply guide
            $buffer .= "<br><br>"
                    . "<b>Old simpleGuide:</b> ".$entry['simpleGuide'];
            
            // Remove old rew
            $newGuide = array();
            $temp = explode(";", $entry['simpleGuide']);
            foreach( $temp as $guideEntry ){
                if( stristr( $guideEntry, "req:" ) || stristr( $guideEntry, "info:" ) || stristr( $guideEntry, "complete:" )|| stristr( $guideEntry, "battle:" )  ){
                    $newGuide[] = $guideEntry;
                }
            }
            
            // Add new rew
            $newEntries = explode(";", $_POST['simpleGuide']);
            foreach( $newEntries as $guideEntry ){
                $newGuide[] = $guideEntry;
            }
            
            // Implode
            $newGuide = implode(";",$newGuide);
            
            // Show new simpleGuide
            $buffer .= "<br><b>New simpleGuide:</b> ".$newGuide;
            
            // Update task entry
            $GLOBALS['database']->execute_query("UPDATE `tasksAndQuests` SET `simpleGuide` = '". addslashes($newGuide)."', `rewards` = '".$_POST['rewardsColumn']."' WHERE  id = ".$entry["id"]." LIMIT 1");

        }
        
        // Log the change
        $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
        (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Mass Edit','','Mission Rewards Changed - ".$desc.". Rewards: ".$_POST['rewardsColumn']." - Guide: ".$newGuide."')");            
        
        // Show message
        $GLOBALS['page']->Message($buffer, 'Mass Content Changes', 'id=' . $_GET['id'] );
    }
    
    // Change the jutsu base of all jutsus
    private function changeAiStats(){
        
        // Do the select on the AIs
        $select = "";
        if( !empty($_POST['level']) && $_POST['level'] !== "all"){
            $select = "WHERE `level` = '".$_POST['level']."'";
        }
        if( !empty($_POST['type']) && $_POST['type'] !== "all"){
            $select .= empty($select) ? "WHERE `type` = '".$_POST['type']."'" : " AND `type` = '".$_POST['type']."'";
        }        
        if( !empty($_POST['location']) && $_POST['location'] !== "all"){
            $select .= empty($select) ? "WHERE `location` = '".$_POST['location']."'" : " AND `location` = '".$_POST['location']."'";
        }
        
        // Get the AIs
        $ais = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` ".$select);
        
        $buffer = "";
        foreach( $ais as $ai ){
            
            // Start update query
            $query = "UPDATE ai SET ";
            
            // Offensive
            if( !empty($_POST['off_stats']) ){
                $query .= "`tai_off` = '".round($this->changeValue($ai['tai_off'], $_POST['off_stats']),2)."',"
                        . "`nin_off` = '".round($this->changeValue($ai['nin_off'], $_POST['off_stats']),2)."',"
                        . "`gen_off` = '".round($this->changeValue($ai['gen_off'], $_POST['off_stats']),2)."',"
                        . "`weap_off` = '".round($this->changeValue($ai['weap_off'], $_POST['off_stats']),2)."',";   
            }
            
            // Defensive
            if( !empty($_POST['def_stats']) ){
                $query .= "`tai_def` = '".round($this->changeValue($ai['tai_def'], $_POST['def_stats']),2)."',"
                        . "`nin_def` = '".round($this->changeValue($ai['nin_def'], $_POST['def_stats']),2)."',"
                        . "`gen_def` = '".round($this->changeValue($ai['gen_def'], $_POST['def_stats']),2)."',"
                        . "`weap_def` = '".round($this->changeValue($ai['weap_def'], $_POST['def_stats']),2)."',";   
            }
            
            // Gens
            if( !empty($_POST['generels']) ){
                $query .= "`strength` = '".round($this->changeValue($ai['strength'], $_POST['generels']),2)."',"
                        . "`intelligence` = '".round($this->changeValue($ai['intelligence'], $_POST['generels']),2)."',"
                        . "`willpower` = '".round($this->changeValue($ai['willpower'], $_POST['generels']),2)."',"
                        . "`speed` = '".round($this->changeValue($ai['speed'], $_POST['generels']),2)."',";   
            }
            
            // HP/chakra/armor
            if( !empty($_POST['hp']) ){ $query .= "`life` = '".round($this->changeValue($ai['life'], $_POST['hp']),2)."',"; }
            if( !empty($_POST['chakra']) ){ $query .= "`chakra` = '".round($this->changeValue($ai['chakra'], $_POST['chakra']),2)."',"; }
            if( !empty($_POST['armor']) ){ $query .= "`armor` = '".round($this->changeValue($ai['armor'], $_POST['armor']),2)."',"; }
            
            // To make sure query is right (commas from previous)
            $query .= "`id`=`id` WHERE `id` = '".$ai['id']."'";
            
            // Get tag
            if( $GLOBALS['database']->execute_query( $query ) == false )
            {
                // throw new Exception("There was an error with this query: ".$query);
            }

            // Get the new AI data
            $newAI = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = '".$ai["id"]."'");
            
            // Info for user
            $buffer .= "<br><b>".$ai['name']."<br>Old Data:</b><br> <i>".print_r($ai, true)."</i><br><b> New data:</b><br><i> ".print_r($newAI,true)."</i><br>";
                
        }
        
        // Log the change
        $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
        (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Mass Edit','','AI Stats Changed - ".$_POST['action']." with AI levels: ".$_POST['level'].". POST-dump: ".print_r($_POST, true)."')");            
        
        // Show message
        $GLOBALS['page']->Message($buffer, 'Mass Content Changes', 'id=' . $_GET['id'] );
    }
    
    // Change the jutsu base of all jutsus
    private function changeJutsuIncrement(){

        $buffer = "";
        $jutsus = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE (`effect_1` LIKE 'DAM%' OR `effect_2` LIKE 'DAM%' OR `effect_3` LIKE 'DAM%' OR `effect_4` LIKE 'DAM%')");
        foreach( $jutsus as $jutsu ){

            // Go through each effect
            foreach( array("effect_1","effect_2","effect_3","effect_4") as $column ){
                if( preg_match("/^DAM.CALC.+$/", $jutsu[$column])  ){
                    
                    // Get tag
                    $temp = explode( ":", $jutsu[$column] );
                    
                    // Old & new values
                    $oldInc = $temp[3];
                    $newInc = round($this->changeValue($temp[3], $_POST['factorValue']),2);
                    
                    // New tag
                    $temp[3] = $newInc;
                    $newTag = implode(":", $temp);
                    
                    // Update jutsu
                    $GLOBALS['database']->execute_query("UPDATE jutsu SET `".$column."` = '".$newTag."' WHERE  id = ".$jutsu["id"]." LIMIT 1");
                    
                    // Info for user
                    $buffer .= "<br>".$jutsu['name']." Increment - Old: ".$oldInc." - New: ".$newInc." -- New Tag: ".$newTag;
                }
            }
        }
        
        // Log the change
        $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
        (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Mass Edit','','Jutsu Increment Changed - ".$_POST['action']." with value: ".$_POST['factorValue']."')");            
        
        // Show message
        $GLOBALS['page']->Message($buffer, 'Mass Content Changes', 'id=' . $_GET['id'] );
    }
    
    // For changing jutsu base tag
    private function changeDamTag( $oldTag ){
        
        // Get tag
        $temp = explode( ":", $oldTag );

        // Old & new values
        $oldBase = $temp[2];
        $newBase = floor($this->changeValue($temp[2], $_POST['factorValue']));

        // New tag
        $temp[2] = $newBase;
        $newTag = implode(":", $temp);
        
        return $newTag;
    }
    
    // Change the jutsu base of all jutsus
    private function changeJutsuBase(){

        $buffer = "";
        $jutsus = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE (`effect_1` LIKE '%DAM%' OR `effect_2` LIKE '%DAM%' OR `effect_3` LIKE '%DAM%' OR `effect_4` LIKE '%DAM%')");
        foreach( $jutsus as $jutsu ){

            // Go through each effect
            foreach( array("effect_1","effect_2","effect_3","effect_4") as $column ){
                
                
                // If it's a split jutsu, handle each tag independently
                if( $jutsu['splitJutsu'] == "yes" ){
                    
                    // Create a new tag                    
                    $newTag = $jutsu[$column];
                    
                    foreach( array("N","T","W","G") as $type ){
                        preg_match( "/".$type."\{(.+)\}/" , $jutsu[ $column ] , $match );
                        if( !empty($match) ){
                            if( preg_match("/^DAM.CALC.+$/", $match[1])  ){
                                $newDamTag = $this->changeDamTag($match[1]);
                                $newTag = str_replace($match[1], $newDamTag, $newTag);
                            }
                        }
                    }
                    
                    // Only do update if updated
                    if( $newTag !== $jutsu[$column] ){
                        
                        // Update jutsu
                        $GLOBALS['database']->execute_query("UPDATE jutsu SET `".$column."` = '".$newTag."' WHERE  id = ".$jutsu["id"]." LIMIT 1");
                        
                        // For user
                        $buffer .= "<br>".$jutsu['name']." Base Change - Old tag: ".$jutsu[$column]." --- New Tag: ".$newTag;
                    }
                    
                    
                }
                elseif( preg_match("/^DAM.CALC.+$/", $jutsu[$column])  ){
                    
                    // Get new jutsu tag
                    $newTag = $this->changeDamTag($jutsu[$column]);
                    
                    // Update jutsu
                    $GLOBALS['database']->execute_query("UPDATE jutsu SET `".$column."` = '".$newTag."' WHERE  id = ".$jutsu["id"]." LIMIT 1");
                    
                    // Info for user
                    $buffer .= "<br>".$jutsu['name']." Base Change - Old tag: ".$jutsu[$column]." --- New Tag: ".$newTag;
                }
            }
        }
        
        // Log the change
        /*
        $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
        (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Mass Edit','','Jutsu Base Changed - ".$_POST['action']." with value: ".$_POST['factorValue']."')");            
        */
        
        // Show message
        $GLOBALS['page']->Message($buffer, 'Mass Content Changes', 'id=' . $_GET['id'] );
    }
    
    // Function for modifying value
    private function changeValue( $originalValue , $factor ){
        switch( $_POST['action'] ){
            case "add": return $originalValue + $factor; break;
            case "subtract": return $originalValue - $factor; break;
            case "multiply": return $originalValue * $factor; break;
            case "raise": return pow($originalValue, $factor); break;
            case "set": return $factor; break;
            default: throw new Exception("Could not identify action");
        }
    }
    
    /* FORMS              */
    /* ****************** */
    
    private function excelUploadForm(){
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "inputFieldName"=>"excelFile",
                "type"=>"file"
            )
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Update jutsus using a excel spread sheet. We've tried to make the script as adaptable as possible, 
             but please keep the excel sheets as close to <a href='http://theninja-development.com/docs/JutsuInputSampleV1.xlsx'>sample1</a> and <a href='http://theninja-development.com/docs/JutsuInputSampleV2.xlsx'>sample2</a> as possible, and be sure to go through
             the confirmation page checking what is being changed before submitting.", // Information
            "Mass Content Change", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id']."&act=".$_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Upload for Review"), // Submit button
            "Return" // Return link name
        );        
    }
    
    private function taskForm(){
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "infoText" => "Comma-separated task IDs: e.g. 'taskID1,taskID2,taskID3...'",
                "inputFieldName" => "tasks", 
                "type" => "input", 
                "inputFieldValue" => ""
            ),
            array(
                "infoText" => "'simpleGuide'-entry (only alter rew:... entries)",
                "inputFieldName" => "simpleGuide", 
                "type" => "textarea", 
                "inputFieldValue" => ""
            ),
            array(
                "infoText" => "'rewards'-column - enter the tags correctly please",
                "inputFieldName" => "rewardsColumn", 
                "type" => "textarea", 
                "inputFieldValue" => ""
            )
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Update multiple tasks based on their IDs", // Information
            "Mass Content Change", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id']."&act=".$_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Submit"), // Submit button
            "Return" // Return link name
        );        
    }
    
    private function missionForm(){
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "inputFieldName"=>"rank",
                "type"=>"select",
                "inputFieldValue"=> array(
                    "a" => "A-ranked",
                    "b" => "B-ranked",
                    "c" => "C-ranked",
                    "d" => "D-ranked"
                )
            ),
            array(
                "infoText" => "'simpleGuide'-entry (only alter rew:... entries)",
                "inputFieldName" => "simpleGuide", 
                "type" => "textarea", 
                "inputFieldValue" => ""
            ),
            array(
                "infoText" => "'rewards'-column",
                "inputFieldName" => "rewardsColumn", 
                "type" => "textarea", 
                "inputFieldValue" => ""
            )
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Update ALL missions of a given rank in the database", // Information
            "Mass Content Change", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id']."&act=".$_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Submit"), // Submit button
            "Return" // Return link name
        );        
    }
    
    private function generalChangeForm( $message ){
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "inputFieldName"=>"action",
                "type"=>"select",
                "inputFieldValue"=> array(
                    "set" => "Set to Value",
                    "add" => "Add Value",
                    "subtract" => "Subtract Value",
                    "multiply" => "Multiply Value",
                    "raise" => "Raise to Power of Value"
                )
            ),
            array("infoText"=>"Value","inputFieldName"=>"factorValue", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            $message, // Information
            "Mass Content Change", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id']."&act=".$_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Submit"), // Submit button
            "Return" // Return link name
        );        
    }
    
    private function aiChangeForm(){
        
        // Get the levels
        $levels = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` GROUP BY `level`");
        $levelOptions = array( "all" => "all" );
        foreach( $levels as $level ){
            $levelOptions[ $level['level'] ] = $level['level'];
        }
        
        // Get types
        $types = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` GROUP BY `type`");
        $typeOptions = array( "all" => "all" );
        foreach( $types as $type ){
            $typeOptions[ $type['type'] ] = $type['type'];
        }
        
        // Get locations
        $locations = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` GROUP BY `location`");
        $locOptions = array( "all" => "all" );
        foreach( $locations as $location ){
            $locOptions[ $location['location'] ] = $location['location'];
        }
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "nextLine"=>true, 
                "infoText"=>"Level to Edit (active in database)",
                "inputFieldName"=>"level",
                "type"=>"select",
                "inputFieldValue"=> $levelOptions
            ),
            array(
                "nextLine"=>true, 
                "infoText"=>"Type to Edit (active in database)",
                "inputFieldName"=>"type",
                "type"=>"select",
                "inputFieldValue"=> $typeOptions
            ),
            array(
                "nextLine"=>true, 
                "infoText"=>"Location to Edit (active in database)",
                "inputFieldName"=>"location",
                "type"=>"select",
                "inputFieldValue"=> $locOptions
            ),
            array(
                "nextLine"=>true, 
                "infoText"=>"Choose Method",
                "inputFieldName"=>"action",
                "type"=>"select",
                "inputFieldValue"=> array(
                    "set" => "Set to Value",
                    "add" => "Add Value",
                    "subtract" => "Subtract Value",
                    "multiply" => "Multiply Value",
                    "raise" => "Raise to Power of Value"
                )
            ),
            array("nextLine"=>true, "infoText"=>"HP edit","inputFieldName"=>"hp", "type" => "input", "inputFieldValue" => ""),
            array("nextLine"=>true, "infoText"=>"Chakra edit","inputFieldName"=>"chakra", "type" => "input", "inputFieldValue" => ""),
            array("nextLine"=>true, "infoText"=>"Generels edit","inputFieldName"=>"generels", "type" => "input", "inputFieldValue" => ""),
            array("nextLine"=>true, "infoText"=>"Off Stats edit","inputFieldName"=>"off_stats", "type" => "input", "inputFieldValue" => ""),
            array("nextLine"=>true, "infoText"=>"Def Stats edit","inputFieldName"=>"def_stats", "type" => "input", "inputFieldValue" => ""),
            array("nextLine"=>true, "infoText"=>"Armor edit","inputFieldName"=>"armor", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Select the AI level you want to change, and the way in which you want to alter the stats", // Information
            "Mass Content Change", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id']."&act=".$_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Submit"), // Submit button
            "Return" // Return link name
        );        
    }
    
    

}

new mass_edits();