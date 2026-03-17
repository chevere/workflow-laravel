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

namespace Chevere\Tests\Fixtures;

use Chevere\Tests\Fixtures\Actions\SendWelcomeEmailAction;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

/**
 * Real-world workflow demonstrating Laravel service integration.
 */
class UserOnboardingWorkflow extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            sendWelcome: sync(
                SendWelcomeEmailAction::class,
                email: variable('email'),
                name: variable('name'),
            ),
        );
    }
}
