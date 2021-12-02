<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

interface Provider
{
    /**
    * @return CodeRepository[]
    */
    public function fetch(FetchCriteria $name): iterable;
}