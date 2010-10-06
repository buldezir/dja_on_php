<?php

abstract class Dja_Db_Model
{
    /**
     * define db table name or it will be choose automaticaly
     * @var sting
     */
    protected static $_dbTableName = null;
    
    /**
     * MUST be provided in each model !
     * @var array
     */
    protected static $_fieldConfig;

    /**
     * true for empty model, set false when cloning
     * @var bool
     */
    protected $_readOnly = false;

    /**
     * loaded from db (update on save) or created (insert on save)
     * @var bool
     */
    protected $_isNew = true;

    /**
     * The data for each column in the row (column_name => value).
     * The keys must match the physical names of columns in the
     * table for which this row is defined.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * This is array of lazy-loaded relation objects (models)
     *
     * @var array
     */
    protected $_relationData = array();

    /**
     * This is set to a copy of $_data when the data is fetched from
     * a database, specified as a new tuple in the constructor, or
     * when dirty data is posted to the database with save().
     *
     * @var array
     */
    protected $_cleanData = array();

    /**
     * Tracks columns where data has been updated. Allows more specific insert and
     * update operations.
     *
     * @var array
     */
    protected $_modifiedFields = array();

    /**
     * field options
     * @var Dja_Db_Model_Metadata
     */
    protected static $_metaData = array();

    /**
     *
     * @var array
     */
    protected static $_helpers = array();
    
    /**
     * Creates or returns the only instance of the a class.
     *
     * @return Dja_Db_Model_Metadata
     */
    final public static function metadata()
    {
        $calledClassName = get_called_class();
        if( !isset(self::$_metaData[$calledClassName]) ) {
            self::$_metaData[$calledClassName] = new Dja_Db_Model_Metadata($calledClassName);
        }
        return self::$_metaData[$calledClassName];
    }

    /**
     * usage:  MyModel::objects()->filter('field', 'value')->order('-field') ...
     * @return Dja_Db_Model_Query
     */
    public static function objects()
    {
        //$className = get_called_class();
        return new Dja_Db_Model_Query(static::metadata());
    }
    
    /**
     *
     * @param string $name
     * @throws Dja_Db_Exception
     * @return Dja_Db_Model_Helper_Abstract
     */
    protected static function _getHelper($name)
    {
        if (isset(static::$_helpers[$name])) {
            if (!static::$_helpers[$name] instanceof Dja_Db_Model_Helper_Abstract) {
                static::$_helpers[$name] = new static::$_helpers[$name];
            }
            return static::$_helpers[$name];
        }
        throw new Dja_Db_Exception('No helper registered with name "'.$name.'"');
    }

    /**
     * shortcut
     * @param string $field
     * @return Dja_Db_Model_Metadata | Dja_Db_Model_Field_Base
     */
    final protected function _f($field = null)
    {
        $metadata = self::metadata();
        if ($field !== null) {
            return $metadata->getField($field);
        }
        return $metadata;
    }

    /**
     *
     */
    final public function __construct(array $data = array(), $isNew = true)
    {
        $this->_isNew = $isNew;
        if ($isNew) {
            $this->_data = $this->_f()->getDefaultValues();
        }
        if (!empty($data)) {
            $this->setFromArray($data, !$isNew);
        }
        $this->init();
    }

    protected function init()
    {

    }
    
    public function __call($method, $args)
    {
        array_unshift($args, $this);
        $matches = array();
        if (preg_match('#^get(\w+)By(\w+)$#', $method, $matches)) {
            $class = $matches[1];
            $field = strtolower($matches[2]);
            $relType = $this->_f()->getBackRel($class, $field);
            if ($relType !== false) {
                $modelClassName = 'Model_'.$class; // !!! change
                switch ($relType) {
                    case Dja_Db_Model_Field_Base::REL_ONE2ONE:
                        return $modelClassName::objects()->get();
                        break;
                    case Dja_Db_Model_Field_Base::REL_FOREIGNKEY:
                        return $modelClassName::objects()->get();
                        break;
                    case Dja_Db_Model_Field_Base::REL_MANY2MANY:
                        return $modelClassName::objects()->get();
                        break;
                }
            } else {
                throw new Dja_Db_Exception('Relation not found !');
            }
        } else {
            $h = static::_getHelper($method);
            return call_user_func_array(array($h, 'call'), $args);
        }
    }

    public function __callStatic($method, $args)
    {
        array_unshift($args, get_called_class());
        $h = static::_getHelper($method);
        return call_user_func_array(array($h, 'callStatic'), $args);
    }

    /**
     * return php-representaion of field value
     *
     * int|string|bool|array for simple fields
     * Dja_Db_Model for OneToOne and ForeignKey fields
     * Dja_Db_Model_Query for ManyToMany fields
     *
     * @param string $key
     * @return mixed
     */
    protected function _get($key)
    {
        $metadata = self::metadata();
        $fieldObj = $metadata->getField($key);
        if ($metadata->isLocal($key)) {
            return $this->_data[$key];
        } elseif ($metadata->isVirtual($key)) {
            if (!$fieldObj->isRelation()) {
                return $this->_data[$fieldObj->name];
            } else {
                if (!array_key_exists($key, $this->_relationData)) {
                    if (!empty($this->_data[$fieldObj->db_column])) {
                        $this->_relationData[$key] = $fieldObj->getRelObject($this->_data[$fieldObj->db_column]);
                    } else {
                        return $this->_data[$fieldObj->db_column];
                    }
                }
                return $this->_relationData[$key];
            }
        } elseif ($metadata->isM2M($key)) {
            if (!isset($this->_relationData[$key])) {
                $this->_relationData[$key] = $fieldObj->getRelQuery($this);
            }
            return $this->_relationData[$key];
        }
    }

    /**
     * set field value after validation
     * @param $key
     * @param $value
     * @param bool $force means it is actual data from db
     * @return void
     */
    protected function _set($key, $value, $force = false)
    {
        if ($this->isReadOnly()) {
            throw new Dja_Db_Exception("object is read-only");
        }
        $metadata = self::metadata();
        $fieldObj = $metadata->getField($key);
        if ($force === false && $fieldObj->editable === false) {
            throw new Dja_Db_Exception('Field "'.$key.'" is read-only');
        }
        if ($metadata->isLocal($key)) {
            if ($force) {
                $value = $fieldObj->toPhp($value);
            }
            $this->_data[$key] = $value;
            $this->_modifiedFields[$key] = true;
        } elseif ($metadata->isVirtual($key)) {
            if (!$fieldObj->isRelation()) {
                if ($force) {
                    $value = $fieldObj->toPhp($value);
                }
                $this->_data[$fieldObj->name] = $value;
                $this->_modifiedFields[$fieldObj->name] = true;
            } else {
                if ($fieldObj->isValid($value)) {
                    $this->_relationData[$key] = $value;
                    $this->_data[$fieldObj->db_column] = $value->getPrimaryKeyValue();
                    $this->_modifiedFields[$fieldObj->db_column] = true;
                } else {
                    throw new Dja_Db_Exception("You provide invalid value for '{$key}'!");
                }
            }
        } elseif ($metadata->isM2M($key)) {
            if ($fieldObj->isValid($value)) {
                $this->_relationData[$key] = $value;
            } else {
                throw new Dja_Db_Exception("You provide invalid value for '{$key}'!");
            }
        }
    }

    public function __get($key)
    {
        return $this->_get($key);
    }

    public function __set($key, $value)
    {
        $this->_set($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    public function getPrimaryKeyValue()
    {
        $fieldObj = $this->_f()->getPrimaryKey();
        if ($fieldObj !== null) {
            return $this->_get($fieldObj->name);
        }
        return null;
    }

    /**
     *
     * @param array $data
     * @param bool $force means it is actual data from db
     * @return void
     */
    public function setFromArray(array $data, $force = false)
    {
        $metadata = self::metadata();
        foreach ($data as $key => $value) {
            if (isset($metadata->$key)) {
                $this->_set($key, $value, $force);
            }
        }
        if ($force) {
            $this->_cleanData = $this->_data;
            $this->_modifiedFields = array();
        }
        return $this;
    }

    /**
     * convert all data (objects) to simple representation
     * @return array
     */
    public function toArray()
    {
        /*$result = array();
        foreach ($this->_data as $key => $value) {
            $fieldObj = $this->_f($key);
            if ($fieldObj->isRelation()) {
                $result[$fieldObj->db_column] = $value->toArray();
            } else {
                $result[$fieldObj->db_column] = $value;
            }
        }
        return $result;*/
        return $this->_data;
    }

    public function dump()
    {
        var_dump($this->_data);
        var_dump($this->_relationData);
    }

    /**
     * the same as toArray(), but returns data as object
     * @return stdClass
     */
    public function toObject()
    {
        return (object) $this->toArray();
    }

    public function save()
    {
        if ($this->isDirty() === false) {
            return false;
        }
        if ($this->fireBeforeSave()) {
            $pkfield = $this->_f()->getPrimaryKey();
            $pk = $this->_get($pkfield->db_column);
            $db = $this->_f()->getAdapter();
            if ($this->_isNew === false) { // update
                $db->update($this->_f()->getDbTableName(), $this->_data, array("{$pkfield->db_column} = ?" => $pk));
            } else { // insert
                $db->insert($this->_f()->getDbTableName(), $this->_data);
                $this->_set($pkfield->db_column, $db->lastInsertId());
            }

            // update actual data:
            $this->_cleanData = $this->_data;
            $this->_modifiedFields = array();

            $this->_isNew = false;
            $this->fireAfterSave();
            return true;
        } else {
            return false;
        }
    }

    /**
     * delete current object from db (not destroy this instance)
     * @return bool
     */
    public function delete()
    {
        if ($this->_isNew === true) {
            return false;
        }
        if ($this->fireBeforeDelete()) {
            $pkfield = $this->_f()->getPrimaryKey();
            $pk = $this->_get($pkfield->db_column);
            $db = $this->_f()->getAdapter();
            $tableName = $this->_f()->getDbTableName();
            $db->delete($tableName, array("{$pkfield->db_column} = ?" => $pk));
            return true;
        } else {
            return false;
        }
    }

    /**
     * beforeSave signal, should cancel action if return false;
     * @return bool
     */
    final public function fireBeforeSave()
    {
        return true;
        if ($this->_beforeSave()) {
            return $this->_f()->fireBeforeSave($this);
        }
        return false;
    }

    /**
     * end-user Model-defined signal
     * @return bool
     */
    protected function _beforeSave()
    {
        return true;
    }

    /**
     * afterSave signal
     * @return void
     */
    final public function fireAfterSave()
    {
        return true;
        $this->_afterSave();
        $this->_f()->fireAfterSave($this);
    }

    /**
     * end-user Model-defined signal
     * @return bool
     */
    protected function _afterSave()
    {
        return true;
    }

    /**
     * beforeDelete signal, should cancel action if return false;
     * @return bool
     */
    final public function fireBeforeDelete()
    {
        return true;
        if ($this->_beforeDelete()) {
            return $this->_f()->fireBeforeDelete($this);
        }
        return false;
    }

    /**
     * end-user Model-defined signal
     * @return bool
     */
    protected function _beforeDelete()
    {
        return true;
    }

    /**
     * revert all changes
     * @return void
     */
    public function rollback()
    {
        $this->_data = $this->_cleanData;
        return $this;
    }

    /**
     * if there any changes ?
     * @return bool
     */
    public function isDirty()
    {
        //return ($this->_data != $this->_cleanData);
        return count($this->_modifiedFields) > 0;
    }

    /**
     *
     * @return void
     */
    public function __clone()
    {
        //$this->_readOnly = false;
    }

    /**
     * if false - method __set is not accessable
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->_readOnly;
    }

    /**
     * is new ?
     * @return bool
     */
    public function isNew()
    {
        return $this->_isNew;
    }
}