<?php
/*
 * $Id: SocketManager.php,v 1.4 2009-04-19 13:42:20 root Exp $
 */
require_once('ConnectionManager.php');

class tSocketManager extends tConnectionManager{

	public $Socketa = array();
	
	function onAttach($Connection){
		$this->Socketa[$Connection->id] = $Connection->Socket;
		//echo "New connection\n";
	}
	
	function onDetach($Connection){
		unset($this->Socketa[$Connection->id]);
		//echo "Closed connection\n";
	}
	
	function getConnection($Socket){
		$key = array_search($Socket, $this->Socketa);
		if ($key === false){}else return $this->connectiona[$key];
		return false;
	}
	
	function CheckConnections(){
		if (!count($this->Socketa)) return;
		foreach ($this->Socketa as $id => $sock){
			if (socket_last_error($sock) > 10000){
				// @todo remove comment
				//echo "socket error - detach\r\n";
				$this->detach($this->connectiona[$id]);
			}
		}
		$r = $this->Socketa;
		$w = $this->Socketa;
		$e = $this->Socketa;
		// Check if data available
		if (false === socket_select($r, $wn =null, $e, 0)){
			//tLog("socket_select() failed, reason: ".socket_strerror(socket_last_error()), 'ERROR');
		}else{
			foreach ($r as $sock){
				$Connection = $this->getConnection($sock);
				//echo "Reading...\n";
				if ($Connection === false){
				}else{
					$Connection->Read();
					if (is_object($Connection) && ($Connection->Socket === null)) {
						//echo "read error - detach\r\n";
						$this->detach($Connection);
					}
				}
			}
			foreach ($e as $sock){
				if ($Connection = $this->getConnection($sock)){
					// @todo remove comment
					//echo "socket select exclude - detach\r\n";
					$this->detach($Connection);
				}
			}
		}
		$w = array();
		foreach ($this->connectiona as $Connection){
			$Connection->pushChk();
			if (strlen($Connection->WriteBUF)){
				$w[] = $this->Socketa[$Connection->id];
			}
		}
		if (!count($w)) return;
		if (false === socket_select($r = null, $w, $e = null, 0)){
			//tLog("socket_select() failed, reason: ".socket_strerror(socket_last_error()), 'ERROR');
		}else{
			foreach ($w as $sock){
				$Connection = $this->getConnection($sock);
				if ($Connection === false){
				}else{
					$Connection->WriteFlush();
				}
			}
		}
		// Write
	}
	
}
?>