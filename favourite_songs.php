<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/skautis_manager.php';
$data = $skautis->usr->UserDetail();
$data = json_decode(json_encode($data), true);
$person = $data['ID_Person'];
$files = scandir(__DIR__ . '/songs/');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fnumber = $_REQUEST['number'];
    $action = $_REQUEST['action'];
    $fname = $files[$fnumber];
    $songs = array();
    $songs = json_decode(file_get_contents(__DIR__ . '/data/usrs/' . $person . '.json'));

    if ($action === 'add') {
        array_push($songs, $fname);
        sort($songs);
        file_put_contents(__DIR__ . '/data/usrs/' . $person . '.json', json_encode($songs, JSON_FORCE_OBJECT), LOCK_EX);
        echo($songs);
        echo ('add');
        //header('Location: https://' . $_SERVER["HTTP_HOST"] . '/songs.php?number=' . $fnumber);
        //exit();
    } elseif ($action === 'remove') {
        $key = array_search($fname, $songs);
        unset($songs[$key]);
        sort($songs);
        file_put_contents(__DIR__ . '/data/usrs/' . $person . '.json', json_encode($songs, JSON_FORCE_OBJECT), LOCK_EX);
        echo($songs);
        echo ('remove');
        //header('Location: https://' . $_SERVER["HTTP_HOST"] . '/songs.php?number=' . $fnumber);
        //exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-32">
    <title>BoRR zpěvník - <?php echo($data['Person']) ?> - oblíbené</title>
    <link rel="icon" href="data/borr.png">
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
<h1>Oblíbené písničky</h1>
<h2><?php echo($data['Person']) ?></h2>
<?php
if (file_exists(__DIR__ . '/data/usrs/' . $person . '.json')) {
    $fileContents = json_decode(file_get_contents(__DIR__ . '/data/usrs/' . $person . '.json'));
    $songFiles = $fileContents['songs'];
    if (!empty($songFiles)) {
        echo('div style="width: 30vw; left: 50%; top: 12em; transform: translate(1vw, 0); position: absolute; text-align: left; margin: 0"');
        foreach ($songFiles as $songfile) {
            $fnumber = array_search($songfile, $files);
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $songfile));
            $name = str_replace(' ', '&nbsp;', $object->matter('author'));
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
        }
        echo(
            '<br>
            </div>
            <div style="width: 30vw; left: 50%; top: 12em; transform: translate(-31vw, 0); position: absolute; text-align: right; margin: 0">'
        );
        foreach ($songFiles as $songfile) {
            $fnumber = array_search($songfile, $files);
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $songfile));
            $name = str_replace(' ', '&nbsp;', $object->matter('title'));
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
        }
        echo(
            '<br>
            </div>
            <div style="width: 2vw; left: 50%; top: 12em; transform: translate(-1vw, 0); position: absolute; text-align: center; margin: 0">'
        );
        foreach ($songFiles as $songfile) {
            $fnumber = array_search($songfile, $files);
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: 2vw" id="list" formaction="songs.php" type="submit">&nbsp;-&nbsp;</button></form>');
        }
        echo('<br>');
    } else {
        echo(
            '<div style="width: 30vw; left: 50%; top: 12em; transform: translate(-15vw, 0); position: absolute; text-align: center; margin: 0">Seznam oblíbených písní je prázdný</div>'
        );
    }
} else {
    $file = fopen(__DIR__ . '/data/usrs/' . $person . '.json', 'w');
    $songs = array();
    fwrite($file, json_encode($songs, JSON_FORCE_OBJECT));
    fclose($file);
    echo(
        '<div style="width: 30vw; left: 50%; top: 12em; transform: translate(-15vw, 0); position: absolute; text-align: center; margin: 0">Seznam oblíbených písní je prázdný</div>'
    );
}