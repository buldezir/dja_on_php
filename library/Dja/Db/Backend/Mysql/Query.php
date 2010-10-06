<?php

class Dja_Db_Backend_Mysql_Query extends Dja_Db_Query
{
    protected $_operators = array(
        'exact' => '= %s',
        'iexact' => 'LIKE %s',
        'contains' => 'LIKE BINARY %s',
        'icontains' => 'LIKE %s',
        'regex' => 'REGEXP BINARY %s',
        'iregex' => 'REGEXP %s',
        'gt' => '> %s',
        'gte' => '>= %s',
        'lt' => '< %s',
        'lte' => '<= %s',
        'startswith' => 'LIKE BINARY %s',
        'endswith' => 'LIKE BINARY %s',
        'istartswith' => 'LIKE %s',
        'iendswith' => 'LIKE %s',
    );
    
    public function toSql()
    {
        
    }
    
    /**
     * array('is_active__exact' => 1, 'is_superuser__exact' => F('is_staff'))
     * array('pub_date__lte' => '2006-01-01')
     */
    public function explaneArguments(array $arguments)
    {
        foreach ($arguments as $lookup => $value) {
            // if exact lookuptype
            if (strpos($lookup, '__') === false) {
                $lookupType = 'exact';
                $lookupArr  = array($lookup);
            } else {
                $lookupArr  = explode('__', $lookup);
                $lookupType = array_pop($lookupArr);
            }
            
            if (count($lookupArr) > 1) { // if join lookup
                throw new Dja_Db_Exception('Join lookups not implemented !');
            } else {
                $colName = $lookupArr[0];
            }
        }
    }
    
    public function processLookup($tableAlias = null, $colName, $dbType, $lookupType, $params = null)
    {
        
    }
    
    public function qn($value)
    {
        return '`'.$value.'`';
    }
    
    public function qv($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return "'" . $this->_connection->real_escape_string($value) . "'";
    }
}