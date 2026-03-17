<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Workflow\Laravel\Tests;

use Chevere\Workflow\Laravel\WorkflowServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            WorkflowServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Workflow' => \Chevere\Workflow\Laravel\Facades\Workflow::class,
        ];
    }
}
