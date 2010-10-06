<?php

class Dja_Db_Backend_Mysql_Creation extends Dja_Db_Creation
{
    protected $_dataTypes = array(
        'AutoField'=>         'integer AUTO_INCREMENT',
        'BooleanField'=>      'bool',
        'CharField'=>         'varchar(%(max_length)s)',
        'CommaSeparatedIntegerField'=> 'varchar(%(max_length)s)',
        'DateField'=>         'date',
        'DateTimeField'=>     'datetime',
        'DecimalField'=>      'numeric(%(max_digits)s, %(decimal_places)s)',
        'FileField'=>         'varchar(%(max_length)s)',
        'FilePathField'=>     'varchar(%(max_length)s)',
        'FloatField'=>        'double precision',
        'IntegerField'=>      'integer',
        'BigIntegerField'=>   'bigint',
        'IPAddressField'=>    'char(15)',
        'NullBooleanField'=>  'bool',
        'OneToOneField'=>     'integer',
        'PositiveIntegerField'=> 'integer UNSIGNED',
        'PositiveSmallIntegerField'=> 'smallint UNSIGNED',
        'SlugField'=>         'varchar(%(max_length)s)',
        'SmallIntegerField'=> 'smallint',
        'TextField'=>         'longtext',
        'TimeField'=>         'time',
        'TimeStampField'=>    'timestamp',
    );
    
    
}