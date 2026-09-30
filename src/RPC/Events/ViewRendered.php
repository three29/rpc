<?php

namespace RPC\Events;

use RPC\View;

/**
 * Event dispatched after a view template is rendered
 */
class ViewRendered
{
	public function __construct(
		public readonly View $view,
		public readonly string $template
	) {}
}
