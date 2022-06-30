<?php

declare(strict_types=1);

namespace App\Controller;

use App\CodeRepositoryProviders\FetchCriteria;
use App\Entity\Criteria;
use App\Repository\CodeRepoRepository;
use App\SynchronizeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepoController extends AbstractController
{
    public function __construct(
        private readonly CodeRepoRepository $codeRepoRepository,
        private readonly SynchronizeRepository $synchronizeRepository
    )
    {}

    #[Route('/repo', name: 'repo')]
    public function index(Request $request): Response
    {
        $body = $request->query;


        $criteria = new Criteria(
            (int) $body->get('page'),
            (int) $body->get('perPage'),
            (string) $body->get('sortingMethod'),
            (string) $body->get('columnName')
        );

        $criteria->setSearchValue($body->get('searchValue'));
        $repositories = $this->codeRepoRepository->findByCriteria($criteria);

        return $this->render('repo/index.html.twig', [
            'repositories' => $repositories['items'] ?? [],
        ]);
    }

    /**
     * @Route("/getrepodata", name="get_repo_data", methods={"GET"})
     */
    public function getRepoData(Request $request): Response
    {
        $body = $request->query;


        $criteria = new Criteria(
            (int) $body->get('page'),
            (int) $body->get('perPage'),
            (string) $body->get('sortingMethod'),
            (string) $body->get('columnName')
        );

        $criteria->setSearchValue($body->get('searchValue'));
//        $criteria->setColumnName($body->get('columnName'));


        return $this->json($this->codeRepoRepository->findByCriteria($criteria));
    }

    #[Route('/sync', name: 'repo_sync', methods: ['POST'])]
    public function synchronizeRepository(Request $request): Response
    {
        $this->synchronizeRepository->execute(new FetchCriteria(
            $request->request->get('organizationName'),
            $request->request->get('providerName'),
            $request->request->get('accessKey')
        ));

        return $this->json(['message' => 'Ok'], 200);
    }
}
