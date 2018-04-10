<?php
namespace Laravay\Traits;

/**
 * Laravay
 *
 * @package Laravay
 * @license MIT
 */
use Illuminate\Support\Facades\Config;

trait LaravayPermissionTrait {


    use LaravayDynamicRelationTrait;

    /**
     * Boots the permission model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * Taken from the Laratrust package.
     */
    public function bootLaravayPermissionTrait()
    {
        static::deleting(function ($permission) {
            if (!method_exists('App\Permission', 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }
        });

        static::deleting(function ($permission) {
            if (method_exists($permission, 'bootSoftDeletes') && !$permission->forceDeleting) {
                return;
            }
            $permission->roles()->sync([]);
            foreach (array_keys(Config::get('laravay.user_models')) as $key) {
                $permission->$key()->sync([]);
            }
        });
    }


    /**
     * Many-to-Many relations with role model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            'App\Role',
            'permission_role',
            'permission_id',
            'role_id'
        );
    }

    /**
     * Morph by Many relationship between the permission and the one of the possible user models.
     *
     * @param string $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     *
     * Taken from the Laratrust package. (changed the name)
     */
    public function morphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('laravay.user_models')[$relationship],
            'user',
            'permission_user',
            'permission_id',
            'user_id'
        );
    }


}