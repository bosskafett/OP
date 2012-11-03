<?php
class Room {
	public $id;
	public $name;
	public $clients;
	public $parent;
	public $x;
	public $y;
	public $data;
	
	function getClients(){
		return $this->clients;
	}
	function getName(){
		return $this->name;
	}
	function getID(){
		return $this->id;
	}
	function addPlayer($client){
		$this->clients[$client->id] = $client;
		$this->clients[$client->id]->write("%rm%" . $this->id . "%" . $client->alerts . "%");
		$this->clients[$client->id]->write("%al%" . $this->clients[$client->id]->alerts . "%");
		$this->clients[$client->id]->x = $this->x + rand(2, 15);
		$this->clients[$client->id]->y = $this->y - rand(2, 15);
		foreach($this->clients as $sclient){
			$client->write("%cnu%" . $sclient->id . "%" . $sclient->name . "%" . $sclient->x . "%" . $sclient->y . "%" . $sclient->crumbs . "%");
		}
		$this->setupBots($client);
		$this->sendAs($client->id, "%ap%" . $client->id . "%" . $client->name . "%" . $client->x . "%" . $client->y . "%" . $client->crumbs . "%");
	}
	function removePlayer($client){
		$client->sendError(4);
		unset($this->clients[$client->id]);
	}
	function movePlayer($id, $x, $y){
			$this->sendAs($id, "%sp%" . $x . "%" . $y . "%" . $id . "%");
	}
	function send($data){
		foreach($this->clients as $sclient){
			$sclient->write($data);
		}
	}
	function getData()
	{
		return unserialize($this->data);
	}
	function setData($data, $value)
	{
		$data_arr = unserialize($this->data);
		$data_arr[$data] = $value;
		$this->data = serialize($data_arr);
		$this->parent->mysql->query("UPDATE rooms SET data = '" . $this->parent->mysql->escape($this->data) . "' WHERE id = '" . $this->parent->mysql->escape($this->id) . "';");
	}
	function setupBots($client){
		if($client->id == null)
			return;
		if($client == null)
			return;
		$client->write("%cnu%0%OpenPenguinsV1%360%250%0|OpenPenguinsV1|6|413|4021|0|0|0|0|0|0|0|%");
	}
	function sendAs($id, $data){
		foreach($this->clients as $sclient){
			if($sclient->id != $id){
				$sclient->write($data);
			}
		}
	}
	function __construct($container, $name, $Id, $x, $y){	
		$this->parent = $container;
		$this->name = $name;
		$this->id = $Id;
		$this->x = $x;
		$this->y = $y;
	}
}