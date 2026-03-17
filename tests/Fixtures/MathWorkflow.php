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
use function Chevere\Workflow\response;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

class MathWorkflow extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            add: sync(
                fn (int $a, int $b): int => $a + $b,
                a: variable('x'),
                b: variable('y'),
            ),
            multiply: sync(
                fn (int $sum, int $factor): int => $sum * $factor,
                sum: response('add'),
                factor: variable('factor'),
            ),
        );
    }
}
