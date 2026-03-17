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

namespace Chevere\Workflow\Laravel\Facades;

use Chevere\Workflow\Interfaces\RunInterface;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use Chevere\Workflow\Laravel\WorkflowManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RunInterface run(WorkflowInterface|class-string<AbstractWorkflow> $workflow, mixed ...$variables)
 *
 * @see WorkflowManager
 */
class Workflow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WorkflowManager::class;
    }
}
