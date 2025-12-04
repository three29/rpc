<?php

namespace RPC\Events;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Event dispatched before a database query is executed
 */
class QueryExecuting implements StoppableEventInterface
{
	protected bool $propagationStopped = false;

	public function __construct(
		public readonly string $sql,
		public readonly string $type
	) {}

	/**
	 * Stop query execution by stopping event propagation
	 *
	 * @return void
	 */
	public function stopExecution(): void
	{
		$this->propagationStopped = true;
	}

	public function isPropagationStopped(): bool
	{
		return $this->propagationStopped;
	}
}
