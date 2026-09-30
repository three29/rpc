<?php

namespace RPC\Registry;

use Psr\Container\ContainerInterface;
use RPC\Registry;

/**
 * PSR-11 compliant container adapter for Registry
 * Legacy fallback when Application container is not available
 */
class ContainerAdapter implements ContainerInterface
{
	/**
	 * PSR-11: Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return mixed Entry.
	 * @throws NotFoundException  No entry was found for this identifier.
	 */
	public function get(string $id): mixed
	{
		if (!isset($GLOBALS['_RPC_REGISTRY_'][$id])) {
			throw new NotFoundException("Entry '{$id}' not found in container");
		}

		return $GLOBALS['_RPC_REGISTRY_'][$id];
	}

	/**
	 * PSR-11: Returns true if the container can return an entry for the given identifier.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 */
	public function has(string $id): bool
	{
		return isset($GLOBALS['_RPC_REGISTRY_'][$id]);
	}
}
