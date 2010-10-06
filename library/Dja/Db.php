<?php

class Dja_Db extends Dja_Singleton
{
    const PK = '__PK__';
    
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db = null;
    
    protected function _initializeObject()
    {
        
    }
    
    public function setAdapter(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }
    
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->_db, $name), $args);
    }
    
    public static function __callstatic($name, $args)
    {
        $i = static::getInstance();
        return call_user_func_array(array($i, $name), $args);
        //return $i->__call($name, $args);
    }
}