<?php

declare(strict_types=1);

namespace App\Command;

use App\CodeRepositoryProviders\FetchCriteria;
use App\SynchronizeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchRepositoryCommand extends Command
{
    public function __construct(private readonly SynchronizeRepository $synchronizeRepository)
    {
        parent::__construct();
    }

    protected static $defaultName = 'app:fetch-repository';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orgname = $input->getArgument('organizationName');
        $provider = $input->getArgument('providerName');
        $accessKey = $input->getArgument('accessKey');

        $output->writeln($input->getArgument('organizationName'));
        $output->writeln($input->getArgument('providerName'));
        $output->writeln($input->getArgument('accessKey'));

        $this->synchronizeRepository->execute(new FetchCriteria(
            $orgname,
            $provider,
            $accessKey
        ));

        return Command::SUCCESS;
    }

    protected function configure()
    {
        parent::configure(); // TODO: Change the autogenerated stub
        $this->addArgument('organizationName', InputArgument::REQUIRED, 'the organization name');
        $this->addArgument('providerName', InputArgument::REQUIRED, 'provider name');
        $this->addArgument('accessKey', InputArgument::OPTIONAL, 'personal access token');
    }
}