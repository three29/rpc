<?php

namespace Sentry;

// Minimal stand-in for the Sentry SDK's captureException(), recording calls
if (!function_exists('Sentry\captureException')) {
    function captureException(\Throwable $exception): void
    {
        $GLOBALS['__sentry_captured'][] = $exception;
    }
}
