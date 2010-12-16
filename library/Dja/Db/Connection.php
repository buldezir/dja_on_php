<?php

abstract class Dja_Db_Connection
{
	protected $_connection = null;
	protected $_options;

	public function  __construct(array $options)
	{
		$this->_options = $options;
	}

	abstract protected function _getConnection();

	/**
	 * replace placeholders with escaped values
	 * @param string $sql
	 * @param array $params
	 * @return string
	 */
	public function applyQueryParams($sql, array $params = array())
	{
		foreach ($params as $key => $value) {
			$value = $this->qv($value);
			if (is_int($key)) {
				$sql = str_replace('?', $value, $sql, 1);
			} else {
				$sql = str_replace(':'.$key, $value, $sql);
			}
		}
		return $sql;
	}

	/**
     * quote table, field name
     * @param string $s
	 * @return string
     */
    abstract public function qn($s);

    /**
     * quote value
     * @param mixed $s
	 * @return string|int|float
     */
    abstract public function qv($s);

	/**
	 *
	 * @param string $sql
	 * @param array $params
	 * @return bool
	 */
	abstract public function query($sql, array $params = null);

	/**
	 *
	 * @param string $sql
	 * @param array $params
	 * @return string|int|false|null
	 */
	abstract public function fetchOne($sql, array $params = null);

	/**
	 *
	 * @param string $sql
	 * @param array $params
	 * @return array|false
	 */
	abstract public function fetchRow($sql, array $params = null);

	/**
	 *
	 * @param string $sql
	 * @param array $params
	 * @return array|false
	 */
	abstract public function fetchAll($sql, array $params = null);

	/**
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}
}