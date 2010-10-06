<?php

class Dja_Db_Model_Field_DateTime extends Dja_Db_Model_Field_Base
{
    public function __construct(array $options = array())
    {
        $this->_options['autoInsert'] = true;
        $this->_options['autoUpdate'] = false;
        parent::__construct($options);
        
        $this->_options['type'] = 'DateTimeField';
    }
    
    public function getSqlType()
    {
        return 'datetime';
    }
    
    /**
     * beforeSave signal
     * @param Dja_Db_Model $model
     * @param string $fieldName
     * @return void
     */
    public function beforeSave(Dja_Db_Model $model, $fieldName)
    {
        if ($this->autoInsert === true) {
            if ($model->isNew()) {
                $model->$fieldName = $this->getDefault();
            }
        }
        if ($this->autoUpdate === true) {
            if (!$model->isNew()) {
                $model->$fieldName = $this->getDefault();
            }
        }
    }
    
    public function isValid($value)
    {
        if (preg_match('#^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$#', $value)) {
            return true;
        }
        return false;
    }
    
    public function getDefault()
    {
        return date('Y-m-d H:i:s');
    }
}