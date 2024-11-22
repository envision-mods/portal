<?php
// Version 1.0; ManageEnvisionModules

/*

Note:  All Help Strings for modules are located in EnvisionHelp.[language].php and EnvisionHelp.[language]-utf8.php

For the Module Title => $helptxt['ep_(name value in ep_modules table)];
For the Module Parameter => $helptxt['ep_(name value in ep_modules table)_(parameter_name in ep_modules']['parameters table)];

*/

// Just the basics.
$txt['ep_admin_title_manage_modules'] = 'Envision Portal - Manage Modules';
$txt['ep_admin_title_add_modules'] = 'Envision Portal - Add Modules';
$txt['ep_admin_modules_desc'] = 'This page allows you to add modules, edit currently installed modules, and change the layout of the main page.';
$txt['ep_admin_modules_help'] = 'Here you can edit the positions and settings of modules. To move modules around you can click and drag. All modules can be duplicated by clicking \'Clone\'. Each module has its own settings and clones have their own settings. There is a quick color changer on the right that you can use to change the white color on modules to something else, such as blue or black. Please note that only the modules displayed on this page change color and the modules displayed on the home page are not affected.';

/***************************
 * Manage Modules
 ***************************/
$txt['ep_admin_modules_manmodules_desc'] = 'Manage the Default Envision Portal Modules Layout. You can drag modules into any of the enabled columns and/or disable them by placing them into the Disabled Modules column.  Modify the Parameters of these modules by clicking on the Modify link. Unchecking sections will disable them from the layout/action.';
$txt['ep_admin_modules_manmodules_head'] = '<strong style="font-size: 16px;">Default Envision Portal Modules Layout</strong><div style="font-size: 12px;">Drag &amp; Drop modules anywhere</div>';
$txt['ep_admin_modules_manage_col_disabled'] = 'Disabled Modules';
$txt['ep_admin_modules_manage_col_section'] = 'Section';
$txt['ep_is_smf_section'] = 'SMF Container';
$txt['ep_admin_modules_manage_modify'] = 'Modify';
$txt['error_string'] = 'Error';
$txt['module_positions_saved'] = 'The module positions have been saved.';
$txt['click_to_close'] = 'Click to close this message.';

/***************************
 * Modify Modules
 ***************************/
// General Strings
$txt['ep_modify_mod'] = 'Envision Portal - Modify Modules';
$txt['ep_modsettings'] = 'Settings';
$txt['ep_modules']['default']['module_title']['title'] = 'Module&#039;s Title';
$txt['ep_modules']['default']['module_title']['desc'] = 'Should not be empty';
$txt['ep_modules']['default']['module_icon']['title'] = 'Module&#039;s Icon';
$txt['ep_modules']['default']['module_link']['title'] = 'Module&#039;s Title Link';
$txt['ep_modules']['default']['module_target']['title'] = 'Module&#039;s target';
$txt['ep_modules']['default']['module_target']['_self'] = 'Same window';
$txt['ep_modules']['default']['module_target']['_parent'] = 'Parent';
$txt['ep_modules']['default']['module_target']['_blank'] = 'New window';
$txt['no_icon'] = '(no icon)';
$txt['ep_modules']['default']['module_groups']['title'] = 'Membergroups that can view this module';
$txt['ep_modules']['default']['module_header_display']['title'] = 'Module&#039;s Header';
$txt['ep_modules']['default']['module_header_display']['disable'] = 'Disable';
$txt['ep_modules']['default']['module_header_display']['enabled'] = 'Enabled';
$txt['ep_modules']['default']['module_header_display']['collapse'] = 'Title Only';

// Checklist order handling...
$txt['checks_order_up'] = 'Up';
$txt['checks_order_down'] = 'Down';

/*
----------------------------------
    Specific Module Settings
----------------------------------
*/

$txt['ep_modules']['announce']['title'] = 'Announcement';
$txt['ep_modules']['announce']['msg']['title'] = 'Announcement Message';
$txt['ep_modules']['announce']['msg']['desc'] = 'Will load up Envision Portal\'s default welcome message if this is left blank.';

$txt['ep_modules']['user_cp']['title'] = 'User Panel';

$txt['ep_modules']['stats']['title'] = 'Statistics';
$txt['ep_modules']['stats']['stat_choices']['title'] = 'Stats Choice List';
$txt['ep_modules']['stats']['stat_choices']['members'] = 'Total Members';
$txt['ep_modules']['stats']['stat_choices']['posts'] = 'Total Posts';
$txt['ep_modules']['stats']['stat_choices']['topics'] = 'Total Topics';
$txt['ep_modules']['stats']['stat_choices']['categories'] = 'Total Categories';
$txt['ep_modules']['stats']['stat_choices']['boards'] = 'Total Boards';
$txt['ep_modules']['stats']['stat_choices']['ontoday'] = 'Most Online Today';
$txt['ep_modules']['stats']['stat_choices']['onever'] = 'Most Online Ever';

$txt['ep_modules']['online']['title'] = 'Who&#039;s Online';
$txt['ep_modules']['online']['show_online']['title'] = 'Show Online';
$txt['ep_modules']['online']['show_online']['users'] = 'Users';
$txt['ep_modules']['online']['show_online']['buddies'] = 'Buddies';
$txt['ep_modules']['online']['show_online']['guests'] = 'Guests';
$txt['ep_modules']['online']['show_online']['hidden'] = 'Hidden';
$txt['ep_modules']['online']['show_online']['spiders'] = 'Spiders';
$txt['ep_modules']['online']['online_groups']['title'] = 'Online Groups';

$txt['ep_modules']['news']['title'] = 'Site News';
$txt['ep_modules']['news']['board']['title'] = 'Select Board';
$txt['ep_modules']['news']['limit']['title'] = 'Number of topics';
$txt['ep_modules']['news']['limit']['desc'] = '0 = default value';

$txt['ep_modules']['recent']['title'] = 'Recent Topics';
$txt['ep_modules']['recent']['num_recent']['title'] = 'Number of topics';
$txt['ep_modules']['recent']['num_recent']['desc'] = '0 = default value';
$txt['ep_modules']['recent']['prop']['title'] = 'Fetch mode';
$txt['ep_modules']['recent']['prop'][0] = 'Exclude these boards';
$txt['ep_modules']['recent']['prop'][1] = 'Include these boards';
$txt['ep_modules']['recent']['boards']['title'] = 'Boards';

$txt['ep_modules']['search']['title'] = 'Search';
$txt['ep_modules']['calendar']['title'] = 'Calendar';
$txt['ep_modules']['calendar']['display_options']['title'] = 'Display Options';
$txt['ep_modules']['calendar']['display_options_month']['title'] = 'Monthly Grid';
$txt['ep_modules']['calendar']['display_options']['grid'] = 'Show Dates';
$txt['ep_modules']['calendar']['display_options']['events'] = 'Events';
$txt['ep_modules']['calendar']['display_options']['holidays'] = 'Holidays';
$txt['ep_modules']['calendar']['display_options']['birthdays'] = 'Birthdays';

$txt['ep_modules']['poll']['title'] = 'Poll';
$txt['ep_modules']['poll']['options']['title'] = 'Poll Options';
$txt['ep_modules']['poll']['options']['def'] = 'Based on Topic Id';
$txt['ep_modules']['poll']['options']['recent'] = 'Most Recent Poll';
$txt['ep_modules']['poll']['topic']['title'] = 'Topic ID<div class="smalltext">0 = disabled</div>';
$txt['ep_modules']['poll']['topic']['desc'] = 'The topic id to get the poll in this module will come from.  Only gets displayed if "Based on Topic Id" was selected as the Poll option.  If set to 0, no content will be displayed.';

$txt['ep_modules']['top_posters']['title'] = 'Top Posters';
$txt['ep_modules']['top_posters']['list_type']['title'] = 'List Style';
$txt['ep_modules']['top_posters']['list_type'][0x1] = 'Avatars';
$txt['ep_modules']['top_posters']['list_type'][0x2] = 'Custom Titles';
$txt['ep_modules']['top_posters']['list_type'][0x4] = 'Group names (not post groups)';
$txt['ep_modules']['top_posters']['list_type'][0x8] = 'Number of Posts';
$txt['ep_modules']['top_posters']['num_posters']['title'] = 'Number of Posters to show';

$txt['ep_modules']['staff']['title'] = 'Forum Staff';
$txt['ep_modules']['staff']['groups']['title'] = 'Listed Groups';
$txt['ep_modules']['staff']['grouping']['title'] = 'Staff Title';
$txt['ep_modules']['staff']['grouping'][0] = 'No Grouping';
$txt['ep_modules']['staff']['grouping'][1] = 'Group Name';
$txt['ep_modules']['staff']['list_type']['title'] = 'List Style';
$txt['ep_modules']['staff']['list_type'][0x1] = 'Avatars';
$txt['ep_modules']['staff']['list_type'][0x2] = 'Custom Titles';
$txt['ep_modules']['staff']['list_type'][0x4] = 'Location';
$txt['ep_modules']['staff']['list_type'][0x8] = 'Blurb (personal text)';
$txt['ep_modules']['staff']['list_type'][0x10] = 'Website';
$txt['ep_modules']['staff']['custom_fields']['title'] = 'Custom Profile Fields';

$txt['ep_modules']['theme_select']['title'] = 'Theme Changer';

$txt['ep_modules']['new_members']['title'] = 'Latest Members';
$txt['ep_modules']['new_members']['list_type']['title'] = 'List Style';
$txt['ep_modules']['new_members']['list_type'][0x1] = 'Avatars';
$txt['ep_modules']['new_members']['list_type'][0x2] = 'Registration date';
$txt['ep_modules']['new_members']['list_type'][0x4] = 'Number of Posts';
$txt['ep_modules']['new_members']['limit']['title'] = 'Number of members';

$txt['ep_modules']['sitemenu']['title'] = 'Site Navigation';
$txt['ep_modules']['sitemenu']['onesm']['title'] = 'Enable Multiple Menu Expansions';

$txt['ep_modules']['custom']['title'] = 'Custom Module';
$txt['ep_modules']['custom']['code']['title'] = 'Code';
$txt['ep_modules']['custom']['code_type']['title'] = 'Code Type';
$txt['ep_modules']['custom']['code_type']['0'] = 'PHP';
$txt['ep_modules']['custom']['code_type']['1'] = 'HTML';
$txt['ep_modules']['custom']['code_type']['2'] = 'BBC';

// Brief Module Information.  Shows a bit of info on the module just below the title.
$txt['ep_modules']['new_members']['info'] = 'Shows the most recently registered members on your forum.';
$txt['ep_modules']['announce']['info'] = 'Show an Announcement of any kind to all of your members and/or guests for any reason.';
$txt['ep_modules']['usercp']['info'] = 'Just your basic, everyday, User Control Panel (UCP).';
$txt['ep_modules']['stats']['info'] = 'Bunch of Statistics on your forum.';
$txt['ep_modules']['online']['info'] = 'Show Who&#039;s online at your forum.';
$txt['ep_modules']['news']['info'] = 'Grabs Topics from any board you specify in your forum and displays them as News.';
$txt['ep_modules']['recent']['info'] = 'Gets the most Recent Topics or Posts on your forum.';
$txt['ep_modules']['search']['info'] = 'Allows users to search conveniently from anywhere the module gets displayed at.';
$txt['ep_modules']['calendar']['info'] = 'A nifty little, and useful Calendar to keep track of Events, Birthdays, and/or Holidays.';
$txt['ep_modules']['poll']['info'] = 'Allows you to specify a Poll (with the topic id) to display for everyone to vote on.';
$txt['ep_modules']['top_posters']['info'] = 'Displays a list of Top Posters at your forum.';
$txt['ep_modules']['staff']['info'] = 'Lists Staff Members on your forum in an organized fashion.';
$txt['ep_modules']['theme_select']['info'] = 'Allows users to quickly and easily change their theme from anywhere this module gets displayed at.';
$txt['ep_modules']['shoutbox']['info'] = 'Gives your users the ability to engage in a live chat while browsing your forum.';
$txt['ep_modules']['custom']['info'] = 'Code that you can put into a module. There are three types to pick from: PHP, HTML, or BBC (Bulletin Board Code).';

$txt['add_layout_title'] = 'Envision Portal - Add Layout';
$txt['add_layout'] = 'Add Layout';
$txt['ep_layout_name'] = 'Layout Name';

$txt['ep_action_type'] = 'Actions';
$txt['select_smf_actions'] = 'Available SMF Actions';
$txt['select_user_defined_actions'] = 'User-defined';
$txt['select_user_defined_actions_desc'] = 'Encase any non-actions within brackets. For example: <i><strong>[board]</strong>, and <strong>[topic]</strong> will point to index.php?board and index.php?topic</i>';

$txt['ep_add_action'] = 'Add Action';
$txt['ep_remove_action'] = 'Remove Action';
$txt['layout_actions'] = 'Layout Actions';
$txt['layout_style'] = 'Layout Style';
$txt['layout_style_ep_'] = 'Default - Envision Portal';
$txt['layout_style_omega'] = 'Omega';
$txt['layout_sections'] = 'Layout Sections';
$txt['layout_sections_desc'] = 'Click on a section below to highlight it.';

$txt['edit_layout'] = 'Edit Layout';
$txt['edit_layout_title'] = 'Envision Portal - Edit Layout';

$txt['delete_layout'] = 'Delete Layout';
$txt['confirm_delete_layout'] = 'Are you sure you want to delete the selected layout?';
$txt['no_layout_selected'] = 'Sorry, this layout doesn\'t exist.';
$txt['select_layout_to_delete'] = 'Select a layout to delete';

// Layout Errors
$txt['layout_error_header'] = 'The following error or errors occurred while adding your layout:';
$txt['edit_layout_error_header'] = 'The following error or errors occurred while editing your layout:';
$txt['ep_no_actions'] = 'No actions were defined within this layout.';
$txt['ep_no_sections'] = 'No sections were defined within this layout.';
$txt['ep_layout_exists'] = 'That layout name is already in use.';
$txt['ep_no_layout_name'] = 'Your layout must have a name.';
$txt['ep_section_error'] = '%s at section %d can only accept digits (be a whole number).';

$txt['ep_smf_mod'] = 'SMF Content';
$txt['ep_edit'] = 'Edit';
$txt['ep_deleted'] = 'Deleted';
$txt['ep_restore'] = 'Restore';
$txt['ep_submit'] = 'Submit';
$txt['ep_cancel'] = 'Cancel';
$txt['ep_add_another'] = 'Add Another';
$txt['new_value'] = 'New Value';

// EDITING a LAYOUT...
$txt['ep_column'] = 'Column';
$txt['ep_colspan'] = 'Colspan';
$txt['ep_row'] = 'Row';
$txt['ep_rowspan'] = 'Rowspan';
$txt['enabled'] = 'Enabled';
$txt['ep_columns_header'] = 'Columns';
$txt['ep_add_column'] = 'Add a column at the end of';
$txt['ep_add_column_button'] = 'Add Column';
$txt['ep_add_row'] = 'Add Row';
$txt['confirm_remove_selected'] = 'Are you sure you want to remove the selected rows and/or columns?\n\nNote: The row that SMF is defined in can not be removed and there must always be atleast one other column besides SMF.  All empty rows will be removed also.';
$txt['ep_not_enough_columns'] = 'There must always be atleast one other column besides SMF.';
$txt['ep_cannot_remove_selected'] = 'The row that SMF is defined in cannot be removed.';
$txt['ep_edit_remove_selected'] = 'Remove Selected';

// Errors
$txt['ep_cannot_modify_module'] = 'The module you are looking for does not exist.';
$txt['cant_find_layout_id'] = 'Unable to edit this layout, either because there was no layout ID value supplied, or this was empty.';
$txt['ep_cant_delete_all'] = 'Sorry, you are unable to delete all columns and/or rows.  If this was your intention, than just Delete the Layout instead!';
$txt['ep_layout_invalid'] = 'Unable to edit this layout because of an inconsistent number of columns in each row.';
