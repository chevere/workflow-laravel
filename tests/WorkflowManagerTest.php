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

use Chevere\Workflow\Interfaces\RunInterface;
use Chevere\Workflow\Laravel\Tests\Fixtures\GreetWorkflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\MathWorkflow;
use Chevere\Workflow\Laravel\WorkflowManager;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

class WorkflowManagerTest extends TestCase
{
    public function testRunWithWorkflowInstance(): void
    {
        /** @var WorkflowManager $manager */
        $manager = $this->app->make(WorkflowManager::class);
        $workflow = workflow(
            greet: sync(
                fn (string $name): string => "Hi, {$name}!",
                name: variable('name'),
            ),
        );
        $run = $manager->run($workflow, name: 'World');
        $this->assertInstanceOf(RunInterface::class, $run);
        $this->assertSame(
            'Hi, World!',
            $run->response('greet')->string()
        );
    }

    public function testRunWithClassName(): void
    {
        /** @var WorkflowManager $manager */
        $manager = $this->app->make(WorkflowManager::class);
        $run = $manager->run(GreetWorkflow::class, name: 'Test');
        $this->assertInstanceOf(RunInterface::class, $run);
        $this->assertSame(
            'Hello, Test!',
            $run->response('greet')->string()
        );
    }

    public function testRunChainedWorkflow(): void
    {
        /** @var WorkflowManager $manager */
        $manager = $this->app->make(WorkflowManager::class);
        $run = $manager->run(MathWorkflow::class, x: 5, y: 3, factor: 2);
        $this->assertSame(8, $run->response('add')->int());
        $this->assertSame(16, $run->response('multiply')->int());
    }
}
