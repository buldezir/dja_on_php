<?php

abstract class Dja_Db_Query
{
    protected $_connection;
    
    /**
     *
     * @param string $backend
     * @return Dja_Db_Creation
     */
    public static function factory($backend)
    {
        $c = 'Dja_Db_Backend_'.ucfirst($backend).'_Query';
        return new $c;
    }
    
    public function __construct($connection)
    {
        $this->_connection = $connection;
    }
    
    abstract public function processLookup($tableAlias, $colName, $dbType, $lookupType, $params = null);
    
    /**
     * quote table, field name
     * @param string $s
     */
    abstract public function qn($s);
    
    /**
     * quote value
     * @param mixed $s
     */
    abstract public function qv($s);
}