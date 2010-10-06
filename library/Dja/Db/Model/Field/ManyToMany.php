<?php

class Dja_Db_Model_Field_ManyToMany extends Dja_Db_Model_Field_Base implements Dja_Db_Model_Field_ManyRelationInterface
{
    public function __construct($options, $metadataObj)
    {
        $this->_options['related_name'] = null;
        $this->_options['db_table']     = null;
        $this->_options['selfColumn']   = null;
        $this->_options['refColumn']    = null;
        
        parent::__construct($options);
        $this->setOption('db_column', false);
        
        if (!$this->related_name) {
            throw new Dja_Db_Exception('"related_name" is required option for Dja_Db_Model_Field_ManyToMany');
        }
        /*if (!$this->through || !isset($this->using['table']) || !isset($this->using['refColumn']) || !isset($this->using['selfColumn'])) {
            throw new Dja_Db_Exception('"using" is required option for Dja_Db_Model_Field_ManyToMany');
        }*/
        //var_dump($this->_options); exit;
        /*if (!$this->db_table) {
            throw new Dja_Db_Exception('"db_table" is required option for Dja_Db_Model_Field_ManyToMany');
        }*/
        $ownerClass = $this->getOption('ownerClass');
        $remoteClass = $this->getOption('relationClass');
        $related_name = $this->related_name;
        if (!$this->db_table) {
            $this->setOption('db_table', $related_name.'_'.$this->name);
        }
        if (!$this->selfColumn) {
            $this->setOption('selfColumn', $metadataObj->getDbTableName().'_id');
        }
        // to prevent recursion:
        if (!isset(self::$_initedBackRels[$ownerClass.'::'.$remoteClass.'::'.$this->db_table])) {
            $remoteTable = $remoteClass::metadata()->getDbTableName();
            if (!$this->refColumn) {
                $this->setOption('refColumn', $remoteTable.'_id');
            }
            $options = array(
                'Dja_Db_Model_Field_ManyToMany',
                'relationClass' => $ownerClass,
                'related_name'  => $this->name,
                'db_table'      => $this->db_table,
                'selfColumn'    => $this->refColumn,
                'refColumn'     => $this->selfColumn
            );
            
            self::$_initedBackRels[$ownerClass.'::'.$remoteClass.'::'.$this->db_table] = true;
            self::$_initedBackRels[$remoteClass.'::'.$ownerClass.'::'.$this->db_table] = true;
            
            $remoteClass::metadata()->addField($related_name, $options);
        }
    }
    
    public function getSqlType()
    {
        return false;
    }
    
    public function isRelation()
    {
        return true;
    }
    
    public function getRelQuery(Dja_Db_Model $model)
    {
        $pk = $model->getPrimaryKeyValue();
        $relationClass = $this->relationClass;
        $relTable = $relationClass::metadata()->getDbTableName();
        $throughTable = $this->db_table;
        $throughSelf = $this->selfColumn;
        $throughRef = $this->refColumn;
        $throughQuery = new Dja_Db_Expr("`{$relTable}`.`id` IN (SELECT `{$throughTable}`.`{$throughRef}` FROM `{$throughTable}` WHERE `{$throughTable}`.`{$throughSelf}` = {$pk})");
        return $relationClass::objects()->filter($throughQuery);
    }
    
    public function isValid($value)
    {
        if ($value instanceof Dja_Db_Model_Query) {
            return true;
        }
        return false;
    }
}