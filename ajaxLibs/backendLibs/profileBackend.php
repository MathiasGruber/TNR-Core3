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

require_once(Data::$absSvrPath.'/libs/profileFunctions/profileLib.inc.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

// Set up class for handling stuff
class profileBackend extends profileFunctions {

    // Variable containing the return data
    public $returnData = array();
    public $availableDataPages = array("statistics","timers");

    // Constructor
    public function __construct( $forcePage = "" ){

        // Get the page to load
        $loadPage = isset($_REQUEST['load']) ? $_REQUEST['load'] : "";
        $loadPage = !empty($forcePage) ? $forcePage : $loadPage;

        // Load profile data
        try{
            if( in_array($loadPage, $this->availableDataPages)){

				if( in_array($GLOBALS['userdata'][0]['layout'], array('default')))
					$GLOBALS['mf'] = 'yes';

                switch( $loadPage ){

                    // Get data for statistics page
                    case "statistics":
                        $this->setCharData( "statistics" , $_SESSION['uid']);
                        $this->setMarriage();
                        $this->setFedSupport();
                        $this->setWinStatistics();
                        $this->setElementMasteries();
                        $GLOBALS['template']->assign('charStats', $this->char_data[0] );
                    break;
                    case "timers":
                        $this->setCharData("level" , $_SESSION['uid']);
                        $this->char_data[0] = array_merge($GLOBALS['userdata'][0], $this->char_data[0]);
                        $this->set_regen();
                        $GLOBALS['template']->assign('timers', $this->getTimers());
                    break;

                }
            }


        } catch (Exception $e) {

            // Rollback
            $GLOBALS['database']->transaction_rollback($e->getMessage());

            // Send menu update
            $this->menuMessage[] = array(
                "txt" => $e->getMessage(),
                "popUp" => true
            );
        }
    }

    // Return elements for display to page
    public function getDisplay(){

        // Only if valid request
        if( isset($_REQUEST['load']) && in_array($_REQUEST['load'], $this->availableDataPages)){
            switch( $_REQUEST['load'] ){
                case "statistics":
                    if( in_array($GLOBALS['userdata'][0]['layout'], array('default')))
                        $this->returnData['statisticsPage'] = $GLOBALS['template']->fetch( './templates/content/profile/profileStatistics_mf.tpl' );
                    else
                        $this->returnData['statisticsPage'] = $GLOBALS['template']->fetch( './templates/content/profile/profileStatistics.tpl' );
                break;
                case "timers":
                    if( in_array($GLOBALS['userdata'][0]['layout'], array('default')))
                        $this->returnData['timersPage'] = $GLOBALS['template']->fetch( './templates/content/profile/profileTimers_mf.tpl' );
                    else
                        $this->returnData['timersPage'] = $GLOBALS['template']->fetch( './templates/content/profile/profileTimers.tpl' );
                break;
            }

        }

        // Return data
        return $this->returnData;
    }
}
