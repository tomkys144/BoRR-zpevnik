<?php
session_start();
require __DIR__ . '/skautis_manager.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['skautis_response'] = $_POST;
    login_finish();
    if (!$_SESSION['backlink']) {
        $_SESSION['backlink'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    header('Location: ' . $_SESSION['backlink']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník</title>
    <link rel="icon" href="data/borr.png">
    <link rel="stylesheet" href="css.css">
    <script>
        let host = window.location.hostname;
        if (host !== 'localhost') {
            let prot = window.location.protocol;
            if (prot === "http:") {
                window.location.href = window.location.href.replace('http://', 'https://');
            }
        }
    </script>
</head>
<body>
<script>
    document.addEventListener('keydown', (e) => {
        switch (e.key) {
            case 'ArrowRight':
                document.getElementById('right').click();
                break;
            default:
                return;
        }
        e.preventDefault();
    })
</script>
<div>
    <h1>BoRR</h1>
    <h1>zpěvník</h1>
</div>
<div>
    <a href="list.php" id="right">
        <button id="right_button" type="button">&gt;</button>
    </a>
</div>
<div style="position: fixed; bottom: 0; text-align: center; width: 100%">
    <!--- Tento zpěvník byl psán s myšlenkou na Valču a nyní je jí i věnován. --->
    <a href="https://github.com/tomkys144/BoRR-zpevnik">
        <button style="background-color: transparent; border: none; font-size: 1em">napsal Tomáš Kysela - Kyslík
        </button>
    </a><!--- A velkou pomoc poskytl Vojta Káně --->
    <p style="font-size: 1em">&#169;2020 BoRR</p>
</div>
</body>
</html>
