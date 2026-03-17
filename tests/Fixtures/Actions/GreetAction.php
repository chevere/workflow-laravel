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

/**
 * Action with constructor dependency that should be auto-resolved by Laravel.
 */
class GreetAction extends Action
{
    public function __construct(
        private GreetingService $greetingService
    ) {
    }

    public function __invoke(string $name): string
    {
        return $this->greetingService->greet($name);
    }
}
