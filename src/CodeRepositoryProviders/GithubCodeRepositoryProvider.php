<?php

declare(strict_types = 1);

namespace App\CodeRepositoryProviders;

use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class GithubCodeRepositoryProvider implements Provider
{
     public function __construct(private HttpClientInterface $httpClient)
     {
     }

    /**
     * @return CodeRepository[]
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
       public function fetch(FetchCriteria $criteria): iterable
       {
           $response = $this->httpClient->request('GET', "https://api.github.com/orgs/$criteria->organizationName/repos?page=1&per_page=100");
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
     * @param array $fetchedData
     * @param CodeRepository[] $codeRepositories
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Exception
     */
    private function buildCodeRepositories(array $fetchedData, FetchCriteria $criteria, array $codeRepositories): array
    {
        foreach ($fetchedData as $item) {

            $contributorsArray = $this->httpClient->request('GET', $item['contributors_url']);
            $headerCommits = $this->fetchLinksFromHeader($contributorsArray->getHeaders());
            $contribubons = $contributorsArray->toArray();
            $contribubons = array_map(static fn(array $contributor) => $contributor['contributions'], $contribubons);
            $contributions = array_sum($contribubons);
            while(isset($headerCommits['next'])) {
                $headerCommitsNext = $this->httpClient->request('GET', $headerCommits['next']);
                $headerArray = $headerCommitsNext->toArray();
                $headerArray = array_map(static fn(array $contributor) => $contributor['contributions'], $headerArray);
                $contributions += array_sum($headerArray);
                $headerCommits = $headerCommits['next'] ??'';
            }

            $codeRepositories[] = new CodeRepository(
                externalId: (string)$item['id'],
                orgname: $criteria->organizationName,
                reponame: $item['name'],
                url: $item['html_url'],
                provider: $criteria->providerName,
                creationdate: new DateTimeImmutable($item['created_at']),
                stargazers: $item['stargazers_count'],
                openIssuesNumber: $item['open_issues_count'],
                contributionsNumber: $contributions
            );
        }

        return $codeRepositories;
    }
}