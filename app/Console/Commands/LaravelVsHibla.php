<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Rcalicdan\FiberAsync\Api\AsyncDB;

class LaravelVsHibla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:laravel-vs-hibla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compares the performance of Hibla AsyncQueryBuilder against the standard Laravel Query Builder for a heavy I/O workload.';

    /**
     * The number of queries to execute for the benchmark.
     */
    const QUERY_COUNT = 1000;

    /**
     * The name of the temporary table for benchmarking.
     */
    const TABLE_NAME = 'benchmark_users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info("  HIBLA vs. LARAVEL - I/O BENCHMARK");
        $this->info("=================================================");
        $this->line("Running " . self::QUERY_COUNT . " individual 'find' queries against each builder...");

        // 1. Setup the database environment
        $this->setupDatabase();

        // 2. Run the benchmarks
        $laravelResults = $this->runLaravelTest();
        $hiblaResults = $this->runHiblaTest();

        // 3. Display the final report
        $this->displayReport($laravelResults, $hiblaResults);

        return self::SUCCESS;
    }

    /**
     * Sets up a temporary database table and seeds it with data.
     */
    private function setupDatabase(): void
    {
        $this->comment("\nSetting up the database...");

        Schema::dropIfExists(self::TABLE_NAME);
        Schema::create(self::TABLE_NAME, function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        $users = [];
        for ($i = 0; $i < self::QUERY_COUNT; $i++) {
            $users[] = ['name' => 'User ' . $i, 'email' => 'user' . $i . '@example.com'];
        }

        foreach (array_chunk($users, 200) as $chunk) {
            DB::table(self::TABLE_NAME)->insert($chunk);
        }

        $this->info("Database setup complete.");
    }

    private function runLaravelTest(): array
    {
        $this->comment("\nRunning Laravel (Synchronous with simulated I/O wait) Test...");

        $startMemory = memory_get_peak_usage(true);
        $startTime = microtime(true);

        for ($i = 1; $i <= self::QUERY_COUNT; $i++) {
            $user = DB::table(self::TABLE_NAME)->find($i);
            usleep(5000);
        }

        $endTime = microtime(true);
        $endMemory = memory_get_peak_usage(true);

        $this->info("Laravel Test Complete.");

        return [
            'time' => $endTime - $startTime,
            'memory' => $endMemory - $startMemory,
        ];
    }

    /**
     * Runs the benchmark using Hibla's concurrent AsyncQueryBuilder.
     */
    private function runHiblaTest(): array
    {
        $this->comment("\nRunning Hibla (Concurrent) Test...");



        $startMemory = memory_get_peak_usage(true);
        $startTime = microtime(true);

        $tasks = [];
        $baseSql = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `id` = ?";
        for ($i = 1; $i <= self::QUERY_COUNT; $i++) {
            $tasks[] = function () use ($i, $baseSql) {
                AsyncDB::raw($baseSql, [$i]);
                await(delay(0.005));
            };
        }

        $users = run_all($tasks);

        $endTime = microtime(true);
        $endMemory = memory_get_peak_usage(true);

        $this->info("Hibla Test Complete.");

        return [
            'time' => $endTime - $startTime,
            'memory' => $endMemory - $startMemory,
        ];
    }

    /**
     * Displays the final, formatted benchmark report.
     */
    private function displayReport(array $laravelResults, array $hiblaResults): void
    {
        $laravelTime = $laravelResults['time'];
        $hiblaTime = $hiblaResults['time'];

        $laravelMem = $laravelResults['memory'];
        $hiblaMem = $hiblaResults['memory'];

        $laravelQPS = self::QUERY_COUNT / $laravelTime;
        $hiblaQPS = self::QUERY_COUNT / $hiblaTime;

        $improvement = (($laravelTime - $hiblaTime) / $laravelTime) * 100;

        $this->info("\n\n================ FINAL REPORT ================");
        $this->table(
            ['Metric', 'Laravel (Sync)', 'Hibla (Concurrent)'],
            [
                ['Execution Time', number_format($laravelTime, 4) . ' s', number_format($hiblaTime, 4) . ' s'],
                ['Peak Memory Usage', number_format($laravelMem / 1024 / 1024, 2) . ' MB', number_format($hiblaMem / 1024 / 1024, 2) . ' MB'],
                ['Queries Per Second', number_format($laravelQPS, 2) . ' QPS', number_format($hiblaQPS, 2) . ' QPS'],
            ]
        );

        $this->line("\n<fg=green;options=bold>Conclusion: For this heavy I/O workload, Hibla's AsyncQueryBuilder was " . number_format($improvement, 2) . "% faster.</>");
    }
}
