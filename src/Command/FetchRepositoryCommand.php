<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Cat;
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

//        'test';
//        "test";
//
//        'https://api.github.com/users/'.$orgname.'/repos';
//
//        sprintf('https://api.github.com/users/%s/repos', $orgname);





        $output->writeln($input->getArgument('organizationName'));
        $output->writeln($input->getArgument('providerName'));

//        $this->httpClient->request('GET', "https://api.github.com/orgs/$orgname/repos");
//
//        $response = $this->httpClient->request('GET', "https://api.github.com/orgs/$orgname/repos");
//
//        $fetchedData = $response->toArray();

        $exampleArray[] = [
            "id" => 296848285,
            "node_id" => "MDEwOlJlcG9zaXRvcnkyOTY4NDgyODU=",
            "name" => "git-history-searcher",
    "full_name" => "cocoders/git-history-searcher",
    "private" => false,
    "owner" => [
        "login" => "cocoders",
      "id" => 7718185,
      "node_id" => "MDEyOk9yZ2FuaXphdGlvbjc3MTgxODU=",
      "avatar_url" => "https://avatars.githubusercontent.com/u/7718185?v=4",
      "gravatar_id" => "",
      "url" => "https://api.github.com/users/cocoders",
      "html_url" => "https://github.com/cocoders",
      "followers_url" => "https://api.github.com/users/cocoders/followers"
      "following_url" => "https://api.github.com/users/cocoders/following{/other_user}"
      "gists_url" => "https://api.github.com/users/cocoders/gists{/gist_id}"
      "starred_url" => "https://api.github.com/users/cocoders/starred{/owner}{/repo}"
      "subscriptions_url" => "https://api.github.com/users/cocoders/subscriptions"
      "organizations_url" => "https://api.github.com/users/cocoders/orgs"
      "repos_url" => "https://api.github.com/users/cocoders/repos"
      "events_url" => "https://api.github.com/users/cocoders/events{/privacy}"
      "received_events_url" => "https://api.github.com/users/cocoders/received_events"
      "type" => "Organization"
      "site_admin" => false
    ]
    "html_url" => "https://github.com/cocoders/git-history-searcher"
    "description" => "Simple project to provide REST api for searching different git repositories."
    "fork" => false
    "url" => "https://api.github.com/repos/cocoders/git-history-searcher"
    "forks_url" => "https://api.github.com/repos/cocoders/git-history-searcher/forks"
    "keys_url" => "https://api.github.com/repos/cocoders/git-history-searcher/keys{/key_id}"
    "collaborators_url" => "https://api.github.com/repos/cocoders/git-history-searcher/collaborators{/collaborator}"
    "teams_url" => "https://api.github.com/repos/cocoders/git-history-searcher/teams"
    "hooks_url" => "https://api.github.com/repos/cocoders/git-history-searcher/hooks"
    "issue_events_url" => "https://api.github.com/repos/cocoders/git-history-searcher/issues/events{/number}"
    "events_url" => "https://api.github.com/repos/cocoders/git-history-searcher/events"
    "assignees_url" => "https://api.github.com/repos/cocoders/git-history-searcher/assignees{/user}"
    "branches_url" => "https://api.github.com/repos/cocoders/git-history-searcher/branches{/branch}"
    "tags_url" => "https://api.github.com/repos/cocoders/git-history-searcher/tags"
    "blobs_url" => "https://api.github.com/repos/cocoders/git-history-searcher/git/blobs{/sha}"
    "git_tags_url" => "https://api.github.com/repos/cocoders/git-history-searcher/git/tags{/sha}"
    "git_refs_url" => "https://api.github.com/repos/cocoders/git-history-searcher/git/refs{/sha}"
    "trees_url" => "https://api.github.com/repos/cocoders/git-history-searcher/git/trees{/sha}"
    "statuses_url" => "https://api.github.com/repos/cocoders/git-history-searcher/statuses/{sha}"
    "languages_url" => "https://api.github.com/repos/cocoders/git-history-searcher/languages"
    "stargazers_url" => "https://api.github.com/repos/cocoders/git-history-searcher/stargazers"
    "contributors_url" => "https://api.github.com/repos/cocoders/git-history-searcher/contributors"
    "subscribers_url" => "https://api.github.com/repos/cocoders/git-history-searcher/subscribers"
    "subscription_url" => "https://api.github.com/repos/cocoders/git-history-searcher/subscription"
    "commits_url" => "https://api.github.com/repos/cocoders/git-history-searcher/commits{/sha}"
    "git_commits_url" => "https://api.github.com/repos/cocoders/git-history-searcher/git/commits{/sha}"
    "comments_url" => "https://api.github.com/repos/cocoders/git-history-searcher/comments{/number}"
    "issue_comment_url" => "https://api.github.com/repos/cocoders/git-history-searcher/issues/comments{/number}"
    "contents_url" => "https://api.github.com/repos/cocoders/git-history-searcher/contents/{+path}"
    "compare_url" => "https://api.github.com/repos/cocoders/git-history-searcher/compare/{base}...{head}"
    "merges_url" => "https://api.github.com/repos/cocoders/git-history-searcher/merges"
    "archive_url" => "https://api.github.com/repos/cocoders/git-history-searcher/{archive_format}{/ref}"
    "downloads_url" => "https://api.github.com/repos/cocoders/git-history-searcher/downloads"
    "issues_url" => "https://api.github.com/repos/cocoders/git-history-searcher/issues{/number}"
    "pulls_url" => "https://api.github.com/repos/cocoders/git-history-searcher/pulls{/number}"
    "milestones_url" => "https://api.github.com/repos/cocoders/git-history-searcher/milestones{/number}"
    "notifications_url" => "https://api.github.com/repos/cocoders/git-history-searcher/notifications{?since,all,participating}"
    "labels_url" => "https://api.github.com/repos/cocoders/git-history-searcher/labels{/name}"
    "releases_url" => "https://api.github.com/repos/cocoders/git-history-searcher/releases{/id}"
    "deployments_url" => "https://api.github.com/repos/cocoders/git-history-searcher/deployments"
    "created_at" => "2020-09-19T10:58:22Z"
    "updated_at" => "2021-02-08T09:31:59Z"
    "pushed_at" => "2021-02-08T09:31:59Z"
    "git_url" => "git://github.com/cocoders/git-history-searcher.git"
    "ssh_url" => "git@github.com:cocoders/git-history-searcher.git"
    "clone_url" => "https://github.com/cocoders/git-history-searcher.git"
    "svn_url" => "https://github.com/cocoders/git-history-searcher"
    "homepage" => null
    "size" => 224
    "stargazers_count" => 0
    "watchers_count" => 0
    "language" => "PHP"
    "has_issues" => true
    "has_projects" => true
    "has_downloads" => true
    "has_wiki" => true
    "has_pages" => false
    "forks_count" => 1
    "mirror_url" => null
    "archived" => false
    "disabled" => false
    "open_issues_count" => 0
    "license" => array:5 [
        "key" => "mit"
      "name" => "MIT License"
      "spdx_id" => "MIT"
      "url" => "https://api.github.com/licenses/mit"
      "node_id" => "MDc6TGljZW5zZTEz"
    ]
    "allow_forking" => true
    "is_template" => false
    "topics" => []
    "visibility" => "public"
    "forks" => 1
    "open_issues" => 0
    "watchers" => 0
    "default_branch" => "master"
    "permissions" => array:5 [
        "admin" => false
      "maintain" => false
      "push" => false
      "triage" => false
      "pull" => true
    ]
  ]


        foreach ($exampleArray as $repo) {
            dump($repo['name'], $repo['owner']['login']);


        }


//        dump($response->toArray());die;

//        $race = 'dachowiec';
//        $cat = new Cat('blue', $race);
//        $cat2 = new Cat('green', 'perski');
//        dump($cat);
//        dump($cat2);
//        $this->entityManager->persist($cat);
//        $this->entityManager->persist($cat2);
//        $this->entityManager->flush();



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

    protected function configure()
    {
        parent::configure(); // TODO: Change the autogenerated stub
        $this->addArgument('organizationName', InputArgument::REQUIRED, 'the organization name');
        $this->addArgument('providerName', InputArgument::REQUIRED, 'provider name');
    }


}