<?php

require_once './vendor/autoload.php';

// What are you doing here, SMF?
const SMF = 1;

function call_integration_hook($hook, $parameters = array())
{
	// You're fired!  You're all fired!  Get outta here!
	return [];
}

function fatal_error($msg, $log)
{
	echo $msg;
}

function loadLanguage($template_name, $lang = '', $fatal = true, $force_reload = false)
{
}