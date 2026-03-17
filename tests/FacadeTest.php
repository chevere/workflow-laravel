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

use Chevere\Workflow\Laravel\Facades\Workflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\GreetWorkflow;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

class FacadeTest extends TestCase
{
    public function testFacadeRunsWorkflowInstance(): void
    {
        $workflow = workflow(
            greet: sync(
                fn (string $name): string => "Hello, {$name}!",
                name: variable('name'),
            ),
        );
        $run = Workflow::run($workflow, name: 'Facade');
        $this->assertSame(
            'Hello, Facade!',
            $run->response('greet')->string()
        );
    }

    public function testFacadeRunsWorkflowClass(): void
    {
        $run = Workflow::run(GreetWorkflow::class, name: 'Laravel');
        $this->assertSame(
            'Hello, Laravel!',
            $run->response('greet')->string()
        );
    }
}
