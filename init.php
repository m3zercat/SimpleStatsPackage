<?php

define('STAT_ROOT', dirname(__FILE__));

require_once(STAT_ROOT.'/config.php');
require_once(STAT_ROOT.'/util-funcs.php');
require_once(STAT_ROOT.'/Twig/lib/Twig/Autoloader.php');
require_once(STAT_ROOT.'/PHPMailer/PHPMailerAutoload.php');

require_once(STAT_ROOT.'/database.php');

Twig_Autoloader::register();
$twig = new Twig_Environment(new Twig_Loader_Filesystem(STAT_ROOT.'/templates'), array());

