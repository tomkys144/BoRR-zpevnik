<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/skautis_manager.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-32">
    <title>BoRR zpěvník - Admin</title>
    <link rel="icon" href="data/imgs/borr.png">
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
<?php
if ($_SERVER["HTTP_HOST"] !== 'localhost:8080') {
    $skautisUser = $skautis->getUser();
    if ($skautisUser->isLoggedIn(true)) {
        if (isAdmin() === false) {
            echo(
            '<div style="width: 100vw; height: 100vh; background-color: red; text-align: center; font-size: 2em; font-weight: bold; color: white">
            <p style="position: center">Uživatel nemá práva</p>
             </div>'
            );
            sleep(5);
            header('Location: /index.php');
            exit();
        } else {
            $ID = $skautisUser->getLoginId();
            $params = ['ID' => $ID];
            $logoutTime = json_decode(json_encode($skautis->UserManagement->loginUpdateRefresh($params)), true);
        }
    } else {
        login();
    }
}
?>
<div style="position: absolute; top: 0; z-index: 1">
    <a href="index.php"><button class="icon_home"></button></a>
    <a href="help.html"><button class="icon_help"></button></a>
    <div class="icon_user">
        <button class="icon_user-btn"></button>
        <div class="icon_user-content">
            <?php
            $skautisUser = $skautis->getUser();
            if ($skautisUser->isLoggedIn(true) || $_SERVER["HTTP_HOST"] === 'localhost:8080') {
                echo (
                    '<a href="favourite_songs.php"><button type="button" class="icon_user-included">Oblíbené</button></a><br>
                    <a href="editor.php"><button type="button" class="icon_user-included">Editor</button></a><br>
                    <form method="get" action="skautis_manager.php">
                    <input type="hidden" name="logout" value="https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '">
                    <input class="icon_user-included" type="submit" value="Odhlásit se">
                    </form>'
                );
            }
            else {
                $_SESSION['backlink'] = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                echo (
                    '<a href="' . $skautis->getLoginUrl("https://zpevnik-borr.skauting.cz/index.php") . '"><button class="icon_user-included" type="button">Přihlásit se</button></a>'
                );
            }
            ?>
        </div>
    </div>
</div>
<div style="position: absolute; width: 64vw; left: 18vw;top: 0; height: 100%">
    <h1>Admin</h1>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_REQUEST['chooseSong'])) {
        echo('<div style="position: absolute; top: 12em; width: 50vw; left: 50%; transform: translate(-50%); height: calc(100vh - 12em); overflow: auto">');
        $songList = array();
        $files = scandir(__DIR__ . '/data/songs/');
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $file));
            $songList[] = $object->matter('title');
        }
        $backupDirs = scandir(__DIR__ . '/data/backup/');
        foreach ($backupDirs as $dir) {
            $backupFiles = scandir(__DIR__ . '/data/backup/' . $dir . '/');
            $backupFiles = array_diff($backupFiles, ['.', '..']);
            $backupFiles = array_values($backupFiles);
            $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/backup/' . $dir . '/' . $backupFiles[0]));
            if (!in_array($object->matter('title'), $songList)) {
                if ($object->matter('title') == '') {
                    continue;
                }
                $songList[] = $object->matter('title');
                asort($songList);
                $songList = array_values($songList);
                $key = array_search($object->matter('title'), $songList);
                $songList[$key] = '<i>' . $object->matter('title') . '</i>';
            } else {
                continue;
            }
        }
        foreach ($songList as $song) {
            $fname = str_replace(' ', '_', $song);
            $fname = str_replace('<i>', '', $fname);
            $fname = str_replace('</i>', '', $fname);
            $fname = iconv('utf-8', 'ascii//TRANSLIT', $fname);
            $fname = str_replace(str_split("\:'*?<>.,!"), "", $fname);
            $fname = strtolower($fname);
            $fname .= ".md";
            echo(
                '<a href="/admin.php?songFile=' . $fname . '"><button class="song_selector" style="text-align: center; width: 100%" type="submit">' . $song . '</button></a><br>'
            );
        }
        echo('</div>');
    } elseif (isset($_REQUEST['songFile'])) {
        $backupDir = str_replace('.md', '', $_REQUEST['songFile']);
        if (isset($_REQUEST['createBackup'])) {
            $fileContents = file_get_contents(__DIR__ . '/data/songs/' . $_REQUEST['songFile']);
            if (!file_exists(__DIR__ . '/data/backup/' . $backupDir)) {
                mkdir(__DIR__ . '/data/backup/' . $backupDir);
            }
            $file = fopen(__DIR__ . '/data/backup/' . $backupDir . '/' . date('Y-m-d_H-i-s'), 'w');
            fwrite($file, $fileContents);
            fclose($file);
        } elseif (isset($_REQUEST['deleteSong'])) {
            $fileContents = file_get_contents(__DIR__ . '/data/songs/' . $_REQUEST['songFile']);
            if (!file_exists(__DIR__ . '/data/backup/' . $backupDir)) {
                mkdir(__DIR__ . '/data/backup/' . $backupDir);
            }
            $file = fopen(__DIR__ . '/data/backup/' . $backupDir . '/' . date('Y-m-d_H-i-s'), 'w');
            fwrite($file, $fileContents);
            fclose($file);
            unlink(__DIR__ . '/data/songs/' . $_REQUEST['songFile']);
        } elseif (isset($_REQUEST['rollback'])) {
            $oldFileContents = file_get_contents(__DIR__ . '/data/songs/' . $_REQUEST['songFile']);
            $newBackup = fopen(__DIR__ . '/data/backup/' . $backupDir . '/' . date('Y-m-d_H-i-s'), 'w');
            fwrite($newBackup, $oldFileContents);
            fclose($newBackup);

            $newFileContents = file_get_contents(__DIR__ . '/data/backup/' . $backupDir . '/' . $_REQUEST['version']);
            $file = fopen(__DIR__ . '/data/songs/' . $_REQUEST['songFile'], 'w');
            fwrite($file, $newFileContents);
            fclose($file);
        }
        $backupFile = $_REQUEST['backupFile'];
        echo (
                '<div style="width: 47vw; height: 6em; position: absolute; top: 12em; left: 50%; transform: translate(-48vw);">'
        );
        $qbackupFile = "'" . $backupFile . "'";
        $qsongFile = "'" . $_REQUEST['songFile'] . "'";
        echo (
                '<a href="admin.php?chooseSong"><button type="button" style="background: transparent; border: 1px solid darkgray; font-size: 1em; height: 4em">Vybrat písničku</button></a>
                <button onclick="rollbackSong(' . $qsongFile . ',' . $qbackupFile . ')" type="button" style="background: transparent; border: 1px solid darkgray; font-size: 1em; height: 4em">Vrátit tuto verzi</button>
                <button onclick="deleteSong(' . $qsongFile . ')" type="button" style="background: transparent; border: 1px solid darkgray; font-size: 1em; height: 4em">Smazat písničku</button>'
        );
        echo ('</div>');
        echo ('<div style="height: max-content; max-height: 8em; width: 47vw; left: 50%; transform: translate(1vw); top: 12em; position: absolute; overflow: auto; direction: rtl; text-align: center; border: 1px solid darkgray">');
        if (file_exists(__DIR__ . '/data/backup/' . $backupDir . '/')) {
            echo ('<ul style="margin: 0; padding: 0">');
            foreach (scandir(__DIR__ . '/data/backup/' . $backupDir . '/') as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                echo('<a href="/admin.php?songFile=' . $_REQUEST['songFile'] . '&backupFile=' . $file . '"><button type="button" style="direction: rtl; width: calc(47vw - 20px); font-size: 1em; background: transparent; border: none; margin: 10px">' . $file . '</button></a><br>');
            }
            echo ('</ul>');
        } else {
            echo ('Neexistuje záloha této písničky.<br>');
            echo ('<a href="/admin.php?songFile=' . $_REQUEST['songFile'] . '&createBackup"><button type="button" style="direction: rtl; width: calc(47vw - 20px); font-size: 1em; background: transparent; border: none; margin: 10px">Vytvořit</button></a>');
        }
        echo ('</div>');
        if (file_exists(__DIR__ . '/data/songs/' . $_REQUEST['songFile'])) {
            echo('<div style="position: absolute; top: 20em; width: 47vw; left: 50%; transform: translate(-48vw); height: max-content; min-height: 60vh; border: 1px solid darkgray"><pre style="white-space: pre-wrap">' . htmlspecialchars(file_get_contents(__DIR__ . '/data/songs/' . $_REQUEST['songFile'])) . '</pre></div>');
        } else {
            echo ('<div style="position: absolute; top: 20em; width: 47vw; left: 50%; transform: translate(-48vw); height: max-content; min-height: 60vh; border: 1px solid darkgray">V produkční verzi tato skladba není.</div><br>');
        }
        if (isset($backupFile)) {
            echo('<div style="position: absolute; top: 20em; width: 47vw; left: 50%; transform: translate(1vw); height: max-content; min-height: 60vh; border: 1px solid darkgray"><pre style="white-space: pre-wrap">' . htmlspecialchars(file_get_contents(__DIR__ . '/data/backup/' . $backupDir . '/' . $backupFile)) . '</pre></div>');
        } else {
            echo ('<div style="position: absolute; top: 20em; width: 47vw; left: 50%; transform: translate(1vw); height: max-content; min-height: 60vh; border: 1px solid darkgray">Zvolte zálohovanou verzi</div>');
        }
    } else {
        header('Location: /admin.php?chooseSong');
        exit();
    }
}
?>
<script>
    function rollbackSong(song, version) {
        if (confirm("Opravdu chcete vrátit tuto verzi?")) {
            window.location.replace("/admin.php?songFile=" + song + "&version=" + version + "&rollback");
        }
    }
    function deleteSong(song) {
        if (confirm("opravdu chcete smazat tuto písničku?")) {
            window.location.replace("/admin.php?songFile=" + song + "&deleteSong")
        }
    }
</script>
</body>
</html>