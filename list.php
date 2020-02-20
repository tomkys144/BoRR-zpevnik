<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník</title>
    <link rel="icon" href="borr.png">
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <div>
        <h1>
            Rejstřík
        </h1>
    </div>
    <div style="width: 30vw; left: 40vw; top: 12em; position: absolute ; text-align: left; margin: 0">
        <?php
            require_once __DIR__.'/vendor/autoload.php';
            $files = scandir(__DIR__.'/songs/');
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') {
                    continue;
                }
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $f));
                echo('<a href="songs/' . $f . '">' . $object->matter('title') . '</a>');
            }
        ?>
    </div>
    <div style="width: 30vw; right: 40vw; top: 12em; position: absolute ; text-align: right; margin: 0">
        <?php
        require_once __DIR__.'/vendor/autoload.php';
        $files = scandir(__DIR__.'/songs/');
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $f));
            echo('<a href="songs/' . $f . '">' . $object->matter('author') . '</a>');
        }
        ?>
    </div>
    <div>
        <button id="left_button" onclick="location.href='index.html'" type="button"><</button>
    </div>
    <div>
        <button id="right_button" onclick="location.href='songs.php'" type="button">></button>
    </div>
</body>
</html>