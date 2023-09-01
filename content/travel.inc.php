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

// Get required libraries
require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');
require_once(Data::$absSvrPath.'/libs/travelSystem/travelLib.php');
require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');

class travel extends travelLib {

    function __construct() {

        try {

            functions::checkActiveSession();

            // Start Transaction
            $GLOBALS['database']->transaction_start();

            // Set Data
            $this->set_data();

            // Load all the travelling
            $this->load_travel();

            // Messages
            $this->parseMessages();

            // Show the map
            $this->show_map();

            // Commit transaction
            $GLOBALS['database']->transaction_commit();

        }
        catch (Exception $e) {
            $this->updateSmarty();
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), 'Travel System', 'id=2');
        }
    }


    // Function for pushing messages to the template
    private function parseMessages() {
        // Set menu notifications
        if(!empty($this->menuMessage)) {
            foreach($this->menuMessage as $message) {
                if( isset($message['options']) ){
                    //if(isset($message['select']))
                    //    $GLOBALS['NOTIFICATIONS']->addTempNotification($message['text'],$message['buttons'],$message['select']);
                    //else if(isset($message['buttons']))
                    //    $GLOBALS['NOTIFICATIONS']->addTempNotification($message['text'],$message['buttons']);
                    //else if(isset($message['text']))
                    //    $GLOBALS['NOTIFICATIONS']->addTempNotification($message['text']);
                }
                else{
                    $GLOBALS['template']->append('travelMessages', $message);
                }
            }
        }
    }

    // Show the map (this does not need to be done on the backend)
    private function show_map() {

        // Get all map information
        $this->mapInformation = mapfunctions::getMapInformation();

        // Get location information
        $this->locationInformation = cachefunctions::getLocations();

        // Get the location owner
        $owner = $this->getLocationOwner($GLOBALS['userdata'][0]['longitude'], $GLOBALS['userdata'][0]['latitude']);

        // Set map tooltips
        $this->setToolTips();

        // Update smarty
        $this->updateSmarty();

        // Run template
        $GLOBALS['template']->assign('owner', $owner);
        $GLOBALS['template']->assign('contentLoad', './templates/content/travel/main.tpl');

    }

    // Get owner of given location
    private function getLocationOwner( $x, $y ){
        $locInfo = mapfunctions::getTerritoryInformation(
            array( "x.y" => $x.".".$y ) ,
            $this->mapInformation,
            $this->locationInformation
        );
        return $locInfo ? $locInfo['owner'] : "Unclaimed Territory";
    }

    // Create the tooltips to be shown on the map
    private function setToolTips(){

        // Not on light layout
        if( !in_array($GLOBALS['layout'], array("mobile","light") ) ){

            // Retrieve from cache
            $toolTipHtml = cachefunctions::getTravelTooltips();
            if( !$toolTipHtml ){

                // Map definitions
                $bsX = mapfunctions::$bsX;
                $bsY = mapfunctions::$bsY;
                $lw = mapfunctions::$lw;
                $topX = mapfunctions::$topX;
                $topY = mapfunctions::$topY;

                // Add the locations to html map-area tag
                $bufferHtml = "";
                foreach( $this->mapInformation as $mapEntry ){

                    // Get the owner, check only for first position
                    list($x, $y) = explode( ".",$mapEntry['positions'][0] );
                    $owner = $this->getLocationOwner($x, $y);
                    $mapEntry['name'] .= ", ".$owner;

                    // For each entry, go through each position
                    foreach( $mapEntry['positions'] as $entryPosition ){

                        // The position in x/y
                        list($x, $y) = explode( ".",$entryPosition );

                        // Get positions
                        $upperLeftX = round($topX+($x-1)*($bsX+$lw));
                        $upperLeftY = round($topY+($y-1)*($bsY+$lw));
                        $bottomRightX = round($upperLeftX + $bsX);
                        $bottomRightY = round($upperLeftY + $bsY);

                        // Add to html buffer
                        $bufferHtml .= '
                            <area shape="rect" coords="'.$upperLeftX.','.$upperLeftY.','.$bottomRightX.','.$bottomRightY.'" href="#" alt="'.$mapEntry['name'].'" title="'.$mapEntry['name'].'" class="tooltip">';

                    }
                }

                // Finalize the entry
                $toolTipHtml = '<map name="tooltipmap">'.$bufferHtml.'</map>';

                // Save in cache
                cachefunctions::setTravelTooltips($toolTipHtml);
            }

            // Send the map information to smarty
            $GLOBALS['template']->assign('toolTips', $toolTipHtml);

            // Add some extra javascript for this
            $GLOBALS['template']->assign('extraJava', '
                <script language="JavaScript" type="text/javascript">
                    $(document).ready(function() {
                        $(".tooltip").tooltipster({
                            delay: 0,speed:0
                        });
                    });
                </script>
            ');

        }
    }
}
new travel();