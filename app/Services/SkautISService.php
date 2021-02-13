<?php


namespace App\Services;


use DateTime;
use JetBrains\PhpStorm\Pure;
use Skautis\Skautis;


class SkautISService
{
    /**
     * @var Skautis|\Skautis\HelperTrait|null
     */
    private Skautis|\Skautis\HelperTrait|null $skautis = null;


    public function __construct()
    {
        $appid = $_ENV['SKAUTIS_APPID'];
        $test = $_ENV['SKAUTIS_TEST'];

        $this->skautis = Skautis::getInstance($appid, $test);
    }

    /**
     * @param string $backlink
     * @return string
     */
    public function login(string $backlink): string
    {
        return $this->skautis->getLoginUrl($backlink);
    }

    /**
     * @param array $data
     */
    public function finishLogin(array $data)
    {
        $this->skautis->setLoginData($data);
    }

    /**
     * @return string
     */
    public function logout(): string
    {
        return $this->skautis->getLogoutUrl();

    }

    /**
     * @param bool $wantID
     * @param bool $wantMemberships
     * @param bool $wantRole
     * @return array
     */
    public function getUserInfo(bool $wantID = false, bool $wantMemberships = false, bool $wantRole = false): array
    {
        $user = $this->skautis->UserManagement->UserDetail();
        $data = array();
        if ($wantID) {
            $data['ID_Person'] = $user->ID_Person;
        }
        if ($wantMemberships) {
            $UnitIDs = array();
            $memberships = $this->skautis->OrganizationUnit->MembershipAllPerson([
                'ID_Person' => $user->ID_Person,
                'ShowHistory' => false,
                'isValid' => true
            ]);
            foreach ($memberships as $membership){
                $UnitIDs[] = $membership['RegistrationNumber'];
            }
            $data['Memberships'] = $UnitIDs;
        }
        if ($wantRole)
        {
            $RoleIDs = array();
            $roles = $this->skautis->UserManagement->UserRoleAll(array('ID_User' => $user->ID));
            foreach ($roles as $role) {
                $RoleIDs[] = $role['ID_Role'];
            }
            $data['Roles'] = $RoleIDs;
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        if ($this->skautis->getUser()->isLoggedIn(true)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @return DateTime|null
     */
    public function logoutTime(): ?DateTime
    {
        return $this->skautis->getUser()->getLogoutDate();
    }


}