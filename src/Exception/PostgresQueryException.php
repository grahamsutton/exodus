<?php

namespace Exodus\Exception;

use Exception;

/**
 * No Database Connection Exception
 *
 * Should be thrown when a Postgres query fails to execute
 * correctly. 
 */
class PostgresQueryException extends Exception {}