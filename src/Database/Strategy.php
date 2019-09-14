<?php

namespace Exodus\Database;

/**
 * The Database Strategy Interface
 *
 * Sets the predefined necessary operations that allow any database
 * to work correctly with Exodus.
 */
interface Strategy
{
    /**
     * Executes an SQL script that creates the migrations table in the
     * SQL language defined by the adapter option in exodus.yml.
     *
     * @return void
     */
    public function createMigrationTable(): void;

    /**
     * Returns whether the migration table has been created.
     *
     * @return bool
     */
    public function isMigrationTableCreated(): bool;

    /**
     * Returns the list of migrations that currently reside in the migrations
     * table. If the file exists in the migrations table, that means it has
     * already been run.
     *
     * @return array
     */
    public function getMigrationsRan(): array;

    /**
     * Inserts a migration into the migrations table. This will happen when a
     * migration file has been successfully executed and the file needs to be
     * inserted so it does not run again.
     *
     * @param array $migrated_files
     *
     * @return void
     */
    public function addMigrations(array $migrated_files = []): void;

    /**
     * Deletes the list of migrations from the migrations table. This will happen
     * when a series of files have been rolled back successfully.
     *
     * @param array $migrated_files
     *
     * @return void
     */
    public function removeMigrations(array $migrated_files = []): void;

    /**
     * Defines how a single migration is ran and what should happen when running
     * a single migration.
     *
     * @param mixed $sql
     *
     * @return void
     */
    public function runMigration($sql): void;

    /**
     * Defines any form of actions needed to setup the database for running
     * migrations. Typically, you might do things here like setting up a 
     * temporary schema.
     *
     * @return void
     */
    public function setUp(): void;

    /**
     * Should perform the opposite action of whatever happens in setUp(). For
     * example, if you were to create a temporary schema in setUp(), use this
     * method to drop the schema when you're done.
     *
     * @return void
     */
    public function tearDown(): void;
}