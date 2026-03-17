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

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\response;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

class ConditionalWorkflow extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            check: sync(
                fn (string $enabled): bool => $enabled === 'true',
                enabled: variable('enabled'),
            ),
            conditionalJob: sync(
                fn (): string => 'This job will be skipped',
            )->withRunIf(response('check')),
            alwaysRun: sync(
                fn (): string => 'This job always runs',
            ),
        );
    }
}
