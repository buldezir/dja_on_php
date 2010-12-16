<?php

class Dja_Db_Backend_Mysql_Connection extends Dja_Db_Connection
{
	/**
	 *
	 * @return mysqli
	 */
	protected function _getConnection()
	{
		if ($this->_connection === null) {
			if (!isset($this->_options['username'])) {
				throw new Dja_Db_Exception('You must provide database username');
			}
			if (!isset($this->_options['password'])) {
				throw new Dja_Db_Exception('You must provide database password');
			}

			if (isset($this->_options['socket'])) {
				$this->_connection = new mysqli(null, $this->_options['username'], $this->_options['password'], $this->_options['dbname'], null, $this->_options['socket']);
			} else {
				$host = isset($this->_options['host']) ? $this->_options['host'] : 'localhost';
				$port = isset($this->_options['port']) ? $this->_options['port'] : '3307';
				$this->_connection = new mysqli($host, $this->_options['username'], $this->_options['password'], $this->_options['dbname'], $port, null);
			}
		}
		return $this->_connection;
	}

	public function qn($value)
    {
        return '`'.$value.'`';
    }

    public function qv($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
		if (is_array($value)) {
			foreach ($value as &$v) {
				$v = $this->qv($v);
			}
			return implode(', ', $value);
		}
        return "'" . $this->_getConnection()->real_escape_string($value) . "'";
    }

	public function query($sql, array $params = null)
	{
		if ($params) {
			$sql = $this->applyQueryParams($sql, $params);
		}
		if ($this->_getConnection()->query($sql) !== false) {
			return true;
		} else {
			return false;
		}
	}

	public function fetchOne($sql, array $params = null)
	{
		if ($params) {
			$sql = $this->applyQueryParams($sql, $params);
		}
		$res = $this->_getConnection()->query($sql);
		if ($res !== false) {
			$row = $res->fetch_row();
			return $row[0];
		} else {
			return false;
		}
	}

	public function fetchRow($sql, array $params = null)
	{
		if ($params) {
			$sql = $this->applyQueryParams($sql, $params);
		}
		$res = $this->_getConnection()->query($sql);
		if ($res !== false) {
			return $res->fetch_assoc();
		} else {
			return false;
		}
	}

	public function fetchAll($sql, array $params = null)
	{
		if ($params) {
			$sql = $this->applyQueryParams($sql, $params);
		}
		$res = $this->_getConnection()->query($sql);
		if ($res !== false) {
			return $res->fetch_all(MYSQLI_ASSOC);
		} else {
			return false;
		}
	}
}