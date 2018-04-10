<?php
namespace Laravay\Commands;

use Traitor\Traitor;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravay\Traits\LaravayUserTrait;

class AddLaravayUserTraitUseCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravay:add-trait';

    /**
     * Trait added to User model
     *
     * @var string
     */
    protected $targetTrait = LaravayUserTrait::class;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $models = $this->getUserModels() ;
        foreach ($models as $model) {
            if (!class_exists($model)) {
                $this->error("Class $model does not exist.");
                return;
            }
            if ($this->alreadyUsesLaravayUserTrait($model)) {
                $this->error("Class $model already uses LaravayUserTrait.");
                continue;
            }
            Traitor::addTrait($this->targetTrait)->toClass($model);
        }
        $this->info("LaravayUserTrait added successfully to {$models->implode(', ')}");
    }

    /**
     * Check if the class already uses LaravayUserTrait.
     *
     * @param  string  $model
     * @return bool
     */
    protected function alreadyUsesLaravayUserTrait($model)
    {
        return in_array(LaravayUserTrait::class, class_uses($model));
    }

    /**
     * Get the description of which clases the LaratrustUserTrait was added.
     *
     * @return string
     */
    public function getDescription()
    {
        return "Add LaravayUserTrait to {$this->getUserModels()->implode(', ')} class";
    }

    /**
     * Return the User models array.
     *
     * @return array
     */
    protected function getUserModels()
    {
        return new Collection(Config::get('laravay.user_models', []));
    }
}