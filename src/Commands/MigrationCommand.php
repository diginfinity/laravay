<?php
namespace Laravay\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;


class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravay:migration';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Laravay specifications.';
    /**
     * Suffix of the migration name.
     *
     * @var string
     */
    protected $migrationSuffix = 'laravay_setup_tables';
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel->view->addNamespace('laravay', __DIR__.'/../../views');
        $this->line('');
        $this->info("Laravay Migration Creation.");
        $this->line('');
        $this->comment($this->generateMigrationMessage());
        $existingMigrations = $this->alreadyExistingMigrations();
        $defaultAnswer = true;
        if ($existingMigrations) {
            $this->line('');
            $this->warn($this->getExistingMigrationsWarning($existingMigrations));
            $defaultAnswer = false;
        }
        $this->line('');
        if (! $this->confirm("Proceed with the migration creation?", $defaultAnswer)) {
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
        $output = $this->laravel->view
            ->make('laravay::migration')
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
     * Generate the message to display when running the
     * console command showing what tables are going
     * to be created.
     *
     * @return string
     */
    protected function generateMigrationMessage()
    {
        $tb = [
            /**
             * Roles table.
             */
            'roles' => 'roles',

            /**
             * Permissions table.
             */
            'permissions' => 'permissions',

            /**
             * Teams table.
             */
            'teams' => 'teams',

            /**
             * Role - User intermediate table.
             */
            'role_user' => 'role_user',

            /**
             * Permission - User intermediate table.
             */
            'permission_user' => 'permission_user',

            /**
             * Permission - Role intermediate table.
             */
            'permission_role' => 'permission_role',

        ];

        $teams = true;

        $tables = Collection::make($tb)
            ->reject(function ($value, $key) use ( $teams ) {
                return $key == 'groups' && !$teams;
            })
            ->sort();
        return "A migration that creates {$tables->implode(', ')} "
            . "tables will be created in database/migrations directory";
    }
    /**
     * Build a warning regarding possible duplication
     * due to already existing migrations.
     *
     * @param  array  $existingMigrations
     * @return string
     */
    protected function getExistingMigrationsWarning(array $existingMigrations)
    {
        if (count($existingMigrations) > 1) {
            $base = "Laravay migrations already exist.\nFollowing files were found: ";
        } else {
            $base = "Laravay migration already exists.\nFollowing file was found: ";
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
     * @param  string|null  $date
     * @return string
     */
    protected function getMigrationPath($date = null)
    {
        $date = $date ?: date('Y_m_d_His');
        return database_path("migrations/${date}_{$this->migrationSuffix}.php");
    }
}