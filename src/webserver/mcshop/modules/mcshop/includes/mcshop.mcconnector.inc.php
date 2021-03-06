<?php
	class MCConnector
	{
		var $host;
		var $port;
		var $password;
		var $stream;

		public function __construct() 
		{
			$this->host = variable_get_value('mcshop_server_host');
			$this->port = variable_get_value('mcshop_server_port');
			$this->password = variable_get_value('mcshop_server_pass');
		}

		/**
		* Connects to a MCShopSystem server.
		* @param string $password
		* @return bool $state
		*         true  success
		*         false  fail
		*/
		public function connect()
		{
			$this->stream = @fsockopen($this->host, $this->port, $errno, $errstr, 2);
			if(!$this->stream)
				return FALSE;
			$this->writeRawByte(21);
			$this->writeString($this->password);
				
			if($this->readRawInt() == 1)
			{
				variable_set('mcshop_mcconnetor', $this->stream);
				return TRUE;
			}
			else
				return FALSE;
		}

		/**
		* Sends a disconnect signal to the currently connected MCShopSystem server.
		*/
		public function disconnect()
		{
			if($this->stream)
			{
				$this->writeRawByte(20);
			    fclose($this->stream);
			}
		}

		//NETWORK IO
		private function writeRawInt( $i )
		{
			fwrite( $this->stream, pack( "N", $i ), 4 );
		}
	
		private function writeRawDouble( $d )
		{
			fwrite( $this->stream, strrev( pack( "d", $d ) ) );
		}

		private function writeRawByte( $b )
		{
			fwrite( $this->stream, strrev( pack( "C", $b ) ) );
		}

		private function writeChar( $char )
		{
			$v = ord($char);
			$this->writeRawByte((0xff & ($v >> 8)));
			$this->writeRawByte((0xff & $v));
		}

		private function writeChars( $string )
		{
			$array = str_split($string);
			foreach($array as &$cur)
			{
				$v = ord($cur);
				$this->writeRawByte((0xff & ($v >> 8)));
				$this->writeRawByte((0xff & $v));
			}
		}

		private function writeString( $string )
		{
			$array = str_split($string);
			$this->writeRawInt(count($array));
			foreach($array as &$cur)
			{
				$v = ord($cur);
				$this->writeRawByte((0xff & ($v >> 8)));
				$this->writeRawByte((0xff & $v));
			}
		}

		private function readRawInt()
		{
			$a = $this->readRawByte();
			$b = $this->readRawByte();
			$c = $this->readRawByte();
			$d = $this->readRawByte();
			$i = ((($a & 0xff) << 24) | (($b & 0xff) << 16) | (($c & 0xff) << 8) | ($d & 0xff));
			return $i;
		}
		private function readRawDouble()
		{
			$up = unpack( "di", strrev( fread( $this->stream, 8 ) ) );
			$d = $up["i"];
			return $d;
		}
		private function readRawByte()
		{
			$up = unpack( "Ci", fread( $this->stream, 1 ) );
			$b = $up["i"];
			return $b;
		}
		private function readChar()
		{
			$byte1 = $this->readRawByte();
			$byte2 = $this->readRawByte();
			$charValue = chr(utf8_decode((($byte1 << 8) | ($byte2 & 0xff))));
			return $charValue;
		}
		private function readChars($len)
		{
			$buf = "";
			for($i = 0;$i<$len;$i++)
			{
				$byte1 = $this->readRawByte();
				$byte2 = $this->readRawByte();
				$buf = $buf.chr(utf8_decode((($byte1 << 8) | ($byte2 & 0xff))));
			}
			return $buf;
		}

		

		/*------------------MCShopSystem SPECIFIC (8.7)-------------------
			----Functions----
			0x1 registerAccount($accountName, $accountPass)
			0x2 checkPlayerAccount($accountName, $accountPass)
			0x3 doCommand($cmmd, $args)
			0x4 getPlayerStatus($accountName)
			0x5 getServerStatus()
			0x6 doBroadcast($message)
		------------------------------------------------------------------*/
		
	
		public function registerAccount($accountName, $accountPass) //return int  if valid return 1 if wrong return 0 if has exist 2
		{
			$this->writeRawByte(1);
			$this->writeString($accountName); //length of strings are sent in writeRawString
			$this->writeString($accountPass);
			
			return $this->readRawInt();
		}
		
		
		public function checkPlayerAccount($accountName, $accountPass) //return int  if valid return 1 if wrong return 0 if not exist 2
		{
			$this->writeRawByte(2);
			$this->writeString($accountName); //length of strings are sent in writeRawString
			$this->writeString($accountPass);
			
			return $this->readRawInt();
		}
		
		
		public function doCommand($cmmd, $args) //return true if command was found
		{
		    foreach ($args as $key => $value)
		      $cmmd .= ' $'.$key.'('.$value.')';
			$this->writeRawByte(3);
			$this->writeString($cmmd);
			
			if($this->readRawInt() == 1)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		
		
		public function getPlayerStatus($accountName) //return string message
		{
			$this->writeRawByte(4);
			$this->writeString($accountName); //length of strings are sent in writeRawString
			$statusMessageLen = $this->readRawInt(); //need to receive length of message
			$statusMessage = $this->readChars($statusMessageLen);
			
			return $statusMessage;
		}

		
		public function getServerStatus() //return string message
		{
			$this->writeRawByte(5);
			$statusMessageLen = $this->readRawInt(); //need to receive length of message
			$statusMessage = $this->readChars($statusMessageLen);
			
			return $statusMessage;
		}
		

		/**
		 * Prints a message to all players and the console.
		 *
		 * @param string $message Message to be shown.
		 * @return bool is it success.
		 */
		public function doBroadcast($message)
		{
			$this->writeRawByte(6);
			$this->writeString($message);

			if($this->readRawInt() == 1)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		
	}
?>