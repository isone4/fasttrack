<?php

declare(strict_types=1);

namespace App\Message;


use App\CodeRepositoryProviders\BitbucketCodeRepositoryProvider;
//use App\Command\FetchRepositoryCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class BitbucketFetchRepositories implements MessageHandlerInterface
{
    public function __invoke(BitbucketCodeRepositoryProvider $fetchRepository)
    {
        dump($fetchRepository);
    }

}