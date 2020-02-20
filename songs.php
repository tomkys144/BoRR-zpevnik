<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník</title>
    <link rel="icon" href="borr.png">
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <?php
        require_once __DIR__.'/vendor/autoload.php';
        $files = scandir(__DIR__.'/songs/');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $song = $_REQUEST['number'];
            if ($song <= 1) {
                header("Location: list.php");
                exit;
            }
            elseif ($song === count($files)-1) {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
                echo('<h1>' . $object->matter('title') . '</h1>');
                echo('<h2>' . $object->matter('author') . '</h2>');
                $previous_song = $song - 1;
                echo(
                    '<div>' . $object->body() . '</div>
                <div><form method="post"><input type="hidden" name="number" value="' . $previous_song . '"><button id="left_button" type="submit"><</button></form></div>'
                );
            }
            else {
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
        }
        else {
            $song = 2;
            if ($song === count($files)-1) {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
                echo('<h1>' . $object->matter('title') . '</h1>');
                echo('<h2>' . $object->matter('author') . '</h2>');
                $previous_song = $song - 1;
                echo(
                    '<div>' . $object->body() . '</div>
                <div><form method="post"><input type="hidden" name="number" value="' . $previous_song . '"><button id="left_button" type="submit"><</button></form></div>'
                );
            }
            else {
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
        }
    ?>
</body>
</html>