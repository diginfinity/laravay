<?php
namespace Laravay\Traits;

/**
 * Trait LaravayEventsTrait
 * @package Laravay
 * @license MIT
 *
 *
 * EventsTraits is copied from the Laratrust package
 * @url https://github.com/santigarcor/laratrust
 */

trait LaravayEventsTrait
{
    /**
     * Register an observer to the Laravay events.
     *
     * @param  object|string  $class
     * @return void
     */
    public static function laravayObserve($class)
    {
        $observables = [
            'roleAttached',
            'roleDetached',
            'permissionAttached',
            'permissionDetached',
            'roleSynced',
            'permissionSynced',
        ];
        $className = is_string($class) ? $class : get_class($class);
        foreach ($observables as $event) {
            if (method_exists($class, $event)) {
                static::registerLaravayEvent(\Illuminate\Support\Str::snake($event, '.'), $className.'@'.$event);
            }
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return mixed
     */
    protected function fireLaravayEvent($event, array $payload)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }
        return static::$dispatcher->fire(
            "laravay.{$event}: ".static::class,
            $payload
        );
    }

    /**
     * Register a laravay event with the dispatcher.
     *
     * @param  string  $event
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function registerLaravayEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;
            static::$dispatcher->listen("laravay.{$event}: {$name}", $callback);
        }
    }

    /**
     * Register a role attached laravay event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleAttached($callback)
    {
        static::registerLaravayEvent('role.attached', $callback);
    }

    /**
     * Register a role detached laravay event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleDetached($callback)
    {
        static::registerLaravayEvent('role.detached', $callback);
    }

    /**
     * Register a permission attached laratrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionAttached($callback)
    {
        static::registerLaravayEvent('permission.attached', $callback);
    }

    /**
     * Register a permission detached laratrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionDetached($callback)
    {
        static::registerLaravayEvent('permission.detached', $callback);
    }

    /**
     * Register a role synced laravay event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleSynced($callback)
    {
        static::registerLaravayEvent('role.synced', $callback);
    }

    /**
     * Register a permission synced laravay event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionSynced($callback)
    {
        static::registerLaravayEvent('permission.synced', $callback);
    }


}