<?php
/*
Steps work like this;
1. Load external files
2. instantiate ../../classes - db and config
3. check if POST contains 'add'. If it does carry out the add script
4. elseif, check if POST contains delete. if it does, carry out delete script
5. elseif, check if POST contains 'getRow' and 'id'. If it does, do a select from DB to populate form to prepare for row edit
*/

require_once("../../../classes/db.class.php");
require_once("../../../classes/ADLog.class.php");
require_once("../../../config/config.inc.php");

$db  = new db();
$log = ADLog::getInstance();

/* Add SMTP Settings Here */

if (isset($_POST['add'])) {
    session_start();
    $errors = array();
    
    if (!empty($_POST['smtpServerAddr'])) {
        /* Begin DB query. This will either be an Insert if $_POST editid is not set - or an edit/Update if editid is set in POST */
        
        if (is_string($_POST['smtpServerAddr'])) {
            $smtpServerAddr = $_POST['smtpServerAddr'];
            
            if (filter_var($_POST['smtpFromAddr'], FILTER_VALIDATE_EMAIL)) {
                $smtpFromAddr = $_POST['smtpFromAddr'];
            } else {
                $errors['smtpFromAddr'] = "Please enter a valid email address";
                $log->Warn("Failure: Please enter a valid email address (File: " . $_SERVER['PHP_SELF'] . ")");
            }
            
            if (isset($_POST['smtpAuth'])) {
                $smtpAuth = $_POST['smtpAuth'];
            } else {
                $smtpAuth = '0';
            }
            
            $smtpAuthUser      = $_POST['smtpAuthUser'];
            $smtpAuthPass      = $_POST['smtpAuthPass'];
            $smtpRecipientAddr = $_POST['smtpRecipientAddr'];
            
            // get each email address from textarea
            $smtpRecipientAddresses = explode("; ", $smtpRecipientAddr);
            $newSmtpRecipientAddr   = '';
            foreach ($smtpRecipientAddresses as $address) {
                if (filter_var($address, FILTER_VALIDATE_EMAIL)) { // validate each address
                    
                    $newSmtpRecipientAddr .= $address . "; ";
                } else {
                    $errors['smtpRecipientAddr'] = "Please enter a valid email address";
                    $log->Warn("Failure: Please enter a valid email address (File: " . $_SERVER['PHP_SELF'] . ")");
                }
            }
            // next once emails validated put email Recipients string back together
            $newSmtpRecipientAddr = rtrim($newSmtpRecipientAddr); // remove trailing whitespace
            $newSmtpRecipientAddr = rtrim($newSmtpRecipientAddr, ";"); // remove trailing ";" from new string
            
            
            
            if (!empty($errors)) {
                $errors['Fail'] = "There were errors!";
                $log->Info("Success:There were errors! on form (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "settings.php#emailSettings");
                exit();
            }
            
            $q = "UPDATE settings SET
					smtpServerAddr = '" . $smtpServerAddr . "', 
					smtpFromAddr = '" . $smtpFromAddr . "',
					smtpAuth =  '" . $smtpAuth . "',
					smtpAuthUser = '" . $smtpAuthUser . "',
					smtpAuthPass = '" . $smtpAuthPass . "',
					smtpRecipientAddr =  '" . $newSmtpRecipientAddr . "'
						WHERE id = 1"; // update on id 1, cause settings tbl will always only have one row
            
            if ($result = $db->q($q)) {
                $errors['Success'] = "SMTP Settings saved";
                $log->Info("Success: SMTP Settings saved (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "settings.php#emailSettings");
                exit();
            } else {
                $errors['Fail'] = "ERROR: " . mysql_error();
                $log->Fatal("Fatal: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "settings.php#?error#emailSettings");
                exit();
            }
            
        } else { // categoryName was NOT a String, and so end back error and kill script
            $errors['categoryName'] = "Server Address Field was not a string";
            $log->Warn("Failure: Server Address Field was not a string (File: " . $_SERVER['PHP_SELF'] . ")");
            $_SESSION['errors'] = $errors;
            session_write_close();
            header("Location: " . $config_basedir . "settings.php?error#emailSettings");
            exit();
        }
        
        
    } else { // categoryName was not filed in, and so end back error and kill script
        $errors['categoryName'] = "Category Field cannot be emtpy";
        $log->Warn("Failure: Category Name Field cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
        $_SESSION['errors'] = $errors;
        session_write_close();
        header("Location: " . $config_basedir . "settings.php?error#emailSettings");
        exit();
    }
    
}
/* end 'add/editid' if*/


/* begin delete check */
elseif (isset($_POST['del'])) {
    /* the query*/
    $q = "UPDATE settings SET
					smtpServerAddr = NULL, 
					smtpFromAddr = NULL,
					smtpAuth = '0',
					smtpAuthUser = NULL,
					smtpAuthPass = NULL,
					smtpRecipientAddr =  NULL
						WHERE id = 1"; // update on id 1, cause settings tbl will always only have one row
    
    if ($result = $db->q($q)) {
        $log->Info("Success: SMTP Settings cleared (File: " . $_SERVER['PHP_SELF'] . ")");
        $response = json_encode(array(
            'success' => true
        ));
        
    } else {
        $log->Warn("Failure: Unable clear SMTP Settings (File: " . $_SERVER['PHP_SELF'] . ")");
        $response = json_encode(array(
            'failure' => true
        ));
    }
    
    echo $response;
}
/* end 'delete' if*/

?>