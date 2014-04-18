<?php

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
