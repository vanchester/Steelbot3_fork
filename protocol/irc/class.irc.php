<?php

class irc
{
	private $fp, $readbuffer, $line, $mcommands;
	public $nick, $ident, $realname, $host, $port;
	
	function irc($nick, $ident, $realname, $host, $port)
	{
		$this->nick 		= $nick;
		$this->ident 		= $ident;
		$this->realname		= $realname;
		$this->host 		= $host;
		$this->port 		= $port;

		$this->fp = fsockopen($host, $port, $erno, $errstr, 30);
		if(!$this->fp) die("Could not connect\r\n");
		echo "NICK ".$nick."\r\n";
		fwrite($this->fp, "NICK ".$nick."\r\n");
		echo "USER ".$ident." ".$host." bla :".$realname."\r\n";
		fwrite($this->fp, "USER ".$ident." ".$host." bla :".$realname."\r\n");

		$this->flush();

	}
	
	function __destruct() {
		$this->disconnect();
	}
	
	function readMessage() {
		$this->line = fgets($this->fp, 256); // wait for a message

		echo $this->line."\n";
		
		if($this->is_ping($this->line)) {
			$this->pong();
		}
		
		if(strstr($this->line,"PRIVMSG"))
		{
			$msg = $this->msgToArray($this->line);
			return $msg;
		}
	}
	
	function loop()
	{
		// now for program loop //
		while (!feof($this->fp)) 
		{
			$this->readMessage();
			
			$this->line = "";
			$this->flush();
			$this->wait(); // time to next cycle
		}

	}
	
	// outgoing //
	function out($msg) {
		if(@empty($msg)) {
			return false;
		}
		
		if(!strstr($msg, "\n")) {
			$msg .= "\n";
		}

		fwrite($this->fp, $msg);
		
		return true;
	}
	
	function setNick($nick) {
		$this->out("NICK ".$nick."\r\n"); $this->nick = $nick;
	}
	
	function joinChan($channel) {
		$this->out("JOIN :".$channel."\r\n");
	}
	
	function quitChan($channel) {
		$this->out("PART :".$channel."\r\n"); 
	}

	function listChans() {
		$this->out("LIST\r\n");
	}
	
	function getTopic($channel) {
		$this->out("TOPIC ".$channel."\r\n");
	}
	
	function msg($msg, $target) {
		$data = explode("\n", $msg);
		$msg = '';
		foreach($data as $txt) {
			$txt = trim($txt);
			if (empty($txt)) {
				continue;
			}
			$msg .= "PRIVMSG $target :$txt\r\n";
		}
		$this->out($msg);
	}
	
	function msgChan($channel, $msg) {
		$this->msg($channel, $msg);
	}
	
	function msgUser($user, $msg) {
		$this->msg($user, $msg);
	}
	
	function pong() {
		$this->out("PONG :".$this->host."\r\n");
	}
	
	function disconnect($msg="") {
		if (is_resource($this->fp)) {
			$this->out("QUIT :$msg\r\n");
			fclose($this->fp);
		}
	}
	
	function state() {
		if (is_resource($this->fp)) {
			return true;
		}
		return false;
	}
	
	// incoming processing //
	function is_ping($line) {
		if(strstr($line, 'PING')) {
			return true;
		}
	}
	
	function is_msg($line) {
		if(strstr($line, 'PRIVMSG')) {
			return true;
		}
	}

	function msgToArray($line) { // array('from, 'channel', 'message');
		$array = explode(":",$line);
				
		$from = explode("!",$array[1]);
		$from = trim($from[0]);
		
		$fromchan = explode("#",$array[1]);
		$fromchan = "#".trim($fromchan[1]);
		
		$string = $array[2];
		$string = trim($string);
		
		$msg = array('from'=>$from, 'channel'=>$fromchan, 'message'=>$string);
		
		return $msg;
	}
	
	// system
	function flush() {
		@ob_flush; @flush();
	}
	
	function wait() {
		usleep(250000);
	}
}

?>