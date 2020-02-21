    <?php
        require_once __DIR__.'/vendor/autoload.php';
        $files = scandir(__DIR__.'/songs/');
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $song = $_REQUEST['number'];
            if ($song <= 1) {
                header("Location: list.php");
                exit;
            }
            elseif ($song == count($files)-1) {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
                echo('<div style="position: absolute; width: 64vw; left: 18vw;top: 0"><h1>' . $object->matter('title') . '</h1>');
                echo('<h2>' . $object->matter('author') . '</h2></div>');
                $previous_song = $song - 1;
                if ($object->matter('capo') != null){
                    echo('<div class="capo">Capo ' . $object->matter('capo') . '</div>');
                }
                echo(
                    '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <title>BoRR zpěvník - ' . $object->matter('title') . '</title>
                        <link rel="icon" href="data/borr.png">
                        <link rel="stylesheet" href="css.css">
                    </head>
                    <body>
                    <div style="position: absolute; top: 0">
                        <a href="index.html"><button class="icon_home"></button></a>
                        <a href="list.php"><button class="icon_list"></button></a>
                        <a href="help.html"><button class="icon_help"></button></a>
                    </div>
                    <div class="song_body"><p id="song_text">' . $object->body() . '</p></div>
                    <div><form method="get"><input type="hidden" name="number" value="' . $previous_song . '"><button id="left_button" type="submit"><</button></form></div>'
                );
            }
            else {
                $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__.'/songs/' . $files[$song]));
                echo('<h1>' . $object->matter('title') . '</h1>');
                echo('<h2>' . $object->matter('author') . '</h2>');
                $next_song = $song+1;
                $previous_song = $song - 1;
                echo(
                    '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <title>BoRR zpěvník - ' . $object->matter('title') . '</title>
                        <link rel="icon" href="data/borr.png">
                        <link rel="stylesheet" href="css.css">
                    </head>
                    <body>
                    <div style="position: absolute; top: 0">
                        <a href="index.html"><button class="icon_home"></button></a>
                        <a href="list.php"><button class="icon_list"></button></a>
                        <a href="help.html"><button class="icon_help"></button></a>
                    </div>
                    <div class="song_body"><p id="song_text">' . $object->body() . '</p></div>
                    <div><form method="get"><input type="hidden" name="number" value="' . $next_song . '"><button id="right_button" type="submit">></button></form></div>
                    <div><form method="get"><input type="hidden" name="number" value="' . $previous_song . '"><button id="left_button" type="submit"><</button></form></div>'
                );
            }
        }
        ?>
</body>
</html>