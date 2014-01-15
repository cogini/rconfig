<?php
/*
 * Functions file containing generic single methods/ Functions for rConfig. If an function is called in a script for ease it should
 * reference this file
*/



/**
 * phpErrorReporting to check if PHP Error reporting is set to on in dbase_add_record
 * if it is start logging errors to DB file. Function is added to head.inc.php on each and every page
 *
 */
function phpErrorReporting(){

require_once("../classes/db.class.php");

$db = new db();
// check and set timeZone to avoid PHP errors
$q      = $db->q("SELECT timeZone FROM settings");
$result = mysql_fetch_assoc($q);
$timeZone = $result['timeZone'];
date_default_timezone_set($timeZone);

$result = $db->q("SELECT phpErrorLogging, phpErrorLoggingLocation FROM settings WHERE id = '1'");
$phpErrorSettings = mysql_fetch_assoc($result);
$phpErrorLevel = $phpErrorSettings['phpErrorLogging'];
$phpErrorLocation = $phpErrorSettings['phpErrorLoggingLocation'];
$phpErrorDate = date('Ymd');
$phpErrorFile =  $phpErrorLocation . 'error_log'.$phpErrorDate.'.txt';

if($phpErrorLevel === '1'){

	if(!file_exists($phpErrorFile)){
	exec("touch " . $phpErrorFile);
	chmod($phpErrorFile,0644);	
	$handle = fopen($phpErrorFile, 'w');
			if(!$handle){
				$log->Fatal("Cannot open file  Func: createFile():  ".$this->fullReportPath."(File: ".$_SERVER['PHP_SELF'].")"); 	
			}
	}
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log',$phpErrorFile);
	error_reporting(E_ALL);
} else {
		ini_set('display_errors', 0);
	}
}


/**
 * urlsearch function is to aid in the search for relevant portion of page name
 * to add the selected="true" attr to the menu to keep open the relevant accordion 
 * page
 */
function urlsearch($string){
include("includes/config.inc.php");
    if(strstr($config_page,$string)){
    echo 'selected="true"';
    }else{
    echo '';
    }
} //urlsearch function


/**
 * regexpMatch function used to compare inputted string against anither string 
 * i.e. from a config file, to ensure a match. used in complianceScript.php
 */
function regexpMatch($string, $line){
	if (preg_match($string, $line)) {
		return true;
	} else {
		return false;
	}
} //urlsearch function

/**
 * Delete the $value Character(s) of a String with PHP
 * 
 * Example;
 * $newString = deleteChar( $oldString,7);
 * 
 */
function deleteChar($string, $value) {
	return substr( $string, $value);
}


/*
 * Extract leaf nodes of multi-dimensional array in PHP - as used in cmd_cat_update.php
 */
function getLeafs($element) {
    $leafs = array();
    foreach ($element as $e) {
        if (is_array($e)) {
            $leafs = array_merge($leafs, getLeafs($e));
        } else {
            $leafs[] = $e;
        }
    }
    return $leafs;
}

/**
 * @desc Outputs inserted value to B, KiB, GiB etc.. based on size
 * @param inout byes in intval
 * @return str 
 */
function _format_bytes($a_bytes) {
    if ($a_bytes < 1024) {
        return $a_bytes .'B';
    } elseif ($a_bytes < 1048576) {
        return round($a_bytes / 1024, 2) .' KiB';
    } elseif ($a_bytes < 1073741824) {
        return round($a_bytes / 1048576, 2) . ' MiB';
    } elseif ($a_bytes < 1099511627776) {
        return round($a_bytes / 1073741824, 2) . ' GiB';
    } elseif ($a_bytes < 1125899906842624) {
        return round($a_bytes / 1099511627776, 2) .' TiB';
    } elseif ($a_bytes < 1152921504606846976) {
        return round($a_bytes / 1125899906842624, 2) .'PiB';
    } elseif ($a_bytes < 1180591620717411303424) {
        return round($a_bytes / 1152921504606846976, 2) .'EiB';
    } elseif ($a_bytes < 1208925819614629174706176) {
        return round($a_bytes / 1180591620717411303424, 2) .'ZiB';
    } else {
        return round($a_bytes / 1208925819614629174706176, 2) .'YiB';
    }
}


/**
 * @desc read filename contents and return lines line by line
 * @param input filename
 * @return array of lines from config file
 */
function fileRead($filename) {
	$lines = file($filename);
		if (!empty($lines)){
		foreach ($lines as $line_num => $line) 
			{ 
			echo "<font color=red>{$line_num}: </font>" . $line . "<br />\n"; 
			//If you are reading HTML code use this line instead
			//print "<font color=red>Line #{$line_num}</font> : " . htmlspecialchars($line) . "<br />\n";
			}
	 } else {
			echo "<font color=red>0 : </font><strong>This file is empty</strong><br />\n";
	 }
}

/*
* @desc get server memory free
*
*/
function get_memory_free() {
  foreach(file('/proc/meminfo') as $ri)
    $m[strtok($ri, ':')] = strtok('');
  return 100 - round(($m['MemFree'] + $m['Buffers'] + $m['Cached']) / $m['MemTotal'] * 100);
}

/*
* @desc get server memory total
*
*/
function get_memory_total() {
  foreach(file('/proc/meminfo') as $ri)
    $m[strtok($ri, ':')] = strtok('');
  return round(substr($m['MemTotal'], 0, -3)*1000); // by 1000 to xlate from KB from proc file to B
}

/*
* @desc get CPU Type
*
*/
function get_cpu_type() {
  foreach(file('/proc/cpuinfo') as $ri)
    $m[trim(str_replace(" ","",strtok($ri, ':')))] = strtok('');
  return $m['modelname'];
}

/**
 * @desc Ping given IP address and return result
 * @url http://php.net/manual/en/function.socket-create.php
 * @param input IP of host, and timeout count
 * @return output
 */
function getHostStatus($host, $port)
{

	$status = array("Unavailable", "Online");
	$fp = @fsockopen($host, $port, $errno, $errstr, 2);
	if ($fp) {
		return "<font color=\"green\">".$status[1]."</font>";
	} else { 
		return "<font color=\"red\">".$status[0]."</font>";
	  }
}


// array_search with partial matches and optional search by key
function array_find($needle, $haystack, $search_keys = false) {
	if(!is_array($haystack)) return false;
	foreach($haystack as $key=>$value) {
		$what = ($search_keys) ? $key : $value;
		if(strpos($what, $needle)!==false) return $key;
	}
	return false;
}

function getTime()
    {
    $a = explode (' ',microtime());
    return(double) $a[0] + $a[1];
    }
	
// from here http://stackoverflow.com/questions/10895343/php-count-total-files-in-directory-and-subdirectory-function
function scan_dir($path){
    $ite=new RecursiveDirectoryIterator($path);

    $bytestotal=0;
    $nbfiles=0;
    foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
        $filesize=$cur->getSize();
        $bytestotal+=$filesize;
        $nbfiles++;
        $files[] = $filename;
    }
		// check if dir is empty after dir iteration and if so, create emtpy var to prevent php notice
		if(empty($files)){
			$files ="";
		}
    $bytestotal=number_format($bytestotal);

    return array('total_files'=>$nbfiles,'total_size'=>$bytestotal,'files'=>$files);
}


/** backup the db OR just a table 
 * from here http://davidwalsh.name/backup-mysql-database-php
 */
function sqlBackup($host,$user,$pass,$name, $backupPath, $tables = '*')
{

  $link = mysql_connect($host,$user,$pass);
	if (!$link = mysql_connect($host, $user, $pass)) {
		echo 'Could not connect to mysql';
		exit;
	}
  mysql_select_db($name,$link);
  
  // check and set timeZone to avoid PHP errors
	$q      = mysql_query("SELECT timeZone FROM settings", $link);
	$result = mysql_fetch_assoc($q);
	$timeZone = $result['timeZone'];
	date_default_timezone_set($timeZone);

  //get all of the tables
  if($tables == '*')
  {
    $tables = array();
    $result = mysql_query('SHOW TABLES');
    while($row = mysql_fetch_row($result))
    {
      $tables[] = $row[0];
    }
  }
  else
  {
    $tables = is_array($tables) ? $tables : explode(',',$tables);
  }

  $return = '';
  
  //cycle through
  foreach($tables as $table)
  {
    $result = mysql_query('SELECT * FROM '.$table);
    $num_fields = mysql_num_fields($result);
    
    $return.= 'DROP TABLE '.$table.';';
    $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
    $return.= "\n\n".$row2[1].";\n\n";
    
    for ($i = 0; $i < $num_fields; $i++) 
    {
      while($row = mysql_fetch_row($result))
      {
        $return.= 'INSERT INTO '.$table.' VALUES(';
        for($j=0; $j<$num_fields; $j++) 
	   {
          $row[$j] = addslashes($row[$j]);
          $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
          if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
          if ($j<($num_fields-1)) { $return.= ','; }
        }
        $return.= ");\n";
      }
    }
    $return.="\n\n\n";
  }
  //save file
  $today = date("Ymd");
  $filenamePath = $backupPath.'rconfig-db-backup-'.$today.'.sql';
  $handle = fopen($filenamePath, 'w+');
  fwrite($handle,$return);
  fclose($handle);
  
  return $filenamePath;
}


function fileBackup($file, $backupFile)
{

	$zip = new ZipArchive();
	$zip->open($backupFile, ZipArchive::CREATE);
	
	if (!is_file($file)) {
	throw new Exception('SQL file ' . $file . ' does not exist');
	}
	$zip->addFile($file);
	
	echo $zip->close();
}


function folderBackup($dir,$backupFile)
{
	$zip = new ZipArchive();
	$zip->open($backupFile, ZipArchive::CREATE);

	$dirName = $dir;

	if (!is_dir($dirName)) {
	throw new Exception('Directory ' . $dirName . ' does not exist');
	}

	$dirName = realpath($dirName);
	if (substr($dirName, -1) != '/') {
	$dirName.= '/';
	}

	/*
	* NOTE BY danbrown AT php DOT net: A good method of making
	* portable code in this case would be usage of the PHP constant
	* DIRECTORY_SEPARATOR in place of the '/' (forward slash) above.
	*/

	$dirStack = array($dirName);
	//Find the index where the last dir starts
	$cutFrom = strrpos(substr($dirName, 0, -1), '/')+1;

	while (!empty($dirStack)) {
		$currentDir = array_pop($dirStack);
		$filesToAdd = array();
	
		$dir = dir($currentDir);
		while (false !== ($node = $dir->read())) {
			if (($node == '..') || ($node == '.')) {
				continue;
			}
			if (is_dir($currentDir . $node)) {
				array_push($dirStack, $currentDir . $node . '/');
			}
			if (is_file($currentDir . $node)) {
				$filesToAdd[] = $node;
			}
		}

		$localDir = substr($currentDir, $cutFrom);
		$zip->addEmptyDir($localDir);
		
		foreach ($filesToAdd as $file) {
			$zip->addFile($currentDir . $file, $localDir . $file);
		}
	}

	$zip->close();
}


/* creates a compressed zip file 
*  From here http://davidwalsh.name/create-zip-php
*/
function createZip($files = array(),$destination = '',$overwrite = false) {
  //if the zip file already exists and overwrite is false, return false
  if(file_exists($destination) && !$overwrite) { return false; }
  //vars
  $valid_files = array();
  //if files were passed in...
  if(is_array($files)) {
    //cycle through each file
    foreach($files as $file) {
      //make sure the file exists
      if(file_exists($file)) {
        $valid_files[] = $file;
      }
    }
  }
  //if we have good files...
  if(count($valid_files)) {
    //create the archive
    $zip = new ZipArchive();
    if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
      return false;
    }
    //add the files
    foreach($valid_files as $file) {
	$new_filename = substr($file,strrpos($file,'/') + 1); // strip filePath
      $zip->addFile($file,$new_filename);
    }
    //debug
    // echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
    
    //close the zip -- done!
    $zip->close();
    
    //check to make sure the file exists
    return file_exists($destination);
  }
  else
  {
    return false;
  }
}

// functions for the configoverview.php output
function cntCategories() {
require_once("../classes/db.class.php");

$db = new db();

   $q = "SELECT * FROM categories WHERE status = 1";
   $result = $db->q($q);
   $num_rows = mysql_numrows($result);
	
	return $num_rows;
}


function cntDevices() {
require_once("../classes/db.class.php");

$db = new db();

   $q = "SELECT * FROM nodes WHERE status = 1";
   $result = $db->q($q);
   $num_rows = mysql_numrows($result);
	
	return $num_rows;
}

// recursivley remove items from a directory
function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}


/**
 * @desc Check if sting contains whitespace
 * @param str
 * @return bool 
 */
function chkWhiteSpaceInStr($string)
{
	return preg_match("/\\s/", $string);
}


?>
