<?php

namespace Exodus\Exception;

use Exception;

/**
 * Invalid Database Adapter Exception
 *
 * Should be thrown when the config file (exodus.yml) is provided
 * an invalid db:adapter parameter.
 */
class InvalidDatabaseAdapterException extends Exception {}