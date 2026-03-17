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

use Chevere\Workflow\Laravel\WorkflowManager;
use Chevere\Workflow\Laravel\WorkflowServiceProvider;

class WorkflowServiceProviderTest extends TestCase
{
    public function testServiceProviderIsRegistered(): void
    {
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(WorkflowServiceProvider::class, $providers);
    }

    public function testWorkflowManagerIsBound(): void
    {
        $manager = $this->app->make(WorkflowManager::class);
        $this->assertInstanceOf(WorkflowManager::class, $manager);
    }

    public function testWorkflowManagerIsSingleton(): void
    {
        $manager1 = $this->app->make(WorkflowManager::class);
        $manager2 = $this->app->make(WorkflowManager::class);
        $this->assertSame($manager1, $manager2);
    }
}
