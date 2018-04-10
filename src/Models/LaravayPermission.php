<?php
namespace Laravay\Models;

/**
 * This file is a part of Laravay core
 *
 * @license MIT
 * @package Laravay
 */
use Illuminate\Database\Eloquent\Model;
use Laravay\Interfaces\LaravayPermissionInterface;
use Laravay\Traits\LaravayPermissionTrait;


class LaravayPermission extends Model implements LaravayPermissionInterface {

    use LaravayPermissionTrait;

    /**
     * Database table name
     *
     * @var string
     */
    protected $table;

    /**
     * LaravayPermission constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'permissions';
    }
}


