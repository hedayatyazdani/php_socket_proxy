<?php
/*
 * $Id: SocketServer.php,v 1.1 2009-04-19 04:00:05 root Exp $
 *
 * manage socket session, distuge connect
 *
 *
 */

require_once('SocketManager.php');
require_once('SocketConnection.php');
require_once('SocketProtocol.php');

class tSocketServer{

	public $Listena = array();
	public $ConnectionManager = null;
	public $Servera = array();
	public $ConnectionClass = 'tSocketConnection';
	public $MaxSockets = 10000;
	public $protocola = array();

	public function attachProtocol($Protocol){
		$Protocol->Server = $this;
		$this->protocola[$Protocol->name] = $Protocol;
		if ($this->ConnectionManager === null){

		}else{
			$this->ConnectionManager->attachProtocol($Protocol);
		}
	}

	public function detachProtocol($ProtocolName){
		$this->protocola[$ProtocolName]->Server = null;
		unset($this->protocola[$ProtocolName]);
		if ($this->ConnectionManager === null){}else{
			$this->ConnectionManager->detachProtocol($ProtocolName);
		}
	}

	function getConnectionInstance(){
		$cc = $this->ConnectionClass;
		$c = new $cc();
		$c->generateId();
		return $c;
	}

	function CheckAccept(){
		$r = $this->Listena;
		if ($num_changed = socket_select($r, $w = null, $e = null, 0)) {
			// SOCKET INCOMING CONNECTION
			//echo "Incoming?\n";
			foreach ($r as $ls){
				if ($sock = socket_accept($ls)){
					//echo "Accepted!\n";
					$Connection = $this->getConnectionInstance();
					$Connection->Socket = $sock;
					$Connection->SetOptions();
					$this->ConnectionManager->attach($Connection);
				}
			}
		}
	}

	function Step(){
		$this->CheckAccept();
		$this->ConnectionManager->CheckConnections();
	}

	function Bind($ip = '0.0.0.0', $port = '5222'){
		return $this->BindTcp($ip, $port);
	}

	private function error(){
		$errorcode = socket_last_error();
	    $errormsg = socket_strerror($errorcode);
	    return  " $errorcode : $errormsg ";
	}

	private function log($msg){
		//echo $msg."; \n";
		return true;
	}

	function BindTcp($ip = '0.0.0.0', $port = '5222'){
		if (!$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
			$this->log($this->error(). ' : 1' );
			return false;
		}
		socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1); // BEFORE BIND !!!!
		socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
		if (!(@socket_bind($sock, $ip, $port))){
			@socket_shutdown($sock, 2);
			@socket_close($sock);
			$this->log($this->error(). ' : 2');
			return false;
		}

		socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
		socket_set_nonblock($sock);
		if (!(@socket_listen($sock, $this->MaxSockets))){
			@socket_shutdown($sock, 2);
			@socket_close($sock);
			$this->log('to many clients');
			return false;
		}
		//$this->connected = true;
		$this->Listena[] = $sock;
		return true;
	}

	function Start(){
		$this->ConnectionManager = new tSocketManager();
		foreach ($this->protocola as $Protocol){
			$this->ConnectionManager->attachProtocol($Protocol);
		}
		while (true){
			$this->Step();
			if(count($this->ConnectionManager->connectiona) > 0){

			}
			usleep(100);
		}
	}

}
?>