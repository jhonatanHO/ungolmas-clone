<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\PendingCommand;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates an interactive console assertion instead of an integer exit code.
     *
     * @param  array<string, mixed>  $parameters
     */
    protected function artisanCommand(string $command, array $parameters = []): PendingCommand
    {
        $pendingCommand = $this->artisan($command, $parameters);
        self::assertInstanceOf(PendingCommand::class, $pendingCommand);

        return $pendingCommand;
    }
}
