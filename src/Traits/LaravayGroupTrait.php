<?php
namespace Laravay\Traits;

use Illuminate\Support\Facades\Config;

/**
 * Trait LaravayGroupTrait
 * @package Laravay\Traits
 */
trait LaravayGroupTrait
{
    use LaravayDynamicRelationTrait;

    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function morphByUserRelation($relationship)
    {
        return $this->morphedByMany(
            Config::get('laravay.user_models')[$relationship],
            'user',
            'role_user',
            'group_id',
            'user_id'
        );
    }

    /**
     * Boots the team model and attaches event listener to
     * remove the many-to-many records when trying to delete.
     * Will NOT delete any records if the team model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootLaravayGroupTrait()
    {
        static::deleting(function ($group) {
            if (method_exists($group, 'bootSoftDeletes') && !$group->forceDeleting) {
                return;
            }
            foreach (array_keys(Config::get('laravay.user_models')) as $key) {
                $group->$key()->sync([]);
            }
        });
    }
}