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

namespace Chevere\Workflow\Laravel;

use Chevere\Workflow\Laravel\Console\ListWorkflowsCommand;
use Chevere\Workflow\Laravel\Console\MakeWorkflowCommand;
use Chevere\Workflow\Laravel\Console\RunWorkflowCommand;
use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorkflowManager::class, function ($app) {
            return new WorkflowManager($app);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeWorkflowCommand::class,
                RunWorkflowCommand::class,
                ListWorkflowsCommand::class,
            ]);
        }
    }
}
