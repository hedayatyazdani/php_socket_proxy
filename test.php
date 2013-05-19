<?php


require_once('SocketServer.php');

class testProtocol extends tSocketProtocol{
	
	private $tohw = null;
	
	function onConnect($Connection){
		$this->WriteTo($Connection->id, "php");
		
		
		// create connect to backend
		$ip = '192.168.1.100';
		$port = 8124;
		if (!$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
			echo 'a';
			return false;
		}
		//socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1); // BEFORE BIND !!!!
		//socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
		if (!(@socket_connect($sock, $ip, $port))){
			echo "1". socket_strerror(socket_last_error($sock));
			@socket_shutdown($sock, 2);
			@socket_close($sock);
			return false;
		}
		
		$this->tohw = $sock;
		var_dump($sock);
		echo "-----\n\n====\n";
	}

	function onDisconnect($Connection){
		$this->WriteTo($Connection->id, "");
	}

	function ReturnBody($serial, $body = ''){
			$msg = '##'. sprintf('%08s', $serial) . $body;
			return $msg;
	}

	function body($status, $gid, $uid, $len = 1,  $data = ''){
		$msg = sprintf('%04s', $len);
		$msg .= sprintf('%04s', $status);
		$msg .= pack('LL', $gid, $uid);
		$msg .= sprintf('%08s', '');
		$msg .= sprintf('%16s', $data);
		return $msg;
	}

	function onData($Connection){
		$hBytesIn = 0;
		$hReadBUF = null;
		var_dump($this->tohw);
		if ($data = @socket_read($this->tohw, 1024, PHP_BINARY_READ)) {
			if ($data !== ''){
				$hBytesIn += strlen($data);
				$hReadBUF .= $data;
			}else{
			}			
		}
		
		// Check $Connection->ReadBUF
		// Write to $Connection->Write($Data);
		// Write to another connection $this->WriteTo($ConnectionId, $Data);
		// Write to all connections $this->WriteToAll($Data);
		// Each connection have own id: $Connection->id
		
		echo $Connection->ReadBUF;
		echo "\n".$hReadBUF."==\n";
		if ( !strlen($Connection->ReadBUF) || !strlen($hReadBUF) ) return;
		$clientReturn = $Connection->ReadBUF;
		$serverReturn = $hReadBUF;
		
		if($serverReturn)
			$Connection->Write($serverReturn);
		
		if($clientReturn) {
			$done = 0;
			$len = strlen($clientReturn);
			while($done < $len){
				if (!$n = @socket_write($this->tohw, substr($this->clientReturn, $done))) break;
				$done += $n;
			}		
		}
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