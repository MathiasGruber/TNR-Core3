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

// Get the battle initiation library
require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/basicFunctions.inc.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');

// Fight Class
class fight extends battleInitiation {

    // Constructor for handling everything
    public function __construct(){

        // Try-Catch
        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // get the challenge
            $this->fetch_challenge();

            $this->territory_status();

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Territory System', 'id='.$_GET['id'],'Return');
        }
    }

    // Set territory challenge
    public function fetch_challenge(){

        // Get the territory
        //$this->terr_info = mapfunctions::getTerritoryInformation(
        //    array( "x.y" => $GLOBALS['userdata'][0]['longitude'].".".$GLOBALS['userdata'][0]['latitude'] ) ,
        //    mapfunctions::getMapInformation(),
        //    cachefunctions::getLocations()
        //);

        // Check that an id is set
        if( isset($GLOBALS['current_tile']) )
        {

            // Check that it's a claimable territory
            if( in_array('claimable', $GLOBALS['current_tile']) ){

                // Get the challenge
                $this->territory_challenge = $GLOBALS['database']->fetch_data("SELECT * FROM `territory_challenge` WHERE `location` = '".str_replace('\'','\\\'',$GLOBALS['current_tile'][1])."' LIMIT 1");

            }
            else{
                throw new Exception( $GLOBALS['current_tile'][1]." is not a claimable territory.");
            }
        }
        else{
            throw new Exception("Could not determine which territory you're in.");
        }
    }

    // Territory status
    public function territory_status(){

        // Information to show user
        if( $this->territory_challenge == "0 rows" ){
            $information = "There are currently no territory challenges active here in ".$GLOBALS['current_tile'][1];
        }
        else{
            $information = $this->territory_challenge[0]['challenger']." has challenged ".$this->territory_challenge[0]['challenged']." for the ownership of ".$this->territory_challenge[0]['location'].". ";
        }
        $GLOBALS['template']->assign('information', $information);

        // Get the main template
        $GLOBALS['template']->assign('contentLoad', './templates/content/terr_challenges/statusPage.tpl');

        // Battle Status
        if( $this->territory_challenge !== "0 rows" ){

            // Check the time
            if($GLOBALS['user']->load_time >= $this->territory_challenge[0]['start_time']){

                // End the territory battle after a certain period
                if( ($GLOBALS['user']->load_time <= $this->territory_challenge[0]['start_time'] + 3600) ){

                    //they do not belong to this challenge
                    if($GLOBALS['userdata'][0]['village'] == $this->territory_challenge[0]['challenger'] || $GLOBALS['userdata'][0]['village'] == $this->territory_challenge[0]['challenged'])
                    {
                        try
                        {
                            //get lock on challenge
                            $GLOBALS['database']->get_lock('battle',$this->territory_challenge[0]['id'],__METHOD__);
                            
                            //start transaction
                            $GLOBALS['database']->transaction_start();

                            $rank = '';
                            if($GLOBALS['userdata'][0]['rank_id'] == 5)
                                $rank = 'e_jounin';
                            else if($GLOBALS['userdata'][0]['rank_id'] == 4)
                                $rank = 'jounin';
                            else
                                $rank = 'chuunin';
                                
                            $status = $this->territory_challenge[0][$rank.'_status'];

                            //check battle status of your rank
                            if($status == 'pre')
                            {
                                //check for 3 users on each side.
                                $challenged_starters_count = substr_count($this->territory_challenge[0][$rank.'_challenged_starters'],'|');
                                $challenger_starters_count = substr_count($this->territory_challenge[0][$rank.'_challenger_starters'],'|');

                                //if( $challenged_starters_count >= 3 && $challenger_starters_count >= 3)
                                if( $challenged_starters_count >= 1 && $challenger_starters_count >= 1)
                                {
                                    //try to start battle
                                    // Get the challenger guard
                                    $guardID = Data::$VILLAGE_GUARDIANS[ $this->territory_challenge[0]['challenger'] ][ $GLOBALS['userdata'][0]['rank_id']-3 ];
                                    $query = "SELECT * FROM `ai` WHERE `id` = '". addslashes($guardID) ."' LIMIT 1";
                                    $challengerGuardian = $GLOBALS['database']->fetch_data($query);

                                    // Get the challenged guard
                                    $guardID = Data::$VILLAGE_GUARDIANS[ $this->territory_challenge[0]['challenged'] ][ $GLOBALS['userdata'][0]['rank_id']-3 ];
                                    $query = "SELECT * FROM `ai` WHERE `id` = '". addslashes($guardID) ."' LIMIT 1";
                                    $challengedGuardian = $GLOBALS['database']->fetch_data($query);

                                    $users = array();
                                    //$users[] = array('id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village']);
                                    if($this->territory_challenge[0][$rank.'_challenged_starters'] != '')
                                        foreach(explode('|',$this->territory_challenge[0][$rank.'_challenged_starters']) as $uid)
                                        {
                                            if($uid != '')
                                                $users[] = array('id'=>$uid, 'team_or_extra_data'=>$this->territory_challenge[0]['challenged']);
                                        }

                                    if($this->territory_challenge[0][$rank.'_challenger_starters'] != '')
                                        foreach(explode('|',$this->territory_challenge[0][$rank.'_challenger_starters']) as $uid)
                                        {
                                            if($uid != '')
                                                $users[] = array('id'=>$uid, 'team_or_extra_data'=>$this->territory_challenge[0]['challenger']);
                                        }

                                    //building ai instertion variable
                                    $ai = array( array('id'=>$challengerGuardian[0]['id'],'team'=>$this->territory_challenge[0]['challenger']),
                                                 array('id'=>$challengedGuardian[0]['id'],'team'=>$this->territory_challenge[0]['challenged'])  );

                                    //starting/getting battle
                                    $battle =
                                        BattleStarter::startBattle(
                                            $users,
                                            $ai,
                                            BattleStarter::territory,
                                            false,
                                            array(  'rank'=>$rank,
                                                    'end_time'=>$this->territory_challenge[0]['start_time'] + 3600,
                                                    'challenger'=>$this->territory_challenge[0]['challenger'],
                                                    'challenged'=>$this->territory_challenge[0]['challenged'],
                                                    'id'=>$this->territory_challenge[0]['id'],
                                                    'location'=>$this->territory_challenge[0]['location']),
                                            true,
                                            $GLOBALS['userdata'][0]['rank_id'].$this->territory_challenge[0]['id'].BattleStarter::territory);

                                    //updating the turn timer to 3 1/2 minutes
                                    $battle->turn_timer = time() + 60;

                                    //saving changes.
                                    $battle->updateCache();

                                    //mark battle as run
                                    $query = "UPDATE `territory_challenge` SET `".($rank.'_status')."` = 'run' WHERE `territory_challenge`.`id` = ".$this->territory_challenge[0]['id'];

                                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                                    catch (Exception $e)
                                    {
                                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                                        catch (Exception $e)
                                        {
                                            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                                            catch (Exception $e)
                                            {
                                                $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                                                throw $e;
                                            }
                                        }
                                    }

                                    //give link to go to battle
                                    $this->showMessage('<a href="?id=113">JOIN</a>','The Battle Has Started!');
                                }
                                //else
                                else
                                {
                                    $winner ='';
                                    if($challenged_starters_count < $challenger_starters_count)
                                        $winner = 'challenger';

                                    else
                                        $winner = 'challenged';

                                    $users = explode('|', $this->territory_challenge[0][$rank.'_challenged_starters'].$this->territory_challenge[0][$rank.'_challenger_starters']);
                                    if($users[count($users)-1] == '')
                                        unset($users[count($users)-1]);
                                    $users = implode(',',$users);


                                    $query = 'UPDATE `users`, `territory_challenge` '.
                                                'SET `status` = "awake", `battle_id` = 0, `'.$rank.'_status'.'` = "'.$winner.'" '.
                                                'WHERE `users`.`id` in ('.$users.') AND `territory_challenge`.`id` = '.$this->territory_challenge[0]['id'];

                                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                                    catch (Exception $e)
                                    {
                                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                                        catch (Exception $e)
                                        {
                                            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                                            catch (Exception $e)
                                            {
                                                $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                                                throw $e;
                                            }
                                        }
                                    }
                                }
                            }

                            //if run
                            else if($status == 'run')
                            {

                                $uids = explode('|',
                                    $this->territory_challenge[0]['chuunin_challenged_starters'].
                                    $this->territory_challenge[0]['chuunin_challenger_starters'].
                                    $this->territory_challenge[0]['jounin_challenged_starters'].
                                    $this->territory_challenge[0]['jounin_challenger_starters'].
                                    $this->territory_challenge[0]['e_jounin_challenged_starters'].
                                    $this->territory_challenge[0]['e_jounin_challenger_starters']);

                                foreach($uids as $key => $id)
                                    if($id == '')
                                        unset($uids[$key]);

                                //check if in a battle
                                if(in_array($_SESSION['uid'], $uids))
                                    //give link to battle
                                    if($GLOBALS['userdata'][0]['status'] == 'combat' || $GLOBALS['userdata'][0]['status'] == 'exiting_combat')
                                        $this->showMessage('<a href="?id=113">JOIN</a>','The Battle Has Started!');
                                    else
                                    {
                                        $message1 = 'The Chuunin battle';
                                        $message2 = 'The Jounin battle';
                                        $message3 = 'The Elite Jounin battle';

                                        if($this->territory_challenge[0]['chuunin_status'] == 'pre')
                                            $message1 .= ' has not begun yet.<br><br>';
                                        else if($this->territory_challenge[0]['chuunin_status'] == 'run')
                                            $message1 .= ' has not finished yet.<br><br>';
                                        else if($this->territory_challenge[0]['chuunin_status'] == 'challenged')
                                            $message1 .= ' has ben won by the challenged.<br><br>';
                                        else if($this->territory_challenge[0]['chuunin_status'] == 'challenger')
                                            $message1 .= ' has ben won by the challenger.<br><br>';

                                        if($this->territory_challenge[0]['jounin_status'] == 'pre')
                                            $message2 .= ' has not begun yet.<br><br>';
                                        else if($this->territory_challenge[0]['jounin_status'] == 'run')
                                            $message2 .= ' has not finished yet.<br><br>';
                                        else if($this->territory_challenge[0]['jounin_status'] == 'challenged')
                                            $message2 .= ' has ben won by the challenged.<br><br>';
                                        else if($this->territory_challenge[0]['jounin_status'] == 'challenger')
                                            $message2 .= ' has ben won by the challenger.<br><br>';

                                        if($this->territory_challenge[0]['e_jounin_status'] == 'pre')
                                            $message3 .= ' has not begun yet.<br><br>';
                                        else if($this->territory_challenge[0]['e_jounin_status'] == 'run')
                                            $message3 .= ' has not finished yet.<br><br>';
                                        else if($this->territory_challenge[0]['e_jounin_status'] == 'challenged')
                                            $message3 .= ' has ben won by the challenged.<br><br>';
                                        else if($this->territory_challenge[0]['e_jounin_status'] == 'challenger')
                                            $message3 .= ' has ben won by the challenger.<br><br>';


                                        $this->showMessage($message1.$message2.$message3,'The Territory Challenge\'s Status.');
                                    }

                                //else
                                else
                                {
                                    if (!isset($_POST['Submit']))
                                        $GLOBALS['page']->Confirm("Would you like to join the territory challenge?", 'Territory Challenge', 'Yes!');

                                    else
                                    {
                                       $battle_id = $GLOBALS['userdata'][0]['rank_id'].$this->territory_challenge[0]['id'].BattleStarter::territory;

                                       //opening the battle
                                       $battle = new Battle($battle_id, 15, false, true);

                                       //adding the user to the battle
                                       $battle->addUser($_SESSION['uid'], array('team'=>$GLOBALS['userdata'][0]['village']));

                                       //saving changes
                                       $battle->updateCache();

                                       //add uid to starters and update user status
                                       $target = '';
                                       if($GLOBALS['userdata'][0]['village'] == $this->territory_challenge[0]['challenged'])
                                       {
                                           if($GLOBALS['userdata'][0]['rank_id'] == 5)
                                               $target = 'e_jounin_challenged_starters';
                                           else if($GLOBALS['userdata'][0]['rank_id'] == 4)
                                               $target = 'jounin_challenged_starters';
                                           else
                                               $target = 'chuunin_challenged_starters';
                                       }
                                       else
                                       {
                                           if($GLOBALS['userdata'][0]['rank_id'] == 5)
                                               $target = 'e_jounin_challenger_starters';
                                           else if($GLOBALS['userdata'][0]['rank_id'] == 4)
                                               $target = 'jounin_challenger_starters';
                                           else
                                               $target = 'chuunin_challenger_starters';

                                       }

                                       $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                                       $query = "UPDATE `users`, `territory_challenge` SET `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$battle_id."', `".$target."` = CONCAT(`".$target."`,'".$_SESSION['uid']."|') WHERE `users`.`id` = ".$_SESSION['uid']." AND `territory_challenge`.`id` = ".$this->territory_challenge[0]['id'];
                                       $GLOBALS['userdata'][0]['database_fallback'] = 0;
                                       $GLOBALS['userdata'][0]['status'] = 'combat';
                                       $GLOBALS['template']->assign('userStatus', 'combat');

                                       try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                                       catch (Exception $e)
                                       {
                                           try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                                           catch (Exception $e)
                                           {
                                               try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                                               catch (Exception $e)
                                               {
                                                   $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                                                   throw $e;
                                               }
                                           }
                                       }

                                       //give link to join battle
                                       $this->showMessage('<a href="?id=113">Go</a>','You Have Joined The Battle!');
                                    }
                                }
                            }

                            //if over state so
                            else
                            {
                                if($GLOBALS['userdata'][0]['rank_id'] == 3)
                                    $this->showMessage('It was won by the '.$status.'.','The Chuunin Battle Is Over!');

                                if($GLOBALS['userdata'][0]['rank_id'] == 4)
                                    $this->showMessage('It was won by the '.$status.'.','The Jounin Battle Is Over!');   
                                   
                                if($GLOBALS['userdata'][0]['rank_id'] == 5)
                                    $this->showMessage('It was won by the '.$status.'.','The Elite Jounin Battle Is Over!');   
                            }

                            //commit transaction
                            $GLOBALS['database']->transaction_commit();

                            //release lock on challenge
                            $GLOBALS['database']->release_lock('battle',$this->territory_challenge[0]['id']);
                        }
                        catch (Exception $e)
                        {
                            //rollback transaction
                            $GLOBALS['database']->transaction_rollback();

                            //release lock on chalange
                            $GLOBALS['database']->release_lock('battle',$this->territory_challenge[0]['id']);

                            //throw exception
                            throw new Exception('Please try again. '.$e);
                        }
                    }
                    else
                        $this->showMessage('Your village does not belong to this challenge.');

                }
                else{
                    $this->showMessage('An hour has passed and the territory battle has been concluded.');
                }
             }
             else
            { // mark the user as joining
                
                $uids = explode('|',
                $this->territory_challenge[0]['chuunin_challenged_starters'].
                $this->territory_challenge[0]['chuunin_challenger_starters'].
                $this->territory_challenge[0]['jounin_challenged_starters'].
                $this->territory_challenge[0]['jounin_challenger_starters'].
                $this->territory_challenge[0]['e_jounin_challenged_starters'].
                $this->territory_challenge[0]['e_jounin_challenger_starters']);

                foreach($uids as $key => $id)
                    if($id == '')
                        unset($uids[$key]);    

                if (!isset($_POST['Submit']) && !in_array($_SESSION['uid'], $uids))
                    $GLOBALS['page']->Confirm("Would you like to join the queue for the territory challenge?", 'Territory Challenge', 'Yes!');

                else
                {
                    $this->showMessage('The battle will start in '.functions::convert_time( $this->territory_challenge[0]['start_time'] - $GLOBALS['user']->load_time, 'challengestart'));

                    try
                    {
                        //double check your village against the challenge's villages
                        //and check that the territory_challenge is marked as pre
                        if(($GLOBALS['userdata'][0]['village'] == $this->territory_challenge[0]['challenged'] || $GLOBALS['userdata'][0]['village'] == $this->territory_challenge[0]['challenger']) )
                        {
                            //get lock on challenge
                            $GLOBALS['database']->get_lock('battle',$this->territory_challenge[0]['id'],__METHOD__);
                            
                            //start transaction
                            $GLOBALS['database']->transaction_start();

                            //add your self to the challenge
                            //change your status to in combat
                            $target = '';
                            $battle_id = $GLOBALS['userdata'][0]['rank_id'].$this->territory_challenge[0]['id'].BattleStarter::territory;

                            if($GLOBALS['userdata'][0]['village'] == $this->territory_challenge[0]['challenged'])
                            {
                                if($GLOBALS['userdata'][0]['rank_id'] == 5)
                                    $target = 'e_jounin_challenged_starters';
                                else if($GLOBALS['userdata'][0]['rank_id'] == 4)
                                    $target = 'jounin_challenged_starters';
                                else
                                    $target = 'chuunin_challenged_starters';
                            }
                            else
                            {
                                if($GLOBALS['userdata'][0]['rank_id'] == 5)
                                    $target = 'e_jounin_challenger_starters';
                                else if($GLOBALS['userdata'][0]['rank_id'] == 4)
                                    $target = 'jounin_challenger_starters';
                                else
                                    $target = 'chuunin_challenger_starters';
                            }

                            $current_starters = explode('|',$this->territory_challenge[0][$target]);

                            //add check to make sure we are not already in battle here.
                            if(!in_array($_SESSION['uid'],$current_starters))
                            {
                                $query = "UPDATE `users`, `territory_challenge` SET `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$battle_id."', `".$target."` = CONCAT(`".$target."`,'".$_SESSION['uid']."|') WHERE `users`.`id` = ".$_SESSION['uid']." AND `territory_challenge`.`id` = ".$this->territory_challenge[0]['id'];
                                $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                                $GLOBALS['userdata'][0]['status'] = 'combat';
                                $GLOBALS['template']->assign('userStatus', 'combat');
                                $GLOBALS['userdata'][0]['database_fallback'] = 0;

                                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                                    catch (Exception $e)
                                    {
                                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                                        catch (Exception $e)
                                        {
                                            $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                                            throw $e;
                                        }
                                    }
                                }
                            }
                            else if($GLOBALS['userdata'][0]['status'] != 'combat' && $GLOBALS['userdata'][0]['status'] != 'exiting_combat')
                            {
                                $query = "UPDATE `users` SET `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$battle_id."' WHERE `users`.`id` = ".$_SESSION['uid'];
                                $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                                $GLOBALS['userdata'][0]['status'] = 'combat';
                                $GLOBALS['template']->assign('userStatus', 'combat');
                                $GLOBALS['userdata'][0]['database_fallback'] = 0;

                                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                                    catch (Exception $e)
                                    {
                                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                                        catch (Exception $e)
                                        {
                                            $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                                            throw $e;
                                        }
                                    }
                                }
                            }


                            //commit transaction
                            $GLOBALS['database']->transaction_commit();

                            //release lock on challenge
                            $GLOBALS['database']->release_lock('battle',$this->territory_challenge[0]['id']);
                        }
                        else
                            throw new Exception('You can not join this battle.');
                    }
                    //if fail
                    catch (Exception $e)
                    {
                        //rollback transaction
                        $GLOBALS['database']->transaction_rollback();

                        //release lock on chalange
                        $GLOBALS['database']->release_lock('battle',$this->territory_challenge[0]['id']);

                        //throw exception
                        throw new Exception('Please try again.');
                    }
                }
             }
        }
    }

    // A message poster for this class
    private function showMessage( $message ){
        $GLOBALS['page']->Message(
            $message, // The message
            "Battle Status", // Title
            false, // Return link
            false, // Return link label
            "terrStatusMessage" // Smarty variable to save message in
        );
    }

}
new fight();