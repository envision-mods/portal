<?php

class ActionFixture implements EnvisionPortal\ActionInterface, Stringable
{
	use EnvisionPortal\ActionTrait;

	public string $var = '';

	public function execute(): void
	{
		$this->var = 'Action Executed';
	}

	public function __toString(): string
	{
		return $this->var;
	}
}
