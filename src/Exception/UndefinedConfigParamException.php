<?php

namespace Exodus\Exception;

use Exception;

/**
 * Undefined Config Parameter Exception
 *
 * Should be thrown when a required parameter for a command
 * is not defined in the exodus.yml file.
 */
class UndefinedConfigParamException extends Exception {}