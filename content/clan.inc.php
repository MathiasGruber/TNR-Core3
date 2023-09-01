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

// Get library and create control class
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
class clan extends clanLib {

    //  Constructor
    public function __construct() {

        // Try running the page
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);


            // Check that in village
            if( $GLOBALS['userdata'][0]['village'] !== "Syndicate" ){

                // Check if an action is set
                if( !isset($_GET['act2']) ){

                    // Check if in clan
                    if( $this->isUserClan($GLOBALS['userdata'][0]['clan']) ){
                        $this->showMenu();
                    }
                    else{
                        if( $application = $this->getUserApplication($_SESSION['uid']) ){
                            $this->showApplication( $application );
                        }
                        else{
                            $this->showClanList( $GLOBALS['userdata'][0]['village'] , false, true);
                        }
                    }
                }
                else{

                    // Get the clan ID we're looking at
                    $id = ( isset($_GET['cid']) ) ? $_GET['cid'] : $GLOBALS['userdata'][0]['clan'];

                    // Handle action
                    switch($_GET['act2']){
                        case "clandetail":
                        case "clanStatus":
                            $this->showClanStatus( $id );
                        break;
                        case "claimLeader":
                            $this->claimLeader();
                        break;
                        case "otherclans":
                            $this->showClanList( $GLOBALS['userdata'][0]['village'] );
                        break;
                        case "agenda":
                            $this->showAgenda( $id );
                        break;
                        case "resign":
                            if (!isset($_POST['Submit'])) {
                                $this->showResign();
                            } else {
                                $this->doResign( $_SESSION['uid'] );
                            }
                        break;
                        case "kick":
                            if (!isset($_POST['Submit'])) {
                                $this->showKickForm();
                            }
                            else {
                                $this->doKickUser();
                            }
                        break;
                        case "clanjutsu":
                            $this->showClanJutsu();
                        break;
                        case "challengeLeader":
                            $this->doChallengeLeader();
                        break;
                        case "apply":
                            if (!isset($_POST['Submit'])) {
                                $GLOBALS['page']->Confirm("Are you sure you want to apply for this clan? 3 out of 5 co-leaders must accept the application.", 'Clan System', 'Apply now!');
                            }
                            else {
                                $this->doAddApplication();
                            }
                        break;
                        case "stopApplication":
                            if (!isset($_POST['Submit'])) {
                                $GLOBALS['page']->Confirm("Are you sure you want to cancel this application", 'Clan System', 'Cancel now!');
                            }
                            else {
                                $this->doCancelApplication();
                            }
                        break;
                        case "applications":
                            $this->showApplications();
                        break;
                        case "manageleaders":
                            if (!isset($_POST['Submit'])) {
                                $this->showLeaderEditForm();
                            }
                            else {
                                $this->doUpdateLeaders();
                            }
                        break;
                        case "editagenda":
                            if (!isset($_POST['Submit'])) {
                                $this->showAgendaForm();
                            } else {
                                $this->doEditAgenda();
                            }
                        break;
                        case "editsignature":
                            if ( isset($_POST['Submit']) ) {
                                $this->doChangeSignature();
                            }
                            else {
                                $this->showSignatureForm();
                            }
                        break;
                        case "points":
                            if (!isset($_POST['Submit'])) {
                                $this->showPointMenu();
                            }
                            else {
                                $this->doSpendPoints();
                            }
                        break;
                        case "support":
                            if (!isset($_POST['Submit'])) {
                                $this->showKageSupport();
                            }
                            else {
                                $this->doUpdateKageSupport();
                            }
                        break;
                    }
                }
            }
            else{
                throw new Exception("Factions, such as the Syndicate, are gatherings of people from around the ninja world and they do not allow sub-separation of their population into clans.");
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Clan System', 'id='.$_GET['id'],'Return');
        }
    }


}
new clan();