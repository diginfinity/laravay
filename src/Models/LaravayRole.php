<?php
namespace Laravay\Models;

use Illuminate\Database\Eloquent\Model;
use Laravay\Interfaces\LaravayRoleInterface;
use Laravay\Traits\LaravayRoleTrait;

class LaravayRole extends Model implements LaravayRoleInterface {

    use LaravayRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * LaravayRole constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'roles';
    }

}