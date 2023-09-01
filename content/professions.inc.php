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

require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
class profession extends professionLib {

    // Constructor
    public function __construct() {

        // Try phrase
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Setup data
            $this->setupProfessionData();

            // Determine what page to show
            if (!isset($_GET['act'])) {

                // Fetch user data:
                $this->fetch_user();

                // Check if user has a job
                if ($this->user[0]['profession'] == 0) {

                    // User doesn't have a job, show list:
                    $this->profession_list( $GLOBALS['userdata'][0]['rank_id'] );

                } else {

                    // Show profession page
                    $this->profession_main();
                }

            } elseif( $_GET['act'] == "detail" ){

                // Show item details
                $this->itemLib->show_item_details( $_GET['iid'] );

            } else {


                // Start a transaction
                $GLOBALS['database']->transaction_start();

                // Fetch user data, lock table
                $this->fetch_user( true );

                // Decide on action
                if( $_GET['act'] == 'getprofession' ){

                    //	Upload new job
                    $this->start_profession();

                }
                elseif ($_GET['act'] == 'quit') {

                    // Confirm Quit
                    if (isset($_POST['Submit'])) {

                        // Do Quit
                        $this->quit_profession();

                    } else {

                        // Confirm Dialog
                        $GLOBALS['page']->Confirm("Are you sure you wish to quit your profession as ".$this->user[0]['name'], 'Profession System', 'Quit Now!');
                    }
                }

                // Commit any finished transactions
                $GLOBALS['database']->transaction_commit();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {

            // If the user doens't have the required tool, then retire the user
            if( isset($this->userHasTool) && $this->userHasTool == false ){
                $this->quit_profession();
            }

            // Rollback & message
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Profession System', 'id='.$_GET['id'],'Return');
        }
    }
}

new profession();