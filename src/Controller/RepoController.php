<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CodeRepo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepoController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/repo', name: 'repo')]
    public function index(): Response
    {
        $repositories = $this->entityManager->getRepository(CodeRepo::class)->findAll();

        return $this->render('repo/index.html.twig', [
            'repositories' => $repositories,
        ]);
    }
}
