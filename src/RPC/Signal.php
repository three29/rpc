<?php

namespace RPC;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 compliant event dispatcher
 *
 * Modern event dispatching system that allows you to register listeners
 * for events and dispatch those events throughout your application.
 *
 * @package Core
 */
class Signal implements EventDispatcherInterface
{
	/**
	 * Registered event listeners
	 *
	 * @var array<string, array<callable>>
	 */
	protected array $listeners = [];

	/**
	 * Singleton instance
	 *
	 * @var self|null
	 */
	protected static ?self $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register an event listener
	 *
	 * @param string $eventName Event class name or event identifier
	 * @param callable $listener Callable to be invoked when event is dispatched
	 * @param int $priority Higher priority listeners are called first (default: 0)
	 * @return void
	 */
	public function listen(string $eventName, callable $listener, int $priority = 0): void
	{
		if (!isset($this->listeners[$eventName])) {
			$this->listeners[$eventName] = [];
		}

		$this->listeners[$eventName][] = [
			'listener' => $listener,
			'priority' => $priority
		];

		// Sort by priority (highest first)
		usort($this->listeners[$eventName], fn($a, $b) => $b['priority'] <=> $a['priority']);
	}

	/**
	 * PSR-14: Provide all relevant listeners with an event to process
	 *
	 * @param object $event The event object to dispatch
	 * @return object The event that was passed, potentially modified by listeners
	 */
	public function dispatch(object $event): object
	{
		$eventName = get_class($event);

		if (!isset($this->listeners[$eventName])) {
			return $event;
		}

		foreach ($this->listeners[$eventName] as $item) {
			// Check if event propagation has been stopped
			if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
				break;
			}

			// Invoke the listener
			call_user_func($item['listener'], $event);
		}

		return $event;
	}

	/**
	 * Remove all listeners for a specific event
	 *
	 * @param string $eventName
	 * @return void
	 */
	public function forget(string $eventName): void
	{
		unset($this->listeners[$eventName]);
	}

	/**
	 * Remove all registered listeners
	 *
	 * @return void
	 */
	public function flush(): void
	{
		$this->listeners = [];
	}

	/**
	 * Check if event has any listeners
	 *
	 * @param string $eventName
	 * @return bool
	 */
	public function hasListeners(string $eventName): bool
	{
		return isset($this->listeners[$eventName]) && count($this->listeners[$eventName]) > 0;
	}

	/**
	 * Get all listeners for a specific event
	 *
	 * @param string $eventName
	 * @return array<callable>
	 */
	public function getListeners(string $eventName): array
	{
		if (!isset($this->listeners[$eventName])) {
			return [];
		}

		return array_map(fn($item) => $item['listener'], $this->listeners[$eventName]);
	}
}
