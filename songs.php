<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Songs</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <?php
        error_reporting(E_ALL);
        require_once __DIR__.'/vendor/autoload.php';
        $files = scandir(__DIR__.'/songs/');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $song = $_REQUEST['number'];
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
            echo('<h1>' . $object->matter('title') . '</h1>');
            echo('<h2>' . $object->matter('author') . '</h2>');
            $next_song = $song+1;
            $previous_song = $song - 1;
            echo(
                '<div>' . $object->body() . '</div>
                <div><form method="post"><input type="hidden" name="number" value="' . $next_song . '"><button id="right_button" type="submit">></button></form></div>
                <div><form method="post"><input type="hidden" name="number" value="' . $previous_song . '"><button id="left_button" type="submit"><</button></form></div>'
            );
        }
        else {
            $song = 2;
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
            echo('<h1>' . $object->matter('title') . '</h1>');
            echo('<h2>' . $object->matter('author') . '</h2>');
            $next_song = $song+1;
            $previous_song = $song - 1;
            echo(
                '<div>' . $object->body() . '</div>
                <div><form method="post"><input type="hidden" name="number" value="' . $next_song . '"><button id="right_button" type="submit">></button></form></div>
                <div><form method="post"><input type="hidden" name="number" value="' . $previous_song . '"><button id="left_button" type="submit"><</button></form></div>'
            );
        }
    ?>
</body>
</html>