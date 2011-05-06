<?php
// Version 1.0; EnvisionProfile

// Starts a buffer to capture rendered data.
function template_render_save_above()
{
	ob_start('renderAttachmentCallBack');
}

// End the buffer and offer to download the attachment.
function template_render_save_below()
{
	saveRenderedAttachment();
}

// This prints XML in it's most generic form. Unmodified.
function template_generic_xml()
{
	global $context, $settings, $options, $txt;

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>';

	// Show the data.
	template_generic_xml_recursive($context['xml_data'], 'smf', '', -1);
}

// Recursive function for displaying generic XML data. Modified slightly to make it more dynamic.
function template_generic_xml_recursive($xml_data, $parent_ident, $child_ident, $level)
{
	// This is simply for neat indentation.
	$level++;

	echo "\n" . str_repeat("\t", $level), '<', $parent_ident, '>';

	foreach ($xml_data as $key => $data)
	{
		// A group?
		if (is_array($data) && isset($data['identifier']))
			template_generic_xml_recursive($data['children'], $data['identifier'], $key, $level);
		// An item...
		elseif (is_array($data) && isset($data['value']))
		{
			echo "\n", str_repeat("\t", $level), '<', $key;

			if (!empty($data['attributes']))
				foreach ($data['attributes'] as $k => $v)
					echo ' ' . $k . '="' . $v . '"';
			echo '><![CDATA[', cleanXml($data['value']), ']]></', $key, '>';
		}

	}

	echo "\n", str_repeat("\t", $level), '</', $parent_ident, '>';
}

?>
