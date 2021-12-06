<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

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
            $response = $this->httpClient->request('GET', "https://gitlab.com/api/v4/users/$criteria->organizationName/projects?private_token=glpat-T9-AYsd4Ns49E8oBhcgT&page=1&per_page=100");
            $headerlinks = $this->fetchLinksFromHeader($response->getHeaders());
            $codeRepositories = $this->buildCodeRepositories($response->toArray(), $criteria, []);

            while (isset($headerlinks['next'])) {
                $response = $this->httpClient->request('GET', $headerlinks['next']);
                $headerlinks = $this->fetchLinksFromHeader($response->getHeaders());
                $codeRepositories = $this->buildCodeRepositories($response->toArray(), $criteria, $codeRepositories);
            }
            return $codeRepositories;
        }

    private function fetchLinksFromHeader(array $header): array
    {
        if (!isset($header['link'])) {
            return [];
        }
        $explodedlinks = explode(",", ($header['link']['0']));
        $headerlinks = [];
        foreach ($explodedlinks as $explodedlink) {
            $explodedlink = trim($explodedlink);
            $beginning = strpos($explodedlink, '<') + 1;
            $end = strpos($explodedlink, '>') - 1;
            $url = substr($explodedlink, $beginning, $end);
            $linktype = strpos($explodedlink, 'rel=') + 5;
            $type = substr($explodedlink, $linktype, -1);
            $headerlinks[$type] = $url;
        }
        return $headerlinks;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Exception
     */
    private function buildCodeRepositories(array $fetchedData, FetchCriteria $criteria, array $codeRepositories): array
        {
            foreach ($fetchedData as $item) {
                $date = new \DateTimeImmutable($item['created_at']);
                $commitsId = $item['id'];
                $commitsArray = $this->httpClient->request('GET', "https://gitlab.com/api/v4/projects/$commitsId/repository/contributors?private_token=glpat-T9-AYsd4Ns49E8oBhcgT&page=1&per_page=100")->toArray();
                $commitsArray = array_map(static fn($contributor) => $contributor['commits'], $commitsArray);
                $commitsNumber = array_sum($commitsArray);
                $openIssues = $item['open_issues_count'] ?? '';
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
            return $codeRepositories;
    }
}