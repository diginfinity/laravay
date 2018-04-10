<?php
namespace Laravay\Interfaces;

/**
 * Interface LaravayGroupInterface
 * @package Laravay
 * @license MIT
 */

interface LaravayGroupInterface {

    /**
     * Morph by Many relationship between the role and the one of the possible user models.
     *
     * @param  string $relationship
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function morphByUserRelation($relationship);
}