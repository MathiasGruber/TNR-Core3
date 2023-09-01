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
require('../libs/villageSystem/warLib.php');

class allianceManager extends warLib {
    public function __construct() {
        
        if (!isset($_POST['Submit'])) {
                
            // List all pages
            $this->main_page();

        } else {

            // Do edit
            $this->editPageDo();
        }
    }

    private function main_page() {
        
        // Available layouts
        $villageArray = array();
        foreach( Data::$VILLAGES as $village ){
            $villageArray[ $village ] = $village;
        }
        // Create the input form
        $GLOBALS['page']->UserInput( 
                "Force alliance status of villages. Be careful with this, 
                 it may mess up war data etc. It does not make extensive tests 
                 to ensure integrity of the different alliances/wars etc. 
                 This is primarily to be used for debugging.", 
                "Layout Change", 
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"village1",
                        "type"=>"select",
                        "inputFieldValue"=> $villageArray
                    ),
                    array(
                        "inputFieldName"=>"village2",
                        "type"=>"select",
                        "inputFieldValue"=> $villageArray
                    ),
                    array(
                        "inputFieldName"=>"alliance",
                        "type"=>"select",
                        "inputFieldValue"=> array(
                            "ally" => "Ally",
                            "war" => "War",
                            "neutral" => "Neutral"
                        )
                    ),
                    array("infoText" => "Note to users<br>", "inputFieldName" => "note", "type" => "textarea", "inputFieldValue" => "")
                ), 
                array(
                    "href"=>"?id=".$_GET['id'] ,
                    "submitFieldName"=>"Submit", 
                    "submitFieldText"=>"Submit"),
                "Return"
        ); 
    }
    
    private function editPageDo(){
        
        // Get the status code
        $status = 0;
        switch( $_POST['alliance'] ){
            case "ally": $status = 1; break;
            case "war": $status = 2; break;
        }
        
        // Update the alliance table
        $this->set_db_alliance( $_POST['village1'], $_POST['village2'], $status);
        
        // Update message to suer
        $GLOBALS['database']->execute_query("UPDATE `users` SET `notifications` = '" . "CONCAT('id:17;duration:none;text:".functions::store_content( $_POST['note'] ).";dismiss:yes;buttons:none;select:none;//',`notifications`)" . "' WHERE `village` = '" . $_POST['village1'] . "' OR `village` = '" . $_POST['village2'] . "' ");
        
        // Give message
        $GLOBALS['page']->Message( "Alliances have been updated" , 'Alliance Manager', 'id='.$_REQUEST['id'],'Return');
    }
}

new allianceManager();