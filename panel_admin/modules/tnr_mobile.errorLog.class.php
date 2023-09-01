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

class sql_errors {

    public function sql_errors() {
        
        try{
            
            if( !isset($_GET['act']) || $_GET['act'] == "main" ){
                $this->main_screen(
                    array(
                        'time' => "Error Time",
                        'count' => "Reportings",
                        'uid' => "UID",
                        'title' => "Title",
                        'message' => "Message",
                        'gameVersion' => "Version"
                    )
                );
            }
            else{
                switch( $_GET['act'] ){
                    case "showXML": 
                        $this->showXML();
                    break;
                    case "delete": 
                        $this->delete();
                        $GLOBALS['page']->Message( "Deleted" , 'SQL Error Log', 'id='.$_GET['id'],'Return');
                    break;
                    case "clearAll": 
                        $this->clearAll();
                        $GLOBALS['page']->Message( "Cleared" , 'SQL Error Log', 'id='.$_GET['id'],'Return');
                    break;
                    case "removeDuplicates": 
                        $this->removeDuplicates();
                        $GLOBALS['page']->Message( "Removed Duplicates" , 'SQL Error Log', 'id='.$_GET['id'],'Return');
                    break;
                }
            }
            
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'SQL Error Log', 'id='.$_GET['id'],'Return');
        }
    }
    
    private function delete(){
        $error = $GLOBALS['database']->fetch_data("SELECT * FROM `log_mobileErrors` WHERE `id` = '".$_GET['eid']."' LIMIT 1");
        if( $error != "0 rows" ){
            if( !$GLOBALS['database']->execute_query( "DELETE FROM `log_mobileErrors` WHERE `message` = '".addslashes($error[0]['message'])."' " ) ){
                throw new Exception("There was an error clearing all the errors");
            }
        }
        else{
            throw new Exception("Could not find error");
        }
        
    }
    
    private function clearAll(){
        $query = "DELETE FROM `log_mobileErrors`";
        if( !$GLOBALS['database']->execute_query( $query ) ){
            throw new Exception("There was an error clearing all the errors");
        }
    }
    
    private function showXML(){
        if( isset($_GET['eid']) ){
            $error = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `log_mobileErrors`                 
                WHERE `id` = '".$_GET['eid']."' LIMIT 1");
            if( $error !== "0 rows" ){
                $GLOBALS['page']->UserInput(
                    "", // Information
                    "XML Log", // Title
                    array(
                        array(
                            "infoText" => "Message",
                            "inputFieldName" => "message", 
                            "type" => "textarea", 
                            "inputFieldValue" => $error[0]['xmlContent']
                        )
                    ), // input fields
                    array(
                        "href" => "?id=" . $_REQUEST['id'] , 
                        "submitFieldName" => "Return",
                        "submitFieldText" => "Return"
                    ), // Submit button
                    false, // Return link name
                    "sendForm"
                );
                
                
              
            }
            else{
                throw new Exception("Could not find the specified error");
            }
        }
        else{
            throw new Exception("No error ID set");
        }
    }
    

    private function main_screen( $showList ) {
        $min = tableParser::get_page_min();
        $errors = $GLOBALS['database']->fetch_data("
            SELECT * , COUNT(`id`) as `count`
            FROM `log_mobileErrors` 
            GROUP BY `message`
            ORDER BY `count` DESC 
            LIMIT " . $min . ",10");
        
        // Show table
        tableParser::show_list(
            'errors', 
            'TNR Mobile Errors', 
             $errors, 
             $showList, 
            array( 
                array( "id" => $_GET['id'], "name" => "Show XML", "act" => "showXML", "eid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "Delete", "act" => "delete", "eid" => "table.id")
            ), 
            true, // Send directly to contentLoad
            true, // No newer/older links
            array(
                array("name" => "Show Overview", "href" => "?id=" . $_GET['id'] . "&act=main" ),
                array("name" => "Clear All", "href" => "?id=" . $_GET['id'] . "&act=clearAll" )
            ), //top options links
            false, // No sorting on columns
            false, // No pretty options
            false // No top search field
        );
    }

}

new sql_errors();