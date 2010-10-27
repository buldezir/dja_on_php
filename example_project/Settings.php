<?php
return array(
    'DEBUG' => true,
    //'TEMPLATE_DIR' => APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates',
    //'TEMPLATE_CACHE_DIR' => APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . '_cache',
    /*'MIDDLEWARE' => array(
        Dja_Function::CONFIG_OVERWRITE,
        'Dja_Contrib_Session_Middleware',
        'Dja_Middleware_Common',
        'Dja_Middleware_Test'
    ),*/
    'INSTALLED_APPS' => array(
        'App'
    ),
    'DATABASE' => array(
        'adapter'  => 'mysqli',
        'dbname'   => 'dja',
        'username' => 'root',
        'password' => '',
        'profiler' => true
    ),
);
