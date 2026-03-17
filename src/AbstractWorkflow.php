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

/**
 * Base class for Laravel workflows.
 *
 * Extend this class and implement the `definition()` method to declare
 * your workflow's jobs. The Laravel container is automatically passed
 * to the workflow engine for dependency injection.
 *
 * ```php
 * class ProcessOrder extends AbstractWorkflow
 * {
 *     protected function definition(): WorkflowInterface
 *     {
 *         return workflow(
 *             validate: sync(ValidateOrder::class, id: variable('orderId')),
 *             charge: sync(ChargePayment::class, order: response('validate')),
 *         );
 *     }
 * }
 * ```
 */
abstract class AbstractWorkflow
{
    private ?WorkflowInterface $workflow = null;

    public function __construct(
        protected Container $container
    ) {
    }

    /**
     * Run this workflow with the given variables.
     *
     * @param mixed ...$variables Runtime variables matching workflow parameters
     */
    public function run(mixed ...$variables): RunInterface
    {
        $workflow = $this->getWorkflow();
        $bridge = new LaravelContainerBridge(
            $this->container,
            $workflow->dependencies()
        );

        return run($workflow, $bridge, ...$variables);
    }

    /**
     * Get the workflow definition (cached after first call).
     */
    public function getWorkflow(): WorkflowInterface
    {
        return $this->workflow ??= $this->definition();
    }

    /**
     * Get the execution graph as an array.
     *
     * @return array<array<string>>
     */
    public function graph(): array
    {
        return $this->getWorkflow()->jobs()->graph()->toArray();
    }

    /**
     * Define the workflow's jobs.
     */
    abstract protected function definition(): WorkflowInterface;
}
