<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/skautis_manager.php';
$files = scandir(__DIR__ . '/data/songs/');

$skautisUser = $skautis->getUser();
if ($skautisUser->isLoggedIn(true)){
    $ID = $skautisUser->getLoginId();
    $params = ['ID' => $ID];
    $logoutTime = json_decode(json_encode($skautis->UserManagement->loginUpdateRefresh($params)), true);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $song = $_REQUEST['number'];
    if ($song <= 1) {
        header("Location: list.php");
        exit();
    }
    else {
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $files[$song]));
    $next_song = $song + 1;
    $previous_song = $song - 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-32">
    <title>BoRR zpěvník - <?php echo($object->matter("title")) ?></title>
    <link rel="icon" href="data/imgs/borr.png">
    <link rel="stylesheet" href="css.css">
    <script>
        let host = window.location.hostname;
        if (host !== "localhost") {
            let prot = window.location.protocol;
            if (prot === "http:") {
                window.location.href = window.location.href.replace("http://", "https://");
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
                document.getElementById('left').submit();
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
<div style="position: absolute; top: 0; z-index: 1">
    <a href="index.php"><button class="icon_home"></button></a>
    <a href="list.php"><button class="icon_list"></button></a>
    <a href="help.html"><button class="icon_help"></button></a>
    <div class="icon_user">
        <button class="icon_user-btn"></button>
        <div class="icon_user-content">
            <?php
            $skautisUser = $skautis->getUser();
            if ($skautisUser->isLoggedIn(true) || $_SERVER["HTTP_HOST"] === 'localhost:8080') {
                $now = DateTime::createFromFormat("Y-m-d H:i:s.v", date("Y-m-d H:i:s.v"));
                $logoutTime=str_replace('T', ' ', $logoutTime['DateLogout']);
                $logout = new DateTime($logoutTime);
                $interval = date_diff($now, $logout);
                $time = $interval->i*60 + $interval->s;
                if ($_SERVER["HTTP_HOST"] === 'localhost:8080') {
                    $time = 1800;
                }
                echo (
                    '<a href="favourite_songs.php"><button type="button" class="icon_user-included">Oblíbené</button></a><br>
                    <a href="editor.php"><button type="button" class="icon_user-included">Editor</button></a><br>');
                if (isAdmin() === true) {
                    echo ('<a href="admin.php"><button type="button" class="icon_user-included">Admin rozhraní</button></a><br>');
                }
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
                    
                        progress(' . $time . ', 1800, $("#logoutTimer"));
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
    <?php
    $skautisUser = $skautis->getUser();
    if ($skautisUser->isLoggedIn(true)) {
        $data = $skautis->usr->UserDetail();
        $data = json_decode(json_encode($data), true);
        $person = $data['ID_Person'];
        $favSongs = json_decode(file_get_contents(__DIR__ . '/data/usrs/' . $person . '.json'), true);

        if (!in_array($files[$song], array_keys($favSongs))) {
            echo(
                '<form action="favourite_songs.php" method="post" style="display: inline-block; width: max-content">
                        <input type="hidden" name="number" value="' . $song . '">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="icon_fav_not"></button>
                    </form>'
            );
        } else {
            echo(
                '<form action="favourite_songs.php" method="post" style="display: inline-block; width: max-content">
                        <input type="hidden" name="number" value="' . $song . '">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="icon_fav"></button>
                    </form>'
            );
        }
    } elseif ($_SERVER["HTTP_HOST"] === 'localhost:8080') {
        $person = 'test';
        $favSongs = json_decode(file_get_contents(__DIR__ . '/data/usrs/' . $person . '.json'), true);

        if (!in_array($files[$song], array_keys($favSongs))) {
            echo(
                '<form action="favourite_songs.php" method="post" style="display: inline-block; width: max-content">
                        <input type="hidden" name="number" value="' . $song . '">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="icon_fav_not"></button>
                    </form>'
            );
        } else {
            echo(
                '<form action="favourite_songs.php" method="post" style="display: inline-block; width: max-content">
                        <input type="hidden" name="number" value="' . $song . '">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="icon_fav"></button>
                    </form>'
            );
        }
    }
    ?>
</div>
<div style="position: absolute; width: 64vw; left: 18vw;top: 0; height: 100%">
    <h1><?php echo($object->matter('title')) ?></h1>
    <h2><?php echo($object->matter('author')) ?></h2>
    <div class="song_body">
        <p id="song_text"><?php echo($object->body()) ?></p>
        <div style="bottom: 0; left: -20vw; margin: 20px; position: relative">
            <p>
            <?php
            if (strpos($object->matter('made'), '{male}') !== false) {
                $made = str_replace(' {male}', '', $object->matter('made'));
                echo ('Zpracoval: ' . $made);
            } elseif (strpos($object->matter('made'), '{female}') !== false) {
                $made = str_replace(' {female}', '', $object->matter('made'));
                echo ('Zpracovala: ' . $made);
            } else {
                $made = $object->matter('made');
                echo('Zpracoval(a): ' . $made);
            }
            if ($object->matter('revision') != null && sizeof($object->matter('revision')) != 1) {
                echo('</p><p>Upravil');
                $start = $object->matter('revision')[0];
                $onlyFemales = true;
                foreach ($object->matter('revision') as $maker) {
                    $makers = '';
                    if (strpos($maker, '{male}') !== false) {
                        $onlyFemales = false;
                    }
                    $maker = str_replace(' {female}', '', $maker);
                    $maker = str_replace(' {male}', '', $maker);
                    if ($start != $maker) {
                        $makers .= ', ' . $maker;
                    } else {
                        $makers .= $maker;
                    }
                }
                if ($onlyFemales) {
                    echo ('y: ' . $makers);
                } else {
                    echo ('i: ' . $makers);
                }
            } elseif (($object->matter('revision') != null && sizeof($object->matter('revision')) == 1)) {
                if (strpos($object->matter('revision')[0], '{male}') !== false) {
                    $revision = str_replace(' {male}', '', $object->matter('revision')[0]);
                    echo('</p><p>Upravil: ' . $revision);
                } elseif (strpos($object->matter('revision')[0], '{female}') !== false) {
                    $revision = str_replace(' {female}', '', $object->matter('revision')[0]);
                    echo('</p><p>Upravila: ' . $revision);
                } else {
                    echo('</p><p>Upravil(a): ' . $object->matter('revision')[0]);
                }
            }
            ?></p>
    </div>
</div>
<?php
        if ($object->matter('capo') != null) {
            echo('<div class="capo">Capo ' . $object->matter('capo') . '</div>');
        }
?>
<div>
    <form method="get" id="left"><input type="hidden" name="number" value="<?php echo($previous_song) ?>">
        <button id="left_button" type="submit">&lt;</button>
    </form>
</div>
<?php
        if ($song != count($files) - 1) {
            echo(
                '<div><form method="get" id="right"><input type="hidden" name="number" value="' . $next_song . '"><button id="right_button" type="submit">&gt;</button></form></div>'
            );
        }
    }
}
?>
</div>
</body>
</html>