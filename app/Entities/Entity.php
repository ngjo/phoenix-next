<?php

namespace App\Entities;

use ArrayAccess;
use Contentful\Delivery\Asset;
use Contentful\Delivery\DynamicEntry;
use InvalidArgumentException;

/**
 * A convenience wrapper for Contentful's DynamicEntity.
 */
class Entity implements ArrayAccess
{
    /**
     * The Contentful entry.
     *
     * @var \Contentful\Delivery\DynamicEntry
     */
    private $entry;

    /**
     * Create instance of the Campaign class.
     *
     * @param \Contentful\Delivery\DynamicEntry $entry
     */
    public function __construct(DynamicEntry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * Dynamically access the campaign's attributes.
     *
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (! $this->offsetExists($property)) {
            return null;
        }

        // @see: DynamicEntry's __call implementation.
        // If trying to get a translation for a field that isn't
        // filled out, it'll throw ErrorException so we catch that.
        try {
            $value = $this->entry->{'get'.ucwords($property)}();
        } catch (\ErrorException $error) {
            $value = null;
        }

        if ($value instanceof Asset) {
            return $value;
        }

        if ($value instanceof DynamicEntry) {
            return new self($value);
        }

        if (is_array($value)) {
            return collect($value)->map(function($value) {
                return new Entity($value);
            });
        }

        return $value;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $fields = array_keys($this->entry->getContentType()->getFields());

        return in_array($offset, $fields);
    }

    /**
     * Offset to set.
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new InvalidArgumentException;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new InvalidArgumentException;
    }
}