<?php

declare(strict_types = 1);

namespace App\CodeRepositoryProviders;

use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GithubCodeRepositoryProvider implements Provider
{
     public function __construct(private HttpClientInterface $httpClient)
     {
     }

     /**
      * @return CodeRepository[]
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
        if (!isset($header['link'])) {
            return [];
        }
        $explodedlinks = explode(",", ($header['link'][0]));
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

            $contributorsArray = $this->httpClient->request('GET', $item['contributors_url'])->toArray();
//            $headerCommits = $this->fetchLinksFromHeader($contributorsArray->getHeaders());

//            dump($contributorsArray);die;
            $contributorsArray = array_map(static fn(array $contributor) => $contributor['contributions'], $contributorsArray);
            $contributions = array_sum($contributorsArray);
//            dump($item['contributors_url']);die;

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

//    private function fetchCommitsNextPage(array $headerCommits): array
//    {
//
//    }
}