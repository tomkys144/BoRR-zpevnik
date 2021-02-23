<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


class PageController extends AbstractController
{
    public function home(): Response
    {
        return $this->render('home.html.twig', [
            'right' => '/list'
        ]);
    }

    /**
     * @return Response
     */
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }
}