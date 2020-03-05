<?php
session_start();
require __DIR__. '/vendor/autoload.php';

$applicationID = "mojeID";
$isTestMode = false;
$skautis = \Skautis\Skautis::getInstance($applicationID, $isTestMode);

function login()
{
    global $skautis;
    $backLink = 'https://zpevnik-borr.skauting.cz/skautis_manager.php';
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    global $skautis;
    $skautis->setLoginData($_REQUEST);
    header('Location: ' . $_SESSION['backlink']);
    exit();
}

elseif (isset($_GET['logout'])) {
    logout();
}