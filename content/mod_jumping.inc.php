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

    class module {

        public function __construct() {

            try {

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Check user rank. Only allow staff
                if (!in_array($GLOBALS['userdata'][0]['user_rank'], array('Admin', 'Tester'), true)) {
                    throw new Exception("You do not have access to view this page!");
                }

                if (!isset($_POST['Submit'])) {
                    self::main_page(); // Show Main Page
                }
                else {

                    // Check for Valid Village Jump Data
                    if (!isset($_POST['village_choice']) || !in_array($_POST['village_choice'], Data::$VILLAGES, true)) {
                        throw new Exception("Your selection is not an active village. Please try again!");
                    }

                    //stop kages from jumping

                    if (!($kages = $GLOBALS['database']->fetch_data("SELECT `leader` FROM `villages`")))
                        throw new Exception('There was an error when obtaining kage list, please try again!');

                    foreach($kages as $kage)
                        if(strtolower($kage['leader']) == strtolower($GLOBALS['userdata'][0]['username']))
                            throw new Exception('while kage you may not jump to another village.');

                    self::do_jump(); // Village Jump Action
                }

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }
            }
            catch (Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "Village Relocation Tool", 'id='.$_GET['id'], 'Return');
            }
        }

        // Main page
        private function main_page() {

            $villages = array();

            // Setup Village Array and Names
            foreach(Data::$VILLAGES as $village) {
                $villages[$village] = $village;
            }

            // Create the input form
            $GLOBALS['page']->UserInput("Make a selection toward which village you want to be relocated at!",
                "Village Relocation Tool",
                array(
                    array(
                        "infoText" => "Village",
                        "inputFieldName" => "village_choice",
                        "type" => "select",
                        "inputFieldValue" => $villages
                    ),
                ),
                array(
                    "href" => "?id=".$_REQUEST['id'] ,
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"),
                false ,
                "jumpForm"
            );

            // Get the orders
            if (!($nindo = $GLOBALS['database']->fetch_data("SELECT `user_notes`.`message`
                FROM `user_notes` WHERE `user_notes`.`user_id` = 0 LIMIT 1"))) {
                throw new Exception('There was an error when obtaining staff orders, please try again!');
            }

            // Input form
            $nindo_text = ($nindo !== "0 rows") ? functions::parse_BB($nindo[0]['message']) : '';

            // Set extra content load to be shown below input
            $GLOBALS['page']->Message(
                $nindo_text,
                'Staff Orders',
                false,
                false,
                'extraContentLoad'
            );
        }

        //	Perform Village Jump/Relocation
        protected function do_jump() {

            // User must be awake
            if( $GLOBALS['userdata'][0]['status'] !== "awake" &&
                ( !stristr($GLOBALS['userdata'][0]['location'],"village") || $_POST['village_choice'] == "Syndicate")
            ){
                throw new Exception("You must be awake or asleep in a village to jump villages!");
            }

            // Get library and instantiate
            require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
            $respectLib = new respectLib();

            // Move village
            $result = $respectLib->moderator_jump($_POST['village_choice']);

            // Show message
            $GLOBALS['page']->Message($result, 'Village Relocation Tool', 'id=' . $_GET['id']);
        }
    }

    new module();