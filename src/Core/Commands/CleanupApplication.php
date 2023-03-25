<?php declare(strict_types=1);

namespace Simtabi\Laranail\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Simtabi\Laranail\Core\Facades\LaranailFacade;

class CleanupApplication extends Command
{

    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tidy {--cache : Indicates if routes and configs should be cached}
                                 {--db : Indicates if there should be a fresh database migration and seeding task}
                                 {--all : Indicates if there should be a fresh database migration and seeding task, routes and configs should be cached.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the following commands under the hood ("clear-compiled, config:clear, route:clear, cache:clear, view:clear, optimize, migrate:fresh, db:seed") to clean up the application';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->line('');
        $this->comment('Let\'s run some tasks to tidy up your application...');

        $this->resetFiles();
        $this->clearCache();

        // Cache things...
        if ($this->option('cache')) {
            $this->resetCache();
        }

        // Run everything...
        if ($this->option('all')) {
            $this->clearCache();
            $this->resetCache();
            $this->resetDb();
        }

        // Remigrate database and seed (fresh) things...
        if ($this->option('db')) {
            $this->resetDb();
        }

        $this->comment('You application has been tidied successfully! Happy Codding:)');
        $this->line('');

        return 0;
    }

    private function clearCache()
    {
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('event:clear');
        $this->call('optimize');
        $this->call('clear-compiled');
        $this->call('optimize:clear');

        $this->info('Application cache files files have been cleared successfully!');
        $this->line('');
    }

    private function resetCache()
    {
        $this->call('view:cache');
        $this->call('route:cache');
        $this->call('event:cache');
        $this->call('config:cache');

        $this->info('Application view, route, event, & config files have been cached successfully!');
        $this->line('');
    }

    private function resetDb()
    {
        $this->call('migrate:fresh');
        $this->call('db:seed');

        $this->info('Application database has been reinitialized successfully!');
        $this->line('');
    }

    private function resetFiles(): static
    {
        if (LaranailFacade::deleteStorageSymlink()) {
            $this->call('storage:link');

            $this->info('Application storage link been created successfully!');
            $this->line('');
        }

        if(LaranailFacade::clearCache()) {
            $this->info('Application cache files have been cleared successfully!');
            $this->line('');
        }

        if(LaranailFacade::clearLogFiles()) {
            $this->info('Application log files have been cleared successfully!');
            $this->line('');
        }

        return $this;
    }

}