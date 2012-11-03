<?php
	require_once("cppsrClient.php");
	require_once("mysql.php");
	require_once("Room.php");
	class CPPSR{
		protected $config;
		protected $hooks;
		protected $master_socket;
		public $max_clients = 10;
		public $max_read = 1024;
		public $clients;
		public $rooms = array();
		public $clothing = array("head", "body", "neck", "feet", "color", "flag", "photo", "hand", "face");
		
		public function __construct($bind_ip,$port){
			set_time_limit(0);
			$this->hooks = array();
			$this->config["ip"] = $bind_ip;
			$this->config["port"] = $port;
			$this->master_socket = socket_create(AF_INET, SOCK_STREAM, 0);
			socket_bind($this->master_socket,$this->config["ip"],$this->config["port"]) or die("Cannot bind");
			socket_getsockname($this->master_socket,$bind_ip,$port);
			socket_listen($this->master_socket);
			$this->mysql = new MySQL();
			$c = $this->mysql->connect("127.0.0.1", "root", "");
			$this->mysql->select_db("CPPSR");
			if($c){
				$this->sendLog("Listenting for connections on {$bind_ip}:{$port}");
				$this->buildWorld();
			}else{
				$this->sendLog("Could not connect to MySQL!");
			}
		}
		public function buildWorld(){
			$this->rooms[1] = new Room($this, "Test Room A", 1, 370, 290);
			$this->rooms[2] = new Room($this, "Test Room B", 2, 380, 280);
			$q = $this->mysql->query("SELECT * FROM rooms");
			while($as = $this->mysql->assoc($q)){
				if($as['id'] == null || $as['id'] < 1000)
					continue;
				if($as['name'] == null)
					continue;
				if($as['x'] == null)
					$as['x'] = 370;
				if($as['y'] == null)
					$as['y'] = 280;
				$this->rooms[$as['id']] = new Room($this, $as['name'], $as['id'], $as['x'], $as['y']);
				if($this->rooms[$as['id']]){
					$this->rooms[$as['id']]->data = $as['data'];
				}
			}
		}
		public function createRoom($client, $name, $data, $x, $y){
/*			if($client == null)
				return;
			if($client->rooms > 5)
				return $client->sendError(7);
			$q = $this->mysql->query("SELECT * FROM rooms WHERE name = '" . $this->mysql->escape($name) . "';");
			$nr = $this->mysql->num_rows($q);
			if($nr > 0)
				return $client->sendError(6);
			$q2 = $this->mysql->query("INSERT INTO rooms (id, name, data, owner, x, y) VALUES (NULL, " . $this->mysql->escape($name) . "', '" . $this->mysql->escape(serialize($data)) . "', '" . $this->mysql->escape($client->id) . "', " . $this->mysql->escape($x) . ", " . $this->mysql->escape($y) . ");");
			if(!$q2)
				return $client->sendError(0);
			$q3 = $this->mysql->query("SELECT * FROM rooms WHERE name = '" . $this->mysql->escape($name) . "';");
			$as = $this->mysql->assoc($q3);
			$id = $as['id'];
			if(!$id)
				return $client->sendError(0);
			$this->rooms[$id] = new Room($this, $as['name'], $as['id'], $as['x'], $as['y']);
			if($this->rooms[$id])
				$client->joinRoom($id);
			// Great Success..... but still php.*/
		}
		public function hook($command,$function){
			$command = strtoupper($command);
			if(!isset($this->hooks[$command])){
				$this->hooks[$command] = array();
			}
			$k = array_search($function,$this->hooks[$command]);
			if($k === FALSE){
				$this->hooks[$command][] = $function;
			}
		}
		public function unhook($command = NULL,$function){
			$command = strtoupper($command);
			if($command !== NULL)
			{
				$k = array_search($function,$this->hooks[$command]);
				if($k !== FALSE)
				{
					unset($this->hooks[$command][$k]);
				}
			} else {
				$k = array_search($this->user_funcs,$function);
				if($k !== FALSE)
				{
					unset($this->user_funcs[$k]);
				}
			}
		}
 
		public function loop_once()
		{
			$read[0] = $this->master_socket;
			for($i = 0; $i < $this->max_clients; $i++)
			{
				if(isset($this->clients[$i]))
				{
					$read[$i + 1] = $this->clients[$i]->socket;
				}
			}
			if(socket_select($read,$write = NULL, $except = NULL, $tv_sec = 5) < 1)
			{
				return true;
			}
			if(in_array($this->master_socket, $read))
			{
				for($i = 0; $i < $this->max_clients; $i++)
				{
					if(empty($this->clients[$i]))
					{
						$temp_sock = $this->master_socket;
						$this->clients[$i] = new Client($this->master_socket,$i,$this);
						$this->trigger_hooks("CONNECT",$this->clients[$i],"");
						break;
					}
					elseif($i == ($this->max_clients-1))
					{
						$this->sendLog("Too many clients.");
					}
				}
 
			}

			for($i = 0; $i < $this->max_clients; $i++)
			{
				if(isset($this->clients[$i]))
				{
					if(in_array($this->clients[$i]->socket, $read))
					{
						$input = socket_read($this->clients[$i]->socket, $this->max_read);
						if($input == null)
						{
							$this->disconnect($i);
						}
						else
						{
							$this->sendLog("[{$this->clients[$i]->ip}{$i}]: {$input}");
							$this->trigger_hooks("INPUT",$this->clients[$i],$input);
						}
					}
				}
			}
			return true;
		}
		
		public function sendToRoom($id, $packet){
			$this->rooms[$id]->send($packet);
		}

		public function disconnect($client_index,$message = "")
		{
			$i = $client_index;
			$this->sendLog("Client [{$this->clients[$i]->ip}({$i})] has disconnected from server.");
			$this->trigger_hooks("DISCONNECT",$this->clients[$i],$message);
			$this->clients[$i]->destroy();
			unset($this->clients[$i]);
		}
 		public static function sendLog($text){
			$filename = "log.txt";
			echo("[" . date("Y/m/d h:i:s", mktime()) . "] " . $text . "\r\n\n");
			$fd = fopen($filename, "a");
			$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $text;
			fwrite($fd, $str . "\n");
			fclose($fd);
		}
		
		public function sendRawPacket(&$sock,$string,$crlf = "\r\n",$nbyte="\0"){
			$this->sendLog("Sent: {$string}");
			if($crlf){
				$string = "{$string}{$nbyte}"; 
			}
			return socket_write($sock,$string,strlen($string));
		}
		public function generateKey($string){
			for($i = 0; $i <= 20; $i++){
				$string = sha1($string);
			}
			$string = strrev($string);
			return $string;
		}
		public function debug($message){
			echo "[Debug] " . $message . "\n";
		}
		public function trigger_hooks($command,&$client,$input){
			if(isset($this->hooks[$command])){
				foreach($this->hooks[$command] as $function){
					//$this->sendLog("Triggering Hook '{$function}' for '{$command}'");
					$continue = call_user_func($function,&$this,&$client,$input);
					if($continue === FALSE){
						break;
					}
				}
			}
		}
		public function infinite_loop(){
			$randomness = true;
			do{
				$randomness = $this->loop_once();
			}while($randomness);
		}
		function &__get($name){
			return $this->{$name};
		}
	}
