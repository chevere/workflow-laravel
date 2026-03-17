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
use Chevere\Workflow\Laravel\Facades\Workflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\AutowiredWorkflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\MultiDependencyWorkflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\UserOnboardingWorkflow;
use Chevere\Workflow\Laravel\WorkflowManager;

class DependencyInjectionTest extends TestCase
{
    public function testActionWithSingleDependencyIsAutowired(): void
    {
        /** @var AutowiredWorkflow $workflow */
        $workflow = $this->app->make(AutowiredWorkflow::class);
        $run = $workflow->run(name: 'Laravel');
        $this->assertInstanceOf(RunInterface::class, $run);
        $this->assertSame('Hello from service, Laravel!', $run->response('greet')->string());
    }

    public function testActionWithMultipleDependenciesIsAutowired(): void
    {
        /** @var MultiDependencyWorkflow $workflow */
        $workflow = $this->app->make(MultiDependencyWorkflow::class);
        $run = $workflow->run(name: 'World');
        $this->assertInstanceOf(RunInterface::class, $run);
        $response = $run->response('process')->array();
        $this->assertSame('Hello from service, World!', $response['greeting']);
        $this->assertTrue($response['logged']);
    }

    public function testWorkflowManagerAutowiresActionDependencies(): void
    {
        /** @var WorkflowManager $manager */
        $manager = $this->app->make(WorkflowManager::class);
        $run = $manager->run(AutowiredWorkflow::class, name: 'Test');
        $this->assertInstanceOf(RunInterface::class, $run);
        $this->assertSame('Hello from service, Test!', $run->response('greet')->string());
    }

    public function testFacadeAutowiresActionDependencies(): void
    {
        $run = Workflow::run(AutowiredWorkflow::class, name: 'Facade');
        $this->assertInstanceOf(RunInterface::class, $run);
        $this->assertSame('Hello from service, Facade!', $run->response('greet')->string());
    }

    public function testNestedDependenciesAreResolved(): void
    {
        // Both GreetingService and LoggerService should be auto-resolved
        /** @var MultiDependencyWorkflow $workflow */
        $workflow = $this->app->make(MultiDependencyWorkflow::class);
        $run = $workflow->run(name: 'Nested');
        $this->assertInstanceOf(RunInterface::class, $run);
        $response = $run->response('process')->array();
        $this->assertArrayHasKey('greeting', $response);
        $this->assertArrayHasKey('logged', $response);
    }

    public function testRealWorldWorkflowWithLaravelServices(): void
    {
        // Test with Laravel's Config service (bound as singleton in framework)
        /** @var UserOnboardingWorkflow $workflow */
        $workflow = $this->app->make(UserOnboardingWorkflow::class);
        $run = $workflow->run(email: 'user@example.com', name: 'John');
        $this->assertInstanceOf(RunInterface::class, $run);
        $response = $run->response('sendWelcome')->array();
        $this->assertSame('user@example.com', $response['email']);
        $this->assertTrue($response['sent']);
        $this->assertStringContainsString('John', $response['message']);
        $this->assertStringContainsString('Laravel', $response['message']); // From config
    }
}
