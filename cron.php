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

echo table(

	// total visits
	tr (
		th("Total visits"),
		td(runCount("SELECT count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;"))
	),

	// total unique visitors
	tr (
		th("Unique visitors"),
		td(runCount("SELECT count(DISTINCT(`user_ident`)) AS `count` FROM stats WHERE $timeRestriction;"))
	),
	
	// total page views
	tr (
		th("Page views"),
		td(runCount("SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction;"))
	),
	
	// average page views
	tr (
		th("Average pages viewed per visit"),
		td(runCount("SELECT count(`id`) / count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;"))
	),

	// max page views in a visit
	tr (
		th("Max page views in a visit"),
		td(runCount("SELECT max(`count`) AS `count` FROM (SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction GROUP BY `visit_ident`) AS ttable;"))
	)
);

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



