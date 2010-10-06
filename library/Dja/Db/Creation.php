<?php

abstract class Dja_Db_Creation
{
    protected $_dataTypes = array();
    
    /**
     *
     * @param string $backend
     * @return Dja_Db_Creation
     */
    public static function factory($backend)
    {
        $c = 'Dja_Db_Backend_'.ucfirst($backend).'_Creation';
        return new $c;
    }
    
    public function createSqlForModel($modelClassName)
    {
        if (!class_exists($modelClassName)) {
            throw new Dja_Exception("Model '{$modelClassName}' does not exist!");
        }
        // IF NOT EXISTS // ?
        $sql = 'CREATE TABLE `'.$modelClassName::metadata()->getDbTableName().'` ('."\n";
        // fields that realy exist in db
        $local = $modelClassName::metadata()->getLocalFields();
        $fSql_A = array();
        foreach ($local as $f) {
            $fSql = "`{$f->db_column}` ";
            $type_s = $this->_dataTypes[$f->type];
            $fSql.= preg_replace_callback('#\%\((\w+)\)s#ui', function($matches)use($f){ return $f->{$matches[1]}; }, $type_s);
            if (!$f->is_null) {
                $fSql.= ' NOT NULL';
            }
            if ($f->primary_key) {
                $fSql.= ' PRIMARY KEY';
            }
            if ($f->is_unique) {
                $fSql.= ' UNIQUE';
            }
            //var_dump($fSql);
            $fSql_A[] = $fSql;
        }
        $sql.= implode(",\n", $fSql_A);
        $sql.= "\n);";
        var_dump($sql);
        
        //$m2mAlreadyDone = array();
        $m2m = $modelClassName::metadata()->getMany2ManyFields();
        foreach ($m2m as $f) {
            if (!$f instanceof Dja_Db_Model_Field_ManyToMany) {
                continue;
            }
            //$m2mAlreadyDone[$f->db_table] = true;
            $m2m_sql = 'CREATE TABLE IF NOT EXISTS `'.$f->db_table.'` ('."\n";
            $m2m_sql.= "`{$f->selfColumn}` integer UNSIGNED,\n";
            $m2m_sql.= "`{$f->refColumn}` integer UNSIGNED,\n";
            $m2m_sql.= "UNIQUE KEY `m2m_unique` (`{$f->selfColumn}`,`{$f->refColumn}`)\n";
            $m2m_sql.= ");";
            var_dump($m2m_sql);
        }
    }
}