<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Songs</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <div>
        <?php
            error_reporting(E_ALL);
            require_once __DIR__.'/vendor/autoload.php';
            $files = scandir(__DIR__.'/songs/');
            $song = 2;
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
            echo('<h1>' . $object->matter('title') . '</h1>');
            echo('<h2>' . $object->matter('author') . '</h2>');
            echo($object->body());
        ?>
    </div>
</body>
</html>