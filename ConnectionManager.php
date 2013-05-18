<?php
/*
 * $Id: ConnectionManager.php,v 1.2 2009-04-19 13:42:20 root Exp $
 */

require_once('ServerConnection.php');

class tConnectionManager{
	// Observer pattern
	public $connectiona = array();
	public $protocola = array();
	
	public function attach(tServerConnection $Connection){
		foreach ($this->protocola as $Protocol){
			$Connection->attachProtocol($Protocol);
		}
		$Connection->beforeAttach($this);
		$this->connectiona[$Connection->id] = $Connection;
		$this->onAttach($Connection);
		$Connection->afterAttach($this);
		$Connection->onConnect();
	}
	
	function onAttach($Connection){}
	
	public function detach(tServerConnection $Connection){
		//echo "detach!\r\n";
		if ($Connection->Socket === null) {}else{
			$Connection->onDisconnect();
		}
		$Connection->beforeDetach($this);
		unset($this->connectiona[$Connection->id]);
		$this->onDetach($Connection);
		$Connection->afterDetach($this);
		unset($Connection);
	}
	
	function onDetach($Connection){}
	
	public function attachProtocol($Protocol){
		$Protocol->ConnectionManager = $this;
		$this->protocola[$Protocol->name] = $Protocol;
		foreach ($this->connectiona as $Connection){
			$Connection->attachProtocol($Protocol);
		}
	}
	
	public function detachProtocol($ProtocolName){
		$this->protocola[$ProtocolName]->ConnectionManager = null;
		unset($this->protocola[$ProtocolName]);
		foreach ($this->connectiona as $Connection){
			$Connection->detachProtocol($Protocol);
		}
	}

}
?>