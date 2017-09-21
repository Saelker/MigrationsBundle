<?php

namespace Saelker\MigrationsBundle\Util;

class SqlStatement
{
	/**
	 * @var string
	 */
	private $sql;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * SqlStatement constructor.
	 * @param string $sql
	 * @param array $params
	 */
	public function __construct($sql, $params)
	{
		$this->sql = $sql;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function getSql()
	{
		return $this->sql;
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
}