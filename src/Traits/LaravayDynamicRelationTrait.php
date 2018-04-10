<?php
namespace Laravay\Traits;


use Illuminate\Support\Facades\Config;

trait LaravayDynamicRelationTrait
{


    /**
     * Dynamically retrieve the relationship value with the possible user models.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, array_keys(Config::get('laravay.user_models')))) {
            return $this->getUserRelationVal($key);
        }
        return parent::__get($key);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (in_array($method, array_keys(Config::get('laravay.user_models')))) {
            return $this->morphByUserRelation($method);
        }
        return parent::__call($method, $params);
    }

    /**
     * Get a relationship
     *
     * @param string $key
     * @return mixed
     */
    public function getUserRelationVal($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }
        return $this->getRelationshipFromMethod($key);
    }



}