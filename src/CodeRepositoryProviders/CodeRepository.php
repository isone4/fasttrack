<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

class CodeRepository
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $orgname,
        public readonly string $reponame,
        public readonly string $url,
        public readonly string $provider,
        public readonly \DateTimeImmutable $creationdate,
        public readonly int $stargazers,
        public readonly int $openIssuesNumber,
        public readonly int $contributionsNumber
    )
    {
    }

}