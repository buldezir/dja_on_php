<?php

class Dja_Loader
{
    public static function init($appDir)
    {
        define('DJA_PATH', realpath(dirname(__FILE__)));
        define('APPLICATION_PATH', $appDir);
        $libPath = realpath(DJA_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        $include = array(
            $libPath,
            $appDir
        );
        set_include_path(implode(PATH_SEPARATOR, $include));
        self::registerAutoloader();
    }
    
    public static function registerAutoloader()
    {
        spl_autoload_register('Dja_Loader::loadClass');
    }
    
    public static function loadClass($class, $file = null)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }
        
        // autodiscover the path from the class name
        if ($file === null) {
            $file = self::format($class);
        }
        
        @include $file;
        
        if (!class_exists($class, false) && !interface_exists($class, false)) {
            throw new Dja_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
        }
    }
    
    /**
     * Dja_Loader::import('dja.conf.globalConfig');
     * @param $name
     * @return unknown_type
     */
    public static function import($name)
    {
        require_once self::format($name);
    }
    
    /**
     * Dja_Loader::get('dja.conf.globalConfig');
     * @param $name
     * @return unknown_type
     */
    public static function get($name, $typeCheckFunc = null, $default = null)
    {
        $file = self::format($name);
        if ($typeCheckFunc === null) {
            return self::loadFile($file);
        } else {
            $result = self::loadFile($file);
            if ($typeCheckFunc($result)) {
                return $result;
            } else {
                return $default;
            }
        }
    }
    
    public static function format($name)
    {
        if (strpos($name, '.') !== false) {
            $a = explode('.', $name);
            $a = array_map('ucfirst', $a);
            return implode(DIRECTORY_SEPARATOR, $a) . '.php';
        } elseif (strpos($name, '_') !== false) {
            if (strpos($name, '__') !== false) {
                $parts = explode('__', $name);
                return str_replace('_', DIRECTORY_SEPARATOR, $parts[0]) . '.php';
            } else {
                return str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
            }
        } else {
            return ucfirst($name) . '.php';
        }
    }
    
    /**
     *
     * @param string $file
     * @return mixed
     * @throws Dja_Exception
     */
    public static function loadFile($file)
    {
        if (self::isReadable($file)) {
            ob_start();
                $result = include($file);
            ob_end_clean();
            return $result;
        } else {
            throw new Dja_Exception('File "'.$file.'" not found');
        }
    }
    
    /**
     * @param string   $filename
     * @return boolean
     */
    public static function isReadable($filename)
    {
        if (is_readable($filename)) {
            // Return early if the filename is readable without needing the
            // include_path
            return true;
        }
        $eIncPath = explode(PATH_SEPARATOR, get_include_path());
        foreach ($eIncPath as $path) {
            if ($path == '.') {
                if (is_readable($filename)) {
                    return true;
                }
                continue;
            }
            $file = $path . '/' . $filename;
            if (is_readable($file)) {
                return true;
            }
        }
        return false;
    }
}