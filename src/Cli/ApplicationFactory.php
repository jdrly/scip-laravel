<?php

declare(strict_types=1);

namespace ScipLaravel\Cli;

use Symfony\Component\Console\Application;

final class ApplicationFactory
{
    public const string VERSION = '0.1.0-dev';

    public static function create(): Application
    {
        $application = new Application('scip-laravel', self::VERSION);
        $application->addCommands([new IndexCommand()]);

        return $application;
    }
}
