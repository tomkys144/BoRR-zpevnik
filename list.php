<?php
require_once __DIR__.'/vendor/autoload.php';
$files = scandir(__DIR__.'/songs/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník - Rejstřík</title>
    <link rel="icon" href="data/borr.png">
    <link rel="stylesheet" href="css.css">
    <script>
        var host = window.location.hostname;
        if (host !== 'localhost') {
            var prot = window.location.protocol;
            if (prot === "http:") {
                window.location.href = window.location.href.replace('http://','https://');
            }
        }
    </script>
</head>
<body>
    <div>
        <a href="index.html"><button class="icon_home"></button></a>
        <a href="help.html"><button class="icon_help"></button></a>
        <a href="https://open.spotify.com/playlist/5hdTuzLBp0KlodbN8ghRng?si=QAGn797uSoOU2vMZko5qYQ"><button class="icon_music"></button></a>
    </div>
    <div style="position: absolute; width: 64vw; left: 18vw;top: 0">
        <h1>
            Rejstřík
        </h1>
    </div>
    <div style="width: 30vw; left: 50%; top: 12em; transform: translate(-31vw, 0); position: absolute; text-align: right; margin: 0">
        <?php
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $fnumber = array_search($f, $files);
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $f));
            $name = str_replace(' ', '&nbsp;', $object->matter('title'));
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
        }
        echo ('<br>')
        ?>
    </div>
    <div style="width: 30vw; left: 50%; top: 12em; transform: translate(1vw, 0); position: absolute; text-align: left; margin: 0">
        <?php
        require_once __DIR__.'/vendor/autoload.php';
        $files = scandir(__DIR__.'/songs/');
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $fnumber = array_search($f, $files);
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $f));
            $name = str_replace(' ', '&nbsp;', $object->matter('author'));
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
        }
        echo ('<br>')
        ?>
    </div>
    <div style="width: 2vw; left: 50%; top: 12em; transform: translate(-1vw, 0); position: absolute; text-align: center; margin: 0">
        <?php
        require_once __DIR__.'/vendor/autoload.php';
        $files = scandir(__DIR__.'/songs/');
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $fnumber = array_search($f, $files);
            echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: 2vw" id="list" formaction="songs.php" type="submit">&nbsp;-&nbsp;</button></form>');
        }
        echo ('<br>')
        ?>
    </div>
    <div>
        <a href="index.html"><button id="left_button" type="button">&lt;</button></a>
    </div>
    <div>
        <form method="get"><input type="hidden" name="number" value="2"><button formaction="songs.php" id="right_button" type="jen velmi rychle skončím jako submit">&gt;</button></form>
    </div>
</body>
</html>
