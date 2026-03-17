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

namespace Chevere\Workflow\Laravel\Console;

use Chevere\Workflow\Laravel\AbstractWorkflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListWorkflowsCommand extends Command
{
    protected $signature = 'workflow:list';

    protected $description = 'List all Chevere Workflows found in the application';

    public function handle(): int
    {
        $workflows = $this->discoverWorkflows();
        if ($workflows === []) {
            $this->info('No workflows found.');
        } else {
            $rows = [];
            foreach ($workflows as $workflowClass) {
                /** @var AbstractWorkflow $instance */
                $instance = app()->make($workflowClass);
                $graph = $instance->graph();
                $jobCount = count($instance->getWorkflow());
                $levels = count($graph);
                $rows[] = [$workflowClass, "{$jobCount} jobs", "{$levels} levels"];
            }
            $this->table(['Workflow', 'Jobs', 'Graph Depth'], $rows);
        }

        return self::SUCCESS;
    }

    /**
     * @return class-string<AbstractWorkflow>[]
     */
    private function discoverWorkflows(): array
    {
        $basePath = app_path();
        if (! is_dir($basePath)) {
            return [];
        }
        $namespace = $this->laravel->getNamespace();
        $workflows = [];
        foreach (File::allFiles($basePath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $class = $namespace . Str::of($file->getRelativePathname())
                ->replace('/', '\\')
                ->replace('.php', '')
                ->__toString();

            if (! class_exists($class) || ! is_subclass_of($class, AbstractWorkflow::class)) {
                continue;
            }
            $workflows[] = $class;
        }

        return $workflows;
    }
}
