<?php


namespace App\Controller;


use App\Service\SongService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


class PageController extends AbstractController
{
    public function home()
    {
        return $this->render('home.html.twig', [
            'right' => '/list'
        ]);
    }

    public function list(Request $request)
    {
        $sortBy = $request->query->get('sortBy');

        return $this->render('list.html.twig', [
            'left' => '/',
            'right' => '/song/$first',
            'songs' => $songs
    ]);
    }
}