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

namespace Chevere\Workflow\Laravel\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * Exception thrown when a container entry is not found or cannot be resolved.
 */
final class ContainerEntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(
        string $id,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            "Container entry '{$id}' not found or could not be resolved.",
            0,
            $previous
        );
    }
}
