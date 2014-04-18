<?php

function println($str)
{
	echo $str.PHP_EOL;
}

function table()
{
	$content = '<table style="border: 1px solid black; border-collapse:collapse;">';
	$content .= implode("", func_get_args());
	return $content . "\n</table>";
}

function tr()
{
    $content = implode("\n\t\t", func_get_args());
    return "\n\t<tr>\n\t\t$content</tr>";
}

function th($data)
{
    return "<th style=\"border: 1px solid black; padding: 3px; border-collapse:collapse;\">$data</th>";
}

function td($data)
{
    return "<td style=\"border: 1px solid black; padding: 3px; border-collapse:collapse;\">$data</td>";
}

function twodp($num)
{
	return number_format($num, 2, '.', '');
}

function debug($message)
{
	if(!defined('DEBUG')){
		define('DEBUG', false);
	}elseif(DEBUG)
	{
		println($message);
	}
}

