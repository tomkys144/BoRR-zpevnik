<?php

namespace App\Controller;

use App\Service\SkautisService;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property SkautisService skautisService
 */
class AccountController extends AbstractController
{
    public function __construct(SkautisService $skautisService)
    {
        $this->skautisService = $skautisService;
    }

    public function navigation(): Response
    {
        if (!$this->skautisService->loginChecker()){
            $url = $this->skautisService->getLoginURL(UrlGeneratorInterface::ABSOLUTE_URL);
            return $this->render('partials/navigationAccount.html.twig', [
                'login' => false,
                'url' => $url
            ]);
        } else {
            $url = $this->skautisService->getLogoutURL();
            $info = $this->skautisService->getUserInfo();
            $permissions = $this->skautisService->permissionsChecker($info['ID']);
            return $this->render('partials/navigationAccount.html.twig', [
                'login' => true,
                'url' => $url,
                'info' => $info,
                'isMaintainer' => $permissions['Maintainer'],
                'isAdmin' => $permissions['Admin']
            ]);
        }
    }
}