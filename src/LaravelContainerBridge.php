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

use Chevere\Container\Interfaces\DependenciesInterface;
use Chevere\Parameter\Interfaces\ObjectParameterInterface;
use Chevere\Workflow\Laravel\Exceptions\ContainerEntryNotFoundException;
use Chevere\Workflow\Laravel\Exceptions\ParameterResolutionException;
use Illuminate\Contracts\Container\Container as LaravelContainer;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * PSR-11 bridge that enables Chevere's dependency extraction to work
 * seamlessly with Laravel's autowiring and service container.
 *
 * Maps Chevere parameter names to their type-hints and resolves via Laravel's DI.
 */
final class LaravelContainerBridge implements ContainerInterface
{
    /**
     * @var array<string, string> Maps parameter names to class names
     */
    private array $parameterTypeMap = [];

    public function __construct(
        private LaravelContainer $laravel,
        ?DependenciesInterface $dependencies = null
    ) {
        if ($dependencies !== null) {
            $this->buildParameterTypeMap($dependencies);
        }
    }

    public function has(string $id): bool
    {
        if ($this->laravel->bound($id)) {
            return true;
        }
        if (isset($this->parameterTypeMap[$id])) {
            $className = $this->parameterTypeMap[$id];

            return class_exists($className) || interface_exists($className);
        }
        if (class_exists($id) || interface_exists($id)) {
            return true;
        }

        return false;
    }

    public function get(string $id): mixed
    {
        if ($this->laravel->bound($id)) {
            return $this->laravel->make($id);
        }
        if (isset($this->parameterTypeMap[$id])) {
            $className = $this->parameterTypeMap[$id];

            try {
                return $this->laravel->make($className);
            } catch (Throwable $e) {
                throw new ParameterResolutionException($id, $className, $e);
            }
        }

        try {
            return $this->laravel->make($id);
        } catch (Throwable $e) {
            throw new ContainerEntryNotFoundException($id, $e);
        }
    }

    /**
     * Build a map of parameter names to their type hints from workflow dependencies.
     */
    private function buildParameterTypeMap(DependenciesInterface $dependencies): void
    {
        $parameters = $dependencies->parameters();
        foreach ($parameters->keys() as $paramName) {
            $parameter = $parameters->get($paramName);
            if ($parameter instanceof ObjectParameterInterface) {
                $this->parameterTypeMap[$paramName] = $parameter->type()->typeHinting();
            }
        }
    }
}
