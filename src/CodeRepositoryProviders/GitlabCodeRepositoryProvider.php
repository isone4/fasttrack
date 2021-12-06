<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabCodeRepositoryProvider implements Provider
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    /**
     * @return CodeRepository[]
     */
        public function fetch(FetchCriteria $criteria): iterable
        {
            $response = $this->httpClient->request('GET', "https://gitlab.com/api/v4/users/$criteria->organizationName/projects?private_token=glpat-KvK-F9czs_ssXAX8vznD&page=1&per_page=100");
            $codeRepositories = $this->buildCodeRepositories($response->toArray(), $criteria, []);
            return $codeRepositories;
        }

    private function buildCodeRepositories(array $fetchedData, FetchCriteria $criteria, array $codeRepositories): array
        {
            foreach ($fetchedData as $item) {
                $date = new \DateTimeImmutable($item['created_at']);
                $commitsId = $item['id'];
                $commitsArray = $this->httpClient->request('GET', "https://gitlab.com/api/v4/projects/$commitsId/repository/contributors?private_token=glpat-KvK-F9czs_ssXAX8vznD&page=1&per_page=100")->toArray();
                $commitsArray = array_map(static fn($contributor) => $contributor['commits'], $commitsArray);
                $commitsNumber = array_sum($commitsArray);
                $openIssues = $item['open_issues_count'] ?? "";

                $codeRepositories[] = new CodeRepository(
                    externalId: (string)$item['id'],
                    orgname: $criteria->organizationName,
                    reponame: $item['name'],
                    url: $item['web_url'],
                    provider: $criteria->providerName,
                    creationdate: $date,
                    stargazers: $item['star_count'],
                    openIssuesNumber: (int)$openIssues,
                    contributionsNumber: $commitsNumber
                );
            }
//            dump($codeRepositories);die;
            return $codeRepositories;
    }
}