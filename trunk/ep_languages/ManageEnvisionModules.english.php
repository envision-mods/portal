<?php
// Version 1.0; ManageEnvisionModules

/*

Note:  All Help Strings for modules are located in EnvisionHelp.[language].php and EnvisionHelp.[language]-utf8.php

For the Module Title => $helptxt['ep_(name value in ep_modules table)];
For the Module Parameter => $helptxt['ep_(name value in ep_modules table)_(parameter_name in ep_module_parameters table)];

*/

// Just the basics.
$txt['ep_admin_modules'] = 'Envision Portal Modules';
$txt['ep_admin_title_manage_modules'] = 'Envision Portal - Manage Modules';
$txt['ep_admin_title_add_modules'] = 'Envision Portal - Add Modules';
$txt['ep_admin_modules_desc'] = 'This page allows you to add modules, edit currently installed modules, and change the layout of the main page.';
$txt['ep_admin_modules_help'] = 'Here you can edit the positions and settings of modules. To move modules around you can click and drag. All modules can be duplicated by clicking \'Clone\'. Each module has its own settings and clones have their own settings. There is a quick color changer on the right that you can use to change the white color on modules to something else, such as blue or black. Please note that only the modules displayed on this page change color and the modules displayed on the home page are not affected.';

/***************************
		Manage Modules
***************************/
$txt['ep_admin_modules_manmodules_desc'] = 'Manage the Default Envision Portal Modules Layout. You can drag modules into any of the enabled columns and/or disable them by placing them into the Disabled Modules column.  Modify the Parameters of these modules by clicking on the Modify link. Unchecking sections will disable them from the layout/action.';
$txt['ep_admin_modules_manmodules_head'] = '<strong style="font-size: 16px;">Default Envision Portal Modules Layout</strong><div style="font-size: 12px;">Drag &amp; Drop modules anywhere</div>';
$txt['ep_admin_modules_manage_col_disabled'] = 'Disabled Modules';
$txt['ep_admin_modules_manage_col_section'] = 'Section';
$txt['ep_is_smf_section'] = 'SMF';
$txt['ep_admin_modules_manage_modify'] = 'Modify';
$txt['ep_admin_modules_manage_uninstall'] = '<span class="smalltext">Uninstall</span>';
$txt['epmodule_uninstall_success'] = 'The module was successfully uninstalled!';
$txt['error_string'] = 'Error';
$txt['module_positions_saved'] = 'The module positions have been saved.';
$txt['click_to_close'] = 'Click to close this message.';

/***************************
		Modify Modules
***************************/
// General Strings
/*
// Playing with an idea to split Options up when modifying modules into separate pages.  Not sure I like it.  Will get back to it...
$txt['ep_modify_section_general'] = 'General Settings';
$txt['ep_modify_section_custom'] = 'Custom Settings';
*/
$txt['ep_modify_mod'] = 'Envision Portal - Modify Modules';
$txt['ep_module_not_installed'] = 'Sorry, unable to retrieve the modules id value, please make sure this module is installed.';
$txt['ep_modsettings'] = '&nbsp;Settings';
$txt['ep_module_title'] = 'Module&#039;s Title<div class="smalltext">(Can not be empty)</div>';
$txt['ep_module_icon'] = 'Module&#039;s Icon';
$txt['ep_module_link'] = 'Module&#039;s Title Link';
$txt['ep_module_target'] = 'Module&#039;s target';
$txt['ep_module_target__self'] = 'Same window';
$txt['ep_module_target__parent'] = 'Parent';
$txt['ep_module_target__blank'] = 'New window';
$txt['no_icon'] = '(no icon)';
$txt['ep_module_template'] = 'Module&#039;s Template';
$txt['ep_module_groups'] = 'Membergroups that can view this module';
$txt['ep_module_header_display'] = 'Module&#039;s Header';
$txt['ep_module_header_display_disable'] = 'Disable';
$txt['ep_module_header_display_enabled'] = 'Enabled';
$txt['ep_module_header_display_collapse'] = 'Title Only';

// File error handling.
$txt['module_file_timeout'] = 'Sorry, file(s) timed-out while uploading.  Please try again.';
$txt['module_wrong_mime_type'] = 'You are not allowed to upload this mime-type: %1$s';
$txt['module_not_image_type'] = 'Valid image types are as follows: gif, jpg, jpe, jpeg, png, bmp, and/or wbmp';
$txt['module_file_limit'] = 'Sorry, you have reached the limit for file upload of this module settings.  Please go back and remove one of your uploads before you will be able to upload any more files.';
$txt['module_files_no_write'] = 'Unable to write to the modules directory.  Please make sure this path is writable!';
$txt['files'] = '<strong>Current Files:</strong>';
$txt['uncheck_unwanted_files'] = 'Uncheck files you no longer want associated with this setting';
$txt['mod_folder_missing'] = 'Unable to get the Module\'s folderpath, which should be located, relative to your SMF Root:';
$txt['module_folderpath_error'] = 'Unable to store files within this modules folderpath.';
$txt['restricted_unexists'] = 'Sorry, seems that either the file is restricted, or does not exist.';
$txt['file_timeout'] = 'File timed-out while uploading, please try again!';
$txt['file_bad_extension'] = 'Unable to upload that type of file.  Please try a different filetype.';

// Checklist order handling...
$txt['checks_order_up'] = 'Up';
$txt['checks_order_down'] = 'Down';

/*
----------------------------------
	Specific Module Settings
----------------------------------

	$txt indexes go like this:
	$txt['ep_module_(module name)]
	$txt['ep_module_(module name)_(parameter_name)]
*/

$txt['ep_module_announce'] = 'Announcement';
$txt['ep_module_announce_msg'] = 'Announcement Message';
$txt['ep_module_usercp'] = 'User Panel';
$txt['ep_module_stats'] = 'Statistics';
$txt['ep_module_stats_stat_choices'] = 'Stats Choice List';
$txt['ep_module_online'] = 'Who&#039;s Online';
$txt['ep_module_online_show_online'] = 'Show Online';
$txt['ep_module_online_online_pos'] = 'Online Position';
$txt['ep_module_online_online_groups'] = 'Online Groups';
$txt['ep_module_news'] = 'Site News';
$txt['ep_module_news_board'] = 'Select Board';
$txt['ep_module_news_limit'] = 'Limit<div class="smalltext">0 = default value</div>';
$txt['ep_module_recent'] = 'Recent Posts/Topics';
$txt['ep_module_recent_post_topic'] = 'Show Recent';
$txt['ep_module_recent_num_recent'] = 'Recent Quantity<div class="smalltext">0 = default value</div>';
$txt['ep_module_recent_show_avatars'] = 'Show Avatars';
$txt['ep_module_search'] = 'Search';
$txt['ep_module_calendar'] = 'Calendar';
$txt['ep_module_calendar_display'] = 'Display Options';
$txt['ep_module_calendar_animate'] = 'Animation Style';
$txt['ep_module_calendar_animate_none'] = 'Disabled';
$txt['ep_module_calendar_animate_horiz'] = 'Horizontal';
$txt['ep_module_calendar_display_month'] = 'Monthly Grid';
$txt['ep_module_calendar_display_info'] = 'Text-Based';
$txt['ep_module_calendar_show_months'] = 'Show all months';
$txt['ep_module_calendar_show_months_year'] = 'in this year';
$txt['ep_module_calendar_show_months_asdefined'] = 'as defined';
$txt['ep_module_calendar_previous'] = 'Previous Months';
$txt['ep_module_calendar_next'] = 'Next Months';
$txt['ep_module_calendar_show_options'] = 'Show Dates';
$txt['ep_module_calendar_show_options_events'] = 'Events';
$txt['ep_module_calendar_show_options_holidays'] = 'Holidays';
$txt['ep_module_calendar_show_options_birthdays'] = 'Birthdays';
$txt['ep_module_poll'] = 'Poll';
$txt['ep_module_poll_topic'] = 'Topic ID<div class="smalltext">0 = disabled</div>';
$txt['ep_module_top_posters'] = 'Top Posters';
$txt['ep_module_top_posters_show_avatar'] = 'Show Avatar';
$txt['ep_module_top_posters_show_postcount'] = 'Show Postcount';
$txt['ep_module_top_posters_num_posters'] = 'Number of Posters to show';
$txt['ep_module_staff'] = 'Forum Staff';
$txt['ep_module_staff_groups'] = 'Listed Groups';
$txt['ep_module_staff_list_type'] = 'List Style';
$txt['ep_module_staff_name_type'] = 'Staff Title';
$txt['ep_module_poll_options'] = 'Poll Options';
$txt['ep_module_theme_select'] = 'Theme Changer';
$txt['ep_module_new_members'] = 'Latest Members';
$txt['ep_module_new_members_limit'] = 'Number of members';
$txt['ep_module_new_members_list_type'] = 'List Style';
$txt['ep_module_sitemenu'] = 'Site Navigation';
$txt['ep_module_sitemenu_onesm'] = 'Enable Multiple Menu Expansions';
$txt['ep_module_shoutbox'] = 'Shoutbox';
$txt['ep_module_shoutbox_refresh_rate'] = 'Refresh Rate<div class="smalltext">In seconds, 0 = 500 milliseconds</div>';
$txt['ep_module_shoutbox_member_color'] = 'Display Membergroup Color<div class="smalltext">If unchecked, will revert to the master setting</div>';
$txt['ep_module_shoutbox_max_count'] = 'Maximum shout count<div class="smalltext">(0 = default value of 15)</div>';
$txt['ep_module_shoutbox_id'] = 'Shoutbox instance';
$txt['ep_module_shoutbox_bbc'] = 'Allowed BBC tags';
$txt['ep_module_shoutbox_max_chars'] = 'Maximum characters per shout<div class="smalltext">(0 = unlimited)</div>';
$txt['ep_module_shoutbox_text_size'] = 'Text Size';
$txt['ep_module_shoutbox_text_size_small'] = 'Small';
$txt['ep_module_shoutbox_text_size_medium'] = 'Medium';
$txt['ep_module_shoutbox_message'] = 'Notice';
$txt['ep_module_shoutbox_message_position'] = 'Notice Position';
$txt['ep_module_shoutbox_message_position_top'] = 'Before the shouts';
$txt['ep_module_shoutbox_message_position_after'] = 'After the shouts';
$txt['ep_module_shoutbox_message_position_bottom'] = 'On the bottom';
$txt['ep_module_shoutbox_message_groups'] = 'Groups that can view the notice';
$txt['ep_module_shoutbox_mod_own'] = 'Moderate Own Shouts';
$txt['ep_module_shoutbox_mod_groups'] = 'Moderate All Shouts';
$txt['ep_module_custom'] = 'Custom Module';
$txt['ep_module_custom_code'] = 'Code';
$txt['ep_module_custom_code_type'] = 'Code Type';

// Brief Module Information.  Shows a bit of info on the module just below the title.
/*
	$txt indexes go like this:
	$txt['ep_module_info_(module name)]
*/

$txt['ep_module_info_new_members'] = 'Shows the most recently registered members on your forum.';
$txt['ep_module_info_announce'] = 'Show an Announcement of any kind to all of your members and/or guests for any reason.';
$txt['ep_module_info_usercp'] = 'Just your basic, everyday, User Control Panel (UCP).';
$txt['ep_module_info_stats'] = 'Bunch of Statistics on your forum.';
$txt['ep_module_info_online'] = 'Show Who&#039;s online at your forum.';
$txt['ep_module_info_news'] = 'Grabs Topics from any board you specify in your forum and displays them as News.';
$txt['ep_module_info_recent'] = 'Gets the most Recent Topics or Posts on your forum.';
$txt['ep_module_info_search'] = 'Allows users to search conveniently from anywhere the module gets displayed at.';
$txt['ep_module_info_calendar'] = 'A nifty little, and useful Calendar to keep track of Events, Birthdays, and/or Holidays.';
$txt['ep_module_info_poll'] = 'Allows you to specify a Poll (with the topic id) to display for everyone to vote on.';
$txt['ep_module_info_top_posters'] = 'Displays a list of Top Posters at your forum.';
$txt['ep_module_info_staff'] = 'Lists Staff Members on your forum in an organized fashion.';
$txt['ep_module_info_theme_select'] = 'Allows users to quickly and easily change their theme from anywhere this module gets displayed at.';
$txt['ep_module_info_shoutbox'] = 'Gives your users the ability to engage in a live chat while browsing your forum.';
$txt['ep_module_info_custom'] = 'Code that you can put into a module. There are three types to pick from: PHP, HTML, or BBC (Bulletin Board Code).';

// Select Options
/*
	$txt indexes go like this:
	$txt['ep_module_(module name)_(parameter_name)_(each value in the parameter_value column after : and separated by a semicolon)]

	For Example, If the parameter_value contains the following:
				1:option1;option2;option3
	Than you'll need 3 txt definitions for after the last underscore ( _option1, _option2, and _option3 should be the last values of the $txt[] index ).
	Note: The 1: means that it will load the array bound of 1 as the default value.  So in this case, option2 will be the default value.
*/

// Recent Topics/Posts module settings.
$txt['ep_module_post_topic_posts'] = 'Posts';
$txt['ep_module_post_topic_topics'] = 'Topics';

// Forum Staff module settings.
$txt['ep_module_staff_name_type_0'] = 'Group Name';
$txt['ep_module_staff_name_type_1'] = 'Custom Title';
$txt['ep_module_staff_name_type_2'] = $txt['ep_module_staff_name_type_1'] . ' or ' . $txt['ep_module_staff_name_type_0'];
$txt['ep_module_staff_list_type_0'] = 'Names Only';
$txt['ep_module_staff_list_type_1'] = 'Avatars Only';
$txt['ep_module_staff_list_type_2'] = 'Names and Avatars';

// Latest Members
$txt['ep_module_new_members_list_type_0'] = 'Names Only';
$txt['ep_module_new_members_list_type_1'] = 'Avatars Only';
$txt['ep_module_new_members_list_type_2'] = 'Names and Avatars';

// Poll Options
$txt['ep_module_poll_options_showPoll'] = 'Based on Topic Id';
$txt['ep_module_poll_options_topPoll'] = 'Most Rated Poll';
$txt['ep_module_poll_options_recentPoll'] = 'Most Recent Poll';

// Statistics Options (using the checklist parameter type)
$txt['ep_module_stats_stat_choices_members'] = 'Total Members';
$txt['ep_module_stats_stat_choices_posts'] = 'Total Posts';
$txt['ep_module_stats_stat_choices_topics'] = 'Total Topics';
$txt['ep_module_stats_stat_choices_categories'] = 'Total Categories';
$txt['ep_module_stats_stat_choices_boards'] = 'Total Boards';
$txt['ep_module_stats_stat_choices_ontoday'] = 'Most Online Today';
$txt['ep_module_stats_stat_choices_onever'] = 'Most Online Ever';

// Who's Online Options
$txt['ep_module_online_show_online_users'] = 'Users';
$txt['ep_module_online_show_online_buddies'] = 'Buddies';
$txt['ep_module_online_show_online_guests'] = 'Guests';
$txt['ep_module_online_show_online_hidden'] = 'Hidden';
$txt['ep_module_online_show_online_spiders'] = 'Spiders';

$txt['ep_module_online_online_pos_top'] = 'Top';
$txt['ep_module_online_online_pos_bottom'] = 'Bottom';

// Custom Code Options
$txt['ep_module_custom_code_type_0'] = 'PHP';
$txt['ep_module_custom_code_type_1'] = 'HTML';
$txt['ep_module_custom_code_type_2'] = 'BBC';

// Add Modules Page
$txt['ep_admin_modules_addmodules_desc'] = 'This page lists all uploaded modules and gives the option to upload a new module.';

$txt['module_name'] = 'Module\'s name';
$txt['module_description'] = 'Module\'s description';
$txt['module_version'] = 'Version';
$txt['module_install'] = 'Install';
$txt['module_settings'] = 'Settings';
$txt['module_uninstall'] = 'Uninstall';
$txt['module_delete'] = 'Delete';

$txt['add_layout_title'] = 'Envision Portal - Add Layout';
$txt['add_layout'] = 'Add Layout';
$txt['ep_layout_name'] = 'Layout Name';

$txt['ep_action_type'] = 'Actions';
$txt['select_smf_actions'] = 'Available SMF Actions';
$txt['select_user_defined_actions'] = 'User-defined';
$txt['select_user_defined_actions_desc'] = 'Encase any non-actions within brackets. For example: <i><strong>[board]</strong>, and <strong>[topic]</strong> will point to index.php?board and index.php?topic</i>';

$txt['ep_add_action'] = 'Add Action';
$txt['ep_remove_actions'] = 'Remove Action(s)';
$txt['layout_actions'] = 'Layout Actions';
$txt['layout_style'] = 'Layout Style';
$txt['layout_style_ep_'] = 'Default - Envision Portal';
$txt['layout_style_omega'] = 'Omega';
$txt['layout_sections'] = 'Layout Sections';

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
$txt['ep_layout_unknown'] = 'Unable to determine the layout type you selected.  Please go back and select your layout.';

$txt['ep_smf_mod'] = 'SMF Content';
$txt['ep_edit'] = 'Edit';
$txt['ep_deleted'] = 'Deleted';
$txt['ep_restore'] = 'Restore';
$txt['ep_submit'] = 'Submit';
$txt['ep_cancel'] = 'Cancel';
$txt['ep_add_another'] = 'Add Another';
$txt['new_value'] = 'New Value';

// Add Module txt strings.
$txt['no_modules'] = 'No Modules exist.  You\'ll have to upload a Module if you want to install any.';
$txt['ep_upload_module'] = 'Upload a Module';
$txt['module_to_upload'] = 'Module to Upload:';
$txt['module_upload'] = 'Upload';

// Module Upload Errors
$txt['ep_module_upload_no_file'] = 'You must select a module to upload into the filepath.';
$txt['module_upload_error_failure'] = 'Sorry, seems there was a problem uploading the file.';
$txt['module_upload_error_type'] = 'Sorry, only zip and tar.gz archives are supported.';
$txt['module_package_corrupt'] = 'Sorry, this module is corrupt and can not be installed.';
$txt['module_has_no_name'] = 'Sorry, it\'s required that all modules have a name, you can not install a module without a name';
$txt['module_restricted_name'] = 'Sorry, unable to add this module due to, either, an invalid name, no name given, or a name that already exists.';
$txt['module_has_no_files'] = 'This module doesn\'t have any script files associated with it and can not be installed without one.';
$txt['module_has_no_main_function'] = 'This module is either missing the main function for output or has more than 1 main function defined, in either case, it can not be installed.';
$txt['module_has_no_functions'] = 'This module doesn&#039;t have any functions associated with it and can not be installed.';
$txt['module_missing_files'] = 'Sorry, this module could not be installed because it is missing files associated with it.';
$txt['module_invalid_filename'] = 'Sorry, this module has an invalid filename associated with it and could not be added.';
$txt['file_missing_functions'] = 'This module is missing function definitions for one or more files and can not be added.';
$txt['invalid_function_name'] = 'This modules main function contains invalid characters and can not be installed.';
$txt['invalid_other_function_name'] = 'This module contains a function with an invalid name and can not be installed.';
$txt['module_has_file_defined_already'] = 'This module is attempting to define 2 files with the same exact filepath and can not be added.';
$txt['module_function_already_exists'] = 'This module is attempting to overwrite a function that you already have defined in SMF and can not be installed.';
$txt['module_function_duplicates'] = 'There is a module already installed that is using a function name that is specified within this module. You will have to uninstall the other module that is using this function name before you can install this module.';
$txt['invalid_language_filepath'] = 'This module could not be installed because one or more language filepaths are currently invalid.';
$txt['epamerr_unknown'] = 'Sorry, but an error occurred while attempting to upload your module.  Please try again.';
$txt['epamerr_UPLOAD_ERR_INI_SIZE'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
$txt['epamerr_UPLOAD_ERR_FORM_SIZE'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
$txt['epamerr_UPLOAD_ERR_PARTIAL'] = 'The uploaded file was only partially uploaded.  Please try again.';
$txt['epamerr_UPLOAD_ERR_NO_FILE'] = 'No file was uploaded.  Please make sure the file exists.';
$txt['epamerr_UPLOAD_ERR_NO_TMP_DIR'] = 'Unable to upload this file due to a missing temporary folder.';
$txt['epamerr_UPLOAD_ERR_CANT_WRITE'] = 'Failed to create this file on your server.  Please make sure that your ./envisionportal/modules directory has write access.';
$txt['epamerr_UPLOAD_ERR_EXTENSION'] = 'A PHP extension prevented this file from being uploaded onto your server, you can examine your list of loaded extensions using phpinfo(), which may help you to ascertain which extension could be causing this.';

// EDITING a LAYOUT...
$txt['ep_row'] = 'Row';
$txt['colspans'] = 'Colspans';
$txt['enabled'] = 'Enabled';
$txt['ep_columns_header'] = 'Columns';
$txt['ep_column'] = 'Column';
$txt['ep_add_column'] = 'Add a column at the end of';
$txt['ep_add_column_button'] = 'Add Column';
$txt['ep_add_row'] = 'Add Row';
$txt['confirm_remove_selected'] = 'Are you sure you want to remove the selected rows and/or columns?\n\nNote: The row that SMF is defined in can not be removed and there must always be atleast 1 other column besides SMF.  All empty rows will be removed also.';
$txt['ep_edit_remove_selected'] = 'Remove Selected';

// Errors
$txt['ep_cannot_modify_module'] = 'The module you are looking for does not exist.';
$txt['cant_find_layout_id'] = 'Unable to edit this layout, either because there was no layout ID value supplied, or this was empty.';
$txt['ep_cant_delete_all'] = 'Sorry, you are unable to delete all columns and/or rows.  If this was your intention, than just Delete the Layout instead!';
$txt['ep_layout_invalid'] = 'Unable to edit this layout because of an inconsistent number of columns in each row.';

?>
