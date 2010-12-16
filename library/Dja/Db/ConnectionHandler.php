<?php

abstract class Dja_Db_ConnectionHandler
{
	const NAME_DEFAULT  = 'default';
	const NAME_FORREAD  = 'for_read';
	const NAME_FORWRITE = 'for_write';

	protected static $_connections = array();

	private function  __construct() {}

	/**
     *
     * @param string $backend
     * @return Dja_Db_Connection
     */
	public static function init(array $config)
	{
		if (!isset($config[self::NAME_DEFAULT])) {
			throw new Dja_Db_Exception('Define default connection.');
		}
		foreach ($config as $connName => $connOptions) {
			if (!isset($connOptions['adapter'])) {
				throw new Dja_Db_Exception('Define "adapter" for "'.$connName.'" connection');
			}
			if (!isset($connOptions['dbname'])) {
				throw new Dja_Db_Exception('Define "dbname" for "'.$connName.'" connection');
			}
			self::$_connections[$connName] = $connOptions;
		}
	}

	/**
	 * return array of inited (!) connections
	 * @return array
	 */
	public static function getAll()
	{
		$result = array();
		foreach (self::$_connections as $connName => $zz) {
			$result[$connName] = self::get($connName);
		}
		return $result;
	}

	/**
	 * lazy load
	 * @param string $name
	 * @return Dja_Db_Connection
	 */
	public static function get($name = self::NAME_DEFAULT)
	{
		if (!isset(self::$_connections[$name])) {
			throw new Dja_Db_Exception('Connection "'.$name.'" doesn\'t exist.');
		}
		if (!self::$_connections[$name] instanceof Dja_Db_Connection) {
			$options = self::$_connections[$name];
			$class = 'Dja_Db_Backend_'.ucfirst($options['adapter']).'_Connection';
			unset($options['adapter']);
			self::$_connections[$name] = new $class($options);
		}
		return self::$_connections[$name];
	}

	/**
	 * shortcut for connection for read queries
	 * @return Dja_Db_Connection
	 */
	public static function getForRead()
	{
		try {
			return self::get(self::NAME_FORREAD);
		} catch (Dja_Db_Exception $e) {
			return self::get();
		}
	}

	/**
	 * shortcut for connection for write queries
	 * @return Dja_Db_Connection
	 */
	public static function getForWrite()
	{
		try {
			return self::get(self::NAME_FORWRITE);
		} catch (Dja_Db_Exception $e) {
			return self::get();
		}
	}
}