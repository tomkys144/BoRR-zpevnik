<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/skautis_manager.php';
if ($_SERVER["HTTP_HOST"] === 'localhost:8080') {
    $person = 'test';
} else {
    $data = $skautis->usr->UserDetail();
    $data = json_decode(json_encode($data), true);
    $dataPerson = $skautis->org->PersonDetail(array("ID" => $data['ID_Person']));
    $dataPerson = json_decode(json_encode($dataPerson), true);
    $person = $dataPerson['DisplayName'];
}
$files = scandir(__DIR__ . '/data/songs/');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fnumber = $_REQUEST['number'];
    $action = $_REQUEST['action'];
    $fname = $files[$fnumber];
    $songs = json_decode(file_get_contents(__DIR__ . '/data/usrs/' . $data['ID_Person'] . '.json'), true);
    if (empty($songs)) {
        $songs = array();
    }

    if ($action === 'add') {
        $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $fname));
        $song = array(
            'title' => $object->matter('title'),
            'author' => $object->matter('author'),
            'file' => $fname
        );
        $new = array($fname => $song);
        $songs = array_merge($songs, $new);
        ksort($songs);
        file_put_contents(__DIR__ . '/data/usrs/' . $data['ID_Person'] . '.json', json_encode($songs, JSON_FORCE_OBJECT), LOCK_EX);
        if ($_SERVER['HTTPS']) {
            header('Location: https://' . $_SERVER["HTTP_HOST"] . '/songs.php?number=' . $fnumber);
        } else {
            header('Location: http://' . $_SERVER["HTTP_HOST"] . '/songs.php?number=' . $fnumber);
        }
        exit();
    } elseif ($action === 'remove') {
        unset($songs[$fname]);
        ksort($songs);
        file_put_contents(__DIR__ . '/data/usrs/' . $data['ID_Person'] . '.json', json_encode($songs, JSON_FORCE_OBJECT), LOCK_EX);
        if ($_SERVER['HTTPS']) {
            header('Location: https://' . $_SERVER["HTTP_HOST"] . '/songs.php?number=' . $fnumber);
        } else {
            header('Location: http://' . $_SERVER["HTTP_HOST"] . '/songs.php?number=' . $fnumber);
        }
        exit();
    }
}
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-32">
        <title>BoRR zpěvník - <?php echo($person) ?> - oblíbené</title>
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
    </head>
<body>
<div>
    <a href="index.php"><button class="icon_home"></button></a>
    <a href="list.php"><button class="icon_list"></button></a>
    <a href="help.html"><button class="icon_help"></button></a>
    <div class="icon_user">
        <button class="icon_user-btn"></button>
        <div class="icon_user-content">
            <?php
            $skautisUser = $skautis->getUser();
            if ($skautisUser->isLoggedIn(true) || $_SERVER["HTTP_HOST"] === 'localhost:8080') {
                echo (
                    '<a href="favourite_songs.php"><button type="button" class="icon_user-included">Oblíbené</button></a><br>
                    <a href="editor.php"><button type="button" class="icon_user-included">Editor</button></a><br>
                    <form method="get" action="skautis_manager.php">
                    <input type="hidden" name="logout" value="http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '">
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
<div style="position: absolute; width: 64vw; left: 18vw;top: 0">
    <h1>Oblíbené písničky</h1>
    <h2><?php echo($person) ?></h2>
</div>
<?php
if (file_exists(__DIR__ . '/data/usrs/' . $data['ID_Person'] . '.json')) {
    $fileContents = json_decode(file_get_contents(__DIR__ . '/data/usrs/' . $data['ID_Person'] . '.json'), true);
    $songFiles = array_keys($fileContents);
    if (!empty($songFiles)) {
        echo('<div style="width: 30vw; left: 50%; top: 17em; transform: translate(1vw, 0); position: absolute; text-align: left; margin: 0">');
        foreach ($fileContents as $songfile) {
            $fnumber = array_search($songfile['file'], $files);
            $name = str_replace(' ', '&nbsp;', $songfile['author']);
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
        }
        echo(
        '<br>
            </div>
            <div style="width: 30vw; left: 50%; top: 17em; transform: translate(-31vw, 0); position: absolute; text-align: right; margin: 0">'
        );
        foreach ($fileContents as $songfile) {
            $fnumber = array_search($songfile['file'], $files);
            $name = str_replace(' ', '&nbsp;', $songfile['title']);
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
        }
        echo(
        '<br>
            </div>
            <div style="width: 2vw; left: 50%; top: 17em; transform: translate(-1vw, 0); position: absolute; text-align: center; margin: 0">'
        );
        foreach ($fileContents as $songfile) {
            $fnumber = array_search($songfile['file'], $files);
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: 2vw" id="list" formaction="songs.php" type="submit">&nbsp;-&nbsp;</button></form>');
        }
        echo('<br>');
    } else {
        echo(
        '<div style="width: 30vw; left: 50%; top: 17em; transform: translate(-15vw, 0); position: absolute; text-align: center; margin: 0">Seznam oblíbených písní je prázdný</div>'
        );
    }
} else {
    $file = fopen(__DIR__ . '/data/usrs/' . $data['ID_Person'] . '.json', 'w');
    $songs = array();
    fwrite($file, json_encode($songs, JSON_FORCE_OBJECT));
    fclose($file);
    echo(
    '<div style="width: 30vw; left: 50%; top: 17em; transform: translate(-15vw, 0); position: absolute; text-align: center; margin: 0">Seznam oblíbených písní je prázdný</div>'
    );
}