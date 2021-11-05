<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Cat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
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
        $race = 'dachowiec';
        $cat = new Cat('blue', $race);
        $cat2 = new Cat('green', 'perski');
        dump($cat);
        dump($cat2);
        $this->entityManager->persist($cat);
        $this->entityManager->persist($cat2);
        $this->entityManager->flush();


//        echo "test".PHP_EOL;
//
//        $this->httpClient->request('GET', 'https://api.github.com/users/octocat/orgs');
//
//        $response = $this->httpClient->request('GET', 'https://api.github.com/users/GITFenix/repos');
//
//        dump($response->toArray());die;


        // ... put here the code to create the user

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}