<?php
Dja_Loader::import('dja.shortcut');

class Dja_App extends Dja_Singleton
{
    protected $_config;
    protected $_request;
    protected $_middleware;
    protected $_urlconf;
    
    protected $_executedOnce = false;
    
    public function run()
    {
        if ($this->_executedOnce === false) {
            $this->_runAll();
            $this->_runApp();
        }
        $this->_executedOnce = true;
    }
    
    /**
     *
     * @return Dja_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     *
     * @return Dja_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
    public function addUrlMatch($pattern, $incOrCall, $method = null)
    {
        if (is_array($incOrCall)) {
            $val = $incOrCall;
        } elseif (is_string($incOrCall) && $method === null) {
            $val = $incOrCall;
        } elseif (is_string($incOrCall) && $method !== null) {
            $val = array($incOrCall, $method);
        }
        $this->_urlconf[$pattern] = $val;
        return $this;
    }
    
    protected function _runAll()
    {
        $methods = get_class_methods($this);
        foreach ($methods as $m) {
            if (strpos($m, '_init') !== false) {
                $this->$m();
            }
        }
    }
    
    protected function _initConfig()
    {
        $globalConf = Dja_Loader::get('dja.conf.globalSettings');
        $localConf  = Dja_Loader::get('settings', 'is_array', array()); // project config
        $conf = Dja_Function::arrayMergeRecursive($globalConf, $localConf);
        $this->_config = new Dja_Config($conf);
        unset($conf);
    }
    
    protected function _initDb()
    {
        $c = $this->_config->DATABASE;
        $db = Zend_Db::factory($c->adapter, $c->toArray());
        Dja_Db::getInstance()->setAdapter($db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }
    
    protected function _initRequest()
    {
        $this->_request = new Dja_Request();
    }
    
    protected function _initTplVars()
    {
        $tpl = Dja_Template::getInstance();
        $tpl->setVar('request', $this->_request);
        $tpl->setVar('config', $this->_config);
    }
    
    protected function _initMiddleware()
    {
        $middlewareConf = $this->_config->MIDDLEWARE->toArray();
        $middlewareConf = array_unique($middlewareConf);
        $this->_middleware = array();
        foreach ($middlewareConf as $middlewareClass) {
            $m = new $middlewareClass();
            if ($m instanceof Dja_Middleware) {
                $m->setRequest($this->_request);
                $this->_middleware[] = $m;
            } else {
                throw new Dja_Exception('Middleware classes must be instanceof Dja_Middleware!');
            }
        }
    }
    
    protected function _initUrls()
    {
        $this->_urlconf = Dja_Loader::get('urls', 'is_array', array());
    }
    
    protected function _initApps()
    {
        $apps = $this->_config->INSTALLED_APPS->toArray();
        foreach ($apps as $app) {
            Dja_Loader::import($app.'.setup');
            $setupClass = ucfirst($app).'_Setup';
            if (class_exists($setupClass, false)) {
                new $setupClass($this);
            }
        }
    }
    
    protected function _initModels()
    {
        if ($this->_config->AUTO_INIT_MODELS !== true) return;
        $loadedClasses1 = get_declared_classes();
        // try to load file with project models:
        try {
            Dja_Loader::loadFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Models.php');
        } catch (Dja_Exception $e) {}
        // load all apps models
        $apps = $this->_config->INSTALLED_APPS->toArray();
        //$apps = array_unique($apps);
        foreach ($apps as $app) {
            $md = APPLICATION_PATH . DIRECTORY_SEPARATOR . ucfirst($app) . DIRECTORY_SEPARATOR . 'Models';
            try {
                $di = new DirectoryIterator($md);
                foreach ($di as $file) {
                    if ($file->isFile()) Dja_Loader::loadFile($file->getPathname());
                }
            } catch (UnexpectedValueException $e) {}
            try {
                Dja_Loader::loadFile($md.'.php');
            } catch (Dja_Exception $e) {}
        }
        $modelClasses = array_diff(get_declared_classes(), $loadedClasses1);
        $modelClasses = array_filter($modelClasses, function($val){ return is_subclass_of($val, 'Dja_Db_Model'); });
        // initiation of all found models
        foreach ($modelClasses as $modelClass) {
            $modelClass::metadata();
        }
    }
    
    protected function _runApp()
    {
        $responseBodyAdd = '';
        ob_start();
        $response = $this->_runRequest();
        foreach ($this->_middleware as $m) {
            $response = $m->processResponse($response);
        }
        $responseBodyAdd = ob_get_contents();
        ob_end_clean();
        $response->appendBody($responseBodyAdd);
        $response->sendResponse();
    }
    
    /**
     * @return Dja_Response
     */
    protected function _runRequest()
    {
        // 1 run request (preRoute) helpers, they can return response
        foreach ($this->_middleware as $m) {
            $response = $m->processRequest();
            if ($response) {
                return $response;
            }
        }
        
        // 2 run Route (urls)
        $router = new Dja_Route($this->_request);
        list($callback, $callbackparams) = $router->resolve($this->_urlconf);
        
        // try to call app init
        /*$apps = $this->_config->INSTALLED_APPS->toArray();
        $posibbleCalledApp = substr($callback[0], 0, strpos($callback[0], '_'));
        if (in_array($posibbleCalledApp, $apps)) {
            Dja_Loader::import($posibbleCalledApp.'.init');
            $initClass = ucfirst($posibbleCalledApp).'_Init';
            if (class_exists($initClass, false)) {
                $init = new $initClass($this);
                if (method_exists($init, 'init')) {
                    $response = $init->init();
                    if ($response) {
                        return $response;
                    }
                }
            }
        }*/
        
        if (is_callable($callback, true)) {
            // 3 run controller/views helpers (preDispatch)
            foreach ($this->_middleware as $m) {
                $response = $m->processView($callback, $callbackparams);
                if ($response) {
                    return $response;
                }
            }
            // 4 run controller method
            array_unshift($callbackparams, $this->_request);
            try {
                if (is_array($callback)) {
                    $controllerObj = new $callback[0];
                    $response = call_user_func_array(array($controllerObj, $callback[1]), $callbackparams);
                } elseif (is_string($callback)) {
                    Dja_Loader::import($callback);
                    if (function_exists($callback)) {
                        $response = call_user_func_array($callback, $callbackparams);
                    } else {
                        throw new Dja_Exception("Can't load view function '{$callback}'!");
                    }
                } else {
                    throw new Dja_Exception("Unknown callback '{$callback}'!");
                }
            } catch (Exception $e) {
                foreach ($this->_middleware as $m) {
                    $response = $m->processException($e);
                    if ($response) {
                        return $response;
                    }
                }
            }
            if (!$response instanceof Dja_Response) {
                throw new Dja_Exception("Controller/View must return Dja_Response object!");
            }
            return $response;
        } else {
            return new Dja_Response_404($router);
        }
    }
    
}
