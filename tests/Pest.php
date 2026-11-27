<?php

declare(strict_types=1);

/**
 * Pest PHP Configuration file for UNGOLMAS Core.
 */
pest()->extend(Tests\TestCase::class)
    ->in('Feature', '../packages/*/tests/Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit', '../packages/*/tests/Unit');
