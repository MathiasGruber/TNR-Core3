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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Mail {

    public function __construct() {

        $this->sender = 'theninja.emailer@gmail.com';
        $this->senderName = 'TheNinja.Emailer';        
        $this->host = 'email-smtp.us-west-2.amazonaws.com';
        $this->port = 587;

        $this->email = new PHPMailer(true);

        // Specify the SMTP settings.
        $this->email->isSMTP();
        $this->email->setFrom($this->sender, $this->senderName);
        $this->email->Username   = EMAIL_USERNAME;
        $this->email->Password   = EMAIL_PASSWORD;
        $this->email->Host       = $this->host;
        $this->email->Port       = $this->port;
        $this->email->SMTPAuth   = true;
        $this->email->SMTPSecure = 'tls';

    }

    public function Send($recipient, $subject, $bodyHtml, $bodyText){
        try {

            $this->email->addAddress($recipient);
            $this->email->isHTML(true);
            $this->email->Subject    = $subject;
            $this->email->Body       = $bodyHtml;
            $this->email->AltBody    = $bodyText;
            $this->email->Send();
            return true;

        } catch (phpmailerException $e) {
            echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
        } catch (Exception $e) {
            echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
        }
    }
}