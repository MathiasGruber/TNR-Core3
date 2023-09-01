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

// Hadnler class
class specialization {

    // Constructor
    public function __construct() {

        // Try-catch
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Decide on what page to show
            if (!isset($_POST['Submit']) ) {
                $this->showOverview();
            }
            else{
                $this->pickSpecialization();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , "Specialization System", 'id='.$_GET['id'],'Return');
        }
    }

    // Show options
    private function showOverview() {

        // Send to smarty
        $specialization = explode(":", $GLOBALS['userdata'][0]['specialization']);
        switch( $specialization[0] ){
            case "N": $specialization[0] = "Ninjutsu"; break;
            case "T": $specialization[0] = "Taijutsu"; break;
            case "G": $specialization[0] = "Genjutsu"; break;
            case "W": $specialization[0] = "Bukijutsu"; break;
        }

        // Set extra information for the user
        $info = "";
        if ($specialization[0] == "0"){
            $info = "You are not currently specialized in anything. Please pick from the list below!";
        }
        else{
            throw new Exception("You are currently specialized in ".$specialization[0].". This choice enables you to use certain jutsu, or change the way certain jutsu work for you. You can change your specialization by going to <a href='?id=59'>black market</a>.");
        }

        // Select array
        $selectArray = array(
            "Ninjutsu" => "Ninjutsu",
            "Taijutsu" => "Taijutsu",
            "Genjutsu" => "Genjutsu",
            "Weapon" => "Bukijutsu"
        );

        // Create the input form
        $GLOBALS['page']->UserInput(
                "As you attain the rank of Chuunin you need specialize in one of the ninja arts. This choice enables you to use certain jutsu, or change the way certain jutsu work for you. But be wary, changing this specialization is not easy, as well as being costly.<br><br>".$info,
                "Specialization System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"specChoice",
                        "type"=>"select",
                        "inputFieldValue"=> $selectArray
                    )
                ),
                array(
                    "href"=>"?id=".$_GET['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );
    }

    // Do change the specialization
    function pickSpecialization(){

        // Check input
        if( isset($_POST['specChoice']) && (
                 $_POST['specChoice'] == "Ninjutsu" ||
                 $_POST['specChoice'] == "Taijutsu" ||
                 $_POST['specChoice'] == "Genjutsu" ||
                 $_POST['specChoice'] == "Weapon"
            )
        ){

            //  Start Transaction
            $GLOBALS['database']->transaction_start();

            // Load character
            $specialization = explode(":", $GLOBALS['userdata'][0]['specialization']);

            // Make sure user hasn't already picked
            if( $specialization[0] == "0" ){

                // New specialization
                switch( $_POST['specChoice'] ){
                    case "Ninjutsu": $newChar = "N"; break;
                    case "Taijutsu": $newChar = "T"; break;
                    case "Genjutsu": $newChar = "G"; break;
                    case "Weapon": $newChar = "W"; break;
                }
                $newSpec = $newChar.":".($specialization[1]+1);

                // Do update db
                $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `specialization` = '" . $newSpec . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");

                $GLOBALS['Events']->acceptEvent('specialization', array('new'=>$newChar, 'old'=>'None' ));

                // Success message
                $GLOBALS['page']->Message("You have specialized in ".$_POST['specChoice'], 'Specialization Success', 'id=' . $_GET['id']);

                //  Start Transaction
                $GLOBALS['database']->transaction_commit();

            }
            else{
                throw new Exception("You have already picked your specialization");
            }
        }
        else{
            throw new Exception("You cannot specialize in this.");
        }
    }
}

new specialization();