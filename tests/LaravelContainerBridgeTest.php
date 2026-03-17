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

namespace Chevere\Workflow\Laravel\Tests;

use Chevere\Container\Interfaces\DependenciesInterface;
use Chevere\Parameter\Interfaces\ObjectParameterInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Workflow\Laravel\Exceptions\ContainerEntryNotFoundException;
use Chevere\Workflow\Laravel\Exceptions\ParameterResolutionException;
use Chevere\Workflow\Laravel\LaravelContainerBridge;
use Chevere\Workflow\Laravel\Tests\Fixtures\AutowiredWorkflow;
use Chevere\Workflow\Laravel\Tests\Fixtures\Services\GreetingService;
use Exception;
use Illuminate\Contracts\Container\Container as LaravelContainer;
use Psr\Container\ContainerInterface;
use stdClass;

class LaravelContainerBridgeTest extends TestCase
{
    public function testHasReturnsTrueForBoundService(): void
    {
        $this->app->bind('test.service', fn () => new GreetingService());
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertTrue($bridge->has('test.service'));
    }

    public function testHasReturnsTrueForExistingClass(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertTrue($bridge->has(GreetingService::class));
        $this->assertTrue($bridge->has(stdClass::class));
    }

    public function testHasReturnsTrueForExistingInterface(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertTrue($bridge->has(ContainerInterface::class));
    }

    public function testHasReturnsFalseForNonExistentClass(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertFalse($bridge->has('NonExistent\\Class\\Name'));
    }

    public function testHasWithParameterTypeMapForExistingClass(): void
    {
        /** @var AutowiredWorkflow $workflow */
        $workflow = $this->app->make(AutowiredWorkflow::class);
        $dependencies = $workflow->getWorkflow()->dependencies();
        $bridge = new LaravelContainerBridge($this->app, $dependencies);
        $this->assertTrue($bridge->has('greetingService'));
        $bridge = new LaravelContainerBridge($this->app, $dependencies);
        $this->assertFalse($bridge->has('nonExistentParameter'));
    }

    public function testHasWithParameterTypeMapForMissingClassReturnsFalse(): void
    {
        $parameters = $this->createMock(ParametersInterface::class);
        $parameters->method('keys')->willReturn(['missing']);
        $parameters->method('get')->with('missing')->willReturn(
            $this->createConfiguredMock(ObjectParameterInterface::class, [
                'type' => $this->createConfiguredMock(TypeInterface::class, [
                    'typeHinting' => 'NonExistent\\Class\\Name',
                ]),
            ])
        );
        $dependencies = $this->createMock(DependenciesInterface::class);
        $dependencies->method('parameters')->willReturn($parameters);
        $bridge = new LaravelContainerBridge($this->app, $dependencies);
        $this->assertFalse($bridge->has('missing'));
    }

    public function testGetReturnsBoundService(): void
    {
        $service = new GreetingService();
        $this->app->instance('test.service', $service);
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertSame($service, $bridge->get('test.service'));
    }

    public function testGetUsesBoundContainerMakeCall(): void
    {
        $service = new GreetingService();

        $parameters = $this->createMock(ParametersInterface::class);
        $parameters->method('keys')->willReturn(['test.service']);
        $parameters->method('get')->with('test.service')->willReturn(
            $this->createConfiguredMock(ObjectParameterInterface::class, [
                'type' => $this->createConfiguredMock(TypeInterface::class, [
                    'typeHinting' => GreetingService::class,
                ]),
            ])
        );
        $dependencies = $this->createMock(DependenciesInterface::class);
        $dependencies->method('parameters')->willReturn($parameters);
        $container = $this->createMock(LaravelContainer::class);
        $container->expects($this->once())
            ->method('bound')
            ->with('test.service')
            ->willReturn(true);
        $container->method('make')
            ->willReturnCallback(static function (string $id) use ($service) {
                if ($id === GreetingService::class) {
                    throw new \RuntimeException('Should not resolve parameter type when bound');
                }

                return $service;
            });
        $bridge = new LaravelContainerBridge($container, $dependencies);
        $this->assertSame($service, $bridge->get('test.service'));
    }

    public function testGetResolvesExistingClass(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $instance = $bridge->get(GreetingService::class);
        $this->assertInstanceOf(GreetingService::class, $instance);
    }

    public function testGetWithParameterTypeMapResolvesClass(): void
    {
        /** @var AutowiredWorkflow $workflow */
        $workflow = $this->app->make(AutowiredWorkflow::class);
        $dependencies = $workflow->getWorkflow()->dependencies();
        $bridge = new LaravelContainerBridge($this->app, $dependencies);
        $service = $bridge->get('greetingService');
        $this->assertInstanceOf(GreetingService::class, $service);
    }

    public function testGetWithParameterTypeMapThrowsParameterResolutionException(): void
    {
        /** @var AutowiredWorkflow $workflow */
        $workflow = $this->app->make(AutowiredWorkflow::class);
        $dependencies = $workflow->getWorkflow()->dependencies();
        $this->app->bind(GreetingService::class, function () {
            throw new Exception('Failed to resolve');
        });
        $bridge = new LaravelContainerBridge($this->app, $dependencies);
        $this->expectException(ParameterResolutionException::class);
        $this->expectExceptionMessage('Failed to resolve');
        $bridge->get('greetingService');
    }

    public function testGetThrowsExceptionForNonExistentClass(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $this->expectException(ContainerEntryNotFoundException::class);
        $bridge->get('NonExistent\\Class\\Name');
    }

    public function testImplementsPsr11ContainerInterface(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertInstanceOf(ContainerInterface::class, $bridge);
    }

    public function testConstructorWithoutDependenciesCreatesEmptyParameterTypeMap(): void
    {
        $bridge = new LaravelContainerBridge($this->app);
        $this->assertTrue($bridge->has(GreetingService::class));
        $this->assertFalse($bridge->has('greetingService'));
    }

    public function testConstructorWithDependenciesBuildsParameterTypeMap(): void
    {
        /** @var AutowiredWorkflow $workflow */
        $workflow = $this->app->make(AutowiredWorkflow::class);
        $dependencies = $workflow->getWorkflow()->dependencies();
        $bridge = new LaravelContainerBridge($this->app, $dependencies);
        $this->assertTrue($bridge->has(GreetingService::class));
        $this->assertTrue($bridge->has('greetingService'));
    }
}
