<?php
namespace Laravay\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
class MakeSeederCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravay:seeder';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the seeder following the Laravay specifications.';
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel->view->addNamespace('laravay', __DIR__.'/../../views');
        if (file_exists($this->seederPath())) {
            $this->line('');
            $this->warn("The LaravaySeeder file already exists. Delete the existing one if you want to create a new one.");
            $this->line('');
            return;
        }
        if ($this->createSeeder()) {
            $this->info("Seeder successfully created!");
        } else {
            $this->error(
                "Couldn't create seeder.\n".
                "Check the write permissions within the database/seeds directory."
            );
        }
        $this->line('');
    }
    /**
     * Create the seeder
     *
     * @return bool
     */
    protected function createSeeder()
    {
        $permission = 'App\Permission';
        $role = 'App\Role';
        $rolePermissions ='permission_role';
        $roleUsers = 'role_user';
        $user = new Collection(Config::get('laravay.user_models', ['App\User']));
        $user = $user->first();
        $output = $this->laravel->view->make('laravay::seeder')
            ->with(compact([
                'role',
                'permission',
                'user',
                'rolePermissions',
                'roleUsers',
            ]))
            ->render();
        if ($fs = fopen($this->seederPath(), 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }
        return false;
    }
    /**
     * Get the seeder path.
     *
     * @return string
     */
    protected function seederPath()
    {
        return database_path("seeds/LaravaySeeder.php");
    }
}