<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BitbucketCodeRepositoryProvider implements Provider
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    /**
     * @return CodeRepository[]
     */
        public function fetch(FetchCriteria $criteria): iterable
        {
            $response = $this->httpClient->request('GET', "https://api.bitbucket.org/2.0/repositories/$criteria->organizationName?page=1&per_page=100");
            $nextPage = $response->toArray();
            $codeRepositories = $this->buildCodeRepositories($response->toArray(), $criteria, []);
            while (isset($nextPage['next'])) {
                $response = $this->httpClient->request('GET', "$nextPage");
                $nextPage = $response['next'];
                $codeRepositories = $this->buildCodeRepositories($response->toArray(), $criteria, $codeRepositories);
            }
            return $codeRepositories;
        }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Exception
     */
    private function buildCodeRepositories(array $fetchedData, FetchCriteria $criteria, array $codeRepositories): array
        {
            foreach ($fetchedData['values'] as $item) {
                $date = new \DateTimeImmutable($item['created_on']);
                $contributorsArray = $this->httpClient->request('GET', $item['links']['commits']['href'])->toArray();
                $contributions = count($contributorsArray['values']);
                $contributionsNext = $contributorsArray['next'] ?? '';
                while($contributionsNext) {
                    $contributorsNextPage = $this->httpClient->request('GET', $contributionsNext)->toArray();
                    $contributions += count($contributorsNextPage['values']);
                    $contributionsNext = $contributorsNextPage['next'] ?? '';
                }
                $openIssuesArray = $this->httpClient->request('GET', $item['links']['pullrequests']['href'])->toArray();
                $openIssues = count($openIssuesArray['values']);
                $issuesNext = $openIssues['next'] ?? '';
                while($issuesNext) {
                    $issuesNextPage = $this->httpClient->request('GET', $issuesNext)->toArray();
                    $openIssues += count($issuesNextPage['values']);
                    $issuesNext = $issuesNextPage['next'] ?? '';
                }
                $codeRepositories[] = new CodeRepository(
                    externalId: (string)$item['uuid'],
                    orgname: $criteria->organizationName,
                    reponame: $item['name'],
                    url: $item['links']['html']['href'],
                    provider: $criteria->providerName,
                    creationdate: $date,
                    stargazers: 0,
                    openIssuesNumber: $openIssues,
                    contributionsNumber: $contributions
                 );
            }
            return $codeRepositories;
        }
}