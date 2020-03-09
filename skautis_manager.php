<?php
session_start();
require __DIR__. '/vendor/autoload.php';

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

function logout() {
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

if (isset($_GET['logout'])) {
    $_SESSION['backlink'] = $_REQUEST['logout'];
    logout();
}