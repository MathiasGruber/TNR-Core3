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
/*------------------------------------------------------*/
/*		Error class				*/
/*	System Error-handling class			*/
/*------------------------------------------------------*/
class tnr_error{
    //	SETTINGS:
    private $CRITICAL_LEVEL = 8;			//      error level at which the script will halt completely
    private $LOG_ERROR = 1;				//	0 = dont log, 1 = log.
    private $LOG_ERROR_LEVEL = 1;			//	Minimum error level the script will log 1 = all
    private $LOG_TYPE = 'NONE'; 			//	Set to either DATABASE or FILE
    private $LOG_FILE_PATH = './logs';                  //	PATH to log files, used only with FILE directive in LOG_TYPE
    private $LOG_FILE_NAME = 'Error [W-Y].log';         //	Error log filename (date syntax between []);
    private $ERROR_OVERRIDE_CONTENT = 'default';	//	Error overrides content values: default (leave it up to the error handler), 
    private $DEBUG = false;
                                                        //      force (forces use of value below), never (never force content override, unadvised)
    private $ERROR_OVERRIDE_CONTENT_LEVEL = 5;          //	Error level at which the content screen will be disabled by default

    private $CAPTCHA_SYSTEM = "SolveMedia"; // Other options: ReCaptcha
    
    private $error_num;

    public function __construct() { 
        $this->error_num = 0; // Default error instance constructor
        
        if( SOLVEMEDIA_VERIFICATION == ""){
            $this->CAPTCHA_SYSTEM = "None";
        }
    }

    public function display_critical($errorno, $errormsg, $errorlvl) {
        $GLOBALS['template']->assign('ERRORNO', $errorno);
        $GLOBALS['template']->assign('ERRORMSG', $errormsg);
        $GLOBALS['template']->assign('ERRORLVL', $errorlvl);
        $GLOBALS['template']->display('./templates/critical_error.tpl');
    }

    public function log_error($errorno,$errormsg,$errorlvl){
        if($this->LOG_ERROR === 1){
            //	Log Error:
            if($this->LOG_TYPE === 'DATABASE'){
                //	Log error in database:
                if(is_object($GLOBALS['database'])){
                    if($query = $GLOBALS['database']->execute_query("INSERT INTO 
                        `error_log` 
                            ( `id` , `errorno` , `errorlvl` , `errormsg`, 
                                `time` , `request_uri` , `ip` )
                        VALUES 
                            (NULL , '".$errorno."', '".$errorlvl."', '".$errormsg."', 
                                UNIX_TIMESTAMP(),'".$_SERVER['REQUEST_URI']."', '".$_SERVER['REMOTE_ADDR']."')")){
                        //	Row inserted into the table, error logged
                        return true;
                    }
                    else {
                        //	Failure in executing insert query, error not inserted!
                        return false;
                    }
                }
                else{
                    //	FAILURE, database object not instantiated, cannot communicate with database!
                    return false;
                }
            }
            elseif($this->LOG_TYPE == 'FILE'){
                //	Log error to file:
                //	Parse filename:
                $temp_filename = explode('[',$this->LOG_FILE_NAME);
                $filename = $temp_filename[0];
                $temp_filename = explode(']',$temp_filename[1]);
                $filename .= date($temp_filename[0]);
                $filename .= $temp_filename[1];
                //	Parse log data:
                $errordata = date('N G:i:s').' - '.$errorlvl.' - '.$errorno.' - '.$errormsg.
                        ' - '.$_SERVER['REQUEST_URI'].' - '.$_SERVER['REMOTE_ADDR']."\r\n";
                //	Make directory if not exists
                if(!is_dir($this->LOG_FILE_PATH)){
                    if(!mkdir($this->LOG_FILE_PATH)){
                        return false;
                    }
                }
                //	Open log file
                if($file = fopen($this->LOG_FILE_PATH.'/'.$filename,'a')){
                    //	Write to log file
                    fwrite($file,$errordata);
                }
                else{
                    return false;
                }
                return true;
            }
        }
        elseif($this->LOG_ERROR === 0){
            //	Errors not logged returns true because it was intended
            return true;
        }
        else{
            //	Invalid LOG_ERROR setting:
            return false;
        }
    }

    public function handle_error($errorno,$errormsg,$errorlvl,$content = false){
        //	Log error if errorlvl > LOG_ERROR_LEVEL
        if($this->LOG_ERROR_LEVEL < $errorlvl){
            if(!$this->log_error($errorno,$errormsg,$errorlvl)){
                $errormsg .= '<br> Additionally, an error occured while logging the error.';
            }
        }
        //	Process error:
        if($errorlvl >= $this->CRITICAL_LEVEL){
            //	System error, script unable to operate
            $this->display_critical($errorno,$errormsg,$errorlvl);
        }
        else{
            // Error encountered
            $GLOBALS['template']->assign('ERRORNO',$errorno);
            $GLOBALS['template']->assign('ERRORMSG',$errormsg);
            $GLOBALS['template']->assign('ERRORLVL',$errorlvl);

            if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
                $GLOBALS['template']->assign('errorLoad','./templates/error_mf.tpl');
            else
                $GLOBALS['template']->assign('errorLoad','./templates/error.tpl');

            if($this->ERROR_OVERRIDE_CONTENT === 'force' && $errorlvl >= $this->ERROR_OVERRIDE_CONTENT_LEVEL ){
                $content = false;
            }
            elseif($this->ERROR_OVERRIDE_CONTENT === 'never'){
                $content = true;
            }
            $GLOBALS['page']->content_visibility($content);
        }
    }
    
    public function captchaRequire($msg, $content = false) {    
        $this->debug( "Requiring Captcha" );        
        switch( $this->CAPTCHA_SYSTEM ){
            case "ReCaptcha": $this->reCaptchaRequire( $msg, $content ); break;
            case "SolveMedia": $this->solveMediaRequire( $msg, $content ); break;
        }  
    }
    
    public function checkCaptcha() {
        $this->debug( "Checking Captcha" );        
        switch( $this->CAPTCHA_SYSTEM ){
            case "ReCaptcha": return $this->reCaptchaCheck(); break;
            case "SolveMedia": return $this->solveMediaCheck(); break;
        } 
    }
	
    public function checkError() {
        return $this->error_num; // Used to check if errors were set previously
    }
    
    public function isCaptchaSubmitted(){
        $this->debug( "Checking if captcha is submitted" );
        switch( $this->CAPTCHA_SYSTEM ){
            case "ReCaptcha": 
                return isset($_POST['g-recaptcha-response']); 
            break;
            case "SolveMedia": 
                return isset($_POST['adcopy_response']) && isset($_POST['adcopy_challenge']); 
            break;
            case "None": 
                return true; 
            break;
        }
    }
    
    private function debug( $text ){
        if( $this->DEBUG == true ){
            echo "<br>".$text.".";
        }
    }
    
    
    // Specific Captcha Implementations //
    // ******************************** //
    
    // Captcha from SolveMedia
    private function solveMediaRequire( $msg, $content = false ){
        $this->debug( "Solve Media Require" );
        // Get the library
        require_once(Data::$absSvrPath.'/global_libs/General/solvemedialib.php');
        
        // Include all other current POST variables
        $loginInfo = "";
        foreach($_POST as $key => $val){
            if($key !== "adcopy_challenge" && $key !== "adcopy_response") {
                $loginInfo .= "<input type='hidden' name='".$key."' value='".$val."'></input>";
            }
        }  
        $GLOBALS['template']->assign('msg', $msg);                      
        $GLOBALS['template']->assign('reCaptcha', solvemedia_get_html( SOLVEMEDIA_CHALLENGE ) );                      
        $GLOBALS['template']->assign('loginInfo', $loginInfo);    
  
        if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
            $GLOBALS['template']->assign('errorLoad', './templates/reCaptcha/confirmationCode_mf.tpl');
        else
            $GLOBALS['template']->assign('errorLoad', './templates/reCaptcha/confirmationCode.tpl');

        $GLOBALS['page']->content_visibility($content); 
    }
    
    private function solveMediaCheck(){  
        $this->debug( "Solve Media Check" );       
        require_once(Data::$absSvrPath.'/global_libs/General/solvemedialib.php');
        $solvemedia_response = solvemedia_check_answer( 
                SOLVEMEDIA_VERIFICATION ,
                $_SERVER["REMOTE_ADDR"],
                $_POST["adcopy_challenge"],
                $_POST["adcopy_response"],
                SOLVEMEDIA_HASH
        );
        if (!$solvemedia_response->is_valid) {
            return false;
        }
        else {
            return true;
        }        
    }
    
    // ReCaptcha from Google
    private function reCaptchaRequire( $msg, $content = false ){

        $loginInfo = "";
        foreach($_POST as $key => $val){
            if($key !== "recaptcha_challenge_field" && $key !== "recaptcha_response_field") {
                $loginInfo .= "<input type='hidden' name='".$key."' value='".$val."'></input>";
            }
        }        
        $GLOBALS['template']->assign('msg', $msg);                      
        $GLOBALS['template']->assign('loginInfo', $loginInfo);      

        $reCaptcha = "<script src='https://www.google.com/recaptcha/api.js'></script>"."<div class='g-recaptcha' data-sitekey='6Lf4jlkUAAAAAIXUsZLD7I_syX1tNPn11omjfK2w'></div>";
        $GLOBALS['template']->assign('reCaptcha', $reCaptcha);                      

        if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
            $GLOBALS['template']->assign('errorLoad', './templates/reCaptcha/confirmationCode_mf.tpl');
        else
            $GLOBALS['template']->assign('errorLoad', './templates/reCaptcha/confirmationCode.tpl');

        $GLOBALS['page']->content_visibility($content); 
    }    
    private function reCaptchaCheck(){

        try
        {
            $secret = '6Lf4jlkUAAAAACNYLZWSmC7Gd6HV2r_jBl5ae02u';
            $response = $_POST['g-recaptcha-response'];
            $userip = $_SERVER['REMOTE_ADDR'];
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$response."&remoteip=".$userip;

            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );

            $result = file_get_contents($url, false, stream_context_create($arrContextOptions));
            $result = json_decode($result);
            $result = $result->success;
        }
        catch (exception $e)
        {
            $result = false;
        }

        return $result; // true if correct, false if incorrect
    }
    
}