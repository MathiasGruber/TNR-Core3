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

    public function __construct() {
        
        try{
            
            if( !isset($_GET['act']) || $_GET['act'] == "main" ){
                $this->main_screen(
                    array(
                        'time' => "Detailed Time",
                        'page' => "Page ID",
                        'uid' => "User ID",
                        'error_message' => "MySQL Error",
                        'error_number' => "MySQL Error-ID"
                    )
                );
            }
            else{
                switch( $_GET['act'] ){
                    case "showQuery": 
                        $this->showQuery();
                    break;
                    case "showPageQueries": 
                        $this->showPageQueries();
                    break;
                    case "clearAll": 
                        $this->clearAll();
                        $GLOBALS['page']->Message( "Cleared" , 'SQL Error Log', 'id='.$_GET['id'],'Return');
                    break;
                    case "clearDeadlock": 
                        $this->clearDeadlock();
                        $GLOBALS['page']->Message( "Cleared" , 'SQL Error Log', 'id='.$_GET['id'],'Return');
                    break;
                    case "failedQueries":
                        $this->main_screen(
                        array(
                            'time' => "Time",
                            'failed_query' => "Query"
                        )
                    );
                    break;
                }
            }
            
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'SQL Error Log', 'id='.$_GET['id'],'Return');
        }
    }
    
    private function clearAll(){
        $query = "DELETE FROM `log_sql_errors`";
        if( !$GLOBALS['database']->execute_query( $query ) ){
            throw new Exception("There was an error clearing all the errors");
        }
    }
    
    private function clearDeadlock(){
        $query = "DELETE FROM `log_sql_errors` WHERE `error_number` = 1213";
        if( !$GLOBALS['database']->execute_query( $query ) ){
            throw new Exception("There was an error clearing all the errors");
        }
    }
    
    
    private function showQuery(){
        if( isset($_GET['eid']) ){
            $error = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `log_sql_errors`
                WHERE `id` = '".$_GET['eid']."' LIMIT 1");
            if( $error !== "0 rows" ){
                $GLOBALS['page']->Message( nl2br($error[0]['failed_query']) , 'SQL Error Log', 'id='.$_GET['id'],'Return');
            }
            else{
                throw new Exception("Could not find the specified error");
            }
        }
        else{
            throw new Exception("No error ID set");
        }
    }
    
    private function showPageQueries(){
        if( isset($_GET['eid']) ){
            $error = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM `log_sql_errors`
                WHERE `id` = '".$_GET['eid']."' LIMIT 1");
            if( $error !== "0 rows" ){
                
                // Format the text
                $text = str_replace("Running query:", "<br><br>Running query:<br>",$error[0]['page_queries'] );
                $text = str_replace("Committing Transaction", "<br><b>Committing Transaction</b>",$text );
                $text = str_replace("Starting Transaction", "<br><b>Starting Transaction</b>",$text );
                
                $GLOBALS['page']->Message($text , 'SQL Error Log', 'id='.$_GET['id'],'Return');
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
            SELECT * 
            FROM `log_sql_errors`
            ORDER BY `time` DESC 
            LIMIT " . $min . ",10");
        
        // Show table
        tableParser::show_list(
            'errors', 
            'SQL Errors', 
             $errors, 
             $showList, 
            array( 
                array( "id" => $_GET['id'], "name" => "Show Query", "act" => "showQuery", "eid" => "table.id"),
                array( "id" => $_GET['id'], "name" => "Page Queries", "act" => "showPageQueries", "eid" => "table.id")
            ), 
            true, // Send directly to contentLoad
            true, // No newer/older links
            array(
                array("name" => "Show Overview", "href" => "?id=" . $_GET['id'] . "&act=main" ),
                array("name" => "Show Failed Queries", "href" => "?id=" . $_GET['id'] . "&act=failedQueries" ),
                array("name" => "Clear All", "href" => "?id=" . $_GET['id'] . "&act=clearAll" ),
                array("name" => "Clear Deadlocks", "href" => "?id=" . $_GET['id'] . "&act=clearDeadlock" )
            ), //top options links
            false, // No sorting on columns
            false, // No pretty options
            false // No top search field
        );
    }

}

new sql_errors();