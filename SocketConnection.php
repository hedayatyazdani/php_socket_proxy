<?php
/*
 * $Id: SocketConnection.php,v 1.4 2009-04-19 13:42:20 root Exp $
 */
require_once('ServerConnection.php');

class tSocketConnection extends tServerConnection{

	public $Socket = null;

	function SetOptions(){
		if(!is_resource($this->Socket)) return false;
		socket_set_option($this->Socket, SOL_SOCKET, SO_KEEPALIVE, 0);
		socket_set_option($this->Socket, SOL_SOCKET, SO_REUSEADDR, 1);
		@stream_set_timeout($this->Socket, 2);
		socket_set_nonblock($this->Socket);
	}

	function beforeDetach($SocketManager){
		// properly closing socket before detach
		$this->Close();
	}

	function Read(){
		if ($data = @socket_read($this->Socket, 1024, PHP_BINARY_READ)) {
			if ($data !== ''){
				$this->BytesIn += strlen($data);
				$this->ReadBUF .= $data;
				$this->onData();
			}else{
			}
		}else{
		}
		//$this->checkStatus();
	}

	function Close(){
		@socket_shutdown($this->Socket, 2);
		@socket_close($this->Socket);
		$this->Socket = null;
	}

	function checkStatus(){
		if (socket_last_error($this->Socket)){
			$n = socket_last_error($this->Socket);
			$s = socket_strerror($n);
			//echo "Socket error [".$n."]:".$s."\n";
			if (in_array($n, array(10053, 10054))){
				// 10053 An established connection was aborted by the software in your host machine.
				// 10054 An existing connection was forcibly closed by the remote host.
				$this->Close();
			}
		}
	}

	function WriteFlush(){
		if ($this->WriteBUF !== ''){
			$done = 0;
			$len = strlen($this->WriteBUF);
			while($done < $len){
				if (!$n = @socket_write($this->Socket, substr($this->WriteBUF, $done))) break;
				$done += $n;
			}
			//$this->checkStatus();
			$this->BytesOut += $done;
			if ($done == $len){
				$this->WriteBUF = '';
				return true;
			}else{
				$this->WriteBUF = substr($this->WriteBUF, $done, ($len - $done));
				# no more error on client disconnect
				return false;
			}
		}
	}

}
?>