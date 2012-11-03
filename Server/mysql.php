<?php
class MySQL{
private $host;
private $username;
private $password;
function connect($host, $username, $password){
	$c = @mysql_connect($host, $username, $password);
	return $c;
}
function select_db($db){
	$db = @mysql_select_db($db);
	return $db;
}
function query($q){
	$q = @mysql_query($q);
	return $q;
}
function num_rows($q){
	return @mysql_num_rows($q);
}
function assoc($q){
	return @mysql_fetch_assoc($q);
}
function escape($str){
	return @mysql_real_escape_string($str);
}
function fetchArray($q){
	return @mysql_fetch_array($q);
}
function encrypt($string){
	$s1 = md5($string);
	$s1 = md5($s1);
	$s1 = md5($s1);
	$s1 = md5($s1);
	$s1 = md5($s1);
	$s1 = md5($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = md5($s1);
	$s1 = md5($s1);
	$s1 = md5($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	$s1 = sha1($s1);
	return $s1;
}
}
?>