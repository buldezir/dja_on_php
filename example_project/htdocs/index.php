<?php
// Dja never let u down :)
error_reporting(E_ALL);
//phpinfo(); exit;

define('HTDOCS_PATH', realpath(dirname(__FILE__)));
define('LIBRARY_PATH', realpath(HTDOCS_PATH.'/../../library'));

############ THIS IS NOT NEEDED IF U SUCCESSFULLY RUN PROJECT ONCE ##################
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    echo '<h1>U need PHP version 5.3 or newer to use this wramework</h1>'; exit;
}
if (dir_is_empty(LIBRARY_PATH . '/Zend')) {
	echo '<h1>Put Zend framework Library contents in library/Zend</h1>'; exit;
}
if (dir_is_empty(LIBRARY_PATH . '/Twig')) {
	echo '<h1>Put Twig library contents in library/Twig</h1>'; exit;
}
if (!file_exits(HTDOCS_PATH.'/.htaccess')) {
	$htaccess = "AddDefaultCharset utf-8
RewriteEngine On
RewriteBase /
# Add trailing slash
RewriteCond %{REQUEST_URI} !(.*)/$
RewriteRule ^(.*)$ $1/ [L,R=301]
#
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA]";
	file_put_contents(HTDOCS_PATH.'/.htaccess', $htaccess);
}
$tplCacheDir = realpath(HTDOCS_PATH.'/../../example_project').'/templates/_cache';
if (!file_exits($tplCacheDir) {
	mkdir($tplCacheDir, 0777);
}
############### END ################################################################

// edit for yours place:
require LIBRARY_PATH . '/Dja/Loader.php';
Dja_Loader::init(realpath(HTDOCS_PATH.'/../../example_project'));
Dja_App::getInstance()->run();

function dump($var)
{
    return '<pre>'.var_export($var, 1).'</pre>';
}
function qprofile()
{
    $qp = Dja_Db::getProfiler()->getQueryProfiles();
    if ($qp === false) {
        return '';
    }
    $s = '<ol>';
    foreach ($qp as $q) {
        $s .= '<li>'.$q->getQuery().'</li>';
    }
    $s .= '</ol>';
    return $s;
}
function dir_is_empty($dir)
{
	$dir = new DirectoryIterator($dir);
	$c = 0;
	foreach ($dir as $f) { if (!$f->isDot()) $c++; }
	return $c === 0;
}

echo '<br/><p>memory usage: '.round(memory_get_usage()/1000,3).' kb < '.round(memory_get_peak_usage()/1000,3).' kb</p>';
echo qprofile();
//echo dump(xcache_info());
