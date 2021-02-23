<?php


namespace App\Service;


use Doctrine\ORM\EntityManager;
use Skautis\HelperTrait;
use Skautis\Skautis;

/**
 * @property EntityManager entityManager
 * @property HelperTrait|Skautis skautis
 */
class SkautisService
{
    /**
     * SkautisService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->skautis = Skautis::getInstance($_ENV['SKAUTIS_APPID'], $_ENV['SKAUTIS_TEST']);
    }

    /**
     * @param string|null $backlink
     * @return string
     */
    public function getLoginURL(?string $backlink): string
    {
        if ($backlink) {
            return $this->skautis->getLoginUrl($backlink);
        }
        return $this->skautis->getLoginUrl();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function setLoginData(array $data): bool
    {
        $this->skautis->setLoginData($data);
        return true;
    }

    /**
     * @return string
     */
    public function getLogoutURL(): string
    {
        return $this->skautis->getLogoutUrl();
    }

    /**
     * @return bool
     */
    public function loginChecker(): bool
    {
        return $this->skautis->getUser()->isLoggedIn(true);
    }
}