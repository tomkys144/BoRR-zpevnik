<?php
require_once __DIR__ . '/vendor/autoload.php';
$files = scandir(__DIR__ . '/songs/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník - Rejstřík</title>
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
    <a href="index.html">
        <button class="icon_home"></button>
    </a>
    <a href="help.html">
        <button class="icon_help"></button>
    </a>
    <a href="https://open.spotify.com/playlist/5hdTuzLBp0KlodbN8ghRng?si=QAGn797uSoOU2vMZko5qYQ">
        <button class="icon_music"></button>
    </a>
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
        $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $f));
        $name = str_replace(' ', '&nbsp;', $object->matter('title'));
        echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
    }
    echo('<br>')
    ?>
</div>
<div style="width: 30vw; left: 50%; top: 12em; transform: translate(1vw, 0); position: absolute; text-align: left; margin: 0">
    <?php
    require_once __DIR__ . '/vendor/autoload.php';
    $files = scandir(__DIR__ . '/songs/');
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        $fnumber = array_search($f, $files);
        $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $f));
        $name = str_replace(' ', '&nbsp;', $object->matter('author'));
        echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: max-content" id="list" formaction="songs.php" type="submit">' . $name . '</button></form>');
    }
    echo('<br>')
    ?>
</div>
<div style="width: 2vw; left: 50%; top: 12em; transform: translate(-1vw, 0); position: absolute; text-align: center; margin: 0">
    <?php
    require_once __DIR__ . '/vendor/autoload.php';
    $files = scandir(__DIR__ . '/songs/');
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        $fnumber = array_search($f, $files);
        echo('<form><input type="hidden" name="number" value="' . $fnumber . '"><button style="width: 2vw" id="list" formaction="songs.php" type="submit">&nbsp;-&nbsp;</button></form>');
    }
    echo('<br>')
    ?>
</div>
<div>
    <a href="index.html" id="left">
        <button id="left_button" type="button">&lt;</button>
    </a>
</div>
<div>
    <form method="get" action="songs.php" id="right"><input type="hidden" name="number" value="2">
        <button id="right_button" type="submit">&gt;</button>
    </form>
</div>
</body>
</html>
