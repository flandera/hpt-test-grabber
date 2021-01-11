<?php

declare(strict_types=1);

namespace App;

interface IOutput
{

	/**
	 * @return string
	 */
	public function getJson(): string;

    public function setResults(array $results);
}
