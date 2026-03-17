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

use Chevere\Workflow\Laravel\Tests\Fixtures\GreetWorkflow;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandTest extends TestCase
{
    public function testMakeWorkflowCommand(): void
    {
        $this->artisan(
            'make:workflow',
            [
                'name' => 'TestWorkflow',
            ]
        )
            ->assertSuccessful();
        $filePath = app_path('Workflows/TestWorkflow.php');
        $this->assertFileExists($filePath);
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('class TestWorkflow extends AbstractWorkflow', $content);
        $this->assertStringContainsString('protected function definition(): WorkflowInterface', $content);
        $this->assertStringContainsString('return workflow(', $content);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testListWorkflowsEmpty(): void
    {
        $this->artisan('workflow:list')
            ->expectsOutput('No workflows found.')
            ->assertSuccessful();
    }

    public function testListWorkflowsFindsWorkflow(): void
    {
        $this->artisan(
            'make:workflow',
            [
                'name' => 'DiscoveredWorkflow',
            ]
        )
            ->assertSuccessful();
        $filePath = app_path('Workflows/DiscoveredWorkflow.php');
        require_once $filePath;
        $workflowClass = 'App\\Workflows\\DiscoveredWorkflow';
        $this->artisan('workflow:list')
            ->expectsTable(
                ['Workflow', 'Jobs', 'Graph Depth'],
                [[$workflowClass, '0 jobs', '0 levels']]
            )
            ->assertSuccessful();
        $filePath = app_path('Workflows/DiscoveredWorkflow.php');
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testListWorkflowsIgnoresNonWorkflowClasses(): void
    {
        $filePath = app_path('NonWorkflow.php');
        file_put_contents(
            $filePath,
            <<<PHP
            <?php

            namespace App;

            class NonWorkflow
            {
            }
            PHP
        );
        require_once $filePath;
        $this->artisan('workflow:list')
            ->expectsOutput('No workflows found.')
            ->assertSuccessful();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testListWorkflowsShowsAllWorkflows(): void
    {
        $this->artisan(
            'make:workflow',
            [
                'name' => 'DiscoveredWorkflowA',
            ]
        )
            ->assertSuccessful();
        $this->artisan(
            'make:workflow',
            [
                'name' => 'DiscoveredWorkflowB',
            ]
        )
            ->assertSuccessful();
        $fileA = app_path('Workflows/DiscoveredWorkflowA.php');
        $fileB = app_path('Workflows/DiscoveredWorkflowB.php');
        require_once $fileB;
        require_once $fileA;
        $baseNamespace = app()->getNamespace() . 'Workflows\\';
        $workflowClassA = $baseNamespace . 'DiscoveredWorkflowA';
        $workflowClassB = $baseNamespace . 'DiscoveredWorkflowB';
        $this->artisan('workflow:list')
            ->expectsTable(
                ['Workflow', 'Jobs', 'Graph Depth'],
                [
                    [$workflowClassA, '0 jobs', '0 levels'],
                    [$workflowClassB, '0 jobs', '0 levels'],
                ]
            )
            ->assertSuccessful();
        if (file_exists($fileA)) {
            unlink($fileA);
        }
        if (file_exists($fileB)) {
            unlink($fileB);
        }
    }

    public function testListWorkflowsWhenAppPathMissing(): void
    {
        $originalAppPath = app()->path();
        $missingPath = sys_get_temp_dir() . '/chevere_test_app_path_missing_' . uniqid();
        if (is_dir($missingPath)) {
            rmdir($missingPath);
        }
        app()->useAppPath($missingPath);

        try {
            $this->artisan('workflow:list')
                ->expectsOutput('No workflows found.')
                ->assertSuccessful();
        } finally {
            app()->useAppPath($originalAppPath);
        }
    }

    public function testRunWorkflowNotFound(): void
    {
        $this->artisan(
            'workflow:run',
            [
                'workflow' => 'App\\Workflows\\NonExistent',
            ]
        )
            ->expectsOutput('Workflow class not found: App\\Workflows\\NonExistent')
            ->assertFailed();
    }

    public function testRunWorkflowInvalidClass(): void
    {
        $this->artisan(
            'workflow:run',
            [
                'workflow' => self::class,
            ]
        )
            ->expectsOutput('Class must extend ' . \Chevere\Workflow\Laravel\AbstractWorkflow::class)
            ->assertFailed();
    }

    public function testRunWorkflowSuccess(): void
    {
        $this->artisan(
            'workflow:run',
            [
                'workflow' => GreetWorkflow::class,
                '--var' => ['name=Artisan'],
            ]
        )
            ->expectsOutput('Running workflow: ' . GreetWorkflow::class)
            ->expectsOutput('Execution graph:')
            ->expectsOutput('  Level 0: greet')
            ->expectsOutputToContain('Workflow completed successfully.')
            ->expectsOutputToContain('UUID: ')
            ->expectsOutputToContain('Responses:')
            ->expectsOutputToContain('greet: Hello, Artisan!')
            ->assertSuccessful();
    }

    public function testRunWorkflowWithSkippedJobs(): void
    {
        $this->artisan(
            'workflow:run',
            [
                'workflow' => Fixtures\ConditionalWorkflow::class,
                '--var' => ['enabled=false'],
            ]
        )
            ->expectsOutput('Skipped jobs: conditionalJob')
            ->assertSuccessful();
    }

    public function testRunWorkflowWarnsOnInvalidVariableFormat(): void
    {
        $this->artisan(
            'workflow:run',
            [
                'workflow' => GreetWorkflow::class,
                '--var' => ['invalid', 'name=Artisan'],
            ]
        )
            ->expectsOutput('Skipping invalid variable format: invalid (expected key=value)')
            ->assertSuccessful();
    }

    public function testRunWorkflowDisplaysJsonForArrayResponses(): void
    {
        $this->artisan(
            'workflow:run',
            [
                'workflow' => Fixtures\UserOnboardingWorkflow::class,
                '--var' => ['email=artisan@example.com', 'name=Artisan'],
            ]
        )
            ->expectsOutputToContain('Responses:')
            ->expectsOutputToContain('sendWelcome:')
            ->assertSuccessful();
    }

    public function testRunWorkflowPrettyPrintsJsonResponses(): void
    {
        $output = new BufferedOutput();

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->call(
            'workflow:run',
            [
                'workflow' => Fixtures\UserOnboardingWorkflow::class,
                '--var' => ['email=artisan@example.com', 'name=Artisan'],
            ],
            $output
        );

        $display = str_replace("\r\n", "\n", $output->fetch());

        $this->assertStringContainsString("sendWelcome: {\n    \"email\": \"artisan@example.com\"", $display);
    }
}
