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

namespace Chevere\Workflow\Laravel\Tests\Fixtures\Actions;

use Chevere\Action\Action;
use Chevere\Workflow\Laravel\Tests\Fixtures\Services\GreetingService;
use Chevere\Workflow\Laravel\Tests\Fixtures\Services\LoggerService;

/**
 * Action with multiple constructor dependencies.
 */
class ProcessAction extends Action
{
    public function __construct(
        private GreetingService $greetingService,
        private LoggerService $loggerService
    ) {
    }

    public function __invoke(string $name): array
    {
        $greeting = $this->greetingService->greet($name);
        $this->loggerService->log("Processed: {$name}");

        return [
            'greeting' => $greeting,
            'logged' => true,
        ];
    }
}
