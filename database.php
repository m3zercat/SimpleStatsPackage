<?php

define('DBDATETIMEF', 'Y-m-d H:i:s');

$_db = new mysqli(DBHOSTNAME, DBUSERNAME, DBPASSWORD, DBNAME);

if(!is_null($_db->connect_error))
{
	trigger_error('DATABASE CONNECTION ERROR: '.$_db->connect_error, E_USER_ERROR);
}

$_db->query('DELETE FROM stats WHERE page = \'/favicon.ico\'');

function db_query($q)
{
	global $_db;
	$result = $_db->query($q);
	if($result === false)
	{
		trigger_error('DATABASE QUERY ERROR: '.$_db->error, E_USER_ERROR);
	}
	return $result;
}

function db_insert()
{
	global $_db;
	$args = func_get_args();
	$q = array_shift($args);
	$matches = array();
	preg_match_all('/\?([a-z])/', $q, $matches);
	$q = preg_replace('/\?([a-z])/', '?', $q);
	array_unshift($args, join($matches[1]));
	$stmt = $_db->prepare($q);
	if($stmt === false)
	{
		trigger_error('DATABASE QUERY ERROR: '.$_db->error, E_USER_ERROR);
	}
	$reflector = new ReflectionClass('mysqli_stmt');
	$method = $reflector->getMethod('bind_param');
	$method->invokeArgs($stmt, $args);
	if(false === $stmt->execute()){
		trigger_error('DATABASE QUERY ERROR: '.$_db->error, E_USER_ERROR);
	}
	$stmt->close();
}

function runQ($q)
{
	$result = db_query($q);
	$return = array();
	while($row = $result->fetch_assoc())
	{
		$return[] = $row;
	}
	return $return;
}

function runCount($q)
{
	$result = db_query($q);
	$row = $result->fetch_assoc();
	return $row['count'];
}
