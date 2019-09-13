<?php

namespace ExodusTests;

use Exodus\Database\Strategy\Factory;
use Exodus\Database\Adapter;
use Exodus\Database\Adapter\Postgres as PostgresAdapter;
use Exodus\Database\Strategy\Postgres as PostgresStrategy;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Exodus\Database\Strategy\Factory.
 */
class FactoryTest extends TestCase
{
    /**
     * @group database.strategy.factory
     */
    public function testGetStrategyReturnsAPostgresStrategyWhenPostgresAdapterIsReceived()
    {
        $adapter = $this->getMockBuilder(PostgresAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(PostgresStrategy::class, Factory::getStrategy($adapter, 'migrations'));
    }

    /**
     * @expectedException \Exodus\Exception\InvalidDatabaseAdapterException
     * @group database.strategy.factory
     */
    // public function testGetStrategyThrowsExceptionWhenInvalidAdapterIsProvided()
    // {
    //     $adapter = $this->getMockBuilder(new class extends Adapter {
            
    //         })
    //         ->disableOriginalConstructor()
    //         ->getMock();

    //     $this->assertInstanceOf(PostgresStrategy::class, Factory::getStrategy($adapter, 'migrations'));
    // }
}