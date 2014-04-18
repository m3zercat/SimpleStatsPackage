#!/usr/bin/php
<?php
require_once(dirname(__FILE__).'/init.php');

println("# Site-Stats running!");
println("");

$month = date('m');
$year = date('Y');
if($month > 1)
{
	$month--;
}
else
{
	$month = 12;
	$year--;
}

$lastMonth = mktime(0,0,0,$month+1,0,$year);
$thisMonth = mktime(0,0,0,date('m')+1,0,date('Y'));

$timeRestriction = "(time <= '".date('Y-m-d H:i:s',$thisMonth)."' AND time > '".date('Y-m-d H:i:s',$lastMonth)."')";

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

//runQ("SELECT id, browser, user_ident, visit_ident, page, time, ip, referer FROM stats WHERE $timeRestriction;");

$simpledata = array();
$data = array();

$simpledata['Period From'] = date('Y-m-d H:i:s',$lastMonth);
$simpledata['Period To'] = date('Y-m-d H:i:s',$thisMonth);

// total visits
$simpledata["Total visits"] = runCount("SELECT count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;");

// total unique visitors
$simpledata["Unique visitors"] = runCount("SELECT count(DISTINCT(`user_ident`)) AS `count` FROM stats WHERE $timeRestriction;");

// total page views
$simpledata["Page views"] = runCount("SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction;");

// average page views
$simpledata["Average pages viewed per visit"] = runCount("SELECT count(`id`) / count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;");

// max page views in a visit
$simpledata["Max page views in a visit"] = runCount("SELECT max(`count`) AS `count` FROM (SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction GROUP BY `visit_ident`) AS ttable;");


$datablock = array();

$lines = runQ("SELECT count(`id`) AS `count`, referer FROM stats GROUP BY referer;");
foreach($lines as $line)
{
	extract($line);
	$datablock[] = array($referer => $count);
}

$data["Referal Sources:"] = $datablock;

$datablock = array();

$lines = runQ("SELECT count(`id`) AS `count`, page FROM stats GROUP BY page;");
foreach($lines as $line)
{
	extract($line);
	$datablock[] = array($page => $count);
}

$data["Views by Page"] = $datablock;


$e = new emailer();
$e->addTo(MAIL_TO, MAIL_TO_NAME);
$e->setSubject('Your stats report - '.date('Y-m-d H:i:s'));
$e->setBody('stats.mail.html', array(
	'bgcolor' => '#9900CC',
	'textbgcolor' => '#ffffff',
	'textcolor' => '#b472e8',
	'bgtextcolor' => '#ffffff',
	'simpledata' => $simpledata,
	'data' => $data));
$e->send();

