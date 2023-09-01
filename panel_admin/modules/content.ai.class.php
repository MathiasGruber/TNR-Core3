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

// Limit execution time to 100 second - nothing should last longer
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2000M'); // PHP Local Memory Limit Value
set_time_limit(3600);
ob_start();

require_once(Data::$absSvrPath.'/global_libs/machineLearning/neuralnetwork.php');

class AI{                    	
    
    public $dontShow = array ("id","NeuralNet");
    
    public function __construct(){
        try {
            
            // Check Pages
            if(!isset($_GET['act'])){
                $this->main_screen();
            }
            elseif($_GET['act'] == 'new'){
                if(!isset($_POST['Submit'])){
                    $this->ai_form();
                }
                else{
                    $this->ai_insert();
                }
            }  
            elseif($_GET['act'] == 'search'){
                if(!isset($_POST['Submit'])){
                    $this->search_form();
                }
                else{
                    $this->execute_search();
                }
            }
            elseif($_GET['act'] == 'edit'){
                if(!isset($_POST['Submit'])){
                    $this->edit_form();
                }
                else{
                    $this->do_edit();
                }
            }        
            elseif($_GET['act'] == 'NeuralNet'){
                $this->neuralNetMain();
            }
            elseif($_GET['act'] == 'picture'){
                if(!isset($_POST['Submit'])){
                    $this->change_avatar();    
                }
                else{
                    $this->do_avatar_change();
                }
            }
            elseif($_GET['act'] == 'delete'){
                if(!isset($_POST['Submit'])){
                    $this->verify_delete();
                }
                else{
                    $this->do_delete();
                }
            }
            
        } catch (Exception $ex) {
            $GLOBALS['page']->Message( $ex->getMessage() , 'ANN System', 'id=' . $_GET['id']);
        }                               
    }
    
    
    //	Main screen
    private function main_screen(){

        $query = "SELECT * FROM `ai` ";
        $where = array();
        
        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'aid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " WHERE ".implode(' AND ',$where);
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
            'ai',
            'AI Admin', 
            $result,
            array(
                'id' => "AI id", 
                'name' => "Name", 
                'type' => "Type", 
                'rank' => "Rank",
                'level' => "Level",                
                'life' => "HP",
                'strength' => "Str",                
                'nin_off' => "Ninjutsu Offence",
                'nin_def' => "Ninjutsu Defence",
                'notes' => 'Notes'
            ), 
            array( 
                array("name" => "ANN", "act" => "NeuralNet", "oid" => "table.id"),
                array("name" => "Picture", "act" => "picture", "oid" => "table.id"), 
                array("name" => "Edit", "act" => "edit", "oid" => "table.id"),
                array("name" => "Delete", "act" => "delete", "oid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            true,
            array(
                array("name" => "New AI", "href" =>"?id=".$_GET["id"]."&act=new"),
                array("name" => "Browse List", "href" =>"?id=".$_GET["id"]),
                array("name" => "Search", "href" =>"?id=".$_GET["id"]."&act=search")
            ),
            false, // No sorting on columns
            false, // No pretty options
            array(
                array(
                    'infoText'=>'Aid',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'aid',
                    'postIdentifier'=>'search',
                    'inputName'=>'aid'
                ),
                array(
                    'infoText'=>'Name',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'name',
                    'postIdentifier'=>'search',
                    'inputName'=>'name'
                ),
                array(
                    'infoText'=>'Notes',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'notes',
                    'postIdentifier'=>'search',
                    'inputName'=>'notes'
                )
            ),
            '<b>System notes: </b> When giving an AI a weapon attack, it is not strictly neccesary to add the weapon to the AI. For jutsus using weapons is is however a requirement.'
        );

    }
    
    // Ai Functions
    private function ai_form(){        
        tableParser::parse_form('ai','Insert new AI',$this->dontShow);
    }
    
    private function ai_insert(){
        if(tableParser::insert_data('ai')){
            $GLOBALS['page']->Message("The AI enemy has been added to the table.", 'AI System', 'id='.$_GET['id']); 
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'AI Change','','New AI named <i>".$_POST['name']."</i> was created')");            
        }
        else{
            $GLOBALS['page']->Message("An error occured while adding the AI to the table.", 'AI System', 'id='.$_GET['id']); 
        }
    }
    
    private function edit_form(){
        if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = '".$_GET['oid']."' LIMIT 1");
            if($data != '0 rows'){
                tableParser::parse_form('ai','Edit opponent',$this->dontShow,$data);
            }
            else{
                $GLOBALS['page']->Message("AI could not be found.", 'AI System', 'id='.$_GET['id']); 
            }
        }
        else{
            $GLOBALS['page']->Message("Invalid AI ID.", 'AI System', 'id='.$_GET['id']); 
        }
    }
    
    private function do_edit(){
        if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = '".$_GET['oid']."' LIMIT 1");
            if($data != '0 rows'){
                $_POST['NeuralNet'] = "";
                $changed = tableParser::check_data('ai','id',$_GET['oid'],array() );                                                    
                if(tableParser::update_data('ai','id',$_GET['oid'])){
                    $GLOBALS['page']->Message("The AI opponent has been updated.", 'AI System', 'id='.$_GET['id']); 
                    // Log Changes                    
                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'AI Change','".$_GET['oid']."','AI ID:".$_GET['oid']." Changed:<br>".$changed."')");            
                }    
                else{
                    $GLOBALS['page']->Message("An error occured while updating the AI opponent.", 'AI System', 'id='.$_GET['id']); 
                }
            }
            else{
                $GLOBALS['page']->Message("AI could not be found.", 'AI System', 'id='.$_GET['id']); 
            }
        }
        else{
            $GLOBALS['page']->Message("Invalid AI ID.", 'AI System', 'id='.$_GET['id']); 
        }
    }
    
    private function verify_delete(){
        if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
            $data = $GLOBALS['database']->fetch_data("SELECT `name` FROM `ai` WHERE `id` = '".$_GET['oid']."' LIMIT 1");
            if($data != '0 rows'){
                  $GLOBALS['page']->Confirm("Delete this AI?", 'AI System', 'Delete now!'); 
            }
            else{
                $GLOBALS['page']->Message("AI could not be found.", 'AI System', 'id='.$_GET['id']); 
            }
        }
        else{
            $GLOBALS['page']->Message("Invalid AI ID.", 'AI System', 'id='.$_GET['id']); 
        }
        
    }
    
    private function do_delete(){
        if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
            
            $GLOBALS['database']->execute_query("DELETE FROM `ai` WHERE `id` = '".$_GET['oid']."' LIMIT 1");
            
            $GLOBALS['page']->Message("The AI opponent was removed.", 'AI System', 'id='.$_GET['id']); 
            
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'AI Change','','AI ID: <i>".$_GET['oid']."</i> was deleted')");            
        }
        else{
            $GLOBALS['page']->Message("Invalid AI ID.", 'AI System', 'id='.$_GET['id']); 
        }
    }
    
    // Change picture form
    function change_avatar(){     
        
        // Get the signature
        $image = functions::getUserImage('/ai/', $_GET['oid']);

        // Get the fileuploadlibrary
        require('../global_libs/General/fileUploads.php');
        fileUploader::uploadForm(array(
            "maxsize" => "100kb",
            "subTitle" => "Change AI Picture",
            "image" => $image,
            "description" => "Change the picture of this AI",
            "dimX" => 200,
            "dimY" => 200
        ));

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
               
    }
    
    function do_avatar_change(){
        
        // Get the fileuploadlibrary
        require('../global_libs/General/fileUploads.php');
        $upload = fileUploader::doUpload(array(
            "maxsize" => 102400,
            "destination" => 'ai/',
            "filename" => $_GET['oid'],
            "dimX" => 200,
            "dimY" => 200
        ));

        // Message to user
        if( $upload == true ){
            $GLOBALS['page']->Message('You have successfully uploaded the AI image.', 'AI System', 'id=' . $_GET['id'] . '');
        }
    }
    
    // Search AI
    private function search_form(){
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/content_ai/search.tpl');		
    }

    private function execute_search(){
        $query = "SELECT `id`,`name`,`rank`,`level`,`location` FROM `ai` ";
        if($_POST['name'] != ''){
                $query .= "WHERE `name` LIKE '%".$_POST['name']."%'";
                $preset = 1;
        }
        if($_POST['rank'] != ''){
                $query .= "WHERE `rank` = '".$_POST['rank']."'";
                $preset = 1;
        }
        if($_POST['type'] != 'any'){
                if($preset == 1){
                        $query .= " AND ";
                }
                else{
                        $query .= "WHERE";
                }
                $query .= "`type` = '".$_POST['type']."'";
        }
        if($_POST['location'] != 'any'){
                if($preset == 1){
                        $query .= " AND ";
                }
                else{
                        $query .= "WHERE";
                }
                $query .= "`type` = '".$_POST['type']."'";
        }
        $query .= " ORDER BY `name` ASC";

        $ai = $GLOBALS['database']->fetch_data($query);
        tableParser::show_list(
            'ai',
            'AI Admin', 
            $ai,
            array(
                'name' => "Name", 
                'rank' => "Rank",
                'level' => "Level",
                'location' => "Location",
            ), 
            array( 
                array("name" => "Picture", "act" => "picture", "oid" => "table.id"), 
                array("name" => "Edit", "act" => "edit", "oid" => "table.id"),
                array("name" => "Delete", "act" => "delete", "oid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            true,
            array(
                array("name" => "New AI", "href" =>"?id=".$_GET["id"]."&act=new"),
                array("name" => "Browse List", "href" =>"?id=".$_GET["id"]),
                array("name" => "Search", "href" =>"?id=".$_GET["id"]."&act=search")
            )
        );
    }
    
    // Function for getting AI data
    private function getAI( $id = "random" ){
        if( $id == "random" ){
            return $GLOBALS['database']->fetch_data("SELECT * FROM `ai` ORDER BY RAND() LIMIT 1");
        }
        else{
            return $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = '".$id."' LIMIT 1");
        }
    }
    
    
    private function ai_reset_ann($aiid){
        $GLOBALS['database']->execute_query("UPDATE `ai` SET `NeuralNet` = '' WHERE `id` = '".$aiid."' LIMIT 1 ");
    }
	
    
    // AI FUNCTIONS RELATING TO ANN
    private function neuralNetMain(){
        
        // This page will display the neural net to the user
        $neuralNet = array();
        
        // Get the AI
        $aiData = $this->getAI($_GET['oid']);
        
        // Check that AI is set to use ANN
        if( $aiData[0]['intelligenceType'] == "random" ){
            throw new Exception("This AI is not set to be using an artificial neural network");
        }
        
        // Perform actions on the AI
        $userInfo = "";
        if( isset($_GET['act2']) ){
            switch( $_GET['act2'] ){
                case "trainSelf":                    
                    $userInfo = $this->ai_fight( $aiData[0], $aiData[0]['id'] );
                break;
                case "trainRandom":                    
                    $userInfo = $this->ai_fight( $aiData[0], "random" );
                break;
                case "resetAI": 
                    $this->ai_reset_ann( $aiData[0]['id'] );
                break;
                case "trainData": 
                    $this->ai_train( $aiData[0]['id'] );
                break;
            }
            
            // Update information
            $aiData = $this->getAI($_GET['oid']);
        }
        
        // Load the ANN model
        $annModel = new ANN_M1( $aiData[0] );
        
        // Add general information to show array
        $neuralNet[] = array( "optionName" => "Layers", "optionValue" => $annModel->layerCount);
        $neuralNet[] = array( "optionName" => "Control Output", "optionValue" => print_r($annModel->getControlOutput(),true) );
        
        // Get number of training battles
        $aiBattles = $GLOBALS['database']->fetch_data("SELECT count(`id`) as `total` FROM `log_aiBattleData`");
        $neuralNet[] = array( "optionName" => "Stored Training Samples", "optionValue" => $aiBattles[0]['total'] );
        
        if( !empty($userInfo) ){
            $userInfo = "<br><br>==========================<br>".$userInfo;
        }
        
        // Show information and options
        tableParser::show_list(
            'ai',
            'AI Admin', 
            $neuralNet,
            array(
                'optionName' => "Option Name", 
                'optionValue' => "Option Value", 
            ), 
            array() ,
            true, // Send directly to contentLoad
            true,
            array(
                array("name" => "Overview",     "href" =>"?id=".$_GET["id"]."&oid=".$_GET["oid"]."&act=NeuralNet"),
                array("name" => "Fight Against Itself",     "href" =>"?id=".$_GET["id"]."&oid=".$_GET["oid"]."&act=NeuralNet&act2=trainSelf"),
                array("name" => "Fight Against Random AI",     "href" =>"?id=".$_GET["id"]."&oid=".$_GET["oid"]."&act=NeuralNet&act2=trainRandom"),
                array("name" => "Train from Data (".$aiBattles[0]['total'].")",     "href" =>"?id=".$_GET["id"]."&oid=".$_GET["oid"]."&act=NeuralNet&act2=trainData"),
                array("name" => "Reset Network",                "href" =>"?id=".$_GET["id"]."&oid=".$_GET["oid"]."&act=NeuralNet&act2=resetAI")
            ),
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'The main function of this feature is to get debugging insight into the neural network of a given AI. 
             <b>Note:</b> that training an AI against itself isn\'t as good as training it against a real opponent'.$userInfo
        );
        
    }
    
    // Function for letting AI fight itself, returns number of times won (0 if none)
    private function ai_fight( $aiData , $opponentID ){
        
        // Number of times to train
        $times = isset($_GET['times']) ? $_GET['times'] : 1;
        
        // Only more than one on localhost
        if( $times > 1 && ENV !== "local" ){
            throw new Exception("Can only train multiple times on localhost. Please don't use admin panel for actively training AI.");
        }
        
        // String containing return information
        $returnString = "";
        $winTimes = 0;
        $drawTimes = 0;
        $loseTimes = 0;
        
        // Perform training
        for( $i=0; $i<$times; $i++ ){
            
            // ===== START COMBAT CODE ===== //
        
            // Create AI with random intelligence
            $random_AI_raw = $this->getAI($opponentID);
            $random_ai = array( functions::make_ai( $random_AI_raw[0] ) );
            $random_ai[0]['intelligenceType'] = "random";
            $random_ai_id = array( $random_ai[0]['id'] );

            // Create AI with its specified intelligence
            $training_ai = array( functions::make_ai( $aiData ) );
            $training_ai_id = array( $training_ai[0]['id'] );

            // Name is the same fo the two AIs
            $aiName = array( $aiData['name'] );

            // Update Database
            $battleID = functions::insertIntoBattle(
                    $training_ai_id, 
                    $random_ai_id, 
                    "training", 
                    "1",  
                    $training_ai, 
                    $random_ai,
                    false, // dont update user status
                    true, // handle own trasaction
                    true // disable transaction calls
            );

            // Run the battle
            $battleHandler = new auto_battle( $battleID );
            $battleHandler->runBattle();

            // Display final health
            if( $times == 1 ){
                $returnString .= "<b>Final Health Status (User% / Opponent%):</b> <br>".$battleHandler->getFinalHealth("user")."% / ".$battleHandler->getFinalHealth("opponent")."%<br><br>";
            
                // Add info to return string
                if( !empty($battleHandler->battle[0]['log']) ){
                    foreach( $battleHandler->battle[0]['log'] as $key => $entry ){
                        $returnString .= "<b>Round ".$key."</b><br>";
                        foreach( $entry as $move ){
                            $returnString .= $move['message']."<br>";
                        }
                        $returnString .= "<br>";
                    }
                }
            }
            
            // Update winCount
            if( $battleHandler->getFinalHealth("user") > 0 ){
                $winTimes += 1;
            }
            elseif( $battleHandler->getFinalHealth("opponent") > 0 ){
                $loseTimes += 1;
            }
            else{
                $drawTimes += 1;
            }
            
            unset($battleHandler);
            
            // ===== END COMBAT CODE ===== //
            
        }
        
        $returnString .= "Wins: ".$winTimes." - Draws: ".$drawTimes." - Losses: ".$loseTimes;
        
        // Return resulting information
        return $returnString;
    }

	
}

new AI();