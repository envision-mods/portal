<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal\PageModes;

use EnvisionPortal\PageModeInterface;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use SimonVomEyser\CommonMarkExtension\LazyImageExtension;

class Markdown implements PageModeInterface
{
	public function parse(string $body): string
	{
		loadTemplate(false, 'ep_css/github-markdown');
		$config = [
			'html_input' => 'strip',
			'allow_unsafe_links' => false,
			'external_link' => [
				'internal_hosts' => $GLOBALS['boardurl'],
				'open_in_new_window' => true,
				'html_class' => 'external-link',
			],
		];
		$environment = new Environment($config);
		$environment->addExtension(new CommonMarkCoreExtension);
		$environment->addExtension(new GithubFlavoredMarkdownExtension);
		$environment->addExtension(new ExternalLinkExtension);
		$environment->addExtension(new HeadingPermalinkExtension);
		$environment->addExtension(new LazyImageExtension);
		$converter = new MarkdownConverter($environment);

		return '<div class="markdown-body">' . $converter->convertToHtml(strtr($body, ['<br>' => "\n"])) . '</div>';
	}

	public function getMode(): string
	{
		return 'markdown';
	}
}