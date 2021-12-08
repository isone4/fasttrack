<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

use Symfony\Component\HttpFoundation\Response;
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
            $response = $this->httpClient->request('GET', "https://gitlab.com/api/v4/users/$criteria->organizationName/projects?private_token=$criteria->accessKey&page=1&per_page=100");
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
        $headerLinksParser = new HeaderLinksParser($header);
        return $headerLinksParser->headerLinks();
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
                $commitsArray = $this->httpClient->request('GET', "https://gitlab.com/api/v4/projects/$commitsId/repository/commits?page=1&per_page=100");
                if ($commitsArray->getStatusCode() === Response::HTTP_NOT_FOUND) {
                    continue;
                }
                $commitsHeader = $this->fetchLinksFromHeader($commitsArray->getHeaders());
                $commitsArray = $commitsArray->toArray();
                $contributions = count($commitsArray);
                $commitsHeaderNext = $commitsHeader['next'] ?? '';
                while($commitsHeaderNext) {
                    $commitsNextPage = $this->httpClient->request('GET', $commitsHeaderNext);
                    $commitsHeaderLinks = $this->fetchLinksFromHeader($commitsNextPage->getHeaders());
                    $commitsNextPage = $commitsNextPage->toArray();
                    $contributions += count($commitsNextPage);
                    $commitsHeaderNext = $commitsHeaderLinks['next'] ?? '';
                }
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
                    contributionsNumber: $contributions
                );
            }
            return $codeRepositories;
    }
}