<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class RepoController extends AbstractController
{
    #[Route('/repo', name: 'repo')]
    public function index(): Response
    {
        return $this->render('repo/index.html.twig', [
            'controller_name' => 'RepoController',
        ]);
    }
}
