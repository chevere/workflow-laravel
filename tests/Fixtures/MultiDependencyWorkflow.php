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

namespace Chevere\Tests\Fixtures;

use Chevere\Tests\Fixtures\Actions\ProcessAction;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Interfaces\WorkflowProviderInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

/**
 * Workflow using action with multiple constructor dependencies.
 */
class MultiDependencyWorkflow extends AbstractWorkflow implements WorkflowProviderInterface
{
    public static function workflow(): WorkflowInterface
    {
        return workflow(
            process: sync(
                ProcessAction::class,
                name: variable('name'),
            ),
        );
    }

    protected function definition(): WorkflowInterface
    {
        return self::workflow();
    }
}
