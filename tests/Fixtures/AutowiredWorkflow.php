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

namespace Chevere\Workflow\Laravel\Tests\Fixtures;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\Actions\GreetAction;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

/**
 * Workflow using action class with constructor dependencies.
 * Dependencies should be auto-resolved by Laravel container.
 */
class AutowiredWorkflow extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            greet: sync(
                GreetAction::class,
                name: variable('name'),
            ),
        );
    }
}
