<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
//use Symfony\Component\RateLimiter\Policy\FixedWindowLimiter;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BitbucketCodeRepositoryProvider implements Provider
{
    public function __construct(private HttpClientInterface $httpClient, private RateLimiterFactory $anonymousApiLimiter, MessageBusInterface $bus)
    {
    }

    /**
     * @return CodeRepository[]
     */
        public function fetch(FetchCriteria $criteria): iterable
        {
            $responseArray = $this->request($criteria->providerName, "https://api.bitbucket.org/2.0/repositories/$criteria->organizationName?page=1&per_page=100");
            $nextPage = $responseArray;
            $codeRepositories = $this->buildCodeRepositories($responseArray, $criteria, []);
            while (isset($nextPage['next'])) {
                $nextPageResponse = $this->request($criteria->providerName, $nextPage['next']);
                $nextPage = $nextPageResponse;
                $codeRepositories = $this->buildCodeRepositories($nextPageResponse, $criteria, $codeRepositories);
            }
            return $codeRepositories;
        }

//        public function bus(FetchCriteria $criteria, MessageBusInterface $bus): iterable
//        {
//            $response = $this->request($criteria->providerName), "https://api.bitbucket.org/2.0/repositories/$criteria->organizationName?page=1&per_page=100");
//            $responseArray
//            $bus->dispatch($nextPage);
//        }

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
                $providerName = $criteria->providerName;
                $contributorsArray = $this->request($providerName, $item['links']['commits']['href']);
                $contributions = count($contributorsArray['values']);
                $contributionsNext = $contributorsArray['next'] ?? '';
                while($contributionsNext) {
                    $contributorsNextPage = $this->request($providerName, $contributionsNext);
                    $contributions += count($contributorsNextPage['values']);
                    $contributionsNext = $contributorsNextPage['next'] ?? '';
                }
                $openIssuesArray = $this->request($providerName, $item['links']['pullrequests']['href']);
                $openIssues = count($openIssuesArray['values']);
                $issuesNext = $openIssuesArray['next'] ?? '';
                while($issuesNext) {
                    $issuesNextPage = $this->request($providerName, $issuesNext);
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

    private function rateLimiter(string $providerName)
    {
        $limiter = $this->anonymousApiLimiter->create($providerName);
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
    }

    /**
     * @param string $providerName
     * @param mixed $url
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function request(string $providerName, mixed $url): array
    {
        $this->rateLimiter($providerName);
        return $this->httpClient->request('GET', $url)->toArray();
    }
}
