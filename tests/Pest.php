<?php

declare(strict_types=1);
use Tests\TestCase;

/**
 * Pest PHP Configuration file for UNGOLMAS Core.
 */
pest()->extend(TestCase::class)
    ->in('Feature', '../packages/*/tests/Feature');

pest()->extend(TestCase::class)
    ->in('Unit', '../packages/*/tests/Unit');
