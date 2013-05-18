<?php
/**
 * Demo How TO Use This
 *
 *
 */

/*
 * $Id: SocketProtocol.php,v 1.2 2009-04-19 13:42:20 root Exp $
 */
require_once('SocketServer.php');

class tSocketProtocol{
	/**
	 * @var tSocketServer
	 */
	public $Server = null;
	/**
	 * @var tSocketManager
	 */
	public $ConnectionManager = null;
	public $name = 'tSocketProtocol';

	function onData($Connection){
		// make your own
		//$this->WriteToAll(":".$Connection->ReadBUF."\r\n");
		//$Connection->ReadBUF = '';
	}

	function onConnect($Connection){}

	function onDisconnect($Connection){}
	
	function pushChk($Connection){}

	function WriteTo($ConnectionId, $Data, $SendNow=true){
		$this->ConnectionManager->connectiona[$ConnectionId]->Write($Data);
		if($SendNow) $this->ConnectionManager->connectiona[$ConnectionId]->WriteFlush();
	}

	function WriteToAll($Data){
		foreach ($this->ConnectionManager->connectiona as $Connection){
			$Connection->Write($Data);
		}
	}

}
?>