<?php
namespace Laravay\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;


class SetupGroupsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravay:setup-groups';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the groups feature in case it is not used';
    /**
     * Suffix of the migration name.
     *
     * @var string
     */
    protected $migrationSuffix = 'laravay_setup_groups';
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        $this->laravel->view->addNamespace('laravay', __DIR__ . '/../../views');
        $this->line('');
        $this->info("The Laravay groups feature setup is going to add a migration and a model");
        $existingMigrations = $this->alreadyExistingMigrations();
        if ($existingMigrations) {
            $this->line('');
            $this->warn($this->getExistingMigrationsWarning($existingMigrations));
        }
        $this->line('');
        if (! $this->confirm("Proceed with the migration creation?", "yes")) {
            return;
        }
        $this->line('');
        $this->line("Creating migration");
        if ($this->createMigration()) {
            $this->info("Migration created successfully.");
        } else {
            $this->error(
                "Couldn't create migration.\n".
                "Check the write permissions within the database/migrations directory."
            );
        }
        $this->line('Creating Group model');
        $this->call('laratrust:group');
        $this->line('');
    }
    /**
     * Create the migration.
     *
     * @return bool
     */
    protected function createMigration()
    {
        $migrationPath = $this->getMigrationPath();
        $this->call('view:clear');
        $output = $this->laravel->view
            ->make('laratrust::setup-groups')
            ->with(['laravay' => Config::get('laravay')])
            ->render();
        if (!file_exists($migrationPath) && $fs = fopen($migrationPath, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }
        return false;
    }
    /**
     * Build a warning regarding possible duplication
     * due to already existing migrations.
     *
     * @param  array $existingMigrations
     * @return string
     */
    protected function getExistingMigrationsWarning(array $existingMigrations)
    {
        if (count($existingMigrations) > 1) {
            $base = "Laravay setup teams migrations already exist.\nFollowing files were found: ";
        } else {
            $base = "Laravay setup teams migration already exists.\nFollowing file was found: ";
        }
        return $base . array_reduce($existingMigrations, function ($carry, $fileName) {
                return $carry . "\n - " . $fileName;
            });
    }
    /**
     * Check if there is another migration
     * with the same suffix.
     *
     * @return array
     */
    protected function alreadyExistingMigrations()
    {
        $matchingFiles = glob($this->getMigrationPath('*'));
        return array_map(function ($path) {
            return basename($path);
        }, $matchingFiles);
    }
    /**
     * Get the migration path.
     *
     * The date parameter is optional for ability
     * to provide a custom value or a wildcard.
     *
     * @param  string|null $date
     * @return string
     */
    protected function getMigrationPath($date = null)
    {
        $date = $date ?: date('Y_m_d_His');
        return database_path("migrations/${date}_{$this->migrationSuffix}.php");
    }
}