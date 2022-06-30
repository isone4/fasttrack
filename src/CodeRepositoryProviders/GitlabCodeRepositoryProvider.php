<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
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

            $response = $this->httpClient->request('GET', 'https://graph.microsoft.com/v1.0/me/calendar', [
                'auth_bearer' => 'EwCAA8l6BAAUwihrrCrmQ4wuIJX5mbj7rQla6TUAAXfkYmnRUDzofmt+teBMDJklNWVqArqILlyq/OFf5rrw8UBn+lsl+5NYZmrTTAW3hb0PaxG5B856mZmTlq5Qs1qxN1JRckRVT0jhkoxyvZlfKAGfUtfjzYKF//5DSYFSC4PuN3R32yxjT/Qb+DWsPRTjRrzhCICKRmUcvk9tOGNHbc6JWshHVDIkvJEGJ+OebiBrP95rq6tuX7q0iuBKvnr/ASBYSJ0Ji+BxTgwcy8c1TF2WllMJfcP5UgbM2hLVUiTLoDrYNAagixTAi3nkdlm2vODrCOlJIqE965P4k4kRKl6UhFTBbFfFPhoVlJdbg2gZMYTdtbVaXesbW7/EOpkDZgAACKgJdrkVDYV0UAKCI4DiWSHw/n25R3LSR6xdiLrMbs+FMtHn/JDIpyA+ez6jDnSr2SWDj77uPLhPF0M0s467DARoDFyVRYGO4CCx0H1cx0kyXMjcjillMilUT/NMbcK88di8S5lSPEtGUh8pZrndkD45KkF4vPfagKUTPI58Vv0xLO+2cqZYaTivBVNTsWtguwYJtUM9gaTeTVFy+Puaqfotp21jwDYP9DhW8BbSIjmgdWKdD737F+3BApFMhRbMoto3KALK2SM8Vwq28BWLt8jiclcZJ1w+4dRiBxtgYh4T3UL8uvI0b7h1/3IFKI22pAiNJg6lt1sSjEJrBCliyK1UJq6BnCeYCGp6+hfvojRPEd/rAdXmrzP0LCFKOucgsjETsgnzN/y9siJkxZMAtl2+yGEPwq47bAzIyKW2x8UakzDhyg14z/NCJnrGuOtIP6ADtdjgUBfqCStyqwj6VHYfPiU9XL/2Qal+Vz9SmwfbxdR9IBfuFDgYaewP4fliI1lcDZUJjVXLOPMbl1W8A2nJ9pVswNnQAMS1GKezOTqcuWESZeSSSQ0Qcl0pqPxuTubq6EZhUJWebcZ4jc8+BNAz04lv3GXD6NIY0gml1JIs2oi4yO9SlcSErMaedhjvr2Why6gJsGNlHQM6ANHNmjAvEhON2hiCwaYV5G4uvnZnKQwj3j1N8tDOBBntW3WR7pxdOFQAn0zcgsfzi5DZdG2ubPMF3IyPMIsTgEZVJlW6L6rN4AnMALHljgoQqFBSN06djDmhMpOS+YNN0Q0+afIfpSXH7RWH+p6bkgI='
            ]);
            dump($response->toArray());die;

            $response = $this->httpClient->request('GET', "https://gitlab.com/api/v4/users/$criteria->organizationName/projects?private_token=$criteria->accessKey&page=1&per_page=100");
//            $response = $this->httpClient->request('GET', 'http://localhost:8080/api/code_repos');
//            dump($response);die;
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
//                $commitsArray = $this->httpClient->request('GET', "https://gitlab.com/api/v4/projects/12862471/repository/commits?page=1&per_page=100");
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
//            dump($codeRepositories);die;
            return $codeRepositories;
    }

    public function bus(MessageBusInterface $bus)
    {
//        $bus->dispatch($message);
    }
}
