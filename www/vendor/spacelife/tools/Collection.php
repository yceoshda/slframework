<?php

namespace spacelife\tools;

use spacelife\exception\SLException;

/**
* Collection
*
*   Original version by Grafikart (www.grafikart.fr)
*
*   Added: __get & __set methods to allow key access as $obj->key
*   Added: allow using collections on regular objects (one interface to rule them all)
*   Added: main error messages with SLException for better SLFramework integration
*   Added: readonly property to write protect Collection content
*/
class Collection implements \IteratorAggregate, \ArrayAccess
{
    protected $items;

    protected $readonly = false;

    protected $type_array = true;

    public function __construct($items, $readonly = false)
    {
        if (is_array($items)) {
            $this->type_array = true;
            $this->items = $items;
        } elseif (is_object($items)) {
            $this->type_array = false;
            $temp = [];
            foreach ($items as $key => $value) {
                $temp[$key] = $value;
            }
            $this->items = $temp;
        } else {
            throw new SLException("Error Collection accepts array or object", 1);
        }

        $this->readonly = $readonly;
    }

    /**
    *   get
    *
    **/
    public function get($key)
    {
        $index = explode('.', $key);
        return $this->getValue($index, $this->items);
    }

    /**
    *   __get
    *       trick the class to accept direct references (as in $obj->key instead of $obj->get(key))
    **/
    public function __get($key)
    {
        return $this->get($key);
    }


    protected function getValue(array $indexes, $value)
    {
        $key = array_shift($indexes);
        if (empty($indexes)) {
            if (!array_key_exists($key, $value)) {
                return null;
            }
            if (is_array($value)) {
                if (is_array($value[$key]) || is_object($value[$key])) {
                    return new Collection($value[$key], $this->readonly);
                } else {
                    return $value[$key];
                }
            }
            if (is_object($value)) {
                if (is_array($value->$key) || is_object($value->$key)) {
                    return new Collection($value[$key], $this->readonly);
                } else {
                    return $value->$key;
                }
            }
        } else {
            return $this->getValue($indexes, $value[$key]);
        }
    }

    /**
    *   set
    *
    **/
    public function set($key, $value)
    {
        if ($this->readonly) {
            throw new SLException("Error seting value to a read only key", 403);
        }
        $this->items[$key] = $value;
    }

    /**
    *   __set
    *
    **/
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }


    /**
    *   has
    *
    **/
    public function has($key)
    {
        if ($this->type_array) {
            return array_key_exists($key, $this->items);
        } else {
            return isset($this->items->$key);
        }
    }

    public function lists($key, $value)
    {
        $results = [];
        foreach ($this->items as $item) {
            $results[$item[$key]] = $item[$value];
        }
        return new Collection($results, $this->readonly);
    }

    public function extract($key)
    {
        $results = [];
        foreach ($this->items as $item) {
            $results[] = $item[$key];
        }
        return new Collection($results, $this->readonly);
    }

    public function join($glue)
    {
        return implode($glue, $this->items);
    }

    public function max($key = false)
    {
        if ($key) {
            return $this->extract($key)->max();
        } else {
            return max($this->items);
        }
    }

    /**
    *   remove
    *
    **/
    public function remove($key)
    {
        $this->items->$key = false;
    }


    /*
    *   ArrayAccess Methods
    */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if($this->has($offset)){
            if ($this->type_array) {
                unset($this->items[$offset]);
            } else {
                unset($this->items->$offset);
            }
        }
    }
    /*
    *   End ArrayAccess Methods
    */

    /**
    *   IteratorAggregate Methos
    */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
    /**
    *   End IteratorAggregate Methos
    */

}