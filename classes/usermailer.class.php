<?php 
/**
 * Mailer.php
 *
 * The Mailer class is meant to simplify the task of sending
 * emails to users. Note: this email system will not work
 * if your server is not setup to send mail.
 *
 * If you are running Windows and want a mail server, check
 * out this website to see a list of freeware programs:
 * <http://www.snapfiles.com/freeware/server/fwmailserver.html>
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 19, 2004
 */
 
 
class Mailer
{
   /**
    * sendWelcome - Sends a welcome message to the newly
    * registered user, also supplying the username and
    * password.
    */
   function sendWelcome($user, $email, $pass){
      $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $subject = "Jpmaster77's Site - Welcome!";
      $body = $user.",\n\n"
             ."Welcome! You've just registered at Jpmaster77's Site "
             ."with the following information:\n\n"
             ."Username: ".$user."\n"
             ."Password: ".$pass."\n\n"
             ."If you ever lose or forget your password, a new "
             ."password will be generated for you and sent to this "
             ."email address, if you would like to change your "
             ."email address you can do so by going to the "
             ."My Account page after signing in.\n\n"
             ."- Jpmaster77's Site";

      return mail($email,$subject,$body,$from);
   }
   
   /**
    * sendNewPass - Sends the newly generated password
    * to the user's email address that was specified at
    * sign-up.
    */
   function sendNewPass($user, $smtpRecipientAddr, $pass){
   require("phpmailer/class.phpmailer.php");
   require("db.class.php");
   require("ADLog.class.php");
   require("../config/config.inc.php");
   
   // declare DB Class
	$db = new db();
	// declare Logging Class
	$log = ADLog::getInstance();	
	// $log->logDir = $config_log_basedir; // set correct log dir
	
        $q = $db->q("SELECT smtpServerAddr, smtpFromAddr, smtpRecipientAddr, smtpAuth, smtpAuthUser, smtpAuthPass FROM settings");
        
        $result            = mysql_fetch_assoc($q);
        $smtpServerAddr    = $result['smtpServerAddr'];
        $smtpFromAddr      = $result['smtpFromAddr'];
        // $smtpRecipientAddr = $result['smtpRecipientAddr'];
        if ($result['smtpAuth'] == 1) {
            $smtpAuth     = $result['smtpAuth'];
            $smtpAuthUser = $result['smtpAuthUser'];
            $smtpAuthPass = $result['smtpAuthPass'];
        }
   
	$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch

	$body = $user.",<br/><br/>"
		 ."A new password has been generated for you "
		 ."at your request to log in rConfig.<br/><br/>"
		 ."Username: ".$user."<br/>"
		 ."New Password: ".$pass."<br/><br/>"
		 ."It is recommended that you change your password "
		 ."to something that is easier to remember, which "
		 ."can be done by going to the My Account page "
		 ."after signing in.<br/><br/>"
		 ."- rConfig Administrator";
			 
	try {
		$mail->IsSMTP(); // telling the class to use SMTP
		if ($result['smtpAuth'] == 1) {
			$mail->SMTPAuth = true; // enable SMTP authentication
			$mail->Username = $smtpAuthUser; // SMTP account username	
			$mail->Password = $smtpAuthPass; // SMTP account password
		}
		$mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent
		$mail->Host          = $smtpServerAddr; // sets the SMTP server
		$mail->Port          = 25; // set the SMTP port for the server
		$mail->SetFrom($smtpFromAddr, $smtpFromAddr);
		$mail->Subject = "rConfig - Your new password";
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($body);
		$mail->AddAddress($smtpRecipientAddr);
		
		if (!$mail->Send()) {
			$log->Fatal('Fatal:  Mailer Error (' . str_replace("@", "&#64;", $smtpRecipientAddr) . ') ' . $mail->ErrorInfo." Error:(File: ".$_SERVER['PHP_SELF'].")");
		} else {
			$log->Info('Info: Email Report sent to :' . $smtpRecipientAddr . ' (' . str_replace("@", "&#64;", $smtpRecipientAddr) . ')'." Error:(File: ".$_SERVER['PHP_SELF'].")");
		}
		// Clear all addresses and attachments for next loop
		$mail->ClearAddresses();
		$mail->ClearAttachments();
			
		$ret = true;
	} catch (phpmailerException $e) {
		$ret = $e->errorMessage(); //Pretty error messages from PHPMailer
		$log->Fatal($e->errorMessage()." Error:(File: ".$_SERVER['PHP_SELF'].")"); 
		$ret = false;
	} catch (Exception $e) {
		$ret = $e->getMessage(); //Boring error messages from anything else!
		$log->Fatal($e->getMessage()." Error:(File: ".$_SERVER['PHP_SELF'].")"); 
		$ret = false;
	}
	
      return $ret;
   }
};

/* Initialize mailer object */
$mailer = new Mailer;
 
?>
