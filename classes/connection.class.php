<?php

 /**
  * rConfig Connection class
  * 
  * Class for managing connections via telnet and SSH to devices
  * This class is heavily modified from the 'Telnet for PHP' class from Ray Soucy <rps@soucy.org>
  * see http://www.soucy.org/ for original file
  *
  *
  * @package   rConfigConnectionClass
  * @originalauthor    Ray Soucy <rps@soucy.org>
  * @modifiedauthor    Stephen Stack <www.rconfig.com>
  * @version   1.0.0
  * @link      http://www.rconfig.com/
  * @license   http://www.rconfig.com/  
  */
  
class Connection 
{

    private $_hostname;
    private $_username;
    private $_password;
    private $_connection;
    private $_data;
    private $_timeout;
    private $_prompt;

	const TELNET_OK = TRUE;        
	
    /**
     * Class Constructor
     * @param  string  $hostname Hostname or IP address of the device
	 * @param  string  $username Username used to connect
     * @param  string  $password Password used to connect
     * @param  string  $enableModeOn Enable Mode On/Off as set in database
     * @param  string  $enablePassword Enable Mode password
     * @param  integer $timeout  Connetion timeout (seconds)
     * @return object  Telnet object
     */
    public function __construct($hostname, $username = "", $password, $enableMode, $enableModePassword, $timeout = 60) 
    {
        $this->_hostname = $hostname;
        $this->_username = $username;
        $this->_password = $password;
        $this->_timeout = $timeout;
        $this->_enableMode = $enableMode;
        $this->_enableModePassword = $enableModePassword;
		// below are headers that telnet requires for proper session setup - google 'fsockopen php telnet' for more info
		// and per here http://www.phpfreaks.com/forums/index.php?topic=201740.0
		// Not currenty used in this version of the class
        $this->_header1 = chr(0xFF).chr(0xFB).chr(0x1F).chr(0xFF).chr(0xFB).chr(0x20).chr(0xFF).chr(0xFB).chr(0x18).chr(0xFF).chr(0xFB).chr(0x27).chr(0xFF).chr(0xFD).chr(0x01).chr(0xFF).chr(0xFB).chr(0x03).chr(0xFF).chr(0xFD).chr(0x03).chr(0xFF).chr(0xFC).chr(0x23).chr(0xFF).chr(0xFC).chr(0x24).chr(0xFF).chr(0xFA).chr(0x1F).chr(0x00).chr(0x50).chr(0x00).chr(0x18).chr(0xFF).chr(0xF0).chr(0xFF).chr(0xFA).chr(0x20).chr(0x00).chr(0x33).chr(0x38).chr(0x34).chr(0x30).chr(0x30).chr(0x2C).chr(0x33).chr(0x38).chr(0x34).chr(0x30).chr(0x30).chr(0xFF).chr(0xF0).chr(0xFF).chr(0xFA).chr(0x27).chr(0x00).chr(0xFF).chr(0xF0).chr(0xFF).chr(0xFA).chr(0x18).chr(0x00).chr(0x58).chr(0x54).chr(0x45).chr(0x52).chr(0x4D).chr(0xFF).chr(0xF0);
        $this->_header2 = chr(0xFF).chr(0xFC).chr(0x01).chr(0xFF).chr(0xFC).chr(0x22).chr(0xFF).chr(0xFE).chr(0x05).chr(0xFF).chr(0xFC).chr(0x21);
    }	
	
	/**
	 * Establish a connection to an IOS based device check for enable mode also and enter enable cmds if needed
	 */
	public function connectTelnet() 
	{
			
		$log = ADLog::getInstance();

		$this->_connection = fsockopen($this->_hostname, 23, $errno, $errstr, $this->_timeout);
			
		if ($this->_connection === false) {
		
			$log->Conn("Failure: Unable to connect to ".$this->_hostname." - fsockopen Error:$errstr ($errno) (File: ".$_SERVER['PHP_SELF'].")");
			return false; 
			
		} 
		stream_set_timeout($this->_connection, $this->_timeout);
		
		$this->_readTo(':');
		if (substr($this->_data, -9) == 'Username:') {
			$this->_send($this->_username);
			$this->_readTo(':');
		} 
		$this->_send($this->_password);

		if ($this->_enableMode === 'on'){

			$this->_prompt = '>';
			$this->_readTo($this->_prompt);
			
			if (strpos($this->_data, $this->_prompt) === false) {
				fclose($this->_connection);            
				
				echo "Error: Authentication Failed for $this->_hostname\n";
				$log->Conn("Error: Authentication Failed for $this->_hostname (File: ".$_SERVER['PHP_SELF'].")");
				return false; 

			} else {
				
				$this->_send('enable');
				$this->_readTo(':');
				$this->_send($this->_enableModePassword);
				$this->_prompt = '#';
				$this->_readTo($this->_prompt);
				if (strpos($this->_data, $this->_prompt) == false) {                
					echo "Error: Authentication Failed for enable mode for $this->_hostname\n";
					$log->Conn("Error: Authentication Failed for enable mode for  enable mode for or $this->_hostname (File: ".$_SERVER['PHP_SELF'].")");
					return false; 
				} 
				// set term pager 0 for ASAs to avoid paging issues _readTo does not work too well for long command output
				// will not take for IOS, but not an issue
				$this->termLen('0'); 
				sleep(1);
			}

		} else {
			$this->_prompt = '#'; 
			$this->_readTo($this->_prompt);
			if (strpos($this->_data, $this->_prompt) === false) {
				fclose($this->_connection);            
				
				echo "Error: Authentication Failed for $this->_hostname\n";
				$log->Conn("Error: Authentication Failed for $this->_hostname (File: ".$_SERVER['PHP_SELF'].")");
				return false; 

			} 
		}
	} 

	/**
	 * Establish a connection to an IOS based device on SSHv2 check for enable mode also and enter enable cmds if needed
	 */	
	public function connectSSH($command, $prompt) 
	{
			
		$log = ADLog::getInstance();
		
		if(!$ssh = new Net_SSH2($this->_hostname, 22, $this->_timeout)){
			$log->Conn("Failure: Unable to connect to ".$this->_hostname." - fsockopen Error:$errstr ($errno) (File: ".$_SERVER['PHP_SELF'].")");
			return false; 
		}
		
		if (!$ssh->login($this->_username, $this->_password)) {
			echo "Error: Authentication Failed for $this->_hostname\n";
			$log->Conn("Error: Authentication Failed for $this->_hostname (File: ".$_SERVER['PHP_SELF'].")");
				return false; 
		}
		$output = '';
		if($this->_enableMode === 'on'){
			// $ssh->write("\n"); // 1st linebreak after above prompt check		
			echo $ssh->read('/.*>/', NET_SSH2_READ_REGEX); // read out to '>'
			$ssh->write("enable\n");
			echo $ssh->read('/.*:/', NET_SSH2_READ_REGEX);
			$ssh->write($this->_enableModePassword."\n");
			echo $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
			$ssh->write("terminal pager 0\n");
			echo $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
			$ssh->write("terminal length 0\n");
			echo $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
			$ssh->write($command."\n");
			$ssh->write("\n"); // to line break after command output
			$output = $ssh->read('/$prompt/', NET_SSH2_READ_REGEX);
		} else {
			// $ssh->write("\n"); // 1st linebreak after above prompt check		
			echo $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
			$ssh->write("terminal pager 0\n"); //set in case device is ASA
			echo $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
			$ssh->write("terminal length 0\n"); //set in case device is ASA
			echo $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
			$ssh->write($command."\n");
			$ssh->write("\n"); // to line break after command output
			$output =  $ssh->read('/'.$prompt.'/', NET_SSH2_READ_REGEX);
		}
		$ssh->disconnect();
		$result = array();
        $this->_data = explode("\r\n", $output);
        array_shift($this->_data);
        array_pop($this->_data);
        if (count($this->_data) > 0) {
            foreach ($this->_data as $line) {
                $line = explode("\r\n", $line); // changed from 3xSpaces to /r/n as a delimiter for explode
                array_push($result, $line[0]);
            } // foreach
        } 
        $this->_data = $result;
        return $this->_data;
	}
	
	/**
     * Telnet Show Command Input
     * @param  string        $cmd The comamnd to execute
     * @param  string        $prompt The device exec mode prompt
     * @return array|boolean On success returns an array, false on failure.
     */
    public function showCmdTelnet($cmd, $prompt, $cliDebugOutput = false) 
    {

        $this->_send($cmd);
		$this->_prompt = $prompt;
        $this->_readTo($this->_prompt, $cliDebugOutput);
		
        $result = array();
        $this->_data = explode("\r\n", $this->_data);
        array_shift($this->_data);
        array_pop($this->_data);
        if (count($this->_data) > 0) {
            foreach ($this->_data as $line) {
                $line = explode("\r\n", $line); 
                array_push($result, $line[0]);
            } // foreach
        } 
        $this->_data = $result;
        return $this->_data;    
	} 
	
    /**
     * Close an active connection for a FWSM/ASA and set term len value on the way out of the device
     */
    public function close($termLen) 
    {
		sleep(1);
		$this->termLen($termLen); // set term pager $termLen for ASAs
        $this->_send('quit');
        fclose($this->_connection);
    } // close
	
    /**
     * Issue a command to the device
     */
    private function _send($command) 
    {
        fputs($this->_connection, $command . "\r\n");
    } // _send

	
	/**
	* Clears internal command buffer
	* 
	* @return void
	*/
    private function _clearBuffer() {
		$this->_data = '';
	}
	
	
    /**
     * Read from socket until $prompt
     * @param string $prompt Single character or string
     */
    private function _readTo($prompt, $cliDebugOutput = false) 
    {
	
		if (!$this->_connection) {
			throw new Exception("Telnet connection closed");            
		}
		// clear the buffer 
		$this->_clearBuffer();
		 
        while (($c = fgetc($this->_connection)) !== false) {

            $this->_data .= $c;
		if ($cliDebugOutput == true)	{
			echo $c; 
		}
			// we've encountered the prompt. send TELNET_OK
			if ((substr($this->_data, strlen($this->_data) - strlen($prompt))) == $prompt){
				return self::TELNET_OK;
				// break;
			}
            // if ($c == $char[0]) break; // old code
            if ($c == '-') {
                // Continue at --More-- prompt
                if (substr($this->_data, -8) == '--More--') fputs($this->_connection, ' ');
            } 
			
			// Remove --More-- and backspace and whitespace
			$this->_data = str_replace('--More--', "", $this->_data);
			$this->_data = str_replace(chr(8), "", $this->_data);
			$this->_data = str_replace('     ', "", $this->_data);
			// Set $_data as false if previous command failed.
			if (strpos($this->_data, '% Invalid input detected') !== false) {
				$this->_data = false;
			}
        } // while
    }
	
	
	/*
	* send termLen value to console		
	*/
    public function termLen($value) 
    {
	$result = false;
	 // if ($this->_prompt == '#') {
        $this->_send('terminal pager '.$value);
		// }
			if ($this->_data !== false) {
                $this->_prompt = '#';
                $result = true;
            } 
            $this->_readTo($this->_prompt);
            return $result;
    } // _send	
	
	
} // Telnet Class
// trailing PHP tag omitted to prevent accidental whitespace