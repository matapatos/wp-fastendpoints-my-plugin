<?php

/**
 * Holds interface for Service Providers
 *
 * @since 1.0.0
 *
 * @license MIT
 */

namespace MyPlugin\Providers;

interface ProviderContract
{
    public function register(): void;
}
