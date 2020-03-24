<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

$applicationID = "6116cad6-ddbc-4380-807a-d9525cc35b95";
$isTestMode = false;
$skautis = \Skautis\Skautis::getInstance($applicationID, $isTestMode);

function login()
{
    global $skautis;
    $backLink = "https://zpevnik-borr.skauting.cz/index.php";
    $loginUrl = $skautis->getLoginUrl($backLink);
    header('Location: ' . $loginUrl);
    exit();
}

function logout()
{
    global $skautis;
    $logoutUrl = $skautis->getLogoutUrl();
    header('Location: ' . $logoutUrl);
    exit();
}

function login_finish()
{
    global $skautis;
    $skautis->setLoginData($_SESSION['skautis_response']);
}

function isAdmin()
{
    $admins = ['42486', '43149', '43296'];
    global $skautis;
    if ($_SERVER["HTTP_HOST"] === 'localhost:8080') {
        return true;
    } else {
        $data = $skautis->UserManagement->UserDetail();
        if (in_array($data->ID_Person, $admins)) {
            return true;
        } else {
            return false;
        }
    }
}

if (isset($_GET['logout'])) {
    $_SESSION['backlink'] = $_REQUEST['logout'];
    logout();
}