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
//        {
//            if ($name->providerName !== self::NAME) {
//                return [];
//            }
        {
            $response = $this->httpClient->request('GET', "https://api.bitbucket.org/2.0/repositories/$criteria->organizationName?page=1&per_page=100");
            $codeRepositories = $this->buildCodeRepositories($response->toArray(), $criteria, []);
            while(isset($codeRepositories['next'])) {
                $response = $this->httpClient->request('GET', $codeRepositories['next']);
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
     */
    private function buildCodeRepositories(array $fetchedData, FetchCriteria $criteria, array $codeRepositories): array
        {
            foreach ($fetchedData['values'] as $item) {
                $date = new \DateTimeImmutable($item['created_on']);
                $contributorsArray = $this->httpClient->request('GET', $item['links']['commits']['href'])->toArray();
                $contributions = count($contributorsArray['values']);
                $openIssuesArray = $this->httpClient->request('GET', $item['links']['pullrequests']['href'])->toArray();
                $openIssues = count($openIssuesArray['values']);

                $nextPage = $fetchedData['next'];
                if(!isset($nextPage))

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

//    private function nextPage()
}