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

use Chevere\Workflow\Interfaces\RunInterface;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use Illuminate\Contracts\Container\Container;
use function Chevere\Workflow\run;

class WorkflowManager
{
    public function __construct(
        private Container $container
    ) {
    }

    /**
     * Run a workflow with the given variables.
     *
     * Accepts either a WorkflowInterface instance or an AbstractWorkflow class name.
     *
     * @param WorkflowInterface|class-string<AbstractWorkflow> $workflow
     * @param mixed ...$variables Runtime variables for the workflow
     */
    public function run(WorkflowInterface|string $workflow, mixed ...$variables): RunInterface
    {
        if (is_string($workflow)) {
            /** @var AbstractWorkflow $instance */
            $instance = $this->container->make($workflow);

            return $instance->run(...$variables);
        }
        $bridge = new LaravelContainerBridge(
            $this->container,
            $workflow->dependencies()
        );

        return run($workflow, $bridge, ...$variables);
    }
}
