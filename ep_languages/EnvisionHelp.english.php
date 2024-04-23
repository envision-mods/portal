<?php
// Version 1.0; EnvisionHelp

global $helptxt;

$helptxt['ep_layout_name'] = 'This is the name of your layout.  Layout Names are unique on a per group basis.  If the name already exists within this group, you will need to come up with a different Layout Name.';
$helptxt['ep_layout_actions'] = 'Type in the action, non-action, or url that you would like this layout to be associated with.  Non-actions are defined within brackets, such as <code>[topic]</code>, and [board]</code>.
<ul>
	<li><b><code>[topic]</code></b> points to index.php?topic</li>
	<li><b><code>[board]</code></b> points to index.php?board</li>
</ul>
Need a different layout for a specific board or topic?<ul>
	<li><b><code>[topic]=1</code></b> points to index.php?topic=1</li>
	<li><b><code>[board]=1</code></b> points to index.php?board=1</li>
</ul>
<p>All of the above will also work with queryless urls.</p>
<p>Need a layout to only appear on a specific action when certain subactions or other values are present in the url (address bar)?</p>
<ul>
	<li><b><code>profile;area=statistics</code></b> will be used in the profile stats page located at <i>index.php?action=profile;area=statistics</i></li>
	<li><b><code>profile;area=statistics,showposts</code></b> will be for either the profile stats area or the area to view posts (subsections for topics and attachments are also included)</li>
	<li><b><code>admin;area=serversettings;sa=cache</code></b> is specfically for the cache settings</li>
	<li><b><code>admin;area=serversettings,featuresettings;sa=cache,layout</code></b> is for either the cache settings or the layout options</li>
</ul>
<p>You can mix and match any url parameters and can even combine them with non-actions.</p>';
$helptxt['ep_layout_curr_actions'] = 'Lists the actions that are currently in use for this layout.  You can remove any action(s) by selecting them and clicking the Remove Action(s) button.';
$helptxt['ep_layout_style'] = 'This is a list of pre-defined Layouts that you can use for Envision Portal.  Select any of these and it will be created for you.';
$helptxt['ep_layout_curr_sections'] = 'Lists the sections that are currently in use for this layout.  You can manage each section by changing its order, colspan, enabled status, and whether SMF uses it. However, you cannot change the SMF position in the default Envision Portal layout.';

/*********************************************
 * All Envision Portal Modules
 *********************************************/
$helptxt['ep_module_link'] = 'Sets the link for the title of this module as well as the target attribute for the link.  If empty, title of module will not have a link associated with it.  If you do not define <strong>http://</strong> or <strong>www.</strong> than links will be relative to <strong>index.php?</strong>.<br />For Example: To go to the SMF Help Menu, type in <strong>action=help</strong> or, if you want a link outside of your forum\'s site, For Example, to link to Envision Portal\'s site: <strong>http://</strong>envisionportal.net or <strong>www.</strong>envisionportal.net';
