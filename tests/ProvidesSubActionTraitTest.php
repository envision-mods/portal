<?php

use PHPUnit\Framework\TestCase;

class ProvidesSubActionTraitTest extends TestCase
{
	public function testSetDefaultSubAction()
	{
		$instance = new class {
			use EnvisionPortal\ProvidesSubActionTrait;
		};

		$instance->setDefaultSubAction('default_action');

		$this->assertEquals('default_action', $instance->getSubAction());
	}

	public function testAddAndHasSubAction()
	{
		$instance = new class {
			use EnvisionPortal\ProvidesSubActionTrait;
		};

		$instance->addSubAction('test_action', fn() => 'Test Passed');

		$this->assertTrue($instance->hasSubAction('test_action'));
	}

	public function testFindRequestedSubAction()
	{
		$instance = new class {
			use EnvisionPortal\ProvidesSubActionTrait;
		};

		$instance->addSubAction('action1', fn() => 'Action 1');
		$instance->addSubAction('action2', fn() => 'Action 2');

		$instance->findRequestedSubAction(null);
		$this->assertEquals('action1', $instance->getSubAction());
		$instance->setDefaultSubAction('action1');

		$instance->findRequestedSubAction('action2');
		$this->assertEquals('action2', $instance->getSubAction());

		$instance->findRequestedSubAction(null);
		$this->assertEquals('action2', $instance->getSubAction());
	}

	public function testCallSubAction()
	{
		$instance = new class {
			use EnvisionPortal\ProvidesSubActionTrait;
		};

		$instance->addSubAction('action1', fn() => 'Action Executed');

		$this->assertEquals('Action Executed', $instance->callSubAction('action1'));
	}
}
