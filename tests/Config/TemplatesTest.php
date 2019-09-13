<?php

namespace ExodusTests;

use Exodus\Config\Templates;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Exodus\Config\Templates.
 */
class TemplatesTest extends TestCase
{
	/**
	 * @group config.templates
	 */
	public function testGetConfigFilePathReturnsCorrectPathToConfigFileTemplate()
	{
		$templates = new Templates('root/tmp');

		$this->assertEquals('root/tmp/exodus.yml', $templates->getConfigFilePath());
	}

	/**
	 * @group config.templates
	 */
	public function testGetSqlTemplatePathReturnsCorrectPathToSQLTemplate()
	{
		$templates = new Templates('root/tmp');

		$this->assertEquals('root/tmp/postgres.sql', $templates->getSQLTemplatePath());
	}
}