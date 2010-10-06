<?php

class Dja_Db_Model_Field_ForeignKey extends Dja_Db_Model_Field_Base implements Dja_Db_Model_Field_SingleRelationInterface
{
    public function __construct($options)
    {
        $this->_options['related_name'] = null;
        $this->_options['to_field']     = null;
        
        $this->setOption('db_index', true);
        
        parent::__construct($options);
        
        $this->_options['type'] = 'PositiveIntegerField';
        
        if (empty($this->db_column)) {
            $this->setOption('db_column', $this->name.'_id');
        }
        
        $this->_setupBackwardsRelation();
    }
    
    protected function _setupBackwardsRelation()
    {
        if (!$this->related_name) {
            throw new Dja_Db_Exception('"related_name" is required option for Dja_Db_Model_Field_ForeignKey');
        }
        $ownerClass = $this->getOption('ownerClass');
        $remoteClass = $this->getOption('relationClass');
        $related_name = $this->related_name;
        $options = array(
            'Dja_Db_Model_Field_ManyToOne',
            'relationClass' => $ownerClass,
            'selfColumn' => $this->to_field,
            'refColumn'  => $this->db_column
        );
        $remoteClass::metadata()->addField($related_name, $options);
    }
    
    public function getSqlType()
    {
        return 'int';
    }
    
    public function isRelation()
    {
        return true;
    }
    
    public function getRelObject($value)
    {
        if (!empty($value)) {
            $relationClass = $this->relationClass;
            $inst = $relationClass::objects()->get($value, $this->to_field);
            return $inst;
        } else {
            return $this->default;
        }
    }
    
    /**
     * converts php object/structure for put in db
     * @param Dja_Db_Model $value
     * @return string|int
     */
    public function getDbPrepValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        if ($this->to_field) {
            return $value->{$this->to_field};
        } else {
            return $value->getPrimaryKeyValue();
        }
    }
    
    public function isValid($value)
    {
        if ($value instanceof $this->relationClass) {
            return true;
        }
        return false;
    }
}