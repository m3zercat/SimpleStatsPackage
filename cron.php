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

// total visits
println(runCount("SELECT count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;")." Total Visits");

// total unique visitors
println(runCount("SELECT count(DISTINCT(`user_ident`)) AS `count` FROM stats WHERE $timeRestriction;")." Unique Visitors");

// total page views
println(runCount("SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction;")." Page Views");

// average page views
println(runCount("SELECT count(`id`) / count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;")." Average Pages Viewed per Visit");

// max page views in a visit
println(runCount("SELECT max(`count`) AS `count` FROM (SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction GROUP BY `visit_ident`) AS ttable;")." Max Visit");

println("");
println("Referal Sources:");
println("----------------");

$lines = runQ("SELECT count(`id`) AS `count`, referer FROM stats GROUP BY referer;");
foreach($lines as $line)
{
	extract($line);
	println("$referer: $count");
}


println("");
println("Views by Page:");
println("--------------");

$lines = runQ("SELECT count(`id`) AS `count`, page FROM stats GROUP BY page;");
foreach($lines as $line)
{
	extract($line);
	println("$page: $count");
}



