<?php
class Client {
		protected $socket;
 		protected $ip;
		protected $hostname;
		protected $server_clients_index;
		protected $parent;
		
		public $id;
		public $name;
		public $room;
		public $crumbs;
		public $inventory;
		public $alerts = 0;
		
		public function login($username, $password){
			$key = $this->parent->generateKey($password);
			$password = sha1($password);
			$q = $this->parent->mysql->query("SELECT * FROM users WHERE username = '" . $this->parent->mysql->escape($username) . "';");
			$nr = $this->parent->mysql->num_rows($q);
			if($nr != 1){
				$this->sendError(2);
				return;
			}
			$q2 = $this->parent->mysql->query("SELECT * FROM users WHERE username = '" . $this->parent->mysql->escape($username) . "' AND password = '" . $this->parent->mysql->escape($password) . "';");
			$nr2 = $this->parent->mysql->num_rows($q2);
			$as = $this->parent->mysql->assoc($q2);
			$playerId = $as['id'];
			if($nr2 != 1){
				$this->sendError(3);
				return;
			}
			$this->id = $playerId;
			$this->name = $username;
			$this->setPlayerCrumbs();
			$this->write("%wrl%$playerId%" . $this->inventory . "%");
			$this->joinRoom(1);
		}
		public function setPlayerCrumbs(){
			$q = $this->parent->mysql->query("SELECT * FROM users WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
			$as = $this->parent->mysql->assoc($q);
			$s = $as['id'];
			$s .= "|" . $as['username'];
			$s .= "|" . $as['color'];
			$s .= "|" . $as['head'];
			$s .= "|" . $as['body'];
			$s .= "|" . $as['face'];
			$s .= "|" . $as['neck'];
			$s .= "|" . $as['feet'];
			$s .= "|" . $as['hand'];
			$s .= "|" . $as['age'];
			$s .= "|" . $as['rank'];
			$s .= "|" . $as['mood'];
			$s .= "|" . $as['coins'];
			$s .= "|" . $as['inventory'];
			$s .= "|" . $as['photo'];
			$s .= "|" . $as['flag'];
			$this->crumbs = $s;
			$this->alerts = $as['alerts'];
			$this->inventory = $as['inventory'];
		}
		public function addItem($id){
			$q = $this->parent->mysql->query("SELECT * FROM users WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
			$as = $this->parent->mysql->assoc($q);
			$inventory = $as['inventory'];
			if($inventory == null || $inventory == ""){
				$new_inventory = $id;
			}else{
				$new_inventory = $inventory .= "/" . $id;
			}
			$this->parent->mysql->query("UPDATE users SET inventory = '" . $this->parent->mysql->escape($new_inventory) . "' WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
			$this->write("%ai%$id%");
		}
		public function removeItem($id){
			$q = $this->parent->mysql->query("SELECT * FROM users WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
			$as = $this->parent->mysql->assoc($q);
			$inventory = $as['inventory'];
			$items = explode("/", $inventory);
			foreach($items as $key => $item){
				if($item[$key] == $id){
					unset($items[$key]);
				}
			}
			$new_inventory = implode("/", $items);
			
			$this->parent->mysql->query("UPDATE users SET inventory = '" . $this->parent->mysql->escape($new_inventory) . "' WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
			$this->write("%ri%$id%$new_inventory%");
		}
		public function isValid(){
			if($this->id == null)
				return false;
			if($this->name == null)
				return false;
			if($this == null)
				return false;
			if($this->crumbs == null)
				return false;
			return true;
		}
		public function frame($frame){
			if(!$this->isValid())
				return;
			$this->parent->sendToRoom($this->room, "%sf%" . $frame  . "%" . $this->id . "%");
		}
		public function chat($message){
			if(!$this->isValid())
				return;
			$show = true;
			$client = $this;
			eval("include(\"Chat.php\");");
			if($show)
				$this->parent->sendToRoom($this->room, "%sm%" . $message  . "%" . $this->id . "%");
		}
		public function move($x, $y){
			if(!$this->isValid())
				return;
			if($x && $y){
				$this->x = $x;
				$this->y = $y;
				$this->parent->rooms[$this->room]->movePlayer($this->id, $x, $y);
			}
		}
		public function emote($emote){
			if(!$this->isValid())
				return;
			$this->parent->sendToRoom($this->room, "%se%" . $emote  . "%" . $this->id . "%");
		}
		public function sendError($id){
			if(!is_numeric($id))
				return;
			$this->write("%err%$id%");
		}
		public function updateItem($type, $value){
			if(!in_array($type, $this->parent->clothing))
				return;
			$this->parent->mysql->query("UPDATE users SET " . $this->parent->mysql->escape($type) . " = '" . $this->parent->mysql->escape($value) . "' WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
			$this->parent->rooms[$this->room]->send("%upi%$type%$value%{$this->id}%");
		}
		public function joinRoom($id){
			if(!$this->isValid())
				return;
			if($this->room)
				$this->removeFromRooms();
			$this->parent->rooms[$id]->addPlayer($this);
			$this->room = $id;
		}
		public function removeFromRooms(){
			$this->parent->rooms[$this->room]->removePlayer($this);
		}
		public function write($data){
			$this->parent->sendRawPacket($this->socket, $data);
		}
		public function __construct(&$socket,$i, $parent){
			$this->server_clients_index = $i;
			$this->socket = socket_accept($socket) or die("Failed to Accept");
			$this->parent = $parent;
			$this->parent->debug("New Client Connected");
			socket_getpeername($this->socket,$ip);
			$this->ip = $ip;
			$this->room = null;
		}
		public function lookup_hostname(){
			$this->hostname = gethostbyaddr($this->ip);
			return $this->hostname;
		}
		public function destroy(){
			socket_close($this->socket);
		}
		function &__get($name){
			return $this->{$name};
		}
		function __isset($name){
			return isset($this->{$name});
		}
}