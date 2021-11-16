<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\CodeRepo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchRepositoryCommand extends Command
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    protected static $defaultName = 'app:fetch-repository';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orgname = $input->getArgument('organizationName');
        $provider = $input->getArgument('providerName');

        $output->writeln($input->getArgument('organizationName'));
        $output->writeln($input->getArgument('providerName'));

        $this->httpClient->request('GET', "https://api.github.com/orgs/$orgname/repos?per_page=100");
        $response = $this->httpClient->request('GET', "https://api.github.com/orgs/$orgname/repos?page=1");

        $headerlinks = $this->fetchLinksFromHeader($response->getHeaders());

        $fetchedData = $response->toArray();

        $counter = 0;
        foreach($fetchedData as $item) {
            $codeRepo = $this->entityManager->getRepository(CodeRepo::class)->findOneBy(['externalId'=>$item['id']]);
            if ($codeRepo) {
                continue;
            }
            $contributors = $this->httpClient->request('GET', ($item['contributors_url']))->toArray();

            foreach($contributors as $contributor) {
                $contributions = $contributor['contributions'];
            }

            $trust = $contributions + ($item['open_issues_count'] * 1.2) + ($item['stargazers_count'] * 2);

            $codeRepo = new CodeRepo(
                (string) $item['id'],
                $orgname,
                $item['name'],
                $item['html_url'],
                'github',
                new \DateTimeImmutable($item['created_at']),
                $item['stargazers_count'],
                $item['open_issues_count'],
                $contributions,
                $trust
            );
            $this->entityManager->persist($codeRepo);
            $counter++;
        }

        $this->entityManager->flush();


        while (isset($headerlinks['next'])) {

            $response = $this->httpClient->request('GET', ($headerlinks['next']));
            $headerlinks = $this->fetchLinksFromHeader($response->getHeaders());
            $fetchedData = $response->toArray();
            foreach ($fetchedData as $item) {
                $codeRepo = $this->entityManager->getRepository(CodeRepo::class)->findOneBy(['externalId' => $item['id']]);
                if ($codeRepo) {
                    continue;
                }
                $contributors = $this->httpClient->request('GET', ($item['contributors_url']))->toArray();

                foreach ($contributors as $contributor) {
                    $contributions = $contributor['contributions'];
                }

                $trust = $contributions + ($item['open_issues_count'] * 1.2) + ($item['stargazers_count'] * 2);

                $codeRepo = new CodeRepo(
                    (string)$item['id'],
                    $orgname,
                    $item['name'],
                    $item['html_url'],
                    'github',
                    new \DateTimeImmutable($item['created_at']),
                    $item['stargazers_count'],
                    $item['open_issues_count'],
                    $contributions,
                    $trust
                );
                $this->entityManager->persist($codeRepo);
                $counter++;
            }
        }
        $this->entityManager->flush();
        $output->writeln('We have saved '.$counter.' new elements');

        return Command::SUCCESS;
    }

    private function fetchLinksFromHeader(array $header): array
    {
        if(!isset($header['link'])) {
            return [];
        }
        $explodedlinks = explode(",", ($header['link'][0]));
        $headerlinks = [];
        foreach($explodedlinks as $explodedlink) {
            $explodedlink = trim($explodedlink);
            $beginning = strpos($explodedlink, '<')+1;
            $end = strpos($explodedlink, '>')-1;
            $url = substr($explodedlink, $beginning, $end);

            $linktype = strpos($explodedlink, 'rel=')+5;
            $type = substr($explodedlink, $linktype, -1);
            $headerlinks[$type] = $url;
        }
        return $headerlinks;
    }

    protected function configure()
    {
        parent::configure(); // TODO: Change the autogenerated stub
        $this->addArgument('organizationName', InputArgument::REQUIRED, 'the organization name');
        $this->addArgument('providerName', InputArgument::REQUIRED, 'provider name');
    }
}