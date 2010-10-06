<?php

class Dja_Config implements Countable, Iterator, ArrayAccess
{
    /**
     * Whether in-memory modifications to configuration data are allowed
     *
     * @var boolean
     */
    protected $_allowModifications;

    /**
     * Iteration index
     *
     * @var integer
     */
    protected $_index;

    /**
     * Number of elements in configuration data
     *
     * @var integer
     */
    protected $_count;

    /**
     * Contains array of configuration data
     *
     * @var array
     */
    protected $_data = array();

    public function __construct(array $array = array(), $allowModifications = false)
    {
        $this->_allowModifications = (boolean) $allowModifications;
        $this->_index = 0;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->_data[$key] = new self($value, $this->_allowModifications);
            } else {
                $this->_data[$key] = $value;
            }
        }
        $this->_count = count($this->_data);
    }
    
    public function get($name, $default = null)
    {
        $result = $default;
        if (array_key_exists($name, $this->_data)) {
            $result = $this->_data[$name];
        }
        return $result;
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function __set($name, $value)
    {
        if ($this->_allowModifications) {
            if (is_array($value)) {
                $this->_data[$name] = new self($value, true);
            } else {
                $this->_data[$name] = $value;
            }
            $this->_count = count($this->_data);
        } else {
            throw new Dja_Exception('Config is read only');
        }
    }
    
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }
    
    public function __unset($name)
    {
        if ($this->_allowModifications) {
            unset($this->_data[$name]);
            $this->_count = count($this->_data);
            $this->_skipNextIteration = true;
        } else {
            throw new Dja_Exception('Config is read only');
        }

    }
    
    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $data = $this->_data;
        foreach ($data as $key => $value) {
            if ($value instanceof Dja_Config) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }
    
    public function setReadOnly()
    {
        $this->_allowModifications = false;
        foreach ($this->_data as $key => $value) {
            if ($value instanceof Dja_Config) {
                $value->setReadOnly();
            }
        }
    }
    
    public function readOnly() { return !$this->_allowModifications; }
    
    /**
     * Defined by Countable interface
     *
     * @return int
     */
    public function count() { return $this->_count; }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        $this->_skipNextIteration = false;
        return current($this->_data);
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function key() { return key($this->_data); }

    /**
     * Defined by Iterator interface
     *
     */
    public function next()
    {
        if ($this->_skipNextIteration) {
            $this->_skipNextIteration = false;
            return;
        }
        next($this->_data);
        $this->_index++;
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function rewind()
    {
        $this->_skipNextIteration = false;
        reset($this->_data);
        $this->_index = 0;
    }

    /**
     * Defined by Iterator interface
     *
     * @return boolean
     */
    public function valid() { return $this->_index < $this->_count; }
    
    /**
     * @param offset
     */
    public function offsetExists ($offset) { return array_key_exists($offset, $this->_data); }

    /**
     * @param offset
     */
    public function offsetGet ($offset) { return $this->_data[$offset]; }

    /**
     * @param offset
     * @param value
     */
    public function offsetSet ($offset, $value) {}

    /**
     * @param offset
     */
    public function offsetUnset ($offset) {}
}