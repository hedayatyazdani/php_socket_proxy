<?php
/*
 * $Id: ServerConnection.php,v 1.2 2009-04-19 13:42:20 root Exp $
 */
 
class tServerConnection{

	public $id;
	public $ip;
	private static $idai = 0;
	public $ReadBUF = '';
	public $WriteBUF = '';
	public $BytesIn = 0;
	public $BytesOut = 0;
	
	public $Data; // Custom dataset
	private $protocola = array();
	private $observers = array();
	
	function beforeAttach($ConnectionManager){}
	function afterAttach($ConnectionManager){}
	function beforeDetach($ConnectionManager){}
	function afterDetach($ConnectionManager){}
	public function attachProtocol($Protocol){
		$this->protocola[$Protocol->name] = $Protocol;
	}
	
	public function detachProtocol($ProtocolName){
		unset($this->protocola[$ProtocolName]);
	}
	
	function onConnect(){
		foreach ($this->protocola as $Protocol){
			$Protocol->onConnect($this);
		}
	}
	
	function onDisconnect(){
		foreach ($this->protocola as $Protocol){
			$Protocol->onDisconnect($this);
		}
	}
	
	function pushChk(){
		foreach ($this->protocola as $Protocol){
			$Protocol->pushChk($this);
		}
	}	
	
	function onData(){
		foreach ($this->protocola as $Protocol){
			$Protocol->onData($this);
		}
	}
	
	function Read(){}
	
	function Write($data){
		$this->WriteBUF .= $data;
	}
	
	function WriteFlush(){}
	
	private static function newId(){
		return self::$idai++;
	}
	
	function generateId(){
		$this->id = self::newId();
	}
	
	function attach($observer){
		$this->observers[] = $observer;
	}
	
}
?>