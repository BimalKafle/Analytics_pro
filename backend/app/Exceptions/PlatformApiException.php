<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised when a platform API call fails. The message must stay safe for
 * logs and API responses: never include tokens or raw credentials.
 */
class PlatformApiException extends Exception {}
