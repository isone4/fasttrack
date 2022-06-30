<?php

namespace App;

use App\CodeRepositoryProviders\CodeRepository;
use App\CodeRepositoryProviders\FetchCriteria;
use App\CodeRepositoryProviders\Provider;
use App\Entity\CodeRepo;
use Doctrine\ORM\EntityManagerInterface;

class SynchronizeRepository
{
    public function __construct(private Provider $provider, private EntityManagerInterface $entityManager)
    {
    }

    public function execute(FetchCriteria $fetchCriteria): void
    {
        /**
         * @var CodeRepository[] $codeRepositories
         */
        $codeRepositories = $this->provider->fetch($fetchCriteria);

        foreach ($codeRepositories as $repository) {
            $trust = $repository->contributionsNumber + ($repository->openIssuesNumber * 1.2) + ($repository->stargazers * 2);

            $codeRepo = new CodeRepo(
                (string) $repository->externalId,
                $repository->orgname,
                $repository->reponame,
                $repository->url,
                $repository->provider,
                $repository->creationdate,
                $repository->stargazers,
                $repository->openIssuesNumber,
                $repository->contributionsNumber,
                $trust
            );
            $this->entityManager->persist($codeRepo);
        }

        $this->entityManager->flush();
    }
}