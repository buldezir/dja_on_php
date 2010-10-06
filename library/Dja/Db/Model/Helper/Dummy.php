<?php

class Dja_Db_Model_Helper_Dummy extends Dja_Db_Model_Helper_Abstract
{
    public function call($instance, $someArg = 1)
    {
        echo '<br>called Dja_Db_Model_Helper_Dummy width instance of '.get_class($instance).'<br>';
    }
    
    public function callStatic($modelName)
    {
        echo '<br>called static Dja_Db_Model_Helper_Dummy width model '.$modelName.'<br>';
    }
}