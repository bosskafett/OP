<?php
	$arg = explode(" ", $message);
	$cmd = strtoupper(substr($arg[0], 1));
	switch($cmd)
	{
		case "ADD":
			$client->addItem($arg[1]);
		break;
		case "REMOVE":
			$client->removeItem($arg[1]);
		break;
		case "NEW":
			$data = array(
				"Objects" => "", 
				"Locked" => false,
			);
			$this->createRoom($client, $arg[1], $data, null, null);
		break;
	}
?>