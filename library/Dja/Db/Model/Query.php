<?php
/**
 *
 * @author sasha
 * @todo : queryset append for many2many
 */
class Dja_Db_Model_Query implements Countable, Iterator
{
    /**
     * rowset data
     * @var array
     */
    protected $_data = array();
    
    protected $_index = 0;
    
    protected $_haveJoins = false;
    protected $_joinMap = array();
    
    /**
     *
     * @var Dja_Db_Model_Metadata
     */
    protected $_tableMetadata;
    
    protected $_escapedTableName;
    
    /**
     *
     * @var Zend_Db_Select
     */
    protected $_zdbs;
    
    protected $_executed = false;
    
    public function __construct(Dja_Db_Model_Metadata $tableMetadata)
    {
        $tblName = $tableMetadata->getDbTableName();
        $this->_tableMetadata = $tableMetadata;
        $this->_escapedTableName = $tableMetadata->getAdapter()->quoteIdentifier($tblName).'.';
        $this->_zdbs = $this->_tableMetadata->getAdapter()->select();
        $this->_zdbs->from($tblName, $this->_tableMetadata->getDbColNames());
    }
    
    public function __toString()
    {
        return $this->_zdbs->__toString();
    }
    
    protected function _fetch()
    {
        if ($this->_executed === false) {
            $this->_data = $this->_tableMetadata->getAdapter()->fetchAll($this->_zdbs);
        }
        $this->_executed = true;
    }
    
    protected function _getColMap($as, $cols)
    {
        $result = array();
        foreach ($cols as $col) {
            $result["{$as}_{$col}"] = $col;
        }
        return $result;
    }
    
    protected function _isEmptyJoin($arr)
    {
        foreach ($arr as &$val) {
            if ($val !== null) return false;
        }
        return true;
    }
    
    public function create()
    {
        $c = $this->_tableMetadata->getModelClassName();
        return new $c;
    }
    
    /**
     * array('is_active__exact' => 1, 'is_superuser__exact' => F('is_staff'))
     * array()
     */
    protected function _explaneArguments(array $arguments)
    {
        
    }
    
    public function in_bulk(array $idList)
    {
        $q = $this->filter(array('pk__in' => $idList));
        $result = array();
        foreach ($q as $obj) {
            $result[$obj->getPrimaryKeyValue()] = $obj;
        }
        return $result;
    }
    
    public function get($value, $field = null)
    {
        if ($field === null) {
            $col = $this->_tableMetadata->getPrimaryKey();
        } else {
            $col = $this->_tableMetadata->$field;
        }
        $this->_zdbs->where($this->_escapedTableName.$col->db_column.' = ?', $value);
        $this->_zdbs->limit(1);
        $row = $this->_tableMetadata->getAdapter()->fetchRow($this->_zdbs);
        if ($row) {
            $class = $this->_tableMetadata->getModelClassName();
            return new $class($row, false);
        } else {
            return null;
        }
    }
    
    /**
     * not recomended to use
     * @return void
     */
    public function delete()
    {
        $hasWhere = count($this->_zdbs->getPart(Zend_Db_Select::WHERE)) > 0;
        if ($hasWhere) {
            $q = $this->_zdbs->__toString();
            $q = preg_replace('#^.+FROM#', 'DELETE FROM', $q);
            $this->_tableMetadata->getAdapter()->query($q);
        } else {
            throw new Dja_Db_Exception('Deleting all data is not allowed, please use filter!');
        }
    }
    
    public function autoJoin(array $fields = null)
    {
        $rel = $this->_tableMetadata->getRelationFields();
        if (count($rel) > 0) {
            $this->_haveJoins = true;
            $t = $this->_tableMetadata->getDbTableName();
            foreach ($rel as $col => $relField) {
                $relClass = $relField->relationClass;
                $meta = $relClass::metadata();
                $tbl = $meta->getDbTableName();
                $pk  = $meta->getPrimaryKey()->db_column;
                $as = $col.'_data';
                $this->_joinMap[$col] = $this->_getColMap($col, $meta->getDbColNames());
                $this->_zdbs->joinLeft(array($as => $tbl), "{$as}.{$pk} = {$t}.{$col}", $this->_joinMap[$col]);
            }
        }
        return $this;
    }
    
    public function all()
    {
        return $this;
    }
    
    public function limit($count = null, $offset = null)
    {
        $this->_zdbs->limit($count, $offset);
        return $this;
    }
    
    /**
     * set Order part for query
     * @return Dja_Db_Model_Query
     */
    public function order()
    {
        $args = func_get_args();
        foreach ($args as $order) {
            if ($order{0} === '-') {
                $order = substr($order, 1).' '.Zend_Db_Select::SQL_DESC;
            }
            $this->_zdbs->order($order);
        }
        return $this;
    }
    
    /**
     * set Where part for query
     * @param $fieldOrStmt
     * @param $value
     * @return Dja_Db_Model_Query
     */
    public function filter($fieldOrStmt, $value = null)
    {
        if (is_string($fieldOrStmt) && $value !== null) {
            $this->_zdbs->where($this->_escapedTableName.$fieldOrStmt.' = ?', $value);
        } else {
            $this->_zdbs->where($fieldOrStmt);
        }
        return $this;
    }
    
    /**
     * Defined by Countable interface
     *
     * @return int
     */
    public function count()
    {
        $this->_fetch();
        return count($this->_data);
    }
    
    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        $this->_fetch();
        $row = current($this->_data);
        if (!$row instanceof Dja_Db_Model) {
            $c = $this->_tableMetadata->getModelClassName();
            if ($this->_haveJoins === true) {
                foreach ($this->_joinMap as $selfcol => $colmap) {
                    $relClass = $this->_tableMetadata->getField($selfcol)->relationClass;
                    $relObjData = array();
                    foreach ($colmap as $as => $colName) {
                        $relObjData[$colName] = $row[$as];
                        unset($row[$as]);
                    }
                    if (!$this->_isEmptyJoin($relObjData)) {
                        $row[$selfcol] = new $relClass($relObjData, false);
                    }
                }
            }
            //echo dump($row);
            $row = $this->_data[key($this->_data)] = new $c($row, false);
        }
        return $row;
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function next()
    {
        next($this->_data);
        $this->_index++;
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function rewind()
    {
        reset($this->_data);
        $this->_index = 0;
    }

    /**
     * Defined by Iterator interface
     *
     * @return boolean
     */
    public function valid()
    {
        $this->_fetch();
        return $this->_index < $this->count();
    }
}