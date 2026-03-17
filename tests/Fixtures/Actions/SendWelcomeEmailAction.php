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

namespace Chevere\Tests\Fixtures\Actions;

use Chevere\Action\Action;
use Chevere\Tests\Fixtures\Services\GreetingService;
use Chevere\Tests\Fixtures\Services\LoggerService;
use Illuminate\Contracts\Config\Repository as Config;

/**
 * Real-world example: Action using Laravel services with nested dependencies.
 */
class SendWelcomeEmailAction extends Action
{
    public function __construct(
        private GreetingService $greetingService,
        private LoggerService $loggerService,
        private Config $config
    ) {
    }

    public function __invoke(string $email, string $name): array
    {
        $greeting = $this->greetingService->greet($name);
        $appName = $this->config->get('app.name', 'Laravel');
        $message = "{$greeting} Welcome to {$appName}!";
        $this->loggerService->log("Email sent to: {$email}");

        return [
            'email' => $email,
            'message' => $message,
            'sent' => true,
        ];
    }
}
