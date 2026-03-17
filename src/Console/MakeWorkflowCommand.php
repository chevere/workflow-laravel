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

namespace Chevere\Workflow\Laravel\Console;

use Illuminate\Console\GeneratorCommand;

class MakeWorkflowCommand extends GeneratorCommand
{
    protected $name = 'make:workflow';

    protected $description = 'Create a new Chevere Workflow class';

    protected $type = 'Workflow';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/workflow.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Workflows';
    }
}
