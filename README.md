# Chevere Workflow for Laravel

![Chevere](chevere.svg)

[![Build](https://img.shields.io/github/actions/workflow/status/chevere/workflow-laravel/test.yml?branch=1.0&style=flat-square)](https://github.com/chevere/workflow-laravel/actions)
![Code size](https://img.shields.io/github/languages/code-size/chevere/workflow-laravel?style=flat-square)
[![Apache-2.0](https://img.shields.io/github/license/chevere/workflow-laravel?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-blueviolet?style=flat-square)](https://phpstan.org/)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchevere%2Fworkflow-laravel%2F1.0)](https://dashboard.stryker-mutator.io/reports/github.com/chevere/workflow-laravel/1.0)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=chevere_workflow-laravel&metric=alert_status)](https://sonarcloud.io/dashboard?id=chevere_workflow-laravel)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_workflow-laravel&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chevere_workflow-laravel)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_workflow-laravel&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chevere_workflow-laravel)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_workflow-laravel&metric=security_rating)](https://sonarcloud.io/dashboard?id=chevere_workflow-laravel)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=chevere_workflow-laravel&metric=coverage)](https://sonarcloud.io/dashboard?id=chevere_workflow-laravel)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=chevere_workflow-laravel&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chevere_workflow-laravel)
[![CodeFactor](https://www.codefactor.io/repository/github/chevere/workflow-laravel/badge)](https://www.codefactor.io/repository/github/chevere/workflow-laravel)

## Summary

**[Chevere Workflow](https://chevere.org/packages/workflow) for Laravel** is a PHP library for building and executing multi-step procedures with automatic dependency resolution. Define independent jobs that can run synchronously or asynchronously, pass data between them using typed responses, and let the engine handle execution order automatically.

**Key features:**

* **Declarative job definitions**: Define what to do, not how to orchestrate it
* **Automatic dependency graph**: Jobs execute in optimal order based on their dependencies
* **Sync and async execution**: Mix blocking and non-blocking jobs freely
* **Type-safe responses**: Access job outputs with full type safety
* **Conditional execution**: Run jobs based on variables or previous responses
* **Built-in retry policies**: Handle transient failures automatically
* **Testable**: Each job is independently testable and workflow graph can be verified

You define jobs and how they connect and depend on each other, **Chevere Workflow** figures out the execution order and runs them accordingly.

---

## What does this solve for Laravel developers?

* **Dependency Management**: Like `Bus::chain()`, execution order follows dependencies. But instead of manually sequencing every step, simply declare what data each job needs with `response()` and the engine infers the order.
* **Parallel Processing**: Like `Bus::batch()`, independent jobs run concurrently using `async()`.
* **Sequential Flows**: Like `Pipeline`, you can model linear operations but workflow graphs also handle branching and fan-in without custom plumbing.
* **Conditional Execution**: Like feature flags or guard clauses, jobs can be skipped using declarative `withRunIf()` and `withRunIfNot()`.

---

## How This Integration Works

This Laravel package is a thin wrapper around [chevere/workflow](https://chevere.org/packages/workflow). It provides:

| Component                 | What It Does                                                                            |
| ------------------------- | --------------------------------------------------------------------------------------- |
| `WorkflowServiceProvider` | Registers the workflow system with Laravel's service container                          |
| `AbstractWorkflow`        | Base class you extend to define workflows (like Laravel's `Mailable` or `Notification`) |
| `WorkflowManager`         | Service that runs workflows with Laravel's container for dependency injection           |
| `Workflow` Facade         | Static access (`Workflow::run(...)`) for convenience                                    |
| Artisan Commands          | `make:workflow`, `workflow:run`, `workflow:list`                                        |

This integration uses a PSR-11 bridge backed by Laravel's container, so your Laravel services (Eloquent models, mailers, cache, etc.) are automatically available for dependency injection inside workflow jobs.

---

## Installing

Workflow for Laravel is available through [Packagist](https://packagist.org/packages/chevere/workflow-laravel) and the repository source is at [chevere/workflow-laravel](https://github.com/chevere/workflow-laravel).

### Step 1: Install the package

```bash
composer require chevere/workflow-laravel
```

### Step 2: Verify installation

```bash
php artisan workflow:list
```

You should see: `No workflows found.`

---

## Quick Start: Your First Workflow

### Step 1: Generate a workflow

```bash
php artisan make:workflow GreetUser
```

This creates `app/Workflows/GreetUser.php`:

```php
namespace App\Workflows;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\sync;
use function Chevere\Workflow\variable;
use function Chevere\Workflow\workflow;

class GreetUser extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            greet: sync(
                fn(string $name): string => "Hello, {$name}!",
                name: variable('name'),
            ),
        );
    }
}
```

### Step 2: Run it from a controller

```php
namespace App\Http\Controllers;

use App\Workflows\GreetUser;
use Illuminate\Http\JsonResponse;

class GreetController extends Controller
{
    public function __invoke(GreetUser $workflow): JsonResponse
    {
        $run = $workflow->run(name: 'Taylor');

        return response()->json([
            'message' => $run->response('greet')->string(),
            // "Hello, Taylor!"
        ]);
    }
}
```

### Step 3: Or use the Facade

```php
use Chevere\Workflow\Laravel\Facades\Workflow;
use App\Workflows\GreetUser;

$run = Workflow::run(GreetUser::class, name: 'Taylor');
echo $run->response('greet')->string(); // "Hello, Taylor!"
```

---

## Core Concepts

Chevere Workflow uses **jobs**, **variables**, and **responses** to build a dependency graph that automatically determines execution order. Jobs can be `sync()` or `async()`, variables are runtime inputs declared with `variable()`, and responses reference outputs from previous jobs using `response()`.

For detailed information about these core concepts, execution graphs, and how the workflow engine works, see the [Chevere Workflow documentation](https://chevere.org/packages/workflow).

**Laravel-specific behavior:** When you extend `AbstractWorkflow`, Laravel's service container is automatically available for dependency injection in your job callables.

---

## The AbstractWorkflow Class

This is the main class you extend. It follows the same pattern as Laravel's `Mailable`, `Notification`, or `FormRequest`:

```php
namespace App\Workflows;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\{workflow, sync, async, variable, response};

class ProcessOrder extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            validate: sync(
                fn(int $orderId): array => ['id' => $orderId, 'valid' => true],
                orderId: variable('orderId'),
            ),
            charge: sync(
                fn(array $order): array => ['charged' => true, 'amount' => 99.99],
                order: response('validate'),
            ),
            sendReceipt: async(
                fn(array $charge): bool => true,
                charge: response('charge'),
            ),
            updateInventory: async(
                fn(array $order): bool => true,
                order: response('validate'),
            ),
        );
    }
}
```

### Methods Available

| Method                     | Returns                | Description                          |
| -------------------------- | ---------------------- | ------------------------------------ |
| `run(mixed ...$variables)` | `RunInterface`         | Execute the workflow                 |
| `getWorkflow()`            | `WorkflowInterface`    | Get the workflow definition (cached) |
| `graph()`                  | `array<array<string>>` | Get the execution graph              |

### Using in Controllers

Laravel auto-injects `AbstractWorkflow` subclasses via the container:

```php
class OrderController extends Controller
{
    public function store(ProcessOrder $workflow, Request $request): JsonResponse
    {
        $run = $workflow->run(orderId: $request->input('order_id'));

        return response()->json([
            'charged' => $run->response('charge', 'charged')->bool(),
            'amount'  => $run->response('charge', 'amount')->float(),
        ]);
    }
}
```

---

## Using the Facade

The `Workflow` facade provides static access to the `WorkflowManager`:

```php
use Chevere\Workflow\Laravel\Facades\Workflow;

// Run a workflow class
$run = Workflow::run(ProcessOrder::class, orderId: 42);

// Run an inline workflow definition
use function Chevere\Workflow\{workflow, sync, variable};

$run = Workflow::run(
    workflow(
        greet: sync(
            fn(string $name): string => "Hello, {$name}!",
            name: variable('name'),
        ),
    ),
    name: 'World'
);
```

---

## Using the WorkflowManager

Inject `WorkflowManager` when you prefer dependency injection over facades:

```php
use Chevere\Workflow\Laravel\WorkflowManager;

class OrderService
{
    public function __construct(
        private WorkflowManager $workflows
    ) {}

    public function processOrder(int $orderId): array
    {
        $run = $this->workflows->run(ProcessOrder::class, orderId: $orderId);

        return $run->toArray();
    }
}
```

---

## Artisan Commands

### `make:workflow`

Generate a new workflow class:

```bash
php artisan make:workflow ProcessOrder
# Creates: app/Workflows/ProcessOrder.php
```

### `workflow:run`

Run a workflow from the command line:

```bash
php artisan workflow:run "App\Workflows\GreetUser" --var=name=Artisan
```

`workflow:run` accepts any class that extends `Chevere\Workflow\Laravel\AbstractWorkflow`.

Output:

```plain
Running workflow: App\Workflows\GreetUser
Execution graph:
  Level 0: greet
Workflow completed successfully.
UUID: a1b2c3d4-...
Responses:
  greet: Hello, Artisan!
```

Pass multiple variables:

```bash
php artisan workflow:run "App\Workflows\ProcessOrder" --var=orderId=42 --var=notify=true
```

### `workflow:list`

List all workflows found in the application:

```bash
php artisan workflow:list
```

Output:

```plain
+-----------------------------------------+--------+-------------+
| Workflow                                | Jobs   | Graph Depth |
+-----------------------------------------+--------+-------------+
| App\Workflows\ProcessOrder              | 4 jobs | 3 levels    |
| App\Workflows\ImageResize               | 4 jobs | 2 levels    |
+-----------------------------------------+--------+-------------+
```

Workflows are auto-discovered by scanning your application's `app/` directory for classes extending `AbstractWorkflow`.

---

## Real-World Use Cases

### Use Case 1: User Registration Flow

This is the **best starting point** for Laravel developers. It mirrors what you'd normally build with events/listeners or job chains, but with automatic ordering.

```php
namespace App\Workflows;

use App\Actions\CreateUser;
use App\Actions\SendWelcomeEmail;
use App\Actions\LogRegistration;
use App\Actions\AssignDefaultRole;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\{workflow, sync, async, variable, response};

class RegisterUser extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            // Step 1: Validate and create the user (must be sync — everything else depends on it)
            createUser: sync(
                CreateUser::class,
                email: variable('email'),
                password: variable('password'),
                name: variable('name'),
            ),

            // Step 2a: Send welcome email (async — can run async with 2b and 2c)
            sendWelcome: async(
                SendWelcomeEmail::class,
                userId: response('createUser', 'id'),
                email: response('createUser', 'email'),
            ),

            // Step 2b: Log the registration (async — runs async with 2a and 2c)
            logEvent: async(
                LogRegistration::class,
                userId: response('createUser', 'id'),
            ),

            // Step 2c: Assign default role (async — runs async with 2a and 2b)
            assignRole: async(
                AssignDefaultRole::class,
                userId: response('createUser', 'id'),
                role: 'member',
            ),
        );
    }
}
```

**Execution graph:**

```plain
Level 0: [createUser]                          ← runs first
Level 1: [sendWelcome, logEvent, assignRole]   ← run async after createUser
```

**In your controller:**

```php
class RegisterController extends Controller
{
    public function store(Request $request, RegisterUser $workflow): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            'name'     => 'required|string',
        ]);

        $run = $workflow->run(
            email: $request->input('email'),
            password: $request->input('password'),
            name: $request->input('name'),
        );

        return response()->json([
            'user_id' => $run->response('createUser', 'id')->int(),
            'message' => 'Registration complete',
        ], 201);
    }
}
```

**What each Action class looks like** (using `chevere/action`):

```php
namespace App\Actions;

use App\Models\User;
use Chevere\Action\Action;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Action
{
    public function __invoke(string $email, string $password, string $name): array
    {
        $user = User::create([
            'email'    => $email,
            'password' => Hash::make($password),
            'name'     => $name,
        ]);

        return ['id' => $user->id, 'email' => $user->email];
    }
}
```

You can also use plain closures instead of Action classes — see the Quick Start example.

---

### Use Case 2: Image Processing Pipeline

Process multiple image sizes async, then store them all:

```php
namespace App\Workflows;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use Illuminate\Support\Facades\Storage;
use function Chevere\Workflow\{workflow, sync, async, variable, response};

class ProcessImage extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            // These 3 resize jobs run async (no dependencies on each other)
            thumb: async(
                fn(string $path): string => $this->resize($path, 150, 150),
                path: variable('imagePath'),
            ),
            medium: async(
                fn(string $path): string => $this->resize($path, 800, 600),
                path: variable('imagePath'),
            ),
            large: async(
                fn(string $path): string => $this->resize($path, 1920, 1080),
                path: variable('imagePath'),
            ),

            // This runs after ALL three above complete
            store: sync(
                fn(string $thumb, string $medium, string $large): array => [
                    'thumb'  => Storage::put('thumbs', $thumb),
                    'medium' => Storage::put('medium', $medium),
                    'large'  => Storage::put('large', $large),
                ],
                thumb: response('thumb'),
                medium: response('medium'),
                large: response('large'),
            ),
        );
    }

    private function resize(string $path, int $w, int $h): string
    {
        // Your image processing logic (Intervention Image, GD, Imagick, etc.)
        return "/resized/{$w}x{$h}/" . basename($path);
    }
}
```

**Execution graph:**

```plain
Level 0: [thumb, medium, large]   ← async
Level 1: [store]                  ← after all three
```

---

### Use Case 3: Order Processing with Conditional Steps

This example demonstrates using `withRunIf()` for conditional job execution ([learn more](https://chevere.org/packages/workflow)):

```php
namespace App\Workflows;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\{workflow, sync, async, variable, response};

class ProcessOrder extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            validate: sync(
                fn(int $orderId): array => ['id' => $orderId, 'total' => 150.00, 'valid' => true],
                orderId: variable('orderId'),
            ),

            charge: sync(
                fn(array $order): array => [
                    'transactionId' => 'txn_' . $order['id'],
                    'amount' => $order['total'],
                ],
                order: response('validate'),
            ),

            // Conditionally send receipt based on runtime variable
            sendReceipt: async(
                fn(string $txnId, float $amount): bool => true,
                txnId: response('charge', 'transactionId'),
                amount: response('charge', 'amount'),
            )->withRunIf(
                variable('sendReceipt')
            ),
        );
    }
}
```

---

### Use Case 4: API Aggregation

Fetch data from multiple external APIs async:

```php
namespace App\Workflows;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use Illuminate\Support\Facades\Http;
use function Chevere\Workflow\{workflow, sync, async, variable, response};

class FetchDashboardData extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            // All three API calls run async
            weather: async(
                fn(string $city): array => Http::get("https://api.weather.example/v1/{$city}")->json(),
                city: variable('city'),
            ),
            news: async(
                fn(string $category): array => Http::get("https://api.news.example/v1/{$category}")->json(),
                category: variable('newsCategory'),
            ),
            stocks: async(
                fn(string $symbol): array => Http::get("https://api.stocks.example/v1/{$symbol}")->json(),
                symbol: variable('stockSymbol'),
            ),

            // Combine all results after they arrive
            dashboard: sync(
                fn(array $weather, array $news, array $stocks): array => [
                    'weather' => $weather,
                    'news' => $news,
                    'stocks' => $stocks,
                    'generated_at' => now()->toISOString(),
                ],
                weather: response('weather'),
                news: response('news'),
                stocks: response('stocks'),
            ),
        );
    }
}
```

Without Workflow, those 3 API calls would run sequentially (3x the latency). With `async()`, they run concurrently.

---

### Use Case 5: Data ETL Pipeline

Extract, transform, and load data with clear step separation:

```php
namespace App\Workflows;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Laravel\AbstractWorkflow;
use function Chevere\Workflow\{workflow, sync, variable, response};

class ImportCsvData extends AbstractWorkflow
{
    protected function definition(): WorkflowInterface
    {
        return workflow(
            // Extract: read and parse CSV
            extract: sync(
                fn(string $path): array => array_map('str_getcsv', file($path)),
                path: variable('csvPath'),
            ),

            // Transform: clean and validate rows
            transform: sync(
                fn(array $rows): array => array_filter($rows, fn($row) => count($row) >= 3),
                rows: response('extract'),
            ),

            // Load: insert into database
            load: sync(
                fn(array $rows): int => count($rows), // DB::table('imports')->insert($rows)
                rows: response('transform'),
            ),
        );
    }
}
```

---

## Advanced Workflow Features

Chevere Workflow supports several advanced features:

* **Conditional Execution**: Use `withRunIf()` and `withRunIfNot()` to conditionally skip jobs based on variables or previous job responses
* **Retry Policies**: Configure automatic retries with `withRetry()` for transient failures
* **Sync/Async Execution**: Use `sync()` for sequential jobs and `async()` for async execution
* **Mermaid Graphs**: Visualize your workflow's execution graph with Mermaid syntax

For detailed documentation on these features, see the [Chevere Workflow documentation](https://chevere.org/packages/workflow).

---

## Dependency Injection

This is where the Laravel integration shines. **Laravel's container is automatically passed to Chevere Workflow**, so your Action classes can use constructor injection:

```php
use Chevere\Action\Action;
use App\Services\PaymentGateway;
use Illuminate\Log\LogManager;

class ChargePayment extends Action
{
    public function __construct(
        private PaymentGateway $gateway,  // ← auto-injected from Laravel
        private LogManager $logger,       // ← auto-injected from Laravel
    ) {}

    public function __invoke(int $orderId, float $amount): array
    {
        $this->logger->info("Charging order {$orderId}: \${$amount}");
        $result = $this->gateway->charge($amount);

        return ['transactionId' => $result->id, 'status' => $result->status];
    }
}
```

Any service registered in Laravel's container can be injected. No extra configuration needed.

---

## Error Handling

When a job fails, Chevere Workflow wraps the original exception in a `WorkflowException`:

```php
use Chevere\Workflow\Exceptions\WorkflowException;

try {
    $run = $workflow->run(orderId: 42);
} catch (WorkflowException $e) {
    // Which job failed?
    echo $e->name;        // "charge"

    // The job instance
    $job = $e->job;

    // The original exception
    $original = $e->throwable;
    echo $original->getMessage();
}
```

In a Laravel controller:

```php
public function store(Request $request, ProcessOrder $workflow): JsonResponse
{
    try {
        $run = $workflow->run(orderId: $request->input('order_id'));

        return response()->json($run->toArray());
    } catch (WorkflowException $e) {
        report($e->throwable); // Log with Laravel

        return response()->json([
            'error' => "Job '{$e->name}' failed: " . $e->throwable->getMessage(),
        ], 500);
    }
}
```

---

## Testing Your Workflows

### Setup

In a Laravel app consuming this package, use your application's base test case as usual:

```php
namespace Tests\Feature;

use Tests\TestCase;

class ProcessOrderTest extends TestCase
{
    // ...
}
```

If you are developing this package itself (or another package), use Orchestra Testbench and register `WorkflowServiceProvider`:

```php
use Chevere\Workflow\Laravel\WorkflowServiceProvider;
use Orchestra\Testbench\TestCase;

class ProcessOrderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [WorkflowServiceProvider::class];
    }
}
```

### Test the Execution Graph

Verify jobs run in the expected order:

```php
public function testGraphOrder(): void
{
    $workflow = $this->app->make(ProcessOrder::class);
    $graph = $workflow->graph();

    // Level 0: validate runs first
    $this->assertSame(['validate'], $graph[0]);

    // Level 1: charge runs after validate
    $this->assertSame(['charge'], $graph[1]);

    // Level 2: sendReceipt, updateInventory run async
    $this->assertContains('sendReceipt', $graph[2]);
    $this->assertContains('updateInventory', $graph[2]);
}
```

### Test Responses

```php
public function testWorkflowResponses(): void
{
    $workflow = $this->app->make(ProcessOrder::class);
    $run = $workflow->run(orderId: 42, sendReceipt: true, hasDiscount: false);

    $this->assertTrue($run->response('charge', 'charged')->bool());
    $this->assertSame(42, $run->response('validate', 'id')->int());
}
```

### Test Conditional Execution

```php
public function testSkippedJobs(): void
{
    $workflow = $this->app->make(ProcessOrder::class);
    $run = $workflow->run(orderId: 42, sendReceipt: false, hasDiscount: false);

    $this->assertTrue($run->skip()->contains('sendReceipt'));
    $this->assertTrue($run->skip()->contains('applyDiscount'));
}
```

### Test Error Handling

```php
use Chevere\Workflow\Exceptions\WorkflowException;

public function testJobFailure(): void
{
    $this->expectException(WorkflowException::class);

    $workflow = $this->app->make(FailingWorkflow::class);
    $workflow->run(input: 'invalid');
}
```

### Test with the Facade

```php
use Chevere\Workflow\Laravel\Facades\Workflow;

public function testFacade(): void
{
    $run = Workflow::run(ProcessOrder::class, orderId: 42);

    $this->assertNotEmpty($run->uuid());
}
```

---

## FAQ

### How is this different from Laravel Queues?

Laravel Queues push jobs to a queue worker (Redis, SQS, database) for background processing. Chevere Workflow runs jobs **in the current process** (with AMP async for concurrency). Use queues when you need background processing; use workflows when you need coordinated multi-step logic with data flowing between steps. By the way, you can dispatch a queued job that internally runs a workflow!

### Can I use Eloquent models inside jobs?

Yes. Laravel's container is passed to the workflow engine, so any service registered in the container (including Eloquent, facades, etc.) works normally.

### Can I mix this with Laravel's queue system?

Yes. You can dispatch a queued job that internally runs a workflow. The workflow itself runs synchronously/async within the queue worker process.

```php
class ProcessOrderJob implements ShouldQueue
{
    public function handle(ProcessOrder $workflow): void
    {
        $workflow->run(orderId: $this->orderId);
    }
}
```

### Does the Facade work in Tinker?

Yes:

```php
>>> Chevere\Workflow\Laravel\Facades\Workflow::run(App\Workflows\GreetUser::class, name: 'Tinker')
```

## Documentation

Documentation is available at [chevere.org/packages/workflow-laravel](https://chevere.org/packages/workflow-laravel).

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
