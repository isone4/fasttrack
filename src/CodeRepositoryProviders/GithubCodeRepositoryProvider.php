<?php

declare(strict_types = 1);

namespace App\CodeRepositoryProviders;

use DateTimeImmutable;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
            $contributorsUrl = $item['contributors_url'];
            $contributorsArray = $this->httpClient->request('GET', "$contributorsUrl?page=1&per_page=100");
            $contributorsHeader = $this->fetchLinksFromHeader($contributorsArray->getHeaders());
//            $commitsArray = $contributorsArray->toArray();
            $commitsArray = array_map(static fn(array $contributor) => $contributor['contributions'], (array)$contributorsArray);
            $contributions = array_sum($commitsArray);
            $commitsNext = $contributorsHeader['next'] ?? '';
            while($commitsNext) {
                $headerCommitsNext = $this->httpClient->request('GET', $contributorsHeader['next']);
                $commitsHeader = $this->fetchLinksFromHeader($headerCommitsNext->getHeaders());
//                $headerArray = $headerCommitsNext->toArray();
                $headerArray = array_map(static fn(array $contributor) => $contributor['contributions'], (array)$headerCommitsNext);
                $contributions += array_sum($headerArray);
                $contributorsHeader = $commitsHeader['next'] ?? '';
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