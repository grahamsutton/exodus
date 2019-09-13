<?php

namespace ExodusTests\Command;

use Exodus\Command\MakeMigrationCommand;
use Exodus\Config\ConfigFile;
use Exodus\Config\Templates;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for Exodus\Command\MakeMigrationCommand.
 */
class MakeMigrationCommandTest extends TestCase
{
    /**
     * The root of the virtual directory that we will test
     * against.
     *
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    protected $file_system;

    /**
     * The location of the database migrations directory (for
     * testing).
     *
     * @var string
     */
    protected $migration_dir;

    /**
     * The location to where the template file for postgres UP()
     * and DOWN() function are located.
     *
     * @var string
     */
    protected $sql_template_path;

    /**
     * Sets up the console application, its dependencies, and the command 
     * we are actively testing.
     *
     * @return void
     */
    protected function setUp()
    {
        $directory = [
            'templates' => [
                'postgres.sql' => 'SELECT * FROM users;'
            ]
        ];

        $this->file_system = vfsStream::setup('root', 444, $directory);

        // Set up virtual directories
        $this->migration_dir     = vfsStream::url('root/database/migrations');
        $this->sql_template_path = vfsStream::url('root/templates/postgres.sql');
    }

    /**
     * @group make:migration
     */
    public function testExecuteMakeMigrationShouldCreateNewMigrationFile()
    {
        $config_file = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config_file->method('getMigrationDir')
            ->will($this->returnValue($this->migration_dir));

        $templates = $this->getMockBuilder(Templates::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templates->method('getSQLTemplatePath')
            ->will($this->returnValue($this->sql_template_path));

        $application = new Application();

        $application->add(new MakeMigrationCommand([
            'config_file' => $config_file,
            'templates'   => $templates
        ]));

        $command = $application->find('make:migration');

        $command_tester = new CommandTester($command);

        $command_tester->execute(['name' => 'create_testing_table']);

        $this->assertTrue($this->file_system->getChild('database/migrations')->hasChildren());
    }
}