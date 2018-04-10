<?php
namespace Laravay\Interfaces;


interface LaravayOwnableInterface
{

    /**
     * Gets the owner key value inside the model or object.
     *
     * @param  mixed  $owner
     * @return mixed
     */
    public function ownerKey($owner);
}