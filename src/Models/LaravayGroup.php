<?php
namespace Laravay\Models;

use Illuminate\Database\Eloquent\Model;
use Laravay\Interfaces\LaravayGroupInterface;
use Laravay\Traits\LaravayGroupTrait;

/**
 * Class LaravayGroup
 * @package Laravay
 * @license MIT
 */
class LaravayGroup extends Model implements LaravayGroupInterface {

    use LaravayGroupTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * LaravayGroup constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'groups';
    }
}