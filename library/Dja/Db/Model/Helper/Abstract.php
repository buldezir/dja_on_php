<?php

abstract class Dja_Db_Model_Helper_Abstract
{
    abstract public function call($instance);
    abstract public function callStatic($modelName);
}