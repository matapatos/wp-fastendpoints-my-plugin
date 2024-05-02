<?php

declare(strict_types=1);

namespace MyPlugin\Tests;

class Helpers
{
    /**
     * Checks if weather we want to run integration tests or not
     */
    public static function isIntegrationTest(): bool
    {
        return isset($GLOBALS['argv']) && in_array('--group=integration', $GLOBALS['argv'], true);
    }
}
