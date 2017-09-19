<?php

namespace Exodus\Exception;

use Exception;

/**
 * Config File Not Found Exception
 *
 * Should be thrown when the config file (exodus.yml) is not
 * found in the user's current working directory.
 */
class ConfigFileNotFoundException extends Exception {}