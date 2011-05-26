<?php

function plugin_mail_info()
{
	global $context, $txt;

	return array(
		'code' => array(
			'enable' => 'plugin_mail_install',
			'disable' => 'plugin_mail_uninstall',
		),
	);
}

function plugin_mail_install()
{
	add_integration_function('integrate_outgoing_email', 'ep_plugin_integrate_outgoing_email');
}

function plugin_mail_uninstall()
{
	remove_integration_function('integrate_outgoing_email', 'ep_plugin_integrate_outgoing_email');
}

function ep_plugin_integrate_outgoing_email($subject, &$message, &$headers)
{
	global $webmaster_email, $context, $modSettings, $txt, $scripturl;
	global $smcFunc;

	// Use sendmail if it's set or if no SMTP server is set.
	$use_sendmail = empty($modSettings['mail_type']) || $modSettings['smtp_host'] == '';

	// Line breaks need to be \r\n only in windows or for SMTP.
	$line_break = $context['server']['is_windows'] || !$use_sendmail ? "\r\n" : "\n";

	if (!isset($context['ep_mail_file']))
		return;

	$file_size = filesize($context['ep_mail_file']);
	$handle = fopen($context['ep_mail_file'], "rb");
	$content = fread($handle, $file_size);
	fclose($handle);
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$header .= "--" . $uid . $line_break;
	$header .= "Content-Type: application/octet-stream; name=\"" . $filename . '"' . $line_break; // use different content types here
	$header .= 'Content-Transfer-Encoding: base64' . $line_break;
	$header .= "Content-Disposition: attachment; filename=\"" . $filename . '"' . $line_break . $line_break;
	$header .= $content . $line_break . $line_break;
	$header .= "--" . $uid . "--";
}


?>