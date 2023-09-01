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

// Message for outlaws:
//
class casino {

    //	List of games
    private $games = array('blackjack', 'kings', 'bakuchi',);
    private $names = array('Blackjack', 'Kings and Queens', 'Cho / Han Bakuchi',);

    // Constructor
    public function __construct() {

        // Try-catch
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Not for outlaws
            if( $GLOBALS['userdata'][0]['village'] !== "Syndicate" ){

                // Decide page
                if (!isset($_GET['game'])) {

                    // Show games
                    $this->main_screen();
                } else {

                    // load game
                    $this->load_game();
                }
            }
            else{
                throw new Exception("The door to the casino is guarded by casino security. As you move closer the security guard stops you. The boss said not to let your kind in, back off!");
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Casino System', 'id='.$_GET['id'],'Return');
        }
    }

    // Show games
    private function main_screen() {
        $GLOBALS['template']->assign('games', $this->games);
        $GLOBALS['template']->assign('names', $this->names);
        $GLOBALS['template']->assign('contentLoad', './templates/content/casino/casino_main.tpl');
    }

    // Load the game in question
    private function load_game() {
        error_reporting(0);
        if (require_once('./libs/' . $this->games[$_GET['game']] . '.inc.php')) {
            if (isset($_SESSION[$this->games[$_GET['game']]])) {
                $game = unserialize($_SESSION[$this->games[$_GET['game']]]);
                if (is_object($game)) {
                    $game->run();
                } else {
                    $GLOBALS['error']->handle_error('500', 'Your casino game data is corrupt, please start a new game.', '5');
                    session_unregister($this->games[$_GET['game']]);
                }
            } else {
                $game = new $this->games[$_GET['game']]();
            }
        } else {
            $GLOBALS['error']->handle_error('500', 'The game library did not exist for this game: ' . $this->names[$_GET['game']] . '. <br>For that reason the game is temporarily unavailable', '1');
        }
    }

}

new casino();