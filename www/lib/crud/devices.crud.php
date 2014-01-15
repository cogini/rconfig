<?php
require_once("../../../classes/db.class.php");
require_once("../../../classes/ADLog.class.php");
require_once("../../../config/config.inc.php");
require_once("../../../config/functions.inc.php");

$db  = new db();
$log = ADLog::getInstance();

/* Add Custom Property Here */
if (isset($_POST['add'])) {
    session_start();
    $errors = array();
    
    // validate deviceName field
    if (!empty($_POST['deviceName'])) {
		$deviceName = mysql_real_escape_string($_POST['deviceName']);
			// check device name for whitespace
		if(!chkWhiteSpaceInStr($deviceName) === false){
			$errors['deviceName'] = "Device Name cannot contain spaces";
			$log->Warn("Failure: Device Name cannot contain spaces (File: " . $_SERVER['PHP_SELF'] . ")");
			$deviceName = ""; // set back to blank so text with spaces is not returned to devices form
		}
    } else {
        $errors['deviceName'] = "Device Name cannot be emtpy";
        $log->Warn("Failure: Device Name cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }

    if (!empty($_POST['deviceIpAddr'])) {
        // validate deviceIpAddr IP address
        if (!filter_var($_POST['deviceIpAddr'], FILTER_VALIDATE_IP)) {
            $errors['deviceIpAddr'] = "IP Address is not valid ";
            $log->Warn("Failure: IP Address is not valid (File: " . $_SERVER['PHP_SELF'] . ")");
        } else {
            $deviceIpAddr = mysql_real_escape_string($_POST['deviceIpAddr']);
        }
    } else {
        $errors['deviceIpAddr'] = "IP Address cannot be emtpy ";
        $log->Warn("Failure: IP Address cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }    
	
    // validate devicePrompt field
    if (!empty($_POST['devicePrompt'])) {
        $devicePrompt = mysql_real_escape_string(str_replace(' ', '', $_POST['devicePrompt']));
    } else {
        $errors['devicePrompt'] = "Device Prompt cannot be emtpy";
        $log->Warn("Failure: Device Prompt cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }
	
    
    // validate vendorId field
    if (!empty($_POST['vendorId']) && ctype_digit($_POST['vendorId'])) {
        $vendorId = mysql_real_escape_string($_POST['vendorId']);
    } else {
        $errors['vendorId'] = "Vendor field cannot be emtpy";
        $log->Warn("Failure: Vendor Field cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }
    
    // validate deviceModel field
    if (!empty($_POST['deviceModel'])) {
        $deviceModel = mysql_real_escape_string($_POST['deviceModel']);
    } else {
        $errors['deviceModel'] = "Device Model cannot be emtpy";
        $log->Warn("Failure: Device Model cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }
    
    // validate deviceUsername field
    if (!empty($_POST['deviceUsername']) && is_string($_POST['deviceUsername'])) {
        $deviceUsername = mysql_real_escape_string($_POST['deviceUsername']);
    } else {
        $errors['deviceUsername'] = "Username cannot be emtpy";
        $log->Warn("Failure: Username field cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }
    
    // validate devicePassword field
    if (!empty($_POST['devicePassword']) && is_string($_POST['devicePassword'])) {
        $devicePassword = mysql_real_escape_string($_POST['devicePassword']);
    } else {
        $errors['devicePassword'] = "Password cannot be emtpy";
        $log->Warn("Failure: Password field cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }
    
    // validate devicePassConf field
    if (!empty($_POST['devicePassConf']) && is_string($_POST['devicePassConf'])) {
        if ($_POST['devicePassConf'] !== $_POST['devicePassword']) {
            $errors['devicePassConf'] = "Passwords to not match";
            $log->Warn("Failure: Passwords to not match (File: " . $_SERVER['PHP_SELF'] . ")");
        } else {
            $devicePassConf = $_POST['devicePassConf'];
        }
    } else {
        $errors['devicePassword'] = "Confirm Password field cannot be emtpy";
        $log->Warn("Failure: Confirm Password field cannot be emtpy (File: " . $_SERVER['PHP_SELF'] . ")");
    }
    
    // if 'deviceEnableMode' is checked - deviceEnablePassword field must be populated
	if (isset($_POST['deviceEnableMode'])){
		if ($_POST['deviceEnableMode'] == 'on' && empty($_POST['deviceEnablePassword'])) {
			$errors['deviceEnableMode'] = "Enable mode checked but password was not entered";
			$log->Warn("Failure: Enable mode checked but password was not entered (File: " . $_SERVER['PHP_SELF'] . ")");
		} else {
			$deviceEnableMode     = $_POST['deviceEnableMode'];
			$deviceEnablePassword = mysql_real_escape_string($_POST['deviceEnablePassword']);
		}
	} else {
		$deviceEnableMode     = 'off';
		$deviceEnablePassword = '';
	}
    
    // validate catId field
    if (ctype_digit($_POST['catId'])) {
        $catId = $_POST['catId'];
    } else {
        $errors['catId'] = "Category field cannot be empty";
        $log->Warn("Failure: Category field did not pass numeric value i.e. catId OR awas empty (File: " . $_SERVER['PHP_SELF'] . ")");
    }
	
	if(isset($_POST['username']) && ctype_alnum($_POST['username'])){
		$username = mysql_real_escape_string($_POST['username']);
	} else {
        $errors['username'] = "Username passed to devices.crud.php was not valid";
        $log->Warn("Failure: Username passed to devices.crud.php was not valid (File: " . $_SERVER['PHP_SELF'] . ")");
	}	

	/* See if category is added to any scheduled tasks and get correct column name if it is */
		$q = $db->q("SELECT id, catId FROM tasks WHERE status = '1'");

		$taskIdColumns = '';
		$taskValue = '';
		while ($taskRow = mysql_fetch_assoc($q)) {
			if (in_array($catId, unserialize($taskRow['catId']))) {
					$taskIdColumns .= ", taskId" . $taskRow['id'];
					$taskValue .= ", '1'";
			}
		}
	
		
	// add query variables
	$taskIdColumns = substr($taskIdColumns, 1); // format values for Query 
	$taskValue = substr($taskValue, 1); // format values for Query 
	// edit/update query variables
	
	$taskIdColumnsUpdateStr = '';
	$taskIdColumnsUpdateStrDefault = '';
	$taskIdColumnsUpdate = explode(", ", $taskIdColumns);
	foreach ($taskIdColumnsUpdate as $item) {
		$taskIdColumnsUpdateStr .= $item . " = '1',";
	}
	
    // validate deviceAccessMethodId field
    if (ctype_digit($_POST['deviceAccessMethodId'])) {
        $deviceAccessMethodId = mysql_real_escape_string($_POST['deviceAccessMethodId']);
    } else {
        $errors['deviceAccessMethodId'] = "deviceAccessMethodId input is incorrect";
        $log->Warn("Failure: deviceAccessMethodId input is incorrect (File: " . $_SERVER['PHP_SELF'] . ")");
    }    
	
	// validate connPort field
    if (ctype_digit($_POST['connPort'])) {
        $connPort = mysql_real_escape_string($_POST['connPort']);
    } else {
        $errors['connPort'] = "connPort input is incorrect";
        $log->Warn("Failure: connPort input is incorrect (File: " . $_SERVER['PHP_SELF'] . ")");
    }

    /* No validation on Custom_ Fields */
    
    // set the session id if any errors occur and redirect back to devices page with ?error set for JS on that page to keep form open 
    if (!empty($errors)) {
		if(isset($deviceName)){ $_SESSION['deviceName'] = $deviceName;}
		if(isset($deviceIpAddr)){ $_SESSION['deviceIpAddr'] = $deviceIpAddr;}
		if(isset($devicePrompt)){ $_SESSION['devicePrompt'] = $devicePrompt;}
		if(isset($vendorId)){ $_SESSION['vendorId'] = $vendorId;}
		if(isset($deviceModel)){ $_SESSION['deviceModel'] = $deviceModel;}
		if(isset($deviceUsername)){ $_SESSION['deviceUsername'] = $deviceUsername;}
		if(isset($devicePassword)){ $_SESSION['devicePassword'] = $devicePassword;}
		if(isset($devicePassConf)){ $_SESSION['devicePassConf'] = $devicePassConf;}
		if(isset($deviceEnableMode)){ $_SESSION['deviceEnableMode'] = $deviceEnableMode;}
		if(isset($deviceEnablePassword)){ $_SESSION['deviceEnablePassword'] = $deviceEnablePassword;}
		if(isset($catId)){ $_SESSION['catId'] = $catId;}
		if(isset($deviceAccessMethodId)){ $_SESSION['deviceAccessMethodId'] = $deviceAccessMethodId;}
		if(isset($connPort)){ $_SESSION['connPort'] = $connPort;}

        $_SESSION['errors'] = $errors;
        session_write_close();
        header("Location: " . $config_basedir . "devices.php?error");
        exit();
    } else {

        
        // Search POST for any key with partial string 'custom_' to get the names of the 
        // custom fields column names in DB
        $custom_results = array();
        foreach ($_POST as $k => $v) {
            if (strstr($k, 'custom_')) {
                // create new 'custom_results' array with key and values from the post matching 'custom'
                $custom_results[$k] = $v;
            }
        }
        
        /* http://php.net/manual/en/function.extract.php*/
        /* Extract Keys as Column Names for dynamic Query
         * and extract values as DB values for dynamic query */
        $dynamicValues = array();
        $dynamicTbls   = array();
		$customPropEditQueryStr = '';
        foreach ($custom_results as $key => $value) {
			$customPropEditQueryStr .= $key." = "."'".$value."', "; // create the edit query for any custom properties fields
            array_push($dynamicValues, $value);
            array_push($dynamicTbls, $key);
        }

        // Output above arrays to simple string variables for use in the query
        $dynamicValuesBlk = implode("', '", $dynamicValues);
        $dynamicTblsBlk   = implode(", ", $dynamicTbls);
        
        // create part of the UPDATE query for custom_ fields
        $customPropQueryStr = "";
        foreach ($custom_results as $k => $v) {
            $customPropQueryStr = $customPropQueryStr . $k . " = '" . $v . "', ";
        }
		
		// next if vars are not empty, add a comma to complete SQL statement
		// because if no custom props, or Tasks added errors will occur
		if(!empty($dynamicTblsBlk)){
			$dynamicTblsBlk = $dynamicTblsBlk .",";
			if(empty($dynamicValuesBlk)){
				$dynamicValuesBlk = "NULL".",";
			} else {
				if(!empty($dynamicValuesBlk)){
					$dynamicValuesBlk = "'".$dynamicValuesBlk ."',";
				}
			}
		}

		if(!empty($taskIdColumns)){$taskIdColumns = $taskIdColumns .",";}
		if(!empty($taskValue)){$taskValue = $taskValue .",";}
		
        /* Begin DB query. This will either be an Insert if $_POST ID is not set - or an edit/Update if ID is set in POST */
        if (empty($_POST['editid'])) {
            $q = "INSERT INTO nodes
		  (deviceName, 
		  deviceIpAddr,
		  devicePrompt,
		  deviceUsername,
		  devicePassword,
		  deviceEnableMode,
		  deviceEnablePassword,
		  deviceAccessMethodId,
		  connPort,
		  model,
		  vendorId,
		  nodeCatId,
		  nodeAddedBy,
		  " . $dynamicTblsBlk . "
		  " . $taskIdColumns . "
		  deviceDateAdded,
		  status
		  ) 
		  VALUES 
				('" . $deviceName . "', 
				'" . $deviceIpAddr . "', 
				'" . $devicePrompt . "', 
				'" . $deviceUsername . "', 
				'" . $devicePassword . "', 
				'" . $deviceEnableMode . "', 
				'" . $deviceEnablePassword . "', 
				'" . $deviceAccessMethodId . "', 
				'" . $connPort . "', 
				'" . $deviceModel . "', 
				'" . $vendorId . "',
				'" . $catId . "',
				'" . $username . "',
				" . $dynamicValuesBlk . "
				" . $taskValue . "
				CURDATE(),
				'1'
				)";
				// echo $q; die();
            if ($result = $db->q($q)) {
                $errors['Success'] = "Added new device " . $deviceName . " to Database";
                $log->Info("Success: Added new device, " . $deviceName . " to DB (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "devices.php");
            } else {
                $errors['Fail'] = "ERROR: " . mysql_error();
                $log->Fatal("Fatal: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "devices.php?error");
                exit();
            }
			
        } else { // if ID is  set in post when running a save from the form do an UPDATE
            $id = $_POST['editid'];
			
            $q  = "UPDATE nodes SET 
					deviceName = '" . $deviceName . "',
					deviceIpAddr = '" . $deviceIpAddr . "',
					devicePrompt = '" . $devicePrompt . "',
					deviceUsername = '" . $deviceUsername . "', 
					devicePassword = '" . $devicePassword . "', 
					deviceEnableMode = '" . $deviceEnableMode . "', 
					deviceEnablePassword = '" . $deviceEnablePassword . "', 
					deviceAccessMethodId = '" . $deviceAccessMethodId . "',
					connPort = '" . $connPort . "', 
					model = '" . $deviceModel . "', 
					vendorId = '" . $vendorId . "', 
					nodeCatId = '" . $catId . "', 
					$customPropEditQueryStr 
					deviceDateAdded = CURDATE()
					WHERE id = $id";

				if ($result = $db->q($q)) {
                $errors['Success'] = "Edit device " . $deviceName . " successful";
                $log->Info("Success: Edit device " . $deviceName . " in DB successful (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "devices.php");
            } else {
                $errors['Fail'] = "ERROR: " . mysql_error();
                $log->Fatal("Fatal: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
                $_SESSION['errors'] = $errors;
                session_write_close();
                header("Location: " . $config_basedir . "devices.php?error");
                exit();
            }
        }
        /* end check if 'id' is iset in input field */
    }
    /* end '!empty($errors)' check*/
}
/* end 'add' if*/


/* begin delete check */
elseif (isset($_POST['del'])) {

    /* the query*/
    $q = "UPDATE nodes SET status = 2 WHERE id = " . $_POST['id'] . ";";
    if ($result = $db->q($q)) {
        $log->Info("Success: Deleted Node ID = " . $_POST['id'] . " in DB (File: " . $_SERVER['PHP_SELF'] . ")");
        $response = json_encode(array(
            'success' => true
        ));
    } else {
        $log->Warn("Failure: Unable to delete node id:" . $_POST['id'] . " in DB (File: " . $_SERVER['PHP_SELF'] . ")");
        $response = json_encode(array(
            'failure' => true
        ));
    }
    echo $response;
} /* end 'delete' if*/ 

elseif (isset($_GET['getRow']) && isset($_GET['id'])) {

    if (ctype_digit($_GET['id'])) {
        $id = $_GET['id'];
    } else {
        $errors['Fail'] = "Fatal: id not of type int for getRow";
        $log->Fatal("Fatal: id not of type int for getRow - " . $_SERVER['PHP_SELF'] . ")");
        $_SESSION['errors'] = $errors;
        session_write_close();
        header("Location: " . $config_basedir . "devices.php?error");
        exit();
    }


    /* first get custom fieldnames  and impode to create part of final SQL query*/
    $q     = $db->q("SELECT customProperty
		FROM customProperties");
    $items = array();
    while ($row = mysql_fetch_assoc($q)) {
        $customProperty = $row['customProperty'];
        array_push($items, $customProperty);
        $customProp_string = implode(", ", $items).', ';
    }
    
    $q  = $db->q("SELECT 
			n.id,
			n.deviceName,
			n.deviceIpAddr,
			n.devicePrompt,
			v.id vendorId,
			n.model,
			n.deviceUsername,
			n.devicePassword,
			n.deviceEnableMode,
			n.deviceEnablePassword,
			n.termLength,
			a.id accessMeth,
			n. connPort,
			" . $customProp_string . "
			cat.id catId
		FROM nodes n
		LEFT OUTER JOIN vendors v ON n.vendorId = v.id
		LEFT OUTER JOIN categories c ON n.nodeCatId = c.id
		LEFT OUTER JOIN devicesaccessmethod a ON n.deviceAccessMethodId = a.id
		LEFT OUTER JOIN categories cat ON n.nodeCatId = cat.id
		WHERE n.status = 1
		AND n.id = '$id'");
    
    $items = array();
    while ($row = mysql_fetch_assoc($q)) {
        array_push($items, $row);
    }
    $result["rows"] = $items;
    echo json_encode($result);
}
/* end GetId */
?>