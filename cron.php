#!/usr/bin/php
<?php
require_once(dirname(__FILE__).'/init.php');

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// preparations

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


$lastMonth = mktime(0,0,0,$month,0,$year);
$thisMonth = mktime(0,0,0,date('m'),0,date('Y'));
$timeRestriction = "(time <= '".date('Y-m-d H:i:s',$thisMonth)."' AND time > '".date('Y-m-d H:i:s',$lastMonth)."')";

if(0<runCount("SELECT count(`timestamp`) AS `count` FROM `stats-processed-data` WHERE `timestamp` = '".date(DBDATETIMEF,$thisMonth)."';")){
	debug("Already Done!");
	die();
}

$currentdata = array();
$specialtext = array();
$comparedata = array();

function gData($key, $val, $cmp=false, $str='')
{
	global $currentdata, $specialtext, $comparedata;
	$currentdata[$key] = $val;
	$comparedata[$key] = $cmp;
	$specialtext[$key] = $str;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// load current data

gData('period_from', date(DBDATETIMEF,$lastMonth));
gData('period_to', date(DBDATETIMEF,$thisMonth));
gData('total_visitors', runCount("SELECT count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;"), true);
gData('unique_visitors', runCount("SELECT count(DISTINCT(`user_ident`)) AS `count` FROM stats WHERE $timeRestriction;"), true);
gData('total_page_views', runCount("SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction;"), true, ' Views');
gData('average_page_views_per_visitor', twodp(runCount("SELECT count(`id`) / count(DISTINCT(`visit_ident`)) AS `count` FROM stats WHERE $timeRestriction;")), true, ' Pages');
gData('max_page_views_by_a_visitor', runCount("SELECT max(`count`) AS `count` FROM (SELECT count(`id`) AS `count` FROM stats WHERE $timeRestriction GROUP BY `visit_ident`) AS ttable;"), true, ' Pages');
gData('average_duration_of_visit', twodp(runCount("SELECT avg(`duration`) AS `count` FROM (SELECT max(`time`) - min(`time`) AS `duration`, `visit_ident` FROM stats WHERE $timeRestriction GROUP BY `visit_ident`) AS ttable")/60), true, ' Minutes');
gData('longest_duration_of_visit', twodp(runCount("SELECT max(`duration`) AS `count` FROM (SELECT max(`time`) - min(`time`) AS `duration`, `visit_ident` FROM stats WHERE $timeRestriction GROUP BY `visit_ident`) AS ttable")/60), true, ' Minutes');
gData('new_visitors', runCount("SELECT count(DISTINCT(`user_ident`)) AS `count` FROM stats WHERE $timeRestriction AND `user_ident` NOT IN (SELECT DISTINCT(`user_ident`) FROM stats WHERE time <= '".date('Y-m-d H:i:s', $lastMonth)."');"), true);

$lines = runQ("SELECT count(`id`) AS `count`, referer FROM stats GROUP BY referer;");
foreach($lines as $line)
{
	$referer = strip_tags($line['referer']);
	if(empty($referer)){
		$referer = 'unknown/direct';
	}else{
		$referer = '<a href="'.$referer.'">'.$referer.'</a>';
	}
	$currentdata['referers'][$referer] = $line['count'];
}

$lines = runQ("SELECT count(`id`) AS `count`, page FROM stats GROUP BY page;");
foreach($lines as $line)
{
	$link = '<a href="http://www.originhospitality.co.uk'.$line['page'].'">'.$line['page'].'</a>';;
	$currentdata['pagedata'][$link] = $line['count'];
}

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// load prior data


$priordata = runQ("SELECT data FROM `stats-processed-data` WHERE timestamp = '".date(DBDATETIMEF,$lastMonth)."';");
if(count($priordata)>0){
	$priordata = json_decode($priordata[0]['data'], true);
}
else
{
	$priordata = array();
}

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// set up general data for template

$generaldata = array();
$generaldata[] = array("", "This Period", "Prior Period", "Difference", 'Diff %');

$keys = array_keys($currentdata);

foreach($keys as $key)
{
	if(is_array($currentdata[$key]))
	{
		continue;
	}
	if(array_key_exists($key, $priordata)){
		$_generaldata = array(ucwords(str_replace("_", " ", $key)), $currentdata[$key].$specialtext[$key], $priordata[$key].$specialtext[$key]);
		if($comparedata[$key])
		{
			$cmpresult = $currentdata[$key] - $priordata[$key];
			$_generaldata[] = $cmpresult;
			$_generaldata[] = twodp((100*$cmpresult)/$priordata[$key])."%";
		}
		else
		{ // if no compare, make empty data
			$_generaldata[] = '';
			$_generaldata[] = '';
		}
		$generaldata[] = $_generaldata;
	}else{
		$generaldata[] = array(ucwords(str_replace("_", " ", $key)), $currentdata[$key].$specialtext[$key], 'No Data', '', '');
	}
}


//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// set up referer data for template

$refererdata = array();
$refererdata[] = array("Referer", "Current Leads", "Current Percentage", "Prior Leads", "Prior Percentage", "Difference", "Diff %");
foreach($currentdata['referers'] as $referer => $count)
{
	if(array_key_exists('referers', $priordata) && array_key_exists($referer, $priordata['referers'])){
		$prior_percent = 'Insufficient Data';
		if(array_key_exists('total_page_views', $priordata)){
			$prior_percent = twodp((100*$priordata['referers'][$referer])/$priordata['total_page_views']).'%';
		}
		$cmpresult = $count - $priordata['referers'][$referer];
		$cmpresultpercent = twodp((100*$cmpresult)/$priordata['referers'][$referer])."%";
		$refererdata[] = array($referer, $count, twodp((100*$count)/$currentdata['total_page_views']).'%', $priordata['referers'][$referer], $prior_percent, $cmpresult, $cmpresultpercent);
		unset($priordata['referers'][$referer]);
	}else{
		$refererdata[] = array($referer, $count, twodp((100*$count)/$currentdata['total_page_views']).'%', 'No Data', 'No Data', '', '');
	}
}
if(array_key_exists('referers', $priordata) && count($priordata['referers'])>0)
{
	foreach($priordata['referers'] as $referer => $count)
	{
		$prior_percent = 'Insufficient Data';
		if(array_key_exists('total_page_views', $priordata)){
			$prior_percent = twodp((100*$count)/$priordata['total_page_views']).'%';
		}
		$refererdata[] = array($referer, 0, '0%', $count, $prior_percent, '!!!', '!!!');
	}
}

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// set up page data for template

$pagedata = array();
$pagedata[] = array("Page", "Current Visits", "Current Percentage", "Prior Visits", "Prior Percentage", "Difference", "Diff %");
foreach($currentdata['pagedata'] as $page => $count)
{
	if(array_key_exists('pagedata', $priordata) && array_key_exists($page, $priordata['pagedata'])){
		$prior_percent = 'Insufficient Data';
		if(array_key_exists('total_page_views', $priordata)){
			$prior_percent = twodp((100*$priordata['pagedata'][$page])/$priordata['total_page_views']).'%';
		}
		$cmpresult = $count - $priordata['pagedata'][$page];
		$cmpresultpercent = twodp((100*$cmpresult)/$priordata['pagedata'][$page])."%";
		$pagedata[] = array($page, $count, twodp((100*$count)/$currentdata['total_page_views']).'%', $priordata['pagedata'][$page], $prior_percent, $cmpresult, $cmpresultpercent);
		unset($priordata['pagedata'][$page]);
	}else{
		$pagedata[] = array($page, $count, twodp((100*$count)/$currentdata['total_page_views']).'%', 'No Data', 'No Data', '', '');
	}
}
if(array_key_exists('pagedata', $priordata) && count($priordata['pagedata'])>0)
{
	foreach($priordata['pagedata'] as $page => $count)
	{
		$prior_percent = 'Insufficient Data';
		if(array_key_exists('total_page_views', $priordata)){
			$prior_percent = twodp((100*$count)/$priordata['total_page_views']).'%';
		}
		$pagedata[] = array($page, 0, '0%', $count, $prior_percent, '!!!', '!!!');
	}
}

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// send out template

$e = new emailer();
$e->addTo(MAIL_TO, MAIL_TO_NAME);
$e->setSubject('Your stats report - '.date('Y-m-d H:i:s'));
$finalData = array(
	'bgcolor' => '#9900CC',
	'textbgcolor' => '#ffffff',
	'textcolor' => '#b472e8',
	'bgtextcolor' => '#ffffff',
	'generaldata' => $generaldata,
	'refererdata' => $refererdata,
	'pagedata' => $pagedata
);
$e->setBody('stats.mail.html', $finalData);
$e->send();

// save data only after sending so that we know we sent it

$var1 = date('Y-m-d H:i:s',$thisMonth);
$var2 = json_encode($currentdata);
db_insert("REPLACE INTO `stats-processed-data` (timestamp, data) VALUES (?s, ?s)", &$var1, &$var2);

debug("SENT!");
