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

use Chevere\Workflow\Interfaces\RunInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use Illuminate\Console\Command;

class RunWorkflowCommand extends Command
{
    protected $signature = 'workflow:run
        {workflow : The fully qualified workflow class name}
        {--var=* : Variables as key=value pairs}';

    protected $description = 'Run a registered Chevere Workflow';

    public function handle(): int
    {
        /** @var string $workflowClass */
        $workflowClass = $this->argument('workflow');
        if (! class_exists($workflowClass)) {
            $this->error("Workflow class not found: {$workflowClass}");

            return self::FAILURE;
        }
        if (! is_subclass_of($workflowClass, AbstractWorkflow::class)) {
            $this->error('Class must extend ' . AbstractWorkflow::class);

            return self::FAILURE;
        }
        $variables = $this->parseVariables();
        $this->info("Running workflow: {$workflowClass}");
        /** @var AbstractWorkflow $workflow */
        $workflow = app()->make($workflowClass);
        $this->printGraph($workflow);
        $run = $workflow->run(...$variables);
        $this->info('Workflow completed successfully.');
        $this->line("UUID: {$run->uuid()}");
        $this->printResponses($run);
        $this->printSkipped($run);

        return self::SUCCESS;
    }

    private function printGraph(AbstractWorkflow $workflow): void
    {
        $graph = $workflow->graph();
        $this->line('Execution graph:');
        foreach ($graph as $level => $jobs) {
            $this->line("  Level {$level}: " . implode(', ', $jobs));
        }
    }

    private function printResponses(RunInterface $run): void
    {
        if (count($run) === 0) {
            return;
        }
        $this->line('Responses:');
        foreach ($run as $job => $response) {
            $display = is_scalar($response)
                ? $response
                : json_encode($response, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            $this->line("  {$job}: {$display}");
        }
    }

    private function printSkipped(RunInterface $run): void
    {
        $skipped = [];
        foreach ($run->skip() as $name) {
            $skipped[] = $name;
        }
        if ($skipped !== []) {
            $this->warn('Skipped jobs: ' . implode(', ', $skipped));
        }
    }

    /**
     * @return array<string, string>
     */
    private function parseVariables(): array
    {
        $variables = [];
        /** @var string[] $rawVars */
        $rawVars = $this->option('var');
        foreach ($rawVars as $var) {
            /** @var string $var */
            $parts = explode('=', $var, 2);
            if (count($parts) !== 2) {
                $this->warn("Skipping invalid variable format: {$var} (expected key=value)");

                continue;
            }
            $variables[$parts[0]] = $parts[1];
        }

        return $variables;
    }
}
