<?php
namespace Laravay\Setup;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravay:setup';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup migration and models for Laravay';
    /**
     * Commands to call with their description.
     *
     * @var array
     */
    protected $calls = [
        'laravay:migration' => 'Creating migration',
        'laravay:role' => 'Creating Role model',
        'laravay:permission' => 'Creating Permission model',
        'laravay:add-trait' => 'Adding LaravayUserTrait to User model'
    ];
    /**
     * Create a new command instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->calls['laravay:group'] = 'Creating Group model';
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->calls as $command => $info) {
            $this->line(PHP_EOL . $info);
            $this->call($command);
        }
    }
}