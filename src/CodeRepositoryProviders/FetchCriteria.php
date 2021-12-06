<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

class FetchCriteria
{
    public function __construct(public readonly string $organizationName, public readonly string $providerName, public $accessKey)
    {
    }
}