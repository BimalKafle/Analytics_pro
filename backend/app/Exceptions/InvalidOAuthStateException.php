<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised when an OAuth callback arrives with an unknown or expired state,
 * which indicates an expired flow or a forged request.
 */
class InvalidOAuthStateException extends Exception {}
