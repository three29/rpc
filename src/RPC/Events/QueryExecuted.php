<?php

namespace RPC\Events;

/**
 * Event dispatched after a database query is executed
 */
class QueryExecuted
{
	public function __construct(
		public readonly string $sql,
		public readonly string $type
	) {}
}
