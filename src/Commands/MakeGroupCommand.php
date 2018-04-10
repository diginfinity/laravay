<?php
namespace Laravay\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeGroupCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravay:group';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Group model if it does not exist';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Group model';
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__. '/../../stubs/group.stub';
    }
    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return 'Group';
    }
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}