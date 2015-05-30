<?php

namespace kidol\curl;

/**
 * Class to provide Yii-like setters/getters.
 */
class Object
{
    
    public function __set($name, $value)
    {
        $setter = "set{$name}";
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, "get{$name}")) {
            throw new \RuntimeException(sprintf("Setting read-only property: %s::\${$name}", get_class($this)));
        } else {
            throw new \RuntimeException(sprintf("Setting unknown property: %s::\${$name}", get_class($this)));
        }
    }
    
    public function __get($name)
    {
        $getter = "get{$name}";
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, "set{$name}")) {
            throw new \RuntimeException(sprintf("Getting write-only property: %s::\${$name}", get_class($this)));
        } else {
            throw new \RuntimeException(sprintf("Getting unknown property: %s::\${$name}", get_class($this)));
        }
    }
    
    public function __isset($name)
    {
        $getter = "get{$name}";
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }
    
    public function __unset($name)
    {
        $setter = "set{$name}";
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, "get{$name}")) {
            throw new \RuntimeException(sprintf("Unsetting read-only property: %s::\${$name}", get_class($this)));
        }
    }
    
}