<?php

class Dja_Template extends Dja_Singleton
{
    /**
     *
     * @var Twig_Environment
     */
    protected $_t;
    
    protected $_globalVars = array();
    
    protected function _construct()
    {
        $contribTplDir = DJA_PATH . DIRECTORY_SEPARATOR . 'Contrib' . DIRECTORY_SEPARATOR . 'Templates';
        $loader = new Twig_Loader_Filesystem(array(CONFIG()->TEMPLATE_DIR, $contribTplDir));
        $this->_t = new Twig_Environment($loader, array(
            'cache' => CONFIG()->TEMPLATE_CACHE_DIR,
            'debug' => CONFIG()->DEBUG
        ));
    }
    
    public function setVar($key, $val)
    {
        $this->_globalVars[$key] = $val;
        return $this;
    }
    
    public function setVars($vars)
    {
        foreach ($vars as $key => $val) {
            $this->setVar($key, $val);
        }
        return $this;
    }
    
    public function render($tpl, array $vars = array())
    {
        if (count($this->_globalVars)) {
            $vars = Dja_Function::arrayMergeRecursive($this->_globalVars, $vars);
        }
        $template = $this->_t->loadTemplate($tpl);
        return $template->render($vars);
    }
}