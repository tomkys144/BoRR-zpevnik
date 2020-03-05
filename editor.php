<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník</title>
    <link rel="icon" href="data/borr.png">
    <link rel="stylesheet" href="css.css">
</head>
<body>
<?
$_SESSION['backlink'] = 'https://zpevnik-borr.skauting.cz/editor.php';
require __DIR__ . '/skautis_manager.php';
$skautisUser = $skautis->getUser();
if (!$skautisUser->isLoggedIn(true)) {
    login();
}
$userDetail      = $skautis->UserManagement->UserDetail();
$userMemberships = $skautis->OrganizationUnit->MembershipAllPerson( [
   'ID_Person'   => $userDetail->ID_Person,
   'ShowHistory' => false,
   'isValid'     => true
] );
foreach ($userMemberships as $membership) {
    if ($membership['RegistrationNumber' === '219.09.006']) {
        $isMember = true;
        continue;
    }
}
if ($isMember != true) {
    echo (
        '<div style="width: 100vw; height: 100vh; background-color: red; text-align: center; font-size: 2em; font-weight: bold; color: white">
        <p style="position: center">Uživatel nemá práva</p>
         </div>'
    );
    sleep(5);
    header('Location: index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_REQUEST['title'];
    $author = $_REQUEST['author'];
    $capo = $_REQUEST['capo'];
    $body = $_REQUEST['body'];

    if ($title != null) {
        $name = str_replace(' ', '_', $title);
        $name = strtolower($name);
        $name = iconv('utf-8', 'ascii//TRANSLIT', $name);

        if ($capo === 0) {
            $capo = null;
        }

        $file = fopen(__DIR__ . '/songs/' . $name . '.md', 'w');
        fwrite($file, "---\n");
        fwrite($file, "title: '" . $title . "'\n");
        fwrite($file, "author: '" . $author . "'\n");
        fwrite($file, "capo: '" . $capo . "'\n");
        fwrite($file, "---\n");
        fwrite($file, "\n");
        fwrite($file, $body);
        fclose($file);

        echo(
            '<div class="success">Písnička ' . $title . ' byla úspěšně přidána!</div>'
        );
    }
}
?>
<div>
    <div class="icon_user">
        <button class="icon_user-btn"></button>
        <div class="icon_user-content">
            <form method="get" action="skautis_manager.php">
                <input type="hidden" name="logout">
                <input class="icon_user-included" type="submit" value="logout">
            </form>
        </div>
    </div>
</div>
<h1>Editor písniček</h1>
<div class="editor">
    <form method="post" id="input_form">
        Název: <input class="editor" type="text" name="title" id="title" required><br>
        Autor: <input class="editor" type="text" name="author" id="author" required><br>
        Capo: <input class="editor" type="number" name="capo" id="capo" required value="0"><br>
        <button class="editor" type="submit">Dokončit</button>
    </form>
    <br>
    <script>
        function autoGrow(element) {
            element.style.height = "5px";
            element.style.height = (element.scrollHeight) + "px";
        }

        function previewMaker() {
            var text = document.getElementById('song').value;
            document.getElementById('preview').innerHTML = text;
        }

        function onInputFnc(element) {
            autoGrow(element);
            previewMaker();
        }

        function addVerse() {
            var number = prompt('Číslo sloky:', '1');
            var text = document.getElementById('song').value;
            number = number.replace(/ /g, '&nbsp;');
            document.getElementById('song').value = text + '<verse number="' + number + ':"></verse>';
        }

        function addChord() {
            var chord = prompt('Akord:', 'C');
            var text = document.getElementById('song').value;
            chord = chord.replace(/ /g, '&nbsp;');
            document.getElementById('song').value = text + '<wrapper><chord>' + chord + '</chord></wrapper>';
        }

        function addBreak() {
            var text = document.getElementById('song').value;
            document.getElementById('song').value = text + '<br>' + '\n';
        }
    </script>
    <div style="width: max-content; left: 2vw; bottom: 10px; margin: 10px; position: sticky">
        <button onclick="addVerse()" class="editor_button">Přidat sloku</button>
        <button onclick="addChord()" class="editor_button">Přidat akord</button>
        <button onclick="addBreak()" class="editor_button">Přidat konec řádku</button>
    </div>
    <textarea wrap="soft" oninput="onInputFnc(this)" class="editor" style="transform: translate(-48vw)" name="body"
              form="input_form" id="song" required></textarea>
    <div class="editor"
         style="position: absolute; width: 46vw; left: 50%; margin: 0; resize: none; overflow: hidden; height: max-content; transform: translate(2vw)">
        <p onchange="autoGrow(this)" style="width: 40vw; transform: translate(6vw)" id="preview"></p>
    </div>
</div>
</body>
</html>