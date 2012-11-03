<?php
	require_once("CPPSR.php");
	$server = new CPPSR("127.0.0.1",8650);
	$server->max_clients = 10;
	$server->hook("INPUT", "handle_input");
	$server->infinite_loop();

	function handle_input(&$server,&$client,$input)
	{
		$trim = trim($input);
		$data = explode("%", $trim);
		$type = strtolower($data[1]);
		array_shift($data);
		switch($type)
		{
			case "aut":
				$client->login($data[1], $data[2]);
			break;
			case "sf":
				$client->frame($data[1]);
			break;
			case "sm":
				$client->chat($data[1]);
			break;
			case "sp":
				$client->move($data[1], $data[2]);
			break;
			case "se":
				$client->emote($data[1]);
			break;
			case "u#hea":
				$client->updateItem("head", $data[1]);
			break;
			case "u#fee":
				$client->updateItem("feet", $data[1]);
			break;
			case "u#bod":
				$client->updateItem("body", $data[1]);
			break;
			case "u#nec":
				$client->updateItem("neck", $data[1]);
			break;
			case "u#han":
				$client->updateItem("hand", $data[1]);
			break;
			case "u#fac":
				$client->updateItem("face", $data[1]);
			break;
			case "u#oth":
				$client->updateItem("other", $data[1]);
			break;
			case "u#col":
				$client->updateItem("color", $data[1]);
			break;
			case "u#fla":
				$client->updateItem("flag", $data[1]);
			break;
			case "u#pho":
				$client->updateItem("photo", $data[1]);
			break;
			case "dis":
				$client->updateItem($data[1], 0);
			break;
			case "ai":
				$client->addItem($data[1]);
			break;
			case "ri":
				$client->removeItem($data[1]);
			break;
			default:
			break;
		}
	}