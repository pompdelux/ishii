<?php

namespace Ishii;

class Page implements \ArrayAccess
{
    protected $container = array();


    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }


    public function setTitle($title)
    {
        $this->container['title'] = $title;
    }

    public function getTitle()
    {
        return $this->container['title'];
    }

    public function __call($name, $arguments)
    {
        $var = strtolower(substr($name, 3));
        switch(strtolower(substr($name, 0, 3))) {
            case 'get':
                if (isset($this->container[$var])) {
                    return $this->container[$var];
                }

                return null;
                break;
            case 'set':
                $this->container[$var] = $arguments[0];
                break;
        }
    }
}
