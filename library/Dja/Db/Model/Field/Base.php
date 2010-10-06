<?php
abstract class Dja_Db_Model_Field_Base
{
    protected static $_initedBackRels = array();
    
    protected $_options = array(
        'name'         => null,
        'ownerClass'   => null,
        'type'         => null,
        'max_length'   => null,
        'is_null'      => false,
        'is_blank'     => false,
        'choices'      => null,
        'db_column'    => null,
        'db_index'     => false,
        'default'      => null,
        'editable'     => true,
        'help_text'    => '',
        'primary_key'  => false,
        'is_unique'    => false,
        'verbose_name' => null,
        'relationClass'=> null,
        'using'        => null,
        'auto_increment'=> false
    );
    
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
        if (empty($this->db_column)) {
            $this->setOption('db_column', $this->name);
        }
    }
    
    //abstract public function getSqlType();
    
    /**
     * beforeSave signal, should cancel action if return false;
     * @param Dja_Db_Model $model
     * @param string $fieldName
     * @return bool
     */
    public function beforeSave(Dja_Db_Model $model, $fieldName)
    {
        return true;
    }
    
    /**
     * afterSave signal
     * @param Dja_Db_Model $model
     * @param string $fieldName
     * @return void
     */
    public function afterSave(Dja_Db_Model $model, $fieldName)
    {
        
    }
    
    /**
     * beforeDelete signal, should cancel action if return false;
     * @param Dja_Db_Model $model
     * @param string $fieldName
     * @return bool
     */
    public function beforeDelete(Dja_Db_Model $model, $fieldName)
    {
        return true;
    }
    
    /**
     * if this field is relation object
     *
     * @return bool
     */
    public function isRelation()
    {
        return false;
    }
    
    /**
     * validate input data for this field type
     * @param $value
     * @return bool
     */
    public function isValid($value)
    {
        return true;
    }
    
    /**
     * return value with needed type
     * @param mixed $value
     * @return mixed
     */
    public function cleanValue($value)
    {
        return $value;
    }
    
    /**
     * converts data stored on field to php object/structure
     * @param $value
     * @return any type
     */
    public function toPhp($value)
    {
        return $value;
    }
    public function getPrepValue($value)
    {
        return $value;
    }
    public function getDbPrepValue($value)
    {
        return $value;
    }
    public function getDbPrepSave($value)
    {
        return $value;
    }
    
    /**
     * return field-specific default value
     * @return unknown_type
     */
    public function getDefault()
    {
        return $this->default;
    }
    
    /**
     * easy access to options
     * @param $key
     * @return anytype
     */
    public function __get($key)
    {
        return $this->getOption($key);
    }
    
    public function issetOption($key)
    {
        return array_key_exists($key, $this->_options);
    }
    
    public function getOption($key)
    {
        if ($this->issetOption($key)) {
            return $this->_options[$key];
        }
        throw new Exception('No such option!');
    }
    
    /**
     * Enter description here...
     *
     * @param string $key
     * @param $value
     * @return Dja_Db_Model_Field_Abstract
     */
    public function setOption($key, $value)
    {
        if ($this->issetOption($key)) {
            $this->_options[$key] = $value;
        } else {
            throw new Exception('No such option!');
        }
        return $this;
    }
    
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
    }
}