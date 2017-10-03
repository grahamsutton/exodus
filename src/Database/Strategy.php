<?php

namespace Exodus\Database;

/**
 * The Database Strategy Class
 *
 * Runs a defined set of code within the database
 * adapter class as a "transaction" to be carried
 * out by the database as defined by the implementing
 * class.
 */
abstract class Strategy
{
    /**
     * The database adapter to execute on.
     *
     * @var mixed
     */
    protected $db_adapter;

    /**
     * Constructor
     *
     * Sets the database adapter on the strategy.
     *
     * @param mixed $db_adapter
     */
    public function __construct($db_adapter)
    {
        $this->db_adapter = $db_adapter;
    }

    /**
     * Run the defined strategy within against the
     * database
     *
     * @return mixed
     */
    abstract public function run();
}