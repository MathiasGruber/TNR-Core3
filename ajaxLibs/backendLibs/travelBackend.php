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

require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');
require_once(Data::$absSvrPath.'/libs/travelSystem/travelLib.php');
require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
require_once(Data::$absSvrPath.'/libs/notificationSystem/notificationSystem.php');

// Set up class for handling stuff
class travelBackend extends travelLib{
    
    // Variable containing the return data
    public $returnData = array();
    
    // Constructor
    public function __construct(){
        
        // Load travel
        try{
            
            // Strat Transaction 
            $GLOBALS['database']->transaction_start();

            // Set Data
            $this->set_data();
            
            // Load all the travelling
            $this->load_travel();

            // Set menu notifications
            if( !empty($this->menuMessage) ){
                foreach( $this->menuMessage as $message){

                    // Always add to travelMessages on travel screen
                    $GLOBALS['template']->append('travelMessages', $message );

                    //// Unless unset, show the messages in the menu
                    //if( !isset($message['hideMSG']) || $message['hideMSG'] == false ){
                    //    if(isset($message['select']))
                    //        $GLOBALS['NOTIFICATIONS']->addTempNotification($message['text'],$message['buttons'],$message['select']);
                    //    else if(isset($message['buttons']))
                    //        $GLOBALS['NOTIFICATIONS']->addTempNotification($message['text'],$message['buttons']);
                    //    else if(isset($message['text']))
                    //        $GLOBALS['NOTIFICATIONS']->addTempNotification($message['text']);                 
                    //}
                    //
                    //// If the user is being attacked, then the user cannot continue
                    //if( isset($message["popUp"]) && $message["popUp"] == true ){
                    //    $this->returnData["popupMessage"] = $message;
                    //}
                }
            }
            
            // Commit transaction
            $GLOBALS['database']->transaction_commit();
            
        } catch (Exception $e) {
            
            // Rollback
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            
            // Send menu update
            $this->menuMessage[] = array(
                "text" => $e->getMessage(),
                "popUp" => true
            );
        }

        $GLOBALS['NOTIFICATIONS']->showNotifications();
    }
    
    // Return elements for display to page
    public function getDisplay(){
        
        // See if owner is set
        if( !isset($GLOBALS['userdata'][0]['owner']) ){
            $GLOBALS['userdata'][0]['owner'] = "Unclaimed";
        }
        
        // Send location
        $this->returnData['longitude'] = $GLOBALS['userdata'][0]['longitude'];
        $this->returnData['latitude'] = $GLOBALS['userdata'][0]['latitude'];
        $this->returnData['widgetLocation'] = $GLOBALS['userdata'][0]['location'];
        $this->returnData["userLocation"] = "Your Location: <b>" . 
                                      $GLOBALS['userdata'][0]['longitude'] . "." . 
                                      $GLOBALS['userdata'][0]['latitude'] . " - " . 
                                      $GLOBALS['userdata'][0]['location'] . "</b> (" . $GLOBALS['userdata'][0]['owner'] . ")";
        
        // Send travel messages
        $contentPage = "../templates/content/travel/messages.tpl";
        $this->returnData['travelMessages'] = $GLOBALS['template']->fetch( $contentPage );
        
        // Return data
        return $this->returnData;
    }
}