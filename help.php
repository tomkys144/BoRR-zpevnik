<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/skautis_manager.php';
$sections = scandir(__DIR__ . '/data/help');
$files = [];
foreach ($sections as $section) {
    if ($section === '.' || $section ==='..') {
        continue;
    } else if (strpos($section, '.md') !== false) {
        $files[$section] = $section;
    } else {
        $files[$section] = scandir(__DIR__ . '/data/help/' . $section . '/');
    }
}
$sections = array_keys($files);

$skautisUser = $skautis->getUser();
if ($skautisUser->isLoggedIn(true)){
    $ID = $skautisUser->getLoginId();
    $params = ['ID' => $ID];
    $logoutTime = json_decode(json_encode($skautis->UserManagement->loginUpdateRefresh($params)), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník - Nápověda</title>
    <link rel="icon" href="data/imgs/borr.png">
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
    <script src="data/libs/jquery-3.4.1.min.js"></script>
</head>
<body>
<script>
    document.addEventListener('keydown', (e) => {
        switch (e.key) {
            case 'ArrowLeft':
                document.getElementById('left').click();
                break;
            case 'ArrowRight':
                document.getElementById('right').submit();
                break;
            default:
                return;
        }
        e.preventDefault();
    })
</script>
<div>
    <a href="index.php"><button class="icon_home"></button></a>
    <div class="icon_user">
        <button class="icon_user-btn"></button>
        <div class="icon_user-content">
            <?php
            $skautisUser = $skautis->getUser();
            if ($skautisUser->isLoggedIn(true)) {
                $now = DateTime::createFromFormat("Y-m-d H:i:s.v", date("Y-m-d H:i:s.v"));
                $logoutTime=str_replace('T', ' ', $logoutTime['DateLogout']);
                $logout = new DateTime($logoutTime);
                $interval = date_diff($now, $logout);
                $time = $interval->i*60 + $interval->s;

                echo (
                    '<button class="icon_user-included">Čas do odhlášení:</button>
                    <div id="logoutTimer">
                        <div id="logoutTimerBar">
                        </div>
                    </div>
                    <script>
                        function progress(timeleft, timetotal, $element) {
                            var progressBarWidth = timeleft * $element.width() / timetotal;
                            $element.find("div").animate({ width: progressBarWidth }, 500).html(Math.floor(timeleft/60) + ":"+ timeleft%60);
                            if(timeleft > 0) {
                                setTimeout(function() {
                                    progress(timeleft - 1, timetotal, $element);
                                }, 1000);
                            } else if (timeleft <= 0) {
                                location.reload();
                            }
                        };
                    
                        progress' . $time . ', 1800, $("#logoutTimer"));
                    </script >
                    <form method="get" action="skautis_manager.php">
                    <input type="hidden" name="logout" value="https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '">
                    <input class="icon_user-included" type="submit" value="Odhlásit se">
                    </form>'
                );
            }
            else {
                $_SESSION['backlink'] = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                echo (
                        '<a href="' . $skautis->getLoginUrl("https://zpevnik-borr.skauting.cz/index.php") . '"><button class="icon_user-included" type="button">Přihlásit se</button></a>'
                );
            }
            ?>
        </div>
    </div>
</div>
<div style="position: absolute; width: 64vw; left: 18vw;top: 0; height: 100%">
    <h1>Nápověda</h1>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_REQUEST['section'])) {
        echo (
            '<div style="width: max-content; position: absolute; left: 50vw; transform: translate(-50%); top: 10em">
            <ul>'
        );
        $x=0;
        foreach ($files as $firstLevel) {
            if (is_string($firstLevel) && strpos($firstLevel, '.md') !== false) {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/help/' . $sections[$x]));
                echo (
                    '<li><form method="get"><input type="hidden" name="section" value="' . $sections[$x] . '"><input type="hidden" name="subsection" value="' . $sections[$x] . '"><button type="submit" style="width: max-content" id="help">' . $object->matter('name') . '</button></form></li>'
                );
                $x++;
            } else {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/help/' . $sections[$x] . '/directoryconfig.md'));
                echo '<li><form method="get"><input type="hidden" name="section" value="' . $sections[$x] . '"><button type="submit" style="width: max-content" id="help">' . $object->matter('name') . '</button></form></li><ul>';
                foreach ($firstLevel as $secondLevel) {
                    if ($secondLevel === '.' || $secondLevel === '..' || $secondLevel === 'directoryconfig.md') {
                        continue;
                    } else {
                        $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/help/' . $sections[$x] . '/' . $secondLevel));
                        echo '<li><form method="get"><input type="hidden" name="section" value="' . $sections[$x] . '"><input type="hidden" name="subsection" value="' . $secondLevel . '"><button type="submit" style="width: max-content" id="help">' . $object->matter('name') . '</button></form></li>';
                    }
                }
                echo '</ul>';
                $x++;
            }
        }
        echo '</ul></div>';
    } elseif (!isset($_REQUEST['subsection'])) {
        echo (
        '<div style="width: max-content; position: absolute; left: 50vw; transform: translate(-50%); top: 10em">
            <ul>'
        );
        foreach ($files[$_REQUEST['section']] as $secondLevel) {
            if ($secondLevel === '.' || $secondLevel === '..' || $secondLevel === 'directoryconfig.md') {
                continue;
            } else {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/help/' . $_REQUEST['section'] . '/' . $secondLevel));
                echo (
                    '<li><form method="get"><input type="hidden" name="section" value="' . $_REQUEST['section'] . '"><input type="hidden" name="subsection" value="' . $secondLevel . '"><button type="submit" style="width: max-content" id="help">' . $object->matter('name') . '</button></form></li>'
                );
            }
        }
        echo '</ul></div>';
    } elseif (isset($_REQUEST['subsection']) && isset($_REQUEST['section'])) {
        if ($_REQUEST['subsection'] !== $_REQUEST['section']) {
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parseFile(__DIR__ . '/data/help/' . $_REQUEST['section'] . '/' . $_REQUEST['subsection']);
            echo '<div style="width: max-content; position: absolute; left: 50vw; transform: translate(-50%); top: 10em"><h2>' . $object->matter('name'). '</h2>' . $object->body() . '</div>';
        } elseif ($_REQUEST['subsection'] === $_REQUEST['section']) {
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parseFile(__DIR__ . '/data/help/' . $_REQUEST['section']);
            echo '<div style="width: max-content; position: absolute; left: 50vw; transform: translate(-50%); top: 10em"><h2>' . $object->matter('name'). '</h2>' . $object->body() . '</div>';
        }
    }
}