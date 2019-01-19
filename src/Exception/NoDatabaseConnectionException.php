<?php

namespace Exodus\Exception;

use Exception;

/**
 * No Database Connection Exception
 *
 * Should be thrown when there is no active database connection
 * formed when trying to perform a query or transaction.
 */
class NoDatabaseConnectionException extends Exception {}