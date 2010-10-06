<?php
/**
 * Тут хранятся данные таблицы и полей модели
 *
 * @author Арутюнов Александр
 *
 */
class Dja_Db_Model_Metadata
{
    /**
     * array or inited field objects
     * @var array
     */
    protected $_localFields = array();
    protected $_many2manyFields = array();
    protected $_virtualFields = array();
    
    protected $_allFields = array(); // just cache
    
    protected $_modelClassName;
    
    /**
     * name of table in db, not required
     * @var string
     */
    protected $_dbTableName = null;
    
    /**
     *
     * @param string $modelClassName
     * @param array $modelFieldConfig
     */
    public function __construct($modelClassName)
    {
        $refl = new ReflectionClass($modelClassName);
        $staticProps = $refl->getStaticProperties();
        //$modelFieldConfig = $refl->getStaticPropertyValue('_fieldConfig', null);
        //$dbTableName      = $refl->getStaticPropertyValue('_dbTableName', null);
        $modelFieldConfig = $staticProps['_fieldConfig'];
        $dbTableName      = $staticProps['_dbTableName'];
        $autoAddPk        = isset($staticProps['_autoAddPk'])?(bool)$staticProps['_autoAddPk']:true;
        if ($dbTableName !== null) {
            $this->_dbTableName = $dbTableName;
        }
        $this->_modelClassName = $modelClassName;
        //Zend_Debug::dump($modelFieldConfig);
        $this->_setupFields($modelFieldConfig, $autoAddPk);
    }
    
    public function addField($name, $options, $throw = false)
    {
        if ($throw) {
            $this->_addField($name, $options);
        } else {
            try {
                $this->_addField($name, $options);
            } catch (Dja_Db_Exception $e) { echo '<pre>'.$e.'</pre>'; /* @todo: error handler */ }
        }
        return $this;
    }
    
    protected function _addField($name, $options)
    {
        $fieldClass = array_shift($options);
        $options['name'] = $name;
        $options['ownerClass'] = $this->_modelClassName;
        $fieldObj = new $fieldClass($options, $this);
        if ($fieldObj instanceof Dja_Db_Model_Field_ManyRelationInterface) {
            if (array_key_exists($name, $this->_allFields)) {
                throw new Dja_Db_Exception('Cant be fields with same name or db_column!');
            } else {
                $this->_many2manyFields[$name] = $fieldObj;
                $this->_allFields[$name] = $fieldObj;
            }
        } else {
            if ($fieldObj->isRelation()) {
                if (array_key_exists($fieldObj->db_column, $this->_allFields) || array_key_exists($name, $this->_allFields)) {
                    throw new Dja_Db_Exception('Cant be fields with same name or db_column!');
                } else {
                    $this->_localFields[$fieldObj->db_column] = $fieldObj;
                    $this->_virtualFields[$name] = $fieldObj;
                    $this->_allFields[$fieldObj->db_column] = $fieldObj;
                    $this->_allFields[$name] = $fieldObj;
                }
            } else {
                if (array_key_exists($name, $this->_allFields)) {
                    throw new Dja_Db_Exception('Cant be fields with same name or db_column!');
                } else {
                    $this->_localFields[$name] = $fieldObj;
                    $this->_allFields[$name] = $fieldObj;
                }
            }
            if ($fieldObj->primary_key) {
                if (array_key_exists('pk', $this->_virtualFields)) {
                    throw new Dja_Db_Exception('More than 1 primary key is not allowed!');
                } else {
                    if (array_key_exists('pk', $this->_allFields)) {
                        throw new Dja_Db_Exception('Cant be fields with same name or db_column!');
                    } else {
                        $this->_virtualFields['pk'] = $fieldObj;
                        $this->_allFields['pk'] = $fieldObj;
                    }
                }
            }
        }
    }
    
    protected function _setupFields(array $modelFieldConfig, $autoAddPk = true)
    {
        foreach ($modelFieldConfig as $name => $options) {
            $this->_addField($name, $options);
        }
        // if no pk has been defined, auto add pk field
        if ($autoAddPk === true && !array_key_exists('pk', $this->_allFields)) {
            /*$pk = new Dja_Db_Model_Field_Auto(array('db_column' => 'id'));
            $this->_localFields = array('id' => $pk) + $this->_localFields;
            $this->_virtualFields['pk'] = $pk;
            $this->_allFields['pk'] = $pk;*/
            $this->_addField('id', array('Dja_Db_Model_Field_Auto'));
        }
    }
    
    public function getRelationModels()
    {
        
    }
    
    /**
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $result = array();
        foreach ($this->_localFields as $name => $fieldObj) {
            $result[$fieldObj->db_column] = $fieldObj->default;
        }
        return $result;
    }
    
    /**
     *
     * @return array Dja_Db_Model_Field_Base[]
     */
    public function getRelationFields()
    {
        $result = array();
        foreach ($this->_localFields as $name => $fieldObj) {
            if ($fieldObj->isRelation()) {
                $result[$fieldObj->db_column] = $fieldObj;
            }
        }
        return $result;
    }
    
    public function __get($key)
    {
        return $this->getField($key);
    }
    
    public function __isset($key)
    {
        return array_key_exists($key, $this->_allFields);
    }
    
    /**
     *
     * @param string $key
     * @return Dja_Db_Model_Field_Base
     */
    public function getField($key)
    {
        if (array_key_exists($key, $this->_allFields)) {
            return $this->_allFields[$key];
        } else {
            throw new Dja_Db_Exception('No field with name or db_column = "'.$key.'"');
        }
    }
    
    /**
     *
     * @return array Dja_Db_Model_Field_Base[]
     */
    public function getFields()
    {
        return $this->_allFields;
    }
    public function getLocalFields()
    {
        return $this->_localFields;
    }
    public function getVirtualFields()
    {
        return $this->_virtualFields;
    }
    public function getMany2ManyFields()
    {
        return $this->_many2manyFields;
    }
    
    public function isLocal($key)
    {
        return array_key_exists($key, $this->_localFields);
    }
    
    public function isVirtual($key)
    {
        return array_key_exists($key, $this->_virtualFields);
    }
    
    public function isM2M($key)
    {
        return array_key_exists($key, $this->_many2manyFields);
    }
    
    public function getDbColNames()
    {
        $result = array();
        foreach ($this->_localFields as $fieldObj) {
            $result[] = $fieldObj->db_column;
        }
        return $result;
    }
    
    public function getDbTableName()
    {
        if ($this->_dbTableName === null) {
            $parts = explode('_', $this->_modelClassName);
            $lastPart = array_pop($parts);
            $name = strtolower($lastPart).'s';
            if (defined('DJA_DB_PREFIX')) {
                $name = DJA_DB_PREFIX.$name;
            }
            $this->_dbTableName = $name;
        }
        return $this->_dbTableName;
    }
    
    public function getModelClassName()
    {
        return $this->_modelClassName;
    }
    
    /**
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return Dja_Db::getInstance();
    }
    
    /**
     *
     * @return Dja_Db_Model_Field_Base|null
     */
    public function getPrimaryKey()
    {
        return isset($this->_virtualFields['pk'])?$this->_virtualFields['pk']:null;
    }
    
    /**
     * fire beforeSave signal for all fields, should cancel action if return false;
     * @param Dja_Db_Model $model
     * @return bool
     */
    public function fireBeforeSave($model)
    {
        $return = true;
        foreach ($this->_fields as $key => $field) {
            if ($field->beforeSave($model, $key) === false) {
                $return = false;
            }
        }
        return $return;
    }
    
    /**
     * fire afterSave signal for all fields
     * @param Dja_Db_Model $model
     * @return void
     */
    public function fireAfterSave($model)
    {
        foreach ($this->_fields as $key => $field) {
            $field->afterSave($model, $key);
        }
    }
    
    /**
     * fire beforeDelete signal for all fields, should cancel action if return false;
     * @param Dja_Db_Model $model
     * @return bool
     */
    public function fireBeforeDelete($model)
    {
        $return = true;
        foreach ($this->_fields as $key => $field) {
            if ($field->beforeDelete($model, $key) === false) {
                $return = false;
            }
        }
        return $return;
    }
}