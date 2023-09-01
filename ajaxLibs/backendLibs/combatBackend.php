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

// Include extra Files
require_once(Data::$absSvrPath.'/libs/battleSystem/basicFunctions.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/battle.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/damage_calc_v2.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/effects_bloodline.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/effects_status.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/jutsu.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/items.inc.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/weapon.inc.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');
require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
require_once(Data::$absSvrPath.'/libs/jutsuSystem/jutsuFunctions.php');

// Define a class for the battle backend
class battleBackend extends battle {

    // Variable containing the return data
    public $returnData = array();
    
    // Constructor
    public function battleBackend(){
        
        //echo"Dont report [BACKEND] - ";
        
        // Use try/catch exceptions
        try {
            
            // Instantiate the battle class
            $this->TURN_SWITCH = 30;
            $this->MAX_HELP = 5; 
            $this->MAX_ROUND = 50; 

            // Start a transaction
            $GLOBALS['database']->transaction_start();
            $GLOBALS['database']->setIsolationLevel("SERIALIZE");
            
            // Fetch the battle for this user
            if ( $this->fetch_battle_data() ) {

                // Check max rounds
                if( $this->round <= $this->MAX_ROUND ){
                
                    // Check if round is over yet
                    $this->check_battle_time();

                    // Determine stage for this user
                    // Saved into $this->stage variable. Values are:
                    // 1: nothing submitted
                    // 2: submitted, waiting for others
                    // 3: all users done, time to run calculations
                    // 4: battle ended, show summary screen
                    $this->determine_stage(); 

                    // Load content for the user depending on the stage
                    if( $this->stage_handler() || !isset($this->hasContentLoad) ){
                        
                        // Load the main battle interface. 
                        $GLOBALS['template']->assign('contentLoad', './templates/content/combat/contentWrapper.tpl');
                        $this->hasContentLoad = true;
                        
                        // Instant update user (backend)
                        $this->backendUpdateArray = array(
                            "cur_cha" => (float) $GLOBALS['userdata'][0]['cur_cha'],
                            "cur_sta" => (float) $GLOBALS['userdata'][0]['cur_sta'],
                            "cur_health" => (float) $GLOBALS['userdata'][0]['cur_health'],
                            "max_cha" => (float) $GLOBALS['userdata'][0]['max_cha'],
                            "max_sta" => (float) $GLOBALS['userdata'][0]['max_sta'],
                            "max_health" => (float) $GLOBALS['userdata'][0]['max_health']
                        );
                        //echo" [ACTION - ".$this->round."] - ";
                        
                    }
                        
                    // Everything should be good at this point
                    $GLOBALS['database']->transaction_commit();
                }
                else{
                    // Force end battle and show the user a message
                    $this->delete_battle();
                    $this->battle_end();
                    $this->hasContentLoad = true;
                    $GLOBALS['database']->transaction_commit();
                    //echo" [END - ".$this->round."] - ";
                    // Throw an exception with the error meesage
                    throw new Exception("Both parts are exhausted from many rounds of battle, and therefore decide to stop.");
                }
            } else {

                // Force end battle and show the user a message
                $this->battle_end();
                $this->hasContentLoad = true;
                $GLOBALS['database']->transaction_commit();

                // Throw an exception with the error meesage
                throw new Exception("As you enter the battle-field you discover that this battle has already been concluded. <br>
                                     There is nothing left here for you to do.");
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $returnLink = isset($_REQUEST['id']) ? 'id='.$_REQUEST['id'] : 'id=2';
            $GLOBALS['page']->Message( $e->getMessage() , 'Battle Exception', $returnLink);
        }
    }
    
    
    // Stage-handler. Decides what the user sees depending on the stage
    public function stage_handler(){
        
        // Must reset all smarty variables each time called, otherwise too much information may be sent to the user. 
        $GLOBALS['template']->assign('mainScreen',"");
        $GLOBALS['template']->assign('secondaryScreen',"");
        $GLOBALS['template']->assign('tertiaryScreen',"");
        $GLOBALS['template']->assign('optionalScreen',"");
        
        // Remember last action of user. Sets the last actions based on info from GET/POST
        $this->remember_action();
        
        // Remember round of this stage handler
        $localRound = $this->round;

        // Handle the stage
        //echo" [Start - STAGE".$this->stage." - ".$localRound."] - ";
        switch( $this->stage ){
            case 1: 

                // Check if this user is stunned or not
                if ( !$this->has_stun_effect( $this->sessionside, $_SESSION['uid'] ) ) {

                    // User is not stunned
                    // Check if submitting action
                    // Check that the form ID matches (if not, it's an indication of double submission, which we want to avoid)
                    // See: http://www.phpro.org/tutorials/Preventing-Multiple-Submits.html
                    if ( 
                        !isset($_REQUEST['action']) ||  // No action specified
                        !isset($_REQUEST['target']) ||  // No target specified
                        (isset($_REQUEST['form_token'], $_SESSION['form_token']) && $_REQUEST['form_token'] != $_SESSION['form_token']) // token did not match
                    ) {

                        // Show the main screen
                        $this->main_screen();

                        // User has not submitted an action
                        $this->battle_options();

                        // Show the battle log
                        $this->battle_log();

                    } else {

                        // User is submitting a move. 
                        $this->upload_move( $_REQUEST['target'] );
                        
                        // Unset token
                        unset( $_SESSION['form_token']);

                        // Determine new stage
                        $this->determine_stage(); 

                        // Recursively call the stage-handler and let it process the next stage
                        $this->stage_handler();
                        return false;
                    }
                } else {

                    // Show the main screen
                    $this->main_screen();

                    // User is stunned
                    $this->stun_screen();

                    // Show the battle log
                    $this->battle_log();
                }
                
            break;
            case 2: 

                // Just show waiting stuff
                $this->main_screen();
                $this->wait_screen();
                $this->battle_log();
                
            break;
            case 3: 

                // Execute moves and stuff
                $this->backup_stats('user');
                $this->backup_stats('opponent');
                $this->execute_moves();

                // Unset the post data from this round
                unset( $_REQUEST );

                // Re-fetch battle data, determine stage, and call the stage handler in the main-function
                $GLOBALS['database']->transaction_commit();
                $this->battleBackend();
                return false;
            break;
            case 4: 
                $this->summary_screen();
            break;
        }
        //echo"[End - STAGE".$this->stage." - ".$localRound."] - ";
        return true;
    }
    
    // Get display
    public function getDisplay(){
        
        // Return main content
        $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;
        $this->returnData['mainContent'] = $GLOBALS['template']->fetch( "file:" . $contentPage );
        
        // Return new user information
        if( isset( $this->backendUpdateArray ) ){
            $this->returnData['userInfo'] = $this->backendUpdateArray;
        }
        
        // Return to echo
        return $this->returnData;
    }    
}