<?php
// Processor and method for general settings options


class Process
{
    /* Class constructor */
    function Process()
    {
        /* User adjust debugging Option*/
        if (isset($_GET['debugOnOff'])) {
            $this->procDebugOnOff();
        } else if (isset($_GET['deviceToutVal'])) {
            $this->procDeviceTimeout();
        }  else if (isset($_GET['timeZoneChange'])) {
            $this->procTimeZoneChange();
        } else if (isset($_GET['getTimeZone'])) {
            $this->getTimeZone();
        } else if (isset($_GET['getDebugStatus'])) {
            $this->getDebugStatus();
        } else if (isset($_GET['phpLoggingOnOff'])) {
            $this->phpLoggingOnOff();
        } else if (isset($_GET['getPhpLoggingStatus'])) {
            $this->getPhpLoggingStatus();
        }
        
    } // end process function
    
    
    
    /**
     * procDebugOnOff - Chnage the debug value in the settings tbl to 1 or 0 to turn
     device output debugging to on or off respectivley
     */
    function procDebugOnOff()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if ($_GET['debugOnOff'] == '1') {
            $status = "On";
        } else {
            $status = "Off";
            
        }
        
        /* Update settings tbl with new option */
        // echo $option;
        $q = $db->q("UPDATE `settings` SET `commandDebug` = " . $_GET['debugOnOff']);
        
        /* Update successful */
        if ($q) {
            $response = "<font color='red'>Debugging status changed successfully to " . $status . "</font>";
        }
        /* Update failed */
        else {
            $response = "failed";
            $log->Warn("Failure: Could not update debugSetting in DB with MYSQL Error: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
        }
        
        echo json_encode($response);
        
    }    
	
	
	/**
     * phpLoggingOnOff - Chnage the php logging value in the settings tbl to 1 or 0 to turn
     php logging to on or off respectivley
     */
    function phpLoggingOnOff()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if ($_GET['phpLoggingOnOff'] == '1') {
            $status = "On";
        } else {
            $status = "Off";
        }
        
        /* Update settings tbl with new option */
        // echo $option;
        $q = $db->q("UPDATE `settings` SET `phpErrorLogging` = " . $_GET['phpLoggingOnOff']);
        
        /* Update successful */
        if ($q) {
            $response = "<font color='red'>PHP Error Logging status changed successfully to " . $status . "</font>";
        }
        /* Update failed */
        else {
            $response = "failed";
            $log->Warn("Failure: Could not update phpErrorLogging in DB with MYSQL Error: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
        }
        echo json_encode($response);
    }
    
    
    /**
     * procDeviceTimeout - Chnage the device connection timeout value
     */
    function procDeviceTimeout()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if (isset($_GET['deviceToutVal'])) {
            $timeout = $_GET['deviceToutVal'];
        }
        
        /* Update settings tbl with new option */
        // echo $option;
        $q = $db->q("UPDATE `settings` SET `deviceConnectionTimout` = " . $_GET['deviceToutVal']);
        
        /* Update successful */
        if ($q) {
            $response = "<br/><font color='green'>Device Connection Timeout changed sucessfully to " . $timeout . " Seconds</font>";
        }
        /* Update failed */
        else {
            $response = "failed";
            $log->Warn("Failure: Could not update deviceConnectionTimout in DB with MYSQL Error: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
        }
        
        echo json_encode($response);
        
    }    
	
/**
 * procTimeZoneChange - Chnage the device connection timeout value
 */
    function procTimeZoneChange()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if (isset($_GET['timeZoneChange'])) {
            $timeZone = $_GET['timeZoneChange'];
        }
        
        /* Update settings tbl with new option */
        // echo $timeZone;
        $q = $db->q("UPDATE `settings` SET `timeZone` = '" . $timeZone ."'");
        
        /* Update successful */
        if ($q) {
            $response = "<br/><font color='green'>Timezone changed sucessfully to " . $timeZone . "</font>";
        }
        /* Update failed */
        else {
            $response = "failed";
            $log->Warn("Failure: Could not update Timezone in DB with MYSQL Error: " . mysql_error() . " (File: " . $_SERVER['PHP_SELF'] . ")");
        }
        
        echo json_encode($response);
        
    }
    
    /**
     * procDeviceTimeout - Chnage the device connection timeout value
     */
    function getDebugStatus()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if (isset($_GET['getDebugStatus'])) {
            /* Update settings tbl with new option */
            // echo $option;
            $q      = $db->q("SELECT commandDebug FROM settings");
            $result = mysql_fetch_assoc($q);
            $status = $result['commandDebug'];
            /* Update successful */
            if ($status == '1') {
                $response = "<font color='red'><strong>Debugging Status: </strong>On<br/></font>";
            }
            /* Update failed */
            else if ($status == '0') {
                $response = "<font color='green'><strong>Debugging Status: </strong>Off</font>";
            }
            
            echo json_encode($response);
        }
    }  
    
    /**
     * getTimeZoneStatus - Chnage the device connection timeout value
     */
    function getTimeZone()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if (isset($_GET['getTimeZone'])) {
            /* Update settings tbl with new option */
            // echo $option;
            $q      = $db->q("SELECT timeZone FROM settings");
            $result = mysql_fetch_assoc($q);
            $timeZone = $result['timeZone'];
            /* Update successful */
            if (!empty($timeZone)) {
                $response = $timeZone;
            }
            /* Update failed */
            else if (empty($timeZone)) {
                $response = "";
            }
            
            echo json_encode($response);
        }
    }  
	
    /**
     * procDeviceTimeout - Chnage the device connection timeout value
     */
    function getPhpLoggingStatus()
    {
        session_start();
        require_once("../../../classes/db.class.php");
        require_once("../../../classes/ADLog.class.php");
        
        $db  = new db();
        $log = ADLog::getInstance();
        
        if (isset($_GET['getPhpLoggingStatus'])) {
            /* Update settings tbl with new option */
            // echo $option;
            $q      = $db->q("SELECT phpErrorLogging FROM settings WHERE ID = '1'");
            $result = mysql_fetch_assoc($q);
            $status = $result['phpErrorLogging'];
            /* Update successful */
            if ($status == '1') {
                $response = "<font color='red'><strong>PHP Error Logging Status: </strong>On<br/></font>";
            }
            /* Update failed */
            else if ($status == '0') {
                $response = "<font color='green'><strong>PHP Error Logging Status: </strong>Off</font>";
            }
            
            echo json_encode($response);
        }
    }  
	
	
}; //end Class

/* Initialize process */
$process = new Process;


?>