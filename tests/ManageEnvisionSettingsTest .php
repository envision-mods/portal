<?php

use PHPUnit\Framework\TestCase;

class ManageEnvisionSettingsTest extends TestCase
{
	private EnvisionPortal\ActionInterfcace $instance;

	public function setUp()
	{
		$this->instance = EnvisionPortal\ManageEnvisionSettings::load();
	}

	public function testGetConfigVarsReturnsArray()
	{
		$configVars = ManageEnvisionSettings::getConfigVars();

		$this->assertIsArray($configVars);
		$this->assertNotEmpty($configVars);
	}

	public function testEpGeneral()
	{
		global $context;

		$this->instance->epgeneral();

		$this->assertArrayHasKey('sub_template', $context);
		$this->assertArrayHasKey('post_url', $context);
		$this->assertArrayHasKey('settings_title', $context);
		$this->assertArrayHasKey('page_title', $context);
	}

	public function testEpInfo()
	{
		global $context;

		$this->instance->epinfo();

		$this->assertArrayHasKey('credits', $context);
		$this->assertArrayHasKey('page_title', $context);
		$this->assertArrayHasKey('sub_template', $context);
	}
}
