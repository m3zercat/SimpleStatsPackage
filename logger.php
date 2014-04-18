<?php

function do_log()
{
	global $_db;
	$stmt = $_db->prepare("INSERT INTO stats (browser, user_ident, visit_ident, page, time, ip, referer) VALUES (?, ?, ?, ?, ?, ?, ?);");

	if($stmt === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
	}

	$uniqid = uniqid();

	if(array_key_exists('_app_support',$_COOKIE))
	{
		$uniqid = $_COOKIE['_app_support'];
	}

	setcookie('_app_support', $uniqid, mktime(12,12,12,1,1,date('Y')+1), '/', 'originhospitality.co.uk', false, true);

	$browser = 'unknown';
	if(array_key_exists('HTTP_USER_AGENT', $_SERVER))
	{
		$browser = $_SERVER['HTTP_USER_AGENT'];
	}

	$visit_ident = session_id();
	$user_ident = $uniqid;

	$page = 'unknown';
        if(array_key_exists('REQUEST_URI', $_SERVER))
        {
                $page = $_SERVER['REQUEST_URI'];
        }

	if(ON_404)
	{
		$page = '404 -> '.$page;
	}

	$ip = 'unknown';
	if(array_key_exists('REMOTE_ADDR', $_SERVER))
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$time = date('Y-m-d H:i:s');

	$referer = '';
	if(array_key_exists('HTTP_REFERER',$_SERVER)){
		$referer = $_SERVER['HTTP_REFERER'];
	}

	$stmt->bind_param('sssssss', $browser, $user_ident, $visit_ident, $page, $time, $ip, $referer);

	$stmt->execute();

}

do_log();

