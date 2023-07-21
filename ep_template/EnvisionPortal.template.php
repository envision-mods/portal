<?php

function template_portal_above(bool $from_below = false)
{
	global $context;

	$below_smf = false;

	echo '
			<div id="ep_main">';

	foreach ($context['ep_cols'] as $col) {
		printf(
			'
			<div class="ep_%s" style="--col: %d / %d; --row: %d / %d;">',
			$col['is_smf'] ? 'smf' : 'col',
			$col['y'] + 1,
			$col['colspan'] + $col['y'] + 1,
			$col['x'] + 1,
			$col['rowspan'] + $col['x'] + 1
		);

		if ($col['is_smf']) {
			break;
		} elseif ($col['modules'] != []) {
			template_module_column($col['modules']);
		}

		echo '
			</div>';
	}
}

function template_portal()
{
}

function template_portal_below()
{
	global $context;

	$below_smf = false;

	foreach ($context['ep_cols'] as $col) {
		if ($col['is_smf']) {
			echo '
			</div>';
			$below_smf = true;
		} elseif ($below_smf) {
			printf(
				'
			<div class="ep_col" style="--col: %d / %d; --row: %d / %d;">',
				$col['y'] + 1,
				$col['colspan'] + $col['y'] + 1,
				$col['x'] + 1,
				$col['rowspan'] + $col['x'] + 1
			);

			if ($col['modules'] != []) {
				template_module_column($col['modules']);
			}

			echo '
			</div>';
		}
	}


	echo '
		</div>
		<link rel="stylesheet" type="text/css" href="', $context['module_icon_url'], '/fugue-sprite.css" />
		<script type="text/javascript" src="' . $GLOBALS['settings']['default_theme_url'] . '/scripts/ep_scripts/envisionportal.js"></script>
		<script>
			initModuleToggles(', $context['user']['is_guest'] ? 'true' : 'false', ', ', JavaScriptEscape(
		$context['session_id']
	), ', ', JavaScriptEscape($context['session_var']), ');
		</script>';
}

function template_module_column($column)
{
	global $modSettings, $settings, $txt;

	foreach ($column as $module) {
		if ($module['header_display'] != 1) {
			echo '
			<div class="cat_bar" data-id="', $module['id'], '" data-collapsed="', $module['is_collapsed'] ? '1' : '0', '">
				<h3 class="', !empty($modSettings['ep_collapse_modules']) && $module['header_display'] != 2 ? 'ep_upshrink' : '', ' catbg">
					', $module['module_icon'] ?? '', $module['module_title'], '
				</h3>
			</div>';
		}

		printf(
			'
			<span class="upperframe"><span></span></span>
			<div class="ep_module_%s roundframe">%s</div>
			<span class="lowerframe"><span></span></span>',
			$module['type'],
			$module['class']
		);
	}
}

function template_envision_pages()
{
	global $context;

	echo '
					<div class="cat_bar">
						<h3 class="catbg">
							', $context['page_title_html_safe'], '
						</h3>
					</div>
					<span class="upperframe"><span></span></span>
						<div class="roundframe">
							', $context['page_data']['body'], '
						</div>
					<span class="lowerframe"><span></span></span>
				';
}

function template_view_shouts()
{
	global $context, $txt;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['shoutbox_view_msg'], '
				</h3>
			</div>';

	echo '
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">';

	if (empty($context['shouts'])) {
		echo '
			', $txt['shoutbox_no_msg'];
	} else {
		foreach ($context['shouts'] as $shout) {
			echo $shout;
		}
	}

	echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>';
}
