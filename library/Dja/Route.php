<?php

class Dja_Route
{
    /**
     *
     * @var Dja_Request
     */
    protected $_request;
    
    protected $_resolveLog = array();
    
    public function __construct(Dja_Request $request)
    {
        $this->_request = $request;
    }
    
    public function getResolveLog()
    {
        return $this->_resolveLog;
    }
    
    public function resolve(array $urls, $path = null)
    {
        if ($path === null) {
            $path = $this->_request->getPathInfo();
        }
        $urls = array_reverse($urls, true);
        foreach ($urls as $regex => $target) {
            $this->_resolveLog[] = $regex;
            $arguments = array();
            if (preg_match('#'.$regex.'#ui', $path, $arguments)) {
                // if url inclusion:
                if (is_string($target) && strpos($target, '::') === false) {
                    $path = str_replace($arguments[0], '', $path);
                    $incUrls = Dja_Loader::get($target, 'is_array', array());
                    return $this->resolve($incUrls, $path);
                } elseif (is_string($target) && strpos($target, '::') !== false) {
                    $target = explode('::', $target);
                    unset($arguments[0]);
                    return array($target, $arguments);
                } elseif (is_array($target) && count($target) === 2) {
                    unset($arguments[0]);
                    return array($target, $arguments);
                } elseif (is_array($target) && count($target) === 1) {
                    unset($arguments[0]);
                    return array($target[0], $arguments);
                } else {
                    throw new Dja_Exception('bad call target in "'.$regex.'"');
                }
                break;
            }
        }
        return array(false, false);
    }
}