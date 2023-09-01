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

    require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');

    class clanChat extends clanLib {

        public function __construct(){

            // Try running the page
            try{

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Check if in clan
                if(!(parent::isUserClan($GLOBALS['userdata'][0]['clan']))) {
                    throw new Exception("You are not currently part of a clan");
                }

                parent::showChat();

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }

            }
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback( $e->getMessage() );
                $GLOBALS['page']->Message( $e->getMessage() , 'Clan System', 'id='.$_GET['id'],'Return');
            }
        }
    }

    new clanChat();