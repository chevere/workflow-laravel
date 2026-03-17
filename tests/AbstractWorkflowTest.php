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

class AbstractWorkflowTest extends TestCase
{
    public function testGreetWorkflow(): void
    {
        /** @var GreetWorkflow $workflow */
        $workflow = $this->app->make(GreetWorkflow::class);
        $this->assertSame(
            $workflow->graph(),
            $workflow->getWorkflow()->jobs()->graph()->toArray()
        );
        $run = $workflow->run(name: 'Laravel');
        $this->assertInstanceOf(RunInterface::class, $run);
        $this->assertSame('Hello, Laravel!', $run->response('greet')->string());
    }

    public function testMathWorkflow(): void
    {
        /** @var MathWorkflow $workflow */
        $workflow = $this->app->make(MathWorkflow::class);
        $this->assertSame(
            $workflow->graph(),
            $workflow->getWorkflow()->jobs()->graph()->toArray()
        );
        $run = $workflow->run(x: 3, y: 4, factor: 10);
        $this->assertSame(7, $run->response('add')->int());
        $this->assertSame(70, $run->response('multiply')->int());
    }

    public function testWorkflowDefinitionIsCached(): void
    {
        /** @var GreetWorkflow $workflow */
        $workflow = $this->app->make(GreetWorkflow::class);
        $definition1 = $workflow->getWorkflow();
        $definition2 = $workflow->getWorkflow();
        $this->assertSame($definition1, $definition2);
    }
}
