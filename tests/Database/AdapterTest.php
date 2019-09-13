<?php

namespace ExodusTests;

use Exodus\Database\Adapter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Exodus\Database\Adapter.
 */
class AdapterTest extends TestCase
{
    /**
     * @group database.adapter
     */
    public function testConstructorCorrectlySetsDatabaseParameters()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->setConstructorArgs([
                [
                    'host'     => 'adapter.exodus.com',
                    'username' => 'exodus',
                    'password' => 'exodus_pw',
                    'port'     => 1234,
                    'name'     => 'exodus_db'
                ]
            ])
            ->getMockForAbstractClass();

        $this->assertEquals('adapter.exodus.com', $adapter->getHost());
        $this->assertEquals('exodus', $adapter->getUsername());
        $this->assertEquals('exodus_pw', $adapter->getPassword());
        $this->assertEquals(1234, $adapter->getPort());
        $this->assertEquals('exodus_db', $adapter->getDatabase());
    }

    /**
     * @group database.adapter
     */
    public function testSetHostCorrectlySetsHostName()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setHost('adapter.exodus.com');

        $this->assertEquals('adapter.exodus.com', $adapter->getHost());
    }

    /**
     * @expectedException TypeError
     * @group database.adapter
     */
    public function testSetHostThrowsTypeErrorWhenReceivingNullParameter()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setHost(null);
    }

    /**
     * @expectedException \Exodus\Exception\UndefinedConfigParamException
     * @group database.adapter
     */
    public function testSetHostThrowsExceptionWhenReceivingEmptyStringParameter()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setHost('');
    }

    /**
     * @group database.adapter
     */
    public function testSetUsernameCorrectlySetsUsername()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setUsername('exodus');

        $this->assertEquals('exodus', $adapter->getUsername());
    }

    /**
     * @expectedException TypeError
     * @group database.adapter
     */
    public function testSetUsernameThrowsTypeErrorWhenReceivingNullAsParameter()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setUsername(null);
    }

    /**
     * @group database.adapter
     */
    public function testSetPasswordCorrectlySetsPassword()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setPassword('exodus');

        $this->assertEquals('exodus', $adapter->getPassword());
    }

    /**
     * @group database.adapter
     */
    public function testSetPasswordIsAllowedToSetNullAsAValue()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setPassword(null);

        $this->assertEquals(null, $adapter->getPassword());
    }

    /**
     * @group database.adapter
     */
    public function testSetPortUsingIntegerCorrectlySetsPortAsInteger()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setPort(1234);

        $this->assertEquals(1234, $adapter->getPort());
    }

    /**
     * @group database.adapter
     */
    public function testSetPortUsingStringCorrectlySetsPortAsInteger()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setPort('1234');

        $this->assertEquals('1234', $adapter->getPort());
    }

    /**
     * @expectedException TypeError
     * @group database.adapter
     */
    public function testSetPortThrowsTypeErrorWhenReceivingNullAsParameter()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setPort(null);
    }

    /**
     * @expectedException TypeError
     * @group database.adapter
     */
    public function testSetPortThrowsExceptionWhenReceivingEmptyStringAsParameter()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setPort('');
    }

    /**
     * @group database.adapter
     */
    public function testSetDatabaseCorrectlySetsDatabase()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setDatabase('exodus_db');

        $this->assertEquals('exodus_db', $adapter->getDatabase());
    }

    /**
     * @expectedException TypeError
     * @group database.adapter
     */
    public function testSetDatabaseThrowsTypeErrorWhenReceivingNullAsParameter()
    {
        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $adapter->setDatabase(null);
    }
}