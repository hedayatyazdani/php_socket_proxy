<?php
/**
 * Socket Server Demo.
 * 
 *
 *
 */

require_once('SocketServer.php');

class testProtocol extends tSocketProtocol{
	
	function onConnect($Connection){
		$this->WriteTo($Connection->id, "Connect");
		
	}

	function onDisconnect($Connection){
		$this->WriteTo($Connection->id, "");
	}

	function onData($Connection){
		// Check $Connection->ReadBUF
		// Write to $Connection->Write($Data);
		// Write to another connection $this->WriteTo($ConnectionId, $Data);
		// Write to all connections $this->WriteToAll($Data);
		// Each connection have own id: $Connection->id
		
		if ( !strlen($Connection->ReadBUF) ) return;
		$p = $Connection->ReadBUF;
		
		
		$Connection->Write($p);
		
	}
}




$ip = ( preg_match('/\d{1,6}\.\d{1,6}(\.\d{1,6}\.\d{1,6}){1,3}/',$argv[1]))? $argv[1] : false ;
$port = is_numeric($argv[2])? $argv[2] : false;

if(!$ip || !$port){
	echo $argv[0]. " ip port\n".
	$argv[0]. " 192.168.1.100  448 ";
	die();
}

$server = new tSocketServer();
$server->attachProtocol(new testProtocol());

if ($server->Bind($ip, $port)){
	echo "Binded\n";
	$server->Start();
}

?>