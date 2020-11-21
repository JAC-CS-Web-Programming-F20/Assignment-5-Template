<?php

namespace AssignmentFiveTests\ModelTests;

use AssignmentFiveTests\AssignmentFiveTest;
use Faker\Factory;

class ModelTest extends AssignmentFiveTest
{
	public static function setUpBeforeClass(): void
	{
		self::$faker = Factory::create();
	}
}
