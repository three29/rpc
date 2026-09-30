<?php

namespace RPC\Registry;

use Psr\Container\NotFoundExceptionInterface;

/**
 * PSR-11 NotFoundException
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
