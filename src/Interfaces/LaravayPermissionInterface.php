<?php
namespace Laravay\Interfaces;

/**
 * Laravay
 *
 * @license MIT
 * @package Laravay
 */
interface LaravayPermissionInterface {


    /**
     * Many-to-many relationships model with role model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * Morph by Many relationship between the permission and the one of the possible user models.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function morphByUserRelation($relationship);

}