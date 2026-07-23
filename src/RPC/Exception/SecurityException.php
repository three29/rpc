<?php

namespace RPC\Exception;

/**
 * Security exception for RPC framework
 * Thrown when security violations occur (CSRF, etc.)
 *
 * @package Exception
 */
class SecurityException extends RuntimeException
{
}
