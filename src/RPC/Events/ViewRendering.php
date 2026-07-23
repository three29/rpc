<?php

namespace RPC\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use RPC\View;

/**
 * Event dispatched before a view template is rendered
 */
class ViewRendering implements StoppableEventInterface
{
	protected bool $propagationStopped = false;

	public function __construct(
		public readonly View $view,
		public readonly string $template
	) {}

	/**
	 * Cancel view rendering by stopping event propagation
	 *
	 * @return void
	 */
	public function cancelRendering(): void
	{
		$this->propagationStopped = true;
	}

	public function isPropagationStopped(): bool
	{
		return $this->propagationStopped;
	}
}
