<?php
// Version 1.0; EnvisionHelp

global $helptxt;

$helptxt['ep_collapse_modules_help'] = 'Sets ability collapse of the modules.';
$helptxt['ep_color_members_help'] = 'Enable/Disable showing of nicknames color based on membergroups color.';
$helptxt['ep_inline_copyright_help'] = 'Option to display the copyright on the same line as SMF\'s';
$helptxt['ep_module_display_style_help'] = 'Sets the current display for all modules appearance within Envision Portal.';
$helptxt['ep_module_enable_animationshelp'] = 'Enable or Disable module animations when expanding and collapsing a module.';
$helptxt['ep_module_animation_speed_help'] = 'Sets how fast the modules throughout Envision Portal will expand and collapse.  Note:  Module animations must be enabled for this to take effect.  The default is set at Normal.';
$helptxt['ep_icon_directory_help'] = 'The filepath for your icon directory is already relative to your SMF Root directory. Sets the directory from which to obtain all categories for Envision Module Icons. Categories are folders within the directory that you specify in here. After changing this folder path, you should update each of your enabled Modules with the correct icon you want for them by modifying the modules themselves. No need to input a backslash at the front or at the end of the filepath.';
$helptxt['ep_disable_custommod_icons_help'] = 'Sets whether or not to install all icons associated with Envision Modules that you install via Modules - Add Modules section.';
$helptxt['ep_enable_custommod_icons_help'] = 'Sets whether or not to uninstall all icons associated with Envision Modules when you Delete a Module via Modules - Add Modules section.  If unchecked, all module icons that are associated with modules, once deleted, will not be available for other modules as well.';
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
