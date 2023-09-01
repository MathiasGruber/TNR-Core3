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

require_once(Data::$absSvrPath.'/vendor/autoload.php');
require_once(Data::$absSvrPath.'/libs/mail.inc.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath."/libs/profileFunctions/registrationChecks.php");
require_once(Data::$absSvrPath."/libs/profileFunctions/registrationLib.php");
require_once(Data::$absSvrPath.'/global_libs/General/facebook.class.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

class register {

    private $enable;
    private $site_link;

    public function __construct() {

        try{
            if(isset($_SESSION['uid']))
                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);
            else
                $GLOBALS['database']->get_lock(__METHOD__,123,__METHOD__);

            $this->enable = true;
            /*
            switch (Data::$target_site) {
                case 'TND_': $this->enable = false; break;
            }*/

            if(isset($_POST['mail']))
                $_POST['mail'] = strtolower($_POST['mail']);
            if(isset($_POST['mail_v']))
                $_POST['mail_v'] = strtolower($_POST['mail_v']);

                

            $this->site_link = Data::$domainName;
            $this->pageTime = $GLOBALS['user']->load_time;

            if (!isset($_GET['act']) && $this->enable) {
                if (!isset($_SESSION['uid'])) {
                    if (!isset($_POST['Submit'])) {
                        $this->show_page();
                    } else {
                        $this->check_register();
                    }
                } else {
                    $GLOBALS['page']->Message("You are already logged in?", 'Account Registration', 'id=2');
                }
            } elseif (isset($_GET['act']) && ($_GET['act'] == 'facebookLogin') && $this->enable) {
                $this->facebookLogin();
            } elseif (isset($_GET['act']) && ($_GET['act'] == 'activate')) {
                $this->activate();
            } elseif (isset($_GET['act']) && ($_GET['act'] == 'forgot')) {
                if (isset($_GET['reqID'])) {
                    $this->recoverForgotPass();
                } elseif (!isset($_POST['Submit'])) {
                    $this->forgotForm();
                } elseif (isset($_POST['Submit'])) {
                    $this->sendForgotMail();
                }
            } elseif (isset($_GET['act']) && ($_GET['act'] == 'resend_activation') && $this->enable) {
                if (!isset($_POST['Submit'])) {
                    $this->resendActivation();
                } else {
                    $this->doResend();
                }
            } elseif (isset($_GET['act']) && ($_GET['act'] == 'send_unlock')) {
                if (!isset($_POST['Submit'])) {
                    $this->unlockForm();
                } else {
                    $this->sendUnlock();
                }
            } elseif (isset($_GET['act']) && isset($_GET['auth']) && ($_GET['act'] == 'do_unlock') && !isset($_POST['lgn_usr_stpd'])) {
                $this->doUnlock();
            } else {
                $GLOBALS['page']->Message("Either Registration is disabled or you were doing something bad. Please wait and try again or contact Support and wait until further notice.", 'Account Registration', 'id=1');
            }

            if( isset($_SESSION['uid']) && $GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , "Registration System", 'id='.$_GET['id'],'Return');
        }
    }

    private function show_page() {

        // Set village list
        $villages = array('Samui', 'Shroud', 'Silence', 'Konoki', 'Shine');
        shuffle($villages);
        $GLOBALS['template']->assign('villageList', $villages);

        // Set clan list
        require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
        $elements = array_map(function($word) {
            return ucfirst($word);
        }, Elements::$mainElements);
        $GLOBALS['template']->assign('clanList', $elements);

        // Set data from Facbeook
        if (isset($_GET['act']) && $_GET['act'] == 'facebookLogin') {

            $GLOBALS['template']->assign('fbID', $this->fbUserId);
            $fbUserInfo = $GLOBALS['facebook']->getUserInfo();

            if ($fbUserInfo) {

                // Pass on name
                if (isset($fbUserInfo["name"]) && $fbUserInfo["name"] !== "") {
                    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $randstring = '';

                    for ($i = 0; $i < 3; $i++) {
                        $randstring .= $characters[random_int(0, strlen($characters))];
                    }
                    $GLOBALS['template']->assign('fbName', explode(" ",$fbUserInfo["name"])[0] . $randstring);
                }

                // Pass on email
                if (isset($fbUserInfo["email"]) && ($fbUserInfo["email"] !== "")) {
                    $GLOBALS['template']->assign('fbEmail', $fbUserInfo["email"]);
                }

                // Pass on gender
                if (isset($fbUserInfo["gender"]) && ($fbUserInfo["gender"] !== "")) {
                    $GLOBALS['template']->assign('fbGender', $fbUserInfo["gender"]);
                }
            }
        }
        $GLOBALS['template']->assign('contentLoad', './templates/content/register/registrationForm.tpl');
    }

    private function facebookLogin() {

        // Get the custom class and SDK
        $GLOBALS['facebook'] = new FBinteract;

        // Connect
        $GLOBALS['facebook']->fbConnect();

        // Get the fb user id
        $this->fbUserId = $GLOBALS['facebook']->getUser();

        // Decide what to show
        if ($this->fbUserId == 0) {
            $_GET['act'] = "";
            $this->show_page(); // Test
        } else {
            // Check user
            $userCheck = $GLOBALS['database']->fetch_data("SELECT `username`, `fbID`
                FROM `users`
                WHERE `fbID` = '" . $this->fbUserId . "' LIMIT 1");

            if ($userCheck !== "0 rows") {
                if (!isset($_SESSION['uid'])) {
                    // User is registered, pass login and go to captcha
                    $GLOBALS['template']->assign('menuLoad', './templates/reCaptcha/reCaptchaInfo.tpl');
                    $_POST['lgn_usr_stpd'] = $userCheck[0]['username'];
                    $GLOBALS['error']->captchaRequire('Confirm your humanity by entering the confirmation code. <br/> Press "Enter" to continue!');
                } else {
                    header("LOCATION: ?id=2");
                }
            } else {
                // User has accepted app, but is not registered. Go to registration page, and save FB id for account
                $this->show_page();
            }
        }
    }

    private function check_register() {
        if( isset($_POST['rules']) &&
            isset($_POST['terms']) &&
            isset($_POST['username']) &&
            isset($_POST['mail']) &&
            isset($_POST['mail_v']) &&
            isset($_POST['password']) &&
            isset($_POST['password_v']) )
        {
            if ($_POST['rules'] == 1) {
                if ($_POST['terms'] == 1) {
                    $_POST['username'] = str_replace(" ", "", $_POST['username']);
                    $testUsername = username_check($_POST['username']);
                    if ($testUsername[1] == 1) {
                        $testEmail = email_check($_POST['mail']);
                        if ($testEmail[1] == 1) {
                            $validateMail = email_check_confirm($_POST['mail'], $_POST['mail_v']);
                            if ($validateMail[1] == 1) {
                                $validatePassword = password_check_confirm($_POST['password'], $_POST['password_v']);
                                if ($validatePassword[1] == 1) {
                                    $testPassword = password_check($_POST['password']);
                                    if ($testPassword[1] == 1) {

                                        // Show password in email
                                        $this->emailPass = $_POST['password'];

                                        // Do checks
                                        $_POST['gender'] = gender_check($_POST['gender']);
                                        $_POST['village'] = village_check($_POST['village']);

                                        // Encrypt password using salt
                                        $newPass = functions::encryptPassword($_POST['password'], $this->pageTime);
                                        $_POST['salted_password'] = $newPass;
                                        $_POST['password'] = "";

                                        // FB check
                                        if (isset($_POST['Facebook'])) {
                                            // Get the fb user id

                                            $GLOBALS['facebook'] = new FBinteract;
                                            $GLOBALS['facebook']->fbConnect();

                                            // Check if the fb is correct
                                            $userCheck = $GLOBALS['database']->fetch_data("SELECT `id`, `username`, `fbID`
                                                FROM `users`
                                                WHERE `fbID` = '" . $_POST['Facebook'] . "' LIMIT 1");

                                            // Only one account / fb
                                            if ($GLOBALS['facebook']->getUser() !== $_POST['Facebook'] || $userCheck !== "0 rows") {
                                                $_POST['Facebook'] = 0;
                                            }
                                        } else {
                                            $_POST['Facebook'] = 0;
                                        }

                                        // No starting reputaiton points
                                        $this->startReps = 0;

                                        // Start the registration
                                        $this->do_register();

                                    } else {
                                        $GLOBALS['page']->Message("<b>Password:</b> " . $testPassword[0], 'Registration Error: Password', 'id=63');
                                    }
                                } else {
                                    $GLOBALS['page']->Message("<b>Password Verification:</b> " . $validatePassword[0], 'Registration Error: Password', 'id=63');
                                }
                            } else {
                                $GLOBALS['page']->Message("<b>Email Verification:</b> " . $validateMail[0], 'Registration Error: Email', 'id=63');
                            }
                        } else {
                            $GLOBALS['page']->Message("<b>Email:</b> " . $testEmail[0], 'Registration Error: Email', 'id=63');
                        }
                    } else {
                        $GLOBALS['page']->Message("<b>Username:</b> " . $testUsername[0], 'Registration Error: Username', 'id=63');
                    }
                } else {
                    $GLOBALS['page']->Message("You did not agree to the terms and conditions", 'Registration Error', 'id=63');
                }
            } else {
                $GLOBALS['page']->Message("You did not agree to the rules", 'Registration Error', 'id=63');
            }
        }
        else {
            $GLOBALS['page']->Message("All fields must be entered", 'Registration Error: Email', 'id=63');
        }
    }

    private function do_register() {

        // Try-catch locally
        try{

            // Start the transaction
            $GLOBALS['database']->transaction_start();

            // Set layout
            $layout = ( $GLOBALS['deviceType'] == "phone" ) ? "default" : "default";

            // Whip out the registration library and use that
            $registrationHandler = new registrationLib;
            $check = $registrationHandler->registerUser( array(
                "username" => $_POST['username'],
                "saltedPassword" => $_POST['salted_password'],
                "mail" => $_POST['mail'],
                "gender" => $_POST['gender'],
                "village" => $_POST['village'],
                "clanElement" => $_POST['clanElement'],
                "layout" => $layout,
                "facebook" => $_POST['Facebook'],
                "pageTime" => $this->pageTime
            ) );

            // Check registration
            if( $check == true ){

                // Send verification e-mail:
                $mail = new Mail();

                // Register the completion
                $GLOBALS['page']->addFacebookEvent("CompleteRegistration");

                // Set subject
                $subject = 'TheNinja-RPG Account';
                $recipient = $_POST['mail'];
                $message = '
                    Thank you for you registration. Click on the following link or paste it to your browser to activate your account. <br>

                    ' . $this->site_link . '/?id=63&amp;act=activate&amp;user=' . $_POST['username'] . '&amp;code=' . md5($_POST['mail'] . 'confirm') . ' <br>
                    <br>
                    Your username: ' . $_POST['username'] . ' <br>
                    Your password: ' . $this->emailPass . ' <br>
                    <br><br>
                    You will find the manual at: <br>
                    http://www.theninja-forum.com/content.php<br>
                    <br>
                    Please note that any spaces or unaccepted characters have been removed from your username!<br>
                    <br>
                    We, TheNinja-RPG team, hope that you will have a joyfull gaming experience. Good luck and have fun! <br>
                    <br>
                    Not your account?: If you didnt recently create this account, please ignore this email.
                ';

                // Commit the transaction
                $GLOBALS['database']->transaction_commit();

                if ($mail->Send($recipient, $subject, $message, $message)) {

                    // For facebook users, go directly to profile
                    if ($_POST['Facebook'] !== 0) {
                        $GLOBALS['template']->assign('menuLoad', './templates/reCaptcha/reCaptchaInfo.tpl');
                        $_POST['lgn_usr_stpd'] = $_POST['username'];
                        $GLOBALS['error']->captchaRequire('Confirm your humanity by entering the confirmation code. <br/> Press "Enter" to continue!');
                    }
                    elseif( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true ){

                        // Show message to user
                        $GLOBALS['page']->Message( "Your account has been successfully registered. Enjoy playing the game!" , 'Registration Success', 'id=2',"Go to profile");

                        // If registered with facebook, do that registration now
                        if( isset($_POST['fbID']) && !empty($_POST['fbID']) ){

                            // Set current values of user
                            $GLOBALS['userdata'][0]['username'] = $_POST['username'];
                            $GLOBALS['userdata'][0]['fbID'] = 0;

                            // Update using facebook class
                            $this->facebook = new FBinteract;
                            $this->facebook->registerUserWithFB($_POST['fbID']);
                        }
                        else{
                            // For mobile, still disable activation requirement
                            $GLOBALS['database']->execute_query("UPDATE `users` SET `activation` = '1' WHERE `username` = '" . $_POST['username'] . "' LIMIT 1");
                        }

                    } else {
                        $GLOBALS['template']->assign('contentLoad', './templates/content/register/registrationSuccess.tpl');
                    }
                } else {
                    throw new Exception("Error sending mail");
                }
            }
            else{
                throw new Exception("Unknown error registering");
            }
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Registration Error', 'id='.$_GET['id']);
        }
    }

    private function activate() {
        if (isset($_GET['code']) && isset($_GET['user'])) {
            $userdata = $GLOBALS['database']->fetch_data("SELECT `mail`, `activation`, `id` FROM `users` WHERE `username` = '" . $_GET['user'] . "' LIMIT 1");
            if ($userdata !== '0 rows') {
                if ($userdata[0]['activation'] == 0) {
                    $_GET['code'] = str_replace("%20", "", str_replace(" ", "",$_GET['code']));
                    if (md5($userdata[0]['mail'] . 'confirm') == $_GET['code']) {
                        if ($GLOBALS['database']->execute_query("UPDATE `users` SET `activation` = '1' WHERE `id` = '" . $userdata[0]['id'] . "' LIMIT 1")) {
                            $GLOBALS['page']->Message("Your account has now been activated, you can now log in.", 'Activation Success', 'id=1');
                        } else {
                            $GLOBALS['page']->Message("An error occured while activating your account in the database", 'Activation Error', 'id=1');
                        }
                    } else {
                        $GLOBALS['page']->Message("This activation code is not valid, please follow the link specified in the mail<br>
                                                   If problems persist contact support.", 'Activation Error', 'id=1');
                    }
                } else {
                    $GLOBALS['page']->Message("This account has already been activated.", 'Activation Error', 'id=1');
                }
            } else {
                $GLOBALS['page']->Message("The user could not be found, please follow the link specified in the mail.<br>
                                           If problems persist, then contact support. ", 'Activation Error', 'id=1');
            }
        } else {
            $GLOBALS['page']->Message("No user or activation code were set, please follow the link specified in the e-mail. ", 'Activation Error', 'id=1');
        }
    }

    // For for sending new password
    private function forgotForm() {

        // Delete old requests
        $GLOBALS['database']->execute_query("DELETE FROM `pass_request` WHERE `time` <= '" . ($GLOBALS['user']->load_time - 1800) . "'");

        // Create the fields to be shown
        $inputFields = array(
            array("infoText" => "E-mail address", "inputFieldName" => "email", "type" => "input", "inputFieldValue" => "")
        );

        // Show the form
        $GLOBALS['page']->UserInput(
                "This feature allows you to recover your password, granted that the e-mail you specified when you created the character is still in use.", // Information
                "Recover password", // Title
                $inputFields, // input fields
                array("href" => "?id=" . $_GET['id'] . "&amp;act=" . $_GET['act'], "submitFieldName" => "Submit", "submitFieldText" => "Submit"), // Submit button
                "Return" // Return link name
        );
    }

    private function sendForgotMail() {
        $_POST['email'] = str_replace("/", "", $_POST['email']);
        $user = $GLOBALS['database']->fetch_data("SELECT `mail`, `username`, `id` FROM `users` WHERE `mail` = '" . $_POST['email'] . "' LIMIT 1");

        if ($user !== '0 rows') {
            // Make Authorization Code as random as possible. Using special character "~" to make brute force harder/impossible.
            $auth_code = md5('~' . $this->generatePassword() . '_' . $user[0]['mail'] . '_' . $this->generatePassword() . '~');

            if ($GLOBALS['database']->execute_query("INSERT INTO
				`pass_request`
					(`time`, `uid`, `username`, `auth_code`, `mail_addr`, `IP`)
				VALUES
					('" . $GLOBALS['user']->load_time . "', '" . $user[0]['id'] . "', '" . $user[0]['username'] . "', '" . $auth_code . "', '" . $user[0]['mail'] . "', '" . $GLOBALS['user']->real_ip_address() . "');")) {


                // Send verification e-mail:
                $mail = new Mail();

                $subject = 'TheNinja-RPG Account Recovery';
                $recipient = $user[0]['mail'];
                $message = '
                    Someone on the following ip: ' . $GLOBALS['user']->real_ip_address() . ' has requested a password reset for your account: ' . $user[0]['username'] . '<br>
                    <br>
                    To verify this request please go to the following URL in your browser:<br>
                    ' . $this->site_link . '/?id=63&amp;act=forgot&amp;reqID=' . $auth_code . '<br>
                    <br>
                    Your new password will be sent to this e-mail address afterwards.
                ';

                if ($mail->Send($recipient, $subject, $message, $message)) {
                    $GLOBALS['page']->Message("Your request has been processed, check your e-mail for further instructions.", 'Forgot Password: Success', 'id=1');
                } else {
                    $GLOBALS['page']->Message("Error sending mail", 'Registration Error: Bingo Book', 'id=63');
                }
            } else {
                $GLOBALS['page']->Message("An error occured uploading your request, please try again later.", 'Forgot Password: Error', 'id=' . $_GET['id'] . '&amp;act=forgot');
            }
        } else {
            $GLOBALS['page']->Message("No user is registered with this e-mail address", 'Forgot Password: Error', 'id=' . $_GET['id'] . '&amp;act=forgot');
        }
    }

    private function generatePassword() {
        // Letterbox
        $letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

        // Set preset vars
        $pass = '';

        // Get password
        for ($i = 0; $i < random_int(12, 16); $i++) {
            $tempkey = $letters[random_int(0, count($letters)-1)];
            if ((random_int(0, 1) == 1) && !is_numeric($tempkey)) {
                $tempkey = strtoupper($tempkey);
            }
            $pass .= $tempkey;
        }
        return $pass;
    }

    private function recoverForgotPass() {
        $request = $GLOBALS['database']->fetch_data("SELECT `pass_request`.`uid`, `pass_request`.`username`, `pass_request`.`IP`, `pass_request`.`auth_code`, `users`.`mail`
			FROM `pass_request`, `users`
			WHERE `auth_code` = '" . $_GET['reqID'] . "' AND `users`.`id` = `pass_request`.`uid` LIMIT 1");

        if ($request !== '0 rows') {
            if ( $GLOBALS['user']->real_ip_address() == $request[0]['IP']) {

                // All checks clear, Generate password
                $password = $this->generatePassword();

                // Delete request
                $GLOBALS['database']->execute_query("DELETE FROM `pass_request` WHERE `auth_code` = '" . $request[0]['auth_code'] . "' LIMIT 1");

                // Upload new password
                $GLOBALS['database']->execute_query("UPDATE `users` SET `password` = '" . md5($password) . "' WHERE `id` = '" . $request[0]['uid'] . "' LIMIT 1");

                // Send verification e-mail:
                $mail = new Mail();

                $subject = 'TheNinja-RPG Password Change';
                $recipient = $request[0]['mail'];
                $message = '
                    <p>A password recovery was requested and completed, and the password to your account ' . $request[0]['username'] . ' has hereby changed.</p>
                    <p>Your new password is:<br><b>' . $password . '</b></p>
                    <p>You can change this password after you log in.</p>
                ';


                if ($mail->Send($recipient, $subject, $message, $message)) {
                    $GLOBALS['page']->Message("Your new password has been sent to your e-mail address. We advise you to change this password to a more secure password as soon as possible.", 'Password Recovery: Success', 'id=1');
                } else {
                    $GLOBALS['page']->Message("An error occured sending the e-mail, please try again later.<br><br>If problems persist please contact support.", 'Password Recovery: Error', 'id=1');
                }
            } else {
                $GLOBALS['page']->Message("Your IP does not match with the one in the request, please re-issue the request in 30 minutes.", 'Password Recovery: Error', 'id=1');
            }
        } else {
            $GLOBALS['page']->Message("No password recovery request was issued for this account.", 'Password Recovery: Error', 'id=1');
        }
    }

    private function resendActivation() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Email","inputFieldName"=>"email", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Enter the email you've used to register a user, and we'll resent the activation email for it.", // Information
            "Resend Verification E-mail", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id']."&amp;act=resend_activation" , "submitFieldName" => "Submit","submitFieldText" => "Send"), // Submit button
            "Return" // Return link name
        );

    }

    private function doResend() {
        if( isset($_POST['email']) ){
            $_POST['email'] = str_replace("%", "", $_POST['email']);
            $_POST['email'] = str_replace(" ", "", $_POST['email']);
            $user = $GLOBALS['database']->fetch_data("SELECT `mail`, `activation`, `username` FROM `users` WHERE `mail` LIKE '" . $_POST['email'] . "'");
            if ($user !== '0 rows') {
                if ($user[0]['activation'] == 0) {

                    // Send verification e-mail:
                    $mail = new Mail();

                    // Set subject
                    $subject = 'TheNinja-RPG Account';
                    $recipient = $user[0]['mail'] ;
                    $message = '
                        <p>Thank you for you registration. Click on the following link or paste it to your browser to activate your account. </p>
                        <p>' . $this->site_link . '/?id=63&amp;act=activate&amp;user=' . $user[0]['username'] . '&amp;code=' . md5($user[0]['mail'] . 'confirm') . '</p>
                        <p>Your username:
                        ' . $user[0]['username'] . ' </p>

                        <p>You will find the manual at:
                        http://www.theninja-forum.com/content.php</p>

                        <p>We, TheNinja-RPG Team, hope that you will have a joyful gaming experience. Good luck and have fun!</p>
                    ';

                    if ($mail->Send($recipient, $subject, $message, $message)) {
                        $GLOBALS['page']->Message("The activation mail for this character has been resent.", 'Password Recovery: Success', 'id=1');
                    } else {
                        throw new Exception("An error occured sending the e-mail, please try again later. If problems persist please contact support.");
                    }
                } else {
                    throw new Exception("This account is already activated");
                }
            } else {
                throw new Exception("No account exists with this email. Potentially, you've mistyped the email when registering, in which case you should try registering again.");
            }
        }
        else{
            throw new Exception("You must specify an email");
        }
    }

    private function unlockForm() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"User","inputFieldName"=>"username", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Enter the username you wish to unlock", // Information
            "Unlock Character", // Title
            $inputFields, // input fields
            array("href" => "" , "submitFieldName" => "Submit","submitFieldText" => "Unlock"), // Submit button
            "Return" // Return link name
        );

    }

    private function doUnlock() {
        $_GET['auth'] = str_replace("%20", "", str_replace(" ", "",$_GET['auth']));
        $request = $GLOBALS['database']->fetch_data("SELECT `unlock`.`time`, `unlock`.`uid`, `unlock`.`auth_code`, `users_preferences`.`lock_count`
		FROM `unlock`, `users`, `users_preferences`
		WHERE `unlock`.`auth_code` = '" . $_GET['auth'] . "' AND `users`.`id` = `unlock`.`uid` AND `users_preferences`.`uid` = `users`.`id` LIMIT 1");

        if ($request !== '0 rows') {
            if ($request[0]['lock_count'] >= 3) {
                if ($GLOBALS['user']->load_time < ($request[0]['time'] + 3600)) {
                    if ($GLOBALS['database']->execute_query("UPDATE `users_preferences`
						SET `lock_count` = 0
						WHERE `uid` = '" . $request[0]['uid'] . "' LIMIT 1")) {
                        $GLOBALS['database']->execute_query("DELETE FROM `unlock` WHERE `auth_code` = '" . $request[0]['auth_code'] . "' LIMIT 1");
                        $GLOBALS['page']->Message("Your account has now been unlocked, you can now log in.", 'Unlock Character: Success', 'id=1');
                    } else {
                        $GLOBALS['page']->Message("A database error occured unlocking the account.", 'Unlock Character: Error', 'id=1');
                    }
                } else {
                    $GLOBALS['page']->Message("This unlock code has expired, please request a new one.", 'Unlock Character: Error', 'id=1');
                }
            } else {
                $GLOBALS['page']->Message("This account hasn't been locked.", 'Unlock Character: Error', 'id=1');
            }
        } else {
            $GLOBALS['page']->Message("The user could not be found, please follow the link specified in the mail<br>
									   If problems persist contact support.", 'Unlock Character: Error', 'id=1');
        }
    }

    private function sendUnlock() {
        $user = $GLOBALS['database']->fetch_data("SELECT `users`.`mail`, `users`.`username`, `users`.`activation`, `users_preferences`.`lock_count`, `users_preferences`.`uid`
			FROM `users`, `users_preferences`
			WHERE `users`.`username` = '" . $_POST['username'] . "' AND `users_preferences`.`uid` = `users`.`id` LIMIT 1");

        if ($user !== '0 rows') {
            if ($user[0]['activation'] !== 0) {
                if ($user[0]['lock_count'] >= 3) {
                    $unlockCode = md5($GLOBALS['user']->load_time + $user[0]['username'] + 'UNLOCK');

                    // Send verification e-mail:
                    $mail = new Mail();

                    $subject = 'TheNinja-RPG Account Unlock';
                    $recipient = $user[0]['mail'] ;
                    $message = '
                    <p>This e-mail has been sent to you because you requested your account on TheNinja-RPG be unlocked. After previously being locked due to having too many invalid login attempts.</p>
                    <p>To unlock your account <a href="' . $this->site_link . '/?id=63&amp;act=do_unlock&amp;auth=' . $unlockCode . '">Click here</a> or copy the following link into your browser:</p>
                    <p>' . $this->site_link . '/?id=63&amp;act=do_unlock&amp;auth=' . $unlockCode . '</p>
                    <p>This unlock code will remain valid for 60 minutes or until the account has been unlocked.</p>
                    <p>Request sent by: ' . $GLOBALS['user']->real_ip_address() . '</p>
                    ';

                    if ($mail->Send($recipient, $subject, $message, $message)) {

                        $GLOBALS['database']->execute_query("DELETE FROM `unlock` WHERE `uid` = '".$user[0]['uid']."'");
                        $GLOBALS['page']->Message("The unlock mail for this character has been sent to ".$user[0]['mail'], 'Unlock Character: Success', 'id=1');

                        $GLOBALS['database']->execute_query("INSERT INTO
                            `unlock`
                                    (`uid`, `time`, `username`, `auth_code`, `IP`)
                            VALUES
                                    ('" . $user[0]['uid'] . "',
                                     '" . $GLOBALS['user']->load_time . "',
                                     '" . $user[0]['username'] . "',
                                     '" . $unlockCode . "',
                                     '" . $GLOBALS['user']->real_ip_address() . "');");

                        $email = explode('@', $user[0]['mail']);
                        $email = substr($email[0],0,floor(strlen($email[0])/2)) . str_repeat('*',ceil(strlen($email[0])/2)). '@' . $email[1];

                        $GLOBALS['page']->Message("The unlock mail for this character has been sent to ".$email, 'Unlock Character: Success', 'id=1');
                    } else {
                        $GLOBALS['page']->Message("An error occured sending the e-mail, please try again later.<br><br>If problems persist please contact support.", 'Account Unlocking', 'id=1');
                    }
                } else {
                    $GLOBALS['page']->Message("The account hasn't been locked!", 'Unlock Character: Error', 'id=63&amp;act=send_unlock');
                }
            } else {
                $GLOBALS['page']->Message("The account hasn't been activated!", 'Unlock Character: Error', 'id=63&amp;act=send_unlock');
            }
        } else {
            $GLOBALS['page']->Message("The account does not exist within the system!", 'Unlock Character: Error', 'id=63&amp;act=send_unlock');
        }
    }

}

new register();