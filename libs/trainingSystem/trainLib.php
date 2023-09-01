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

require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

// The main training library
class trainLib {

    //** Main function for setting up a training system **//
    public function setupTrainingSystem( $params ) {

        // Save the params
        $this->params = $params;

        // Set the name of the system
        $this->systemName = isset($this->params['systemName']) ? $this->params['systemName'] : "Training System";

        // Try-catch setup
        try{

            // Decide what page to show
            if( isset($_REQUEST['page']) && $_REQUEST['page'] == "special" ){

                // Get special jutsu
                $this->getSpecialJutsu();

            }
            elseif ( !isset($_REQUEST['train']) ){

                // Set the main page
                $this->showTrainingTypes();
            }
            else{

                // Check type
                switch( $_REQUEST['train'] ){
                    case "gen":
                    case "nin":
                    case "tai":
                    case "weap":
                        if ( isset($_REQUEST['train_amount']) ) {
                            $this->doStatTraining();
                        }
                        elseif( isset($_REQUEST['train_type']) ){
                            $this->showStatAmountForm();
                        }
                        else{
                            $this->showStatTypeForm();
                        }
                    break;
                    case "jutsu":
                        if (isset($_REQUEST['jid'])) {
                            $this->doJutsuTraining();
                        }
                        elseif( isset($_REQUEST['jutsu_type']) &&
                                isset($_REQUEST['attack_type']) &&
                                isset($_REQUEST['rank_type']) &&
                                isset($_REQUEST['element']) )
                        {
                            $this->showJutsuList();
                        }
                        else{
                            $this->showJutsuSelection();
                        }
                    break;
                    case "mastery":
                        if ( isset($_REQUEST['jid']) &&
                             isset($_REQUEST['train_amount'])
                        ) {
                            $this->doMasteryTraining();
                        }
                        elseif( isset($_REQUEST['jid']) ){
                            $this->showMasteryAmountForm();
                        }
                        else{
                            $this->showMasterList();
                        }
                    break;
                    /*
                    case "elemental_mastery":
                        if ( isset($_REQUEST['option']) &&
                             isset($_REQUEST['train_amount'])
                        ) {
                            $this->doElementalTraining();
                        }
                        elseif( isset($_REQUEST['option']) ){
                            $this->showElementalAmountForm();
                        }
                        else{
                            $this->showElementalList();
                        }
                    break; */
                    case "strength":
                    case "speed":
                    case "intelligence":
                    case "willpower":
                        if (isset($_REQUEST['train_amount'])) {
                            $this->doGeneralTraining();
                        }
                        else{
                            $this->showGeneralAmountForm();
                        }
                    break;
                    default:
                        throw new Exception("Messing around can get you in trouble. ".$_REQUEST['train'].".");
                    break;
                }
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , $this->systemName, 'id='.$_REQUEST['id'],'Return');
        }
    }

    // Page validation codes
    private function getValidationCode( $userID, $uniqueIdentifier, $timeOffset = 0 ){
        return md5( ($GLOBALS['user']->load_time - $timeOffset) . "-" . $userID . "-" .$uniqueIdentifier);
    }

    // Validate click
    private function checkCode( $userID, $uniqueIdentifier ){
        $timeArr = array();
        for ($counter = 0; $counter <= 15; $counter++) {
            $timeArr[] = $this->getValidationCode($userID, $uniqueIdentifier, $counter);
        }

        // Test Encryption code for the last 15 seconds
        if ( in_array($_REQUEST['code'], $timeArr, true) ){
            return true;
        }
        return false;
    }

    // Main page, show all training options
    public function showTrainingWrapper()
    {
        // Get user data, no lock
        $this->getUserData();

        // Avatar
        $GLOBALS['template']->assign('avatar', functions::getAvatar($this->user[0]['id']));

        // Send user information to smarty
        $GLOBALS['template']->assign('user', $this->user[0]);

        // Take the thing currently stored in contentLoad, and store it in wrapLoad instead
        $contentLoad = $GLOBALS['template']->tpl_vars['contentLoad']->value;

        if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
            $contentLoad = str_replace('.tpl','_mf.tpl',$contentLoad);

        $GLOBALS['template']->assign('wrapLoad', $contentLoad);

        // Retrieve the main overview page
        $GLOBALS['template']->assign('contentLoad', './templates/content/training/trainingWrapper.tpl');

        // Things for the backend to work
        $GLOBALS['template']->assign('trainToken', $this->setTrainingToken() );
        $GLOBALS['template']->assign('setupData', $this->encodeSetup() );
    }

    // Show training types
    public function showTrainingTypes(){

        // Create input options
        $options = array();

        // Generals
        foreach( $this->params['availableGenerals'] as $entry){
            $options[ $entry ] = ucfirst($entry) . " Training";
        }

        // Stats
        if( !empty($this->params['availableStats']) ){
            foreach( $this->params['availableStats'] as $entry ){
                switch( $entry ){
                    case "nin": $options[ $entry ] = "Ninjutsu Training"; break;
                    case "gen": $options[ $entry ] = "Genjutsu Training"; break;
                    case "tai": $options[ $entry ] = "Taijutsu Training"; break;
                    case "weap": $options[ $entry ] = "Bukijutsu Training"; break;
                }
            }
        }


        // Jutsu training
        if( !empty( $this->params['jutsuTypes'] ) ){
            $options[ "jutsu" ] = "Jutsu Training";
        }

        // Jutsu mastery
        if( isset( $this->params['jutsuMastery'] ) &&  $this->params['jutsuMastery'] == true ){
            $options[ "mastery" ] = "Jutsu Mastery";
        }

        // Elemental mastery
        if( isset( $this->params['elementalMastery'] ) &&  $this->params['elementalMastery'] == true ){
            $options[ "elemental_mastery" ] = "Elemental Mastery";
        }

        // Message
        if( isset( $this->params['mainText'] ) ){
            $mainDescription = $this->params['mainText'];
        }
        else{
            $mainDescription = "You prepare for your training. What do you want to do:";
        }

        // Create the input form
        $GLOBALS['page']->UserInput(
                $mainDescription,
                $this->systemName,
                array(
                    // A select box
                    array(
                        "infoText"=>"",
                        "inputFieldName"=>"train",
                        "type"=>"select",
                        "inputFieldValue"=> $options
                    )
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                false ,
                "trainingForm"
        );
    }


    //** All Functions below are related to offense/defence training **//

    // Shortstat to long stat
    private function shortStatLongStat( $stat ){
        switch($stat){
            case "nin": return "ninjutsu"; break;
            case "gen": return "genjutsu"; break;
            case "tai": return "taijutsu"; break;
            case "weap": return "bukijutsu"; break;
            default: throw new Exception("Could not identify stat type"); break;
        }
    }

    // Let the user pick training style
    protected function showStatTypeForm(){

        // Check type
        if( !in_array( $_REQUEST['train'], $this->params['availableStats'] , true ) ){
            throw new Exception("You cannot train this stat here");
        }

        // Create the input form
        $GLOBALS['page']->UserInput(
                "As a ninja it is important to train hard, both on defensive and offensive ".$this->shortStatLongStat($_REQUEST['train'])." techniques. <br>You have following options:",
                "Training System",
                array(
                    // A select box
                    array(
                        "infoText"=>"",
                        "inputFieldName"=>"train_type",
                        "type"=>"select",
                        "inputFieldValue"=> array(
                           "Offensive" => "Offensive Training",
                           "Defensive" => "Defensive Training"
                        )
                    ),
                    // Pass on type in a hidden entry in the form
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train'])
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );
    }

    // 	Set Offense/Defense Statistics Amount
    protected function showStatAmountForm() {

        // Get user
        $this->user = $GLOBALS['database']->fetch_data('
                SELECT
                    `users_statistics`.`tai_off`, `users_statistics`.`nin_off`,
                    `users_statistics`.`gen_off`, `users_statistics`.`weap_off`,
                    `users_statistics`.`tai_def`, `users_statistics`.`nin_def`,
                    `users_statistics`.`gen_def`, `users_statistics`.`weap_def`,
                    `users_statistics`.`experience`,
                    `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`,
                    `users_statistics`.`uid`,
                    `users_statistics`.`rank_id`
                FROM `users_statistics`
                WHERE
                    `users_statistics`.`uid` = '.$_SESSION['uid'].'
                LIMIT 1'
        );

        // Get Offense/Defense Type (Ninjutsu, Weapon, etc) and Chakra/Stamina Costs
        $this->train_data = $this->getStatTrainInfo( $_REQUEST['train'] , $_REQUEST['train_type'] );

        // Create the input form
        $GLOBALS['page']->UserInput(
                "As a ninja it is important to train hard, how many times do you want to train your ".( isset($_REQUEST['train_type']) ? strtolower($_REQUEST['train_type']) : "" )." ".$this->shortStatLongStat($_REQUEST['train'])."?<br>You have following options:",
                "Training System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"train_amount",
                        "type"=>"range",
                        'inputFieldValue' => $this->train_data['max_times'] ? $this->train_data['max_times'] : 0,
                        'inputFieldMin' => 0,
                        'inputFieldMax' => $this->train_data['max_times'],
                        'inputFieldDisabled' => $this->train_data['max_times'] == 0
                    ),
                    // Pass on type in a hidden entry in the form
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train']),
                    array("type"=>"hidden", "inputFieldName"=>"train_type", "inputFieldValue"=>$_REQUEST['train_type'])
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );
    }

    // Implement Offense/Defense Statistics Training
    protected function doStatTraining() {

        //  Start Transaction
        $GLOBALS['database']->transaction_start();

        //  Grab Necessary User Information to Update
        if(!( $this->user = $GLOBALS['database']->fetch_data('
                SELECT
                    `users_statistics`.`tai_off`, `users_statistics`.`nin_off`,
                    `users_statistics`.`gen_off`, `users_statistics`.`weap_off`,
                    `users_statistics`.`tai_def`, `users_statistics`.`nin_def`,
                    `users_statistics`.`gen_def`, `users_statistics`.`weap_def`,
                    `users_statistics`.`experience`,
                    `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`,
                    `users_statistics`.`max_cha`, `users_statistics`.`max_sta`,
                    `users_statistics`.`uid`,
                    `users_statistics`.`rank_id`
                FROM `users_statistics`
                WHERE
                    `users_statistics`.`uid` = '.$_SESSION['uid'].'
                LIMIT 1
                FOR UPDATE')
        )) {
            throw new Exception('An error occured while training, please try again!'); // Query Failure, Rollback Transaction
        }

        // Get Offense/Defense Type (Ninjutsu, Weapon, etc) and Chakra/Stamina Costs
        $this->train_data = $this->getStatTrainInfo( $_REQUEST['train'] , $_REQUEST['train_type'] );

        //  Obtain Training Amount. Check it, this should ensure we don't overcap chakra/stamina and the stats
        $task_amount = $this->checkTrainingAmount( $_REQUEST['train_amount'] );

        // Multiply gains & costs with times
        $this->multipleData( $task_amount );

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedTraining") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $this->train_data['stat_gain'] *= round($event['data'] / 100,2);
                $this->train_data['experience_gain'] *= round($event['data'] / 100,2);
                $this->train_data['chakra_gain'] *= round($event['data'] / 100,2);
                $this->train_data['stamina_gain'] *= round($event['data'] / 100,2);
            }
        }


        // Check for global event modifications
        if( ($event = functions::getGlobalEvent("IncreasedTrainingLowRank")) && $this->user[0]['rank_id'] <= 2){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $this->train_data['stat_gain'] *= round($event['data'] / 100,2);
                $this->train_data['experience_gain'] *= round($event['data'] / 100,2);
                $this->train_data['chakra_gain'] *= round($event['data'] / 100,2);
                $this->train_data['stamina_gain'] *= round($event['data'] / 100,2);
            }
        }


        // New value of stat
        $newValue = $this->calcNewValue(
                $this->train_data['stat_type'],
                $this->train_data['stat_gain'],
                Data::${'ST_MAX_'.$this->user[0]['rank_id']}
        );

        //  Attempt to Execute Training and Status
        if (($GLOBALS['database']->execute_query('
            UPDATE `users_statistics`
            SET `users_statistics`.`cur_cha` = `users_statistics`.`cur_cha` - '.$this->train_data['cha_cost'].',
                `users_statistics`.`cur_sta` = `users_statistics`.`cur_sta` - '.$this->train_data['sta_cost'].',
                `users_statistics`.`max_cha` = `users_statistics`.`max_cha` + '.$this->train_data['chakra_gain'].',
                `users_statistics`.`max_sta` = `users_statistics`.`max_sta` + '.$this->train_data['stamina_gain'].',
                `users_statistics`.`'.$this->train_data['stat_type'].'` = '.$newValue.',
                `users_statistics`.`experience` = `users_statistics`.`experience` + '.$this->train_data['experience_gain'].'
            WHERE
                `users_statistics`.`uid` = '.$this->user[0]['uid'].' LIMIT 1')) === false)
        {
            throw new Exception('There was an error updating the user data'); // Query Failed, Rollback Transaction
        }

        $stat = $this->train_data['stat_type'];
        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$GLOBALS['userdata'][0]['cur_cha']    - $this->train_data['cha_cost'], 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$GLOBALS['userdata'][0]['cur_sta']    - $this->train_data['sta_cost'], 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$GLOBALS['userdata'][0]['max_cha']    + $this->train_data['chakra_gain'], 'old'=>$GLOBALS['userdata'][0]['max_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$GLOBALS['userdata'][0]['max_sta']    + $this->train_data['stamina_gain'], 'old'=>$GLOBALS['userdata'][0]['max_sta'] ));
        $GLOBALS['Events']->acceptEvent('experience', array('new'=>$GLOBALS['userdata'][0]['experience'] + $this->train_data['experience_gain'], 'old'=>$GLOBALS['userdata'][0]['experience'] ));
        $GLOBALS['Events']->acceptEvent('stats_'.$stat, array('new'=> $newValue, 'old'=>$GLOBALS['userdata'][0][$stat] ));

        // Instant update globals (no js)
        $this->instantUpdateUserdata();

        // Message
        $GLOBALS['page']->Message( $this->getImprovement() , 'Training System', 'id='.$_REQUEST['id'],'Return');

        // Commit Transaction Data
        $GLOBALS['database']->transaction_commit();
    }


    // Obtain Base Offense/Defense Stat Costs
    protected function getStatTrainInfo( $stat , $type ) {

        // An Array with Chakra/Stamina Costs etc
        $data = array(
            'stat' => '',
            'stat_type' => '',
            'stat_gain' => 0,
            'cha_cost' => 0,
            'sta_cost' => 0,
            'type' => '',
            'max_times' => 0,
            'stamina_gain' => 0,
            'chakra_gain' => 0
        );

        // Set the costs depending on the training type
        switch( $stat ) { // Offense/Defense Type
            case('gen'): // Genjutsu
            case('nin'):  // Ninjutsu
                $data['stat'] = ($stat === 'gen') ? 'Genjutsu' : 'Ninjutsu'; // Training Type
                $data['cha_cost'] = 5;
                $data['sta_cost'] = 2.5;
            break;
            case('tai'): // Taijutsu
            case('weap'):  // Weapon
                $data['stat'] = ($stat === 'tai') ? 'Taijutsu' : 'Bukijutsu'; // Training Type
                $data['cha_cost'] = 2.5;
                $data['sta_cost'] = 5;
            break;
            default: throw new Exception("The type of stat you're trying to train does not make sense");
        }

        // Chakra/Stamina Gains
        $chakra_gain = $stamina_gain = 0;
        switch( $data['stat'] ) {
            case('Ninjutsu'):
            case('Genjutsu'): $data['chakra_gain'] = 0.05; break;
            case('Taijutsu'):
            case('Bukijutsu'): $data['stamina_gain'] = 0.05; break;
            default: throw new Exception("Could not figure out the training type: ".$type);
        }

        // Offense/Defense Stat Gain
        $data['stat_gain'] = 0.1;

        // Figure out if offensive/defensive
        switch( $type ){
            case "Offensive": $data['type'] = 'off'; break;
            case "Defensive": $data['type'] = 'def'; break;
            default: throw new Exception("Could not figure out if offensive of defensive training");
        }

        // Set the stat type in the form used in the DB
        $data['stat_type'] = $stat.'_'.$data['type'];

        // Max number of trainings according to cha / sta
        $cha_times = floor($this->user[0]['cur_cha'] / $data['cha_cost']); // Maximum number of training with chakra
        $sta_times = floor($this->user[0]['cur_sta'] / $data['sta_cost']); // Maximum number of training with stamina

        // Max number of trainings by stat caps
        $max_times = ($cha_times > $sta_times) ? $sta_times : $cha_times;
        $max_times = ($max_times < 0) ? 0 : $max_times;

        // Obtain Max training times based on capped stats
        $max_rank_pools = Data::${'ST_MAX_'.$this->user[0]['rank_id']};
        $max_pool_times = ($max_rank_pools - $this->user[0][$data['stat_type']]) / $data['stat_gain'];
        $max_pool_times = ($max_pool_times < 0) ? 0 : $max_pool_times;

        // If just about to cap, set to 1
        if( $max_pool_times > 0 && $max_pool_times < 1 ){
            $max_pool_times = 1;
        }

        // Determine actual max
        $data['max_times'] = ($max_times > $max_pool_times) ? $max_pool_times : $max_times;

        // Returnd ata
        return $data;
    }


    //** All Functions below are related to general training **//

    // 	Set General Statistics Amount
    protected function showGeneralAmountForm() {

        // Check type
        if( !in_array( $_REQUEST['train'], $this->params['availableGenerals'] , true ) ){
            throw new Exception("You cannot train this general here");
        }

        // Obtain Necessary User Information
        $this->user = $GLOBALS['database']->fetch_data('
            SELECT
                `users_statistics`.`'.$_REQUEST['train'].'`, `users_statistics`.`cur_cha`,
                `users_statistics`.`cur_sta`, `users_statistics`.`rank_id`
            FROM `users_statistics`
            WHERE `users_statistics`.`uid` = '.$_SESSION['uid'].'
            LIMIT 1'
        );

        // Get information
        $this->train_data = $this->getGeneralTrainInfo( $_REQUEST['train'] );

        // Create the input form
        $GLOBALS['page']->UserInput(
                'If you want to progress as a ninja, an important aspect is '.ucfirst($_REQUEST['train']).', so train hard!',
                "Training System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"train_amount",
                        "type"=>"range",
                        'inputFieldValue' => $this->train_data['max_times'] ? $this->train_data['max_times'] : 0,
                        'inputFieldMin' => 0,
                        'inputFieldMax' => $this->train_data['max_times'],
                        'inputFieldDisabled' => $this->train_data['max_times'] == 0
                    ),
                    // Pass on type in a hidden entry in the form
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train']),
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );

    }

    // 	Implement General Statistics Training
    protected function doGeneralTraining() {

        // Check type
        if( !in_array( $_REQUEST['train'], $this->params['availableGenerals'] , true ) ){
            throw new Exception("You cannot train this general here");
        }

        // Start Transaction
        $GLOBALS['database']->transaction_start();

        // Obtain General Type
        if(!($this->user = $GLOBALS['database']->fetch_data('
            SELECT
                `users_statistics`.`'.$_REQUEST['train'].'`, `users_statistics`.`uid`,
                `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`,
                `users_statistics`.`max_cha`, `users_statistics`.`max_sta`,
                `users_statistics`.`rank_id`
            FROM `users`
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` IN("awake", "jailed")
            LIMIT 1 FOR UPDATE'))
        ) {
            throw new Exception('An error occured while training, please try again!');
        }

        // Get information
        $this->train_data = $this->getGeneralTrainInfo( $_REQUEST['train'] );

        //  Obtain Training Amount. Check it, this should ensure we don't overcap chakra/stamina and the stats
        $task_amount = $this->checkTrainingAmount( $_REQUEST['train_amount'] );

        // Multiply gains & costs with times
        $this->multipleData( $task_amount , 100 );

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedTraining") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $this->train_data['stat_gain'] *= round($event['data'] / 100,2);
            }
        }


        // Check for global event modifications
        if( ($event = functions::getGlobalEvent("IncreasedTrainingLowRank")) && $this->user[0]['rank_id'] <= 2){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $this->train_data['stat_gain'] *= round($event['data'] / 100,2);
            }
        }


        // New value of stat
        $newValue = $this->calcNewValue(
                $_REQUEST['train'],
                $this->train_data['stat_gain'],
                Data::${'GEN_MAX_'.$this->user[0]['rank_id']}
        );

        // Update user
        if (($GLOBALS['database']->execute_query("
            UPDATE `users_statistics`
            SET `users_statistics`.`".$_REQUEST['train']."` = ".$newValue.",
                `users_statistics`.`cur_cha` = `users_statistics`.`cur_cha` - ".$this->train_data['cha_cost'].",
                `users_statistics`.`cur_sta` = `users_statistics`.`cur_sta` - ".$this->train_data['sta_cost'].",
                `users_statistics`.`max_cha` = `users_statistics`.`max_cha` + ".$this->train_data['chakra_gain'].",
                `users_statistics`.`max_sta` = `users_statistics`.`max_sta` + ".$this->train_data['stamina_gain'].",
                `users_statistics`.`experience` = `users_statistics`.`experience` + ".$this->train_data['experience_gain']."
            WHERE `users_statistics`.`uid` = ".$this->user[0]['uid']."
            LIMIT 1")) === false)
        {
                throw new Exception('There was an error updating the user data');
        }

        $stat = $_REQUEST['train'];
        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=> $GLOBALS['userdata'][0]['cur_cha']    - $this->train_data['cha_cost'], 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$GLOBALS['userdata'][0]['cur_sta']    - $this->train_data['sta_cost'], 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$GLOBALS['userdata'][0]['max_cha']    + $this->train_data['chakra_gain'], 'old'=>$GLOBALS['userdata'][0]['max_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$GLOBALS['userdata'][0]['max_sta']    + $this->train_data['stamina_gain'], 'old'=>$GLOBALS['userdata'][0]['max_sta'] ));
        $GLOBALS['Events']->acceptEvent('experience', array('new'=>$GLOBALS['userdata'][0]['experience'] + $this->train_data['experience_gain'], 'old'=>$GLOBALS['userdata'][0]['experience'] ));
        $GLOBALS['Events']->acceptEvent('stats_'.$stat, array('new'=>$GLOBALS['userdata'][0][$stat]        + $newValue, 'old'=>$GLOBALS['userdata'][0][$stat] ));


        // Instant update
        $this->instantUpdateUserdata();

        // Message
        $GLOBALS['page']->Message( $this->getImprovement() , 'Training System', 'id='.$_REQUEST['id'],'Return');

        $GLOBALS['database']->transaction_commit(); // Commit Transaction

    }

    // Obtain Base Offense/Defense Stat Costs
    protected function getGeneralTrainInfo( $type ) {

        // An Array with Chakra/Stamina Costs etc
        $data = array(
            'stat' => $type,
            'stat_type' => $type,
            'stat_gain' => 0,
            'cha_cost' => 0,
            'sta_cost' => 0,
            'max_times' => 0,
            'stamina_gain' => 0,
            'chakra_gain' => 0
        );

        switch($type) {
            case('intelligence'):
            case('willpower'):
                $data['cha_cost'] = 50;
                $data['sta_cost'] = 25;
            break;
            case('strength'):
            case('speed'):
                $data['cha_cost'] = 25;
                $data['sta_cost'] = 50;
            break;
            default: throw new Exception("The type of stat you're trying to train does not make sense: ".$type);
        }

        // Chakra/Stamina Gains
        switch( $type ) {
            case('willpower'):
            case('intelligence'): $data['chakra_gain'] = 0.5; break;
            case('strength'):
            case('speed'): $data['stamina_gain'] = 0.5; break;
        }

        // Offense/Defense Stat Gain
        $data['stat_gain'] = 0.1;

        // Max number of trainings according to cha / sta
        $cha_times = floor($this->user[0]['cur_cha'] / $data['cha_cost']); // Maximum number of training with chakra
        $sta_times = floor($this->user[0]['cur_sta'] / $data['sta_cost']); // Maximum number of training with stamina

        // Max number of trainings by stat caps
        $max_times = ($cha_times > $sta_times) ? $sta_times : $cha_times;
        $max_times = ($max_times < 0) ? 0 : $max_times;

        // Obtain Max training times based on capped stats
        $max_rank_pools = Data::${'GEN_MAX_'.$this->user[0]['rank_id']};
        $max_pool_times = ($max_rank_pools - $this->user[0][ $type ]) / $data['stat_gain'];
        $max_pool_times = ($max_pool_times < 0) ? 0 : $max_pool_times;

        // If just about to cap, set to 1
        if( $max_pool_times > 0 && $max_pool_times < 1 ){
            $max_pool_times = 1;
        }

        // Determine actual max
        $data['max_times'] = ($max_times > $max_pool_times) ? $max_pool_times : $max_times;

        // Returnd ata
        return $data;
    }

    //** All Functions below are related to jutsu training **//

    //	Provide User Jutsu Selection
    protected function showJutsuSelection() {
        $elements = new Elements();


        // Selection Lists
        $select1 = array();
        $select2 = array();
        $select3 = array();
        $select4 = array();

        // Get user information
        if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `bloodlines`.`name` AS "bloodline", `users`.`id`, `users`.`username`,
                `users_loyalty`.`village`,
                `users_statistics`.`specialization`, `users_statistics`.`rank_id`,
                `bloodlines`.`special_jutsu`,
                `villages`.`leader`,
                `clans`.`clan_jutsu`
            FROM `users`
                INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                LEFT JOIN `bloodlines` ON (`users`.`bloodline` LIKE CONCAT(`bloodlines`.`name`,"%"))
                LEFT JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                LEFT JOIN `clans` ON (`clans`.`id` = `users_preferences`.`clan`)
            WHERE `users`.`id` = '.$_SESSION['uid'].'
            LIMIT 1')))
        {
            throw new Exception('There was an error retrieving user information.');
        }

        // Existing Forbidden Jutsu Check
        if(!($forbidden = $GLOBALS['database']->fetch_data('SELECT `users_jutsu`.`jid`
            FROM `users_jutsu`, `jutsu`
            WHERE `users_jutsu`.`uid` = '.$this->user[0]['id'].' AND `jutsu`.`jutsu_type` = "forbidden"
                AND `users_jutsu`.`jid` = `jutsu`.`id` LIMIT 1'))) {
            throw new Exception('2');
        }

        // Forbidden Jutsu Selection
        if ($forbidden !== '0 rows') {
            $select1["forbidden"] = 'Forbidden Jutsu';
        }

        // Clan Jutsu
        if( $this->user[0]['clan_jutsu'] !== null ){
            $select1["clan"] = 'Clan Jutsu';
        }

        // Loyalty jutsu
        if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
            if( $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 15 ){
                $select1["loyalty"] = 'Loyalty Jutsu';
            }
        }

        // Bloodline Jutsu Selection
        if($this->user[0]['bloodline'] !== "" && $this->user[0]['bloodline'] !== "None" ){
            if ($this->user[0]['special_jutsu'] !== null && $this->user[0]['special_jutsu'] === 'yes') {
                $select1["bloodline"] = 'Bloodline Jutsu';
            }
        }

        // Kage Jutsu Selection
        if ($this->user[0]['leader'] !== null) {
            if ($this->user[0]['leader'] === $this->user[0]['username']) {
                $select1["kage"] = "Kage Jutsu";
            }
        }

        // Speciality Jutsu Selection
        if($this->user[0]['specialization'] !== "0:0") {
            $select2["highest"] = 'Speciality Jutsu';
        }

        // Jutsu Rank Selection
        foreach(array(1, 2, 3, 4, 5) as $value) {
            if($this->user[0]['rank_id'] >= $value) {
                $select3[''.$value.''] = Data::$RANKNAMES[$value];
            }
        }

        $temp = $elements->getUserElements();
        if($temp != false)
        {
            foreach ($temp as $element)
            {
                if(!empty($element))
                {
                    $select4[$element] = ucfirst($element);
                }
            }
        }

        $GLOBALS['template']->assign('train', $_REQUEST['train']);
        $GLOBALS['template']->assign('select1', $select1);
        $GLOBALS['template']->assign('select2', $select2);
        $GLOBALS['template']->assign('select3', $select3);
        $GLOBALS['template']->assign('select4', $select4);
        $GLOBALS['template']->assign('contentLoad', './templates/content/training/setJutsuType.tpl');
    }

    private function getElements() {
        $elements = $GLOBALS['database']->fetch_data("SELECT DISTINCT(`element`) FROM `jutsu`");

        $return = array();

        if (!empty($elements)) {
            foreach ($elements as $element) {
                $return[] = $element['element'];
            }
        }

        return $return;
    }

    //	Provide User with Jutsu Choices
    protected function showJutsuList() {
        $elements = new Elements();


        // Obtain Necessary User Information
        if(!($this->user = $GLOBALS['database']->fetch_data('
            SELECT
                `bloodlines`.`name` AS "bloodline", `users`.`username`, `users`.`id`,
                `users_loyalty`.`village`,
                `villages`.`leader`,
                `bloodlines`.`regen_increase`,`users_statistics`.`rank_id`,
                `clans`.`clan_jutsu`
            FROM `users`
                INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                LEFT JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                LEFT JOIN `bloodlines` ON (`users`.`bloodline` LIKE CONCAT(`bloodlines`.`name`,"%"))
                LEFT JOIN `clans` ON (`clans`.`id` = `users_preferences`.`clan`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1')))
        {
            throw new Exception('There was an error retrieving user information.');
        }

        // Locally save the three search selectors
        $atk_type = $_REQUEST['attack_type']; // Attack Type Selection
        $jts_type = $_REQUEST['jutsu_type']; // Jutsu Type Selection
        $rnk_type = $_REQUEST['rank_type']; // Rank Type Selection
        $element  = $_REQUEST['element'];

        if($element == 'none')
            $element = 'None';

        // Construct SELECT query
        $query = "";

        // Jutsu type
        switch( $jts_type ){
            case "normal":
            case "special":
            case "forbidden":
                $query .= " `jutsu`.`jutsu_type` = '".$jts_type."'";
            break;
            case "loyalty":
                $query .= " `jutsu`.`jutsu_type` = '".$jts_type."' AND `loyaltyRespectReq` <= '".$GLOBALS['userdata'][0]['vil_loyal_pts']."' ";
            break;
            case "village":
                $query .= " `jutsu`.`village` = '".$this->user[0]['village']."' AND (`jutsu_type` != 'forbidden' OR `users_jutsu`.`entry_id` IS NOT NULL) ";
            break;
            case "bloodline":
                $query .= " `jutsu`.`bloodline` = '".addslashes($this->user[0]['bloodline'])."'";
            break;
            case "kage":
                $query .= " `jutsu`.`kage` = 'yes'";
            break;
            case "clan":
                if( $this->user[0]['clan_jutsu'] !== null ){
                    $query .= " `jutsu_type` = 'clan' AND `id` = '".$this->user[0]['clan_jutsu']."'";
                }
                else{
                    throw new Exception("Your clan has not yet unlocked their jutsu.");
                }
            break;
            default: throw new Exception("Could not figure out what kind of jutsu you're looking for");
        }

        // Get elements of user & check jutsus
        $availableElements = $elements->getUserElements();

        if($availableElements != false)
        {
            $availableElements['None'] = 'None';
            $query .= " AND `jutsu`.`element` IN ('".implode("','",$availableElements)."') ";
        }
        // Never ever show event jutsus
        $query .= " AND `jutsu`.`jutsu_type` != 'event' ";

        // Bloodline Check
        if( !empty($this->user[0]['bloodline']) ) {
            $query .= ' AND `jutsu`.`bloodline` IN ("", "'.$this->user[0]['bloodline'].'")';
        }

        // Attack Type
        if( $atk_type !== "x" ){
            if( in_array( $atk_type, $this->params['jutsuAttackTypes'], true ) ){
                $query .= ' AND `jutsu`.`attack_type` = "'.$atk_type.'"';
            }
            else{
                throw new Exception("You cannot train jutsu with this attack type here");
            }
        }


        // Rank Type Check
        if( is_numeric($rnk_type) && $rnk_type > 0 && $rnk_type <= $this->user[0]['rank_id'] ){
            $query .= ' AND `jutsu`.`required_rank` = "'.$rnk_type.'"';
        }
        elseif( $rnk_type == "x" ){
            $query .= ' AND `jutsu`.`required_rank` <= "'.$this->user[0]['rank_id'].'"';
        }
        else{
            throw new Exception("You are not eligible for jutsus of this rank ID: ".$rnk_type);
        }

        // Obtain Jutsu Specifications based on Village
        $query .= ' AND `jutsu`.`village` IN ("", "'.$this->user[0]['village'].'")';

        // Kage jutsu
        if ($this->user[0]['leader'] !== null) {
            if ($this->user[0]['leader'] !== $this->user[0]['username']) {
                $query .= " AND `jutsu`.`kage` = 'no' ";
            }
        }

        if ($element !== "x"){
            if (in_array($element, $this->getElements()) || $element == 'none') {
                $query .= ' AND `jutsu`.`element` = "'.$element.'"';
            } else {
                throw new Exception('Unknown element: '.$element);
            }
        }

        // Select array
        $choices = $GLOBALS['database']->fetch_data("
            SELECT `jutsu`.`name`, `jutsu`.`id`, `jutsu`.`element`, `jutsu`.`jutsu_type`, `users_jutsu`.`jid`
            FROM `jutsu`
            LEFT JOIN `users_jutsu` ON (`users_jutsu`.`jid` = `jutsu`.`id` AND `users_jutsu`.`uid` = '".$_SESSION['uid']."')
            WHERE ".$query );

        // Filter off unowned forbidden jutsu
        if( $jts_type == "forbidden" || $jts_type == 'kage'){
            foreach( $choices as $key => $value ){
                if( $value['jid'] == null && $value['jutsu_type'] == 'forbidden' ){
                    unset( $choices[$key] );
                }
            }
            $choices = array_values($choices);
        }

        // Select array
        $selectArray = array();
        if( $choices !== "0 rows" ){
            foreach( $choices as $jutsu ){
                $selectArray[ $jutsu['id'] ] = $jutsu['name'];
            }
        }

        // Sort the array
        asort($selectArray);

        // Create the input form
        $GLOBALS['page']->UserInput(
"To succeed as a ninja knowledge and mastery of jutsu is of the greatest importance <br>
 You can train the following ".$jts_type." jutsu",
                "Training System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"jid",
                        "type"=>"select",
                        "inputFieldValue"=> $selectArray
                    ),
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train'])
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "?id=".$_REQUEST['id']."&amp;train=".$_REQUEST['train'] ,
                "trainingForm"
        );
    }

    //	Perform Jutsu Training | type-options are "standard" and "mastery"
    protected function doJutsuTraining() {

        // Start Transaction
        $GLOBALS['database']->transaction_start();

        // Set & Check the jutsu ID
        $jid = $_REQUEST['jid'];
        if (ctype_digit($jid) !== true) {
            throw new Exception("Could not understand what jutsu you're looking for.");
        }

        $elements = new elements();

        // Fetch Necessary Data From Database
        if(!($this->user = $GLOBALS['database']->fetch_data('SELECT
            `users`.`username`, `users`.`id`,
            `users_timer`.`jutsu`, `users_timer`.`jutsu_timer`,

            `users_statistics`.`rank_id`, `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`,
            `users_statistics`.`max_cha`, `users_statistics`.`money`,

            `bloodlines`.`regen_increase`,

            `users_loyalty`.`village` AS `user_village`, `users_loyalty`.`time_in_vil`,

            `jutsu`.`price`, `jutsu`.`price_increment`, `jutsu`.`jutsu_type`, `jutsu`.`required_rank`, `jutsu`.`bloodline`, `jutsu`.`village`,
            `jutsu`.`max_level`, `jutsu`.`kage`, `jutsu`.`name`, `jutsu`.`element`, `jutsu`.`loyaltyRespectReq`,

            `users_jutsu`.`level`,
            `villages`.`leader`
            FROM `users`
                LEFT JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                LEFT JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                LEFT JOIN `bloodlines` ON (`users`.`bloodline` LIKE CONCAT(`bloodlines`.`name`,"%"))
                LEFT JOIN `jutsu` ON (`jutsu`.`id` = '.$jid.')
                LEFT JOIN `users_jutsu` ON (`users_jutsu`.`uid` = `users`.`id` AND `users_jutsu`.`jid` = '.$jid.')
                LEFT JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` = "awake" LIMIT 1 FOR UPDATE')))
        {
            throw new Exception('There was an error retrieving the user data.');
        }

        // If user couldn't be retrieved
        if( $this->user == "0 rows" ){
            throw new Exception("There was an error collecting all the needed userdata.");
        }

        // Only train one jutsu at a time
        if( !empty($this->user[0]['jutsu_timer']) ){
            throw new Exception("You can not train more jutsus right now since you're already training");
        }

        // Get elements of user & check jutsu element
        $availableElements = $elements->getUserElements();
        $this->user[0]['element'] = strtolower($this->user[0]['element']);
        if( !in_array( $this->user[0]['element'], $availableElements, true) && !strtolower($this->user[0]['element']) == 'none' ){
            throw new Exception("You cannot train this. You do not match its elemental affinity. Its affinity is ".$this->user[0]['element'].".");
        }

        // Convenience store the jutsu data
        $jutsu_data = array();
        if ( isset($this->user[0]['jutsu_type']) && !empty($this->user[0]['jutsu_type']) ){
            $jutsu_data = array(
                'price' => $this->user[0]['price'],
                'price_increment' => $this->user[0]['price_increment'],
                'jutsu_type' => $this->user[0]['jutsu_type'],
                'required_rank' => $this->user[0]['required_rank'],
                'bloodline' => $this->user[0]['bloodline'],
                'village' => $this->user[0]['village'],
                'max_level' => $this->user[0]['max_level'],
                'kage' => $this->user[0]['kage'],
                'name' => $this->user[0]['name'],
                'loyaltyRespectReq' => $this->user[0]['loyaltyRespectReq']
            );
        }
        else {
            throw new Exception('This jutsu does not exist!');
        }

        //  Set some standard values for the jutsu
        $level = ($this->user[0]['level'] === null) ? 1 : $this->user[0]['level'] + 1;
        $price = $jutsu_data['price'] + ($jutsu_data['price_increment'] * $level);
        $pool_cost_cha = 90;
        $pool_cost_sta = 90;
        $villagekage = $this->user[0]['leader'];
        $max_rank_lv = Data::${'JUT_MAX_'.$this->user[0]['rank_id']};

        // Set special value if not already set
        $this->user[0]['special_element_mastery'] = $elements->getUserElementMastery(3);

        // Ryo cost
        if( !in_array($jutsu_data['jutsu_type'], array("village","forbidden"), true) ){

            // Return whether bonus is available or not
            $perc = $elements->checkMasteryBonus($this->user[0]['element'], "RyoReduction", $jutsu_data['required_rank']);

            // Percentage
            if( $perc !== false && $perc > 0 ){
                $price -= 0.01 * $perc * $price;
            }
        }

        // Never ever show event jutsus
        if( $jutsu_data['jutsu_type'] == "event" ){
            throw new Exception('You cannot train event jutsus');
        }

        // Pre-Check for Jutsu Cap
        if( $level > $max_rank_lv || $level > $jutsu_data['max_level']) {
            throw new Exception('You cannot level this jutsu any further');
        }

        // Check the jutsu types agains what is available in this training system
        if(!in_array($jutsu_data['jutsu_type'], $this->params['jutsuTypes'], true) ){
            throw new Exception("You can not train this type of jutsu here.");
        }

        // Pre-Check for Current Pools
        if(in_array($jutsu_data['jutsu_type'], array('normal', 'village', 'forbidden'), true)) {
            if($this->user[0]['cur_cha'] < $pool_cost_cha || $this->user[0]['cur_sta'] < $pool_cost_sta ) {
                throw new Exception('You do not have enough chakra/stamina to train this jutsu! You need '.$pool_cost_cha." chakra and ".$pool_cost_sta." stamina.");
            }
        }

        // Pre-Check for Current Money Amount
        if(in_array($jutsu_data['jutsu_type'], array('normal', 'bloodline', 'clan', 'special', 'loyalty'), true)) {
            if($this->user[0]['money'] < $price) {
                throw new Exception('You do not have enough ryo to train this jutsu, you need '.$price.' ryo!');
            }
        }
        elseif( $jutsu_data['jutsu_type'] == "forbidden" ){
            if( $this->user[0]['max_sta'] - $price < 1000 ) {
                throw new Exception('You do not have enough maximum stamina, you need '.$price.' maximum stamina!');
            }
            if( $this->user[0]['max_cha'] - $price < 1000 ) {
                throw new Exception('You do not have enough maximum chakra, you need '.$price.' maximum chakra!');
            }
        }

        // Pre-Check for Required Rank
        if($jutsu_data['required_rank'] > $this->user[0]['rank_id']) {
            throw new Exception('You do not have the required rank to train this jutsu!');
        }

        // Pre-Check Conditions for Acceptable Forbidden Jutsu Training
        if($jutsu_data['jutsu_type'] === 'forbidden' && $level === 1) {
            throw new Exception('You cannot train forbidden jutsu you do not already know!');
        }

        // Pre-Check Conditions for Acceptable loyalty Jutsu Training
        if($jutsu_data['jutsu_type'] === 'loyalty' && $GLOBALS['userdata'][0]['vil_loyal_pts'] < $jutsu_data['loyaltyRespectReq']) {
            throw new Exception('You do not have the required respect in your village to train this jutsu');
        }

        // Pre-Check Conditions for Acceptable Bloodline Jutsu Training
        if($jutsu_data['bloodline'] !== $this->user[0]['bloodline']) {
            if($jutsu_data['bloodline'] !== null) {
                throw new Exception('You do not have the required bloodline to train this jutsu!');
            }
        }

        // Pre-Check Conditions for Acceptable Village Jutsu Training
        if($jutsu_data['village'] !== $this->user[0]['village']) {
            if( !empty($jutsu_data['village']) ) {
                throw new Exception('Your instructor cannot teach you about jutsu specific to other villages.');
            }
        }

        // Pre-Check Conditions for Acceptable Kage Jutsu Training
        if(strtolower($this->user[0]['username']) !== strtolower($villagekage)) {
            if($jutsu_data['kage'] === 'yes') {
                throw new Exception('You are not a village kage to train this jutsu!');
            }
        }

        /*
            ----Next Generation Jutsu Hierarchy----
            Normal = Pool + Ryo
            Bloodline/Clan = Ryo + 1.5 Hours
            Special/Kage = Ryo + 2 Hours
            Village/Forbidden = Pool + Max Pool + Drain Pool on Level Up
        */
        $trainTime = 0;
        switch( $jutsu_data['jutsu_type'] ){
            case "normal":
                $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$this->user[0]['cur_cha'] - $pool_cost_cha, 'old'=>$this->user[0]['cur_cha'] ));
                $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$this->user[0]['cur_sta'] - $pool_cost_sta, 'old'=>$this->user[0]['cur_sta'] ));
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$this->user[0]['money'],'new'=> $this->user[0]['money'] - $price));
                $this->user[0]['money'] -= $price;
                $this->user[0]['cur_cha'] -= $pool_cost_cha;
                $this->user[0]['cur_sta'] -= $pool_cost_sta;
            break;
            case "bloodline":
            case "clan":
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$this->user[0]['money'],'new'=> $this->user[0]['money'] - $price));
                $this->user[0]['money'] -= $price;
                $trainTime = 1.5*3600;
            break;
            case "special":
            case "loyalty":
            case "kage":
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$this->user[0]['money'],'new'=> $this->user[0]['money'] - $price));
                $this->user[0]['money'] -= $price;
                $trainTime = 2*3600;
            break;
            case "village":
            case "forbidden":
                $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>0, 'old'=>$this->user[0]['cur_cha'] ));
                $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>0, 'old'=>$this->user[0]['cur_sta'] ));
                $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$this->user[0]['max_cha'] - $price, 'old'=>$this->user[0]['max_cha'] ));
                $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$this->user[0]['max_sta'] - $price, 'old'=>$this->user[0]['max_sta'] ));
                $this->user[0]['cur_cha'] = 0;
                $this->user[0]['cur_sta'] = 0;
                $this->user[0]['max_cha'] -= $price;
                $this->user[0]['max_sta'] -= $price;
            break;
        }

        // Reduce time based on elemental mastery if applicable
        if( $this->user[0]['element'] !== "none" ){

            // Timers
            if($jutsu_data['jutsu_type'] == "bloodline" || $jutsu_data['jutsu_type'] == "special" ){

                // Special or bloodline
                $type = $jutsu_data['jutsu_type']=="bloodline" ? "BloodlineJutsu" : "SpecialJutsu";

                // Return whether bonus is available or not
                $timeReduction = $elements->checkMasteryBonus($this->user[0]['element'], $type, $jutsu_data['required_rank']);

                if( $timeReduction !== false && $timeReduction > 0 ){
                    $trainTime -= $timeReduction;
                    if( $trainTime < 0 ){
                        throw new Exception("Train time was calculated to be negative, something is wrong.");
                    }

                }
            }
        }

        // Update query
        $updateQuery = "`users_statistics`.`money` = '".$this->user[0]['money']."',
                        `users_statistics`.`cur_cha` = '".$this->user[0]['cur_cha']."',
                        `users_statistics`.`cur_sta` = '".$this->user[0]['cur_sta']."',
                        `users_statistics`.`max_cha` = '".$this->user[0]['max_cha']."',
                        `users_statistics`.`max_sta` = '".$this->user[0]['max_sta']."'";

        // Extra if time jutsu
        if( $trainTime > 0 ){
            $updateQuery .= ', `users_timer`.`jutsu` = '.$jid.'
                           , `users_timer`.`jutsu_timer` = '.($GLOBALS['user']->load_time + $trainTime);
        }

        // Update the user jutsu
        $this->updateUserJutsu($jid, $level, $updateQuery);

        // Instant update user (backend)
        $this->backendUpdateArray = array(
            "cur_cha" => (float) $this->user[0]['cur_cha'],
            "cur_sta" => (float) $this->user[0]['cur_sta'],
            "max_cha" => (float) $this->user[0]['max_cha'],
            "max_sta" => (float) $this->user[0]['max_sta'],
            "money" => (float) $this->user[0]['money']
        );

        // Message
        if( $trainTime > 0 ){
            $GLOBALS['page']->Message('You have started training '.stripslashes($this->user[0]['name']).' to level '.$level.'', 'Jutsu Training', 'id='.$_REQUEST['id']);
        }
        else{
            $GLOBALS['page']->Message('You have trained '.stripslashes($this->user[0]['name']).' to level '.$level.'',
                'Jutsu Training',
                'id='.$_REQUEST['id'].'&amp;train=jutsu&amp;jid='.$jid,
                'Try to train one more level',
                'contentLoad',
                'retrainClass'
            );
        }

        // Commit the transaction
        $GLOBALS['database']->transaction_commit();
    }

    //** All Functions below are related to jutsu mastery **//

    //	Provide User with Jutsu Choices
    protected function showMasterList() {

        // Set jutsu mastery information
        $this->getJutsuMaxLevels();

        // Select array
        $choices = $GLOBALS['database']->fetch_data("
            SELECT `jutsu`.`name`, `jutsu`.`id`
            FROM `users_jutsu`, `jutsu`
            WHERE
                `users_jutsu`.`uid` = '".$_SESSION['uid']."' AND
                `users_jutsu`.`jid` = `jutsu`.`id` AND
                ".$this->jutsuQuery."
        " );

        // Select array
        $selectArray = array();
        if( $choices !== "0 rows" ){
            foreach( $choices as $jutsu ){
                $selectArray[ $jutsu['id'] ] = $jutsu['name'];
            }
        }

        // Create the input form
        $GLOBALS['page']->UserInput(
"Once your jutsus reach a lvl 100, training them becomes a lot more difficult. This is where jutsu mastery comes into play.
 Here you will be able to train jutsus beyond level 100 using chakra and stamina pools.
 You can use jutsu mastery to train the following jutsu",
                "Training System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"jid",
                        "type"=>"select",
                        "inputFieldValue"=> $selectArray
                    ),
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train'])
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );
    }

    // Show the amount of training times available
    protected function showMasteryAmountForm(){

        // Set & Check the jutsu ID
        $jid = $_REQUEST['jid'];
        if (ctype_digit($jid) !== true) {
            throw new Exception("Could not understand what jutsu you're looking for.");
        }

        // Set jutsu mastery information
        $this->getJutsuMaxLevels();

        // Obtain Necessary User Information
        $this->data = $GLOBALS['database']->fetch_data("
            SELECT
                `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`,
                `users_statistics`.`rank_id`, `users_jutsu`.*, `jutsu`.*
            FROM
                `users_statistics`,`users_jutsu`,`jutsu`
            WHERE
                `users_statistics`.`uid` = '".$_SESSION['uid']."' AND
                `users_jutsu`.`uid` = `users_statistics`.`uid` AND
                `users_jutsu`.`jid` = `jutsu`.`id` AND
                ".$this->jutsuQuery." AND
                `jutsu`.`id` = ".$jid."
            LIMIT 1"
        );

        // Only if jutsu was found
        if( $this->data !== "0 rows" ){

            // Set the information for jutsu training
            $this->train_data = $this->getJutsuMasteryInfo();

            // Create the input form
            $GLOBALS['page']->UserInput(
                    'How many times do you want to train the mastery of the jutsu '.$this->data[0]['name'].'?',
                    "Training System",
                    array(
                        // A select box
                        array(
                            "inputFieldName"=>"train_amount",
                            "type"=>"range",
                            'inputFieldValue' => $this->train_data['max_times'] ? $this->train_data['max_times'] : 0,
                            'inputFieldMin' => 0,
                            'inputFieldMax' => $this->train_data['max_times'],
                            'inputFieldDisabled' => $this->train_data['max_times'] == 0
                        ),
                        // Pass on type in a hidden entry in the form
                        array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train']),
                        array("type"=>"hidden", "inputFieldName"=>"jid", "inputFieldValue"=>$jid),
                    ),
                    array(
                        "href"=>"?id=".$_REQUEST['id'] ,
                        "submitFieldName"=>"Submit",
                        "submitFieldText"=>"Submit"),
                    "Return" ,
                    "trainingForm"
            );
        }
    }

     //	Perform Jutsu Training | type-options are "standard" and "mastery"
    protected function doMasteryTraining() {

        // Start Transaction
        $GLOBALS['database']->transaction_start();

        // Set & Check the jutsu ID
        $jid = $_REQUEST['jid'];
        if (ctype_digit($jid) !== true) {
            throw new Exception("Could not understand what jutsu you're looking for.");
        }

        // Set jutsu mastery information
        $this->getJutsuMaxLevels();

        // Fetch Necessary Data From Database
        if(!($this->data = $GLOBALS['database']->fetch_data("
            SELECT
                `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`,
                `users_statistics`.`max_cha`, `users_statistics`.`max_sta`,
                `users_statistics`.`rank_id`,
                `users_jutsu`.*,
                `jutsu`.*,
                `users_timer`.`jutsu_timer`
            FROM
                `users_statistics`,`users_jutsu`,`jutsu`, `users_timer`
            WHERE
                `users_statistics`.`uid` = '".$_SESSION['uid']."' AND
                `users_jutsu`.`uid` = `users_statistics`.`uid` AND
                `users_timer`.`userid` = `users_statistics`.`uid` AND
                `users_jutsu`.`jid` = `jutsu`.`id` AND
                ".$this->jutsuQuery." AND
                `jutsu`.`id` = ".$jid."
            LIMIT 1 FOR UPDATE")))
        {
            throw new Exception('There was an error retrieving the user data.');
        }

        // Set the information for jutsu training
        $this->train_data = $this->getJutsuMasteryInfo();

        //  Obtain Training Amount. Check it, this should ensure we don't overcap chakra/stamina and the stats
        $task_amount = $this->checkTrainingAmount( $_REQUEST['train_amount'] );
        $this->train_data['cha_cost'] *= $task_amount;
        $this->train_data['sta_cost'] *= $task_amount;
        $this->train_data['exp_increase'] *= $task_amount;

        // Update user data
        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$this->data[0]['cur_cha'] - $this->train_data['cha_cost'], 'old'=>$this->data[0]['cur_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$this->data[0]['cur_sta'] - $this->train_data['sta_cost'], 'old'=>$this->data[0]['cur_sta'] ));

        $this->data[0]['cur_cha'] -= $this->train_data['cha_cost'];
        $this->data[0]['cur_sta'] -= $this->train_data['sta_cost'];

        // Only train one jutsu at a time
        if( !empty($this->data[0]['jutsu_timer']) ){
            throw new Exception("You can not train more jutsus right now since you're already training");
        }

        // Check the jutsu types agains what is available in this training system
        if(!in_array( $this->data[0]['jutsu_type'], $this->params['jutsuTypes'], true) ){
            throw new Exception("You can not train this type of jutsu here.");
        }

        // Update query
        $updateQuery = "`users_statistics`.`cur_cha` = '".$this->data[0]['cur_cha']."',
                        `users_statistics`.`cur_sta` = '".$this->data[0]['cur_sta']."' ";
        $messageInfo = "";

        // Figure out if level or extra exp
        $gotLevel = ($this->train_data['exp_increase'] + $this->data[0]['exp'] >= Data::$JUTSU_EXP_PER_LEVEL[ $this->data[0]['jutsu_type'] ] );
        if( $gotLevel ){
            $messageInfo = " to level ".($this->data[0]['level']+1);
            $updateQuery .= ", `users_jutsu`.`level` =  `users_jutsu`.`level` + 1,  `users_jutsu`.`exp` = 0 ";
            $GLOBALS['Events']->acceptEvent('jutsu_level', array('old'=>$this->data[0]['level'],'new'=>$this->data[0]['level']+1,'data'=>$_REQUEST['jid'], 'context'=>$_REQUEST['jid']));
        }
        else{
            $messageInfo = " and gained ".$this->train_data['exp_increase']." experience in the jutsu";
            $updateQuery .= ", `users_jutsu`.`exp` = `users_jutsu`.`exp` + '".$this->train_data['exp_increase']."' ";
        }

        // Update the user jutsu
        $this->updateUserJutsu($jid, $this->data[0]['level'], $updateQuery);

        // Instant update user (backend)
        $this->backendUpdateArray = array(
            "cur_cha" => (float) $this->data[0]['cur_cha'],
            "cur_sta" => (float) $this->data[0]['cur_sta'],
            "max_cha" => (float) $this->data[0]['max_cha'],
            "max_sta" => (float) $this->data[0]['max_sta']
        );

        // Message
        $GLOBALS['page']->Message('You have trained '.stripslashes($this->data[0]['name']).$messageInfo, 'Jutsu Training', 'id='.$_REQUEST['id']);

        // Commit the transaction
        $GLOBALS['database']->transaction_commit();
    }

    // Set information for jutsu mastery
    protected function getJutsuMaxLevels(){
        $elements = new Elements();

        // Set the max levels we can trian with mastery
        $this->maxLevel = array(
            "normal" => 120,
            "bloodline" => 125,
            "clan" => 130,
            "special" => 135,
            "loyalty" => 135,
            "village" => 140,
            "forbidden" => 150
        );

        // If jutsu data is set, get more stuff
        $this->jutsuQuery = "";
        foreach( $this->maxLevel as $key => $level){
            $this->jutsuQuery .= ($this->jutsuQuery == "") ?
                "( `jutsu`.`jutsu_type` = '".$key."' AND `users_jutsu`.`level` < '".$level."' )" :
                " OR ( `jutsu`.`jutsu_type` = '".$key."' AND `users_jutsu`.`level` < '".$level."' )";
        }
        $this->jutsuQuery = "(".$this->jutsuQuery.") AND `users_jutsu`.`level` >= '100'";

        // Elemental restriction
        $availableElements = $elements->getUserElements();
        $this->jutsuQuery .= " AND `jutsu`.`element` IN ('".implode("','",$availableElements)."') ";
    }

    // Set the information in regards to pool costs / gains etc
    protected function getJutsuMasteryInfo(){

        // An Array with Chakra/Stamina Costs etc
        $data = array(
            'cha_cost' => 0,
            'sta_cost' => 0,
            'max_times' => 0,
            'exp_increase' => 0
        );

        // Set costs
        $data['cha_cost'] = $this->data[0]['cha_cost'];
        $data['sta_cost'] = $this->data[0]['sta_cost'];

        // Set cost to 1 if not set
        if( $data['cha_cost'] == 0 ){ $data['cha_cost'] = 1; }
        if( $data['sta_cost'] == 0 ){ $data['sta_cost'] = 1; }

        // Max number of trainings according to cha / sta
        $cha_times = floor($this->data[0]['cur_cha'] / $data['cha_cost']); // Maximum number of training with chakra
        $sta_times = floor($this->data[0]['cur_sta'] / $data['sta_cost']); // Maximum number of training with stamina

        // Max number of trainings by stat caps
        $max_times = ($cha_times > $sta_times) ? $sta_times : $cha_times;
        $max_times = ($max_times < 0) ? 0 : $max_times;

        // Times to next level
        $data['exp_increase'] = floor(500 / ( 1 * $this->data[0]['level']));
        $exp_left = Data::$JUTSU_EXP_PER_LEVEL[ $this->data[0]['jutsu_type'] ] - $this->data[0]['exp'];
        $timesToLevel = ceil($exp_left / $data['exp_increase']);

        // Determine actual max
        $data['max_times'] = ($max_times > $timesToLevel) ? $timesToLevel : $max_times;

        // Return
        return $data;
    }

    //** All Functions below are related to elemental mastery **//

     //	Provide User with choices
    protected function showElementalList() {

        // Set elemental mastery information
        $this->getElementalOptions();

        // Create the input form
        $GLOBALS['page']->UserInput(
"In order to use elemental techniques, it is important that you train your
 elemental affinity, or mastery if you will. Only by being proficient with the given elements,
 will you be able to fully utilize the strengths of elemental techniques. Once you reach Chuunin level,
 you will be able to train your primary affinity, and at Jounin-level you will also be able to train your
 secondary affinity.",
                "Training System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"option",
                        "type"=>"select",
                        "inputFieldValue"=> $this->trainingChoices
                    ),
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train'])
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );
    }

    // Show the amount of training times available
    protected function showElementalAmountForm(){

        // Set jutsu mastery information
        $this->getElementalOptions();

        // Set & Check the jutsu ID
        $option = $_REQUEST['option'];
        if ( !array_key_exists($option, $this->trainingChoices) ) {
            throw new Exception("You can not train this type of elemental affinity yet.");
        }

        // Obtain Necessary User Information
        $this->user = $GLOBALS['database']->fetch_data('
            SELECT `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`, `users_statistics`.`rank_id`,
            FROM `users_statistics`
            WHERE `users_statistics`.`uid` = '.$_SESSION['uid'].'
            LIMIT 1'
        );

        // Set the information for jutsu training
        $this->train_data = $this->getElementalInfo( $option );

        // Create the input form
        $GLOBALS['page']->UserInput(
                'How many times do you want to train your '.$option.' elemental mastery?',
                "Training System",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"train_amount",
                        "type"=>"range",
                        'inputFieldValue' => $this->train_data['max_times'] ? $this->train_data['max_times'] : 0,
                        'inputFieldMin' => 0,
                        'inputFieldMax' => $this->train_data['max_times'],
                        'inputFieldDisabled' => $this->train_data['max_times'] == 0
                    ),
                    // Pass on type in a hidden entry in the form
                    array("type"=>"hidden", "inputFieldName"=>"train", "inputFieldValue"=>$_REQUEST['train']),
                    array("type"=>"hidden", "inputFieldName"=>"option", "inputFieldValue"=>$_REQUEST['option']),
                ),
                array(
                    "href"=>"?id=".$_REQUEST['id'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Submit"),
                "Return" ,
                "trainingForm"
        );

    }

     //	Perform elemental training
    protected function doElementalTraining() {

         // Set jutsu mastery information
        $this->getElementalOptions();

        // Set & Check the jutsu ID
        $option = $_REQUEST['option'];
        if ( !array_key_exists($option, $this->trainingChoices) ) {
            throw new Exception("You can not train this type of elemental affinity yet.");
        }

        // Start Transaction
        $GLOBALS['database']->transaction_start();

        // Obtain General Type
        if(!($this->user = $GLOBALS['database']->fetch_data('
            SELECT
                `users_statistics`.`uid`,
                `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`,
                `users_statistics`.`max_cha`, `users_statistics`.`max_sta`,
                `users_statistics`.`rank_id`, `users_statistics`.`experience`,
                `users_statistics`.`'.$this->train_data['stat_type'].'`
            FROM `users`
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` IN("awake", "jailed")
            LIMIT 1 FOR UPDATE'))
        ) {
            throw new Exception('An error occured while training, please try again!');
        }

        // Get information
        $this->train_data = $this->getElementalInfo( $option );

        //  Obtain Training Amount. Check it, this should ensure we don't overcap chakra/stamina and the stats
        $task_amount = $this->checkTrainingAmount( $_REQUEST['train_amount'] );

        // Multiply gains & costs with times
        $this->multipleData( $task_amount );

        // Update user

        $stat = $this->train_data['stat_type'];
        $GLOBALS['Events']->acceptEvent('stats_'.$stat, array('new'=>$this->user[0][$stat] + $this->train_data['stat_gain'], 'old'=>$this->user[0][$stat] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$this->user[0]['cur_cha']    - $this->train_data['cha_cost'], 'old'=>$this->user[0]['cur_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$this->user[0]['cur_sta']    - $this->train_data['sta_cost'], 'old'=>$this->user[0]['cur_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$this->user[0]['max_cha']    + $this->train_data['chakra_gain'], 'old'=>$this->user[0]['max_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$this->user[0]['max_sta']    + $this->train_data['stamina_gain'], 'old'=>$this->user[0]['max_sta'] ));
        $GLOBALS['Events']->acceptEvent('experience', array('new'=>$this->user[0]['experience'] + $this->train_data['experience_gain'], 'old'=>$this->user[0]['experience'] ));


        if (($GLOBALS['database']->execute_query("
            UPDATE `users_statistics`
            SET `users_statistics`.`".$this->train_data['stat_type']."` = `users_statistics`.`".$this->train_data['stat_type']."` + ".$this->train_data['stat_gain'].",
                `users_statistics`.`cur_cha` = `users_statistics`.`cur_cha` - ".$this->train_data['cha_cost'].",
                `users_statistics`.`cur_sta` = `users_statistics`.`cur_sta` - ".$this->train_data['sta_cost'].",
                `users_statistics`.`max_cha` = `users_statistics`.`max_cha` + ".$this->train_data['chakra_gain'].",
                `users_statistics`.`max_sta` = `users_statistics`.`max_sta` + ".$this->train_data['stamina_gain'].",
                `users_statistics`.`experience` = `users_statistics`.`experience` + ".$this->train_data['experience_gain']."
            WHERE `users_statistics`.`uid` = ".$this->user[0]['uid']."
            LIMIT 1")) === false)
        {
                throw new Exception('There was an error updating the user data');
        }

        // Instant update
        $this->instantUpdateUserdata();

        // Message
        $GLOBALS['page']->Message( $this->getImprovement() , 'Training System', 'id='.$_REQUEST['id'],'Return');

        // Commit Transaction
        $GLOBALS['database']->transaction_commit();
    }


    // Set information related to elemental mastery training
    private function getElementalOptions(){

        // Set the given training options
        $this->trainingChoices = array();
        if( $GLOBALS['userdata'][0]['rank_id'] >= 3 ){
            $this->trainingChoices["primary"] = "Primary Mastery";
        }
        if( $GLOBALS['userdata'][0]['rank_id'] >= 4 ){
            $this->trainingChoices["secondary"] = "Secondary Mastery";
        }
    }

    // Get elemental information
    private function getElementalInfo( $option ){

        // An Array with Chakra/Stamina Costs etc
        $data = array(
            'stat' => ucfirst($option).' Elemental Mastery',
            'stat_type' => '',
            'stat_gain' => 0,
            'cha_cost' => 4,
            'sta_cost' => 4,
            'type' => '',
            'max_times' => 0,
            'stamina_gain' => 0.025,
            'chakra_gain' => 0.025
        );

        // Set the gain based on the user rank id
        switch($this->user[0]['rank_id']) {
            case('3'):
            case('4'): $data['stat_gain'] = 0.15; break;
            case('5'): $data['stat_gain'] = 0.2; break;
            default: throw new Exception("Could not determine rank.");
        }

        // Set the stat type in the form used in the DB
        switch( $option ){
            case "primary": $data['stat_type'] = "element_mastery_1"; break;
            case "secondary": $data['stat_type'] = "element_mastery_2"; break;
        }

        // Max number of trainings according to cha / sta
        $cha_times = floor($this->user[0]['cur_cha'] / $data['cha_cost']); // Maximum number of training with chakra
        $sta_times = floor($this->user[0]['cur_sta'] / $data['sta_cost']); // Maximum number of training with stamina

        // Max number of trainings by stat caps
        $max_times = ($cha_times > $sta_times) ? $sta_times : $cha_times;
        $max_times = ($max_times < 0) ? 0 : $max_times;

        // Obtain Max training times based on capped stats
        $max_rank_pools = Data::${'GEN_MAX_'.$this->user[0]['rank_id']};
        $max_pool_times = ($max_rank_pools - $this->user[0][$data['stat_type']]) / $data['stat_gain'];
        $max_pool_times = ($max_pool_times < 0) ? 0 : $max_pool_times;

        // If just about to cap, set to 1
        if( $max_pool_times > 0 && $max_pool_times < 1 ){
            $max_pool_times = 1;
        }

        // Determine actual max
        $data['max_times'] = ($max_times > $max_pool_times) ? $max_pool_times : $max_times;

        // Returnd ata
        return $data;
    }


    //** All Functions below are related to special jutsu training **//

    // 	Train Special Jutsu
    protected function getSpecialJutsu() {

        //  Start Transaction
        $GLOBALS['database']->transaction_start();

        if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users`.`id`,
            `users_timer`.`jutsu`,
            `users_timer`.`jutsu_timer`,
            `users_statistics`.`regen_rate`,
            `jutsu`.`name`,
            `users_jutsu`.`level`
            FROM `users`
                LEFT JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                LEFT JOIN `users_jutsu` ON (`users_jutsu`.`jid` = `users_timer`.`jutsu` AND `users_jutsu`.`uid` = `users`.`id`)
                LEFT JOIN `jutsu` ON (`jutsu`.`id` = `users_timer`.`jutsu`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' AND `users`.`status` = "awake" LIMIT 1 FOR UPDATE')))
        {
            throw new Exception('There was an error retrieving the data required');
        }

        // Check jutsu is set
        if ($this->user[0]['jutsu'] == '') {
            throw new Exception('You are not training a special jutsu!');
        }

        // Check timer
        if ($this->user[0]['jutsu_timer'] >= $GLOBALS['user']->load_time) {
            throw new Exception('You have not yet finished training this jutsu!');
        }

        // Update data
        $updateQuery = '`users_timer`.`jutsu` = ""
                       , `users_timer`.`jutsu_timer` = 0';

        // Instant update globals
        $GLOBALS['userdata'][0]['jutsu_timer'] = 0;

        // Update database
        $this->updateUserJutsu($this->user[0]['jutsu'], 0, $updateQuery);

        // Message
        $GLOBALS['page']->Message('You have finally finished training '.stripslashes($this->user[0]['name']).' to level '.$this->user[0]['level'].'', 'Special Jutsu Training', 'id='.$_REQUEST['id']);

        // Commit Transaction
        $GLOBALS['database']->transaction_commit();

    }

    // Function for updating a jutsu in the user database
    protected function updateUserJutsu( $jid , $level, $updateQuery ){

        // Figure out if we're inserting new or updating
        if( $level == 1 ){

            // Insert
            if (($GLOBALS['database']->execute_query('
                UPDATE `users_statistics`, `users_timer`
                SET '.$updateQuery.'
                WHERE
                    `users_statistics`.`uid` = '.$_SESSION['uid'].' AND
                    `users_timer`.`userid` = `users_statistics`.`uid`')) === false)
            {
                throw new Exception('There was an error updating user information');
            }

            //			Insert New Jutsu under their Jutsu Tables
            if(($GLOBALS['database']->execute_query('INSERT INTO
                `users_jutsu` (`uid`, `jid`, `level`, `exp`, `tagged`)
                VALUES ('.$_SESSION['uid'].', '.$jid.', 1, 0, "no")')) === false)
            {
                throw new Exception('There was an error inserting the jutsu into the database');
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('jutsu_learned', array('data'=>$jid, 'context'=>$jid));
                $GLOBALS['Events']->acceptEvent('jutsu_level',   array('new'=>1, 'old'=>0, 'data'=>$jid, 'context'=>$jid));
            }
        }
        else{

            // Figure level update
            $levelUpdate = ($level == 0) ? "" : "`users_jutsu`.`level` = '".$level."',";

            // Update
            if(($GLOBALS['database']->execute_query(
                "UPDATE `users_jutsu`,`users_statistics`,`users_timer`
                SET ".$levelUpdate." ".$updateQuery."
                WHERE `users_jutsu`.`jid` = '".$jid."'
                    AND `users_jutsu`.`uid` = '".$_SESSION['uid']."'
                    AND `users_statistics`.`uid` = `users_jutsu`.`uid`
                    AND `users_timer`.`userid` = `users_statistics`.`uid`"
                )) === false)
            {
                throw new Exception('There was an error updating user and jutsu information');
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('jutsu_level', array('new'=>$level,'old'=>$level-1,'data'=>$jid, 'context'=>$jid));
            }
        }
    }


    //** ALL GET/SET/INSERT/REMOVE & CONVENIENCE FUNCTIONS BELOW **//

    // Get User Information
    protected function getUserData( $lock = false ) {

        // Create Query
        $query = "SELECT
                `users`.*,
                `users_timer`.`jutsu`, `users_timer`.`jutsu_timer`, `users_timer`.`jail_timer`,
                `users_loyalty`.`time_in_vil`,
                `users_statistics`.`tai_off`, `users_statistics`.`tai_def`, `users_statistics`.`nin_off`, `users_statistics`.`nin_def`,
                `users_statistics`.`gen_off`, `users_statistics`.`gen_def`, `users_statistics`.`weap_off`, `users_statistics`.`weap_def`,
                `users_statistics`.`intelligence`, `users_statistics`.`strength`, `users_statistics`.`willpower`, `users_statistics`.`speed`,
                `users_statistics`.`cur_cha`, `users_statistics`.`cur_sta`, `users_statistics`.`max_cha`, `users_statistics`.`max_sta`,
                `users_statistics`.`rank_id`, `users_statistics`.`rank`, `users_statistics`.`specialization`, `users_statistics`.`level`
            FROM `users`
                LEFT JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                LEFT JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
            WHERE `users`.`id` = ".$_SESSION['uid']." LIMIT 1";

        // Lock
        if( $lock == true ){
            $query .= " FOR UPDATE";
        }

        // Run in database
        $this->user = $GLOBALS['database']->fetch_data($query);

        $elements = new Elements();
        $masteries = $elements->getUserElementMastery();
        $affinities = $elements->getUserElements();

        $this->user[0]['element_affinity_1'] = $affinities[0];
        $this->user[0]['element_affinity_2'] = $affinities[1];
        $this->user[0]['element_affinity_special'] = $affinities[2];
        $this->user[0]['element_mastery_1'] = $masteries[0];
        $this->user[0]['element_mastery_2'] = $masteries[1];
        $this->user[0]['element_mastery_special'] = $masteries[2];
    }

    //	Check/Set Max or Future Chakra/Stamina Pool Gains
    protected function cha_sta_limit($pool_max, $gain) {
        if ($this->user[0][$pool_max] >= Data::${'MAX_'.$this->user[0]['rank_id']}) {
            return 0;
        }
        elseif (($this->user[0][$pool_max] + $gain) >= Data::${'MAX_'.$this->user[0]['rank_id']}) {
            return round(Data::${'MAX_'.$this->user[0]['rank_id']} - $this->user[0][$pool_max],2 ); // Return Difference
        }
        return $gain;
    }


    //  Build Training Times Array
    protected function timesArrayBuild( ) {
        $timeArray = array();
        $timeArray[ $this->train_data['max_times'] ] = $this->train_data['max_times']." times";
        for($i = 1; $i < $this->train_data['max_times']; $i++) {
            $timeArray[$i] = $i." times";
            if($i <= 5) { $i += ($i === 5) ? 4 : 3; }
            elseif($i < 50) { $i += 9; }
            elseif($i < 200) { $i += 49; }
            elseif($i < 1000) { $i += 99; }
            elseif($i < 5000) { $i += 499; }
            elseif($i < 10000) { $i += 999; }
            elseif($i < 50000) { $i += 4999; }
            elseif($i < 100000) { $i += 9999; }
            elseif($i < 500000) { $i += 49999; }
            elseif($i < 1000000) { $i += 99999; }
            else { break; }
        }
        return $timeArray;
    }

    //	Check/Set Training Amount in Numeric Value
    protected function checkTrainingAmount( $amount ) {

        // Set amount
        $amount = (ctype_digit($amount) === true) ? (int)$amount : 0;
        $amount = ($amount < 1) ? 0 : $amount; // Anything less than 1 or negative results as a 0

        // Check amount
        if( $amount > $this->train_data['max_times'] || $amount <= 0 ){
            throw new Exception("You cannot train this amount of times: ".$amount.". Max times are: ".$this->train_data['max_times']);
        }

        // Return amount
        return $amount;
    }


    //** Functions relating to the backend ** //

    // Calculated an encoded string for the setup
    protected function encodeSetup(){
        $encrypted = json_encode($this->params);
        $serialized = urlencode( $encrypted );
        return $serialized;
    }

    // Decode the setup
    protected function decodeSetup( $string ){
        $decodedSetup = urldecode( $string );
        $unserialized = json_decode( $decodedSetup , true );
        return $unserialized;
    }

    // Function for setting a chat token for interaction with the backend
    protected function setTrainingToken(){

        // If we have a original setup, use that, otherwise use the constructor
        $setup = isset($this->params['originalSetup']) ? $this->params['originalSetup'] : $this->params;

        // Create the chat token from user data & chat setup
        $this->trainingToken = functions::createHash(
            array_merge( array( $GLOBALS['userdata'][0]['id'], $GLOBALS['userdata'][0]['login_id'] ) , $setup )
        );

        // Return
        return $this->trainingToken;
    }

    // Instant update values on the page, so they don't only show up on the following pageload
    protected function instantUpdateUserdata(){

        // Instant update globals (no js)
        $GLOBALS['userdata'][0]['cur_cha'] -= $this->train_data['cha_cost'];
        $GLOBALS['userdata'][0]['cur_sta'] -= $this->train_data['sta_cost'];

        // Instant update user (backend)
        $this->backendUpdateArray = array(
            "cur_cha" => (float) $GLOBALS['userdata'][0]['cur_cha'],
            "cur_sta" => (float) $GLOBALS['userdata'][0]['cur_sta'],
            "max_cha" => (float) $this->user[0]['max_cha'] + $this->train_data['chakra_gain'],
            "max_sta" => (float) $this->user[0]['max_sta'] + $this->train_data['stamina_gain'],
            "stat" => $this->train_data['stat_type'],
            "statValue" => (float) $this->user[0][ $this->train_data['stat_type'] ] + $this->train_data['stat_gain']
        );
    }

    // Update the train_data with the amount of times the user does the action
    protected function multipleData( $task_amount , $expFactor = 10 ){

        // Multiply data
        $this->train_data['cha_cost'] = round($this->train_data['cha_cost'] * $task_amount, 2);
        $this->train_data['sta_cost'] = round($this->train_data['sta_cost'] * $task_amount, 2);
        $this->train_data['stamina_gain'] = round($this->train_data['stamina_gain'] * $task_amount, 2);
        $this->train_data['chakra_gain'] = round($this->train_data['chakra_gain'] * $task_amount, 2);
        $this->train_data['stat_gain'] = round($this->train_data['stat_gain'] * $task_amount, 2);
        $this->train_data['experience_gain'] = round($expFactor * $task_amount);

        //  Max Chakra / Stamina Limiter
        $this->train_data['chakra_gain'] = $this->cha_sta_limit("max_cha", $this->train_data['chakra_gain']);
        $this->train_data['stamina_gain'] = $this->cha_sta_limit("max_sta", $this->train_data['stamina_gain']);

    }

    // get the implement message
    protected function getImprovement(){

        // Set the improvement
        $improvement = 'You gained '.$this->train_data['experience_gain'].' exp and improved '.$this->train_data['stat_gain'].' points in '.( isset($_REQUEST['train_type']) ? strtolower($_REQUEST['train_type'])." " : "" ).''.$this->train_data['stat'].'.<br>';

        $improvement .= ($this->train_data['chakra_gain'] > 0) ?
            ('Your maximum chakra has been increased by '.$this->train_data['chakra_gain'].'<br>') : '';

        $improvement .= ($this->train_data['stamina_gain'] > 0) ?
            ('Your maximum stamina has been increased by '.$this->train_data['stamina_gain'].'<br>') : '';

        $improvement .= ($this->train_data['cha_cost'] > 0) ?
            ('You have used '.$this->train_data['cha_cost'].' chakra during this training.<br>') : '';

        $improvement .= ($this->train_data['sta_cost'] > 0) ?
            ('You have used '.$this->train_data['sta_cost'].' stamina during this training.<br>') : '';

        return $improvement;
    }


    // Retrieve new stat values, ensuring it does not exceed max
    protected function calcNewValue( $stat, $curGain, $statMax ){
        $newValue = $this->user[0][ $stat ];
        if( $newValue > 0 ){
            if( $this->user[0][ $stat ] + $curGain > $statMax ){
                $newValue = $statMax;
                $this->train_data['stat_gain'] = round($statMax - $this->user[0][ $stat ],2);
            } else {
                $newValue = $this->user[0][ $stat ] + $curGain;
            }
            return $newValue;
        }
        else{
            throw new Exception("Something's up, could not retrieve current stat value");
        }
    }
}