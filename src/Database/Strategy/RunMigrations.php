<?php

namespace Exodus\Database\Strategy;

use Exodus\Database\Strategy;

/**
 * The Run Migrations Class
 *
 * This class is used to run migrations against the 
 * database.
 */
class RunMigrations extends Strategy
{
    /**
     * Runs migrations against the database.
     *
     * @return void
     */
    public function run()
    {
        $this->db_adapter->query('CREATE TABLE graham_is_cool (name VARCHAR NOT NULL);');
    }
}