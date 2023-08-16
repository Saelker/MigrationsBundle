<?php

namespace Saelker\MigrationsBundle\Util;

class SqlStatement
{
	public function __construct(private readonly string $sql, private readonly ?array $params)
	{
	}

	public function getSql(): string
	{
		return $this->sql;
	}

	public function getParams(): ?array
	{
		return $this->params;
	}
}