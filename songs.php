<?php
require_once __DIR__ . '/vendor/autoload.php';
$files = scandir(__DIR__ . '/songs/');
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $song = $_REQUEST['number'];
    if ($song <= 1) {
        header("Location: list.php");
        exit();
    }
    else {
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $files[$song]));
    $next_song = $song + 1;
    $previous_song = $song - 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-32">
    <title>BoRR zpěvník - <?php echo($object->matter("title")) ?></title>
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
<div style="position: absolute; top: 0">
    <a href="index.php"><button class="icon_home"></button></a>
    <a href="list.php"><button class="icon_list"></button></a>
    <a href="help.html"><button class="icon_help"></button></a>
    <div class="icon_user">
        <button class="icon_user-btn"></button>
        <div class="icon_user-content">
            <form method="get" action="skautis_manager.php">
                <input type="hidden" name="logout" value="<?php echo('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
                <input class="icon_user-included" type="submit" value="logout">
            </form>
        </div>
    </div>
</div>
<div style="position: absolute; width: 64vw; left: 18vw;top: 0; max-height: 265px">
    <h1><?php echo($object->matter('title')) ?></h1>
    <h2><?php echo($object->matter('author')) ?></h2>
    <div class="song_body"><p id="song_text"><?php echo($object->body()) ?></p></div>
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
</body>
</html>