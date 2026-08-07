<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return new Response('MOA is running.');
    }
}
