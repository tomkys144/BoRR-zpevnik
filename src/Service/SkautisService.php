<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Skautis\HelperTrait;
use Skautis\Skautis;


/**
 * @property EntityManagerInterface entityManager
 * @property HelperTrait|Skautis skautis
 */
class SkautisService
{
    public function __construct(EntityManagerInterface $entityManager)
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
        if (str_contains($_SERVER['HTTP_HOST'], 'localhost')) {
            return true;
        } else {
            return $this->skautis->getUser()->isLoggedIn(true);
        }
    }

    /**
     * @return array
     */
    public function getUserInfo(): array
    {
        if (!$this->loginChecker()) {
            return array();
        } elseif (str_contains($_SERVER['HTTP_HOST'], 'localhost')) {
            return array('ID' => 'dev', 'Name' => 'Developer', 'Sex' => 'male');
        }
        $data = $this->skautis->usr->userDetail();
        $info = $this->skautis->org->PersonDetail(array("ID" => $data->ID_Person));
        return array('ID' => $data->ID_Person, 'Name' => $info->DisplayName, 'Sex' => $info->ID_Sex);
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        $data = json_decode(json_encode($this->skautis->usr->UserRoleAll), true);
        $result = array();
        foreach ($data as $role) {
            $result[] = array('ID' => $role['ID_Role'], 'Unit_Number' => $role['RegistrationNumber']);
        }
        return $result;
    }

    /**
     * @param int|string $ID
     * @return bool[]
     */
    #[ArrayShape(['Admin' => "false|mixed", 'Maintainer' => "false|mixed"])] public function permissionsChecker(int|string $ID): array
    {
        $rolesOther = array(31, 132, 63, 74, 47, 125, 91, 117, 41, 29, 44);
        $rolesAdmin = array(22, 20, 18);
        $result = array('Admin' => false, 'Maintainer' => false);
        if ($ID == 'dev') {
            $result['Admin'] = true;
            $result['Maintainer'] = true;
            return $result;
        }
        if (in_array($ID, array('42486', '43149', '43296'))) {
            $result['Admin'] = true;
            $result['Maintainer'] = true;
            return $result;
        }
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            if (in_array($role['ID'], $rolesAdmin) && str_contains('219.09', $role['Unit_Number'])) {
                $result['Admin'] = true;
                $result['Maintainer'] = true;
                break;
            } elseif (in_array($role['ID'], $rolesOther) && str_contains('219.09', $role['Unit_Number'])) {
                $result['Maintainer'] = true;
            }
        }
        return $result;
    }
}