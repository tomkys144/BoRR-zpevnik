<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rejstřík</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <div>
        <?php
            error_reporting(E_ALL);
            require_once __DIR__.'/vendor/autoload.php';
            $files = scandir(__DIR__.'/songs/');
            $list = array();
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') {
                    continue;
                }
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents($f));
                array_push($list, '<a href="songs/' . $f . '">' . $object->matter('title') . ' - ' . $object->matter('author') . '</a>');
            }
            print_r($list);
        ?>
    </div>
    <div>
        <button id="left_button" onclick="location.href='index.html'" type="button"><</button>
    </div>
    <div>
        <button id="right_button" onclick="location.href='songs.html'" type="button">></button>
    </div>
</body>
</html>