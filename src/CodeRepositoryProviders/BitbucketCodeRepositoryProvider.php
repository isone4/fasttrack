<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

use CodeRepository;

class BitbucketCodeRepositoryProvider implements Provider
{
    const NAME = 'bitbucket';

    public function fetch(FetchCriteria $name): iterable
    {
        if ($name->providerName !== self::NAME) {
            return [];
        }

        // TODO: logic for bitbucket

        return [];
    }
}