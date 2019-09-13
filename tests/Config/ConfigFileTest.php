<?php

namespace ExodusTests;

use Exodus\Config\ConfigFile;
use Exodus\Database\Adapter;
use Exodus\Database\Adapter\Postgres as PostgresAdapter;
use Exodus\Database\Adapter\Factory as AdapterFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Exodus\Config\ConfigFile.
 */
class ConfigFileTest extends TestCase
{
    /**
     * @group config.config_file
     */
    public function testGetMigrationDirCorrectlyReturnsMigrationDirectoryIfItExistsInConfigFile()
    {
        $contents = [
            'migration_dir' => 'database/migrations/'
        ];

        $db_adapter_factory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = new ConfigFile([
            'contents'           => $contents,
            'db_adapter_factory' => $db_adapter_factory
        ]);

        // getMigrationDir trims forward slash on the right
        $this->assertEquals('database/migrations', $config_file->getMigrationDir());
    }

    /**
     * @expectedException \Exodus\Exception\UndefinedConfigParamException
     * @group config.config_file
     */
    public function testGetMigrationDirThrowsExceptionIfMigrationDirDoesNotExist()
    {
        $contents = [];

        $db_adapter_factory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = new ConfigFile([
            'contents'           => $contents,
            'db_adapter_factory' => $db_adapter_factory
        ]);

        $config_file->getMigrationDir();
    }

    /**
     * @group config.config_file
     */
    public function testGetMigrationTableReturnsMigrationTableNameIfItExistsInConfigFile()
    {
        $contents = [
            'migration_table' => 'migrations'
        ];

        $db_adapter_factory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = new ConfigFile([
            'contents'           => $contents,
            'db_adapter_factory' => $db_adapter_factory
        ]);

        $this->assertEquals('migrations', $config_file->getMigrationTable());
    }

    /**
     * @expectedException \Exodus\Exception\UndefinedConfigParamException
     * @group config.config_file
     */
    public function testGetMigrationTableThrowsExceptionIfMigrationTableDoesNotExist()
    {
        $contents = [];

        $db_adapter_factory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = new ConfigFile([
            'contents'           => $contents,
            'db_adapter_factory' => $db_adapter_factory
        ]);

        $config_file->getMigrationTable();
    }

    /**
     * @group config.config_file
     */
    public function testGetDbAdapterReturnsAnInstanceOfDbAdapterIfOneWasCorrectlyDefinedInConfigFile()
    {
        $contents = [
            'db' => [
                'adapter' => 'postgresql'
            ]
        ];

        $db_adapter = $this->getMockBuilder(PostgresAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db_adapter_factory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db_adapter_factory->method('getDbAdapter')
            ->will($this->returnValue($db_adapter));

        $config_file = new ConfigFile([
            'contents'           => $contents,
            'db_adapter_factory' => $db_adapter_factory
        ]);

        $this->assertInstanceOf(Adapter::class, $config_file->getDbAdapter());
    }

    /**
     * @expectedException \Exodus\Exception\UndefinedConfigParamException
     * @group config.config_file
     */
    public function testGetDbAdapterThrowsAnExceptionIfNoAdapterIsDefinedInConfigFile()
    {
        $contents = [
            'db' => [
                'adapter' => ''
            ]
        ];

        $db_adapter_factory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = new ConfigFile([
            'contents'           => $contents,
            'db_adapter_factory' => $db_adapter_factory
        ]);

        $config_file->getDbAdapter();
    }
}