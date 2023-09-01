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

abstract class tableParser {

    //    Returns the place to start query. Used e.g. in show_list()
    public static function get_page_min() {
        if (!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0) {
            return 0;
        } else {
            return abs((int)($_GET['min']));;
        }
    }
    
    // Save the number of rows to show in a GET variable
    public static function set_items_showed( $value ){
        $_GET['itemsToShow'] = $value;
        return $value;
    }
    
    //    Returns the order by columns. Used e.g. in show_list()
    public static function get_page_order( $validFields ) {
        if (!isset($_GET['order']) || !in_array( $_GET['order'] , $validFields ) ) {
            return "";
        } else {
            if( isset($_GET['orderType']) && $_GET['orderType'] == "ASC" ){
                return " ORDER BY `".$_GET['order']."` ASC ";
            }
            else{
                return " ORDER BY `".$_GET['order']."` DESC ";
            }
        }
    }
    
    // Function for converting a row from the database for viewing in the table parser;
    // i.e. it transforms a single row to a multi-row array with each row having a key and value
    public static function parseDatarowForDisplay( $dataRow ){
        
        // New data array
        $newData = array();
        if( !empty($dataRow) ){
            foreach( $dataRow as $key => $value ){
                $newData[] = array( "key" => $key, "value" => $value );
            }
        }
        return $newData;
    }

    //    Parses a form for the specified table:
    //    For a return link, use: $GLOBALS['template']->assign("returnLink", LINK or true);
    //    
    //    options_fields is an array consisting of option arrays on the form:
    //    array("name" => "Use", "act" => "usechar", "uid" => "table.id")
    //
    //     topOptions is an array consisting of options arrays on the form:
    //     Eg. array( "name" => "Event Char Hijack", "href" => "?id=".$id."&act=logs&type=hijack"" ),
    //
    //     $topSearchFields is an array consisting of option arrays on the form:
    //     Eg. array("infoText"=>"Search by username","href"=>"Link","postField"=>"UserName", "postIdentifier"=>"postUserName", "inputName"=>"Search User")
    public static function show_list(
            $smartyTitle,               // Variable name used for smarty. Use to include in template
            $title = 'Table',           // title for page
            $data,                      // Query result
            $show_fields,               // Fields to show in the table. E.g. array('username' => "User Name", 'rank' => "User Rank")        
            $option_fields = false,     // Supply options for each row
            $full_page = false,         // Use to load as full page. Otherwise will be saved in smarty variable.
            $bottomLinks = false,       // Show the newer/older links
            $topOptions = false,        // An array of links to show at the top.
            $sortOnColums = false,      // Enables the user to sort on the columns shown
            $prettyHideOptions = false, // Make the option fields hidden / shown with AJAX
            $topSearchFields = false,   // An array of top search options
            $topInformationText = false,  // information text to be displayed at top of table
            $backEndFileCorrection=""   // Used for the back to navigate to the current files
    ) {

        // Pass title to smarty
        $GLOBALS['template']->assign('subHeader_' . $smartyTitle, $title);

        // Determine whether we should set a form for catching e.g. checkBoxes in options
        $checkBoxForm = false;
        
        // Get the data ready for smarty
        $smartyArray = array();
        
        // Array to contain AJAX-hidden options
        $ajaxSmartyArray = array();
        $GLOBALS['template']->assign('hideOptions_' . $smartyTitle, $prettyHideOptions);

        // Disable sort on columns for mobile
        if( (isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true) || ( isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes') ){
            $sortOnColums = false;
        }
        
        // First load the fields and options
        $nColumns = 0;
        foreach ($show_fields as $key => $value) {
            switch( $sortOnColums ){
                case true: 
                    $link = "?order=".$key;
                    if( isset($_GET['orderType']) && $_GET['orderType'] == "ASC"){
                        $link .= "&amp;orderType=DESC";
                    }
                    else{
                        $link .= "&amp;orderType=ASC";
                    }
                    foreach ($_GET as $k => $v ) {
                        // Only inlude common GET-variables here
                        if( $k !== "order" && $k !== "min" && $k !== "orderType" && preg_match("/(act|profile|name|id|type)/", $k)  ){
                            $link .= "&amp;".$k."=".$v;
                        }
                    }
                    $smartyArray[0][] = "<a class='showTableOrderLink' href='" . $link . "'> " . $value . " </a>";
                break;
                case false: 
                    $smartyArray[0][] = $value;
                break;
            }
            $nColumns++;
        }
        if ($option_fields !== false) {
            if( $prettyHideOptions == true ){
                foreach ($option_fields as $option) {
                    $ajaxSmartyArray[0][] = $option["name"];
                }
            }
            else{
                foreach ($option_fields as $option) {
                    $smartyArray[0][] = $option["name"];
                    $nColumns++;
                    
                    // Enable submit button for checkBox form
                    if( isset($option["parseType"]) && $option["parseType"] == "select" ){
                        $GLOBALS['template']->assign('checkBoxFormLink', $option['href']);
                        $GLOBALS['template']->assign('checkBoxFormSubmit', $option['submitName']);
                    }
                }
            }
        }
        $GLOBALS['template']->assign('nColumns_' . $smartyTitle, $nColumns);
        $GLOBALS['template']->assign('topOptions_' . $smartyTitle, $topOptions);
        $GLOBALS['template']->assign('topInfo_' . $smartyTitle, $topInformationText);
        $GLOBALS['template']->assign('topSearchFields_' . $smartyTitle, $topSearchFields);

        // Load the smarty array
        if ($data != "0 rows") {

            // Go through the data
            $i = 1;
            foreach ($data as $entry) {

                // Add element to smarty array
                $smartyArray[$i] = array();
                
                // If it's a subtitle, just add that and stop
                if(array_key_exists("TP_subtitle", $entry) ){
                    $smartyArray[$i]["TP_subtitle"] = $entry['TP_subtitle'];
                }
                else{
                    
                    // Include only chosen fields
                    foreach ($show_fields as $field => $name) {
                        if (isset($entry[$field]) ) {
                            $smartyArray[$i][] = $entry[$field];
                        }
                        else{
                            $smartyArray[$i][] = "";
                        }
                    }

                    // Include the chosen options
                    if ($option_fields != null) {
                        foreach ($option_fields as $option) {
                            $link = "?";
                            $alreadyHasID = false;
                            foreach( array_keys($option) as $key ){
                                if( $key == "id" ){
                                    $alreadyHasID = true;
                                }
                                elseif( $key == "link" ){
                                    $link = $option[$key];
                                    $alreadyHasID = true;
                                }
                            }
                            if( $alreadyHasID == false ){
                                $link .= "id=" . $_GET["id"];
                            }

                            $entryID = "";
                            foreach ($option as $key => $value) {
                                if( $key !== "name" && $key !== "link" ){
                                    if( $link !== "?"){
                                        $link .= "&amp;";
                                    }
                                    if (strstr($value, "table.")) {
                                        $value = explode(".", $value);
                                        $value = $value[1];
                                        $entryID = $entry[$value];
                                        $link .= $key . "=" . $entryID;
                                    } else {
                                        $link .= $key . "=" . $value;
                                    }
                                }
                            }
                            if( $prettyHideOptions == true ){
                                $ajaxSmartyArray[$i][] = array(
                                    "name" => $option["name"],
                                    "href" => $link
                                );
                            }
                            elseif( isset($option["parseType"]) && $option["parseType"] == "select" ){
                                $smartyArray[$i][] = "<input type='checkbox' name='".$option['formName']."[".$i."]' value='".$entryID."' />";
                            }
                            else{
                                if(strpos($link,'http') !== 0)
                                {
                                    $link_and_path = "window.location.pathname + '".ltrim($link,'/')."'";
                                    $link = "'/".ltrim($link,'/')."'";
                                }
                                else
                                {
                                    $link_and_path = "'$link'";
                                    $link = "'$link'";
                                }

                                $temp = 
                                "
                                (function(e)
                                {
                                    e = e || window.event;
                                    if(e.which === 1)
                                    {
                                        if($.isFunction('loadPage'))
                                        {
                                            loadPage({$link},'all', null, 'post', true);
                                        }
                                        else
                                        {
                                            window.location.href = {$link_and_path};
                                        }
                                    }
                                    else
                                    {
                                        window.open({$link_and_path},'_blank');  
                                    }
                                })();
                                return false;
                                ";
                                $smartyArray[$i][] = "<a href={$link} oncontextmenu=\"return false\" onmousedown=\"".$temp."\">". $option["name"] . " </a>";
                            }
                        }
                    }                    
                }
                $i++;
            }
        }
        
        // Set to false if empty
        if( empty($smartyArray) ){
            $smartyArray = false;
        }
  
        // Pass to smarty
        $GLOBALS['template']->assign('data_' . $smartyTitle, $smartyArray);
        $GLOBALS['template']->assign('dataHidden_' . $smartyTitle, $ajaxSmartyArray);
        $GLOBALS['template']->assign('subSelect', $smartyTitle);

        // Pass bottom links
        if ($bottomLinks !== false) {
            if (!isset($_GET['id'])) {
                $_GET['id'] = 1;
            }
            $link = "?id=" . $_GET['id'];
            foreach ($_GET as $key => $value) {
                if ($key != "id" && preg_match("/(act|profile|name|id|type|orderType|order|filter)/", $key) ) {
                    $link .= "&amp;" . $key . "=" . $value;
                }
            }
            
            // Set items to show
            $itemsToShow = (isset($_GET['itemsToShow']) && is_numeric($_GET['itemsToShow']) && $_GET['itemsToShow'] < 500) ? $_GET['itemsToShow'] : 10;
            if (!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0) {
                $min = 0;
                $newmini = $itemsToShow;
                $newminm = 0;
            } else {
                $min = $_GET['min'];
                $newminm = $min - $itemsToShow;
                if ($newminm < 0) {
                    $newminm = 0;
                }
                $newmini = $min + $itemsToShow;
            }

            $GLOBALS['template']->assign("newerLink_" . $smartyTitle, $link . "&amp;min=" . $newminm);
            $GLOBALS['template']->assign("olderLink_" . $smartyTitle, $link . "&amp;min=" . $newmini);
        }

        $GLOBALS['template']->assign('full_page', $full_page);
        if ($full_page == true) {            
            $GLOBALS['template']->assign('contentLoad', './templates/dbparser/showTable.tpl');
        } else {
            // Set the smarty template
            if(isset($GLOBALS['mf']) && $GLOBALS['mf']=='yes')
            {
                $GLOBALS['template']->assign($smartyTitle, $backEndFileCorrection.'templates/dbparser/showTable_mf.tpl');
            }
            else
            {
                $GLOBALS['template']->assign($smartyTitle, $backEndFileCorrection.'templates/dbparser/showTable.tpl');
            }
        }
    }

    //	Parses a form for the specified table:
    public static function parse_form(
        $table, 
        $title = 'Form', 
        $ignore_fields = null, 
        $data = null, 
        $smartyTitle = null,
        $formAction = "",
        $stripSlashes = true,
        $collapsedRegions = false
    ) {

        // Pass title to smarty
        $GLOBALS['template']->assign('subHeader', $title);

        //handle collapsed regions
        $GLOBALS['template']->assign('collapsedRegions', $collapsedRegions);

        $collapsedContent = array();
        if(is_array($collapsedRegions))
            foreach($collapsedRegions as $region_key => $region_data)
                if(is_array($region_data))
                    foreach($region_data as $field)
                        $collapsedContent[] = $field;

        $GLOBALS['template']->assign('collapsedContent', $collapsedContent);

        // Get fields in tables
        $fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `" . $table . "`");

        // data array for smarty
        $smartyData = array();

        // Go thought he fields
        if ($fields != '0 rows') {
            $i = 0;
            while ($i < count($fields)) {
                if (!in_array($fields[$i]['Field'], $ignore_fields)) {
                    //new type - boolean: tinyint(1), selectbox with Yes/No added by nazarov@a2design.biz
                    if ($fields[$i]['Type'] == 'tinyint(1)') {
                        $items = array();

                        if ($data != null) 
                        {
                            $items[] = array($data[0][$fields[$i]['Field']] == 1 ? 'selected' : '', '1', 'Yes');
                            $items[] = array($data[0][$fields[$i]['Field']] == 0 ? 'selected' : '', '0', 'No');
                        }
                        else
                        {
                            $items[] = array($fields[$i]['Default'] == 1 ? 'selected' : '', '1', 'Yes');
                            $items[] = array($fields[$i]['Default'] == 0 ? 'selected' : '', '0', 'No');
                        }


                        $smartyData[] = array("enum", $fields[$i]['Field'], $items);
                    } elseif (stristr($fields[$i]['Type'], 'enum')) {
                        //	Dropbox, ENUM
                        $temp = str_replace('enum(', '', $fields[$i]['Type']);
                        $temp .= '$1233';
                        $temp = str_replace(')$1233', '', $temp);
                        $temp = '#' . $temp . '#';
                        $temp = str_replace('#\'', '', $temp);
                        $temp = str_replace('\'#', '', $temp);
                        $temp = explode('\',\'', $temp);
                        $u = 0;
                        $override = false;

                        // Smarty variables
                        $enumData = array(); // contains arrays of type array( selectOption , valueKey, showValue );
                        // Go through the options
                        while ($u < count($temp)) {
                            $selected = "";
                            if ($data != null) {
                                if ($data[0][$fields[$i]['Field']] == $temp[$u]) {
                                    $override = true;
                                    $selected = "selected";
                                }
                            } elseif ($temp[$u] == $fields[$i]['Default']) {
                                $selected = "selected";
                            }
                            $enumData[] = array($selected, $temp[$u], ($stripSlashes) ? stripslashes($temp[$u]) : $temp[$u] );
                            $u++;
                        }
                        if ($fields[$i]['Null'] == 'YES') {
                            if ($override == false) {
                                $enumData[] = array("selected", "NULL", "NULL");
                            } else {
                                $enumData[] = array(0, "NULL", "NULL");
                            }
                        }
                        $smartyData[] = array("enum", $fields[$i]['Field'], $enumData);
                    } elseif (stristr($fields[$i]['Type'], 'TEXT') || stristr($fields[$i]['Type'], 'BLOB')) {
                        if ($data != null) {
                            $value = $data[0][$fields[$i]['Field']];
                        } elseif ($fields[$i]['Default'] != null) {
                            $value = $fields[$i]['Default'];
                        } else {
                            $value = '';
                        }
                        $textData = array("name" => $fields[$i]['Field'], "value" => ($stripSlashes) ? stripslashes($value) : $value);
                        $smartyData[] = array("text", $fields[$i]['Field'], $textData);
                    } else {
                        //	INT, float or VARCHAR
                        if ($data != null) {
                            $value = $data[0][$fields[$i]['Field']];
                        } elseif ($fields[$i]['Default'] != null) {
                            $value = $fields[$i]['Default'];
                        } else {
                            $value = '';
                        }
                        if ($fields[$i]['Field'] == 'password') {
                            $value = '';
                        }
                        
                        // If it's a date, convert format
                        if ( in_array($fields[$i]['Field'], array("start_date","end_date")) ) {
                            if( !empty($value) && $value > 0){
                                $value = date( "m/d/Y" , $value);
                            }
                            else{
                                $value = "";
                            }
                        }

                        $inputData = array($fields[$i]['Field'], ($stripSlashes) ? stripslashes($value) : $value); // Name, Value
                        $smartyData[] = array("input", str_replace('_', ' ', $fields[$i]['Field']), $inputData);
                    }
                    if ($data != null) {
                        $hiddenData = array("id" => 'old' . $fields[$i]['Field'],
                            "name" => 'old' . $fields[$i]['Field'],
                            "value" => (!stristr($fields[$i]['Type'], 'text') || strlen($data[0][$fields[$i]['Field']]) < 20000) ? (($stripSlashes) ? stripslashes($data[0][$fields[$i]['Field']]) : $data[0][$fields[$i]['Field']]) : "Contents too long.");
                        $smartyData[] = array("hidden", $hiddenData);
                    }
                }
                $i++;
            }

            // Pass title to smarty
            $GLOBALS['template']->assign('data', $smartyData);
            $GLOBALS['template']->assign('formAction', $formAction);

            // Load the smarty template
            if( $smartyTitle == null ){
                $GLOBALS['template']->assign('contentLoad', 'templates/dbparser/parseTable.tpl');
            }
            else{
                // Set the smarty template
                $GLOBALS['template']->assign($smartyTitle, 'templates/dbparser/parseTable.tpl');
            }
            
        } else {
            $GLOBALS['page']->Message('Error, the specified table:' . $table . ' contains no columns', 'Table parsing error', 'id=' . $_GET['id']);
        }
    }

    // Insert new data into the database
    public static function insert_data($table, $fdata = null) {
        $fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `" . $table . "` ");
        $i = 0;
        $preset = 0;
        $data = "";
        $values = "";
        while ($i < count($fields)) {
            self::parsePostField($fields[$i]['Field']);
            if (isset($_POST[$fields[$i]['Field']])) {
                if ($_POST[$fields[$i]['Field']] != '' && $_POST[$fields[$i]['Field']] != 'NULL') {
                    if ($fields[$i]['Field'] == 'password') {
                        $_POST[$fields[$i]['Field']] = md5($_POST[$fields[$i]['Field']]);
                    }
                    if ($preset == 0) {
                        $data .= "`" . $fields[$i]['Field'] . "`";
                        $values .= "'" . addslashes($_POST[$fields[$i]['Field']]) . "'";
                    } else {
                        $data .= ", `" . $fields[$i]['Field'] . "`";
                        $values .= ", '" . addslashes($_POST[$fields[$i]['Field']]) . "'";
                    }
                    $preset = 1;
                }
            } elseif (isset($fdata[$fields[$i]['Field']]) && $fdata[$fields[$i]['Field']] != '') {
                if ($preset == 0) {
                    $data .= "`" . $fields[$i]['Field'] . "`";
                    $values .= "'" . addslashes($fdata[$fields[$i]['Field']]) . "'";
                } else {
                    $data .= ", `" . $fields[$i]['Field'] . "`";
                    $values .= ", '" . addslashes($fdata[$fields[$i]['Field']]) . "'";
                }
                $preset = 1;
            }
            $i++;
        }
        $query = "INSERT INTO `" . $table . "` (" . $data . ") VALUES(" . $values . ")";

        if ($GLOBALS['database']->execute_query($query)) {
            return true;
        } else {
            return false;
        }
    }

    // Update table
    public static function update_data($table, $key, $key_value) {
        $fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `" . $table . "` ");
        $i = 0;
        $preset = 0;
        $data = "";
        while ($i < count($fields)) {
            if (isset($_POST[$fields[$i]['Field']])) {
                if (
                    (
                    isset($_POST[$fields[$i]['Field']]) &&
                    isset($_POST['old' . $fields[$i]['Field']]) &&
                    $_POST[$fields[$i]['Field']] != $_POST['old' . $fields[$i]['Field']]
                    ) ||
                    !isset($_POST['old' . $fields[$i]['Field']])
                ) {
                    self::parsePostField($fields[$i]['Field']);
                    if ($fields[$i]['Null'] == 'YES' && $_POST[$fields[$i]['Field']] == "") {
                        if ($preset == 0) {
                            $data = "`" . $fields[$i]['Field'] . "`" . " = NULL ";
                        } else {
                            $data .= ", `" . $fields[$i]['Field'] . "`" . " = NULL ";
                        }
                        $preset = 1;
                    }
                    else {
                        if ($fields[$i]['Field'] == 'password') {
                            $_POST[$fields[$i]['Field']] = md5($_POST[$fields[$i]['Field']]);
                        }
                        if ($preset == 0) {
                            $data = "`".$fields[$i]['Field']."`"." = '".addslashes($_POST[$fields[$i]['Field']])."'";
                        } else {
                            $data .= ", `".$fields[$i]['Field']."`"." = '".addslashes($_POST[$fields[$i]['Field']])."'";
                        }
                        $preset = 1;
                    } 
                }
            }
            $i++;
        }
        $query = "UPDATE `" . $table . "` SET " . $data . " WHERE `" . $key . "` = '" . $key_value . "' LIMIT 1";
        if ($data !== "" && $GLOBALS['database']->execute_query($query) !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function check_data($table, $key, $key_value, $skip_fields = array()) {

        $query = "SELECT * FROM `" . $table . "` WHERE `" . $key . "` = '" . $key_value . "' LIMIT 1";

        $old_data = $GLOBALS['database']->fetch_data($query);
        $new_data = $_POST;
        $fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `" . $table . "`");
        $output = '';
        if ($old_data !== "0 rows") {
            $i = 0;
            while ($i < count($fields)) {
                if ($fields[$i]['Field'] != $key) {
                    if (!in_array($fields[$i]['Field'], $skip_fields)) {
                        if (
                                (isset($fields[$i]['Field']) && $fields[$i]['Field'] != 'password' && $fields[$i]['Field'] != 'description') ||
                                (isset($new_data['password']) && $new_data['password'] != '')
                        ) {
                            if (isset($_POST[$fields[$i]['Field']]) && isset($_POST['old' . $fields[$i]['Field']]) && $_POST[$fields[$i]['Field']] != $_POST['old' . $fields[$i]['Field']]) {
                                if ($old_data[0][$fields[$i]['Field']] != $new_data[$fields[$i]['Field']]) {
                                    $output .= '<b>Altered Field:</b> "' . $fields[$i]['Field'] . '" old value: "' . $old_data[0][$fields[$i]['Field']] . '" new value: "' . $new_data[$fields[$i]['Field']] . '" <br>';
                                }
                            }
                        }
                    }
                }
                $i++;
            }
        } else {
            $output = 'Old data not found in database';
        }
        if ($output == '') {
            $output = 'No fields altered';
        }
        return $output;
    }

   
    // parse post field value - e.g. dates
    // Saves directly to POST variables
    private static function parsePostField( $field ){
        if( $field == "start_date" || $field == "end_date" ){
            if( !empty($_POST[$field]) ){
                $_POST[$field] = strtotime($_POST[$field]);
            }
        }
    }
    
    
}