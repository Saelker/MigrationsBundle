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
	 *
	 * @param string $sql
	 * @param array $params
	 */
	public function __construct(string $sql, ?array $params)
	{
		$this->sql = $sql;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function getSql(): string
	{
		return $this->sql;
	}

	/**
	 * @return array|null
	 */
	public function getParams(): ?array
	{
		return $this->params;
	}
}