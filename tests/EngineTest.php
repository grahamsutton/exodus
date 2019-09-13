<?php

namespace ExodusTests;

use Exodus\Engine;
use Exodus\Config\ConfigFile;
use Exodus\File\Handler as FileHandler;
use Exodus\Database\Strategy\Postgres as PostgresStrategy;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for Exodus\Engine.
 */
class EngineTest extends TestCase
{
    /**
     * @group engine
     */
    public function testMigrationTableIsCreatedIfItDoesNotExistYetOnFirstExecution()
    {
        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('isMigrationTableCreated')
            ->will($this->returnValue(false));

        $strategy->method('createMigrationTable')
            ->will($this->returnValue(null));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler
        ]);

        $this->assertTrue($engine->createMigrationTable());
    }

    /**
     * @group engine
     */
    public function testMigrationTableIsNotCreatedIfItAlreadyExists()
    {
        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('isMigrationTableCreated')
            ->will($this->returnValue(true));

        $strategy->method('createMigrationTable')
            ->will($this->returnValue(null));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler
        ]);

        $this->assertFalse($engine->createMigrationTable());
    }

    /**
     * @group engine
     */
    public function testGetMigrationsToRunReturnsAllPendingMigrationsIfItHasNotMigratedAnythingBefore()
    {
        $migration_dir = 'database/migrations';

        $migration_dir_nodes = [
            '.',
            '..',
            '0000000000_create_test_table1.sql',
            '0000000000_create_test_table2.sql',
            '0000000000_create_test_table3.sql',
            '0000000000_create_test_table4.sql',
        ];

        $migrations_ran = [];

        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file_handler->method('scanDir')
            ->will($this->returnValue($migration_dir_nodes));

        $file_handler->method('isFile')
            ->will($this->returnValueMap([
                [$migration_dir . '/.', false],
                [$migration_dir . '/..', false],
                [$migration_dir . '/0000000000_create_test_table1.sql', true],
                [$migration_dir . '/0000000000_create_test_table2.sql', true],
                [$migration_dir . '/0000000000_create_test_table3.sql', true],
                [$migration_dir . '/0000000000_create_test_table4.sql', true]
            ]));

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file->method('getMigrationDir')
            ->will($this->returnValue($migration_dir));

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Empty array to imply no migrations have ran yet.
        $strategy->method('getMigrationsRan')
            ->will($this->returnValue($migrations_ran));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler
        ]);

        $this->assertEquals(
            [
                '0000000000_create_test_table1.sql',
                '0000000000_create_test_table2.sql',
                '0000000000_create_test_table3.sql',
                '0000000000_create_test_table4.sql',
            ],
            $engine->getMigrationsToRun()
        );
    }

    /**
     * @group engine
     */
    public function testGetMigrationsToRunExcludesMigrationsThatHaveAlreadyRanBefore()
    {
        $migration_dir = 'database/migrations';

        $migration_dir_nodes = [
            '.',
            '..',
            '0000000000_create_test_table1.sql',
            '0000000000_create_test_table2.sql',
            '0000000000_create_test_table3.sql',
            '0000000000_create_test_table4.sql',
        ];

        $migrations_ran = [
            '0000000000_create_test_table1.sql',
            '0000000000_create_test_table2.sql',
        ];

        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file_handler->method('scanDir')
            ->will($this->returnValue($migration_dir_nodes));

        $file_handler->method('isFile')
            ->will($this->returnValueMap([
                [$migration_dir . '/.', false],
                [$migration_dir . '/..', false],
                [$migration_dir . '/0000000000_create_test_table1.sql', true],
                [$migration_dir . '/0000000000_create_test_table2.sql', true],
                [$migration_dir . '/0000000000_create_test_table3.sql', true],
                [$migration_dir . '/0000000000_create_test_table4.sql', true]
            ]));

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file->method('getMigrationDir')
            ->will($this->returnValue($migration_dir));

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('getMigrationsRan')
            ->will($this->returnValue($migrations_ran));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler
        ]);

        $this->assertEquals(
            [
                '0000000000_create_test_table3.sql',
                '0000000000_create_test_table4.sql',
            ],
            $engine->getMigrationsToRun()
        );
    }

    /**
     * This test checks to see if the SQL in the migration files that are run are correctly
     * passed to the strategy for execution. Since the strategy's runMigration method nor the
     * engine's runMigrations methods return a value, ultimately we are just verifying that the
     * parameters contain the correct values (the SQL code) to be executed.
     *
     * If the parameters to the strategy's runMigration method don't match the expected parameters,
     * the test will fail. That is our assertion.
     *
     * NOTE: This test performs $this->assertTrue(true), which may seem dumb, but this allows this
     * unit test to be marked by code coverage. Using doesNotPerformAssertions annotation allows the
     * unit test to run, but it does not mark the unit test in code coverage.
     *
     * @group engine
     */
    public function testRunMigrationsExecutesTheMigrationCodeFromEachMigrationToBeRun()
    {
        $migrations_to_run = [
            '0000000000_create_test_table1.sql',
            '0000000001_create_test_table2.sql',
            '0000000002_create_test_table3.sql',
            '0000000003_create_test_table4.sql',
        ];

        $directory = [
            'database' => [
                'migrations' => [
                    '0000000000_create_test_table1.sql' => 'SELECT * FROM users;',
                    '0000000001_create_test_table2.sql' => 'SELECT * FROM locations;',
                    '0000000002_create_test_table3.sql' => 'SELECT * FROM flights;',
                    '0000000003_create_test_table4.sql' => 'SELECT * FROM boats;',
                ]
            ]
        ];

        $file_system = vfsStream::setup('root', 444, $directory);

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file->method('getMigrationDir')
            ->will($this->returnValue(
                vfsStream::url('root/database/migrations')
            ));

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('setUp')
            ->will($this->returnValue(null));

        $strategy->method('runMigration')
            ->with(
                $this->logicalOr(
                    $this->equalTo('SELECT * FROM users;'),
                    $this->equalTo('SELECT * FROM locations;'),
                    $this->equalTo('SELECT * FROM flights;'),
                    $this->equalTo('SELECT * FROM boats;')
                )
            );

        $strategy->method('addMigrations')
            ->with($migrations_to_run)
            ->will($this->returnValue(null));

        $strategy->method('tearDown')
            ->will($this->returnValue(null));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => new FileHandler()
        ]);

        $engine->runMigrations($migrations_to_run);

        $this->assertTrue(true);
    }

    /**
     * This test validates that the SQL from the migration gets executed on rollback. See the comments
     * for the unit test above this one for a more detailed explanation. It is basically the same thing
     * as this unit test, execpt this unit test is for testing rollback execution.
     *
     * If the parameters to the strategy's runRollback method don't match the expected parameters,
     * the test will fail. That is our assertion.
     *
     * NOTE: This test performs $this->assertTrue(true) to allow the unit test to be marked by code
     * coverage.
     *
     * @group engine
     */
    public function testRollbackMigrationsExecutesTheMigrationCodeFromEachMigrationToBeRun()
    {
        $migrations_to_rollback = [
            '0000000000_create_test_table1.sql',
            '0000000001_create_test_table2.sql',
            '0000000002_create_test_table3.sql',
            '0000000003_create_test_table4.sql',
        ];

        $directory = [
            'database' => [
                'migrations' => [
                    '0000000000_create_test_table1.sql' => 'SELECT * FROM users;',
                    '0000000001_create_test_table2.sql' => 'SELECT * FROM locations;',
                    '0000000002_create_test_table3.sql' => 'SELECT * FROM flights;',
                    '0000000003_create_test_table4.sql' => 'SELECT * FROM boats;',
                ]
            ]
        ];

        $file_system = vfsStream::setup('root', 444, $directory);

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file->method('getMigrationDir')
            ->will($this->returnValue(
                vfsStream::url('root/database/migrations')
            ));

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('setUp')
            ->will($this->returnValue(null));

        $strategy->method('runRollback')
            ->with(
                $this->logicalOr(
                    $this->equalTo('SELECT * FROM users;'),
                    $this->equalTo('SELECT * FROM locations;'),
                    $this->equalTo('SELECT * FROM flights;'),
                    $this->equalTo('SELECT * FROM boats;')
                )
            );

        $strategy->method('removeMigrations')
            ->with($migrations_to_rollback)
            ->will($this->returnValue(null));

        $strategy->method('tearDown')
            ->will($this->returnValue(null));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => new FileHandler()
        ]);

        $engine->rollbackMigrations($migrations_to_rollback);

        $this->assertTrue(true);
    }

    /**
     * @group
     */
    public function testGetMigrationsToRollbackReturnsAllMigrationsIfNoOptionIsSpecified()
    {
        $migrations_ran = [
            '0000000000_create_test_table1.sql',
            '0000000001_create_test_table2.sql',
            '0000000002_create_test_table3.sql',
            '0000000003_create_test_table4.sql',
        ];

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('getMigrationsRan')
            ->will($this->returnValue($migrations_ran));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler,
        ]);

        $this->assertEquals(array_reverse($migrations_ran), $engine->getMigrationsToRollback());
    }

    /**
     * @group
     */
    public function testGetMigrationsToRollbackReturnsOnlyNMigrationsToRollbackWhenLastOptionIsProvided()
    {
        $migrations_ran = [
            '0000000000_create_test_table1.sql',
            '0000000001_create_test_table2.sql',
            '0000000002_create_test_table3.sql',
            '0000000003_create_test_table4.sql',
        ];

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('getMigrationsRan')
            ->will($this->returnValue($migrations_ran));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler,
        ]);

        $expected = [
            '0000000002_create_test_table3.sql',
            '0000000003_create_test_table4.sql',
        ];

        $this->assertEquals(array_reverse($expected), $engine->getMigrationsToRollback(2));
    }

    /**
     * @group
     */
    public function testGetMigrationsToRollbackReturnsAllMigrationsIfLastOptionIsGreaterThanTotalAmountOfMigrations()
    {
        $migrations_ran = [
            '0000000000_create_test_table1.sql',
            '0000000001_create_test_table2.sql',
            '0000000002_create_test_table3.sql',
            '0000000003_create_test_table4.sql',
        ];

        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file_handler = $this->getMockBuilder(FileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy = $this->getMockBuilder(PostgresStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->method('getMigrationsRan')
            ->will($this->returnValue($migrations_ran));

        $engine = new Engine([
            'strategy'     => $strategy,
            'config_file'  => $config_file,
            'file_handler' => $file_handler,
        ]);

        $this->assertEquals(array_reverse($migrations_ran), $engine->getMigrationsToRollback(25));
    }
}