# SimpleStatsPackage

Creates a simple stats report for a site and then emails it.

## Installation

* Pretty sure this requires php 5.3.

Simply clone this repository (and submodules) to your server then copy the config.sample.php and adjust the values to match your setup.

You will also need to add tables to your database using this sql:

[code]
CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `browser` varchar(255) NOT NULL,
  `user_ident` varchar(255) NOT NULL,
  `visit_ident` varchar(255) NOT NULL,
  `page` varchar(255) NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  `ip` varchar(25) NOT NULL,
  `referer` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `visit_ident` (`visit_ident`),
  KEY `user_ident` (`user_ident`),
  KEY `browser` (`browser`),
  KEY `page` (`page`),
  KEY `time_2` (`time`),
  KEY `ip` (`ip`),
  KEY `referer` (`referer`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=115 ;


CREATE TABLE IF NOT EXISTS `stats-processed-data` (
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` longtext NOT NULL,
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
[/code]

Once this is done set it up to run cron.php from your crontab at least once a month to have stats emailed to you.
Stats are calculated from month beginning to month end of the month prior to the execution. That is if you execute the program on 10th of March, you'll get data from beginning to end of Feb.

## TODO

* Clear old data from the stats table when it is no longer necessary.
* Add Graphs/Charts?

## Example Report

An example report. You can theme it for your self or your clients - it uses a twig template.

![SimpleStatsReport](https://github.com/m3zercat/SimpleStatsPackage/raw/master/sample-report.png)
