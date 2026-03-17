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
 * Exception thrown when a workflow parameter cannot be resolved from the container.
 */
final class ParameterResolutionException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(
        string $paramName,
        string $className,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            "Cannot resolve parameter '{$paramName}' (type: {$className}). " .
            "Laravel autowiring failed: {$previous?->getMessage()}",
            0,
            $previous
        );
    }
}
