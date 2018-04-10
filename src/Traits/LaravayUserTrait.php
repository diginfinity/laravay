<?php
namespace Laravay\Traits;

use Laravay\Helpers\LaravayHelper;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Trait LaravayUserTrait
 * @package Laravay
 */
trait LaravayUserTrait
{
    use LaravayEventsTrait;

    /**
     * Boots the user model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaravayUserTrait()
    {
        $flushCache = function ($user) {
            $user->flushCache();
        };
        // If the user doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }
        static::deleted($flushCache);
        static::saved($flushCache);
        static::deleting(function ($user) {
            if (method_exists($user, 'bootSoftDeletes') && !$user->forceDeleting) {
                return;
            }
            $user->roles()->sync([]);
            $user->permissions()->sync([]);
        });
    }

    /**
     * Tries to return all the cached roles of the user.
     * If it can't bring the roles from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cachedRoles()
    {
        $cacheKey = 'laravay_roles_for_user_' . $this->getKey();
        if (! Config::get('laravay.use_cache')) {
            return $this->roles()->get();
        }
        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->roles()->get()->toArray();
        });
    }

    /**
     * Tries to return all the cached permissions of the user
     * and if it can't bring the permissions from the cache,
     * it brings them back from the DB.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cachedPermissions()
    {
        $cacheKey = 'laravay_permissions_for_user_' . $this->getKey();
        if (! Config::get('laravay.use_cache')) {
            return $this->permissions()->get();
        }
        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->permissions()->get()->toArray();
        });
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles()
    {
        $roles = $this->morphToMany(
            'App\Role',
            'user',
            'role_user',
            'user_id',
            'role_id'
        )->withPivot('group_id');

        return $roles;
    }


    /**
     * Many-to-Many relations with Group associated through the roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function rolesGroups()
    {
        return $this->morphToMany(
            'App\Group',
            'user',
            'role_user',
            'user_id',
            'group_id'
        )->withPivot('role_id');
    }


    /**
     * Many-to-Many relations with Permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions()
    {
        $permissions = $this->morphToMany(
            'App\Permission',
            'user',
            'permission_user',
            'user_id',
            'permission_id'
        )->withPivot('group_id');
        return $permissions;
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param  string|array  $name       Role name or array of role names.
     * @param  string|bool   $team      Team name or requiredAll roles.
     * @param  bool          $requireAll All roles in the array are required.
     * @return bool
     */
    public function hasRole($name, $team = null, $requireAll = false)
    {
        $name = LaravayHelper::standardize($name);
        list($team, $requireAll) = LaravayHelper::assignRealValuesTo($team, $requireAll, 'is_bool');
        if (is_array($name)) {
            if (empty($name)) {
                return true;
            }
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName, $team);
                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }
            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll.
            return $requireAll;
        }
        $team = LaravayHelper::fetchGroup($team);
        foreach ($this->cachedRoles() as $role) {
            if ($role['name'] == $name && LaravayHelper::isInSameGroup($role, $team)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param  string|array  $name       Role name or array of role names.
     * @param  string|bool   $team      Team name or requiredAll roles.
     * @param  bool          $requireAll All roles in the array are required.
     * @return bool
     */
    public function isA($role, $team = null, $requireAll = false)
    {
        return $this->hasRole($role, $team, $requireAll);
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param  string|array  $name       Role name or array of role names.
     * @param  string|bool   $team      Team name or requiredAll roles.
     * @param  bool          $requireAll All roles in the array are required.
     * @return bool
     */
    public function isAn($role, $team = null, $requireAll = false)
    {
        return $this->hasRole($role, $team, $requireAll);
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll roles.
     * @param  bool  $requireAll All roles in the array are required.
     * @return bool
     */
    public function hasPermission($permission, $team = null, $requireAll = false)
    {
        $permission = LaravayHelper::standardize($permission);
        list($team, $requireAll) = LaravayHelper::assignRealValuesTo($team, $requireAll, 'is_bool');
        if (is_array($permission)) {
            if (empty($permission)) {
                return true;
            }
            foreach ($permission as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName, $team);
                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }
            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found.
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll.
            return $requireAll;
        }
        $team = LaravayHelper::fetchGroup($team);
        foreach ($this->cachedPermissions() as $perm) {
            if (LaravayHelper::isInSameGroup($perm, $team) && str_is($permission, $perm['name'])) {
                return true;
            }
        }
        foreach ($this->cachedRoles() as $role) {
            $role = LaravayHelper::hidrateModel(Config::get('laratrust.models.role'), $role);
            if (LaravayHelper::isInSameGroup($role, $team) && $role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission Permission string or array of permissions.
     * @param  string|bool  $team      Team name or requiredAll roles.
     * @param  bool  $requireAll All permissions in the array are required.
     * @return bool
     */
    public function can($permission, $team = null, $requireAll = false)
    {
        return $this->hasPermission($permission, $team, $requireAll);
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param  string|array  $permission  Permission string or array of permissions.
     * @param  string|bool  $team  Team name or requiredAll roles.
     * @param  bool  $requireAll  All permissions in the array are required.
     * @return bool
     */
    public function isAbleTo($permission, $team = null, $requireAll = false)
    {
        return $this->hasPermission($permission, $team, $requireAll);
    }

    /**
     * Checks role(s) and permission(s).
     *
     * @param  string|array  $roles       Array of roles or comma separated string
     * @param  string|array  $permissions Array of permissions or comma separated string.
     * @param  string|bool  $team      Team name or requiredAll roles.
     * @param  array  $options     validate_all (true|false) or return_type (boolean|array|both)
     * @throws \InvalidArgumentException
     * @return array|bool
     */
    public function ability($roles, $permissions, $team = null, $options = [])
    {
        list($team, $options) = LaravayHelper::assignRealValuesTo($team, $options, 'is_array');
        // Convert string to array if that's what is passed in.
        $roles = LaravayHelper::standardize($roles, true);
        $permissions = LaravayHelper::standardize($permissions, true);
        // Set up default values and validate options.
        $options = LaravayHelper::checkOrSet('validate_all', $options, [false, true]);
        $options = LaravayHelper::checkOrSet('return_type', $options, ['boolean', 'array', 'both']);
        // Loop through roles and permissions and check each.
        $checkedRoles = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role, $team);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->hasPermission($permission, $team);
        }
        // If validate all and there is a false in either.
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && !(in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }
        // Return based on option.
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        }
        return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  string  $relationship
     * @param  mixed  $object
     * @param  mixed  $team
     * @return static
     */
    private function attachModel($relationship, $object, $team)
    {
        if (!LaravayHelper::isValidRelationship($relationship)) {
            throw new InvalidArgumentException;
        }
        $attributes = [];
        $objectType = Str::singular($relationship);
        $object = LaravayHelper::getIdFor($object, $objectType);

        $team = LaravayHelper::getIdFor($team, 'group');
        if (
        $this->$relationship()
            ->wherePivot(LaravayHelper::groupForeignKey(), $team)
            ->wherePivot("l{$objectType}", $object)
            ->count()
        ) {
            return $this;
        }
        $attributes[LaravayHelper::groupForeignKey()] = $team;

        $this->$relationship()->attach(
            $object,
            $attributes
        );
        $this->flushCache();
        $this->fireLaravayEvent("{$objectType}.attached", [$this, $object, $team]);
        return $this;
    }


    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  string  $relationship
     * @param  mixed  $object
     * @param  mixed  $team
     * @return static
     */
    private function detachModel($relationship, $object, $team)
    {
        if (!LaravayHelper::isValidRelationship($relationship)) {
            throw new InvalidArgumentException;
        }
        $objectType = Str::singular($relationship);
        $relationshipQuery = $this->$relationship();
        $relationshipQuery->wherePivot(
            LaravayHelper::groupForeignKey(),
            LaravayHelper::getIdFor($team, 'group')
        );
        $object = LaravayHelper::getIdFor($object, $objectType);
        $relationshipQuery->detach($object);
        $this->flushCache();
        $this->fireLaravayEvent("{$objectType}.detached", [$this, $object, $team]);
        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's sync() method.
     *
     * @param  string  $relationship
     * @param  mixed  $objects
     * @param  mixed  $team
     * @param  boolean  $detaching
     * @return static
     */
    private function syncModels($relationship, $objects, $team, $detaching)
    {
        if (!LaravayHelper::isValidRelationship($relationship)) {
            throw new InvalidArgumentException;
        }
        $objectType = Str::singular($relationship);
        $mappedObjects = [];
        $useTeams = true;
        $team = $useTeams ? LaravayHelper::getIdFor($team, 'group') : null;
        foreach ($objects as $object) {
            if ($useTeams && $team) {
                $mappedObjects[LaravayHelper::getIdFor($object, $objectType)] = [LaravayHelper::groupForeignKey() => $team];
            } else {
                $mappedObjects[] = LaravayHelper::getIdFor($object, $objectType);
            }
        }
        $relationshipToSync = $this->$relationship();
        if ($useTeams && $team) {
            $relationshipToSync->wherePivot(LaravayHelper::groupForeignKey(), $team);
        }
        $result = $relationshipToSync->sync($mappedObjects, $detaching);
        $this->flushCache();
        $this->fireLaravayvent("{$objectType}.synced", [$this, $result, $team]);
        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $role
     * @param  mixed  $team
     * @return static
     */
    public function attachRole($role, $team = null)
    {
        return $this->attachModel('roles', $role, $team);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $role
     * @param  mixed  $team
     * @return static
     */
    public function detachRole($role, $team = null)
    {
        return $this->detachModel('roles', $role, $team);
    }

    /**
     * Attach multiple roles to a user.
     *
     * @param  mixed  $roles
     * @param  mixed  $team
     * @return static
     */
    public function attachRoles($roles = [], $team = null)
    {
        foreach ($roles as $role) {
            $this->attachRole($role, $team);
        }
        return $this;
    }

    /**
     * Detach multiple roles from a user.
     *
     * @param  mixed  $roles
     * @param  mixed  $team
     * @return static
     */
    public function detachRoles($roles = [], $team = null)
    {
        if (empty($roles)) {
            $roles = $this->roles()->get();
        }
        foreach ($roles as $role) {
            $this->detachRole($role, $team);
        }
        return $this;
    }

    /**
     * Sync roles to the user.
     *
     * @param  array  $roles
     * @param  mixed  $team
     * @param  boolean  $detaching
     * @return static
     */
    public function syncRoles($roles = [], $team = null, $detaching = true)
    {
        return $this->syncModels('roles', $roles, $team, $detaching);
    }

    /**
     * Sync roles to the user without detaching.
     *
     * @param  array  $roles
     * @param  mixed  $team
     * @return static
     */
    public function syncRolesWithoutDetaching($roles = [], $team = null)
    {
        return $this->syncRoles($roles, $team, false);
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function attachPermission($permission, $team = null)
    {
        return $this->attachModel('permissions', $permission, $team);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param  mixed  $permission
     * @param  mixed  $team
     * @return static
     */
    public function detachPermission($permission, $team = null)
    {
        return $this->detachModel('permissions', $permission, $team);
    }

    /**
     * Attach multiple permissions to a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function attachPermissions($permissions = [], $team = null)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission, $team);
        }
        return $this;
    }

    /**
     * Detach multiple permissions from a user.
     *
     * @param  mixed  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function detachPermissions($permissions = [], $team = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }
        foreach ($permissions as $permission) {
            $this->detachPermission($permission, $team);
        }
        return $this;
    }

    /**
     * Sync permissions to the user.
     *
     * @param  array  $permissions
     * @param  mixed  $team
     * @param  boolean  $detaching
     * @return static
     */
    public function syncPermissions($permissions = [], $team = null, $detaching = true)
    {
        return $this->syncModels('permissions', $permissions, $team, $detaching);
    }

    /**
     * Sync permissions to the user without detaching.
     *
     * @param  array  $permissions
     * @param  mixed  $team
     * @return static
     */
    public function syncPermissionsWithoutDetaching($permissions = [], $team = null)
    {
        return $this->syncPermissions($permissions, $team, false);
    }

    /**
     * Checks if the user owns the thing.
     *
     * @param  Object  $thing
     * @param  string  $foreignKeyName
     * @return boolean
     */
    public function owns($thing, $foreignKeyName = null)
    {
        if ($thing instanceof \Laravay\Interfaces\LaravayOwnableInterface) {
            $ownerKey = $thing->ownerKey($this);
        } else {
            $className = (new \ReflectionClass($this))->getShortName();
            $foreignKeyName = $foreignKeyName ?: Str::snake($className . 'Id');
            $ownerKey = $thing->$foreignKeyName;
        }
        return $ownerKey == $this->getKey();
    }


    /**
     * Checks if the user has some role and if he owns the thing.
     *
     * @param  string|array  $role
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function hasRoleAndOwns($role, $thing, $options = [])
    {
        $options = LaravayHelper::checkOrSet('requireAll', $options, [false, true]);
        $options = LaravayHelper::checkOrSet('group', $options, [null]);
        $options = LaravayHelper::checkOrSet('foreignKeyName', $options, [null]);
        return $this->hasRole($role, $options['group'], $options['requireAll'])
            && $this->owns($thing, $options['foreignKeyName']);
    }


    /**
     * Checks if the user can do something and if he owns the thing.
     *
     * @param  string|array  $permission
     * @param  Object  $thing
     * @param  array  $options
     * @return boolean
     */
    public function canAndOwns($permission, $thing, $options = [])
    {
        $options = LaravayHelper::checkOrSet('requireAll', $options, [false, true]);
        $options = LaravayHelper::checkOrSet('foreignKeyName', $options, [null]);
        $options = LaravayHelper::checkOrSet('group', $options, [null]);
        return $this->hasPermission($permission, $options['group'], $options['requireAll'])
            && $this->owns($thing, $options['foreignKeyName']);
    }


    /**
     * Return all the user permissions.
     *
     * @return boolean
     */
    public function allPermissions()
    {
        $roles = $this->roles()->with('permissions')->get();
        $roles = $roles->flatMap(function ($role) {
            return $role->permissions;
        });
        return $this->permissions->merge($roles)->unique('name');
    }


    /**
     * This scope allows to retrive the users with a specific role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereRoleIs($query, $role = '')
    {
        return $query->whereHas('roles', function ($roleQuery) use ($role) {
            $roleQuery->where('name', $role);
        });
    }


    /**
     * This scope allows to retrieve the users with a specific permission.
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWherePermissionIs($query, $permission = '')
    {
        return $query->whereHas('roles.permissions', function ($permissionQuery) use ($permission) {
            $permissionQuery->where('name', $permission);
        })->orWhereHas('permissions', function ($permissionQuery) use ($permission) {
            $permissionQuery->where('name', $permission);
        });
    }


    /**
     * Flush the user's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('laravay_roles_for_user_' . $this->getKey());
        Cache::forget('laravay_permissions_for_user_' . $this->getKey());
    }

    /**
     * Handles the call to the magic methods with can,
     * like $user->canEditSomething().
     * @param  string  $method
     * @param  array  $parameters
     * @return boolean
     */
    private function handleMagicCan($method, $parameters)
    {
        $case = str_replace('_case', '', Config::get('laravay.magic_can_method_case'));
        $method = preg_replace('/^can/', '', $method);
        if ($case == 'kebab') {
            $permission = Str::snake($method, '-');
        } else {
            $permission = Str::$case($method);
        }
        return $this->hasPermission($permission, array_shift($parameters), false);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (!preg_match('/^can[A-Z].*/', $method)) {
            return parent::__call($method, $parameters);
        }
        return $this->handleMagicCan($method, $parameters);
    }
}