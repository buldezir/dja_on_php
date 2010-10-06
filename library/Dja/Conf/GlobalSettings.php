<?php
return array(
    'DEBUG' => true,
    'AUTO_INIT_MODELS' => true,

    'MIDDLEWARE' => array(
        'Dja_Contrib_Session_Middleware',
        'Dja_Middleware_Common'
    ),
    
    'TEMPLATE_DIR' => APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates',
    'TEMPLATE_CACHE_DIR' => APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . '_cache',
    
    'SESSION' => array(
        'handler' => 'Dja_Contrib_Session_Handler_Model',
        'cookie_name' => 'dja_sessid',
        'cookie_params' => array(
            'lifetime' => 3600*24*30,
            'httponly' => true
        ),
    ),
    
    'INSTALLED_APPS' => array(),
    
    'AUTH' => array(
        'model' => 'Dja_Contrib_Auth_Models__User'
    ),
);